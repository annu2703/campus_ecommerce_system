<?php
session_start();
require_once 'config.php';

// Check if the user is logged in and the product ID is provided
if (isset($_SESSION['user_id']) && isset($_POST['product_id']) && isset($_POST['quantity'])) {
    $userId = $_SESSION['user_id'];
    $productId = $_POST['product_id'];
    $quantity = $_POST['quantity'];

    // Check if the item is already in the cart
    $stmt = $link->prepare("SELECT id FROM cart WHERE user_id = ? AND product_id = ?");
    $stmt->bind_param("ii", $userId, $productId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // If the item is already in the cart, update the quantity
        $row = $result->fetch_assoc();
        $cartId = $row['id'];
        $updateStmt = $link->prepare("UPDATE cart SET quantity = quantity + ? WHERE id = ?");
        $updateStmt->bind_param("ii", $quantity, $cartId);
        $updateStmt->execute();
        $updateStmt->close();
    } else {
        // If the item is not in the cart, insert a new record
        $insertStmt = $link->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
        $insertStmt->bind_param("iii", $userId, $productId, $quantity);
        $insertStmt->execute();
        $insertStmt->close();
    }

    $stmt->close();
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "message" => "User not logged in or invalid product ID or quantity"]);
}

$link->close();
?>