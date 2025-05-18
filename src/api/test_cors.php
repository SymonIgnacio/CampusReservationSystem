<?php
// Set CORS headers directly
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");

// Return debug information
echo json_encode([
    "success" => true,
    "message" => "CORS test successful",
    "headers_sent" => headers_sent(),
    "request_method" => $_SERVER['REQUEST_METHOD'],
    "timestamp" => date("Y-m-d H:i:s")
]);
?>