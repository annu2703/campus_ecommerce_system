<?php
session_start();
require_once 'config.php'; // Database connection file

header('Content-Type: application/json');

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

$userId = $_SESSION['user_id'];

// Start transaction
$link->begin_transaction(MYSQLI_TRANS_START_READ_WRITE);

try {
    // Remove items from the user's cart
    $stmt = $link->prepare("DELETE FROM cart WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->close();

    // Update product quantities based on the items that were in the cart
    // NOTE: This requires the cart to have stored the quantity to remove from products.
    // You would adjust this logic based on how your application should handle inventory.
    // This is just a simplified placeholder.
    $stmt = $link->prepare("UPDATE product SET quantity = quantity - ? WHERE id = ?");
    // You should loop through each product that was purchased and update accordingly
    // $stmt->bind_param("ii", $quantityToRemove, $productId);
    // $stmt->execute();

    // If all operations were successful, commit the transaction
    $link->commit();
    echo json_encode(['success' => true, 'message' => 'Payment processed and cart cleared']);
} catch (Exception $e) {
    // If an error occurs, roll back the transaction
    $link->rollback();
    echo json_encode(['success' => false, 'message' => 'Error processing payment']);
}

// Close connection
$link->close();
?>