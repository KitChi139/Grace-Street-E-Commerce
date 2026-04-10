<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grace Street</title>

    <!-- css connection -->
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'additional/header.php'; ?>
    <?php include 'chat.php'; ?>

    <section>
        <div class="home-content">
            <!-- imageslider -->
            <div class="slider-container">
                <div class="slider-text">
                    <h1>Welcome to <br><span style="font-size: 50px;">GRACE STREET</span></br> <span style="font-weight: 300;">Clothing Shop</span></h1>
                </div>
                <div class="slider">
                    <img src="img/img1.png" alt="Image 1">
                    <img src="img/img2.png" alt="Image 2">
                    <img src="img/img3.png" alt="Image 3">
                    <img src="img/img4.png" alt="Image 4">
                </div>
            </div>

            <!-- Advertisement Section -->
            <div class="advertise-container">
                <div class="advertise-content">
                    <div class="ads" style="background-image: url(img/Products.jpg); background-size: contain; background-repeat: no-repeat;">
                        
                        <h1>All Products</h1>
                        <p>Get 20% OFF on slected products.</p>
                        <a href=""><button>Shop now</button></a>
                    </div>

                    <div class="ads" style="background-image: url(img/Mens.jpg); background-size: contain; background-repeat: no-repeat;">
                        <h1>Men's Clothing</h1>
                        <p>Style for every occasion.</p>
                        <a href=""><button>Shop now</button></a>
                    </div>
                    <div class="ads" style="background-image: url(img/Womens.jpg); background-size: contain; background-repeat: no-repeat;">
                        <h1>Women's Clothing</h1>
                        <p>Find your perfect fit.</p>
                        <a href=""><button>Shop now</button></a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include 'additional/footer.php'; ?>
    <script src="js/home.js"></script>
</body>
</html>