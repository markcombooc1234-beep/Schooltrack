<?php
$id = integer('id',1);
        transaction(function() use ($id) {
            $loan = query('SELECT * FROM borrowings WHERE id=? FOR UPDATE',[$id])->fetch();
            if (!$loan || (!manager() && $loan['user_id'] != $_SESSION['uid'])) throw new RuntimeException('Borrowing record not accessible.');
            if ($loan['status'] !== 'borrowed') throw new RuntimeException('This item has already been returned.');
            query('UPDATE items SET quantity=quantity+? WHERE id=?',[$loan['quantity'],$loan['item_id']]);
            query("UPDATE borrowings SET status='returned',returned_at=NOW() WHERE id=?",[$id]);
            audit('Item returned',"Returned borrowing #$id");
        });
