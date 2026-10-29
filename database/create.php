<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }
$c = require __DIR__.'/../config.php';
if (!preg_match('/^[a-zA-Z0-9_]+$/', $c['database'])) throw new RuntimeException('Invalid database name.');
$pdo = new PDO("mysql:host={$c['host']};port={$c['port']};charset=utf8mb4", $c['username'], $c['password'], [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
$pdo->exec('CREATE DATABASE IF NOT EXISTS `'.$c['database'].'` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
$pdo->exec('USE `'.$c['database'].'`');
foreach (explode(';', file_get_contents(__DIR__.'/schema.sql')) as $sql) {
    if (trim($sql) !== '') $pdo->exec($sql);
}
echo "Database ready: ".$c['database'].PHP_EOL;
foreach ($pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN) as $table) echo $table.PHP_EOL;
