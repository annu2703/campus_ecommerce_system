<?php
session_start();

$sellerUsername = $_SESSION['username'];
$matricNum = $_SESSION['matrics_number'];

require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Process the form submission
    $userId = $_SESSION['user_id'];
    $meetupLocation = $_POST['meetup_location'];
    $date = $_POST['date'];
    $time = $_POST['time'];
    $deliveryMode = $_POST['delivery_mode'];

    // Prepare the statement for inserting into the orders table
    $stmt = $link->prepare("INSERT INTO orders (product_id, product_name, quantity, unit_price, total_price, seller_name, meetup_location, date, time, delivery_mode, buyer_username, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");

    foreach ($_POST['selected_items'] as $key => $productId) {
        $productName = $_POST['selected_item_names'][$key];
        $quantity = $_POST['selected_item_quantities'][$key];
        $unitPrice = $_POST['selected_item_prices'][$key];
        $totalPrice = $_POST['selected_item_total_prices'][$key];
        $buyerUsername = $_SESSION['username'];

        // Fetch the seller name from the products table
        $sellerStmt = $link->prepare("SELECT seller_name FROM product WHERE id = ?");
        $sellerStmt->bind_param("i", $productId);
        $sellerStmt->execute();
        $sellerResult = $sellerStmt->get_result();
        $sellerRow = $sellerResult->fetch_assoc();
        $sellerName = $sellerRow['seller_name'];
        $sellerStmt->close();

        // Bind and execute the insert statement
        $stmt->bind_param("isiddssssss", $productId, $productName, $quantity, $unitPrice, $totalPrice, $sellerName, $meetupLocation, $date, $time, $deliveryMode, $buyerUsername);
        $stmt->execute();
    }

    // Clear the cart after purchase
    $deleteSql = "DELETE FROM cart WHERE user_id = ?";
    $deleteStmt = $link->prepare($deleteSql);
    $deleteStmt->bind_param("i", $userId);
    $deleteStmt->execute();
    $deleteStmt->close();

    // Redirect to a success page or display a success message
    header("Location: purchase.php");
    exit();
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buyer Dashboard</title>
    <link rel="stylesheet" href="purchase.css"> <!-- Link to your CSS file -->
</head>

<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="sidebar-top">
                <div class="profile-section">
                    <h2>
                        <?php echo ucfirst($sellerUsername) . ' - ' . $matricNum; ?>
                    </h2>
                </div>
                <nav class="sidebar-nav">
                    <ul>
                        <li><a href="buyerDashboard.php" style="text-decoration: none;"><i
                                    class="icon-dashboard"></i>Product Listing</a></li>
                        <li class="active"><a href="viewMyCart.php" style="text-decoration: none;"><i
                                    class="icon-insights"></i>Payment</a>
                        </li>
                    </ul>
                </nav>
            </div>
            <div class="sidebar-bottom">
                <a href="logout.php" class="logout"><i class="icon-logout"></i>Log out</a>
            </div>
        </aside>
        <main class="main-content">
            <header class="dashboard-header">
                <h1>Welcome</h1>
            </header>

            <div class="payment-container">
                <h2>PURCHASE CONFIRMED</h2>
                <h3> CASH ON DELIVERY CONFIRMED</h3>
                <img src="confirm.jpeg" alt="confirmation image" class="mock-qr">
                <!-- Replace with your mock QR code image -->
                <!-- <p>Contact us through our WhatsApp number for further information.</p>
                <a href="javascript:void(0);" onclick="confirmPayment()" class="whatsapp-link">Click this WhatsApp
                    Link</a> -->
            </div>
            <script>
                function confirmPayment() {
                    // Generate the WhatsApp URL
                    const whatsappNumber = "+60163620092"; // Replace with your actual WhatsApp number
                    const message = encodeURIComponent("I have made the payment, here is the proof of my purchase.");
                    const whatsappUrl = https://wa.me/${whatsappNumber}?text=${message};

                        // Perform the backend operation before opening WhatsApp
                        fetch('processPayment.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({ action: 'confirm_payment' }) // Send whatever data you need
                        })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    // Open WhatsApp only if the backend process is successful
                                    window.open(whatsappUrl, '_blank');
                                } else {
                                    alert('There was a problem processing the payment.');
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                alert('An error occurred while processing your payment.');
                            });
                }
            </script>


    </div>

    </main>
    </div>
</body>

</html>
