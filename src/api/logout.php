<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Content-Type: application/json");
require_once 'cors_fix.php';

// Log logout attempt
error_log("Logout attempt. Session before: " . json_encode($_SESSION));

// Clear all session data
$_SESSION = array();

// Destroy the session
if (session_status() === PHP_SESSION_ACTIVE) {
    session_destroy();
}

// Log after logout
error_log("Session after logout: " . json_encode($_SESSION));

// Return success response
echo json_encode([
    "success" => true,
    "message" => "Logged out successfully"
]);
?>