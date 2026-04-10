<?php


// Check if the user clicks on the sign out link
if(isset($_GET['logout'])) {
    // Destroy all session data
    session_destroy();
    // Redirect to the login page
    header('Location: ../login.php');
    exit;
}
?>

<header>
    <section class="dashboard_container">
        <div class="dashboard_box">
            <div class="dashboard_logo">
                <div class="logo">
                    <img src="assets/grace_logo.jpg" alt="" width="50px">
                </div>
                <div class="logo_text">
                    <h1>GRACE STREET</h1>
                    <p>CLOTHE YOURSELF WITH TRUTH</p>
                </div>
            </div>
        </div>
        <div class="dashboard_menu_box">
            <div class="dashboard_menu">
               <a href="../admin_employee/dashboard.php" class="home">
                    <div class="dashboard_sec">
                        <div class="dash_icon">
                            <i class="fa-solid fa-house"></i>
                        </div>
                        <div class="dash_text">
                            <h1>Dashboard</h1>
                        </div>
                    </div>
               </a>
                <a href="../admin_employee/products.php" class="products">
                    <div class="dashboard_sec">
                        <div class="dash_icon">
                            <i class="fa-solid fa-box"></i>
                        </div>
                        <div class="dash_text">
                            <h1>Products</h1>
                        </div>
                    </div>
                </a>
                <a href="../admin_employee/orders.php" class="orders">
                    <div class="dashboard_sec">
                        <div class="dash_icon">
                            <i class="fa-solid fa-clipboard"></i>
                        </div>
                        <div class="dash_text">
                            <h1>Orders</h1>
                        </div>
                    </div>
                </a>
                <a href="../admin_employee/notification.php" class="notif">
                    <div class="dashboard_sec">
                        <div class="dash_icon">
                            <i class="fa-solid fa-envelope"></i>
                        </div>
                        <div class="dash_text">
                            <h1>Notification</h1>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <div class="dashboard_logout_box">
            <div class="dashboard_menu">
            <a href="?logout" class="home" onclick="return confirm('Are you sure you want to log out?');">
                    <div class="dashboard_sec">
                        <div class="dash_icon">
                            <i class="fa-solid fa-arrow-right-from-bracket"></i>
                        </div>
                        <div class="dash_text">
                            <h1>Sign Out</h1>
                        </div>
                    </div>
                </a>
            </div>
        </div>
       
    </section>
</header>
