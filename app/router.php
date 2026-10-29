<?php
$page = $_GET['page'] ?? 'dashboard';
$pages = require __DIR__.'/pages.php';
$publicPages = ['login', 'register'];

if (!is_string($page) || (!isset($pages[$page]) && !in_array($page, $publicPages, true))) {
    http_response_code(404);
    exit('Page not found.');
}

require __DIR__.'/actions.php';

// user() does not connect when there is no authenticated session.
$me = user();
if (!$me && !in_array($page, $publicPages, true)) {
    redirect('login');
}
if ($me && in_array($page, $publicPages, true)) {
    redirect('dashboard');
}
if (in_array($page, ['logs', 'users'], true)) {
    $allowed = $me && ($page === 'users' ? $me['role'] === 'admin' : manager());
    if (!$allowed) {
        http_response_code(403);
        exit('Access denied.');
    }
}

if ($page === 'reports' && isset($_GET['export'])) {
    require dirname(__DIR__).'/modules/reports/export.php';
    exit;
}

require __DIR__.'/views/forms.php';
require __DIR__.'/views/layout.php';
