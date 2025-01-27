<?php

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

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Include database connection file
    require_once "config.php";

    // Check if form values are set and assign them to variables
    $productName = isset($_POST['productName']) ? $_POST['productName'] : null;
    $businessDesc = isset($_POST['businessDesc']) ? $_POST['businessDesc'] : null;
    $category = isset($_POST['productCategory1']) ? $_POST['productCategory1'] : null;
    $quantity = isset($_POST['inventoryQuantity']) ? $_POST['inventoryQuantity'] : null;
    $price = isset($_POST['price']) ? $_POST['price'] : null;
    $halalCert = isset($_POST['halalCert']) ? $_POST['halalCert'] : null;
    $vegan = isset($_POST['vegan']) ? $_POST['vegan'] : null;
    $vegetarian = isset($_POST['vegetarian']) ? $_POST['vegetarian'] : null;
    $expiryDate = isset($_POST['expiryDate']) ? $_POST['expiryDate'] : null;
    $productBreakdown = isset($_POST['productCategory2']) ? $_POST['productCategory2'] : null;
    $whatsappURL = isset($_POST['whatsappURL']) ? $_POST['whatsappURL'] : null;

    // Initialize an array to store the file paths
    $filePaths = [];

    // Check if files were uploaded
    if (!empty($_FILES['fileUpload']['name'][0])) {
        // Loop through each uploaded file
        foreach ($_FILES['fileUpload']['name'] as $key => $fileName) {
            $targetDir = "uploads/";
            $targetFilePath = $targetDir . basename($fileName);

            // Check if file was uploaded successfully
            if (move_uploaded_file($_FILES['fileUpload']['tmp_name'][$key], $targetFilePath)) {
                // Add file path to the array
                $filePaths[] = $targetFilePath;
            }
        }
    }

    // Prepare the SQL statement
    $sql = "INSERT INTO product (product_name, product_description, category, quantity, price, halal_cert, vegan, vegetarian, `expiry_date`, product_breakdown, image_path, whatsappURL, seller_name) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    // Prepare the statement
    if ($stmt = $link->prepare($sql)) {
        // Bind the variables to the prepared statement as parameters
        $stmt->bind_param("sssidssssssss", $productName, $businessDesc, $category, $quantity, $price, $halalCert, $vegan, $vegetarian, $expiryDate, $productBreakdown, $imagePath, $whatsappURL, $sellerUsername);

        // Execute the statement
        $imageName = '';
        foreach ($filePaths as $imagePath) {
            $imageName .= basename($imagePath) . ', ';
        }
        $imageName = rtrim($imageName, ', '); // Remove the last comma
        $stmt->execute();
    } else {
        echo "Error: " . $link->error;
    }

    // Close the statement
    $stmt->close();

    // Close the database connection
    $link->close();
}

?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Listing</title>
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
                        <li><a href="productListing.php" style="text-decoration: none;"><i
                                    class="icon-insights"></i>Products Listing</a></li>
                        <li class="active"><a href="addNewProduct.php" style="text-decoration: none;"><i
                                    class="icon-reports"></i>Add Product</a></li>
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
                <h1>Add New Listing</h1>
                <!-- <div class="header-icons">
                    <img src="settings.png" alt="Settings" class="icon-settings">
                </div> -->
            </header>
            <form action="addNewProduct.php" method="post" enctype="multipart/form-data">
                <div class="container">

                    <div class="content">
                        <div class="left-column">
                            <div class="description-container">
                                <h2>Description</h2>
                                <div class="product-name">
                                    <label for="productName">Listing Name</label>
                                    <input required type="text" id="productName" name="productName">
                                </div>
                                <div class="business-description">
                                    <label for="businessDesc">Listing Description</label>
                                    <textarea id="businessDesc" name="businessDesc"></textarea>
                                </div>
                            </div>

                            <!-- Category Section -->
                            <div class="category-container">
                                <h2>Category</h2>
                                <div class="dropdown">
                                    <label for="productCategory1">Choose One</label>
                                    <select id="productCategory1" name="productCategory1">
                                        <option value="" disabled selected>Choose One</option>
                                        <option value="product">Product</option>
                                        <option value="service">Services</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Additional Category Breakdown for Products -->
                            <div id="additionalCategory" style="display: none;">
                                <div class="category-container">
                                    <h2>Product Category</h2>
                                    <div class="dropdown">
                                        <label for="productCategory2">Product Category</label>
                                        <select id="productCategory2" name="productCategory2">
                                            <option value="" disabled selected>Choose One</option>
                                            <option value="health">Health & Medicine</option>
                                            <option value="apparel">Apparels</option>
                                            <option value="food">Food & Beverages</option>
                                            <option value="others">Others</option>
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
                                                <option value="NA">NA</option>
                                                <option value="Yes">Yes</option>
                                                <option value="No">No</option>
                                            </select>
                                        </div>
                                        <div class="dropdown">
                                            <label for="vegan">Vegan</label>
                                            <select id="vegan" name="vegan">
                                                <option value="NA">NA</option>
                                                <option value="Yes">Yes</option>
                                                <option value="No">No</option>
                                            </select>
                                        </div>
                                        <div class="dropdown">
                                            <label for="vegetarian">Vegetarian</label>
                                            <select id="vegetarian" name="vegetarian">
                                                <option value="NA">NA</option>
                                                <option value="Yes">Yes</option>
                                                <option value="No">No</option>
                                            </select>
                                        </div>
                                        <div class="expiry-date">
                                            <label for="expiryDate">Expiry Date</label>
                                            <input type="date" id="expiryDate" name="expiryDate">
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
                                        step="1">
                                </div>
                            </div>
                        </div>

                        <div class="right-column">
                            <!-- Product Images Section -->
                            <div class="product-images-container">
                                <h2>Listing Images</h2>
                                <div class="image-upload-container">
                                    <div class="upload-box">
                                        <label for="fileUpload" class="custom-file-upload">
                                            <u>Click to upload</u>
                                        </label>
                                        <input required id="fileUpload" name="fileUpload[]" type="file" multiple
                                            onchange="previewFiles()" />
                                        <!-- Image preview elements -->
                                        <div id="imagePreviews" class="image-previews"></div>
                                    </div>
                                </div>
                            </div>


                            <script>
                                function previewFiles() {
                                    const files = document.getElementById('fileUpload').files;
                                    const previewContainer = document.getElementById('imagePreviews');
                                    const uploadLabel = document.querySelector('.custom-file-upload');

                                    // Hide the label
                                    uploadLabel.style.display = 'none';

                                    previewContainer.innerHTML = ''; // Clear previous previews

                                    for (let i = 0; i < files.length; i++) {
                                        const file = files[i];
                                        const reader = new FileReader();

                                        reader.onloadend = function () {
                                            const preview = document.createElement('img');
                                            preview.classList.add('preview-image');
                                            preview.src = reader.result;

                                            // Set width and height attributes
                                            preview.width = 200;
                                            preview.height = 200;

                                            previewContainer.appendChild(preview);
                                        };

                                        if (file) {
                                            reader.readAsDataURL(file);
                                        }
                                    }
                                }

                            </script>

                            <!-- <div class="product-images-container">
                                <h2>Product Image</h2>
                                <div class="image-upload-container">
                                    <div class="upload-box">
                                        <label for="fileUpload" class="custom-file-upload">
                                            <u>Click to upload</u>
                                        </label>
                                        <input required id="fileUpload" name="fileUpload" type="file"
                                            onchange="previewFile()" />
                                        <img id="imagePreview" src="#" />
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

                            </div> -->

                            <!-- Pricing Section -->
                            <div class="pricing-container">
                                <h2>Pricing</h2>
                                <div class="price-input">
                                    <span class="currency-symbol">RM</span>
                                    <input required type="number" name="price" id="price" name="price" min="0"
                                        value="180.00" step="0.01">
                                </div>
                            </div>

                            <div class="description-container">
                                <h2>WhatsApp URL</h2>
                                <div class="product-name">
                                    <label for="productName">Seller WhatsApp URL</label>
                                    <input required type="text" id="whatsappURL" name="whatsappURL">
                                </div>
                            </div>

                            <div class="button-container">
                                <button type="button" class="btn discard" id="discardBtn">Discard</button>
                                <button type="submit" class="btn add-product" value="Add Product">Add Listing</button>
                            </div>
                            <script>
                                document.getElementById('discardBtn').addEventListener('click', function () {
                                    window.location.href = 'sellerDashboard.php'; // Change 'dashboard.php' to the actual path of your dashboard page
                                });
                            </script>
                        </div>
                    </div>
                </div>
            </form>
        </main>
    </div>
    <?php if (!empty($login_err)): ?>
        <script>
            // When the window loads, show the alert with the PHP variable
            window.onload = function () {
                alert("<?php echo addslashes($login_err); ?>");
            };
        </script>
    <?php endif; ?>
    <script>
        // Get the select element
        var primaryCategorySelect = document.getElementById('productCategory1');
        // Get the div containing the additional category breakdown
        var additionalCategoryDiv = document.getElementById('additionalCategory');
        var inventoryContainer = document.getElementById('inventoryContainer');

        // Add event listener to detect changes in the select element
        primaryCategorySelect.addEventListener('change', function () {
            // Check if the selected value is 'product'
            if (primaryCategorySelect.value === 'product') {
                // Show the additional category breakdown div
                additionalCategoryDiv.style.display = 'block';
                inventoryContainer.style.display = 'block';
            } else {
                // Hide the additional category breakdown div
                additionalCategoryDiv.style.display = 'none';
                inventoryContainer.style.display = 'none';
            }
        });
    </script>

    <script>
        var productCategorySelect = document.getElementById('productCategory2');
        var additionalFieldsDiv = document.getElementById('additionalFields');

        productCategorySelect.addEventListener('change', function () {
            if (productCategorySelect.value === 'health' || productCategorySelect.value === 'food') {
                additionalFieldsDiv.style.display = 'block';
            } else {
                additionalFieldsDiv.style.display = 'none';
            }
        });
    </script>
</body>

</html>