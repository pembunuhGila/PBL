<?php
/**
 * @var PDO $pdo
 * @var string $current_page
 * @var int $current_id_user
 */
$required_role = "admin";
include "../auth.php";
include "../../conn.php";

$page_title = "Kontak";
$current_page = "kontak.php";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ambil semua data dari form
    $whatsapp = $_POST['whatsapp'] ?? '';
    $email = $_POST['email'] ?? '';
    $alamat = $_POST['alamat'] ?? '';
    $linkedin = $_POST['linkedin'] ?? '';
    $jam_operasional = $_POST['jam_operasional'] ?? '';
    $instagram = $_POST['instagram'] ?? '';
    $youtube = $_POST['youtube'] ?? '';
    $facebook = $_POST['facebook'] ?? '';
    $maps = $_POST['maps'] ?? '';
    $status = 'active';
    
    try {
        // Check if kontak exists
        $check = $pdo->query("SELECT id_kontak, status FROM kontak LIMIT 1")->fetch();
        
        if ($check) {
            // UPDATE - Get status lama
            $status_lama = $check['status'];
            
            $stmt = $pdo->prepare("UPDATE kontak SET whatsapp=?, email=?, alamat=?, linkedin=?, jam_operasional=?, instagram=?, youtube=?, facebook=?, maps=?, status=?, updated_at=CURRENT_TIMESTAMP WHERE id_kontak=?");
            $stmt->execute([$whatsapp, $email, $alamat, $linkedin, $jam_operasional, $instagram, $youtube, $facebook, $maps, $status, $check['id_kontak']]);
            
            // Catat riwayat JIKA status berubah
            if ($status_lama != $status) {
                $stmt_riwayat = $pdo->prepare("INSERT INTO riwayat_pengajuan (tabel_sumber, id_data, id_admin, status_lama, status_baru, catatan) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt_riwayat->execute(['kontak', $check['id_kontak'], $_SESSION['id_user'], $status_lama, $status, 'Update info kontak']);
            }
        } else {
            // INSERT
            $stmt = $pdo->prepare("INSERT INTO kontak (whatsapp, email, alamat, linkedin, jam_operasional, instagram, youtube, facebook, maps, status, id_user) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$whatsapp, $email, $alamat, $linkedin, $jam_operasional, $instagram, $youtube, $facebook, $maps, $status, $_SESSION['id_user']]);
            
            $new_id = $pdo->lastInsertId();
            
            // Catat riwayat
            $stmt_riwayat = $pdo->prepare("INSERT INTO riwayat_pengajuan (tabel_sumber, id_data, id_admin, status_lama, status_baru, catatan) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt_riwayat->execute(['kontak', $new_id, $_SESSION['id_user'], null, $status, 'Tambah info kontak']);
        }
        
        $success = "Informasi kontak berhasil disimpan!";
    } catch (PDOException $e) {
        $error = "Gagal menyimpan: " . $e->getMessage();
    }
}

// Get existing data
$kontak = $pdo->query("SELECT * FROM kontak LIMIT 1")->fetch();

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

<div class="row">
    <div class="col-md-8">
        <div class="card shadow">
            <div class="card-header">
                <h5 class="mb-0">Form Kontak</h5>
            </div>
            <div class="card-body">
                <form method="POST">
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
                    
                    <div class="mb-3">
                        <label class="form-label">
                            <i class="bi bi-linkedin text-primary"></i> LinkedIn
                        </label>
                        <input type="url" class="form-control" name="linkedin" 
                               value="<?php echo htmlspecialchars($kontak['linkedin'] ?? ''); ?>"
                               placeholder="https://linkedin.com/company/...">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">
                            <i class="bi bi-instagram text-danger"></i> Instagram
                        </label>
                        <input type="url" class="form-control" name="instagram" 
                               value="<?php echo htmlspecialchars($kontak['instagram'] ?? ''); ?>"
                               placeholder="https://instagram.com/...">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">
                            <i class="bi bi-youtube text-danger"></i> YouTube
                        </label>
                        <input type="url" class="form-control" name="youtube" 
                               value="<?php echo htmlspecialchars($kontak['youtube'] ?? ''); ?>"
                               placeholder="https://youtube.com/@...">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">
                            <i class="bi bi-facebook text-primary"></i> Facebook
                        </label>
                        <input type="url" class="form-control" name="facebook" 
                               value="<?php echo htmlspecialchars($kontak['facebook'] ?? ''); ?>"
                               placeholder="https://facebook.com/...">
                    </div>
                    
                    <hr class="my-4">
                    
                    <div class="mb-3">
                        <label class="form-label">
                            <i class="bi bi-map"></i> Google Maps Embed URL
                        </label>
                        <textarea class="form-control" name="maps" rows="3" placeholder="<iframe src='https://www.google.com/maps/embed?...' ...></iframe>"><?php echo htmlspecialchars($kontak['maps'] ?? ''); ?></textarea>
                        <small class="text-muted">Copy embed code dari Google Maps</small>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Simpan Perubahan
                    </button>
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