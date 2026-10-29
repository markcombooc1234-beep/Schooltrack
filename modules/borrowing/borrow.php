<?php
$itemId = integer('item_id',1); $qty = integer('quantity',1); $due = required('due_date');
        $date = DateTime::createFromFormat('!Y-m-d',$due); if (!$date || $date->format('Y-m-d') !== $due || $due < date('Y-m-d')) throw new RuntimeException('Choose a valid due date today or later.');
        transaction(function() use ($itemId,$qty,$due) {
            $item = query('SELECT * FROM items WHERE id=? FOR UPDATE',[$itemId])->fetch();
            if (!$item || $item['quantity']<$qty) throw new RuntimeException('Insufficient available stock.');
            query('UPDATE items SET quantity=quantity-? WHERE id=?',[$qty,$itemId]);
            query('INSERT INTO borrowings(item_id,user_id,quantity,due_date) VALUES (?,?,?,?)',[$itemId,$_SESSION['uid'],$qty,$due]);
            audit('Item borrowed',"Borrowed $qty of item #$itemId");
        });
