
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
// 2. MENGAMBIL RIWAYAT PENDAFTARAN
// ==========================================

$sql = $db->prepare("
    SELECT
        d.*,

        p.nama_program,

        j.tanggal_latihan,
        j.waktu_mulai,
        j.waktu_selesai,
        j.lokasi

    FROM pendaftaran d

    JOIN program p
        ON p.id_program = d.id_program

    LEFT JOIN jadwal j
        ON j.id_jadwal = d.id_jadwal

    WHERE d.id_peserta = ?

    ORDER BY d.tanggal_daftar DESC
");

$sql->bind_param('s', $id);

$sql->execute();

$rows = $sql->get_result();


// ==========================================
// 3. HEADER HALAMAN
// ==========================================

peserta_header('Pendaftaran Saya', $u);

?>


<!-- ======================================
     RIWAYAT PENDAFTARAN PESERTA
======================================= -->

<section class="panel">


    <!-- JUDUL DAN TOMBOL DAFTAR PROGRAM -->

    <div
        class="actions"
        style="justify-content: space-between;"
    >

        <h2>
            Riwayat Pendaftaran
        </h2>

        <a class="btn" href="program.php">
            + Daftar Program
        </a>

    </div>


    <!-- ==================================
         TABEL RIWAYAT PENDAFTARAN
    =================================== -->

    <div class="table-wrap">

        <table>


            <!-- TABLE HEADER -->

            <thead>

                <tr>

                    <th>Program</th>

                    <th>Tanggal daftar</th>

                    <th>Jadwal</th>

                    <th>Status</th>

                    <th>Catatan</th>

                </tr>

            </thead>


            <!-- TABLE BODY -->

            <tbody>


                <!-- KONDISI JIKA BELUM ADA PENDAFTARAN -->

                <?php if (!$rows->num_rows): ?>

                    <tr>

                        <td colspan="5" class="empty">

                            Belum ada pendaftaran.

                        </td>

                    </tr>

                <?php endif; ?>


                <!-- MENAMPILKAN DATA PENDAFTARAN -->

                <?php while ($r = $rows->fetch_assoc()): ?>

                    <tr>


                        <!-- NAMA PROGRAM -->

                        <td>
                            <?= e($r['nama_program']) ?>
                        </td>


                        <!-- TANGGAL PENDAFTARAN -->

                        <td>
                            <?= e($r['tanggal_daftar']) ?>
                        </td>


                        <!-- INFORMASI JADWAL -->

                        <td>

                            <?= e(
                                $r['tanggal_latihan']
                                    ? (
                                        $r['tanggal_latihan']
                                        . ' '
                                        . $r['waktu_mulai']
                                        . '-'
                                        . $r['waktu_selesai']
                                        . ' · '
                                        . $r['lokasi']
                                    )
                                    : 'Menunggu jadwal'
                            ) ?>

                        </td>


                        <!-- STATUS PENDAFTARAN -->

                        <td>

                            <span class="badge">
                                <?= e($r['status_pendaftaran']) ?>
                            </span>

                        </td>


                        <!-- CATATAN ADMIN -->

                        <td>
                            <?= e($r['catatan']) ?>
                        </td>


                    </tr>

                <?php endwhile; ?>


            </tbody>

        </table>

    </div>

</section>


<?php

// ==========================================
// 4. MENUTUP QUERY DAN FOOTER
// ==========================================

$sql->close();

peserta_footer();

?>
