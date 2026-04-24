<!DOCTYPE html>
<head>
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
        <div class="stat-value">8,234</div>
        <div class="stat-change"><span class="up">+15.2%</span> from last month</div>
      </div>

      <div class="stat-card">
        <div class="stat-header">
          <span class="stat-label">Active Sellers</span>
          <span class="stat-icon">🏪</span>
        </div>
        <div class="stat-value">342</div>
        <div class="stat-change"><span class="up">+8.3%</span> from last month</div>
      </div>

      <div class="stat-card">
        <div class="stat-header">
          <span class="stat-label">Active Customers</span>
          <span class="stat-icon">📊</span>
        </div>
        <div class="stat-value">7,892</div>
        <div class="stat-change"><span class="up">+16.1%</span> from last month</div>
      </div>

      <div class="stat-card">
        <div class="stat-header">
          <span class="stat-label">Pending Applications</span>
          <span class="stat-icon">ℹ️</span>
        </div>
        <div class="stat-value">12</div>
        <div class="stat-change"><span class="up">+3</span> from last month</div>
      </div>
    </div>

    <!-- BOTTOM PANELS -->
    <div class="bottom-grid">

      <!-- Active Sellers -->
      <div class="panel">
        <div class="panel-header">
          <div>
            <div class="panel-title">Active Sellers</div>
            <div class="panel-subtitle">Top performing sellers</div>
          </div>
          <button class="panel-action">View All</button>
        </div>

        <div class="seller-item">
          <div class="seller-info">
            <div class="seller-name">My Fashion Store</div>
            <div class="seller-email">seller@example.com</div>
            <div class="seller-rating">⭐ 4.8 rating</div>
          </div>
          <div class="seller-sales">
            <div class="amount">₱542,345</div>
            <div class="label">Total Sales</div>
          </div>
        </div>

        <div class="seller-item">
          <div class="seller-info">
            <div class="seller-name">Urban Threads PH</div>
            <div class="seller-email">urban@example.com</div>
            <div class="seller-rating">⭐ 4.6 rating</div>
          </div>
          <div class="seller-sales">
            <div class="amount">₱389,120</div>
            <div class="label">Total Sales</div>
          </div>
        </div>

        <div class="seller-item">
          <div class="seller-info">
            <div class="seller-name">Bloom Streetwear</div>
            <div class="seller-email">bloom@example.com</div>
            <div class="seller-rating">⭐ 4.5 rating</div>
          </div>
          <div class="seller-sales">
            <div class="amount">₱274,890</div>
            <div class="label">Total Sales</div>
          </div>
        </div>
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
        </div>

        <div class="app-item">
          <div class="app-info">
            <div class="app-name">Carter's Fashion</div>
            <div class="app-email">alex@example.com</div>
            <div class="app-date">Applied 2026-04-10</div>
          </div>
          <div class="app-actions">
            <button class="btn-approve">✓</button>
            <button class="btn-reject">✕</button>
          </div>
        </div>

        <div class="app-item">
          <div class="app-info">
            <div class="app-name">Emma's Boutique</div>
            <div class="app-email">emma@example.com</div>
            <div class="app-date">Applied 2026-04-09</div>
          </div>
          <div class="app-actions">
            <button class="btn-approve">✓</button>
            <button class="btn-reject">✕</button>
          </div>
        </div>

        <div class="app-item">
          <div class="app-info">
            <div class="app-name">Lopez Clothing Co.</div>
            <div class="app-email">ryan@example.com</div>
            <div class="app-date">Applied 2026-04-08</div>
          </div>
          <div class="app-actions">
            <button class="btn-approve">✓</button>
            <button class="btn-reject">✕</button>
          </div>
        </div>

        <div class="app-item">
          <div class="app-info">
            <div class="app-name">Tres Marias Apparel</div>
            <div class="app-email">tresmarias@example.com</div>
            <div class="app-date">Applied 2026-04-07</div>
          </div>
          <div class="app-actions">
            <button class="btn-approve">✓</button>
            <button class="btn-reject">✕</button>
          </div>
        </div>

      </div>
    </div>
  </main>

</body>
</html>