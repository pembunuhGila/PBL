<?php
// konten_detail.php - Detail Konten (Pure Front-End)
$activePage = 'konten';
include 'navbar.php';
?>
<link rel="stylesheet" href="assets/css/style.css">
<link rel="stylesheet" href="assets/css/konten_detail.css">

<!-- HERO BREADCRUMB -->
<div class="breadcrumb-section">
  <div class="container">
    <nav class="breadcrumb">
      <a href="index.php">Home</a>
      <span class="separator">›</span>
      <a href="konten.php">Konten</a>
      <span class="separator">›</span>
      <a href="konten.php?kategori=berita">Berita</a>
      <span class="separator">›</span>
      <span class="current">Judul Artikel</span>
    </nav>
  </div>
</div>

<!-- DETAIL SECTION -->
<section class="detail-section">
  <div class="container">
    <div class="detail-layout">
      
      <!-- MAIN CONTENT KIRI -->
      <main class="detail-main">
        
        <!-- Featured Image -->
        <div class="detail-featured">
          <div class="featured-placeholder">Featured Image</div>
        </div>
        
        <!-- Article Header -->
        <div class="article-header">
          <span class="article-badge badge-berita">
            Berita
          </span>
          
          <h1 class="article-title">Mahasiswa Jurusan Teknologi Informasi Raih Juara di Kompetisi Data Science Nasional</h1>
          
          <div class="article-meta">
            <span class="meta-item">
              <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                <line x1="16" y1="2" x2="16" y2="6"></line>
                <line x1="8" y1="2" x2="8" y2="6"></line>
                <line x1="3" y1="10" x2="21" y2="10"></line>
              </svg>
              October 31, 2025
            </span>
            
            <span class="meta-item">
              <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                <circle cx="12" cy="7" r="4"></circle>
              </svg>
              Admin Lab DT
            </span>
            
            <span class="meta-item">
              <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                <circle cx="12" cy="12" r="3"></circle>
              </svg>
              5 Comment
            </span>
          </div>
        </div>
        
        <!-- Article Content -->
        <div class="article-content">
          <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aliquam vitae, congue ante. Sed aliquet lacus eget lacus egestas, vel eleifend ex tempus. Aliquam tristique erat nunc at sagittis. Nulla vitae sagittis ligula. Aliquam tempor augue at gravida dictum. In sit amet posuere, massa in consequat est. Suspendisse quis magna cursus, consectetur, pede et, lobortis mauris. Mauris ultrices nulla at, sit amet consequat. Aenean blandit suscipit. Proin facilisis massa sed dolor ultrices, non elementum, tortor lacus. Nunc commodo mi libero, at dapibus est tristique ac. Pellentesque id justo velit.</p>
          
          <p>Proin molestie mauris nec cursus. Cras cursus elit quis velit pretium ornare. Nam egestas velit id sollicitudin rutrum. Praesent, ullamcorper sem vitae mauris molestie vestibulum. Phasellus rhoncus, mauris metus. Fusce efficitur imperdiet justo ut lacinia nulla fermentum, at. Sed bibendum tempor ipsum ut dignissim. Aenean ut maximus quam, vitae feugiat neque. Phasellus at nunc eu est aliquam vulputate vitae, aliquam libero. Nulla semper nulla magna, eget accumsan ante faucibus vel.</p>
        </div>
        
        <!-- Share Buttons -->
        <div class="article-share">
          <span class="share-label">Share:</span>
          <div class="share-buttons">
            <a href="#" class="share-btn facebook">
              <svg width="18" height="18" fill="currentColor" viewBox="0 0 24 24">
                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
              </svg>
            </a>
            <a href="#" class="share-btn twitter">
              <svg width="18" height="18" fill="currentColor" viewBox="0 0 24 24">
                <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
              </svg>
            </a>
            <a href="#" class="share-btn whatsapp">
              <svg width="18" height="18" fill="currentColor" viewBox="0 0 24 24">
                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
              </svg>
            </a>
          </div>
        </div>
        
        <!-- Navigation Prev/Next -->
        <div class="article-navigation">
          <a href="#" class="nav-link prev">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path d="M15 18l-6-6 6-6"/>
            </svg>
            <div>
              <span>Previous</span>
              <strong>Artikel Sebelumnya</strong>
            </div>
          </a>
          
          <a href="#" class="nav-link next">
            <div>
              <span>Next</span>
              <strong>Artikel Selanjutnya</strong>
            </div>
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path d="M9 18l6-6-6-6"/>
            </svg>
          </a>
        </div>
        
      </main>
      
      <!-- SIDEBAR KANAN -->
      <aside class="detail-sidebar">
        
        <!-- Berita Terbaru -->
        <div class="sidebar-widget">
          <h3 class="widget-title">Berita terbaru</h3>
          <ul class="berita-list">
            <li class="berita-item">
              <a href="#">
                <h4>BEASISWA UNGGULAN BAGI MAHASISWA BERPRESTASI DAN PENYANDANG DISABILITAS 2025</h4>
                <span class="berita-date">Juli 5, 2025</span>
              </a>
            </li>
            <li class="berita-item">
              <a href="#">
                <h4>BATAS PENDAFTARAN DAN PELAKSANAAN UJIAN SKRIPSI TAHAP III TAHUN AJARAN 2024/2025</h4>
                <span class="berita-date">Juli 5, 2025</span>
              </a>
            </li>
            <li class="berita-item">
              <a href="#">
                <h4>BATAS PENDAFTARAN DAN PELAKSANAAN UJIAN SKRIPSI TAHAP II TAHUN AJARAN 2024/2025</h4>
                <span class="berita-date">Juli 5, 2025</span>
              </a>
            </li>
            <li class="berita-item">
              <a href="#">
                <h4>BATAS PENDAFTARAN BAGI TAL ANGKATAN LULUS SKRIPSI TAHAP III TAHUN AJARAN 2024/2025</h4>
                <span class="berita-date">Juli 5, 2025</span>
              </a>
            </li>
            <li class="berita-item">
              <a href="#">
                <h4>BATAS PENDAFTARAN DAN PELAKSANAAN UJIAN SKRIPSI TAHAP II TAHUN AJARAN 2024/2025</h4>
                <span class="berita-date">Juli 3, 2025</span>
              </a>
            </li>
          </ul>
        </div>
        
        <!-- Kategori -->
        <div class="sidebar-widget">
          <h3 class="widget-title">Kategori</h3>
          <ul class="kategori-sidebar-list">
            <li>
              <a href="konten.php?kategori=agenda">
                Agenda
                <span class="kat-count">(12)</span>
              </a>
            </li>
            <li>
              <a href="konten.php?kategori=berita">
                Berita
                <span class="kat-count">(45)</span>
              </a>
            </li>
            <li>
              <a href="konten.php?kategori=pengumuman">
                Pengumuman
                <span class="kat-count">(23)</span>
              </a>
            </li>
            <li>
              <a href="konten.php">
                Lainnya
                <span class="kat-count">(8)</span>
              </a>
            </li>
          </ul>
        </div>
        
      </aside>
      
    </div>
  </div>
</section>

<?php include 'footer.php'; ?>