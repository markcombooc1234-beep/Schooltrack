<?php
/** Validate before connecting; public registration never accepts a role. */
function registerStaffAccount(): void
{
    $name = required('name');
    $email = required('email');
    $password = (string) ($_POST['password'] ?? '');
    $confirmation = (string) ($_POST['password_confirmation'] ?? '');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new RuntimeException('Enter a valid email address.');
    }
    if (strlen($password) < 10) {
        throw new RuntimeException('Use at least 10 characters for your password.');
    }
    if ($password !== $confirmation) {
        throw new RuntimeException('The passwords do not match.');
    }

    transaction(function () use ($name, $email, $password): void {
        try {
            query("INSERT INTO users(name,email,password,role) VALUES (?,?,?,'staff')", [
                $name, $email, password_hash($password, PASSWORD_DEFAULT),
            ]);
        } catch (PDOException $error) {
            if ((int) ($error->errorInfo[1] ?? 0) === 1062) {
                throw new RuntimeException('This email already has an account. Sign in instead.');
            }
            throw $error;
        }

        $id = (int) db()->lastInsertId();
        query('INSERT INTO activity_logs(user_id,action,details) VALUES (?,?,?)', [
            $id, 'Account registered', 'Created a staff account',
        ]);
    });
}
