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
            
            if ($existing && ($existing['status'] == 'pending' || $existing['status'] == 'rejected')) {
                // UPDATE - hanya jika masih pending/rejected
                if ($logo_lab) {
                    $stmt = $pdo->prepare("UPDATE tentang_kami SET profil_lab=?, logo_lab=?, penjelasan_logo=?, status=?, updated_at=CURRENT_TIMESTAMP WHERE id_profil=?");
                    $stmt->execute([$profil_lab, $logo_lab, $penjelasan_logo, $status, $existing['id_profil']]);
                } else {
                    $stmt = $pdo->prepare("UPDATE tentang_kami SET profil_lab=?, penjelasan_logo=?, status=?, updated_at=CURRENT_TIMESTAMP WHERE id_profil=?");
                    $stmt->execute([$profil_lab, $penjelasan_logo, $status, $existing['id_profil']]);
                }
                
                $stmt_riwayat = $pdo->prepare("INSERT INTO riwayat_pengajuan (tabel_sumber, id_data, id_operator, status_lama, status_baru, catatan) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt_riwayat->execute(['tentang_kami', $existing['id_profil'], $_SESSION['id_user'], $existing['status'], $status, 'Update profil lab']);
                
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
            $stmt = $pdo->prepare("INSERT INTO visi (isi_visi, urutan, status, id_user) VALUES (?, ?, ?, ?)");
            $stmt->execute([$_POST['isi_visi'], $_POST['urutan'] ?? 1, 'pending', $_SESSION['id_user']]);
            
            $new_id = $pdo->lastInsertId();
            
            $stmt_riwayat = $pdo->prepare("INSERT INTO riwayat_pengajuan (tabel_sumber, id_data, id_operator, status_lama, status_baru, catatan) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt_riwayat->execute(['visi', $new_id, $_SESSION['id_user'], null, 'pending', 'Tambah visi']);
            
            $success = "Visi berhasil ditambahkan! Menunggu persetujuan admin.";
        }
        
        elseif ($action == 'edit_visi') {
            // Cek kepemilikan dan status
            $stmt_check = $pdo->prepare("SELECT id_user, status FROM visi WHERE id_visi = ?");
            $stmt_check->execute([$_POST['id_visi']]);
            $data = $stmt_check->fetch();
            
            if ($data && $data['id_user'] == $_SESSION['id_user'] && ($data['status'] == 'pending' || $data['status'] == 'rejected')) {
                $stmt = $pdo->prepare("UPDATE visi SET isi_visi=?, urutan=?, status='pending' WHERE id_visi=?");
                $stmt->execute([$_POST['isi_visi'], $_POST['urutan'] ?? 1, $_POST['id_visi']]);
                
                $success = "Visi berhasil diupdate! Menunggu persetujuan admin.";
            } else {
                $error = "Anda hanya bisa edit data pending/rejected milik Anda!";
            }
        }
        
        elseif ($action == 'add_misi') {
            $stmt = $pdo->prepare("INSERT INTO misi (isi_misi, urutan, status, id_user) VALUES (?, ?, ?, ?)");
            $stmt->execute([$_POST['isi_misi'], $_POST['urutan'], 'pending', $_SESSION['id_user']]);
            
            $new_id = $pdo->lastInsertId();
            
            $stmt_riwayat = $pdo->prepare("INSERT INTO riwayat_pengajuan (tabel_sumber, id_data, id_operator, status_lama, status_baru, catatan) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt_riwayat->execute(['misi', $new_id, $_SESSION['id_user'], null, 'pending', 'Tambah misi #' . $_POST['urutan']]);
            
            $success = "Misi berhasil ditambahkan! Menunggu persetujuan admin.";
        }
        
        elseif ($action == 'edit_misi') {
            // Cek kepemilikan dan status
            $stmt_check = $pdo->prepare("SELECT id_user, status FROM misi WHERE id_misi = ?");
            $stmt_check->execute([$_POST['id_misi']]);
            $data = $stmt_check->fetch();
            
            if ($data && $data['id_user'] == $_SESSION['id_user'] && ($data['status'] == 'pending' || $data['status'] == 'rejected')) {
                $stmt = $pdo->prepare("UPDATE misi SET isi_misi=?, urutan=?, status='pending' WHERE id_misi=?");
                $stmt->execute([$_POST['isi_misi'], $_POST['urutan'], $_POST['id_misi']]);
                
                $success = "Misi berhasil diupdate! Menunggu persetujuan admin.";
            } else {
                $error = "Anda hanya bisa edit data pending/rejected milik Anda!";
            }
        }
        
        elseif ($action == 'add_roadmap') {
            $stmt = $pdo->prepare("INSERT INTO sejarah (tahun, judul, deskripsi, urutan, status, id_user) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$_POST['tahun'], $_POST['judul'], $_POST['deskripsi'], $_POST['urutan'], 'pending', $_SESSION['id_user']]);
            
            $new_id = $pdo->lastInsertId();
            
            $stmt_riwayat = $pdo->prepare("INSERT INTO riwayat_pengajuan (tabel_sumber, id_data, id_operator, status_lama, status_baru, catatan) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt_riwayat->execute(['sejarah', $new_id, $_SESSION['id_user'], null, 'pending', 'Tambah roadmap: ' . $_POST['judul']]);
            
            $success = "Roadmap berhasil ditambahkan! Menunggu persetujuan admin.";
        }
        
        elseif ($action == 'edit_roadmap') {
            // Cek kepemilikan dan status
            $stmt_check = $pdo->prepare("SELECT id_user, status FROM sejarah WHERE id_sejarah = ?");
            $stmt_check->execute([$_POST['id_sejarah']]);
            $data = $stmt_check->fetch();
            
            if ($data && $data['id_user'] == $_SESSION['id_user'] && ($data['status'] == 'pending' || $data['status'] == 'rejected')) {
                $stmt = $pdo->prepare("UPDATE sejarah SET tahun=?, judul=?, deskripsi=?, urutan=?, status='pending' WHERE id_sejarah=?");
                $stmt->execute([$_POST['tahun'], $_POST['judul'], $_POST['deskripsi'], $_POST['urutan'], $_POST['id_sejarah']]);
                
                $success = "Roadmap berhasil diupdate! Menunggu persetujuan admin.";
            } else {
                $error = "Anda hanya bisa edit data pending/rejected milik Anda!";
            }
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
    
    if ($data && $data['id_user'] == $_SESSION['id_user'] && ($data['status'] == 'pending' || $data['status'] == 'rejected')) {
        $pdo->prepare("DELETE FROM visi WHERE id_visi = ?")->execute([$_GET['delete_visi']]);
        
        $stmt_riwayat = $pdo->prepare("INSERT INTO riwayat_pengajuan (tabel_sumber, id_data, id_operator, status_lama, status_baru, catatan) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt_riwayat->execute(['visi', $_GET['delete_visi'], $_SESSION['id_user'], $data['status'], 'deleted', 'Hapus visi']);
        
        $success = "Visi berhasil dihapus!";
    } else {
        $error = "Anda hanya bisa menghapus data pending/rejected milik Anda!";
    }
}

// Handle DELETE Misi
if (isset($_GET['delete_misi'])) {
    $stmt_check = $pdo->prepare("SELECT id_user, status FROM misi WHERE id_misi = ?");
    $stmt_check->execute([$_GET['delete_misi']]);
    $data = $stmt_check->fetch();
    
    if ($data && $data['id_user'] == $_SESSION['id_user'] && ($data['status'] == 'pending' || $data['status'] == 'rejected')) {
        $pdo->prepare("DELETE FROM misi WHERE id_misi = ?")->execute([$_GET['delete_misi']]);
        
        $stmt_riwayat = $pdo->prepare("INSERT INTO riwayat_pengajuan (tabel_sumber, id_data, id_operator, status_lama, status_baru, catatan) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt_riwayat->execute(['misi', $_GET['delete_misi'], $_SESSION['id_user'], $data['status'], 'deleted', 'Hapus misi']);
        
        $success = "Misi berhasil dihapus!";
    } else {
        $error = "Anda hanya bisa menghapus data pending/rejected milik Anda!";
    }
}

// Handle DELETE Roadmap
if (isset($_GET['delete_roadmap'])) {
    $stmt_check = $pdo->prepare("SELECT id_user, judul, status FROM sejarah WHERE id_sejarah = ?");
    $stmt_check->execute([$_GET['delete_roadmap']]);
    $data = $stmt_check->fetch();
    
    if ($data && $data['id_user'] == $_SESSION['id_user'] && ($data['status'] == 'pending' || $data['status'] == 'rejected')) {
        $pdo->prepare("DELETE FROM sejarah WHERE id_sejarah = ?")->execute([$_GET['delete_roadmap']]);
        
        $stmt_riwayat = $pdo->prepare("INSERT INTO riwayat_pengajuan (tabel_sumber, id_data, id_operator, status_lama, status_baru, catatan) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt_riwayat->execute(['sejarah', $_GET['delete_roadmap'], $_SESSION['id_user'], $data['status'], 'deleted', 'Hapus roadmap: ' . $data['judul']]);
        
        $success = "Roadmap berhasil dihapus!";
    } else {
        $error = "Anda hanya bisa menghapus data pending/rejected milik Anda!";
    }
}

// Get data - hanya milik operator ini
$stmt_profil = $pdo->prepare("SELECT * FROM tentang_kami WHERE id_user = ? LIMIT 1");
$stmt_profil->execute([$_SESSION['id_user']]);
$profil_data = $stmt_profil->fetch();

$stmt_visi = $pdo->prepare("SELECT * FROM visi WHERE id_user = ? ORDER BY urutan, id_visi");
$stmt_visi->execute([$_SESSION['id_user']]);
$visi_list = $stmt_visi->fetchAll();

$stmt_misi = $pdo->prepare("SELECT * FROM misi WHERE id_user = ? ORDER BY urutan");
$stmt_misi->execute([$_SESSION['id_user']]);
$misi_list = $stmt_misi->fetchAll();

$stmt_roadmap = $pdo->prepare("SELECT * FROM sejarah WHERE id_user = ? ORDER BY tahun DESC, urutan");
$stmt_roadmap->execute([$_SESSION['id_user']]);
$roadmap_list = $stmt_roadmap->fetchAll();

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
    <i class="bi bi-info-circle"></i> Semua data yang Anda tambahkan akan berstatus <span class="badge bg-warning text-dark">Pending</span> dan menunggu persetujuan admin.
</div>

<!-- Profil Lab -->
<div class="card shadow mb-4">
    <div class="card-header">
        <h5 class="mb-0">Profil Lab & Logo</h5>
    </div>
    <div class="card-body">
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="profil">
            
            <?php if ($profil_data && $profil_data['status'] == 'active'): ?>
                <div class="alert alert-success">
                    <i class="bi bi-check-circle"></i> Profil Anda sudah disetujui admin. Anda tidak bisa mengubahnya lagi.
                </div>
            <?php elseif ($profil_data && $profil_data['status'] == 'pending'): ?>
                <div class="alert alert-warning">
                    <i class="bi bi-clock"></i> Profil Anda sedang menunggu persetujuan admin.
                </div>
            <?php elseif ($profil_data && $profil_data['status'] == 'rejected'): ?>
                <div class="alert alert-danger">
                    <i class="bi bi-x-circle"></i> Profil Anda ditolak admin. Silakan perbaiki dan ajukan kembali.
                </div>
            <?php endif; ?>
            
            <div class="mb-3">
                <label class="form-label">Logo Lab</label>
                <?php if ($profil_data && $profil_data['logo_lab']): ?>
                    <div class="mb-2">
                        <img src="../../uploads/tentang/<?php echo $profil_data['logo_lab']; ?>" height="100">
                    </div>
                <?php endif; ?>
                <?php if (!$profil_data || $profil_data['status'] == 'pending' || $profil_data['status'] == 'rejected'): ?>
                    <input type="file" class="form-control" name="logo_lab" accept="image/*">
                <?php endif; ?>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Penjelasan Logo</label>
                <textarea class="form-control" name="penjelasan_logo" rows="3" <?php echo ($profil_data && $profil_data['status'] == 'active') ? 'readonly' : ''; ?>><?php echo $profil_data['penjelasan_logo'] ?? ''; ?></textarea>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Profil Lab</label>
                <textarea class="form-control" name="profil_lab" rows="6" required <?php echo ($profil_data && $profil_data['status'] == 'active') ? 'readonly' : ''; ?>><?php echo $profil_data['profil_lab'] ?? ''; ?></textarea>
            </div>
            
            <?php if (!$profil_data || $profil_data['status'] == 'pending' || $profil_data['status'] == 'rejected'): ?>
                <button type="submit" class="btn btn-primary">Simpan & Ajukan</button>
            <?php endif; ?>
        </form>
    </div>
</div>

<!-- Visi -->
<div class="card shadow mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Visi</h5>
        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#visiModal" onclick="resetVisiForm()">
            <i class="bi bi-plus"></i> Tambah
        </button>
    </div>
    <div class="card-body">
        <?php if (count($visi_list) > 0): ?>
            <?php foreach ($visi_list as $visi): ?>
            <div class="alert alert-info d-flex justify-content-between align-items-start">
                <div class="flex-grow-1">
                    <?php echo htmlspecialchars($visi['isi_visi']); ?>
                    <?php if ($visi['status'] == 'pending'): ?>
                        <span class="badge bg-warning text-dark ms-2">Pending</span>
                    <?php elseif ($visi['status'] == 'active'): ?>
                        <span class="badge bg-success ms-2">Approved</span>
                    <?php else: ?>
                        <span class="badge bg-danger ms-2">Rejected</span>
                    <?php endif; ?>
                </div>
                <?php if ($visi['status'] == 'pending' || $visi['status'] == 'rejected'): ?>
                <div class="ms-3 d-flex gap-1 flex-shrink-0">
                    <button class="btn btn-sm btn-warning" onclick='editVisi(<?php echo json_encode($visi); ?>)' title="Edit">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <a href="?delete_visi=<?php echo $visi['id_visi']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Hapus?')" title="Hapus">
                        <i class="bi bi-trash"></i>
                    </a>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="text-muted">Belum ada visi</p>
        <?php endif; ?>
    </div>
</div>

<!-- Misi -->
<div class="card shadow mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Misi</h5>
        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#misiModal" onclick="resetMisiForm()">
            <i class="bi bi-plus"></i> Tambah
        </button>
    </div>
    <div class="card-body">
        <?php if (count($misi_list) > 0): ?>
            <ol class="list-group list-group-numbered">
            <?php foreach ($misi_list as $misi): ?>
                <li class="list-group-item d-flex justify-content-between align-items-start">
                    <div class="ms-2 me-auto">
                        <?php echo htmlspecialchars($misi['isi_misi']); ?>
                        <?php if ($misi['status'] == 'pending'): ?>
                            <span class="badge bg-warning text-dark ms-2">Pending</span>
                        <?php elseif ($misi['status'] == 'active'): ?>
                            <span class="badge bg-success ms-2">Approved</span>
                        <?php else: ?>
                            <span class="badge bg-danger ms-2">Rejected</span>
                        <?php endif; ?>
                    </div>
                    <?php if ($misi['status'] == 'pending' || $misi['status'] == 'rejected'): ?>
                    <div class="d-flex gap-1 flex-shrink-0">
                        <button class="btn btn-sm btn-warning" onclick='editMisi(<?php echo json_encode($misi); ?>)' title="Edit">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <a href="?delete_misi=<?php echo $misi['id_misi']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Hapus?')" title="Hapus">
                            <i class="bi bi-trash"></i>
                        </a>
                    </div>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
            </ol>
        <?php else: ?>
            <p class="text-muted">Belum ada misi</p>
        <?php endif; ?>
    </div>
</div>

<!-- Roadmap -->
<div class="card shadow mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Roadmap</h5>
        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#roadmapModal" onclick="resetRoadmapForm()">
            <i class="bi bi-plus"></i> Tambah
        </button>
    </div>
    <div class="card-body">
        <?php if (count($roadmap_list) > 0): ?>
            <?php foreach ($roadmap_list as $rdm): ?>
            <div class="card mb-3 border-start border-primary border-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="text-primary mb-1"><?php echo htmlspecialchars($rdm['tahun']); ?></h6>
                            <strong class="d-block mb-2"><?php echo htmlspecialchars($rdm['judul']); ?></strong>
                            <?php if ($rdm['status'] == 'pending'): ?>
                                <span class="badge bg-warning text-dark">Pending</span>
                            <?php elseif ($rdm['status'] == 'active'): ?>
                                <span class="badge bg-success">Approved</span>
                            <?php else: ?>
                                <span class="badge bg-danger">Rejected</span>
                            <?php endif; ?>
                            <p class="mb-0 text-muted mt-2"><?php echo htmlspecialchars($rdm['deskripsi'] ?? ''); ?></p>
                        </div>
                        <?php if ($rdm['status'] == 'pending' || $rdm['status'] == 'rejected'): ?>
                        <div class="d-flex gap-1 flex-shrink-0 ms-3">
                            <button class="btn btn-sm btn-warning" onclick='editRoadmap(<?php echo json_encode($rdm); ?>)' title="Edit">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <a href="?delete_roadmap=<?php echo $rdm['id_sejarah']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Hapus?')" title="Hapus">
                                <i class="bi bi-trash"></i>
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="text-muted">Belum ada roadmap</p>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Visi -->
<div class="modal fade" id="visiModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" id="visi_action" value="add_visi">
                <input type="hidden" name="id_visi" id="id_visi">
                <div class="modal-header">
                    <h5 class="modal-title" id="visiModalTitle">Tambah Visi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="bi bi-info-circle"></i> Data akan berstatus <strong>Pending</strong>
                    </div>
                    <div class="mb-3">
                        <label>Isi Visi *</label>
                        <textarea class="form-control" name="isi_visi" id="isi_visi" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label>Urutan</label>
                        <input type="number" class="form-control" name="urutan" id="urutan_visi" value="<?php echo count($visi_list) + 1; ?>" min="1">
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
                <input type="hidden" name="action" id="misi_action" value="add_misi">
                <input type="hidden" name="id_misi" id="id_misi">
                <div class="modal-header">
                    <h5 class="modal-title" id="misiModalTitle">Tambah Misi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="bi bi-info-circle"></i> Data akan berstatus <strong>Pending</strong>
                    </div>
                    <div class="mb-3">
                        <label>Isi Misi *</label>
                        <textarea class="form-control" name="isi_misi" id="isi_misi" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label>Urutan</label>
                        <input type="number" class="form-control" name="urutan" id="urutan_misi" value="<?php echo count($misi_list) + 1; ?>" min="1">
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

<!-- Modal Roadmap -->
<div class="modal fade" id="roadmapModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" id="roadmap_action" value="add_roadmap">
                <input type="hidden" name="id_sejarah" id="id_roadmap">
                <div class="modal-header">
                    <h5 class="modal-title" id="roadmapModalTitle">Tambah Roadmap</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="bi bi-info-circle"></i> Data akan berstatus <strong>Pending</strong>
                    </div>
                    <div class="mb-3">
                        <label>Tahun *</label>
                        <input type="text" class="form-control" name="tahun" id="tahun_roadmap" required placeholder="2024">
                    </div>
                    <div class="mb-3">
                        <label>Judul *</label>
                        <input type="text" class="form-control" name="judul" id="judul_roadmap" required>
                    </div>
                    <div class="mb-3">
                        <label>Deskripsi</label>
                        <textarea class="form-control" name="deskripsi" id="deskripsi_roadmap" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label>Urutan</label>
                        <input type="number" class="form-control" name="urutan" id="urutan_roadmap" value="1" min="1">
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
// Visi Functions
function resetVisiForm() {
    document.getElementById('visiModalTitle').textContent = 'Tambah Visi';
    document.getElementById('visi_action').value = 'add_visi';
    document.getElementById('id_visi').value = '';
    document.getElementById('isi_visi').value = '';
    document.getElementById('urutan_visi').value = <?php echo count($visi_list) + 1; ?>;
}

function editVisi(data) {
    document.getElementById('visiModalTitle').textContent = 'Edit Visi';
    document.getElementById('visi_action').value = 'edit_visi';
    document.getElementById('id_visi').value = data.id_visi;
    document.getElementById('isi_visi').value = data.isi_visi;
    document.getElementById('urutan_visi').value = data.urutan || 1;
    new bootstrap.Modal(document.getElementById('visiModal')).show();
}

// Misi Functions
function resetMisiForm() {
    document.getElementById('misiModalTitle').textContent = 'Tambah Misi';
    document.getElementById('misi_action').value = 'add_misi';
    document.getElementById('id_misi').value = '';
    document.getElementById('isi_misi').value = '';
    document.getElementById('urutan_misi').value = <?php echo count($misi_list) + 1; ?>;
}

function editMisi(data) {
    document.getElementById('misiModalTitle').textContent = 'Edit Misi';
    document.getElementById('misi_action').value = 'edit_misi';
    document.getElementById('id_misi').value = data.id_misi;
    document.getElementById('isi_misi').value = data.isi_misi;
    document.getElementById('urutan_misi').value = data.urutan;
    new bootstrap.Modal(document.getElementById('misiModal')).show();
}

// Roadmap Functions
function resetRoadmapForm() {
    document.getElementById('roadmapModalTitle').textContent = 'Tambah Roadmap';
    document.getElementById('roadmap_action').value = 'add_roadmap';
    document.getElementById('id_roadmap').value = '';
    document.getElementById('tahun_roadmap').value = '';
    document.getElementById('judul_roadmap').value = '';
    document.getElementById('deskripsi_roadmap').value = '';
    document.getElementById('urutan_roadmap').value = 1;
}

function editRoadmap(data) {
    document.getElementById('roadmapModalTitle').textContent = 'Edit Roadmap';
    document.getElementById('roadmap_action').value = 'edit_roadmap';
    document.getElementById('id_roadmap').value = data.id_sejarah;
    document.getElementById('tahun_roadmap').value = data.tahun;
    document.getElementById('judul_roadmap').value = data.judul;
    document.getElementById('deskripsi_roadmap').value = data.deskripsi || '';
    document.getElementById('urutan_roadmap').value = data.urutan || 1;
    new bootstrap.Modal(document.getElementById('roadmapModal')).show();
}
</script>

<?php include "footer.php"; ?>