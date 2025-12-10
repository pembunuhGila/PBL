<?php
$required_role = "operator";
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

// Build unified query untuk semua riwayat (gabungan riwayat_pengajuan + kontak + tentang kami)
$id_user = $_SESSION['id_user'];

// UNION query untuk gabungkan semua data - PERBAIKAN: cast semua id_data ke TEXT
$union_parts = [];
$union_params = [];

// 1. Data dari riwayat_pengajuan (modul lama) - CAST id_data ke TEXT
$riwayat_query = "
    SELECT 
        r.created_at,
        r.tabel_sumber,
        r.id_data::text as id_data,
        r.status_lama,
        r.status_baru,
        u.nama as admin_nama,
        r.catatan
    FROM riwayat_pengajuan r
    LEFT JOIN users u ON r.id_admin = u.id_user
    WHERE r.id_operator = ?
";
$union_parts[] = $riwayat_query;
$union_params[] = $id_user;

// 2. Data dari kontak (gunakan updated_at)
$kontak_query = "
    SELECT 
        updated_at as created_at,
        'kontak' as tabel_sumber,
        id_kontak::text as id_data,
        NULL as status_lama,
        status as status_baru,
        NULL as admin_nama,
        NULL as catatan
    FROM kontak
    WHERE id_user = ? AND status IN ('pending', 'rejected')
";
$union_parts[] = $kontak_query;
$union_params[] = $id_user;

// 3. Data dari tentang_kami (gunakan updated_at)
$profil_query = "
    SELECT 
        updated_at as created_at,
        'tentang_kami' as tabel_sumber,
        id_profil::text as id_data,
        NULL as status_lama,
        status as status_baru,
        NULL as admin_nama,
        NULL as catatan
    FROM tentang_kami
    WHERE id_user = ? AND status IN ('pending', 'rejected')
";
$union_parts[] = $profil_query;
$union_params[] = $id_user;

// 4. Data dari visi (gunakan created_at)
$visi_query = "
    SELECT 
        created_at,
        'visi' as tabel_sumber,
        id_visi::text as id_data,
        NULL as status_lama,
        status as status_baru,
        NULL as admin_nama,
        NULL as catatan
    FROM visi
    WHERE id_user = ? AND status IN ('pending', 'rejected')
";
$union_parts[] = $visi_query;
$union_params[] = $id_user;

// 5. Data dari misi (gunakan created_at)
$misi_query = "
    SELECT 
        created_at,
        'misi' as tabel_sumber,
        id_misi::text as id_data,
        NULL as status_lama,
        status as status_baru,
        NULL as admin_nama,
        NULL as catatan
    FROM misi
    WHERE id_user = ? AND status IN ('pending', 'rejected')
";
$union_parts[] = $misi_query;
$union_params[] = $id_user;

// 6. Data dari sejarah/roadmap (gunakan created_at)
$sejarah_query = "
    SELECT 
        created_at,
        'sejarah' as tabel_sumber,
        id_sejarah::text as id_data,
        NULL as status_lama,
        status as status_baru,
        NULL as admin_nama,
        NULL as catatan
    FROM sejarah
    WHERE id_user = ? AND status IN ('pending', 'rejected')
";
$union_parts[] = $sejarah_query;
$union_params[] = $id_user;

// Gabungkan semua query dengan UNION ALL
$base_query = "SELECT * FROM (" . implode(" UNION ALL ", $union_parts) . ") as all_riwayat";

// Apply filters
$where_clauses = [];
$filter_params = [];

if ($filter_tabel) {
    $where_clauses[] = "tabel_sumber = ?";
    $filter_params[] = $filter_tabel;
}

if ($filter_status) {
    $where_clauses[] = "status_baru = ?";
    $filter_params[] = $filter_status;
}

if ($filter_bulan) {
    $where_clauses[] = "TO_CHAR(created_at, 'YYYY-MM') = ?";
    $filter_params[] = $filter_bulan;
}

$where_sql = "";
if (count($where_clauses) > 0) {
    $where_sql = " WHERE " . implode(" AND ", $where_clauses);
}

// Count total
$count_query = "SELECT COUNT(*) FROM ($base_query $where_sql) as count_table";
$count_stmt = $pdo->prepare($count_query);
$count_stmt->execute(array_merge($union_params, $filter_params));
$total_records = $count_stmt->fetchColumn();
$total_pages = ceil($total_records / $limit);

// Get paginated data
$final_query = "$base_query $where_sql ORDER BY created_at DESC LIMIT ? OFFSET ?";
$all_params = array_merge($union_params, $filter_params, [$limit, $offset]);

$stmt = $pdo->prepare($final_query);
$stmt->execute($all_params);
$riwayat_list = $stmt->fetchAll();

// Get statistics dengan data lengkap
$stats_params = array_fill(0, 6, $id_user); // 6 kali id_user untuk semua UNION
$pending_query = "SELECT COUNT(*) FROM ($base_query) as riwayat_data WHERE status_baru = 'pending'";
$pending_stmt = $pdo->prepare($pending_query);
$pending_stmt->execute($stats_params);
$pending = $pending_stmt->fetchColumn();

$approved_query = "SELECT COUNT(*) FROM ($base_query) as riwayat_data WHERE status_baru = 'active'";
$approved_stmt = $pdo->prepare($approved_query);
$approved_stmt->execute($stats_params);
$approved = $approved_stmt->fetchColumn();

$rejected_query = "SELECT COUNT(*) FROM ($base_query) as riwayat_data WHERE status_baru = 'rejected'";
$rejected_stmt = $pdo->prepare($rejected_query);
$rejected_stmt->execute($stats_params);
$rejected = $rejected_stmt->fetchColumn();

// Get available months untuk filter
$months_query = "SELECT DISTINCT TO_CHAR(created_at, 'YYYY-MM') as month FROM ($base_query) as riwayat_data ORDER BY month DESC LIMIT 12";
$months_stmt = $pdo->prepare($months_query);
$months_stmt->execute($stats_params);
$available_months = $months_stmt->fetchAll(PDO::FETCH_COLUMN);

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
    'sejarah' => 'Roadmap',
    'kontak' => 'Kontak'
];

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
                        <div class="h4 mb-0 font-weight-bold"><?php echo $pending; ?></div>
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
                        <div class="h4 mb-0 font-weight-bold"><?php echo $approved; ?></div>
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
                        <div class="h4 mb-0 font-weight-bold"><?php echo $rejected; ?></div>
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
                        <div class="h4 mb-0 font-weight-bold"><?php echo $total_records; ?></div>
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

<!-- Info Alert -->
<div class="alert alert-info">
    <i class="bi bi-info-circle"></i> 
    <strong>Informasi:</strong> Menampilkan semua pengajuan Anda termasuk <strong>Kontak</strong> dan <strong>Tentang Kami</strong>. 
    Status <span class="badge bg-warning">Pending</span> menunggu review admin, 
    <span class="badge bg-success">Approved</span> sudah disetujui, 
    <span class="badge bg-danger">Rejected</span> ditolak oleh admin.
</div>

<!-- Riwayat Table -->
<div class="card shadow">
    <div class="card-header bg-white">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="bi bi-file-earmark-text"></i> Daftar Riwayat 
                <span class="badge bg-primary"><?php echo $total_records; ?> total</span>
            </h5>
            <small class="text-muted">Halaman <?php echo $page; ?> dari <?php echo max(1, $total_pages); ?></small>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-sm mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">No</th>
                        <th>Waktu</th>
                        <th>Tabel</th>
                        <th>ID Data</th>
                        <th>Perubahan Status</th>
                        <th>Admin Review</th>
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
                            <td class="ps-3"><?php echo $no++; ?></td>
                            <td>
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
                                        <span class="badge <?php echo $status_baru_badge; ?>">
                                            <?php echo $status_baru_icon . ' ' . ucfirst($riwayat['status_baru']); ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="badge <?php echo $status_baru_badge; ?>">
                                            <?php echo $status_baru_icon . ' ' . ucfirst($riwayat['status_baru']); ?>
                                        </span>
                                    <?php endif; ?>
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
                        </tr>
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
.border-start {
    border-left-width: 4px !important;
}

.table td, .table th {
    vertical-align: middle;
}

.badge {
    font-weight: 500;
}
</style>

<?php include "footer.php"; ?>