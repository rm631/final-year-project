<?php
	session_start();
    $con = mysqli_connect("", "", "", "");

    if (!$con) { die("Could not connect: " . mysqli_error()); }
	
	if(isset($_POST['send_inv'])) {
		$group_id = getGroupId($_POST['group']);
		$query = "SELECT * FROM usergroups WHERE group_id = $group_id";
		
		$statement = $con->prepare($query);
		//$statement->bind_param("i", $group_id);
		if(!$statement->execute()) {
			echo "Error: " . mysqli_error();
		}
		$result = $statement->get_result();
		while($row = mysqli_fetch_assoc($result)) {
			if($row['user_id'] !== getUserId($_SESSION['username'])) {
				$query2 = "INSERT INTO invites(invite_to_id, invite_from_id, room_id) VALUES ((SELECT id FROM users WHERE id=".$row['user_id']."), (SELECT id FROM users WHERE id=".getUserId($_SESSION['username'])."), '".$_POST['room']."')";
				$statement2 = $con->prepare($query2);
				$statement2->execute();
			}
		}
	}
	
	if(isset($_GET['get_inv'])) {
		$modal_content = '';
		
		$user_id = getUserId($_SESSION['username']);
		
		$query = "SELECT * FROM invites WHERE invite_to_id=? ORDER BY time_sent LIMIT 1";
		$statement = $con->prepare($query);
		$statement->bind_param("i", $user_id);
		$statement->execute();
		$result = $statement->get_result();
		while($row = mysqli_fetch_assoc($result)) {
			$modal_content .= '<div class="modal-dialog" id="invite">';
			$modal_content .= getUsername($row['invite_from_id']).' has invited you to view a lecture!';
			$modal_content .= '<div class="modal-footer">';
			$modal_content .= '<button type="button" class="btn btn-secondary" data-dismiss="modal" onClick="declineInv(this.id)" id="'.$row['room_id'].'">Decline</button>';
			$modal_content .= '<button type="button" class="btn btn-primary" onClick="acceptInv(this.id)" id="'.$row['room_id'].'">Accept</button>';
			$modal_content .= '</div></div>';
		}
		echo $modal_content;
	}
	
	if(isset($_POST['remove_inv'])) {
		$user_id = getUserId($_SESSION['username']);
		$query = "DELETE FROM invites WHERE invite_to_id=? AND room_id=?";
		$statement = $con->prepare($query);
		$statement->bind_param("is", $user_id, $_POST['room']);
		$statement->execute();
	}
	
	if (isset($_GET["username"])) {
        $username = $_GET["username"];
		$username = $username."%";
        // Prepared statement to prevent SQL injection
        $statement = $con->prepare("SELECT * FROM users WHERE username LIKE ?");
        $statement->bind_param("s", $username);
        $statement->execute();
        $result = $statement->get_result();
        if (mysqli_num_rows($result) == 0) {
            echo "Username doesn't exist";
        }
        else {
            while ($row = mysqli_fetch_assoc($result)) {
                echo $row['first_name'], " ", $row['last_name'],
                    " <button type=\"button\" class=\"btn btn-primary btn-xs\" 
                    id=".$row['username']." 
                    onclick=\"inviteToGroup(this.id)\">Invite to group</button>", "<br><br>";
            }
        }
        $statement->close();
    }
	
	if((isset($_POST['invite_user'])) && (isset($_POST['group_id']))) {
		$invite_user_id = getUserId($_POST['invite_user']);
		$group_id = $_POST['group_id'];
		$checkStatement = $con->prepare("SELECT * FROM usergroups WHERE group_id=? AND user_id=?");
		$checkStatement->bind_param("ii", $group_id, $invite_user_id);
		$checkStatement->execute();
		$result = $checkStatement->get_result();
		if(mysqli_num_rows($result) == 0) {
			$statement = $con->prepare("INSERT INTO usergroups(group_id, user_id) VALUES(?,?)");
			$statement->bind_param("ii", $_POST['group_id'], $invite_user_id);
			if($statement->execute()) {
				echo "User successfully added to group!";
			} else {
				echo "Error adding user to group!";
			}
			
		} else {
			echo "User already in group!";
		}
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
	
	function getGroupId($group_name) {
		$con = mysqli_connect("", "", "", "");
		if (!$con) { die("Could not connect: " . mysqli_error()); }
		$query = "SELECT id FROM studentgroups WHERE group_name = '$group_name'";
		$statement = $con->prepare($query);
		$statement->execute();
		$result = $statement->get_result();
		$statement->close();
		while($row = mysqli_fetch_assoc($result)) {
			return $row['id'];
		}
	}
	
?>