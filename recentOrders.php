<?php
require_once 'config.php'; // Ensure this path is correct

session_start();

// Check if the user is logged in as a seller
if (!isset($_SESSION['username']) || $_SESSION['user_role'] !== 'seller') {
    // Redirect to login page or handle unauthorized access
    header("Location: index.php");
    exit();
}

// Fetch the seller's username from the session data
$sellerUsername = $_SESSION['username'];

$matricNum = $_SESSION['matrics_number'];

// Fetch recent orders from the database
$sql = "SELECT product_name, quantity, time, date, meetup_location, delivery_mode FROM orders WHERE seller_name = ? ORDER BY date DESC, time DESC";

$stmt = $link->prepare($sql);
$stmt->bind_param("s", $sellerUsername);
$stmt->execute();
$result = $stmt->get_result();


?>

<!DOCTYPE html>

<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recent Orders</title>
    <link rel="stylesheet" href="productListing.css"> <!-- Link to your CSS file -->
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
                        <li><a href="sellerDashboard.php" style="text-decoration: none;"><i
                                    class="icon-dashboard"></i>Dashboard</a></li>
                        <li><a href="productListing.php" style="text-decoration: none;"><i
                                    class="icon-insights"></i>Products Listing</a></li>
                        <li><a href="addNewProduct.php" style="text-decoration: none;"><i class="icon-reports"></i>Add
                                Product</a></li>
                        <li class="active"><a href="recentOrders.php" style="text-decoration: none;"><i
                                    class="icon-channels"></i>Recent Orders</a></li>
                    </ul>
                </nav>
            </div>
            <div class="sidebar-bottom">
                <a href="logout.php" class="logout"><i class="icon-logout"></i>Log out</a>
            </div>
        </aside>
        <main class="main-content">
            <header class="dashboard-header">
                <h1>Recent Orders</h1>
                <!-- <div class="header-icons">
                    <img src="settings.png" alt="Settings" class="icon-settings">
                </div> -->
            </header>

            <div class="table-container">
                <div class="table-header">
                    <div class="header-item">ORDER DETAILS</div>
                    <div class="header-item">TIME</div>
                    <div class="header-item">DATE</div>
                    <div class="header-item">MEETUP LOCATION</div>
                    <div class="header-item">DELIVERY MODE</div>
                </div>

                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        // Output each order as a table row
                        echo '<div class="table-row">';
                        echo '<div class="row-item">';
                        echo 'Product Name: ' . htmlspecialchars($row['product_name']) . '<br>';
                        echo 'Quantity: ' . htmlspecialchars($row['quantity']);
                        echo '</div>';
                        echo '<div class="row-item">' . htmlspecialchars($row['time']) . '</div>';
                        echo '<div class="row-item">' . htmlspecialchars($row['date']) . '</div>';
                        echo '<div class="row-item">' . htmlspecialchars($row['meetup_location']) . '</div>';
                        echo '<div class="row-item">' . htmlspecialchars($row['delivery_mode']) . '</div>';
                        echo '</div>';
                    }
                } else {
                    // Output if no recent orders found
                    echo '<div class="no-orders">No recent orders found.</div>';
                }
                ?>

                <!-- <div class="table-row">
                    <div class="row-item">
                        Product Name : Table
                        Quantity : 2
                    </div>
                    <div class="row-item">
                        03:00 PM
                    </div>
                    <div class="row-item">
                        12/04/2024
                    </div>
                    <div class="row-item">
                        Cafeteria
                    </div>
                    <div class="row-item">
                        Delivery
                    </div>
                </div>

                <div class="table-row">
                    <div class="row-item">
                        Product Name : Chair
                        Quantity : 7
                    </div>
                    <div class="row-item">
                        08:00 AM
                    </div>
                    <div class="row-item">
                        27/04/2024
                    </div>
                    <div class="row-item">
                        Library
                    </div>
                    <div class="row-item">
                        Pick Up
                    </div>
                </div>

                <div class="table-row">
                    <div class="row-item">
                        Product Name : Notebook
                        Quantity : 1
                    </div>
                    <div class="row-item">
                        12:00 PM
                    </div>
                    <div class="row-item">
                        25/04/2024
                    </div>
                    <div class="row-item">
                        Cafeteria
                    </div>
                    <div class="row-item">
                        Pick Up
                    </div>
                </div> -->

            </div>

        </main>
</body>

</html>