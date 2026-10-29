<?php
$name = required('name'); $email = required('email'); if (!filter_var($email,FILTER_VALIDATE_EMAIL)) throw new RuntimeException('Enter a valid email.');
        transaction(function() use ($name,$email) {
            query('UPDATE users SET name=?,email=? WHERE id=?', [$name,$email,$_SESSION['uid']]);
            if (!empty($_POST['password'])) {
                $hash = query('SELECT password FROM users WHERE id=?', [$_SESSION['uid']])->fetchColumn();
                if (!password_verify($_POST['current_password'] ?? '',$hash)) throw new RuntimeException('Current password is incorrect.');
                if (strlen($_POST['password'])<10) throw new RuntimeException('Use at least 10 characters for your password.');
                query('UPDATE users SET password=? WHERE id=?',[password_hash($_POST['password'], PASSWORD_DEFAULT),$_SESSION['uid']]);
            }
            audit('Profile updated','Account information updated');
        });
