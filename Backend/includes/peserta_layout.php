
<?php

// ==========================================
// 1. HEADER PESERTA
// ==========================================

function peserta_header(string $title, array $user): void
{
?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="utf-8">

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>
        <?= e($title) ?> | JerMate
    </title>

    <link
        rel="stylesheet"
        href="<?= e(FRONTEND_URL) ?>/assets/styles/app.css"
    >

</head>

<body>

    <div class="app-shell">

        <!-- SIDEBAR -->

        <aside class="sidebar">

            <!-- BRAND / LOGO -->

            <a class="brand" href="index.php">

                <img
                    src="<?= e(FRONTEND_URL) ?>/assets/images/logo.png"
                    alt="JerMate Logo"
                >

                <span>
                    JerMate

                    <small>
                        MEMBER AREA
                    </small>
                </span>

            </a>


            <!-- NAVIGATION -->

            <nav>

                <a href="index.php">
                    ▦ Ringkasan
                </a>

                <a href="program.php">
                    ♜ Program Kursus
                </a>

                <a href="pendaftaran.php">
                    ☷ Pendaftaran Saya
                </a>

                <a href="jadwal.php">
                    ◷ Jadwal Latihan
                </a>

                <a href="pembayaran.php">
                    ◇ Pembayaran
                </a>

                <a href="kehadiran.php">
                    ✓ Kehadiran
                </a>

                <a href="notifikasi.php">
                    ♧ Notifikasi
                </a>

                <a href="profil.php">
                    ⚙ Profil Saya
                </a>

            </nav>


            <!-- LOGOUT -->

            <form
                action="<?= e(BACKEND_URL) ?>/logout.php"
                method="post"
            >

                <input
                    type="hidden"
                    name="csrf"
                    value="<?= e(csrf_token()) ?>"
                >

                <button class="logout" type="submit">
                    Keluar
                </button>

            </form>

        </aside>


        <!-- MAIN CONTENT -->

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


                <!-- PROFILE -->

                <div class="profile">

                    <?= e($user['nama']) ?>

                    <span>
                        PESERTA
                    </span>

                </div>

            </header>


            <!-- FLASH MESSAGE -->

            <?php show_flash(); ?>


<?php
}


// ==========================================
// 2. FOOTER PESERTA
// ==========================================

function peserta_footer(): void
{
?>

        </main>

    </div>

</body>

</html>

<?php
}
?>
