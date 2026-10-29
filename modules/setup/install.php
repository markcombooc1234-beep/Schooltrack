<?php
/** Called only after an explicit, validated setup POST. */
function installSchoolTrak(string $name, string $email, string $password): int
{
    $config = require dirname(__DIR__, 2).'/config.php';
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $config['database'])) {
        throw new RuntimeException('Invalid database name in config.php.');
    }

    // Shared hosting provides the database already; do not request CREATE DATABASE.
    $server = databaseConnection();
    $lockName = 'schooltrak_setup_'.$config['database'];
    $lock = $server->prepare('SELECT GET_LOCK(?, 5)');
    $lock->execute([$lockName]);
    if ((int) $lock->fetchColumn() !== 1) {
        throw new RuntimeException('Another setup request is running. Wait for it to finish.');
    }

    try {
        $initialize = false;
        try {
            // Query only our own table, not the server-wide metadata catalogue.
            $installed = query('SELECT id FROM users LIMIT 1')->fetchColumn();
            if ($installed !== false) {
                throw new RuntimeException('Setup is locked because an account already exists. Open index.php to sign in.');
            }
        } catch (PDOException $error) {
            if ((int) ($error->errorInfo[1] ?? 0) !== 1146) {
                throw $error;
            }
            $initialize = true;
        }

        if ($initialize) {
            foreach (explode(';', file_get_contents(dirname(__DIR__, 2).'/database/schema.sql')) as $sql) {
                if (trim($sql) !== '') {
                    db()->exec($sql);
                }
            }
        }

        return transaction(function () use ($name, $email, $password): int {
            query("INSERT INTO users(name,email,password,role) VALUES (?,?,?,'admin')", [$name, $email, password_hash($password, PASSWORD_DEFAULT)]);
            $id = (int) db()->lastInsertId();
            foreach (['Computers', 'Furniture', 'Laboratory equipment', 'Office supplies'] as $category) {
                query('INSERT INTO categories(name) VALUES (?) ON DUPLICATE KEY UPDATE name=VALUES(name)', [$category]);
            }
            query('INSERT INTO activity_logs(user_id,action,details) VALUES (?,?,?)', [$id, 'Installation', 'Created administrator and starter categories']);
            return $id;
        });
    } finally {
        try {
            $release = $server->prepare('SELECT RELEASE_LOCK(?)');
            $release->execute([$lockName]);
        } catch (PDOException $error) {
            error_log('Setup lock connection closed: '.$error->getMessage());
        }
    }
}
