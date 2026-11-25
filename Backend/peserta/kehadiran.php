
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
// 2. MENGAMBIL DATA RIWAYAT KEHADIRAN
// ==========================================

$sql = $db->prepare("
    SELECT
        k.*,

        p.nama_program,

        j.tanggal_latihan,
        j.waktu_mulai

    FROM kehadiran k

    JOIN jadwal j
        ON j.id_jadwal = k.id_jadwal

    JOIN program p
        ON p.id_program = j.id_program

    WHERE k.id_peserta = ?

    ORDER BY j.tanggal_latihan DESC
");

$sql->bind_param('s', $id);

$sql->execute();

$rows = $sql->get_result();


// ==========================================
// 3. HEADER HALAMAN
// ==========================================

peserta_header('Riwayat Kehadiran', $u);

?>


<!-- ======================================
     REKAP KEHADIRAN PESERTA
======================================= -->

<section class="panel">

    <h2>
        Rekap Kehadiran
    </h2>


    <div class="table-wrap">

        <table>

            <!-- TABLE HEADER -->

            <thead>

                <tr>

                    <th>Tanggal</th>

                    <th>Program</th>

                    <th>Waktu</th>

                    <th>Status</th>

                    <th>Keterangan</th>

                </tr>

            </thead>


            <!-- TABLE BODY -->

            <tbody>

                <?php if (!$rows->num_rows): ?>

                    <tr>

                        <td colspan="5" class="empty">

                            Belum ada catatan kehadiran.

                        </td>

                    </tr>

                <?php endif; ?>


                <?php while ($r = $rows->fetch_assoc()): ?>

                    <tr>

                        <!-- TANGGAL LATIHAN -->

                        <td>
                            <?= e($r['tanggal_latihan']) ?>
                        </td>


                        <!-- NAMA PROGRAM -->

                        <td>
                            <?= e($r['nama_program']) ?>
                        </td>


                        <!-- WAKTU LATIHAN -->

                        <td>
                            <?= e($r['waktu_mulai']) ?>
                        </td>


                        <!-- STATUS KEHADIRAN -->

                        <td>
                            <?= e($r['status_kehadiran']) ?>
                        </td>


                        <!-- KETERANGAN -->

                        <td>
                            <?= e($r['keterangan']) ?>
                        </td>

                    </tr>

                <?php endwhile; ?>

            </tbody>

        </table>

    </div>

</section>


<?php

// ==========================================
// 4. FOOTER HALAMAN
// ==========================================

$sql->close();

peserta_footer();

?>
