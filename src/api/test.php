<?php
// Include CORS headers and session configuration
require_once 'cors_fix.php';
require_once 'session_config.php';

// Return a simple success response
echo json_encode([
    "success" => true,
    "message" => "API is working",
    "timestamp" => date("Y-m-d H:i:s"),
    "session_active" => session_status() === PHP_SESSION_ACTIVE,
    "session_id" => session_id(),
    "php_version" => phpversion()
]);
?>