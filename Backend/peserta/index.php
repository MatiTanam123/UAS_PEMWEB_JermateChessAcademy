
<?php

// ==========================================
// 1. IMPORT FILE DAN AUTENTIKASI
// ==========================================

require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../config/koneksi.php';
require __DIR__ . '/../includes/peserta_layout.php';

$u = require_role('peserta');

$id = $u['id'];


// ==========================================
// 2. FUNGSI MENGHITUNG DATA
// ==========================================

function pcnt(mysqli $db, string $sql, string $id): int
{
    $stmt = $db->prepare($sql);

    $stmt->bind_param('s', $id);

    $stmt->execute();

    $count = (int) $stmt->get_result()->fetch_row()[0];

    $stmt->close();

    return $count;
}


// ==========================================
// 3. MENGHITUNG STATISTIK PESERTA
// ==========================================

// Total pendaftaran

$nDaftar = pcnt(
    $db,
    "
        SELECT COUNT(*)
        FROM pendaftaran
        WHERE id_peserta = ?
    ",
    $id
);


// Pembayaran yang masih menunggu

$nBayar = pcnt(
    $db,
    "
        SELECT COUNT(*)

        FROM pembayaran b

        JOIN pendaftaran d
            ON d.id_pendaftaran = b.id_pendaftaran

        WHERE d.id_peserta = ?
        AND b.status_pembayaran = 'menunggu'
    ",
    $id
);


// Jadwal latihan mendatang

$nJadwal = pcnt(
    $db,
    "
        SELECT COUNT(*)

        FROM pendaftaran d

        JOIN jadwal j
            ON j.id_jadwal = d.id_jadwal

        WHERE d.id_peserta = ?
        AND d.status_pendaftaran IN ('disetujui','selesai')
        AND j.tanggal_latihan >= CURDATE()
        AND j.status_jadwal <> 'Dibatalkan'
    ",
    $id
);


// ==========================================
// 4. MENGAMBIL AKTIVITAS PENDAFTARAN TERBARU
// ==========================================

$sql = $db->prepare("
    SELECT
        d.tanggal_daftar,
        d.status_pendaftaran,

        pr.nama_program,

        j.tanggal_latihan,
        j.waktu_mulai,
        j.lokasi

    FROM pendaftaran d

    JOIN program pr
        ON pr.id_program = d.id_program

    LEFT JOIN jadwal j
        ON j.id_jadwal = d.id_jadwal

    WHERE d.id_peserta = ?

    ORDER BY d.tanggal_daftar DESC

    LIMIT 6
");

$sql->bind_param('s', $id);

$sql->execute();

$recent = $sql->get_result();


// ==========================================
// 5. HEADER DASHBOARD PESERTA
// ==========================================

peserta_header('Dashboard Peserta', $u);

?>


<!-- ======================================
     WELCOME SECTION
======================================= -->

<section class="welcome">

    <h2>
        Halo, <?= e($u['nama']) ?> 👋
    </h2>

    <p>
        Selamat datang di area peserta JerMate Chess Academy.
    </p>


    <!-- SHORTCUT MENU -->

    <div class="login-shortcut">

        <a class="btn" href="program.php">
            Jelajahi Program
        </a>

        <a class="btn secondary" href="pendaftaran.php">
            Pendaftaran Saya
        </a>

    </div>

</section>


<!-- ======================================
     STATISTIK PESERTA
======================================= -->

<section class="stats">

    <!-- TOTAL PENDAFTARAN -->

    <article class="stat">

        <small>
            Total pendaftaran
        </small>

        <strong>
            <?= number_format($nDaftar, 0, ',', '.') ?>
        </strong>

    </article>


    <!-- PEMBAYARAN MENUNGGU -->

    <article class="stat">

        <small>
            Pembayaran menunggu
        </small>

        <strong>
            <?= number_format($nBayar, 0, ',', '.') ?>
        </strong>

    </article>


    <!-- JADWAL MENDATANG -->

    <article class="stat">

        <small>
            Jadwal mendatang
        </small>

        <strong>
            <?= number_format($nJadwal, 0, ',', '.') ?>
        </strong>

    </article>

</section>


<!-- ======================================
     AKTIVITAS PENDAFTARAN
======================================= -->

<section class="panel">

    <h2>
        Aktivitas Pendaftaran
    </h2>


    <div class="table-wrap">

        <table>

            <!-- TABLE HEADER -->

            <thead>

                <tr>

                    <th>Program</th>

                    <th>Tanggal daftar</th>

                    <th>Jadwal</th>

                    <th>Status</th>

                </tr>

            </thead>


            <!-- TABLE BODY -->

            <tbody>

                <?php if (!$recent->num_rows): ?>

                    <tr>

                        <td colspan="4" class="empty">
                            Belum ada pendaftaran.
                        </td>

                    </tr>

                <?php endif; ?>


                <?php while ($r = $recent->fetch_assoc()): ?>

                    <tr>

                        <!-- NAMA PROGRAM -->

                        <td>
                            <?= e($r['nama_program']) ?>
                        </td>


                        <!-- TANGGAL PENDAFTARAN -->

                        <td>
                            <?= e($r['tanggal_daftar']) ?>
                        </td>


                        <!-- JADWAL LATIHAN -->

                        <td>

                            <?= e(
                                $r['tanggal_latihan']
                                    ? (
                                        $r['tanggal_latihan']
                                        . ' '
                                        . $r['waktu_mulai']
                                    )
                                    : 'Belum ditentukan'
                            ) ?>

                        </td>


                        <!-- STATUS PENDAFTARAN -->

                        <td>

                            <span class="badge">
                                <?= e($r['status_pendaftaran']) ?>
                            </span>

                        </td>

                    </tr>

                <?php endwhile; ?>

            </tbody>

        </table>

    </div>

</section>


<?php

// ==========================================
// 6. FOOTER DASHBOARD PESERTA
// ==========================================

$sql->close();

peserta_footer();

?>
