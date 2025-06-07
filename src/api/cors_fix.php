<?php
// cors_fix.php - Include this file at the top of all API files to ensure consistent CORS handling

// Skip setting CORS headers since they're already set in .htaccess and web.config
// This prevents duplicate headers that cause CORS errors

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Set content type for JSON responses
header("Content-Type: application/json");
?>