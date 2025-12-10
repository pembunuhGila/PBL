<?php
/**
 * @var PDO $pdo
 * @var string $current_page
 * @var int $current_id_user
 */
$required_role = "operator";
include "../auth.php";
include "../../conn.php";

$page_title = "Dashboard";
$current_page = "dashboard.php";

// Query untuk statistik operator
$query_publikasi = "SELECT COUNT(*) as total FROM publikasi WHERE id_user = ?";
$stmt = $pdo->prepare($query_publikasi);
$stmt->execute([$_SESSION['id_user']]);
$total_publikasi = $stmt->fetch()['total'];

$query_anggota = "SELECT COUNT(*) as total FROM anggota_lab WHERE id_user = ?";
$stmt = $pdo->prepare($query_anggota);
$stmt->execute([$_SESSION['id_user']]);
$total_anggota = $stmt->fetch()['total'];

$query_fasilitas = "SELECT COUNT(*) as total FROM fasilitas WHERE id_user = ?";
$stmt = $pdo->prepare($query_fasilitas);
$stmt->execute([$_SESSION['id_user']]);
$total_fasilitas = $stmt->fetch()['total'];

$query_galeri = "SELECT COUNT(*) as total FROM galeri WHERE id_user = ?";
$stmt = $pdo->prepare($query_galeri);
$stmt->execute([$_SESSION['id_user']]);
$total_galeri = $stmt->fetch()['total'];

// HITUNG PENDING dari TABEL ASLI (bukan dari riwayat_pengajuan)
// Ini akan menghitung semua data dengan status pending milik operator

// 1. Pending dari modul dengan riwayat_pengajuan
$query_pending_old = "
    SELECT COUNT(*) as total FROM (
        SELECT id_publikasi FROM publikasi WHERE id_user = ? AND status = 'pending'
        UNION ALL
        SELECT id_anggota FROM anggota_lab WHERE id_user = ? AND status = 'pending'
        UNION ALL
        SELECT id_fasilitas FROM fasilitas WHERE id_user = ? AND status = 'pending'
        UNION ALL
        SELECT id_galeri FROM galeri WHERE id_user = ? AND status = 'pending'
        UNION ALL
        SELECT id_struktur FROM struktur_lab WHERE id_user = ? AND status = 'pending'
        UNION ALL
        SELECT id_konten FROM konten WHERE id_user = ? AND status = 'pending'
    ) as old_pending
";
$stmt = $pdo->prepare($query_pending_old);
$stmt->execute([
    $_SESSION['id_user'], 
    $_SESSION['id_user'], 
    $_SESSION['id_user'], 
    $_SESSION['id_user'],
    $_SESSION['id_user'],
    $_SESSION['id_user']
]);
$pending_old = $stmt->fetch()['total'];

// 2. Pending dari modul Tentang Kami
$query_pending_tentang = "
    SELECT COUNT(*) as total FROM (
        SELECT id_profil FROM tentang_kami WHERE id_user = ? AND status = 'pending'
        UNION ALL
        SELECT id_visi FROM visi WHERE id_user = ? AND status = 'pending'
        UNION ALL
        SELECT id_misi FROM misi WHERE id_user = ? AND status = 'pending'
        UNION ALL
        SELECT id_sejarah FROM sejarah WHERE id_user = ? AND status = 'pending'
    ) as tentang_pending
";
$stmt = $pdo->prepare($query_pending_tentang);
$stmt->execute([$_SESSION['id_user'], $_SESSION['id_user'], $_SESSION['id_user'], $_SESSION['id_user']]);
$pending_tentang = $stmt->fetch()['total'];

// 3. Pending dari modul Kontak
$query_pending_kontak = "SELECT COUNT(*) as total FROM kontak WHERE id_user = ? AND status = 'pending'";
$stmt = $pdo->prepare($query_pending_kontak);
$stmt->execute([$_SESSION['id_user']]);
$pending_kontak = $stmt->fetch()['total'];

// Total semua pending
$total_pending = $pending_old + $pending_tentang + $pending_kontak;

// Query approved (dari riwayat_pengajuan)
$query_approved = "SELECT COUNT(*) as total FROM riwayat_pengajuan WHERE id_operator = ? AND status_baru = 'active'";
$stmt = $pdo->prepare($query_approved);
$stmt->execute([$_SESSION['id_user']]);
$total_approved = $stmt->fetch()['total'];

// Query untuk data terbaru milik operator
$query_latest = "
    SELECT 
        'publikasi' as tipe,
        judul as nama,
        status,
        created_at as tanggal
    FROM publikasi 
    WHERE id_user = ?
    
    UNION ALL
    SELECT 
        'anggota' as tipe,
        nama,
        status,
        created_at as tanggal
    FROM anggota_lab 
    WHERE id_user = ?
    
    UNION ALL
    SELECT 
        'fasilitas' as tipe,
        judul as nama,
        status,
        created_at as tanggal
    FROM fasilitas
    WHERE id_user = ?
    
    UNION ALL
    SELECT 
        'kontak' as tipe,
        CONCAT('Kontak - ', email) as nama,
        status,
        updated_at as tanggal
    FROM kontak
    WHERE id_user = ?
    
    UNION ALL
    SELECT 
        'profil' as tipe,
        'Profil Lab' as nama,
        status,
        updated_at as tanggal
    FROM tentang_kami
    WHERE id_user = ?
    
    UNION ALL
    SELECT 
        'visi' as tipe,
        CONCAT('Visi: ', LEFT(isi_visi, 30), '...') as nama,
        status,
        created_at as tanggal
    FROM visi
    WHERE id_user = ?
    
    UNION ALL
    SELECT 
        'misi' as tipe,
        CONCAT('Misi #', urutan) as nama,
        status,
        created_at as tanggal
    FROM misi
    WHERE id_user = ?
    
    UNION ALL
    SELECT 
        'roadmap' as tipe,
        CONCAT(tahun, ' - ', judul) as nama,
        status,
        created_at as tanggal
    FROM sejarah
    WHERE id_user = ?
    
    ORDER BY tanggal DESC 
    LIMIT 10
";
$stmt_latest = $pdo->prepare($query_latest);
$stmt_latest->execute([
    $_SESSION['id_user'], // publikasi
    $_SESSION['id_user'], // anggota
    $_SESSION['id_user'], // fasilitas
    $_SESSION['id_user'], // kontak - updated_at
    $_SESSION['id_user'], // profil - updated_at
    $_SESSION['id_user'], // visi - created_at
    $_SESSION['id_user'], // misi - created_at
    $_SESSION['id_user']  // roadmap - created_at
]);

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
                            Data Saya
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?php echo $total_publikasi + $total_anggota + $total_fasilitas + $total_galeri; ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-files fa-2x text-gray-300" style="font-size: 2rem; color: #4e73df;"></i>
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
                            Disetujui Admin
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_approved; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-check-circle fa-2x text-gray-300" style="font-size: 2rem; color: #1cc88a;"></i>
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
                            Menunggu Review
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_pending; ?></div>
                        <?php if ($pending_kontak > 0 || $pending_tentang > 0 || $pending_old > 0): ?>
                        <?php endif; ?>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-hourglass-split fa-2x text-gray-300" style="font-size: 2rem; color: #f6c23e;"></i>
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
                            Publikasi Saya
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_publikasi; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-journal-text fa-2x text-gray-300" style="font-size: 2rem; color: #36b9cc;"></i>
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
                <h6 class="m-0 font-weight-bold text-primary">Data Terbaru Saya</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-sm">
                        <thead>
                            <tr>
                                <th>Tipe</th>
                                <th>Nama</th>
                                <th>Status</th>
                                <th>Tanggal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $stmt_latest->fetch()): ?>
                            <tr>
                                <td>
                                    <?php if ($row['tipe'] == 'publikasi'): ?>
                                        <span class="badge bg-primary">Publikasi</span>
                                    <?php elseif ($row['tipe'] == 'anggota'): ?>
                                        <span class="badge bg-success">Anggota</span>
                                    <?php elseif ($row['tipe'] == 'fasilitas'): ?>
                                        <span class="badge bg-info">Fasilitas</span>
                                    <?php elseif ($row['tipe'] == 'kontak'): ?>
                                        <span class="badge bg-warning">Kontak</span>
                                    <?php elseif ($row['tipe'] == 'profil'): ?>
                                        <span class="badge bg-secondary">Profil</span>
                                    <?php elseif ($row['tipe'] == 'visi'): ?>
                                        <span class="badge bg-primary">Visi</span>
                                    <?php elseif ($row['tipe'] == 'misi'): ?>
                                        <span class="badge bg-success">Misi</span>
                                    <?php elseif ($row['tipe'] == 'roadmap'): ?>
                                        <span class="badge bg-info">Roadmap</span>
                                    <?php endif; ?>
                                </td>
                                <td><small><?php echo htmlspecialchars($row['nama']); ?></small></td>
                                <td>
                                    <?php if ($row['status'] == 'pending'): ?>
                                        <span class="badge bg-warning">Pending</span>
                                    <?php elseif ($row['status'] == 'active'): ?>
                                        <span class="badge bg-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Rejected</span>
                                    <?php endif; ?>
                                </td>
                                <td><small><?php echo date('d M Y', strtotime($row['tanggal'])); ?></small></td>
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
                    <a href="kontak.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-telephone me-2"></i> Kelola Kontak
                    </a>
                    <a href="tentang.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-building me-2"></i> Kelola Tentang Kami
                    </a>
                    <a href="riwayat_pengajuan.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-clock-history me-2"></i> Lihat Riwayat
                    </a>
                </div>
            </div>
        </div>

        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Info</h6>
            </div>
            <div class="card-body">
                <p class="mb-2"><strong>Total Publikasi:</strong> <?php echo $total_publikasi; ?></p>
                <p class="mb-2"><strong>Total Anggota:</strong> <?php echo $total_anggota; ?></p>
                <p class="mb-2"><strong>Total Fasilitas:</strong> <?php echo $total_fasilitas; ?></p>
                <p class="mb-2"><strong>Total Galeri:</strong> <?php echo $total_galeri; ?></p>
                <hr>
                <p class="mb-2"><strong>Pending Review:</strong></p>
                <ul class="mb-2 ps-3">
                    <li><small>Kontak: <?php echo $pending_kontak; ?></small></li>
                    <li><small>Tentang Kami: <?php echo $pending_tentang; ?></small></li>
                    <li><small>Lainnya: <?php echo $pending_old; ?></small></li>
                </ul>
                <hr>
                <p class="mb-0 text-muted small">
                    <i class="bi bi-info-circle"></i> Semua data yang Anda tambahkan akan direview oleh admin.
                </p>
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