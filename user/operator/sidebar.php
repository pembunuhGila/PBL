<!-- Sidebar -->
<nav id="sidebar" class="col-md-3 col-lg-2 d-md-block sidebar">
    <div class="sidebar-header">
        <h4><i class="bi bi-layers"></i> Lab Operator</h4>
        <small>Management System</small>
    </div>
    <div class="position-sticky pt-3">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>" href="dashboard.php">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
            </li>
            
            <!-- SECTION: CONTENT MANAGEMENT -->
            <li class="nav-item mt-3">
                <small class="text-white-50 px-3">KONTEN WEBSITE</small>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'slider.php') ? 'active' : ''; ?>" href="slider.php">
                    <i class="bi bi-images"></i> Slider Homepage
                    <?php
                    // Cek pending slider operator
                    try {
                        $stmt_pending = $pdo->prepare("SELECT COUNT(*) FROM slider WHERE id_user = ? AND status = 'pending'");
                        $stmt_pending->execute([$_SESSION['id_user']]);
                        $count_pending = $stmt_pending->fetchColumn();
                        if ($count_pending > 0) {
                            echo '<span class="badge bg-warning ms-2">' . $count_pending . '</span>';
                        }
                    } catch (PDOException $e) {
                        // Silent fail
                    }
                    ?>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'konten.php') ? 'active' : ''; ?>" href="konten.php">
                    <i class="bi bi-file-text"></i> Konten
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'galeri.php') ? 'active' : ''; ?>" href="galeri.php">
                    <i class="bi bi-images"></i> Galeri
                </a>
            </li>
            
            <!-- SECTION: LAB DATA -->
            <li class="nav-item mt-3">
                <small class="text-white-50 px-3">DATA LABORATORIUM</small>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'anggota.php') ? 'active' : ''; ?>" href="anggota.php">
                    <i class="bi bi-people"></i> Anggota Lab
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'struktur.php') ? 'active' : ''; ?>" href="struktur.php">
                    <i class="bi bi-diagram-3"></i> Struktur Lab
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'publikasi.php') ? 'active' : ''; ?>" href="publikasi.php">
                    <i class="bi bi-journal-text"></i> Publikasi
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'fasilitas.php') ? 'active' : ''; ?>" href="fasilitas.php">
                    <i class="bi bi-building"></i> Fasilitas
                </a>
            </li>
            
            <!-- SECTION: ABOUT -->
            <li class="nav-item mt-3">
                <small class="text-white-50 px-3">PROFIL & KONTAK</small>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'tentang.php') ? 'active' : ''; ?>" href="tentang.php">
                    <i class="bi bi-info-circle"></i> Tentang Kami
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'kontak.php') ? 'active' : ''; ?>" href="kontak.php">
                    <i class="bi bi-telephone"></i> Kontak
                </a>
            </li>
            
            <!-- SECTION: SYSTEM -->
            <li class="nav-item mt-3">
                <small class="text-white-50 px-3">SISTEM</small>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'riwayat_pengajuan.php') ? 'active' : ''; ?>" href="riwayat_pengajuan.php">
                    <i class="bi bi-clock-history"></i> Riwayat Saya
                    <?php
                    // Cek jumlah pending milik operator
                    try {
                        $stmt_pending = $pdo->prepare("SELECT COUNT(*) FROM riwayat_pengajuan WHERE id_operator = ? AND status_baru = 'pending'");
                        $stmt_pending->execute([$_SESSION['id_user']]);
                        $count_pending = $stmt_pending->fetchColumn();
                        if ($count_pending > 0) {
                            echo '<span class="badge bg-warning ms-2">' . $count_pending . '</span>';
                        }
                    } catch (PDOException $e) {
                        // Silent fail
                    }
                    ?>
                </a>
            </li>
            
            <li class="nav-item mt-3">
                <a class="nav-link text-danger" href="logout.php">
                    <i class="bi bi-box-arrow-right"></i> Logout
                </a>
            </li>
        </ul>
    </div>
</nav>

<style>
    #sidebar {
        min-height: 100vh;
        background: linear-gradient(180deg, #1e3c72 0%, #2a5298 100%);
        transition: all 0.3s;
        overflow-y: auto;
    }
    
    #sidebar .nav-link {
        color: rgba(255, 255, 255, 0.8);
        padding: 12px 20px;
        margin: 5px 15px;
        border-radius: 8px;
        transition: all 0.3s;
        position: relative;
    }
    
    #sidebar .nav-link:hover {
        background: rgba(255, 255, 255, 0.1);
        color: white;
        transform: translateX(5px);
    }
    
    #sidebar .nav-link.active {
        background: rgba(255, 255, 255, 0.2);
        color: white;
        font-weight: 600;
    }
    
    #sidebar .nav-link.active::before {
        content: '';
        position: absolute;
        left: 0;
        top: 50%;
        transform: translateY(-50%);
        width: 4px;
        height: 70%;
        background: white;
        border-radius: 0 4px 4px 0;
    }
    
    #sidebar .nav-link i {
        margin-right: 10px;
        font-size: 1.1rem;
    }
    
    #sidebar .nav-link .badge {
        padding: 3px 8px;
        font-size: 0.75rem;
    }
    
    .sidebar-header {
        padding: 20px;
        background: rgba(0, 0, 0, 0.1);
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }
    
    .sidebar-header h4 {
        color: white;
        margin: 0;
        font-weight: 700;
    }
    
    .sidebar-header small {
        color: rgba(255, 255, 255, 0.7);
    }
    
    @media (max-width: 768px) {
        #sidebar {
            position: fixed;
            left: -100%;
            z-index: 1000;
            width: 280px;
        }
        
        #sidebar.show {
            left: 0;
        }
        
        .overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 999;
        }
        
        .overlay.show {
            display: block;
        }
    }
</style>