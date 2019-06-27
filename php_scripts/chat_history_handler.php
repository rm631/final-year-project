<?php
	
	function fetchUserChatHistory($from_user_id, $to_user_id) {
		$con = mysqli_connect("", "", "", "");
		if (!$con) { die("Could not connect: " . mysqli_error()); }
		$query = "
			SELECT * FROM chatmsg 
			WHERE (sender_id = '".$from_user_id."' 
			AND reciever_id = '".$to_user_id."') 
			OR (sender_id = '".$to_user_id."' 
			AND reciever_id = '".$from_user_id."') 
			ORDER BY timestamp ASC";
		$statement = $con->prepare($query);
		$statement->execute();
		$result = $statement->get_result();
		$out = '<ul class="list-unstyled">';
		while($row = mysqli_fetch_assoc($result)) {
			$username;
			if($row['sender_id'] == $from_user_id) {
				$username = '<b class="text-sent">You</b>';
			} else {
				$username = '<b class="text-recieved">'.getUsername($row['sender_id']).'</b>';
			}
			$out .= '
				<li style="border-bottom:1px dotted #ccc">
					<p>'.$username.' - '.$row["msg"].'
						<div align="right">
							- <small><em>'.$row['timestamp'].'</em></small>
						</div>
					</p>
				</li>';
		}
		$out .= '</ul>';
		$statement->close();
		return $out;
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