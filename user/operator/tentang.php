<?php
$required_role = "operator";
include "../auth.php";
include "../../conn.php";

$page_title = "Tentang Kami";
$current_page = "tentang.php";

// Handle POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'];
    
    try {
        if ($action == 'profil') {
            $profil_lab = $_POST['profil_lab'];
            $penjelasan_logo = $_POST['penjelasan_logo'];
            
            $logo_lab = null;
            if (isset($_FILES['logo_lab']) && $_FILES['logo_lab']['error'] == 0) {
                $target_dir = "../../uploads/tentang/";
                if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);
                $logo_lab = 'logo_' . time() . '.' . pathinfo($_FILES['logo_lab']['name'], PATHINFO_EXTENSION);
                move_uploaded_file($_FILES['logo_lab']['tmp_name'], $target_dir . $logo_lab);
            }
            
            $stmt_check = $pdo->prepare("SELECT id_profil, status FROM tentang_kami WHERE id_user = ? AND status IN ('pending', 'rejected') LIMIT 1");
            $stmt_check->execute([$_SESSION['id_user']]);
            $existing = $stmt_check->fetch();
            
            if ($existing) {
                if ($logo_lab) {
                    $stmt = $pdo->prepare("UPDATE tentang_kami SET profil_lab=?, logo_lab=?, penjelasan_logo=?, status='pending', updated_at=CURRENT_TIMESTAMP WHERE id_profil=?");
                    $stmt->execute([$profil_lab, $logo_lab, $penjelasan_logo, $existing['id_profil']]);
                } else {
                    $stmt = $pdo->prepare("UPDATE tentang_kami SET profil_lab=?, penjelasan_logo=?, status='pending', updated_at=CURRENT_TIMESTAMP WHERE id_profil=?");
                    $stmt->execute([$profil_lab, $penjelasan_logo, $existing['id_profil']]);
                }
                $success = "Profil diupdate! Menunggu approval admin.";
            } else {
                $stmt = $pdo->prepare("INSERT INTO tentang_kami (profil_lab, logo_lab, penjelasan_logo, status, id_user) VALUES (?, ?, ?, 'pending', ?)");
                $stmt->execute([$profil_lab, $logo_lab, $penjelasan_logo, $_SESSION['id_user']]);
                $success = "Profil ditambahkan! Menunggu approval admin.";
            }
        }
        
        elseif ($action == 'edit_visi') {
            $id_visi = $_POST['id_visi'];
            $stmt_check = $pdo->prepare("SELECT id_user, status FROM visi WHERE id_visi = ?");
            $stmt_check->execute([$id_visi]);
            $data = $stmt_check->fetch();
            
            if ($data && $data['id_user'] == $_SESSION['id_user'] && in_array($data['status'], ['pending', 'rejected'])) {
                $stmt = $pdo->prepare("UPDATE visi SET isi_visi=?, urutan=?, status='pending', updated_at=CURRENT_TIMESTAMP WHERE id_visi=?");
                $stmt->execute([$_POST['isi_visi'], $_POST['urutan'], $id_visi]);
                $success = "Visi diupdate! Menunggu approval admin.";
            } elseif ($data && $data['status'] == 'active') {
                // Edit active -> insert pending baru
                $stmt = $pdo->prepare("INSERT INTO visi (isi_visi, urutan, status, id_user) VALUES (?, ?, 'pending', ?)");
                $stmt->execute([$_POST['isi_visi'], $_POST['urutan'], $_SESSION['id_user']]);
                $success = "Visi diupdate! Menunggu approval admin.";
            } else {
                $error = "Tidak bisa edit data ini!";
            }
        }
        
        elseif ($action == 'add_misi') {
            $stmt = $pdo->prepare("INSERT INTO misi (isi_misi, urutan, status, id_user) VALUES (?, ?, 'pending', ?)");
            $stmt->execute([$_POST['isi_misi'], $_POST['urutan'], $_SESSION['id_user']]);
            $success = "Misi ditambahkan! Menunggu approval admin.";
        }
        
        elseif ($action == 'edit_misi') {
            $id_misi = $_POST['id_misi'];
            $stmt_check = $pdo->prepare("SELECT id_user, status FROM misi WHERE id_misi = ?");
            $stmt_check->execute([$id_misi]);
            $data = $stmt_check->fetch();
            
            if ($data && $data['id_user'] == $_SESSION['id_user'] && in_array($data['status'], ['pending', 'rejected'])) {
                $stmt = $pdo->prepare("UPDATE misi SET isi_misi=?, urutan=?, status='pending', updated_at=CURRENT_TIMESTAMP WHERE id_misi=?");
                $stmt->execute([$_POST['isi_misi'], $_POST['urutan'], $id_misi]);
                $success = "Misi diupdate! Menunggu approval admin.";
            } elseif ($data && $data['status'] == 'active') {
                // Edit active -> insert pending baru
                $stmt = $pdo->prepare("INSERT INTO misi (isi_misi, urutan, status, id_user) VALUES (?, ?, 'pending', ?)");
                $stmt->execute([$_POST['isi_misi'], $_POST['urutan'], $_SESSION['id_user']]);
                $success = "Misi diupdate! Menunggu approval admin.";
            } else {
                $error = "Tidak bisa edit data ini!";
            }
        }
        
        elseif ($action == 'add_roadmap') {
            $stmt = $pdo->prepare("INSERT INTO sejarah (tahun, judul, deskripsi, urutan, status, id_user) VALUES (?, ?, ?, ?, 'pending', ?)");
            $stmt->execute([$_POST['tahun'], $_POST['judul'], $_POST['deskripsi'], $_POST['urutan'], $_SESSION['id_user']]);
            $success = "Roadmap ditambahkan! Menunggu approval admin.";
        }
        
        elseif ($action == 'edit_roadmap') {
            $id_sejarah = $_POST['id_sejarah'];
            $stmt_check = $pdo->prepare("SELECT id_user, status FROM sejarah WHERE id_sejarah = ?");
            $stmt_check->execute([$id_sejarah]);
            $data = $stmt_check->fetch();
            
            if ($data && $data['id_user'] == $_SESSION['id_user'] && in_array($data['status'], ['pending', 'rejected'])) {
                $stmt = $pdo->prepare("UPDATE sejarah SET tahun=?, judul=?, deskripsi=?, urutan=?, status='pending', updated_at=CURRENT_TIMESTAMP WHERE id_sejarah=?");
                $stmt->execute([$_POST['tahun'], $_POST['judul'], $_POST['deskripsi'], $_POST['urutan'], $id_sejarah]);
                $success = "Roadmap diupdate! Menunggu approval admin.";
            } elseif ($data && $data['status'] == 'active') {
                // Edit active -> insert pending baru
                $stmt = $pdo->prepare("INSERT INTO sejarah (tahun, judul, deskripsi, urutan, status, id_user) VALUES (?, ?, ?, ?, 'pending', ?)");
                $stmt->execute([$_POST['tahun'], $_POST['judul'], $_POST['deskripsi'], $_POST['urutan'], $_SESSION['id_user']]);
                $success = "Roadmap diupdate! Menunggu approval admin.";
            } else {
                $error = "Tidak bisa edit data ini!";
            }
        }
    } catch (PDOException $e) {
        $error = "Gagal: " . $e->getMessage();
    }
}

// Handle DELETE (only pending/rejected milik sendiri)
if (isset($_GET['delete_visi'])) {
    $stmt_check = $pdo->prepare("SELECT id_user, status FROM visi WHERE id_visi = ?");
    $stmt_check->execute([$_GET['delete_visi']]);
    $data = $stmt_check->fetch();
    
    if ($data && $data['id_user'] == $_SESSION['id_user'] && in_array($data['status'], ['pending', 'rejected'])) {
        $pdo->prepare("DELETE FROM visi WHERE id_visi = ?")->execute([$_GET['delete_visi']]);
        $success = "Visi dihapus!";
    }
}

if (isset($_GET['delete_misi'])) {
    $stmt_check = $pdo->prepare("SELECT id_user, status FROM misi WHERE id_misi = ?");
    $stmt_check->execute([$_GET['delete_misi']]);
    $data = $stmt_check->fetch();
    
    if ($data && $data['id_user'] == $_SESSION['id_user'] && in_array($data['status'], ['pending', 'rejected'])) {
        $pdo->prepare("DELETE FROM misi WHERE id_misi = ?")->execute([$_GET['delete_misi']]);
        $success = "Misi dihapus!";
    }
}

if (isset($_GET['delete_roadmap'])) {
    $stmt_check = $pdo->prepare("SELECT id_user, status FROM sejarah WHERE id_sejarah = ?");
    $stmt_check->execute([$_GET['delete_roadmap']]);
    $data = $stmt_check->fetch();
    
    if ($data && $data['id_user'] == $_SESSION['id_user'] && in_array($data['status'], ['pending', 'rejected'])) {
        $pdo->prepare("DELETE FROM sejarah WHERE id_sejarah = ?")->execute([$_GET['delete_roadmap']]);
        $success = "Roadmap dihapus!";
    }
}

// Get ACTIVE data
$profil_active = $pdo->query("SELECT * FROM tentang_kami WHERE status = 'active' LIMIT 1")->fetch();
$visi_active = $pdo->query("SELECT * FROM visi WHERE status = 'active' ORDER BY urutan, id_visi")->fetchAll();
$misi_active = $pdo->query("SELECT * FROM misi WHERE status = 'active' ORDER BY urutan, id_misi")->fetchAll();
$roadmap_active = $pdo->query("SELECT * FROM sejarah WHERE status = 'active' ORDER BY tahun DESC, urutan")->fetchAll();

// Get MY pending/rejected data
$stmt_my_profil = $pdo->prepare("SELECT * FROM tentang_kami WHERE id_user = ? AND status IN ('pending', 'rejected') LIMIT 1");
$stmt_my_profil->execute([$_SESSION['id_user']]);
$my_profil = $stmt_my_profil->fetch();

$stmt_my_visi = $pdo->prepare("SELECT * FROM visi WHERE id_user = ? AND status IN ('pending', 'rejected') ORDER BY urutan, id_visi");
$stmt_my_visi->execute([$_SESSION['id_user']]);
$my_visi = $stmt_my_visi->fetchAll();

$stmt_my_misi = $pdo->prepare("SELECT * FROM misi WHERE id_user = ? AND status IN ('pending', 'rejected') ORDER BY urutan, id_misi");
$stmt_my_misi->execute([$_SESSION['id_user']]);
$my_misi = $stmt_my_misi->fetchAll();

$stmt_my_roadmap = $pdo->prepare("SELECT * FROM sejarah WHERE id_user = ? AND status IN ('pending', 'rejected') ORDER BY tahun DESC, urutan");
$stmt_my_roadmap->execute([$_SESSION['id_user']]);
$my_roadmap = $stmt_my_roadmap->fetchAll();

$total_my_pending = count($my_visi) + count($my_misi) + count($my_roadmap);
if ($my_profil) $total_my_pending++;

include "header.php";
include "sidebar.php";
include "navbar.php";
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Tentang Kami</h1>
    <?php if ($total_my_pending > 0): ?>
        <span class="badge bg-warning fs-6">
            <i class="bi bi-clock-history"></i> <?php echo $total_my_pending; ?> Pending
        </span>
    <?php endif; ?>
</div>

<?php if (isset($success)): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <i class="bi bi-check-circle"></i> <?php echo $success; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (isset($error)): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="bi bi-exclamation-triangle"></i> <?php echo $error; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="alert alert-info">
    <i class="bi bi-info-circle"></i> <strong>Info:</strong> Setiap perubahan data akan berstatus <span class="badge bg-warning text-dark">Pending</span> dan perlu approval admin.
</div>

<!-- MY PENDING/REJECTED SUBMISSIONS -->
<?php if ($total_my_pending > 0): ?>
<div class="card shadow mb-4 border-warning">
    <div class="card-header bg-warning">
        <h5 class="mb-0">
            <i class="bi bi-hourglass-split"></i> Pengajuan Saya 
            <span class="badge bg-dark"><?php echo $total_my_pending; ?></span>
        </h5>
    </div>
    <div class="card-body">
        
        <!-- PROFIL PENDING -->
        <?php if ($my_profil): ?>
        <h6 class="text-primary mb-3"><i class="bi bi-building"></i> Profil Lab</h6>
        <div class="card mb-3 border-<?php echo $my_profil['status'] == 'pending' ? 'warning' : 'danger'; ?>">
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span class="badge bg-<?php echo $my_profil['status'] == 'pending' ? 'warning' : 'danger'; ?> text-<?php echo $my_profil['status'] == 'pending' ? 'dark' : 'white'; ?>">
                        <?php echo ucfirst($my_profil['status']); ?>
                    </span>
                    <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editProfilModal">
                        <i class="bi bi-pencil"></i> Edit
                    </button>
                </div>
                
                <?php if ($my_profil['logo_lab']): ?>
                    <img src="../../uploads/tentang/<?php echo $my_profil['logo_lab']; ?>" height="80" class="mb-2 border">
                <?php endif; ?>
                <p class="mb-1"><strong>Profil:</strong></p>
                <p class="small"><?php echo nl2br(htmlspecialchars($my_profil['profil_lab'])); ?></p>
                <?php if ($my_profil['penjelasan_logo']): ?>
                    <p class="small text-muted mb-0"><em>Penjelasan: <?php echo htmlspecialchars($my_profil['penjelasan_logo']); ?></em></p>
                <?php endif; ?>
            </div>
        </div>
        <hr>
        <?php endif; ?>
        
        <!-- VISI PENDING -->
        <?php if (count($my_visi) > 0): ?>
        <h6 class="text-primary mb-3"><i class="bi bi-eye"></i> Visi</h6>
        <?php foreach ($my_visi as $v): ?>
        <div class="alert alert-<?php echo $v['status'] == 'pending' ? 'warning' : 'danger'; ?> d-flex justify-content-between align-items-start mb-2">
            <div class="flex-grow-1">
                <?php echo htmlspecialchars($v['isi_visi']); ?>
                <span class="badge bg-<?php echo $v['status'] == 'pending' ? 'secondary' : 'dark'; ?> ms-2">
                    <?php echo ucfirst($v['status']); ?>
                </span>
            </div>
            <div class="d-flex gap-1 ms-3">
                <button class="btn btn-xs btn-warning" onclick='editVisi(<?php echo json_encode($v); ?>)'>
                    <i class="bi bi-pencil"></i>
                </button>
                <a href="?delete_visi=<?php echo $v['id_visi']; ?>" class="btn btn-xs btn-danger" onclick="return confirm('Hapus?')">
                    <i class="bi bi-trash"></i>
                </a>
            </div>
        </div>
        <?php endforeach; ?>
        <hr>
        <?php endif; ?>
        
        <!-- MISI PENDING -->
        <?php if (count($my_misi) > 0): ?>
        <h6 class="text-primary mb-3"><i class="bi bi-list-check"></i> Misi</h6>
        <?php foreach ($my_misi as $m): ?>
        <div class="alert alert-<?php echo $m['status'] == 'pending' ? 'warning' : 'danger'; ?> d-flex justify-content-between align-items-start mb-2">
            <div class="flex-grow-1">
                <strong>#<?php echo $m['urutan']; ?>:</strong> <?php echo htmlspecialchars($m['isi_misi']); ?>
                <span class="badge bg-<?php echo $m['status'] == 'pending' ? 'secondary' : 'dark'; ?> ms-2">
                    <?php echo ucfirst($m['status']); ?>
                </span>
            </div>
            <div class="d-flex gap-1 ms-3">
                <button class="btn btn-xs btn-warning" onclick='editMisi(<?php echo json_encode($m); ?>)'>
                    <i class="bi bi-pencil"></i>
                </button>
                <a href="?delete_misi=<?php echo $m['id_misi']; ?>" class="btn btn-xs btn-danger" onclick="return confirm('Hapus?')">
                    <i class="bi bi-trash"></i>
                </a>
            </div>
        </div>
        <?php endforeach; ?>
        <hr>
        <?php endif; ?>
        
        <!-- ROADMAP PENDING -->
        <?php if (count($my_roadmap) > 0): ?>
        <h6 class="text-primary mb-3"><i class="bi bi-signpost"></i> Roadmap</h6>
        <?php foreach ($my_roadmap as $r): ?>
        <div class="alert alert-<?php echo $r['status'] == 'pending' ? 'warning' : 'danger'; ?> d-flex justify-content-between align-items-start mb-2">
            <div class="flex-grow-1">
                <strong><?php echo htmlspecialchars($r['tahun']); ?>:</strong> <?php echo htmlspecialchars($r['judul']); ?>
                <span class="badge bg-<?php echo $r['status'] == 'pending' ? 'secondary' : 'dark'; ?> ms-2">
                    <?php echo ucfirst($r['status']); ?>
                </span>
                <?php if ($r['deskripsi']): ?>
                    <p class="mb-0 mt-1 small text-muted"><?php echo htmlspecialchars($r['deskripsi']); ?></p>
                <?php endif; ?>
            </div>
            <div class="d-flex gap-1 ms-3">
                <button class="btn btn-xs btn-warning" onclick='editRoadmap(<?php echo json_encode($r); ?>)'>
                    <i class="bi bi-pencil"></i>
                </button>
                <a href="?delete_roadmap=<?php echo $r['id_sejarah']; ?>" class="btn btn-xs btn-danger" onclick="return confirm('Hapus?')">
                    <i class="bi bi-trash"></i>
                </a>
            </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
        
    </div>
</div>
<?php endif; ?>

<!-- DATA ACTIVE -->
<div class="row">
    <div class="col-md-8">
        <div class="card shadow mb-4">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="bi bi-building"></i> Profil Lab</h5>
            </div>
            <div class="card-body">
                <?php if ($profil_active): ?>
                    <?php if ($profil_active['logo_lab']): ?>
                        <img src="../../uploads/tentang/<?php echo $profil_active['logo_lab']; ?>" height="100" class="mb-3 border">
                    <?php endif; ?>
                    <p><?php echo nl2br(htmlspecialchars($profil_active['profil_lab'])); ?></p>
                    <?php if ($profil_active['penjelasan_logo']): ?>
                        <p class="text-muted small"><em>Penjelasan Logo: <?php echo htmlspecialchars($profil_active['penjelasan_logo']); ?></em></p>
                    <?php endif; ?>
                    
                    <?php if (!$my_profil): ?>
                        <button class="btn btn-success btn-sm mt-2" data-bs-toggle="modal" data-bs-target="#editProfilAktifModal">
                            <i class="bi bi-pencil-square"></i> Edit & Ajukan Perubahan
                        </button>
                    <?php endif; ?>
                <?php else: ?>
                    <p class="text-muted">Belum ada profil</p>
                    <?php if (!$my_profil): ?>
                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#profilModal">
                            <i class="bi bi-plus-circle"></i> Tambah Profil
                        </button>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Visi -->
        <div class="card shadow mb-4">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="bi bi-eye"></i> Visi</h5>
            </div>
            <div class="card-body">
                <?php if (count($visi_active) > 0): ?>
                    <?php foreach ($visi_active as $v): ?>
                    <div class="alert alert-success mb-2 py-2 d-flex justify-content-between align-items-start">
                        <small class="flex-grow-1"><?php echo htmlspecialchars($v['isi_visi']); ?></small>
                        <button class="btn btn-xs btn-warning ms-2" onclick='editVisi(<?php echo json_encode($v); ?>)'>
                            <i class="bi bi-pencil"></i>
                        </button>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-muted small">Belum ada visi</p>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Misi -->
        <div class="card shadow mb-4">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="bi bi-list-check"></i> Misi</h5>
            </div>
            <div class="card-body">
                <?php if (count($misi_active) > 0): ?>
                    <ol class="ps-3">
                    <?php foreach ($misi_active as $m): ?>
                        <li class="mb-2">
                            <small class="d-flex justify-content-between align-items-start">
                                <span><?php echo htmlspecialchars($m['isi_misi']); ?></span>
                                <button class="btn btn-xs btn-warning ms-2" onclick='editMisi(<?php echo json_encode($m); ?>)'>
                                    <i class="bi bi-pencil"></i>
                                </button>
                            </small>
                        </li>
                    <?php endforeach; ?>
                    </ol>
                <?php else: ?>
                    <p class="text-muted small">Belum ada misi</p>
                <?php endif; ?>
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#misiModal" onclick="resetMisiForm()">
                    <i class="bi bi-plus-circle"></i> Tambah Misi
                </button>
            </div>
        </div>
        
        <!-- Roadmap -->
        <div class="card shadow mb-4">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="bi bi-signpost"></i> Roadmap</h5>
            </div>
            <div class="card-body">
                <?php if (count($roadmap_active) > 0): ?>
                    <?php foreach ($roadmap_active as $r): ?>
                    <div class="card mb-2 border-success">
                        <div class="card-body p-2 d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <strong class="text-success"><?php echo htmlspecialchars($r['tahun']); ?>:</strong> 
                                <small><?php echo htmlspecialchars($r['judul']); ?></small>
                                <?php if ($r['deskripsi']): ?>
                                    <p class="mb-0 mt-1 text-muted" style="font-size: 11px;"><?php echo htmlspecialchars($r['deskripsi']); ?></p>
                                <?php endif; ?>
                            </div>
                            <button class="btn btn-xs btn-warning ms-2" onclick='editRoadmap(<?php echo json_encode($r); ?>)'>
                                <i class="bi bi-pencil"></i>
                            </button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-muted small">Belum ada roadmap</p>
                <?php endif; ?>
                <button class="btn btn-primary btn-sm mt-2" data-bs-toggle="modal" data-bs-target="#roadmapModal" onclick="resetRoadmapForm()">
                    <i class="bi bi-plus-circle"></i> Tambah Roadmap
                </button>
            </div>
        </div>
    </div>
</div>

<!-- MODALS -->

<!-- Modal Edit Profil Aktif (jika belum punya pending) -->
<?php if (!$my_profil && $profil_active): ?>
<div class="modal fade" id="editProfilAktifModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="profil">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Profil Lab</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="bi bi-info-circle"></i> Perubahan akan berstatus <strong>Pending</strong>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label"><strong>Logo Lab</strong></label>
                        <?php if ($profil_active['logo_lab']): ?>
                            <div class="mb-2">
                                <img src="../../uploads/tentang/<?php echo $profil_active['logo_lab']; ?>" height="100" class="border">
                            </div>
                        <?php endif; ?>
                        <input type="file" class="form-control" name="logo_lab" accept="image/*">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label"><strong>Penjelasan Logo</strong></label>
                        <textarea class="form-control" name="penjelasan_logo" rows="2"><?php echo htmlspecialchars($profil_active['penjelasan_logo'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label"><strong>Profil Lab *</strong></label>
                        <textarea class="form-control" name="profil_lab" rows="5" required><?php echo htmlspecialchars($profil_active['profil_lab']); ?></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-pencil-square"></i> Simpan & Ajukan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Modal Tambah Profil Baru -->
<?php if (!$my_profil && !$profil_active): ?>
<div class="modal fade" id="profilModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="profil">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Profil Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="bi bi-info-circle"></i> Data akan berstatus <strong>Pending</strong>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Logo Lab</label>
                        <input type="file" class="form-control" name="logo_lab" accept="image/*">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Penjelasan Logo</label>
                        <textarea class="form-control" name="penjelasan_logo" rows="2"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label"><strong>Profil Lab *</strong></label>
                        <textarea class="form-control" name="profil_lab" rows="5" required></textarea>
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
<?php endif; ?>

<!-- Modal Visi -->
<div class="modal fade" id="visiModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" id="visi_action" value="add_visi">
                <input type="hidden" name="id_visi" id="id_visi">
                <div class="modal-body">
                    <div class="alert alert-warning small">
                        <i class="bi bi-info-circle"></i> Status: <strong>Pending</strong>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><strong>Isi Visi *</strong></label>
                        <textarea class="form-control" name="isi_visi" id="isi_visi" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Urutan</label>
                        <input type="number" class="form-control" name="urutan" id="urutan_visi" value="1" min="1">
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
                <input type="hidden" name="action" id="misi_action" value="add_misi">
                <input type="hidden" name="id_misi" id="id_misi">
                <div class="modal-header">
                    <h5 class="modal-title" id="misiModalTitle">Tambah Misi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning small">
                        <i class="bi bi-info-circle"></i> Status: <strong>Pending</strong>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><strong>Isi Misi *</strong></label>
                        <textarea class="form-control" name="isi_misi" id="isi_misi" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Urutan</label>
                        <input type="number" class="form-control" name="urutan" id="urutan_misi" value="1" min="1">
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
                    <div class="alert alert-warning small">
                        <i class="bi bi-info-circle"></i> Status: <strong>Pending</strong>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><strong>Tahun *</strong></label>
                        <input type="text" class="form-control" name="tahun" id="tahun_roadmap" placeholder="2024" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><strong>Judul *</strong></label>
                        <input type="text" class="form-control" name="judul" id="judul_roadmap" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea class="form-control" name="deskripsi" id="deskripsi_roadmap" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Urutan</label>
                        <input type="number" class="form-control" name="urutan" id="urutan_roadmap" value="1" min="1">
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
// VISI
function editVisi(data) {
    document.getElementById('visiModalTitle').textContent = 'Edit Visi';
    document.getElementById('visi_action').value = 'edit_visi';
    document.getElementById('id_visi').value = data.id_visi;
    document.getElementById('isi_visi').value = data.isi_visi;
    document.getElementById('urutan_visi').value = data.urutan || 1;
    new bootstrap.Modal(document.getElementById('visiModal')).show();
}

// MISI
function resetMisiForm() {
    document.getElementById('misiModalTitle').textContent = 'Tambah Misi';
    document.getElementById('misi_action').value = 'add_misi';
    document.getElementById('id_misi').value = '';
    document.getElementById('isi_misi').value = '';
    document.getElementById('urutan_misi').value = 1;
}

function editMisi(data) {
    document.getElementById('misiModalTitle').textContent = 'Edit Misi';
    document.getElementById('misi_action').value = 'edit_misi';
    document.getElementById('id_misi').value = data.id_misi;
    document.getElementById('isi_misi').value = data.isi_misi;
    document.getElementById('urutan_misi').value = data.urutan || 1;
    new bootstrap.Modal(document.getElementById('misiModal')).show();
}

// ROADMAP
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