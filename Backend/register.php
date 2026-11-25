
<?php

// ==========================================
// 1. IMPORT FILE DAN KONEKSI DATABASE
// ==========================================

require __DIR__ . '/includes/auth.php';
require __DIR__ . '/config/koneksi.php';


// ==========================================
// 2. CEK SESSION PENGGUNA
// ==========================================

// Jika pengguna sudah login,
// arahkan ke dashboard sesuai role.

if (current_user()) {

    header(
        'Location: ' . dashboard_url(
            current_user()['role']
        )
    );

    exit;

}


// ==========================================
// 3. INISIALISASI VARIABEL
// ==========================================

$error = '';

$old = [
    'nama'     => '',
    'username' => '',
    'email'    => '',
    'no_hp'    => ''
];


// ==========================================
// 4. PROSES REGISTRASI
// ==========================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Validasi CSRF
    csrf_check();


    // ======================================
    // 4A. MENGAMBIL DATA FORM
    // ======================================

    foreach ($old as $key => $value) {

        $old[$key] = trim($_POST[$key] ?? '');

    }

    $pw = (string) ($_POST['password'] ?? '');

    $confirm = (string) ($_POST['konfirmasi'] ?? '');


    // ======================================
    // 4B. VALIDASI NAMA LENGKAP
    // ======================================

    if (
        $old['nama'] === '' ||
        mb_strlen($old['nama']) > 100
    ) {

        $error = 'Nama wajib diisi (maksimal 100 karakter).';

    }


    // ======================================
    // 4C. VALIDASI USERNAME
    // ======================================

    elseif (
        !preg_match(
            '/^[A-Za-z0-9_]{4,50}$/',
            $old['username']
        )
    ) {

        $error = 'Username harus 4–50 karakter (huruf, angka, underscore).';

    }


    // ======================================
    // 4D. VALIDASI EMAIL
    // ======================================

    elseif (
        !filter_var(
            $old['email'],
            FILTER_VALIDATE_EMAIL
        )
    ) {

        $error = 'Email tidak valid.';

    }


    // ======================================
    // 4E. VALIDASI PASSWORD
    // ======================================

    elseif (
        $pw !== $confirm ||
        strlen($pw) < 8
    ) {

        $error = 'Password minimal 8 karakter dan konfirmasi harus sama.';

    }


    // ======================================
    // 4F. CEK USERNAME DAN EMAIL DUPLIKAT
    // ======================================

    else {

        $sql = $db->prepare("
            SELECT id_peserta
            FROM peserta

            WHERE username = ?
               OR email = ?

            UNION

            SELECT id_admin
            FROM admin

            WHERE username = ?
               OR email = ?

            LIMIT 1
        ");

        $sql->bind_param(
            'ssss',
            $old['username'],
            $old['email'],
            $old['username'],
            $old['email']
        );

        $sql->execute();

        $exists = $sql->get_result()->num_rows;

        $sql->close();


        // ==================================
        // 4G. JIKA DATA SUDAH DIGUNAKAN
        // ==================================

        if ($exists) {

            $error = 'Username atau email sudah digunakan.';

        } else {


            // ==================================
            // 4H. MEMBUAT ID PESERTA
            // ==================================

            $id = new_id('PST');


            // ==================================
            // 4I. HASHING PASSWORD
            // ==================================

            $hash = password_hash(
                $pw,
                PASSWORD_DEFAULT
            );


            // ==================================
            // 4J. MENYIMPAN DATA PESERTA
            // ==================================

            $sql = $db->prepare("
                INSERT INTO peserta (
                    id_peserta,
                    nama_lengkap,
                    username,
                    email,
                    password,
                    no_hp,
                    status_akun
                )

                VALUES (?, ?, ?, ?, ?, ?, 'aktif')
            ");

            $sql->bind_param(
                'ssssss',
                $id,
                $old['nama'],
                $old['username'],
                $old['email'],
                $hash,
                $old['no_hp']
            );

            $sql->execute();

            $sql->close();


            // ==================================
            // 4K. REDIRECT KE HALAMAN LOGIN
            // ==================================

            header(
                'Location: ' . BACKEND_URL . '/login.php?registered=1'
            );

            exit;

        }

    }

}

?>


<!-- ======================================
     5. STRUKTUR HTML REGISTRASI
======================================= -->

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="utf-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <title>
        Registrasi Peserta | JerMate
    </title>


    <!-- CSS AUTENTIKASI -->

    <link
        rel="stylesheet"
        href="<?= e(FRONTEND_URL) ?>/assets/styles/auth.css"
    >


    <!-- CSS UTAMA -->

    <link
        rel="stylesheet"
        href="<?= e(FRONTEND_URL) ?>/assets/styles/app.css"
    >

</head>


<body class="auth-body">


    <!-- ==================================
         REGISTRATION CARD
    =================================== -->

    <main class="auth-card auth-card-wide">


        <!-- ==================================
             BRAND / LOGO
        =================================== -->

        <a
            class="auth-brand"
            href="<?= e(FRONTEND_URL) ?>/index.html"
        >

            <img
                src="<?= e(FRONTEND_URL) ?>/assets/images/logo.png"
                alt=""
            >

            <span>

                <strong>
                    JerMate
                </strong>

                <small>
                    Chess Academy
                </small>

            </span>

        </a>


        <!-- ==================================
             JUDUL REGISTRASI
        =================================== -->

        <h1>
            Buat Akun Peserta
        </h1>

        <p class="auth-sub">
            Buat akun sebelum mendaftar program kursus.
        </p>


        <!-- ==================================
             PESAN ERROR
        =================================== -->

        <?php if ($error): ?>

            <div class="alert alert-error">

                <?= e($error) ?>

            </div>

        <?php endif; ?>


        <!-- ==================================
             FORM REGISTRASI
        =================================== -->

        <form method="post">


            <!-- CSRF TOKEN -->

            <input
                type="hidden"
                name="csrf"
                value="<?= e(csrf_token()) ?>"
            >


            <!-- ==================================
                 NAMA LENGKAP
            =================================== -->

            <div class="field">

                <label>
                    Nama lengkap
                </label>

                <input
                    type="text"
                    name="nama"
                    maxlength="100"
                    value="<?= e($old['nama']) ?>"
                    required
                >

            </div>


            <!-- ==================================
                 USERNAME
            =================================== -->

            <div class="field">

                <label>
                    Username
                </label>

                <input
                    type="text"
                    name="username"
                    maxlength="50"
                    value="<?= e($old['username']) ?>"
                    required
                >

            </div>


            <!-- ==================================
                 EMAIL DAN NOMOR HP
            =================================== -->

            <div class="field-row">


                <!-- EMAIL -->

                <div class="field">

                    <label>
                        Email
                    </label>

                    <input
                        type="email"
                        name="email"
                        value="<?= e($old['email']) ?>"
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
                        maxlength="20"
                        value="<?= e($old['no_hp']) ?>"
                    >

                </div>


            </div>


            <!-- ==================================
                 PASSWORD
            =================================== -->

            <div class="field">

                <label>
                    Password
                </label>

                <input
                    type="password"
                    name="password"
                    minlength="8"
                    required
                >

            </div>


            <!-- ==================================
                 KONFIRMASI PASSWORD
            =================================== -->

            <div class="field">

                <label>
                    Konfirmasi password
                </label>

                <input
                    type="password"
                    name="konfirmasi"
                    minlength="8"
                    required
                >

            </div>


            <!-- ==================================
                 TOMBOL REGISTRASI
            =================================== -->

            <button
                class="btn-auth"
                type="submit"
            >

                Buat Akun

            </button>


        </form>


        <!-- ==================================
             LINK LOGIN
        =================================== -->

        <p class="auth-switch">

            Sudah punya akun?

            <a href="<?= e(BACKEND_URL) ?>/login.php">
                Masuk
            </a>

        </p>


    </main>


</body>

</html>
