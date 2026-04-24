<?php
include('../components/connect.php'); // Assuming connect.php is in the same directory
include('list_account.php'); // Include the list_account.php to fetch user data
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// HANDLE UNLOCK FIRST
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['unlock_user'])) {

    $id = $_POST['unlock_user'];

    $stmt = $con->prepare("UPDATE grace_user SET is_active = 1 WHERE userID = ?");
    $stmt->bind_param("i", $id);

    $stmt->execute();

    header("Location: user_management.php");
    exit();
}

?>


<!DOCTYPE html>
<head>
  <title>Grace Street Clothing – User Management</title>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=DM+Serif+Display&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="./styles/user_management.css">
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
  <!-- MAIN -->
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

    <!-- USER MANAGEMENT PANEL -->
    <div class="panel">
      <div class="panel-header">
        <div class="panel-title">User Management</div>
        <div class="panel-subtitle">Manage all customers and sellers on the platform</div>
      </div>

      <table>
        <thead>
          <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Role</th>
            <th>Status</th>
            <th>Joined</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($users as $user): ?>
            <tr>
              <td><?php echo htmlspecialchars($user['name']); ?></td>
              <td><?php echo htmlspecialchars($user['email']); ?></td>
              <td><?php echo htmlspecialchars($user['role']); ?></td>
              <?php if ($user['is_active'] == 1): ?>
                <td style='color:green;'>Active</td>
              <?php else: ?>
                <td style='color:red;'>Locked</td>
              <?php endif; ?>
              <td><?php echo htmlspecialchars($user['joined_date'] ?? 'N/A'); ?></td>
              <td>
                <?php if ($user['is_active'] == 1): ?>
                  -
                <?php else: ?>
                  <form method='post' style='display:inline;'>
                        <input type="hidden" name="unlock_user" value="<?php echo htmlspecialchars($user['id']); ?>">
                        <button type='submit'>Unlock</button>
                    </form>
                <?php endif; ?>    
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </main>

</body>
</html>
