<?php
// fasilitas.php - Halaman Fasilitas Lab Data Technology
$activePage = 'fasilitas';
include 'conn.php'; // Koneksi database
include 'navbar.php'; // Navbar
?>
<link rel="stylesheet" href="assets/css/style.css">
<link rel="stylesheet" href="assets/css/fasilitas.css">

<!-- ============================================
     HERO SECTION FASILITAS
============================================= -->
<div class="page-hero" style="background-image: url('assets/img/tentang-kami.jpeg');">
  <div class="page-hero-overlay">
    <div class="container">
      <h1 class="page-title">Fasilitas & Peralatan</h1>
      <p class="page-subtitle">Laboratorium modern dengan peralatan terkini untuk mendukung riset dan pembelajaran</p>
    </div>
  </div>
</div>

<!-- ============================================
     KATEGORI 1: RUANG PRAKTIKUM & PENELITIAN
============================================= -->
<section class="section kategori-section">
  <div class="container">
    <div class="kategori-header">
      <div class="kategori-icon">
        <svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
          <line x1="9" y1="9" x2="15" y2="9"></line>
          <line x1="9" y1="15" x2="15" y2="15"></line>
        </svg>
      </div>
      <div>
        <h2 class="kategori-title">Ruang Praktikum & Penelitian</h2>
        <p class="kategori-desc">Ruang laboratorium yang nyaman untuk kegiatan praktikum, eksperimen, dan penelitian</p>
      </div>
    </div>
    
    <div class="fasilitas-grid">
      <?php
      // Query untuk ambil data fasilitas kategori "Ruang"
      // $stmt_ruang = $pdo->prepare("SELECT * FROM fasilitas WHERE kategori = 'Ruang' ORDER BY urutan ASC");
      // $stmt_ruang->execute();
      // $fasilitas_ruang = $stmt_ruang->fetchAll();
      
      // PLACEHOLDER DATA - Ganti dengan loop dari database
      $placeholder_ruang = [
        [
          'nama' => 'Lab Utama Data Technology',
          'gambar' => 'lab-utama.jpg',
          'deskripsi' => 'Ruang praktikum utama dengan kapasitas 40 mahasiswa, dilengkapi AC, proyektor, dan whiteboard interaktif untuk pembelajaran yang efektif.'
        ],
        [
          'nama' => 'Ruang Riset & Kolaborasi',
          'gambar' => 'ruang-riset.jpg',
          'deskripsi' => 'Ruang khusus untuk penelitian dan kolaborasi tim dengan meja diskusi, papan brainstorming, dan koneksi internet berkecepatan tinggi.'
        ],
        [
          'nama' => 'Meeting Room',
          'gambar' => 'meeting-room.jpg',
          'deskripsi' => 'Ruang pertemuan untuk presentasi, seminar, dan diskusi kelompok dengan kapasitas 20 orang dan fasilitas video conference.'
        ],
      ];
      ?>
      
      <?php foreach($placeholder_ruang as $item): ?>
        <div class="fasilitas-card">
          <!-- 🖼️ FOTO FASILITAS: uploads/fasilitas/<?= $item['gambar'] ?> -->
          <div class="fasilitas-image">
            <img src="assets/img/placeholder-facility.jpg" alt="<?= $item['nama'] ?>" 
                 onerror="this.src='assets/img/placeholder-facility.jpg'">
            <div class="image-overlay">
              <svg width="32" height="32" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <circle cx="11" cy="11" r="8"></circle>
                <path d="m21 21-4.35-4.35"></path>
              </svg>
            </div>
          </div>
          
          <div class="fasilitas-content">
            <h3><?= $item['nama'] ?></h3>
            <p><?= $item['deskripsi'] ?></p>
          </div>
        </div>
      <?php endforeach; ?>
      
    </div>
  </div>
</section>

<!-- ============================================
     KATEGORI 2: PERANGKAT LUNAK
============================================= -->
<section class="section kategori-section" style="background: #f8f9fb;">
  <div class="container">
    <div class="kategori-header">
      <div class="kategori-icon">
        <svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <polyline points="16 18 22 12 16 6"></polyline>
          <polyline points="8 6 2 12 8 18"></polyline>
        </svg>
      </div>
      <div>
        <h2 class="kategori-title">Perangkat Lunak</h2>
        <p class="kategori-desc">Software analisis data, machine learning, serta tools big data untuk kebutuhan riset dan pembelajaran</p>
      </div>
    </div>
    
    <div class="fasilitas-grid">
      <?php
      // PLACEHOLDER DATA - Ganti dengan loop dari database
      $placeholder_software = [
        [
          'nama' => 'Python & Jupyter Notebook',
          'gambar' => 'python.jpg',
          'deskripsi' => 'Lingkungan pengembangan Python lengkap dengan Jupyter Notebook, NumPy, Pandas, Scikit-learn untuk analisis data dan machine learning.'
        ],
        [
          'nama' => 'Apache Hadoop & Spark',
          'gambar' => 'hadoop.jpg',
          'deskripsi' => 'Platform big data processing untuk mengelola dan menganalisis data dalam skala besar dengan distributed computing.'
        ],
        [
          'nama' => 'Tableau & Power BI',
          'gambar' => 'tableau.jpg',
          'deskripsi' => 'Tools visualisasi data interaktif untuk membuat dashboard dan laporan yang menarik dan mudah dipahami.'
        ],
        [
          'nama' => 'TensorFlow & PyTorch',
          'gambar' => 'tensorflow.jpg',
          'deskripsi' => 'Framework deep learning untuk pengembangan model AI dan neural networks dalam berbagai aplikasi.'
        ],
        [
          'nama' => 'MySQL & PostgreSQL',
          'gambar' => 'database.jpg',
          'deskripsi' => 'Sistem manajemen database relasional untuk penyimpanan dan pengelolaan data terstruktur.'
        ],
        [
          'nama' => 'RapidMiner & KNIME',
          'gambar' => 'rapidminer.jpg',
          'deskripsi' => 'Platform data science visual untuk pemodelan prediktif, data mining, dan analisis tanpa coding kompleks.'
        ],
      ];
      ?>
      
      <?php foreach($placeholder_software as $item): ?>
        <div class="fasilitas-card">
          <div class="fasilitas-image">
            <img src="assets/img/placeholder-software.jpg" alt="<?= $item['nama'] ?>"
                 onerror="this.src='assets/img/placeholder-software.jpg'">
            <div class="image-overlay">
              <svg width="32" height="32" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <circle cx="11" cy="11" r="8"></circle>
                <path d="m21 21-4.35-4.35"></path>
              </svg>
            </div>
          </div>
          
          <div class="fasilitas-content">
            <h3><?= $item['nama'] ?></h3>
            <p><?= $item['deskripsi'] ?></p>
          </div>
        </div>
      <?php endforeach; ?>
      
    </div>
  </div>
</section>

<!-- ============================================
     KATEGORI 3: PERANGKAT KOMPUTER
============================================= -->
<section class="section kategori-section">
  <div class="container">
    <div class="kategori-header">
      <div class="kategori-icon">
        <svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect>
          <line x1="8" y1="21" x2="16" y2="21"></line>
          <line x1="12" y1="17" x2="12" y2="21"></line>
        </svg>
      </div>
      <div>
        <h2 class="kategori-title">Perangkat Komputer</h2>
        <p class="kategori-desc">Dilengkapi perangkat komputer berperforma tinggi untuk mendukung praktikum dan penelitian data</p>
      </div>
    </div>
    
    <div class="fasilitas-grid">
      <?php
      // PLACEHOLDER DATA - Ganti dengan loop dari database
      $placeholder_hardware = [
        [
          'nama' => 'Workstation Desktop',
          'gambar' => 'desktop.jpg',
          'deskripsi' => '40 unit komputer desktop dengan spesifikasi Intel Core i7, RAM 16GB, SSD 512GB, dan GPU dedicated untuk komputasi intensif.'
        ],
        [
          'nama' => 'Server Cluster',
          'gambar' => 'server.jpg',
          'deskripsi' => 'Server cluster dengan total 128GB RAM dan storage 10TB untuk menjalankan aplikasi big data dan machine learning model training.'
        ],
        [
          'nama' => 'High-Performance GPU',
          'gambar' => 'gpu.jpg',
          'deskripsi' => 'Kartu grafis NVIDIA RTX series untuk akselerasi training deep learning model dan komputasi paralel.'
        ],
        [
          'nama' => 'Network Infrastructure',
          'gambar' => 'network.jpg',
          'deskripsi' => 'Jaringan gigabit ethernet dengan koneksi internet 100 Mbps dan switch managed untuk performa optimal.'
        ],
      ];
      ?>
      
      <?php foreach($placeholder_hardware as $item): ?>
        <div class="fasilitas-card">
          <div class="fasilitas-image">
            <img src="assets/img/placeholder-hardware.jpg" alt="<?= $item['nama'] ?>"
                 onerror="this.src='assets/img/placeholder-hardware.jpg'">
            <div class="image-overlay">
              <svg width="32" height="32" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <circle cx="11" cy="11" r="8"></circle>
                <path d="m21 21-4.35-4.35"></path>
              </svg>
            </div>
          </div>
          
          <div class="fasilitas-content">
            <h3><?= $item['nama'] ?></h3>
            <p><?= $item['deskripsi'] ?></p>
          </div>
        </div>
      <?php endforeach; ?>
      
    </div>
  </div>
</section>

<?php include 'footer.php'; ?>

<script>
// Image lightbox effect (optional)
document.addEventListener('DOMContentLoaded', function() {
  const fasilitasCards = document.querySelectorAll('.fasilitas-card');
  
  fasilitasCards.forEach(card => {
    card.addEventListener('click', function() {
      const img = this.querySelector('.fasilitas-image img');
      if(img && img.src) {
        // Optional: Tambahkan lightbox/modal untuk zoom gambar
        console.log('Clicked:', img.alt);
      }
    });
  });
});
</script>