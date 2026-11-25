
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
// 2. MENGAMBIL DATA JADWAL PESERTA
// ==========================================

$sql = $db->prepare("
    SELECT
        j.*,

        p.nama_program,

        l.nama_pelatih

    FROM pendaftaran d

    JOIN jadwal j
        ON j.id_jadwal = d.id_jadwal

    JOIN program p
        ON p.id_program = j.id_program

    LEFT JOIN pelatih l
        ON l.id_pelatih = j.id_pelatih

    WHERE d.id_peserta = ?
      AND d.status_pendaftaran IN ('disetujui', 'selesai')

    ORDER BY j.tanggal_latihan
");

$sql->bind_param('s', $id);

$sql->execute();

$rows = $sql->get_result();


// ==========================================
// 3. HEADER HALAMAN
// ==========================================

peserta_header('Jadwal Latihan', $u);

?>


<!-- ======================================
     DAFTAR JADWAL LATIHAN
======================================= -->

<section class="panel">

    <h2>
        Jadwal yang Terhubung dengan Akun
    </h2>


    <div class="table-wrap">

        <table>

            <!-- TABLE HEADER -->

            <thead>

                <tr>

                    <th>Program</th>

                    <th>Tanggal</th>

                    <th>Waktu</th>

                    <th>Pelatih</th>

                    <th>Lokasi</th>

                    <th>Status</th>

                </tr>

            </thead>


            <!-- TABLE BODY -->

            <tbody>

                <?php if (!$rows->num_rows): ?>

                    <tr>

                        <td colspan="6" class="empty">

                            Belum ada jadwal latihan. Jadwal akan muncul setelah pendaftaran disetujui dan admin menetapkan jadwal.

                        </td>

                    </tr>

                <?php endif; ?>


                <?php while ($r = $rows->fetch_assoc()): ?>

                    <tr>

                        <!-- NAMA PROGRAM -->

                        <td>
                            <?= e($r['nama_program']) ?>
                        </td>


                        <!-- TANGGAL LATIHAN -->

                        <td>
                            <?= e($r['tanggal_latihan']) ?>
                        </td>


                        <!-- WAKTU LATIHAN -->

                        <td>

                            <?= e(
                                $r['waktu_mulai']
                                . ' - '
                                . $r['waktu_selesai']
                            ) ?>

                        </td>


                        <!-- NAMA PELATIH -->

                        <td>
                            <?= e(
                                $r['nama_pelatih'] ?? 'Belum ditentukan'
                            ) ?>
                        </td>


                        <!-- LOKASI LATIHAN -->

                        <td>
                            <?= e($r['lokasi']) ?>
                        </td>


                        <!-- STATUS JADWAL -->

                        <td>
                            <?= e($r['status_jadwal']) ?>
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
