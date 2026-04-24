<!DOCTYPE html>
<head>
  <title>Grace Street Clothing – Settings</title>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=DM+Serif+Display&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="./styles/edit_profile.css">
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

    <!-- TWO PANELS -->
    <div class="two-col">

      <!-- Admin Profile -->
      <div class="panel">
        <div class="panel-title">Admin Profile</div>
        <div class="panel-subtitle">Update your admin account details</div>

        <div class="form-group">
          <label>Full Name</label>
          <input type="text" value="Admin User" />
        </div>

        <div class="form-group">
          <label>Email Address</label>
          <input type="email" value="admin@gracestreet.com" />
        </div>

        <div class="form-group">
          <label>Phone Number</label>
          <input type="tel" value="+63 (912) 345-6789" />
        </div>

        <button class="btn-primary">Save Changes</button>
      </div>

      <!-- Security -->
      <div class="panel">
        <div class="panel-title">Security</div>
        <div class="panel-subtitle">Change your admin password</div>

        <div class="form-group">
          <label>Current Password</label>
          <input type="password" placeholder="" />
        </div>

        <div class="form-group">
          <label>New Password</label>
          <input type="password" placeholder="" />
        </div>

        <div class="form-group">
          <label>Confirm Password</label>
          <input type="password" placeholder="" />
        </div>

        <button class="btn-primary">Update Password</button>
      </div>

    </div>
  </main>

</body>
</html>
