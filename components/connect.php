<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once __DIR__ . '/session_timeout.php';

	$con = mysqli_connect('localhost','root','','grace_street1');
	if (!$con) {
		echo "Database Not Connected";
	}
 ?>
