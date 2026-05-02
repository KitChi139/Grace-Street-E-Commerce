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
        font-size: 14px;
        font-family: 'Jost', sans-serif;
    }
    .orders-table th, .orders-table td {
        padding: 15px 20px;
        text-align: left;
        border-bottom: 0.5px solid #E8DED2;
    }
    .orders-table th {
        font-size: 0.72rem;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        color: #A09486;
        font-weight: 400;
        background: transparent;
    }
    .orders-table tr:hover td {
        background-color: rgba(232,222,210,0.2);
    }
    .orders-table tr.order-row {
        cursor: pointer;
    }
    .orders-table .received-order, 
    .orders-table .cancel-btn, 
    .orders-table .remove-order {
        padding: 7px 14px;
        border: none;
        cursor: pointer;
        font-size: 0.7rem;
        font-family: 'Jost', sans-serif;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        margin-right: 5px;
        transition: background-color 0.25s, color 0.25s;
    }
    .orders-table .received-order {
        background-color: transparent;
        color: #2C2825;
        border: 0.5px solid #2C2825;
    }
    .orders-table .received-order:hover {
        background-color: #2C2825;
        color: #F7F3EE;
    }
    .orders-table .cancel-btn {
        background-color: transparent;
        color: #B85C38;
        border: 0.5px solid #B85C38;
    }
    .orders-table .cancel-btn:hover {
        background-color: #B85C38;
        color: #F7F3EE;
    }
    .orders-table .remove-order {
        background-color: transparent;
        color: #A09486;
        border: 0.5px solid #A09486;
    }
    .orders-table .remove-order:hover {
        background-color: #A09486;
        color: #F7F3EE;
    }
    .orders-table .print-link {
        display: inline-block;
        padding: 7px 14px;
        background-color: #2C2825;
        color: #F7F3EE;
        text-decoration: none;
        font-family: 'Jost', sans-serif;
        font-size: 0.7rem;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        transition: background-color 0.25s;
    }
    .orders-table .print-link:hover {
        background-color: #8B6F56;
    }
    .no-orders {
        text-align: center;
        padding: 40px;
        font-size: 0.9rem;
        color: #A09486;
        font-family: 'Jost', sans-serif;
    }
    .no-results {
        text-align: center;
        padding: 20px;
        font-size: 0.9rem;
        color: #A09486;
        font-family: 'Jost', sans-serif;
    }
    .search-container {
        margin: 20px 0;
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 0;
    }
    .search-container input[type="text"] {
        padding: 12px 20px;
        width: 400px;
        border: 0.5px solid #E8DED2;
        background: rgba(232,222,210,0.3);
        font-family: 'Jost', sans-serif;
        font-size: 0.85rem;
        color: #2C2825;
        outline: none;
        border-radius: 0;
    }
    .search-container button {
        padding: 12px 20px;
        border: none;
        background-color: #2C2825;
        color: #F7F3EE;
        cursor: pointer;
        font-family: 'Jost', sans-serif;
        font-size: 0.75rem;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        transition: background-color 0.25s;
        border-radius: 0;
    }
    .search-container button:hover {
        background-color: #8B6F56;
    }
    #viewAllBtn {
        margin-left: 8px !important;
        border-radius: 0 !important;
    }
    </style>
</head>
<body>
    <?php include 'additional/header.php'; ?>
    <?php include 'chat.php'; ?>
    <section>
        <div class="invoice_container">
            <div class="invoice_content" style="background: rgba(247,243,238,0.85); border: 0.5px solid #E8DED2; border-radius: 12px; box-shadow: 0 8px 24px rgba(44,40,37,0.08); padding: 2rem; max-width: 1100px; margin: 0 auto;">
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

                    $sql = "SELECT 
                            mo.mainOrderID AS ID,
                            mo.created_at AS Placed_on,
                            mo.total_price AS Total_Price,
                            mo.status AS Order_Status,
                            u.first_name,
                            u.last_name,
                            u.username,
                            u.address AS Address,
                            u.contact_number AS Number
                        FROM main_order mo
                        JOIN grace_user u ON mo.userID = u.userID
                        WHERE mo.userID = ?
                        ORDER BY mo.created_at DESC";
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
                            // Construct the full name for the invoice
                            $fullName = trim($row['first_name'] . ' ' . $row['last_name']);
                            if (empty($fullName)) {
                                $fullName = $row['username'];
                            }
                            $statusDisplay = '';
                            if ($row['Order_Status'] == 'Pending') {
                                $statusDisplay = '<span style="color: orange;">Pending</span>';
                            } else if ($row['Order_Status'] == 'Shipped' || $row['Order_Status'] == 'Paid') {
                                $statusDisplay = '<span style="color: green;">Paid</span>';
                            } else if ($row['Order_Status'] == 'Ready') {
                                $statusDisplay = '<span style="color: blue;">Ready for Pickup</span>';
                            } else if ($row['Order_Status'] == 'In Transit') {
                                $statusDisplay = '<span style="color: #8B6F56;">On Route</span>';
                            } else if ($row['Order_Status'] == 'Completed') {
                                $statusDisplay = '<span style="color: green;">Delivered</span>';
                            } else if ($row['Order_Status'] == 'Canceled') {
                                $statusDisplay = '<span style="color: red;">Canceled</span>';
                            } else {
                                $statusDisplay = '<span>' . $row['Order_Status'] . '</span>';
                            }
                            ?>
                            <tr class="order-row" data-id="<?php echo $row['ID']; ?>" title="Open shipment tracking">
                                <td><a href="logistics_tracking.php?id=<?php echo (int) $row['ID']; ?>" class="track-order-link" style="color:inherit;text-decoration:none;">#<?php echo $row['ID']; ?></a></td>
                                <td><?php echo $row['Placed_on']; ?></td>
                                <td>PHP <?php echo number_format($row['Total_Price'], 2); ?></td>
                                <td class="status-cell"><?php echo $statusDisplay; ?></td>
                                <td class="actions-cell">
                                    <?php if ($row['Order_Status'] != 'Pending'): ?>
                                        <?php if ($row['Order_Status'] != 'Cancelled' && $row['Order_Status'] != 'Canceled'): ?>
                                            <?php if ($row['Order_Status'] != 'Ready'): ?>
                                                <button class="received-order" data-id="<?php echo $row['ID']; ?>">Received</button>
                                            <?php endif; ?>
                                            <a class="print-link" href="generate_invoice.php?id=<?php echo $row['ID']; ?>&name=<?php echo urlencode($fullName); ?>&address=<?php echo urlencode($row['Address']); ?>&number=<?php echo urlencode($row['Number']); ?>&total_price=<?php echo urlencode((string)$row['Total_Price']); ?>">Invoice</a>
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
            $('.order-row').on('click', function(e) {
                if ($(e.target).closest('button, a').length) {
                    return;
                }
                var id = $(this).data('id');
                window.location.href = 'logistics_tracking.php?id=' + id;
            });

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
