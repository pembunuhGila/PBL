<?php
// fasilitas.php - Halaman Fasilitas Lab Data Technology (Publik)
$activePage = 'fasilitas';
include 'conn.php';
include 'navbar.php';

// Pagination per kategori
$items_per_page = 6;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $items_per_page;

try {
    // Fetch data fasilitas dengan pagination
    $stmt = $pdo->prepare("SELECT * FROM fasilitas WHERE status = 'active' ORDER BY kategori_fasilitas ASC, created_at DESC LIMIT ? OFFSET ?");
    $stmt->execute([$items_per_page, $offset]);
    $fasilitasData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get total count
    $stmt_count = $pdo->prepare("SELECT COUNT(*) FROM fasilitas WHERE status = 'active'");
    $stmt_count->execute();
    $total_items = $stmt_count->fetchColumn();
    $total_pages = ceil($total_items / $items_per_page);
    
    // Kelompokkan berdasarkan kategori
    $fasilitasByKategori = [];
    foreach ($fasilitasData as $item) {
        $kategori = $item['kategori_fasilitas'] ?? 'Lainnya';
        if (!isset($fasilitasByKategori[$kategori])) {
            $fasilitasByKategori[$kategori] = [];
        }
        $fasilitasByKategori[$kategori][] = $item;
    }
} catch (PDOException $e) {
    $fasilitasByKategori = [];
    $total_pages = 0;
    $error = "Gagal mengambil data fasilitas: " . $e->getMessage();
}

// Definisi kategori
$kategoriInfo = [
    'Ruang Praktikum & Penelitian' => [
        'icon' => '<svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><line x1="9" y1="9" x2="15" y2="9"></line><line x1="9" y1="15" x2="15" y2="15"></line></svg>',
        'deskripsi' => 'Ruang laboratorium yang nyaman untuk kegiatan praktikum, eksperimen, dan penelitian'
    ],
    'Perangkat Lunak' => [
        'icon' => '<svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="16 18 22 12 16 6"></polyline><polyline points="8 6 2 12 8 18"></polyline></svg>',
        'deskripsi' => 'Software analisis data, machine learning, serta tools big data untuk kebutuhan riset dan pembelajaran'
    ],
    'Perangkat Komputer' => [
        'icon' => '<svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect><line x1="8" y1="21" x2="16" y2="21"></line><line x1="12" y1="17" x2="12" y2="21"></line></svg>',
        'deskripsi' => 'Dilengkapi perangkat komputer berperforma tinggi untuk mendukung praktikum dan penelitian data'
    ]
];
?>
<link rel="stylesheet" href="assets/css/style.css">
<link rel="stylesheet" href="assets/css/fasilitas.css">

<!-- HERO SECTION -->
<div class="page-hero" style="background-image: url('assets/img/tentang-kami.jpeg');">
  <div class="page-hero-overlay">
    <div class="container">
      <h1 class="page-title">Fasilitas & Peralatan</h1>
      <p class="page-subtitle">Laboratorium modern dengan peralatan terkini untuk mendukung riset dan pembelajaran</p>
    </div>
  </div>
</div>

<?php if (isset($error)): ?>
  <div class="container" style="padding: 20px 0;">
    <div style="background: #fff3cd; border: 1px solid #ffc107; padding: 15px; border-radius: 8px; color: #856404;">
      <strong>⚠️ Peringatan:</strong> <?php echo htmlspecialchars($error); ?>
    </div>
  </div>
<?php endif; ?>

<!-- KATEGORI SECTIONS -->
<?php 
$bgColor = ['#fff', '#f8f9fb'];
$bgIndex = 0;
$kategoriOrder = ['Ruang Praktikum & Penelitian', 'Perangkat Lunak', 'Perangkat Komputer'];

foreach ($kategoriOrder as $kategori): 
    if (!isset($fasilitasByKategori[$kategori])) continue;
    
    $items = $fasilitasByKategori[$kategori];
    $info = $kategoriInfo[$kategori] ?? ['icon' => '', 'deskripsi' => ''];
    $bg = $bgColor[$bgIndex % 2];
    $bgIndex++;
?>

<section class="section kategori-section" style="background-color: <?php echo $bg; ?>;">
  <div class="container">
    <div class="kategori-header">
      <div class="kategori-icon">
        <?php echo $info['icon']; ?>
      </div>
      <div>
        <h2 class="kategori-title"><?php echo htmlspecialchars($kategori); ?></h2>
        <p class="kategori-desc"><?php echo htmlspecialchars($info['deskripsi']); ?></p>
      </div>
    </div>
    
    <div class="fasilitas-grid">
      <?php foreach ($items as $item): ?>
        <?php 
          $gambar = htmlspecialchars($item['gambar']);
          $judul = htmlspecialchars($item['judul']);
          $deskripsi = htmlspecialchars($item['deskripsi']);
          $imageExists = !empty($gambar) && file_exists('uploads/fasilitas/' . $gambar);
        ?>
        
        <div class="fasilitas-card">
          <div class="fasilitas-image">
            <?php if ($imageExists): ?>
              <img src="uploads/fasilitas/<?php echo $gambar; ?>" alt="<?php echo $judul; ?>">
            <?php else: ?>
              <div style="width: 100%; height: 100%; background: linear-gradient(135deg, #e8ebef, #d5dae0); display: flex; align-items: center; justify-content: center;">
                <span style="color: #999; font-weight: 600;">Foto Fasilitas</span>
              </div>
            <?php endif; ?>
            
            <div class="image-overlay">
              <svg width="32" height="32" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <circle cx="11" cy="11" r="8"></circle>
                <path d="m21 21-4.35-4.35"></path>
              </svg>
            </div>
          </div>
          
          <div class="fasilitas-content">
            <h3><?php echo $judul; ?></h3>
            <p><?php echo $deskripsi; ?></p>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php endforeach; ?>

<!-- PAGINATION -->
<?php if ($total_pages > 1): ?>
<section class="section" style="padding: 40px 0; background: #f8f9fb;">
  <div class="container" style="text-align: center;">
    <div style="display: flex; justify-content: center; gap: 10px; flex-wrap: wrap; margin-bottom: 20px;">
      <?php
      if ($page > 1) {
        echo '<a href="?page=' . ($page - 1) . '" style="padding: 10px 16px; background: var(--primary-blue); color: #fff; border-radius: 6px; text-decoration: none; font-weight: 600;">← Previous</a>';
      }
      
      $start = max(1, $page - 2);
      $end = min($total_pages, $page + 2);
      
      if ($start > 1) {
        echo '<a href="?page=1" style="padding: 10px 16px; background: #fff; border: 2px solid #ddd; border-radius: 6px; text-decoration: none;">1</a>';
        if ($start > 2) echo '<span style="padding: 10px 5px;">...</span>';
      }
      
      for ($i = $start; $i <= $end; $i++) {
        if ($i == $page) {
          echo '<span style="padding: 10px 16px; background: var(--primary-blue); color: #fff; border-radius: 6px; font-weight: 600;">' . $i . '</span>';
        } else {
          echo '<a href="?page=' . $i . '" style="padding: 10px 16px; background: #fff; border: 2px solid #ddd; border-radius: 6px; text-decoration: none;">' . $i . '</a>';
        }
      }
      
      if ($end < $total_pages) {
        if ($end < $total_pages - 1) echo '<span style="padding: 10px 5px;">...</span>';
        echo '<a href="?page=' . $total_pages . '" style="padding: 10px 16px; background: #fff; border: 2px solid #ddd; border-radius: 6px; text-decoration: none;">' . $total_pages . '</a>';
      }
      
      if ($page < $total_pages) {
        echo '<a href="?page=' . ($page + 1) . '" style="padding: 10px 16px; background: var(--primary-blue); color: #fff; border-radius: 6px; text-decoration: none; font-weight: 600;">Next →</a>';
      }
      ?>
    </div>
    <p style="color: #666; font-size: 14px;">
      Halaman <strong><?php echo $page; ?></strong> dari <strong><?php echo $total_pages; ?></strong> 
      (Total: <strong><?php echo $total_items; ?></strong> fasilitas)
    </p>
  </div>
</section>
<?php endif; ?>

<?php if (count($fasilitasByKategori) === 0): ?>
<section class="section">
  <div class="container">
    <div style="text-align: center; padding: 60px 20px; color: #999;">
      <p style="font-size: 18px;">📦 Belum ada data fasilitas yang ditampilkan.</p>
    </div>
  </div>
</section>
<?php endif; ?>

<?php include 'footer.php'; ?>