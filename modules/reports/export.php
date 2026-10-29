<?php
$type=$_GET['export'];
if (!in_array($type,['inventory','borrowing'],true)) { http_response_code(400); exit('Unknown report.'); }
if($type==='inventory') $rows=query('SELECT i.id,i.name,c.name category,i.quantity,i.unit,i.item_condition,i.location,i.low_stock FROM items i JOIN categories c ON c.id=i.category_id ORDER BY i.name')->fetchAll();
else $rows=query('SELECT b.id,i.name item,u.name borrower,b.quantity,b.borrowed_at,b.due_date,b.returned_at,b.status FROM borrowings b JOIN items i ON i.id=b.item_id JOIN users u ON u.id=b.user_id'.(!manager()?' WHERE b.user_id=?':'').' ORDER BY b.id DESC',!manager()?[$me['id']]:[])->fetchAll();
audit('Report exported',ucfirst($type).' CSV');
header('Content-Type: text/csv; charset=utf-8'); header('Content-Disposition: attachment; filename="schooltrak-'.$type.'-'.date('Y-m-d').'.csv"');
$out=fopen('php://output','w'); fwrite($out,"\xEF\xBB\xBF");
$headers=$rows?array_keys($rows[0]):($type==='inventory'?['id','name','category','quantity','unit','item_condition','location','low_stock']:['id','item','borrower','quantity','borrowed_at','due_date','returned_at','status']);
fputcsv($out,$headers,',','"',''); foreach($rows as $row) fputcsv($out,array_map(fn($v)=>preg_match('/^[=+@\-\t\r\n]/',(string)$v)?"'".$v:$v,$row),',','"',''); fclose($out);
