<?php
require_once 'config.php';

if (!isset($_GET['product_id'])) {
    echo json_encode(array('success' => false, 'message' => 'Product ID not provided.'));
    exit();
}

$productId = $_GET['product_id'];

// Fetch the quantity of the product
$sql = "SELECT quantity FROM product WHERE id = ?";
$stmt = $link->prepare($sql);
$stmt->bind_param("i", $productId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $quantity = $row['quantity'];
    echo json_encode(array('success' => true, 'quantity' => $quantity));
} else {
    echo json_encode(array('success' => false, 'message' => 'Product not found.'));
}

$stmt->close();
$link->close();
?>
