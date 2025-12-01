<?php
$required_role = "admin";
include "../../auth.php";
include "../../conn.php";

$page_title = "Galeri";
$current_page = "galeri.php";

// Handle Delete
if (isset($_GET['delete'])) {
    try {
        // Get data untuk riwayat
        $stmt_old = $pdo->prepare("SELECT judul, status FROM galeri WHERE id_galeri = ?");
        $stmt_old->execute([$_GET['delete']]);
        $old_data = $stmt_old->fetch();
        
        $stmt = $pdo->prepare("DELETE FROM galeri WHERE id_galeri = ?");
        $stmt->execute([$_GET['delete']]);
        
        // Catat riwayat
        $stmt_riwayat = $pdo->prepare("INSERT INTO riwayat_pengajuan (tabel_sumber, id_data, id_admin, status_lama, status_baru, catatan) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt_riwayat->execute(['galeri', $_GET['delete'], $_SESSION['id_user'], $old_data['status'], 'deleted', 'Hapus foto: ' . ($old_data['judul'] ?? 'Galeri')]);
        
        $success = "Foto berhasil dihapus!";
    } catch (PDOException $e) {
        $error = "Gagal menghapus: " . $e->getMessage();
    }
}

// Handle Add/Edit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $judul = $_POST['judul'];
    $deskripsi = $_POST['deskripsi'];
    $filter_kategori = $_POST['filter_kategori'];
    $status = $_POST['status'];
    
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
            if ($gambar) {
                $stmt = $pdo->prepare("UPDATE galeri SET judul=?, deskripsi=?, filter_kategori=?, gambar=?, status=? WHERE id_galeri=?");
                $stmt->execute([$judul, $deskripsi, $filter_kategori, $gambar, $status, $id]);
            } else {
                $stmt = $pdo->prepare("UPDATE galeri SET judul=?, deskripsi=?, filter_kategori=?, status=? WHERE id_galeri=?");
                $stmt->execute([$judul, $deskripsi, $filter_kategori, $status, $id]);
            }
            $success = "Galeri berhasil diupdate!";
        } else {
            if (!$gambar) {
                $error = "Gambar wajib diupload!";
            } else {
                $stmt = $pdo->prepare("INSERT INTO galeri (judul, deskripsi, filter_kategori, gambar, status, id_user) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$judul, $deskripsi, $filter_kategori, $gambar, $status, $_SESSION['id_user']]);
                $success = "Foto berhasil ditambahkan!";
            }
        }
    } catch (PDOException $e) {
        $error = "Gagal menyimpan: " . $e->getMessage();
    }
}

$stmt = $pdo->query("SELECT * FROM galeri ORDER BY created_at DESC");
$galeri_list = $stmt->fetchAll();

// Get unique categories
$stmt_cat = $pdo->query("SELECT DISTINCT filter_kategori FROM galeri WHERE filter_kategori IS NOT NULL ORDER BY filter_kategori");
$categories = $stmt_cat->fetchAll(PDO::FETCH_COLUMN);

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

<!-- Filter -->
<div class="mb-3">
    <button class="btn btn-sm btn-outline-primary active filter-btn" data-filter="all">Semua</button>
    <?php foreach ($categories as $cat): ?>
        <button class="btn btn-sm btn-outline-primary filter-btn" data-filter="<?php echo htmlspecialchars($cat); ?>">
            <?php echo htmlspecialchars($cat); ?>
        </button>
    <?php endforeach; ?>
</div>

<div class="row" id="galeriContainer">
    <?php foreach ($galeri_list as $gal): ?>
    <div class="col-md-3 mb-4 galeri-item" data-category="<?php echo htmlspecialchars($gal['filter_kategori'] ?? ''); ?>">
        <div class="card shadow">
            <img src="../../uploads/galeri/<?php echo $gal['gambar']; ?>" class="card-img-top" style="height: 200px; object-fit: cover;" data-bs-toggle="modal" data-bs-target="#viewModal<?php echo $gal['id_galeri']; ?>">
            <div class="card-body p-2">
                <small class="d-block text-truncate"><strong><?php echo htmlspecialchars($gal['judul']); ?></strong></small>
                <small class="text-muted d-block"><?php echo htmlspecialchars($gal['filter_kategori'] ?? '-'); ?></small>
                <span class="badge <?php echo $gal['status']=='active' ? 'bg-success' : 'bg-warning'; ?> badge-sm"><?php echo $gal['status']; ?></span>
            </div>
            <div class="card-footer p-2 bg-white">
                <button class="btn btn-sm btn-warning" onclick='editGaleri(<?php echo json_encode($gal); ?>)'>
                    <i class="bi bi-pencil"></i>
                </button>
                <a href="?delete=<?php echo $gal['id_galeri']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin hapus?')">
                    <i class="bi bi-trash"></i>
                </a>
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
                    <p class="mt-3"><?php echo htmlspecialchars($gal['deskripsi'] ?? ''); ?></p>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Add/Edit Modal -->
<div class="modal fade" id="galeriModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Upload Foto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id_galeri" id="id_galeri">
                    
                    <div class="mb-3">
                        <label class="form-label">Gambar *</label>
                        <input type="file" class="form-control" name="gambar" id="gambar" accept="image/*">
                        <small class="text-muted">Wajib upload saat tambah baru</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Judul</label>
                        <input type="text" class="form-control" name="judul" id="judul">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Kategori</label>
                        <input type="text" class="form-control" name="filter_kategori" id="filter_kategori" list="categoryList" placeholder="Kegiatan, Penelitian, Fasilitas, dll">
                        <datalist id="categoryList">
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo htmlspecialchars($cat); ?>">
                            <?php endforeach; ?>
                        </datalist>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea class="form-control" name="deskripsi" id="deskripsi" rows="3"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status" id="status">
                            <option value="active">Active</option>
                            <option value="pending">Pending</option>
                            <option value="rejected">Rejected</option>
                        </select>
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
    document.getElementById('filter_kategori').value = data.filter_kategori || '';
    document.getElementById('deskripsi').value = data.deskripsi || '';
    document.getElementById('status').value = data.status;
    new bootstrap.Modal(document.getElementById('galeriModal')).show();
}

// Filter functionality
document.querySelectorAll('.filter-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        
        const filter = this.dataset.filter;
        document.querySelectorAll('.galeri-item').forEach(item => {
            if (filter === 'all' || item.dataset.category === filter) {
                item.style.display = 'block';
            } else {
                item.style.display = 'none';
            }
        });
    });
});
</script>

<?php include "footer.php"; ?>