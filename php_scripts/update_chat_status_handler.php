<?php
	session_start();
	$con = mysqli_connect("", "", "", "");
    if (!$con) { die("Could not connect: " . mysqli_error()); }
	
	$status = 0;
	$sender_id = $_POST['to_user_id'];//getUserId($_SESSION['username']);
	$reciever_id = getUserId($_SESSION['username']);//$_POST['to_user_id'];
	$query = "UPDATE chatmsg SET status=$status WHERE (sender_id=$sender_id AND reciever_id=$reciever_id)";
	$statement = $con->prepare($query);
	$statement->execute();
	
	function getUserId($username) {
		$con = mysqli_connect("", "", "", "");
		if (!$con) { die("Could not connect: " . mysqli_error()); }
		$query = "SELECT id FROM users WHERE username = '$username'";
		$statement = $con->prepare($query);
		$statement->execute();
		$result = $statement->get_result();
		$statement->close();
		while($row = mysqli_fetch_assoc($result)) {
			return $row['id'];
		}
	}
?>