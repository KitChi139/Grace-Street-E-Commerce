<?php
include('../components/connect.php');

?>
<!DOCTYPE html>
<head>
  <title>Grace Street Clothing – Audit Trail</title>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=DM+Serif+Display&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="./styles/audit_trail.css">
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
          <tr>
            <td class="action-name">User Account Terminated</td>
            <td class="performed-by">Admin User</td>
            <td class="target">Mike Johnson (Customer)</td>
            <td class="timestamp">2026-04-11 10:30 AM</td>
            <td><span class="badge badge-critical">Critical</span></td>
          </tr>
          <tr>
            <td class="action-name">Seller Application Approved</td>
            <td class="performed-by">Admin User</td>
            <td class="target">Sarah's Store (Seller)</td>
            <td class="timestamp">2026-04-11 09:15 AM</td>
            <td><span class="badge badge-info">Info</span></td>
          </tr>
          <tr>
            <td class="action-name">User Account Modified</td>
            <td class="performed-by">Admin User</td>
            <td class="target">John Doe (Customer)</td>
            <td class="timestamp">2026-04-10 04:45 PM</td>
            <td><span class="badge badge-warning">Warning</span></td>
          </tr>
          <tr>
            <td class="action-name">Seller Application Rejected</td>
            <td class="performed-by">Admin User</td>
            <td class="target">Test Store (Seller)</td>
            <td class="timestamp">2026-04-10 02:20 PM</td>
            <td><span class="badge badge-info">Info</span></td>
          </tr>
        </tbody>
      </table>
    </div>
  </main>

</body>
</html>
