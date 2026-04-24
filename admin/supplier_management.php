<!DOCTYPE html>
<head>
  <title>Grace Street Clothing – Supplier Applications</title>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=DM+Serif+Display&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="./styles/supplier_management.css">
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

    <!-- SUPPLIER APPLICATIONS PANEL -->
    <div class="panel">
      <div class="panel-title">Supplier Applications</div>
      <div class="panel-subtitle">Review and manage supplier registration requests</div>

      <div class="app-cards">

        <!-- Card 1 -->
        <div class="app-card">
          <span class="status-badge badge-verified">Verified</span>
          <div class="app-name">Carter's Fashion</div>
          <div class="app-contact">Alex Carter</div>
          <div class="app-email">alex@example.com</div>
          <div class="app-date">🕐 Applied on 2026-04-10</div>
          <div class="app-actions">
            <button class="btn-approve">✅ Approve Application</button>
            <button class="btn-reject">⊘ Reject Application</button>
            <button class="btn-docs">View Documents</button>
          </div>
        </div>

        <!-- Card 2 -->
        <div class="app-card">
          <span class="status-badge badge-pending">Pending Review</span>
          <div class="app-name">Emma's Boutique</div>
          <div class="app-contact">Emma Davis</div>
          <div class="app-email">emma@example.com</div>
          <div class="app-date">🕐 Applied on 2026-04-09</div>
          <div class="app-actions">
            <button class="btn-approve">✅ Approve Application</button>
            <button class="btn-reject">⊘ Reject Application</button>
            <button class="btn-docs">View Documents</button>
          </div>
        </div>

        <!-- Card 3 -->
        <div class="app-card">
          <span class="status-badge badge-pending">Pending Review</span>
          <div class="app-name">Lopez Clothing Co.</div>
          <div class="app-contact">Ryan Lopez</div>
          <div class="app-email">ryan@example.com</div>
          <div class="app-date">🕐 Applied on 2026-04-08</div>
          <div class="app-actions">
            <button class="btn-approve">✅ Approve Application</button>
            <button class="btn-reject">⊘ Reject Application</button>
            <button class="btn-docs">View Documents</button>
          </div>
        </div>

      </div>
    </div>
  </main>

</body>
</html>