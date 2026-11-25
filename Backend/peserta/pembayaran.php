<?php
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../config/koneksi.php';
require __DIR__ . '/../includes/peserta_layout.php';

$u = require_role('peserta');
$idPeserta = (string)$u['id'];
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $idPendaftaran = trim((string)($_POST['id_pendaftaran'] ?? ''));
    $method = (string)($_POST['metode'] ?? 'Transfer');
    $allowedMethods = ['Transfer', 'Tunai', 'QRIS'];
    $file = $_FILES['bukti'] ?? null;
    $relative = null;

    try {
        if ($idPendaftaran === '') throw new RuntimeException('Pilih pendaftaran yang akan dibayar.');
        if (!in_array($method, $allowedMethods, true)) throw new RuntimeException('Metode pembayaran tidak valid.');
        if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) throw new RuntimeException('Bukti pembayaran wajib diunggah.');
        if (($file['size'] ?? 0) <= 0 || ($file['size'] ?? 0) > 3 * 1024 * 1024 || !is_uploaded_file($file['tmp_name'])) {
            throw new RuntimeException('File tidak valid atau ukurannya melebihi 3 MB.');
        }
        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
        $types = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'application/pdf' => 'pdf'];
        if (!isset($types[$mime])) throw new RuntimeException('Bukti harus berupa JPG, PNG, atau PDF.');
        if (str_starts_with($mime, 'image/') && @getimagesize($file['tmp_name']) === false) {
            throw new RuntimeException('File gambar tidak valid.');
        }

        $db->begin_transaction();

        // Kunci baris pendaftaran agar dua permintaan pembayaran bersamaan tidak lolos.
        $q = $db->prepare("SELECT d.id_pendaftaran, d.status_pendaftaran, p.nama_program, p.biaya
            FROM pendaftaran d JOIN program p ON p.id_program = d.id_program
            WHERE d.id_pendaftaran = ? AND d.id_peserta = ? FOR UPDATE");
        $q->bind_param('ss', $idPendaftaran, $idPeserta);
        $q->execute();
        $reg = $q->get_result()->fetch_assoc();
        $q->close();

        if (!$reg) throw new RuntimeException('Pendaftaran tidak ditemukan.');
        if ($reg['status_pendaftaran'] !== 'disetujui') {
            throw new RuntimeException('Pembayaran hanya dapat dilakukan setelah pendaftaran disetujui admin.');
        }
        $total = (float)$reg['biaya'];
        if ($total <= 0) throw new RuntimeException('Biaya program belum diatur dengan benar. Hubungi admin.');

        // Satu pembayaran penuh: tidak menerima cicilan atau pembayaran berulang.
        $q = $db->prepare("SELECT id_pembayaran, status_pembayaran FROM pembayaran
            WHERE id_pendaftaran = ? AND status_pembayaran IN ('menunggu', 'terverifikasi')
            LIMIT 1 FOR UPDATE");
        $q->bind_param('s', $idPendaftaran);
        $q->execute();
        $existing = $q->get_result()->fetch_assoc();
        $q->close();
        if ($existing) {
            if ($existing['status_pembayaran'] === 'terverifikasi') {
                throw new RuntimeException('Pembayaran program ini sudah lunas dan terverifikasi.');
            }
            throw new RuntimeException('Bukti pembayaran sebelumnya masih menunggu verifikasi admin.');
        }

        $dir = __DIR__ . '/../uploads/payments/';
        if (!is_dir($dir) && !mkdir($dir, 0750, true) && !is_dir($dir)) {
            throw new RuntimeException('Folder penyimpanan bukti tidak dapat dibuat.');
        }
        $filename = 'pay_' . bin2hex(random_bytes(16)) . '.' . $types[$mime];
        if (!move_uploaded_file($file['tmp_name'], $dir . $filename)) {
            throw new RuntimeException('Gagal menyimpan bukti pembayaran.');
        }
        $relative = 'uploads/payments/' . $filename;

        $code = 'JM-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(4)));
        $newId = new_id('PAY');
        $status = 'menunggu';
        $date = date('Y-m-d H:i:s');
        // Nominal selalu diambil dari biaya program di database, bukan dari input browser.
        $ins = $db->prepare("INSERT INTO pembayaran
            (id_pembayaran, id_pendaftaran, kode_transaksi, jumlah_pembayaran,
             metode_pembayaran, bukti_pembayaran, status_pembayaran, tanggal_bayar)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $ins->bind_param('sssdssss', $newId, $idPendaftaran, $code, $total, $method, $relative, $status, $date);
        $ins->execute();
        $ins->close();

        $db->commit();
        flash('Bukti pembayaran penuh berhasil dikirim. Menunggu verifikasi admin.');
        header('Location: pembayaran.php');
        exit;
    } catch (Throwable $e) {
        try { $db->rollback(); } catch (Throwable $ignored) {}
        if ($relative && is_file(__DIR__ . '/../' . $relative)) @unlink(__DIR__ . '/../' . $relative);
        $error = $e instanceof mysqli_sql_exception
            ? 'Pembayaran gagal disimpan. Periksa data dan coba kembali.'
            : $e->getMessage();
    }
}

$sql = $db->prepare("SELECT d.id_pendaftaran, p.nama_program, p.biaya, d.status_pendaftaran
    FROM pendaftaran d JOIN program p ON p.id_program = d.id_program
    WHERE d.id_peserta = ? AND d.status_pendaftaran = 'disetujui'
      AND NOT EXISTS (
        SELECT 1 FROM pembayaran b WHERE b.id_pendaftaran = d.id_pendaftaran
          AND b.status_pembayaran IN ('menunggu', 'terverifikasi')
      )
    ORDER BY d.tanggal_daftar DESC");
$sql->bind_param('s', $idPeserta);
$sql->execute();
$registrations = $sql->get_result();

$history = $db->prepare("SELECT b.*, p.nama_program FROM pembayaran b
    JOIN pendaftaran d ON d.id_pendaftaran = b.id_pendaftaran
    JOIN program p ON p.id_program = d.id_program
    WHERE d.id_peserta = ? ORDER BY b.created_at DESC");
$history->bind_param('s', $idPeserta);
$history->execute();
$payments = $history->get_result();

peserta_header('Pembayaran', $u);
?>
<section class="panel">
    <h2>Kirim Bukti Pembayaran</h2>
    <?php if ($error): ?><div class="notice error"><?= e($error) ?></div><?php endif; ?>
    <?php if (!$registrations->num_rows): ?>
        <p class="muted">Belum ada pendaftaran yang dapat dibayar. Pastikan pendaftaran sudah disetujui admin dan tidak sedang menunggu verifikasi pembayaran.</p>
    <?php else: ?>
        <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
            <div class="field">
                <label for="id_pendaftaran">Pendaftaran yang disetujui</label>
                <select name="id_pendaftaran" id="id_pendaftaran" required>
                    <option value="">-- Pilih program --</option>
                    <?php while ($r = $registrations->fetch_assoc()): ?>
                        <option value="<?= e($r['id_pendaftaran']) ?>">
                            <?= e($r['nama_program']) ?> — Total wajib dibayar Rp<?= number_format((float)$r['biaya'], 0, ',', '.') ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="field">
                <label for="metode">Metode pembayaran</label>
                <select name="metode" id="metode" required>
                    <option value="Transfer">Transfer</option>
                    <option value="QRIS">QRIS</option>
                    <option value="Tunai">Tunai</option>
                </select>
            </div>
            <div class="field">
                <label for="bukti">Bukti pembayaran penuh (JPG/PNG/PDF, maksimal 3 MB)</label>
                <input type="file" name="bukti" id="bukti" accept=".jpg,.jpeg,.png,.pdf" required>
            </div>
            <button class="btn" type="submit">Kirim Bukti Pembayaran</button>
        </form>
        <p class="muted">JerMate hanya menerima pembayaran penuh sesuai biaya program. Pembayaran sebagian atau cicilan tidak diterima.</p>
    <?php endif; ?>
</section>
<section class="panel">
    <h2>Riwayat Pembayaran</h2>
    <div class="table-wrap"><table>
        <thead><tr><th>Kode transaksi</th><th>Program</th><th>Jumlah</th><th>Metode</th><th>Status</th><th>Bukti</th></tr></thead>
        <tbody>
        <?php if (!$payments->num_rows): ?><tr><td colspan="6" class="empty">Belum ada pembayaran.</td></tr><?php endif; ?>
        <?php while ($p = $payments->fetch_assoc()): ?>
            <tr>
                <td><?= e($p['kode_transaksi']) ?></td>
                <td><?= e($p['nama_program']) ?></td>
                <td>Rp <?= number_format((float)$p['jumlah_pembayaran'], 0, ',', '.') ?></td>
                <td><?= e($p['metode_pembayaran']) ?></td>
                <td><?= e($p['status_pembayaran']) ?></td>
                <td><?php if ($p['bukti_pembayaran']): ?><a href="bukti_pembayaran.php?id=<?= rawurlencode($p['id_pembayaran']) ?>" target="_blank" rel="noopener">Lihat bukti</a><?php else: ?>-<?php endif; ?></td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table></div>
</section>
<?php
$sql->close();
$history->close();
peserta_footer();
