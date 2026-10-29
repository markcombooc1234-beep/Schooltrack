<?php
function required(string $key): string { $v = trim((string)($_POST[$key] ?? '')); if ($v === '') throw new RuntimeException(ucfirst(str_replace('_',' ',$key)).' is required.'); if (strlen($v)>255) throw new RuntimeException('Value is too long.'); return $v; }
function integer(string $key, int $minimum = 0): int { $v = filter_var($_POST[$key] ?? '', FILTER_VALIDATE_INT); if ($v === false || $v < $minimum) throw new RuntimeException('Invalid '.str_replace('_',' ',$key).'.'); return $v; }
