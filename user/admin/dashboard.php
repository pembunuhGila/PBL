<?php
$required_role = "admin";
include "../../auth.php";
include "../../conn.php";

$page_title = "Dashboard";
$current_page = "dashboard.php";

// Query untuk statistik
$query_publikasi = "SELECT COUNT(*) as total FROM publikasi WHERE status = 'active'";
$stmt = $pdo->query($query_publikasi);
$total_publikasi = $stmt->fetch()['total'];

$query_anggota = "SELECT COUNT(*) as total FROM anggota_lab WHERE status = 'active'";
$stmt = $pdo->query($query_anggota);
$total_anggota = $stmt->fetch()['total'];

$query_fasilitas = "SELECT COUNT(*) as total FROM fasilitas WHERE status = 'active'";
$stmt = $pdo->query($query_fasilitas);
$total_fasilitas = $stmt->fetch()['total'];

$query_galeri = "SELECT COUNT(*) as total FROM galeri WHERE status = 'active'";
$stmt = $pdo->query($query_galeri);
$total_galeri = $stmt->fetch()['total'];

$query_pending = "SELECT COUNT(*) as total FROM riwayat_pengajuan WHERE status_baru = 'pending'";
$stmt = $pdo->query($query_pending);
$total_pending = $stmt->fetch()['total'];

// Query untuk data terbaru
$query_latest = "
    SELECT 
        'publikasi' as tipe,
        judul as nama,
        created_at as tanggal
    FROM publikasi 
    WHERE status = 'active'
    UNION ALL
    SELECT 
        'anggota' as tipe,
        nama,
        created_at as tanggal
    FROM anggota_lab 
    WHERE status = 'active'
    ORDER BY tanggal DESC 
    LIMIT 5
";
$stmt_latest = $pdo->query($query_latest);

include "header.php";
include "sidebar.php";
include "navbar.php";
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Dashboard</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <button type="button" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-calendar"></i> <?php echo date('d F Y'); ?>
            </button>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Total Publikasi
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_publikasi; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-journal-text fa-2x text-gray-300" style="font-size: 2rem; color: #4e73df;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Total Anggota Lab
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_anggota; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-people fa-2x text-gray-300" style="font-size: 2rem; color: #1cc88a;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                            Total Fasilitas
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_fasilitas; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-building fa-2x text-gray-300" style="font-size: 2rem; color: #36b9cc;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            Pengajuan Pending
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_pending; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-clock-history fa-2x text-gray-300" style="font-size: 2rem; color: #f6c23e;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Activity & Quick Links -->
<div class="row">
    <div class="col-lg-8 mb-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Aktivitas Terbaru</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Tipe</th>
                                <th>Nama</th>
                                <th>Tanggal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $stmt_latest->fetch()): ?>
                            <tr>
                                <td>
                                    <?php if ($row['tipe'] == 'publikasi'): ?>
                                        <span class="badge bg-primary">Publikasi</span>
                                    <?php else: ?>
                                        <span class="badge bg-success">Anggota</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($row['nama']); ?></td>
                                <td><?php echo date('d M Y', strtotime($row['tanggal'])); ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4 mb-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
            </div>
            <div class="card-body">
                <div class="list-group">
                    <a href="anggota.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-plus-circle me-2"></i> Tambah Anggota Baru
                    </a>
                    <a href="publikasi.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-plus-circle me-2"></i> Tambah Publikasi
                    </a>
                    <a href="fasilitas.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-plus-circle me-2"></i> Tambah Fasilitas
                    </a>
                    <a href="galeri.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-plus-circle me-2"></i> Upload Galeri
                    </a>
                    <a href="riwayat_pengajuan.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-clock-history me-2"></i> Lihat Pengajuan
                    </a>
                </div>
            </div>
        </div>

        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Informasi</h6>
            </div>
            <div class="card-body">
                <p class="mb-2"><strong>Total Foto Galeri:</strong> <?php echo $total_galeri; ?></p>
                <p class="mb-2"><strong>Sistem:</strong> Lab Management v1.0</p>
                <p class="mb-0"><strong>Role:</strong> <?php echo ucfirst($_SESSION['role']); ?></p>
            </div>
        </div>
    </div>
</div>

<style>
    .border-left-primary {
        border-left: 4px solid #4e73df !important;
    }
    .border-left-success {
        border-left: 4px solid #1cc88a !important;
    }
    .border-left-info {
        border-left: 4px solid #36b9cc !important;
    }
    .border-left-warning {
        border-left: 4px solid #f6c23e !important;
    }
    .card {
        border-radius: 8px;
    }
    .shadow {
        box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15) !important;
    }
</style>

<?php include "footer.php"; ?>