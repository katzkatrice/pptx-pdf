<?php
declare(strict_types=1);

require_once __DIR__ . '/src/Database.php';

return [
    'app_env' => getenv('APP_ENV') ?: 'production',
    'app_debug' => filter_var(getenv('APP_DEBUG') ?: 'false', FILTER_VALIDATE_BOOLEAN),
];
