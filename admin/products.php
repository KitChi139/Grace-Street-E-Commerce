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
    
    // Fetch product name for logging before deletion
    $fetch_name = mysqli_query($con, "SELECT name FROM product WHERE id = $delete_id");
    $product_name = mysqli_fetch_assoc($fetch_name)['name'] ?? "Unknown Product";

    $delete_sql = "DELETE FROM product WHERE id = $delete_id";
    if (mysqli_query($con, $delete_sql)) {
        // Log product deletion
        include_once '../components/audit_logger.php';
        log_audit('Product Deleted', $_SESSION['user-id'], $product_name, 'Warning');
    }
}

// Modify the SQL query to include search functionality
$sql = "SELECT 
            p.*, 
            MAX(CASE WHEN s.sizes = 'S' THEN i.stock ELSE 0 END) AS product_stock_s,
            MAX(CASE WHEN s.sizes = 'M' THEN i.stock ELSE 0 END) AS product_stock_m,
            MAX(CASE WHEN s.sizes = 'L' THEN i.stock ELSE 0 END) AS product_stock_l,
            MAX(CASE WHEN s.sizes = 'XL' THEN i.stock ELSE 0 END) AS product_stock_xl,
            MAX(CASE WHEN s.sizes = 'XXL' THEN i.stock ELSE 0 END) AS product_stock_xxl
        FROM product p
        LEFT JOIN inventory i ON p.proID = i.proID
        LEFT JOIN sizes s ON i.sizeID = s.sizeID
        WHERE p.name LIKE '%$search%' 
        GROUP BY p.proID
        LIMIT $start, $limit";
$result = mysqli_query($con, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles/index.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>

    </style>
    <title>Dashboard</title>
</head>
<body>
<?php include 'dashboard_header.php'; ?>    
   <section class="main_dash_container">
        <!-- <div class="product_Added" id="productAdded" style="display: none;">
            Product Added
        </div> -->
        <div class="main_container">
        <h1 class="main_title">Products</h1>
           <div class="search_products">
            <div class="main_products_add" onclick="showProductPopup()">
                    <div>
                        <h1 class="add_text">Add Products</h1>
                    </div>
                    <div>
                        <i class="fa-solid fa-circle-plus"></i>
                    </div>
                </div>
                <!-- Search Bar -->
                <div class="search-bar">
                    <form action="" method="GET">
                        <div class="search-input-container">
                            <input type="text" name="search" placeholder="Search..." value="<?php echo $search; ?>">
                            <button type="submit"><i class="fa-solid fa-search"></i></button>
                        </div>
                    </form>
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
                            <th>Stock (S)</th>
                            <th>Stock (M)</th>
                            <th>Stock (L)</th>
                            <th>Stock (XL)</th>
                            <th>Stock (XXL)</th>
                            <th>Price</th>
                            <th>Discount</th>
                            <th>Status</th>
                            <th>Description</th>
                            <th>Gender</th> <!-- Added Gender column -->
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            while($row = mysqli_fetch_assoc($result)){  
                            ?>
                            <tr>
                                <td><img width="30" src="../uploads/images/<?php echo $row['image']; ?>" alt="<?php echo $row['name']; ?>"></td>
                                <td><?php echo $row['name']; ?></td>
                                <td><?php echo $row['product_stock_s']; ?></td>
                                <td><?php echo $row['product_stock_m']; ?></td>
                                <td><?php echo $row['product_stock_l']; ?></td>
                                <td><?php echo $row['product_stock_xl']; ?></td>
                                <td><?php echo $row['product_stock_xxl']; ?></td>
                                <td><?php echo $row['price']; ?></td>
                                <td><?php echo $row['product_discount']; ?>%</td>
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
            </div>
            <!-- Pagination links -->
            <?php
                // Count total pages for pagination
                $sql_count = "SELECT COUNT(*) AS total FROM product WHERE name LIKE '%$search%'";
                $result_count = mysqli_query($con, $sql_count);
                $row_count = mysqli_fetch_assoc($result_count);
                $total_pages = ceil($row_count['total'] / $limit);

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
                        <div class="productname">
                            <label for="total_discount">Discount</label>
                            <select id="total_discount" name="product_discount" required>
                                <option>Add Discount</option>
                                <option value="10">10%</option>
                                <option value="20">20%</option>
                                <option value="30">30%</option>
                                <option value="40">40%</option>
                                <option value="50">50%</option>
                                <option value="60">60%</option>
                                <option value="70">70%</option>
                            </select>   
                        </div>
                       <div class="stock_grid">
                            <div class="productname">
                                <label for="total_stock">Stock (S)</label>
                                <input type="number" id="total_stock_s" name="product_stock_s" required>
                            </div>
                            <div class="productname">
                                <label for="total_stock">Stock (M)</label>
                                <input type="number" id="total_stock_m" name="product_stock_m" required>
                            </div>
                            <div class="productname">
                                <label for="total_stock">Stock (L)</label>
                                <input type="number" id="total_stock_l" name="product_stock_l" required>
                            </div>
                            <div class="productname">
                                <label for="total_stock">Stock (XL)</label>
                                <input type="number" id="total_stock_xl" name="product_stock_xl" required>
                            </div>
                            <div class="productname">
                                <label for="total_stock">Stock (XXL)</label>
                                <input type="number" id="total_stock_xxl" name="product_stock_xxl" required>
                            </div>
                       </div>
                        <div class="productname">
                            <label for="image">Product Image</label>
                            <input type="file" id="image" name="image" required>
                        </div>
                        <div class="productname">
                            <label for="price">Product Price</label>
                            <input type="number" id="price" name="price" min="100" max='9999' required>
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
                            <input type="text" value="Available" name="status" hidden>
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

</script>

<script>
        // Function to handle click event on submit button
        function submitForm() {
            // Show alert
            alert("Product Added");
        }
</script>

</body>
</html>
