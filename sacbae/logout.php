<?php

declare(strict_types=1);

require __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'auth.php';

sacbaeStartSession();
$_SESSION = [];
session_destroy();

header('Location: ' . sacbaeUrl('sacbae/login.php'), true, 303);
exit;
