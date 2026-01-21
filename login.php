<?php
session_start();
include "db_conn.php";
header('Content-Type: application/json');

// ================== INPUT ==================
$action = $_POST['action'] ?? '';
$uname  = trim($_POST['uname'] ?? '');

// ================== VALIDATION ==================
if (!$uname) {
    exit(json_encode(['error' => 'ERR_NO_USERNAME']));
}

// ================== USER LOOKUP ==================
$stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE user_name=?");
mysqli_stmt_bind_param($stmt, "s", $uname);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

if (!$user) {
    exit(json_encode(['error' => 'ERR_USER_NOT_FOUND']));
}

if (empty($user['pgp_public_key'])) {
    exit(json_encode(['error' => 'ERR_NO_PGP_KEY']));
}

// ================== ACTION: CHALLENGE ==================
if ($action === 'challenge') {
    $challenge = bin2hex(random_bytes(16));
    $_SESSION['pgp_hash'] = hash('sha256', $challenge);
    $_SESSION['pgp_uid']  = $user['id'];

    exit(json_encode([
        'challenge' => $challenge,
        'publicKey' => $user['pgp_public_key']
    ]));
}

// ================== ACTION: VERIFY ==================
if ($action === 'verify') {
    $answer = trim($_POST['answer'] ?? '');

    if (!$answer || empty($_SESSION['pgp_hash']) || empty($_SESSION['pgp_uid'])) {
        exit(json_encode(['error' => 'ERR_MISSING_DATA']));
    }

    // Sprawdzenie odpowiedzi
    if (hash('sha256', $answer) === $_SESSION['pgp_hash'] && $_SESSION['pgp_uid'] == $user['id']) {
        // Logowanie użytkownika
        $_SESSION['id'] = $user['id'];
        $_SESSION['user_name'] = $user['user_name'];
        $_SESSION['name'] = $user['name'];

        // Usuwamy dane challenga
        unset($_SESSION['pgp_hash'], $_SESSION['pgp_uid']);

        exit(json_encode(['success' => true]));
    }

    exit(json_encode(['error' => 'ERR_INVALID_RESPONSE']));
}

// ================== FALLBACK ==================
exit(json_encode(['error' => 'ERR_UNKNOWN_ACTION']));