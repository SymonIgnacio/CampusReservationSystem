<?php
// Allow from any origin
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Content-Type: application/json");

// Database connection details
$db_host = "localhost";
$db_name = "campus_db";
$db_user = "root";
$db_pass = "";

try {
    // Connect to database
    $conn = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get monthly request data
    $monthly_query = "SELECT 
                        MONTHNAME(STR_TO_DATE(date_need_from, '%Y-%m-%d')) as month, 
                        COUNT(*) as count 
                      FROM request 
                      GROUP BY month 
                      ORDER BY MONTH(STR_TO_DATE(date_need_from, '%Y-%m-%d'))";
    $monthly_stmt = $conn->prepare($monthly_query);
    $monthly_stmt->execute();
    $monthly_data = $monthly_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get department request data
    $dept_query = "SELECT 
                    department_organization as department, 
                    COUNT(*) as count 
                   FROM request 
                   GROUP BY department 
                   ORDER BY count DESC 
                   LIMIT 5";
    $dept_stmt = $conn->prepare($dept_query);
    $dept_stmt->execute();
    $department_data = $dept_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get status distribution data
    $pending_query = "SELECT COUNT(*) as count FROM request WHERE status = 'pending'";
    $pending_stmt = $conn->prepare($pending_query);
    $pending_stmt->execute();
    $pending_count = $pending_stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    $approved_query = "SELECT COUNT(*) as count FROM approved_request";
    $approved_stmt = $conn->prepare($approved_query);
    $approved_stmt->execute();
    $approved_count = $approved_stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    $declined_query = "SELECT COUNT(*) as count FROM declined_request";
    $declined_stmt = $conn->prepare($declined_query);
    $declined_stmt->execute();
    $declined_count = $declined_stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    $status_data = [
        ['status' => 'Approved', 'count' => (int)$approved_count],
        ['status' => 'Pending', 'count' => (int)$pending_count],
        ['status' => 'Declined', 'count' => (int)$declined_count]
    ];
    
    // Return all chart data as JSON
    echo json_encode([
        'status' => 'success',
        'monthly_data' => $monthly_data,
        'department_data' => $department_data,
        'status_data' => $status_data
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>