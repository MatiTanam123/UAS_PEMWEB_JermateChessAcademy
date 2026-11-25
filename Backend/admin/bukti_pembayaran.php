
<?php

require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../config/koneksi.php';

$u = require_role('admin');

$id = trim((string) ($_GET['id'] ?? ''));

$stmt = $db->prepare("
    SELECT bukti_pembayaran
    FROM pembayaran
    WHERE id_pembayaran = ?
    LIMIT 1
");

$stmt->bind_param('s', $id);
$stmt->execute();

$row = $stmt->get_result()->fetch_assoc();

$stmt->close();

if (!$row || !$row['bukti_pembayaran']) {
    http_response_code(404);
    exit('Bukti pembayaran tidak ditemukan.');
}

$path = realpath(__DIR__ . '/../' . $row['bukti_pembayaran']);
$base = realpath(__DIR__ . '/../uploads/payments');

if (
    !$path ||
    !$base ||
    !str_starts_with($path, $base . DIRECTORY_SEPARATOR) ||
    !is_file($path)
) {
    http_response_code(404);
    exit('File tidak ditemukan.');
}

$mime = (new finfo(FILEINFO_MIME_TYPE))->file($path);

if (!in_array($mime, [
    'image/jpeg',
    'image/png',
    'application/pdf'
], true)) {
    http_response_code(415);
    exit('Format file tidak diizinkan.');
}

header('Content-Type: ' . $mime);
header('X-Content-Type-Options: nosniff');
header('Content-Disposition: inline; filename="bukti-pembayaran"');
header('Content-Length: ' . filesize($path));

readfile($path);

exit;
