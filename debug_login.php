<?php
// FILE DEBUG - Letakkan di root folder (sejajar dengan login.php)
// Akses via browser: http://localhost/debug_login.php

require_once __DIR__ . '/conn.php';

echo "<h2>🔍 DEBUG LOGIN SYSTEM</h2>";
echo "<hr>";

// 1. CEK KONEKSI DATABASE
echo "<h3>1. Koneksi Database</h3>";
if (isset($pdo)) {
    echo "✅ Koneksi database <strong>BERHASIL</strong><br>";
} else {
    echo "❌ Koneksi database <strong>GAGAL</strong><br>";
    die();
}

// 2. CEK DATA USER OPERATOR
echo "<hr><h3>2. Data User Operator di Database</h3>";
try {
    $stmt = $pdo->prepare("SELECT id_user, username, password, role, LENGTH(password) as panjang_hash FROM users WHERE username = 'operator'");
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        echo "✅ User operator <strong>DITEMUKAN</strong><br>";
        echo "<pre>";
        print_r($user);
        echo "</pre>";
        
        $db_password = $user['password'];
        $panjang = $user['panjang_hash'];
        
        echo "Password Hash di DB: <code>$db_password</code><br>";
        echo "Panjang Hash: <strong>$panjang karakter</strong> ";
        
        if ($panjang == 32) {
            echo "✅ (MD5 Valid)<br>";
        } else {
            echo "❌ (MD5 harus 32 karakter!)<br>";
        }
    } else {
        echo "❌ User operator <strong>TIDAK DITEMUKAN</strong><br>";
    }
} catch (PDOException $e) {
    echo "❌ Error query: " . $e->getMessage();
}

// 3. TEST PASSWORD HASH
echo "<hr><h3>3. Test Password Hash</h3>";

$test_passwords = [
    'operator1234',
    'operator123',
    'operator',
    'admin123'
];

echo "<table border='1' cellpadding='10' style='border-collapse:collapse;'>";
echo "<tr><th>Password Input</th><th>MD5 Hash</th><th>Match DB?</th></tr>";

foreach ($test_passwords as $pwd) {
    $hash = md5($pwd);
    $match = ($hash === $db_password) ? "✅ <strong>COCOK</strong>" : "❌ Tidak cocok";
    echo "<tr>";
    echo "<td><code>$pwd</code></td>";
    echo "<td><code>$hash</code></td>";
    echo "<td>$match</td>";
    echo "</tr>";
}

echo "</table>";

// 4. SIMULASI LOGIN
echo "<hr><h3>4. Simulasi Proses Login</h3>";

$test_user = 'operator';
$test_pass = 'operator1234';

echo "Username test: <code>$test_user</code><br>";
echo "Password test: <code>$test_pass</code><br>";
echo "MD5 password test: <code>" . md5($test_pass) . "</code><br><br>";

try {
    $query = "SELECT * FROM users WHERE username = :username LIMIT 1";
    $stmt = $pdo->prepare($query);
    $stmt->bindValue(':username', $test_user);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        echo "✅ User ditemukan<br>";
        
        $password_input = md5($test_pass);
        $password_db = $user['password'];
        
        echo "Password dari input (MD5): <code>$password_input</code><br>";
        echo "Password dari DB: <code>$password_db</code><br><br>";
        
        if ($password_input === $password_db) {
            echo "✅ <strong style='color:green;'>PASSWORD COCOK - LOGIN SEHARUSNYA BERHASIL!</strong><br>";
        } else {
            echo "❌ <strong style='color:red;'>PASSWORD TIDAK COCOK - LOGIN AKAN GAGAL!</strong><br>";
            
            // Cek karakter per karakter
            echo "<br><strong>Detail Perbandingan:</strong><br>";
            echo "Sama persis? " . ($password_input === $password_db ? "Ya" : "Tidak") . "<br>";
            echo "Sama (case insensitive)? " . (strtolower($password_input) === strtolower($password_db) ? "Ya" : "Tidak") . "<br>";
            
            // Cek hidden characters
            echo "<br><strong>Check Hidden Characters:</strong><br>";
            echo "Input bytes: " . bin2hex($password_input) . "<br>";
            echo "DB bytes: " . bin2hex($password_db) . "<br>";
        }
    } else {
        echo "❌ User tidak ditemukan<br>";
    }
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage();
}

// 5. REKOMENDASI
echo "<hr><h3>5. ✨ Solusi & Rekomendasi</h3>";
echo "<p><strong>Jika password tidak cocok, jalankan query ini di database:</strong></p>";
echo "<pre style='background:#f4f4f4;padding:10px;border:1px solid #ddd;'>";
echo "UPDATE users \n";
echo "SET password = '1c3b2b820d4d63d61bb64abd0c4f76d0' \n";
echo "WHERE username = 'operator';";
echo "</pre>";
echo "<p>Lalu login dengan:</p>";
echo "<ul>";
echo "<li>Username: <code>operator</code></li>";
echo "<li>Password: <code>operator1234</code></li>";
echo "</ul>";

echo "<hr>";
echo "<p><strong>⚠️ PENTING:</strong> Hapus file ini setelah selesai debug untuk keamanan!</p>";
?>