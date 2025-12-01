<?php
$required_role = "operator";
include "../../auth.php";
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
    $status = 'pending'; // AUTO PENDING untuk operator
    
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
            
            $stmt_check = $pdo->prepare("SELECT id_user, status FROM fasilitas WHERE id_fasilitas = ?");
            $stmt_check->execute([$id]);
            $data_owner = $stmt_check->fetch();
            
            if ($data_owner && $data_owner['id_user'] == $_SESSION['id_user'] && $data_owner['status'] == 'pending') {
                $status_lama = $data_owner['status'];
                
                if ($gambar) {
                    $stmt = $pdo->prepare("UPDATE fasilitas SET judul=?, deskripsi=?, kategori_fasilitas=?, gambar=?, status=? WHERE id_fasilitas=?");
                    $stmt->execute([$judul, $deskripsi, $kategori_fasilitas, $gambar, $status, $id]);
                } else {
                    $stmt = $pdo->prepare("UPDATE fasilitas SET judul=?, deskripsi=?, kategori_fasilitas=?, status=? WHERE id_fasilitas=?");
                    $stmt->execute([$judul, $deskripsi, $kategori_fasilitas, $status, $id]);
                }
                
                $success = "Fasilitas berhasil diupdate! Menunggu persetujuan admin.";
            } else {
                $error = "Anda hanya bisa edit data pending milik Anda!";
            }
        } else {
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

$stmt = $pdo->prepare("SELECT * FROM fasilitas WHERE id_user = ? ORDER BY created_at DESC");
$stmt->execute([$_SESSION['id_user']]);
$fasilitas_list = $stmt->fetchAll();

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
    <i class="bi bi-info-circle"></i> Semua fasilitas yang Anda tambahkan akan berstatus <span class="badge bg-warning">Pending</span> dan menunggu persetujuan admin.
</div>

<div class="row">
    <?php foreach ($fasilitas_list as $fas): ?>
    <div class="col-md-4 mb-4">
        <div class="card shadow h-100">
            <?php if ($fas['gambar']): ?>
                <img src="../../uploads/fasilitas/<?php echo $fas['gambar']; ?>" class="card-img-top" style="height: 200px; object-fit: cover;">
            <?php else: ?>
                <div class="bg-secondary" style="height: 200px;"></div>
            <?php endif; ?>
            <div class="card-body">
                <span class="badge bg-info mb-2"><?php echo htmlspecialchars($fas['kategori_fasilitas'] ?? 'Umum'); ?></span>
                <?php if ($fas['status'] == 'pending'): ?>
                    <span class="badge bg-warning mb-2">Pending</span>
                <?php elseif ($fas['status'] == 'active'): ?>
                    <span class="badge bg-success mb-2">Approved</span>
                <?php else: ?>
                    <span class="badge bg-danger mb-2">Rejected</span>
                <?php endif; ?>
                <h5 class="card-title"><?php echo htmlspecialchars($fas['judul']); ?></h5>
                <p class="card-text text-muted"><?php echo htmlspecialchars(substr($fas['deskripsi'] ?? '', 0, 100)); ?>...</p>
            </div>
            <div class="card-footer bg-white">
                <?php if ($fas['status'] == 'pending' || $fas['status'] == 'rejected'): ?>
                    <button class="btn btn-sm btn-warning" onclick='editFasilitas(<?php echo json_encode($fas); ?>)'>
                        <i class="bi bi-pencil"></i> Edit
                    </button>
                    <a href="?delete=<?php echo $fas['id_fasilitas']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin hapus?')">
                        <i class="bi bi-trash"></i> Hapus
                    </a>
                <?php else: ?>
                    <span class="text-muted small">Sudah disetujui</span>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

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
                    
                    <div class="alert alert-warning">
                        <i class="bi bi-info-circle"></i> Data akan berstatus <strong>Pending</strong> dan menunggu persetujuan admin
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Judul *</label>
                        <input type="text" class="form-control" name="judul" id="judul" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Gambar</label>
                        <input type="file" class="form-control" name="gambar" accept="image/*">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Kategori</label>
                        <input type="text" class="form-control" name="kategori_fasilitas" id="kategori_fasilitas" placeholder="Hardware, Software, Ruangan, dll">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea class="form-control" name="deskripsi" id="deskripsi" rows="4"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan & Ajukan</button>
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
}

function editFasilitas(data) {
    document.getElementById('modalTitle').textContent = 'Edit Fasilitas';
    document.getElementById('id_fasilitas').value = data.id_fasilitas;
    document.getElementById('judul').value = data.judul;
    document.getElementById('kategori_fasilitas').value = data.kategori_fasilitas || '';
    document.getElementById('deskripsi').value = data.deskripsi || '';
    new bootstrap.Modal(document.getElementById('fasilitasModal')).show();
}
</script>

<?php include "footer.php"; ?>