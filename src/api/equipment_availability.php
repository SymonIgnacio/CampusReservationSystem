<?php
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    $host = "localhost";
    $dbname = "campus_db"; 
    $dbuser = "root";
    $dbpass = "";

    $conn = new mysqli($host, $dbuser, $dbpass, $dbname);

    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    // Get date parameter (optional)
    $checkDate = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

    // Get all equipment with their base quantities
    $sql = "SELECT equipment_id, name, quantity_available as total_stock FROM equipment ORDER BY name ASC";
    $result = $conn->query($sql);
    
    $equipment = [];
    while ($row = $result->fetch_assoc()) {
        $equipmentId = $row['equipment_id'];
        $equipmentName = $row['name'];
        $totalStock = $row['total_stock'];
        
        // Calculate used quantity for the specific date
        $usedSql = "SELECT COALESCE(SUM(
            CASE 
                WHEN equipments_needed LIKE CONCAT('%', ?, ' (', '%', ')%') THEN 
                    CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(equipments_needed, CONCAT(?, ' ('), -1), ')', 1) AS UNSIGNED)
                ELSE 0 
            END
        ), 0) as used_quantity
        FROM approved_request 
        WHERE date_need_from <= ? AND date_need_until >= ? 
        AND equipments_needed LIKE CONCAT('%', ?, '%')";
        
        $stmt = $conn->prepare($usedSql);
        $stmt->bind_param("sssss", $equipmentName, $equipmentName, $checkDate, $checkDate, $equipmentName);
        $stmt->execute();
        $usedResult = $stmt->get_result();
        $usedRow = $usedResult->fetch_assoc();
        $usedQuantity = $usedRow['used_quantity'] ?? 0;
        
        $availableQuantity = max(0, $totalStock - $usedQuantity);
        
        $equipment[] = [
            'equipment_id' => $equipmentId,
            'name' => $equipmentName,
            'total_stock' => $totalStock,
            'used_quantity' => $usedQuantity,
            'available_quantity' => $availableQuantity,
            'check_date' => $checkDate
        ];
        
        $stmt->close();
    }

    echo json_encode([
        "success" => true,
        "equipment" => $equipment,
        "check_date" => $checkDate
    ]);

    $conn->close();
} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}