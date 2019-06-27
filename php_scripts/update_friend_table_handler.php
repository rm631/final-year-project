<?php
	session_start();
	$con = mysqli_connect("", "", "", "");
    if (!$con) { die("Could not connect: " . mysqli_error()); }
	
	//$returnArr = array();
	
	//countUnseen($_SESSION['username'], $_POST['user_id']);
	//fetchLastActive($_POST['user_id']);
	//array_push($return, countUnseen($_SESSION['username'], $_POST['user_id']));
	//array_push($return, fetchLastActive($_POST['user_id']));
	
	if(isset($_POST['notif'])) {	
		echo countUnseen($_SESSION['username'], $_POST['user_id']);
	} else if(isset($_POST['active'])) {
		echo fetchLastActive($_POST['user_id']);
	} else {
		echo json_encode(array(countUnseen($_SESSION['username'], $_POST['user_id']), fetchLastActive($_POST['user_id'])));
	}
	
	function fetchLastActive($id) {
		$con = mysqli_connect("", "", "", "");
		$query = "SELECT last_activity FROM logindetails WHERE user_id ='$id'";
		$statement = $con->prepare($query);
		$statement->execute();
		$result = $statement->get_result();
		$statement->close();
		$output = '';
		while($row = mysqli_fetch_assoc($result)) {
			//return $row['last_activity'];
			$current_datetime = date('Y-m-d H:i:s', strtotime(date("Y-m-d H:i:s") . '- 10 second'));
			if($row['last_activity'] > $current_datetime) {
				$output .= '<td><button style="border: none; width: 10px; height: 10px; outline: 0; background: green; border-radius: 50%; bottom: 20px;" class="chatBtn" id="'.$id.'lastActive" value="'.getUsername($id).'">';
				$output .= '</button></td>';
			}
			else    {
				$output .= '<td><button style="border: none; width: 10px; height: 10px; outline: 0; background: red; border-radius: 50%; bottom: 20px;" class="chatBtn" id="'.$id.'lastActive" value="'.getUsername($id).'">';
				$output .= '</button></td>';
			}
		}
		return $output;
	}
	
	
	function countUnseen($from_user_id, $to_user_id) {
		$con = mysqli_connect("", "", "", "");
		if (!$con) { die("Could not connect: " . mysqli_error()); }
		$from_user_id = intval(mysqli_real_escape_string($con, getUserId($from_user_id)));
		$to_user_id = intval(mysqli_real_escape_string($con, $to_user_id));
		$query = "SELECT * FROM chatmsg 
			WHERE sender_id = '$to_user_id'
			AND reciever_id = '$from_user_id' 
			AND status = '1'
			";
		$statement = $con->prepare($query);
		$statement->execute();
		$result = $statement->get_result();
		
		// add the if in if you don't want to display 0
		//if($result->num_rows > 0) {
			return $result->num_rows;
		//}
	}
	
	
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
	
	function getUsername($user_id) {
		$con = mysqli_connect("", "", "", "");
		if (!$con) { die("Could not connect: " . mysqli_error()); }
		$query = "SELECT username FROM users WHERE id = '$user_id'";
		$statement = $con->prepare($query);
		$statement->execute();
		$result = $statement->get_result();
		$statement->close();
		while($row = mysqli_fetch_assoc($result)) {
			return $row['username'];
		}
	}
?>