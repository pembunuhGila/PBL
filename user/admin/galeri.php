<?php
/**
 * @var PDO $pdo
 * @var string $current_page
 * @var int $current_id_user
 */
$required_role = "admin";
include "../auth.php";
include "../../conn.php";

$page_title = "Fasilitas";
$current_page = "fasilitas.php";

// Handle Approve/Reject
if (isset($_GET['approve']) || isset($_GET['reject'])) {
    $id = isset($_GET['approve']) ? $_GET['approve'] : $_GET['reject'];
    $new_status = isset($_GET['approve']) ? 'active' : 'rejected';
    
    try {
        $stmt_old = $pdo->prepare("SELECT judul, status FROM fasilitas WHERE id_fasilitas = ?");
        $stmt_old->execute([$id]);
        $old_data = $stmt_old->fetch();
        
        $stmt = $pdo->prepare("UPDATE fasilitas SET status = ? WHERE id_fasilitas = ?");
        $stmt->execute([$new_status, $id]);
        
        $stmt_riwayat = $pdo->prepare("INSERT INTO riwayat_pengajuan (tabel_sumber, id_data, id_admin, status_lama, status_baru, catatan) VALUES (?, ?, ?, ?, ?, ?)");
        $catatan = isset($_GET['approve']) ? 'DISETUJUI fasilitas: ' . $old_data['judul'] : 'DITOLAK fasilitas: ' . $old_data['judul'];
        $stmt_riwayat->execute(['fasilitas', $id, $_SESSION['id_user'], $old_data['status'], $new_status, $catatan]);
        
        header("Location: fasilitas.php?success=" . ($new_status == 'active' ? 'approved' : 'rejected') . "&page=" . (isset($_GET['page']) ? $_GET['page'] : 1));
        exit;
    } catch (PDOException $e) {
        $error = "Gagal: " . $e->getMessage();
    }
}

// Handle Delete
if (isset($_GET['delete'])) {
    try {
        $stmt_old = $pdo->prepare("SELECT judul, status FROM fasilitas WHERE id_fasilitas = ?");
        $stmt_old->execute([$_GET['delete']]);
        $old_data = $stmt_old->fetch();
        
        $stmt = $pdo->prepare("DELETE FROM fasilitas WHERE id_fasilitas = ?");
        $stmt->execute([$_GET['delete']]);
        
        $stmt_riwayat = $pdo->prepare("INSERT INTO riwayat_pengajuan (tabel_sumber, id_data, id_admin, status_lama, status_baru, catatan) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt_riwayat->execute(['fasilitas', $_GET['delete'], $_SESSION['id_user'], $old_data['status'], 'deleted', 'Hapus fasilitas: ' . $old_data['judul']]);
        
        header("Location: fasilitas.php?success=deleted&page=" . (isset($_GET['page']) ? $_GET['page'] : 1));
        exit;
    } catch (PDOException $e) {
        $error = "Gagal menghapus: " . $e->getMessage();
    }
}

// Handle Add/Edit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $judul = $_POST['judul'];
    $deskripsi = $_POST['deskripsi'];
    $kategori_fasilitas = $_POST['kategori_fasilitas'];
    $status = 'active';
    
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
            
            $stmt_old = $pdo->prepare("SELECT status FROM fasilitas WHERE id_fasilitas = ?");
            $stmt_old->execute([$id]);
            $old_data = $stmt_old->fetch();
            $status_lama = $old_data['status'];
            
            if ($gambar) {
                $stmt = $pdo->prepare("UPDATE fasilitas SET judul=?, deskripsi=?, kategori_fasilitas=?, gambar=?, status=? WHERE id_fasilitas=?");
                $stmt->execute([$judul, $deskripsi, $kategori_fasilitas, $gambar, $status, $id]);
            } else {
                $stmt = $pdo->prepare("UPDATE fasilitas SET judul=?, deskripsi=?, kategori_fasilitas=?, status=? WHERE id_fasilitas=?");
                $stmt->execute([$judul, $deskripsi, $kategori_fasilitas, $status, $id]);
            }
            
            if ($status_lama != $status) {
                $stmt_riwayat = $pdo->prepare("INSERT INTO riwayat_pengajuan (tabel_sumber, id_data, id_admin, status_lama, status_baru, catatan) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt_riwayat->execute(['fasilitas', $id, $_SESSION['id_user'], $status_lama, $status, 'Update fasilitas: ' . $judul]);
            }
            
            $success = "Fasilitas berhasil diupdate!";
        } else {
            $stmt = $pdo->prepare("INSERT INTO fasilitas (judul, deskripsi, kategori_fasilitas, gambar, status, id_user) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$judul, $deskripsi, $kategori_fasilitas, $gambar, $status, $_SESSION['id_user']]);
            
            $new_id = $pdo->lastInsertId();
            
            $stmt_riwayat = $pdo->prepare("INSERT INTO riwayat_pengajuan (tabel_sumber, id_data, id_admin, status_lama, status_baru, catatan) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt_riwayat->execute(['fasilitas', $new_id, $_SESSION['id_user'], null, $status, 'Tambah fasilitas: ' . $judul]);
            
            $success = "Fasilitas berhasil ditambahkan!";
        }
    } catch (PDOException $e) {
        $error = "Gagal menyimpan: " . $e->getMessage();
    }
}

// Pagination
$items_per_page = 9; // 3 kolom x 3 baris
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $items_per_page;

// Get total count
$count_stmt = $pdo->query("SELECT COUNT(*) FROM fasilitas");
$total_items = $count_stmt->fetchColumn();
$total_pages = ceil($total_items / $items_per_page);

// Get paginated data
$stmt = $pdo->prepare("SELECT f.*, u.nama as nama_pembuat FROM fasilitas f LEFT JOIN users u ON f.id_user = u.id_user ORDER BY f.created_at DESC LIMIT ? OFFSET ?");
$stmt->execute([$items_per_page, $offset]);
$fasilitas_list = $stmt->fetchAll();

// Get pending count
$pending_count_query = "SELECT COUNT(*) FROM fasilitas WHERE status = 'pending'";
$pending_count_stmt = $pdo->prepare($pending_count_query);
$pending_count_stmt->execute();
$pending_count = $pending_count_stmt->fetchColumn();

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

<?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <?php 
        if ($_GET['success'] == 'approved') echo "✅ Pengajuan fasilitas berhasil disetujui!";
        if ($_GET['success'] == 'rejected') echo "❌ Pengajuan fasilitas berhasil ditolak!";
        if ($_GET['success'] == 'deleted') echo "✅ Fasilitas berhasil dihapus!";
        ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if ($pending_count > 0): ?>
    <div class="alert alert-warning alert-dismissible fade show">
        <i class="bi bi-exclamation-circle"></i> Ada <strong><?php echo $pending_count; ?> fasilitas</strong> menunggu persetujuan Anda
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row">
    <?php if (count($fasilitas_list) > 0): ?>
        <?php foreach ($fasilitas_list as $fas): ?>
        <div class="col-md-4 mb-4">
            <div class="card shadow h-100 position-relative">
                <!-- Status Badge di pojok kanan atas -->
                <div class="position-absolute top-0 end-0 p-2" style="z-index: 10;">
                    <?php if ($fas['status'] == 'pending'): ?>
                        <span class="badge bg-secondary">Pending</span>
                    <?php elseif ($fas['status'] == 'active'): ?>
                        <span class="badge bg-success">Active</span>
                    <?php else: ?>
                        <span class="badge bg-danger">Rejected</span>
                    <?php endif; ?>
                </div>
                
                <?php if ($fas['gambar']): ?>
                    <img src="../../uploads/fasilitas/<?php echo $fas['gambar']; ?>" class="card-img-top" style="height: 200px; object-fit: cover;">
                <?php else: ?>
                    <div class="bg-secondary d-flex align-items-center justify-content-center text-white" style="height: 200px;">
                        <i class="bi bi-image" style="font-size: 3rem;"></i>
                    </div>
                <?php endif; ?>
                <div class="card-body">
                    <span class="badge bg-info mb-2"><?php echo htmlspecialchars($fas['kategori_fasilitas'] ?? 'Umum'); ?></span>
                    <small class="text-muted d-block mb-2">By: <?php echo htmlspecialchars($fas['nama_pembuat'] ?? 'Admin'); ?></small>
                    
                    <h5 class="card-title"><?php echo htmlspecialchars($fas['judul']); ?></h5>
                    <p class="card-text text-muted"><?php echo htmlspecialchars(substr($fas['deskripsi'] ?? '', 0, 100)); ?><?php echo strlen($fas['deskripsi'] ?? '') > 100 ? '...' : ''; ?></p>
                </div>
                <div class="card-footer bg-white">
                    <button class="btn btn-sm btn-warning" onclick='editFasilitas(<?php echo json_encode($fas); ?>)'>
                        <i class="bi bi-pencil"></i> Edit
                    </button>
                    
                    <?php if ($fas['status'] == 'pending'): ?>
                        <a href="?approve=<?php echo $fas['id_fasilitas']; ?>&page=<?php echo $page; ?>" class="btn btn-sm btn-success" onclick="return confirm('Setujui pengajuan ini?')">
                            <i class="bi bi-check"></i> Acc
                        </a>
                        <a href="?reject=<?php echo $fas['id_fasilitas']; ?>&page=<?php echo $page; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Tolak pengajuan ini?')">
                            <i class="bi bi-x"></i> Reject
                        </a>
                    <?php endif; ?>
                    
                    <a href="?delete=<?php echo $fas['id_fasilitas']; ?>&page=<?php echo $page; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin hapus?')">
                        <i class="bi bi-trash"></i> Hapus
                    </a>
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
            <a class="page-link" href="?page=<?php echo $page - 1; ?>">Previous</a>
        </li>
        
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
            </li>
        <?php endfor; ?>
        
        <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
            <a class="page-link" href="?page=<?php echo $page + 1; ?>">Next</a>
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
                    
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> Data akan langsung aktif setelah disimpan
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
                    <button type="submit" class="btn btn-primary">Simpan & Aktifkan</button>
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
    
    new bootstrap.Modal(document.getElementById('fasilitasModal')).show();
}
</script>

<?php include "footer.php"; ?>