<?php
// Include CORS fix
require_once 'cors_fix.php';

// Include session configuration
require_once 'session_config.php';

// Disable error display in response
ini_set('display_errors', 0);
error_reporting(E_ALL);

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

    // Get department stats from approved requests
    $dept_query = "SELECT department_organization as department, COUNT(*) as count 
                  FROM approved_request 
                  GROUP BY department_organization 
                  ORDER BY count DESC";
    $result = $conn->query($dept_query);
    
    if (!$result) {
        throw new Exception("Query failed for department stats: " . $conn->error);
    }
    
    $departments = [];
    while ($row = $result->fetch_assoc()) {
        $departments[] = $row;
    }
    
    // Get monthly stats
    $monthly_query = "SELECT 
                      YEAR(date_need_from) as year,
                      MONTH(date_need_from) as month,
                      COUNT(*) as count
                      FROM approved_request
                      WHERE date_need_from >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
                      GROUP BY YEAR(date_need_from), MONTH(date_need_from)
                      ORDER BY year ASC, month ASC";
    $result = $conn->query($monthly_query);
    
    if (!$result) {
        throw new Exception("Query failed for monthly stats: " . $conn->error);
    }
    
    $monthly = [];
    while ($row = $result->fetch_assoc()) {
        // Convert month number to name
        $monthNum = $row['month'];
        $dateObj = DateTime::createFromFormat('!m', $monthNum);
        $monthName = $dateObj->format('M');
        
        $monthly[] = [
            'month' => $monthName . ' ' . $row['year'],
            'count' => $row['count']
        ];
    }
    
    // Return data
    echo json_encode([
        'status' => 'success',
        'departments' => $departments,
        'monthly' => $monthly
    ]);

    $conn->close();
} catch (Exception $e) {
    // Log error to server log
    error_log("Error in department_stats.php: " . $e->getMessage());
    
    // Return error as JSON
    echo json_encode([
        'status' => 'error',
        'message' => 'Error fetching stats: ' . $e->getMessage()
    ]);
}