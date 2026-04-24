<!DOCTYPE html>
<head>
  <title>Grace Street Clothing – Admin Panel</title>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=DM+Serif+Display&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="account_management.css">
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
      <button class="cart-btn">
        🛒
        <span class="cart-badge">3</span>
      </button>
      <button class="user-btn">👤</button>
    </div>
  </nav> -->
  <?php include 'dashboard_header.php'; ?>    
  <!-- MAIN -->
  <main>
    <div class="two-col">

      <!-- LEFT: Pending Seller Approvals -->
      <div class="panel">
        <div class="panel-header">
          <div class="panel-header-title">🟠 Pending Seller Approvals</div>
          <div class="panel-header-sub">Review and approve new sellers</div>
        </div>

        <div class="applicant-row">
          <div class="applicant-info">
            <div class="app-name">Alex Carter</div>
            <div class="app-email">alex@example.com</div>
            <div class="app-date">2026-02-21</div>
          </div>
          <div class="applicant-actions">
            <button class="btn-approve">Approve</button>
            <button class="btn-reject">Reject</button>
          </div>
        </div>

        <div class="applicant-row">
          <div class="applicant-info">
            <div class="app-name">Emma Davis</div>
            <div class="app-email">emma@example.com</div>
            <div class="app-date">2026-02-20</div>
          </div>
          <div class="applicant-actions">
            <button class="btn-approve">Approve</button>
            <button class="btn-reject">Reject</button>
          </div>
        </div>

        <div class="applicant-row">
          <div class="applicant-info">
            <div class="app-name">Ryan Lopez</div>
            <div class="app-email">ryan@example.com</div>
            <div class="app-date">2026-02-19</div>
          </div>
          <div class="applicant-actions">
            <button class="btn-approve">Approve</button>
            <button class="btn-reject">Reject</button>
          </div>
        </div>

      </div>

      <!-- RIGHT: Product Management -->
      <div class="panel">
        <div class="product-panel-header">
          <div class="product-panel-titles">
            <div class="ptitle">Product Management</div>
            <div class="psub">Manage platform products</div>
          </div>
          <button class="btn-add-product">Add Product</button>
        </div>

        <div class="menu-list">
          <div class="menu-item">
            <span class="menu-item-icon">📦</span>
            View All Products
          </div>
          <div class="menu-item">
            <span class="menu-item-icon">✏️</span>
            Manage Categories
          </div>
          <div class="menu-item">
            <span class="menu-item-icon">🕐</span>
            Review Pending Products
          </div>
          <div class="menu-item">
            <span class="menu-item-icon">👥</span>
            Manage Users
          </div>
        </div>

      </div>

    </div>
  </main>

</body>
</html>