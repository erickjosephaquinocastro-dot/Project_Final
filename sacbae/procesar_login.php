<?php

declare(strict_types=1);

require __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . sacbaeUrl('sacbae/login.php'), true, 303);
    exit;
}

$credentials = require __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'credentials.php';
$username = trim((string) ($_POST['usuario'] ?? ''));
$password = (string) ($_POST['password'] ?? '');

if (hash_equals($credentials['username'], $username) && password_verify($password, $credentials['password_hash'])) {
    sacbaeStartSession();
    session_regenerate_id(true);
    $_SESSION['sacbae_authenticated'] = true;
    $_SESSION['sacbae_username'] = $username;
    header('Location: ' . sacbaeUrl('modules/hikvision/web/dashboard.php'), true, 303);
    exit;
}

header('Location: ' . sacbaeUrl('sacbae/login.php?error=invalid'), true, 303);
exit;
