<?php
/**
 * @var PDO $pdo
 * @var string $current_page
 * @var int $current_id_user
 */
$required_role = "admin";
include "../auth.php";
include "../../conn.php";

$page_title = "Galeri";
$current_page = "galeri.php";

// Handle Approve/Reject
if (isset($_GET['approve']) || isset($_GET['reject'])) {
    $id = isset($_GET['approve']) ? $_GET['approve'] : $_GET['reject'];
    $new_status = isset($_GET['approve']) ? 'active' : 'rejected';
    
    try {
        $stmt_old = $pdo->prepare("SELECT judul, status FROM galeri WHERE id_galeri = ?");
        $stmt_old->execute([$id]);
        $old_data = $stmt_old->fetch();
        
        $stmt = $pdo->prepare("UPDATE galeri SET status = ? WHERE id_galeri = ?");
        $stmt->execute([$new_status, $id]);
        
        $stmt_riwayat = $pdo->prepare("INSERT INTO riwayat_pengajuan (tabel_sumber, id_data, id_admin, status_lama, status_baru, catatan) VALUES (?, ?, ?, ?, ?, ?)");
        $catatan = isset($_GET['approve']) ? 'DISETUJUI foto: ' . $old_data['judul'] : 'DITOLAK foto: ' . $old_data['judul'];
        $stmt_riwayat->execute(['galeri', $id, $_SESSION['id_user'], $old_data['status'], $new_status, $catatan]);
        
        header("Location: galeri.php?success=" . ($new_status == 'active' ? 'approved' : 'rejected') . "&page=" . (isset($_GET['page']) ? $_GET['page'] : 1));
        exit;
    } catch (PDOException $e) {
        $error = "Gagal: " . $e->getMessage();
    }
}

// Handle Delete
if (isset($_GET['delete'])) {
    try {
        $stmt_old = $pdo->prepare("SELECT judul, status, gambar FROM galeri WHERE id_galeri = ?");
        $stmt_old->execute([$_GET['delete']]);
        $old_data = $stmt_old->fetch();
        
        if ($old_data) {
            // Hapus file gambar
            if ($old_data['gambar'] && file_exists("../../uploads/galeri/" . $old_data['gambar'])) {
                unlink("../../uploads/galeri/" . $old_data['gambar']);
            }
            
            $stmt = $pdo->prepare("DELETE FROM galeri WHERE id_galeri = ?");
            $stmt->execute([$_GET['delete']]);
            
            $stmt_riwayat = $pdo->prepare("INSERT INTO riwayat_pengajuan (tabel_sumber, id_data, id_admin, status_lama, status_baru, catatan) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt_riwayat->execute(['galeri', $_GET['delete'], $_SESSION['id_user'], $old_data['status'], 'deleted', 'Hapus foto: ' . ($old_data['judul'] ?? 'Galeri')]);
            
            header("Location: galeri.php?success=deleted&page=" . (isset($_GET['page']) ? $_GET['page'] : 1));
            exit;
        }
    } catch (PDOException $e) {
        $error = "Gagal menghapus: " . $e->getMessage();
    }
}

// Handle Add/Edit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $judul = trim($_POST['judul']);
    $deskripsi = trim($_POST['deskripsi']);
    $status = 'active';
    
    $gambar = null;
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
        $target_dir = "../../uploads/galeri/";
        if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);
        
        $file_extension = strtolower(pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION));
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (in_array($file_extension, $allowed_extensions)) {
            $gambar = 'galeri_' . time() . '_' . uniqid() . '.' . $file_extension;
            move_uploaded_file($_FILES['gambar']['tmp_name'], $target_dir . $gambar);
        } else {
            $error = "Format gambar tidak valid! Gunakan JPG, PNG, atau GIF.";
        }
    }
    
    if (!isset($error)) {
        try {
            if (isset($_POST['id_galeri']) && !empty($_POST['id_galeri'])) {
                $id = $_POST['id_galeri'];
                
                $stmt_old = $pdo->prepare("SELECT status, gambar FROM galeri WHERE id_galeri = ?");
                $stmt_old->execute([$id]);
                $old_data = $stmt_old->fetch();
                $status_lama = $old_data['status'];
                
                if ($gambar) {
                    // Hapus gambar lama
                    if ($old_data['gambar'] && file_exists("../../uploads/galeri/" . $old_data['gambar'])) {
                        unlink("../../uploads/galeri/" . $old_data['gambar']);
                    }
                    
                    $stmt = $pdo->prepare("UPDATE galeri SET judul=?, deskripsi=?, gambar=?, status=? WHERE id_galeri=?");
                    $stmt->execute([$judul, $deskripsi, $gambar, $status, $id]);
                } else {
                    $stmt = $pdo->prepare("UPDATE galeri SET judul=?, deskripsi=?, status=? WHERE id_galeri=?");
                    $stmt->execute([$judul, $deskripsi, $status, $id]);
                }
                
                if ($status_lama != $status) {
                    $stmt_riwayat = $pdo->prepare("INSERT INTO riwayat_pengajuan (tabel_sumber, id_data, id_admin, status_lama, status_baru, catatan) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt_riwayat->execute(['galeri', $id, $_SESSION['id_user'], $status_lama, $status, 'Update foto: ' . $judul]);
                }
                
                $success = "Galeri berhasil diupdate!";
            } else {
                if (!$gambar) {
                    $error = "Gambar wajib diupload!";
                } else {
                    $stmt = $pdo->prepare("INSERT INTO galeri (judul, deskripsi, gambar, status, id_user) VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute([$judul, $deskripsi, $gambar, $status, $_SESSION['id_user']]);
                    
                    $new_id = $pdo->lastInsertId();
                    
                    $stmt_riwayat = $pdo->prepare("INSERT INTO riwayat_pengajuan (tabel_sumber, id_data, id_admin, status_lama, status_baru, catatan) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt_riwayat->execute(['galeri', $new_id, $_SESSION['id_user'], null, $status, 'Tambah foto: ' . $judul]);
                    
                    $success = "Foto berhasil ditambahkan!";
                }
            }
        } catch (PDOException $e) {
            $error = "Gagal menyimpan: " . $e->getMessage();
        }
    }
}

// Pagination
$items_per_page = 9;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $items_per_page;

// Get total count
$count_stmt = $pdo->query("SELECT COUNT(*) FROM galeri");
$total_items = $count_stmt->fetchColumn();
$total_pages = ceil($total_items / $items_per_page);

// Get paginated data
$stmt = $pdo->prepare("SELECT g.*, u.nama as nama_pembuat FROM galeri g LEFT JOIN users u ON g.id_user = u.id_user ORDER BY g.created_at DESC LIMIT ? OFFSET ?");
$stmt->execute([$items_per_page, $offset]);
$galeri_list = $stmt->fetchAll();

// Get pending count
$pending_count_query = "SELECT COUNT(*) FROM galeri WHERE status = 'pending'";
$pending_count_stmt = $pdo->prepare($pending_count_query);
$pending_count_stmt->execute();
$pending_count = $pending_count_stmt->fetchColumn();

include "header.php";
include "sidebar.php";
include "navbar.php";
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Galeri Foto</h1>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#galeriModal" onclick="resetForm()">
        <i class="bi bi-plus-circle"></i> Tambah Foto
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
        if ($_GET['success'] == 'approved') echo "✅ Pengajuan foto berhasil disetujui!";
        if ($_GET['success'] == 'rejected') echo "❌ Pengajuan foto berhasil ditolak!";
        if ($_GET['success'] == 'deleted') echo "✅ Foto berhasil dihapus!";
        ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row">
    <?php if (count($galeri_list) > 0): ?>
        <?php foreach ($galeri_list as $gal): ?>
        <div class="col-md-4 mb-4">
            <div class="card shadow h-100 position-relative">
                <!-- Status Badge di pojok kanan atas -->
                <div class="position-absolute top-0 end-0 m-2" style="z-index: 10;">
                    <?php if ($gal['status'] == 'pending'): ?>
                        <span class="badge bg-warning text-dark">Pending</span>
                    <?php elseif ($gal['status'] == 'active'): ?>
                        <span class="badge bg-success">Active</span>
                    <?php else: ?>
                        <span class="badge bg-danger">Rejected</span>
                    <?php endif; ?>
                </div>
                
                <?php if ($gal['gambar']): ?>
                    <img src="../../uploads/galeri/<?php echo htmlspecialchars($gal['gambar']); ?>" class="card-img-top" style="height: 200px; object-fit: cover; cursor: pointer;" onclick="viewImage('<?php echo htmlspecialchars($gal['gambar']); ?>', '<?php echo htmlspecialchars($gal['judul']); ?>')">
                <?php else: ?>
                    <div class="bg-secondary d-flex align-items-center justify-content-center text-white" style="height: 200px;">
                        <i class="bi bi-image" style="font-size: 3rem;"></i>
                    </div>
                <?php endif; ?>
                
                <div class="card-body">
                    <small class="text-muted d-block mb-2">
                        <i class="bi bi-person"></i> <?php echo htmlspecialchars($gal['nama_pembuat'] ?? 'Admin'); ?>
                    </small>
                    
                    <h5 class="card-title"><?php echo htmlspecialchars($gal['judul']); ?></h5>
                    <p class="card-text text-muted small"><?php echo htmlspecialchars(substr($gal['deskripsi'] ?? '', 0, 100)); ?><?php echo strlen($gal['deskripsi'] ?? '') > 100 ? '...' : ''; ?></p>
                </div>
                
                <div class="card-footer bg-white">
                    <!-- Edit Button -->
                    <button class="btn btn-sm btn-warning" onclick='editGaleri(<?php echo json_encode($gal); ?>)'>
                        <i class="bi bi-pencil"></i>
                    </button>
                    
                    <!-- Approve/Reject Buttons (hanya untuk pending) -->
                    <?php if ($gal['status'] == 'pending'): ?>
                        <a href="?approve=<?php echo $gal['id_galeri']; ?>&page=<?php echo $page; ?>" 
                           class="btn btn-sm btn-success" 
                           onclick="return confirm('Setujui foto: <?php echo htmlspecialchars($gal['judul']); ?>?')">
                            <i class="bi bi-check-circle"></i>
                        </a>
                        <a href="?reject=<?php echo $gal['id_galeri']; ?>&page=<?php echo $page; ?>" 
                           class="btn btn-sm btn-danger" 
                           onclick="return confirm('Tolak foto: <?php echo htmlspecialchars($gal['judul']); ?>?')">
                            <i class="bi bi-x-circle"></i>
                        </a>
                    <?php endif; ?>
                    
                    <!-- Delete Button -->
                    <a href="?delete=<?php echo $gal['id_galeri']; ?>&page=<?php echo $page; ?>" 
                       class="btn btn-sm btn-danger" 
                       onclick="return confirm('Yakin hapus: <?php echo htmlspecialchars($gal['judul']); ?>?')">
                        <i class="bi bi-trash"></i>
                    </a>
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

<!-- Modal Add/Edit -->
<div class="modal fade" id="galeriModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Tambah Foto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id_galeri" id="id_galeri">
                    
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
                        <input type="file" class="form-control" name="gambar" accept="image/*">
                        <small class="text-muted">Kosongkan jika tidak ingin mengubah gambar</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea class="form-control" name="deskripsi" id="deskripsi" rows="4"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal View Image -->
<div class="modal fade" id="viewImageModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewImageTitle"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <img id="viewImageSrc" src="" class="img-fluid">
            </div>
        </div>
    </div>
</div>

<script>
function resetForm() {
    document.getElementById('modalTitle').textContent = 'Tambah Foto';
    document.querySelector('form').reset();
    document.getElementById('id_galeri').value = '';
    document.getElementById('currentImage').style.display = 'none';
}

function editGaleri(data) {
    document.getElementById('modalTitle').textContent = 'Edit Foto';
    document.getElementById('id_galeri').value = data.id_galeri;
    document.getElementById('judul').value = data.judul || '';
    document.getElementById('deskripsi').value = data.deskripsi || '';
    
    if (data.gambar) {
        document.getElementById('currentImage').style.display = 'block';
        document.getElementById('previewImage').src = '../../uploads/galeri/' + data.gambar;
    } else {
        document.getElementById('currentImage').style.display = 'none';
    }
    
    new bootstrap.Modal(document.getElementById('galeriModal')).show();
}

function viewImage(gambar, judul) {
    document.getElementById('viewImageTitle').textContent = judul;
    document.getElementById('viewImageSrc').src = '../../uploads/galeri/' + gambar;
    new bootstrap.Modal(document.getElementById('viewImageModal')).show();
}
</script>

<?php include "footer.php"; ?>