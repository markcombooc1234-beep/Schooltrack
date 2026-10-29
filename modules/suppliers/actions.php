<?php
if ($action === 'save_supplier') {
                $values = [required('name'),trim($_POST['contact_person'] ?? ''),trim($_POST['phone'] ?? ''),trim($_POST['address'] ?? ''),trim($_POST['email'] ?? '')];
                if ($values[4] && !filter_var($values[4],FILTER_VALIDATE_EMAIL)) throw new RuntimeException('Enter a valid supplier email.');
                if ($id) query('UPDATE suppliers SET name=?,contact_person=?,phone=?,address=?,email=? WHERE id=?',[...$values,$id]); else query('INSERT INTO suppliers(name,contact_person,phone,address,email) VALUES (?,?,?,?,?)',$values);
            } elseif ($action === 'delete_supplier') query('DELETE FROM suppliers WHERE id=?',[$id]);
