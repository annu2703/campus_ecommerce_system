<?php
session_start();
require_once 'config.php';

// Check if the user is logged in as a seller
if (!isset($_SESSION['username']) || $_SESSION['user_role'] !== 'seller') {
    // Redirect to login page or handle unauthorized access
    header("Location: index.php");
    exit();
}

// Fetch the seller's username from the session data
$sellerUsername = $_SESSION['username'];

$matricNum = $_SESSION['matrics_number'];

$sql = "SELECT buyer_username, COUNT(*) AS order_count FROM orders  WHERE seller_name = ? GROUP BY buyer_username ORDER BY order_count DESC";

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
    <title>Dashboard</title>
    <link rel="stylesheet" href="sellerDashboard.css"> <!-- Link to your CSS file -->
</head>

<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="sidebar-top">
                <div class="profile-section">
                    <h2>
                        <?php echo ucfirst($sellerUsername) . ' - ' . $matricNum;
                        ; ?>
                    </h2>
                </div>
                <nav class="sidebar-nav">
                    <ul>
                        <li class="active"><a href="sellerDashboard.php" style="text-decoration: none;"><i
                                    class="icon-dashboard"></i>Dashboard</a></li>
                        <li><a href="productListing.php" style="text-decoration: none;"><i
                                    class="icon-insights"></i>Products Listing</a></li>
                        <li><a href="addNewProduct.php" style="text-decoration: none;"><i class="icon-reports"></i>Add
                                Product</a></li>
                        <li><a href="recentOrders.php" style="text-decoration: none;"><i
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
                <h1>Welcome</h1>
                <!-- <div class="header-icons">
                    <img src="settings.png" alt="Settings" class="icon-settings">
                </div> -->
            </header>
            <section class="statistics-cards">
                <a href="addNewProduct.php" class="card">
                    <span>Quick Menu</span>
                    <h3>Add Product</h3>
                </a>
                <a href="productListing.php" class="card">
                    <span>Quick Menu</span>
                    <h3>Product Listing</h3>
                </a>
            </section>
            <section class="channels">
                <h2>Recent Orders</h2>
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        // Output each channel card with the buyer username and order count
                        echo '<div class="channel-card">';
                        echo '<span>' . htmlspecialchars($row['buyer_username']) . '</span>';
                        echo '<div class="channel-stats">' . htmlspecialchars($row['order_count']) . '</div>';
                        echo '</div>';
                    }
                } else {
                    // Output if no recent orders found
                    echo '<div class="no-orders">No recent orders found.</div>';
                }
                ?>
            </section>
        </main>
    </div>
    <script src="dashboard.js"></script> <!-- Link to your JavaScript file -->
</body>

</html>