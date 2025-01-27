<?php
session_start();
require_once 'config.php';

// Check if the user is logged in as a seller
if (!isset($_SESSION['username']) || $_SESSION['user_role'] !== 'buyer') {
    // Redirect to login page or handle unauthorized access
    header("Location: index.php");
    exit();
}

// Fetch the seller's username from the session data
$sellerUsername = $_SESSION['username'];

$matricNum = $_SESSION['matrics_number'];

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['selected_items'])) {
    $selectedItems = $_POST['selected_items'];

    // Fetch the details of the selected items from the database
    $sql = "SELECT p.product_name, c.quantity, p.price FROM cart c JOIN product p ON c.product_id = p.id WHERE c.user_id = ? AND c.product_id IN (" . implode(',', array_fill(0, count($selectedItems), '?')) . ")";
    $stmt = $link->prepare($sql);

    // Prepare the bind parameters
    $userId = $_SESSION['user_id'];
    $placeholders = str_repeat('?,', count($selectedItems) - 1) . '?';
    $types = str_repeat('i', count($selectedItems)) . 'i';

    // Create a reference array for the parameters
    $params = array($types);
    foreach ($selectedItems as $item) {
        $params[] = &$item;
    }
    $params[] = &$userId;

    // Bind the parameters
    call_user_func_array(array($stmt, 'bind_param'), $params);

    $stmt->execute();
    $result = $stmt->get_result();


    // Calculate the grand total
    $grandTotal = 0;
    $items = [];
    while ($row = $result->fetch_assoc()) {
        $itemName = $row['product_name'];
        $itemQuantity = $row['quantity'];
        $itemPrice = $row['price'];
        $itemTotal = $itemQuantity * $itemPrice;
        $grandTotal += $itemTotal;

        // Store item details for display
        $items[] = [
            'name' => $itemName,
            'quantity' => $itemQuantity,
            'price' => $itemPrice,
            'total' => $itemTotal
        ];
    }

    // Display the selected items with their details and the grand total
    ?>
    <div class="selected-items-container">
        <h2>Selected Items</h2>
        <div class="items-table">
            <div class="table-header">
                <div class="header-item">PRODUCT NAME</div>
                <div class="header-item">QUANTITY</div>
                <div class="header-item">UNIT PRICE</div>
                <div class="header-item">TOTAL PRICE</div>
            </div>
            <?php foreach ($items as $item): ?>
                <div class="table-row">
                    <div class="row-item"><?php echo htmlspecialchars($item['name']); ?></div>
                    <div class="row-item"><?php echo htmlspecialchars($item['quantity']); ?></div>
                    <div class="row-item">RM <?php echo htmlspecialchars(number_format($item['price'], 2)); ?></div>
                    <div class="row-item">RM <?php echo number_format($item['total'], 2); ?></div>
                </div>
            <?php endforeach; ?>
            <div class="table-row">
                <div class="row-item"></div>
                <div class="row-item"></div>
                <div class="row-item">Grand Total:</div>
                <div class="row-item">RM <?php echo number_format($grandTotal, 2); ?></div>
            </div>
        </div>
    </div>
    <div class="input-details-container">
        <h2>Enter Details</h2>
        <!-- Add form fields for meetup location, date, time, and delivery mode here -->
        <!-- Example: -->
        <form action="processPurchase.php" method="post">
            <label for="meetup_location">Meetup Location:</label>
            <input type="text" id="meetup_location" name="meetup_location" required>
            <label for="date">Date:</label>
            <input type="date" id="date" name="date" required>
            <label for="time">Time:</label>
            <input type="time" id="time" name="time" required>
            <label for="delivery_mode">Delivery Mode:</label>
            <select id="delivery_mode" name="delivery_mode" required>
                <option value="pickup">Pickup</option>
                <option value="delivery">Delivery</option>
            </select>
            <input type="submit" value="Confirm Purchase">
        </form>
    </div>
    <?php
} else {
    // Redirect if the form is not submitted or if no items are selected
    header("Location: viewMyCart.php");
    exit();
}
?>
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
                <h1>Confirm Purchase</h1>
            </header>
        </main>
    </div>
</body>

</html>