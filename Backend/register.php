
<?php

require __DIR__ . '/includes/auth.php';
require __DIR__ . '/config/koneksi.php';

if (current_user()) {
    header('Location: ' . dashboard_url(current_user()['role']));
    exit;
}

$error = '';

$old = [
    'nama'     => '',
    'username' => '',
    'email'    => '',
    'no_hp'    => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    foreach ($old as $key => $value) {
        $old[$key] = trim($_POST[$key] ?? '');
    }

    $pw = (string) ($_POST['password'] ?? '');
    $confirm = (string) ($_POST['konfirmasi'] ?? '');

    if (
        $old['nama'] === '' ||
        mb_strlen($old['nama']) > 100
    ) {
        $error = 'Nama wajib diisi (maksimal 100 karakter).';
    } elseif (
        !preg_match('/^[A-Za-z0-9_]{4,50}$/', $old['username'])
    ) {
        $error = 'Username harus 4–50 karakter (huruf, angka, underscore).';
    } elseif (
        !filter_var($old['email'], FILTER_VALIDATE_EMAIL)
    ) {
        $error = 'Email tidak valid.';
    } elseif (
        $pw !== $confirm ||
        strlen($pw) < 8
    ) {
        $error = 'Password minimal 8 karakter dan konfirmasi harus sama.';
    } else {
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

        if ($exists) {
            $error = 'Username atau email sudah digunakan.';
        } else {
            $id = new_id('PST');

            $hash = password_hash($pw, PASSWORD_DEFAULT);

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

            header('Location: ' . BACKEND_URL . '/login.php?registered=1');
            exit;
        }
    }
}

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Registrasi Peserta | JerMate</title>

    <link
        rel="stylesheet"
        href="<?= e(FRONTEND_URL) ?>/assets/styles/auth.css"
    >

    <link
        rel="stylesheet"
        href="<?= e(FRONTEND_URL) ?>/assets/styles/app.css"
    >
</head>

<body class="auth-body">

    <main class="auth-card auth-card-wide">

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

        <h1>Buat Akun Peserta</h1>

        <p class="auth-sub">
            Buat akun sebelum mendaftar program kursus.
        </p>

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
                <label>Nama lengkap</label>

                <input
                    type="text"
                    name="nama"
                    maxlength="100"
                    value="<?= e($old['nama']) ?>"
                    required
                >
            </div>

            <div class="field">
                <label>Username</label>

                <input
                    type="text"
                    name="username"
                    maxlength="50"
                    value="<?= e($old['username']) ?>"
                    required
                >
            </div>

            <div class="field-row">

                <div class="field">
                    <label>Email</label>

                    <input
                        type="email"
                        name="email"
                        value="<?= e($old['email']) ?>"
                        required
                    >
                </div>

                <div class="field">
                    <label>Nomor HP</label>

                    <input
                        type="text"
                        name="no_hp"
                        maxlength="20"
                        value="<?= e($old['no_hp']) ?>"
                    >
                </div>

            </div>

            <div class="field">
                <label>Password</label>

                <input
                    type="password"
                    name="password"
                    minlength="8"
                    required
                >
            </div>

            <div class="field">
                <label>Konfirmasi password</label>

                <input
                    type="password"
                    name="konfirmasi"
                    minlength="8"
                    required
                >
            </div>

            <button class="btn-auth" type="submit">
                Buat Akun
            </button>

        </form>

        <p class="auth-switch">
            Sudah punya akun?

            <a href="<?= e(BACKEND_URL) ?>/login.php">
                Masuk
            </a>
        </p>

    </main>

</body>

</html>
