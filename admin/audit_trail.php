<?php
include_once '../components/connect.php';
include_once '../components/audit_logger.php';

// Ensure table exists before querying
ensure_audit_table_exists();

// Fetch audit trail with user details
$select_audit = mysqli_query($con, "
    SELECT a.*, u.username, r.role 
    FROM audit_trail a 
    JOIN grace_user u ON a.performed_by = u.userID 
    JOIN roles r ON u.roleID = r.roleID 
    ORDER BY a.timestamp DESC
") or die('Query failed');
?>
<!DOCTYPE html>
<head>
  <title>Grace Street Clothing – Audit Trail</title>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=DM+Serif+Display&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="./styles/audit_trail.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
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

    <!-- AUDIT TRAIL PANEL -->
    <div class="panel">
      <div class="panel-header">
        <div class="panel-title">Audit Trail</div>
        <div class="panel-subtitle">Track all administrative actions and system changes</div>
      </div>

      <table>
        <thead>
          <tr>
            <th>Action</th>
            <th>Performed By</th>
            <th>Target</th>
            <th>Timestamp</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <?php
          if (mysqli_num_rows($select_audit) > 0) {
              while ($row = mysqli_fetch_assoc($select_audit)) {
                  $status_class = 'badge-info';
                  if ($row['status'] == 'Critical') $status_class = 'badge-critical';
                  if ($row['status'] == 'Warning') $status_class = 'badge-warning';
                  
                  echo "<tr>";
                  echo "<td class='action-name'>" . htmlspecialchars($row['action']) . "</td>";
                  echo "<td class='performed-by'>" . htmlspecialchars($row['username']) . " (" . ucfirst($row['role']) . ")</td>";
                  echo "<td class='target'>" . ($row['target'] ? htmlspecialchars($row['target']) : '-') . "</td>";
                  echo "<td class='timestamp'>" . date('Y-m-d h:i A', strtotime($row['timestamp'])) . "</td>";
                  echo "<td><span class='badge $status_class'>" . htmlspecialchars($row['status']) . "</span></td>";
                  echo "</tr>";
              }
          } else {
              echo "<tr><td colspan='5' style='text-align:center;'>No administrative actions recorded yet.</td></tr>";
          }
          ?>
        </tbody>
      </table>
    </div>
  </main>

</body>
</html>
