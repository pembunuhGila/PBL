<?php
/**
 * @var PDO $pdo
 * @var string $current_page
 * @var int $current_id_user
 */
$required_role = "admin";
include "../auth.php";
include "../../conn.php";

$page_title = "Riwayat Pengajuan";
$current_page = "riwayat_pengajuan.php";

// Filter
$filter_tabel = $_GET['tabel'] ?? '';
$filter_status = $_GET['status'] ?? '';
$filter_bulan = $_GET['bulan'] ?? '';

// Pagination
$limit = 20;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$where_clauses = [];
$params = [];

if ($filter_tabel) {
    $where_clauses[] = "tabel_sumber = ?";
    $params[] = $filter_tabel;
}

if ($filter_status) {
    $where_clauses[] = "status_baru = ?";
    $params[] = $filter_status;
}

if ($filter_bulan) {
    $where_clauses[] = "TO_CHAR(created_at, 'YYYY-MM') = ?";
    $params[] = $filter_bulan;
}

$where_sql = $where_clauses ? "WHERE " . implode(" AND ", $where_clauses) : "";

// Get total records
$count_query = "SELECT COUNT(*) FROM v_riwayat_pengajuan $where_sql";
$count_stmt = $pdo->prepare($count_query);
$count_stmt->execute($params);
$total_records = $count_stmt->fetchColumn();
$total_pages = ceil($total_records / $limit);

// Query menggunakan VIEW dengan pagination
$query = "
    SELECT * FROM v_riwayat_pengajuan
    $where_sql
    ORDER BY created_at DESC
    LIMIT ? OFFSET ?
";

$params[] = $limit;
$params[] = $offset;

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$riwayat_list = $stmt->fetchAll();

// Get available months for filter
$months_query = $pdo->query("SELECT DISTINCT TO_CHAR(created_at, 'YYYY-MM') as month FROM v_riwayat_pengajuan ORDER BY month DESC LIMIT 12");
$available_months = $months_query->fetchAll(PDO::FETCH_COLUMN);

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

include "header.php";
include "sidebar.php";
include "navbar.php";
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-clock-history"></i> Riwayat Pengajuan & Aktivitas</h1>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card shadow border-start border-warning border-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Pending Review</div>
                        <div class="h4 mb-0 font-weight-bold">
                            <?php 
                            $pending = $pdo->query("SELECT COUNT(*) FROM v_riwayat_pengajuan WHERE status_baru = 'pending'")->fetchColumn();
                            echo $pending;
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
                            $approved = $pdo->query("SELECT COUNT(*) FROM v_riwayat_pengajuan WHERE status_baru = 'active'")->fetchColumn();
                            echo $approved;
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
                            $rejected = $pdo->query("SELECT COUNT(*) FROM v_riwayat_pengajuan WHERE status_baru = 'rejected'")->fetchColumn();
                            echo $rejected;
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
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Activity</div>
                        <div class="h4 mb-0 font-weight-bold">
                            <?php 
                            $total = $pdo->query("SELECT COUNT(*) FROM v_riwayat_pengajuan")->fetchColumn();
                            echo $total;
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
            <div class="col-md-4">
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
                    <option value="active" <?php echo $filter_status == 'active' ? 'selected' : ''; ?>>✅ Active</option>
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
            
            <div class="col-md-2 d-flex align-items-end gap-2">
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

<!-- Alert info untuk pending items -->
<?php if ($pending > 0): ?>
<div class="alert alert-warning alert-dismissible fade show">
    <i class="bi bi-exclamation-triangle"></i> 
    <strong>Perhatian:</strong> Ada <strong><?php echo $pending; ?> pengajuan</strong> yang menunggu review Anda.
    <a href="?status=pending" class="alert-link">Lihat semua pending</a>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- Riwayat Table -->
<div class="card shadow">
    <div class="card-header bg-white">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="bi bi-file-earmark-text"></i> Daftar Riwayat 
                <span class="badge bg-primary"><?php echo $total_records; ?> total</span>
            </h5>
            <small class="text-muted">Halaman <?php echo $page; ?> dari <?php echo $total_pages; ?></small>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-sm mb-0">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>Waktu</th>
                        <th>Tabel</th>
                        <th>ID</th>
                        <th>Status</th>
                        <th>Operator</th>
                        <th>Admin</th>
                        <th>Catatan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($riwayat_list) > 0): ?>
                        <?php 
                        $no = $offset + 1;
                        foreach ($riwayat_list as $riwayat): 
                        ?>
                        <tr>
                            <td><?php echo $no++; ?></td>
                            <td>
                                <small><?php echo date('d M Y', strtotime($riwayat['created_at'])); ?></small><br>
                                <small class="text-muted"><?php echo date('H:i', strtotime($riwayat['created_at'])); ?></small>
                            </td>
                            <td>
                                <span class="badge bg-secondary">
                                    <?php echo $all_tables[$riwayat['tabel_sumber']] ?? ucfirst($riwayat['tabel_sumber']); ?>
                                </span>
                            </td>
                            <td><code>#<?php echo $riwayat['id_data']; ?></code></td>
                            <td>
                                <?php
                                if ($riwayat['status_baru'] == 'active') {
                                    echo '<span class="badge bg-success">✅ Active</span>';
                                } elseif ($riwayat['status_baru'] == 'pending') {
                                    echo '<span class="badge bg-warning">⏳ Pending</span>';
                                } elseif ($riwayat['status_baru'] == 'rejected') {
                                    echo '<span class="badge bg-danger">❌ Rejected</span>';
                                } elseif ($riwayat['status_baru'] == 'deleted') {
                                    echo '<span class="badge bg-dark">🗑️ Deleted</span>';
                                }
                                ?>
                            </td>
                            <td>
                                <?php if ($riwayat['operator_nama']): ?>
                                    <small><?php echo htmlspecialchars($riwayat['operator_nama']); ?></small>
                                <?php else: ?>
                                    <small class="text-muted"><i>Admin Direct</i></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($riwayat['admin_nama']): ?>
                                    <small><?php echo htmlspecialchars($riwayat['admin_nama']); ?></small>
                                <?php else: ?>
                                    <small class="text-muted"><i>-</i></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($riwayat['catatan']): ?>
                                    <small><?php echo htmlspecialchars(substr($riwayat['catatan'], 0, 50)); ?><?php echo strlen($riwayat['catatan']) > 50 ? '...' : ''; ?></small>
                                <?php else: ?>
                                    <small class="text-muted">-</small>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="bi bi-inbox" style="font-size: 3rem; opacity: 0.3;"></i>
                                    <p class="mt-3 mb-0">Belum ada riwayat aktivitas</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
    <div class="card-footer bg-white">
        <nav aria-label="Page navigation">
            <ul class="pagination justify-content-center mb-0">
                <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $page - 1; ?><?php echo $filter_tabel ? '&tabel=' . $filter_tabel : ''; ?><?php echo $filter_status ? '&status=' . $filter_status : ''; ?><?php echo $filter_bulan ? '&bulan=' . $filter_bulan : ''; ?>">
                        <i class="bi bi-chevron-left"></i> Previous
                    </a>
                </li>
                
                <?php
                $start_page = max(1, $page - 2);
                $end_page = min($total_pages, $page + 2);
                
                if ($start_page > 1): ?>
                    <li class="page-item"><a class="page-link" href="?page=1<?php echo $filter_tabel ? '&tabel=' . $filter_tabel : ''; ?><?php echo $filter_status ? '&status=' . $filter_status : ''; ?><?php echo $filter_bulan ? '&bulan=' . $filter_bulan : ''; ?>">1</a></li>
                    <?php if ($start_page > 2): ?>
                        <li class="page-item disabled"><span class="page-link">...</span></li>
                    <?php endif; ?>
                <?php endif; ?>
                
                <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $i; ?><?php echo $filter_tabel ? '&tabel=' . $filter_tabel : ''; ?><?php echo $filter_status ? '&status=' . $filter_status : ''; ?><?php echo $filter_bulan ? '&bulan=' . $filter_bulan : ''; ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>
                
                <?php if ($end_page < $total_pages): ?>
                    <?php if ($end_page < $total_pages - 1): ?>
                        <li class="page-item disabled"><span class="page-link">...</span></li>
                    <?php endif; ?>
                    <li class="page-item"><a class="page-link" href="?page=<?php echo $total_pages; ?><?php echo $filter_tabel ? '&tabel=' . $filter_tabel : ''; ?><?php echo $filter_status ? '&status=' . $filter_status : ''; ?><?php echo $filter_bulan ? '&bulan=' . $filter_bulan : ''; ?>"><?php echo $total_pages; ?></a></li>
                <?php endif; ?>
                
                <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $page + 1; ?><?php echo $filter_tabel ? '&tabel=' . $filter_tabel : ''; ?><?php echo $filter_status ? '&status=' . $filter_status : ''; ?><?php echo $filter_bulan ? '&bulan=' . $filter_bulan : ''; ?>">
                        Next <i class="bi bi-chevron-right"></i>
                    </a>
                </li>
            </ul>
        </nav>
    </div>
    <?php endif; ?>
</div>

<style>
.table-hover tbody tr:hover {
    background-color: rgba(0, 0, 0, 0.02);
}
.table td {
    vertical-align: middle;
}
</style>

<?php include "footer.php"; ?>