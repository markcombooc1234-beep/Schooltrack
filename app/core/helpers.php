<?php
function e($value): string { return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8'); }
function csrf(): string { return '<input type="hidden" name="csrf" value="'.e($_SESSION['csrf']).'">'; }
function redirect(string $page): never { header('Location: index.php?page='.$page); exit; }
function flash(string $message): void { $_SESSION['flash'] = $message; }
