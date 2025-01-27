<?php
require_once 'config.php'; // Database connection

$id = $_POST['id'] ?? null;
$status = $_POST['status'] ?? null;

$response = ['success' => false];

if ($id !== null && $status !== null) {
    $sql = "UPDATE product SET approved = ? WHERE id = ?";
    if ($stmt = $link->prepare($sql)) {
        $stmt->bind_param("ii", $status, $id);
        if ($stmt->execute()) {
            $response['success'] = true;
        }
        $stmt->close();
    }
    $link->close();
}

echo json_encode($response);
?>