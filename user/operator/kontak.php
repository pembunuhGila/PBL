<?php
/**
 * Operator Kontak - Global System
 * Operator bisa lihat data active
 * Operator bisa edit active (akan jadi pending baru)
 * Operator bisa submit/update pending miliknya
 */
$required_role = "operator";
include "../auth.php";
include "../../conn.php";

$page_title = "Kontak";
$current_page = "kontak.php";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
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
        // Check if sudah ada pending/rejected milik operator ini
        $check = $pdo->prepare("SELECT id_kontak, status FROM kontak WHERE id_user = ? AND status IN ('pending', 'rejected') LIMIT 1");
        $check->execute([$_SESSION['id_user']]);
        $existing = $check->fetch();
        
        if ($existing) {
            // UPDATE jika ada pending/rejected
            $stmt = $pdo->prepare("UPDATE kontak SET whatsapp=?, email=?, alamat=?, linkedin=?, jam_operasional=?, instagram=?, youtube=?, facebook=?, maps=?, status='pending', updated_at=CURRENT_TIMESTAMP WHERE id_kontak=?");
            $stmt->execute([$whatsapp, $email, $alamat, $linkedin, $jam_operasional, $instagram, $youtube, $facebook, $maps, $existing['id_kontak']]);
            $success = "Informasi kontak berhasil diupdate! Menunggu persetujuan admin.";
        } else {
            // INSERT baru dengan status pending
            $stmt = $pdo->prepare("INSERT INTO kontak (whatsapp, email, alamat, linkedin, jam_operasional, instagram, youtube, facebook, maps, status, id_user) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?)");
            $stmt->execute([$whatsapp, $email, $alamat, $linkedin, $jam_operasional, $instagram, $youtube, $facebook, $maps, $_SESSION['id_user']]);
            $success = "Informasi kontak berhasil ditambahkan! Menunggu persetujuan admin.";
        }
    } catch (PDOException $e) {
        $error = "Gagal menyimpan: " . $e->getMessage();
    }
}

// Get active kontak (untuk referensi dan edit)
$kontak_active = $pdo->query("SELECT * FROM kontak WHERE status = 'active' LIMIT 1")->fetch();

// Get my pending/rejected data
$my_kontak = $pdo->prepare("SELECT * FROM kontak WHERE id_user = ? AND status IN ('pending', 'rejected') LIMIT 1");
$my_kontak->execute([$_SESSION['id_user']]);
$my_kontak = $my_kontak->fetch();

include "header.php";
include "sidebar.php";
include "navbar.php";
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Informasi Kontak</h1>
    <?php if ($my_kontak): ?>
        <span class="badge bg-<?php echo $my_kontak['status'] == 'pending' ? 'warning' : 'danger'; ?> fs-6">
            <i class="bi bi-<?php echo $my_kontak['status'] == 'pending' ? 'clock-history' : 'x-circle'; ?>"></i> 
            <?php echo ucfirst($my_kontak['status']); ?>
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
    <i class="bi bi-info-circle"></i> <strong>Info:</strong> Setiap perubahan data kontak akan berstatus <span class="badge bg-warning text-dark">Pending</span> dan perlu approval admin.
</div>

<!-- MY PENDING/REJECTED SECTION -->
<?php if ($my_kontak && in_array($my_kontak['status'], ['pending', 'rejected'])): ?>
<div class="card shadow mb-4 border-<?php echo $my_kontak['status'] == 'pending' ? 'warning' : 'danger'; ?>">
    <div class="card-header bg-<?php echo $my_kontak['status'] == 'pending' ? 'warning' : 'danger'; ?> text-<?php echo $my_kontak['status'] == 'pending' ? 'dark' : 'white'; ?>">
        <h5 class="mb-0">
            <i class="bi bi-<?php echo $my_kontak['status'] == 'pending' ? 'clock-history' : 'x-circle'; ?>"></i> 
            Pengajuan Kontak Saya 
            <span class="badge bg-<?php echo $my_kontak['status'] == 'pending' ? 'secondary' : 'dark'; ?>">
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
                    <i class="bi bi-map"></i> Google Maps Embed URL
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
<?php endif; ?>

<!-- DATA ACTIVE (EDITABLE) -->
<?php if (!$my_kontak): ?>
<div class="row">
    <div class="col-md-8">
        <div class="card shadow">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">
                    <i class="bi bi-check-circle"></i> Form Kontak
                </h5>
            </div>
            <div class="card-body">
                <?php if ($kontak_active): ?>
                        <!-- EDITABLE FORM jika belum ada pending -->
                        <form method="POST">
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
                                    <i class="bi bi-map"></i> Google Maps Embed URL
                                </label>
                                <textarea class="form-control" name="maps" rows="3" placeholder="<iframe src='https://www.google.com/maps/embed?...' ...></iframe>"><?php echo htmlspecialchars($kontak_active['maps'] ?? ''); ?></textarea>
                                <small class="text-muted">Copy embed code dari Google Maps</small>
                            </div>
                            
                            <div class="alert alert-warning">
                                <i class="bi bi-exclamation-triangle"></i> <strong>Perhatian:</strong> Perubahan akan berstatus <strong>Pending</strong> dan perlu di-approve admin.
                            </div>
                            
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-pencil-square"></i> Edit & Ajukan Perubahan
                            </button>
                        </form>
                <?php else: ?>
                    <!-- NO ACTIVE DATA -->
                    <p class="text-muted">Belum ada data kontak active</p>
                    <?php if (!$my_kontak): ?>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#kontakModal">
                            <i class="bi bi-plus-circle"></i> Tambah Kontak Baru
                        </button>
                    <?php endif; ?>
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

<!-- Modal Kontak (untuk kasus belum ada data sama sekali) -->
<?php if (!$my_kontak && !$kontak_active): ?>
<div class="modal fade" id="kontakModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Kontak Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="bi bi-info-circle"></i> Data akan berstatus <strong>Pending</strong> dan perlu approval admin
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">WhatsApp</label>
                            <input type="text" class="form-control" name="whatsapp" placeholder="+62812345678" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" placeholder="lab@university.ac.id" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Alamat Lengkap</label>
                        <textarea class="form-control" name="alamat" rows="3" required></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Jam Operasional</label>
                        <input type="text" class="form-control" name="jam_operasional" placeholder="Senin - Jumat, 08:00 - 16:00">
                    </div>
                    
                    <hr class="my-4">
                    <h6 class="mb-3">Social Media</h6>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">LinkedIn</label>
                            <input type="url" class="form-control" name="linkedin" placeholder="https://linkedin.com/company/...">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Instagram</label>
                            <input type="url" class="form-control" name="instagram" placeholder="https://instagram.com/...">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">YouTube</label>
                            <input type="url" class="form-control" name="youtube" placeholder="https://youtube.com/@...">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Facebook</label>
                            <input type="url" class="form-control" name="facebook" placeholder="https://facebook.com/...">
                        </div>
                    </div>
                    
                    <hr class="my-4">
                    
                    <div class="mb-3">
                        <label class="form-label">Google Maps Embed URL</label>
                        <textarea class="form-control" name="maps" rows="3" placeholder="<iframe src='https://www.google.com/maps/embed?...' ...></iframe>"></textarea>
                        <small class="text-muted">Copy embed code dari Google Maps</small>
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
<?php endif; ?>

<?php include "footer.php"; ?>