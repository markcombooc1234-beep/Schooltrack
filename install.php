<?php
require __DIR__.'/app/bootstrap.php';

// A refresh is presentation-only: no connection, metadata scan, or schema writes.
$error = '';
$done = !empty($_SESSION['setup_complete']);
$name = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (!hash_equals($_SESSION['csrf'], (string) ($_POST['csrf'] ?? ''))) {
            http_response_code(403);
            throw new RuntimeException('Invalid request token. Reload the page.');
        }
        $name = required('name');
        $email = required('email');
        $password = $_POST['password'] ?? '';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 10) {
            throw new RuntimeException('Use a valid email and a password of at least 10 characters.');
        }

        require __DIR__.'/modules/setup/install.php';
        $userId = installSchoolTrak($name, $email, $password);
        // Authenticate only after the administrator transaction has committed.
        session_regenerate_id(true);
        $_SESSION['uid'] = $userId;
        $_SESSION['setup_complete'] = true;
        header('Location: install.php', true, 303);
        exit;
    } catch (PDOException $exception) {
        error_log((string) $exception);
        http_response_code(503);
        $error = databaseErrorMessage($exception);
    } catch (RuntimeException $exception) {
        $error = $exception->getMessage();
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Set up SchoolTrak</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<main class="auth">
    <section class="panel login">
        <div class="brand">▦ SchoolTrak</div>
        <h1>Set up your school</h1>
        <?php if ($error): ?>
            <div class="notice" role="alert"><?= e($error) ?></div>
        <?php endif ?>
        <?php if ($done): ?>
            <p>Setup is complete. Your administrator account is ready.</p>
            <a class="button" href="index.php">Open dashboard</a>
        <?php else: ?>
            <p>Create the first administrator. Existing accounts prevent installation. If you already have an account, <a href="index.php?page=login">sign in</a>.</p>
            <form method="post">
                <?= csrf() ?>
                <label>Administrator name<input name="name" value="<?= e($name) ?>" required maxlength="255" autocomplete="name"></label>
                <label>Email<input name="email" type="email" value="<?= e($email) ?>" required maxlength="255" autocomplete="email"></label>
                <label>Password (at least 10 characters)<input name="password" type="password" minlength="10" required autocomplete="new-password"></label>
                <button>Install SchoolTrak</button>
            </form>
            <p>This page connects to MySQL only when you submit setup.</p>
        <?php endif ?>
    </section>
</main>
</body>
</html>
