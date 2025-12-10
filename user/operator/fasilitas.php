<?php
/**
 * @var PDO $pdo
 * @var string $current_page
 * @var int $current_id_user
 */
$required_role = "operator";
include "../auth.php";
include "../../conn.php";

$page_title = "Fasilitas";
$current_page = "fasilitas.php";

// Handle Delete
if (isset($_GET['delete'])) {
    try {
        $stmt_check = $pdo->prepare("SELECT id_user, status, judul FROM fasilitas WHERE id_fasilitas = ?");
        $stmt_check->execute([$_GET['delete']]);
        $old_data = $stmt_check->fetch();
        
        if ($old_data && $old_data['id_user'] == $_SESSION['id_user'] && $old_data['status'] == 'pending') {
            $stmt = $pdo->prepare("DELETE FROM fasilitas WHERE id_fasilitas = ?");
            $stmt->execute([$_GET['delete']]);
            
            $stmt_riwayat = $pdo->prepare("INSERT INTO riwayat_pengajuan (tabel_sumber, id_data, id_operator, status_lama, status_baru, catatan) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt_riwayat->execute(['fasilitas', $_GET['delete'], $_SESSION['id_user'], $old_data['status'], 'deleted', 'Hapus fasilitas: ' . $old_data['judul']]);
            
            $success = "Fasilitas berhasil dihapus!";
        } else {
            $error = "Anda hanya bisa menghapus data pending milik Anda!";
        }
    } catch (PDOException $e) {
        $error = "Gagal menghapus: " . $e->getMessage();
    }
}

// Handle Add/Edit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $judul = $_POST['judul'];
    $deskripsi = $_POST['deskripsi'];
    $kategori_fasilitas = $_POST['kategori_fasilitas'];
    $status = 'pending';
    
    $gambar = null;
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
        $target_dir = "../../uploads/fasilitas/";
        if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);
        
        $file_extension = pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION);
        $gambar = 'fasilitas_' . time() . '.' . $file_extension;
        move_uploaded_file($_FILES['gambar']['tmp_name'], $target_dir . $gambar);
    }
    
    try {
        if (isset($_POST['id_fasilitas']) && !empty($_POST['id_fasilitas'])) {
            $id = $_POST['id_fasilitas'];
            
            // Cek data yang akan diedit
            $stmt_check = $pdo->prepare("SELECT id_user, status, gambar FROM fasilitas WHERE id_fasilitas = ?");
            $stmt_check->execute([$id]);
            $existing_data = $stmt_check->fetch();
            
            if (!$existing_data) {
                $error = "Data tidak ditemukan!";
            } else {
                // Jika data milik operator sendiri dan masih pending/rejected, langsung update
                if ($existing_data['id_user'] == $_SESSION['id_user'] && 
                    ($existing_data['status'] == 'pending' || $existing_data['status'] == 'rejected')) {
                    
                    $status_lama = $existing_data['status'];
                    
                    // Jika tidak upload gambar baru, pakai gambar lama
                    if (!$gambar) {
                        $gambar = $existing_data['gambar'];
                    }
                    
                    $stmt = $pdo->prepare("UPDATE fasilitas SET judul=?, deskripsi=?, kategori_fasilitas=?, gambar=?, status=?, id_user=? WHERE id_fasilitas=?");
                    $stmt->execute([$judul, $deskripsi, $kategori_fasilitas, $gambar, $status, $_SESSION['id_user'], $id]);
                    
                    $stmt_riwayat = $pdo->prepare("INSERT INTO riwayat_pengajuan (tabel_sumber, id_data, id_operator, status_lama, status_baru, catatan) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt_riwayat->execute(['fasilitas', $id, $_SESSION['id_user'], $status_lama, $status, 'Update fasilitas: ' . $judul]);
                    
                    $success = "Fasilitas berhasil diupdate! Menunggu persetujuan admin.";
                    
                } else {
                    // Jika data sudah active (milik siapapun), buat pengajuan edit baru
                    // Simpan gambar lama jika tidak upload baru
                    if (!$gambar) {
                        $gambar = $existing_data['gambar'];
                    }
                    
                    // Insert data baru dengan status pending
                    $stmt = $pdo->prepare("INSERT INTO fasilitas (judul, deskripsi, kategori_fasilitas, gambar, status, id_user) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$judul, $deskripsi, $kategori_fasilitas, $gambar, $status, $_SESSION['id_user']]);
                    
                    $new_id = $pdo->lastInsertId();
                    
                    $stmt_riwayat = $pdo->prepare("INSERT INTO riwayat_pengajuan (tabel_sumber, id_data, id_operator, status_lama, status_baru, catatan) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt_riwayat->execute(['fasilitas', $new_id, $_SESSION['id_user'], null, $status, 'Pengajuan edit fasilitas (dari ID: ' . $id . '): ' . $judul]);
                    
                    $success = "Pengajuan edit fasilitas berhasil dibuat! Menunggu persetujuan admin. Data baru akan menggantikan data lama setelah disetujui.";
                }
            }
        } else {
            // Tambah baru
            $stmt = $pdo->prepare("INSERT INTO fasilitas (judul, deskripsi, kategori_fasilitas, gambar, status, id_user) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$judul, $deskripsi, $kategori_fasilitas, $gambar, $status, $_SESSION['id_user']]);
            
            $new_id = $pdo->lastInsertId();
            
            $stmt_riwayat = $pdo->prepare("INSERT INTO riwayat_pengajuan (tabel_sumber, id_data, id_operator, status_lama, status_baru, catatan) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt_riwayat->execute(['fasilitas', $new_id, $_SESSION['id_user'], null, $status, 'Tambah fasilitas: ' . $judul]);
            
            $success = "Fasilitas berhasil ditambahkan! Menunggu persetujuan admin.";
        }
    } catch (PDOException $e) {
        $error = "Gagal menyimpan: " . $e->getMessage();
    }
}

// Filter untuk menampilkan data
$filter = $_GET['filter'] ?? 'all';

// Pagination
$items_per_page = 9;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $items_per_page;

// Build query berdasarkan filter
if ($filter == 'my') {
    // Hanya data milik operator
    $count_stmt = $pdo->prepare("SELECT COUNT(*) FROM fasilitas WHERE id_user = ?");
    $count_stmt->execute([$_SESSION['id_user']]);
    $total_items = $count_stmt->fetchColumn();
    
    $stmt = $pdo->prepare("SELECT * FROM fasilitas WHERE id_user = ? ORDER BY created_at DESC LIMIT ? OFFSET ?");
    $stmt->execute([$_SESSION['id_user'], $items_per_page, $offset]);
} else {
    // Semua data (all)
    $count_stmt = $pdo->query("SELECT COUNT(*) FROM fasilitas");
    $total_items = $count_stmt->fetchColumn();
    
    $stmt = $pdo->prepare("SELECT * FROM fasilitas ORDER BY created_at DESC LIMIT ? OFFSET ?");
    $stmt->execute([$items_per_page, $offset]);
}

$fasilitas_list = $stmt->fetchAll();
$total_pages = ceil($total_items / $items_per_page);

include "header.php";
include "sidebar.php";
include "navbar.php";
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Fasilitas Lab</h1>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#fasilitasModal" onclick="resetForm()">
        <i class="bi bi-plus-circle"></i> Tambah Fasilitas
    </button>
</div>

<?php if (isset($success)): ?>
    <div class="alert alert-success alert-dismissible fade show"><?php echo $success; ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>
<?php if (isset($error)): ?>
    <div class="alert alert-danger alert-dismissible fade show"><?php echo $error; ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<div class="alert alert-info">
    <i class="bi bi-info-circle"></i> 
    <strong>Info:</strong> Anda bisa mengedit semua fasilitas. Edit data yang sudah <span class="badge bg-success">Active</span> akan membuat pengajuan baru dengan status <span class="badge bg-warning text-dark">Pending</span>.
</div>

<!-- Filter Tabs -->
<ul class="nav nav-tabs mb-3">
    <li class="nav-item">
        <a class="nav-link <?php echo $filter == 'all' ? 'active' : ''; ?>" href="?filter=all">
            <i class="bi bi-list-ul"></i> Semua Fasilitas
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?php echo $filter == 'my' ? 'active' : ''; ?>" href="?filter=my">
            <i class="bi bi-person-circle"></i> Data Saya
        </a>
    </li>
</ul>

<div class="row">
    <?php if (count($fasilitas_list) > 0): ?>
        <?php foreach ($fasilitas_list as $fas): ?>
        <div class="col-md-4 mb-4">
            <div class="card shadow h-100">
                <?php if ($fas['gambar']): ?>
                    <img src="../../uploads/fasilitas/<?php echo $fas['gambar']; ?>" class="card-img-top" style="height: 200px; object-fit: cover;">
                <?php else: ?>
                    <div class="bg-secondary d-flex align-items-center justify-content-center text-white" style="height: 200px;">
                        <i class="bi bi-image" style="font-size: 3rem;"></i>
                    </div>
                <?php endif; ?>
                <div class="card-body">
                    <div class="mb-2">
                        <span class="badge bg-info"><?php echo htmlspecialchars($fas['kategori_fasilitas'] ?? 'Umum'); ?></span>
                        <?php if ($fas['status'] == 'pending'): ?>
                            <span class="badge bg-warning">Pending</span>
                        <?php elseif ($fas['status'] == 'active'): ?>
                            <span class="badge bg-success">Active</span>
                        <?php else: ?>
                            <span class="badge bg-danger">Rejected</span>
                        <?php endif; ?>
                        
                        <?php if ($fas['id_user'] == $_SESSION['id_user']): ?>
                            <span class="badge bg-secondary">
                                <i class="bi bi-person"></i> Milik Saya
                            </span>
                        <?php endif; ?>
                    </div>
                    <h5 class="card-title"><?php echo htmlspecialchars($fas['judul']); ?></h5>
                    <p class="card-text text-muted small"><?php echo htmlspecialchars(substr($fas['deskripsi'] ?? '', 0, 100)); ?><?php echo strlen($fas['deskripsi'] ?? '') > 100 ? '...' : ''; ?></p>
                </div>
                <div class="card-footer bg-white">
                    <button class="btn btn-sm btn-warning" onclick='editFasilitas(<?php echo json_encode($fas); ?>)'>
                        <i class="bi bi-pencil"></i> Edit
                    </button>
                    
                    <?php if ($fas['id_user'] == $_SESSION['id_user'] && $fas['status'] == 'pending'): ?>
                        <a href="?delete=<?php echo $fas['id_fasilitas']; ?>&page=<?php echo $page; ?>&filter=<?php echo $filter; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin hapus?')">
                            <i class="bi bi-trash"></i> Hapus
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="col-12">
            <div class="alert alert-info text-center">
                <i class="bi bi-info-circle"></i> Belum ada data fasilitas
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Pagination -->
<?php if ($total_pages > 1): ?>
<nav aria-label="Page navigation">
    <ul class="pagination justify-content-center">
        <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
            <a class="page-link" href="?page=<?php echo $page - 1; ?>&filter=<?php echo $filter; ?>">Previous</a>
        </li>
        
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                <a class="page-link" href="?page=<?php echo $i; ?>&filter=<?php echo $filter; ?>"><?php echo $i; ?></a>
            </li>
        <?php endfor; ?>
        
        <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
            <a class="page-link" href="?page=<?php echo $page + 1; ?>&filter=<?php echo $filter; ?>">Next</a>
        </li>
    </ul>
</nav>
<?php endif; ?>

<!-- Modal -->
<div class="modal fade" id="fasilitasModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Tambah Fasilitas</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id_fasilitas" id="id_fasilitas">
                    
                    <div class="alert alert-warning" id="alertEdit" style="display: none;">
                        <i class="bi bi-info-circle"></i> Anda mengedit fasilitas yang sudah <strong>Active</strong>. Perubahan akan membuat pengajuan baru dan menunggu approval admin.
                    </div>
                    
                    <div class="alert alert-info" id="alertNew">
                        <i class="bi bi-info-circle"></i> Data akan berstatus <strong>Pending</strong> dan menunggu persetujuan admin
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Judul *</label>
                        <input type="text" class="form-control" name="judul" id="judul" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Gambar</label>
                        <div id="currentImage" style="display: none;" class="mb-2">
                            <img id="previewImage" src="" class="img-thumbnail" style="max-height: 150px;">
                            <small class="d-block text-muted">Gambar saat ini</small>
                        </div>
                        <input type="file" class="form-control" name="gambar" id="inputGambar" accept="image/*">
                        <small class="text-muted">Kosongkan jika tidak ingin mengubah gambar</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Kategori *</label>
                        <select class="form-select" name="kategori_fasilitas" id="kategori_fasilitas" required>
                            <option value="">-- Pilih Kategori --</option>
                            <option value="Ruang Praktikum & Penelitian">Ruang Praktikum & Penelitian</option>
                            <option value="Perangkat Lunak">Perangkat Lunak</option>
                            <option value="Perangkat Komputer">Perangkat Komputer</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea class="form-control" name="deskripsi" id="deskripsi" rows="4"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-send"></i> Simpan & Ajukan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function resetForm() {
    document.getElementById('modalTitle').textContent = 'Tambah Fasilitas';
    document.querySelector('form').reset();
    document.getElementById('id_fasilitas').value = '';
    document.getElementById('alertEdit').style.display = 'none';
    document.getElementById('alertNew').style.display = 'block';
    document.getElementById('currentImage').style.display = 'none';
}

function editFasilitas(data) {
    document.getElementById('modalTitle').textContent = 'Edit Fasilitas';
    document.getElementById('id_fasilitas').value = data.id_fasilitas;
    document.getElementById('judul').value = data.judul;
    document.getElementById('kategori_fasilitas').value = data.kategori_fasilitas || '';
    document.getElementById('deskripsi').value = data.deskripsi || '';
    
    // Show current image if exists
    if (data.gambar) {
        document.getElementById('currentImage').style.display = 'block';
        document.getElementById('previewImage').src = '../../uploads/fasilitas/' + data.gambar;
    } else {
        document.getElementById('currentImage').style.display = 'none';
    }
    
    // Show appropriate alert
    if (data.status === 'active') {
        document.getElementById('alertEdit').style.display = 'block';
        document.getElementById('alertNew').style.display = 'none';
    } else {
        document.getElementById('alertEdit').style.display = 'none';
        document.getElementById('alertNew').style.display = 'block';
    }
    
    new bootstrap.Modal(document.getElementById('fasilitasModal')).show();
}
</script>

<?php include "footer.php"; ?>