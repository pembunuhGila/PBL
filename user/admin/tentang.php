<?php
/**
 * @var PDO $pdo
 * @var string $current_page
 * @var int $current_id_user
 */
$required_role = "admin";
include "../auth.php";
include "../../conn.php";

$page_title = "Tentang Kami";
$current_page = "tentang.php";

// Handle operations
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'];
    
    try {
        if ($action == 'profil') {
            $profil_lab = $_POST['profil_lab'];
            $penjelasan_logo = $_POST['penjelasan_logo'];
            $status = 'active'; 
            
            $logo_lab = null;
            if (isset($_FILES['logo_lab']) && $_FILES['logo_lab']['error'] == 0) {
                $target_dir = "../../uploads/tentang/";
                if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);
                $file_extension = pathinfo($_FILES['logo_lab']['name'], PATHINFO_EXTENSION);
                $logo_lab = 'logo_' . time() . '.' . $file_extension;
                move_uploaded_file($_FILES['logo_lab']['tmp_name'], $target_dir . $logo_lab);
            }
            
            // Check if profil exists
            $check = $pdo->query("SELECT id_profil, status FROM tentang_kami LIMIT 1")->fetch();
            
            if ($check) {
                // UPDATE - Get status lama
                $status_lama = $check['status'];
                
                if ($logo_lab) {
                    $stmt = $pdo->prepare("UPDATE tentang_kami SET profil_lab=?, logo_lab=?, penjelasan_logo=?, status=?, updated_at=CURRENT_TIMESTAMP WHERE id_profil=?");
                    $stmt->execute([$profil_lab, $logo_lab, $penjelasan_logo, $status, $check['id_profil']]);
                } else {
                    $stmt = $pdo->prepare("UPDATE tentang_kami SET profil_lab=?, penjelasan_logo=?, status=?, updated_at=CURRENT_TIMESTAMP WHERE id_profil=?");
                    $stmt->execute([$profil_lab, $penjelasan_logo, $status, $check['id_profil']]);
                }
                
                // Catat riwayat JIKA status berubah
                if ($status_lama != $status) {
                    $stmt_riwayat = $pdo->prepare("INSERT INTO riwayat_pengajuan (tabel_sumber, id_data, id_admin, status_lama, status_baru, catatan) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt_riwayat->execute(['tentang_kami', $check['id_profil'], $_SESSION['id_user'], $status_lama, $status, 'Update profil lab']);
                }
            } else {
                // INSERT
                $stmt = $pdo->prepare("INSERT INTO tentang_kami (profil_lab, logo_lab, penjelasan_logo, status, id_user) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$profil_lab, $logo_lab, $penjelasan_logo, $status, $_SESSION['id_user']]);
                
                $new_id = $pdo->lastInsertId();
                
                // Catat riwayat
                $stmt_riwayat = $pdo->prepare("INSERT INTO riwayat_pengajuan (tabel_sumber, id_data, id_admin, status_lama, status_baru, catatan) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt_riwayat->execute(['tentang_kami', $new_id, $_SESSION['id_user'], null, $status, 'Tambah profil lab']);
            }
            $success = "Profil berhasil disimpan!";
        }
        
        elseif ($action == 'add_visi') {
            // INSERT Visi
            $stmt = $pdo->prepare("INSERT INTO visi (isi_visi, status, id_user) VALUES (?, ?, ?)");
            $stmt->execute([$_POST['isi_visi'], 'active', $_SESSION['id_user']]);
            
            $new_id = $pdo->lastInsertId();
            
            // Catat riwayat
            $stmt_riwayat = $pdo->prepare("INSERT INTO riwayat_pengajuan (tabel_sumber, id_data, id_admin, status_lama, status_baru, catatan) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt_riwayat->execute(['visi', $new_id, $_SESSION['id_user'], null, $_POST['status'], 'Tambah visi']);
            
            $success = "Visi berhasil ditambahkan!";
        }
        
        elseif ($action == 'add_misi') {
            // INSERT Misi
            $stmt = $pdo->prepare("INSERT INTO misi (isi_misi, urutan, status, id_user) VALUES (?, ?, ?, ?)");
            $stmt->execute([$_POST['isi_misi'], $_POST['urutan'], 'active' , $_SESSION['id_user']]);
            
            $new_id = $pdo->lastInsertId();
            
            // Catat riwayat
            $stmt_riwayat = $pdo->prepare("INSERT INTO riwayat_pengajuan (tabel_sumber, id_data, id_admin, status_lama, status_baru, catatan) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt_riwayat->execute(['misi', $new_id, $_SESSION['id_user'], null, $_POST['status'], 'Tambah misi #' . $_POST['urutan']]);
            
            $success = "Misi berhasil ditambahkan!";
        }
        
        elseif ($action == 'add_sejarah') {
            // INSERT Sejarah
            $stmt = $pdo->prepare("INSERT INTO sejarah (tahun, judul, deskripsi, urutan, status, id_user) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$_POST['tahun'], $_POST['judul'], $_POST['deskripsi'], $_POST['urutan'], 'active' , $_SESSION['id_user']]);
            
            $new_id = $pdo->lastInsertId();
            
            // Catat riwayat
            $stmt_riwayat = $pdo->prepare("INSERT INTO riwayat_pengajuan (tabel_sumber, id_data, id_admin, status_lama, status_baru, catatan) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt_riwayat->execute(['sejarah', $new_id, $_SESSION['id_user'], null, $_POST['status'], 'Tambah sejarah: ' . $_POST['judul']]);
            
            $success = "Sejarah berhasil ditambahkan!";
        }
    } catch (PDOException $e) {
        $error = "Gagal menyimpan: " . $e->getMessage();
    }
}

// Handle DELETE Visi
if (isset($_GET['delete_visi'])) {
    // Get data lama
    $stmt_old = $pdo->prepare("SELECT status FROM visi WHERE id_visi = ?");
    $stmt_old->execute([$_GET['delete_visi']]);
    $old_data = $stmt_old->fetch();
    
    // Delete
    $pdo->prepare("DELETE FROM visi WHERE id_visi = ?")->execute([$_GET['delete_visi']]);
    
    // Catat riwayat
    $stmt_riwayat = $pdo->prepare("INSERT INTO riwayat_pengajuan (tabel_sumber, id_data, id_admin, status_lama, status_baru, catatan) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt_riwayat->execute(['visi', $_GET['delete_visi'], $_SESSION['id_user'], $old_data['status'], 'deleted', 'Hapus visi']);
    
    $success = "Visi berhasil dihapus!";
}

// Handle DELETE Misi
if (isset($_GET['delete_misi'])) {
    // Get data lama
    $stmt_old = $pdo->prepare("SELECT status FROM misi WHERE id_misi = ?");
    $stmt_old->execute([$_GET['delete_misi']]);
    $old_data = $stmt_old->fetch();
    
    // Delete
    $pdo->prepare("DELETE FROM misi WHERE id_misi = ?")->execute([$_GET['delete_misi']]);
    
    // Catat riwayat
    $stmt_riwayat = $pdo->prepare("INSERT INTO riwayat_pengajuan (tabel_sumber, id_data, id_admin, status_lama, status_baru, catatan) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt_riwayat->execute(['misi', $_GET['delete_misi'], $_SESSION['id_user'], $old_data['status'], 'deleted', 'Hapus misi']);
    
    $success = "Misi berhasil dihapus!";
}

// Handle DELETE Sejarah
if (isset($_GET['delete_sejarah'])) {
    // Get data lama
    $stmt_old = $pdo->prepare("SELECT judul, status FROM sejarah WHERE id_sejarah = ?");
    $stmt_old->execute([$_GET['delete_sejarah']]);
    $old_data = $stmt_old->fetch();
    
    // Delete
    $pdo->prepare("DELETE FROM sejarah WHERE id_sejarah = ?")->execute([$_GET['delete_sejarah']]);
    
    // Catat riwayat
    $stmt_riwayat = $pdo->prepare("INSERT INTO riwayat_pengajuan (tabel_sumber, id_data, id_admin, status_lama, status_baru, catatan) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt_riwayat->execute(['sejarah', $_GET['delete_sejarah'], $_SESSION['id_user'], $old_data['status'], 'deleted', 'Hapus sejarah: ' . $old_data['judul']]);
    
    $success = "Sejarah berhasil dihapus!";
}

// Get data
$profil = $pdo->query("SELECT * FROM tentang_kami LIMIT 1")->fetch();
$visi_list = $pdo->query("SELECT * FROM visi ORDER BY urutan, created_at")->fetchAll();
$misi_list = $pdo->query("SELECT * FROM misi ORDER BY urutan")->fetchAll();
$sejarah_list = $pdo->query("SELECT * FROM sejarah ORDER BY tahun DESC, urutan")->fetchAll();

include "header.php";
include "sidebar.php";
include "navbar.php";
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Tentang Kami</h1>
</div>

<?php if (isset($success)): ?>
    <div class="alert alert-success alert-dismissible fade show"><?php echo $success; ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>
<?php if (isset($error)): ?>
    <div class="alert alert-danger alert-dismissible fade show"><?php echo $error; ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<!-- Profil Lab -->
<div class="card shadow mb-4">
    <div class="card-header">
        <h5 class="mb-0">Profil Lab & Logo</h5>
    </div>
    <div class="card-body">
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="profil">
            
            <div class="mb-3">
                <label class="form-label">Logo Lab</label>
                <?php if ($profil && $profil['logo_lab']): ?>
                    <div class="mb-2">
                        <img src="../../uploads/tentang/<?php echo $profil['logo_lab']; ?>" height="100">
                    </div>
                <?php endif; ?>
                <input type="file" class="form-control" name="logo_lab" accept="image/*">
            </div>
            
            <div class="mb-3">
                <label class="form-label">Penjelasan Logo</label>
                <textarea class="form-control" name="penjelasan_logo" rows="3"><?php echo $profil['penjelasan_logo'] ?? ''; ?></textarea>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Profil Lab</label>
                <textarea class="form-control" name="profil_lab" rows="6" required><?php echo $profil['profil_lab'] ?? ''; ?></textarea>
            </div>
            
            <button type="submit" class="btn btn-primary">Simpan Profil</button>
        </form>
    </div>
</div>

<!-- Visi -->
<div class="card shadow mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Visi</h5>
        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#visiModal">
            <i class="bi bi-plus"></i> Tambah
        </button>
    </div>
    <div class="card-body">
        <?php foreach ($visi_list as $visi): ?>
        <div class="alert alert-info d-flex justify-content-between align-items-center">
            <div><?php echo htmlspecialchars($visi['isi_visi']); ?></div>
            <a href="?delete_visi=<?php echo $visi['id_visi']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Hapus?')">
                <i class="bi bi-trash"></i>
            </a>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Misi -->
<div class="card shadow mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Misi</h5>
        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#misiModal">
            <i class="bi bi-plus"></i> Tambah
        </button>
    </div>
    <div class="card-body">
        <ol>
        <?php foreach ($misi_list as $misi): ?>
            <li class="mb-2">
                <?php echo htmlspecialchars($misi['isi_misi']); ?>
                <a href="?delete_misi=<?php echo $misi['id_misi']; ?>" class="btn btn-sm btn-danger ms-2" onclick="return confirm('Hapus?')">
                    <i class="bi bi-trash"></i>
                </a>
            </li>
        <?php endforeach; ?>
        </ol>
    </div>
</div>

<!-- Sejarah -->
<div class="card shadow mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Sejarah</h5>
        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#sejarahModal">
            <i class="bi bi-plus"></i> Tambah
        </button>
    </div>
    <div class="card-body">
        <?php foreach ($sejarah_list as $sej): ?>
        <div class="border-start border-primary border-3 ps-3 mb-3">
            <h6 class="text-primary"><?php echo htmlspecialchars($sej['tahun']); ?></h6>
            <strong><?php echo htmlspecialchars($sej['judul']); ?></strong>
            <p class="mb-1"><?php echo htmlspecialchars($sej['deskripsi'] ?? ''); ?></p>
            <a href="?delete_sejarah=<?php echo $sej['id_sejarah']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Hapus?')">
                <i class="bi bi-trash"></i>
            </a>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Modal Visi -->
<div class="modal fade" id="visiModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" value="add_visi">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Visi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Isi Visi *</label>
                        <textarea class="form-control" name="isi_visi" rows="3" required></textarea>
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

<!-- Modal Misi -->
<div class="modal fade" id="misiModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" value="add_misi">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Misi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Isi Misi *</label>
                        <textarea class="form-control" name="isi_misi" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label>Urutan</label>
                        <input type="number" class="form-control" name="urutan" value="<?php echo count($misi_list) + 1; ?>" min="1">
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

<!-- Modal Sejarah -->
<div class="modal fade" id="sejarahModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" value="add_sejarah">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Sejarah</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Tahun *</label>
                        <input type="text" class="form-control" name="tahun" required placeholder="2024">
                    </div>
                    <div class="mb-3">
                        <label>Judul *</label>
                        <input type="text" class="form-control" name="judul" required>
                    </div>
                    <div class="mb-3">
                        <label>Deskripsi</label>
                        <textarea class="form-control" name="deskripsi" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label>Urutan</label>
                        <input type="number" class="form-control" name="urutan" value="1" min="1">
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

<?php include "footer.php"; ?>