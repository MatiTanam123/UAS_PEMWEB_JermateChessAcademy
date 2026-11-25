
<?php

require __DIR__ . '/includes/auth.php';
require __DIR__ . '/config/koneksi.php';

if (current_user()) {
    header('Location: ' . dashboard_url(current_user()['role']));
    exit;
}

$error = '';
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    $username = trim($_POST['username'] ?? '');
    $pw = (string) ($_POST['password'] ?? '');

    if ($username === '' || $pw === '') {
        $error = 'Username dan password wajib diisi.';
    } else {
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

        $stmt->bind_param('ss', $username, $username);
        $stmt->execute();

        $account = $stmt->get_result()->fetch_assoc();

        $stmt->close();

        if (
            !$account ||
            !password_verify($pw, (string) $account['password'])
        ) {
            $error = 'Username atau password salah.';
        } else {
            if ($account['role'] === 'peserta') {
                $sql = $db->prepare("
                    SELECT status_akun
                    FROM peserta
                    WHERE id_peserta = ?
                ");

                $sql->bind_param('s', $account['id']);
                $sql->execute();

                $status = $sql->get_result()->fetch_column();

                $sql->close();

                if ($status !== 'aktif') {
                    $error = 'Akun peserta tidak aktif. Hubungi admin.';
                }
            }

            if ($error === '') {
                session_regenerate_id(true);

                $_SESSION['user'] = [
                    'id'       => $account['id'],
                    'nama'     => $account['nama'],
                    'username' => $account['username'],
                    'email'    => $account['email'],
                    'role'     => $account['role']
                ];

                header('Location: ' . dashboard_url($account['role']));
                exit;
            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Masuk | JerMate</title>

    <link
        rel="stylesheet"
        href="<?= e(FRONTEND_URL) ?>/assets/styles/auth.css"
    >
</head>

<body class="auth-body">

    <main class="auth-card">

        <a class="auth-brand" href="<?= e(FRONTEND_URL) ?>/index.html">
            <img
                src="<?= e(FRONTEND_URL) ?>/assets/images/logo.png"
                alt=""
            >

            <span>
                <strong>JerMate</strong>
                <small>Chess Academy</small>
            </span>
        </a>

        <h1>Selamat Datang</h1>

        <p class="auth-sub">
            Masuk untuk mengakses layanan JerMate Chess Academy.
        </p>

        <?php if (isset($_GET['registered'])): ?>
            <div class="alert alert-success">
                Registrasi berhasil. Silakan login.
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-error">
                <?= e($error) ?>
            </div>
        <?php endif; ?>

        <form method="post">

            <input
                type="hidden"
                name="csrf"
                value="<?= e(csrf_token()) ?>"
            >

            <div class="field">
                <label>Username</label>

                <input
                    type="text"
                    name="username"
                    value="<?= e($username) ?>"
                    autocomplete="username"
                    required
                >
            </div>

            <div class="field">
                <label>Password</label>

                <input
                    type="password"
                    name="password"
                    autocomplete="current-password"
                    required
                >
            </div>

            <button class="btn-auth" type="submit">
                Masuk
            </button>

        </form>

        <p class="auth-switch">
            Belum punya akun?

            <a href="<?= e(BACKEND_URL) ?>/register.php">
                Registrasi peserta
            </a>
        </p>

        <p class="auth-back">
            <a href="<?= e(FRONTEND_URL) ?>/index.html">
                ← Kembali ke beranda
            </a>
        </p>

    </main>

</body>

</html>
