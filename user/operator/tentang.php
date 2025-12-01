<?php
/**
 * @var PDO $pdo
 * @var string $current_page
 * @var int $current_id_user
 */
$required_role = "operator";
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
            $status = 'pending'; // AUTO PENDING
            
            $logo_lab = null;
            if (isset($_FILES['logo_lab']) && $_FILES['logo_lab']['error'] == 0) {
                $target_dir = "../../uploads/tentang/";
                if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);
                $file_extension = pathinfo($_FILES['logo_lab']['name'], PATHINFO_EXTENSION);
                $logo_lab = 'logo_' . time() . '.' . $file_extension;
                move_uploaded_file($_FILES['logo_lab']['tmp_name'], $target_dir . $logo_lab);
            }
            
            // Check if profil exists yang dibuat oleh operator ini
            $check = $pdo->prepare("SELECT id_profil, status FROM tentang_kami WHERE id_user = ? LIMIT 1");
            $check->execute([$_SESSION['id_user']]);
            $existing = $check->fetch();
            
            if ($existing && $existing['status'] == 'pending') {
                // UPDATE - hanya jika masih pending
                $status_lama = $existing['status'];
                
                if ($logo_lab) {
                    $stmt = $pdo->prepare("UPDATE tentang_kami SET profil_lab=?, logo_lab=?, penjelasan_logo=?, status=?, updated_at=CURRENT_TIMESTAMP WHERE id_profil=?");
                    $stmt->execute([$profil_lab, $logo_lab, $penjelasan_logo, $status, $existing['id_profil']]);
                } else {
                    $stmt = $pdo->prepare("UPDATE tentang_kami SET profil_lab=?, penjelasan_logo=?, status=?, updated_at=CURRENT_TIMESTAMP WHERE id_profil=?");
                    $stmt->execute([$profil_lab, $penjelasan_logo, $status, $existing['id_profil']]);
                }
                $success = "Profil berhasil diupdate! Menunggu persetujuan admin.";
            } else {
                // INSERT
                $stmt = $pdo->prepare("INSERT INTO tentang_kami (profil_lab, logo_lab, penjelasan_logo, status, id_user) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$profil_lab, $logo_lab, $penjelasan_logo, $status, $_SESSION['id_user']]);
                
                $new_id = $pdo->lastInsertId();
                
                $stmt_riwayat = $pdo->prepare("INSERT INTO riwayat_pengajuan (tabel_sumber, id_data, id_operator, status_lama, status_baru, catatan) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt_riwayat->execute(['tentang_kami', $new_id, $_SESSION['id_user'], null, $status, 'Tambah profil lab']);
                
                $success = "Profil berhasil ditambahkan! Menunggu persetujuan admin.";
            }
        }
        
        elseif ($action == 'add_visi') {
            $stmt = $pdo->prepare("INSERT INTO visi (isi_visi, status, id_user) VALUES (?, ?, ?)");
            $stmt->execute([$_POST['isi_visi'], 'pending', $_SESSION['id_user']]);
            
            $new_id = $pdo->lastInsertId();
            
            $stmt_riwayat = $pdo->prepare("INSERT INTO riwayat_pengajuan (tabel_sumber, id_data, id_operator, status_lama, status_baru, catatan) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt_riwayat->execute(['visi', $new_id, $_SESSION['id_user'], null, 'pending', 'Tambah visi']);
            
            $success = "Visi berhasil ditambahkan! Menunggu persetujuan admin.";
        }
        
        elseif ($action == 'add_misi') {
            $stmt = $pdo->prepare("INSERT INTO misi (isi_misi, urutan, status, id_user) VALUES (?, ?, ?, ?)");
            $stmt->execute([$_POST['isi_misi'], $_POST['urutan'], 'pending', $_SESSION['id_user']]);
            
            $new_id = $pdo->lastInsertId();
            
            $stmt_riwayat = $pdo->prepare("INSERT INTO riwayat_pengajuan (tabel_sumber, id_data, id_operator, status_lama, status_baru, catatan) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt_riwayat->execute(['misi', $new_id, $_SESSION['id_user'], null, 'pending', 'Tambah misi #' . $_POST['urutan']]);
            
            $success = "Misi berhasil ditambahkan! Menunggu persetujuan admin.";
        }
        
        elseif ($action == 'add_sejarah') {
            $stmt = $pdo->prepare("INSERT INTO sejarah (tahun, judul, deskripsi, urutan, status, id_user) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$_POST['tahun'], $_POST['judul'], $_POST['deskripsi'], $_POST['urutan'], 'pending', $_SESSION['id_user']]);
            
            $new_id = $pdo->lastInsertId();
            
            $stmt_riwayat = $pdo->prepare("INSERT INTO riwayat_pengajuan (tabel_sumber, id_data, id_operator, status_lama, status_baru, catatan) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt_riwayat->execute(['sejarah', $new_id, $_SESSION['id_user'], null, 'pending', 'Tambah sejarah: ' . $_POST['judul']]);
            
            $success = "Sejarah berhasil ditambahkan! Menunggu persetujuan admin.";
        }
    } catch (PDOException $e) {
        $error = "Gagal menyimpan: " . $e->getMessage();
    }
}

// Handle DELETE Visi
if (isset($_GET['delete_visi'])) {
    $stmt_check = $pdo->prepare("SELECT id_user, status FROM visi WHERE id_visi = ?");
    $stmt_check->execute([$_GET['delete_visi']]);
    $data = $stmt_check->fetch();
    
    if ($data && $data['id_user'] == $_SESSION['id_user'] && $data['status'] == 'pending') {
        $pdo->prepare("DELETE FROM visi WHERE id_visi = ?")->execute([$_GET['delete_visi']]);
        
        $stmt_riwayat = $pdo->prepare("INSERT INTO riwayat_pengajuan (tabel_sumber, id_data, id_operator, status_lama, status_baru, catatan) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt_riwayat->execute(['visi', $_GET['delete_visi'], $_SESSION['id_user'], $data['status'], 'deleted', 'Hapus visi']);
        
        $success = "Visi berhasil dihapus!";
    } else {
        $error = "Anda hanya bisa menghapus data pending milik Anda!";
    }
}

// Handle DELETE Misi
if (isset($_GET['delete_misi'])) {
    $stmt_check = $pdo->prepare("SELECT id_user, status FROM misi WHERE id_misi = ?");
    $stmt_check->execute([$_GET['delete_misi']]);
    $data = $stmt_check->fetch();
    
    if ($data && $data['id_user'] == $_SESSION['id_user'] && $data['status'] == 'pending') {
        $pdo->prepare("DELETE FROM misi WHERE id_misi = ?")->execute([$_GET['delete_misi']]);
        
        $stmt_riwayat = $pdo->prepare("INSERT INTO riwayat_pengajuan (tabel_sumber, id_data, id_operator, status_lama, status_baru, catatan) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt_riwayat->execute(['misi', $_GET['delete_misi'], $_SESSION['id_user'], $data['status'], 'deleted', 'Hapus misi']);
        
        $success = "Misi berhasil dihapus!";
    } else {
        $error = "Anda hanya bisa menghapus data pending milik Anda!";
    }
}

// Handle DELETE Sejarah
if (isset($_GET['delete_sejarah'])) {
    $stmt_check = $pdo->prepare("SELECT id_user, judul, status FROM sejarah WHERE id_sejarah = ?");
    $stmt_check->execute([$_GET['delete_sejarah']]);
    $data = $stmt_check->fetch();
    
    if ($data && $data['id_user'] == $_SESSION['id_user'] && $data['status'] == 'pending') {
        $pdo->prepare("DELETE FROM sejarah WHERE id_sejarah = ?")->execute([$_GET['delete_sejarah']]);
        
        $stmt_riwayat = $pdo->prepare("INSERT INTO riwayat_pengajuan (tabel_sumber, id_data, id_operator, status_lama, status_baru, catatan) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt_riwayat->execute(['sejarah', $_GET['delete_sejarah'], $_SESSION['id_user'], $data['status'], 'deleted', 'Hapus sejarah: ' . $data['judul']]);
        
        $success = "Sejarah berhasil dihapus!";
    } else {
        $error = "Anda hanya bisa menghapus data pending milik Anda!";
    }
}

// Get data - hanya milik operator ini
$profil = $pdo->prepare("SELECT * FROM tentang_kami WHERE id_user = ? LIMIT 1");
$profil->execute([$_SESSION['id_user']]);
$profil = $profil->fetch();

$visi_list = $pdo->prepare("SELECT * FROM visi WHERE id_user = ? ORDER BY urutan, created_at");
$visi_list->execute([$_SESSION['id_user']]);
$visi_list = $visi_list->fetchAll();

$misi_list = $pdo->prepare("SELECT * FROM misi WHERE id_user = ? ORDER BY urutan");
$misi_list->execute([$_SESSION['id_user']]);
$misi_list = $misi_list->fetchAll();

$sejarah_list = $pdo->prepare("SELECT * FROM sejarah WHERE id_user = ? ORDER BY tahun DESC, urutan");
$sejarah_list->execute([$_SESSION['id_user']]);
$sejarah_list = $sejarah_list->fetchAll();

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

<div class="alert alert-info">
    <i class="bi bi-info-circle"></i> Semua data yang Anda tambahkan akan berstatus <span class="badge bg-warning">Pending</span> dan menunggu persetujuan admin.
</div>

<!-- Profil Lab -->
<div class="card shadow mb-4">
    <div class="card-header">
        <h5 class="mb-0">Profil Lab & Logo</h5>
    </div>
    <div class="card-body">
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="profil">
            
            <?php if ($profil && $profil['status'] == 'active'): ?>
                <div class="alert alert-success">
                    <i class="bi bi-check-circle"></i> Profil Anda sudah disetujui admin. Anda tidak bisa mengubahnya lagi.
                </div>
            <?php endif; ?>
            
            <div class="mb-3">
                <label class="form-label">Logo Lab</label>
                <?php if ($profil && $profil['logo_lab']): ?>
                    <div class="mb-2">
                        <img src="../../uploads/tentang/<?php echo $profil['logo_lab']; ?>" height="100">
                    </div>
                <?php endif; ?>
                <?php if (!$profil || $profil['status'] == 'pending'): ?>
                    <input type="file" class="form-control" name="logo_lab" accept="image/*">
                <?php endif; ?>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Penjelasan Logo</label>
                <textarea class="form-control" name="penjelasan_logo" rows="3" <?php echo ($profil && $profil['status'] == 'active') ? 'readonly' : ''; ?>><?php echo $profil['penjelasan_logo'] ?? ''; ?></textarea>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Profil Lab</label>
                <textarea class="form-control" name="profil_lab" rows="6" required <?php echo ($profil && $profil['status'] == 'active') ? 'readonly' : ''; ?>><?php echo $profil['profil_lab'] ?? ''; ?></textarea>
            </div>
            
            <?php if (!$profil || $profil['status'] == 'pending'): ?>
                <button type="submit" class="btn btn-primary">Simpan & Ajukan</button>
            <?php endif; ?>
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
            <div>
                <?php echo htmlspecialchars($visi['isi_visi']); ?>
                <?php if ($visi['status'] == 'pending'): ?>
                    <span class="badge bg-warning ms-2">Pending</span>
                <?php elseif ($visi['status'] == 'active'): ?>
                    <span class="badge bg-success ms-2">Approved</span>
                <?php else: ?>
                    <span class="badge bg-danger ms-2">Rejected</span>
                <?php endif; ?>
            </div>
            <?php if ($visi['status'] == 'pending' || $visi['status'] == 'rejected'): ?>
                <a href="?delete_visi=<?php echo $visi['id_visi']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Hapus?')">
                    <i class="bi bi-trash"></i>
                </a>
            <?php endif; ?>
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
                <?php if ($misi['status'] == 'pending'): ?>
                    <span class="badge bg-warning">Pending</span>
                <?php elseif ($misi['status'] == 'active'): ?>
                    <span class="badge bg-success">Approved</span>
                <?php else: ?>
                    <span class="badge bg-danger">Rejected</span>
                <?php endif; ?>
                <?php if ($misi['status'] == 'pending' || $misi['status'] == 'rejected'): ?>
                    <a href="?delete_misi=<?php echo $misi['id_misi']; ?>" class="btn btn-sm btn-danger ms-2" onclick="return confirm('Hapus?')">
                        <i class="bi bi-trash"></i>
                    </a>
                <?php endif; ?>
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
            <?php if ($sej['status'] == 'pending'): ?>
                <span class="badge bg-warning">Pending</span>
            <?php elseif ($sej['status'] == 'active'): ?>
                <span class="badge bg-success">Approved</span>
            <?php else: ?>
                <span class="badge bg-danger">Rejected</span>
            <?php endif; ?>
            <p class="mb-1"><?php echo htmlspecialchars($sej['deskripsi'] ?? ''); ?></p>
            <?php if ($sej['status'] == 'pending' || $sej['status'] == 'rejected'): ?>
                <a href="?delete_sejarah=<?php echo $sej['id_sejarah']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Hapus?')">
                    <i class="bi bi-trash"></i>
                </a>
            <?php endif; ?>
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
                    <div class="alert alert-warning">
                        <i class="bi bi-info-circle"></i> Data akan berstatus <strong>Pending</strong>
                    </div>
                    <div class="mb-3">
                        <label>Isi Visi *</label>
                        <textarea class="form-control" name="isi_visi" rows="3" required></textarea>
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
                    <div class="alert alert-warning">
                        <i class="bi bi-info-circle"></i> Data akan berstatus <strong>Pending</strong>
                    </div>
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
                    <button type="submit" class="btn btn-primary">Simpan & Ajukan</button>
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
                    <div class="alert alert-warning">
                        <i class="bi bi-info-circle"></i> Data akan berstatus <strong>Pending</strong>
                    </div>
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
                    <button type="submit" class="btn btn-primary">Simpan & Ajukan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include "footer.php"; ?>