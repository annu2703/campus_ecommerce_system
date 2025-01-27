<?php
session_start();

// Check if the user is logged in as a seller
if (!isset($_SESSION['username']) || $_SESSION['user_role'] !== 'buyer') {
    // Redirect to login page or handle unauthorized access
    header("Location: index.php");
    exit();
}

// Fetch the seller's username from the session data
$sellerUsername = $_SESSION['username'];

$matricNum = $_SESSION['matrics_number'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buyer Dashboard</title>
    <link rel="stylesheet" href="buyerDashboard.css"> <!-- Link to your CSS file -->
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
                        <li class="active"><a href="buyerDashboard.php" style="text-decoration: none;"><i
                                    class="icon-dashboard"></i>Product Listing</a></li>
                        <li><a href="viewMyCart.php" style="text-decoration: none;"><i class="icon-insights"></i>View My
                                Cart</a>
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
                <div class="welcome-container">
                    <h1>Welcome</h1>
                    <button class="header-button" onclick="window.location.href='addNewProduct.php'">+ Create
                        Listing</button>
                </div>
            </header>
            <div class="search-container">
                <input type="text" id="search-bar" placeholder="Search products..." onkeyup="filterProducts()">
            </div>

            <div class="banner">
                <img src="banner.png" alt="Banner Image">
            </div>

            <!-- Modal HTML -->
            <div id="quantityModal" class="modal" style="display: none;">
                <div class="modal-content">
                    <span class="close">&times;</span>
                    <h2>Enter Quantity</h2>
                    <input type="number" id="quantityInput" placeholder="Enter quantity..." min="1" step="1"
                        max="<?php echo htmlspecialchars($quantity); ?>">
                    <button id="modalButton" onclick="confirmAddToCart()">Add to Cart</button>
                    <!-- Hidden input field to store the product ID -->
                    <input type="hidden" id="productId">
                </div>
            </div>


            <div id="product-grid" class="product-grid">
                <!-- Dynamically populated with PHP -->
                <?php
                require_once 'config.php';
                // Fetch approved products from the database
                $sql = "SELECT id, product_name, price, quantity, image_path, whatsappURL FROM product WHERE approved = 1";
                $result = $link->query($sql);

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo '<div class="product-card">';
                        echo '<img src="' . htmlspecialchars($row['image_path']) . '" alt="' . htmlspecialchars($row['product_name']) . '">';
                        echo '<h3>' . htmlspecialchars($row['product_name']) . '</h3>';
                        echo '<p>RM' . htmlspecialchars($row['price']) . '</p>';
                        echo '<p>Quantity: ' . htmlspecialchars($row['quantity']) . '</p>';
                        echo '<a href="' . htmlspecialchars($row['whatsappURL']) . '" target="_blank"><img class="whatsapp-icon" src="whatsapp-icon.png" alt="WhatsApp"></a>';
                        // Pass the product ID to the addToCart function
                        echo '<button onclick="openQuantityModal(' . $row['id'] . ')">Add to Cart</button>';
                        echo '</div>';
                    }
                } else {
                    echo 'No approved products found.';
                }
                ?>
            </div>
            <script>

                let maxQuantity = 0;

                function openQuantityModal(productId) {
                    // Open the modal
                    document.getElementById('quantityModal').style.display = 'block';

                    // Store the product ID in a hidden field within the modal
                    document.getElementById('productId').value = productId;

                    // Fetch the quantity of the selected product
                    fetch('getProductQuantity.php?product_id=' + productId)
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                // Set the max attribute of the quantity input to the quantity of the product
                                document.getElementById('quantityInput').setAttribute('max', data.quantity);
                                maxQuantity = data.quantity; // Update the maxQuantity variable
                            } else {
                                alert(data.message);
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                        });

                    // Add an input event listener to the quantity input field
                    document.getElementById('quantityInput').addEventListener('input', function () {
                        let currentValue = parseInt(this.value);
                        if (currentValue > maxQuantity) {
                            // Clear the input if the entered value exceeds the maximum quantity
                            this.value = '';
                        }
                    });
                }



                function confirmAddToCart() {
                    // Get the selected quantity from the input field
                    var quantity = document.getElementById('quantityInput').value;
                    // Get the product ID stored in the hidden field
                    var productId = document.getElementById('productId').value;

                    fetch('addToCart.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        // Pass both product ID and quantity to the addToCart script
                        body: `product_id=${productId}&quantity=${quantity}`
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                alert('Product added to cart!');
                                // Clear the quantity input field
                                document.getElementById('quantityInput').value = '';
                                // Close the modal after adding the product to the cart
                                document.getElementById('quantityModal').style.display = 'none';
                            } else {
                                alert(data.message);
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                        });
                }

                // Close the modal when the user clicks on the close button
                document.getElementsByClassName('close')[0].onclick = function () {
                    document.getElementById('quantityModal').style.display = 'none';
                };

                // Close the modal when the user clicks anywhere outside of it
                window.onclick = function (event) {
                    if (event.target == document.getElementById('quantityModal')) {
                        document.getElementById('quantityModal').style.display = 'none';
                    }
                };

                // Filter products based on search query
                function filterProducts() {
                    let searchQuery = document.getElementById('search-bar').value.toLowerCase();
                    let productCards = document.querySelectorAll('.product-card');

                    productCards.forEach(card => {
                        let productName = card.querySelector('h3').textContent.toLowerCase();
                        if (productName.includes(searchQuery)) {
                            card.style.display = '';
                        } else {
                            card.style.display = 'none';
                        }
                    });
                }
                // function addToCart(productId) {
                //     fetch('addToCart.php', {
                //         method: 'POST',
                //         headers: {
                //             'Content-Type': 'application/x-www-form-urlencoded',
                //         },
                //         body: `product_id=${productId}`
                //     })
                //         .then(response => response.json())
                //         .then(data => {
                //             if (data.success) {
                //                 alert('Product added to cart!');
                //             } else {
                //                 alert(data.message);
                //             }
                //         })
                //         .catch(error => {
                //             console.error('Error:', error);
                //         });
                // }

                // function filterProducts() {
                //     let searchQuery = document.getElementById('search-bar').value.toLowerCase();
                //     let productCards = document.querySelectorAll('.product-card');

                //     productCards.forEach(card => {
                //         let productName = card.querySelector('h3').textContent.toLowerCase();
                //         if (productName.includes(searchQuery)) {
                //             card.style.display = '';
                //         } else {
                //             card.style.display = 'none';
                //         }
                //     });
                // }

            </script>
        </main>
    </div>
</body>

</html>