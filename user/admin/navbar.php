<!-- Main Content -->
<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
    <!-- Navbar -->
    <nav class="navbar navbar-light bg-white sticky-top mb-4">
        <div class="container-fluid">
            <button class="btn btn-outline-secondary" id="sidebarToggle">
                <i class="bi bi-list"></i>
            </button>
            <div class="d-flex align-items-center">
                <span class="me-3"><?php echo $_SESSION['nama'] ?? 'Admin User'; ?></span>
                <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['nama'] ?? 'Admin User'); ?>&background=1e3c72&color=fff" 
                     class="rounded-circle" width="40" height="40" alt="User">
            </div>
        </div>
    </nav>

    <!-- Content Area -->
    <div id="content">