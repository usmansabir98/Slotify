<?php
	ob_start(); // output buffer
	session_start();	// allows you to use session variables;

	$timezone = date_default_timezone_set("Asia/Karachi");

	$con = mysqli_connect("localhost", "root", "", "slotify");

	if(mysqli_connect_errno()){
		echo "Failed to connect: ".mysqli_connect_errno();
	}
?>