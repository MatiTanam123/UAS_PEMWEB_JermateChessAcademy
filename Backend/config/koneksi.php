<?php
declare(strict_types=1);
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$host = getenv('DB_HOST') ?: 'localhost';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') ?: '';
$name = getenv('DB_NAME') ?: 'jermate_chess_academy';
try {
    $db = new mysqli($host, $user, $pass, $name);
    $db->set_charset('utf8mb4');
} catch (mysqli_sql_exception $e) {
    error_log('Database connection error: '.$e->getMessage());
    http_response_code(500);
    exit('Koneksi database gagal. Periksa database/schema.sql dan config/koneksi.php.');
}
