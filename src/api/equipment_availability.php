<?php
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET");
header("Content-Type: application/json");

$date = $_GET['date'] ?? date('Y-m-d');

try {
    $conn = new mysqli("localhost", "root", "", "campus_db");
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    // Calculate available equipment for the specific date
    $sql = "SELECT 
        e.equipment_id,
        e.name,
        e.quantity_available as total_stock,
        COALESCE(SUM(CASE 
            WHEN ar.equipments_needed LIKE CONCAT('%', e.name, ' (%') 
            AND ar.date_need_from <= ? AND ar.date_need_until >= ?
            THEN CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(ar.equipments_needed, CONCAT(e.name, ' ('), -1), ')', 1) AS UNSIGNED)
            ELSE 0
        END), 0) as reserved_quantity
    FROM equipment e
    LEFT JOIN approved_request ar ON ar.equipments_needed LIKE CONCAT('%', e.name, ' (%')
    GROUP BY e.equipment_id, e.name, e.quantity_available
    ORDER BY e.name";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $date, $date);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $equipment = [];
    while ($row = $result->fetch_assoc()) {
        $available = max(0, $row['total_stock'] - $row['reserved_quantity']);
        $equipment[] = [
            'equipment_id' => $row['equipment_id'],
            'name' => $row['name'],
            'total_stock' => $row['total_stock'],
            'reserved_quantity' => $row['reserved_quantity'],
            'available_quantity' => $available
        ];
    }
    
    echo json_encode([
        "success" => true,
        "equipment" => $equipment,
        "date" => $date
    ]);
    
    $conn->close();
} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}
?>