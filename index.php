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

// Ambil galeri untuk kegiatan (filter atau random)
$stmt_galeri = $pdo->query("SELECT * FROM galeri WHERE status = 'active' ORDER BY created_at DESC LIMIT 4");
$galeri_list = $stmt_galeri->fetchAll();

// Ambil mata kuliah terkait dari anggota lab (unique)
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
<script src="assets/js/main.js" defer></script>

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
            <img src="uploads/slider/<?= htmlspecialchars($slide['gambar']) ?>" 
                 alt="<?= htmlspecialchars($slide['judul']) ?>">
            
            <?php if($slide['judul'] || $slide['deskripsi']): ?>
              <div class="slide-caption">
                <?php if($slide['judul']): ?>
                  <h2><?= htmlspecialchars($slide['judul']) ?></h2>
                <?php endif; ?>
                <?php if($slide['deskripsi']): ?>
                  <p><?= htmlspecialchars($slide['deskripsi']) ?></p>
                <?php endif; ?>
              </div>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
        
        <!-- Slider Navigation Dots -->
        <div class="slider-dots">
          <?php foreach($sliders as $index => $slide): ?>
            <span class="<?= $index === 0 ? 'active' : '' ?>" data-slide="<?= $index ?>"></span>
          <?php endforeach; ?>
        </div>
      </div>
    <?php else: ?>
      <!-- PLACEHOLDER jika belum ada data slider -->
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
          <a href="fasilitas.php?kategori=<?= urlencode($kategori['kategori_fasilitas']) ?>" class="facility-card" style="text-decoration: none; color: inherit; cursor: pointer; transition: transform 0.3s;">
            <div class="icon-placeholder">Icon</div>
            <h4><?= htmlspecialchars($kategori['kategori_fasilitas']) ?></h4>
            <p>Lihat detail fasilitas <?= htmlspecialchars($kategori['kategori_fasilitas']) ?></p>
          </a>
        <?php endforeach; ?>
      <?php else: ?>
        <!-- PLACEHOLDER jika belum ada data -->
        <a href="fasilitas.php?kategori=Ruang Praktikum & Penelitian" class="facility-card" style="text-decoration: none; color: inherit;">
          <div class="icon-placeholder">Icon</div>
          <h4>Ruang Praktikum & Penelitian</h4>
          <p>Ruang laboratorium yang nyaman untuk kegiatan praktikum, eksperimen, dan penelitian.</p>
        </a>
        
        <a href="fasilitas.php?kategori=Perangkat Lunak" class="facility-card" style="text-decoration: none; color: inherit;">
          <div class="icon-placeholder">Icon</div>
          <h4>Perangkat Lunak</h4>
          <p>Software analisis data, machine learning, serta tools big data untuk kebutuhan riset dan pembelajaran.</p>
        </a>
        
        <a href="fasilitas.php?kategori=Perangkat Komputer" class="facility-card" style="text-decoration: none; color: inherit;">
          <div class="icon-placeholder">Icon</div>
          <h4>Perangkat Komputer</h4>
          <p>Software analisis data, machine learning, serta tools big data untuk kebutuhan riset dan pembelajaran.</p>
        </a>
      <?php endif; ?>
    </div>
  </div>
</section>

<!-- ============================================
     BERITA SECTION
============================================= -->
<section class="news-section">
  <div class="container">
    <h2 class="section-title">Berita</h2>
    <p style="opacity: 0.85; margin-top: -20px; margin-bottom: 24px;">Kabar terbaru dan aktivitas kampus terkini</p>
    
    <div class="news-grid">
      <?php if(count($berita_list) > 0): ?>
        <?php foreach($berita_list as $berita): ?>
          <div class="news-card">
            <?php if($berita['gambar']): ?>
              <img src="uploads/konten/<?= htmlspecialchars($berita['gambar']) ?>" alt="<?= htmlspecialchars($berita['judul']) ?>" class="thumb" style="width: 100%; height: 200px; object-fit: cover;">
            <?php else: ?>
              <div class="thumb">Thumbnail Berita</div>
            <?php endif; ?>
            <div class="news-card-content">
              <h4><?= htmlspecialchars($berita['judul']) ?></h4>
              <p><?= htmlspecialchars(substr(strip_tags($berita['isi']), 0, 120)) ?>...</p>
              <small class="text-muted"><?= date('d F Y', strtotime($berita['tanggal_posting'])) ?></small>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <!-- PLACEHOLDER jika belum ada data -->
        <div class="news-card">
          <div class="thumb">Thumbnail Berita</div>
          <div class="news-card-content">
            <h4>Prestasi Gemilang! Mahasiswa Prodi D4 Sistem Informasi Bisnis Juara</h4>
            <p>Mahasiswa Prodi D4 Sistem Informasi Bisnis Juara di Entrepreneurs Festival 2025 Politeknik Negeri Malang.</p>
          </div>
        </div>
        
        <div class="news-card">
          <div class="thumb">Thumbnail Berita</div>
          <div class="news-card-content">
            <h4>Prestasi Gemilang! Mahasiswa Prodi D4 Sistem Informasi Bisnis Juara</h4>
            <p>Mahasiswa Prodi D4 Sistem Informasi Bisnis Juara di Entrepreneurs Festival 2025 Politeknik Negeri Malang.</p>
          </div>
        </div>
      <?php endif; ?>
    </div>
    
    <div style="text-align: center;">
      <a href="konten.php?kategori=Berita" class="load-more-btn">Load more</a>
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
            <?php if($pub['cover']): ?>
              <img src="uploads/publikasi/cover/<?= htmlspecialchars($pub['cover']) ?>" alt="<?= htmlspecialchars($pub['judul']) ?>" class="pub-placeholder" style="width: 100%; height: 250px; object-fit: cover;">
            <?php else: ?>
              <div class="pub-placeholder">Cover Publikasi</div>
            <?php endif; ?>
            <div class="pub-card-inner">
              <h4><?= htmlspecialchars($pub['judul']) ?></h4>
              <p class="pub-meta"><?= htmlspecialchars($pub['penulis'] ?? 'Penulis') ?> • <?= htmlspecialchars($pub['tahun']) ?></p>
              <?php if($pub['link_shinta']): ?>
                <a href="<?= htmlspecialchars($pub['link_shinta']) ?>" target="_blank" class="pub-link">Baca Selengkapnya >>></a>
              <?php elseif($pub['file_path']): ?>
                <a href="uploads/publikasi/files/<?= htmlspecialchars($pub['file_path']) ?>" target="_blank" class="pub-link">Baca Selengkapnya >>></a>
              <?php else: ?>
                <span class="pub-link text-muted">Tidak ada link tersedia</span>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <!-- PLACEHOLDER -->
        <div class="pub-card">
          <div class="pub-placeholder">Cover Publikasi</div>
          <div class="pub-card-inner">
            <h4>Sistem Prediksi Penjualan Frozen Food dengan Metode Monte Carlo (Studi Kasus: Supermama Frozen Food)</h4>
            <p class="pub-meta">Penulis • 2025</p>
            <a href="#" class="pub-link">Baca Selengkapnya >>></a>
          </div>
        </div>
        <div class="pub-card">
          <div class="pub-placeholder">Cover Publikasi</div>
          <div class="pub-card-inner">
            <h4>Sistem Prediksi Penjualan Frozen Food dengan Metode Monte Carlo (Studi Kasus: Supermama Frozen Food)</h4>
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
            <img src="uploads/galeri/<?= htmlspecialchars($galeri['gambar']) ?>" alt="<?= htmlspecialchars($galeri['judul']) ?>" class="activity-placeholder" style="width: 100%; height: 200px; object-fit: cover;">
            <div class="activity-card-content">
              <h4><?= htmlspecialchars($galeri['judul']) ?></h4>
              <p><?= htmlspecialchars(substr($galeri['deskripsi'], 0, 80)) ?><?= strlen($galeri['deskripsi']) > 80 ? '...' : '' ?></p>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <!-- PLACEHOLDER -->
        <div class="activity-card">
          <div class="activity-placeholder">Foto Kegiatan</div>
          <div class="activity-card-content">
            <h4>Praktikum Mahasiswa</h4>
            <p>Kegiatan pembelajaran berbasis praktik untuk mempertajam kemampuan teknikal siswa.</p>
          </div>
        </div>
        <div class="activity-card">
          <div class="activity-placeholder">Foto Kegiatan</div>
          <div class="activity-card-content">
            <h4>Praktikum Mahasiswa</h4>
            <p>Kegiatan pembelajaran berbasis praktik untuk mempertajam kemampuan teknikal siswa.</p>
          </div>
        </div>
        <div class="activity-card">
          <div class="activity-placeholder">Foto Kegiatan</div>
          <div class="activity-card-content">
            <h4>Praktikum Mahasiswa</h4>
            <p>Kegiatan pembelajaran berbasis praktik untuk mempertajam kemampuan teknikal siswa.</p>
          </div>
        </div>
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
        <!-- PLACEHOLDER -->
        <div class="activity-card">
          <div class="activity-placeholder">Icon Matakuliah</div>
          <div class="activity-card-content">
            <h4>Basis Data</h4>
            <p>Perancangan, Implementasi, dan pengaturan sistem basis data</p>
          </div>
        </div>
        <div class="activity-card">
          <div class="activity-placeholder">Icon Matakuliah</div>
          <div class="activity-card-content">
            <h4>Basis Data</h4>
            <p>Perancangan, Implementasi, dan pengaturan sistem basis data</p>
          </div>
        </div>
        <div class="activity-card">
          <div class="activity-placeholder">Icon Matakuliah</div>
          <div class="activity-card-content">
            <h4>Basis Data</h4>
            <p>Perancangan, Implementasi, dan pengaturan sistem basis data</p>
          </div>
        </div>
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