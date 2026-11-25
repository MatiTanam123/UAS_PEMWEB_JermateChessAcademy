
<?php

if (!defined('PROJECT_URL')) {
    $scriptPath = str_replace('\\\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    $backendMarker = strpos($scriptPath, '/Backend');
    $projectUrl = ($backendMarker === false) ? '' : substr($scriptPath, 0, $backendMarker);
    define('PROJECT_URL', rtrim($projectUrl, '/'));
    define('BACKEND_URL', PROJECT_URL . '/Backend');
    define('FRONTEND_URL', PROJECT_URL . '/Frontend');
}
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params(['lifetime'=>0,'path'=>'/','httponly'=>true,'samesite'=>'Lax']);
    session_start();
}
function e($v): string { return htmlspecialchars((string)($v ?? ''), ENT_QUOTES|ENT_SUBSTITUTE, 'UTF-8'); }
function current_user(): ?array { return $_SESSION['user'] ?? null; }
function csrf_token(): string {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf']=bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}
function csrf_check(): void {
    if (!hash_equals($_SESSION['csrf'] ?? '', (string)($_POST['csrf'] ?? ''))) {
        http_response_code(419); exit('Sesi formulir kedaluwarsa. Muat ulang halaman.');
    }
}
function dashboard_url(string $role): string { return $role==='admin' ? 'admin/index.php' : 'peserta/index.php'; }
function require_role(string $role): array {
    $u=current_user();
    if (!$u) {
        $prefix=(str_contains($_SERVER['SCRIPT_NAME'] ?? '', '/admin/') || str_contains($_SERVER['SCRIPT_NAME'] ?? '', '/peserta/'))?'../':'';
        header('Location: '.$prefix.'login.php'); exit;
    }
    if (($u['role'] ?? '')!==$role) {
        $prefix=(str_contains($_SERVER['SCRIPT_NAME'] ?? '', '/admin/') || str_contains($_SERVER['SCRIPT_NAME'] ?? '', '/peserta/'))?'../':'';
        header('Location: '.$prefix.dashboard_url($u['role'])); exit;
    }
    return $u;
}
function flash(string $msg, string $type='success'): void { $_SESSION['flash']=['msg'=>$msg,'type'=>$type]; }
function show_flash(): void {
    if (!empty($_SESSION['flash'])) { $f=$_SESSION['flash']; echo '<div class="notice '.e($f['type']).'">'.e($f['msg']).'</div>'; unset($_SESSION['flash']); }
}
function new_id(string $prefix='JM'): string { return $prefix.'-'.strtoupper(bin2hex(random_bytes(10))); }
