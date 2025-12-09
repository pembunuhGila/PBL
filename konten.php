<?php
// konten.php - Halaman Konten (Berita, Pengumuman, Agenda)
$activePage = 'konten';
include 'conn.php';
include 'navbar.php';

// Pagination & Search
$items_per_page = 6;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $items_per_page;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$kategori_filter = isset($_GET['kategori']) ? trim($_GET['kategori']) : '';

try {
    // Build WHERE clause
    $where = "WHERE status = 'active'";
    $params = [];
    
    if ($search) {
        $where .= " AND (judul ILIKE ? OR isi ILIKE ?)";
        $search_term = "%$search%";
        $params[] = $search_term;
        $params[] = $search_term;
    }
    
    if ($kategori_filter) {
        $where .= " AND kategori_konten = ?";
        $params[] = $kategori_filter;
    }
    
    // Get total count
    $count_stmt = $pdo->prepare("SELECT COUNT(*) FROM konten $where");
    $count_stmt->execute($params);
    $total_items = $count_stmt->fetchColumn();
    $total_pages = ceil($total_items / $items_per_page);
    
    // Fetch konten
    $fetch_params = array_merge($params, [$items_per_page, $offset]);
    $stmt = $pdo->prepare("SELECT * FROM konten $where ORDER BY tanggal_posting DESC LIMIT ? OFFSET ?");
    $stmt->execute($fetch_params);
    $kontenList = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get kategori list
    $kategori_stmt = $pdo->query("SELECT DISTINCT kategori_konten FROM konten WHERE status = 'active' ORDER BY kategori_konten");
    $kategoriList = $kategori_stmt->fetchAll(PDO::FETCH_COLUMN);
    
} catch (PDOException $e) {
    $kontenList = [];
    $kategoriList = [];
    $total_pages = 0;
    $error = "Gagal mengambil data konten: " . $e->getMessage();
}
?>
<link rel="stylesheet" href="assets/css/style.css">
<link rel="stylesheet" href="assets/css/konten.css">

<!-- HERO -->
<div class="page-hero" style="background-image: url('assets/img/tentang-kami.jpeg');">
  <div class="page-hero-overlay">
    <div class="container">
      <h1 class="page-title">Berita & Konten</h1>
      <p class="page-subtitle">Informasi terbaru, pengumuman, dan agenda Lab Data Technology</p>
    </div>
  </div>
</div>

<!-- CONTENT SECTION -->
<section class="section konten-section">
  <div class="container">
    
    <!-- SEARCH BAR BESAR -->
    <div style="margin-bottom: 50px;">
      <form method="GET" action="konten.php" style="display: flex; gap: 12px; margin-bottom: 30px;">
        <div style="flex: 1;">
          <input type="text" name="search" placeholder="🔍 Cari berita, pengumuman, atau agenda..." 
                 value="<?php echo htmlspecialchars($search); ?>" 
                 style="width: 100%; padding: 18px 24px; font-size: 16px; border: 2px solid #ddd; border-radius: 12px; transition: all 0.3s;">
        </div>
        <button type="submit" style="padding: 18px 32px; background: var(--primary-blue); color: #fff; border: none; border-radius: 12px; font-weight: 600; cursor: pointer; font-size: 16px; transition: all 0.3s;">
          Cari
        </button>
        <?php if ($search || $kategori_filter): ?>
          <a href="konten.php" style="padding: 18px 24px; background: #f0f0f0; color: #333; border: none; border-radius: 12px; font-weight: 600; cursor: pointer; text-decoration: none; font-size: 16px;">
            Reset
          </a>
        <?php endif; ?>
      </form>
      
      <!-- KATEGORI FILTER -->
      <div style="display: flex; gap: 8px; flex-wrap: wrap;">
        <a href="konten.php" style="padding: 10px 16px; background: <?php echo !$kategori_filter ? 'var(--primary-blue)' : '#f0f0f0'; ?>; color: <?php echo !$kategori_filter ? '#fff' : '#333'; ?>; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 14px;">
          Semua
        </a>
        <?php foreach ($kategoriList as $kat): ?>
          <a href="?kategori=<?php echo urlencode($kat); ?>" 
             style="padding: 10px 16px; background: <?php echo $kategori_filter === $kat ? 'var(--primary-blue)' : '#f0f0f0'; ?>; color: <?php echo $kategori_filter === $kat ? '#fff' : '#333'; ?>; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 14px;">
            <?php echo htmlspecialchars($kat); ?>
          </a>
        <?php endforeach; ?>
      </div>
    </div>
    
    <?php if (isset($error)): ?>
      <div style="background: #fff3cd; border: 1px solid #ffc107; padding: 15px; border-radius: 8px; margin-bottom: 30px; color: #856404;">
        <strong>⚠️ Error:</strong> <?php echo htmlspecialchars($error); ?>
      </div>
    <?php endif; ?>
    
    <!-- HASIL PENCARIAN -->
    <?php if ($search || $kategori_filter): ?>
      <p style="margin-bottom: 30px; color: #666; font-size: 14px;">
        Menampilkan <strong><?php echo count($kontenList); ?></strong> hasil
        <?php if ($search): ?>
          untuk pencarian "<strong><?php echo htmlspecialchars($search); ?></strong>"
        <?php endif; ?>
        <?php if ($kategori_filter): ?>
          dalam kategori <strong><?php echo htmlspecialchars($kategori_filter); ?></strong>
        <?php endif; ?>
      </p>
    <?php endif; ?>
    
    <!-- KONTEN GRID -->
    <div class="konten-grid">
      <?php
      if (count($kontenList) > 0) {
        foreach ($kontenList as $item) {
          $gambar = htmlspecialchars($item['gambar']);
          $judul = htmlspecialchars($item['judul']);
          $excerpt = htmlspecialchars(substr($item['isi'], 0, 150));
          $imageExists = !empty($gambar) && file_exists('uploads/konten/' . $gambar);
          $kategori = htmlspecialchars($item['kategori_konten']);
          $tanggal = date('d M Y', strtotime($item['tanggal_posting']));
          
          $badge_class = 'badge-berita';
          if ($kategori === 'Pengumuman') $badge_class = 'badge-pengumuman';
          if ($kategori === 'Agenda') $badge_class = 'badge-agenda';
      ?>
      
      <article class="konten-card">
        <div class="konten-thumb">
          <?php if ($imageExists): ?>
            <img src="uploads/konten/<?php echo $gambar; ?>" alt="<?php echo $judul; ?>">
          <?php else: ?>
            <div style="width: 100%; height: 200px; background: linear-gradient(135deg, #e8ebef, #d5dae0); display: flex; align-items: center; justify-content: center;">
              <span style="color: #999;">Tanpa Gambar</span>
            </div>
          <?php endif; ?>
          <span class="konten-badge <?php echo $badge_class; ?>"><?php echo $kategori; ?></span>
        </div>
        
        <div class="konten-body">
          <h3 class="konten-title"><a href="#"><?php echo $judul; ?></a></h3>
          <p class="konten-excerpt"><?php echo $excerpt; ?>...</p>
          
          <div class="konten-meta">
            <span class="meta-date">📅 <?php echo $tanggal; ?></span>
            <a href="#" class="read-more">Baca Selengkapnya →</a>
          </div>
        </div>
      </article>
      
      <?php
        }
      } else {
      ?>
      <div style="grid-column: 1/-1; text-align: center; padding: 60px 20px; color: #999;">
        <p style="font-size: 18px;">📰 Tidak ada konten yang ditemukan</p>
      </div>
      <?php
      }
      ?>
    </div>
    
    <!-- PAGINATION -->
    <?php if ($total_pages > 1): ?>
    <div style="margin-top: 50px; display: flex; justify-content: center; gap: 10px; flex-wrap: wrap;">
      <?php
      $query_string = ($search ? '&search=' . urlencode($search) : '') . ($kategori_filter ? '&kategori=' . urlencode($kategori_filter) : '');
      
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
    <p style="text-align: center; color: #666; margin-top: 20px; font-size: 14px;">
      Halaman <strong><?php echo $page; ?></strong> dari <strong><?php echo $total_pages; ?></strong> 
      (Total: <strong><?php echo $total_items; ?></strong> konten)
    </p>
    <?php endif; ?>
    
  </div>
</section>

<?php include 'footer.php'; ?>