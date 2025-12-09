<?php
$auth_required = false;
include "auth.php";

$activePage = 'struktur';
require_once 'conn.php';

$id_anggota = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Ambil data anggota
$stmt = $pdo->prepare("SELECT * FROM anggota_lab WHERE id_anggota = ? AND status = 'active'");
$stmt->execute([$id_anggota]);
$anggota = $stmt->fetch();

if (!$anggota) {
    header("Location: tentang.php");
    exit();
}

// Ambil social media
$stmt_social = $pdo->prepare("SELECT * FROM social_media_anggota WHERE id_anggota = ?");
$stmt_social->execute([$id_anggota]);
$social_media = $stmt_social->fetchAll();

// Ambil SEMUA publikasi anggota (bukan hanya 3)
$stmt_publikasi = $pdo->prepare("
    SELECT p.* FROM publikasi p
    JOIN publikasi_anggota pa ON p.id_publikasi = pa.id_publikasi
    WHERE pa.id_anggota = ? AND p.status = 'active'
    ORDER BY p.tahun DESC, p.tanggal_publikasi DESC
");
$stmt_publikasi->execute([$id_anggota]);
$publikasi_list = $stmt_publikasi->fetchAll();

// Count publikasi by year
$stmt_year_count = $pdo->prepare("
    SELECT p.tahun, COUNT(*) as total 
    FROM publikasi p
    JOIN publikasi_anggota pa ON p.id_publikasi = pa.id_publikasi
    WHERE pa.id_anggota = ? AND p.status = 'active'
    GROUP BY p.tahun
    ORDER BY p.tahun DESC
");
$stmt_year_count->execute([$id_anggota]);
$publikasi_by_year = $stmt_year_count->fetchAll();

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

<style>
/* Stats Cards */
.stats-cards {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 16px;
  margin-bottom: 24px;
}

.stat-card {
  background: linear-gradient(135deg, var(--primary-blue), #2563a8);
  padding: 20px;
  border-radius: 12px;
  text-align: center;
  color: #fff;
  box-shadow: 0 4px 15px rgba(30, 74, 122, 0.3);
}

.stat-number {
  font-size: 32px;
  font-weight: 700;
  margin-bottom: 4px;
}

.stat-label {
  font-size: 13px;
  opacity: 0.9;
}

/* Publication Timeline */
.pub-timeline {
  position: relative;
  padding-left: 30px;
  margin-top: 24px;
}

.pub-timeline::before {
  content: '';
  position: absolute;
  left: 8px;
  top: 0;
  bottom: 0;
  width: 2px;
  background: #e8ebef;
}

.timeline-year {
  position: relative;
  margin-bottom: 32px;
}

.timeline-year::before {
  content: '';
  position: absolute;
  left: -26px;
  top: 6px;
  width: 16px;
  height: 16px;
  background: var(--primary-blue);
  border: 3px solid #fff;
  border-radius: 50%;
  box-shadow: 0 0 0 3px rgba(30, 74, 122, 0.1);
}

.timeline-year-label {
  font-size: 18px;
  font-weight: 700;
  color: var(--primary-blue);
  margin-bottom: 16px;
}

/* Enhanced Publication Card */
.publication-item {
  position: relative;
  overflow: hidden;
}

.publication-item::before {
  content: '';
  position: absolute;
  left: 0;
  top: 0;
  bottom: 0;
  width: 4px;
  background: var(--primary-blue);
  transform: scaleY(0);
  transition: transform 0.3s;
}

.publication-item:hover::before {
  transform: scaleY(1);
}

/* Research Areas Pills */
.research-pills {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  margin-top: 12px;
}

.research-pill {
  padding: 6px 14px;
  background: linear-gradient(135deg, #e8ebef, #d5dae0);
  border-radius: 20px;
  font-size: 12px;
  font-weight: 600;
  color: var(--text-dark);
}

@media (max-width: 768px) {
  .stats-cards {
    grid-template-columns: 1fr;
  }
}
</style>

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
                <?php elseif ($social['platform'] == 'instagram'): ?>
                  <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                  </svg>
                <?php elseif ($social['platform'] == 'facebook'): ?>
                  <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
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
        
        <!-- Statistics Cards -->
        <div class="stats-cards">
          <div class="stat-card">
            <div class="stat-number"><?= count($publikasi_list) ?></div>
            <div class="stat-label">Total Publikasi</div>
          </div>
          <div class="stat-card">
            <div class="stat-number"><?= count($publikasi_by_year) ?></div>
            <div class="stat-label">Tahun Aktif</div>
          </div>
          <div class="stat-card">
            <div class="stat-number">
              <?php 
              $latest_year = !empty($publikasi_by_year) ? $publikasi_by_year[0]['total'] : 0;
              echo $latest_year;
              ?>
            </div>
            <div class="stat-label">Publikasi Terbaru</div>
          </div>
        </div>
        
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
              <?php if ($anggota['pendidikan']): ?>
              <div class="bio-item">
                <span class="bio-label">Pendidikan:</span>
                <span class="bio-value"><?= nl2br(htmlspecialchars($anggota['pendidikan'])) ?></span>
              </div>
              <?php endif; ?>
              
              <?php if ($anggota['bidang_keahlian']): ?>
              <div class="bio-item">
                <span class="bio-label">Bidang Keahlian:</span>
                <span class="bio-value"><?= nl2br(htmlspecialchars($anggota['bidang_keahlian'])) ?></span>
              </div>
              <?php endif; ?>
              
              <?php if ($anggota['tanggal_bergabung']): ?>
              <div class="bio-item">
                <span class="bio-label">Bergabung Sejak:</span>
                <span class="bio-value"><?= date('Y', strtotime($anggota['tanggal_bergabung'])) ?></span>
              </div>
              <?php endif; ?>
            </div>
            
            <!-- Research Areas -->
            <?php if ($anggota['bidang_keahlian']): ?>
            <div style="margin-top: 24px; padding-top: 24px; border-top: 2px solid #e8ebef;">
              <h4 style="font-size: 16px; font-weight: 700; margin-bottom: 12px; color: var(--text-dark);">
                🎯 Area Penelitian
              </h4>
              <div class="research-pills">
                <?php 
                $keahlian = explode(',', $anggota['bidang_keahlian']);
                foreach ($keahlian as $k) {
                  echo '<span class="research-pill">' . htmlspecialchars(trim($k)) . '</span>';
                }
                ?>
              </div>
            </div>
            <?php endif; ?>
          </div>
        </div>
        
        <!-- Publikasi dengan Timeline -->
        <?php if (count($publikasi_list) > 0): ?>
        <div class="content-section">
          <h2 class="section-title">
            <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
              <polyline points="14 2 14 8 20 8"></polyline>
            </svg>
            Publikasi (<?= count($publikasi_list) ?>)
          </h2>
          <div class="content-box">
            
            <div class="pub-timeline">
              <?php 
              $current_year = '';
              foreach($publikasi_list as $pub): 
                if ($pub['tahun'] != $current_year):
                  if ($current_year != '') echo '</div>'; // Close previous year group
                  $current_year = $pub['tahun'];
              ?>
              <div class="timeline-year">
                <div class="timeline-year-label">📅 <?= htmlspecialchars($current_year) ?></div>
                <div class="publication-list">
              <?php endif; ?>
              
              <article class="publication-item">
                <div class="pub-content">
                  <h3 class="pub-title">
                    <a href="publikasi_detail.php?id=<?= $pub['id_publikasi'] ?>">
                      <?= htmlspecialchars($pub['judul']) ?>
                    </a>
                  </h3>
                  <p class="pub-journal">
                    <?= htmlspecialchars($pub['jurnal'] ?? 'Tidak ada informasi jurnal') ?>
                  </p>
                  <?php if ($pub['abstrak']): ?>
                  <p class="pub-authors" style="margin-top: 8px; font-size: 13px; color: #666;">
                    <?= htmlspecialchars(substr($pub['abstrak'], 0, 150)) ?>...
                  </p>
                  <?php endif; ?>
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
                    
                    <?php if ($pub['link_shinta']): ?>
                    <a href="<?= htmlspecialchars($pub['link_shinta']) ?>" class="pub-action-link" target="_blank">
                      <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path>
                        <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path>
                      </svg>
                      Link
                    </a>
                    <?php endif; ?>
                    
                    <a href="publikasi_detail.php?id=<?= $pub['id_publikasi'] ?>" class="pub-action-link" style="background: var(--accent-green);">
                      <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                        <circle cx="12" cy="12" r="3"></circle>
                      </svg>
                      Detail
                    </a>
                  </div>
                </div>
              </article>
              
              <?php endforeach; ?>
              </div></div> <!-- Close last year group -->
            </div>
            
          </div>
        </div>
        <?php endif; ?>
        
      </main>
      
    </div>
  </div>
</section>

<?php include 'footer.php'; ?>