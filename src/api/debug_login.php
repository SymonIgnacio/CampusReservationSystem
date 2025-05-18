<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Content-Type: application/json");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {    http_response_code(200);    exit();}// Output HTML for debugging


// Database connection
$host = "localhost";
$username = "root";
$password = "";
$database = "campus_db";

echo "<p>Connecting to database...</p>";

$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    echo "<p style='color:red'>Connection failed: " . $conn->connect_error . "</p>";
    exit;
}

echo "<p style='color:green'>Connected to database successfully</p>";

// Check if users table exists
$tableCheck = $conn->query("SHOW TABLES LIKE 'users'");
echo "<p>Users table exists: " . ($tableCheck->num_rows > 0 ? "Yes" : "No") . "</p>";

if ($tableCheck->num_rows == 0) {
    echo "<h2>Creating users table...</h2>";
    
    // Create users table
    $createTableSQL = "CREATE TABLE users (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        firstname VARCHAR(255) NOT NULL,
        middlename VARCHAR(255),
        lastname VARCHAR(255) NOT NULL,
        department VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL UNIQUE,
        username VARCHAR(255) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        role VARCHAR(50) DEFAULT 'student',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    if ($conn->query($createTableSQL)) {
        echo "<p style='color:green'>Users table created successfully</p>";
        
        // Create a default admin user
        $adminFirstName = "Admin";
        $adminMiddleName = "";
        $adminLastName = "User";
        $adminDepartment = "Administration";
        $adminEmail = "admin@example.com";
        $adminUsername = "admin";
        $adminPassword = password_hash("admin123", PASSWORD_DEFAULT);
        $adminRole = "admin";
        
        $sql = "INSERT INTO users (firstname, middlename, lastname, department, email, username, password, role)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssss", $adminFirstName, $adminMiddleName, $adminLastName, $adminDepartment, $adminEmail, $adminUsername, $adminPassword, $adminRole);
        
        if ($stmt->execute()) {
            echo "<p style='color:green'>Default admin user created successfully</p>";
            echo "<p>Username: admin</p>";
            echo "<p>Password: admin123</p>";
        } else {
            echo "<p style='color:red'>Error creating admin user: " . $stmt->error . "</p>";
        }
        
        $stmt->close();
    } else {
        echo "<p style='color:red'>Error creating users table: " . $conn->error . "</p>";
    }
} else {
    // Show users in the table
    echo "<h2>Users in the database:</h2>";
    $result = $conn->query("SELECT id, username, firstname, lastname, email, role FROM users");
    
    if ($result->num_rows > 0) {
        echo "<table border='1'>";
        echo "<tr><th>ID</th><th>Username</th><th>Name</th><th>Email</th><th>Role</th></tr>";
        
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['id'] . "</td>";
            echo "<td>" . $row['username'] . "</td>";
            echo "<td>" . $row['firstname'] . " " . $row['lastname'] . "</td>";
            echo "<td>" . $row['email'] . "</td>";
            echo "<td>" . $row['role'] . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    } else {
        echo "<p>No users found in the database.</p>";
    }
}

// Test login API
echo "<h2>Testing Login API</h2>";
echo "<form method='post' action='login.php' target='_blank'>";
echo "<label for='username'>Username:</label>";
echo "<input type='text' id='username' name='username' value='admin'><br><br>";
echo "<label for='password'>Password:</label>";
echo "<input type='password' id='password' name='password' value='admin123'><br><br>";
echo "<input type='submit' value='Test Login'>";
echo "</form>";

// Show login.php content
echo "<h2>Login.php Content:</h2>";
echo "<pre>" . htmlspecialchars(file_get_contents("login.php")) . "</pre>";

$conn->close();
echo "<p>Database connection closed.</p>";
?>