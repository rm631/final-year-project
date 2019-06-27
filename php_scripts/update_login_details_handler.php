<?php 
	session_start();
	
	$con = mysqli_connect("", "", "", "");
    if (!$con) { die("Could not connect: " . mysqli_error()); }
	
	$query = "UPDATE logindetails SET last_activity = now() WHERE login_details_id = '".getUserId($_SESSION["username"])."'";
	$statement = $con->prepare($query);
	$statement->execute();
	
	/**
	 *	Usernames *should* be unique (See register_handler.php, it adds an int to the end 
	 *	of a username if it already exists in the db), hence we shouldn't need to worry
	 *	about this kicking back multiple IDs
	 */
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