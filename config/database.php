<?php
declare(strict_types=1);

function env_value(string $key, ?string $default = null): ?string
{
    $value = getenv($key);
    return $value === false ? $default : $value;
}

return [
    'host' => env_value('DB_HOST', '127.0.0.1'),
    'port' => (int) env_value('DB_PORT', '3306'),
    'database' => env_value('DB_DATABASE', ''),
    'username' => env_value('DB_USERNAME', ''),
    'password' => env_value('DB_PASSWORD', ''),
];
