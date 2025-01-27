<?php
require_once 'config.php'; // Ensure this path is correct

$timePeriod = $_GET['time_period'] ?? 'month';

// Adjust the SQL query based on the time period
switch ($timePeriod) {
    case 'year':
        $sql = "SELECT product_name, SUM(quantity) as total_quantity 
                FROM orders 
                WHERE YEAR(created_at) = YEAR(CURRENT_DATE) 
                GROUP BY product_name";
        break;
    case 'week':
        $sql = "SELECT product_name, SUM(quantity) as total_quantity 
                FROM orders 
                WHERE YEARWEEK(created_at, 1) = YEARWEEK(CURRENT_DATE, 1) 
                GROUP BY product_name";
        break;
    case 'month':
    default:
        $sql = "SELECT product_name, SUM(quantity) as total_quantity 
                FROM orders 
                WHERE MONTH(created_at) = MONTH(CURRENT_DATE) 
                  AND YEAR(created_at) = YEAR(CURRENT_DATE) 
                GROUP BY product_name";
        break;
}

$result = $link->query($sql);
$data = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
}

echo json_encode($data);

$link->close();
?>
