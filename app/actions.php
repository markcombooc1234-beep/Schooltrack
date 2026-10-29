<?php
/** Dispatch POST commands to their feature modules. */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    return;
}
if (!hash_equals($_SESSION['csrf'], (string) ($_POST['csrf'] ?? ''))) {
    http_response_code(403);
    exit('Invalid request token. Reload the page.');
}
$action = $_POST['action'] ?? '';
// Clear a stale login without querying an unavailable database server.
if ($action === 'clear_session') {
    $_SESSION = [];
    session_regenerate_id(true);
    redirect('login');
}
$moduleRoot = dirname(__DIR__).'/modules';
try {
    if ($action === 'register') {
        if (isset($_SESSION['uid'])) {
            redirect('dashboard');
        }
        $_SESSION['registration_input'] = [
            'name' => substr(trim((string) ($_POST['name'] ?? '')), 0, 255),
            'email' => substr(trim((string) ($_POST['email'] ?? '')), 0, 255),
        ];
        require $moduleRoot.'/auth/registration.php';
        registerStaffAccount();
        unset($_SESSION['registration_input']);
        flash('Account created successfully. Sign in with your email and password.');
        redirect('login');
    }
    require $moduleRoot.'/auth/actions.php';
    $personalActions = [
        'profile' => '/profile/actions.php',
        'borrow' => '/borrowing/borrow.php',
        'return' => '/borrowing/return.php',
    ];
    $managementActions = [
        'save_item' => '/inventory/actions.php',
        'delete_item' => '/inventory/actions.php',
        'save_category' => '/categories/actions.php',
        'delete_category' => '/categories/actions.php',
        'save_supplier' => '/suppliers/actions.php',
        'delete_supplier' => '/suppliers/actions.php',
        'create_user' => '/users/actions.php',
    ];
    if (isset($personalActions[$action])) {
        require $moduleRoot.$personalActions[$action];
    } elseif (isset($managementActions[$action])) {
        if (!manager()) {
            throw new RuntimeException('Only administrators and custodians can manage inventory.');
        }
        // The record change and audit log share one transaction.
        transaction(function () use ($action, $moduleRoot, $managementActions) {
            $id = (int) ($_POST['id'] ?? 0);
            require $moduleRoot.$managementActions[$action];
            audit($action, "Record #$id");
        });
    } else {
        throw new RuntimeException('Unknown action.');
    }
    flash('Changes saved successfully.');
} catch (Throwable $error) {
    if ($error instanceof PDOException) {
        error_log((string) $error);
        flash(in_array($action, ['register', 'login'], true)
            ? databaseErrorMessage($error)
            : 'Unable to save. Check for duplicate values or records still in use.');
    } else {
        flash($error->getMessage());
    }
}
$allowedPages = ['dashboard', 'inventory', 'categories', 'suppliers', 'borrowing', 'reports', 'profile', 'logs', 'users', 'login', 'register'];
if ($action === 'register') {
    redirect('register');
}
redirect(in_array($page, $allowedPages, true) ? $page : 'dashboard');
