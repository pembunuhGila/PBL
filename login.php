<?php
// Cek session dulu sebelum start
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Jika sudah login, redirect sesuai role
if (isset($_SESSION['id_user'])) {
    if ($_SESSION['role'] === 'admin') {
        header('Location: user/admin/dashboard.php');
    } else {
        header('Location: user/operator/dashboard.php');
    }
    exit;
}

// Load koneksi database
require_once __DIR__ . '/conn.php';

if (!isset($pdo)) {
    die("Koneksi database tidak tersedia.");
}

$error = "";
$debug_mode = isset($_GET['debug']); // Aktifkan dengan ?debug=1

// PROSES LOGIN
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username'] ?? '');
    $password_raw = trim($_POST['password'] ?? '');

    if ($username === '' || $password_raw === '') {
        $error = "Isi username dan password.";
    } else {

        $query = "SELECT * FROM users WHERE username = :username LIMIT 1";

        try {
            $stmt = $pdo->prepare($query);
            $stmt->bindValue(':username', $username);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                
                // Hash password dengan MD5
                $password_hashed = md5($password_raw);
                
                // Debug info
                if ($debug_mode) {
                    echo "<pre>";
                    echo "Username Input: " . $username . "\n";
                    echo "Password Input: " . $password_raw . "\n";
                    echo "Password MD5: " . $password_hashed . "\n";
                    echo "DB Password: " . $user['password'] . "\n";
                    echo "Match: " . ($password_hashed === $user['password'] ? "YES" : "NO") . "\n";
                    echo "</pre>";
                    exit;
                }

                // Cocokkan password
                if ($password_hashed === $user['password']) {

                    // SET SESSION
                    $_SESSION['id_user'] = $user['id_user'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['nama'] = $user['nama'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['foto'] = null;

                    // Redirect sesuai role
                    if ($user['role'] === 'admin') {
                        header("Location: user/admin/dashboard.php");
                    } else {
                        header("Location: user/operator/dashboard.php");
                    }
                    exit;

                } else {
                    $error = "Password salah!";
                }

            } else {
                $error = "Username tidak ditemukan!";
            }

        } catch (PDOException $e) {
            $error = "Database Error: " . $e->getMessage();
        }

    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - Lab Data Technology</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/login.css">
</head>
<body>
  
  <!-- LOGIN CONTAINER -->
  <div class="login-wrapper">
    
    <!-- LEFT SIDE - Form -->
    <div class="login-left">
      <div class="login-box">
        
        <!-- Logo & Title -->
        <div class="login-header">
          <img src="assets/img/Logo Polinema.png" alt="Logo Polinema" class="login-logo">
          <h1>Lab Data Technology</h1>
          <p>Admin Dashboard Login</p>
        </div>
        
        <!-- Login Form -->
        <form class="login-form" id="loginForm" method="POST">
          
          <!-- Alert Error dari PHP -->
          <?php if (!empty($error)): ?>
          <div class="alert alert-error" id="alertBox">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <circle cx="12" cy="12" r="10"></circle>
              <line x1="12" y1="8" x2="12" y2="12"></line>
              <line x1="12" y1="16" x2="12.01" y2="16"></line>
            </svg>
            <span id="alertMessage"><?php echo $error; ?></span>
          </div>
          <?php endif; ?>
          
          <!-- Debug Info (hanya jika ?debug=1) -->
          <?php if ($debug_mode && isset($_POST['username'])): ?>
          <div class="alert" style="background: #fff3cd; color: #856404; border-left: 4px solid #ffc107;">
            <strong>🐛 DEBUG MODE</strong><br>
            <small>Username: <?php echo htmlspecialchars($_POST['username']); ?></small><br>
            <small>Cek console/terminal untuk detail</small>
          </div>
          <?php endif; ?>
          
          <!-- Username -->
          <div class="form-group">
            <label for="username">Username</label>
            <div class="input-wrapper">
              <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" class="input-icon">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                <circle cx="12" cy="7" r="4"></circle>
              </svg>
              <input type="text" 
                     id="username" 
                     name="username" 
                     placeholder="Masukkan username" 
                     value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                     autocomplete="username"
                     required>
            </div>
          </div>
          
          <!-- Password -->
          <div class="form-group">
            <label for="password">Password</label>
            <div class="input-wrapper">
              <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" class="input-icon">
                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
              </svg>
              <input type="password" 
                     id="password" 
                     name="password" 
                     placeholder="Masukkan password" 
                     autocomplete="current-password"
                     required>
              <button type="button" class="toggle-password" id="togglePassword">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" id="eyeIcon">
                  <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                  <circle cx="12" cy="12" r="3"></circle>
                </svg>
              </button>
            </div>
          </div>

          <!-- Submit Button -->
          <button type="submit" class="btn-login">
            <span class="btn-text">Login</span>
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" class="btn-icon">
              <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4M10 17l5-5-5-5M15 12H3"/>
            </svg>
          </button>
        
        <!-- Footer -->
        <div class="login-footer">
          <a href="index.php" class="back-home">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
              <polyline points="9 22 9 12 15 12 15 22"></polyline>
            </svg>
            Kembali ke Beranda
          </a>
        </div>
        
      </div>
    </div>
    
    <!-- RIGHT SIDE - Image/Pattern -->
    <div class="login-right">
      <div class="login-overlay">
        <div class="welcome-text">
          <h2>Welcome Back!</h2>
          <p>Kelola konten dan data Lab Data Technology dengan mudah</p>
        </div>
        
        <!-- Decorative Pattern -->
        <div class="pattern-dots">
          <div class="dot"></div>
          <div class="dot"></div>
          <div class="dot"></div>
          <div class="dot"></div>
          <div class="dot"></div>
          <div class="dot"></div>
          <div class="dot"></div>
          <div class="dot"></div>
          <div class="dot"></div>
        </div>
      </div>
    </div>
    
  </div>
  
  <script>
    // Toggle Password Visibility
    const togglePassword = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('password');
    const eyeIcon = document.getElementById('eyeIcon');
    
    if (togglePassword) {
      togglePassword.addEventListener('click', function() {
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        
        // Toggle icon
        if(type === 'text') {
          eyeIcon.innerHTML = `
            <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
            <line x1="1" y1="1" x2="23" y2="23"></line>
          `;
        } else {
          eyeIcon.innerHTML = `
            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
            <circle cx="12" cy="12" r="3"></circle>
          `;
        }
      });
    }
    
    // Hide alert when user starts typing
    const alertBox = document.getElementById('alertBox');
    if(alertBox) {
      const usernameInput = document.getElementById('username');
      const passwordInputField = document.getElementById('password');
      
      if (usernameInput) {
        usernameInput.addEventListener('input', function() {
          alertBox.style.display = 'none';
        });
      }
      
      if (passwordInputField) {
        passwordInputField.addEventListener('input', function() {
          alertBox.style.display = 'none';
        });
      }
    }
    
    // Auto-fill untuk testing (HAPUS DI PRODUCTION!)
    // Uncomment baris ini untuk testing cepat
    // document.getElementById('username').value = 'operator';
    // document.getElementById('password').value = 'operator123';
  </script>
  
</body>
</html>