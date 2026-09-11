<?php

declare(strict_types=1);

require __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'auth.php';
require __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . sacbaeUrl('sacbae/login.php'), true, 303);
    exit;
}

$username = trim((string) ($_POST['usuario'] ?? ''));
$password = (string) ($_POST['password'] ?? '');
$database = sacbaeDatabase();
$user = null;

if ($database !== null) {
    $statement = $database->prepare(
        'SELECT id, username, password_hash, role FROM users WHERE username = :username AND active = 1 LIMIT 1'
    );
    $statement->execute(['username' => $username]);
    $candidate = $statement->fetch();
    if (is_array($candidate) && password_verify($password, $candidate['password_hash'])) {
        $user = $candidate;
    }
} else {
    $credentials = require __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'credentials.php';
    if (hash_equals($credentials['username'], $username) && password_verify($password, $credentials['password_hash'])) {
        $user = ['id' => null, 'username' => $username, 'role' => 'admin'];
    }
}

if ($user !== null) {
    sacbaeStartSession();
    session_regenerate_id(true);
    $_SESSION['sacbae_authenticated'] = true;
    $_SESSION['sacbae_user_id'] = $user['id'];
    $_SESSION['sacbae_username'] = $user['username'];
    $_SESSION['sacbae_role'] = $user['role'];
    header('Location: ' . sacbaeUrl('modules/hikvision/web/dashboard.php'), true, 303);
    exit;
}

header('Location: ' . sacbaeUrl('sacbae/login.php?error=invalid'), true, 303);
exit;
