<?php
// fix_users.php - Script to fix any issues with the users table
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Content-Type: application/json");

// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Database connection parameters
$host = "localhost";
$dbname = "campus_db"; 
$dbuser = "root";
$dbpass = "";

// Connect to database
$conn = new mysqli($host, $dbuser, $dbpass, $dbname);

if ($conn->connect_error) {
    echo json_encode([
        "success" => false,
        "message" => "Database connection failed: " . $conn->connect_error
    ]);
    exit;
}

$actions = [];

// Check if users table exists
$tableExists = $conn->query("SHOW TABLES LIKE 'users'")->num_rows > 0;

if (!$tableExists) {
    // Create users table if it doesn't exist
    $createTableSQL = "CREATE TABLE `users` (
        `user_id` int(11) NOT NULL AUTO_INCREMENT,
        `firstname` varchar(50) NOT NULL,
        `middlename` varchar(50) DEFAULT NULL,
        `lastname` varchar(50) NOT NULL,
        `department` varchar(100) DEFAULT NULL,
        `email` varchar(100) NOT NULL,
        `username` varchar(50) NOT NULL,
        `password` varchar(255) NOT NULL,
        `role` enum('student','faculty','admin') DEFAULT 'student',
        `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
        PRIMARY KEY (`user_id`),
        UNIQUE KEY `email` (`email`),
        UNIQUE KEY `username` (`username`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
    
    if ($conn->query($createTableSQL)) {
        $actions[] = "Created users table";
    } else {
        $actions[] = "Failed to create users table: " . $conn->error;
    }
} else {
    $actions[] = "Users table already exists";
    
    // Check if there's at least one admin user
    $adminCheck = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'admin'");
    $adminCount = 0;
    
    if ($adminCheck) {
        $row = $adminCheck->fetch_assoc();
        $adminCount = $row['count'];
    }
    
    if ($adminCount == 0) {
        // Create default admin user if none exists
        $adminFirstName = "Admin";
        $adminLastName = "User";
        $adminEmail = "admin@example.com";
        $adminUsername = "admin";
        $adminPassword = password_hash("admin123", PASSWORD_DEFAULT);
        $adminRole = "admin";
        
        $sql = "INSERT INTO users (firstname, lastname, email, username, password, role) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssss", 
            $adminFirstName, 
            $adminLastName, 
            $adminEmail, 
            $adminUsername, 
            $adminPassword, 
            $adminRole
        );
        
        if ($stmt->execute()) {
            $actions[] = "Created default admin user (username: admin, password: admin123)";
        } else {
            $actions[] = "Failed to create admin user: " . $stmt->error;
        }
        
        $stmt->close();
    } else {
        $actions[] = "Admin user(s) already exist";
    }
}

// Return results
echo json_encode([
    "success" => true,
    "actions" => $actions
]);

$conn->close();
?>