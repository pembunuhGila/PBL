<?php
// navbar.php - Updated navbar with logo text and link to Polinema
$activePage = basename($_SERVER['PHP_SELF'], ".php");
?>
<link rel="stylesheet" href="assets/css/style.css">
<nav class="main-navbar">
  <div class="nav-container">

    <!-- LOGO WITH TEXT - LINK KE WEBSITE POLINEMA -->
    <a href="https://jti.polinema.ac.id" target="_blank" class="nav-logo" style="text-decoration: none;">
      <img src="assets/img/Logo Polinema.png" alt="Logo Politeknik Negeri Malang">
      <div class="nav-logo-text">
        <strong>Jurusan Teknologi Informasi</strong>
        <span>Politeknik Negeri Malang</span>
      </div>
    </a>

    <!-- MENU (PILL SHAPE) -->
    <div class="nav-menu-wrapper">
      <ul class="nav-menu">
        <li class="<?= $activePage == 'index' ? 'active' : '' ?>">
          <a href="index.php">Beranda</a>
        </li>

        <li class="<?= $activePage == 'tentang' ? 'active' : '' ?>">
          <a href="tentang.php">Tentang Kami</a>
        </li>

        <li class="<?= $activePage == 'fasilitas' ? 'active' : '' ?>">
          <a href="fasilitas.php">Fasilitas</a>
        </li>

        <li class="<?= $activePage == 'konten' ? 'active' : '' ?>">
          <a href="konten.php">Konten</a>
        </li>

        <li class="<?= $activePage == 'publikasi' ? 'active' : '' ?>">
          <a href="publikasi.php">Publikasi</a>
        </li>

        <li class="<?= $activePage == 'galeri' ? 'active' : '' ?>">
          <a href="galeri.php">Galeri</a>
        </li>

        <li class="<?= $activePage == 'kontak' ? 'active' : '' ?>">
          <a href="kontak.php">Kontak</a>
        </li>
      </ul>
    </div>
  </div>
</nav>

<?php
// navbar.php - Updated navbar with logo text and link to Polinema
$activePage = basename($_SERVER['PHP_SELF'], ".php");
?>
<link rel="stylesheet" href="assets/css/style.css">
<nav class="main-navbar">
  <div class="nav-container">

    <!-- LOGO WITH TEXT - LINK KE WEBSITE POLINEMA -->
    <a href="https://jti.polinema.ac.id" target="_blank" class="nav-logo" style="text-decoration: none;">
      <img src="assets/img/Logo Polinema.png" alt="Logo Politeknik Negeri Malang">
      <div class="nav-logo-text">
        <strong>Jurusan Teknologi Informasi</strong>
        <span>Politeknik Negeri Malang</span>
      </div>
    </a>

    <!-- MENU (PILL SHAPE) -->
    <div class="nav-menu-wrapper">
      <ul class="nav-menu">
        <li class="<?= $activePage == 'index' ? 'active' : '' ?>">
          <a href="index.php">Beranda</a>
        </li>

        <li class="<?= $activePage == 'tentang' ? 'active' : '' ?>">
          <a href="tentang.php">Tentang Kami</a>
        </li>

        <li class="<?= $activePage == 'fasilitas' ? 'active' : '' ?>">
          <a href="fasilitas.php">Fasilitas</a>
        </li>

        <li class="<?= $activePage == 'konten' ? 'active' : '' ?>">
          <a href="konten.php">Konten</a>
        </li>

        <li class="<?= $activePage == 'publikasi' ? 'active' : '' ?>">
          <a href="publikasi.php">Publikasi</a>
        </li>

        <li class="<?= $activePage == 'galeri' ? 'active' : '' ?>">
          <a href="galeri.php">Galeri</a>
        </li>

        <li class="<?= $activePage == 'kontak' ? 'active' : '' ?>">
          <a href="kontak.php">Kontak</a>
        </li>
      </ul>
    </div>

    <a href="login.php" class="login-btn">
      <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path>
        <polyline points="10 17 15 12 10 7"></polyline>
        <line x1="15" y1="12" x2="3" y2="12"></line>
      </svg>
      Login
    </a>
  </div>
</nav>