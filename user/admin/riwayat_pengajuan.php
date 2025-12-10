<?php
/**
 * Admin Riwayat Pengajuan - Connected to ALL modules
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

// ========================================
// HANDLE APPROVE/REJECT
// ========================================
if (isset($_POST['action']) && isset($_POST['id_riwayat'])) {
    $action = $_POST['action'];
    $id_riwayat = $_POST['id_riwayat'];
    $new_status = ($action == 'approve') ? 'active' : 'rejected';
    
    try {
        // Get riwayat data
        $stmt_get = $pdo->prepare("SELECT tabel_sumber, id_data FROM riwayat_pengajuan WHERE id_riwayat = ?");
        $stmt_get->execute([$id_riwayat]);
        $riwayat_data = $stmt_get->fetch();
        
        if (!$riwayat_data) {
            throw new Exception("Data riwayat tidak ditemukan");
        }
        
        $tabel = $riwayat_data['tabel_sumber'];
        $id_data = $riwayat_data['id_data'];
        
        // Update riwayat
        $stmt = $pdo->prepare("UPDATE riwayat_pengajuan SET status_baru = ?, id_admin = ? WHERE id_riwayat = ?");
        $stmt->execute([$new_status, $_SESSION['id_user'], $id_riwayat]);
        
        // Map tabel ke kolom id
        $id_columns = [
            'anggota_lab' => 'id_anggota',
            'publikasi' => 'id_publikasi',
            'fasilitas' => 'id_fasilitas',
            'galeri' => 'id_galeri',
            'konten' => 'id_konten',
            'slider' => 'id_slider',
            'struktur_lab' => 'id_struktur',
            'tentang_kami' => 'id_profil',
            'visi' => 'id_visi',
            'misi' => 'id_misi',
            'sejarah' => 'id_sejarah',
            'kontak' => 'id_kontak'
        ];
        
        // Update status di tabel sumber
        if (isset($id_columns[$tabel])) {
            $id_col = $id_columns[$tabel];
            $update_query = "UPDATE $tabel SET status = ? WHERE $id_col = ?";
            $stmt_update = $pdo->prepare($update_query);
            $stmt_update->execute([$new_status, $id_data]);
        }
        
        header("Location: riwayat_pengajuan.php?success=" . ($new_status == 'active' ? 'approved' : 'rejected') . "&page=" . $page . ($filter_tabel ? "&tabel=" . urlencode($filter_tabel) : "") . ($filter_status ? "&status=" . urlencode($filter_status) : "") . ($filter_bulan ? "&bulan=" . urlencode($filter_bulan) : ""));
        exit;
    } catch (Exception $e) {
        $error = "Gagal: " . $e->getMessage();
    }
}

// ========================================
// BUILD UNIFIED QUERY - GABUNGKAN SEMUA MODUL
// ========================================

// UNION query untuk gabungkan semua sumber data
$union_parts = [];

// 1. Data dari riwayat_pengajuan (modul lama dengan operator tracking)
$riwayat_query = "
    SELECT 
        r.id_riwayat,
        r.created_at,
        r.tabel_sumber,
        r.id_data::text as id_data,
        r.status_lama,
        r.status_baru,
        uo.nama as operator_nama,
        ua.nama as admin_nama,
        r.catatan
    FROM riwayat_pengajuan r
    LEFT JOIN users uo ON r.id_operator = uo.id_user
    LEFT JOIN users ua ON r.id_admin = ua.id_user
";
$union_parts[] = $riwayat_query;

// 2. Data dari kontak (gunakan updated_at)
$kontak_query = "
    SELECT 
        NULL as id_riwayat,
        k.updated_at as created_at,
        'kontak' as tabel_sumber,
        k.id_kontak::text as id_data,
        NULL as status_lama,
        k.status as status_baru,
        u.nama as operator_nama,
        NULL as admin_nama,
        CONCAT('Kontak - ', k.email) as catatan
    FROM kontak k
    LEFT JOIN users u ON k.id_user = u.id_user
    WHERE k.status IN ('pending', 'rejected', 'active')
";
$union_parts[] = $kontak_query;

// 3. Data dari tentang_kami (gunakan updated_at)
$profil_query = "
    SELECT 
        NULL as id_riwayat,
        t.updated_at as created_at,
        'tentang_kami' as tabel_sumber,
        t.id_profil::text as id_data,
        NULL as status_lama,
        t.status as status_baru,
        u.nama as operator_nama,
        NULL as admin_nama,
        'Profil Lab' as catatan
    FROM tentang_kami t
    LEFT JOIN users u ON t.id_user = u.id_user
    WHERE t.status IN ('pending', 'rejected', 'active')
";
$union_parts[] = $profil_query;

// 4. Data dari visi
$visi_query = "
    SELECT 
        NULL as id_riwayat,
        v.created_at,
        'visi' as tabel_sumber,
        v.id_visi::text as id_data,
        NULL as status_lama,
        v.status as status_baru,
        u.nama as operator_nama,
        NULL as admin_nama,
        CONCAT('Visi: ', LEFT(v.isi_visi, 40), '...') as catatan
    FROM visi v
    LEFT JOIN users u ON v.id_user = u.id_user
    WHERE v.status IN ('pending', 'rejected', 'active')
";
$union_parts[] = $visi_query;

// 5. Data dari misi
$misi_query = "
    SELECT 
        NULL as id_riwayat,
        m.created_at,
        'misi' as tabel_sumber,
        m.id_misi::text as id_data,
        NULL as status_lama,
        m.status as status_baru,
        u.nama as operator_nama,
        NULL as admin_nama,
        CONCAT('Misi #', m.urutan, ': ', LEFT(m.isi_misi, 40), '...') as catatan
    FROM misi m
    LEFT JOIN users u ON m.id_user = u.id_user
    WHERE m.status IN ('pending', 'rejected', 'active')
";
$union_parts[] = $misi_query;

// 6. Data dari sejarah/roadmap
$sejarah_query = "
    SELECT 
        NULL as id_riwayat,
        s.created_at,
        'sejarah' as tabel_sumber,
        s.id_sejarah::text as id_data,
        NULL as status_lama,
        s.status as status_baru,
        u.nama as operator_nama,
        NULL as admin_nama,
        CONCAT('Roadmap ', s.tahun, ': ', s.judul) as catatan
    FROM sejarah s
    LEFT JOIN users u ON s.id_user = u.id_user
    WHERE s.status IN ('pending', 'rejected', 'active')
";
$union_parts[] = $sejarah_query;

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

// Count total records
$count_query = "SELECT COUNT(*) FROM ($base_query $where_sql) as count_table";
$count_stmt = $pdo->prepare($count_query);
$count_stmt->execute($filter_params);
$total_records = $count_stmt->fetchColumn();
$total_pages = ceil($total_records / $limit);

// Get paginated data
$final_query = "$base_query $where_sql ORDER BY created_at DESC LIMIT ? OFFSET ?";
$all_params = array_merge($filter_params, [$limit, $offset]);

$stmt = $pdo->prepare($final_query);
$stmt->execute($all_params);
$riwayat_list = $stmt->fetchAll();

// ========================================
// GET STATISTICS
// ========================================
$stats_pending_query = "SELECT COUNT(*) FROM ($base_query) as riwayat_data WHERE status_baru = 'pending'";
$stats_pending_stmt = $pdo->prepare($stats_pending_query);
$stats_pending_stmt->execute();
$pending = $stats_pending_stmt->fetchColumn();

$stats_approved_query = "SELECT COUNT(*) FROM ($base_query) as riwayat_data WHERE status_baru = 'active'";
$stats_approved_stmt = $pdo->prepare($stats_approved_query);
$stats_approved_stmt->execute();
$approved = $stats_approved_stmt->fetchColumn();

$stats_rejected_query = "SELECT COUNT(*) FROM ($base_query) as riwayat_data WHERE status_baru = 'rejected'";
$stats_rejected_stmt = $pdo->prepare($stats_rejected_query);
$stats_rejected_stmt->execute();
$rejected = $stats_rejected_stmt->fetchColumn();

$stats_deleted_query = "SELECT COUNT(*) FROM ($base_query) as riwayat_data WHERE status_baru = 'deleted'";
$stats_deleted_stmt = $pdo->prepare($stats_deleted_query);
$stats_deleted_stmt->execute();
$deleted = $stats_deleted_stmt->fetchColumn();

// Get available months for filter
$months_query = "SELECT DISTINCT TO_CHAR(created_at, 'YYYY-MM') as month FROM ($base_query) as riwayat_data ORDER BY month DESC LIMIT 12";
$months_stmt = $pdo->prepare($months_query);
$months_stmt->execute();
$available_months = $months_stmt->fetchAll(PDO::FETCH_COLUMN);

// Daftar tabel
$all_tables = [
    'anggota_lab' => 'Anggota Lab',
    'struktur_lab' => 'Struktur Lab',
    'publikasi' => 'Publikasi',
    'fasilitas' => 'Fasilitas',
    'galeri' => 'Galeri',
    'konten' => 'Konten',
    'slider' => 'Slider',
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
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Activity</div>
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
                        <th>ID</th>
                        <th>Status</th>
                        <th>Operator</th>
                        <th>Admin</th>
                        <th>Catatan</th>
                        <th>Aksi</th>
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
                            <td>
                                <?php if ($riwayat['status_baru'] == 'pending'): ?>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="id_riwayat" value="<?php echo htmlspecialchars($riwayat['id_riwayat'] ?? ''); ?>">
                                        <button type="submit" name="action" value="approve" class="btn btn-sm btn-success" onclick="return confirm('Setujui pengajuan ini?')">
                                            <i class="bi bi-check"></i> Acc
                                        </button>
                                        <button type="submit" name="action" value="reject" class="btn btn-sm btn-danger" onclick="return confirm('Tolak pengajuan ini?')">
                                            <i class="bi bi-x"></i> Reject
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <small class="text-muted">-</small>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="bi bi-inbox" style="font-size: 3rem; opacity: 0.3;"></i>
                                    <p class="mt-3 mb-0">
                                        <?php if ($filter_tabel || $filter_status || $filter_bulan): ?>
                                            Tidak ada riwayat yang sesuai dengan filter
                                        <?php else: ?>
                                            Belum ada riwayat aktivitas
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
.table-hover tbody tr:hover {
    background-color: rgba(0, 0, 0, 0.02);
}
.table td {
    vertical-align: middle;
}
.border-start {
    border-left-width: 4px !important;
}
</style>

<?php include "footer.php"; ?>