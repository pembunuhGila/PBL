<?php
/**
 * Admin Dashboard - Connected to ALL modules
 * @var PDO $pdo
 * @var string $current_page
 * @var int $current_id_user
 */
$required_role = "admin";
include "../auth.php";
include "../../conn.php";

$page_title = "Dashboard";
$current_page = "dashboard.php";

// ========================================
// STATISTIK DARI SEMUA MODUL
// ========================================

// 1. Modul dengan riwayat_pengajuan (publikasi, anggota, fasilitas, galeri, konten, struktur, slider)
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

$query_konten = "SELECT COUNT(*) as total FROM konten WHERE status = 'active'";
$stmt = $pdo->query($query_konten);
$total_konten = $stmt->fetch()['total'];

$query_struktur = "SELECT COUNT(*) as total FROM struktur_lab WHERE status = 'active'";
$stmt = $pdo->query($query_struktur);
$total_struktur = $stmt->fetch()['total'];

$query_slider = "SELECT COUNT(*) as total FROM slider WHERE status = 'active'";
$stmt = $pdo->query($query_slider);
$total_slider = $stmt->fetch()['total'];

// 2. Modul Tentang Kami (profil, visi, misi, sejarah/roadmap)
$query_profil = "SELECT COUNT(*) as total FROM tentang_kami WHERE status = 'active'";
$stmt = $pdo->query($query_profil);
$total_profil = $stmt->fetch()['total'];

$query_visi = "SELECT COUNT(*) as total FROM visi WHERE status = 'active'";
$stmt = $pdo->query($query_visi);
$total_visi = $stmt->fetch()['total'];

$query_misi = "SELECT COUNT(*) as total FROM misi WHERE status = 'active'";
$stmt = $pdo->query($query_misi);
$total_misi = $stmt->fetch()['total'];

$query_roadmap = "SELECT COUNT(*) as total FROM sejarah WHERE status = 'active'";
$stmt = $pdo->query($query_roadmap);
$total_roadmap = $stmt->fetch()['total'];

// 3. Modul Kontak (global)
$query_kontak = "SELECT COUNT(*) as total FROM kontak WHERE status = 'active'";
$stmt = $pdo->query($query_kontak);
$total_kontak = $stmt->fetch()['total'];

// ========================================
// HITUNG PENDING DARI SEMUA MODUL
// ========================================

// Pending dari modul lama (dengan riwayat_pengajuan)
$pending_old_query = "
    SELECT COUNT(*) as total FROM (
        SELECT id_publikasi FROM publikasi WHERE status = 'pending'
        UNION ALL
        SELECT id_anggota FROM anggota_lab WHERE status = 'pending'
        UNION ALL
        SELECT id_fasilitas FROM fasilitas WHERE status = 'pending'
        UNION ALL
        SELECT id_galeri FROM galeri WHERE status = 'pending'
        UNION ALL
        SELECT id_konten FROM konten WHERE status = 'pending'
        UNION ALL
        SELECT id_struktur FROM struktur_lab WHERE status = 'pending'
        UNION ALL
        SELECT id_slider FROM slider WHERE status = 'pending'
    ) as old_pending
";
$stmt = $pdo->query($pending_old_query);
$pending_old = $stmt->fetch()['total'];

// Pending dari modul Tentang Kami
$pending_tentang_query = "
    SELECT COUNT(*) as total FROM (
        SELECT id_profil FROM tentang_kami WHERE status = 'pending'
        UNION ALL
        SELECT id_visi FROM visi WHERE status = 'pending'
        UNION ALL
        SELECT id_misi FROM misi WHERE status = 'pending'
        UNION ALL
        SELECT id_sejarah FROM sejarah WHERE status = 'pending'
    ) as tentang_pending
";
$stmt = $pdo->query($pending_tentang_query);
$pending_tentang = $stmt->fetch()['total'];

// Pending dari modul Kontak
$pending_kontak_query = "SELECT COUNT(*) as total FROM kontak WHERE status = 'pending'";
$stmt = $pdo->query($pending_kontak_query);
$pending_kontak = $stmt->fetch()['total'];

// Total pending
$total_pending = $pending_old + $pending_tentang + $pending_kontak;

// ========================================
// AKTIVITAS TERBARU DARI SEMUA MODUL
// ========================================
$query_latest = "
    SELECT 
        'publikasi' as tipe,
        judul as nama,
        status,
        created_at as tanggal
    FROM publikasi 
    WHERE status = 'active'
    
    UNION ALL
    SELECT 
        'anggota' as tipe,
        nama,
        status,
        created_at as tanggal
    FROM anggota_lab 
    WHERE status = 'active'
    
    UNION ALL
    SELECT 
        'fasilitas' as tipe,
        judul as nama,
        status,
        created_at as tanggal
    FROM fasilitas
    WHERE status = 'active'
    
    UNION ALL
    SELECT 
        'galeri' as tipe,
        judul as nama,
        status,
        created_at as tanggal
    FROM galeri
    WHERE status = 'active'
    
    UNION ALL
    SELECT 
        'konten' as tipe,
        judul as nama,
        status,
        tanggal_posting as tanggal
    FROM konten
    WHERE status = 'active'
    
    UNION ALL
    SELECT 
        'struktur' as tipe,
        jabatan as nama,
        status,
        created_at as tanggal
    FROM struktur_lab
    WHERE status = 'active'
    
    UNION ALL
    SELECT 
        'slider' as tipe,
        judul as nama,
        status,
        tanggal_dibuat as tanggal
    FROM slider
    WHERE status = 'active'
    
    UNION ALL
    SELECT 
        'profil' as tipe,
        'Profil Lab' as nama,
        status,
        updated_at as tanggal
    FROM tentang_kami
    WHERE status = 'active'
    
    UNION ALL
    SELECT 
        'visi' as tipe,
        CONCAT('Visi: ', LEFT(isi_visi, 30), '...') as nama,
        status,
        created_at as tanggal
    FROM visi
    WHERE status = 'active'
    
    UNION ALL
    SELECT 
        'misi' as tipe,
        CONCAT('Misi #', urutan) as nama,
        status,
        created_at as tanggal
    FROM misi
    WHERE status = 'active'
    
    UNION ALL
    SELECT 
        'roadmap' as tipe,
        CONCAT(tahun, ' - ', judul) as nama,
        status,
        created_at as tanggal
    FROM sejarah
    WHERE status = 'active'
    
    UNION ALL
    SELECT 
        'kontak' as tipe,
        CONCAT('Kontak - ', email) as nama,
        status,
        updated_at as tanggal
    FROM kontak
    WHERE status = 'active'
    
    ORDER BY tanggal DESC 
    LIMIT 10
";
$stmt_latest = $pdo->query($query_latest);

include "header.php";
include "sidebar.php";
include "navbar.php";
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Dashboard Admin</h1>
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
    <!-- Card 1: Total Konten -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Data Aktif</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?php 
                            $total_all = $total_publikasi + $total_anggota + $total_fasilitas + 
                                        $total_galeri + $total_konten + $total_struktur + 
                                        $total_slider + $total_profil + $total_visi + 
                                        $total_misi + $total_roadmap + $total_kontak;
                            echo $total_all; 
                            ?>
                        </div>
                        <small class="text-muted">Semua Modul</small>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-database fa-2x text-gray-300" style="font-size: 2rem; color: #4e73df;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Card 2: Publikasi -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Publikasi</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_publikasi; ?></div>
                        <small class="text-muted">+ <?php echo $total_anggota; ?> Anggota</small>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-journal-text fa-2x text-gray-300" style="font-size: 2rem; color: #1cc88a;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Card 3: Konten & Media -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Konten & Media</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?php echo $total_konten + $total_galeri + $total_slider; ?>
                        </div>
                        <small class="text-muted"><?php echo $total_konten; ?> konten, <?php echo $total_galeri; ?> foto</small>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-image fa-2x text-gray-300" style="font-size: 2rem; color: #36b9cc;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Card 4: Pending Review -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Pending Review</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_pending; ?></div>
                        <small class="text-muted">Menunggu Approval</small>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-clock-history fa-2x text-gray-300" style="font-size: 2rem; color: #f6c23e;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Detail Statistics -->
<div class="row mb-4">
    <div class="col-lg-8">
    </div>
</div>

<!-- Recent Activity -->
<div class="row">
    <div class="col-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Aktivitas Terbaru</h6>
                <a href="riwayat_pengajuan.php" class="btn btn-sm btn-primary">Lihat Semua</a>
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
                            <?php 
                            $row_count = 0;
                            while ($row = $stmt_latest->fetch()): 
                                $row_count++;
                            ?>
                            <tr>
                                <td>
                                    <?php
                                    $badge_colors = [
                                        'publikasi' => 'primary',
                                        'anggota' => 'success',
                                        'fasilitas' => 'info',
                                        'galeri' => 'warning',
                                        'konten' => 'danger',
                                        'struktur' => 'secondary',
                                        'slider' => 'dark',
                                        'profil' => 'success',
                                        'visi' => 'primary',
                                        'misi' => 'info',
                                        'roadmap' => 'warning',
                                        'kontak' => 'danger'
                                    ];
                                    $color = $badge_colors[$row['tipe']] ?? 'secondary';
                                    ?>
                                    <span class="badge bg-<?php echo $color; ?>">
                                        <?php echo ucfirst($row['tipe']); ?>
                                    </span>
                                </td>
                                <td><small><?php echo htmlspecialchars($row['nama']); ?></small></td>
                                <td>
                                    <span class="badge bg-success">Active</span>
                                </td>
                                <td>
                                    <small><?php echo date('d M Y', strtotime($row['tanggal'])); ?></small>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                            
                            <?php if ($row_count == 0): ?>
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">
                                    <i class="bi bi-inbox" style="font-size: 2rem; opacity: 0.3;"></i>
                                    <p class="mt-2 mb-0">Belum ada aktivitas</p>
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
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