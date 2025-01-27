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

$sql = "SELECT id, product_name, price, image_path FROM product WHERE approved = 0";
$result = $link->query($sql);
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="adminDashboard.css"> <!-- Link to your CSS file -->
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
                        <li class="active"><a href="adminDashboard.php" style="text-decoration: none;"><i
                                    class="icon-dashboard"></i>Product Approval</a></li>
                        <li><a href="recentTransaction.php" style="text-decoration: none;"><i
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
                <h1>Welcome</h1>
                <p>Review and Provide your product approval</p>
            </header>

            <div class="product-grid">
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="product" id="product-<?php echo $row['id']; ?>">
                        <img src="<?php echo $row['image_path']; ?>" alt="<?php echo $row['product_name']; ?>">
                        <h3>
                            <?php echo $row['product_name']; ?>
                        </h3>
                        <p>RM
                            <?php echo $row['price']; ?>
                        </p>
                        <div class="button-group">
                            <button class="btn-approve"
                                onclick="updateProductStatus(<?php echo $row['id']; ?>, 1)">Approve</button>
                            <button class="btn-reject"
                                onclick="updateProductStatus(<?php echo $row['id']; ?>, 2)">Reject</button>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
            <script>
                function updateProductStatus(productId, status) {
                    fetch('updateProductStatus.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `id=${productId}&status=${status}`
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                // Remove the product grid
                                document.getElementById(`product-${productId}`).style.display = 'none';
                            } else {
                                alert('An error occurred');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                        });
                }
            </script>


        </main>
    </div>
    <script src="dashboard.js"></script> <!-- Link to your JavaScript file -->
</body>

</html>