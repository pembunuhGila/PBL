<?php
// tentang.php - Halaman Tentang Kami Lab Data Technology (Dynamic from Database)
$activePage = 'tentang';
include 'conn.php'; // Koneksi database

// Ambil data profil lab
$profil_data = $pdo->query("SELECT * FROM tentang_kami WHERE status = 'active' LIMIT 1")->fetch();

// Ambil data visi
$visi_list = $pdo->query("SELECT * FROM visi WHERE status = 'active' ORDER BY urutan, id_visi")->fetchAll();

// Ambil data misi
$misi_list = $pdo->query("SELECT * FROM misi WHERE status = 'active' ORDER BY urutan")->fetchAll();

// Ambil data sejarah/roadmap
$roadmap_list = $pdo->query("SELECT * FROM sejarah WHERE status = 'active' ORDER BY tahun DESC, urutan")->fetchAll();

// Ambil data struktur organisasi
$struktur_list = $pdo->query("
    SELECT s.*, a.nama, a.nip, a.foto, a.id_anggota 
    FROM struktur_lab s
    LEFT JOIN anggota_lab a ON s.id_anggota = a.id_anggota
    WHERE s.status = 'active' AND a.status = 'active'
    ORDER BY s.urutan ASC
")->fetchAll();

// HELPER FUNCTION: Cari logo di multiple locations
function get_logo_path($logo_name) {
    if (empty($logo_name)) return '';
    
    $possible_paths = [
        'uploads/tentang/' . htmlspecialchars($logo_name),
        'uploads/' . htmlspecialchars($logo_name),
        'assets/img/' . htmlspecialchars($logo_name)
    ];
    
    foreach ($possible_paths as $path) {
        if (file_exists($path)) {
            return $path;
        }
    }
    
    return '';
}

// HELPER FUNCTION: Cari foto di multiple locations
function get_foto_path($foto_name) {
    if (empty($foto_name)) return '';
    
    $possible_paths = [
        'assets/img/anggota/' . htmlspecialchars($foto_name),
        'uploads/anggota/' . htmlspecialchars($foto_name),
        'uploads/' . htmlspecialchars($foto_name)
    ];
    
    foreach ($possible_paths as $path) {
        if (file_exists($path)) {
            return $path;
        }
    }
    
    return '';
}

include 'navbar.php'; // Navbar
?>
<link rel="stylesheet" href="assets/css/style.css">
<link rel="stylesheet" href="assets/css/tentang.css">

<!-- ============================================
     HERO SECTION TENTANG KAMI
============================================= -->
<div class="page-hero" style="background-image: url('assets/img/tentang-kami.jpeg');">
  <div class="page-hero-overlay">
    <div class="container">
      <h1 class="page-title">Tentang Kami</h1>
      <p class="page-subtitle">Mengenal Lab Data Technology lebih dekat</p>
    </div>
  </div>
</div>

<!-- ============================================
     QUICK NAVIGATION
============================================= -->
<section class="quick-nav-section">
  <div class="container">
    <div class="quick-nav">
      <a href="#profil" class="quick-nav-item">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
        </svg>
        <span>Profil</span>
      </a>
      <a href="#identitas" class="quick-nav-item">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
          <circle cx="8.5" cy="8.5" r="1.5"></circle>
          <polyline points="21 15 16 10 5 21"></polyline>
        </svg>
        <span>Identitas</span>
      </a>
      <a href="#visi-misi" class="quick-nav-item">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
          <circle cx="12" cy="12" r="3"></circle>
        </svg>
        <span>Visi & Misi</span>
      </a>
      <a href="#roadmap" class="quick-nav-item">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="12" cy="12" r="10"></circle>
          <polyline points="12 6 12 12 16 14"></polyline>
        </svg>
        <span>Roadmap</span>
      </a>
      <a href="#struktur" class="quick-nav-item">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
          <circle cx="9" cy="7" r="4"></circle>
          <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
          <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
        </svg>
        <span>Struktur</span>
      </a>
    </div>
  </div>
</section>

<!-- ============================================
     PROFIL LAB
============================================= -->
<section id="profil" class="section profil-section">
  <div class="container">
    <h2 class="section-title">Profil Lab Data Technology</h2>
    <div class="profil-text-content">
      <?php if ($profil_data && $profil_data['profil_lab']): ?>
        <?php 
        $paragraphs = explode("\n\n", $profil_data['profil_lab']);
        foreach ($paragraphs as $paragraph): 
          if (trim($paragraph)): ?>
            <p><?= nl2br(htmlspecialchars(trim($paragraph))) ?></p>
          <?php endif;
        endforeach; ?>
      <?php else: ?>
        <p>Informasi profil lab belum tersedia.</p>
      <?php endif; ?>
    </div>
  </div>
</section>

<!-- ============================================
     LOGO & IDENTITAS LAB
============================================= -->
<section id="identitas" class="section logo-section">
  <div class="container">
    <h2 class="section-title">Identitas Lab</h2>
    <div class="logo-identity">
      <div class="logo-box">
        <?php 
          $logo_path = '';
          
          // Cek logo dari database
          if ($profil_data && $profil_data['logo_lab']) {
            $logo_path = get_logo_path($profil_data['logo_lab']);
          }
          
          // Fallback ke asset default
          if (empty($logo_path)) {
            $logo_path = 'assets/img/logo-lab-dt.png';
          }
        ?>
        
        <img 
          src="<?= $logo_path ?>" 
          alt="Logo Lab Data Technology" 
          class="lab-logo"
          loading="lazy"
          onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22200%22 height=%22200%22 viewBox=%220 0 24 24%22 fill=%22none%22 stroke=%221e4a7a%22 stroke-width=%222%22%3E%3Crect x=%223%22 y=%223%22 width=%2218%22 height=%2218%22 rx=%222%22/%3E%3Cpath d=%22M9 12h6M12 9v6%22/%3E%3C/svg%3E'"
          style="max-width: 100%; max-height: 280px; height: auto; display: block; margin: 0 auto;">
      </div>
      
      <div class="logo-description">
        <h3 class="logo-title">Lab Data Technology</h3>
        <?php if ($profil_data && $profil_data['penjelasan_logo']): ?>
          <p class="logo-desc"><?= nl2br(htmlspecialchars($profil_data['penjelasan_logo'])) ?></p>
        <?php else: ?>
          <p class="logo-desc">Logo Lab Data Technology merepresentasikan visi kami dalam mengembangkan teknologi data yang inovatif dan berkelanjutan.</p>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>

<!-- ============================================
     VISI & MISI
============================================= -->
<section id="visi-misi" class="section visi-misi-section">
  <div class="container">
    <h2 class="section-title">Visi & Misi</h2>
    
    <div class="visi-misi-wrapper">
      <!-- VISI -->
      <div class="vm-card visi-card">
        <div class="vm-icon">
          <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
            <circle cx="12" cy="12" r="3"></circle>
          </svg>
        </div>
        <h3>Visi</h3>
        <?php if (count($visi_list) > 0): ?>
          <?php foreach($visi_list as $visi): ?>
            <p><?= nl2br(htmlspecialchars($visi['isi_visi'])) ?></p>
          <?php endforeach; ?>
        <?php else: ?>
          <p>Menjadi laboratorium teknologi data terkemuka yang menghasilkan lulusan berkualitas, inovatif, dan kompetitif di tingkat nasional maupun internasional pada tahun 2030.</p>
        <?php endif; ?>
      </div>
      
      <!-- MISI -->
      <div class="vm-card misi-card">
        <div class="vm-icon">
          <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
            <polyline points="14 2 14 8 20 8"></polyline>
            <line x1="16" y1="13" x2="8" y2="13"></line>
            <line x1="16" y1="17" x2="8" y2="17"></line>
          </svg>
        </div>
        <h3>Misi</h3>
        <?php if (count($misi_list) > 0): ?>
          <ul class="misi-list">
            <?php foreach($misi_list as $misi): ?>
              <li><?= htmlspecialchars($misi['isi_misi']) ?></li>
            <?php endforeach; ?>
          </ul>
        <?php else: ?>
          <ul class="misi-list">
            <li>Menyelenggarakan pendidikan dan pengajaran berkualitas di bidang teknologi data</li>
            <li>Melaksanakan penelitian dan pengembangan teknologi informasi yang inovatif</li>
            <li>Memberikan layanan kepada masyarakat melalui pengabdian dan kerja sama</li>
          </ul>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>

<!-- ============================================
     ROADMAP - REDESIGNED
============================================= -->
<section id="roadmap" class="section roadmap-section">
  <div class="container">
    <h2 class="section-title">Roadmap Lab Data Technology</h2>
    <p class="section-subtitle">Perjalanan dan pencapaian kami dari masa ke masa</p>
    
    <?php if (count($roadmap_list) > 0): ?>
      <div class="roadmap-container">
        <?php foreach($roadmap_list as $index => $roadmap): ?>
          <div class="roadmap-item <?= $index % 2 == 0 ? 'left' : 'right' ?>">
            <div class="roadmap-content">
              <div class="roadmap-year"><?= htmlspecialchars($roadmap['tahun']) ?></div>
              <div class="roadmap-card">
                <div class="roadmap-icon">
                  <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                  </svg>
                </div>
                <h4><?= htmlspecialchars($roadmap['judul']) ?></h4>
                <?php if ($roadmap['deskripsi']): ?>
                  <p><?= nl2br(htmlspecialchars($roadmap['deskripsi'])) ?></p>
                <?php endif; ?>
              </div>
            </div>
            <div class="roadmap-dot"></div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <p style="text-align: center; margin-top: 40px; color: #888; font-size: 14px; font-style: italic;">
        Data roadmap belum tersedia.
      </p>
    <?php endif; ?>
  </div>
</section>

<!-- ============================================
     STRUKTUR ORGANISASI
============================================= -->
<section id="struktur" class="section struktur-section">
  <div class="container">
    <h2 class="section-title">Struktur Organisasi</h2>
    <p class="section-subtitle">Tim pengelola Lab Data Technology</p>
    
    <?php if (count($struktur_list) > 0): ?>
      <div class="struktur-grid">
        <?php foreach($struktur_list as $index => $struktur): ?>
          <a href="anggota_detail.php?id=<?= $struktur['id_anggota'] ?>" class="struktur-card <?= $index === 0 ? 'kepala' : '' ?>">
            <div class="struktur-photo">
              <?php 
                $foto_path = '';
                
                // Cek foto dari database
                if ($struktur['foto']) {
                  $foto_path = get_foto_path($struktur['foto']);
                }
              ?>
              
              <?php if (!empty($foto_path)): ?>
                <img 
                  src="<?= $foto_path ?>" 
                  alt="<?= htmlspecialchars($struktur['nama']) ?>"
                  class="struktur-photo-img"
                  loading="lazy"
                  onerror="this.src='https://ui-avatars.com/api/?name=<?= urlencode($struktur['nama']) ?>&background=1e4a7a&color=fff'">
              <?php else: ?>
                <!-- Avatar placeholder jika foto tidak ada -->
                <img 
                  src="https://ui-avatars.com/api/?name=<?= urlencode($struktur['nama']) ?>&background=1e4a7a&color=fff" 
                  alt="<?= htmlspecialchars($struktur['nama']) ?>"
                  class="struktur-photo-img"
                  loading="lazy">
              <?php endif; ?>
            </div>
            
            <h4><?= htmlspecialchars($struktur['nama']) ?></h4>
            <p class="jabatan"><?= htmlspecialchars($struktur['jabatan']) ?></p>
            
            <?php if ($struktur['nip']): ?>
              <p class="nip"><?= htmlspecialchars($struktur['nip']) ?></p>
            <?php endif; ?>
          </a>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <p style="text-align: center; margin-top: 40px; color: #888; font-size: 14px; font-style: italic;">
        Data struktur organisasi belum tersedia. Silakan hubungi administrator untuk informasi lebih lanjut.
      </p>
    <?php endif; ?>
  </div>
</section>

<?php include 'footer.php'; ?>