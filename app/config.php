<?php

declare(strict_types=1);

return [
    // Opcional. Si se omite, la URL base se detecta desde la ruta actual.
    'base_url' => rtrim((string) (getenv('SACBAE_BASE_URL') ?: ''), '/'),
];
