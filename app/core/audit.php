<?php
function audit(string $action, string $details): void { query('INSERT INTO activity_logs (user_id,action,details) VALUES (?,?,?)', [$_SESSION['uid'] ?? null,$action,$details]); }
