<?php
// tentang.php - Halaman Tentang Kami Lab Data Technology
$activePage = 'tentang';
include 'conn.php'; // Koneksi database
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
     PROFIL LAB (TANPA STATS)
============================================= -->
<section class="section profil-section">
  <div class="container">
    <h2 class="section-title">Profil Lab Data Technology</h2>
    <div class="profil-text-content">
      <p>
        Lab Data Technology merupakan salah satu laboratorium unggulan di Jurusan Teknologi Informasi, 
        Politeknik Negeri Malang yang berfokus pada bidang teknologi data, analitik, dan kecerdasan buatan.
      </p>
      <p>
        Laboratorium ini didirikan untuk mendukung kegiatan pembelajaran, penelitian, dan pengabdian 
        masyarakat dalam bidang data science, big data, machine learning, dan teknologi informasi terkini.
      </p>
      <p>
        Dengan fasilitas modern dan tim yang kompeten, Lab Data Technology berkomitmen untuk menghasilkan 
        lulusan yang siap bersaing di industri teknologi global dan berkontribusi dalam pengembangan 
        teknologi data di Indonesia.
      </p>
    </div>
  </div>
</section>

<!-- ============================================
     LOGO & IDENTITAS LAB
============================================= -->
<section class="section logo-section">
  <div class="container">
    <div class="logo-identity">
      <!-- 🖼️ LOGO LAB - ganti dengan logo lab Anda -->
      <div class="logo-box">
        <img src="assets/img/logo-lab-dt.png" alt="Logo Lab Data Technology" class="lab-logo">
      </div>
      
      <div class="logo-description">
        <h2 class="section-title" style="text-align: left; margin-bottom: 20px;">Identitas Lab</h2>
        <h3 style="color: var(--primary-blue); font-size: 22px; margin-bottom: 12px;">Lab Data Technology</h3>
        <p style="line-height: 1.8; color: #555; margin-bottom: 16px;">
          Logo Lab Data Technology melambangkan integrasi antara teknologi, data, dan inovasi. 
          Kombinasi warna biru dan hijau merepresentasikan profesionalisme, kepercayaan, dan pertumbuhan 
          dalam bidang teknologi informasi.
        </p>
        <p style="line-height: 1.8; color: #555;">
          Elemen-elemen dalam logo mencerminkan komitmen kami terhadap keunggulan akademik, 
          penelitian berkualitas, dan pengembangan sumber daya manusia di bidang data technology.
        </p>
      </div>
    </div>
  </div>
</section>

<!-- ============================================
     VISI & MISI
============================================= -->
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
        <p>
          Menjadi laboratorium teknologi data terkemuka yang menghasilkan lulusan berkualitas, 
          inovatif, dan kompetitif di tingkat nasional maupun internasional pada tahun 2030.
        </p>
      </div>
      
      <!-- MISI -->
      <div class="vm-card misi-card">
        <div class="vm-icon">
          <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
            <polyline points="14 2 14 8 20 8"></polyline>
            <line x1="16" y1="13" x2="8" y2="13"></line>
            <line x1="16" y1="17" x2="8" y2="17"></line>
            <polyline points="10 9 9 9 8 9"></polyline>
          </svg>
        </div>
        <h3>Misi</h3>
        <ul class="misi-list">
          <li>Menyelenggarakan pendidikan dan pembelajaran berbasis teknologi data yang berkualitas</li>
          <li>Melaksanakan penelitian dan pengembangan di bidang data science dan big data</li>
          <li>Menjalin kerjasama dengan industri dan institusi untuk meningkatkan kompetensi</li>
          <li>Mengembangkan SDM yang profesional dan beretika dalam bidang teknologi informasi</li>
          <li>Memberikan layanan pengabdian masyarakat melalui penerapan teknologi data</li>
        </ul>
      </div>
    </div>
  </div>
</section>

<!-- ============================================
     SEJARAH
============================================= -->
<section class="section sejarah-section">
  <div class="container">
    <h2 class="section-title">Sejarah Lab Data Technology</h2>
    
    <div class="timeline">
      <div class="timeline-item">
        <div class="timeline-marker"></div>
        <div class="timeline-content">
          <h4>2015</h4>
          <h5>Pendirian Laboratorium</h5>
          <p>
            Lab Data Technology didirikan sebagai respons terhadap kebutuhan industri akan tenaga 
            ahli di bidang analisis data dan teknologi informasi.
          </p>
        </div>
      </div>
      
      <div class="timeline-item">
        <div class="timeline-marker"></div>
        <div class="timeline-content">
          <h4>2017</h4>
          <h5>Pengembangan Fasilitas</h5>
          <p>
            Penambahan perangkat komputer berperforma tinggi dan software analisis data untuk 
            mendukung kegiatan praktikum dan penelitian mahasiswa.
          </p>
        </div>
      </div>
      
      <div class="timeline-item">
        <div class="timeline-marker"></div>
        <div class="timeline-content">
          <h4>2019</h4>
          <h5>Kerjasama Industri</h5>
          <p>
            Menjalin kerjasama dengan berbagai perusahaan teknologi untuk program magang, 
            penelitian kolaboratif, dan sertifikasi internasional.
          </p>
        </div>
      </div>
      
      <div class="timeline-item">
        <div class="timeline-marker"></div>
        <div class="timeline-content">
          <h4>2022</h4>
          <h5>Akreditasi & Penghargaan</h5>
          <p>
            Lab Data Technology meraih pengakuan sebagai salah satu laboratorium terbaik di 
            lingkungan Politeknik Negeri Malang dengan berbagai prestasi mahasiswa.
          </p>
        </div>
      </div>
      
      <div class="timeline-item">
        <div class="timeline-marker"></div>
        <div class="timeline-content">
          <h4>2025</h4>
          <h5>Era Digital & AI</h5>
          <p>
            Transformasi laboratorium dengan fokus pada Artificial Intelligence, Machine Learning, 
            dan Big Data Analytics untuk menghadapi era Revolusi Industri 4.0.
          </p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ============================================
     STRUKTUR ORGANISASI
============================================= -->
<section class="section struktur-section">
  <div class="container">
    <h2 class="section-title">Struktur Organisasi</h2>
    <p style="text-align: center; color: #666; margin-top: -20px; margin-bottom: 40px;">
      Tim pengelola Lab Data Technology
    </p>
    
    <div class="struktur-grid">
      <!-- PLACEHOLDER untuk data dari database -->
      
      <!-- Kepala Lab -->
      <div class="struktur-card kepala">
        <div class="struktur-photo">
          <!-- 🖼️ Foto: uploads/struktur/foto-nama.jpg -->
          <div class="photo-placeholder">Foto</div>
        </div>
        <h4>Dr. Nama Dosen, M.Kom</h4>
        <p class="jabatan">Kepala Laboratorium</p>
        <p class="nip">NIP: 198501012010121001</p>
      </div>
      
      <!-- Koordinator -->
      <div class="struktur-card">
        <div class="struktur-photo">
          <div class="photo-placeholder">Foto</div>
        </div>
        <h4>Nama Koordinator, S.Kom, M.T</h4>
        <p class="jabatan">Koordinator Praktikum</p>
        <p class="nip">NIP: 199001012015041001</p>
      </div>
      
      <!-- Teknisi -->
      <div class="struktur-card">
        <div class="struktur-photo">
          <div class="photo-placeholder">Foto</div>
        </div>
        <h4>Nama Teknisi, A.Md</h4>
        <p class="jabatan">Teknisi Laboratorium</p>
        <p class="nip">NIP: 199505012018031001</p>
      </div>
      
      <!-- Asisten Lab 1 -->
      <div class="struktur-card">
        <div class="struktur-photo">
          <div class="photo-placeholder">Foto</div>
        </div>
        <h4>Nama Asisten 1</h4>
        <p class="jabatan">Asisten Laboratorium</p>
        <p class="nip">NIM: 2241720001</p>
      </div>
      
      <!-- Asisten Lab 2 -->
      <div class="struktur-card">
        <div class="struktur-photo">
          <div class="photo-placeholder">Foto</div>
        </div>
        <h4>Nama Asisten 2</h4>
        <p class="jabatan">Asisten Laboratorium</p>
        <p class="nip">NIM: 2241720002</p>
      </div>
      
      <!-- Asisten Lab 3 -->
      <div class="struktur-card">
        <div class="struktur-photo">
          <div class="photo-placeholder">Foto</div>
        </div>
        <h4>Nama Asisten 3</h4>
        <p class="jabatan">Asisten Laboratorium</p>
        <p class="nip">NIM: 2241720003</p>
      </div>
      
    </div>
    
    <p style="text-align: center; margin-top: 40px; color: #888; font-size: 14px; font-style: italic;">
      * Data struktur organisasi akan diambil dari database secara dinamis
    </p>
  </div>
</section>

<?php include 'footer.php'; ?>