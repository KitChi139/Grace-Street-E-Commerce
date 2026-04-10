<?php

include('../components/connect.php');
$sql = "SELECT COUNT(*) AS total_products FROM product_list"; // Query to get the total count of records
$result = mysqli_query($con, $sql);

$row = mysqli_fetch_assoc($result);
$totalProducts = $row['total_products']; // Total number of products


$userSql = "SELECT COUNT(*) AS total_users FROM grace_user";
$userResult = mysqli_query($con, $userSql);
$userRow = mysqli_fetch_assoc($userResult);
$totalUsers = $userRow['total_users'];

$totalSql = "SELECT SUM(Total_Price) AS total_price FROM orders WHERE Order_Status = 0 AND Order_Status != 'Received'";
$totalResult = mysqli_query($con, $totalSql);
$totalRow = mysqli_fetch_assoc($totalResult);
$totalprice = $totalRow['total_price'];

$approveSql = "SELECT SUM(Total_Price) AS total_approve FROM orders WHERE Order_Status IN (1, 'Received')";
$approveResult = mysqli_query($con, $approveSql);
$approveRow = mysqli_fetch_assoc($approveResult);
$approveUsers = $approveRow['total_approve'];

$placedSql = "SELECT COUNT(*) AS total_placed FROM orders WHERE Order_Status = 0 AND Order_Status != 'Received'";
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
<?php include '../admin/dashboard_header.php'; ?>
   <section class="main_dash_container">
        <div class="main_container">
            <h1 class="main_title">Dashboard</h1>
            <div class="main_dash_analytics">
                <div class="main_dash_top">
                    <div class="main_dash_box wide">
                        <div class="main_dash_info">
                            <h3>Total Pendings</h3>
                            <h1 class="main_large">₱ <?php echo number_format(($totalprice > 0) ? $totalprice : 0, 0, '.', ','); ?>.00</h1>
                        </div>
                    </div>
                    <div class="main_dash_box wide">
                        <div class="main_dash_info">
                            <h3>Total Sales</h3>
                            <h1 class="main_large">₱ <?php echo number_format(($approveUsers > 0) ? $approveUsers : 0, 0, '.', ','); ?>.00</h1>

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
        </div>
   </section>
</body>
</html>