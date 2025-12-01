<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cek apakah user sudah login
if (!isset($_SESSION['id_user'])) {
    header('Location: ../../login.php');
    exit;
}

// Cek role jika diperlukan
if (isset($required_role) && $_SESSION['role'] !== $required_role) {
    header('Location: ../../login.php');
    exit;
}

// Set variabel global untuk user info
$current_id_user = $_SESSION['id_user'];
$current_username = $_SESSION['username'];
$current_nama = $_SESSION['nama'] ?? 'User';
$current_email = $_SESSION['email'] ?? 'user@lab.com';
$current_role = $_SESSION['role'];
$current_foto = $_SESSION['foto'] ?? null;

// Avatar URL
$avatar_url = !empty($current_foto) ? "../../" . $current_foto : "https://ui-avatars.com/api/?name=" . urlencode($current_nama) . "&background=random";
?>