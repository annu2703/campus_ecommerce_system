<?php
require_once 'config.php'; // Ensure this path is correct

session_start();

// Check if the user is logged in as a seller
if (!isset($_SESSION['username']) || $_SESSION['user_role'] !== 'admin') {
    // Redirect to login page or handle unauthorized access
    header("Location: index.php");
    exit();
}

// Fetch the seller's username from the session data
$sellerUsername = $_SESSION['username'];

$matricNum = $_SESSION['matrics_number'];

// Prepare the SQL statement to fetch products
$sql = "SELECT id, product_name, quantity, total_price, buyer_username, created_at FROM orders";

// Attempt to execute the prepared statement
$result = $link->query($sql);

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
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
                        <li><a href="adminDashboard.php" style="text-decoration: none;"><i
                                    class="icon-dashboard"></i>Product Approval</a></li>
                        <li class="active"><a href="recentTransaction.php" style="text-decoration: none;"><i
                                    class="icon-insights"></i>Recent Orders</a>
                        </li>
                        <li><a href="salesTrend.php" style="text-decoration: none;"><i class="icon-insights"></i>Sales
                                Trend</a>
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
                <h1>Recent Orders</h1>
            </header>

            <div class="table-container">
                <div class="table-header">
                    <div class="header-item">PRODUCT NAME</div>
                    <div class="header-item">QUANTITY</div>
                    <div class="header-item">PRICE PAID</div>
                    <div class="header-item">BUYER USERNAME</div>
                    <div class="header-item">PURCHASE DATE</div>
                </div>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <div class="table-row">
                            <div class="row-item">
                                <?php echo htmlspecialchars($row['product_name']); ?>
                            </div>
                            <div class="row-item">
                                <?php echo htmlspecialchars($row['quantity']); ?>
                            </div>
                            <div class="row-item">
                                <?php echo htmlspecialchars($row['total_price']); ?>
                            </div>
                            <div class="row-item">
                                <?php echo htmlspecialchars($row['buyer_username']); ?>
                            </div>
                            <div class="row-item">
                                <?php echo htmlspecialchars($row['created_at']); ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="table-row">No orders placed.</div>
                <?php endif; ?>


                <?php
                // Close database connection
                $link->close();
                ?>
                <!-- Repeat .table-row for each product -->

                <script>
                    document.getElementById('editButton').addEventListener('click', function () {
                        window.location.href = 'editProduct.php'; // Change 'dashboard.php' to the actual path of your dashboard page
                    });
                    document.querySelectorAll('.editButton').forEach(button => {
                        button.addEventListener('click', function () {
                            // Retrieve the product ID from this button's data-product-id attribute
                            const productId = this.getAttribute('data-product-id');

                            // Redirect to the edit page, passing the product ID as a query parameter
                            window.location.href = `editProduct.php?product_id=${productId}`;
                        });
                    });
                </script>
            </div>



        </main>
    </div>
    <script src="dashboard.js"></script> <!-- Link to your JavaScript file -->
</body>

</html>