<?php
// tentang.php - Halaman Tentang Kami Lab Data Technology
$activePage = 'tentang';
require_once 'conn.php';

// Ambil data profil lab
$stmt_profil = $pdo->query("SELECT * FROM tentang_kami WHERE status = 'active' ORDER BY created_at DESC LIMIT 1");
$profil = $stmt_profil->fetch();

// Ambil data visi
$stmt_visi = $pdo->query("SELECT * FROM visi WHERE status = 'active' ORDER BY urutan ASC LIMIT 1");
$visi = $stmt_visi->fetch();

// Ambil data misi
$stmt_misi = $pdo->query("SELECT * FROM misi WHERE status = 'active' ORDER BY urutan ASC");
$misi_list = $stmt_misi->fetchAll();

// Ambil data sejarah
$stmt_sejarah = $pdo->query("SELECT * FROM sejarah WHERE status = 'active' ORDER BY urutan ASC, tahun ASC");
$sejarah_list = $stmt_sejarah->fetchAll();

// Ambil data struktur lab
$stmt_struktur = $pdo->query("
    SELECT s.*, a.nama, a.nip, a.foto 
    FROM struktur_lab s
    JOIN anggota_lab a ON s.id_anggota = a.id_anggota
    WHERE s.status = 'active' AND a.status = 'active'
    ORDER BY s.urutan ASC
");
$struktur_list = $stmt_struktur->fetchAll();

include 'navbar.php';
?>
<link rel="stylesheet" href="assets/css/style.css">
<link rel="stylesheet" href="assets/css/tentang.css">

<!-- HERO SECTION -->
<div class="page-hero" style="background-image: url('assets/img/tentang-kami.jpeg');">
  <div class="page-hero-overlay">
    <div class="container">
      <h1 class="page-title">Tentang Kami</h1>
      <p class="page-subtitle">Mengenal Lab Data Technology lebih dekat</p>
    </div>
  </div>
</div>

<!-- PROFIL LAB -->
<section class="section profil-section">
  <div class="container">
    <h2 class="section-title">Profil Lab Data Technology</h2>
    <div class="profil-text-content">
      <?php if ($profil): ?>
        <?= nl2br(htmlspecialchars($profil['profil_lab'])) ?>
      <?php else: ?>
        <p>Lab Data Technology merupakan salah satu laboratorium unggulan di Jurusan Teknologi Informasi, Politeknik Negeri Malang yang berfokus pada bidang teknologi data, analitik, dan kecerdasan buatan.</p>
        <p>Laboratorium ini didirikan untuk mendukung kegiatan pembelajaran, penelitian, dan pengabdian masyarakat dalam bidang data science, big data, machine learning, dan teknologi informasi terkini.</p>
      <?php endif; ?>
    </div>
  </div>
</section>

<!-- LOGO & IDENTITAS LAB -->
<section class="section logo-section">
  <div class="container">
    <div class="logo-identity">
      <div class="logo-box">
        <?php if ($profil && $profil['logo_lab']): ?>
          <img src="assets/img/logo/<?= htmlspecialchars($profil['logo_lab']) ?>" alt="Logo Lab Data Technology" class="lab-logo">
        <?php else: ?>
          <img src="assets/img/logo-lab-dt.png" alt="Logo Lab Data Technology" class="lab-logo">
        <?php endif; ?>
      </div>
      
      <div class="logo-description">
        <h2 class="section-title" style="text-align: left; margin-bottom: 20px;">Identitas Lab</h2>
        <h3 style="color: var(--primary-blue); font-size: 22px; margin-bottom: 12px;">Lab Data Technology</h3>
        <?php if ($profil && $profil['penjelasan_logo']): ?>
          <?= nl2br(htmlspecialchars($profil['penjelasan_logo'])) ?>
        <?php else: ?>
          <p style="line-height: 1.8; color: #555; margin-bottom: 16px;">
            Logo Lab Data Technology melambangkan integrasi antara teknologi, data, dan inovasi. 
            Kombinasi warna biru dan hijau merepresentasikan profesionalisme, kepercayaan, dan pertumbuhan dalam bidang teknologi informasi.
          </p>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>

<!-- VISI & MISI -->
<section class="section visi-misi-section">
  <div class="container">
    <h2 class="section-title">Visi & Misi</h2>
    
    <div class="visi-misi-wrapper">
      <!-- VISI -->
      <div class="vm-card visi-card">
        <div class="vm-icon">
          <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
            <circle cx="12" cy="12" r="3"></circle>
          </svg>
        </div>
        <h3>Visi</h3>
        <?php if ($visi): ?>
          <p><?= nl2br(htmlspecialchars($visi['isi_visi'])) ?></p>
        <?php else: ?>
          <p>Menjadi laboratorium teknologi data terkemuka yang menghasilkan lulusan berkualitas, inovatif, dan kompetitif di tingkat nasional maupun internasional pada tahun 2030.</p>
        <?php endif; ?>
      </div>
      
      <!-- MISI -->
      <div class="vm-card misi-card">
        <div class="vm-icon">
          <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
            <polyline points="14 2 14 8 20 8"></polyline>
            <line x1="16" y1="13" x2="8" y2="13"></line>
            <line x1="16" y1="17" x2="8" y2="17"></line>
          </svg>
        </div>
        <h3>Misi</h3>
        <ul class="misi-list">
          <?php if (count($misi_list) > 0): ?>
            <?php foreach($misi_list as $misi): ?>
              <li><?= htmlspecialchars($misi['isi_misi']) ?></li>
            <?php endforeach; ?>
          <?php else: ?>
            <li>Menyelenggarakan pendidikan dan pembelajaran berbasis teknologi data yang berkualitas</li>
            <li>Melaksanakan penelitian dan pengembangan di bidang data science dan big data</li>
            <li>Menjalin kerjasama dengan industri dan institusi untuk meningkatkan kompetensi</li>
            <li>Mengembangkan SDM yang profesional dan beretika dalam bidang teknologi informasi</li>
            <li>Memberikan layanan pengabdian masyarakat melalui penerapan teknologi data</li>
          <?php endif; ?>
        </ul>
      </div>
    </div>
  </div>
</section>

<!-- SEJARAH -->
<section class="section sejarah-section">
  <div class="container">
    <h2 class="section-title">Sejarah Lab Data Technology</h2>
    
    <div class="timeline">
      <?php if (count($sejarah_list) > 0): ?>
        <?php foreach($sejarah_list as $sejarah): ?>
          <div class="timeline-item">
            <div class="timeline-marker"></div>
            <div class="timeline-content">
              <h4><?= htmlspecialchars($sejarah['tahun']) ?></h4>
              <h5><?= htmlspecialchars($sejarah['judul']) ?></h5>
              <p><?= nl2br(htmlspecialchars($sejarah['deskripsi'])) ?></p>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <!-- Default timeline jika belum ada data -->
        <div class="timeline-item">
          <div class="timeline-marker"></div>
          <div class="timeline-content">
            <h4>2015</h4>
            <h5>Pendirian Laboratorium</h5>
            <p>Lab Data Technology didirikan sebagai respons terhadap kebutuhan industri akan tenaga ahli di bidang analisis data dan teknologi informasi.</p>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </div>
</section>

<!-- STRUKTUR ORGANISASI -->
<section class="section struktur-section">
  <div class="container">
    <h2 class="section-title">Struktur Organisasi</h2>
    <p style="text-align: center; color: #666; margin-top: -20px; margin-bottom: 40px;">
      Tim pengelola Lab Data Technology
    </p>
    
    <div class="struktur-grid">
      <?php if (count($struktur_list) > 0): ?>
        <?php foreach($struktur_list as $index => $struktur): ?>
          <div class="struktur-card <?= $index === 0 ? 'kepala' : '' ?>">
            <div class="struktur-photo">
              <?php if ($struktur['foto']): ?>
                <img src="assets/img/anggota/<?= htmlspecialchars($struktur['foto']) ?>" alt="<?= htmlspecialchars($struktur['nama']) ?>">
              <?php else: ?>
                <div class="photo-placeholder">Foto</div>
              <?php endif; ?>
            </div>
            <h4><?= htmlspecialchars($struktur['nama']) ?></h4>
            <p class="jabatan"><?= htmlspecialchars($struktur['jabatan']) ?></p>
            <p class="nip">NIP: <?= htmlspecialchars($struktur['nip'] ?? '-') ?></p>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="struktur-card kepala">
          <div class="struktur-photo">
            <div class="photo-placeholder">Foto</div>
          </div>
          <h4>Dr. Nama Dosen, M.Kom</h4>
          <p class="jabatan">Kepala Laboratorium</p>
          <p class="nip">NIP: 198501012010121001</p>
        </div>
      <?php endif; ?>
    </div>
    
    <?php if (count($struktur_list) === 0): ?>
    <p style="text-align: center; margin-top: 40px; color: #888; font-size: 14px; font-style: italic;">
      * Data struktur organisasi sedang dalam proses update
    </p>
    <?php endif; ?>
  </div>
</section>

<?php include 'footer.php'; ?>