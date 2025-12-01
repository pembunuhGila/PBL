<?php
$required_role = "operator";
include "../../auth.php";
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
    $status = 'pending'; // AUTO PENDING untuk operator
    
    try {
        // Check if kontak exists yang dibuat oleh operator ini
        $check = $pdo->prepare("SELECT id_kontak, status FROM kontak WHERE id_user = ? LIMIT 1");
        $check->execute([$_SESSION['id_user']]);
        $existing = $check->fetch();
        
        if ($existing && $existing['status'] == 'pending') {
            // UPDATE - hanya jika masih pending
            $stmt = $pdo->prepare("UPDATE kontak SET whatsapp=?, email=?, alamat=?, linkedin=?, jam_operasional=?, instagram=?, youtube=?, facebook=?, maps=?, status=?, updated_at=CURRENT_TIMESTAMP WHERE id_kontak=?");
            $stmt->execute([$whatsapp, $email, $alamat, $linkedin, $jam_operasional, $instagram, $youtube, $facebook, $maps, $status, $existing['id_kontak']]);
            
            $success = "Informasi kontak berhasil diupdate! Menunggu persetujuan admin.";
        } else {
            // INSERT
            $stmt = $pdo->prepare("INSERT INTO kontak (whatsapp, email, alamat, linkedin, jam_operasional, instagram, youtube, facebook, maps, status, id_user) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$whatsapp, $email, $alamat, $linkedin, $jam_operasional, $instagram, $youtube, $facebook, $maps, $status, $_SESSION['id_user']]);
            
            $new_id = $pdo->lastInsertId();
            
            $stmt_riwayat = $pdo->prepare("INSERT INTO riwayat_pengajuan (tabel_sumber, id_data, id_operator, status_lama, status_baru, catatan) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt_riwayat->execute(['kontak', $new_id, $_SESSION['id_user'], null, $status, 'Tambah info kontak']);
            
            $success = "Informasi kontak berhasil ditambahkan! Menunggu persetujuan admin.";
        }
    } catch (PDOException $e) {
        $error = "Gagal menyimpan: " . $e->getMessage();
    }
}

// Get existing data - hanya milik operator ini
$kontak = $pdo->prepare("SELECT * FROM kontak WHERE id_user = ? LIMIT 1");
$kontak->execute([$_SESSION['id_user']]);
$kontak = $kontak->fetch();

include "header.php";
include "sidebar.php";
include "navbar.php";
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Informasi Kontak</h1>
</div>

<?php if (isset($success)): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <?php echo $success; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (isset($error)): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <?php echo $error; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="alert alert-info">
    <i class="bi bi-info-circle"></i> Data kontak yang Anda tambahkan akan berstatus <span class="badge bg-warning">Pending</span> dan menunggu persetujuan admin.
</div>

<?php if ($kontak && $kontak['status'] == 'active'): ?>
    <div class="alert alert-success">
        <i class="bi bi-check-circle"></i> Informasi kontak Anda sudah disetujui admin. Anda tidak bisa mengubahnya lagi.
    </div>
<?php endif; ?>

<div class="row">
    <div class="col-md-8">
        <div class="card shadow">
            <div class="card-header">
                <h5 class="mb-0">
                    Form Kontak
                    <?php if ($kontak): ?>
                        <?php if ($kontak['status'] == 'pending'): ?>
                            <span class="badge bg-warning">Pending</span>
                        <?php elseif ($kontak['status'] == 'active'): ?>
                            <span class="badge bg-success">Approved</span>
                        <?php else: ?>
                            <span class="badge bg-danger">Rejected</span>
                        <?php endif; ?>
                    <?php endif; ?>
                </h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <?php if ($kontak && $kontak['status'] == 'active'): ?>
                        <div class="alert alert-warning">
                            <i class="bi bi-lock"></i> Form tidak bisa diubah karena sudah disetujui admin.
                        </div>
                    <?php endif; ?>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">
                                <i class="bi bi-whatsapp text-success"></i> WhatsApp
                            </label>
                            <input type="text" class="form-control" name="whatsapp" 
                                   value="<?php echo htmlspecialchars($kontak['whatsapp'] ?? ''); ?>"
                                   placeholder="+62812345678"
                                   <?php echo ($kontak && $kontak['status'] == 'active') ? 'readonly' : ''; ?>>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">
                                <i class="bi bi-envelope text-danger"></i> Email
                            </label>
                            <input type="email" class="form-control" name="email" 
                                   value="<?php echo htmlspecialchars($kontak['email'] ?? ''); ?>"
                                   placeholder="lab@university.ac.id"
                                   <?php echo ($kontak && $kontak['status'] == 'active') ? 'readonly' : ''; ?>>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">
                            <i class="bi bi-geo-alt text-primary"></i> Alamat Lengkap
                        </label>
                        <textarea class="form-control" name="alamat" rows="3" 
                                  <?php echo ($kontak && $kontak['status'] == 'active') ? 'readonly' : ''; ?>><?php echo htmlspecialchars($kontak['alamat'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">
                            <i class="bi bi-clock text-info"></i> Jam Operasional
                        </label>
                        <input type="text" class="form-control" name="jam_operasional" 
                               value="<?php echo htmlspecialchars($kontak['jam_operasional'] ?? ''); ?>"
                               placeholder="Senin - Jumat, 08:00 - 16:00"
                               <?php echo ($kontak && $kontak['status'] == 'active') ? 'readonly' : ''; ?>>
                    </div>
                    
                    <hr class="my-4">
                    <h6 class="mb-3">Social Media</h6>
                    
                    <div class="mb-3">
                        <label class="form-label">
                            <i class="bi bi-linkedin text-primary"></i> LinkedIn
                        </label>
                        <input type="url" class="form-control" name="linkedin" 
                               value="<?php echo htmlspecialchars($kontak['linkedin'] ?? ''); ?>"
                               placeholder="https://linkedin.com/company/..."
                               <?php echo ($kontak && $kontak['status'] == 'active') ? 'readonly' : ''; ?>>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">
                            <i class="bi bi-instagram text-danger"></i> Instagram
                        </label>
                        <input type="url" class="form-control" name="instagram" 
                               value="<?php echo htmlspecialchars($kontak['instagram'] ?? ''); ?>"
                               placeholder="https://instagram.com/..."
                               <?php echo ($kontak && $kontak['status'] == 'active') ? 'readonly' : ''; ?>>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">
                            <i class="bi bi-youtube text-danger"></i> YouTube
                        </label>
                        <input type="url" class="form-control" name="youtube" 
                               value="<?php echo htmlspecialchars($kontak['youtube'] ?? ''); ?>"
                               placeholder="https://youtube.com/@..."
                               <?php echo ($kontak && $kontak['status'] == 'active') ? 'readonly' : ''; ?>>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">
                            <i class="bi bi-facebook text-primary"></i> Facebook
                        </label>
                        <input type="url" class="form-control" name="facebook" 
                               value="<?php echo htmlspecialchars($kontak['facebook'] ?? ''); ?>"
                               placeholder="https://facebook.com/..."
                               <?php echo ($kontak && $kontak['status'] == 'active') ? 'readonly' : ''; ?>>
                    </div>
                    
                    <hr class="my-4">
                    
                    <div class="mb-3">
                        <label class="form-label">
                            <i class="bi bi-map"></i> Google Maps Embed URL
                        </label>
                        <textarea class="form-control" name="maps" rows="3" 
                                  placeholder="<iframe src='https://www.google.com/maps/embed?...' ...></iframe>"
                                  <?php echo ($kontak && $kontak['status'] == 'active') ? 'readonly' : ''; ?>><?php echo htmlspecialchars($kontak['maps'] ?? ''); ?></textarea>
                        <small class="text-muted">Copy embed code dari Google Maps</small>
                    </div>
                    
                    <?php if (!$kontak || $kontak['status'] == 'pending' || $kontak['status'] == 'rejected'): ?>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Simpan & Ajukan
                        </button>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card shadow mb-3">
            <div class="card-header bg-primary text-white">
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
                        <small><?php echo htmlspecialchars($kontak['alamat'] ?? '-'); ?></small>
                    </p>
                    
                    <p class="mb-2">
                        <i class="bi bi-clock text-info"></i> 
                        <strong>Jam:</strong><br>
                        <small><?php echo htmlspecialchars($kontak['jam_operasional'] ?? '-'); ?></small>
                    </p>
                    
                    <hr>
                    
                    <div class="d-flex gap-2">
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
        <div class="card shadow">
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

<?php include "footer.php"; ?>