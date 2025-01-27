<?php
// Include database connection file
require_once "config.php";

session_start();

if (!isset($_SESSION['username']) || $_SESSION['user_role'] !== 'seller') {
    // Redirect to login page or handle unauthorized access
    header("Location: index.php");
    exit();
}

$sellerUsername = $_SESSION['username'];

$matricNum = $_SESSION['matrics_number'];

// Check if a delete request was made
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['product_id'])) {
    $productId = $_GET['product_id'];

    // Prepare SQL query to delete product
    $sql = "DELETE FROM product WHERE id = ?";
    if ($stmt = $link->prepare($sql)) {
        $stmt->bind_param("i", $productId);
        if ($stmt->execute()) {

            // Optionally redirect to the same page to refresh the list
            // header("Location: productListing.php");
            // exit;
        } else {
            echo "<h1>alert('Error deleting product: " . $stmt->error . "');</h1>";
        }
        $stmt->close();
    }
}

// Prepare the SQL statement to fetch products
$sql = "SELECT id, product_name, category, price, quantity, approved, image_path FROM product WHERE seller_name = ? ORDER BY id ASC";

if ($stmt = $link->prepare($sql)) {
    // Bind the seller username to the prepared statement
    $stmt->bind_param("s", $sellerUsername);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    echo "Error: " . $link->error;
}

// Attempt to execute the prepared statement
// $result = $link->query($sql);


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product List</title>
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
                        <li class="active"><a href="productListing.php" style="text-decoration: none;"><i
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
                <h1>Product List</h1>
                <!-- <div class="header-icons">
                    <img src="settings.png" alt="Settings" class="icon-settings">
                </div> -->
            </header>

            <div class="table-container">
                <div class="table-header">
                    <div class="header-item">IMAGE</div>
                    <div class="header-item">PRODUCT NAME</div>
                    <div class="header-item">CATEGORY</div>
                    <div class="header-item">PRICE</div>
                    <div class="header-item">STOCK</div>
                    <div class="header-item">APPROVAL</div>
                    <div class="header-item">OPERATE</div>
                </div>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <div class="table-row">
                            <div class="row-item"><img src="<?php echo htmlspecialchars($row['image_path']); ?>"
                                    alt="<?php echo htmlspecialchars($row['product_name']); ?>"
                                    style="width: 50px; height: 50px;"></div>
                            <div class="row-item">
                                <?php echo htmlspecialchars($row['product_name']); ?>
                            </div>
                            <div class="row-item">
                                <?php echo htmlspecialchars($row['category']); ?>
                            </div>
                            <div class="row-item">
                                <?php echo htmlspecialchars($row['price']); ?>
                            </div>
                            <div class="row-item">
                                <?php echo htmlspecialchars($row['quantity']); ?>
                            </div>
                            <div class="row-item">
                                <?php echo $row['approved'] ? 'Yes' : 'No'; ?>
                            </div>
                            <div class="row-item">
                                <button class="editButton" id="editButton"
                                    data-product-id="<?php echo htmlspecialchars($row['id']); ?>">Edit</button>
                                <button class="btn-danger delete-product-btn"
                                    data-product-id="<?php echo $row['id']; ?>">Delete</button>


                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="table-row">No products found.</div>
                <?php endif; ?>
                <script>
                    document.querySelectorAll('.delete-product-btn').forEach(button => {
                        button.addEventListener('click', function () {
                            const productId = this.getAttribute('data-product-id');
                            if (confirm('Are you sure you want to delete this product?')) {
                                // Append action=delete in the query string
                                window.location.href = `productListing.php?action=delete&product_id=${productId}`;
                            }
                        });
                    });

                </script>

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
</body>

</html>