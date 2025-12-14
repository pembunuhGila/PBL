<?php
// index.php - Halaman Beranda Lab Data Technology (Cleaned Version)
$activePage = 'index';
include 'conn.php';
include 'navbar.php';

// Ambil data slider
$stmt_slider = $pdo->query("SELECT * FROM slider WHERE status = 'active' ORDER BY urutan, tanggal_dibuat DESC LIMIT 5");
$sliders = $stmt_slider->fetchAll();

// Ambil kategori fasilitas yang unik
$stmt_fasilitas = $pdo->query("SELECT DISTINCT kategori_fasilitas FROM fasilitas WHERE status = 'active' AND kategori_fasilitas IS NOT NULL ORDER BY kategori_fasilitas LIMIT 3");
$fasilitas_kategori = $stmt_fasilitas->fetchAll();

// Ambil berita terbaru
$stmt_berita = $pdo->query("SELECT * FROM konten WHERE status = 'active' AND kategori_konten = 'Berita' ORDER BY tanggal_posting DESC LIMIT 2");
$berita_list = $stmt_berita->fetchAll();

// Ambil publikasi terbaru
$stmt_publikasi = $pdo->query("
    SELECT p.*, 
           STRING_AGG(a.nama, ', ' ORDER BY pa.urutan_penulis) as penulis
    FROM publikasi p
    LEFT JOIN publikasi_anggota pa ON p.id_publikasi = pa.id_publikasi
    LEFT JOIN anggota_lab a ON pa.id_anggota = a.id_anggota
    WHERE p.status = 'active'
    GROUP BY p.id_publikasi
    ORDER BY p.created_at DESC
    LIMIT 3
");
$publikasi_list = $stmt_publikasi->fetchAll();

// Ambil galeri untuk kegiatan
$stmt_galeri = $pdo->query("SELECT * FROM galeri WHERE status = 'active' ORDER BY created_at DESC LIMIT 4");
$galeri_list = $stmt_galeri->fetchAll();

// Helper function untuk cek path gambar
function check_image_path($filename, $folder) {
    if (empty($filename)) return '';
    
    $possible_paths = [
        "uploads/{$folder}/" . htmlspecialchars($filename),
        'uploads/' . htmlspecialchars($filename),
        "assets/img/{$folder}/" . htmlspecialchars($filename)
    ];
    
    foreach ($possible_paths as $path) {
        if (file_exists($path)) {
            return $path;
        }
    }
    return '';
}
?>
<link rel="stylesheet" href="assets/css/style.css">

<!-- HERO / SLIDER SECTION -->
<div class="slider-wrapper">
  <section class="hero">
    <?php if(count($sliders) > 0): ?>
      <div class="gallery-slider">
        <?php foreach($sliders as $index => $slide): ?>
          <div class="slide <?= $index === 0 ? 'active' : '' ?>">
            <?php 
              $slider_path = check_image_path($slide['gambar'], 'slider');
            ?>
            
            <?php if (!empty($slider_path)): ?>
              <img src="<?= $slider_path ?>" alt="<?= htmlspecialchars($slide['judul']) ?>" loading="lazy">
            <?php else: ?>
              <div class="slide-placeholder"></div>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
        
        <!-- Navigation Buttons -->
        <button class="slider-btn slider-btn-prev" aria-label="Previous">
          <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
            <polyline points="15 18 9 12 15 6"></polyline>
          </svg>
        </button>
        
        <button class="slider-btn slider-btn-next" aria-label="Next">
          <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
            <polyline points="9 18 15 12 9 6"></polyline>
          </svg>
        </button>
        
        <!-- Dots -->
        <div class="slider-dots">
          <?php foreach($sliders as $index => $slide): ?>
            <span class="<?= $index === 0 ? 'active' : '' ?>" data-slide="<?= $index ?>"></span>
          <?php endforeach; ?>
        </div>
      </div>
    <?php else: ?>
      <div class="gallery-placeholder">
        <svg width="80" height="80" fill="none" stroke="#999" stroke-width="2" viewBox="0 0 24 24">
          <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
          <circle cx="8.5" cy="8.5" r="1.5"></circle>
          <polyline points="21 15 16 10 5 21"></polyline>
        </svg>
        <p style="margin-top: 12px;">Slider belum tersedia</p>
        <div class="slider-dots">
          <span class="active"></span>
          <span></span>
          <span></span>
        </div>
      </div>
    <?php endif; ?>
  </section>
</div>

<!-- TENTANG KAMI SECTION -->
<section class="about-section">
  <div class="container">
    <div class="about-overlay">
      <small>Lab DT Overview</small>
      <h2>Tentang Kami</h2>
      <p>Pusat riset yang beroperasi sebagai fasilitas khusus untuk penemuan ilmiah dan analisis.</p>
      <a href="tentang.php" class="read-more-btn">Read More</a>
    </div>
  </div>
</section>

<!-- FASILITAS & PERALATAN -->
<section class="section">
  <div class="container">
    <h2 class="section-title">Fasilitas & Peralatan</h2>
    
    <div class="grid-3">
      <?php if(count($fasilitas_kategori) > 0): ?>
        <?php foreach($fasilitas_kategori as $kategori): ?>
          <a href="fasilitas.php?kategori=<?= urlencode($kategori['kategori_fasilitas']) ?>" class="facility-card">
            <div class="icon-placeholder">
              <svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect>
                <line x1="8" y1="21" x2="16" y2="21"></line>
                <line x1="12" y1="17" x2="12" y2="21"></line>
              </svg>
            </div>
            <h4><?= htmlspecialchars($kategori['kategori_fasilitas']) ?></h4>
            <p>Lihat detail fasilitas dan peralatan</p>
          </a>
        <?php endforeach; ?>
      <?php else: ?>
        <p style="grid-column: 1/-1; text-align: center; color: #999; padding: 40px 0;">
          Data fasilitas belum tersedia
        </p>
      <?php endif; ?>
    </div>
  </div>
</section>

<!-- BERITA SECTION -->
<section class="news-section">
  <div class="container">
    <h2 class="section-title">Berita</h2>
    <p>Kabar terbaru dan aktivitas kampus terkini</p>
    
    <?php if(count($berita_list) > 0): ?>
      <div class="news-grid">
        <?php foreach($berita_list as $berita): ?>
          <a href="konten_detail.php?id=<?= (int)$berita['id_konten'] ?>" class="news-card">
            <div class="thumb">
              <?php 
                $berita_img = check_image_path($berita['gambar'], 'konten');
              ?>
              <?php if($berita_img): ?>
                <img src="<?= $berita_img ?>" alt="<?= htmlspecialchars($berita['judul']) ?>">
              <?php else: ?>
                <div class="thumb-placeholder">
                  <svg width="60" height="60" fill="none" stroke="#999" stroke-width="2" viewBox="0 0 24 24">
                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                    <circle cx="8.5" cy="8.5" r="1.5"></circle>
                    <polyline points="21 15 16 10 5 21"></polyline>
                  </svg>
                </div>
              <?php endif; ?>
            </div>
            
            <div class="news-card-content">
              <span class="news-badge"><?= htmlspecialchars($berita['kategori_konten'] ?? 'Berita') ?></span>
              <h4><?= htmlspecialchars($berita['judul']) ?></h4>
              <p><?= htmlspecialchars(substr(strip_tags($berita['isi']), 0, 150)) ?>...</p>
              <small class="text-muted"><?= date('d F Y', strtotime($berita['tanggal_posting'])) ?></small>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="news-empty">
        <svg width="64" height="64" fill="none" stroke="rgba(255,255,255,0.3)" stroke-width="2" viewBox="0 0 24 24">
          <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
          <polyline points="14 2 14 8 20 8"></polyline>
          <line x1="16" y1="13" x2="8" y2="13"></line>
          <line x1="16" y1="17" x2="8" y2="17"></line>
        </svg>
        <p>Belum ada berita terbaru</p>
      </div>
    <?php endif; ?>
    
    <div style="text-align: center; margin-top: 40px;">
      <a href="konten.php?kategori=Berita" class="load-more-btn">Lihat Semua Berita →</a>
    </div>
  </div>
</section>

<!-- PUBLIKASI SECTION -->
<section class="section publikasi-section">
  <div class="container">
    <div class="publikasi-header">
      <h2 class="section-title" style="margin: 0;">Publikasi</h2>
      <a href="https://sinta.kemdikbud.go.id/" target="_blank" rel="noopener noreferrer" class="sinta-btn">
        View in Sinta
      </a>
    </div>
    <p style="text-align: center; color: #666; margin-top: -20px; margin-bottom: 32px;">
      Publikasi dari anggota Lab Data Technology
    </p>

    <div class="pub-row">
      <?php if(count($publikasi_list) > 0): ?>
        <?php foreach($publikasi_list as $pub): ?>
          <div class="pub-card">
            <?php 
              $cover_img = check_image_path($pub['cover'], 'publikasi/cover');
            ?>
            <?php if($cover_img): ?>
              <img src="<?= $cover_img ?>" alt="<?= htmlspecialchars($pub['judul']) ?>" class="pub-placeholder">
            <?php else: ?>
              <div class="pub-placeholder">
                <svg width="60" height="60" fill="none" stroke="#999" stroke-width="2" viewBox="0 0 24 24">
                  <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                  <polyline points="14 2 14 8 20 8"></polyline>
                </svg>
              </div>
            <?php endif; ?>
            <div class="pub-card-inner">
              <h4><?= htmlspecialchars(substr($pub['judul'], 0, 100)) ?><?= strlen($pub['judul']) > 100 ? '...' : '' ?></h4>
              <p class="pub-meta">
                <?= htmlspecialchars(substr($pub['penulis'] ?? 'Penulis', 0, 50)) ?><?= strlen($pub['penulis'] ?? '') > 50 ? '...' : '' ?> • 
                <?= htmlspecialchars($pub['tahun']) ?>
              </p>
              <a href="publikasi_detail.php?id=<?= $pub['id_publikasi'] ?>" class="pub-link">
                Baca Selengkapnya →
              </a>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <p style="grid-column: 1/-1; text-align: center; color: #999; padding: 40px 0;">
          Data publikasi belum tersedia
        </p>
      <?php endif; ?>
    </div>

    <div style="text-align: center; margin-top: 40px;">
      <a href="publikasi.php" class="load-more-btn" style="background: var(--primary-blue); border-color: var(--primary-blue);">
        Load more
      </a>
    </div>
  </div>
</section>

<!-- KEGIATAN & PROYEK -->
<section class="section">
  <div class="container">
    <h2 class="section-title">Kegiatan & Proyek</h2>
    
    <div class="grid-4">
      <?php if(count($galeri_list) > 0): ?>
        <?php foreach($galeri_list as $galeri): ?>
          <div class="activity-card">
            <?php 
              $galeri_img = check_image_path($galeri['gambar'], 'galeri');
            ?>
            <?php if($galeri_img): ?>
              <img src="<?= $galeri_img ?>" alt="<?= htmlspecialchars($galeri['judul']) ?>" class="activity-placeholder">
            <?php else: ?>
              <div class="activity-placeholder">
                <svg width="60" height="60" fill="none" stroke="#999" stroke-width="2" viewBox="0 0 24 24">
                  <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                  <circle cx="8.5" cy="8.5" r="1.5"></circle>
                  <polyline points="21 15 16 10 5 21"></polyline>
                </svg>
              </div>
            <?php endif; ?>
            <div class="activity-card-content">
              <h4><?= htmlspecialchars($galeri['judul']) ?></h4>
              <p><?= htmlspecialchars(substr($galeri['deskripsi'], 0, 80)) ?><?= strlen($galeri['deskripsi']) > 80 ? '...' : '' ?></p>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <p style="grid-column: 1/-1; text-align: center; color: #999; padding: 40px 0;">
          Data kegiatan belum tersedia
        </p>
      <?php endif; ?>
    </div>
  </div>
</section>

<?php include 'footer.php'; ?>

<!-- SLIDER JAVASCRIPT -->
<script>
class SliderController {
  constructor() {
    this.slides = document.querySelectorAll('.gallery-slider .slide');
    this.dots = document.querySelectorAll('.slider-dots span');
    this.btnPrev = document.querySelector('.slider-btn-prev');
    this.btnNext = document.querySelector('.slider-btn-next');
    this.currentIndex = 0;
    this.autoplayInterval = null;
    
    if (this.slides.length > 0) {
      this.init();
    }
  }

  init() {
    this.startAutoplay();

    this.dots.forEach((dot, index) => {
      dot.addEventListener('click', () => this.goToSlide(index));
    });

    if (this.btnPrev) {
      this.btnPrev.addEventListener('click', () => this.prevSlide());
    }
    if (this.btnNext) {
      this.btnNext.addEventListener('click', () => this.nextSlide());
    }

    document.addEventListener('keydown', (e) => {
      if (e.key === 'ArrowLeft') this.prevSlide();
      if (e.key === 'ArrowRight') this.nextSlide();
    });

    this.addTouchSupport();
  }

  goToSlide(index) {
    if (index < 0) {
      this.currentIndex = this.slides.length - 1;
    } else if (index >= this.slides.length) {
      this.currentIndex = 0;
    } else {
      this.currentIndex = index;
    }

    this.slides.forEach((slide, i) => {
      slide.classList.toggle('active', i === this.currentIndex);
    });

    this.dots.forEach((dot, i) => {
      dot.classList.toggle('active', i === this.currentIndex);
    });

    this.resetAutoplay();
  }

  nextSlide() {
    this.goToSlide(this.currentIndex + 1);
  }

  prevSlide() {
    this.goToSlide(this.currentIndex - 1);
  }

  startAutoplay() {
    this.autoplayInterval = setInterval(() => {
      this.nextSlide();
    }, 5000);
  }

  resetAutoplay() {
    clearInterval(this.autoplayInterval);
    this.startAutoplay();
  }

  addTouchSupport() {
    let touchStartX = 0;
    let touchEndX = 0;

    const slider = document.querySelector('.gallery-slider');
    if (!slider) return;

    slider.addEventListener('touchstart', (e) => {
      touchStartX = e.changedTouches[0].screenX;
    });

    slider.addEventListener('touchend', (e) => {
      touchEndX = e.changedTouches[0].screenX;
      this.handleSwipe();
    });

    this.handleSwipe = () => {
      if (touchEndX < touchStartX - 50) {
        this.nextSlide();
      }
      if (touchEndX > touchStartX + 50) {
        this.prevSlide();
      }
    };
  }
}

document.addEventListener('DOMContentLoaded', () => {
  new SliderController();
});
</script>