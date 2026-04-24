<?php
include('../components/connect.php'); // Assuming connect.php is in the same directory
include('list_account.php'); // Include the list_account.php to fetch user data
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// HANDLE UNLOCK
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['unlock_user'])) {
    $id = $_POST['unlock_user'];
    $stmt = $con->prepare("UPDATE grace_user SET is_active = 1, login_attempts = 0 WHERE userID = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: user_management.php");
    exit();
}

// HANDLE LOCK
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['lock_user'])) {
    $id = $_POST['lock_user'];
    $stmt = $con->prepare("UPDATE grace_user SET is_active = 0 WHERE userID = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: user_management.php");
    exit();
}

// HANDLE EDIT
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_user'])) {
    $id = $_POST['user_id'];
    $name = $_POST['username'];
    $email = $_POST['email'];
    $role_name = $_POST['role'];

    // Update username
    $stmt = $con->prepare("UPDATE grace_user SET username = ? WHERE userID = ?");
    $stmt->bind_param("si", $name, $id);
    $stmt->execute();

    // Get roleID
    $role_res = mysqli_query($con, "SELECT roleID FROM roles WHERE role = '$role_name'");
    if ($role_row = mysqli_fetch_assoc($role_res)) {
        $roleID = $role_row['roleID'];
        $stmt = $con->prepare("UPDATE grace_user SET roleID = ? WHERE userID = ?");
        $stmt->bind_param("ii", $roleID, $id);
        $stmt->execute();
    }

    // Update email
    $user_email_res = mysqli_query($con, "SELECT emailID FROM grace_user WHERE userID = '$id'");
    if ($user_email_row = mysqli_fetch_assoc($user_email_res)) {
        $emailID = $user_email_row['emailID'];
        $stmt = $con->prepare("UPDATE email SET email = ? WHERE emailID = ?");
        $stmt->bind_param("si", $email, $emailID);
        $stmt->execute();
    }

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
                <div style="display: flex; gap: 5px;">
                  <button type='button' class="edit-btn" onclick="openEditModal(<?php echo htmlspecialchars(json_encode($user)); ?>)">Edit</button>
                  
                  <?php if ($user['is_active'] == 1): ?>
                    <form method='post' style='display:inline;' onsubmit="return confirm('Are you sure you want to lock this account?')">
                          <input type="hidden" name="lock_user" value="<?php echo htmlspecialchars($user['id']); ?>">
                          <button type='submit' style="background-color: #e74c3c;">Lock</button>
                      </form>
                  <?php else: ?>
                    <form method='post' style='display:inline;'>
                          <input type="hidden" name="unlock_user" value="<?php echo htmlspecialchars($user['id']); ?>">
                          <button type='submit' style="background-color: #2ecc71;">Unlock</button>
                      </form>
                  <?php endif; ?>    
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </main>

  <!-- Edit User Modal -->
  <div id="editModal" class="modal" style="display:none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5);">
    <div class="modal-content" style="background-color: white; margin: 10% auto; padding: 20px; border-radius: 8px; width: 400px; box-shadow: 0 4px 8px rgba(0,0,0,0.2);">
      <h2 style="margin-top: 0;">Edit User</h2>
      <form method="post">
        <input type="hidden" name="user_id" id="edit_user_id">
        <div style="margin-bottom: 15px;">
          <label style="display: block; margin-bottom: 5px;">Username</label>
          <input type="text" name="username" id="edit_username" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;" required>
        </div>
        <div style="margin-bottom: 15px;">
          <label style="display: block; margin-bottom: 5px;">Email</label>
          <input type="email" name="email" id="edit_email" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;" required>
        </div>
        <div style="margin-bottom: 20px;">
          <label style="display: block; margin-bottom: 5px;">Role</label>
          <select name="role" id="edit_role" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
            <option value="admin">Admin</option>
            <option value="employee">Employee</option>
            <option value="user">User</option>
          </select>
        </div>
        <div style="text-align: right; gap: 10px; display: flex; justify-content: flex-end;">
          <button type="button" onclick="closeEditModal()" style="background-color: #95a5a6; padding: 8px 16px;">Cancel</button>
          <button type="submit" name="edit_user" style="background-color: #3498db; padding: 8px 16px;">Save Changes</button>
        </div>
      </form>
    </div>
  </div>

  <script>
    function openEditModal(user) {
      document.getElementById('edit_user_id').value = user.id;
      document.getElementById('edit_username').value = user.name;
      document.getElementById('edit_email').value = user.email;
      document.getElementById('edit_role').value = user.role;
      document.getElementById('editModal').style.display = 'block';
    }

    function closeEditModal() {
      document.getElementById('editModal').style.display = 'none';
    }

    window.onclick = function(event) {
      if (event.target == document.getElementById('editModal')) {
        closeEditModal();
      }
    }
  </script>

</body>
</html>
