<?php
    include('../components/connect.php');

    // Fetch contact data from the database
    $sql = "SELECT id, name, email, number, message FROM contact";
    $result = mysqli_query($con, $sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles/index.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <title>Notification</title>
</head>
<body>
<?php include '../admin/dashboard_header.php'; ?>
   <section class="main_orders_container">
        <div class="main_container">
            <h1 class="main_title">Notification</h1>
            <div class="mains_products_box">
                <div class="notif_products_table">
<?php
    // Check if there are any contact entries
    if(mysqli_num_rows($result) > 0) {
        // Loop through each contact entry
        while($row = mysqli_fetch_assoc($result)) {
?>
                    <div class="notif-box">
                        <i class="fa-solid fa-circle-xmark notif-mark" data-contact-id="<?php echo $row['id']; ?>"></i>
                        <div class="notif-info">
                            <h1><?php echo $row['name']; ?></h1>
                            <p><?php echo $row['email']; ?></p>
                        </div>
                        <div class="notif-message">
                            <p><?php echo $row['message']; ?></p>
                        </div>
                    </div>
<?php
        }
    } else {
        echo "<p>No contact entries found.</p>";
    }
?>
                </div>
            </div>
        </div>
   </section>
   <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.notif-mark').click(function() {
                // Get the contact ID
                var contactId = $(this).data('contact-id');

                // Send an AJAX request to delete the contact entry
                $.ajax({
                    type: 'POST',
                    url: 'delete_contact.php',
                    data: { contact_id: contactId },
                    success: function(response) {
                        // Reload the page after successful deletion
                        location.reload();
                    },
                    error: function(xhr, status, error) {
                        console.error(xhr.responseText);
                    }
                });
            });
        });
    </script>
</body>
</html>
