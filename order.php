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
        span.order-status {
            color: orange;
        }
        span.order-status[data-status="Canceled"]{
            color: red;
        }
        span.order-status[data-status="Approved"] {
            color: green;
        }

        span.order-status[data-status="Received"] {
            color: blue;
        }
        .print-invoice{
            text-decoration: none;
            background-color: black;
            color: white;
            text-align: center;
            padding: 12px 0;
            width: 300px;
            font-size: 14px;
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

                <div class="invoice_order">
                    <?php
                    include('./components/connect.php');
                    
                    if (isset($_SESSION['user-email'])) {
                        $userEmail = $_SESSION['user-email'];
                    
                        $sql = "SELECT * FROM orders WHERE order_email = ?";
                        $stmt = $con->prepare($sql);
                        $stmt->bind_param("s", $userEmail);
                        $stmt->execute();
                        $result = $stmt->get_result();

                        if (isset($result) && $result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                ?>
                                <div class="invoice_item" data-id="<?php echo $row['ID']; ?>">
                                <p style="display: block; margin-bottom: 15px;">Ordered Date: <span><?php echo $row['Placed_on']; ?></span></p>
                                <p style="display: block; margin-bottom: 15px;">Name: <span><?php echo $row['Name']; ?></span></p>
                                <p style="display: block; margin-bottom: 15px;">Number: <span><?php echo $row['Number']; ?></span></p>
                                <p style="display: block; margin-bottom: 15px;">Email: <span><?php echo $row['Email']; ?></span></p>
                                <p style="display: block; margin-bottom: 15px;">Address: <span><?php echo $row['Address']; ?></span></p>
                                <p style="display: block; margin-bottom: 15px;">Orders: <span style="display: block; margin-bottom: 15px;"><?php echo $row['Total_Products']; ?></span></p>
                                <p style="display: block; margin-bottom: 15px;">Payment Method: <span><?php echo $row['Method']; ?></span></p>
                                <p style="display: block; margin-bottom: 15px;">Total Price: PHP <span><?php echo number_format($row['Total_Price'], 2); ?></span></p>
                                <p style="display: block; margin-bottom: 15px;">Order Status: <span class="order-status" data-status="<?php echo $row['Order_Status']; ?>">
                                <?php 
                                    if ($row['Order_Status'] == 0) {
                                        echo '<span style="color: orange;">Pending</span>';
                                    } else if ($row['Order_Status'] == 1) {
                                        echo '<span style="color: green;">Approved</span>';
                                    } else {
                                        echo '<span>' . $row['Order_Status'] . '</span>';
                                    }
                                ?>
                                </span></p>

                                    
                                    <?php if ($row['Order_Status'] != 'Pending'): ?>
                                        <?php if ($row['Order_Status'] != 'Canceled'):?>
                                            <div class="invoice_button">
                                                <?php if ($row['Order_Status'] != 'Received'): ?>
                                                    <button class="received-order" data-id="<?php echo $row['ID']; ?>">Received Order</button>
                                                    <td><a class="print-invoice" type="submit" href="generate_invoice.php?id=<?php echo $row['ID']; ?>&name=<?php echo urlencode($row['Name']); ?>&address=<?php echo urlencode($row['Address']); ?>&number=<?php echo urlencode($row['Number']); ?>&total_products=<?php echo urlencode($row['Total_Products']); ?>&total_price=<?php echo urlencode($row['Total_Price']); ?>&method=<?php echo urlencode($row['Method']); ?>">Print Invoice</a></td>
                                                <?php else: ?>
                                                    
                                                    <td><a style="width: 100%;" class="print-invoice" type="submit" href="generate_invoice.php?id=<?php echo $row['ID']; ?>&name=<?php echo urlencode($row['Name']); ?>&address=<?php echo urlencode($row['Address']); ?>&number=<?php echo urlencode($row['Number']); ?>&total_products=<?php echo urlencode($row['Total_Products']); ?>&total_price=<?php echo urlencode($row['Total_Price']); ?>&method=<?php echo urlencode($row['Method']); ?>">Print Invoice</a></td>

                                                <?php endif; ?>
                                                
                                            </div>
                                        <?php endif; ?>
                                        <div class="invoice_cancel">
                                            <button class="remove-order" data-id="<?php echo $row['ID']; ?>">Remove</button>
                                        </div>
                                    <?php else: ?>
                                        <?php if ($row['Order_Status'] != 'Canceled'):?>
                                            <div class="invoice_cancel">
                                                <button class="cancel-btn" data-id="<?php echo $row['ID'];?>">Cancel Order</button>
                                            </div>
                                        <?php endif; ?>
                                    <?php endif; ?>                                    
                                    
                                </div>
                                <?php
                            }
                        } else {
                            echo "<p>Your Placed Orders is Empty.</p>";
                        }

                    } else {
                        echo '<div style="text-align: center;">
                                <p>Please log in to view your placed orders.</p>
                                <a href="login.php"><button style="cursor: pointer; width: 25vh; border: none; border-radius: 5px; padding: 10px 30px; background-color: black; color: white;">Login</button></a>
                            </div>';
                    }
                    
                    ?>
                </div>
            </div>
        </div>
    </section>
    <?php include 'additional/footer.php'; ?>

    <script>
        $(document).ready(function() {
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
