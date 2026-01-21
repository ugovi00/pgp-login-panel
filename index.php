<?php
session_start();

// ================== AUTO-DETECT LANGUAGES ==================
$langFiles = glob(__DIR__ . '/Lang/*.php'); 
$available_langs = [];
foreach ($langFiles as $file) {
    $available_langs[] = basename($file, '.php'); 
}

// wybór języka: GET > COOKIE > domyślny 'en'
$lang = $_GET['lang'] ?? ($_COOKIE['lang'] ?? 'en');
if (!in_array($lang, $available_langs)) $lang = 'en';

// zapis cookie na 30 dni
setcookie('lang', $lang, time() + 30*24*60*60, "/");

// załaduj tłumaczenia
$langFile = __DIR__ . "/Lang/$lang.php";
$T = require $langFile;
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
<meta charset="UTF-8">
<title><?= $T['login_title'] ?></title>
<link rel="stylesheet" href="style.css">
<script src="https://unpkg.com/openpgp@5.11.0/dist/openpgp.min.js"></script>
</head>
<body>

<form id="loginForm" onsubmit="event.preventDefault(); getChallenge();">

    <!-- HEADER: Tytuł + SELECT -->
    <div class="header-line">
        <h2><?= $T['login_title'] ?></h2>
        <div class="header-right">
            <select id="langSelect" onchange="changeLang(this.value)">
                <?php foreach ($available_langs as $l): ?>
                    <option value="<?= $l ?>" <?= ($l == $lang ? 'selected' : '') ?>>
                        <?= strtoupper($l) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div id="error" class="error"></div>

    <label><?= $T['username'] ?></label>
    <input type="text" id="uname" placeholder="<?= $T['username'] ?>">

    <!-- STEP 2 -->
    <div id="step2" class="hidden">
        <label><?= $T['encrypted_msg'] ?></label>
        <textarea id="encrypted" readonly></textarea>

        <label><?= $T['paste_decrypted'] ?></label>
        <input type="text" id="decrypted">

        <button type="button" class="btn-danger" onclick="verify()">
            <?= $T['login'] ?>
        </button>
    </div>

    <!-- STEP 1 -->
    <button type="submit" id="btn-next" class="btn-danger">
        <?= $T['next'] ?>
    </button>
</form>

<script>
const i18n = <?= json_encode($T, JSON_UNESCAPED_UNICODE) ?>;

function changeLang(lang){
    const params = new URLSearchParams(window.location.search);
    params.set('lang', lang);
    window.location.search = params.toString();
}

let publicKey = null;

async function getChallenge() {
    const uname = document.getElementById('uname').value.trim();
    const error = document.getElementById('error');
    error.textContent = '';

    if(!uname){
        error.textContent = i18n.ERR_NO_USERNAME;
        return;
    }

    const form = new FormData();
    form.append('uname', uname);
    form.append('action', 'challenge');

    let res, data;
    try {
        res = await fetch('login.php', {method:'POST', body:form});
        data = await res.json();
    } catch {
        error.textContent = i18n.ERR_CONNECTION;
        return;
    }

    if(data.error){
        error.textContent = i18n[data.error] ?? data.error;
        return;
    }

    try {
        publicKey = await openpgp.readKey({armoredKey: data.publicKey});
        const encrypted = await openpgp.encrypt({
            message: await openpgp.createMessage({text: data.challenge}),
            encryptionKeys: publicKey
        });

        document.getElementById('encrypted').value = encrypted;
        document.getElementById('step2').classList.remove('hidden');
        document.getElementById('btn-next').style.display = 'none';
        document.getElementById('uname').disabled = true;
    } catch {
        error.textContent = i18n.ERR_PGP;
    }
}

async function verify() {
    const answer = document.getElementById('decrypted').value.trim();
    const uname = document.getElementById('uname').value.trim();
    const error = document.getElementById('error');
    error.textContent = '';

    if(!answer){
        error.textContent = i18n.ERR_EMPTY_ANSWER;
        return;
    }

    const form = new FormData();
    form.append('uname', uname);
    form.append('answer', answer);
    form.append('action', 'verify');

    let res, data;
    try {
        res = await fetch('login.php', {method:'POST', body:form});
        data = await res.json();
    } catch {
        error.textContent = i18n.ERR_CONNECTION;
        return;
    }

    if(data.error){
        error.textContent = i18n[data.error] ?? data.error;
        return;
    }

    if(data.success){
        window.location.href = "home.php";
    }
}
</script>
</body>
</html>