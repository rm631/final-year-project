<?php
	session_start();
    $con = mysqli_connect("", "", "", "");

    if (!$con)
    {
        die("Could not connect: " . mysqli_error());
    }

    // Receive a GET with a friend's username
    if (isset($_GET["username"]))
    {
        $username = $_GET["username"];
		$username = $username."%";
        // Prepared statement to prevent SQL injection
        $statement = $con->prepare("SELECT * FROM users WHERE username LIKE ?");
        $statement->bind_param("s", $username);
        $statement->execute();
        $result = $statement->get_result();
        if (mysqli_num_rows($result) == 0)
        {
            echo "Username doesn't exist";
        }
        else
        {
            while ($row = mysqli_fetch_assoc($result))
            {
                echo $row['first_name'], " ", $row['last_name'],
                    " <button type=\"button\" class=\"btn btn-primary btn-xs\" 
                    id=".$row['username']." 
                    onclick=\"addFriend(this.id)\">Add friend</button>", "<br><br>";
            }
        }
        $statement->close();
    }

    // Receive a POST with a friend's first and last name
    if (isset($_POST["adduser"]))
    {
		$username = $_POST["adduser"];
        $statement1 = $con->prepare("SELECT username FROM users WHERE username = ?");
        $statement1->bind_param("s", $username);
        $statement1->execute();
        $friendusername = $statement1->get_result();
        $statement1->close();
        while ($row = mysqli_fetch_assoc($friendusername))
        {
			if(!alreadyFriends($row['username'])) {
				// bind_param against SQL injection
				$statement2 = $con->prepare("INSERT INTO friends (user_id, friend_id) VALUES ('".$_SESSION['username']."', '".$row['username']."')");
				//$statement2->execute();
				$statement3 = $con->prepare("INSERT INTO friends (user_id, friend_id) VALUES ('".$row['username']."', '".$_SESSION['username']."')");
				//$statement3->execute();
				if(($statement2->execute()) && ($statement3->execute())) {
					echo "You are now friends with " . $row['username'];
				}
			} else { echo "You are already friends with " . $row['username']; }
        }
    }

    // Receive a POST with the entered group name
    if (isset($_POST["grName"]))
    {
        $groupN = $_POST["grName"];
		if(!groupNameTaken($groupN)) {
			$statement = $con->prepare("INSERT INTO studentgroups (group_name, owner_username) VALUES (?, ?)");
			$statement->bind_param("ss", $groupN, $_SESSION['username']);
			if($statement->execute()) {
				$statement2 = $con->prepare("SELECT id FROM studentgroups WHERE (group_name=? AND owner_username='".$_SESSION['username']."')");
				$statement2->bind_param("s", $groupN);
				$statement2->execute();
				$groupId = $statement2->get_result();
				while($row = mysqli_fetch_assoc($groupId)) {
					//$sql = $con->prepare("INSERT INTO usergroups (group_id, user_id) VALUES ('".$row['id']."', '".getUserId($_SESSION['username'])."'");
					$sql = $con->prepare("INSERT INTO usergroups (group_id, user_id) VALUES ((SELECT id FROM studentgroups WHERE id=".$row['id']."), (SELECT id FROM users where id=".getUserId($_SESSION['username'])."))");
					$sql->execute();
				}
			}
			$statement->close();
			echo "Group created!";
		} else {
			echo "Group name already taken!";
		}
    }
	
	if(isset($_POST['get_all_groups'])) {
		$video_id = $_POST['get_all_groups'];
		$user_id = getUserId($_SESSION['username']);
		$statement = $con->prepare("SELECT * FROM studentgroups WHERE id IN (SELECT group_id FROM usergroups WHERE user_id = ?)");
		$statement->bind_param("s", $user_id);
		$statement->execute();
		$result = $statement->get_result();
		
		$characters = 'abcdefghijklmnopqrstuvwxyz0123456789';
		$max = strlen($characters) - 1;
		$random_string_length = 13;
		$room = '';
		for ($i = 0; $i < $random_string_length; $i++) {
			$room .= $characters[mt_rand(0, $max)];
		}
		$output = '<ul class="list-unstyled">';
		while($row = mysqli_fetch_assoc($result)) {
			$output .= '<li style="border-bottom:1px dotted #ccc">';
			$output .= '<a href="videoplayer.php?room='.$room.'&vid='.$video_id.'" id="'.$video_id.'" class="openVideo">';
			$output .= $row['group_name'].'</a>';
			$output .= '</li>';
		}
		$output .= '</ul>';
		$statement->close();
		echo $output;
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
	
	function alreadyFriends($friendusername) {
		$con = mysqli_connect("", "", "", "");
		if (!$con) { die("Could not connect: " . mysqli_error()); }
		$query = "SELECT * FROM friends WHERE user_id = ? AND friend_id = ?";
		$statement = $con->prepare($query);
		$statement->bind_param("ss", $_SESSION['username'], $friendusername);
		$statement->execute();
		$result = $statement->get_result();
		$statement->close();
		if(mysqli_num_rows($result) > 0) {
			return true;
		} else {
			return false;
		}
	}
	
	function groupNameTaken($group_name) {
		$con = mysqli_connect("", "", "", "");
		if (!$con) { die("Could not connect: " . mysqli_error()); }
		$query = "SELECT * FROM studentgroups WHERE group_name = ?";
		$statement = $con->prepare($query);
		$statement->bind_param("s", $group_name);
		$statement->execute();
		$result = $statement->get_result();
		$statement->close();
		if(mysqli_num_rows($result) > 0) {
			return true;
		} else {
			return false;
		}
	}
	
?>