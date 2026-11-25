
<?php


function admin_header(string $title, array $user): void
{
    // Mengambil nama file halaman yang sedang dibuka
    $current = basename($_SERVER['SCRIPT_NAME']);

    // Daftar menu sidebar
    $menus = [
        'index.php' => [
            'label' => 'Dashboard',
            'icon'  => '▦'
        ],
        'peserta.php' => [
            'label' => 'Peserta',
            'icon'  => '♟'
        ],
        'program.php' => [
            'label' => 'Program',
            'icon'  => '♜'
        ],
        'pelatih.php' => [
            'label' => 'Pelatih',
            'icon'  => '♙'
        ],
        'jadwal.php' => [
            'label' => 'Jadwal',
            'icon'  => '◷'
        ],
        'pendaftaran.php' => [
            'label' => 'Pendaftaran',
            'icon'  => '☷'
        ],

        'pembayaran.php' => [
            'label' => 'Pembayaran',
            'icon'  => '◇'
        ],
        'kehadiran.php' => [
            'label' => 'Kehadiran',
            'icon'  => '✓'
        ],
        'galeri.php' => [
            'label' => 'Galeri',
            'icon'  => '▧'
        ],
        'testimoni.php' => [
            'label' => 'Testimoni',
            'icon'  => '☆'
        ],
        'notifikasi.php' => [
            'label' => 'Notifikasi',
            'icon'  => '♧'
        ],
        'pengaturan.php' => [
            'label' => 'Pengaturan',
            'icon'  => '⚙'
        ],
    ];

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>
        <?= e($title) ?> | JerMate Admin
    </title>

    <!-- CSS utama -->
    <link rel="stylesheet" href="<?= e(FRONTEND_URL) ?>/assets/styles/app.css">

    <!-- CSS khusus admin -->
    <link rel="stylesheet" href="<?= e(FRONTEND_URL) ?>/assets/styles/admin.css">

</head>

<body>

<div class="app-shell">


    <aside class="sidebar">

        <!-- BRAND -->
        <a href="index.php" class="brand">

            <img
                src="<?= e(FRONTEND_URL) ?>/assets/images/logo.png"
                alt="JerMate Logo"
            >

            <span>
                JerMate
                <small>ADMIN CONSOLE</small>
            </span>

        </a>


        <!-- NAVIGATION -->
        <nav>

            <?php foreach ($menus as $file => $menu): ?>

                <?php
                    $isActive = ($current === $file);
                ?>

                <a
                    href="<?= e($file) ?>"
                    class="<?= $isActive ? 'active' : '' ?>"
                    <?= $isActive ? 'aria-current="page"' : '' ?>
                >

                    <span>
                        <?= e($menu['icon']) ?>
                    </span>

                    <?= e($menu['label']) ?>

                </a>

            <?php endforeach; ?>

        </nav>


        <!-- LOGOUT -->
        <form
            action="<?= e(BACKEND_URL) ?>/logout.php"
            method="POST"
        >

            <input
                type="hidden"
                name="csrf"
                value="<?= e(csrf_token()) ?>"
            >

            <button
                type="submit"
                class="logout"
            >
                Keluar
            </button>

        </form>

    </aside>



    <main class="main-area">

        <!-- TOPBAR -->
        <header class="topbar">

            <div>

                <span class="eyebrow">
                    JERMATE CHESS ACADEMY
                </span>

                <h1>
                    <?= e($title) ?>
                </h1>

            </div>


            <!-- PROFILE ADMIN -->
            <div class="profile">

                <?= e($user['nama'] ?? 'Administrator') ?>

                <span>
                    ADMIN
                </span>

            </div>

        </header>


        <!-- FLASH MESSAGE -->
        <?php show_flash(); ?>


<?php
}

function admin_footer(): void
{
?>

    </main>

</div>

</body>
</html>

<?php
}
?>
