<?php
// index.php - Halaman Beranda Lab Data Technology
$activePage = 'index';
include 'conn.php'; // Koneksi database
include 'navbar.php'; // Navbar

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
    LIMIT 2
");
$publikasi_list = $stmt_publikasi->fetchAll();

// Ambil galeri untuk kegiatan
$stmt_galeri = $pdo->query("SELECT * FROM galeri WHERE status = 'active' ORDER BY created_at DESC LIMIT 4");
$galeri_list = $stmt_galeri->fetchAll();

// Ambil mata kuliah terkait
$stmt_matakuliah = $pdo->query("
    SELECT DISTINCT jsonb_array_elements(bidang_keahlian::jsonb)->>'nama' as nama_mk
    FROM anggota_lab 
    WHERE status = 'active' 
    AND bidang_keahlian IS NOT NULL
    LIMIT 4
");
$matakuliah_list = $stmt_matakuliah->fetchAll();
?>
<link rel="stylesheet" href="assets/css/style.css">

<!-- ============================================
     HERO / SLIDER SECTION
============================================= -->
<div class="slider-wrapper">
  <section class="hero">
    <?php if(count($sliders) > 0): ?>
      <!-- SLIDER REAL DARI DATABASE -->
      <div class="gallery-slider">
        <?php foreach($sliders as $index => $slide): ?>
          <div class="slide <?= $index === 0 ? 'active' : '' ?>">
            <?php 
              $slider_path = '';
              if ($slide['gambar']) {
                $possible_paths = [
                  'uploads/slider/' . htmlspecialchars($slide['gambar']),
                  'uploads/' . htmlspecialchars($slide['gambar']),
                  'assets/img/slider/' . htmlspecialchars($slide['gambar'])
                ];
                foreach ($possible_paths as $path) {
                  if (file_exists($path)) {
                    $slider_path = $path;
                    break;
                  }
                }
              }
            ?>
            
            <?php if (!empty($slider_path)): ?>
              <img 
                src="<?= $slider_path ?>" 
                alt="<?= htmlspecialchars($slide['judul']) ?>"
                loading="lazy"
                style="width: 100%; height: 100%; object-fit: cover;">
            <?php else: ?>
              <div style="width: 100%; height: 100%; background: linear-gradient(135deg, #d8d8d8, #c5c5c5); display: flex; align-items: center; justify-content: center; color: #999; font-weight: 600;">
                📸 Slider Image
              </div>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
        
        <!-- SLIDER NAVIGATION BUTTONS -->
        <button class="slider-btn slider-btn-prev" aria-label="Previous slide">
          <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
            <polyline points="15 18 9 12 15 6"></polyline>
          </svg>
        </button>
        
        <button class="slider-btn slider-btn-next" aria-label="Next slide">
          <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
            <polyline points="9 18 15 12 9 6"></polyline>
          </svg>
        </button>
        
        <!-- Slider Navigation Dots -->
        <div class="slider-dots">
          <?php foreach($sliders as $index => $slide): ?>
            <span class="<?= $index === 0 ? 'active' : '' ?>" data-slide="<?= $index ?>"></span>
          <?php endforeach; ?>
        </div>
      </div>
    <?php else: ?>
      <div class="gallery-placeholder">
        Galeri Slider (Belum ada data)
        <div class="slider-dots">
          <span class="active"></span>
          <span></span>
          <span></span>
        </div>
      </div>
    <?php endif; ?>
  </section>
</div>

<!-- ============================================
     TENTANG KAMI SECTION (Overlay)
============================================= -->
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

<!-- ============================================
     FASILITAS & PERALATAN
============================================= -->
<section class="section">
  <div class="container">
    <h2 class="section-title">Fasilitas & Peralatan</h2>
    
    <div class="grid-3">
      <?php if(count($fasilitas_kategori) > 0): ?>
        <?php foreach($fasilitas_kategori as $kategori): ?>
          <a href="fasilitas.php?kategori=<?= urlencode($kategori['kategori_fasilitas']) ?>" class="facility-card">
            <div class="icon-placeholder">Icon</div>
            <h4><?= htmlspecialchars($kategori['kategori_fasilitas']) ?></h4>
            <p>Lihat detail fasilitas <?= htmlspecialchars($kategori['kategori_fasilitas']) ?></p>
          </a>
        <?php endforeach; ?>
      <?php else: ?>
        <a href="fasilitas.php?kategori=Ruang Praktikum & Penelitian" class="facility-card">
          <div class="icon-placeholder">Icon</div>
          <h4>Ruang Praktikum & Penelitian</h4>
          <p>Ruang laboratorium yang nyaman untuk kegiatan praktikum, eksperimen, dan penelitian.</p>
        </a>
        
        <a href="fasilitas.php?kategori=Perangkat Lunak" class="facility-card">
          <div class="icon-placeholder">Icon</div>
          <h4>Perangkat Lunak</h4>
          <p>Software analisis data, machine learning, serta tools big data untuk kebutuhan riset dan pembelajaran.</p>
        </a>
        
        <a href="fasilitas.php?kategori=Perangkat Komputer" class="facility-card">
          <div class="icon-placeholder">Icon</div>
          <h4>Perangkat Komputer</h4>
          <p>Software analisis data, machine learning, serta tools big data untuk kebutuhan riset dan pembelajaran.</p>
        </a>
      <?php endif; ?>
    </div>
  </div>
</section>

<!-- ============================================
     BERITA SECTION - IMPROVED & MODERN
============================================= -->
<section class="news-section">
  <div class="container">
    <h2 class="section-title">Berita</h2>
    <p>Kabar terbaru dan aktivitas kampus terkini</p>
    
    <?php if(count($berita_list) > 0): ?>
      <div class="news-grid">
        <?php foreach($berita_list as $berita): ?>
          <a href="konten_detail.php?id=<?= (int)$berita['id_konten'] ?>" class="news-card">
            <div class="thumb">
              <?php if($berita['gambar'] && file_exists("uploads/konten/" . $berita['gambar'])): ?>
                <img src="uploads/konten/<?= htmlspecialchars($berita['gambar']) ?>" 
                     alt="<?= htmlspecialchars($berita['judul']) ?>">
              <?php else: ?>
                <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #d8d8d8, #c5c5c5); color: #999; font-size: 14px; font-weight: 600;">
                  📰 Thumbnail Berita
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
        <p>Belum ada berita terbaru</p>
      </div>
    <?php endif; ?>
    
    <div style="text-align: center; margin-top: 40px;">
      <a href="konten.php?kategori=Berita" class="load-more-btn">Lihat Semua Berita →</a>
    </div>
  </div>
</section>

<!-- ============================================
     PUBLIKASI SECTION
============================================= -->
<section class="section publikasi-section">
  <div class="container">
    <div class="publikasi-header">
      <h2 class="section-title" style="margin: 0;">Publikasi</h2>
      <a href="https://sinta.kemdikbud.go.id/" target="_blank" class="sinta-btn">Load more in sinta</a>
    </div>
    <p style="text-align: center; color: #666; margin-top: -20px; margin-bottom: 32px;">Publikasi dari anggota Lab Data Technology</p>

    <div class="pub-row">
      <?php if(count($publikasi_list) > 0): ?>
        <?php foreach($publikasi_list as $pub): ?>
          <div class="pub-card">
            <?php if($pub['cover'] && file_exists("uploads/publikasi/cover/" . $pub['cover'])): ?>
              <img src="uploads/publikasi/cover/<?= htmlspecialchars($pub['cover']) ?>" alt="<?= htmlspecialchars($pub['judul']) ?>" class="pub-placeholder" style="width: 100%; height: 250px; object-fit: cover;">
            <?php else: ?>
              <div class="pub-placeholder">Cover Publikasi</div>
            <?php endif; ?>
            <div class="pub-card-inner">
              <h4><?= htmlspecialchars($pub['judul']) ?></h4>
              <p class="pub-meta"><?= htmlspecialchars($pub['penulis'] ?? 'Penulis') ?> • <?= htmlspecialchars($pub['tahun']) ?></p>
              <?php if($pub['link_shinta']): ?>
                <a href="<?= htmlspecialchars($pub['link_shinta']) ?>" target="_blank" class="pub-link">Baca Selengkapnya >>></a>
              <?php elseif($pub['file_path'] && file_exists("uploads/publikasi/" . $pub['file_path'])): ?>
                <a href="uploads/publikasi/<?= htmlspecialchars($pub['file_path']) ?>" target="_blank" class="pub-link">Baca Selengkapnya >>></a>
              <?php else: ?>
                <span class="pub-link text-muted">Tidak ada link tersedia</span>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="pub-card">
          <div class="pub-placeholder">Cover Publikasi</div>
          <div class="pub-card-inner">
            <h4>Sistem Prediksi Penjualan Frozen Food dengan Metode Monte Carlo</h4>
            <p class="pub-meta">Penulis • 2025</p>
            <a href="#" class="pub-link">Baca Selengkapnya >>></a>
          </div>
        </div>
      <?php endif; ?>
    </div>

    <div style="text-align: center; margin-top: 40px;">
      <a href="publikasi.php" class="load-more-btn" style="background: var(--primary-blue); border-color: var(--primary-blue);">Load more</a>
    </div>
  </div>
</section>

<!-- ============================================
     KEGIATAN & PROYEK
============================================= -->
<section class="section">
  <div class="container">
    <h2 class="section-title">Kegiatan & Proyek</h2>
    
    <div class="grid-4">
      <?php if(count($galeri_list) > 0): ?>
        <?php foreach($galeri_list as $galeri): ?>
          <div class="activity-card">
            <?php if($galeri['gambar'] && file_exists("uploads/galeri/" . $galeri['gambar'])): ?>
              <img src="uploads/galeri/<?= htmlspecialchars($galeri['gambar']) ?>" alt="<?= htmlspecialchars($galeri['judul']) ?>" class="activity-placeholder" style="width: 100%; height: 200px; object-fit: cover;">
            <?php else: ?>
              <div class="activity-placeholder">Foto Kegiatan</div>
            <?php endif; ?>
            <div class="activity-card-content">
              <h4><?= htmlspecialchars($galeri['judul']) ?></h4>
              <p><?= htmlspecialchars(substr($galeri['deskripsi'], 0, 80)) ?><?= strlen($galeri['deskripsi']) > 80 ? '...' : '' ?></p>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="activity-card">
          <div class="activity-placeholder">Foto Kegiatan</div>
          <div class="activity-card-content">
            <h4>Praktikum Mahasiswa</h4>
            <p>Kegiatan pembelajaran berbasis praktik untuk mempertajam kemampuan teknikal siswa.</p>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </div>
</section>

<!-- ============================================
     PERKULIAHAN TERKAIT
============================================= -->
<section class="section" style="padding-top: 0;">
  <div class="container">
    <h2 class="section-title">Perkuliahan terkait</h2>
    <div class="grid-4">
      <?php if(count($matakuliah_list) > 0): ?>
        <?php foreach($matakuliah_list as $mk): ?>
          <?php if(!empty($mk['nama_mk'])): ?>
          <div class="activity-card">
            <div class="activity-placeholder">Icon Matakuliah</div>
            <div class="activity-card-content">
              <h4><?= htmlspecialchars($mk['nama_mk']) ?></h4>
              <p>Mata kuliah yang diampu oleh dosen Lab Data Technology</p>
            </div>
          </div>
          <?php endif; ?>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="activity-card">
          <div class="activity-placeholder">Icon Matakuliah</div>
          <div class="activity-card-content">
            <h4>Basis Data</h4>
            <p>Perancangan, Implementasi, dan pengaturan sistem basis data</p>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </div>
</section>

<?php include 'footer.php'; ?>

<!-- ============================================
     SLIDER JAVASCRIPT - INLINE
============================================= -->
<script>
class SliderController {
  constructor() {
    this.slides = document.querySelectorAll('.gallery-slider .slide');
    this.dots = document.querySelectorAll('.slider-dots span');
    this.btnPrev = document.querySelector('.slider-btn-prev');
    this.btnNext = document.querySelector('.slider-btn-next');
    this.currentIndex = 0;
    this.autoplayInterval = null;
    
    this.init();
  }

  init() {
    // Auto-play slider setiap 5 detik
    this.startAutoplay();

    // Event listener untuk dots
    this.dots.forEach((dot, index) => {
      dot.addEventListener('click', () => this.goToSlide(index));
    });

    // Event listener untuk navigation buttons
    if (this.btnPrev) {
      this.btnPrev.addEventListener('click', () => this.prevSlide());
    }
    if (this.btnNext) {
      this.btnNext.addEventListener('click', () => this.nextSlide());
    }

    // Keyboard navigation
    document.addEventListener('keydown', (e) => {
      if (e.key === 'ArrowLeft') this.prevSlide();
      if (e.key === 'ArrowRight') this.nextSlide();
    });

    // Touch support untuk mobile
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

    // Update slides visibility
    this.slides.forEach((slide, i) => {
      slide.classList.remove('active');
      if (i === this.currentIndex) {
        slide.classList.add('active');
      }
    });

    // Update dots
    this.dots.forEach((dot, i) => {
      dot.classList.remove('active');
      if (i === this.currentIndex) {
        dot.classList.add('active');
      }
    });

    // Reset autoplay
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

    const handleSwipe = () => {
      if (touchEndX < touchStartX - 50) {
        this.nextSlide(); // Swipe ke kiri = next
      }
      if (touchEndX > touchStartX + 50) {
        this.prevSlide(); // Swipe ke kanan = prev
      }
    };

    this.handleSwipe = handleSwipe;
  }
}

// Initialize slider ketika DOM ready
document.addEventListener('DOMContentLoaded', () => {
  new SliderController();
});
</script>