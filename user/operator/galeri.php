<?php
/**
 * @var PDO $pdo
 * @var string $current_page
 * @var int $current_id_user
 */
$required_role = "operator";
include "../auth.php";
include "../../conn.php";

$page_title = "Galeri";
$current_page = "galeri.php";

// Handle Delete
if (isset($_GET['delete'])) {
    try {
        $stmt_check = $pdo->prepare("SELECT id_user, status, judul FROM galeri WHERE id_galeri = ?");
        $stmt_check->execute([$_GET['delete']]);
        $old_data = $stmt_check->fetch();
        
        if ($old_data && $old_data['id_user'] == $_SESSION['id_user'] && $old_data['status'] == 'pending') {
            $stmt = $pdo->prepare("DELETE FROM galeri WHERE id_galeri = ?");
            $stmt->execute([$_GET['delete']]);
            
            $stmt_riwayat = $pdo->prepare("INSERT INTO riwayat_pengajuan (tabel_sumber, id_data, id_operator, status_lama, status_baru, catatan) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt_riwayat->execute(['galeri', $_GET['delete'], $_SESSION['id_user'], $old_data['status'], 'deleted', 'Hapus foto: ' . ($old_data['judul'] ?? 'Galeri')]);
            
            $success = "Foto berhasil dihapus!";
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
    $status = 'pending';
    
    $gambar = null;
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
        $target_dir = "../../uploads/galeri/";
        if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);
        
        $file_extension = pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION);
        $gambar = 'galeri_' . time() . '.' . $file_extension;
        move_uploaded_file($_FILES['gambar']['tmp_name'], $target_dir . $gambar);
    }
    
    try {
        if (isset($_POST['id_galeri']) && !empty($_POST['id_galeri'])) {
            $id = $_POST['id_galeri'];
            
            $stmt_check = $pdo->prepare("SELECT id_user, status FROM galeri WHERE id_galeri = ?");
            $stmt_check->execute([$id]);
            $data_owner = $stmt_check->fetch();
            
            if ($data_owner && $data_owner['id_user'] == $_SESSION['id_user'] && ($data_owner['status'] == 'pending' || $data_owner['status'] == 'rejected')) {
                $status_lama = $data_owner['status'];
                
                if ($gambar) {
                    $stmt = $pdo->prepare("UPDATE galeri SET judul=?, deskripsi=?, gambar=?, status=?, filter_kategori=NULL WHERE id_galeri=?");
                    $stmt->execute([$judul, $deskripsi, $gambar, $status, $id]);
                } else {
                    $stmt = $pdo->prepare("UPDATE galeri SET judul=?, deskripsi=?, status=?, filter_kategori=NULL WHERE id_galeri=?");
                    $stmt->execute([$judul, $deskripsi, $status, $id]);
                }
                
                $stmt_riwayat = $pdo->prepare("INSERT INTO riwayat_pengajuan (tabel_sumber, id_data, id_operator, status_lama, status_baru, catatan) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt_riwayat->execute(['galeri', $id, $_SESSION['id_user'], $status_lama, $status, 'Update foto: ' . $judul]);
                
                $success = "Galeri berhasil diupdate! Menunggu persetujuan admin.";
            } else {
                $error = "Anda hanya bisa edit data pending/rejected milik Anda!";
            }
        } else {
            if (!$gambar) {
                $error = "Gambar wajib diupload!";
            } else {
                $stmt = $pdo->prepare("INSERT INTO galeri (judul, deskripsi, gambar, status, filter_kategori, id_user) VALUES (?, ?, ?, ?, NULL, ?)");
                $stmt->execute([$judul, $deskripsi, $gambar, $status, $_SESSION['id_user']]);
                
                $new_id = $pdo->lastInsertId();
                
                $stmt_riwayat = $pdo->prepare("INSERT INTO riwayat_pengajuan (tabel_sumber, id_data, id_operator, status_lama, status_baru, catatan) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt_riwayat->execute(['galeri', $new_id, $_SESSION['id_user'], null, $status, 'Tambah foto: ' . $judul]);
                
                $success = "Foto berhasil ditambahkan! Menunggu persetujuan admin.";
            }
        }
    } catch (PDOException $e) {
        $error = "Gagal menyimpan: " . $e->getMessage();
    }
}

// Pagination
$items_per_page = 12;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $items_per_page;

// Get total count
$count_stmt = $pdo->prepare("SELECT COUNT(*) FROM galeri WHERE id_user = ?");
$count_stmt->execute([$_SESSION['id_user']]);
$total_items = $count_stmt->fetchColumn();
$total_pages = ceil($total_items / $items_per_page);

// Get paginated data
$stmt = $pdo->prepare("SELECT * FROM galeri WHERE id_user = ? ORDER BY created_at DESC LIMIT ? OFFSET ?");
$stmt->execute([$_SESSION['id_user'], $items_per_page, $offset]);
$galeri_list = $stmt->fetchAll();

include "header.php";
include "sidebar.php";
include "navbar.php";
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Galeri Foto</h1>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#galeriModal" onclick="resetForm()">
        <i class="bi bi-plus-circle"></i> Upload Foto
    </button>
</div>

<?php if (isset($success)): ?>
    <div class="alert alert-success alert-dismissible fade show"><?php echo $success; ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>
<?php if (isset($error)): ?>
    <div class="alert alert-danger alert-dismissible fade show"><?php echo $error; ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<div class="alert alert-info">
    <i class="bi bi-info-circle"></i> Semua foto yang Anda upload akan berstatus <span class="badge bg-warning">Pending</span> dan menunggu persetujuan admin.
</div>

<div class="row">
    <?php if (count($galeri_list) > 0): ?>
        <?php foreach ($galeri_list as $gal): ?>
        <div class="col-md-4 mb-4">
            <div class="card shadow h-100">
                <img src="../../uploads/galeri/<?php echo $gal['gambar']; ?>" class="card-img-top" style="height: 200px; object-fit: cover; cursor: pointer;" data-bs-toggle="modal" data-bs-target="#viewModal<?php echo $gal['id_galeri']; ?>">
                <div class="card-body">
                    <?php if ($gal['status'] == 'pending'): ?>
                        <span class="badge bg-warning mb-2">Pending</span>
                    <?php elseif ($gal['status'] == 'active'): ?>
                        <span class="badge bg-success mb-2">Approved</span>
                    <?php else: ?>
                        <span class="badge bg-danger mb-2">Rejected</span>
                    <?php endif; ?>
                    <h5 class="card-title"><?php echo htmlspecialchars($gal['judul']); ?></h5>
                    <p class="card-text text-muted"><?php echo htmlspecialchars(substr($gal['deskripsi'] ?? '', 0, 100)); ?><?php echo strlen($gal['deskripsi'] ?? '') > 100 ? '...' : ''; ?></p>
                </div>
                <div class="card-footer bg-white">
                    <?php if ($gal['status'] == 'pending' || $gal['status'] == 'rejected'): ?>
                        <button class="btn btn-sm btn-warning" onclick='editGaleri(<?php echo json_encode($gal); ?>)'>
                            <i class="bi bi-pencil"></i> Edit
                        </button>
                        <a href="?delete=<?php echo $gal['id_galeri']; ?>&page=<?php echo $page; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin hapus?')">
                            <i class="bi bi-trash"></i> Hapus
                        </a>
                    <?php else: ?>
                        <span class="text-muted small">Disetujui</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- View Modal -->
        <div class="modal fade" id="viewModal<?php echo $gal['id_galeri']; ?>" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><?php echo htmlspecialchars($gal['judul']); ?></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body text-center">
                        <img src="../../uploads/galeri/<?php echo $gal['gambar']; ?>" class="img-fluid">
                        <?php if ($gal['deskripsi']): ?>
                            <p class="mt-3"><?php echo nl2br(htmlspecialchars($gal['deskripsi'])); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="col-12">
            <div class="alert alert-info text-center">
                <i class="bi bi-info-circle"></i> Belum ada foto di galeri
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

<!-- Add/Edit Modal -->
<div class="modal fade" id="galeriModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Upload Foto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id_galeri" id="id_galeri">
                    
                    <div class="alert alert-warning">
                        <i class="bi bi-info-circle"></i> Data akan berstatus <strong>Pending</strong> dan menunggu persetujuan admin
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Judul *</label>
                        <input type="text" class="form-control" name="judul" id="judul" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Gambar *</label>
                        <input type="file" class="form-control" name="gambar" id="gambar" accept="image/*">
                        <small class="text-muted">Wajib upload saat tambah baru, opsional saat edit</small>
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
    document.getElementById('modalTitle').textContent = 'Upload Foto';
    document.querySelector('form').reset();
    document.getElementById('id_galeri').value = '';
}

function editGaleri(data) {
    document.getElementById('modalTitle').textContent = 'Edit Foto';
    document.getElementById('id_galeri').value = data.id_galeri;
    document.getElementById('judul').value = data.judul || '';
    document.getElementById('deskripsi').value = data.deskripsi || '';
    new bootstrap.Modal(document.getElementById('galeriModal')).show();
}
</script>

<?php include "footer.php"; ?>