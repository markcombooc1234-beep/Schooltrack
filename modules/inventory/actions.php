<?php
            if ($action === 'save_item') {
                $values = [required('name'),integer('category_id',1),($_POST['supplier_id'] ?? '') !== '' ? integer('supplier_id',1) : null,integer('quantity'),required('unit'),required('item_condition'),required('location'),integer('low_stock')];
                if ($id) { query('SELECT id FROM items WHERE id=? FOR UPDATE',[$id]); query('UPDATE items SET name=?,category_id=?,supplier_id=?,quantity=?,unit=?,item_condition=?,location=?,low_stock=? WHERE id=?',[...$values,$id]); }
                else query('INSERT INTO items(name,category_id,supplier_id,quantity,unit,item_condition,location,low_stock) VALUES (?,?,?,?,?,?,?,?)',$values);
            } elseif ($action === 'delete_item') {
                if (query('SELECT COUNT(*) FROM borrowings WHERE item_id=?',[$id])->fetchColumn()) throw new RuntimeException('Items with borrowing history cannot be deleted.');
                query('DELETE FROM items WHERE id=?',[$id]);

}
