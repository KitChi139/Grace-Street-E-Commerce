<?php
include('./components/connect.php');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$user_id = $_SESSION['user-id'] ?? $_SESSION['user_id'] ?? null;

$fetch_user = null;
if($user_id) {
    $select_user = mysqli_query($con, "SELECT u.*, r.role FROM grace_user u JOIN roles r ON u.roleID = r.roleID WHERE u.userID = '$user_id'") or die("query failed");
    if(mysqli_num_rows($select_user) > 0){
        $fetch_user = mysqli_fetch_assoc($select_user);
        
        // Role check for admin module
        $current_role = strtolower(trim($fetch_user['role'] ?? ''));
        if ($current_role !== 'admin') {
            header('Location: ../login.php');
            exit();
        }
    } else {
        header('Location: ../login.php');
        exit();
    }
} else {
    header('Location: ../login.php');
    exit();
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
    unset($_SESSION['user_id']);
    session_destroy();
    header('location:../login.php');
    exit();
}
?>


<header>
  <section class="flex">
          <div class="header-container">
            <div class="header-content">
              <a href="./overview.php" class="header-logo"><img src="./img/Logo.png" alt="" width="300px" height="auto" /></a>
              <nav>
                <a href="./overview.php">Home</a>
                <a href="./user_management.php">User Management</a>
                <a href="./supplier_management.php">Supplier Management</a>
                <a href="./locked_accounts.php">Locked Accounts</a>
                <a href="./audit_trail.php">Audit Trail</a>
                <a href="./settings.php">Settings</a>
              </nav>
              <div class="header-btn">
                <?php if (!$user_id): ?>
                <div class="login-register-btn">
                    <a href="../login.php"><button>Login</button></a>
                    <div>|</div>
                    <a href="../register.php"><button>Register</button></a>
                </div>
                <?php endif; ?>
                <div class="header-icons">
                    <?php if ($fetch_user): ?>
                    <span class="user-display-name" style="font-size: 18px; font-weight: 600; margin-right: 15px; color: #333;">
                        Hello, <?= htmlspecialchars($fetch_user['username']); ?>
                    </span>
                    <?php endif; ?>
                    <i class="fa-solid fa-user" onclick="togglePopup()" style="cursor: pointer; font-size: 20px;"></i>

                    <!-- Pop up -->
                    <div id="popupForm" class="pop-container">
                      <?php
                          if($user_id && $fetch_user) {
                              echo '<div class="pop-content" style="text-align: center;">
                                      <h2>Welcome<br><span>' . htmlspecialchars($fetch_user['username']) . '</span></h2>
                                      <a href="./update_profile.php"><button onclick="updateProfile()">Update Profile</button></a>
                                      <a href="overview.php?logout=' . $user_id . '" onclick="return confirm(\'Are you sure you want to logout?\')"><button class="logBtn">Log out</button></a>
                                    </div>';
                          } else {
                              echo '<div class="pop-content" style="text-align: center;">
                                      
                                      <a href="../login.php"><button style="cursor: pointer; width: 25vh; border: none; border-radius: 5px; padding: 10px 30px; background-color: black; color: white;">Login</button></a>
                                      <a href="../register.php"><button style="cursor: pointer; width: 25vh; border: none; border-radius: 5px; padding: 10px 30px; background-color: black; color: white;">Register</button></a>
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
