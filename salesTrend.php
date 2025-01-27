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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns@2.0.0/dist/chartjs-adapter-date-fns.bundle.min.js"></script>
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="salesTrend.css"> <!-- Link to your CSS file -->
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
                        <li><a href="adminDashboard.php" style="text-decoration: none;"><i class="icon-dashboard"></i>Product Approval</a></li>
                        <li><a href="recentTransaction.php" style="text-decoration: none;"><i class="icon-insights"></i>Recent Orders</a></li>
                        <li class="active"><a href="salesTrend.php" style="text-decoration: none;"><i class="icon-insights"></i>Sales Trend</a></li>
                    </ul>
                </nav>
            </div>
            <div class="sidebar-bottom">
                <a href="logout.php" class="logout"><i class="icon-logout"></i>Log out</a>
            </div>
        </aside>
        <main class="main-content">
            <header class="dashboard-header">
                <h1>Sales Trend</h1>
            </header>

            <div>
                <button onclick="updateChart('week')">This Week</button>
                <button onclick="updateChart('month')">This Month</button>
                <button onclick="updateChart('year')">This Year</button>
            </div>

            <canvas id="salesChart" width="400" height="170"></canvas>

            <script>
                let salesChart;

                function fetchSalesData(timePeriod) {
                    return fetch(`get_sales_data.php?time_period=${timePeriod}`)
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('Network response was not ok ' + response.statusText);
                            }
                            return response.json();
                        });
                }

                function updateChart(timePeriod) {
                    fetchSalesData(timePeriod)
                        .then(data => {
                            const productNames = data.map(entry => entry.product_name);
                            const quantities = data.map(entry => entry.total_quantity);
                            drawBarGraph(productNames, quantities);
                        })
                        .catch(error => console.error('Error fetching sales data:', error));
                }

                document.addEventListener('DOMContentLoaded', function () {
                    updateChart('month'); // Default to monthly view
                });

                function drawBarGraph(productNames, quantities) {
                    const ctx = document.getElementById('salesChart').getContext('2d');

                    if (salesChart) {
                        salesChart.destroy();
                    }

                    salesChart = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: productNames,
                            datasets: [{
                                label: 'Total Quantity Purchased',
                                data: quantities,
                                backgroundColor: 'rgba(75, 192, 192, 0.5)',
                                borderColor: 'rgba(75, 192, 192, 1)',
                                borderWidth: 2
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            interaction: {
                                mode: 'index',
                                intersect: false
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    title: {
                                        display: true,
                                        text: 'Total Quantity Purchased',
                                        font: {
                                            size: 16
                                        }
                                    },
                                    ticks: {
                                        font: {
                                            size: 14
                                        }
                                    }
                                },
                                x: {
                                    title: {
                                        display: true,
                                        text: 'Product Name',
                                        font: {
                                            size: 16
                                        }
                                    },
                                    ticks: {
                                        font: {
                                            size: 14
                                        }
                                    }
                                }
                            },
                            plugins: {
                                title: {
                                    display: true,
                                    text: 'Sales Trend of Products',
                                    font: {
                                        size: 20
                                    }
                                },
                                legend: {
                                    display: false
                                },
                                tooltip: {
                                    mode: 'index',
                                    intersect: false,
                                    callbacks: {
                                        label: function (context) {
                                            return `${context.dataset.label}: ${context.raw}`;
                                        }
                                    }
                                }
                            },
                            barPercentage: 0.9,
                            categoryPercentage: 0.8
                        }
                    });
                }
            </script>

        </main>
    </div>
    <script src="dashboard.js"></script> <!-- Link to your JavaScript file -->
</body>

</html>
