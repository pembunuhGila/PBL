<?php
$required_role = "admin";
include "../../auth.php";
include "../../conn.php";

$page_title = "Riwayat Pengajuan";
$current_page = "riwayat_pengajuan.php";

// Filter
$filter_tabel = $_GET['tabel'] ?? '';
$filter_status = $_GET['status'] ?? '';

$where_clauses = [];
$params = [];

if ($filter_tabel) {
    $where_clauses[] = "r.tabel_sumber = ?";
    $params[] = $filter_tabel;
}

if ($filter_status) {
    $where_clauses[] = "r.status_baru = ?";
    $params[] = $filter_status;
}

$where_sql = $where_clauses ? "WHERE " . implode(" AND ", $where_clauses) : "";

// Get riwayat with user details
$query = "
    SELECT 
        r.*,
        u_op.nama as operator_nama,
        u_adm.nama as admin_nama
    FROM riwayat_pengajuan r
    LEFT JOIN users u_op ON r.id_operator = u_op.id_user
    LEFT JOIN users u_adm ON r.id_admin = u_adm.id_user
    $where_sql
    ORDER BY r.created_at DESC
    LIMIT 100
";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$riwayat_list = $stmt->fetchAll();

// Daftar semua tabel yang ada di sistem (hardcoded untuk konsistensi)
$all_tables = [
    'anggota_lab' => 'Anggota Lab',
    'struktur_lab' => 'Struktur Lab',
    'publikasi' => 'Publikasi',
    'fasilitas' => 'Fasilitas',
    'galeri' => 'Galeri',
    'konten' => 'Konten',
    'tentang_kami' => 'Tentang Kami',
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
    <h1 class="h2">Riwayat Pengajuan</h1>
</div>

<!-- Filter -->
<div class="card shadow mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Tabel</label>
                <select class="form-select" name="tabel">
                    <option value="">Semua Tabel</option>
                    <?php foreach ($all_tables as $table_key => $table_name): ?>
                        <option value="<?php echo $table_key; ?>" <?php echo $filter_tabel == $table_key ? 'selected' : ''; ?>>
                            <?php echo $table_name; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="col-md-4">
                <label class="form-label">Status</label>
                <select class="form-select" name="status">
                    <option value="">Semua Status</option>
                    <option value="pending" <?php echo $filter_status == 'pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="active" <?php echo $filter_status == 'active' ? 'selected' : ''; ?>>Active</option>
                    <option value="rejected" <?php echo $filter_status == 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                    <option value="deleted" <?php echo $filter_status == 'deleted' ? 'selected' : ''; ?>>Deleted</option>
                </select>
            </div>
            
            <div class="col-md-4 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-funnel"></i> Filter
                </button>
                <a href="riwayat_pengajuan.php" class="btn btn-secondary">
                    <i class="bi bi-arrow-clockwise"></i> Reset
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card shadow border-left-warning">
            <div class="card-body">
                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Pending</div>
                <div class="h5 mb-0 font-weight-bold">
                    <?php 
                    $pending = $pdo->query("SELECT COUNT(*) FROM riwayat_pengajuan WHERE status_baru = 'pending'")->fetchColumn();
                    echo $pending;
                    ?>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card shadow border-left-success">
            <div class="card-body">
                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Approved</div>
                <div class="h5 mb-0 font-weight-bold">
                    <?php 
                    $approved = $pdo->query("SELECT COUNT(*) FROM riwayat_pengajuan WHERE status_baru = 'active'")->fetchColumn();
                    echo $approved;
                    ?>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card shadow border-left-danger">
            <div class="card-body">
                <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Rejected</div>
                <div class="h5 mb-0 font-weight-bold">
                    <?php 
                    $rejected = $pdo->query("SELECT COUNT(*) FROM riwayat_pengajuan WHERE status_baru = 'rejected'")->fetchColumn();
                    echo $rejected;
                    ?>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card shadow border-left-info">
            <div class="card-body">
                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Total</div>
                <div class="h5 mb-0 font-weight-bold">
                    <?php 
                    $total = $pdo->query("SELECT COUNT(*) FROM riwayat_pengajuan")->fetchColumn();
                    echo $total;
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Riwayat Table -->
<div class="card shadow">
    <div class="card-header">
        <h5 class="mb-0">
            Daftar Riwayat 
            <span class="badge bg-primary"><?php echo count($riwayat_list); ?> records</span>
        </h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover table-sm">
                <thead class="table-light">
                    <tr>
                        <th>Waktu</th>
                        <th>Tabel</th>
                        <th>ID Data</th>
                        <th>Operator</th>
                        <th>Admin</th>
                        <th>Perubahan Status</th>
                        <th>Catatan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($riwayat_list) > 0): ?>
                        <?php foreach ($riwayat_list as $riwayat): ?>
                        <tr>
                            <td>
                                <small><?php echo date('d M Y', strtotime($riwayat['created_at'])); ?></small><br>
                                <small class="text-muted"><?php echo date('H:i', strtotime($riwayat['created_at'])); ?></small>
                            </td>
                            <td>
                                <span class="badge bg-secondary">
                                    <?php 
                                    // Tampilkan nama tabel yang lebih friendly
                                    echo $all_tables[$riwayat['tabel_sumber']] ?? ucfirst(str_replace('_', ' ', $riwayat['tabel_sumber'])); 
                                    ?>
                                </span>
                            </td>
                            <td><code>#<?php echo $riwayat['id_data']; ?></code></td>
                            <td>
                                <?php if ($riwayat['operator_nama']): ?>
                                    <small><?php echo htmlspecialchars($riwayat['operator_nama']); ?></small>
                                <?php else: ?>
                                    <small class="text-muted">-</small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($riwayat['admin_nama']): ?>
                                    <small><?php echo htmlspecialchars($riwayat['admin_nama']); ?></small>
                                <?php else: ?>
                                    <small class="text-muted">-</small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php
                                $status_lama_badge = 'bg-secondary';
                                if ($riwayat['status_lama'] == 'active') $status_lama_badge = 'bg-success';
                                elseif ($riwayat['status_lama'] == 'pending') $status_lama_badge = 'bg-warning';
                                elseif ($riwayat['status_lama'] == 'rejected') $status_lama_badge = 'bg-danger';
                                
                                $status_baru_badge = 'bg-secondary';
                                if ($riwayat['status_baru'] == 'active') $status_baru_badge = 'bg-success';
                                elseif ($riwayat['status_baru'] == 'pending') $status_baru_badge = 'bg-warning';
                                elseif ($riwayat['status_baru'] == 'rejected') $status_baru_badge = 'bg-danger';
                                elseif ($riwayat['status_baru'] == 'deleted') $status_baru_badge = 'bg-dark';
                                ?>
                                <span class="badge <?php echo $status_lama_badge; ?> badge-sm">
                                    <?php echo $riwayat['status_lama'] ?? 'new'; ?>
                                </span>
                                <i class="bi bi-arrow-right"></i>
                                <span class="badge <?php echo $status_baru_badge; ?> badge-sm">
                                    <?php echo $riwayat['status_baru']; ?>
                                </span>
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
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                                <p class="mt-2">
                                    <?php if ($filter_tabel || $filter_status): ?>
                                        Tidak ada riwayat yang sesuai dengan filter
                                    <?php else: ?>
                                        Belum ada riwayat pengajuan
                                    <?php endif; ?>
                                </p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
.border-left-warning {
    border-left: 4px solid #f6c23e !important;
}
.border-left-success {
    border-left: 4px solid #1cc88a !important;
}
.border-left-danger {
    border-left: 4px solid #e74a3b !important;
}
.border-left-info {
    border-left: 4px solid #36b9cc !important;
}
.badge-sm {
    font-size: 0.75rem;
}
</style>

<?php include "footer.php"; ?>