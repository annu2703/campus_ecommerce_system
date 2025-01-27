<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
// Start the session and include the database connection file
session_start();

if (!isset($_SESSION['username']) || $_SESSION['user_role'] !== 'seller') {
    // Redirect to login page or handle unauthorized access
    header("Location: index.php");
    exit();
}

// Fetch the seller's username from the session data
$sellerUsername = $_SESSION['username'];

$matricNum = $_SESSION['matrics_number'];

require_once "config.php";

$productID = isset($_GET['product_id']) ? $_GET['product_id'] : null;
$productDetails = null;

if ($productID) {

    // Prepare SQL query to fetch product details
    $sql = "SELECT product_name, product_description, category, quantity, price, image_path, product_breakdown, vegan, vegetarian, expiry_date, halal_cert, whatsappURL FROM product WHERE id = ?";
    if ($stmt = $link->prepare($sql)) {
        $stmt->bind_param("i", $productID);
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            if ($result->num_rows == 1) {
                // Get the result as an associative array
                $productDetails = $result->fetch_assoc();
            } else {
                echo "Product not found.";
            }
        } else {
            echo "Error: " . $stmt->error;
        }
        $stmt->close();
    }
    $link->close();

}


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require_once "config.php"; // Database connection file

    // Fetch the data from POST request
    $productId = isset($_POST['productId']) ? $_POST['productId'] : null;
    $businessDesc = isset($_POST['businessDesc']) ? $_POST['businessDesc'] : null;
    $productCategory1 = isset($_POST['productCategory1']) ? $_POST['productCategory1'] : null;
    $quantity = isset($_POST['inventoryQuantity']) ? $_POST['inventoryQuantity'] : null;
    $price = isset($_POST['price']) ? $_POST['price'] : null;
    $product_breakdown = isset($_POST['productCategory2']) ? $_POST['productCategory2'] : null;
    $halal_cert = isset($_POST['halalCert']) ? $_POST['halalCert'] : null;
    $vegan = isset($_POST['vegan']) ? $_POST['vegan'] : null;
    $vegetarian = isset($_POST['vegetarian']) ? $_POST['vegetarian'] : null;
    $expiry_date = isset($_POST['expiryDate']) ? $_POST['expiryDate'] : null;
    $whatsappURL = isset($_POST['whatsappURL']) ? $_POST['whatsappURL'] : null;

    // Start building the SQL query
    $sql_update = "UPDATE product SET ";
    $bind_types = "";
    $bind_params = array();

    // Initialize an array to store the updated column names
    $updated_columns = array();

    // Check each field and add it to the query if it's not null or empty
    if (!empty($businessDesc)) {
        $sql_update .= "product_description=?, ";
        $bind_types .= "s";
        $bind_params[] = &$businessDesc;
        $updated_columns[] = "product_description";
    }
    if (!empty($productCategory1)) {
        $sql_update .= "category=?, ";
        $bind_types .= "s";
        $bind_params[] = &$productCategory1;
        $updated_columns[] = "category";
    }
    if (!empty($quantity)) {
        $sql_update .= "quantity=?, ";
        $bind_types .= "i";
        $bind_params[] = &$quantity;
        $updated_columns[] = "quantity";
    }
    if (!empty($price)) {
        $sql_update .= "price=?, ";
        $bind_types .= "d";
        $bind_params[] = &$price;
        $updated_columns[] = "price";
    }
    if (!empty($product_breakdown)) {
        $sql_update .= "product_breakdown=?, ";
        $bind_types .= "s";
        $bind_params[] = &$product_breakdown;
        $updated_columns[] = "product_breakdown";
    }
    if (!empty($halal_cert)) {
        $sql_update .= "halal_cert=?, ";
        $bind_types .= "s";
        $bind_params[] = &$halal_cert;
        $updated_columns[] = "halal_cert";
    }
    if (!empty($vegan)) {
        $sql_update .= "vegan=?, ";
        $bind_types .= "s";
        $bind_params[] = &$vegan;
        $updated_columns[] = "vegan";
    }
    if (!empty($vegetarian)) {
        $sql_update .= "vegetarian=?, ";
        $bind_types .= "s";
        $bind_params[] = &$vegetarian;
        $updated_columns[] = "vegetarian";
    }
    if (!empty($expiry_date)) {
        $sql_update .= "expiry_date=?, ";
        $bind_types .= "s";
        $bind_params[] = &$expiry_date;
        $updated_columns[] = "expiry_date";
    }
    if (!empty($whatsappURL)) {
        $sql_update .= "whatsappURL=?, ";
        $bind_types .= "s";
        $bind_params[] = &$whatsappURL;
        $updated_columns[] = "whatsappURL";
    }

    // Remove the trailing comma and space from the SQL query
    $sql_update = rtrim($sql_update, ", ");

    // Add the WHERE clause
    $sql_update .= " WHERE id=?";

    // Bind the parameters
    $bind_types .= "i";
    $bind_params[] = &$productId;

    // Prepare the SQL statement
    if ($stmt_update = $link->prepare($sql_update)) {
        // Bind parameters dynamically
        call_user_func_array(array($stmt_update, 'bind_param'), array_merge(array($bind_types), $bind_params));

        // Execute the statement
        if ($stmt_update->execute()) {
            header("Location: productListing.php");
        } else {
            echo "Error: " . $stmt_update->error;
        }

        // Close statement
        $stmt_update->close();
    }

    // Close database connection
    $link->close();
}




?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product</title>
    <link rel="stylesheet" href="addNewProduct.css"> <!-- Link to your CSS file -->
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
                <h1>Edit Product</h1>
                <!-- <div class="header-icons">
                    <img src="settings.png" alt="Settings" class="icon-settings">
                </div> -->
            </header>
            <form action="editProduct.php" method="post" enctype="multipart/form-data">
                <div class="container">

                    <div class="content">
                        <div class="left-column">
                            <div class="description-container">
                                <input type="hidden" name="productId"
                                    value="<?php echo htmlspecialchars($productID); ?>">
                                <h2>Description</h2>
                                <div class="product-name">
                                    <label for="productName">Product Name</label>
                                    <input disabled required type="text" id="productName" name="productName"
                                        value="<?php echo isset($productDetails['product_name']) ? htmlspecialchars($productDetails['product_name']) : ''; ?>">
                                </div>
                                <div class="business-description">
                                    <label for="businessDesc">Product Description</label>
                                    <textarea id="businessDesc"
                                        name="businessDesc"><?php echo isset($productDetails['product_description']) ? htmlspecialchars($productDetails['product_description']) : ''; ?></textarea>
                                </div>
                            </div>

                            <!-- Category Section -->
                            <div class="category-container">
                                <h2>Category</h2>
                                <div class="dropdown">
                                    <label for="productCategory1">Product Category</label>
                                    <select id="productCategory1" name="productCategory1">
                                        <option value="" disabled selected>Choose One</option>
                                        <option value="product" <?php echo (isset($productDetails['category']) && $productDetails['category'] == 'product') ? 'selected' : ''; ?>>Product
                                        </option>
                                        <option value="service" <?php echo (isset($productDetails['category']) && $productDetails['category'] == 'service') ? 'selected' : ''; ?>>Services
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <div id="additionalCategory" style="display: none;">
                                <div class="category-container">
                                    <h2>Product Category</h2>
                                    <div class="dropdown">
                                        <label for="productCategory2">Product Category</label>
                                        <select id="productCategory2" name="productCategory2">
                                            <option value="" disabled selected>Choose One</option>
                                            <option value="health" <?php echo (isset($productDetails['product_breakdown']) && $productDetails['product_breakdown'] == 'health') ? 'selected' : ''; ?>>
                                                Health & Medicine</option>
                                            <option value="apparel" <?php echo (isset($productDetails['product_breakdown']) && $productDetails['product_breakdown'] == 'apparel') ? 'selected' : ''; ?>>
                                                Apparels</option>
                                            <option value="food" <?php echo (isset($productDetails['product_breakdown']) && $productDetails['product_breakdown'] == 'food') ? 'selected' : ''; ?>>
                                                Food & Beverages</option>
                                            <option value="others" <?php echo (isset($productDetails['product_breakdown']) && $productDetails['product_breakdown'] == 'others') ? 'selected' : ''; ?>>
                                                Others</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Additional fields for Health & Medicine and Food & Beverages -->
                                <div id="additionalFields" style="display: none;">
                                    <div class="category-container">
                                        <h2>Additional Information</h2>
                                        <div class="dropdown">
                                            <label for="halalCert">Halal Certification</label>
                                            <select id="halalCert" name="halalCert">
                                                <option value="NA" <?php echo ($productDetails['halal_cert'] == 'null') ? 'selected' : ''; ?>>NA</option>
                                                <option value="Yes" <?php echo ($productDetails['halal_cert'] == 'Yes') ? 'selected' : ''; ?>>Yes</option>
                                                <option value="No" <?php echo ($productDetails['halal_cert'] == 'No') ? 'selected' : ''; ?>>No</option>
                                            </select>
                                        </div>
                                        <div class="dropdown">
                                            <label for="vegan">Vegan</label>
                                            <select id="vegan" name="vegan">
                                                <option value="NA" <?php echo ($productDetails['vegan'] == 'null') ? 'selected' : ''; ?>>NA</option>
                                                <option value="Yes" <?php echo ($productDetails['vegan'] == 'Yes') ? 'selected' : ''; ?>>Yes</option>
                                                <option value="No" <?php echo ($productDetails['vegan'] == 'No') ? 'selected' : ''; ?>>No</option>
                                            </select>
                                        </div>
                                        <div class="dropdown">
                                            <label for="vegetarian">Vegetarian</label>
                                            <select id="vegetarian" name="vegetarian">
                                                <option value="NA" <?php echo ($productDetails['vegetarian'] == 'null') ? 'selected' : ''; ?>>NA</option>
                                                <option value="Yes" <?php echo ($productDetails['vegetarian'] == 'Yes') ? 'selected' : ''; ?>>Yes</option>
                                                <option value="No" <?php echo ($productDetails['vegetarian'] == 'No') ? 'selected' : ''; ?>>No</option>
                                            </select>
                                        </div>
                                        <div class="expiry-date">
                                            <label for="expiryDate">Expiry Date</label>
                                            <input type="date" id="expiryDate" name="expiryDate"
                                                value="<?php echo isset($productDetails['expiry_date']) ? $productDetails['expiry_date'] : ''; ?>">
                                        </div>
                                    </div>
                                </div>
                            </div>


                            <!-- Inventory Section -->
                            <div class="inventory-container" id="inventoryContainer">
                                <h2>Inventory</h2>
                                <div class="product-name">
                                    <label for="productName">Quantity</label>
                                    <input type="number" id="inventoryQuantity" name="inventoryQuantity" min="0"
                                        step="1"
                                        value="<?php echo isset($productDetails['quantity']) ? htmlspecialchars($productDetails['quantity']) : ''; ?>">
                                </div>
                            </div>
                        </div>

                        <div class="right-column">
                            <!-- Product Images Section -->
                            <div class="product-images-container">
                                <h2>Product Image</h2>
                                <div class="image-upload-container">
                                    <div class="upload-box">
                                        <label for="fileUpload" class="custom-file-upload">
                                            <u>Click to upload</u>
                                        </label>
                                        <input required id="fileUpload" name="fileUpload" type="file"
                                            onchange="previewFile()" />
                                        <!-- Image preview element -->
                                        <img id="imagePreview"
                                            src="<?php echo isset($productDetails['image_path']) ? htmlspecialchars($productDetails['image_path']) : '#'; ?>"
                                            alt="Product Image" <?php echo isset($productDetails['image_path']) ? 'style="display: block;"' : ''; ?>>
                                    </div>
                                </div>

                                <script>
                                    function previewFile() {
                                        const file = document.getElementById('fileUpload').files[0];
                                        const reader = new FileReader();

                                        reader.onloadend = function () {
                                            const preview = document.getElementById('imagePreview');
                                            const uploadLabel = document.querySelector('.custom-file-upload');

                                            if (reader.result) {
                                                // File selected, hide label, show image
                                                preview.src = reader.result;
                                                preview.style.display = 'block'; // Show the image
                                                uploadLabel.style.display = 'none'; // Hide the label
                                                document.getElementById('fileUpload').style.visibility = 'hidden'; // Hide file input visually but keep in the document flow
                                            } else {
                                                // No file selected, reset to initial state
                                                preview.style.display = 'none'; // Hide image
                                                uploadLabel.style.display = 'block'; // Show label
                                                document.getElementById('fileUpload').style.visibility = 'visible'; // Show file input
                                            }
                                        };

                                        if (file) {
                                            reader.readAsDataURL(file);
                                        } else {
                                            // If no file is selected, manually trigger the onloadend event handler
                                            reader.onloadend();
                                        }
                                    }
                                </script>

                            </div>

                            <!-- Pricing Section -->
                            <div class="pricing-container">
                                <h2>Pricing</h2>
                                <div class="price-input">
                                    <span class="currency-symbol">RM</span>
                                    <input required type="number" name="price" id="price" min="0"
                                        value="<?php echo isset($productDetails['price']) ? htmlspecialchars($productDetails['price']) : ''; ?>"
                                        step="0.01">
                                </div>
                            </div>

                            <div class="description-container">
                                <h2>WhatsApp URL</h2>
                                <div class="product-name">
                                    <label for="productName">Seller WhatsApp URL</label>
                                    <input required type="text" id="whatsappURL" name="whatsappURL"
                                        value="<?php echo isset($productDetails['whatsappURL']) ? htmlspecialchars($productDetails['whatsappURL']) : ''; ?>">
                                </div>
                            </div>

                            <div class="button-container">
                                <button type="button" class="btn discard" id="discardBtn">Discard</button>
                                <button type="submit" class="btn add-product" id="editBtn">Update
                                    Product</button>
                            </div>
                            <script>
                                document.getElementById('discardBtn').addEventListener('click', function () {
                                    window.location.href = 'sellerDashboard.php'; // Change 'dashboard.php' to the actual path of your dashboard page
                                });

                                document.getElementById('editBtn').addEventListener('click', function () {
                                    // Trigger the form submission when the button is clicked
                                    document.querySelector('form').submit();
                                });

                            </script>
                        </div>
                    </div>
                </div>
            </form>
        </main>
    </div>
    <?php
    // Check if the fetched category is 'product' and show the additional category breakdown
    if (isset($productDetails['category']) && $productDetails['category'] == 'product') {
        echo '<script>document.getElementById("additionalCategory").style.display = "block";</script>';
    }

    if (isset($productDetails['product_breakdown']) && $productDetails['product_breakdown'] == 'health') {
        echo '<script>document.getElementById("additionalFields").style.display = "block";</script>';
    }

    ?>
    <?php if (!empty($login_err)): ?>
        <script>
            // When the window loads, show the alert with the PHP variable
            window.onload = function () {
                alert("<?php echo addslashes($login_err); ?>");
            };
        </script>
    <?php endif; ?>
    <script>

        document.addEventListener('DOMContentLoaded', function () {
            var productCategory1 = document.getElementById('productCategory1');
            var productCategory2 = document.getElementById('productCategory2');
            var additionalCategory = document.getElementById('additionalCategory');
            var additionalFields = document.getElementById('additionalFields');
            var inventoryContainer = document.getElementById('inventoryContainer');

            // Function to toggle the visibility of the additional fields based on selected category
            function toggleFields() {
                if (productCategory1.value === 'product') {
                    additionalCategory.style.display = 'block';
                    inventoryContainer.style.display = 'block'; // Show the inventory container for products
                } else {
                    additionalCategory.style.display = 'none';
                    inventoryContainer.style.display = 'none'; // Hide the inventory container for services
                }

                if (productCategory2.value === 'health' || productCategory2.value === 'food') {
                    additionalFields.style.display = 'block';
                } else {
                    additionalFields.style.display = 'none';
                }
            }

            // Event listener to handle the change event on the first product category dropdown
            productCategory1.addEventListener('change', toggleFields);
            productCategory2.addEventListener('change', toggleFields);

            // Initial toggle call to set the correct fields on page load
            toggleFields();
        });


    </script>


</body>

</html>