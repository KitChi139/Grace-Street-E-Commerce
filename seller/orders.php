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
            box-shadow: 1px 3px 14px -8.5px #000000;
            width: 80%;
            max-width: 900px;
            margin: auto;
            padding: 20px;
            border-radius: 10px;
            z-index: 9999;
            display: none;
            flex-direction: column;
            overflow-y: auto;
            border: 1px solid #ddd;
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
            font-size: 13px;
        }
        .order-view td{
            font-size: 14px;
        }
        .main_products_table th {
            font-size: 15px;
            font-weight: 600;
        }

        /* New styles for search, filters and grid view */
        .controls_container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding: 0 10px;
            gap: 15px;
            flex-wrap: wrap;
        }
        .search_box {
            position: relative;
            flex: 1;
            min-width: 200px;
        }
        .search_box input {
            width: 100%;
            padding: 12px 15px 12px 40px;
            border: 1px solid #ddd;
            border-radius: 25px;
            outline: none;
            font-size: 16px;
        }
        .search_box i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #888;
            font-size: 18px;
        }
        .filter_box select {
            padding: 10px 20px;
            border: 1px solid #ddd;
            border-radius: 25px;
            outline: none;
            font-size: 16px;
            cursor: pointer;
            background: white;
        }
        .view_toggle {
            display: flex;
            gap: 10px;
        }
        .view_btn {
            padding: 10px 18px;
            border: 1px solid #ddd;
            border-radius: 5px;
            cursor: pointer;
            background: white;
            color: #333;
            transition: all 0.3s;
            font-size: 16px;
        }
        .view_btn.active {
            background: #333;
            color: white;
            border-color: #333;
        }
        
        /* Grid View Styling */
        .grid_view_container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 25px;
            padding: 10px;
            display: none; /* Hidden by default */
        }
        .order_card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            transition: transform 0.3s, box-shadow 0.3s;
            cursor: pointer;
            border: 1px solid #eee;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        .order_card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }
        .order_card .order_header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 5px;
        }
        .order_card .order_id {
            font-weight: 600;
            color: #333;
            font-size: 15px;
        }
        .order_card .order_date {
            font-size: 13px;
            color: #888;
        }
        .order_card .customer_name {
            font-size: 18px;
            font-weight: 600;
            margin: 5px 0;
            color: #222;
        }
        .order_card .order_status {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 500;
        }
        .order_card .total_price {
            font-size: 22px;
            font-weight: 700;
            color: #2c3e50;
            margin-top: auto;
        }
        
        /* Modal Detail Styles */
        .modal_content {
            display: flex;
            flex-direction: column;
            gap: 20px;
            padding: 10px;
        }
        .modal_header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #eee;
            padding-bottom: 15px;
        }
        .modal_header h2 {
            font-size: 24px;
        }
        .detail_group {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 15px;
            font-size: 16px;
        }
        .detail_label {
            font-weight: 600;
            color: #555;
        }
        .detail_value {
            color: #333;
        }
        .products_list {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 12px;
            margin-top: 10px;
        }
        .products_list h4 {
            margin-bottom: 12px;
            font-size: 18px;
            font-weight: 600;
        }
        .products_list .detail_value {
            line-height: 1.6;
        }
</style>
<body>
<?php include 'dashboard_header.php'; ?>
   <section class="main_orders_container">
        <div class="main_container">
            <h1 class="main_title">Orders</h1>
            
            <div class="controls_container">
                <div class="search_box">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" id="orderSearch" placeholder="Search orders by name, ID, or address...">
                </div>
                <div class="filter_box">
                    <select id="statusFilter">
                        <option value="all">All Statuses</option>
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                        <option value="received">Received</option>
                    </select>
                </div>
                <div class="view_toggle">
                    <button class="view_btn active" id="listViewBtn" title="List View">
                        <i class="fa-solid fa-list"></i>
                    </button>
                    <button class="view_btn" id="gridViewBtn" title="Grid View">
                        <i class="fa-solid fa-table-cells"></i>
                    </button>
                </div>
            </div>

            <div class="main_products_box">
                <div class="main_products_table" id="listView">
                    <table>
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
                        <tbody id="orderTableBody">
                        <?php if(mysqli_num_rows($result) > 0): 
                            mysqli_data_seek($result, 0); // Reset result pointer
                            while($row = mysqli_fetch_assoc($result)): 
                                $status_text = '';
                                $status_class = '';
                                if ($row['Order_Status'] == 0) {
                                    $status_text = 'Pending';
                                    $status_class = 'orange';
                                } elseif ($row['Order_Status'] == 1) {
                                    $status_text = 'Approved';
                                    $status_class = 'green';
                                } elseif ($row['Order_Status'] == 'Received') {
                                    $status_text = 'Received';
                                    $status_class = 'blue';
                                }
                            ?>
                            <tr class="order-view" data-status="<?php echo strtolower($status_text); ?>" 
                                data-name="<?php echo htmlspecialchars($row['Name']); ?>"
                                data-address="<?php echo htmlspecialchars($row['Address']); ?>"
                                data-id="<?php echo $row['ID']; ?>">
                                <td><?php echo $row['Placed_on']; ?></td>
                                <td><?php echo $row['Name']; ?></td>
                                <td><?php echo $row['Address']; ?></td>
                                <td><?php echo $row['Number']; ?></td>
                                <td><?php echo $row['Total_Products']; ?></td>
                                <td><?php echo $row['Total_Price']; ?></td>
                                <td><?php echo $row['Method']; ?></td>
                                <td style="color: <?php echo $status_class; ?>"><?php echo $status_text; ?></td>
                                <td><a class="printbtn" href="generate_invoice.php?id=<?php echo $row['ID']; ?>&name=<?php echo urlencode($row['Name']); ?>&address=<?php echo urlencode($row['Address']); ?>&number=<?php echo urlencode($row['Number']); ?>&total_products=<?php echo urlencode($row['Total_Products']); ?>&total_price=<?php echo urlencode($row['Total_Price']); ?>&method=<?php echo urlencode($row['Method']); ?>">Print</a></td>
                            </tr>
                            <?php endwhile; ?>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="grid_view_container" id="gridView">
                    <?php 
                    if(mysqli_num_rows($result) > 0): 
                        mysqli_data_seek($result, 0); // Reset result pointer
                        while($row = mysqli_fetch_assoc($result)): 
                            $status_text = '';
                            $status_color = '';
                            if ($row['Order_Status'] == 0) {
                                $status_text = 'Pending';
                                $status_color = '#ffa500';
                                $status_bg = '#fff4e6';
                            } elseif ($row['Order_Status'] == 1) {
                                $status_text = 'Approved';
                                $status_color = '#2ecc71';
                                $status_bg = '#e8f8f0';
                            } elseif ($row['Order_Status'] == 'Received') {
                                $status_text = 'Received';
                                $status_color = '#3498db';
                                $status_bg = '#ebf5fb';
                            }
                    ?>
                    <div class="order_card" 
                         onclick="showOrderDetails(<?php echo htmlspecialchars(json_encode($row)); ?>)"
                         data-status="<?php echo strtolower($status_text); ?>"
                         data-name="<?php echo htmlspecialchars($row['Name']); ?>"
                         data-address="<?php echo htmlspecialchars($row['Address']); ?>">
                        <div class="order_header">
                            <span class="order_id">#ORD-<?php echo $row['ID']; ?></span>
                            <span class="order_date"><?php echo $row['Placed_on']; ?></span>
                        </div>
                        <div class="customer_name"><?php echo $row['Name']; ?></div>
                        <div class="order_status" style="color: <?php echo $status_color; ?>; background: <?php echo $status_bg; ?>">
                            <?php echo $status_text; ?>
                        </div>
                        <div class="total_price">₱ <?php echo number_format($row['Total_Price'], 2); ?></div>
                    </div>
                    <?php endwhile; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
   </section>

   <!-- Order Details Modal -->
   <div id="orderDetailModal" class="model-back">
        <div class="closebtn">
            <i class="fa-solid fa-circle-xmark" onclick="closeModal()"></i>
        </div>
        <div class="modal_content" id="modalContent">
            <!-- Content will be injected by JS -->
        </div>
   </div>

   <script>
       const listView = document.getElementById('listView');
       const gridView = document.getElementById('gridView');
       const listViewBtn = document.getElementById('listViewBtn');
       const gridViewBtn = document.getElementById('gridViewBtn');
       const orderSearch = document.getElementById('orderSearch');
       const statusFilter = document.getElementById('statusFilter');
       const orderDetailModal = document.getElementById('orderDetailModal');
       const modalContent = document.getElementById('modalContent');

       // View Toggling
       listViewBtn.addEventListener('click', () => {
           listView.style.display = 'block';
           gridView.style.display = 'none';
           listViewBtn.classList.add('active');
           gridViewBtn.classList.remove('active');
       });

       gridViewBtn.addEventListener('click', () => {
           listView.style.display = 'none';
           gridView.style.display = 'grid';
           gridViewBtn.classList.add('active');
           listViewBtn.classList.remove('active');
       });

       // Search and Filter Logic
        function filterOrders() {
            const searchTerm = orderSearch.value.toLowerCase();
            const filterStatus = statusFilter.value.toLowerCase();
            let listVisibleCount = 0;
            let gridVisibleCount = 0;

            // Filter List View
            const listRows = document.querySelectorAll('#orderTableBody tr');
            listRows.forEach(row => {
                const name = row.getAttribute('data-name').toLowerCase();
                const address = row.getAttribute('data-address').toLowerCase();
                const status = row.getAttribute('data-status').toLowerCase();
                const id = row.getAttribute('data-id').toLowerCase();

                const matchesSearch = name.includes(searchTerm) || address.includes(searchTerm) || id.includes(searchTerm);
                const matchesStatus = filterStatus === 'all' || status === filterStatus;

                if (matchesSearch && matchesStatus) {
                    row.style.display = '';
                    listVisibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            // Filter Grid View
            const gridCards = document.querySelectorAll('.grid_view_container .order_card');
            gridCards.forEach(card => {
                const name = card.getAttribute('data-name').toLowerCase();
                const address = card.getAttribute('data-address').toLowerCase();
                const status = card.getAttribute('data-status').toLowerCase();
                const id = card.querySelector('.order_id').textContent.toLowerCase();

                const matchesSearch = name.includes(searchTerm) || address.includes(searchTerm) || id.includes(searchTerm);
                const matchesStatus = filterStatus === 'all' || status === filterStatus;

                if (matchesSearch && matchesStatus) {
                    card.style.display = 'flex';
                    gridVisibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });

            // Handle empty states (optional: could add a "No results" message div)
            console.log(`Visible orders: List(${listVisibleCount}), Grid(${gridVisibleCount})`);
        }

       orderSearch.addEventListener('input', filterOrders);
       statusFilter.addEventListener('change', filterOrders);

       // Modal Logic
       function showOrderDetails(order) {
           let statusText = '';
           let statusColor = '';
           if (order.Order_Status == 0) {
               statusText = 'Pending';
               statusColor = 'orange';
           } else if (order.Order_Status == 1) {
               statusText = 'Approved';
               statusColor = 'green';
           } else if (order.Order_Status == 'Received') {
               statusText = 'Received';
               statusColor = 'blue';
           }

           modalContent.innerHTML = `
               <div class="modal_header">
                   <h2>Order Details #ORD-${order.ID}</h2>
                   <span class="order_status" style="color: ${statusColor}; font-weight: bold;">${statusText}</span>
               </div>
               <div class="modal_body">
                   <div class="detail_group">
                       <span class="detail_label">Customer Name:</span>
                       <span class="detail_value">${order.Name}</span>
                   </div>
                   <div class="detail_group">
                       <span class="detail_label">Address:</span>
                       <span class="detail_value">${order.Address}</span>
                   </div>
                   <div class="detail_group">
                       <span class="detail_label">Phone Number:</span>
                       <span class="detail_value">${order.Number}</span>
                   </div>
                   <div class="detail_group">
                       <span class="detail_label">Placed On:</span>
                       <span class="detail_value">${order.Placed_on}</span>
                   </div>
                   <div class="detail_group">
                       <span class="detail_label">Payment Method:</span>
                       <span class="detail_value">${order.Method}</span>
                   </div>
                   <div class="detail_group">
                       <span class="detail_label">Total Price:</span>
                       <span class="detail_value" style="font-weight: bold; font-size: 1.2em;">₱ ${parseFloat(order.Total_Price).toLocaleString(undefined, {minimumFractionDigits: 2})}</span>
                   </div>
                   <div class="products_list">
                       <h4>Ordered Products:</h4>
                       <div class="detail_value">${order.Total_Products.replace(/-/g, '<br>•')}</div>
                   </div>
               </div>
               <div style="margin-top: 20px; display: flex; gap: 10px; justify-content: flex-end;">
                   <a href="generate_invoice.php?id=${order.ID}&name=${encodeURIComponent(order.Name)}&address=${encodeURIComponent(order.Address)}&number=${encodeURIComponent(order.Number)}&total_products=${encodeURIComponent(order.Total_Products)}&total_price=${encodeURIComponent(order.Total_Price)}&method=${encodeURIComponent(order.Method)}" class="printbtn">Print Invoice</a>
               </div>
           `;
           orderDetailModal.style.display = 'flex';
       }

       function closeModal() {
           orderDetailModal.style.display = 'none';
       }

       // Close modal when clicking outside
       window.onclick = function(event) {
           if (event.target == orderDetailModal) {
               closeModal();
           }
       }
   </script>
</body>
</html>
