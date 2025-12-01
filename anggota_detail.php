<?php
$auth_required = false; // TAMU boleh masuk
include "auth.php";
?>


<?php
// anggota_detail.php - Detail Profil Anggota
$activePage = 'struktur';
require_once 'conn.php';

// Ambil ID anggota dari URL
$id_anggota = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Ambil data anggota
$stmt = $pdo->prepare("
    SELECT * FROM anggota_lab 
    WHERE id_anggota = ? AND status = 'active'
");
$stmt->execute([$id_anggota]);
$anggota = $stmt->fetch();

// Jika anggota tidak ditemukan, redirect
if (!$anggota) {
    header("Location: tentang.php");
    exit();
}

// Ambil social media anggota
$stmt_social = $pdo->prepare("
    SELECT * FROM social_media_anggota 
    WHERE id_anggota = ?
");
$stmt_social->execute([$id_anggota]);
$social_media = $stmt_social->fetchAll();

// Ambil publikasi anggota (limit 3 untuk preview)
$stmt_publikasi = $pdo->prepare("
    SELECT p.* FROM publikasi p
    JOIN publikasi_anggota pa ON p.id_publikasi = pa.id_publikasi
    WHERE pa.id_anggota = ? AND p.status = 'active'
    ORDER BY p.tahun DESC, p.tanggal_publikasi DESC
    LIMIT 3
");
$stmt_publikasi->execute([$id_anggota]);
$publikasi_list = $stmt_publikasi->fetchAll();

// Ambil jabatan di struktur lab
$stmt_jabatan = $pdo->prepare("
    SELECT jabatan FROM struktur_lab 
    WHERE id_anggota = ? AND status = 'active'
    ORDER BY urutan ASC LIMIT 1
");
$stmt_jabatan->execute([$id_anggota]);
$jabatan = $stmt_jabatan->fetch();

include 'navbar.php';
?>
<link rel="stylesheet" href="assets/css/style.css">
<link rel="stylesheet" href="assets/css/anggota_detail.css">

<!-- BREADCRUMB -->
<div class="breadcrumb-section">
  <div class="container">
    <nav class="breadcrumb">
      <a href="index.php">Home</a>
      <span class="separator">›</span>
      <a href="tentang.php">Tentang Kami</a>
      <span class="separator">›</span>
      <span class="current">Detail Anggota</span>
    </nav>
  </div>
</div>

<!-- PROFILE SECTION -->
<section class="profile-section">
  <div class="container">
    <div class="profile-layout">
      
      <!-- SIDEBAR PROFIL -->
      <aside class="profile-sidebar">
        
        <!-- Photo -->
        <div class="profile-photo">
          <?php if ($anggota['foto']): ?>
            <img src="assets/img/anggota/<?= htmlspecialchars($anggota['foto']) ?>" alt="<?= htmlspecialchars($anggota['nama']) ?>">
          <?php else: ?>
            <div class="photo-placeholder">
              <svg width="80" height="80" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                <circle cx="12" cy="7" r="4"></circle>
              </svg>
            </div>
          <?php endif; ?>
        </div>
        
        <!-- Info Box -->
        <div class="profile-info-box">
          <h1 class="profile-name"><?= htmlspecialchars($anggota['nama']) ?></h1>
          <?php if ($jabatan): ?>
            <p class="profile-position"><?= htmlspecialchars($jabatan['jabatan']) ?></p>
          <?php endif; ?>
          
          <div class="info-list">
            <?php if ($anggota['nip']): ?>
            <div class="info-item">
              <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
              </svg>
              <div>
                <span class="info-label">NIP/NIDN</span>
                <span class="info-value"><?= htmlspecialchars($anggota['nip']) ?></span>
              </div>
            </div>
            <?php endif; ?>
            
            <?php if ($anggota['email']): ?>
            <div class="info-item">
              <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                <polyline points="22,6 12,13 2,6"></polyline>
              </svg>
              <div>
                <span class="info-label">Email</span>
                <span class="info-value"><?= htmlspecialchars($anggota['email']) ?></span>
              </div>
            </div>
            <?php endif; ?>
            
            <?php if ($anggota['kontak']): ?>
            <div class="info-item">
              <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
              </svg>
              <div>
                <span class="info-label">Kontak</span>
                <span class="info-value"><?= htmlspecialchars($anggota['kontak']) ?></span>
              </div>
            </div>
            <?php endif; ?>
            
            <?php if ($anggota['ruangan']): ?>
            <div class="info-item">
              <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                <circle cx="12" cy="10" r="3"></circle>
              </svg>
              <div>
                <span class="info-label">Ruangan</span>
                <span class="info-value"><?= htmlspecialchars($anggota['ruangan']) ?></span>
              </div>
            </div>
            <?php endif; ?>
          </div>
          
          <!-- Social Links -->
          <?php if (count($social_media) > 0): ?>
          <div class="profile-social">
            <?php foreach($social_media as $social): ?>
              <a href="<?= htmlspecialchars($social['url']) ?>" class="social-link" title="<?= ucfirst($social['platform']) ?>" target="_blank">
                <?php if ($social['platform'] == 'email'): ?>
                  <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M0 3v18h24v-18h-24zm6.623 7.929l-4.623 5.712v-9.458l4.623 3.746zm-4.141-5.929h19.035l-9.517 7.713-9.518-7.713zm5.694 7.188l3.824 3.099 3.83-3.104 5.612 6.817h-18.779l5.513-6.812zm9.208-1.264l4.616-3.741v9.348l-4.616-5.607z"/>
                  </svg>
                <?php elseif ($social['platform'] == 'scholar'): ?>
                  <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 24a7 7 0 110-14 7 7 0 010 14zm0-24L0 9.5l4.838 3.94A8 8 0 0112 9a8 8 0 017.162 4.44L24 9.5z"/>
                  </svg>
                <?php elseif ($social['platform'] == 'linkedin'): ?>
                  <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                  </svg>
                <?php endif; ?>
              </a>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>
          
        </div>
        
      </aside>
      
      <!-- MAIN CONTENT -->
      <main class="profile-main">
        
        <!-- Biodata / About -->
        <div class="content-section">
          <h2 class="section-title">
            <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
              <circle cx="12" cy="7" r="4"></circle>
            </svg>
            Biodata
          </h2>
          <div class="content-box">
            <?php if ($anggota['biodata_teks']): ?>
              <p><?= nl2br(htmlspecialchars($anggota['biodata_teks'])) ?></p>
            <?php else: ?>
              <p>Informasi biodata belum tersedia.</p>
            <?php endif; ?>
            
            <div class="bio-grid">
              <?php if ($anggota['pendidikan_terakhir']): ?>
              <div class="bio-item">
                <span class="bio-label">Pendidikan Terakhir:</span>
                <span class="bio-value"><?= htmlspecialchars($anggota['pendidikan_terakhir']) ?></span>
              </div>
              <?php endif; ?>
              
              <?php if ($anggota['bidang_keahlian']): ?>
              <div class="bio-item">
                <span class="bio-label">Bidang Keahlian:</span>
                <span class="bio-value"><?= htmlspecialchars($anggota['bidang_keahlian']) ?></span>
              </div>
              <?php endif; ?>
              
              <?php if ($anggota['tanggal_bergabung']): ?>
              <div class="bio-item">
                <span class="bio-label">Bergabung Sejak:</span>
                <span class="bio-value"><?= date('Y', strtotime($anggota['tanggal_bergabung'])) ?></span>
              </div>
              <?php endif; ?>
            </div>
          </div>
        </div>
        
        <!-- Publikasi -->
        <?php if (count($publikasi_list) > 0): ?>
        <div class="content-section">
          <h2 class="section-title">
            <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
              <polyline points="14 2 14 8 20 8"></polyline>
            </svg>
            Publikasi Terbaru
          </h2>
          <div class="content-box">
            
            <div class="publication-list">
              <?php foreach($publikasi_list as $pub): ?>
              <article class="publication-item">
                <div class="pub-year"><?= htmlspecialchars($pub['tahun']) ?></div>
                <div class="pub-content">
                  <h3 class="pub-title">
                    <a href="publikasi_detail.php?id=<?= $pub['id_publikasi'] ?>">
                      <?= htmlspecialchars($pub['judul']) ?>
                    </a>
                  </h3>
                  <p class="pub-journal"><?= htmlspecialchars($pub['jurnal'] ?? 'Tidak ada informasi jurnal') ?></p>
                  <div class="pub-actions">
                    <?php if ($pub['file_path']): ?>
                    <a href="assets/uploads/publikasi/<?= htmlspecialchars($pub['file_path']) ?>" class="pub-action-link" target="_blank">
                      <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                        <polyline points="7 10 12 15 17 10"></polyline>
                        <line x1="12" y1="15" x2="12" y2="3"></line>
                      </svg>
                      PDF
                    </a>
                    <?php endif; ?>
                    
                    <?php if ($pub['doi']): ?>
                    <a href="<?= htmlspecialchars($pub['doi']) ?>" class="pub-action-link" target="_blank">
                      <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path>
                        <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path>
                      </svg>
                      DOI
                    </a>
                    <?php endif; ?>
                  </div>
                </div>
              </article>
              <?php endforeach; ?>
            </div>
            
            <div class="view-all-container">
              <a href="publikasi.php" class="view-all-btn">
                Lihat Semua Publikasi
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path d="M5 12h14M12 5l7 7-7 7"/>
                </svg>
              </a>
            </div>
            
          </div>
        </div>
        <?php endif; ?>
        
      </main>
      
    </div>
  </div>
</section>

<?php include 'footer.php'; ?>