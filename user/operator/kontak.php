<?php
/**
 * Operator Kontak, Navbar, Footer - Integrated System
 * Operator bisa submit data pending yang perlu approval admin
 */
$required_role = "operator";
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
            $check = $pdo->prepare("SELECT id_kontak FROM kontak WHERE id_user = ? AND status IN ('pending', 'rejected') LIMIT 1");
            $check->execute([$_SESSION['id_user']]);
            $existing = $check->fetch();
            
            if ($existing) {
                $stmt = $pdo->prepare("UPDATE kontak SET whatsapp=?, email=?, alamat=?, linkedin=?, jam_operasional=?, instagram=?, youtube=?, facebook=?, maps=?, status='pending', updated_at=CURRENT_TIMESTAMP WHERE id_kontak=?");
                $stmt->execute([$whatsapp, $email, $alamat, $linkedin, $jam_operasional, $instagram, $youtube, $facebook, $maps, $existing['id_kontak']]);
                $success = "Kontak berhasil diupdate! Menunggu persetujuan admin.";
            } else {
                $stmt = $pdo->prepare("INSERT INTO kontak (whatsapp, email, alamat, linkedin, jam_operasional, instagram, youtube, facebook, maps, status, id_user) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?)");
                $stmt->execute([$whatsapp, $email, $alamat, $linkedin, $jam_operasional, $instagram, $youtube, $facebook, $maps, $_SESSION['id_user']]);
                $success = "Kontak berhasil ditambahkan! Menunggu persetujuan admin.";
            }
        } catch (PDOException $e) {
            $error = "Gagal menyimpan kontak: " . $e->getMessage();
        }
    }
    
    // NAVBAR FORM
    if (isset($_POST['form_type']) && $_POST['form_type'] == 'navbar') {
        try {
            if (!isset($_FILES['logo_navbar']) || $_FILES['logo_navbar']['error'] != 0) {
                throw new Exception("File logo navbar harus diupload");
            }
            
            $upload = handleUpload($_FILES['logo_navbar']);
            if (!$upload['success']) {
                throw new Exception($upload['message']);
            }
            
            $logo_path = 'uploads/branding/' . $upload['filename'];
            
            $check = $pdo->prepare("SELECT id_nav FROM navbar WHERE id_user = ? AND status IN ('pending', 'rejected') LIMIT 1");
            $check->execute([$_SESSION['id_user']]);
            $existing = $check->fetch();
            
            if ($existing) {
                $stmt = $pdo->prepare("UPDATE navbar SET logo=?, status='pending', updated_at=CURRENT_TIMESTAMP WHERE id_nav=?");
                $stmt->execute([$logo_path, $existing['id_nav']]);
                $success = "Logo navbar berhasil diupdate! Menunggu persetujuan admin.";
            } else {
                $stmt = $pdo->prepare("INSERT INTO navbar (logo, status, id_user) VALUES (?, 'pending', ?)");
                $stmt->execute([$logo_path, $_SESSION['id_user']]);
                $success = "Logo navbar berhasil ditambahkan! Menunggu persetujuan admin.";
            }
        } catch (Exception $e) {
            $error = "Gagal menyimpan navbar: " . $e->getMessage();
        }
    }
    
    // FOOTER FORM
    if (isset($_POST['form_type']) && $_POST['form_type'] == 'footer') {
        try {
            if (!isset($_FILES['logo_footer']) || $_FILES['logo_footer']['error'] != 0) {
                throw new Exception("File logo footer harus diupload");
            }
            
            $upload = handleUpload($_FILES['logo_footer']);
            if (!$upload['success']) {
                throw new Exception($upload['message']);
            }
            
            $logo_path = 'uploads/branding/' . $upload['filename'];
            
            $check = $pdo->prepare("SELECT id_footer FROM footer WHERE id_user = ? AND status IN ('pending', 'rejected') LIMIT 1");
            $check->execute([$_SESSION['id_user']]);
            $existing = $check->fetch();
            
            if ($existing) {
                $stmt = $pdo->prepare("UPDATE footer SET logo=?, status='pending', updated_at=CURRENT_TIMESTAMP WHERE id_footer=?");
                $stmt->execute([$logo_path, $existing['id_footer']]);
                $success = "Logo footer berhasil diupdate! Menunggu persetujuan admin.";
            } else {
                $stmt = $pdo->prepare("INSERT INTO footer (logo, status, id_user) VALUES (?, 'pending', ?)");
                $stmt->execute([$logo_path, $_SESSION['id_user']]);
                $success = "Logo footer berhasil ditambahkan! Menunggu persetujuan admin.";
            }
        } catch (Exception $e) {
            $error = "Gagal menyimpan footer: " . $e->getMessage();
        }
    }
}

// ==================== GET DATA ====================
$kontak_active = $pdo->query("SELECT * FROM kontak WHERE status = 'active' LIMIT 1")->fetch();
$navbar_active = $pdo->query("SELECT * FROM navbar WHERE status = 'active' LIMIT 1")->fetch();
$footer_active = $pdo->query("SELECT * FROM footer WHERE status = 'active' LIMIT 1")->fetch();

$my_kontak = $pdo->prepare("SELECT * FROM kontak WHERE id_user = ? AND status IN ('pending', 'rejected') LIMIT 1");
$my_kontak->execute([$_SESSION['id_user']]);
$my_kontak = $my_kontak->fetch();

$my_navbar = $pdo->prepare("SELECT * FROM navbar WHERE id_user = ? AND status IN ('pending', 'rejected') LIMIT 1");
$my_navbar->execute([$_SESSION['id_user']]);
$my_navbar = $my_navbar->fetch();

$my_footer = $pdo->prepare("SELECT * FROM footer WHERE id_user = ? AND status IN ('pending', 'rejected') LIMIT 1");
$my_footer->execute([$_SESSION['id_user']]);
$my_footer = $my_footer->fetch();

include "header.php";
include "sidebar.php";
include "navbar.php";
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-gear"></i> Kontak & Branding</h1>
    <?php 
    $my_pending_count = 0;
    if ($my_kontak) $my_pending_count++;
    if ($my_navbar) $my_pending_count++;
    if ($my_footer) $my_pending_count++;
    ?>
    <?php if ($my_pending_count > 0): ?>
        <span class="badge bg-warning fs-6">
            <i class="bi bi-clock-history"></i> <?php echo $my_pending_count; ?> Pending
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

<!-- ==================== TABS ==================== -->
<ul class="nav nav-tabs mb-4" role="tablist">
    <li class="nav-item">
        <a class="nav-link active" data-bs-toggle="tab" href="#tab-branding">
            <i class="bi bi-image"></i> Branding (Navbar & Footer)
            <?php if ($my_navbar || $my_footer): ?>
                <span class="badge bg-warning text-dark ms-1">
                    <?php echo ($my_navbar ? 1 : 0) + ($my_footer ? 1 : 0); ?>
                </span>
            <?php endif; ?>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-bs-toggle="tab" href="#tab-kontak">
            <i class="bi bi-telephone"></i> Informasi Kontak
            <?php if ($my_kontak): ?>
                <span class="badge bg-warning text-dark ms-1">1</span>
            <?php endif; ?>
        </a>
    </li>
</ul>

<div class="tab-content">
    
    <!-- ==================== TAB BRANDING ==================== -->
    <div class="tab-pane fade show active" id="tab-branding">
        <div class="row">
            
            <!-- NAVBAR LOGO -->
            <div class="col-md-6">
                <div class="card shadow mb-4 <?php echo $my_navbar ? 'border-warning' : ''; ?>">
                    <div class="card-header <?php echo $my_navbar ? 'bg-warning' : 'bg-primary text-white'; ?>">
                        <h5 class="mb-0">
                            <i class="bi bi-image"></i> Logo Navbar
                            <?php if ($my_navbar): ?>
                                <span class="badge bg-<?php echo $my_navbar['status'] == 'pending' ? 'secondary' : 'danger'; ?> float-end">
                                    <?php echo ucfirst($my_navbar['status']); ?>
                                </span>
                            <?php endif; ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        
                        <!-- MY PENDING/REJECTED -->
                        <?php if ($my_navbar): ?>
                            <div class="alert alert-<?php echo $my_navbar['status'] == 'pending' ? 'warning' : 'danger'; ?>">
                                <i class="bi bi-<?php echo $my_navbar['status'] == 'pending' ? 'clock-history' : 'x-circle'; ?>"></i>
                                <?php if ($my_navbar['status'] == 'rejected'): ?>
                                    <strong>Ditolak Admin!</strong> Silakan upload ulang logo navbar.
                                <?php else: ?>
                                    <strong>Menunggu Approval</strong> Logo Anda sedang direview admin.
                                <?php endif; ?>
                            </div>
                            
                            <div class="text-center mb-3">
                                <img src="../../<?php echo htmlspecialchars($my_navbar['logo']); ?>" alt="Logo Navbar Pending" class="img-thumbnail" style="max-height: 150px;">
                                <p class="text-muted mt-2 mb-0"><small>Logo yang diajukan</small></p>
                            </div>
                        <?php endif; ?>
                        
                        <!-- ACTIVE LOGO REFERENCE -->
                        <?php if ($navbar_active && $navbar_active['logo']): ?>
                        <div class="text-center mb-3">
                            <img src="../../<?php echo htmlspecialchars($navbar_active['logo']); ?>" alt="Logo Navbar Active" class="img-thumbnail" style="max-height: 150px;">
                            <p class="text-muted mt-2 mb-0"><small>Logo navbar saat ini <?php echo $my_navbar ? '(aktif)' : ''; ?></small></p>
                        </div>
                        <?php endif; ?>
                        
                        <!-- FORM -->
                        <form method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="form_type" value="navbar">
                            
                            <div class="mb-3">
                                <label class="form-label">Upload Logo Navbar <?php echo $my_navbar ? 'Baru' : ''; ?></label>
                                <input type="file" class="form-control" name="logo_navbar" accept="image/*" required>
                                <small class="text-muted">Format: JPG, PNG, SVG, WEBP (Max 2MB)</small>
                            </div>
                            
                            <button type="submit" class="btn btn-<?php echo $my_navbar ? 'warning' : 'primary'; ?> w-100">
                                <i class="bi bi-<?php echo $my_navbar ? 'arrow-repeat' : 'upload'; ?>"></i> 
                                <?php echo $my_navbar ? 'Update & Ajukan Ulang' : 'Upload Logo Navbar'; ?>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- FOOTER LOGO -->
            <div class="col-md-6">
                <div class="card shadow mb-4 <?php echo $my_footer ? 'border-warning' : ''; ?>">
                    <div class="card-header <?php echo $my_footer ? 'bg-warning' : 'bg-success text-white'; ?>">
                        <h5 class="mb-0">
                            <i class="bi bi-image"></i> Logo Footer
                            <?php if ($my_footer): ?>
                                <span class="badge bg-<?php echo $my_footer['status'] == 'pending' ? 'secondary' : 'danger'; ?> float-end">
                                    <?php echo ucfirst($my_footer['status']); ?>
                                </span>
                            <?php endif; ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        
                        <!-- MY PENDING/REJECTED -->
                        <?php if ($my_footer): ?>
                            <div class="alert alert-<?php echo $my_footer['status'] == 'pending' ? 'warning' : 'danger'; ?>">
                                <i class="bi bi-<?php echo $my_footer['status'] == 'pending' ? 'clock-history' : 'x-circle'; ?>"></i>
                                <?php if ($my_footer['status'] == 'rejected'): ?>
                                    <strong>Ditolak Admin!</strong> Silakan upload ulang logo footer.
                                <?php else: ?>
                                    <strong>Menunggu Approval</strong> Logo Anda sedang direview admin.
                                <?php endif; ?>
                            </div>
                            
                            <div class="text-center mb-3">
                                <img src="../../<?php echo htmlspecialchars($my_footer['logo']); ?>" alt="Logo Footer Pending" class="img-thumbnail" style="max-height: 150px;">
                                <p class="text-muted mt-2 mb-0"><small>Logo yang diajukan</small></p>
                            </div>
                        <?php endif; ?>
                        
                        <!-- ACTIVE LOGO REFERENCE -->
                        <?php if ($footer_active && $footer_active['logo']): ?>
                        <div class="text-center mb-3">
                            <img src="../../<?php echo htmlspecialchars($footer_active['logo']); ?>" alt="Logo Footer Active" class="img-thumbnail" style="max-height: 150px;">
                            <p class="text-muted mt-2 mb-0"><small>Logo footer saat ini <?php echo $my_footer ? '(aktif)' : ''; ?></small></p>
                        </div>
                        <?php endif; ?>
                        
                        <!-- FORM -->
                        <form method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="form_type" value="footer">
                            
                            <div class="mb-3">
                                <label class="form-label">Upload Logo Footer <?php echo $my_footer ? 'Baru' : ''; ?></label>
                                <input type="file" class="form-control" name="logo_footer" accept="image/*" required>
                                <small class="text-muted">Format: JPG, PNG, SVG, WEBP (Max 2MB)</small>
                            </div>
                            
                            <button type="submit" class="btn btn-<?php echo $my_footer ? 'warning' : 'success'; ?> w-100">
                                <i class="bi bi-<?php echo $my_footer ? 'arrow-repeat' : 'upload'; ?>"></i> 
                                <?php echo $my_footer ? 'Update & Ajukan Ulang' : 'Upload Logo Footer'; ?>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
    
    <!-- ==================== TAB KONTAK ==================== -->
    <div class="tab-pane fade" id="tab-kontak">
        
        <!-- MY PENDING/REJECTED KONTAK -->
        <?php if ($my_kontak): ?>
        <div class="card shadow mb-4 border-<?php echo $my_kontak['status'] == 'pending' ? 'warning' : 'danger'; ?>">
            <div class="card-header bg-<?php echo $my_kontak['status'] == 'pending' ? 'warning' : 'danger'; ?> text-<?php echo $my_kontak['status'] == 'pending' ? 'dark' : 'white'; ?>">
                <h5 class="mb-0">
                    <i class="bi bi-<?php echo $my_kontak['status'] == 'pending' ? 'clock-history' : 'x-circle'; ?>"></i> 
                    Pengajuan Kontak Saya
                    <span class="badge bg-<?php echo $my_kontak['status'] == 'pending' ? 'secondary' : 'dark'; ?> float-end">
                        <?php echo ucfirst($my_kontak['status']); ?>
                    </span>
                </h5>
            </div>
            <div class="card-body">
                <?php if ($my_kontak['status'] == 'rejected'): ?>
                    <div class="alert alert-danger">
                        <i class="bi bi-x-circle"></i> <strong>Ditolak Admin!</strong> Silakan perbaiki data dan ajukan kembali.
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning">
                        <i class="bi bi-clock-history"></i> <strong>Menunggu Approval</strong> Data Anda sedang direview oleh admin.
                    </div>
                <?php endif; ?>
                
                <form method="POST">
                    <input type="hidden" name="form_type" value="kontak">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">
                                <i class="bi bi-whatsapp text-success"></i> WhatsApp
                            </label>
                            <input type="text" class="form-control" name="whatsapp" 
                                   value="<?php echo htmlspecialchars($my_kontak['whatsapp'] ?? ''); ?>"
                                   placeholder="+62812345678" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">
                                <i class="bi bi-envelope text-danger"></i> Email
                            </label>
                            <input type="email" class="form-control" name="email" 
                                   value="<?php echo htmlspecialchars($my_kontak['email'] ?? ''); ?>"
                                   placeholder="lab@university.ac.id" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">
                            <i class="bi bi-geo-alt text-primary"></i> Alamat Lengkap
                        </label>
                        <textarea class="form-control" name="alamat" rows="3" required><?php echo htmlspecialchars($my_kontak['alamat'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">
                            <i class="bi bi-clock text-info"></i> Jam Operasional
                        </label>
                        <input type="text" class="form-control" name="jam_operasional" 
                               value="<?php echo htmlspecialchars($my_kontak['jam_operasional'] ?? ''); ?>"
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
                                   value="<?php echo htmlspecialchars($my_kontak['linkedin'] ?? ''); ?>"
                                   placeholder="https://linkedin.com/company/...">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">
                                <i class="bi bi-instagram text-danger"></i> Instagram
                            </label>
                            <input type="url" class="form-control" name="instagram" 
                                   value="<?php echo htmlspecialchars($my_kontak['instagram'] ?? ''); ?>"
                                   placeholder="https://instagram.com/...">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">
                                <i class="bi bi-youtube text-danger"></i> YouTube
                            </label>
                            <input type="url" class="form-control" name="youtube" 
                                   value="<?php echo htmlspecialchars($my_kontak['youtube'] ?? ''); ?>"
                                   placeholder="https://youtube.com/@...">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">
                                <i class="bi bi-facebook text-primary"></i> Facebook
                            </label>
                            <input type="url" class="form-control" name="facebook" 
                                   value="<?php echo htmlspecialchars($my_kontak['facebook'] ?? ''); ?>"
                                   placeholder="https://facebook.com/...">
                        </div>
                    </div>
                    
                    <hr class="my-4">
                    
                    <div class="mb-3">
                        <label class="form-label">
                            <i class="bi bi-map"></i> Google Maps Embed Code
                        </label>
                        <textarea class="form-control" name="maps" rows="3" placeholder="<iframe src='https://www.google.com/maps/embed?...' ...></iframe>"><?php echo htmlspecialchars($my_kontak['maps'] ?? ''); ?></textarea>
                        <small class="text-muted">Copy embed code dari Google Maps</small>
                    </div>
                    
                    <button type="submit" class="btn btn-<?php echo $my_kontak['status'] == 'rejected' ? 'danger' : 'warning'; ?>">
                        <i class="bi bi-arrow-repeat"></i> Update & Ajukan Ulang
                    </button>
                </form>
            </div>
        </div>
        <?php else: ?>
        
        <!-- NO PENDING - SHOW ACTIVE DATA -->
        <div class="row">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="bi bi-telephone"></i> Form Kontak</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($kontak_active): ?>
                            <form method="POST">
                                <input type="hidden" name="form_type" value="kontak">
                                
                                <div class="alert alert-warning">
                                    <i class="bi bi-exclamation-triangle"></i> <strong>Perhatian:</strong> Perubahan akan berstatus <strong>Pending</strong> dan perlu di-approve admin.
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">
                                            <i class="bi bi-whatsapp text-success"></i> WhatsApp
                                        </label>
                                        <input type="text" class="form-control" name="whatsapp" 
                                               value="<?php echo htmlspecialchars($kontak_active['whatsapp'] ?? ''); ?>"
                                               placeholder="+62812345678" required>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">
                                            <i class="bi bi-envelope text-danger"></i> Email
                                        </label>
                                        <input type="email" class="form-control" name="email" 
                                               value="<?php echo htmlspecialchars($kontak_active['email'] ?? ''); ?>"
                                               placeholder="lab@university.ac.id" required>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">
                                        <i class="bi bi-geo-alt text-primary"></i> Alamat Lengkap
                                    </label>
                                    <textarea class="form-control" name="alamat" rows="3" required><?php echo htmlspecialchars($kontak_active['alamat'] ?? ''); ?></textarea>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">
                                        <i class="bi bi-clock text-info"></i> Jam Operasional
                                    </label>
                                    <input type="text" class="form-control" name="jam_operasional" 
                                           value="<?php echo htmlspecialchars($kontak_active['jam_operasional'] ?? ''); ?>"
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
                                               value="<?php echo htmlspecialchars($kontak_active['linkedin'] ?? ''); ?>"
                                               placeholder="https://linkedin.com/company/...">
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">
                                            <i class="bi bi-instagram text-danger"></i> Instagram
                                        </label>
                                        <input type="url" class="form-control" name="instagram" 
                                               value="<?php echo htmlspecialchars($kontak_active['instagram'] ?? ''); ?>"
                                               placeholder="https://instagram.com/...">
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">
                                            <i class="bi bi-youtube text-danger"></i> YouTube
                                        </label>
                                        <input type="url" class="form-control" name="youtube" 
                                               value="<?php echo htmlspecialchars($kontak_active['youtube'] ?? ''); ?>"
                                               placeholder="https://youtube.com/@...">
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">
                                            <i class="bi bi-facebook text-primary"></i> Facebook
                                        </label>
                                        <input type="url" class="form-control" name="facebook" 
                                               value="<?php echo htmlspecialchars($kontak_active['facebook'] ?? ''); ?>"
                                               placeholder="https://facebook.com/...">
                                    </div>
                                </div>
                                
                                <hr class="my-4">
                                
                                <div class="mb-3">
                                    <label class="form-label">
                                        <i class="bi bi-map"></i> Google Maps Embed Code
                                    </label>
                                    <textarea class="form-control" name="maps" rows="3" placeholder="<iframe src='https://www.google.com/maps/embed?...' ...></iframe>"><?php echo htmlspecialchars($kontak_active['maps'] ?? ''); ?></textarea>
                                    <small class="text-muted">Copy embed code dari Google Maps</small>
                                </div>
                                
                                <button type="submit" class="btn btn-success">
                                    <i class="bi bi-pencil-square"></i> Edit & Ajukan Perubahan
                                </button>
                            </form>
                        <?php else: ?>
                            <p class="text-muted">Belum ada data kontak active</p>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#kontakModal">
                                <i class="bi bi-plus-circle"></i> Tambah Kontak Baru
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card shadow mb-3">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0">Preview Kontak Aktif</h6>
                    </div>
                    <div class="card-body">
                        <?php if ($kontak_active): ?>
                            <p class="mb-2">
                                <i class="bi bi-whatsapp text-success"></i> 
                                <strong>WhatsApp:</strong><br>
                                <small><?php echo htmlspecialchars($kontak_active['whatsapp'] ?? '-'); ?></small>
                            </p>
                            
                            <p class="mb-2">
                                <i class="bi bi-envelope text-danger"></i> 
                                <strong>Email:</strong><br>
                                <small><?php echo htmlspecialchars($kontak_active['email'] ?? '-'); ?></small>
                            </p>
                            
                            <p class="mb-2">
                                <i class="bi bi-geo-alt text-primary"></i> 
                                <strong>Alamat:</strong><br>
                                <small><?php echo nl2br(htmlspecialchars($kontak_active['alamat'] ?? '-')); ?></small>
                            </p>
                            
                            <p class="mb-2">
                                <i class="bi bi-clock text-info"></i> 
                                <strong>Jam:</strong><br>
                                <small><?php echo htmlspecialchars($kontak_active['jam_operasional'] ?? '-'); ?></small>
                            </p>
                            
                            <hr>
                            
                            <div class="d-flex gap-2 flex-wrap">
                                <?php if ($kontak_active['linkedin']): ?>
                                    <a href="<?php echo htmlspecialchars($kontak_active['linkedin']); ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-linkedin"></i>
                                    </a>
                                <?php endif; ?>
                                
                                <?php if ($kontak_active['instagram']): ?>
                                    <a href="<?php echo htmlspecialchars($kontak_active['instagram']); ?>" target="_blank" class="btn btn-sm btn-outline-danger">
                                        <i class="bi bi-instagram"></i>
                                    </a>
                                <?php endif; ?>
                                
                                <?php if ($kontak_active['youtube']): ?>
                                    <a href="<?php echo htmlspecialchars($kontak_active['youtube']); ?>" target="_blank" class="btn btn-sm btn-outline-danger">
                                        <i class="bi bi-youtube"></i>
                                    </a>
                                <?php endif; ?>
                                
                                <?php if ($kontak_active['facebook']): ?>
                                    <a href="<?php echo htmlspecialchars($kontak_active['facebook']); ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-facebook"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-muted text-center">Belum ada data kontak</p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <?php if ($kontak_active && $kontak_active['maps']): ?>
                <div class="card shadow">
                    <div class="card-header">
                        <h6 class="mb-0">Preview Maps</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="ratio ratio-1x1">
                            <?php echo $kontak_active['maps']; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
</div>

<?php include "footer.php"; ?>