<?php
$activePage = 'konten';
require_once 'conn.php';

$id_konten = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Ambil data konten
$stmt = $pdo->prepare("
    SELECT k.*, u.nama as author_name 
    FROM konten k
    LEFT JOIN users u ON k.id_user = u.id_user
    WHERE k.id_konten = ? AND k.status = 'active'
");
$stmt->execute([$id_konten]);
$konten = $stmt->fetch();

if (!$konten) {
    header("Location: konten.php");
    exit();
}

// Ambil konten terkait (kategori yang sama)
$stmt_related = $pdo->prepare("
    SELECT * FROM konten 
    WHERE status = 'active' 
    AND id_konten != ?
    AND kategori_konten = ?
    ORDER BY tanggal_posting DESC
    LIMIT 5
");
$stmt_related->execute([$id_konten, $konten['kategori_konten']]);
$related_konten = $stmt_related->fetchAll();

// Ambil konten berikutnya dan sebelumnya
$stmt_prev = $pdo->prepare("
    SELECT id_konten, judul FROM konten 
    WHERE status = 'active' AND tanggal_posting < ?
    ORDER BY tanggal_posting DESC LIMIT 1
");
$stmt_prev->execute([$konten['tanggal_posting']]);
$prev_konten = $stmt_prev->fetch();

$stmt_next = $pdo->prepare("
    SELECT id_konten, judul FROM konten 
    WHERE status = 'active' AND tanggal_posting > ?
    ORDER BY tanggal_posting ASC LIMIT 1
");
$stmt_next->execute([$konten['tanggal_posting']]);
$next_konten = $stmt_next->fetch();

// Get kategori count
$stmt_kategori = $pdo->query("
    SELECT kategori_konten, COUNT(*) as total 
    FROM konten 
    WHERE status = 'active' 
    GROUP BY kategori_konten 
    ORDER BY kategori_konten
");
$kategori_counts = $stmt_kategori->fetchAll();

include 'navbar.php';
?>
<link rel="stylesheet" href="assets/css/style.css">
<link rel="stylesheet" href="assets/css/konten_detail.css">

<style>
/* Enhanced Styles */
.article-header {
  position: relative;
  background: linear-gradient(135deg, #f8f9fb, #e8ebef);
  padding: 32px;
  border-radius: 12px;
  margin-bottom: 32px;
}

.article-meta {
  display: flex;
  gap: 24px;
  flex-wrap: wrap;
  align-items: center;
}

.meta-item {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 8px 16px;
  background: #fff;
  border-radius: 20px;
  font-size: 14px;
  color: #666;
  box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

/* Reading Progress Bar */
.reading-progress {
  position: fixed;
  top: 85px;
  left: 0;
  width: 0%;
  height: 4px;
  background: linear-gradient(90deg, var(--primary-blue), var(--accent-green));
  z-index: 1000;
  transition: width 0.1s;
}

/* Enhanced Content */
.article-content {
  font-size: 17px;
  line-height: 1.9;
  color: #333;
}

.article-content p:first-of-type:first-letter {
  font-size: 3em;
  font-weight: 700;
  float: left;
  margin: 0 10px 0 0;
  line-height: 0.9;
  color: var(--primary-blue);
}

.article-content blockquote {
  margin: 24px 0;
  padding: 20px 24px;
  background: #f8f9fb;
  border-left: 4px solid var(--primary-blue);
  font-style: italic;
  color: #666;
}

/* Table of Contents */
.toc-box {
  background: linear-gradient(135deg, #f8f9fb, #e8ebef);
  padding: 24px;
  border-radius: 12px;
  margin-bottom: 32px;
  border: 2px solid #e8ebef;
}

.toc-title {
  font-size: 16px;
  font-weight: 700;
  color: var(--primary-blue);
  margin-bottom: 12px;
  display: flex;
  align-items: center;
  gap: 8px;
}

.toc-list {
  list-style: none;
  padding: 0;
  margin: 0;
}

.toc-list li {
  margin-bottom: 8px;
}

.toc-list a {
  color: var(--text-dark);
  text-decoration: none;
  transition: color 0.3s;
  font-size: 14px;
}

.toc-list a:hover {
  color: var(--primary-blue);
}

/* Enhanced Share */
.article-share {
  background: linear-gradient(135deg, var(--primary-blue), #2563a8);
  color: #fff;
}

.share-label {
  color: #fff;
}

.share-btn {
  box-shadow: 0 2px 8px rgba(0,0,0,0.2);
}

/* Enhanced Sidebar */
.sidebar-widget {
  transition: transform 0.3s;
}

.sidebar-widget:hover {
  transform: translateY(-4px);
}

.berita-item {
  transition: all 0.3s;
  padding: 12px;
  margin: 0 -12px 12px -12px;
  border-radius: 8px;
}

.berita-item:hover {
  background: rgba(255,255,255,0.1);
}

/* Tags */
.article-tags {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
  margin-top: 24px;
  padding-top: 24px;
  border-top: 2px solid #e8ebef;
}

.tag-pill {
  padding: 6px 14px;
  background: #f8f9fb;
  border: 2px solid #e8ebef;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 600;
  color: var(--text-dark);
  text-decoration: none;
  transition: all 0.3s;
}

.tag-pill:hover {
  background: var(--primary-blue);
  color: #fff;
  border-color: var(--primary-blue);
}

/* Responsive Enhancements */
@media (max-width: 768px) {
  .article-content p:first-of-type:first-letter {
    font-size: 2em;
    margin: 0 8px 0 0;
  }
  
  .reading-progress {
    top: 70px;
  }
}
</style>

<!-- Reading Progress Bar -->
<div class="reading-progress" id="progressBar"></div>

<!-- BREADCRUMB -->
<div class="breadcrumb-section">
  <div class="container">
    <nav class="breadcrumb">
      <a href="index.php">Home</a>
      <span class="separator">›</span>
      <a href="konten.php">Konten</a>
      <span class="separator">›</span>
      <a href="konten.php?kategori=<?= urlencode($konten['kategori_konten']) ?>">
        <?= htmlspecialchars($konten['kategori_konten']) ?>
      </a>
      <span class="separator">›</span>
      <span class="current"><?= htmlspecialchars(substr($konten['judul'], 0, 30)) ?>...</span>
    </nav>
  </div>
</div>

<!-- DETAIL SECTION -->
<section class="detail-section">
  <div class="container">
    <div class="detail-layout">
      
      <!-- MAIN CONTENT -->
      <main class="detail-main">
        
        <!-- Featured Image -->
        <div class="detail-featured">
          <?php if ($konten['gambar'] && file_exists('uploads/konten/' . $konten['gambar'])): ?>
            <img src="uploads/konten/<?= htmlspecialchars($konten['gambar']) ?>" alt="<?= htmlspecialchars($konten['judul']) ?>" loading="lazy">
          <?php else: ?>
            <div class="featured-placeholder">
              <svg width="80" height="80" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                <line x1="16" y1="2" x2="16" y2="6"></line>
                <line x1="8" y1="2" x2="8" y2="6"></line>
                <line x1="3" y1="10" x2="21" y2="10"></line>
              </svg>
              <p style="margin-top: 12px; color: #999; font-size: 16px;">Featured Image</p>
            </div>
          <?php endif; ?>
        </div>
        
        <!-- Article Header -->
        <div class="article-header">
          <?php
          $badge_class = 'badge-berita';
          if ($konten['kategori_konten'] === 'Pengumuman') $badge_class = 'badge-pengumuman';
          if ($konten['kategori_konten'] === 'Agenda') $badge_class = 'badge-agenda';
          ?>
          <span class="article-badge <?= $badge_class ?>">
            <?= htmlspecialchars($konten['kategori_konten']) ?>
          </span>
          
          <h1 class="article-title"><?= htmlspecialchars($konten['judul']) ?></h1>
          
          <div class="article-meta">
            <span class="meta-item">
              <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                <line x1="16" y1="2" x2="16" y2="6"></line>
                <line x1="8" y1="2" x2="8" y2="6"></line>
                <line x1="3" y1="10" x2="21" y2="10"></line>
              </svg>
              <?= date('d F Y', strtotime($konten['tanggal_posting'])) ?>
            </span>
            
            <?php if ($konten['author_name']): ?>
            <span class="meta-item">
              <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                <circle cx="12" cy="7" r="4"></circle>
              </svg>
              <?= htmlspecialchars($konten['author_name']) ?>
            </span>
            <?php endif; ?>
            
            <span class="meta-item">
              <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="10"></circle>
                <polyline points="12 6 12 12 16 14"></polyline>
              </svg>
              <?php 
              $word_count = str_word_count(strip_tags($konten['isi']));
              $read_time = $word_count > 0 ? ceil($word_count / 200) : 1;
              echo $read_time;
              ?> min read
            </span>
          </div>
        </div>
        
        <!-- Article Content -->
        <div class="article-content">
          <?= nl2br(htmlspecialchars($konten['isi'])) ?>
        </div>
        
        <!-- Tags -->
        <div class="article-tags">
          <span style="font-weight: 700; color: #666;">🏷️ Tags:</span>
          <a href="konten.php?kategori=<?= urlencode($konten['kategori_konten']) ?>" class="tag-pill">
            <?= htmlspecialchars($konten['kategori_konten']) ?>
          </a>
          <a href="konten.php" class="tag-pill">Lab Data Technology</a>
          <a href="konten.php" class="tag-pill">Teknologi Informasi</a>
        </div>
        
        <!-- Share Buttons -->
        <div class="article-share">
          <span class="share-label">📤 Bagikan Artikel:</span>
          <div class="share-buttons">
            <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) ?>" class="share-btn facebook" target="_blank" rel="noopener noreferrer">
              <svg width="18" height="18" fill="currentColor" viewBox="0 0 24 24">
                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
              </svg>
            </a>
            <a href="https://twitter.com/intent/tweet?url=<?= urlencode('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) ?>&text=<?= urlencode($konten['judul']) ?>" class="share-btn twitter" target="_blank" rel="noopener noreferrer">
              <svg width="18" height="18" fill="currentColor" viewBox="0 0 24 24">
                <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
              </svg>
            </a>
            <a href="https://wa.me/?text=<?= urlencode($konten['judul'] . ' - http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) ?>" class="share-btn whatsapp" target="_blank" rel="noopener noreferrer">
              <svg width="18" height="18" fill="currentColor" viewBox="0 0 24 24">
                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
              </svg>
            </a>
          </div>
        </div>
        
        <!-- Navigation Prev/Next -->
        <div class="article-navigation">
          <?php if ($prev_konten): ?>
          <a href="konten_detail.php?id=<?= $prev_konten['id_konten'] ?>" class="nav-link prev">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path d="M15 18l-6-6 6-6"/>
            </svg>
            <div>
              <span>Previous</span>
              <strong><?= htmlspecialchars(substr($prev_konten['judul'], 0, 40)) ?>...</strong>
            </div>
          </a>
          <?php else: ?>
          <div></div>
          <?php endif; ?>
          
          <?php if ($next_konten): ?>
          <a href="konten_detail.php?id=<?= $next_konten['id_konten'] ?>" class="nav-link next">
            <div>
              <span>Next</span>
              <strong><?= htmlspecialchars(substr($next_konten['judul'], 0, 40)) ?>...</strong>
            </div>
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path d="M9 18l6-6-6-6"/>
            </svg>
          </a>
          <?php endif; ?>
        </div>
        
      </main>
      
      <!-- SIDEBAR -->
      <aside class="detail-sidebar">
        
        <!-- Konten Terbaru -->
        <?php if (count($related_konten) > 0): ?>
        <div class="sidebar-widget">
          <h3 class="widget-title">📰 <?= htmlspecialchars($konten['kategori_konten']) ?> Terbaru</h3>
          <ul class="berita-list">
            <?php foreach($related_konten as $rel): ?>
            <li class="berita-item">
              <a href="konten_detail.php?id=<?= $rel['id_konten'] ?>">
                <h4><?= htmlspecialchars($rel['judul']) ?></h4>
                <span class="berita-date"><?= date('d M Y', strtotime($rel['tanggal_posting'])) ?></span>
              </a>
            </li>
            <?php endforeach; ?>
          </ul>
        </div>
        <?php endif; ?>
        
        <!-- Kategori -->
        <div class="sidebar-widget">
          <h3 class="widget-title">🗂️ Kategori</h3>
          <ul class="kategori-sidebar-list">
            <?php foreach($kategori_counts as $kat): ?>
            <li>
              <a href="konten.php?kategori=<?= urlencode($kat['kategori_konten']) ?>">
                <?= htmlspecialchars($kat['kategori_konten']) ?>
                <span class="kat-count">(<?= $kat['total'] ?>)</span>
              </a>
            </li>
            <?php endforeach; ?>
          </ul>
        </div>
        
      </aside>
      
    </div>
  </div>
</section>

<?php include 'footer.php'; ?>

<script>
// Reading Progress Bar
window.addEventListener('scroll', function() {
  const winScroll = document.body.scrollTop || document.documentElement.scrollTop;
  const height = document.documentElement.scrollHeight - document.documentElement.clientHeight;
  const scrolled = (winScroll / height) * 100;
  const progressBar = document.getElementById('progressBar');
  if (progressBar) {
    progressBar.style.width = scrolled + '%';
  }
});

// Smooth scroll for TOC links (if exists)
document.querySelectorAll('.toc-list a').forEach(anchor => {
  anchor.addEventListener('click', function (e) {
    e.preventDefault();
    const target = document.querySelector(this.getAttribute('href'));
    if (target) {
      target.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
  });
});
</script>