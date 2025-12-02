<?php
/**
 * @var PDO $pdo
 * @var string $current_page
 * @var int $current_id_user
 */
$required_role = "operator";
include "../auth.php";
include "../../conn.php";

$page_title = "Riwayat Pengajuan";
$current_page = "riwayat_pengajuan.php";

// Filter
$filter_tabel = $_GET['tabel'] ?? '';
$filter_status = $_GET['status'] ?? '';
$filter_bulan = $_GET['bulan'] ?? '';

$where_clauses = ["r.id_operator = ?"];
$params = [$_SESSION['id_user']];

if ($filter_tabel) {
    $where_clauses[] = "r.tabel_sumber = ?";
    $params[] = $filter_tabel;
}

if ($filter_status) {
    $where_clauses[] = "r.status_baru = ?";
    $params[] = $filter_status;
}

if ($filter_bulan) {
    $where_clauses[] = "TO_CHAR(r.created_at, 'YYYY-MM') = ?";
    $params[] = $filter_bulan;
}

$where_sql = "WHERE " . implode(" AND ", $where_clauses);

// Get riwayat dengan join ke tabel users untuk nama operator dan admin
$query = "
    SELECT 
        r.*,
        u_adm.nama as admin_nama
    FROM riwayat_pengajuan r
    LEFT JOIN users u_adm ON r.id_admin = u_adm.id_user
    $where_sql
    ORDER BY r.created_at DESC
    LIMIT 200
";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$riwayat_list = $stmt->fetchAll();

// Daftar tabel
$all_tables = [
    'anggota_lab' => 'Anggota Lab',
    'struktur_lab' => 'Struktur Lab',
    'publikasi' => 'Publikasi',
    'fasilitas' => 'Fasilitas',
    'galeri' => 'Galeri',
    'konten' => 'Konten',
    'tentang_kami' => 'Profil Lab',
    'visi' => 'Visi',
    'misi' => 'Misi',
    'sejarah' => 'Sejarah',
    'kontak' => 'Kontak'
];

// Get available months for filter (PostgreSQL compatible)
$months_query = $pdo->prepare("
    SELECT DISTINCT TO_CHAR(created_at, 'YYYY-MM') as month 
    FROM riwayat_pengajuan 
    WHERE id_operator = ?
    ORDER BY month DESC
    LIMIT 12
");
$months_query->execute([$_SESSION['id_user']]);
$available_months = $months_query->fetchAll(PDO::FETCH_COLUMN);

include "header.php";
include "sidebar.php";
include "navbar.php";
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-clock-history"></i> Riwayat Pengajuan Saya</h1>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card shadow border-start border-warning border-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Pending</div>
                        <div class="h4 mb-0 font-weight-bold">
                            <?php 
                            $pending = $pdo->prepare("SELECT COUNT(*) FROM riwayat_pengajuan WHERE id_operator = ? AND status_baru = 'pending'");
                            $pending->execute([$_SESSION['id_user']]);
                            echo $pending->fetchColumn();
                            ?>
                        </div>
                    </div>
                    <div class="text-warning">
                        <i class="bi bi-hourglass-split fs-2"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card shadow border-start border-success border-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Approved</div>
                        <div class="h4 mb-0 font-weight-bold">
                            <?php 
                            $approved = $pdo->prepare("SELECT COUNT(*) FROM riwayat_pengajuan WHERE id_operator = ? AND status_baru = 'active'");
                            $approved->execute([$_SESSION['id_user']]);
                            echo $approved->fetchColumn();
                            ?>
                        </div>
                    </div>
                    <div class="text-success">
                        <i class="bi bi-check-circle fs-2"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card shadow border-start border-danger border-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Rejected</div>
                        <div class="h4 mb-0 font-weight-bold">
                            <?php 
                            $rejected = $pdo->prepare("SELECT COUNT(*) FROM riwayat_pengajuan WHERE id_operator = ? AND status_baru = 'rejected'");
                            $rejected->execute([$_SESSION['id_user']]);
                            echo $rejected->fetchColumn();
                            ?>
                        </div>
                    </div>
                    <div class="text-danger">
                        <i class="bi bi-x-circle fs-2"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card shadow border-start border-primary border-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total</div>
                        <div class="h4 mb-0 font-weight-bold">
                            <?php 
                            $total = $pdo->prepare("SELECT COUNT(*) FROM riwayat_pengajuan WHERE id_operator = ?");
                            $total->execute([$_SESSION['id_user']]);
                            echo $total->fetchColumn();
                            ?>
                        </div>
                    </div>
                    <div class="text-primary">
                        <i class="bi bi-list-check fs-2"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filter -->
<div class="card shadow mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <label class="form-label"><i class="bi bi-table"></i> Tabel</label>
                <select class="form-select" name="tabel">
                    <option value="">Semua Tabel</option>
                    <?php foreach ($all_tables as $table_key => $table_name): ?>
                        <option value="<?php echo $table_key; ?>" <?php echo $filter_tabel == $table_key ? 'selected' : ''; ?>>
                            <?php echo $table_name; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="col-md-3">
                <label class="form-label"><i class="bi bi-flag"></i> Status</label>
                <select class="form-select" name="status">
                    <option value="">Semua Status</option>
                    <option value="pending" <?php echo $filter_status == 'pending' ? 'selected' : ''; ?>>⏳ Pending</option>
                    <option value="active" <?php echo $filter_status == 'active' ? 'selected' : ''; ?>>✅ Approved</option>
                    <option value="rejected" <?php echo $filter_status == 'rejected' ? 'selected' : ''; ?>>❌ Rejected</option>
                    <option value="deleted" <?php echo $filter_status == 'deleted' ? 'selected' : ''; ?>>🗑️ Deleted</option>
                </select>
            </div>
            
            <div class="col-md-3">
                <label class="form-label"><i class="bi bi-calendar-month"></i> Bulan</label>
                <select class="form-select" name="bulan">
                    <option value="">Semua Bulan</option>
                    <?php foreach ($available_months as $month): ?>
                        <option value="<?php echo $month; ?>" <?php echo $filter_bulan == $month ? 'selected' : ''; ?>>
                            <?php 
                            $date = DateTime::createFromFormat('Y-m', $month);
                            echo $date->format('F Y'); 
                            ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="col-md-3 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary flex-grow-1">
                    <i class="bi bi-funnel"></i> Filter
                </button>
                <a href="riwayat_pengajuan.php" class="btn btn-secondary">
                    <i class="bi bi-arrow-clockwise"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Info Alert -->
<div class="alert alert-info">
    <i class="bi bi-info-circle"></i> 
    <strong>Informasi:</strong> Semua pengajuan Anda akan tercatat di sini. Status <span class="badge bg-warning">Pending</span> menunggu review admin, 
    <span class="badge bg-success">Approved</span> sudah disetujui dan tampil di website, 
    <span class="badge bg-danger">Rejected</span> ditolak oleh admin.
</div>

<!-- Riwayat Table -->
<div class="card shadow">
    <div class="card-header bg-white">
        <h5 class="mb-0">
            <i class="bi bi-file-earmark-text"></i> Daftar Riwayat 
            <span class="badge bg-primary"><?php echo count($riwayat_list); ?> records</span>
        </h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-sm mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Waktu</th>
                        <th>Tabel</th>
                        <th>ID Data</th>
                        <th>Perubahan Status</th>
                        <th>Admin Review</th>
                        <th>Catatan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($riwayat_list) > 0): ?>
                        <?php foreach ($riwayat_list as $riwayat): ?>
                        <tr>
                            <td class="ps-3">
                                <small class="d-block"><strong><?php echo date('d M Y', strtotime($riwayat['created_at'])); ?></strong></small>
                                <small class="text-muted"><?php echo date('H:i', strtotime($riwayat['created_at'])); ?> WIB</small>
                            </td>
                            <td>
                                <span class="badge bg-secondary">
                                    <?php echo $all_tables[$riwayat['tabel_sumber']] ?? ucfirst(str_replace('_', ' ', $riwayat['tabel_sumber'])); ?>
                                </span>
                            </td>
                            <td><code class="text-primary">#<?php echo $riwayat['id_data']; ?></code></td>
                            <td>
                                <?php
                                // Badge untuk status lama
                                $status_lama_badge = 'bg-secondary';
                                $status_lama_icon = '';
                                if ($riwayat['status_lama'] == 'active') {
                                    $status_lama_badge = 'bg-success';
                                    $status_lama_icon = '✅';
                                } elseif ($riwayat['status_lama'] == 'pending') {
                                    $status_lama_badge = 'bg-warning';
                                    $status_lama_icon = '⏳';
                                } elseif ($riwayat['status_lama'] == 'rejected') {
                                    $status_lama_badge = 'bg-danger';
                                    $status_lama_icon = '❌';
                                }
                                
                                // Badge untuk status baru
                                $status_baru_badge = 'bg-secondary';
                                $status_baru_icon = '';
                                if ($riwayat['status_baru'] == 'active') {
                                    $status_baru_badge = 'bg-success';
                                    $status_baru_icon = '✅';
                                } elseif ($riwayat['status_baru'] == 'pending') {
                                    $status_baru_badge = 'bg-warning';
                                    $status_baru_icon = '⏳';
                                } elseif ($riwayat['status_baru'] == 'rejected') {
                                    $status_baru_badge = 'bg-danger';
                                    $status_baru_icon = '❌';
                                } elseif ($riwayat['status_baru'] == 'deleted') {
                                    $status_baru_badge = 'bg-dark';
                                    $status_baru_icon = '🗑️';
                                }
                                ?>
                                <div class="d-flex align-items-center gap-2">
                                    <?php if ($riwayat['status_lama']): ?>
                                        <span class="badge <?php echo $status_lama_badge; ?>">
                                            <?php echo $status_lama_icon . ' ' . ucfirst($riwayat['status_lama']); ?>
                                        </span>
                                        <i class="bi bi-arrow-right"></i>
                                    <?php endif; ?>
                                    <span class="badge <?php echo $status_baru_badge; ?>">
                                        <?php echo $status_baru_icon . ' ' . ucfirst($riwayat['status_baru']); ?>
                                    </span>
                                </div>
                            </td>
                            <td>
                                <?php if ($riwayat['admin_nama']): ?>
                                    <small>
                                        <i class="bi bi-person-check text-success"></i> 
                                        <?php echo htmlspecialchars($riwayat['admin_nama']); ?>
                                    </small>
                                <?php else: ?>
                                    <small class="text-muted">
                                        <i class="bi bi-hourglass-split"></i> Menunggu review
                                    </small>
                                <?php endif; ?>
                            </td>
                            <td style="max-width: 250px;">
                                <?php if ($riwayat['catatan']): ?>
                                    <small class="d-block text-truncate" title="<?php echo htmlspecialchars($riwayat['catatan']); ?>">
                                        <?php echo htmlspecialchars($riwayat['catatan']); ?>
                                    </small>
                                <?php else: ?>
                                    <small class="text-muted">-</small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-outline-info" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#detailModal<?php echo $riwayat['id_riwayat']; ?>">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </td>
                        </tr>
                        
                        <!-- Modal Detail -->
                        <div class="modal fade" id="detailModal<?php echo $riwayat['id_riwayat']; ?>" tabindex="-1">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">
                                            <i class="bi bi-info-circle"></i> Detail Riwayat #<?php echo $riwayat['id_riwayat']; ?>
                                        </h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <table class="table table-bordered">
                                            <tr>
                                                <th width="200">Waktu Pengajuan</th>
                                                <td><?php echo date('d F Y, H:i:s', strtotime($riwayat['created_at'])); ?> WIB</td>
                                            </tr>
                                            <tr>
                                                <th>Tabel Sumber</th>
                                                <td>
                                                    <span class="badge bg-secondary">
                                                        <?php echo $all_tables[$riwayat['tabel_sumber']] ?? $riwayat['tabel_sumber']; ?>
                                                    </span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>ID Data</th>
                                                <td><code>#<?php echo $riwayat['id_data']; ?></code></td>
                                            </tr>
                                            <tr>
                                                <th>Status Lama</th>
                                                <td>
                                                    <?php if ($riwayat['status_lama']): ?>
                                                        <span class="badge <?php echo $status_lama_badge; ?>">
                                                            <?php echo ucfirst($riwayat['status_lama']); ?>
                                                        </span>
                                                    <?php else: ?>
                                                        <em class="text-muted">Baru (belum ada status sebelumnya)</em>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Status Baru</th>
                                                <td>
                                                    <span class="badge <?php echo $status_baru_badge; ?>">
                                                        <?php echo ucfirst($riwayat['status_baru']); ?>
                                                    </span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Admin yang Review</th>
                                                <td>
                                                    <?php if ($riwayat['admin_nama']): ?>
                                                        <i class="bi bi-person-check text-success"></i> 
                                                        <?php echo htmlspecialchars($riwayat['admin_nama']); ?>
                                                    <?php else: ?>
                                                        <em class="text-muted">Belum direview</em>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Catatan</th>
                                                <td>
                                                    <?php if ($riwayat['catatan']): ?>
                                                        <div class="alert alert-info mb-0">
                                                            <?php echo nl2br(htmlspecialchars($riwayat['catatan'])); ?>
                                                        </div>
                                                    <?php else: ?>
                                                        <em class="text-muted">Tidak ada catatan</em>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="bi bi-inbox" style="font-size: 4rem; opacity: 0.3;"></i>
                                    <p class="mt-3 mb-0">
                                        <?php if ($filter_tabel || $filter_status || $filter_bulan): ?>
                                            <strong>Tidak ada riwayat yang sesuai dengan filter</strong><br>
                                            <small>Coba ubah filter atau <a href="riwayat_pengajuan.php">reset filter</a></small>
                                        <?php else: ?>
                                            <strong>Belum ada riwayat pengajuan</strong><br>
                                            <small>Mulai tambahkan data di menu-menu yang tersedia</small>
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
.border-start {
    border-left-width: 4px !important;
}

.table td, .table th {
    vertical-align: middle;
}

.badge {
    font-weight: 500;
}

.card-hover:hover {
    transform: translateY(-5px);
    transition: transform 0.3s;
}
</style>

<?php include "footer.php"; ?>