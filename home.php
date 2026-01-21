<?php
session_start();
include "db_conn.php";

/* ===== JĘZYKI ===== */
$langFiles = glob(__DIR__ . '/Lang/*.php');
$available_langs = [];
foreach ($langFiles as $file) {
    $available_langs[] = basename($file, '.php');
}

$lang = $_GET['lang'] ?? ($_COOKIE['lang'] ?? 'en');
if (!in_array($lang, $available_langs)) $lang = 'en';

setcookie('lang', $lang, time() + 30*24*60*60, "/");

$T = require __DIR__ . "/Lang/$lang.php";

/* ===== AUTORYZACJA ===== */
if (!isset($_SESSION['id'])) {
    header("Location: index.php?lang=$lang");
    exit();
}
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
    <meta charset="UTF-8">
    <title><?= $T['home_title'] ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="home-container">

    <!-- ===== LINIA 1: TYTUŁ + JĘZYK ===== -->
    <div class="header-line">
        <h1><nobr><?= $T['home_panel'] ?></nobr></h1>

        <div class="header-right">
            <select id="langSelect" onchange="changeLang(this.value)">
                <?php foreach ($available_langs as $l): ?>
                    <option value="<?= $l ?>" <?= ($l === $lang ? 'selected' : '') ?>>
                        <?= strtoupper($l) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <!-- ===== LINIA 2: POWITANIE + LOGOUT ===== -->
    <div class="header-line">
        <h2><?= $T['welcome'] ?>, <?= htmlspecialchars($_SESSION['name']) ?></h2>

        <div class="header-right">
            <a href="logout.php" class="btn"><?= $T['logout'] ?></a>
        </div>
    </div>

</div>

<script>
function changeLang(lang) {
    const params = new URLSearchParams(window.location.search);
    params.set('lang', lang);
    window.location.search = params.toString();
}
</script>

</body>
</html>