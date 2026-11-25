
<?php

// ==========================================
// 1. IMPORT FILE DAN AUTENTIKASI
// ==========================================

require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../config/koneksi.php';
require __DIR__ . '/../includes/peserta_layout.php';

$u = require_role('peserta');


// ==========================================
// 2. MENGAMBIL DATA PROGRAM AKTIF
// ==========================================

$rows = $db->query("
    SELECT *
    FROM program

    WHERE status_aktif = 1

    ORDER BY biaya ASC
");


// ==========================================
// 3. HEADER HALAMAN
// ==========================================

peserta_header('Program Kursus', $u);

?>


<!-- ======================================
     WELCOME SECTION
======================================= -->

<section class="welcome">

    <h2>
        Temukan program yang tepat
    </h2>

    <p>
        Pilih tingkat belajar yang sesuai dengan kemampuan catur kamu.
    </p>

</section>


<!-- ======================================
     DAFTAR PROGRAM KURSUS
======================================= -->

<section class="stats">


    <!-- KONDISI JIKA TIDAK ADA PROGRAM -->

    <?php if (!$rows->num_rows): ?>

        <p class="empty">
            Belum ada program aktif.
        </p>

    <?php endif; ?>


    <!-- MENAMPILKAN SETIAP PROGRAM -->

    <?php while ($p = $rows->fetch_assoc()): ?>

        <article class="stat">


            <!-- TINGKAT PROGRAM -->

            <small>
                <?= e($p['tingkat']) ?>
            </small>


            <!-- NAMA PROGRAM -->

            <strong style="font-size: 20px;">
                <?= e($p['nama_program']) ?>
            </strong>


            <!-- DESKRIPSI PROGRAM -->

            <p>
                <?= e($p['deskripsi']) ?>
            </p>


            <!-- INFORMASI BIAYA DAN PROGRAM -->

            <p>

                <b>
                    Rp <?= number_format(
                        (float) $p['biaya'],
                        0,
                        ',',
                        '.'
                    ) ?>
                </b>

                ·

                <?= e($p['durasi_minggu']) ?> minggu

                ·

                kuota <?= e($p['kuota']) ?>

            </p>


            <!-- TOMBOL PENDAFTARAN -->

            <a
                class="btn"
                href="daftar.php?program=<?= urlencode($p['id_program']) ?>"
            >

                Daftar Program

            </a>


        </article>

    <?php endwhile; ?>


</section>


<?php

// ==========================================
// 4. FOOTER HALAMAN
// ==========================================

peserta_footer();

?>
