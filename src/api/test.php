<?php
// Include CORS fix first to handle all CORS headers properly
require_once 'cors_fix.php';

// OPTIONS request is already handled in cors_fix.php

// Simple API test endpoint
echo json_encode([
    "success" => true,
    "message" => "API connection successful",
    "timestamp" => date('Y-m-d H:i:s')
]);
?>