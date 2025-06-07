<?php
// Central API entry point that applies CORS headers to all requests
// Place this file in the api directory

// CORS headers are already set in .htaccess and web.config
// Do not set them here to avoid duplicates

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Log API access for debugging
error_log("API accessed: " . $_SERVER['REQUEST_URI']);

// Continue normal execution for non-OPTIONS requests
?>