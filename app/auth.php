<?php

declare(strict_types=1);

function sacbaeStartSession(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
}

function sacbaeBaseUrl(): string
{
    $config = require __DIR__ . DIRECTORY_SEPARATOR . 'config.php';
    if ($config['base_url'] !== '') {
        return $config['base_url'];
    }

    $scriptName = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? ''));
    foreach (['/sacbae/', '/modules/hikvision/web/'] as $marker) {
        $position = strpos($scriptName, $marker);
        if ($position !== false) {
            return substr($scriptName, 0, $position);
        }
    }

    return '/Project_Final';
}

function sacbaeUrl(string $path = ''): string
{
    return sacbaeBaseUrl() . '/' . ltrim($path, '/');
}

function sacbaeIsAuthenticated(): bool
{
    sacbaeStartSession();
    return ($_SESSION['sacbae_authenticated'] ?? false) === true;
}

function sacbaeRequireAuthentication(): void
{
    if (!sacbaeIsAuthenticated()) {
        header('Location: ' . sacbaeUrl('sacbae/login.php'), true, 302);
        exit;
    }
}
