<?php
function user(): ?array { return isset($_SESSION['uid']) ? query('SELECT id,name,email,role FROM users WHERE id=?', [$_SESSION['uid']])->fetch() ?: null : null; }
function manager(): bool { return in_array(user()['role'] ?? '', ['admin','custodian'], true); }
