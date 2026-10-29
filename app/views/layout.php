<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pages[$page] ?? ($page === 'register' ? 'Create account' : 'Sign in')) ?> · SchoolTrak</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<?php if ($me): ?>
    <aside>
        <a class="brand" href="index.php">▦ SchoolTrak</a>
        <div class="subtitle">SCHOOL INVENTORY SYSTEM</div>
        <nav>
            <?php foreach ($pages as $key => $label): ?>
                <?php
                if (($key === 'logs' && !manager()) || ($key === 'users' && $me['role'] !== 'admin')) {
                    continue;
                }
                ?>
                <a class="<?= $key === $page ? 'active' : '' ?>" href="?page=<?= $key ?>"><?= e($label) ?></a>
            <?php endforeach ?>
        </nav>
        <div class="account">
            <?= e($me['name']) ?>
            <small><?= e(ucfirst($me['role'])) ?></small>
            <?php startForm('logout'); endForm('Sign out'); ?>
        </div>
    </aside>
<?php endif ?>
<main class="<?= !$me ? 'auth' : '' ?>">
    <?php if ($me): ?>
        <header>
            <div class="subtitle">YOUR SCHOOL, ORGANIZED</div>
            <h1><?= e($pages[$page]) ?></h1>
            <p>Keep track of the resources that keep learning moving.</p>
        </header>
    <?php endif ?>

    <?php if (isset($_SESSION['flash'])): ?>
        <div class="notice" role="status"><?= e($_SESSION['flash']) ?></div>
        <?php unset($_SESSION['flash']); ?>
    <?php endif ?>

    <?php require dirname(__DIR__, 2).'/modules/'.(in_array($page, ['login', 'register'], true) ? 'auth/'.$page.'.php' : $page.'/view.php'); ?>
</main>
</body>
</html>
