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

// Fetch user and their role using a more reliable query
$user_query = mysqli_query($con, "SELECT u.*, r.role FROM grace_user u JOIN roles r ON u.roleID = r.roleID WHERE u.userID = '$user_id'");
$user_data = mysqli_fetch_assoc($user_query);

if (!$user_data || strtolower(trim($user_data['role'])) !== 'admin') {
    header('Location: ../login.php');
    exit();
}

$message = "";

// Handle Approve
if (isset($_POST['approve'])) {
    $app_id = $_POST['app_id'];
    
    // Fetch application details
    $app_query = mysqli_query($con, "SELECT * FROM seller_applications WHERE application_id = '$app_id'");
    if ($app_data = mysqli_fetch_assoc($app_query)) {
        $owner_name = $app_data['owner_name'];
        $username = $app_data['username'];
        $emailID = $app_data['emailID'];
        $password = $app_data['password'];
        $contact = $app_data['contact_number'];
        $address = $app_data['address'];

        // Get roleID for 'employee' (as per current system's seller/employee role)
        // Check if role 'seller' exists, otherwise use 'employee'
        $role_res = mysqli_query($con, "SELECT roleID FROM roles WHERE role = 'employee'");
        $role_row = mysqli_fetch_assoc($role_res);
        $roleID = $role_row['roleID'] ?? 2; // Default to 2 if not found

        // Move to grace_user
        $insert_user = $con->prepare("INSERT INTO grace_user (username, emailID, password, contact_number, address, is_active, roleID) VALUES (?, ?, ?, ?, ?, 1, ?)");
        $insert_user->bind_param("sisssi", $username, $emailID, $password, $contact, $address, $roleID);
        
        if ($insert_user->execute()) {
            // Update status in seller_applications
            mysqli_query($con, "UPDATE seller_applications SET status = 'approved' WHERE application_id = '$app_id'");
            $message = "Application approved successfully!";
        } else {
            $message = "Error creating user account.";
        }
    }
}

// Handle Reject
if (isset($_POST['reject'])) {
    $app_id = $_POST['app_id'];
    mysqli_query($con, "UPDATE seller_applications SET status = 'rejected' WHERE application_id = '$app_id'");
    $message = "Application rejected.";
}

// Fetch all applications
$apps_query = mysqli_query($con, "SELECT sa.*, e.email FROM seller_applications sa JOIN email e ON sa.emailID = e.emailID ORDER BY sa.created_at DESC");
$applications = mysqli_fetch_all($apps_query, MYSQLI_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Grace Street Clothing – Supplier Applications</title>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=DM+Serif+Display&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="./styles/supplier_management.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<style>
    .badge-rejected { background: #ff7675; color: white; }
    .badge-approved { background: #55efc4; color: #2d3436; }
</style>
<body>
  <?php include 'dashboard_header.php'; ?>    
  
  <main>
    <h1 class="page-title">Admin Dashboard</h1>
    <p class="page-subtitle">Manage your e-commerce platform</p>

    <div class="panel">
      <div class="panel-title">Supplier Applications</div>
      <div class="panel-subtitle">Review and manage supplier registration requests</div>

      <div class="app-cards">
        <?php if (empty($applications)): ?>
            <p style="padding: 20px; text-align: center; color: #666;">No applications found.</p>
        <?php endif; ?>

        <?php foreach ($applications as $app): ?>
        <div class="app-card">
          <?php 
            $status_class = "badge-pending";
            $status_text = "Pending Review";
            if($app['status'] == 'approved') { $status_class = "badge-approved"; $status_text = "Approved"; }
            if($app['status'] == 'rejected') { $status_class = "badge-rejected"; $status_text = "Rejected"; }
          ?>
          <span class="status-badge <?= $status_class ?>"><?= $status_text ?></span>
          <div class="app-name"><?= htmlspecialchars($app['owner_name']) ?></div>
          <div class="app-email"><?= htmlspecialchars($app['email']) ?></div>
          <div class="app-date">🕐 Applied on <?= date('Y-m-d', strtotime($app['created_at'])) ?></div>
          
          <div class="app-actions">
            <?php if ($app['status'] == 'pending'): ?>
            <form method="post" style="display:inline;" onsubmit="return confirm('Approve this seller?')">
                <input type="hidden" name="app_id" value="<?= $app['application_id'] ?>">
                <button type="submit" name="approve" class="btn-approve">✅ Approve</button>
            </form>
            <form method="post" style="display:inline;" onsubmit="return confirm('Reject this seller?')">
                <input type="hidden" name="app_id" value="<?= $app['application_id'] ?>">
                <button type="submit" name="reject" class="btn-reject">⊘ Reject</button>
            </form>
            <?php endif; ?>
            
            <a href="../<?= $app['document_path'] ?>" target="_blank">
                <button class="btn-docs">View Documents</button>
            </a>
          </div>
        </div>
        <?php endforeach; ?>

      </div>
    </div>
  </main>

  <?php if ($message): ?>
    <script>
        Swal.fire({
            text: '<?= $message ?>',
            icon: 'info',
            confirmButtonColor: '#000'
        });
    </script>
  <?php endif; ?>

</body>
</html>
