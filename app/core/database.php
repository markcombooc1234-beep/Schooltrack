<?php
/** Reuse one PDO connection for every query and transaction in the request. */
function db(): PDO
{
    static $pdo;

    if (!$pdo) {
        $pdo = databaseConnection();
    }

    return $pdo;
}

function databaseConnection(bool $withDatabase = true): PDO
{
    $config = require dirname(__DIR__, 2).'/config.php';
    $dsn = "mysql:host={$config['host']};port={$config['port']};charset=utf8mb4";
    if ($withDatabase) {
        $dsn .= ';dbname='.$config['database'];
    }

    return new PDO($dsn, $config['username'], $config['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_TIMEOUT => 5,
        PDO::ATTR_PERSISTENT => false,
    ]);
}

function databaseErrorMessage(PDOException $error): string
{
    $code = (int) ($error->errorInfo[1] ?? 0);
    $config = require dirname(__DIR__, 2).'/config.php';
    $endpoint = $config['host'].':'.$config['port'];

    return match ($code) {
        1045 => 'Database access was denied. Check the username and password in config.php.',
        1049 => 'The configured database does not exist. Create it in your hosting control panel and check its full name in config.php.',
        2006, 2013 => 'MySQL disconnected during the request. Check the XAMPP MySQL error log for a server crash or InnoDB corruption. The application will not retry automatically.',
        2002, 2003 => 'Cannot connect to MySQL at '.$endpoint.'. Check the host and port in config.php. InfinityFree databases must be accessed from PHP uploaded to that hosting account, not from localhost.',
        default => 'The database operation failed. Check the XAMPP MySQL and PHP error logs before trying again.',
    };
}

function query(string $sql, array $params = []): PDOStatement
{
    $statement = db()->prepare($sql);
    $statement->execute($params);

    return $statement;
}

/** Commit the entire operation, or undo every write if any step fails. */
function transaction(callable $work): mixed
{
    db()->beginTransaction();

    try {
        $result = $work();
        db()->commit();

        return $result;
    } catch (Throwable $error) {
        if (db()->inTransaction()) {
            db()->rollBack();
        }

        throw $error;
    }
}
