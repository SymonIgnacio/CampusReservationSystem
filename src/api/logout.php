<?php
// Include centralized CORS handler
require_once 'cors_handler.php';

// Include session configuration
require_once 'session_config.php';

// Destroy the session
session_destroy();

echo json_encode([
    "success" => true,
    "message" => "Logged out successfully"
]);
?>