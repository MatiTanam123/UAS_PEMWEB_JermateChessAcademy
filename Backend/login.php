
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
// arahkan langsung ke dashboard masing-masing.

if (current_user()) {

    header(
        'Location: ' . dashboard_url(current_user()['role'])
    );

    exit;

}


// ==========================================
// 3. INISIALISASI VARIABEL
// ==========================================

$error = '';

$username = '';


// ==========================================
// 4. PROSES LOGIN
// ==========================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Validasi CSRF
    csrf_check();


    // ======================================
    // 4A. MENGAMBIL INPUT FORM
    // ======================================

    $username = trim($_POST['username'] ?? '');

    $pw = (string) ($_POST['password'] ?? '');


    // ======================================
    // 4B. VALIDASI INPUT
    // ======================================

    if ($username === '' || $pw === '') {

        $error = 'Username dan password wajib diisi.';

    } else {


        // ==================================
        // 4C. MENCARI AKUN ADMIN DAN PESERTA
        // ==================================

        $stmt = $db->prepare("
            SELECT
                id_admin AS id,
                nama_admin AS nama,
                username,
                email,
                password,
                'admin' AS role

            FROM admin

            WHERE username = ?

            UNION ALL

            SELECT
                id_peserta AS id,
                nama_lengkap AS nama,
                username,
                email,
                password,
                'peserta' AS role

            FROM peserta

            WHERE username = ?

            LIMIT 1
        ");

        $stmt->bind_param(
            'ss',
            $username,
            $username
        );

        $stmt->execute();

        $account = $stmt->get_result()->fetch_assoc();

        $stmt->close();


        // ==================================
        // 4D. VERIFIKASI USERNAME DAN PASSWORD
        // ==================================

        if (
            !$account ||
            !password_verify(
                $pw,
                (string) $account['password']
            )
        ) {

            $error = 'Username atau password salah.';

        } else {


            // ==================================
            // 4E. CEK STATUS AKUN PESERTA
            // ==================================

            if ($account['role'] === 'peserta') {

                $sql = $db->prepare("
                    SELECT status_akun

                    FROM peserta

                    WHERE id_peserta = ?
                ");

                $sql->bind_param(
                    's',
                    $account['id']
                );

                $sql->execute();

                $status = $sql->get_result()->fetch_column();

                $sql->close();


                // Peserta harus memiliki akun aktif

                if ($status !== 'aktif') {

                    $error = 'Akun peserta tidak aktif. Hubungi admin.';

                }

            }


            // ==================================
            // 4F. MEMBUAT SESSION LOGIN
            // ==================================

            if ($error === '') {

                // Regenerasi session ID untuk keamanan
                session_regenerate_id(true);


                // Menyimpan informasi pengguna ke session

                $_SESSION['user'] = [

                    'id'       => $account['id'],

                    'nama'     => $account['nama'],

                    'username' => $account['username'],

                    'email'    => $account['email'],

                    'role'     => $account['role']

                ];


                // ==================================
                // 4G. REDIRECT KE DASHBOARD
                // ==================================

                header(
                    'Location: ' . dashboard_url(
                        $account['role']
                    )
                );

                exit;

            }

        }

    }

}

?>


<!-- ======================================
     5. STRUKTUR HTML LOGIN
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
        Masuk | JerMate
    </title>


    <!-- CSS LOGIN -->

    <link
        rel="stylesheet"
        href="<?= e(FRONTEND_URL) ?>/assets/styles/auth.css"
    >

</head>


<body class="auth-body">


    <!-- ==================================
         LOGIN CARD
    =================================== -->

    <main class="auth-card">


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
             JUDUL LOGIN
        =================================== -->

        <h1>
            Selamat Datang
        </h1>

        <p class="auth-sub">
            Masuk untuk mengakses layanan JerMate Chess Academy.
        </p>


        <!-- ==================================
             PESAN REGISTRASI BERHASIL
        =================================== -->

        <?php if (isset($_GET['registered'])): ?>

            <div class="alert alert-success">

                Registrasi berhasil. Silakan login.

            </div>

        <?php endif; ?>


        <!-- ==================================
             PESAN ERROR LOGIN
        =================================== -->

        <?php if ($error): ?>

            <div class="alert alert-error">

                <?= e($error) ?>

            </div>

        <?php endif; ?>


        <!-- ==================================
             FORM LOGIN
        =================================== -->

        <form method="post">


            <!-- CSRF TOKEN -->

            <input
                type="hidden"
                name="csrf"
                value="<?= e(csrf_token()) ?>"
            >


            <!-- USERNAME -->

            <div class="field">

                <label>
                    Username
                </label>

                <input
                    type="text"
                    name="username"
                    value="<?= e($username) ?>"
                    autocomplete="username"
                    required
                >

            </div>


            <!-- PASSWORD -->

            <div class="field">

                <label>
                    Password
                </label>

                <input
                    type="password"
                    name="password"
                    autocomplete="current-password"
                    required
                >

            </div>


            <!-- TOMBOL LOGIN -->

            <button
                class="btn-auth"
                type="submit"
            >

                Masuk

            </button>


        </form>


        <!-- ==================================
             LINK REGISTRASI
        =================================== -->

        <p class="auth-switch">

            Belum punya akun?

            <a href="<?= e(BACKEND_URL) ?>/register.php">

                Registrasi peserta

            </a>

        </p>


        <!-- ==================================
             KEMBALI KE BERANDA
        =================================== -->

        <p class="auth-back">

            <a href="<?= e(FRONTEND_URL) ?>/index.html">

                ← Kembali ke beranda

            </a>

        </p>


    </main>


</body>

</html>
