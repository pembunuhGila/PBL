<?php
// galeri.php - Halaman Galeri Lab Data Technology
$activePage = 'galeri';
include 'conn.php'; // Koneksi database
include 'navbar.php'; // Navbar
?>
<link rel="stylesheet" href="assets/css/style.css">
<link rel="stylesheet" href="assets/css/galeri.css">

<!-- ============================================
     HERO SECTION GALERI
============================================= -->
<div class="page-hero" style="background-image: url('assets/img/tentang-kami.jpeg');">
  <div class="page-hero-overlay">
    <div class="container">
      <h1 class="page-title">Galeri</h1>
      <p class="page-subtitle">Dokumentasi kegiatan dan suasana Lab Data Technology</p>
    </div>
  </div>
</div>

<!-- ============================================
     GALERI SECTION
============================================= -->
<section class="section galeri-section">
  <div class="container">
    <div class="galeri-intro">
      <p>Koleksi foto dan dokumentasi berbagai kegiatan di Lab Data Technology, mulai dari praktikum, 
      penelitian, seminar, hingga kegiatan kolaborasi dengan industri dan institusi lainnya.</p>
    </div>
    
    <!-- FILTER (Optional - bisa ditambahkan nanti) -->
    <!-- <div class="galeri-filter">
      <button class="filter-btn active" data-filter="all">Semua</button>
      <button class="filter-btn" data-filter="praktikum">Praktikum</button>
      <button class="filter-btn" data-filter="penelitian">Penelitian</button>
      <button class="filter-btn" data-filter="event">Event</button>
    </div> -->
    
    <!-- GALERI GRID -->
    <div class="galeri-grid">
      
      <!-- Item 1 -->
      <div class="galeri-item">
        <div class="galeri-image">
          <div class="galeri-placeholder">Foto Kegiatan</div>
        </div>
        
        <div class="galeri-overlay">
          <div class="galeri-content">
            <h3>Praktikum Machine Learning</h3>
            <p>Mahasiswa sedang melakukan praktikum machine learning menggunakan Python dan TensorFlow</p>
            
            <button class="view-btn" onclick="openLightbox('placeholder', 'Praktikum Machine Learning', 'Mahasiswa sedang melakukan praktikum')">
              <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <circle cx="11" cy="11" r="8"></circle>
                <path d="m21 21-4.35-4.35"></path>
                <line x1="11" y1="8" x2="11" y2="14"></line>
                <line x1="8" y1="11" x2="14" y2="11"></line>
              </svg>
              Lihat Detail
            </button>
          </div>
        </div>
      </div>
      
      <!-- Item 2 -->
      <div class="galeri-item">
        <div class="galeri-image">
          <div class="galeri-placeholder">Foto Kegiatan</div>
        </div>
        <div class="galeri-overlay">
          <div class="galeri-content">
            <h3>Workshop Data Science</h3>
            <p>Workshop data science bersama praktisi industri tentang big data analytics</p>
            <button class="view-btn">
              <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <circle cx="11" cy="11" r="8"></circle>
                <path d="m21 21-4.35-4.35"></path>
                <line x1="11" y1="8" x2="11" y2="14"></line>
                <line x1="8" y1="11" x2="14" y2="11"></line>
              </svg>
              Lihat Detail
            </button>
          </div>
        </div>
      </div>
      
      <!-- Item 3 -->
      <div class="galeri-item">
        <div class="galeri-image">
          <div class="galeri-placeholder">Foto Kegiatan</div>
        </div>
        <div class="galeri-overlay">
          <div class="galeri-content">
            <h3>Penelitian Kolaboratif</h3>
            <p>Tim peneliti lab sedang melakukan riset kolaboratif dengan industri teknologi</p>
            <button class="view-btn">
              <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <circle cx="11" cy="11" r="8"></circle>
                <path d="m21 21-4.35-4.35"></path>
                <line x1="11" y1="8" x2="11" y2="14"></line>
                <line x1="8" y1="11" x2="14" y2="11"></line>
              </svg>
              Lihat Detail
            </button>
          </div>
        </div>
      </div>
      
      <!-- Item 4 -->
      <div class="galeri-item">
        <div class="galeri-image">
          <div class="galeri-placeholder">Foto Kegiatan</div>
        </div>
        <div class="galeri-overlay">
          <div class="galeri-content">
            <h3>Seminar Teknologi AI</h3>
            <p>Seminar nasional tentang perkembangan artificial intelligence di Indonesia</p>
            <button class="view-btn">
              <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <circle cx="11" cy="11" r="8"></circle>
                <path d="m21 21-4.35-4.35"></path>
                <line x1="11" y1="8" x2="11" y2="14"></line>
                <line x1="8" y1="11" x2="14" y2="11"></line>
              </svg>
              Lihat Detail
            </button>
          </div>
        </div>
      </div>
      
      <!-- Item 5 -->
      <div class="galeri-item">
        <div class="galeri-image">
          <div class="galeri-placeholder">Foto Kegiatan</div>
        </div>
        <div class="galeri-overlay">
          <div class="galeri-content">
            <h3>Kompetisi Data Mining</h3>
            <p>Mahasiswa berhasil meraih juara dalam kompetisi data mining tingkat nasional</p>
            <button class="view-btn">
              <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <circle cx="11" cy="11" r="8"></circle>
                <path d="m21 21-4.35-4.35"></path>
                <line x1="11" y1="8" x2="11" y2="14"></line>
                <line x1="8" y1="11" x2="14" y2="11"></line>
              </svg>
              Lihat Detail
            </button>
          </div>
        </div>
      </div>
      
      <!-- Item 6 -->
      <div class="galeri-item">
        <div class="galeri-image">
          <div class="galeri-placeholder">Foto Kegiatan</div>
        </div>
        <div class="galeri-overlay">
          <div class="galeri-content">
            <h3>Kunjungan Industri</h3>
            <p>Kunjungan ke perusahaan teknologi untuk mempelajari implementasi big data</p>
            <button class="view-btn">
              <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <circle cx="11" cy="11" r="8"></circle>
                <path d="m21 21-4.35-4.35"></path>
                <line x1="11" y1="8" x2="11" y2="14"></line>
                <line x1="8" y1="11" x2="14" y2="11"></line>
              </svg>
              Lihat Detail
            </button>
          </div>
        </div>
      </div>
      
      <!-- Item 7-9 -->
      <div class="galeri-item">
        <div class="galeri-image">
          <div class="galeri-placeholder">Foto Kegiatan</div>
        </div>
        <div class="galeri-overlay">
          <div class="galeri-content">
            <h3>Pelatihan Deep Learning</h3>
            <p>Pelatihan intensif deep learning untuk mahasiswa dan dosen</p>
            <button class="view-btn">
              <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <circle cx="11" cy="11" r="8"></circle>
                <path d="m21 21-4.35-4.35"></path>
                <line x1="11" y1="8" x2="11" y2="14"></line>
                <line x1="8" y1="11" x2="14" y2="11"></line>
              </svg>
              Lihat Detail
            </button>
          </div>
        </div>
      </div>
      
      <div class="galeri-item">
        <div class="galeri-image">
          <div class="galeri-placeholder">Foto Kegiatan</div>
        </div>
        <div class="galeri-overlay">
          <div class="galeri-content">
            <h3>Presentasi Tugas Akhir</h3>
            <p>Mahasiswa mempresentasikan hasil penelitian tugas akhir di bidang data science</p>
            <button class="view-btn">
              <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <circle cx="11" cy="11" r="8"></circle>
                <path d="m21 21-4.35-4.35"></path>
                <line x1="11" y1="8" x2="11" y2="14"></line>
                <line x1="8" y1="11" x2="14" y2="11"></line>
              </svg>
              Lihat Detail
            </button>
          </div>
        </div>
      </div>
      
      <div class="galeri-item">
        <div class="galeri-image">
          <div class="galeri-placeholder">Foto Kegiatan</div>
        </div>
        <div class="galeri-overlay">
          <div class="galeri-content">
            <h3>Hackathon Lab DT</h3>
            <p>Event hackathon internal lab dengan tema smart city dan IoT analytics</p>
            <button class="view-btn">
              <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <circle cx="11" cy="11" r="8"></circle>
                <path d="m21 21-4.35-4.35"></path>
                <line x1="11" y1="8" x2="11" y2="14"></line>
                <line x1="8" y1="11" x2="14" y2="11"></line>
              </svg>
              Lihat Detail
            </button>
          </div>
        </div>
      </div>
      
    </div>
    
  </div>
</section>

<!-- ============================================
     LIGHTBOX MODAL
============================================= -->
<div class="lightbox-modal" id="lightboxModal">
  <div class="lightbox-overlay" onclick="closeLightbox()"></div>
  <div class="lightbox-content">
    <button class="lightbox-close" onclick="closeLightbox()">
      <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <line x1="18" y1="6" x2="6" y2="18"></line>
        <line x1="6" y1="6" x2="18" y2="18"></line>
      </svg>
    </button>
    
    <div class="lightbox-image-wrapper">
      <img id="lightboxImage" src="" alt="">
    </div>
    
    <div class="lightbox-info">
      <h3 id="lightboxTitle"></h3>
      <p id="lightboxDesc"></p>
    </div>
    
    <!-- Navigation arrows (optional untuk prev/next) -->
    <!-- <button class="lightbox-nav prev" onclick="navigateLightbox(-1)">‹</button>
    <button class="lightbox-nav next" onclick="navigateLightbox(1)">›</button> -->
  </div>
</div>

<?php include 'footer.php'; ?>

<script>
// ============================================
// LIGHTBOX FUNCTIONS
// ============================================
function openLightbox(foto, judul, deskripsi) {
  const modal = document.getElementById('lightboxModal');
  const image = document.getElementById('lightboxImage');
  const title = document.getElementById('lightboxTitle');
  const desc = document.getElementById('lightboxDesc');
  
  // Set content
  image.src = 'uploads/galeri/' + foto;
  image.alt = judul;
  title.textContent = judul;
  desc.textContent = deskripsi || '';
  
  // Show modal
  modal.classList.add('active');
  document.body.style.overflow = 'hidden';
}

function closeLightbox() {
  const modal = document.getElementById('lightboxModal');
  modal.classList.remove('active');
  document.body.style.overflow = '';
}

// Close on ESC key
document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') {
    closeLightbox();
  }
});


</script>