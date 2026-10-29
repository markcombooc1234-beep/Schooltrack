<?php
if ($action === 'save_category') {
                if ($id) query('UPDATE categories SET name=? WHERE id=?',[required('name'),$id]); else query('INSERT INTO categories(name) VALUES (?)',[required('name')]);
            } elseif ($action === 'delete_category') query('DELETE FROM categories WHERE id=?',[$id]);
