<?php
//Declaring variables to prevent errors
$fname = "";//First name
$lname = "";//Last name
$em = ""; //Email
$em2 = ""; //Email2
$password = ""; //Password
$password2 = ""; //Password 2
$date = ""; //Date
$error_array = array(); //Holds error messages

if(isset($_POST['register_button'])){

	//Registration form values

	//First name 
	$fname = strip_tags($_POST['reg_fname']); //Remove html tags
	$fname = str_replace('', '', $fname); //Remove spaces
	$fname = ucfirst(strtolower($fname)); //Uppercase first letter
	$_SESSION['reg_fname'] = $fname; //Stores first name into session variable
	//Last name
	$lname = strip_tags($_POST['reg_lname']); //Remove html tags
	$lname = str_replace('', '', $lname); //Remove spaces
	$lname = ucfirst(strtolower($lname)); //Uppercase first letter
	$_SESSION['reg_lname'] = $lname; //Stores last name into session variable
	//Email
	$em = strip_tags($_POST['reg_email']); //Remove html tags
	$em = str_replace('', '', $em); //Remove spaces
	$em = ucfirst(strtolower($em)); //Uppercase first letter
	$_SESSION['reg_email'] = $em;	//Stores email into session variable
	//Email 2
	$em2 = strip_tags($_POST['reg_email2']); //Remove html tags
	$em2 = str_replace('', '', $em2); //Remove spaces
	$em2 = ucfirst(strtolower($em2)); //Uppercase first letter
	$_SESSION['reg_email2'] = $em2;	//Stores email2 into session variable

	//Password
	$password = strip_tags($_POST['reg_password']); //Remove html tags

	//Password 2
	$password2 = strip_tags($_POST['reg_password2']); //Remove html tags

	//Date
	$date = date("Y-m-d"); //Gets the current date

	if($em == $em2) {

		
		if(filter_var($em, FILTER_VALIDATE_EMAIL)){
			
			$em = filter_var($em, FILTER_VALIDATE_EMAIL);

			
			$e_check = mysqli_query($con, "SELECT email FROM users WHERE email='$em'");
			if($e_check) {
				//Count number of rows returned
				$num_rows = mysqli_num_rows($e_check);
				
				if($num_rows > 0){
					array_push($error_array,"Email already in use<br>");
				}
			}
		}
		else{

			array_push($error_array, "Invalid email format<br>");
		}  

		
	}

	else{
		array_push($error_array,"Emails dont match<br>");
	}

	if(strlen($fname) > 25 || strlen($fname) < 2){
	array_push($error_array,"Your first name should be between 2 and 25 characters<br>");
	}

	if(strlen($lname) > 25 || strlen($lname) < 2){
	array_push($error_array,"Your last name should be between 2 and 25 characters<br>");
	}

	if($password != $password2){
		array_push($error_array, "Your passwords do not match<br>");
	} 
	else{
		if(preg_match('/[^A-Za-z0-9]/', $password)){
			array_push($error_array,"Your password can only contain english characters or numbers<br>");
		}
	}

	if(strlen($password) > 30 || strlen($password) < 5){
		array_push($error_array,"Your password must be between 5 and 30 characters<br>");
	}
	
	if(empty($error_array)){

		$password = md5($password); //Encrypt password before sending to database

		//Generate username by concatinating first name and last name

		$username = strtolower($fname . "_" . $lname);
		$check_username_query = mysqli_query($con, "SELECT username FROM users WHERE username='$username'");
		if($check_username_query) {
			$i = 0;
			//if username exists add number to username
			while(mysqli_num_rows($check_username_query) != 0){
				$i++; //Add one to i;
				$username = $username . "_" . $i;
				$check_username_query = mysqli_query($con, "SELECT username FROM users WHERE username='$username'");
			}
		}
		//Profile picture assignment
		$rand = rand(1,2); //Random number between 1 and 2
		if($rand == 1){
			$profile_pic ="assets/images/profile_pics/defaults/default.png";
		}
		else if($rand == 2){
			$profile_pic ="assets/images/profile_pics/defaults/default2.png";
		}

		$query = mysqli_query($con, "INSERT INTO users VALUES ('','$fname', '$lname', '$username', '$em', '$password', '$date', '$profile_pic', '0', '0', 'no')");
		if ( false===$query ) {
			printf("error: %s\n", mysqli_error($con));
		} else {
			$user_id = mysqli_query($con, "SELECT id FROM users WHERE username = '$username'");
			while($row = $user_id->fetch_assoc()) {
				$sql = mysqli_query($con, "INSERT INTO logindetails VALUES ('', '".$row['id']."', '')");
			}
		}

		array_push($error_array, "<span style = 'color: #14C800'> You're all set! Go ahead and login!</span><br>");

		$_SESSION['reg_fname'] = "";
		$_SESSION['reg_lname'] = "";
		$_SESSION['reg_email'] = "";
		$_SESSION['reg_email2'] = "";
	}
	
}
?>