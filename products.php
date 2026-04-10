<?php
include('./components/connect.php');

$sort_order = "DESC";
$sort_by = "id"; 
$category_label = "Category";

if(isset($_GET['sort'])) {
    if($_GET['sort'] == 'lowest') {
        $sort_order = "ASC";
        $sort_by = "product_price";
        $category_label = "Lowest to Highest Price";
    } elseif($_GET['sort'] == 'highest') {
        $sort_order = "DESC";
        $sort_by = "product_price";
        $category_label = "Highest to Lowest Price";
    }
}

if(isset($_GET['category']) && $_GET['category'] == 'All') {
    $sort_order = "DESC";
    $category_label = "Category";
}

if(isset($_GET['category']) && $_GET['category'] == 'New Items') {
    $category_label = "New Items";
    $select_product = mysqli_query($con, "SELECT * FROM product_list WHERE date >= DATE_SUB(CURDATE(), INTERVAL 3 DAY) ORDER BY $sort_by $sort_order") or die('query failed');
} else {
    if(isset($_GET['category']) && ($_GET['category'] == "Men's Clothing" || $_GET['category'] == "Women's Clothing")) {
        $gender = $_GET['category'] == "Men's Clothing" ? "Mens" : "Womens";
        $select_product = mysqli_query($con, "SELECT * FROM product_list WHERE gender='$gender' ORDER BY $sort_by $sort_order") or die('query failed');
        $category_label = $_GET['category'];
    } else {
        $select_product = mysqli_query($con, "SELECT * FROM product_list ORDER BY $sort_by $sort_order") or die('query failed');
    }
}

if(isset($_POST['search'])) {
    $search_term = $_POST['search'];
    if(empty($search_term)) {
        $category_label = "Category";
        $select_product = mysqli_query($con, "SELECT * FROM product_list ORDER BY id DESC") or die('query failed');
    } else {
        $category_label = "Category";
        $search_query = mysqli_query($con, "SELECT * FROM product_list WHERE product_name LIKE '%$search_term%'") or die('search query failed');
        $select_product = $search_query;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grace Street/All Products</title>
    <link rel="stylesheet" href="Css/style.css">
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<style>
    .discount_price{
        display: flex;
        justify-content: space-between;
        font-size: 13px;
    }
    .discount_price .discount{
        background-color: red;
        color: white;
        padding: 2px;
    }
</style>
<body>
    <?php include 'additional/header.php'; ?>
    <?php include 'chat.php'; ?>
    <section>
        <div class="products-container">
            <div class="filter">
                <div style="margin: 5vh 0;">
                    <form method="POST" action="">
                        <p style="margin:0 5px;">Search:</p>
                        <input type="text" placeholder="Type Item Name" name="search" style="padding: 10px; font-size: 13px; width: 28vh; border: none; background-color: #e8e8e8;">
                        <button type="submit" style="display: none;"></button>
                    </form>
                </div>
                <nav class="filter-nav">
                    <label for="touch"><span><?php echo $category_label; ?></span></label>
                    <input type="checkbox" id="touch">
                    <ul>
                        <li><a href="?category=All">All</a></li>
                        <li><a href="?category=New Items">New Items</a></li>
                        <li><a href="?category=Women's Clothing">Women's Clothing</a></li>
                        <li><a href="?category=Men's Clothing">Men's Clothing</a></li>
                        <li><a href="?sort=lowest">Lowest to Highest Price</a></li>
                        <li><a href="?sort=highest">Highest to Lowest Price</a></li>
                    </ul>
                </nav>
            </div>
            <div class="products">
                <div class="products-header">
                    <h1>Shop All</h1>
                </div>
                <div class="products-table">
                <?php
                    if(mysqli_num_rows($select_product) > 0) {
                        while($fetch_product = mysqli_fetch_assoc($select_product)) {
                            $original_price = $fetch_product['product_price'];
                            $discount = $fetch_product['product_discount'];
                            $discounted_price = $original_price - ($original_price * ($discount / 100));
                    ?>
                    <form id="productForm<?= $fetch_product['id']; ?>">
                            <div class="items-product">
                                <div class="product_table">
                                    <div class="items-content">
                                        <div class="quick_view">
                                            <a href="quick_view.php?pid=<?= $fetch_product['id']; ?>" class="fas fa-eye"></a>
                                            <a href="#" class="fa-solid fa-heart wishlist-btn" 
                                                data-pid="<?= $fetch_product['id']; ?>" 
                                                data-product-image="<?= $fetch_product['product_image']; ?>" 
                                                data-product-name="<?= $fetch_product['product_name']; ?>" 
                                                data-product-price="<?= $fetch_product['product_price']; ?>" 
                                                data-discounted-price="<?= $discounted_price; ?>" 
                                                onclick="addToWishlist(event)"></a>
                                        </div>
                                        <input type="hidden" name="pid" value="<?= $fetch_product['id']; ?>">
                                        <div class="product_images">
                                            <img src="uploads/images/<?php echo $fetch_product['product_image'];?>" alt="">
                                        </div>
                                        <h1 style="margin: 0; margin-top: 10px; font-size: 15px;" class="product_name"><?php echo $fetch_product['product_name'];?></h1>
                                        <div class="discount_price">
                                            <p style="margin: 0; font-size: 13px;">
                                                <?php
                                                if ($fetch_product['product_stock_s'] > 0) {
                                                    echo $fetch_product['product_stock_s'] . '<span> in stock</span>';
                                                } else {
                                                    echo '<span style="background-color: red; color: white; padding: 3px;">Out of stock</span>';
                                                }
                                                ?>
                                            </p>
                                            <?php if ($fetch_product['product_discount'] > 0): ?>
                                                <p class="discount"><?php echo $fetch_product['product_discount']; ?>% off</p>
                                            <?php endif; ?>
                                        </div>
                                        <div class="itembottom-content">
                                            <button class="add-btn" <?php echo ($fetch_product['product_stock_s'] > 0) ? '' : 'disabled'; ?> onclick="addToCart(<?php echo $fetch_product['id']; ?>, '<?php echo $fetch_product['product_image']; ?>', '<?php echo $fetch_product['product_name']; ?>', '<?php echo $fetch_product['product_price'];  ?>' , '<?php echo $discounted_price  ?>')">Add to cart</button>
                                            <span style="margin: 0; color:#8c8989; font-size: 12px;">PHP
                                                <?php if ($fetch_product['product_discount'] > 0): ?>
                                                    <p class="product-price" style="color: green; font-size: 16px; margin: 0; text-decoration: line-through;">
                                                        <?php echo $original_price; ?>.00
                                                    </p>
                                                    <p class="discounted-price" style="color: black; font-size: 18px; margin: 0;">
                                                        <?php echo number_format($discounted_price, 2); ?>
                                                    </p>
                                                <?php else: ?>
                                                    <p class="product-price" style="color: black; font-size: 18px; margin: 0;">
                                                        <?php echo $original_price; ?>.00
                                                    </p>
                                                <?php endif; ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    <?php
                            }
                        } else {
                            echo "<p>No product found!</p>";
                        }
                    ?>
                </div>
            </div>
        </div>
    </section>
    <?php include 'additional/footer.php'; ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
    <script>
        $(document).ready(function() {
           
            $("#searchInput").on("keyup", function() {
                var value = $(this).val().toLowerCase();
                $(".items-product").filter(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
                });
            });
        });

        function addToCart(productId, productImage, productName, productPrice, discountedPrice) {
            console.log("Adding to cart:", productId, productImage, productName, productPrice, discountedPrice);

            $.ajax({
                type: 'POST',
                url: 'addToCart.php',
                data: {
                    productId: productId,
                    productImage: productImage,
                    productName: productName,
                    productPrice: productPrice,
                    discountedPrice: discountedPrice, // Pass discounted price
                    productQuantity: 1  // Default quantity is 1
                },
                dataType: 'json',
                success: function(response) {
                    if (response && response.success) {
                        alert('Item added successfully!');
                        window.location.href = "products.php";
                    } else {
                        alert('Error adding item to cart: ' + response.error);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error adding item to cart:', error);
                }
            });
        }
        function addToWishlist(event) {
    event.preventDefault();

    var productId = $(event.target).data('pid');
    var productImage = $(event.target).data('product-image');
    var productName = $(event.target).data('product-name');
    var productPrice = $(event.target).data('product-price');
    var discountedPrice = $(event.target).data('discounted-price');

    $.ajax({
        type: 'POST',
        url: 'wishlist_db.php',
        data: {
            productId: productId,
            productImage: productImage,
            productName: productName,
            productPrice: productPrice,
            discountedPrice: discountedPrice // Pass discounted price to the server
        },
        dataType: 'json',
        success: function(response) {
            if (response && response.success) {
                alert('Item added to wishlist successfully!');
                window.location.href = "wishlist.php";
            } else {
                alert('Error adding item to wishlist: ' + response.error);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error adding item to wishlist:', error);
        }
    });
}
       
    </script>
    
</body>
</html>
