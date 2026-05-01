<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grace Street/Orders</title>

    <!-- css connection -->
    <link rel="stylesheet" href="css/style.css">
    <!-- jQuery UI CSS -->
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
   
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://unpkg.com/jspdf@latest/dist/jspdf.umd.min.js"></script>
    <style>
        .orders-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            font-size: 14px;
            background-color: #fff;
        }
        .orders-table th, .orders-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .orders-table th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: #333;
        }
        .orders-table tr:hover {
            background-color: #f5f5f5;
        }
        .orders-table .status-cell {
            font-weight: 500;
        }
        .orders-table .actions-cell {
            white-space: nowrap;
        }
        .orders-table .received-order, .orders-table .cancel-btn, .orders-table .remove-order {
            padding: 8px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 13px;
            margin-right: 5px;
        }
        .orders-table .received-order {
            background-color: #28a745;
            color: white;
        }
        .orders-table .cancel-btn {
            background-color: #dc3545;
            color: white;
        }
        .orders-table .remove-order {
            background-color: #6c757d;
            color: white;
        }
        .orders-table .print-link {
            display: inline-block;
            padding: 8px 15px;
            background-color: black;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-size: 13px;
        }
        .orders-table .print-link:hover {
            background-color: #333;
        }
        .no-orders {
            text-align: center;
            padding: 40px;
            font-size: 16px;
            color: #666;
        }
        .no-results {
            text-align: center;
            padding: 20px;
            font-size: 16px;
            color: #666;
            margin-top: 10px;
        }

        .search-container {
            margin: 20px 0;
            display: flex;
            justify-content: center;
        }
        .search-container input[type="text"] {
            padding: 12px 20px;
            width: 400px;
            border: 1px solid #ccc;
            border-radius: 25px 0 0 25px;
            font-size: 16px;
            outline: none;
        }
        .search-container button {
            padding: 12px 25px;
            border: 1px solid #ccc;
            border-left: none;
            border-radius: 0 25px 25px 0;
            background-color: black;
            color: white;
            cursor: pointer;
            font-size: 16px;
        }
        .search-container button:hover {
            background-color: #333;
        }

        @media (max-width: 768px) {
            .orders-table {
                font-size: 12px;
            }
            .orders-table th, .orders-table td {
                padding: 10px 8px;
            }
            .orders-table .received-order, .orders-table .cancel-btn, .orders-table .remove-order, .orders-table .print-link {
                padding: 6px 10px;
                font-size: 11px;
                display: block;
                margin-bottom: 5px;
            }
        }
    </style>
</head>
<body>
    <?php include 'additional/header.php'; ?>
    <?php include 'chat.php'; ?>
    <section>
        <div class="invoice_container">
            <div class="invoice_content">
                <div class="invoice_header">
                    <h1>Placed Orders</h1>
                </div>           
                <div class="search-container">
                    <input type="text" id="searchOrders" placeholder="Search orders by ID, date, or status...">
                    <button type="button" id="searchBtn"><i class="fas fa-search"></i> Search</button>
                    <button type="button" id="viewAllBtn" style="margin-left: 10px; border-radius: 25px; display: none;"><i class="fas fa-list"></i> View All</button>
                </div>

                <?php
                include('./components/connect.php');

                if (isset($_SESSION['user-id'])) {
                    $userId = $_SESSION['user-id'];

                    $sql = "SELECT o.orderID AS ID, o.time_ordered AS Placed_on, u.username AS Name, u.contact_number AS Number, e.email AS Email, u.address AS Address, o.price AS Total_Price, o.status AS Order_Status 
                            FROM orders o 
                            JOIN grace_user u ON o.userID = u.userID 
                            JOIN email e ON u.emailID = e.emailID 
                            WHERE o.userID = ?";
                    $stmt = $con->prepare($sql);
                    $stmt->bind_param("i", $userId);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    if (isset($result) && $result->num_rows > 0) {
                        ?>
                        <table class="orders-table">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Date</th>
                                    <th>Total Price</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                        <?php
                        while ($row = $result->fetch_assoc()) {
                            $statusDisplay = '';
                            if ($row['Order_Status'] == 'Pending') {
                                $statusDisplay = '<span style="color: orange;">Pending</span>';
                            } else if ($row['Order_Status'] == 'Shipped' || $row['Order_Status'] == 'Paid') {
                                $statusDisplay = '<span style="color: green;">Paid</span>';
                            } else if ($row['Order_Status'] == 'Completed') {
                                $statusDisplay = '<span style="color: blue;">Received</span>';
                            } else if ($row['Order_Status'] == 'Canceled') {
                                $statusDisplay = '<span style="color: red;">Canceled</span>';
                            } else {
                                $statusDisplay = '<span>' . $row['Order_Status'] . '</span>';
                            }
                            ?>
                            <tr class="order-row" data-id="<?php echo $row['ID']; ?>">
                                <td>#<?php echo $row['ID']; ?></td>
                                <td><?php echo $row['Placed_on']; ?></td>
                                <td>PHP <?php echo number_format($row['Total_Price'], 2); ?></td>
                                <td class="status-cell"><?php echo $statusDisplay; ?></td>
                                <td class="actions-cell">
                                    <?php if ($row['Order_Status'] != 'Pending'): ?>
                                        <?php if ($row['Order_Status'] != 'Cancelled' && $row['Order_Status'] != 'Canceled'): ?>
                                            <?php if ($row['Order_Status'] != 'Completed'): ?>
                                                <button class="received-order" data-id="<?php echo $row['ID']; ?>">Received</button>
                                            <?php endif; ?>
                                            <a class="print-link" href="generate_invoice.php?id=<?php echo $row['ID']; ?>&name=<?php echo urlencode($row['Name']); ?>&address=<?php echo urlencode($row['Address']); ?>&number=<?php echo urlencode($row['Number']); ?>&total_price=<?php echo urlencode($row['Total_Price']); ?>">Invoice</a>
                                            <button class="remove-order" data-id="<?php echo $row['ID']; ?>">Remove</button>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <?php if ($row['Order_Status'] != 'Canceled'): ?>
                                            <button class="cancel-btn" data-id="<?php echo $row['ID']; ?>">Cancel</button>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php
                        }
                        ?>
                            </tbody>
                        </table>
                        <?php
                    } else {
                        echo "<p class='no-orders'>Your Placed Orders is Empty.</p>";
                    }

                } else {
                    echo '<div style="text-align: center;">
                        <p>Please log in to view your placed orders.</p>
                        <a href="login.php"><button style="cursor: pointer; width: 25vh; border: none; padding: 15px 30px; background-color: #2C2825; color: #F7F3EE; font-family: Jost, sans-serif; font-size: 0.8rem; letter-spacing: 0.1em; text-transform: uppercase; transition: background-color 0.25s;">Login</button></a>
                        </div>';
                }

                ?>
            </div>
        </div>
    </section>
    <?php include 'additional/footer.php'; ?>

    <script>
        $(document).ready(function() {
            function filterOrders() {
                var searchText = $('#searchOrders').val().toLowerCase();
                var hasResults = false;

                $('.order-row').each(function() {
                    var orderId = $(this).data('id').toString().toLowerCase();
                    var orderData = $(this).text().toLowerCase();
                    var matches = orderId.includes(searchText) || orderData.includes(searchText);

                    if (matches) {
                        $(this).show();
                        hasResults = true;
                    } else {
                        $(this).hide();
                    }
                });

                if (!hasResults && searchText !== '') {
                    if ($('.no-results').length === 0) {
                        $('.orders-table').after('<div class="no-results">No orders found matching your search.</div>');
                    }
                } else {
                    $('.no-results').remove();
                }

                if (searchText !== '') {
                    $('#viewAllBtn').show();
                } else {
                    $('#viewAllBtn').hide();
                }
            }

            $('#searchBtn').click(function() {
                filterOrders();
            });

            $('#searchOrders').on('keyup', function(e) {
                if (e.key === 'Enter') {
                    filterOrders();
                }
            });

            $('#viewAllBtn').click(function() {
                $('#searchOrders').val('');
                $('.order-row').show();
                $('.no-results').remove();
                $(this).hide();
            });

            $('.received-order').click(function() {
                var orderId = $(this).data('id');
                $.ajax({
                    type: 'POST',
                    url: 'update_order_status.php',
                    data: { order_id: orderId, new_status: 'Received' },
                    success: function(response) {
                        alert("Item Received by the user successfully");
                        location.reload();
                    },
                    error: function(xhr, status, error) {
                        console.error(error);
                    }
                });
            });

            $('.cancel-btn').click(function() {
                var orderId = $(this).data('id');
                if (confirm("Are you sure you want to cancel this order?")) {
                    $.ajax({
                        type: 'POST',
                        url: 'update_order_status.php',
                        data: { order_id: orderId, new_status: 'Canceled' },
                        success: function(response) {
                            alert("Order canceled successfully");
                            location.reload();
                        },
                        error: function(xhr, status, error) {
                            console.error(error);
                        }
                    });
                }
            });

            $('.remove-order').click(function() {
                var orderId = $(this).data('id');
                if (confirm("Are you sure you want to remove this order?")) {
                    $.ajax({
                        type: 'POST',
                        url: 'delete_order.php',
                        data: { order_id: orderId },
                        success: function(response) {
                            alert("Order removed successfully");
                            location.reload();
                        },
                        error: function(xhr, status, error) {
                            console.error(error);
                        }
                    });
                }
            });

        });
    </script>

</body>
</html>
