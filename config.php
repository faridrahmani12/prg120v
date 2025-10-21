<?php
function dbConfig(): array {
    return [
        'host' => getenv('DB_HOST') ?: 'DB_HOST',
        'user' => getenv('DB_USER') ?: 'farah6535',
        'pass' => getenv('DB_PASS') ?: 'a999farah6535',
        'name' => getenv('DB_NAME') ?: 'farah6535',
        'port' => (int) (getenv('DB_PORT') ?: 3306),
    ];
}
