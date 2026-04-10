<?php
include './components/connect.php';

if (isset($_SESSION['user-id'])) {
    $user_id = $_SESSION['user-id'];
} else {
    $user_id = '';
}

if (isset($_POST['submit'])) {
    $first_name = mysqli_real_escape_string($con, $_POST['first_name']);
    $last_name = mysqli_real_escape_string($con, $_POST['last_name']);
    $name = mysqli_real_escape_string($con, $_POST['name']);
    $email = mysqli_real_escape_string($con, $_POST['email']);

    $update_profile = $con->prepare("UPDATE `grace_user` SET first_name = ?, last_name = ?, username = ?, email = ? WHERE id = ?");
    $update_profile->bind_param("ssssi", $first_name, $last_name, $name, $email, $user_id);
    $update_profile->execute();

    echo "<script>alert('Profile updated successfully!');</script>";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GraceStreet/Update Profile</title>

    <!-- css connection -->
    <link rel="stylesheet" href="Css/style.css">

    <!-- jQuery UI CSS -->
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
</head>

<body>
    <?php include 'additional/header.php'; ?>
    <section>
        <div class="update-container">
            <form action="" method="post">
                <h1 style="text-align: center;">Update profile</h1>

                <label for="first_name">First Name</label>
                <input type="text" id="first_name" name="first_name" required placeholder="Enter your first name" maxlength="50" class="box" value="<?= $fetch_user["first_name"]; ?>">

                <label for="last_name">Last Name</label>
                <input type="text" id="last_name" name="last_name" required placeholder="Enter your last name" maxlength="50" class="box" value="<?= $fetch_user["last_name"]; ?>">

                <label for="username">Username</label>
                <input type="text" id="username" name="name" required placeholder="Enter your username" maxlength="20" class="box" value="<?= $fetch_user["username"]; ?>">

                <label for="email">Email</label>
                <input type="email" id="email" name="email" required placeholder="Enter your email" maxlength="50" class="box" value="<?= $fetch_user["email"]; ?>" oninput="this.value = this.value.replace(/\s/g, '')">
                
                <input type="submit" value="Update Profile" class="btn" name="submit">

            </form>

        </div>
    </section>
    <?php include 'additional/footer.php'; ?>
</body>

</html>
