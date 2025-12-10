<?php
/**
 * Admin Kontak - Global System
 * Admin bisa edit data active
 * Admin bisa approve/reject pending dari operator
 */
$required_role = "admin";
include "../auth.php";
include "../../conn.php";

$page_title = "Kontak";
$current_page = "kontak.php";

// Handle Approve/Reject
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = $_GET['id'];
    $action = $_GET['action'];
    
    try {
        $stmt_old = $pdo->prepare("SELECT * FROM kontak WHERE id_kontak = ?");
        $stmt_old->execute([$id]);
        $old_data = $stmt_old->fetch();
        
        if ($action == 'approve') {
            // Hapus kontak active yang lama
            $pdo->query("DELETE FROM kontak WHERE status = 'active'");
            // Set yang baru jadi active
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

// Handle form submission (Edit kontak active)
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
        // Get active kontak
        $active = $pdo->query("SELECT id_kontak FROM kontak WHERE status = 'active' LIMIT 1")->fetch();
        
        if ($active) {
            // UPDATE active kontak
            $stmt = $pdo->prepare("UPDATE kontak SET whatsapp=?, email=?, alamat=?, linkedin=?, jam_operasional=?, instagram=?, youtube=?, facebook=?, maps=?, updated_at=CURRENT_TIMESTAMP WHERE id_kontak=?");
            $stmt->execute([$whatsapp, $email, $alamat, $linkedin, $jam_operasional, $instagram, $youtube, $facebook, $maps, $active['id_kontak']]);
        } else {
            // INSERT baru langsung active
            $stmt = $pdo->prepare("INSERT INTO kontak (whatsapp, email, alamat, linkedin, jam_operasional, instagram, youtube, facebook, maps, status, id_user) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'active', ?)");
            $stmt->execute([$whatsapp, $email, $alamat, $linkedin, $jam_operasional, $instagram, $youtube, $facebook, $maps, $_SESSION['id_user']]);
        }
        
        $success = "Informasi kontak berhasil disimpan!";
    } catch (PDOException $e) {
        $error = "Gagal menyimpan: " . $e->getMessage();
    }
}

// Get active kontak
$kontak = $pdo->query("SELECT * FROM kontak WHERE status = 'active' LIMIT 1")->fetch();

// Get pending submissions dengan info operator
$pending_list = $pdo->query("
    SELECT k.*, u.nama as operator_nama 
    FROM kontak k
    LEFT JOIN users u ON k.id_user = u.id_user
    WHERE k.status = 'pending'
    ORDER BY k.updated_at DESC
")->fetchAll();

$total_pending = count($pending_list);

include "header.php";
include "sidebar.php";
include "navbar.php";
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Informasi Kontak</h1>
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

<!-- Pending Submissions -->
<?php if ($total_pending > 0): ?>
<div class="card shadow mb-4 border-warning">
    <div class="card-header bg-warning">
        <h5 class="mb-0"><i class="bi bi-clock-history"></i> Pengajuan Pending dari Operator</h5>
    </div>
    <div class="card-body">
        <?php foreach ($pending_list as $pending): ?>
        <div class="card mb-3 border-warning">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <strong>Dari: <?php echo htmlspecialchars($pending['operator_nama'] ?? 'Unknown'); ?></strong>
                        <small class="text-muted d-block"><?php echo date('d M Y H:i', strtotime($pending['updated_at'])); ?></small>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="?action=approve&id=<?php echo $pending['id_kontak']; ?>" class="btn btn-sm btn-success" onclick="return confirm('Approve dan aktifkan kontak ini? Data kontak aktif saat ini akan diganti.')">
                            <i class="bi bi-check-circle"></i> Approve
                        </a>
                        <a href="?action=reject&id=<?php echo $pending['id_kontak']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Reject pengajuan ini?')">
                            <i class="bi bi-x-circle"></i> Reject
                        </a>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <p class="mb-2"><strong>WhatsApp:</strong> <?php echo htmlspecialchars($pending['whatsapp'] ?? '-'); ?></p>
                        <p class="mb-2"><strong>Email:</strong> <?php echo htmlspecialchars($pending['email'] ?? '-'); ?></p>
                        <p class="mb-2"><strong>Jam Operasional:</strong> <?php echo htmlspecialchars($pending['jam_operasional'] ?? '-'); ?></p>
                        <p class="mb-2"><strong>LinkedIn:</strong> <?php echo htmlspecialchars($pending['linkedin'] ?? '-'); ?></p>
                    </div>
                    <div class="col-md-6">
                        <p class="mb-2"><strong>Alamat:</strong> <?php echo nl2br(htmlspecialchars($pending['alamat'] ?? '-')); ?></p>
                        <p class="mb-2"><strong>Instagram:</strong> <?php echo htmlspecialchars($pending['instagram'] ?? '-'); ?></p>
                        <p class="mb-2"><strong>YouTube:</strong> <?php echo htmlspecialchars($pending['youtube'] ?? '-'); ?></p>
                        <p class="mb-2"><strong>Facebook:</strong> <?php echo htmlspecialchars($pending['facebook'] ?? '-'); ?></p>
                    </div>
                </div>
                
                <?php if ($pending['maps']): ?>
                <hr>
                <div class="mb-2">
                    <strong>Google Maps:</strong>
                    <div class="ratio ratio-16x9 mt-2" style="max-width: 400px;">
                        <?php echo $pending['maps']; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- Active Kontak Form -->
<div class="row">
    <div class="col-md-8">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-telephone"></i> Form Kontak</h5>
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
                            <i class="bi bi-map"></i> Google Maps Embed URL
                        </label>
                        <textarea class="form-control" name="maps" rows="3" placeholder="<iframe src='https://www.google.com/maps/embed?...' ...></iframe>"><?php echo htmlspecialchars($kontak['maps'] ?? ''); ?></textarea>
                        <small class="text-muted">Copy embed code dari Google Maps</small>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-floppy"></i> Simpan Perubahan
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