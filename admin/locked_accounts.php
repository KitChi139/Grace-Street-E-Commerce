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

$user_query = mysqli_query($con, "SELECT u.*, r.role FROM grace_user u JOIN roles r ON u.roleID = r.roleID WHERE u.userID = '$user_id'");
$user_data = mysqli_fetch_assoc($user_query);

if (!$user_data || strtolower(trim($user_data['role'])) !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// Fetch locked accounts
$locked_query = mysqli_query($con, "SELECT * FROM locked_accounts ORDER BY locked_at DESC");
$locked_accounts = mysqli_fetch_all($locked_query, MYSQLI_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Grace Street Clothing – Locked Accounts</title>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=DM+Serif+Display&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="./styles/audit_trail.css"> <!-- Reusing audit trail styles -->
</head>
<body>
  <?php include 'dashboard_header.php'; ?>    
  
  <main>
    <h1 class="page-title">Admin Dashboard</h1>
    <p class="page-subtitle">Security & Account Monitoring</p>

    <div class="panel">
      <div class="panel-header">
        <div class="panel-title">Locked Accounts Log</div>
        <div class="panel-subtitle">History of accounts locked due to excessive login attempts</div>
      </div>

      <table>
        <thead>
          <tr>
            <th>Username</th>
            <th>Failed Attempts</th>
            <th>Locked At</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($locked_accounts)): ?>
            <tr>
                <td colspan="4" style="text-align: center; padding: 20px; color: #666;">No locked accounts recorded.</td>
            </tr>
          <?php else: ?>
            <?php foreach ($locked_accounts as $row): ?>
            <tr>
              <td class="action-name"><?= htmlspecialchars($row['username']) ?></td>
              <td><?= ($row['tries'] > 0) ? htmlspecialchars($row['tries']) . ' tries' : '<span style="color: #3498db; font-weight: 600;">Admin Lock</span>' ?></td>
              <td class="timestamp"><?= date('Y-m-d h:i A', strtotime($row['locked_at'])) ?></td>
              <td><span class="badge badge-critical">Locked</span></td>
            </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </main>
</body>
</html>
