<?php
session_start();
include('../components/connect.php');
if (!isset($_SESSION['user-id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user-id'];
// Pagination variables
$limit = 10; // Number of rows per page
$page = isset($_GET['page']) ? $_GET['page'] : 1;
$start = ($page - 1) * $limit;

// Check if search parameter is provided
$search = isset($_GET['search']) ? $_GET['search'] : '';
try {
    if(isset($_GET['delete_id'])) {
        $delete_id = $_GET['delete_id'];
        $stmt = $con->prepare("DELETE FROM inventory WHERE proID = ?");
        $stmt->bind_param("i", $delete_id);
        $stmt->execute();
        $stmt->close();
        $stmt = $con->prepare("DELETE FROM product WHERE proID = ?");
        $stmt->bind_param("i", $delete_id);
        $stmt->execute();
        header("Location: products.php");
        $stmt->close();
        exit();
    }
} catch (Exception $e) {

}
// Modify the SQL query to include search functionality
$stmt = $con->prepare("SELECT 
                        p.*, 
                        g.*,
                        i.*,
                        gu.*,
                        MAX(CASE WHEN s.sizes = 'S' THEN i.stock ELSE 0 END) AS product_stock_s,
                        MAX(CASE WHEN s.sizes = 'M' THEN i.stock ELSE 0 END) AS product_stock_m,
                        MAX(CASE WHEN s.sizes = 'L' THEN i.stock ELSE 0 END) AS product_stock_l,
                        MAX(CASE WHEN s.sizes = 'XL' THEN i.stock ELSE 0 END) AS product_stock_xl,
                        MAX(CASE WHEN s.sizes = 'XXL' THEN i.stock ELSE 0 END) AS product_stock_xxl
                    FROM product p
                    LEFT JOIN inventory i ON p.proID = i.proID
                    LEFT JOIN sizes s ON i.sizeID = s.sizeID
                    LEFT JOIN gender g ON g.genderID = p.genderID
                    LEFT JOIN grace_user gu ON gu.userID = p.sellerID
                    WHERE p.name LIKE ? AND p.sellerID = ?
                    GROUP BY p.proID, g.genderID, gu.userID
                    LIMIT ?, ?");
$search_param = '%' . $search . '%';
$stmt->bind_param("siii", $search_param, $user_id, $start, $limit);
$stmt->execute();
$result = $stmt->get_result();
$products = [];
while($row = mysqli_fetch_assoc($result)){
    $products[] = $row;
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles/index.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 20px;
        }
        .pagination a {
            display: inline-block;
            padding: 10px;
            margin: 0 5px;
            border: 1px solid #ccc;
            text-decoration: none;
            color: #333;
            border-radius: 5px;
        }
        .pagination a:hover {
            background-color: #f0f0f0;
        }
        .pagination .current {
            background-color: #333;
            color: #fff;
        }
        .no-products {
            text-align: center;
            font-weight: bold;
            color: red;
            padding-bottom: 20px;
        }
        .search_products{
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }
        .search-bar {
    margin-top: 20px;
}

.search-input-container {
    cursor: pointer;
    width: 220px;
    background-color: white;
    -webkit-box-shadow: 1px 3px 14px -8.5px #000000;
    -moz-box-shadow: 1px 3px 14px -8.5px #000000;
    box-shadow: 1px 3px 14px -8.5px #000000;
    display: flex;
    padding: 7px 10px;
    justify-content: space-between;
    align-items: center;
    gap: 10px;
    border-radius: 10px;
}

.search-input-container input[type="text"] {
    border: none;
    outline: none;
    flex: 1;
    padding: 5px;
}

.search-input-container button {
    border: none;
    background: none;
    cursor: pointer;
    padding: 5px;
}
.view-toggle {
    display: flex;
    gap: 8px;
    align-items: center;
}
.view-toggle-btn {
    padding: 8px 14px;
    border: 1px solid #ccc;
    background: white;
    border-radius: 6px;
    cursor: pointer;
    font-size: 14px;
    display: flex;
    align-items: center;
    gap: 6px;
    transition: all 0.2s ease;
}
.view-toggle-btn:hover {
    background-color: #f0f0f0;
}
.view-toggle-btn.active {
    background-color: #333;
    color: white;
    border-color: #333;
}
.grid-view {
    display: none;
    padding: 15px;
}
.grid-view.active {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 20px;
}
.table-view {
    overflow-x: auto;
}
.table-view.hidden {
    display: none;
}
.product-card {
    background: white;
    border-radius: 12px;
    padding: 15px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    display: flex;
    flex-direction: column;
}
.product-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.15);
}
.product-card-image {
    width: 100%;
    height: 180px;
    object-fit: cover;
    border-radius: 8px;
    margin-bottom: 12px;
}
.product-card-name {
    font-weight: 600;
    font-size: 16px;
    margin-bottom: 8px;
    color: #333;
}
.product-card-price {
    font-size: 18px;
    font-weight: 700;
    color: #e74c3c;
    margin-bottom: 8px;
}
.product-card-stock {
    font-size: 12px;
    color: #666;
    margin-bottom: 8px;
}
.product-card-stock span {
    display: inline-block;
    background: #f0f0f0;
    padding: 2px 6px;
    border-radius: 4px;
    margin-right: 4px;
}
.product-card-status {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    margin-bottom: 8px;
}
.product-card-status.available {
    background: #d4edda;
    color: #155724;
}
.product-card-status.unavailable {
    background: #f8d7da;
    color: #721c24;
}
.product-card-gender {
    font-size: 12px;
    color: #888;
    margin-bottom: 8px;
}
.product-card-description {
    font-size: 13px;
    color: #555;
    margin-bottom: 12px;
    flex-grow: 1;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.product-card-actions {
    display: flex;
    gap: 8px;
    margin-top: auto;
}
.product-card-actions button,
.product-card-actions a {
    flex: 1;
    padding: 8px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: 13px;
    text-align: center;
    text-decoration: none;
}
.product-card-actions button {
    background: #3498db;
    color: white;
}
.product-card-actions a {
    background: #e74c3c;
    color: white;
}
    </style>
    <title>Dashboard</title>
</head>
<body>
<?php include '../seller/dashboard_header.php'; ?>
   <section class="main_dash_container">

        <div class="main_container">
        <h1 class="main_title">Products</h1>
        <p class="main_subtitle">Manage Product Inventory</p>
           <div class="search_products">
                <div class="main_products_add" onclick="showProductPopup()">
                    <div>
                        <h1 class="add_text">Add Products</h1>
                    </div>
                    <div>
                        <i class="fa-solid fa-circle-plus"></i>
                    </div>
                </div>
                <!-- Search Bar and Filters -->
                <div class="search_box">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" id="productSearch" placeholder="Search products by name, id, or description..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <div class="filter_box">
                    <select id="productStatusFilter">
                        <option value="all">All Statuses</option>
                        <option value="available">Available</option>
                        <option value="unavailable">Unavailable</option>
                    </select>
                </div>
                <div class="filter_box">
                    <select id="productGenderFilter">
                        <option value="all">All Genders</option>
                        <option value="mens">Male</option>
                        <option value="womens">Female</option>
                    </select>
                </div>
                <div class="view-toggle">
                    <button class="view-toggle-btn active" onclick="setView('table')" id="tableViewBtn">
                        <i class="fa-solid fa-table"></i> Table
                    </button>
                    <button class="view-toggle-btn" onclick="setView('grid')" id="gridViewBtn">
                        <i class="fa-solid fa-grid-2"></i> Grid
                    </button>
                </div>
           </div>
            <div class="main_products_box">
            <div class="main_products_table table-view" id="tableView">
                <?php if(count($products) > 0): ?>
                    <table>
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Product Name</th>
                            <th>Stock (S/M/L/XL/XXL)</th>
                            <th>Price</th>
                            <th>Status</th>
                            <th>Description</th>
                            <th>Gender</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="productTableBody">
                        <?php
                            foreach($products as $row){  
                            ?>
                            <tr data-name="<?php echo htmlspecialchars($row['name']); ?>" 
                            data-desc="<?php echo htmlspecialchars($row['description']); ?>" 
                            data-status="<?php echo strtolower($row['status']); ?>"
                            data-gender="<?php echo strtolower($row['gender']); ?>"
                            data-id="<?php echo $row['proID']; ?>">
                                <td><img width="30" src="../uploads/images/<?php echo $row['image']; ?>" alt="<?php echo $row['name']; ?>"></td>
                                <td><?php echo $row['name']; ?></td>
                                <td>
                                    <?php
                                        $s = isset($row['product_stock_s']) ? $row['product_stock_s'] : 0;
                                        $m = isset($row['product_stock_m']) ? $row['product_stock_m'] : 0;
                                        $l = isset($row['product_stock_l']) ? $row['product_stock_l'] : 0;
                                        $xl = isset($row['product_stock_xl']) ? $row['product_stock_xl'] : 0;
                                        $xxl = isset($row['product_stock_xxl']) ? $row['product_stock_xxl'] : 0;
                                        echo 'S: ' . $s . ' / M: ' . $m . ' / L: ' . $l . ' / XL: ' . $xl . ' / XXL: ' . $xxl;
                                    ?>
                                </td>
                                <td><?php echo $row['price']; ?></td>
                                <td><?php echo $row['status']; ?></td>
                                <td  style="5px"><?php echo $row['description']; ?></td>
                                <td><?php echo $row['gender']; ?></td>
                                <td class='action-buttons'>
                                    <button onclick='updateItem(<?php echo $row['proID']; ?>)'>Update</button>
                                    <a href="?delete_id=<?php echo $row['proID']; ?>" class="delete-button" onclick="return confirm('Are you sure you want to delete this product?')">Delete</a>
                                </td>
                            </tr>
                            <?php
                            }
                        ?>
                    </tbody>
                </table>
                <?php else: ?>
                <p class="no-products">No products found.</p>
                <?php endif; ?>
            </div>
            <div class="grid-view" id="gridView">
                <?php if(count($products) > 0):
                    foreach($products as $row){  
                        $s = isset($row['product_stock_s']) ? $row['product_stock_s'] : 0;
                        $m = isset($row['product_stock_m']) ? $row['product_stock_m'] : 0;
                        $l = isset($row['product_stock_l']) ? $row['product_stock_l'] : 0;
                        $xl = isset($row['product_stock_xl']) ? $row['product_stock_xl'] : 0;
                        $xxl = isset($row['product_stock_xxl']) ? $row['product_stock_xxl'] : 0;
                        $statusClass = strtolower($row['status']) === 'available' ? 'available' : 'unavailable';
                ?>
                    <div class="product-card" 
                         data-name="<?php echo htmlspecialchars($row['name']); ?>" 
                         data-desc="<?php echo htmlspecialchars($row['description']); ?>" 
                         data-status="<?php echo strtolower($row['status']); ?>"
                         data-gender="<?php echo strtolower($row['gender']); ?>"
                         data-id="<?php echo $row['proID']; ?>">
                        <img class="product-card-image" src="../uploads/images/<?php echo $row['image']; ?>" alt="<?php echo $row['name']; ?>">
                        <div class="product-card-name"><?php echo $row['name']; ?></div>
                        <div class="product-card-price">₱<?php echo number_format($row['price'], 2); ?></div>
                        <div class="product-card-stock">
                            <span>S: <?php echo $s; ?></span>
                            <span>M: <?php echo $m; ?></span>
                            <span>L: <?php echo $l; ?></span>
                            <span>XL: <?php echo $xl; ?></span>
                            <span>XXL: <?php echo $xxl; ?></span>
                        </div>
                        <div class="product-card-status <?php echo $statusClass; ?>"><?php echo $row['status']; ?></div>
                        <div class="product-card-gender"><?php echo $row['gender']; ?></div>
                        <div class="product-card-description"><?php echo $row['description']; ?></div>
                        <div class="product-card-actions">
                            <button onclick='updateItem(<?php echo $row['proID']; ?>)'>Update</button>
                            <a href="?delete_id=<?php echo $row['proID']; ?>" onclick="return confirm('Are you sure you want to delete this product?')">Delete</a>
                        </div>
                    </div>
                <?php } ?>
                <?php else: ?>
                <p class="no-products">No products found.</p>
                <?php endif; ?>
            </div>
            </div>
            <!-- Pagination links -->
            <?php
                // Count total pages for pagination'
                $stmt = $con->prepare("SELECT COUNT(DISTINCT p.proID) AS total FROM product p LEFT JOIN inventory i ON p.proID = i.proID LEFT JOIN sizes s ON i.sizeID = s.sizeID WHERE p.name LIKE ?");
                $stmt->bind_param("s", $search_param);
                $stmt->execute();
                $result_count = $stmt->get_result();
                $row_count = $result_count->fetch_assoc(); 
                $total_pages = ceil($row_count['total'] / $limit);
                $stmt->close();

                echo "<div class='pagination'>";
                for ($i = 1; $i <= $total_pages; $i++) {
                    echo "<a href='?page=" . $i . "&search=$search'>" . $i . "</a>";
                }
                echo "</div>";
            ?>
        </div>
   </section>


   <div class="main_product_bg" id="mainProductBg" style="display: none;">
        <div class="main_product_bg_add">
            <div class="products_add">
                <div>
                    <h1 class="products_add_text">ADD NEW PRODUCTS</h1>
                </div>
                <div class="procducts_add_icon">
                    <i class="fa-solid fa-circle-xmark" onclick="hideProductPopup()" id="closeProductBtn"></i>
                </div>
            </div>

            <div>
            <form id="productForm" action="add_product.php" method="POST" enctype="multipart/form-data">
                <div class="products_add_info_pad">
                    <div class="products_add_info">
                        <div class="productname">
                            <label for="name">Product Name</label>
                            <input type="text" placeholder="20 Characters Only" id="name" name="name" required maxlength="20"> 
                        </div>
                        <div class="productname-short">
                            <label for="small_stock">Small Stocks</label>
                            <input type="number" id="small_stock" name="small_stock" required>
                        </div>
                        <div class="productname-short">
                            <label for="medium_stock">Medium Stocks</label>
                            <input type="number" id="medium_stock" name="medium_stock" required>
                        </div>
                        <div class="productname-short">
                            <label for="large_stock">Large Stocks</label>
                            <input type="number" id="large_stock" name="large_stock" required>
                        </div>
                        <div class="productname-short">
                            <label for="xlarge_stock">XLarge Stock</label>
                            <input type="number" id="xlarge_stock" name="xlarge_stock" required>
                        </div>
                        <div class="productname-short">
                            <label for="xxlarge_stock">XXLarge Stock</label>
                            <input type="number" id="xxlarge_stock" name="xxlarge_stock" required>
                        </div>
                        <div class="productname">
                            <label for="image">Product Image</label>
                            <input type="file" id="image" name="image" required>
                        </div>
                        <div class="productname">
                            <label for="price">Product Price</label>
                            <input type="number" id="price" name="price" min="0" max='999999' step="0.01" required>
                        </div>
                        <div class="productname">
                            <label for="product_gender">Gender</label>
                            <select id="product_gender" name="product_gender">
                                <option value="" selected disabled>Gender</option>
                                <option value="1">Male</option>
                                <option value="2">Female</option>
                            </select>
                        </div>
                        <div class="productname">
                            <label for="Description">Description</label>
                            <input type="text" id="Description" name="Description" maxlength="100"  >
                        </div>
                        <div class="productname">
                            <input type="text" value="In Stock" name="status" hidden>
                        </div>
                    </div>
                    <div class="products_addbtn">
                        <button type="submit" onclick="submitForm()">Submit</button>
                    </div>
                </div>
            </form>

            </div>
        </div>
   </div>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script>
function showProductPopup() {
    var mainProductBg = document.getElementById("mainProductBg");
    mainProductBg.style.display = "block";
}

function hideProductPopup() {
    var mainProductBg = document.getElementById("mainProductBg");
    mainProductBg.style.display = "none";
}
function updateItem(productId) {
    window.location.href = "update_product.php?id=" + productId;
}


        // Function to handle click event on submit button
        function submitForm() {
            // Show alert
            alert("Product Added");
        }

        function setView(view) {
            const tableView = document.getElementById('tableView');
            const gridView = document.getElementById('gridView');
            const tableViewBtn = document.getElementById('tableViewBtn');
            const gridViewBtn = document.getElementById('gridViewBtn');

            if (view === 'table') {
                tableView.classList.remove('hidden');
                gridView.classList.remove('active');
                tableViewBtn.classList.add('active');
                gridViewBtn.classList.remove('active');
            } else {
                tableView.classList.add('hidden');
                gridView.classList.add('active');
                tableViewBtn.classList.remove('active');
                gridViewBtn.classList.add('active');
            }
        }

        const productSearch = document.getElementById('productSearch');
        const statusFilter = document.getElementById('productStatusFilter');
        const genderFilter = document.getElementById('productGenderFilter');

        function filterProducts() {
            const searchTerm = (productSearch ? productSearch.value : '').toLowerCase();
            const filterStatus = (statusFilter ? statusFilter.value : 'all').toLowerCase();
            const filterGender = (genderFilter ? genderFilter.value : 'all').toLowerCase();

            const tableRows = document.querySelectorAll('#productTableBody tr');
            const gridCards = document.querySelectorAll('.product-card');

            tableRows.forEach(row => {
                const name = (row.getAttribute('data-name') || '').toLowerCase();
                const desc = (row.getAttribute('data-desc') || '').toLowerCase();
                const status = (row.getAttribute('data-status') || '').toLowerCase();
                const gender = (row.getAttribute('data-gender') || '').toLowerCase();
                const id = (row.getAttribute('data-id') || '').toLowerCase();

                const matchesSearch = name.includes(searchTerm) || desc.includes(searchTerm) || id.includes(searchTerm);
                const matchesStatus = filterStatus === 'all' || (filterStatus === 'available' && status === 'available') || (filterStatus === 'unavailable' && status !== 'available');
                const matchesGender = filterGender === 'all' || gender === filterGender;

                if (matchesSearch && matchesStatus && matchesGender) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });

            gridCards.forEach(card => {
                const name = (card.getAttribute('data-name') || '').toLowerCase();
                const desc = (card.getAttribute('data-desc') || '').toLowerCase();
                const status = (card.getAttribute('data-status') || '').toLowerCase();
                const gender = (card.getAttribute('data-gender') || '').toLowerCase();
                const id = (card.getAttribute('data-id') || '').toLowerCase();

                const matchesSearch = name.includes(searchTerm) || desc.includes(searchTerm) || id.includes(searchTerm);
                const matchesStatus = filterStatus === 'all' || (filterStatus === 'available' && status === 'available') || (filterStatus === 'unavailable' && status !== 'available');
                const matchesGender = filterGender === 'all' || gender === filterGender;

                if (matchesSearch && matchesStatus && matchesGender) {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            });
        }

        if (productSearch) productSearch.addEventListener('input', filterProducts);
        if (statusFilter) statusFilter.addEventListener('change', filterProducts);
        if (genderFilter) genderFilter.addEventListener('change', filterProducts);

</script>

</body>
</html>
