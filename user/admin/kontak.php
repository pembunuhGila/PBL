<?php
/**
 * Admin Kontak, Navbar, Footer - Integrated System
 * Admin bisa edit semua data active
 * Admin bisa approve/reject pending dari operator
 */
$required_role = "admin";
include "../auth.php";
include "../../conn.php";

$page_title = "Kontak & Branding";
$current_page = "kontak.php";

// ==================== HANDLE UPLOAD ====================
function handleUpload($file, $target_dir = "../../uploads/branding/") {
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $file_extension = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'svg', 'webp'];
    
    if (!in_array($file_extension, $allowed_extensions)) {
        return ['success' => false, 'message' => 'Format file tidak didukung'];
    }
    
    $new_filename = uniqid() . '_' . time() . '.' . $file_extension;
    $target_file = $target_dir . $new_filename;
    
    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        return ['success' => true, 'filename' => $new_filename];
    }
    
    return ['success' => false, 'message' => 'Gagal upload file'];
}

// ==================== HANDLE KONTAK ACTIONS ====================
if (isset($_GET['action']) && isset($_GET['id']) && isset($_GET['type']) && $_GET['type'] == 'kontak') {
    $id = $_GET['id'];
    $action = $_GET['action'];
    
    try {
        if ($action == 'approve') {
            $pdo->query("DELETE FROM kontak WHERE status = 'active'");
            $stmt = $pdo->prepare("UPDATE kontak SET status = 'active', updated_at = CURRENT_TIMESTAMP WHERE id_kontak = ?");
            $stmt->execute([$id]);
            $success = "Kontak berhasil disetujui dan diaktifkan!";
        } elseif ($action == 'reject') {
            $stmt = $pdo->prepare("UPDATE kontak SET status = 'rejected', updated_at = CURRENT_TIMESTAMP WHERE id_kontak = ?");
            $stmt->execute([$id]);
            $success = "Kontak berhasil ditolak!";
        }
    } catch (PDOException $e) {
        $error = "Gagal: " . $e->getMessage();
    }
}

// ==================== HANDLE NAVBAR ACTIONS ====================
if (isset($_GET['action']) && isset($_GET['id']) && isset($_GET['type']) && $_GET['type'] == 'navbar') {
    $id = $_GET['id'];
    $action = $_GET['action'];
    
    try {
        if ($action == 'approve') {
            $pdo->query("DELETE FROM navbar WHERE status = 'active'");
            $stmt = $pdo->prepare("UPDATE navbar SET status = 'active', updated_at = CURRENT_TIMESTAMP WHERE id_nav = ?");
            $stmt->execute([$id]);
            $success = "Logo navbar berhasil disetujui!";
        } elseif ($action == 'reject') {
            $stmt = $pdo->prepare("UPDATE navbar SET status = 'rejected', updated_at = CURRENT_TIMESTAMP WHERE id_nav = ?");
            $stmt->execute([$id]);
            $success = "Logo navbar berhasil ditolak!";
        }
    } catch (PDOException $e) {
        $error = "Gagal: " . $e->getMessage();
    }
}

// ==================== HANDLE FOOTER ACTIONS ====================
if (isset($_GET['action']) && isset($_GET['id']) && isset($_GET['type']) && $_GET['type'] == 'footer') {
    $id = $_GET['id'];
    $action = $_GET['action'];
    
    try {
        if ($action == 'approve') {
            $pdo->query("DELETE FROM footer WHERE status = 'active'");
            $stmt = $pdo->prepare("UPDATE footer SET status = 'active', updated_at = CURRENT_TIMESTAMP WHERE id_footer = ?");
            $stmt->execute([$id]);
            $success = "Logo footer berhasil disetujui!";
        } elseif ($action == 'reject') {
            $stmt = $pdo->prepare("UPDATE footer SET status = 'rejected', updated_at = CURRENT_TIMESTAMP WHERE id_footer = ?");
            $stmt->execute([$id]);
            $success = "Logo footer berhasil ditolak!";
        }
    } catch (PDOException $e) {
        $error = "Gagal: " . $e->getMessage();
    }
}

// ==================== HANDLE FORM SUBMISSIONS ====================
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // KONTAK FORM
    if (isset($_POST['form_type']) && $_POST['form_type'] == 'kontak') {
        $whatsapp = $_POST['whatsapp'] ?? '';
        $email = $_POST['email'] ?? '';
        $alamat = $_POST['alamat'] ?? '';
        $linkedin = $_POST['linkedin'] ?? '';
        $jam_operasional = $_POST['jam_operasional'] ?? '';
        $instagram = $_POST['instagram'] ?? '';
        $youtube = $_POST['youtube'] ?? '';
        $facebook = $_POST['facebook'] ?? '';
        $maps = $_POST['maps'] ?? '';
        
        try {
            $active = $pdo->query("SELECT id_kontak FROM kontak WHERE status = 'active' LIMIT 1")->fetch();
            
            if ($active) {
                $stmt = $pdo->prepare("UPDATE kontak SET whatsapp=?, email=?, alamat=?, linkedin=?, jam_operasional=?, instagram=?, youtube=?, facebook=?, maps=?, updated_at=CURRENT_TIMESTAMP WHERE id_kontak=?");
                $stmt->execute([$whatsapp, $email, $alamat, $linkedin, $jam_operasional, $instagram, $youtube, $facebook, $maps, $active['id_kontak']]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO kontak (whatsapp, email, alamat, linkedin, jam_operasional, instagram, youtube, facebook, maps, status, id_user) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'active', ?)");
                $stmt->execute([$whatsapp, $email, $alamat, $linkedin, $jam_operasional, $instagram, $youtube, $facebook, $maps, $_SESSION['id_user']]);
            }
            
            $success = "Informasi kontak berhasil disimpan!";
        } catch (PDOException $e) {
            $error = "Gagal menyimpan kontak: " . $e->getMessage();
        }
    }
    
    // NAVBAR FORM
    if (isset($_POST['form_type']) && $_POST['form_type'] == 'navbar') {
        try {
            $logo_path = null;
            
            if (isset($_FILES['logo_navbar']) && $_FILES['logo_navbar']['error'] == 0) {
                $upload = handleUpload($_FILES['logo_navbar']);
                if ($upload['success']) {
                    $logo_path = 'uploads/branding/' . $upload['filename'];
                } else {
                    throw new Exception($upload['message']);
                }
            }
            
            $active = $pdo->query("SELECT id_nav FROM navbar WHERE status = 'active' LIMIT 1")->fetch();
            
            if ($active) {
                if ($logo_path) {
                    $stmt = $pdo->prepare("UPDATE navbar SET logo=?, updated_at=CURRENT_TIMESTAMP WHERE id_nav=?");
                    $stmt->execute([$logo_path, $active['id_nav']]);
                }
            } else {
                if ($logo_path) {
                    $stmt = $pdo->prepare("INSERT INTO navbar (logo, status, id_user) VALUES (?, 'active', ?)");
                    $stmt->execute([$logo_path, $_SESSION['id_user']]);
                }
            }
            
            $success = "Logo navbar berhasil disimpan!";
        } catch (Exception $e) {
            $error = "Gagal menyimpan navbar: " . $e->getMessage();
        }
    }
    
    // FOOTER FORM
    if (isset($_POST['form_type']) && $_POST['form_type'] == 'footer') {
        try {
            $logo_path = null;
            
            if (isset($_FILES['logo_footer']) && $_FILES['logo_footer']['error'] == 0) {
                $upload = handleUpload($_FILES['logo_footer']);
                if ($upload['success']) {
                    $logo_path = 'uploads/branding/' . $upload['filename'];
                } else {
                    throw new Exception($upload['message']);
                }
            }
            
            $active = $pdo->query("SELECT id_footer FROM footer WHERE status = 'active' LIMIT 1")->fetch();
            
            if ($active) {
                if ($logo_path) {
                    $stmt = $pdo->prepare("UPDATE footer SET logo=?, updated_at=CURRENT_TIMESTAMP WHERE id_footer=?");
                    $stmt->execute([$logo_path, $active['id_footer']]);
                }
            } else {
                if ($logo_path) {
                    $stmt = $pdo->prepare("INSERT INTO footer (logo, status, id_user) VALUES (?, 'active', ?)");
                    $stmt->execute([$logo_path, $_SESSION['id_user']]);
                }
            }
            
            $success = "Logo footer berhasil disimpan!";
        } catch (Exception $e) {
            $error = "Gagal menyimpan footer: " . $e->getMessage();
        }
    }
}

// ==================== GET DATA ====================
$kontak = $pdo->query("SELECT * FROM kontak WHERE status = 'active' LIMIT 1")->fetch();
$navbar = $pdo->query("SELECT * FROM navbar WHERE status = 'active' LIMIT 1")->fetch();
$footer = $pdo->query("SELECT * FROM footer WHERE status = 'active' LIMIT 1")->fetch();

$pending_kontak = $pdo->query("SELECT k.*, u.nama as operator_nama FROM kontak k LEFT JOIN users u ON k.id_user = u.id_user WHERE k.status = 'pending' ORDER BY k.updated_at DESC")->fetchAll();
$pending_navbar = $pdo->query("SELECT n.*, u.nama as operator_nama FROM navbar n LEFT JOIN users u ON n.id_user = u.id_user WHERE n.status = 'pending' ORDER BY n.updated_at DESC")->fetchAll();
$pending_footer = $pdo->query("SELECT f.*, u.nama as operator_nama FROM footer f LEFT JOIN users u ON f.id_user = u.id_user WHERE f.status = 'pending' ORDER BY f.updated_at DESC")->fetchAll();

$total_pending = count($pending_kontak) + count($pending_navbar) + count($pending_footer);

include "header.php";
include "sidebar.php";
include "navbar.php";
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-gear"></i> Kontak & Branding</h1>
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

<!-- ==================== PENDING SUBMISSIONS ==================== -->
<?php if ($total_pending > 0): ?>
<div class="card shadow mb-4 border-warning">
    <div class="card-header bg-warning">
        <h5 class="mb-0"><i class="bi bi-clock-history"></i> Pengajuan Pending dari Operator</h5>
    </div>
    <div class="card-body">
        
        <!-- PENDING NAVBAR -->
        <?php if (count($pending_navbar) > 0): ?>
        <h6 class="text-primary mb-3"><i class="bi bi-image"></i> Logo Navbar</h6>
        <?php foreach ($pending_navbar as $pnav): ?>
        <div class="card mb-3 border-primary">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <strong>Dari: <?php echo htmlspecialchars($pnav['operator_nama'] ?? 'Unknown'); ?></strong>
                        <small class="text-muted d-block"><?php echo date('d M Y H:i', strtotime($pnav['updated_at'])); ?></small>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="?action=approve&id=<?php echo $pnav['id_nav']; ?>&type=navbar" class="btn btn-sm btn-success" onclick="return confirm('Approve logo navbar ini?')">
                            <i class="bi bi-check-circle"></i> Approve
                        </a>
                        <a href="?action=reject&id=<?php echo $pnav['id_nav']; ?>&type=navbar" class="btn btn-sm btn-danger" onclick="return confirm('Reject pengajuan ini?')">
                            <i class="bi bi-x-circle"></i> Reject
                        </a>
                    </div>
                </div>
                <?php if ($pnav['logo']): ?>
                <div class="mt-3">
                    <img src="../../<?php echo htmlspecialchars($pnav['logo']); ?>" alt="Logo Navbar" class="img-thumbnail" style="max-height: 100px;">
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
        <hr>
        <?php endif; ?>
        
        <!-- PENDING FOOTER -->
        <?php if (count($pending_footer) > 0): ?>
        <h6 class="text-success mb-3"><i class="bi bi-image"></i> Logo Footer</h6>
        <?php foreach ($pending_footer as $pfoot): ?>
        <div class="card mb-3 border-success">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <strong>Dari: <?php echo htmlspecialchars($pfoot['operator_nama'] ?? 'Unknown'); ?></strong>
                        <small class="text-muted d-block"><?php echo date('d M Y H:i', strtotime($pfoot['updated_at'])); ?></small>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="?action=approve&id=<?php echo $pfoot['id_footer']; ?>&type=footer" class="btn btn-sm btn-success" onclick="return confirm('Approve logo footer ini?')">
                            <i class="bi bi-check-circle"></i> Approve
                        </a>
                        <a href="?action=reject&id=<?php echo $pfoot['id_footer']; ?>&type=footer" class="btn btn-sm btn-danger" onclick="return confirm('Reject pengajuan ini?')">
                            <i class="bi bi-x-circle"></i> Reject
                        </a>
                    </div>
                </div>
                <?php if ($pfoot['logo']): ?>
                <div class="mt-3">
                    <img src="../../<?php echo htmlspecialchars($pfoot['logo']); ?>" alt="Logo Footer" class="img-thumbnail" style="max-height: 100px;">
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
        <hr>
        <?php endif; ?>
        
        <!-- PENDING KONTAK -->
        <?php if (count($pending_kontak) > 0): ?>
        <h6 class="text-info mb-3"><i class="bi bi-telephone"></i> Informasi Kontak</h6>
        <?php foreach ($pending_kontak as $pending): ?>
        <div class="card mb-3 border-info">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <strong>Dari: <?php echo htmlspecialchars($pending['operator_nama'] ?? 'Unknown'); ?></strong>
                        <small class="text-muted d-block"><?php echo date('d M Y H:i', strtotime($pending['updated_at'])); ?></small>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="?action=approve&id=<?php echo $pending['id_kontak']; ?>&type=kontak" class="btn btn-sm btn-success" onclick="return confirm('Approve kontak ini?')">
                            <i class="bi bi-check-circle"></i> Approve
                        </a>
                        <a href="?action=reject&id=<?php echo $pending['id_kontak']; ?>&type=kontak" class="btn btn-sm btn-danger" onclick="return confirm('Reject pengajuan ini?')">
                            <i class="bi bi-x-circle"></i> Reject
                        </a>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <p class="mb-2"><strong>WhatsApp:</strong> <?php echo htmlspecialchars($pending['whatsapp'] ?? '-'); ?></p>
                        <p class="mb-2"><strong>Email:</strong> <?php echo htmlspecialchars($pending['email'] ?? '-'); ?></p>
                        <p class="mb-2"><strong>Jam Operasional:</strong> <?php echo htmlspecialchars($pending['jam_operasional'] ?? '-'); ?></p>
                    </div>
                    <div class="col-md-6">
                        <p class="mb-2"><strong>LinkedIn:</strong> <?php echo htmlspecialchars($pending['linkedin'] ?? '-'); ?></p>
                        <p class="mb-2"><strong>Instagram:</strong> <?php echo htmlspecialchars($pending['instagram'] ?? '-'); ?></p>
                        <p class="mb-2"><strong>YouTube:</strong> <?php echo htmlspecialchars($pending['youtube'] ?? '-'); ?></p>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
        
    </div>
</div>
<?php endif; ?>

<!-- ==================== TABS ==================== -->
<ul class="nav nav-tabs mb-4" role="tablist">
    <li class="nav-item">
        <a class="nav-link active" data-bs-toggle="tab" href="#tab-branding">
            <i class="bi bi-image"></i> Branding (Navbar & Footer)
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-bs-toggle="tab" href="#tab-kontak">
            <i class="bi bi-telephone"></i> Informasi Kontak
        </a>
    </li>
</ul>

<div class="tab-content">
    
    <!-- ==================== TAB BRANDING ==================== -->
    <div class="tab-pane fade show active" id="tab-branding">
        <div class="row">
            
            <!-- NAVBAR LOGO -->
            <div class="col-md-6">
                <div class="card shadow mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="bi bi-image"></i> Logo Navbar</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($navbar && $navbar['logo']): ?>
                        <div class="text-center mb-3">
                            <img src="../../<?php echo htmlspecialchars($navbar['logo']); ?>" alt="Logo Navbar" class="img-thumbnail" style="max-height: 150px;">
                            <p class="text-muted mt-2 mb-0"><small>Logo saat ini</small></p>
                        </div>
                        <?php endif; ?>
                        
                        <form method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="form_type" value="navbar">
                            
                            <div class="mb-3">
                                <label class="form-label">Upload Logo Navbar Baru</label>
                                <input type="file" class="form-control" name="logo_navbar" accept="image/*" required>
                                <small class="text-muted">Format: JPG, PNG, SVG, WEBP (Max 2MB)</small>
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-upload"></i> Upload Logo Navbar
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- FOOTER LOGO -->
            <div class="col-md-6">
                <div class="card shadow mb-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="bi bi-image"></i> Logo Footer</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($footer && $footer['logo']): ?>
                        <div class="text-center mb-3">
                            <img src="../../<?php echo htmlspecialchars($footer['logo']); ?>" alt="Logo Footer" class="img-thumbnail" style="max-height: 150px;">
                            <p class="text-muted mt-2 mb-0"><small>Logo saat ini</small></p>
                        </div>
                        <?php endif; ?>
                        
                        <form method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="form_type" value="footer">
                            
                            <div class="mb-3">
                                <label class="form-label">Upload Logo Footer Baru</label>
                                <input type="file" class="form-control" name="logo_footer" accept="image/*" required>
                                <small class="text-muted">Format: JPG, PNG, SVG, WEBP (Max 2MB)</small>
                            </div>
                            
                            <button type="submit" class="btn btn-success w-100">
                                <i class="bi bi-upload"></i> Upload Logo Footer
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
    
    <!-- ==================== TAB KONTAK ==================== -->
    <div class="tab-pane fade" id="tab-kontak">
        <div class="row">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="bi bi-telephone"></i> Form Kontak</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="form_type" value="kontak">
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">
                                        <i class="bi bi-whatsapp text-success"></i> WhatsApp
                                    </label>
                                    <input type="text" class="form-control" name="whatsapp" 
                                           value="<?php echo htmlspecialchars($kontak['whatsapp'] ?? ''); ?>"
                                           placeholder="+62812345678">
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">
                                        <i class="bi bi-envelope text-danger"></i> Email
                                    </label>
                                    <input type="email" class="form-control" name="email" 
                                           value="<?php echo htmlspecialchars($kontak['email'] ?? ''); ?>"
                                           placeholder="lab@university.ac.id">
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">
                                    <i class="bi bi-geo-alt text-primary"></i> Alamat Lengkap
                                </label>
                                <textarea class="form-control" name="alamat" rows="3"><?php echo htmlspecialchars($kontak['alamat'] ?? ''); ?></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">
                                    <i class="bi bi-clock text-info"></i> Jam Operasional
                                </label>
                                <input type="text" class="form-control" name="jam_operasional" 
                                       value="<?php echo htmlspecialchars($kontak['jam_operasional'] ?? ''); ?>"
                                       placeholder="Senin - Jumat, 08:00 - 16:00">
                            </div>
                            
                            <hr class="my-4">
                            <h6 class="mb-3">Social Media</h6>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">
                                        <i class="bi bi-linkedin text-primary"></i> LinkedIn
                                    </label>
                                    <input type="url" class="form-control" name="linkedin" 
                                           value="<?php echo htmlspecialchars($kontak['linkedin'] ?? ''); ?>"
                                           placeholder="https://linkedin.com/company/...">
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">
                                        <i class="bi bi-instagram text-danger"></i> Instagram
                                    </label>
                                    <input type="url" class="form-control" name="instagram" 
                                           value="<?php echo htmlspecialchars($kontak['instagram'] ?? ''); ?>"
                                           placeholder="https://instagram.com/...">
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">
                                        <i class="bi bi-youtube text-danger"></i> YouTube
                                    </label>
                                    <input type="url" class="form-control" name="youtube" 
                                           value="<?php echo htmlspecialchars($kontak['youtube'] ?? ''); ?>"
                                           placeholder="https://youtube.com/@...">
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">
                                        <i class="bi bi-facebook text-primary"></i> Facebook
                                    </label>
                                    <input type="url" class="form-control" name="facebook" 
                                           value="<?php echo htmlspecialchars($kontak['facebook'] ?? ''); ?>"
                                           placeholder="https://facebook.com/...">
                                </div>
                            </div>
                            
                            <hr class="my-4">
                            
                            <div class="mb-3">
                                <label class="form-label">
                                    <i class="bi bi-map"></i> Google Maps Embed Code
                                </label>
                                <textarea class="form-control" name="maps" rows="3" placeholder="<iframe src='https://www.google.com/maps/embed?...' ...></iframe>"><?php echo htmlspecialchars($kontak['maps'] ?? ''); ?></textarea>
                                <small class="text-muted">Copy embed code dari Google Maps</small>
                            </div>
                            
                            <button type="submit" class="btn btn-info text-white">
                                <i class="bi bi-floppy"></i> Simpan Perubahan
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card shadow">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0">Preview Kontak</h6>
                    </div>
                    <div class="card-body">
                        <?php if ($kontak): ?>
                            <p class="mb-2">
                                <i class="bi bi-whatsapp text-success"></i> 
                                <strong>WhatsApp:</strong><br>
                                <small><?php echo htmlspecialchars($kontak['whatsapp'] ?? '-'); ?></small>
                            </p>
                            
                            <p class="mb-2">
                                <i class="bi bi-envelope text-danger"></i> 
                                <strong>Email:</strong><br>
                                <small><?php echo htmlspecialchars($kontak['email'] ?? '-'); ?></small>
                            </p>
                            
                            <p class="mb-2">
                                <i class="bi bi-geo-alt text-primary"></i> 
                                <strong>Alamat:</strong><br>
                                <small><?php echo nl2br(htmlspecialchars($kontak['alamat'] ?? '-')); ?></small>
                            </p>
                            
                            <p class="mb-2">
                                <i class="bi bi-clock text-info"></i> 
                                <strong>Jam:</strong><br>
                                <small><?php echo htmlspecialchars($kontak['jam_operasional'] ?? '-'); ?></small>
                            </p>
                            
                            <hr>
                            
                            <div class="d-flex gap-2 flex-wrap">
                                <?php if ($kontak['linkedin']): ?>
                                    <a href="<?php echo htmlspecialchars($kontak['linkedin']); ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-linkedin"></i>
                                    </a>
                                <?php endif; ?>
                                
                                <?php if ($kontak['instagram']): ?>
                                    <a href="<?php echo htmlspecialchars($kontak['instagram']); ?>" target="_blank" class="btn btn-sm btn-outline-danger">
                                        <i class="bi bi-instagram"></i>
                                    </a>
                                <?php endif; ?>
                                
                                <?php if ($kontak['youtube']): ?>
                                    <a href="<?php echo htmlspecialchars($kontak['youtube']); ?>" target="_blank" class="btn btn-sm btn-outline-danger">
                                        <i class="bi bi-youtube"></i>
                                    </a>
                                <?php endif; ?>
                                
                                <?php if ($kontak['facebook']): ?>
                                    <a href="<?php echo htmlspecialchars($kontak['facebook']); ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-facebook"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-muted text-center">Belum ada data kontak</p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <?php if ($kontak && $kontak['maps']): ?>
                <div class="card shadow mt-3">
                    <div class="card-header">
                        <h6 class="mb-0">Preview Maps</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="ratio ratio-1x1">
                            <?php echo $kontak['maps']; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
</div>

<?php include "footer.php"; ?>