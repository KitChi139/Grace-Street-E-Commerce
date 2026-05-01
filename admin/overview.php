<?php
include('../components/connect.php');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check admin role
$user_id = $_SESSION['user-id'] ?? $_SESSION['user_id'] ?? null;
if (!$user_id) {
    header('Location: ../login.php');
    exit();
}

// Fetch user and their role using a more reliable query
$user_query = mysqli_query($con, "SELECT u.*, r.role FROM grace_user u JOIN roles r ON u.roleID = r.roleID WHERE u.userID = '$user_id'");
$user_data = mysqli_fetch_assoc($user_query);

$current_role = strtolower(trim($user_data['role'] ?? ''));
if (!$user_data || $current_role !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// Handle quick actions from overview
if (isset($_POST['approve_quick']) || isset($_POST['reject_quick'])) {
    $app_id = $_POST['app_id'];
    if (isset($_POST['approve_quick'])) {
        $app_query = mysqli_query($con, "SELECT * FROM seller_applications WHERE application_id = '$app_id'");
        if ($app_data = mysqli_fetch_assoc($app_query)) {
            $owner_name = $app_data['owner_name'];
            $username = $app_data['username'];
            $emailID = $app_data['emailID'];
            $password = $app_data['password'];
            $contact = $app_data['contact_number'];
            $address = $app_data['address'];
            $role_res = mysqli_query($con, "SELECT roleID FROM roles WHERE role = 'employee'");
            $role_row = mysqli_fetch_assoc($role_res);
            $roleID = $role_row['roleID'] ?? 2;

            $insert_user = $con->prepare("INSERT INTO grace_user (username, emailID, password, contact_number, address, is_active, roleID) VALUES (?, ?, ?, ?, ?, 1, ?)");
            $insert_user->bind_param("sisssi", $username, $emailID, $password, $contact, $address, $roleID);
            if ($insert_user->execute()) {
                mysqli_query($con, "UPDATE seller_applications SET status = 'approved' WHERE application_id = '$app_id'");
            }
        }
    } else {
        mysqli_query($con, "UPDATE seller_applications SET status = 'rejected' WHERE application_id = '$app_id'");
    }
    header("Location: overview.php");
    exit();
}

// Fetch stats
$total_users_query = mysqli_query($con, "SELECT COUNT(*) as count FROM grace_user");
$total_users = mysqli_fetch_assoc($total_users_query)['count'];

$active_sellers_query = mysqli_query($con, "SELECT COUNT(*) as count FROM grace_user u JOIN roles r ON u.roleID = r.roleID WHERE r.role = 'employee' AND u.is_active = 1");
$active_sellers = mysqli_fetch_assoc($active_sellers_query)['count'];

$pending_apps_query = mysqli_query($con, "SELECT COUNT(*) as count FROM seller_applications WHERE status = 'pending'");
$pending_count = mysqli_fetch_assoc($pending_apps_query)['count'];

// Fetch pending applications for the list
$pending_list_query = mysqli_query($con, "SELECT sa.*, e.email FROM seller_applications sa JOIN email e ON sa.emailID = e.emailID WHERE sa.status = 'pending' ORDER BY sa.created_at DESC LIMIT 4");
$pending_apps = mysqli_fetch_all($pending_list_query, MYSQLI_ASSOC);

// Fetch active sellers for the list
$active_sellers_list_query = mysqli_query($con, "SELECT u.username, e.email FROM grace_user u JOIN email e ON u.emailID = e.emailID JOIN roles r ON u.roleID = r.roleID WHERE r.role = 'employee' AND u.is_active = 1 ORDER BY u.userID DESC LIMIT 3");
$active_sellers_list = mysqli_fetch_all($active_sellers_list_query, MYSQLI_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Grace Street Clothing – Admin Dashboard</title>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=DM+Serif+Display&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="./styles/overview.css">
</head>
<body>

  <!-- NAVBAR
  <nav>
    <div class="nav-brand">
      <div class="dot"></div>
      Grace Street Clothing
    </div>

    <div class="nav-search">
      <div class="nav-search-inner">
        🔍 &nbsp;Search...
      </div>
    </div>

    <div class="nav-links">
      <a href="#">Home</a>
      <a href="#">Shop</a>
      <a href="#">Admin</a>
    </div>

    <div class="nav-right">
      <div class="nav-role">Admin &nbsp;▾</div>
      <div class="nav-user">AdminUser &nbsp;👤</div>
    </div>
  </nav> -->
  <?php include 'dashboard_header.php'; ?>    
  <!-- MAIN CONTENT -->
  <main>
    <h1 class="page-title">Admin Dashboard</h1>
    <p class="page-subtitle">Manage your e-commerce platform</p>

    <!-- TABS
    <div class="tabs">
      <div class="tab">👥 Overview</div>
      <div class="tab">🛡️ User Management</div>
      <div class="tab">📦 Supplier Applications</div>
      <div class="tab active">📈 Audit Trail</div>
      <div class="tab">⚙️ Settings</div>
    </div> -->

    <!-- STAT CARDS -->
    <div class="stat-grid">
      <div class="stat-card">
        <div class="stat-header">
          <span class="stat-label">Total Users</span>
          <span class="stat-icon">👥</span>
        </div>
        <div class="stat-value"><?= number_format($total_users) ?></div>
        <div class="stat-change">Platform growth</div>
      </div>

      <div class="stat-card">
        <div class="stat-header">
          <span class="stat-label">Active Sellers</span>
          <span class="stat-icon">🏪</span>
        </div>
        <div class="stat-value"><?= number_format($active_sellers) ?></div>
        <div class="stat-change">Registered sellers</div>
      </div>

      <div class="stat-card">
        <div class="stat-header">
          <span class="stat-label">Total Products</span>
          <span class="stat-icon">📊</span>
        </div>
        <?php 
          $prod_count_query = mysqli_query($con, "SELECT COUNT(*) as count FROM product");
          $prod_count = mysqli_fetch_assoc($prod_count_query)['count'] ?? 0;
        ?>
        <div class="stat-value"><?= number_format($prod_count) ?></div>
        <div class="stat-change">Items in store</div>
      </div>

      <div class="stat-card">
        <div class="stat-header">
          <span class="stat-label">Pending Applications</span>
          <span class="stat-icon">ℹ️</span>
        </div>
        <div class="stat-value"><?= number_format($pending_count) ?></div>
        <div class="stat-change">Awaiting review</div>
      </div>
    </div>

    <!-- BOTTOM PANELS -->
    <div class="bottom-grid">

      <!-- Active Sellers -->
      <div class="panel">
        <div class="panel-header">
          <div>
            <div class="panel-title">Active Sellers</div>
            <div class="panel-subtitle">Recently approved sellers</div>
          </div>
          <a href="user_management.php"><button class="panel-action">View All</button></a>
        </div>

        <?php if (empty($active_sellers_list)): ?>
            <p style="padding: 20px; text-align: center; color: #666;">No active sellers yet.</p>
        <?php endif; ?>

        <?php foreach ($active_sellers_list as $seller): ?>
        <div class="seller-item">
          <div class="seller-info">
            <div class="seller-name"><?= htmlspecialchars($seller['username']) ?></div>
            <div class="seller-email"><?= htmlspecialchars($seller['email']) ?></div>
            <div class="seller-rating">⭐ New Seller</div>
          </div>
          <div class="seller-sales">
            <div class="amount">₱0</div>
            <div class="label">Total Sales</div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>

      <!-- Pending Applications -->
      <div class="panel">
        <div class="panel-header">
          <div class="pending-header">
            <span class="pending-icon">🟠</span>
            <div>
              <div class="panel-title">Pending Applications</div>
              <div class="panel-subtitle">Review new supplier requests</div>
            </div>
          </div>
          <a href="supplier_management.php"><button class="panel-action">View All</button></a>
        </div>

        <?php if (empty($pending_apps)): ?>
            <p style="padding: 20px; text-align: center; color: #666;">No pending applications.</p>
        <?php endif; ?>

        <?php foreach ($pending_apps as $app): ?>
        <div class="app-item">
          <div class="app-info">
            <div class="app-name"><?= htmlspecialchars($app['owner_name']) ?></div>
            <div class="app-email"><?= htmlspecialchars($app['email']) ?></div>
            <div class="app-date">Applied <?= date('Y-m-d', strtotime($app['created_at'])) ?></div>
          </div>
          <div class="app-actions">
            <form method="post" style="display:inline;">
                <input type="hidden" name="app_id" value="<?= $app['application_id'] ?>">
                <button type="submit" name="approve_quick" class="btn-approve" title="Approve">✓</button>
                <button type="submit" name="reject_quick" class="btn-reject" title="Reject">✕</button>
            </form>
          </div>
        </div>
        <?php endforeach; ?>

      </div>
    </div>
  </main>

</body>
</html>
