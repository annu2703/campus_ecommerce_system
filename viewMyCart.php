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

$userId = $_SESSION['user_id'];

// Handle delete request
if (isset($_POST['delete_item'])) {
    $productId = $_POST['product_id'];
    $deleteSql = "DELETE FROM cart WHERE user_id = ? AND product_id = ?";
    $deleteStmt = $link->prepare($deleteSql);
    $deleteStmt->bind_param("ii", $userId, $productId);
    $deleteStmt->execute();
    $deleteStmt->close();
}


// Fetch the cart items
$sql = "SELECT p.product_name, c.quantity, p.price, c.product_id 
        FROM cart c 
        JOIN product p ON c.product_id = p.id 
        WHERE c.user_id = ?";

$stmt = $link->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

$cartItems = [];
while ($row = $result->fetch_assoc()) {
    $row['total_price'] = $row['quantity'] * $row['price'];  // Calculate total price
    $cartItems[] = $row;
}

$stmt->close();
$link->close();

// Calculate grand total
$grandTotal = 0;
foreach ($cartItems as $item) {
    $grandTotal += $item['total_price'];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buyer Dashboard</title>
    <link rel="stylesheet" href="viewMyCart.css"> <!-- Link to your CSS file -->
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
                                    class="icon-insights"></i>View My Cart</a>
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

            <div class="table-container">
                <div class="table-header">
                    <div class="header-item"></div> <!-- This will be for the checkbox -->
                    <div class="header-item">PRODUCT NAME</div>
                    <div class="header-item">QUANTITY</div>
                    <div class="header-item">UNIT PRICE</div>
                    <div class="header-item">TOTAL PRICE</div>
                    <div class="header-item">ACTION</div> <!-- Added Action column -->
                </div>
                <form id="cart-form" action="purchase.php" method="post">
                    <?php foreach ($cartItems as $item): ?>
                        <div class="table-row">
                            <!-- Checkbox and other item details -->
                            <div class="row-item checkbox-container">
                                <input type="checkbox" name="selected_items[]"
                                    value="<?php echo htmlspecialchars($item['product_id']); ?>">
                            </div>
                            <div class="row-item product-name"><?php echo htmlspecialchars($item['product_name']); ?></div>
                            <div class="row-item quantity"><?php echo htmlspecialchars($item['quantity']); ?></div>
                            <div class="row-item price">RM <?php echo htmlspecialchars(number_format($item['price'], 2)); ?>
                            </div>
                            <div class="row-item total-price">RM <?php echo number_format($item['total_price'], 2); ?></div>
                            <div class="row-item">
                                <!-- <form action="viewMyCart.php" method="post"
                                    onsubmit="return confirm('Are you sure you want to delete this item?');">
                                    <input type="hidden" name="product_id"
                                        value="<?php echo htmlspecialchars($item['product_id']); ?>">
                                    <button type="submit" name="delete_item" class="delete-button">Delete</button>
                                </form> -->
                                <button type="button" onclick="deleteItem(<?php echo htmlspecialchars($item['product_id']); ?>)"
                                    class="delete-button">Delete</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </form>
            </div>
            <button class="buy-now-button" onclick="buyNow()">Buy Now</button>
        </main>
    </div>

    <!-- Modal HTML -->
    <div id="checkoutModal" class="modal" style="display: none;">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2>Selected Items</h2>
            <div id="selectedItemsContainer"></div>
            <!-- Input fields for meetup location, date, time, and delivery mode -->
            <form id="checkoutForm" action="purchase.php" method="post">
                <div class="form-row">
                    <label for="meetup_location">Meetup Location:</label>
                    <input type="text" id="meetup_location" name="meetup_location" required>
                </div>
                <div class="form-row">
                    <label for="date">Date:</label>
                    <input type="date" id="date" name="date" required>
                </div>
                <div class="form-row">
                    <label for="time">Time:</label>
                    <input type="time" id="time" name="time" required>
                </div>
                <div class="form-row">
                    <label for="delivery_mode">Delivery Mode:</label>
                    <select id="delivery_mode" name="delivery_mode" required>
                        <option value="pickup">Pickup</option>
                        <option value="delivery">Delivery</option>
                    </select>
                </div>
                <div class="form-row center-bottom">
                    <button type="submit">Confirm Purchase</button>
                </div>

                <?php foreach ($cartItems as $item): ?>
                    <input type="hidden" name="selected_items[]"
                        value="<?php echo htmlspecialchars($item['product_id']); ?>">
                    <input type="hidden" name="selected_item_names[]"
                        value="<?php echo htmlspecialchars($item['product_name']); ?>">
                    <input type="hidden" name="selected_item_quantities[]"
                        value="<?php echo htmlspecialchars($item['quantity']); ?>">
                    <input type="hidden" name="selected_item_prices[]"
                        value="<?php echo htmlspecialchars($item['price']); ?>">
                    <input type="hidden" name="selected_item_total_prices[]"
                        value="<?php echo htmlspecialchars($item['total_price']); ?>">
                <?php endforeach; ?>
            </form>
        </div>
    </div>

    <!-- JavaScript for modal functionality -->
    <script>
        function closeModal() {
            document.getElementById('checkoutModal').style.display = 'none';
        }

        function buyNow() {
            var selectedItems = [];
            var checkboxes = document.querySelectorAll('input[type="checkbox"]:checked');
            var grandTotal = 0; // Initialize grand total
            checkboxes.forEach(function (checkbox) {
                var itemRow = checkbox.closest('.table-row');
                var itemName = itemRow.querySelector('.product-name').textContent.trim();
                var itemQuantity = itemRow.querySelector('.quantity').textContent.trim();
                var itemPrice = parseFloat(itemRow.querySelector('.price').textContent.trim().replace('RM ', '')); // Parse price as float
                var itemTotal = itemPrice * itemQuantity; // Calculate total price
                grandTotal += itemTotal; // Add total price to grand total
                selectedItems.push({
                    name: itemName,
                    quantity: itemQuantity,
                    price: itemPrice,
                    total: itemTotal.toFixed(2) // Ensure total has 2 decimal places
                });
            });

            var modalContent = document.getElementById('selectedItemsContainer');
            modalContent.innerHTML = '';
            selectedItems.forEach(function (item) {
                var itemElement = document.createElement('div');
                itemElement.innerHTML =
                    '<div class="row-item">' + item.name + '</div>' +
                    '<div class="row-item">' + item.quantity + '</div>' +
                    '<div class="row-item">RM ' + item.price.toFixed(2) + '</div>' +
                    '<div class="row-item">RM ' + item.total + '</div>';
                modalContent.appendChild(itemElement);
            });

            // Display the grand total in the modal
            var grandTotalElement = document.createElement('div');
            grandTotalElement.innerHTML = `
                <div class="row-item"></div>
                <div class="row-item"></div>
                <div class="row-item">Grand Total:</div>
                <div class="row-item"><b>RM ${grandTotal.toFixed(2)}</b></div>
            `;
            modalContent.appendChild(grandTotalElement);

            document.getElementById('checkoutModal').style.display = 'block';
        }

        function deleteItem(productId) {
            if (confirm('Are you sure you want to delete this item?')) {
                fetch('viewMyCart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `delete_item=1&product_id=${productId}`
                })
                    .then(response => {
                        if (response.ok) {
                            location.reload(); // Reload the page to reflect the changes
                        } else {
                            alert('Failed to delete item.');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                    });
            }
        }

    </script>
</body>

</html>