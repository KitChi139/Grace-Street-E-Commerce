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
    $delete_sql = "DELETE FROM product_list WHERE id = $delete_id";
    mysqli_query($con, $delete_sql);
}

if(isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $delete_sql = "DELETE FROM grace_user WHERE id = $delete_id";
    mysqli_query($con, $delete_sql);
}

// Modify the SQL query to include search functionality
$sql = "SELECT * FROM grace_user WHERE role IN ('admin','employee') ";
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
<?php include '../admin/dashboard_header.php'; ?>
   <section class="main_dash_container">
        <!-- <div class="product_Added" id="productAdded" style="display: none;">
            Product Added
        </div> -->
        <div class="main_container">
        <h1 class="main_title">Employees</h1>
           <div class="search_products">
            <div class="main_products_add" onclick="showProductPopup()">
                    <div>
                        <h1 class="add_text">Add Employee</h1>
                    </div>
                    <div>
                        <i class="fa-solid fa-circle-plus"></i>
                    </div>
                </div>
                
           </div>
            <div class="main_products_box">
            <div class="main_products_table">
                <?php if(mysqli_num_rows($result) > 0): ?>
                    <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Role</th>
                            <th>Email</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                            $count = 0;
                            while($row = mysqli_fetch_assoc($result)){  
                                $count++;
                            ?>
                            <tr>
                                <td><?php echo $count ?></td>
                                <td><?php echo $row['username']; ?></td>
                                <td><?php echo $row['role']; ?></td>
                                <td><?php echo $row['email']; ?></td>
                                <td><a href="?delete_id=<?php echo $row['id']; ?>" class="delete-button" onclick="return confirm('Are you sure you want to delete this product?')">Delete</a></td>
                            </tr>
                            <?php
                            }
                        ?>
                    </tbody>
                </table>
                <?php else: ?>
                <p class="no-products">No Employee found.</p>
                <?php endif; ?>
            </div>
           
   </section>


   <div class="main_product_bg" id="mainProductBg" style="display: none;">
        <div class="main_product_bg_add">
            <div class="products_add">
                <div>
                    <h1 class="products_add_text">Add New Employee</h1>
                </div>
                <div class="procducts_add_icon">
                    <i class="fa-solid fa-circle-xmark" onclick="hideProductPopup()" id="closeProductBtn"></i>
                </div>
            </div>

            <div>
            <form id="productForm" action="add_employee.php" method="POST" enctype="multipart/form-data">
                <div class="products_add_info_pad">
                    <div class="products_add_info">
                        <div class="productname">
                            <label for="product_name">Employee Name</label>
                            <input type="text" id="name" name="name" required> 
                        </div>
                        <div class="productname">
                            <label for="total_stock">Email</label>
                            <input type="email" id="email" name="email" required>
                        </div>
                        <div class="productname">
                            <label for="product_image">Password</label>
                            <input type="password" id="password" name="password" required>
                        </div>
                        <div class="productname">
                            <label for="product_price">Confirm Password</label>
                            <input type="password" id="cpassword" name="cpassword" required>
                        </div>
                        <div class="productname">
                            <label for="role">Role</label>
                            <select id="role" name="role" required>
                                <option value="" selected disabled>Role</option>
                                <option value="admin">Admin</option>
                                <option value="employee">Employee</option>
                            </select>
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
            alert("Employee Added");
        }
</script>

</body>
</html>
