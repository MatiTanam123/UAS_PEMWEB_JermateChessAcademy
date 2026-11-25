<?php
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../config/koneksi.php';
require __DIR__ . '/../includes/peserta_layout.php';

$u = require_role('peserta');
$idPeserta = (string)$u['id'];
$idProgram = trim((string)($_GET['program'] ?? $_POST['id_program'] ?? ''));
$error = '';

$stmt = $db->prepare("SELECT id_program,nama_program,deskripsi,biaya,durasi_minggu,kuota FROM program WHERE id_program=? AND status_aktif=1 LIMIT 1");
$stmt->bind_param('s', $idProgram);
$stmt->execute();
$program = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$program) { flash('Program tidak ditemukan atau tidak aktif.', 'error'); header('Location: program.php'); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $idJadwal = trim((string)($_POST['id_jadwal'] ?? ''));
    try {
        // Semua pemeriksaan dan insert berjalan dalam satu transaksi.
        $db->begin_transaction();

        // Lock program agar pendaftaran pada program yang sama diproses berurutan.
        $lock = $db->prepare("SELECT id_program,kuota FROM program WHERE id_program=? AND status_aktif=1 FOR UPDATE");
        $lock->bind_param('s', $idProgram); $lock->execute();
        $programLocked = $lock->get_result()->fetch_assoc(); $lock->close();
        if (!$programLocked) throw new RuntimeException('Program sudah tidak tersedia.');

        $programCount = $db->prepare("SELECT COUNT(*) AS total FROM pendaftaran WHERE id_program=? AND status_pendaftaran IN ('menunggu','disetujui','selesai')");
        $programCount->bind_param('s', $idProgram);
        $programCount->execute();
        $programUsed = (int)$programCount->get_result()->fetch_assoc()['total'];
        $programCount->close();
        if ($programUsed >= (int)$programLocked['kuota']) {
            throw new RuntimeException('Kuota program sudah penuh.');
        }

        $dup = $db->prepare("SELECT id_pendaftaran FROM pendaftaran WHERE id_peserta=? AND id_program=? AND status_pendaftaran IN ('menunggu','disetujui') LIMIT 1");
        $dup->bind_param('ss', $idPeserta, $idProgram); $dup->execute();
        $already = $dup->get_result()->fetch_assoc(); $dup->close();
        if ($already) throw new RuntimeException('Kamu sudah memiliki pendaftaran aktif untuk program ini.');

        $jadwalDb = null;
        if ($idJadwal !== '') {
            $js = $db->prepare("SELECT id_jadwal,id_program,kapasitas,status_jadwal,tanggal_latihan,waktu_mulai,waktu_selesai FROM jadwal WHERE id_jadwal=? FOR UPDATE");
            $js->bind_param('s', $idJadwal); $js->execute(); $jadwal = $js->get_result()->fetch_assoc(); $js->close();
            if (!$jadwal || $jadwal['id_program'] !== $idProgram || $jadwal['status_jadwal'] !== 'Tersedia' || $jadwal['tanggal_latihan'] < date('Y-m-d')) {
                throw new RuntimeException('Jadwal tidak tersedia untuk program ini.');
            }
            $count = $db->prepare("SELECT COUNT(*) AS total FROM pendaftaran WHERE id_jadwal=? AND status_pendaftaran IN ('menunggu','disetujui','selesai')");
            $count->bind_param('s', $idJadwal); $count->execute();
            $terisi = (int)$count->get_result()->fetch_assoc()['total']; $count->close();
            if ($terisi >= (int)$jadwal['kapasitas']) throw new RuntimeException('Kuota jadwal sudah penuh. Silakan pilih jadwal lain.');
            $jadwalDb = $idJadwal;
        }

        $newId = new_id('REG');
        $status = 'menunggu';
        $insert = $db->prepare("INSERT INTO pendaftaran(id_pendaftaran,id_peserta,id_program,id_jadwal,status_pendaftaran,catatan) VALUES(?,?,?,?,?,'')");
        $insert->bind_param('sssss', $newId, $idPeserta, $idProgram, $jadwalDb, $status);
        $insert->execute(); $insert->close();

        $notifId = new_id('NTF');
        $title = 'Pendaftaran berhasil dikirim';
        $message = 'Pendaftaran program ' . $program['nama_program'] . ' menunggu persetujuan admin.';
        $type = 'Pendaftaran';
        $notif = $db->prepare("INSERT INTO notifikasi(id_notifikasi,id_peserta,judul,pesan,tipe) VALUES(?,?,?,?,?)");
        $notif->bind_param('sssss', $notifId, $idPeserta, $title, $message, $type);
        $notif->execute(); $notif->close();

        $db->commit();
        flash('Pendaftaran berhasil dikirim dan menunggu persetujuan admin.');
        header('Location: pendaftaran.php'); exit;
    } catch (Throwable $e) {
        if ($db->errno === 0 || $db->ping()) { try { $db->rollback(); } catch (Throwable $ignored) {} }
        $error = $e instanceof mysqli_sql_exception ? 'Pendaftaran gagal diproses. Periksa apakah kamu sudah terdaftar pada program ini.' : $e->getMessage();
    }
}

$js = $db->prepare("SELECT j.id_jadwal,j.tanggal_latihan,j.waktu_mulai,j.waktu_selesai,j.lokasi,j.kapasitas,(SELECT COUNT(*) FROM pendaftaran d WHERE d.id_jadwal=j.id_jadwal AND d.status_pendaftaran IN ('menunggu','disetujui','selesai')) AS terisi FROM jadwal j WHERE j.id_program=? AND j.status_jadwal='Tersedia' AND j.tanggal_latihan>=CURDATE() ORDER BY j.tanggal_latihan,j.waktu_mulai");
$js->bind_param('s', $idProgram); $js->execute(); $jadwalRows = $js->get_result();
peserta_header('Form Pendaftaran', $u);
?>
<section class="panel">
    <h2><?= e($program['nama_program']) ?></h2>
    <p><?= e($program['deskripsi']) ?></p>
    <p><b>Biaya: Rp <?= number_format((float)$program['biaya'],0,',','.') ?></b> · Durasi <?= e($program['durasi_minggu']) ?> minggu</p>
    <?php if ($error): ?><div class="notice error"><?= e($error) ?></div><?php endif; ?>
    <form method="post">
        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="id_program" value="<?= e($idProgram) ?>">
        <div class="field"><label for="id_jadwal">Pilih jadwal latihan</label>
            <select name="id_jadwal" id="id_jadwal"><option value="">Jadwal ditentukan kemudian</option>
            <?php while ($j = $jadwalRows->fetch_assoc()): if ((int)$j['terisi'] < (int)$j['kapasitas']): ?>
                <option value="<?= e($j['id_jadwal']) ?>"><?= e($j['tanggal_latihan'].' | '.$j['waktu_mulai'].'-'.$j['waktu_selesai'].' | '.$j['lokasi'].' | Sisa '.((int)$j['kapasitas']-(int)$j['terisi']).' kursi') ?></option>
            <?php endif; endwhile; ?></select>
        </div>
        <button class="btn" type="submit">Kirim Pendaftaran</button>
        <a class="btn secondary" href="program.php">Batal</a>
    </form>
</section>
<?php $js->close(); peserta_footer(); ?>
