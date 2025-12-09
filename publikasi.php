<?php
// publikasi.php - Halaman Publikasi
$activePage = 'publikasi';
include 'conn.php';
include 'navbar.php';

// Pagination & Search
$items_per_page = 6;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $items_per_page;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$tahun_filter = isset($_GET['tahun']) ? trim($_GET['tahun']) : '';

try {
    // Build WHERE clause
    $where = "WHERE p.status = 'active'";
    $params = [];
    
    if ($search) {
        $where .= " AND (p.judul ILIKE ? OR p.jurnal ILIKE ? OR p.abstrak ILIKE ?)";
        $search_term = "%$search%";
        $params[] = $search_term;
        $params[] = $search_term;
        $params[] = $search_term;
    }
    
    if ($tahun_filter) {
        $where .= " AND p.tahun = ?";
        $params[] = $tahun_filter;
    }
    
    // Get total count
    $count_stmt = $pdo->prepare("SELECT COUNT(DISTINCT p.id_publikasi) FROM publikasi p $where");
    $count_stmt->execute($params);
    $total_items = $count_stmt->fetchColumn();
    $total_pages = ceil($total_items / $items_per_page);
    
    // Fetch publikasi dengan penulis
    $fetch_params = array_merge($params, [$items_per_page, $offset]);
    $stmt = $pdo->prepare("
        SELECT p.*, 
               STRING_AGG(a.nama, ', ' ORDER BY pa.urutan_penulis) as penulis
        FROM publikasi p
        LEFT JOIN publikasi_anggota pa ON p.id_publikasi = pa.id_publikasi
        LEFT JOIN anggota_lab a ON pa.id_anggota = a.id_anggota
        $where
        GROUP BY p.id_publikasi
        ORDER BY p.created_at DESC
        LIMIT ? OFFSET ?
    ");
    $stmt->execute($fetch_params);
    $publikasiList = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get tahun list
    $tahun_stmt = $pdo->query("SELECT DISTINCT tahun FROM publikasi WHERE status = 'active' ORDER BY tahun DESC");
    $tahunList = $tahun_stmt->fetchAll(PDO::FETCH_COLUMN);
    
} catch (PDOException $e) {
    $publikasiList = [];
    $tahunList = [];
    $total_pages = 0;
    $error = "Gagal mengambil data publikasi: " . $e->getMessage();
}
?>
<link rel="stylesheet" href="assets/css/style.css">
<link rel="stylesheet" href="assets/css/publikasi.css">

<!-- HERO SECTION -->
<div class="page-hero" style="background-image: url('assets/img/tentang-kami.jpeg');">
  <div class="page-hero-overlay">
    <div class="container">
      <h1 class="page-title">Publikasi Ilmiah</h1>
      <p class="page-subtitle">Publikasi anggota laboratorium di jurnal nasional dan internasional</p>
    </div>
  </div>
</div>

<!-- PUBLIKASI SECTION -->
<section class="section publikasi-section">
  <div class="container">
    
    <!-- HEADER -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px; flex-wrap: wrap; gap: 20px;">
      <div>
        <h2 style="font-size: 36px; font-weight: 700; color: var(--primary-blue); margin: 0 0 8px 0;">Publikasi</h2>
        <p style="color: #666; margin: 0;">Publikasi Anggota Laboratorium Data Technology</p>
      </div>
      <a href="https://sinta.kemdikbud.go.id/" target="_blank" 
         style="padding: 12px 28px; background: #f0f0f0; color: var(--primary-blue); text-decoration: none; border-radius: 8px; font-weight: 600; transition: all 0.3s;">
        Lihat di SINTA →
      </a>
    </div>
    
    <!-- SEARCH BAR BESAR -->
    <div style="margin-bottom: 40px;">
      <form method="GET" action="publikasi.php" style="display: flex; gap: 12px; margin-bottom: 20px; flex-wrap: wrap;">
        <div style="flex: 1; min-width: 250px;">
          <input type="text" name="search" placeholder="🔍 Cari judul publikasi, penulis, atau jurnal..." 
                 value="<?php echo htmlspecialchars($search); ?>" 
                 style="width: 100%; padding: 16px 24px; font-size: 16px; border: 2px solid #ddd; border-radius: 10px; transition: all 0.3s;">
        </div>
        <select name="tahun" style="padding: 16px 20px; font-size: 16px; border: 2px solid #ddd; border-radius: 10px; transition: all 0.3s;">
          <option value="">Semua Tahun</option>
          <?php foreach ($tahunList as $tahun): ?>
            <option value="<?php echo $tahun; ?>" <?php echo $tahun_filter === $tahun ? 'selected' : ''; ?>>
              <?php echo $tahun; ?>
            </option>
          <?php endforeach; ?>
        </select>
        <button type="submit" style="padding: 16px 32px; background: var(--primary-blue); color: #fff; border: none; border-radius: 10px; font-weight: 600; cursor: pointer; font-size: 16px; transition: all 0.3s;">
          Cari
        </button>
        <?php if ($search || $tahun_filter): ?>
          <a href="publikasi.php" style="padding: 16px 24px; background: #f0f0f0; color: #333; border-radius: 10px; font-weight: 600; text-decoration: none; font-size: 16px;">
            Reset
          </a>
        <?php endif; ?>
      </form>
    </div>
    
    <?php if (isset($error)): ?>
      <div style="background: #fff3cd; border: 1px solid #ffc107; padding: 15px; border-radius: 8px; margin-bottom: 30px; color: #856404;">
        <strong>⚠️ Error:</strong> <?php echo htmlspecialchars($error); ?>
      </div>
    <?php endif; ?>
    
    <!-- HASIL PENCARIAN -->
    <?php if ($search || $tahun_filter): ?>
      <p style="margin-bottom: 30px; color: #666; font-size: 14px;">
        Menampilkan <strong><?php echo count($publikasiList); ?></strong> hasil
        <?php if ($search): ?>
          untuk pencarian "<strong><?php echo htmlspecialchars($search); ?></strong>"
        <?php endif; ?>
        <?php if ($tahun_filter): ?>
          tahun <strong><?php echo htmlspecialchars($tahun_filter); ?></strong>
        <?php endif; ?>
      </p>
    <?php endif; ?>
    
    <!-- PUBLIKASI GRID -->
    <div class="publikasi-grid">
      <?php
      if (count($publikasiList) > 0) {
        foreach ($publikasiList as $pub) {
          $id = (int)$pub['id_publikasi'];
          $cover = htmlspecialchars($pub['cover']);
          $judul = htmlspecialchars($pub['judul']);
          $penulis = htmlspecialchars($pub['penulis'] ?? 'Tidak ada penulis');
          $tahun = htmlspecialchars($pub['tahun']);
          $imageExists = !empty($cover) && file_exists('uploads/publikasi/cover/' . $cover);
      ?>
      
      <article class="publikasi-card">
        <div class="publikasi-cover">
          <?php if ($imageExists): ?>
            <img src="uploads/publikasi/cover/<?php echo $cover; ?>" alt="<?php echo $judul; ?>">
          <?php else: ?>
            <div style="width: 100%; height: 200px; background: linear-gradient(135deg, #e8ebef, #d5dae0); display: flex; align-items: center; justify-content: center;">
              <span style="color: #999; text-align: center;">📖 No Cover</span>
            </div>
          <?php endif; ?>
        </div>
        
        <div class="publikasi-body">
          <h3 class="publikasi-title">
            <a href="publikasi_detail.php?id=<?php echo $id; ?>"><?php echo $judul; ?></a>
          </h3>
          <p class="publikasi-meta">
            <strong><?php echo $penulis; ?></strong><br>
            <span style="color: #888; font-size: 13px;">📅 <?php echo $tahun; ?></span>
          </p>
          <a href="publikasi_detail.php?id=<?php echo $id; ?>" class="publikasi-link">
            Baca Selengkapnya >>>
          </a>
        </div>
      </article>
      
      <?php
        }
      } else {
      ?>
      <div style="grid-column: 1/-1; text-align: center; padding: 60px 20px; color: #999;">
        <p style="font-size: 18px;">📄 Tidak ada publikasi yang ditemukan</p>
      </div>
      <?php
      }
      ?>
    </div>
    
    <!-- PAGINATION -->
    <?php if ($total_pages > 1): ?>
    <div style="margin-top: 50px; text-align: center;">
      <div style="display: flex; justify-content: center; gap: 10px; flex-wrap: wrap; margin-bottom: 20px;">
        <?php
        $query_string = ($search ? '&search=' . urlencode($search) : '') . ($tahun_filter ? '&tahun=' . urlencode($tahun_filter) : '');
        
        if ($page > 1) {
          echo '<a href="?page=' . ($page - 1) . $query_string . '" style="padding: 10px 16px; background: var(--primary-blue); color: #fff; border-radius: 6px; text-decoration: none; font-weight: 600;">← Previous</a>';
        }
        
        $start = max(1, $page - 2);
        $end = min($total_pages, $page + 2);
        
        if ($start > 1) {
          echo '<a href="?page=1' . $query_string . '" style="padding: 10px 16px; background: #f0f0f0; border-radius: 6px; text-decoration: none;">1</a>';
          if ($start > 2) echo '<span style="padding: 10px 5px;">...</span>';
        }
        
        for ($i = $start; $i <= $end; $i++) {
          if ($i == $page) {
            echo '<span style="padding: 10px 16px; background: var(--primary-blue); color: #fff; border-radius: 6px; font-weight: 600;">' . $i . '</span>';
          } else {
            echo '<a href="?page=' . $i . $query_string . '" style="padding: 10px 16px; background: #f0f0f0; border-radius: 6px; text-decoration: none;">' . $i . '</a>';
          }
        }
        
        if ($end < $total_pages) {
          if ($end < $total_pages - 1) echo '<span style="padding: 10px 5px;">...</span>';
          echo '<a href="?page=' . $total_pages . $query_string . '" style="padding: 10px 16px; background: #f0f0f0; border-radius: 6px; text-decoration: none;">' . $total_pages . '</a>';
        }
        
        if ($page < $total_pages) {
          echo '<a href="?page=' . ($page + 1) . $query_string . '" style="padding: 10px 16px; background: var(--primary-blue); color: #fff; border-radius: 6px; text-decoration: none; font-weight: 600;">Next →</a>';
        }
        ?>
      </div>
      <p style="color: #666; font-size: 14px;">
        Halaman <strong><?php echo $page; ?></strong> dari <strong><?php echo $total_pages; ?></strong> 
        (Total: <strong><?php echo $total_items; ?></strong> publikasi)
      </p>
    </div>
    <?php endif; ?>
    
  </div>
</section>

<?php include 'footer.php'; ?>