<?php
	session_start();
	
	$con = mysqli_connect("", "", "", "");
    if (!$con) { die("Could not connect: " . mysqli_error()); }
	
	if(isset($_POST['sub_unsub'])) {
		if($_POST['sub_unsub'] === "Subscribe") {
			$query =  "INSERT INTO usermodules (module_id, user_id) VALUES ((SELECT module_id FROM modules WHERE module_id='".$_POST['module_id']."'), (SELECT id FROM users WHERE username='".$_SESSION['username']."'))";
			$statement = $con->prepare($query);
			$statement->execute();
		} else {
			//$query =  "DELETE FROM usermodules (module_id, user_id) VALUES ((SELECT module_id FROM modules WHERE module_id='".$_POST['module_id']."'), (SELECT id FROM users WHERE username='".$_SESSION['username']."'))";
			$query = "DELETE FROM usermodules WHERE (module_id=(SELECT module_id FROM modules WHERE module_id='".$_POST['module_id']."') AND user_id=(SELECT id FROM users WHERE username='".$_SESSION['username']."'))";
			$statement = $con->prepare($query);
			if($statement === false) { 
				die('prepare() failed: ' . htmlspecialchars($con->error));
			}
			$statement->execute();
		}
	}
?>