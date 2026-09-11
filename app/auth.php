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
    return $config['base_url'];
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
