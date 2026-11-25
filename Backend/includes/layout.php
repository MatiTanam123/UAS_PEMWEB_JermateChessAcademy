<?php
// Kerangka halaman dashboard (dipakai admin dan peserta)

function dash_top(string $judul, array $user): void
{
    $peran = $user['role'] === 'admin' ? 'Admin' : 'Peserta';
    ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($judul) ?> | JerMate Chess Academy</title>
    <link rel="stylesheet" href="<?= e(FRONTEND_URL) ?>/assets/styles/auth.css">
</head>
<body class="dash-body">
    <header class="dash-header">
        <div class="dash-wrap dash-header-inner">
            <a href="<?= e(FRONTEND_URL) ?>/index.html" class="dash-brand">
                <img src="<?= e(FRONTEND_URL) ?>/assets/images/logo.png" alt="Logo JerMate">
                <span><strong>JerMate</strong><small>Chess Academy</small></span>
            </a>
            <div class="dash-user">
                <span class="dash-user-name"><?= e($user['nama']) ?> <em><?= e($peran) ?></em></span>
                <form action="<?= e(BACKEND_URL) ?>/logout.php" method="post">
                    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                    <button type="submit" class="btn-logout">Keluar</button>
                </form>
            </div>
        </div>
    </header>
    <main class="dash-wrap dash-main">
<?php
}

function dash_bottom(): void
{
    ?>
    </main>
    <footer class="dash-footer">&copy; 2026 JerMate Chess Academy.</footer>
</body>
</html>
<?php
}
