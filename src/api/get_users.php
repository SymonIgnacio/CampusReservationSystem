<?php
// Include CORS headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, OPTIONS");
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Connect to DB
$host = "localhost";
$dbname = "campus_db"; 
$dbuser = "root";
$dbpass = "";
header("Content-Type: application/json");

$conn = new mysqli($host, $dbuser, $dbpass, $dbname);

if ($conn->connect_error) {
    echo json_encode([
        "success" => false,
        "message" => "Connection failed: " . $conn->connect_error
    ]);
    exit;
}

// Check if users table exists
$usersTableExists = $conn->query("SHOW TABLES LIKE 'users'")->num_rows > 0;

if (!$usersTableExists) {
    // Create users table
    $createTableSQL = "CREATE TABLE users (
        user_id INT(11) AUTO_INCREMENT PRIMARY KEY,
        firstname VARCHAR(255) NOT NULL,
        middlename VARCHAR(255),
        lastname VARCHAR(255) NOT NULL,
        department VARCHAR(255),
        email VARCHAR(255) NOT NULL UNIQUE,
        username VARCHAR(255) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        role VARCHAR(50) DEFAULT 'student',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    if (!$conn->query($createTableSQL)) {
        echo json_encode([
            "success" => false,
            "message" => "Failed to create users table: " . $conn->error
        ]);
        exit;
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

// Get only students and faculty from the table (exclude admins)
$sql = "SELECT user_id, firstname, lastname, email, username, department, role FROM users WHERE role IN ('student', 'faculty')";
$result = $conn->query($sql);

if (!$result) {
    echo json_encode([
        "success" => false,
        "message" => "Query failed: " . $conn->error
    ]);
    exit;
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
?>