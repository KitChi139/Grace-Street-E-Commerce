<?php
session_start();
include('../components/connect.php');

// Pagination variables
$limit = 10; // Number of rows per page
$page = isset($_GET['page']) ? $_GET['page'] : 1;
$start = ($page - 1) * $limit;

// Check if search parameter is provided
$search = isset($_GET['search']) ? $_GET['search'] : '';

if(isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $stmt = $con->prepare("DELETE FROM product_list WHERE id = ?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    header("Location: products.php");
    $stmt->close();
    exit();
}

// Modify the SQL query to include search functionality
$stmt = $con->prepare("SELECT * FROM product_list WHERE product_name LIKE ? LIMIT ?, ?");
$search_param = '%' . $search . '%';
$stmt->bind_param("sii", $search_param, $start, $limit);
$stmt->execute();
$result = $stmt->get_result();
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
            margin-top: 20px;
            font-weight: bold;
            color: red;
        }
        .search_products{
            display: flex;
            justify-content: space-between;
            align-items: center;
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
                        <option value="mens">Mens</option>
                        <option value="womens">Womens</option>
                    </select>
                </div>
           </div>
            <div class="main_products_box">
            <div class="main_products_table">
                <?php if(mysqli_num_rows($result) > 0): ?>
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
                            while($row = mysqli_fetch_assoc($result)){  
                            ?>
                            <tr data-name="<?php echo htmlspecialchars($row['product_name']); ?>" data-desc="<?php echo htmlspecialchars($row['description']); ?>" data-status="<?php echo strtolower($row['product_status']); ?>" data-gender="<?php echo strtolower($row['gender']); ?>" data-id="<?php echo $row['id']; ?>">
                                <td><img width="30" src="../uploads/images/<?php echo $row['product_image']; ?>" alt="<?php echo $row['product_name']; ?>"></td>
                                <td><?php echo $row['product_name']; ?></td>
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
                                <td><?php echo $row['product_price']; ?></td>
                                <td><?php echo $row['product_status']; ?></td>
                                <td  style="5px"><?php echo $row['description']; ?></td>
                                <td><?php echo $row['gender']; ?></td>
                                <td class='action-buttons'>
                                    <button onclick='updateItem(<?php echo $row['id']; ?>)'>Update</button>
                                    <a href="?delete_id=<?php echo $row['id']; ?>" class="delete-button" onclick="return confirm('Are you sure you want to delete this product?')">Delete</a>
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
            </div>
            <!-- Pagination links -->
            <?php
                // Count total pages for pagination'
                $stmt = $con->prepare("SELECT COUNT(*) AS total FROM product_list WHERE product_name LIKE ?");
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
                            <label for="product_name">Product Name</label>
                            <input type="text" placeholder="20 Characters Only" id="product_name" name="product_name" required maxlength="20"> 
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
                            <label for="product_image">Product Image</label>
                            <input type="file" id="product_image" name="product_image" required>
                        </div>
                        <div class="productname">
                            <label for="product_price">Product Price</label>
                            <input type="number" id="product_price" name="product_price" min="100" max='9999' required>
                        </div>
                        <div class="productname">
                            <label for="product_gender">Gender</label>
                            <select id="product_gender" name="product_gender">
                                <option value="" selected disabled>Gender</option>
                                <option value="Mens">Mens</option>
                                <option value="Womens">Womens</option>
                            </select>
                        </div>
                        <div class="productname">
                            <label for="Description">Description</label>
                            <input type="text" id="Description" name="Description" maxlength="100"  >
                        </div>
                        <div class="productname">
                            <input type="text" value="Available" name="product_status" hidden>
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
// Function to handle click event on addProductBtn
function showProductPopup() {
    var mainProductBg = document.getElementById("mainProductBg");
    mainProductBg.style.display = "block";
}

// Function to handle click event on closeProductBtn
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

        const productSearch = document.getElementById('productSearch');
        const statusFilter = document.getElementById('productStatusFilter');
        const genderFilter = document.getElementById('productGenderFilter');

        // Search and Filter Logic for products
        function filterProducts() {
            const searchTerm = (productSearch ? productSearch.value : '').toLowerCase();
            const filterStatus = (statusFilter ? statusFilter.value : 'all').toLowerCase();
            const filterGender = (genderFilter ? genderFilter.value : 'all').toLowerCase();

            const rows = document.querySelectorAll('#productTableBody tr');
            rows.forEach(row => {
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
        }

        if (productSearch) productSearch.addEventListener('input', filterProducts);
        if (statusFilter) statusFilter.addEventListener('change', filterProducts);
        if (genderFilter) genderFilter.addEventListener('change', filterProducts);

</script>

</body>
</html>
