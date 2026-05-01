<?php
// dashboard_header.php
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
/* =========================
   SIDEBAR LAYOUT
========================= */
body {
  margin: 0;
  font-family: Arial, sans-serif;
}

.sidebar {
  width: 250px;
  height: 100vh;
  position: fixed;
  left: 0;
  top: 0;
  background: #ffffff;
  color: white;
  padding-top: 20px;
  display: flex;
  flex-direction: column;
  padding: 20px;
  box-shadow: rgba(0,0,0,0.24) 0px 3px 8px;
}

.sidebar-logo {
  text-align: center;
  margin-bottom: 20px;
}

.sidebar-logo img {
  width: 180px;
}

/* NAV LINKS */
.sidebar nav {
  display: flex;
  flex-direction: column;
}

.sidebar nav a,
.dropdown-btn {
  padding: 15px;
  text-decoration: none;
  color: black;
  font-size: 14px;
  border: none;
  background: none;
  text-align: left;
  cursor: pointer;
  width: 100%;
}

.sidebar nav a:hover,
.dropdown-btn:hover {
  background: #c2c2c2;
}

/* =========================
   ORDERS DROPDOWN
========================= */
.dropdown-btn {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.dropdown-container {
  display: none;
  flex-direction: column;
  background: #ffffff;
}

.dropdown-container a {
  padding-left: 40px;
  font-size: 13px;
}

/* =========================
   USER / ICON AREA
========================= */
.sidebar-bottom {
  margin-top: auto;
  padding: 15px;
  border-top: 1px solid #333;
  position: relative;
}

.user-icon {
  cursor: pointer;
  font-size: 20px;
}

/* POPUP */
.pop-container {
  background-color: white;
  padding: 15px;
  border-radius: 5px;
  position: absolute;
  bottom: 60px;
  left: 20px;
  display: none;
  box-shadow: rgba(0,0,0,0.24) 0px 3px 8px;
}

.pop-container button {
  width: 100%;
  padding: 10px;
  margin: 5px 0;
  border: none;
  background: black;
  color: white;
  cursor: pointer;
}
</style>

<!-- SIDEBAR -->
<div class="sidebar">

  <!-- LOGO -->
  <div class="sidebar-logo">
    <a href="./1dashboard.php">
      <img src="./img/Logo.png" alt="Logo">
    </a>
  </div>

  <!-- NAVIGATION -->
  <nav>
    <a href="./1dashboard.php"><i class="fa fa-chart-line"></i> Dashboard</a>

    <!-- ORDERS DROPDOWN -->
    <button class="dropdown-btn" onclick="toggleOrders()">
      <span><i class="fa fa-box"></i> Orders</span>
      <i class="fa fa-chevron-down"></i>
    </button>

    <div class="dropdown-container" id="ordersMenu">
      <a href="./2porders.php"><i class="fa fa-clock"></i> Pending Orders</a>
      <a href="./3rtransit.php"><i class="fa fa-truck"></i> Ready for Transit</a>
      <a href="./4intransit.php"><i class="fa fa-road"></i> In Transit</a>
      <a href="./5completed.php"><i class="fa fa-check-circle"></i> Completed</a>
    </div>

    <a href="./6logs.php"><i class="fa fa-file-alt"></i> Logs</a>
    <a href="../logout.php"><i class="fa fa-sign-out-alt"></i> Logout</a>
  </nav>

  <!-- USER SECTION -->
  <div class="sidebar-bottom">
    <i class="fa-solid fa-user user-icon" onclick="togglePopup()"></i>

    <div id="popupForm" class="pop-container">
      <a href="../login.php"><button>Login</button></a>
      <a href="../register.php"><button>Register</button></a>
    </div>
  </div>
</div>

<script>
function toggleOrders() {
  var menu = document.getElementById("ordersMenu");
  menu.style.display = (menu.style.display === "flex") ? "none" : "flex";
}

function togglePopup() {
  var popup = document.getElementById("popupForm");
  popup.style.display = (popup.style.display === "block") ? "none" : "block";
}
</script>