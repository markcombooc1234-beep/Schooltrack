<?php
if ($action === 'create_user') {
                if (user()['role'] !== 'admin') throw new RuntimeException('Administrator access required.');
                $role = required('role'); if (!in_array($role,['admin','custodian','teacher','staff'],true)) throw new RuntimeException('Invalid role.');
                if (!filter_var(required('email'),FILTER_VALIDATE_EMAIL) || strlen($_POST['password'] ?? '')<10) throw new RuntimeException('Use a valid email and a password of at least 10 characters.');
                query('INSERT INTO users(name,email,password,role) VALUES (?,?,?,?)',[required('name'),required('email'),password_hash($_POST['password'],PASSWORD_DEFAULT),$role]);
            } else throw new RuntimeException('Unknown action.');
