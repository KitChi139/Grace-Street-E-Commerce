<?php
include('./components/connect.php');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$user_id = isset($_SESSION['user-id']) ? $_SESSION['user-id'] : null;

$fetch_user = null;
$headerRole = '';
if($user_id) {
    $select_user = mysqli_query($con, "SELECT u.*, r.role FROM grace_user u JOIN roles r ON u.roleID = r.roleID WHERE u.userID = '$user_id'") or die("query failed");
    if(mysqli_num_rows($select_user) > 0){
        $fetch_user = mysqli_fetch_assoc($select_user);
        
        $headerRole = strtolower(trim($fetch_user['role']));
        if (!in_array($headerRole, ['user', 'courier'], true)) {
            header('Location: ./login.php');
            exit();
        }
    } else {
        header('Location: ./login.php');
        exit();
    }
}

function getCartItemCount($user_id, $con) {
    $count_query = mysqli_query($con, "SELECT COUNT(*) AS count FROM cart WHERE userID = '$user_id'");
    $count_row = mysqli_fetch_assoc($count_query);
    return $count_row['count'];
}
function getWishListItemCount($user_id, $con) {
    $count_query = mysqli_query($con, "SELECT COUNT(*) AS count FROM wishlist WHERE userID = '$user_id'");
    $count_row = mysqli_fetch_assoc($count_query);
    return $count_row['count'];
}

if(isset($_GET['logout'])){
    unset($_SESSION['user-id']); 
    session_destroy();
    header('location:login.php');
}
?>


<header>
  <section class="flex">
          <div class="header-container">
            <div class="header-content" style="<?= ($headerRole === 'courier') ? 'justify-content: flex-end;' : ''; ?>">
              <?php if ($headerRole !== 'courier'): ?>
              <a href="./home.php" class="header-logo"><img src="./img/Logo.png" alt="" width="300px" height="auto" /></a>
              <nav>
                <a href="./home.php">Home</a>
                <a href="./products.php">Shop All</a>
                <a href="./order.php">Order</a>
                <a href="./contact.php">Contact</a>
                <a href="./reviews.php">Reviews</a>
                <a href="./seller_register.php" style="color: #d35400; font-weight: bold;">Sell on Grace Street</a>
              </nav>
              <?php endif; ?>
              <div class="header-btn">
                <?php if (!isset($user_id)): ?>
                <div class="login-register-btn">
                    <a href="./login.php"><button>Login</button></a>
                    <div>|</div>
                    <a href="./register.php"><button>Register</button></a>
                </div>
                <?php endif; ?>
                <div class="header-icons">
                    <?php if ($fetch_user): ?>
                    <span class="user-display-name" style="font-size: 18px; font-weight: 600; margin-right: 15px; color: #333;">
                        Hello, <?= htmlspecialchars(!empty($fetch_user['first_name']) ? $fetch_user['first_name'] : $fetch_user['username']); ?>
                    </span>
                    <?php endif; ?>
                    
                    <?php if ($headerRole !== 'courier'): ?>
                    <a href="./wishlist.php" class="icon-badge-wrap"><i class="fa-solid fa-heart"></i><?php if (isset($user_id) && getWishListItemCount($user_id, $con) > 0): ?><span class="icon-badge"><?php echo getWishListItemCount($user_id, $con); ?></span><?php endif; ?></a>
                    <a href="./cart.php" class="icon-badge-wrap"><i class="fa-solid fa-cart-shopping"></i><?php if (isset($user_id) && getCartItemCount($user_id, $con) > 0): ?><span class="icon-badge"><?php echo getCartItemCount($user_id, $con); ?></span><?php endif; ?></a>
                    <?php endif; ?>
                    
                    <i class="fa-solid fa-user" onclick="togglePopup()" style="cursor: pointer; font-size: 20px; margin-left: 12px;"></i>

                    <!-- Pop up -->
                    <div id="popupForm" class="pop-container">
                      <?php
                          if(isset($user_id) && $fetch_user) {
                              $displayName = !empty($fetch_user['first_name']) ? $fetch_user['first_name'] : $fetch_user['username'];
                              echo '<div class="pop-content" style="text-align: center;">
                                      <h2>Welcome<br><span>' . htmlspecialchars($displayName) . '</span></h2>
                                      <a href="./update_profile.php"><button onclick="updateProfile()">Update Profile</button></a>
                                      <a href="home.php?logout=' . $user_id . '" onclick="return confirm(\'Are you sure you want to logout?\')"><button class="logBtn">Log out</button></a>
                                    </div>';
                          } else {
                              echo '<div class="pop-content" style="text-align: center;">
                                  <a href="./login.php"><button>Login</button></a>
                                  <a href="./register.php"><button>Register</button></a>
                                  </div>';
                          }
                      ?>
                    </div>

                </div>
              </div>
            </div>
          </div>
        </section>

  <script>
    function togglePopup() {
      var popup = document.getElementById("popupForm");
      if (popup.style.display === "none" || popup.style.display === "") {
        popup.style.display = "block";
      } else {
        popup.style.display = "none";
      }
    }
  </script>
  <script src="../js/script.js"></script>
</header>
