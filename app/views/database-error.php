<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Database unavailable · SchoolTrak</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<main class="auth">
    <section class="panel login">
        <div class="brand">▦ SchoolTrak</div>
        <h1>Database unavailable</h1>
        <p role="alert"><?= e(databaseErrorMessage($error)) ?></p>
        <p>If XAMPP reports a shutdown, its MySQL error log contains the cause. Reinstalling SchoolTrak will not repair database storage.</p>
        <form method="post">
            <?= csrf() ?>
            <input type="hidden" name="action" value="clear_session">
            <button>Return to sign in</button>
        </form>
    </section>
</main>
</body>
</html>
