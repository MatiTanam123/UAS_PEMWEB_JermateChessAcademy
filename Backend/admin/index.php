
<?php

require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../config/koneksi.php';
require __DIR__ . '/../includes/admin_layout.php';

$u = require_role('admin');


$tables = [
    'peserta'      => 'Peserta',
    'program'      => 'Program',
    'pelatih'      => 'Pelatih',
    'jadwal'       => 'Jadwal',
    'pendaftaran'  => 'Pendaftaran',
    'pembayaran'   => 'Pembayaran',
    'kehadiran'    => 'Kehadiran',
    'galeri'       => 'Galeri'
];

$counts = [];

foreach ($tables as $table => $label) {

    $result = $db->query(
        "SELECT COUNT(*) FROM `$table`"
    );

    $counts[$label] = (int) $result->fetch_row()[0];
}


/* Untuk  bagian Pendaftaran */

$recent = $db->query("
    SELECT
        p.nama_lengkap,
        pr.nama_program,
        d.status_pendaftaran,
        d.tanggal_daftar

    FROM pendaftaran d

    JOIN peserta p
        ON p.id_peserta = d.id_peserta

    JOIN program pr
        ON pr.id_program = d.id_program

    ORDER BY d.tanggal_daftar DESC

    LIMIT 8
");


/*Bagian Status Pendaftaran*/

$status = $db->query("
    SELECT
        status_pendaftaran,
        COUNT(*) AS jumlah

    FROM pendaftaran

    GROUP BY status_pendaftaran
")->fetch_all(MYSQLI_ASSOC);

$statusMap = [];

foreach ($status as $item) {

    $statusMap[$item['status_pendaftaran']] =
        (int) $item['jumlah'];
}




admin_header('Dashboard Admin', $u);

?>



<section class="welcome">

    <div>
        <span class="welcome-kicker">
            PUSAT KENDALI AKADEMI
        </span>

        <h2>
            Selamat datang, <?= e($u['nama']) ?>!
        </h2>

        <p>
            Kelola seluruh aktivitas JerMate Chess Academy.
        </p>
    </div>

    <a class="btn btn-gold" href="jadwal.php">
        + Kelola Jadwal
    </a>

</section>




<section class="stats">

    <?php foreach ($counts as $label => $number): ?>

        <article class="stat">

            <small>
                <?= e($label) ?>
            </small>

            <strong>
                <?= number_format($number, 0, ',', '.') ?>
            </strong>

            <span class="stat-note">
                Total data tersimpan
            </span>

        </article>

    <?php endforeach; ?>

</section>




<div class="dashboard-grid">

    <!-- PENDAFTARAN TERBARU -->

    <section class="panel">

        <div class="panel-heading">

            <h2>Pendaftaran Terbaru</h2>

            <a class="text-link" href="pendaftaran.php">
                Lihat semua →
            </a>

        </div>

        <div class="table-wrap">

            <table>

                <thead>
                    <tr>
                        <th>Peserta</th>
                        <th>Program</th>
                        <th>Status</th>
                        <th>Tanggal</th>
                    </tr>
                </thead>

                <tbody>

                    <?php if (!$recent->num_rows): ?>

                        <tr>
                            <td colspan="4" class="empty">
                                Belum ada pendaftaran.
                            </td>
                        </tr>

                    <?php endif; ?>


                    <?php while ($row = $recent->fetch_assoc()): ?>

                        <tr>

                            <td>
                                <?= e($row['nama_lengkap']) ?>
                            </td>

                            <td>
                                <?= e($row['nama_program']) ?>
                            </td>

                            <td>
                                <span class="badge">
                                    <?= e($row['status_pendaftaran']) ?>
                                </span>
                            </td>

                            <td>
                                <?= e(date(
                                    'd/m/Y',
                                    strtotime($row['tanggal_daftar'])
                                )) ?>
                            </td>

                        </tr>

                    <?php endwhile; ?>

                </tbody>

            </table>

        </div>

    </section>


    <!-- STATUS PENDAFTARAN -->

    <section class="panel">

        <h2>Status Pendaftaran</h2>

        <?php

        $statusLabels = [
            'menunggu'  => 'Menunggu',
            'disetujui' => 'Disetujui',
            'ditolak'   => 'Ditolak',
            'selesai'   => 'Selesai'
        ];

        ?>

        <?php foreach ($statusLabels as $key => $label): ?>

            <div class="status-line">

                <span>
                    <?= e($label) ?>
                </span>

                <strong>
                    <?= $statusMap[$key] ?? 0 ?>
                </strong>

            </div>

        <?php endforeach; ?>

    </section>

</div>


<!-- Bagian akses Cepat -->

<section class="panel">

    <h2>Akses Cepat</h2>

    <?php

    $quickLinks = [
        'program'       => 'Program',
        'peserta'       => 'Peserta',
        'pelatih'       => 'Pelatih',
        'jadwal'        => 'Jadwal',
        'pendaftaran'   => 'Pendaftaran',
        'pembayaran'    => 'Pembayaran',
        'kehadiran'     => 'Kehadiran',
        'galeri'        => 'Galeri',
        'testimoni'     => 'Testimoni',
        'notifikasi'    => 'Notifikasi',
        'pengaturan'    => 'Pengaturan'
    ];

    ?>

    <div class="quick-links">

        <?php foreach ($quickLinks as $file => $label): ?>

            <a href="<?= e($file) ?>.php">

                <b>♟</b>

                <span>

                    <strong>
                        <?= e($label) ?>
                    </strong>

                    <small>
                        Kelola data <?= e(strtolower($label)) ?>
                    </small>

                </span>

                <i>→</i>

            </a>

        <?php endforeach; ?>

    </div>

</section>


<?php


admin_footer();

?>
