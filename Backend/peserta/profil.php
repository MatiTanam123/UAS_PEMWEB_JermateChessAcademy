
<?php

// ==========================================
// 1. IMPORT FILE DAN AUTENTIKASI
// ==========================================

require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../config/koneksi.php';
require __DIR__ . '/../includes/peserta_layout.php';

$u = require_role('peserta');

$id = $u['id'];

$err = '';


// ==========================================
// 2. MENGAMBIL DATA PROFIL PESERTA
// ==========================================

$sql = $db->prepare("
    SELECT *
    FROM peserta
    WHERE id_peserta = ?
");

$sql->bind_param('s', $id);

$sql->execute();

$p = $sql->get_result()->fetch_assoc();

$sql->close();


// ==========================================
// 3. PROSES UPDATE PROFIL
// ==========================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Validasi CSRF
    csrf_check();


    // ======================================
    // 3A. MENGAMBIL DATA DARI FORM
    // ======================================

    $name = trim($_POST['nama_lengkap'] ?? '');

    $email = trim($_POST['email'] ?? '');

    $phone = trim($_POST['no_hp'] ?? '');

    $alamat = trim($_POST['alamat'] ?? '');

    $birth = $_POST['tanggal_lahir'] ?? '';
    $birth = $birth !== '' ? $birth : null;

    $gender = $_POST['jenis_kelamin'] ?? '';
    $gender = $gender !== '' ? $gender : null;


    // ======================================
    // 3B. VALIDASI INPUT
    // ======================================

    if (
        $name === '' ||
        !filter_var($email, FILTER_VALIDATE_EMAIL)
    ) {

        $err = 'Nama dan email valid wajib diisi.';

    } else {


        // ==================================
        // 3C. UPDATE DATA PESERTA
        // ==================================

        $sql = $db->prepare("
            UPDATE peserta

            SET
                nama_lengkap = ?,
                email = ?,
                no_hp = ?,
                alamat = ?,
                tanggal_lahir = ?,
                jenis_kelamin = ?

            WHERE id_peserta = ?
        ");

        $sql->bind_param(
            'sssssss',
            $name,
            $email,
            $phone,
            $alamat,
            $birth,
            $gender,
            $id
        );

        $sql->execute();

        $sql->close();


        // ==================================
        // 3D. UPDATE SESSION PENGGUNA
        // ==================================

        $_SESSION['user']['nama'] = $name;


        // ==================================
        // 3E. PESAN BERHASIL DAN REDIRECT
        // ==================================

        flash('Profil berhasil diperbarui.');

        header('Location: profil.php');
        exit;

    }

}


// ==========================================
// 4. HEADER HALAMAN
// ==========================================

peserta_header('Profil Saya', $u);

?>


<!-- ======================================
     INFORMASI AKUN PESERTA
======================================= -->

<section class="panel">

    <h2>
        Informasi Akun
    </h2>


    <!-- ==================================
         PESAN ERROR
    =================================== -->

    <?php if ($err): ?>

        <div class="notice error">
            <?= e($err) ?>
        </div>

    <?php endif; ?>


    <!-- ==================================
         FORM EDIT PROFIL
    =================================== -->

    <form method="post">


        <!-- CSRF TOKEN -->

        <input
            type="hidden"
            name="csrf"
            value="<?= e(csrf_token()) ?>"
        >


        <!-- FORM GRID -->

        <div class="form-grid">


            <!-- NAMA LENGKAP -->

            <div class="field">

                <label>
                    Nama lengkap
                </label>

                <input
                    type="text"
                    name="nama_lengkap"
                    value="<?= e($p['nama_lengkap']) ?>"
                    required
                >

            </div>


            <!-- USERNAME -->

            <div class="field">

                <label>
                    Username
                </label>

                <input
                    type="text"
                    value="<?= e($p['username']) ?>"
                    disabled
                >

            </div>


            <!-- EMAIL -->

            <div class="field">

                <label>
                    Email
                </label>

                <input
                    type="email"
                    name="email"
                    value="<?= e($p['email']) ?>"
                    required
                >

            </div>


            <!-- NOMOR HP -->

            <div class="field">

                <label>
                    Nomor HP
                </label>

                <input
                    type="text"
                    name="no_hp"
                    value="<?= e($p['no_hp']) ?>"
                >

            </div>


            <!-- TANGGAL LAHIR -->

            <div class="field">

                <label>
                    Tanggal lahir
                </label>

                <input
                    type="date"
                    name="tanggal_lahir"
                    value="<?= e($p['tanggal_lahir'] ?? '') ?>"
                >

            </div>


            <!-- JENIS KELAMIN -->

            <div class="field">

                <label>
                    Jenis kelamin
                </label>

                <select name="jenis_kelamin">

                    <option value="">
                        Pilih
                    </option>


                    <?php foreach (
                        ['Laki-laki', 'Perempuan'] as $g
                    ): ?>

                        <option
                            value="<?= e($g) ?>"
                            <?= ($p['jenis_kelamin'] ?? '') === $g
                                ? 'selected'
                                : ''
                            ?>
                        >

                            <?= e($g) ?>

                        </option>

                    <?php endforeach; ?>

                </select>

            </div>


            <!-- ALAMAT -->

            <div class="field full">

                <label>
                    Alamat
                </label>

                <textarea name="alamat"><?= e($p['alamat'] ?? '') ?></textarea>

            </div>


        </div>


        <!-- TOMBOL SIMPAN -->

        <button class="btn" type="submit">
            Simpan Profil
        </button>


    </form>

</section>


<?php

// ==========================================
// 5. FOOTER HALAMAN
// ==========================================

peserta_footer();

?>
