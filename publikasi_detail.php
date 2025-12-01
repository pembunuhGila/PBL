<?php
// publikasi_detail.php - Detail Publikasi
$activePage = 'publikasi';
include 'navbar.php';
?>
<link rel="stylesheet" href="assets/css/style.css">
<link rel="stylesheet" href="assets/css/publikasi_detail.css">

<!-- BREADCRUMB -->
<div class="breadcrumb-section">
  <div class="container">
    <nav class="breadcrumb">
      <a href="index.php">Home</a>
      <span class="separator">›</span>
      <a href="publikasi.php">Publikasi</a>
      <span class="separator">›</span>
      <span class="current">Detail Publikasi</span>
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
          <div class="cover-placeholder-large">Cover Publikasi</div>
        </div>
        
        <!-- Content -->
        <div class="publikasi-content">
          
          <!-- Title -->
          <h1 class="publikasi-detail-title">
            Sistem Prediksi Penjualan Frozen Food dengan Metode Monte Carlo (Studi Kasus: Supermama Frozen Food)
          </h1>
          
          <!-- Meta Info -->
          <div class="publikasi-meta-detail">
            <div class="meta-item">
              <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                <circle cx="12" cy="7" r="4"></circle>
              </svg>
              <div>
                <span class="meta-label">Penulis:</span>
                <span class="meta-value">Dr. Ahmad Fauzi, M.Kom., Ir. Siti Nur Aini, M.T.</span>
              </div>
            </div>
            
            <div class="meta-item">
              <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                <line x1="16" y1="2" x2="16" y2="6"></line>
                <line x1="8" y1="2" x2="8" y2="6"></line>
                <line x1="3" y1="10" x2="21" y2="10"></line>
              </svg>
              <div>
                <span class="meta-label">Tahun:</span>
                <span class="meta-value">2025</span>
              </div>
            </div>
            
            <div class="meta-item">
              <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                <polyline points="14 2 14 8 20 8"></polyline>
              </svg>
              <div>
                <span class="meta-label">Tanggal Publikasi:</span>
                <span class="meta-value">15 Januari 2025</span>
              </div>
            </div>
          </div>
          
          <!-- Abstract / Deskripsi -->
          <div class="publikasi-section-box">
            <h2 class="section-box-title">Abstrak</h2>
            <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Penelitian ini bertujuan untuk mengembangkan sistem prediksi penjualan frozen food menggunakan metode Monte Carlo simulation. Metode ini dipilih karena kemampuannya dalam menangani ketidakpastian dan variabilitas data penjualan yang tinggi.</p>
            
            <p>Studi kasus dilakukan pada Supermama Frozen Food, sebuah perusahaan retail frozen food yang beroperasi di wilayah Jawa Timur. Data penjualan historis dari periode 2020-2024 digunakan sebagai basis untuk membangun model prediksi.</p>
            
            <p>Hasil penelitian menunjukkan bahwa metode Monte Carlo mampu memberikan prediksi dengan tingkat akurasi 87.5% dan dapat membantu manajemen dalam pengambilan keputusan terkait inventory management dan perencanaan produksi.</p>
          </div>
          
          <!-- Download / Action Buttons -->
          <div class="publikasi-actions">
            <a href="#" class="btn-download">
              <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                <polyline points="7 10 12 15 17 10"></polyline>
                <line x1="12" y1="15" x2="12" y2="3"></line>
              </svg>
              Download PDF
            </a>
            
            <a href="#" class="btn-cite">
              <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
              </svg>
              Cite This
            </a>
            
            <a href="#" class="btn-share">
              <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <circle cx="18" cy="5" r="3"></circle>
                <circle cx="6" cy="12" r="3"></circle>
                <circle cx="18" cy="19" r="3"></circle>
                <line x1="8.59" y1="13.51" x2="15.42" y2="17.49"></line>
                <line x1="15.41" y1="6.51" x2="8.59" y2="10.49"></line>
              </svg>
              Share
            </a>
          </div>
          
        </div>
        
      </main>
      
      <!-- SIDEBAR -->
      <aside class="detail-sidebar">
        
        <!-- Related Publications -->
        <div class="sidebar-widget">
          <h3 class="widget-title">Publikasi Terkait</h3>
          <ul class="related-list">
            <li class="related-item">
              <a href="#">
                <div class="related-cover">
                  <div class="related-cover-placeholder">Cover</div>
                </div>
                <div class="related-info">
                  <h4>Analisis Sentimen Media Sosial Menggunakan Deep Learning</h4>
                  <span class="related-year">2025</span>
                </div>
              </a>
            </li>
            <li class="related-item">
              <a href="#">
                <div class="related-cover">
                  <div class="related-cover-placeholder">Cover</div>
                </div>
                <div class="related-info">
                  <h4>Implementasi Machine Learning untuk Prediksi Kelulusan</h4>
                  <span class="related-year">2024</span>
                </div>
              </a>
            </li>
            <li class="related-item">
              <a href="#">
                <div class="related-cover">
                  <div class="related-cover-placeholder">Cover</div>
                </div>
                <div class="related-info">
                  <h4>Optimasi Algoritma K-Means untuk Segmentasi Pelanggan</h4>
                  <span class="related-year">2024</span>
                </div>
              </a>
            </li>
          </ul>
        </div>
        
      </aside>
      
    </div>
  </div>
</section>

<?php include 'footer.php'; ?>

<script>
// Download Button
document.querySelector('.btn-download').addEventListener('click', function(e) {
  e.preventDefault();
  alert('Download PDF akan diimplementasikan dengan link ke file PDF publikasi');
});

// Cite Button
document.querySelector('.btn-cite').addEventListener('click', function(e) {
  e.preventDefault();
  const citation = 'Fauzi, A., & Aini, S. N. (2025). Sistem Prediksi Penjualan Frozen Food dengan Metode Monte Carlo. Journal of Data Science and Analytics, 12(1).';
  navigator.clipboard.writeText(citation);
  alert('Citation copied to clipboard!\n\n' + citation);
});

// Share Button
document.querySelector('.btn-share').addEventListener('click', function(e) {
  e.preventDefault();
  if (navigator.share) {
    navigator.share({
      title: 'Publikasi - Lab Data Technology',
      text: 'Sistem Prediksi Penjualan Frozen Food dengan Metode Monte Carlo',
      url: window.location.href
    });
  } else {
    alert('Share link: ' + window.location.href);
  }
});
</script>