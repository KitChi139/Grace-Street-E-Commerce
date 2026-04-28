<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles/index.css">
    <title>Admin Login</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <?php
    if (isset($_GET['timeout'])) {
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'info',
                    title: 'Session Expired',
                    text: 'Your session has timed out due to inactivity. Please login again.',
                    confirmButtonColor: '#000'
                });
            });
        </script>";
    }
    ?>
</head>
<body>
    <div class="admin_login_container">
        <div class="admin_login_box">
            <div class="admin_login_title">
                <h1>ADMIN PANEL</h1>
                <P>Login to your account</P>
            </div>
           <form action="">
                <div class="admin_login_form">
                    <div class="admin_login_input">
                        <div class="admin_input">
                            <label for="" class="admin_text">Email</label>
                            <input type="text" required>
                        </div>
                        <div class="admin_input">
                            <label for="" class="admin_text">Password</label>
                            <input type="password" required>
                        </div>
                        <div class="adminbtn">
                           <button class="">Login</button>
                        </div>
                    </div>
                    
                </div>
           </form>
        </div>
    </div>
</body>
</html>
