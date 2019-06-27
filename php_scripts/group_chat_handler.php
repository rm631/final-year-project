<?php
	session_start();
	
	$con = mysqli_connect("", "", "", "");

    if (!$con) { die("Could not connect: " . mysqli_error()); }
	
	if(isset($_POST['insert_true'])) {
		$group_id = mysqli_real_escape_string($con, $_POST['to_user_id']);
		$sender_id = mysqli_real_escape_string($con, getUserId($_SESSION['username']));
		$msg = mysqli_real_escape_string($con, $_POST['msg']);
		
		$query = "INSERT INTO groupchatmsg(group_id, sender_id, msg) VALUES ((SELECT id FROM studentgroups WHERE id=$group_id), (SELECT id FROM users WHERE id=$sender_id), '$msg')";
			
		$statement = $con->prepare($query);
		if($statement === false) { 
			die('prepare() failed: ' . htmlspecialchars($con->error));
		}
		
		if($statement->execute()) {
			//echo fetchUserChatHistory(getUserId($_SESSION['username']), $_POST['to_user_id']);
			
			// get all users in group, insert them into groupchatstatus
			$groupchatmsg_id = $con->insert_id;
			$query2 = "SELECT user_id FROM usergroups WHERE group_id='$group_id'";
			$statement2 = $con->prepare($query2);
			if($statement2 === false) { 
				die('prepare() failed: ' . htmlspecialchars($con->error));
			}
			if($statement2->execute()) {
				$user_id = $statement2->get_result();
				while($row = mysqli_fetch_assoc($user_id)) {
					$statement3 = $con->prepare("INSERT INTO groupchatstatus (chat_msg_id, user_id, status) VALUES ('$groupchatmsg_id', '".$row['user_id']."', '1')");
					if($statement3 === false) { 
						die('prepare() failed: ' . htmlspecialchars($con->error));
					}
					$statement3->execute();
				}
				/******* output *******/
				echo fetchGroupChatHistory($sender_id, $group_id);
			} else { echo $stmt->error; }
		} else { echo $stmt->error; }
	} else if(isset($_POST['update'])) {
		$group_id = mysqli_real_escape_string($con, $_POST['group_id']);
		$user_id = mysqli_real_escape_string($con, getUserId($_SESSION['username']));
		return countUnseen($user_id, $group_id);
	} else if(isset($_POST['clear'])) { 
		$status = 0;
		$group_id = mysqli_real_escape_string($con, $_POST['to_user_id']);
		$user_id = mysqli_real_escape_string($con, getUserId($_SESSION['username']));
		//$query = "UPDATE groupchatstatus SET status=$status WHERE (user_id=$user_id AND reciever_id=$reciever_id)";
		/*$query = "UPDATE groupchatstatus SET status=$status FROM groupchatstatus
				INNER JOIN groupchatmsg ON groupchatstatus.chat_msg_id = groupchatmsg.chat_msg_id
				WHERE user_id = '$user_id' AND status = '1' AND group_id = '$group_id'
				";*/
		//$query = "UPDATE groupchatstatus SET status='$status' WHERE (user_id = '$user_id' AND status = '1' AND (SELECT group_id FROM groupchatmsg WHERE group_id = '$group_id'))";
		$query = "SELECT id FROM groupchatstatus a INNER JOIN groupchatmsg b ON a.chat_msg_id = b.chat_msg_id WHERE user_id = $user_id AND status = '1' AND b.group_id = $group_id";
		$statement = $con->prepare($query);
		$statement->execute();
		$result = $statement->get_result();
		while($row = mysqli_fetch_assoc($result)) {
			$query2 = "UPDATE groupchatstatus SET status='$status' WHERE id =".$row['id']."";
			$statement2 = $con->prepare($query2);
			$statement2->execute();
		}
	} else {
		$group_id = mysqli_real_escape_string($con, $_POST['to_user_id']);
		$sender_id = mysqli_real_escape_string($con, getUserId($_SESSION['username']));
		echo fetchGroupChatHistory($sender_id, $group_id);
	}
	function fetchGroupChatHistory($user_id, $group_id) {
		$con = mysqli_connect("", "", "", "");
		if (!$con) { die("Could not connect: " . mysqli_error()); }
		$query = "
			SELECT * FROM groupchatmsg 
			WHERE (group_id = '".$group_id."') 
			ORDER BY timestamp ASC";
		$statement = $con->prepare($query);
		$statement->execute();
		$result = $statement->get_result();
		$out = '<ul class="list-unstyled">';
		while($row = mysqli_fetch_assoc($result)) {
			$username;
			if($row['sender_id'] == $user_id) {
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
	
	function countUnseen($user_id, $group_id) {
		$con = mysqli_connect("", "", "", "");
		if (!$con) { die("Could not connect: " . mysqli_error()); }
		$query = "SELECT groupchatmsg.chat_msg_id, groupchatstatus.chat_msg_id FROM groupchatstatus
				INNER JOIN groupchatmsg ON groupchatstatus.chat_msg_id = groupchatmsg.chat_msg_id
				WHERE user_id = '$user_id' AND status = '1' AND group_id = '$group_id'
				";
		$statement = $con->prepare($query);
		$statement->execute();
		$result = $statement->get_result();
		
		// add the if in if you don't want to display 0
		//if($result->num_rows > 0) {
		echo $result->num_rows;
		//}
	}
	
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