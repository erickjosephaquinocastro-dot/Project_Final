<?php

declare(strict_types=1);

return [
    // Cambia esta ruta si el proyecto se publica con otro nombre en Apache.
    'base_url' => rtrim((string) (getenv('SACBAE_BASE_URL') ?: '/Project_Final'), '/'),
];
