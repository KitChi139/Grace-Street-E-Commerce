<?php
include('./components/connect.php');

if (!isset($_SESSION['user-id'])) {
    header("Location: ./login.php");
    exit();
}

$user_id = $_SESSION['user-id'];

$sort_order = "DESC";
$sort_by = "proID"; 
$category_label = "Category";

if(isset($_GET['sort'])) {
    if($_GET['sort'] == 'lowest') {
        $sort_order = "ASC";
        $sort_by = "price";
        $category_label = "Lowest to Highest Price";
    } elseif($_GET['sort'] == 'highest') {
        $sort_order = "DESC";
        $sort_by = "price";
        $category_label = "Highest to Lowest Price";
    }
}

if(isset($_GET['category']) && $_GET['category'] == 'All') {
    $sort_order = "DESC";
    $category_label = "Category";
}

if(isset($_GET['category']) && $_GET['category'] == 'New Items') {
    $category_label = "New Items";
    $select_product = mysqli_query($con, "SELECT p.*, 
                                            MAX(CASE WHEN s.sizes = 'S' THEN i.stock ELSE 0 END) AS product_stock_s
                                          FROM product p
                                          LEFT JOIN inventory i ON p.proID = i.proID
                                          LEFT JOIN sizes s ON i.sizeID = s.sizeID
                                          GROUP BY p.proID
                                          ORDER BY p.proID DESC LIMIT 10") or die('query failed');
} else {
    if(isset($_GET['category']) && ($_GET['category'] == "Men's Clothing" || $_GET['category'] == "Women's Clothing")) {
        $gender = $_GET['category'] == "Men's Clothing" ? "Male" : "Female";
        $select_product = mysqli_query($con, "SELECT p.*, 
                                                MAX(CASE WHEN s.sizes = 'S' THEN i.stock ELSE 0 END) AS product_stock_s
                                              FROM product p
                                              JOIN gender g ON p.genderID = g.genderID
                                              LEFT JOIN inventory i ON p.proID = i.proID
                                              LEFT JOIN sizes s ON i.sizeID = s.sizeID
                                              WHERE g.gender = '$gender' 
                                              GROUP BY p.proID
                                              ORDER BY $sort_by $sort_order") or die('query failed');
        $category_label = $_GET['category'];
    } else {
        $select_product = mysqli_query($con, "SELECT p.*, 
                                                MAX(CASE WHEN s.sizes = 'S' THEN i.stock ELSE 0 END) AS product_stock_s
                                              FROM product p
                                              LEFT JOIN inventory i ON p.proID = i.proID
                                              LEFT JOIN sizes s ON i.sizeID = s.sizeID
                                              GROUP BY p.proID
                                              ORDER BY $sort_by $sort_order") or die('query failed');
    }
}

if(isset($_POST['search'])) {
    $search_term = $_POST['search'];
    if(empty($search_term)) {
        $category_label = "Category";
        $select_product = mysqli_query($con, "SELECT p.*, MAX(CASE WHEN s.sizes = 'S' THEN i.stock ELSE 0 END) AS product_stock_s FROM product p LEFT JOIN inventory i ON p.proID = i.proID LEFT JOIN sizes s ON i.sizeID = s.sizeID GROUP BY p.proID ORDER BY p.proID DESC") or die('query failed');
    } else {
        $category_label = "Category";
        $search_query = mysqli_query($con, "SELECT p.*, MAX(CASE WHEN s.sizes = 'S' THEN i.stock ELSE 0 END) AS product_stock_s FROM product p LEFT JOIN inventory i ON p.proID = i.proID LEFT JOIN sizes s ON i.sizeID = s.sizeID WHERE p.name LIKE '%$search_term%' GROUP BY p.proID") or die('search query failed');
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
                <div style="margin: 0 0 2rem 0;">
                    <form method="POST" action="" onsubmit="return false;">
                        <p style="margin:0 5px;">Search:</p>
                        <input type="text" id="searchInput" placeholder="Type Item Name" name="search" style="padding: 10px; font-size: 16px; width: 100%; border: none; background-color: #e8e8e8; box-sizing: border-box;">
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
                            $original_price = $fetch_product['price'];
                            $discount = (float)($fetch_product['discount'] ?? 0);

                            $discounted_price = $original_price;

                            if ($discount > 0) {
                                $discounted_price = $original_price - ($original_price * ($discount / 100));
                            }
                    ?>
                    <form id="productForm<?= $fetch_product['proID']; ?>">
                            <div class="items-product" onclick="window.location.href='quick_view.php?pid=<?= $fetch_product['proID']; ?>';" style="cursor: pointer;">
                                <div class="product_table">
                                    <div class="items-content">
                                        <div class="quick_view">
                                            <a href="quick_view.php?pid=<?= $fetch_product['proID']; ?>" class="fas fa-eye"></a>
                                            <a href="#" class="fa-solid fa-heart wishlist-btn" 
                                                data-pid="<?= $fetch_product['proID']; ?>" 
                                                data-product-image="<?= htmlspecialchars($fetch_product['image']); ?>" 
                                                data-product-name="<?= htmlspecialchars($fetch_product['name']); ?>" 
                                                data-product-price="<?= $fetch_product['price']; ?>" 
                                                data-discounted-price="<?= $discounted_price; ?>" 
                                                onclick="event.stopPropagation(); addToWishlist(event)"></a>
                                        </div>
                                        <input type="hidden" name="pid" value="<?= $fetch_product['proID']; ?>">
                                        <div class="product_images">
                                            <img src="uploads/images/<?php echo $fetch_product['image'];?>" alt="">
                                        </div>
                                        <h1 style="margin: 0; margin-top: 10px; font-size: 20px;" class="name"><?php echo $fetch_product['name'];?></h1>
                                        <div class="discount_price">
                                            <p style="margin: 0; font-size: 16px;">
                                                <?php
                                                if ($fetch_product['product_stock_s'] > 0) {
                                                    echo $fetch_product['product_stock_s'] . '<span> in stock</span>';
                                                } else {
                                                    echo '<span style="background-color: #B85C38; color: #F7F3EE; padding: 3px 8px; font-size: 0.7rem; letter-spacing: 0.08em; text-transform: uppercase; font-family: Jost, sans-serif;">Out of Stock</span>';
                                                }
                                                ?>
                                            </p>
                                            <?php if ($fetch_product['discount'] > 0): ?>
                                                <p class="discount"><?php echo $fetch_product['product_discount']; ?>% off</p>
                                            <?php endif; ?>
                                        </div>
                                        <div class="itembottom-content">
                                            <a href="quick_view.php?pid=<?= $fetch_product['proID']; ?>" class="add-btn" style="text-decoration: none; text-align: center; display: block; width: 100%;" <?php echo ($fetch_product['product_stock_s'] > 0) ? '' : 'style="pointer-events: none; opacity: 0.5;"'; ?> onclick="event.stopPropagation();">Select Size</a>
                                            <span style="margin: 0; color:#8c8989; font-size: 15px;">PHP
                                                <?php if ($fetch_product['discount'] > 0): ?>
                                                    <p class="product-price" style="color: green; font-size: 16px; margin: 0; text-decoration: line-through;">
                                                        <?php echo $original_price; ?>.00
                                                    </p>
                                                    <p class="discounted-price" style="color: black; font-size: 18px; margin: 0;">
                                                        <?php echo number_format($discounted_price, 2); ?>
                                                    </p>
                                                <?php else: ?>
                                                    <p class="product-price" style="color: black; font-size: 20px; margin: 0;">
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
        <!-- Pagination -->
        <div id="pagination" style="display: flex; justify-content: center; align-items: center; gap: 8px; padding: 2rem 0 3rem;"></div>
    </section>

<?php include 'additional/footer.php'; ?>

<script>
    const allItems = document.querySelectorAll('.items-product');
    const perPage = 12;
    let currentPage = 1;

    function getTotalPages() {
        const visible = [...allItems].filter(i => i.style.display !== 'none' || i.getAttribute('data-hidden') !== 'filter');
        return Math.ceil(allItems.length / perPage);
    }

    function showPage(page) {
        currentPage = page;
        const start = (page - 1) * perPage;
        const end = start + perPage;

        allItems.forEach((item, i) => {
            item.style.display = (i >= start && i < end) ? '' : 'none';
        });

        renderPagination();
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function renderPagination() {
        const total = Math.ceil(allItems.length / perPage);
        const container = document.getElementById('pagination');
        container.innerHTML = '';

        if (total <= 1) return;

        // Prev button
        const prev = document.createElement('button');
        prev.textContent = '←';
        prev.disabled = currentPage === 1;
        applyBtnStyle(prev, currentPage === 1);
        prev.onclick = () => showPage(currentPage - 1);
        container.appendChild(prev);

        // Page numbers
        for (let i = 1; i <= total; i++) {
            const btn = document.createElement('button');
            btn.textContent = i;
            applyBtnStyle(btn, false, i === currentPage);
            btn.onclick = () => showPage(i);
            container.appendChild(btn);
        }

        // Next button
        const next = document.createElement('button');
        next.textContent = '→';
        next.disabled = currentPage === total;
        applyBtnStyle(next, currentPage === total);
        next.onclick = () => showPage(currentPage + 1);
        container.appendChild(next);
    }

    function applyBtnStyle(btn, disabled = false, active = false) {
        btn.style.cssText = `
            padding: 8px 14px;
            border: 0.5px solid #2C2825;
            background: ${active ? '#2C2825' : 'transparent'};
            color: ${active ? '#F7F3EE' : '#2C2825'};
            font-family: 'Jost', sans-serif;
            font-size: 0.8rem;
            letter-spacing: 0.08em;
            cursor: ${disabled ? 'not-allowed' : 'pointer'};
            opacity: ${disabled ? '0.35' : '1'};
            transition: all 0.2s;
            border-radius: 4px;
        `;
        if (!active && !disabled) {
            btn.onmouseover = () => { btn.style.background = '#2C2825'; btn.style.color = '#F7F3EE'; };
            btn.onmouseout = () => { btn.style.background = 'transparent'; btn.style.color = '#2C2825'; };
        }
    }

    // Init
    showPage(1);
</script>

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
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: 'Item added successfully!',
                            confirmButtonColor: '#000'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.href = "products.php";
                            }
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: 'Error adding item to cart: ' + response.error,
                            confirmButtonColor: '#000'
                        });
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
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: 'Item added to wishlist successfully!',
                    confirmButtonColor: '#000'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = "wishlist.php";
                    }
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'Error adding item to wishlist: ' + response.error,
                    confirmButtonColor: '#000'
                });
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
