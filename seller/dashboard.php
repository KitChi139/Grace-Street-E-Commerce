<?php

include('../components/connect.php');
$sql = "SELECT COUNT(*) AS total_products FROM product"; // Query to get the total count of records
$result = mysqli_query($con, $sql);

$row = mysqli_fetch_assoc($result);
$totalProducts = $row['total_products']; // Total number of products


$userSql = "SELECT COUNT(*) AS total_users FROM grace_user";
$userResult = mysqli_query($con, $userSql);
$userRow = mysqli_fetch_assoc($userResult);
$totalUsers = $userRow['total_users'];

$totalSql = "SELECT SUM(price) AS total_price FROM orders WHERE status = 'Pending'";
$totalResult = mysqli_query($con, $totalSql);
$totalRow = mysqli_fetch_assoc($totalResult);
$totalprice = $totalRow['total_price'];

$approveSql = "SELECT SUM(price) AS total_approve FROM orders WHERE status IN ('Shipped', 'Completed', 'Paid')";
$approveResult = mysqli_query($con, $approveSql);
$approveRow = mysqli_fetch_assoc($approveResult);
$approveUsers = $approveRow['total_approve'];

$placedSql = "SELECT COUNT(*) AS total_placed FROM orders WHERE status = 'Pending'";
$placedResult = mysqli_query($con, $placedSql);
$placedRow = mysqli_fetch_assoc($placedResult);
$placedUsers = $placedRow['total_placed'];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles/index.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <title>Dashboard</title>
</head>
<body>
<?php include '../seller/dashboard_header.php'; ?>
   <section class="main_dash_container">
        <div class="main_container">
            <h1 class="main_title">Seller Dashboard</h1>
            <p class="main_subtitle">Welcome to your dashboard, manage your products and orders efficiently.</p>
            <!-- <div class="main_dash_analytics">
                <div class="main_dash_top">
                    <div class="main_dash_box wide">
                        <div class="main_dash_info">
                            <h3>Total Pendings</h3>
                            <h1 class="main_large">₱ <?php echo number_format(($totalprice > 0) ? $totalprice : 0, 0, '.', ','); ?>.00</h1>
                        </div>
                    </div>
                </div>
                <div class="main_dash_bottom"> 
                    <div class="main_dash_box">
                        <div class="main_dash_info">
                            <h3>Order Placed</h3>
                            <h1 class="">₱ <?php echo ($placedUsers > 0) ? $placedUsers : 0; ?></h1>
                        </div>
                    </div>
                    
                    <div class="main_dash_box">
                        <div class="main_dash_info">
                            <h3>Total Products</h3>
                            <h1><?php echo $totalProducts; ?></h1>
                        </div>
                    </div>
                    <div class="main_dash_box">
                        <div class="main_dash_info">
                            <h3>Total Users</h3>
                            <h1><?php echo $totalUsers; ?></h1>
                        </div>
                    </div>
                </div>
            </div> -->
            <!-- <div class="col-row">
                <div class="col">
                    <div class="box">
                        <h3>Processed Orders</h3>
                        <p>View and Analyze all orders.</p>
                    </div>
                </div>
                <div class="col">
                    <div class="box">
                        <h3>Product Analytics</h3>
                        <p>View and Analyze your product performance.</p>
                    </div>
                </div>
                 <div class="col">
                    <div class="box">
                        <h3>Box 3</h3>
                        <p>Content or stats go here.</p>
                    </div>
                </div> 
            </div> -->
            <div class="col-row">
                <div class="col">
                    <div class="box">
                        <h3>My products</h3>
                        <p>View and manage your products.</p>
                    </div>
                </div>
                <div class="col">
                    <div class="box">
                        <h3>Total sales</h3>
                        <p>View your total sales performance.</p>
                    </div>
                </div>
                <div class="col">
                    <div class="box">
                        <h3>Orders</h3>
                        <p>View and manage your orders.</p>
                    </div>
                </div>
                <div class="col">
                    <div class="box">
                        <h3>Conversion</h3>
                        <p>View your conversion rates.</p>
                    </div>
                </div>
            </div>
            <div class="col-row">
                <!-- Latest-updated products summary -->
                <div class="main_dash_box" style="margin-top:20px;">
                    <h3 style="margin-bottom:12px;">Latest Updated Stock</h3>
                    <?php
                        $latestSql = "SELECT 
                                        p.name, 
                                        MAX(CASE WHEN s.sizes = 'S' THEN i.stock ELSE 0 END) AS stock_s,
                                        MAX(CASE WHEN s.sizes = 'M' THEN i.stock ELSE 0 END) AS stock_m,
                                        MAX(CASE WHEN s.sizes = 'L' THEN i.stock ELSE 0 END) AS stock_l,
                                        MAX(CASE WHEN s.sizes = 'XL' THEN i.stock ELSE 0 END) AS stock_xl,
                                        MAX(CASE WHEN s.sizes = 'XXL' THEN i.stock ELSE 0 END) AS stock_xxl
                                    FROM product p
                                    LEFT JOIN inventory i ON p.proID = i.proID
                                    LEFT JOIN sizes s ON i.sizeID = s.sizeID
                                    GROUP BY p.proID, p.name
                                    ORDER BY p.proID DESC 
                                    LIMIT 5";
                        $latestRes = mysqli_query($con, $latestSql);
                        if($latestRes && mysqli_num_rows($latestRes) > 0):
                    ?>
                        <table style="width:100%; border-collapse:collapse;">
                            <thead>
                                <tr>
                                    <th style="text-align:left; padding:8px; border-bottom:1px solid #eee;">Product</th>
                                    <th style="text-align:left; padding:8px; border-bottom:1px solid #eee;">Stock (S/M/L/XL/XXL)</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php while($p = mysqli_fetch_assoc($latestRes)): 
                                $s = isset($p['stock_s']) ? $p['stock_s'] : 0;
                                $m = isset($p['stock_m']) ? $p['stock_m'] : 0;
                                $l = isset($p['stock_l']) ? $p['stock_l'] : 0;
                                $xl = isset($p['stock_xl']) ? $p['stock_xl'] : 0;
                                $xxl = isset($p['stock_xxl']) ? $p['stock_xxl'] : 0;
                            ?>
                                <tr>
                                    <td style="padding:8px; border-bottom:1px solid #f6f6f6;"><?php echo htmlspecialchars($p['name']); ?></td>
                                    <td style="padding:8px; border-bottom:1px solid #f6f6f6;"><?php echo 'S:'.$s.' / M:'.$m.' / L:'.$l.' / XL:'.$xl.' / XXL:'.$xxl; ?></td>
                                </tr>
                            <?php endwhile; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p style="color:#777;">No recent product updates.</p>
                    <?php endif; ?>
                </div> 
            </div>
            <row class="col-row">
                <div class="col" style="flex:1 1 0;">
                    <div class="main_dash_box">
                        <h3 style="margin-bottom:12px;">Recent Orders</h3>
                        <?php
                            $ordersSql = "SELECT o.orderID AS ID, o.time_ordered AS Placed_on, u.username AS Name, o.price AS Total_Price, o.status AS Order_Status 
                                          FROM orders o 
                                          JOIN grace_user u ON o.userID = u.userID 
                                          ORDER BY o.time_ordered DESC 
                                          LIMIT 5";
                            $ordersRes = mysqli_query($con, $ordersSql);
                            if($ordersRes && mysqli_num_rows($ordersRes) > 0):
                        ?>
                        <table style="width:100%; border-collapse:collapse;">
                            <thead>
                                <tr>
                                    <th style="text-align:left; padding:8px; border-bottom:1px solid #eee;">Order #</th>
                                    <th style="text-align:left; padding:8px; border-bottom:1px solid #eee;">Placed</th>
                                    <th style="text-align:left; padding:8px; border-bottom:1px solid #eee;">Customer</th>
                                    <th style="text-align:left; padding:8px; border-bottom:1px solid #eee;">Total</th>
                                    <th style="text-align:left; padding:8px; border-bottom:1px solid #eee;">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($o = mysqli_fetch_assoc($ordersRes)): ?>
                                    <tr>
                                        <td style="padding:8px; border-bottom:1px solid #f6f6f6;">#<?php echo $o['ID']; ?></td>
                                        <td style="padding:8px; border-bottom:1px solid #f6f6f6;"><?php echo date('Y-m-d', strtotime($o['Placed_on'])); ?></td>
                                        <td style="padding:8px; border-bottom:1px solid #f6f6f6;"><?php echo htmlspecialchars($o['Name']); ?></td>
                                        <td style="padding:8px; border-bottom:1px solid #f6f6f6;">₱ <?php echo number_format($o['Total_Price'], 2); ?></td>
                                        <td style="padding:8px; border-bottom:1px solid #f6f6f6; color:"><?php
                                            if ($o['Order_Status'] == 'Pending') {
                                                echo 'Pending';
                                            } elseif ($o['Order_Status'] == 'Shipped' || $o['Order_Status'] == 'Paid') {
                                                echo 'Approved';
                                            } elseif ($o['Order_Status'] == 'Completed') {
                                                echo 'Received';
                                            } else {
                                                echo htmlspecialchars($o['Order_Status']);
                                            }
                                        ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                        <?php else: ?>
                            <p style="color:#777;">No recent orders.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </row>
            <div class="col-row">
                <div class="col">
                    <div class="box">
                        <h3>Quick Actions</h3>
                        <div class=col-row>
                            <div class="colQuick">
                                <h3>Update Inventory</h3>
                            </div>
                            <div class="colQuick">
                                <h3>Process Orders</h3>
                            </div>
                            <div class="colQuick">
                                <h3>View Analytics</h3>
                            </div>
                            <div class="colQuick">
                                <h3>View Earning</h3>
                            </div>
                    </div>
                </div>
            </div>
        </div>
   </section>
</body>
</html>
