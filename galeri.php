<?php
// galeri.php - Halaman Galeri Lab Data Technology (Publik)
$activePage = 'galeri';
include 'conn.php';
include 'navbar.php';

// Pagination
$items_per_page = 12;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $items_per_page;

try {
    // Get total count
    $stmt_count = $pdo->prepare("SELECT COUNT(*) FROM galeri WHERE status = 'active'");
    $stmt_count->execute();
    $total_items = $stmt_count->fetchColumn();
    $total_pages = ceil($total_items / $items_per_page);
    
    // Fetch data galeri dengan pagination
    $stmt = $pdo->prepare("SELECT * FROM galeri WHERE status = 'active' ORDER BY created_at DESC LIMIT ? OFFSET ?");
    $stmt->execute([$items_per_page, $offset]);
    $galeriItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $galeriItems = [];
    $total_pages = 0;
    $error = "Gagal mengambil data galeri: " . $e->getMessage();
}
?>
<link rel="stylesheet" href="assets/css/style.css">
<link rel="stylesheet" href="assets/css/galeri.css">

<!-- HERO SECTION -->
<div class="page-hero" style="background-image: url('assets/img/tentang-kami.jpeg');">
  <div class="page-hero-overlay">
    <div class="container">
      <h1 class="page-title">Galeri</h1>
      <p class="page-subtitle">Dokumentasi kegiatan dan suasana Lab Data Technology</p>
    </div>
  </div>
</div>

<!-- GALERI SECTION -->
<section class="section galeri-section">
  <div class="container">
    <div class="galeri-intro">
      <p>Koleksi foto dan dokumentasi berbagai kegiatan di Lab Data Technology, mulai dari praktikum, penelitian, seminar, hingga kegiatan kolaborasi dengan industri dan institusi lainnya.</p>
    </div>
    
    <?php if (isset($error)): ?>
      <div style="background: #fff3cd; border: 1px solid #ffc107; padding: 15px; border-radius: 8px; margin-bottom: 30px; color: #856404;">
        <strong>⚠️ Peringatan:</strong> <?php echo htmlspecialchars($error); ?>
      </div>
    <?php endif; ?>
    
    <!-- GALERI GRID -->
    <div class="galeri-grid">
      <?php
      if (count($galeriItems) > 0) {
        foreach ($galeriItems as $item) {
          $foto = htmlspecialchars($item['gambar']);
          $judul = htmlspecialchars($item['judul']);
          $deskripsi = htmlspecialchars($item['deskripsi']);
          $imageExists = file_exists('uploads/galeri/' . $foto);
      ?>
      <div class="galeri-item">
        <div class="galeri-image">
          <?php if ($imageExists): ?>
            <img src="uploads/galeri/<?php echo $foto; ?>" alt="<?php echo $judul; ?>">
          <?php else: ?>
            <div class="galeri-placeholder">Foto Kegiatan</div>
          <?php endif; ?>
        </div>
        
        <div class="galeri-overlay">
          <div class="galeri-content">
            <h3><?php echo $judul; ?></h3>
            <p><?php echo $deskripsi; ?></p>
          </div>
        </div>
      </div>
      <?php
        }
      } else {
      ?>
      <p style="text-align: center; grid-column: 1/-1; color: #999; padding: 40px;">Belum ada galeri yang ditampilkan.</p>
      <?php
      }
      ?>
    </div>
    
    <!-- PAGINATION -->
    <?php if ($total_pages > 1): ?>
    <div style="margin-top: 50px; display: flex; justify-content: center; gap: 10px; flex-wrap: wrap;">
      <?php
      // Previous button
      if ($page > 1) {
        echo '<a href="?page=' . ($page - 1) . '" style="padding: 10px 16px; background: var(--primary-blue); color: #fff; border-radius: 6px; text-decoration: none; font-weight: 600;">← Previous</a>';
      }
      
      // Page numbers
      $start = max(1, $page - 2);
      $end = min($total_pages, $page + 2);
      
      if ($start > 1) {
        echo '<a href="?page=1" style="padding: 10px 16px; background: #f0f0f0; border-radius: 6px; text-decoration: none;">1</a>';
        if ($start > 2) echo '<span style="padding: 10px 5px;">...</span>';
      }
      
      for ($i = $start; $i <= $end; $i++) {
        if ($i == $page) {
          echo '<span style="padding: 10px 16px; background: var(--primary-blue); color: #fff; border-radius: 6px; font-weight: 600;">' . $i . '</span>';
        } else {
          echo '<a href="?page=' . $i . '" style="padding: 10px 16px; background: #f0f0f0; border-radius: 6px; text-decoration: none;">' . $i . '</a>';
        }
      }
      
      if ($end < $total_pages) {
        if ($end < $total_pages - 1) echo '<span style="padding: 10px 5px;">...</span>';
        echo '<a href="?page=' . $total_pages . '" style="padding: 10px 16px; background: #f0f0f0; border-radius: 6px; text-decoration: none;">' . $total_pages . '</a>';
      }
      
      // Next button
      if ($page < $total_pages) {
        echo '<a href="?page=' . ($page + 1) . '" style="padding: 10px 16px; background: var(--primary-blue); color: #fff; border-radius: 6px; text-decoration: none; font-weight: 600;">Next →</a>';
      }
      ?>
    </div>
    <p style="text-align: center; color: #666; margin-top: 20px; font-size: 14px;">
      Halaman <strong><?php echo $page; ?></strong> dari <strong><?php echo $total_pages; ?></strong> 
      (Total: <strong><?php echo $total_items; ?></strong> foto)
    </p>
    <?php endif; ?>
  </div>
</section>

<?php include 'footer.php'; ?>