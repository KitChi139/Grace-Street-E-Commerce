<?php 

include('../components/connect.php');


$stmt = $con->prepare("SELECT 
                        mo.mainOrderID, 
                        mo.total_price,
                        gu.first_name, 
                        gu.last_name, 
                        mo.status AS main_status,
                        SUM(oi.quantity) AS total_quantity
                    FROM main_order mo
                    JOIN orders o ON mo.mainOrderID = o.mainOrderID
                    JOIN order_items oi ON o.orderID = oi.orderID
                    JOIN grace_user gu ON mo.userID = gu.userID
                    WHERE mo.status = 'In Transit'
                    GROUP BY mo.mainOrderID
                ");
$stmt->execute();
$result = $stmt->get_result();
$orders = [];
while ($row = $result->fetch_assoc()) {
    $orders[] = $row;
}

?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Pending Orders</title>
        <link rel="stylesheet" href="styles/dashboard_header.css">
        <link rel="stylesheet" href="styles/style.css">
    </head>
    <body>
        <?php include 'dashboard_header.php'; ?>  
        <div class="main-container">
             <h1>Pending Orders</h1>
             <table>
                <thead>
                    <tr>
                        <th></th>
                        <th>Order ID</th>
                        <th>Customer Name</th>
                        <th>Total Price</th>
                        <th>Quantity</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                    <tr>
                        <td><input type="checkbox" name="select_order" value="<?php echo $order['mainOrderID']; ?>"></td>
                        <td><?php echo $order['mainOrderID']; ?></td>
                        <td><?php echo $order['first_name'] . ' ' . $order['last_name']; ?></td>
                        <td><?php echo $order['total_price']; ?></td>
                        <td><?php echo $order['total_quantity']; ?></td>
                        <td><?php echo $order['main_status']; ?></td>
                        <td>
                            <button class="view-details" data-order-id="<?php echo $order['mainOrderID']; ?>">
                                View Details
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
             </table>
        </div>
        <div class="modal" id="orderModal">
            <div class="modal-content">
                <span class="close" id="modalClose">&times;</span>
                <h2>Order Details</h2>
                <p>More information about the order will be displayed here.</p>
                <p>Customer: <span id="modalCustomer">John Doe</span></p>
                <p>Status: <span id="modalStatus">Pending</span></p>
                <p>Total: ₱<span id="modalTotal">1000</span></p>
                <hr>
                <h3 id="sellerHeader">Seller Order #12345</h3>
                <div class="order-items">
                    <table>
                        <thead>
                            <tr>
                                <th>Product Name</th>
                                <th>Quantity</th>
                                <th>Size</th>
                                <th>Price</th>
                                <th>Seller</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="modalItems">
                            <tr class="sample-row">
                                <td>Product A</td>
                                <td>2</td>
                                <td>Medium</td>
                                <td>₱500</td>
                                <td>Seller 1</td>
                                <td>Pending</td>
                            </tr>
                            <tr class="sample-row">
                                <td>Product B</td>
                                <td>1</td>
                                <td>Large</td>
                                <td>₱300</td>   
                                <td>Seller 2</td>
                                <td>Pending</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <hr>
            </div>  
        </div>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('orderModal');
    const closeBtn = document.getElementById('modalClose');
    const modalCustomer = document.getElementById('modalCustomer');
    const modalStatus = document.getElementById('modalStatus');
    const modalTotal = document.getElementById('modalTotal');
    const modalItems = document.getElementById('modalItems');

    let currentOrderId = null; // ✅ store active order

    document.querySelectorAll('.view-details').forEach(function(btn) {
        btn.addEventListener('click', function() {

            const orderId = this.dataset.orderId;
            currentOrderId = orderId; // ✅ store it

            modalItems.innerHTML = '';

            // Loading state
            modalItems.innerHTML = `<tr><td colspan="6">Loading...</td></tr>`;

            fetch(`order_details.php?id=${orderId}`)
                .then(res => res.json())
                .then(data => {

                    if (data.error) {
                        modalItems.innerHTML = `<tr><td colspan="6">${data.error}</td></tr>`;
                        return;
                    }

                    // MAIN INFO
                    modalCustomer.textContent = data.main.first_name + ' ' + data.main.last_name;
                    modalStatus.textContent = data.main.main_status;
                    modalTotal.textContent = data.main.total_price;

                    modalItems.innerHTML = '';

                    let allCompleted = true;

                    data.sellers.forEach((seller) => {

                        seller.items.forEach(item => {
                            const row = document.createElement('tr');

                            row.innerHTML = `
                                <td>${item.name}</td>
                                <td>${item.quantity}</td>
                                <td>${item.sizes}</td>
                                <td>₱${seller.price}</td>
                                <td>${seller.seller_first_name} ${seller.seller_last_name}</td>
                                <td>${seller.status}</td>
                            `;

                            modalItems.appendChild(row);
                        });
                    });

                })
                .catch(err => {
                    console.error(err);
                    modalItems.innerHTML = `<tr><td colspan="6">Error loading data</td></tr>`;
                });

            modal.style.display = 'block';
        });
    });

    // ✅ MARK COMPLETE BUTTON
    markCompleteBtn.onclick = function() {

        if (!currentOrderId) return;

        fetch(`mark_complete.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `id=${currentOrderId}`
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert('Order marked as completed!');
                location.reload();
            } else {
                alert('Failed to update order.');
            }
        })
        .catch(err => console.error(err));
    };

    // CLOSE MODAL
    closeBtn.onclick = () => modal.style.display = 'none';

    window.onclick = (e) => {
        if (e.target === modal) {
            modal.style.display = 'none';
        }
    };
});
        </script>
    </body>
</html>