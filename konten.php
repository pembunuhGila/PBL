<?php
// kontak.php - Halaman konten Lab Data Technology 
$activePage = 'konten';
include 'conn.php'; // Koneksi database
include 'navbar.php'; // Navbar
?>

<link rel="stylesheet" href="assets/css/style.css">
<link rel="stylesheet" href="assets/css/konten.css">

<!-- HERO -->
<div class="page-hero" style="background-image: url('assets/img/tentang-kami.jpeg');">
  <div class="page-hero-overlay">
    <div class="container">
      <h1 class="page-title">Konten</h1>
      <p class="page-subtitle">Berita, Pengumuman, dan Agenda Lab Data Technology</p>
    </div>
  </div>
</div>

<!-- CONTENT -->
<section class="section konten-section">
  <div class="container">
    <div class="konten-layout">
      
      <!-- SIDEBAR -->
      <aside class="konten-sidebar">

        <!-- SEARCH -->
        <div class="sidebar-widget search-widget">
          <form class="search-form">
            <input type="text" placeholder="Cari berita..." class="search-input">
            <button type="button" class="search-btn">
              🔍
            </button>
          </form>
        </div>

        <!-- KATEGORI -->
        <div class="sidebar-widget kategori-widget">
          <h3 class="widget-title">Kategori</h3>
          <ul class="kategori-list">
            <li><a href="#" class="kategori-link active">Semua Konten <span class="kategori-count">12</span></a></li>
            <li><a href="#" class="kategori-link">Berita <span class="kategori-count">4</span></a></li>
            <li><a href="#" class="kategori-link">Pengumuman <span class="kategori-count">5</span></a></li>
            <li><a href="#" class="kategori-link">Agenda <span class="kategori-count">3</span></a></li>
          </ul>
        </div>

        <!-- TERBARU -->
        <div class="sidebar-widget recent-widget">
          <h3 class="widget-title">Terbaru</h3>
          <ul class="recent-list">

            <li class="recent-item">
              <div class="recent-thumb">
                <img src="https://via.placeholder.com/80" alt="">
              </div>
              <div class="recent-info">
                <h4><a href="#">Judul Berita Terbaru 1</a></h4>
                <span class="recent-date">12 Jan 2025</span>
              </div>
            </li>

            <li class="recent-item">
              <div class="recent-thumb">
                <img src="https://via.placeholder.com/80" alt="">
              </div>
              <div class="recent-info">
                <h4><a href="#">Judul Pengumuman Penting</a></h4>
                <span class="recent-date">10 Jan 2025</span>
              </div>
            </li>

            <li class="recent-item">
              <div class="recent-thumb">
                <img src="https://via.placeholder.com/80" alt="">
              </div>
              <div class="recent-info">
                <h4><a href="#">Agenda Workshop</a></h4>
                <span class="recent-date">5 Jan 2025</span>
              </div>
            </li>

          </ul>
        </div>

      </aside>

      <!-- MAIN -->
      <main class="konten-main">

        <div class="konten-header">
          <h2 class="konten-main-title">Semua Konten Terbaru</h2>
        </div>

        <div class="konten-grid">

          <!-- Item 1 -->
          <article class="konten-card">
            <div class="konten-thumb">
              <img src="https://via.placeholder.com/350x200" alt="">
              <span class="konten-badge badge-berita">Berita</span>
            </div>
            <div class="konten-body">
              <h3 class="konten-title"><a href="#">Judul Berita Placeholder</a></h3>
              <p class="konten-excerpt">Ini adalah contoh deskripsi singkat konten sebagai placeholder...</p>
              <div class="konten-meta">
                <span class="meta-date">12 Jan 2025</span>
                <a href="#" class="read-more">Baca Selengkapnya →</a>
              </div>
            </div>
          </article>

          <!-- Item 2 -->
          <article class="konten-card">
            <div class="konten-thumb">
              <img src="https://via.placeholder.com/350x200" alt="">
              <span class="konten-badge badge-pengumuman">Pengumuman</span>
            </div>
            <div class="konten-body">
              <h3 class="konten-title"><a href="#">Pengumuman Placeholder</a></h3>
              <p class="konten-excerpt">Pengumuman penting mengenai kegiatan laboratorium...</p>
              <div class="konten-meta">
                <span class="meta-date">10 Jan 2025</span>
                <a href="#" class="read-more">Baca Selengkapnya →</a>
              </div>
            </div>
          </article>

          <!-- Item 3 -->
          <article class="konten-card">
            <div class="konten-thumb">
              <img src="https://via.placeholder.com/350x200" alt="">
              <span class="konten-badge badge-agenda">Agenda</span>
            </div>
            <div class="konten-body">
              <h3 class="konten-title"><a href="#">Agenda Placeholder</a></h3>
              <p class="konten-excerpt">Sebuah agenda workshop atau seminar yang akan datang...</p>
              <div class="konten-meta">
                <span class="meta-date">5 Jan 2025</span>
                <a href="#" class="read-more">Baca Selengkapnya →</a>
              </div>
            </div>
          </article>

        </div>

        <!-- Pagination Dummy -->
        <div class="pagination">
          <a href="#" class="page-btn prev">← Previous</a>
          <span class="page-info">1 - 6 of 12</span>
          <a href="#" class="page-btn next">Next →</a>
        </div>

      </main>

    </div>
  </div>
</section>
<?php include 'footer.php'; ?>