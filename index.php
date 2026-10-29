<?php
require __DIR__.'/app/bootstrap.php';

// Public pages do not need a database connection. Query errors on protected
// pages replace buffered HTML with a complete service-unavailable response.
$initialBufferLevel = ob_get_level();
ob_start();
try {
    require __DIR__.'/app/router.php';
    ob_end_flush();
} catch (PDOException $error) {
    while (ob_get_level() > $initialBufferLevel) {
        ob_end_clean();
    }
    error_log((string) $error);
    http_response_code(503);
    require __DIR__.'/app/views/database-error.php';
}
