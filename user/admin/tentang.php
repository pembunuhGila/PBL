<?php
/**
 * Admin Tentang Kami - Simple Version
 * Admin bisa approve/reject pengajuan dari operator
 * Admin bisa edit data active langsung
 */
$required_role = "admin";
include "../auth.php";
include "../../conn.php";

$page_title = "Tentang Kami";
$current_page = "tentang.php";

// Handle Approve/Reject
if (isset($_GET['action']) && isset($_GET['type']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $type = $_GET['type'];
    $id = $_GET['id'];
    
    try {
        if ($type == 'profil') {
            if ($action == 'approve') {
                $pdo->query("DELETE FROM tentang_kami WHERE status = 'active'");
                $stmt = $pdo->prepare("UPDATE tentang_kami SET status = 'active', updated_at = CURRENT_TIMESTAMP WHERE id_profil = ?");
                $stmt->execute([$id]);
                $success = "Profil berhasil disetujui!";
            } elseif ($action == 'reject') {
                $stmt = $pdo->prepare("DELETE FROM tentang_kami WHERE id_profil = ?");
                $stmt->execute([$id]);
                $success = "Profil berhasil ditolak!";
            }
        }
        elseif ($type == 'visi') {
            if ($action == 'approve') {
                $stmt = $pdo->prepare("UPDATE visi SET status = 'active', updated_at = CURRENT_TIMESTAMP WHERE id_visi = ?");
                $stmt->execute([$id]);
                $success = "Visi berhasil disetujui!";
            } elseif ($action == 'reject') {
                $stmt = $pdo->prepare("DELETE FROM visi WHERE id_visi = ?");
                $stmt->execute([$id]);
                $success = "Visi berhasil ditolak!";
            }
        }
        elseif ($type == 'misi') {
            if ($action == 'approve') {
                $stmt = $pdo->prepare("UPDATE misi SET status = 'active', updated_at = CURRENT_TIMESTAMP WHERE id_misi = ?");
                $stmt->execute([$id]);
                $success = "Misi berhasil disetujui!";
            } elseif ($action == 'reject') {
                $stmt = $pdo->prepare("DELETE FROM misi WHERE id_misi = ?");
                $stmt->execute([$id]);
                $success = "Misi berhasil ditolak!";
            }
        }
        elseif ($type == 'roadmap') {
            if ($action == 'approve') {
                $stmt = $pdo->prepare("UPDATE sejarah SET status = 'active', updated_at = CURRENT_TIMESTAMP WHERE id_sejarah = ?");
                $stmt->execute([$id]);
                $success = "Roadmap berhasil disetujui!";
            } elseif ($action == 'reject') {
                $stmt = $pdo->prepare("DELETE FROM sejarah WHERE id_sejarah = ?");
                $stmt->execute([$id]);
                $success = "Roadmap berhasil ditolak!";
            }
        }
    } catch (PDOException $e) {
        $error = "Gagal: " . $e->getMessage();
    }
}

// Handle POST - Edit Active
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
            
            $check = $pdo->query("SELECT id_profil FROM tentang_kami WHERE status = 'active' LIMIT 1")->fetch();
            
            if ($check) {
                if ($logo_lab) {
                    $stmt = $pdo->prepare("UPDATE tentang_kami SET profil_lab=?, logo_lab=?, penjelasan_logo=?, updated_at=CURRENT_TIMESTAMP WHERE id_profil=?");
                    $stmt->execute([$profil_lab, $logo_lab, $penjelasan_logo, $check['id_profil']]);
                } else {
                    $stmt = $pdo->prepare("UPDATE tentang_kami SET profil_lab=?, penjelasan_logo=?, updated_at=CURRENT_TIMESTAMP WHERE id_profil=?");
                    $stmt->execute([$profil_lab, $penjelasan_logo, $check['id_profil']]);
                }
            } else {
                $stmt = $pdo->prepare("INSERT INTO tentang_kami (profil_lab, logo_lab, penjelasan_logo, status, id_user) VALUES (?, ?, ?, 'active', ?)");
                $stmt->execute([$profil_lab, $logo_lab, $penjelasan_logo, $_SESSION['id_user']]);
            }
            $success = "Profil berhasil disimpan!";
        }
        elseif ($action == 'add_visi') {
            $stmt = $pdo->prepare("INSERT INTO visi (isi_visi, urutan, status, id_user) VALUES (?, ?, 'active', ?)");
            $stmt->execute([$_POST['isi_visi'], $_POST['urutan'], $_SESSION['id_user']]);
            $success = "Visi berhasil ditambahkan!";
        }
        elseif ($action == 'edit_visi') {
            $stmt = $pdo->prepare("UPDATE visi SET isi_visi=?, urutan=?, updated_at=CURRENT_TIMESTAMP WHERE id_visi=?");
            $stmt->execute([$_POST['isi_visi'], $_POST['urutan'], $_POST['id_visi']]);
            $success = "Visi berhasil diupdate!";
        }
        elseif ($action == 'add_misi') {
            $stmt = $pdo->prepare("INSERT INTO misi (isi_misi, urutan, status, id_user) VALUES (?, ?, 'active', ?)");
            $stmt->execute([$_POST['isi_misi'], $_POST['urutan'], $_SESSION['id_user']]);
            $success = "Misi berhasil ditambahkan!";
        }
        elseif ($action == 'edit_misi') {
            $stmt = $pdo->prepare("UPDATE misi SET isi_misi=?, urutan=?, updated_at=CURRENT_TIMESTAMP WHERE id_misi=?");
            $stmt->execute([$_POST['isi_misi'], $_POST['urutan'], $_POST['id_misi']]);
            $success = "Misi berhasil diupdate!";
        }
        elseif ($action == 'add_roadmap') {
            $stmt = $pdo->prepare("INSERT INTO sejarah (tahun, judul, deskripsi, urutan, status, id_user) VALUES (?, ?, ?, ?, 'active', ?)");
            $stmt->execute([$_POST['tahun'], $_POST['judul'], $_POST['deskripsi'], $_POST['urutan'], $_SESSION['id_user']]);
            $success = "Roadmap berhasil ditambahkan!";
        }
        elseif ($action == 'edit_roadmap') {
            $stmt = $pdo->prepare("UPDATE sejarah SET tahun=?, judul=?, deskripsi=?, urutan=?, updated_at=CURRENT_TIMESTAMP WHERE id_sejarah=?");
            $stmt->execute([$_POST['tahun'], $_POST['judul'], $_POST['deskripsi'], $_POST['urutan'], $_POST['id_sejarah']]);
            $success = "Roadmap berhasil diupdate!";
        }
    } catch (PDOException $e) {
        $error = "Gagal: " . $e->getMessage();
    }
}

// Handle DELETE
if (isset($_GET['delete_visi'])) {
    $pdo->prepare("DELETE FROM visi WHERE id_visi = ?")->execute([$_GET['delete_visi']]);
    $success = "Visi berhasil dihapus!";
}
if (isset($_GET['delete_misi'])) {
    $pdo->prepare("DELETE FROM misi WHERE id_misi = ?")->execute([$_GET['delete_misi']]);
    $success = "Misi berhasil dihapus!";
}
if (isset($_GET['delete_roadmap'])) {
    $pdo->prepare("DELETE FROM sejarah WHERE id_sejarah = ?")->execute([$_GET['delete_roadmap']]);
    $success = "Roadmap berhasil dihapus!";
}

// Get DATA
$profil_data = $pdo->query("SELECT * FROM tentang_kami WHERE status = 'active' LIMIT 1")->fetch();
$visi_list = $pdo->query("SELECT * FROM visi WHERE status = 'active' ORDER BY urutan, id_visi")->fetchAll();
$misi_list = $pdo->query("SELECT * FROM misi WHERE status = 'active' ORDER BY urutan, id_misi")->fetchAll();
$roadmap_list = $pdo->query("SELECT * FROM sejarah WHERE status = 'active' ORDER BY tahun DESC, urutan")->fetchAll();

// Get PENDING
$pending_profil = $pdo->query("SELECT t.*, u.nama as operator_nama FROM tentang_kami t LEFT JOIN users u ON t.id_user = u.id_user WHERE t.status = 'pending' ORDER BY t.updated_at DESC")->fetchAll();
$pending_visi = $pdo->query("SELECT v.*, u.nama as operator_nama FROM visi v LEFT JOIN users u ON v.id_user = u.id_user WHERE v.status = 'pending' ORDER BY v.updated_at DESC")->fetchAll();
$pending_misi = $pdo->query("SELECT m.*, u.nama as operator_nama FROM misi m LEFT JOIN users u ON m.id_user = u.id_user WHERE m.status = 'pending' ORDER BY m.updated_at DESC")->fetchAll();
$pending_roadmap = $pdo->query("SELECT s.*, u.nama as operator_nama FROM sejarah s LEFT JOIN users u ON s.id_user = u.id_user WHERE s.status = 'pending' ORDER BY s.updated_at DESC")->fetchAll();

$total_pending = count($pending_profil) + count($pending_visi) + count($pending_misi) + count($pending_roadmap);

include "header.php";
include "sidebar.php";
include "navbar.php";
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Tentang Kami</h1>
    <?php if ($total_pending > 0): ?>
        <span class="badge bg-warning fs-6">
            <i class="bi bi-clock-history"></i> <?php echo $total_pending; ?> Pending
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

<!-- PENDING SECTION -->
<?php if ($total_pending > 0): ?>
<div class="card shadow mb-4 border-warning">
    <div class="card-header bg-warning">
        <h5 class="mb-0">
            <i class="bi bi-clock-history"></i> Pengajuan Pending 
            <span class="badge bg-dark"><?php echo $total_pending; ?></span>
        </h5>
    </div>
    <div class="card-body">
        
        <?php if (count($pending_profil) > 0): ?>
        <h6 class="text-primary mb-3"><i class="bi bi-building"></i> Profil Lab</h6>
        <?php foreach ($pending_profil as $p): ?>
        <div class="card mb-3 border-warning">
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <div>
                        <strong>Dari: <?php echo htmlspecialchars($p['operator_nama']); ?></strong>
                        <small class="text-muted d-block"><?php echo date('d M Y H:i', strtotime($p['updated_at'])); ?></small>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="?action=approve&type=profil&id=<?php echo $p['id_profil']; ?>" class="btn btn-sm btn-success" onclick="return confirm('Approve dan aktifkan?')">
                            <i class="bi bi-check-circle"></i> Approve
                        </a>
                        <a href="?action=reject&type=profil&id=<?php echo $p['id_profil']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Reject?')">
                            <i class="bi bi-x-circle"></i> Reject
                        </a>
                    </div>
                </div>
                
                <?php if ($p['logo_lab']): ?>
                    <img src="../../uploads/tentang/<?php echo $p['logo_lab']; ?>" height="80" class="mb-2 border">
                <?php endif; ?>
                <p class="mb-1"><strong>Profil:</strong></p>
                <p class="small"><?php echo nl2br(htmlspecialchars($p['profil_lab'])); ?></p>
                <?php if ($p['penjelasan_logo']): ?>
                    <p class="small text-muted mb-0"><em>Penjelasan: <?php echo htmlspecialchars($p['penjelasan_logo']); ?></em></p>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
        <hr>
        <?php endif; ?>
        
        <?php if (count($pending_visi) > 0): ?>
        <h6 class="text-primary mb-3"><i class="bi bi-eye"></i> Visi</h6>
        <?php foreach ($pending_visi as $v): ?>
        <div class="alert alert-warning d-flex justify-content-between align-items-start mb-2">
            <div class="flex-grow-1">
                <?php echo htmlspecialchars($v['isi_visi']); ?>
                <br><small class="text-muted">Dari: <?php echo htmlspecialchars($v['operator_nama']); ?></small>
            </div>
            <div class="d-flex gap-1 ms-3">
                <a href="?action=approve&type=visi&id=<?php echo $v['id_visi']; ?>" class="btn btn-xs btn-success"><i class="bi bi-check"></i></a>
                <a href="?action=reject&type=visi&id=<?php echo $v['id_visi']; ?>" class="btn btn-xs btn-danger"><i class="bi bi-x"></i></a>
            </div>
        </div>
        <?php endforeach; ?>
        <hr>
        <?php endif; ?>
        
        <?php if (count($pending_misi) > 0): ?>
        <h6 class="text-primary mb-3"><i class="bi bi-list-check"></i> Misi</h6>
        <?php foreach ($pending_misi as $m): ?>
        <div class="alert alert-warning d-flex justify-content-between align-items-start mb-2">
            <div class="flex-grow-1">
                <strong>#<?php echo $m['urutan']; ?>:</strong> <?php echo htmlspecialchars($m['isi_misi']); ?>
                <br><small class="text-muted">Dari: <?php echo htmlspecialchars($m['operator_nama']); ?></small>
            </div>
            <div class="d-flex gap-1 ms-3">
                <a href="?action=approve&type=misi&id=<?php echo $m['id_misi']; ?>" class="btn btn-xs btn-success"><i class="bi bi-check"></i></a>
                <a href="?action=reject&type=misi&id=<?php echo $m['id_misi']; ?>" class="btn btn-xs btn-danger"><i class="bi bi-x"></i></a>
            </div>
        </div>
        <?php endforeach; ?>
        <hr>
        <?php endif; ?>
        
        <?php if (count($pending_roadmap) > 0): ?>
        <h6 class="text-primary mb-3"><i class="bi bi-signpost"></i> Roadmap</h6>
        <?php foreach ($pending_roadmap as $r): ?>
        <div class="alert alert-warning d-flex justify-content-between align-items-start mb-2">
            <div class="flex-grow-1">
                <strong><?php echo htmlspecialchars($r['tahun']); ?>:</strong> <?php echo htmlspecialchars($r['judul']); ?>
                <?php if ($r['deskripsi']): ?>
                    <p class="mb-0 mt-1 small text-muted"><?php echo htmlspecialchars($r['deskripsi']); ?></p>
                <?php endif; ?>
                <small class="text-muted">Dari: <?php echo htmlspecialchars($r['operator_nama']); ?></small>
            </div>
            <div class="d-flex gap-1 ms-3">
                <a href="?action=approve&type=roadmap&id=<?php echo $r['id_sejarah']; ?>" class="btn btn-xs btn-success"><i class="bi bi-check"></i></a>
                <a href="?action=reject&type=roadmap&id=<?php echo $r['id_sejarah']; ?>" class="btn btn-xs btn-danger"><i class="bi bi-x"></i></a>
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
        <!-- Profil -->
        <div class="card shadow mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-building"></i> Profil Lab</h5>
            </div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="profil">
                    
                    <div class="mb-3">
                        <label class="form-label"><strong>Logo Lab</strong></label>
                        <?php if ($profil_data && $profil_data['logo_lab']): ?>
                            <div class="mb-2">
                                <img src="../../uploads/tentang/<?php echo $profil_data['logo_lab']; ?>" height="100" class="border">
                            </div>
                        <?php endif; ?>
                        <input type="file" class="form-control" name="logo_lab" accept="image/*">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label"><strong>Penjelasan Logo</strong></label>
                        <textarea class="form-control" name="penjelasan_logo" rows="2"><?php echo htmlspecialchars($profil_data['penjelasan_logo'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label"><strong>Profil Lab *</strong></label>
                        <textarea class="form-control" name="profil_lab" rows="5" required><?php echo htmlspecialchars($profil_data['profil_lab'] ?? ''); ?></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-floppy"></i> Simpan
                    </button>
                </form>
            </div>
        </div>
        
        <!-- Visi -->
        <div class="card shadow mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-eye"></i> Visi</h5>
            </div>
            <div class="card-body">
                <?php if (count($visi_list) > 0): ?>
                    <?php foreach ($visi_list as $v): ?>
                    <div class="alert alert-success mb-2 py-2 d-flex justify-content-between align-items-start">
                        <small class="flex-grow-1"><?php echo htmlspecialchars($v['isi_visi']); ?></small>
                        <div class="d-flex gap-1 ms-2">
                            <button class="btn btn-xs btn-warning" onclick='editVisi(<?php echo json_encode($v); ?>)'><i class="bi bi-pencil"></i></button>
                            <a href="?delete_visi=<?php echo $v['id_visi']; ?>" class="btn btn-xs btn-danger" onclick="return confirm('Hapus?')"><i class="bi bi-trash"></i></a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-muted small">Belum ada visi</p>
                <?php endif; ?>
                <button class="btn btn-primary btn-sm mt-2" data-bs-toggle="modal" data-bs-target="#visiModal" onclick="resetVisiForm()">
                    <i class="bi bi-plus-circle"></i> Tambah Visi
                </button>
            </div>
        </div>
        
        <!-- Misi -->
        <div class="card shadow mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-list-check"></i> Misi</h5>
            </div>
            <div class="card-body">
                <?php if (count($misi_list) > 0): ?>
                    <ol class="ps-3">
                    <?php foreach ($misi_list as $m): ?>
                        <li class="mb-2">
                            <small class="d-flex justify-content-between align-items-start">
                                <span><?php echo htmlspecialchars($m['isi_misi']); ?></span>
                                <div class="d-flex gap-1 ms-2">
                                    <button class="btn btn-xs btn-warning" onclick='editMisi(<?php echo json_encode($m); ?>)'><i class="bi bi-pencil"></i></button>
                                    <a href="?delete_misi=<?php echo $m['id_misi']; ?>" class="btn btn-xs btn-danger" onclick="return confirm('Hapus?')"><i class="bi bi-trash"></i></a>
                                </div>
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
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-signpost"></i> Roadmap</h5>
            </div>
            <div class="card-body">
                <?php if (count($roadmap_list) > 0): ?>
                    <?php foreach ($roadmap_list as $r): ?>
                    <div class="card mb-2 border-primary">
                        <div class="card-body p-2 d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <strong class="text-primary"><?php echo htmlspecialchars($r['tahun']); ?>:</strong> 
                                <small><?php echo htmlspecialchars($r['judul']); ?></small>
                                <?php if ($r['deskripsi']): ?>
                                    <p class="mb-0 mt-1 text-muted" style="font-size: 11px;"><?php echo htmlspecialchars($r['deskripsi']); ?></p>
                                <?php endif; ?>
                            </div>
                            <div class="d-flex gap-1 ms-2">
                                <button class="btn btn-xs btn-warning" onclick='editRoadmap(<?php echo json_encode($r); ?>)'><i class="bi bi-pencil"></i></button>
                                <a href="?delete_roadmap=<?php echo $r['id_sejarah']; ?>" class="btn btn-xs btn-danger" onclick="return confirm('Hapus?')"><i class="bi bi-trash"></i></a>
                            </div>
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
                    <div class="mb-3">
                        <label class="form-label"><strong>Tahun *</strong></label>
                        <input type="number" class="form-control" name="tahun" id="tahun" min="1900" max="2100" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><strong>Judul *</strong></label>
                        <input type="text" class="form-control" name="judul" id="judul" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea class="form-control" name="deskripsi" id="deskripsi" rows="3"></textarea>
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
// Visi Functions
function resetVisiForm() {
    document.getElementById('visi_action').value = 'add_visi';
    document.getElementById('id_visi').value = '';
    document.getElementById('isi_visi').value = '';
    document.getElementById('urutan_visi').value = '1';
    document.getElementById('visiModalTitle').textContent = 'Tambah Visi';
}

function editVisi(data) {
    document.getElementById('visi_action').value = 'edit_visi';
    document.getElementById('id_visi').value = data.id_visi;
    document.getElementById('isi_visi').value = data.isi_visi;
    document.getElementById('urutan_visi').value = data.urutan;
    document.getElementById('visiModalTitle').textContent = 'Edit Visi';
    
    var modal = new bootstrap.Modal(document.getElementById('visiModal'));
    modal.show();
}

// Misi Functions
function resetMisiForm() {
    document.getElementById('misi_action').value = 'add_misi';
    document.getElementById('id_misi').value = '';
    document.getElementById('isi_misi').value = '';
    document.getElementById('urutan_misi').value = '1';
    document.getElementById('misiModalTitle').textContent = 'Tambah Misi';
}

function editMisi(data) {
    document.getElementById('misi_action').value = 'edit_misi';
    document.getElementById('id_misi').value = data.id_misi;
    document.getElementById('isi_misi').value = data.isi_misi;
    document.getElementById('urutan_misi').value = data.urutan;
    document.getElementById('misiModalTitle').textContent = 'Edit Misi';
    
    var modal = new bootstrap.Modal(document.getElementById('misiModal'));
    modal.show();
}

// Roadmap Functions
function resetRoadmapForm() {
    document.getElementById('roadmap_action').value = 'add_roadmap';
    document.getElementById('id_roadmap').value = '';
    document.getElementById('tahun').value = '';
    document.getElementById('judul').value = '';
    document.getElementById('deskripsi').value = '';
    document.getElementById('urutan_roadmap').value = '1';
    document.getElementById('roadmapModalTitle').textContent = 'Tambah Roadmap';
}

function editRoadmap(data) {
    document.getElementById('roadmap_action').value = 'edit_roadmap';
    document.getElementById('id_roadmap').value = data.id_sejarah;
    document.getElementById('tahun').value = data.tahun;
    document.getElementById('judul').value = data.judul;
    document.getElementById('deskripsi').value = data.deskripsi;
    document.getElementById('urutan_roadmap').value = data.urutan;
    document.getElementById('roadmapModalTitle').textContent = 'Edit Roadmap';
    
    var modal = new bootstrap.Modal(document.getElementById('roadmapModal'));
    modal.show();
}
</script>

<?php include "footer.php"; ?>