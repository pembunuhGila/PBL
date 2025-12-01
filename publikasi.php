<?php
// publikasi.php - Halaman Publikasi
$activePage = 'publikasi';
include 'navbar.php';
?>
<link rel="stylesheet" href="assets/css/style.css">
<link rel="stylesheet" href="assets/css/publikasi.css">

<!-- HERO SECTION -->
<div class="page-hero" style="background-image: url('assets/img/tentang-kami.jpeg');">
  <div class="page-hero-overlay">
    <div class="container">
      <h1 class="page-title">Publikasi</h1>
      <p class="page-subtitle">Publikasi dari anggota Lab Data Technology</p>
    </div>
  </div>
</div>

<!-- PUBLIKASI SECTION -->
<section class="section publikasi-section">
  <div class="container">
    
    <!-- Header dengan Button -->
    <div class="publikasi-header">
      <h2 class="section-title" style="margin: 0; text-align: left;">Publikasi</h2>
      <a href="https://sinta.kemdikbud.go.id/" target="_blank" class="sinta-btn">
        Load more in sinta
      </a>
    </div>
    
    <p class="publikasi-subtitle">Publikasi dari anggota Lab Data Technology</p>
    
    <!-- PUBLIKASI GRID -->
    <div class="publikasi-grid">
      
      <!-- Item 1 -->
      <article class="publikasi-card">
        <div class="publikasi-cover">
          <div class="cover-placeholder">Cover Publikasi</div>
        </div>
        <div class="publikasi-body">
          <h3 class="publikasi-title">
            <a href="publikasi_detail.php?id=1">
              Sistem Prediksi Penjualan Frozen Food dengan Metode Monte Carlo (Studi Kasus: Supermama Frozen Food)
            </a>
          </h3>
          <p class="publikasi-meta">Penulis • 2025</p>
          <a href="publikasi_detail.php?id=1" class="publikasi-link">
            Baca Selengkapnya >>>
          </a>
        </div>
      </article>
      
      <!-- Item 2 -->
      <article class="publikasi-card">
        <div class="publikasi-cover">
          <div class="cover-placeholder">Cover Publikasi</div>
        </div>
        <div class="publikasi-body">
          <h3 class="publikasi-title">
            <a href="publikasi_detail.php?id=2">
              Analisis Sentimen Media Sosial Menggunakan Deep Learning dan Natural Language Processing
            </a>
          </h3>
          <p class="publikasi-meta">Penulis • 2025</p>
          <a href="publikasi_detail.php?id=2" class="publikasi-link">
            Baca Selengkapnya >>>
          </a>
        </div>
      </article>
      
      <!-- Item 3 -->
      <article class="publikasi-card">
        <div class="publikasi-cover">
          <div class="cover-placeholder">Cover Publikasi</div>
        </div>
        <div class="publikasi-body">
          <h3 class="publikasi-title">
            <a href="publikasi_detail.php?id=3">
              Implementasi Machine Learning untuk Prediksi Kelulusan Mahasiswa Berbasis Data Akademik
            </a>
          </h3>
          <p class="publikasi-meta">Penulis • 2024</p>
          <a href="publikasi_detail.php?id=3" class="publikasi-link">
            Baca Selengkapnya >>>
          </a>
        </div>
      </article>
      
      <!-- Item 4 -->
      <article class="publikasi-card">
        <div class="publikasi-cover">
          <div class="cover-placeholder">Cover Publikasi</div>
        </div>
        <div class="publikasi-body">
          <h3 class="publikasi-title">
            <a href="publikasi_detail.php?id=4">
              Optimasi Algoritma K-Means untuk Segmentasi Pelanggan pada E-Commerce
            </a>
          </h3>
          <p class="publikasi-meta">Penulis • 2024</p>
          <a href="publikasi_detail.php?id=4" class="publikasi-link">
            Baca Selengkapnya >>>
          </a>
        </div>
      </article>
      
      <!-- Item 5 -->
      <article class="publikasi-card">
        <div class="publikasi-cover">
          <div class="cover-placeholder">Cover Publikasi</div>
        </div>
        <div class="publikasi-body">
          <h3 class="publikasi-title">
            <a href="publikasi_detail.php?id=5">
              Penerapan Big Data Analytics untuk Sistem Rekomendasi Produk Menggunakan Collaborative Filtering
            </a>
          </h3>
          <p class="publikasi-meta">Penulis • 2024</p>
          <a href="publikasi_detail.php?id=5" class="publikasi-link">
            Baca Selengkapnya >>>
          </a>
        </div>
      </article>
      
      <!-- Item 6 -->
      <article class="publikasi-card">
        <div class="publikasi-cover">
          <div class="cover-placeholder">Cover Publikasi</div>
        </div>
        <div class="publikasi-body">
          <h3 class="publikasi-title">
            <a href="publikasi_detail.php?id=6">
              Deteksi Fraud Transaksi Keuangan Menggunakan Random Forest dan Neural Network
            </a>
          </h3>
          <p class="publikasi-meta">Penulis • 2023</p>
          <a href="publikasi_detail.php?id=6" class="publikasi-link">
            Baca Selengkapnya >>>
          </a>
        </div>
      </article>
      
    </div>
    
    <!-- Load More Button -->
    <div class="publikasi-footer">
      <button class="load-more-btn" id="loadMoreBtn">
        Load more
      </button>
    </div>
    
  </div>
</section>

<?php include 'footer.php'; ?>

<script>
// Load More Button Interaction
document.getElementById('loadMoreBtn').addEventListener('click', function() {
  alert('Load more akan diimplementasikan dengan AJAX untuk load publikasi berikutnya');
});
</script>