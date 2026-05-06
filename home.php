<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grace Street</title>

    <!-- css connection -->
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body>
    <?php include 'additional/header.php'; ?>
    <?php include 'chat.php'; ?>

    <section>
  <div class="home-content">

    <!-- Slider -->
    <div class="slider-container">
      <div class="slider-text">
        <h1>GRACE STREET<br><span style="font-weight:300;font-size:1.5rem;letter-spacing:0.2em;">Clothing Shop</span></h1>
      </div>
      <div class="slider">
        <img src="uploads/images/1.jpg" alt="Collection 1">
        <img src="uploads/images/2.jpg" alt="Collection 2">
        <img src="uploads/images/5.jpg" alt="Collection 3">
        <img src="uploads/images/6.jpg" alt="Collection 4">
      </div>
    </div>

    <!-- Category Boxes -->
    <div class="advertise-container">
      <div class="advertise-content">
        <div class="ads" style="background-image:url(img/Products.jpg);">
          <h1>All Products</h1>
          <p>Get 20% OFF on selected products.</p>
          <a href="products.php"><button>Shop now</button></a>
        </div>
        <div class="ads" style="background-image:url(img/Mens.jpg);">
          <h1>Men's Clothing</h1>
          <p>Style for every occasion.</p>
          <a href="products.php?category=mens"><button>Shop now</button></a>
        </div>
        <div class="ads" style="background-image:url(img/Womens.jpg);">
          <h1>Women's Clothing</h1>
          <p>Find your perfect fit.</p>
          <a href="products.php?category=womens"><button>Shop now</button></a>
        </div>
      </div>
    </div>

  </div>
</section>

    <?php include 'additional/footer.php'; ?>
    <script src="js/home.js"></script>
</body>
</html>
