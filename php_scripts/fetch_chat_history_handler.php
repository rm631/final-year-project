<?php
	/**
	 *	This handler is the handler for the handler (it echos out what the actual handler returns
	 *	chat_handler needs it to return and index wants it to echo...
	 *	..This is an incredibly janky way of making this work, but we'll deal with that later. 
	 */
	include('chat_history_handler.php');
	session_start();
	
	$con = mysqli_connect("", "", "", "");

    if (!$con) { die("Could not connect: " . mysqli_error()); }
	
	echo fetchUserChatHistory(getUserId($_SESSION['username']), $_POST['to_user_id']);
?>