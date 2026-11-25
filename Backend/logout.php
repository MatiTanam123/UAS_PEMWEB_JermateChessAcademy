
<?php

// ==========================================
// 1. IMPORT FILE AUTENTIKASI
// ==========================================

require __DIR__ . '/includes/auth.php';


// ==========================================
// 2. VALIDASI METHOD REQUEST
// ==========================================

// Logout hanya boleh dilakukan melalui POST request.

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header('Location: ' . FRONTEND_URL . '/index.html');

    exit;

}


// ==========================================
// 3. VALIDASI CSRF TOKEN
// ==========================================

csrf_check();


// ==========================================
// 4. MENGHAPUS DATA SESSION
// ==========================================

$_SESSION = [];


// ==========================================
// 5. MENGHAPUS COOKIE SESSION
// ==========================================

if (ini_get('session.use_cookies')) {

    // Mengambil konfigurasi cookie session
    $params = session_get_cookie_params();


    // Menghapus cookie dengan konfigurasi yang sama
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'] ?? '',
        $params['secure'] ?? false,
        $params['httponly'] ?? true
    );

}


// ==========================================
// 6. MENGHANCURKAN SESSION
// ==========================================

session_destroy();


// ==========================================
// 7. REDIRECT KE HALAMAN LOGIN
// ==========================================

header(
    'Location: ' . BACKEND_URL . '/login.php?logout=1'
);

exit;

?>
