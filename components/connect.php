<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

	$con = mysqli_connect('localhost','root','','grace_street1');
	if (!$con) {
		echo "Database Not Connected";
	}
 ?>
