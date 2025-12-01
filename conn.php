<?php
// conn.php
// Sesuaikan credential DB-mu
$DB_HOST = 'localhost';
$DB_PORT = '5432';
$DB_NAME = 'db_LabDT ';
$DB_USER = 'postgres';
$DB_PASS = 'crazyMamad13*';

$pdo = null;
$conn = null;

try {
    // Koneksi PDO
    $dsn = "pgsql:host={$DB_HOST};port={$DB_PORT};dbname={$DB_NAME};";
    $pdo = new PDO($dsn, $DB_USER, $DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    
    // Koneksi pg_connect (untuk backward compatibility)
    $conn_string = "host={$DB_HOST} port={$DB_PORT} dbname={$DB_NAME} user={$DB_USER} password={$DB_PASS}";
    $conn = pg_connect($conn_string);
    
    if (!$conn) {
        throw new Exception("Koneksi pg_connect gagal");
    }
    
} catch (PDOException $e) {
    http_response_code(500);
    echo "Koneksi PDO gagal: " . htmlspecialchars($e->getMessage());
    exit;
} catch (Exception $e) {
    http_response_code(500);
    echo "Koneksi database gagal: " . htmlspecialchars($e->getMessage());
    exit;
}