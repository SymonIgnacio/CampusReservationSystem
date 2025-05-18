<?php
// Disable error display in response
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Direct CORS headers first - must be before any output
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    // Connect to DB
    $host = "localhost";
    $dbname = "campus_db"; 
    $dbuser = "root";
    $dbpass = "";

    $conn = new mysqli($host, $dbuser, $dbpass, $dbname);

    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    // Check if users table exists with correct structure
    $tableCheck = $conn->query("SHOW TABLES LIKE 'users'");
    
    if ($tableCheck->num_rows == 0) {
        // Create users table with the correct structure from campus_db.sql
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
        
        if (!$conn->query($createTableSQL)) {
            throw new Exception("Failed to create users table: " . $conn->error);
        }
        
        // Create default admin user
        $adminFirstName = "Admin";
        $adminMiddleName = "";
        $adminLastName = "User";
        $adminDepartment = "IT Department";
        $adminEmail = "admin@example.com";
        $adminUsername = "admin";
        $adminPassword = password_hash("admin123", PASSWORD_DEFAULT);
        $adminRole = "admin";
        
        $sql = "INSERT INTO users (firstname, middlename, lastname, department, email, username, password, role) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssss", 
            $adminFirstName, 
            $adminMiddleName, 
            $adminLastName, 
            $adminDepartment, 
            $adminEmail, 
            $adminUsername, 
            $adminPassword, 
            $adminRole
        );
        
        $stmt->execute();
        $stmt->close();
    }

    // Get all users from the table EXCEPT admin users
    $sql = "SELECT user_id, firstname, lastname, email, username, department, role FROM users WHERE role != 'admin'";
    $result = $conn->query($sql);

    if (!$result) {
        throw new Exception("Query failed: " . $conn->error);
    }

    $users = [];
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }

    // Return results
    echo json_encode([
        "success" => true,
        "users" => $users
    ]);

    $conn->close();
} catch (Exception $e) {
    // Log error to server log
    error_log("Error in users.php: " . $e->getMessage());
    
    // Return error as JSON
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}
?>