<?php

declare(strict_types=1);

return [
    // Credenciales iniciales locales: admin / Cambiar123!
    // Sustituye este hash antes de publicar el sistema.
    'username' => getenv('SACBAE_ADMIN_USERNAME') ?: 'admin',
    'password_hash' => getenv('SACBAE_ADMIN_PASSWORD_HASH') ?: '$2y$10$vOnzgUM89M5ZjG5/4IFzYe.fX9mHH8DAFoUTNkM8h/d5alGwnK8Dy',
];
