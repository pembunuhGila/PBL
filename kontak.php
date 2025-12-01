<?php
// kontak.php - Halaman Kontak Lab Data Technology (Tanpa Form)
$activePage = 'kontak';
include 'conn.php'; // Koneksi database
include 'navbar.php'; // Navbar
?>
<link rel="stylesheet" href="assets/css/style.css">
<link rel="stylesheet" href="assets/css/kontak.css">

<!-- ============================================
     HERO SECTION KONTAK
============================================= -->
<div class="page-hero" style="background-image: url('assets/img/tentang-kami.jpeg');">
  <div class="page-hero-overlay">
    <div class="container">
      <h1 class="page-title">Hubungi Kami</h1>
      <p class="page-subtitle">Kami siap membantu menjawab pertanyaan Anda</p>
    </div>
  </div>
</div>

<!-- ============================================
     SECTION CONTACT CARDS
============================================= -->
<section class="section kontak-cards-section">
  <div class="container">
    <h2 class="section-title">Informasi Kontak</h2>
    <p style="text-align: center; color: #666; margin-top: -20px; margin-bottom: 50px; max-width: 700px; margin-left: auto; margin-right: auto; line-height: 1.7;">
      Hubungi kami melalui WhatsApp atau Email untuk pertanyaan, kerjasama, atau informasi lebih lanjut tentang Lab Data Technology
    </p>
    
    <div class="contact-cards-grid">
      
      <!-- WHATSAPP CARD -->
      <a href="https://wa.me/628123456789?text=Halo%20Lab%20Data%20Technology" target="_blank" class="contact-card whatsapp-card">
        <div class="card-icon">
          <svg width="40" height="40" viewBox="0 0 24 24" fill="currentColor">
            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
          </svg>
        </div>
        <h3>WhatsApp</h3>
        <p class="contact-value">+62 812-3456-789</p>
        <p class="contact-desc">Chat langsung dengan tim kami untuk respon cepat</p>
        <div class="card-action">
          <span>Chat Sekarang</span>
          <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path d="M5 12h14M12 5l7 7-7 7"/>
          </svg>
        </div>
      </a>
      
      <!-- EMAIL CARD -->
      <a href="mailto:datatechnologies.labti@gmail.com?subject=Pertanyaan tentang Lab Data Technology" class="contact-card email-card">
        <div class="card-icon">
          <svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
            <polyline points="22,6 12,13 2,6"></polyline>
          </svg>
        </div>
        <h3>Email</h3>
        <p class="contact-value">datatechnologies.labti@gmail.com</p>
        <p class="contact-desc">Kirim email untuk pertanyaan atau kerjasama resmi</p>
        <div class="card-action">
          <span>Kirim Email</span>
          <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path d="M5 12h14M12 5l7 7-7 7"/>
          </svg>
        </div>
      </a>
      
    </div>
  </div>
</section>

<!-- ============================================
     SECTION INFORMASI LAINNYA
============================================= -->
<section class="section info-lainnya-section">
  <div class="container">
    <h2 class="section-title">Informasi Lainnya</h2>
    
    <div class="info-grid">
      
      <!-- ALAMAT -->
      <div class="info-box">
        <div class="info-icon">
          <svg width="32" height="32" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
            <circle cx="12" cy="10" r="3"></circle>
          </svg>
        </div>
        <h3>Alamat Laboratorium</h3>
        <p>Gedung Sipil Lantai 8<br>
        Jurusan Teknologi Informasi<br>
        Politeknik Negeri Malang<br>
        Jl. Soekarno Hatta No.9<br>
        Malang, Jawa Timur 65141</p>
      </div>
      
      <!-- TELEPON -->
      <div class="info-box">
        <div class="info-icon">
          <svg width="32" height="32" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
          </svg>
        </div>
        <h3>Telepon</h3>
        <p>
          <strong>Kampus:</strong> +62 341 404424<br>
          <strong>Laboratorium:</strong> +62 812-3456-789<br>
          <br>
          <em style="color: #888; font-size: 13px;">Hubungi kami pada jam kerja</em>
        </p>
      </div>
      
      <!-- JAM OPERASIONAL -->
      <div class="info-box">
        <div class="info-icon">
          <svg width="32" height="32" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <circle cx="12" cy="12" r="10"></circle>
            <polyline points="12 6 12 12 16 14"></polyline>
          </svg>
        </div>
        <h3>Jam Operasional</h3>
        <p>
          <strong>Senin - Jumat</strong><br>
          08.00 - 16.00 WIB<br>
          <br>
          <strong>Sabtu - Minggu</strong><br>
          Tutup<br>
          <br>
          <em style="color: #888; font-size: 13px;">Kecuali hari libur nasional</em>
        </p>
      </div>
      
      <!-- MEDIA SOSIAL -->
      <div class="info-box">
        <div class="info-icon">
          <svg width="32" height="32" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <circle cx="12" cy="12" r="10"></circle>
            <path d="M8.56 2.75c4.37 6.03 6.02 9.42 8.03 17.72m2.54-15.38c-3.72 4.35-8.94 5.66-16.88 5.85m19.5 1.9c-3.5-.93-6.63-.82-8.94 0-2.58.92-5.01 2.86-7.44 6.32"></path>
          </svg>
        </div>
        <h3>Media Sosial</h3>
        <div class="social-links-alt">
          <a href="#" class="social-alt-link">
            <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
              <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
            </svg>
            Instagram
          </a>
          <a href="#" class="social-alt-link">
            <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
              <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
            </svg>
            YouTube
          </a>
          <a href="#" class="social-alt-link">
            <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
              <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
            </svg>
            Facebook
          </a>
        </div>
        <p style="margin-top: 16px; font-size: 14px; color: #666;">
          Follow kami untuk update terbaru
        </p>
      </div>
      
    </div>
  </div>
</section>

<!-- ============================================
     SECTION GOOGLE MAPS
============================================= -->
<section class="section maps-section">
  <div class="container">
    <h2 class="section-title">Lokasi Kami</h2>
    <p style="text-align: center; color: #666; margin-top: -20px; margin-bottom: 40px;">
      Kunjungi Lab Data Technology di kampus Politeknik Negeri Malang
    </p>
    
    <div class="maps-wrapper">
      <!-- 🗺️ GOOGLE MAPS EMBED - Politeknik Negeri Malang -->
      <iframe 
        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3951.4204766693547!2d112.61315207501!3d-7.946635392072286!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e78827687d272e7%3A0x789ce9a636cd3aa2!2sPoliteknik%20Negeri%20Malang!5e0!3m2!1sid!2sid!4v1699999999999!5m2!1sid!2sid" 
        width="100%" 
        height="450" 
        style="border:0; border-radius: 12px;" 
        allowfullscreen="" 
        loading="lazy" 
        referrerpolicy="no-referrer-when-downgrade">
      </iframe>
      
      <div class="maps-info">
        <p style="color: #666; line-height: 1.7; margin-bottom: 16px;">
          <strong style="color: var(--primary-blue);">Akses Transportasi:</strong><br>
          Lab Data Technology terletak di dalam kampus Politeknik Negeri Malang yang mudah diakses 
          dengan berbagai moda transportasi umum maupun kendaraan pribadi.
        </p>
        <a href="https://goo.gl/maps/xxxxxxx" target="_blank" class="direction-btn">
          <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
            <circle cx="12" cy="10" r="3"></circle>
          </svg>
          Dapatkan Petunjuk Arah
        </a>
      </div>
    </div>
  </div>
</section>

<?php include 'footer.php'; ?>