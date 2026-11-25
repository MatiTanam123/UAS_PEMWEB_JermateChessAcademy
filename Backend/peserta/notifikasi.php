
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
// 2. PROSES TANDAI SEMUA DIBACA
// ==========================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Validasi CSRF
    csrf_check();


    // Update status seluruh notifikasi milik peserta
    $sql = $db->prepare("
        UPDATE notifikasi

        SET status_baca = 1

        WHERE id_peserta = ?
        AND status_baca = 0
    ");

    $sql->bind_param('s', $id);

    $sql->execute();

    $sql->close();


    // Pesan berhasil
    flash('Notifikasi ditandai telah dibaca.');


    // Redirect kembali ke halaman notifikasi
    header('Location: notifikasi.php');
    exit;

}


// ==========================================
// 3. MENGAMBIL DATA NOTIFIKASI
// ==========================================

$sql = $db->prepare("
    SELECT *

    FROM notifikasi

    WHERE id_peserta = ?
       OR id_peserta IS NULL

    ORDER BY created_at DESC
");

$sql->bind_param('s', $id);

$sql->execute();

$rows = $sql->get_result();


// ==========================================
// 4. HEADER HALAMAN
// ==========================================

peserta_header('Notifikasi', $u);

?>


<!-- ======================================
     DAFTAR NOTIFIKASI
======================================= -->

<section class="panel">


    <!-- JUDUL DAN TOMBOL AKSI -->

    <div
        class="actions"
        style="justify-content: space-between;"
    >

        <h2>
            Pemberitahuan
        </h2>


        <!-- FORM TANDAI SEMUA DIBACA -->

        <form method="post">

            <input
                type="hidden"
                name="csrf"
                value="<?= e(csrf_token()) ?>"
            >

            <button class="btn secondary" type="submit">
                Tandai semua dibaca
            </button>

        </form>

    </div>


    <!-- ==================================
         KONDISI JIKA BELUM ADA NOTIFIKASI
    =================================== -->

    <?php if (!$rows->num_rows): ?>

        <p class="empty">
            Belum ada notifikasi.
        </p>

    <?php endif; ?>


    <!-- ==================================
         MENAMPILKAN NOTIFIKASI
    =================================== -->

    <?php while ($r = $rows->fetch_assoc()): ?>

        <article class="panel">


            <!-- JUDUL DAN INFORMASI NOTIFIKASI -->

            <div
                class="actions"
                style="justify-content: space-between;"
            >

                <b>
                    <?= e($r['judul']) ?>
                </b>


                <span class="badge">

                    <?= e($r['tipe']) ?>

                    ·

                    <?= e($r['created_at']) ?>

                </span>

            </div>


            <!-- ISI PESAN -->

            <p>
                <?= nl2br(e($r['pesan'])) ?>
            </p>


            <!-- STATUS BACA -->

            <small>

                <?= $r['status_baca']
                    ? 'Sudah dibaca'
                    : 'Belum dibaca'
                ?>

            </small>


        </article>

    <?php endwhile; ?>


</section>


<?php

// ==========================================
// 5. FOOTER HALAMAN
// ==========================================

$sql->close();

peserta_footer();

?>
