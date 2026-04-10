<?php
include('./components/connect.php');

$queries = [
    "ALTER TABLE `grace_user` ADD `first_name` VARCHAR(100) NOT NULL AFTER `id` ",
    "ALTER TABLE `grace_user` ADD `last_name` VARCHAR(100) NOT NULL AFTER `first_name` ",
    "ALTER TABLE `grace_user` CHANGE `password` `password` VARCHAR(255) NOT NULL",
    "ALTER TABLE `grace_user` ADD `is_active` TINYINT(1) NOT NULL DEFAULT 0",
    "ALTER TABLE `grace_user` ADD `activation_token` VARCHAR(255) NULL",
    "UPDATE `grace_user` SET `is_active` = 1"
];

foreach ($queries as $query) {
    if (mysqli_query($con, $query)) {
        echo "Success: $query <br>";
    } else {
        echo "Error: " . mysqli_error($con) . "<br>";
    }
}
?>
