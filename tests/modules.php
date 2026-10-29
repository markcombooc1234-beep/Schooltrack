<?php
/** CLI regression check. Creates and removes a separate, randomly named database. */
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit;
}

$root = dirname(__DIR__);
$config = require $root.'/config.php';
$testDatabase = 'schooltrak_test_'.bin2hex(random_bytes(6));
$server = new PDO(
    "mysql:host={$config['host']};port={$config['port']};charset=utf8mb4",
    $config['username'],
    $config['password'],
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

function check(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

try {
    $server->exec("CREATE DATABASE `$testDatabase` CHARACTER SET utf8mb4");
    putenv('DB_NAME='.$testDatabase);
    require $root.'/app/bootstrap.php';
    foreach (explode(';', file_get_contents($root.'/database/schema.sql')) as $sql) {
        if (trim($sql) !== '') {
            db()->exec($sql);
        }
    }

    query("INSERT INTO users(name,email,password,role) VALUES ('Test admin','admin@example.test',?,'admin')", [password_hash('test-password', PASSWORD_DEFAULT)]);
    $_SESSION['uid'] = (int) db()->lastInsertId();
    query("INSERT INTO categories(name) VALUES ('Test category')");
    $categoryId = (int) db()->lastInsertId();

    // Execute the same inventory handler and transaction boundary as the dispatcher.
    $action = 'save_item';
    $id = 0;
    $_POST = ['name'=>'Test item', 'category_id'=>$categoryId, 'quantity'=>5, 'unit'=>'pieces', 'item_condition'=>'Good', 'location'=>'Test room', 'low_stock'=>1];
    transaction(function () use ($root, $action, $id) {
        require $root.'/modules/inventory/actions.php';
        audit($action, 'Test item created');
    });
    $itemId = (int) query('SELECT id FROM items')->fetchColumn();

    $_POST = ['item_id'=>$itemId, 'quantity'=>2, 'due_date'=>date('Y-m-d')];
    require $root.'/modules/borrowing/borrow.php';
    check((int) query('SELECT quantity FROM items WHERE id=?', [$itemId])->fetchColumn() === 3, 'Borrow did not deduct stock.');
    $loanId = (int) query('SELECT id FROM borrowings')->fetchColumn();

    $_POST['quantity'] = 4;
    $rejected = false;
    try {
        require $root.'/modules/borrowing/borrow.php';
    } catch (RuntimeException $error) {
        $rejected = $error->getMessage() === 'Insufficient available stock.';
    }
    check($rejected, 'Over-borrowing should fail.');
    check((int) query('SELECT quantity FROM items WHERE id=?', [$itemId])->fetchColumn() === 3, 'Failed borrowing changed stock.');
    check((int) query('SELECT COUNT(*) FROM borrowings')->fetchColumn() === 1, 'Failed borrowing created a loan.');

    $_POST = ['id'=>$loanId];
    require $root.'/modules/borrowing/return.php';
    check((int) query('SELECT quantity FROM items WHERE id=?', [$itemId])->fetchColumn() === 5, 'Return did not restore stock.');
    $rejected = false;
    try {
        require $root.'/modules/borrowing/return.php';
    } catch (RuntimeException $error) {
        $rejected = $error->getMessage() === 'This item has already been returned.';
    }
    check($rejected, 'Duplicate return should fail.');
    check((int) query('SELECT quantity FROM items WHERE id=?', [$itemId])->fetchColumn() === 5, 'Duplicate return changed stock.');

    $_POST = ['name'=>'Should roll back', 'email'=>'changed@example.test', 'password'=>'new-test-password', 'current_password'=>'incorrect'];
    $rejected = false;
    try {
        require $root.'/modules/profile/actions.php';
    } catch (RuntimeException $error) {
        $rejected = $error->getMessage() === 'Current password is incorrect.';
    }
    check($rejected, 'Incorrect current password should fail.');
    check(user()['name'] === 'Test admin', 'Profile change was not rolled back.');
    check(!db()->inTransaction(), 'A transaction was left open.');
    echo "PASS: inventory creation, borrowing, failed borrowing rollback, return, duplicate return, and profile rollback.\n";
} finally {
    if (function_exists('db') && db()->inTransaction()) {
        db()->rollBack();
    }
    try {
        // Reconnect for cleanup in case the original server connection expired.
        $cleanup = new PDO(
            "mysql:host={$config['host']};port={$config['port']};charset=utf8mb4",
            $config['username'],
            $config['password'],
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        $cleanup->exec("DROP DATABASE IF EXISTS `$testDatabase`");
    } catch (PDOException $cleanupError) {
        fwrite(STDERR, "Test database cleanup unavailable: $testDatabase. Remove this test database when MySQL is running again.\n");
    }
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_destroy();
    }
}
