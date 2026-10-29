<?php
// Private credentials belong in the ignored config.local.php or environment.
$local = is_file(__DIR__.'/config.local.php')
    ? require __DIR__.'/config.local.php'
    : [];

return [
    'host' => getenv('DB_HOST') ?: ($local['host'] ?? '127.0.0.1'),
    'port' => getenv('DB_PORT') ?: ($local['port'] ?? '3307'),
    'database' => getenv('DB_NAME') ?: ($local['database'] ?? 'schooltrak'),
    'username' => getenv('DB_USER') ?: ($local['username'] ?? 'root'),
    'password' => getenv('DB_PASS') !== false ? getenv('DB_PASS') : ($local['password'] ?? ''),
];
