<?php
// Hapus baris auth.php yang error
// $auth_required = false;
// include "auth.php";

$activePage = 'publikasi';
require_once 'conn.php';

$id_publikasi = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Ambil data publikasi
$stmt = $pdo->prepare("SELECT * FROM publikasi WHERE id_publikasi = ? AND status = 'active'");
$stmt->execute([$id_publikasi]);
$publikasi = $stmt->fetch();

if (!$publikasi) {
    header("Location: publikasi.php");
    exit();
}

// Ambil penulis/anggota
$stmt_penulis = $pdo->prepare("
    SELECT a.*, pa.urutan_penulis 
    FROM anggota_lab a
    JOIN publikasi_anggota pa ON a.id_anggota = pa.id_anggota
    WHERE pa.id_publikasi = ?
    ORDER BY pa.urutan_penulis ASC
");
$stmt_penulis->execute([$id_publikasi]);
$penulis_list = $stmt_penulis->fetchAll();

// Ambil publikasi terkait (tahun yang sama atau penulis yang sama)
$stmt_related = $pdo->prepare("
    SELECT DISTINCT p.* 
    FROM publikasi p
    WHERE p.status = 'active' 
    AND p.id_publikasi != ?
    AND (p.tahun = ? OR p.jurnal = ?)
    ORDER BY p.created_at DESC
    LIMIT 5
");
$stmt_related->execute([$id_publikasi, $publikasi['tahun'], $publikasi['jurnal']]);
$related_publikasi = $stmt_related->fetchAll();

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

include 'navbar.php';
?>
<link rel="stylesheet" href="assets/css/style.css">
<link rel="stylesheet" href="assets/css/publikasi_detail.css">

<style>
/* Enhanced Styles */
.authors-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  gap: 16px;
  margin-top: 20px;
}

.author-card {
  display: flex;
  gap: 12px;
  padding: 16px;
  background: #f8f9fb;
  border-radius: 10px;
  border: 2px solid #e8ebef;
  transition: all 0.3s;
  text-decoration: none;
  color: inherit;
}

.author-card:hover {
  border-color: var(--primary-blue);
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(30, 74, 122, 0.1);
}

.author-avatar {
  width: 50px;
  height: 50px;
  border-radius: 50%;
  background: linear-gradient(135deg, var(--primary-blue), #2563a8);
  display: flex;
  align-items: center;
  justify-content: center;
  color: #fff;
  font-weight: 700;
  font-size: 18px;
  flex-shrink: 0;
  overflow: hidden;
}

.author-avatar img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  border-radius: 50%;
}

.author-info {
  flex: 1;
  min-width: 0;
}

.author-name {
  font-size: 14px;
  font-weight: 600;
  color: var(--text-dark);
  margin-bottom: 2px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.author-order {
  font-size: 11px;
  color: #888;
}

/* Citation Box */
.citation-box {
  background: #f8f9fb;
  padding: 20px;
  border-radius: 10px;
  border-left: 4px solid var(--primary-blue);
  margin-top: 24px;
  font-family: 'Courier New', monospace;
  font-size: 13px;
  line-height: 1.8;
  color: #444;
  position: relative;
  padding-top: 30px;
}

.citation-box::before {
  content: '📑 Citation';
  position: absolute;
  top: -10px;
  left: 16px;
  background: var(--primary-blue);
  color: #fff;
  padding: 4px 12px;
  border-radius: 4px;
  font-size: 11px;
  font-weight: 600;
  font-family: sans-serif;
}

.copy-citation-btn {
  position: absolute;
  top: 16px;
  right: 16px;
  padding: 6px 12px;
  background: var(--primary-blue);
  color: #fff;
  border: none;
  border-radius: 6px;
  font-size: 11px;
  cursor: pointer;
  transition: all 0.3s;
}

.copy-citation-btn:hover {
  background: #2563a8;
}

/* Stats Pills */
.pub-stats {
  display: flex;
  gap: 12px;
  flex-wrap: wrap;
  margin-top: 20px;
  margin-bottom: 24px;
}

.stat-pill {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 8px 16px;
  background: linear-gradient(135deg, #e8ebef, #d5dae0);
  border-radius: 20px;
  font-size: 13px;
  font-weight: 600;
  color: var(--text-dark);
}

.stat-pill svg {
  color: var(--primary-blue);
}

/* Animated Cover */
@keyframes float {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-10px); }
}

.publikasi-cover-large:hover img {
  animation: float 3s ease-in-out infinite;
}

/* Related item enhancement */
.related-item {
  transition: all 0.3s;
}

.related-item:hover {
  background: #f8f9fb;
  padding: 12px;
  margin: -8px -12px 8px -12px;
  border-radius: 8px;
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .authors-grid {
    grid-template-columns: 1fr;
  }
  
  .pub-stats {
    flex-direction: column;
  }
  
  .stat-pill {
    justify-content: center;
  }
  
  .citation-box {
    padding: 20px 16px;
    font-size: 12px;
  }
  
  .copy-citation-btn {
    position: static;
    display: block;
    width: 100%;
    margin-bottom: 12px;
  }
}
</style>

<!-- BREADCRUMB -->
<div class="breadcrumb-section">
  <div class="container">
    <nav class="breadcrumb">
      <a href="index.php">Home</a>
      <span class="separator">›</span>
      <a href="publikasi.php">Publikasi</a>
      <span class="separator">›</span>
      <span class="current"><?= htmlspecialchars(substr($publikasi['judul'], 0, 50)) ?>...</span>
    </nav>
  </div>
</div>

<!-- DETAIL SECTION -->
<section class="detail-section">
  <div class="container">
    <div class="detail-layout">
      
      <!-- MAIN CONTENT -->
      <main class="detail-main">
        
        <!-- Cover Image -->
        <div class="publikasi-cover-large">
          <?php if ($publikasi['cover'] && file_exists('uploads/publikasi/cover/' . $publikasi['cover'])): ?>
            <img src="uploads/publikasi/cover/<?= htmlspecialchars($publikasi['cover']) ?>" alt="Cover">
          <?php else: ?>
            <div class="cover-placeholder-large">
              <svg width="80" height="80" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                <polyline points="14 2 14 8 20 8"></polyline>
              </svg>
              <p style="margin-top: 12px; color: #999;">Cover Publikasi</p>
            </div>
          <?php endif; ?>
        </div>
        
        <!-- Content -->
        <div class="publikasi-content">
          
          <!-- Title -->
          <h1 class="publikasi-detail-title">
            <?= htmlspecialchars($publikasi['judul']) ?>
          </h1>
          
          <!-- Stats Pills -->
          <div class="pub-stats">
            <span class="stat-pill">
              <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                <line x1="16" y1="2" x2="16" y2="6"></line>
                <line x1="8" y1="2" x2="8" y2="6"></line>
                <line x1="3" y1="10" x2="21" y2="10"></line>
              </svg>
              <?= htmlspecialchars($publikasi['tahun']) ?>
            </span>
            
            <span class="stat-pill">
              <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                <circle cx="12" cy="7" r="4"></circle>
              </svg>
              <?= count($penulis_list) ?> Penulis
            </span>
            
            <?php if ($publikasi['tanggal_publikasi']): ?>
            <span class="stat-pill">
              <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="10"></circle>
                <polyline points="12 6 12 12 16 14"></polyline>
              </svg>
              <?= date('d M Y', strtotime($publikasi['tanggal_publikasi'])) ?>
            </span>
            <?php endif; ?>
          </div>
          
          <!-- Meta Info -->
          <div class="publikasi-meta-detail">
            <?php if ($publikasi['jurnal']): ?>
            <div class="meta-item">
              <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                <polyline points="14 2 14 8 20 8"></polyline>
              </svg>
              <div>
                <span class="meta-label">Jurnal:</span>
                <span class="meta-value"><?= htmlspecialchars($publikasi['jurnal']) ?></span>
              </div>
            </div>
            <?php endif; ?>
            
            <?php if ($publikasi['link_shinta']): ?>
            <div class="meta-item">
              <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path>
                <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path>
              </svg>
              <div>
                <span class="meta-label">Link Shinta:</span>
                <span class="meta-value">
                  <a href="<?= htmlspecialchars($publikasi['link_shinta']) ?>" target="_blank" rel="noopener noreferrer" style="color: var(--primary-blue); text-decoration: none;">
                    <?= htmlspecialchars($publikasi['link_shinta']) ?>
                  </a>
                </span>
              </div>
            </div>
            <?php endif; ?>
          </div>
          
          <!-- Authors Grid -->
          <?php if (count($penulis_list) > 0): ?>
          <div class="publikasi-section-box">
            <h2 class="section-box-title">👥 Penulis</h2>
            <div class="authors-grid">
              <?php foreach($penulis_list as $penulis): ?>
              <a href="anggota_detail.php?id=<?= $penulis['id_anggota'] ?>" class="author-card">
                <div class="author-avatar">
                  <?php 
                    $foto_path = get_foto_path($penulis['foto']);
                    if (!empty($foto_path)): 
                  ?>
                    <img 
                      src="<?= $foto_path ?>" 
                      alt="<?= htmlspecialchars($penulis['nama']) ?>"
                      loading="lazy"
                      onerror="this.style.display='none'; this.parentElement.textContent='<?= strtoupper(substr($penulis['nama'], 0, 1)) ?>';">
                  <?php else: ?>
                    <?= strtoupper(substr($penulis['nama'], 0, 1)) ?>
                  <?php endif; ?>
                </div>
                <div class="author-info">
                  <div class="author-name"><?= htmlspecialchars($penulis['nama']) ?></div>
                  <div class="author-order">Penulis <?= $penulis['urutan_penulis'] ?></div>
                </div>
              </a>
              <?php endforeach; ?>
            </div>
          </div>
          <?php endif; ?>
          
          <!-- Abstract / Deskripsi -->
          <?php if ($publikasi['abstrak']): ?>
          <div class="publikasi-section-box">
            <h2 class="section-box-title">📄 Abstrak</h2>
            <p><?= nl2br(htmlspecialchars($publikasi['abstrak'])) ?></p>
          </div>
          <?php endif; ?>
          
          <!-- Citation -->
          <div class="citation-box">
            <button class="copy-citation-btn" onclick="copyCitation()">
              📋 Copy
            </button>
            <div id="citation-text" style="margin-top: 8px;">
              <?php 
              $authors = array_map(function($p) { return $p['nama']; }, $penulis_list);
              $authors_str = implode(', ', $authors);
              $citation = $authors_str . ' (' . $publikasi['tahun'] . '). ' . $publikasi['judul'] . '. ' . ($publikasi['jurnal'] ?? 'Journal');
              echo htmlspecialchars($citation);
              ?>
            </div>
          </div>
          
          <!-- Download / Action Buttons -->
          <div class="publikasi-actions">
            <?php if ($publikasi['file_path'] && file_exists('uploads/publikasi/' . $publikasi['file_path'])): ?>
            <a href="uploads/publikasi/<?= htmlspecialchars($publikasi['file_path']) ?>" class="btn-download" target="_blank">
              <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                <polyline points="7 10 12 15 17 10"></polyline>
                <line x1="12" y1="15" x2="12" y2="3"></line>
              </svg>
              Download PDF
            </a>
            <?php endif; ?>
            
            <?php if ($publikasi['link_shinta']): ?>
            <a href="<?= htmlspecialchars($publikasi['link_shinta']) ?>" class="btn-cite" target="_blank" rel="noopener noreferrer">
              <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path>
                <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path>
              </svg>
              Lihat di Shinta
            </a>
            <?php endif; ?>
            
            <button class="btn-share" onclick="sharePublication()">
              <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <circle cx="18" cy="5" r="3"></circle>
                <circle cx="6" cy="12" r="3"></circle>
                <circle cx="18" cy="19" r="3"></circle>
                <line x1="8.59" y1="13.51" x2="15.42" y2="17.49"></line>
                <line x1="15.41" y1="6.51" x2="8.59" y2="10.49"></line>
              </svg>
              Share
            </button>
          </div>
          
        </div>
        
      </main>
      
      <!-- SIDEBAR -->
      <aside class="detail-sidebar">
        
        <!-- Related Publications -->
        <?php if (count($related_publikasi) > 0): ?>
        <div class="sidebar-widget">
          <h3 class="widget-title">📚 Publikasi Terkait</h3>
          <ul class="related-list">
            <?php foreach($related_publikasi as $rel): ?>
            <li class="related-item">
              <a href="publikasi_detail.php?id=<?= $rel['id_publikasi'] ?>">
                <div class="related-cover">
                  <?php if ($rel['cover'] && file_exists('uploads/publikasi/cover/' . $rel['cover'])): ?>
                    <img src="uploads/publikasi/cover/<?= htmlspecialchars($rel['cover']) ?>" alt="Cover">
                  <?php else: ?>
                    <div class="related-cover-placeholder">
                      <svg width="30" height="30" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                      </svg>
                    </div>
                  <?php endif; ?>
                </div>
                <div class="related-info">
                  <h4><?= htmlspecialchars(substr($rel['judul'], 0, 80)) ?>...</h4>
                  <span class="related-year">📅 <?= htmlspecialchars($rel['tahun']) ?></span>
                </div>
              </a>
            </li>
            <?php endforeach; ?>
          </ul>
        </div>
        <?php endif; ?>
        
        <!-- Quick Info -->
        <div class="sidebar-widget" style="background: linear-gradient(135deg, var(--primary-blue), #2563a8); color: #fff;">
          <h3 class="widget-title" style="border-color: rgba(255,255,255,0.2); color: #fff;">ℹ️ Info Cepat</h3>
          <div style="display: flex; flex-direction: column; gap: 16px; font-size: 14px;">
            <div>
              <strong>Tahun Publikasi:</strong><br>
              <?= htmlspecialchars($publikasi['tahun']) ?>
            </div>
            <?php if ($publikasi['jurnal']): ?>
            <div>
              <strong>Jurnal:</strong><br>
              <?= htmlspecialchars($publikasi['jurnal']) ?>
            </div>
            <?php endif; ?>
            <div>
              <strong>Jumlah Penulis:</strong><br>
              <?= count($penulis_list) ?> orang
            </div>
            <?php if ($publikasi['tanggal_publikasi']): ?>
            <div>
              <strong>Tanggal Publikasi:</strong><br>
              <?= date('d F Y', strtotime($publikasi['tanggal_publikasi'])) ?>
            </div>
            <?php endif; ?>
          </div>
        </div>
        
      </aside>
      
    </div>
  </div>
</section>

<?php include 'footer.php'; ?>

<script>
function copyCitation() {
  const citationText = document.getElementById('citation-text').innerText;
  navigator.clipboard.writeText(citationText).then(() => {
    const btn = document.querySelector('.copy-citation-btn');
    const originalText = btn.textContent;
    btn.textContent = '✓ Copied!';
    btn.style.background = '#27ae60';
    setTimeout(() => {
      btn.textContent = originalText;
      btn.style.background = '';
    }, 2000);
  }).catch(err => {
    alert('Gagal menyalin: ' + err);
  });
}

function sharePublication() {
  const title = '<?= addslashes($publikasi['judul']) ?>';
  const text = 'Publikasi ilmiah dari Lab Data Technology';
  const url = window.location.href;
  
  if (navigator.share) {
    navigator.share({
      title: title,
      text: text,
      url: url
    }).catch(err => {
      console.log('Error sharing:', err);
    });
  } else {
    navigator.clipboard.writeText(url).then(() => {
      alert('Link telah disalin ke clipboard:\n' + url);
    }).catch(err => {
      alert('Gagal menyalin link: ' + err);
    });
  }
}
</script>