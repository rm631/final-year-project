<?php
	include('chat_history_handler.php');
	session_start();
	
	$con = mysqli_connect("", "", "", "");

    if (!$con) { die("Could not connect: " . mysqli_error()); }
	
	$reciever_id = mysqli_real_escape_string($con, $_POST['to_user_id']);
	$sender_id = mysqli_real_escape_string($con, getUserId($_SESSION['username']));
	$msg = mysqli_real_escape_string($con, $_POST['msg']);
	
	$query = "INSERT INTO chatmsg(reciever_id, sender_id, msg, status) VALUES ('$reciever_id', '$sender_id', '$msg', '1')";
		
	$statement = $con->prepare($query);
	if($statement === false) { 
		die('prepare() failed: ' . htmlspecialchars($con->error));
	}
	
	if($statement->execute()) {
		echo fetchUserChatHistory(getUserId($_SESSION['username']), $_POST['to_user_id']);
	}
	$statement->close();
?>