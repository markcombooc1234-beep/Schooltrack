<?php
    if ($action === 'login') {
        $email = required('email'); $identity = hash('sha256', ($_SERVER['REMOTE_ADDR'] ?? '').strtolower($email));
        if (query('SELECT COUNT(*) FROM login_attempts WHERE identity_hash=? AND created_at > DATE_SUB(NOW(), INTERVAL 15 MINUTE)', [$identity])->fetchColumn() >= 5) throw new RuntimeException('Too many attempts. Try again in 15 minutes.');
        $account = query('SELECT * FROM users WHERE email=?', [$email])->fetch();
        if (!$account || !password_verify($_POST['password'] ?? '', $account['password'])) { query('INSERT INTO login_attempts(identity_hash) VALUES (?)', [$identity]); throw new RuntimeException('Email or password is incorrect.'); }
        session_regenerate_id(true); $_SESSION['uid'] = $account['id']; audit('Login','Signed in'); redirect('dashboard');
    }
    if (!user()) redirect('login');
    if ($action === 'logout') { audit('Logout','Signed out'); $_SESSION = []; session_regenerate_id(true); redirect('login'); }
