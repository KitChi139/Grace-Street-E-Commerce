<?php
include('../components/connect.php');

$sql = "SELECT * FROM orders";
$result = mysqli_query($con, $sql);

$sqls = "SELECT COUNT(*) as total_rows FROM orders WHERE Order_Status = 0 AND Order_Status != 'Received'";
$results = mysqli_query($con, $sqls);
$row = mysqli_fetch_assoc($results);
$totalRows = $row['total_rows'];

if(isset($_POST['approve'])) {
    // Get the order ID from the submitted form
    $orderId = $_POST['appid'];
    
    // Update the order status to 1 (approved)
    $updateSql = "UPDATE orders SET Order_Status = 1 WHERE ID = $orderId";
    $updateQuery = mysqli_query($con, $updateSql);
    
    // Check if the update was successful
    if($updateQuery) {
        // Show alert message
        echo "<script>alert('Order Approved');</script>";
        // Redirect after a delay
        echo "<script>setTimeout(function(){ window.location.href = '{$_SERVER['PHP_SELF']}'; }, 1000);</script>";
        exit();
    } else {
        // Handle the error
        echo "Error updating order status: " . mysqli_error($con);
    }
}
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
<style>
    .modal-view{
       height: 100vh;
       width: 100%;
    }
    .approvebtn{
        color: white;
        background-color: green;
        text-decoration: none;
        padding: 7px 15px;
        border-radius: 10px;
    }
    .pen_div{
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 5px;
        width: 120px;
        border-radius: 10px;
        background-color: orange;
        padding: 5px 5px;
        margin: -20px 0 10px 10px;
        position: relative;
        cursor: pointer;
        box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1); 
    }
    .pendings{
        text-decoration: none;
        color: white;
        font-size: 15px;
    }
    .orange_count{
        top: -10px;
        box-shadow: 1px 1px 5px 0px rgba(0,0,0,0.2);
        right: -10px;
        width: 25px;
        height: 25px;
        border-radius: 100px;
        background-color: red;
        position: absolute;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 13px;
    }
    .model-back .pendings_table table,
.model-back .pendings_table th,
.model-back .pendings_table td {
    font-size: 12px;
}
.model-back {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: white;
            -webkit-box-shadow: 1px 3px 14px -8.5px #000000;
            -moz-box-shadow: 1px 3px 14px -8.5px #000000;
            box-shadow: 1px 3px 14px -8.5px #000000;
            width: 80%;
            max-width: 900px;
            margin: auto;
            padding: 20px;
            border-radius: 10px;
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow-y: auto;
            border: 2px solid black;

        }

        /* Style for the table */
        .custom-table {
            width: 100%;
            border-collapse: collapse;
        }

        .custom-table th,
        .custom-table td {
            padding: 8px;
            text-align: left;
            
        }
        .custom-table table{
            border-collapse: collapse;
            background-color: white;
        }
        .custom-table th {
            border-bottom: 1px solid rgba(0,0,0,0.5);
        }

        /* Style for the scrollbar */
        .table-container {
            max-height: 400px; /* Adjust as needed */
            overflow-y: auto;
        }
        .approve-btn{
            cursor: pointer;
            background-color: green;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 10px;
        }
        .closebtn{
           display: flex;
           justify-content: end;
           align-items: center;
           margin: 1px 10px 10px 5px;
        }
        .closebtn i{
            font-size: 25px;
            cursor: pointer;
        }
        .printbtn{
            text-decoration: none;
            background-color: darkgreen;
            padding: 5px 18px;
            border-radius: 10px;
            color: white;
        }
        .order-view td{
            font-size: 11px;
        }
</style>
<body>
<?php include '../admin/dashboard_header.php'; ?>
   <section class="main_orders_container">
        <div class="main_container">
            <h1 class="main_title">Orders</h1>
            <div class="main_products_box">
                <div class="main_products_table">
                    <table>
                        <div class="pen_div" id="pen_div_trigger">
                            <div class="orange_count"><?php echo $totalRows ?></div>
                            <i class="fa-solid fa-spinner" style="color: #ffffff;"></i>
                            <a class="pendings">Pendings</a>    
                        </div>
                        <thead>
                            <tr>
                                <th>Place on</th>
                                <th>Customer Name</th>
                                <th>Address</th>
                                <th>Number</th>
                                <th>Total Products</th>
                                <th>Total Price</th>
                                <th>Payment Method</th>
                                <th>Payment Status</th>
                                <th>Invoice</th>
                            </tr>
                        </thead>
                        <tbody>
                        <tbody>
                        <?php if(mysqli_num_rows($result) > 0): ?>
                            <?php while($row = mysqli_fetch_assoc($result)): ?>
                            <tr class="order-view" >
                                <td><?php echo $row['Placed_on']; ?></td>
                                <td><?php echo $row['Name']; ?></td>
                                <td><?php echo $row['Address']; ?></td>
                                <td><?php echo $row['Number']; ?></td>
                                <td><?php echo $row['Total_Products']; ?></td>
                                <td><?php echo $row['Total_Price']; ?></td>
                                <td><?php echo $row['Method']; ?></td>
                                <td style="color: <?php
                                    if ($row['Order_Status'] == 0) {
                                        echo 'orange';
                                    } elseif ($row['Order_Status'] == 1) {
                                        echo 'green';
                                    } elseif ($row['Order_Status'] == 'Received') {
                                        echo 'blue';
                                    }
                                ?>">
                                    <?php 
                                    if ($row['Order_Status'] == 0) {
                                        echo 'Pending';
                                    } elseif ($row['Order_Status'] == 1) {
                                        echo 'Approved';
                                    } elseif ($row['Order_Status'] == 'Received') {
                                        echo 'Received';
                                    }
                                    ?>
                                </td>
                                <td><a class="printbtn" type="submit" href="generate_invoice.php?id=<?php echo $row['ID']; ?>&name=<?php echo urlencode($row['Name']); ?>&address=<?php echo urlencode($row['Address']); ?>&number=<?php echo urlencode($row['Number']); ?>&total_products=<?php echo urlencode($row['Total_Products']); ?>&total_price=<?php echo urlencode($row['Total_Price']); ?>&method=<?php echo urlencode($row['Method']); ?>">Print</a></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>


        <div class="modal-view">
        <div class="model-back" id="myModel1" style="display: none;">
            <div class="pendings_table">
                <div class="modal-content">
                    <div class="table-container">
                        <div class="closebtn">
                            <i class="fa-solid fa-circle-xmark" id="closeTab"></i>
                        </div>
                        <div class="custom-table">
                            <table class="">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Place on</th>
                                        <th>Customer Name</th>
                                        <th>Address</th>
                                        <th>Number</th>
                                        <th>Total Products</th>
                                        <th>Total Price</th>
                                        <th>Payment Method</th>
                                        <th>Payment Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $sql = "SELECT * FROM orders WHERE Order_Status = 0 AND Order_Status != 'Received'";
                                    $que = mysqli_query($con, $sql);
                                    $cnt = 1;
                                    // Check if there are pending orders
                                    if(mysqli_num_rows($que) > 0) {
                                        while ($result = mysqli_fetch_assoc($que)) {
                                    ?>
                                            <tr>
                                                <td><?php echo $cnt; ?></td>
                                                <td><?php echo $result['Placed_on']; ?></td>
                                                <td><?php echo $result['Name']; ?></td>
                                                <td><?php echo $result['Address']; ?></td>
                                                <td><?php echo $result['Number']; ?></td>
                                                <td><?php echo $result['Total_Products']; ?></td>
                                                <td><?php echo $result['Total_Price']; ?></td>
                                                <td><?php echo $result['Method']; ?></td>
                                                <td>
                                                    <?php
                                                    if ($result['Order_Status'] == 0) {
                                                        echo "Pending";
                                                    } else {
                                                        echo "Approved";
                                                    }
                                                    ?>
                                                </td>
                                                <td>
                                                    <?php if ($result['Order_Status'] == 0) { ?>
                                                        <form method="POST">
                                                            <input type="hidden" name="appid" value="<?php echo $result['ID']; ?>">
                                                            <input type="submit" class="approve-btn" name="approve" value="Approve">
                                                        </form>
                                                    <?php } ?>
                                                </td>
                                            </tr>
                                    <?php
                                            $cnt++;
                                        }
                                    } else {
                                        // Display message if there are no pending orders
                                            echo "<tr><td colspan='10' style='text-align: center; font-size: 15px;'>No pendings</td></tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
	    </div>
        </div>
   </section>
   <script>
       document.getElementById('pen_div_trigger').addEventListener('click', function() {
           document.getElementById('myModel1').style.display = 'block'; // Show modal
       });
       document.getElementById('closeTab').addEventListener('click', function(){
            document.getElementById('myModel1').style.display = 'none';
       })
   </script>
</body>
</html>
