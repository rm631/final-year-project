<?php 
	require "config/config.php";
	if(!isset($_SESSION['username'])) {
		header("location:register.php"); 
	} 
?>
<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" href="css/index_style.css">
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/css/bootstrap.min.css">
<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.2/css/all.css" integrity="sha384-fnmOCqbTlWIlj8LyTjo7mOUStjsKC4pOpQbqyi7RrhN7udi9RwhKkMHpvLbHG9Sr" crossorigin="anonymous">
<link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/js/bootstrap.min.js"></script>
<script src="https://code.jquery.com/ui/1.12.0/jquery-ui.js" integrity="sha256-0YPKAwZP7Mp3ALMRVB2i8GXeEndvCq3eSl/WsAl1Ryk=" crossorigin="anonymous"></script>

<title>NoteCast</title>


</head>
<body>

<!--nav bar-->
<div class="topnav">
	<a href="index.php">Home</a>
	<a href="modules.php">Modules</a>
	<div class="search-container">
		<form action="action_page.php">
		<input type="text" placeholder="Search..." name="search">
		<button type="submit"><i class="fa fa-search"></i></button>
		</form>
	</div>
</div>


<!-- Sidebar --><br><br>
<div id="sidebarWrapper" class="w3-sidebar w3-light-grey w3-bar-block" style="min-width:260px; float: right;">
	<h3 id="friends" class="w3-bar-Friends">Friends <button class="btn" data-toggle="modal" data-target="#friendModal"><i class="fas fa-plus-circle"></i></button></h3>
	<?php
		$result = mysqli_query($con, "SELECT * FROM `users` WHERE `username` IN ( SELECT `friend_id` FROM `friends` WHERE `user_id` = '" . $_SESSION['username'] . "' )");
		$output = '<table id="friendsTable">';
		while($row = $result->fetch_assoc()) {
			$output .= '<tr class="friend">';
			$output .= '<td><button style="border: none; width: 200px; height: 40px; outline: 0; background: #f1f1f1;" class="chatBtn" id="'.$row['id'].'" value="'.$row['username'].'">';
			$output .= '<span class=\'image\' style="border-radius: 50%; height: 32px; width: 32px; float: left ">
                        <img class="manImg" src="https://cdn.pixabay.com/photo/2015/10/05/22/37/blank-profile-picture-973460_960_720.png"  style="width: 32px; height: 32px; border-radius: 50%;">
                        </span>';
			$output .= '<span class="name" style="bottom: 5px">';
			$output .= $row['first_name'] . " " . $row['last_name'];
			$output .= '</span></button></td>';
			$output .= '<td><button style="border: none; width: 20px; height: 40px; outline: 0; background: #f1f1f1;" class="chatBtn" id="'.$row['id'].'" value="'.$row['username'].'">';
			$output .= '<span style="bottom: 5px" id='.$row['id'].'Notifications>'.countUnseen($_SESSION['username'], $row['id']).'</span></button></td>';
			
			$last_active = mysqli_query($con, "SELECT last_activity FROM logindetails WHERE user_id='".$row['id']."'");
			while($row2 = $last_active->fetch_assoc()) {
				$current_datetime = date('Y-m-d H:i:s', strtotime(date("Y-m-d H:i:s") . '- 10 second'));
				if($row2['last_activity'] > $current_datetime) {
					$output .= '<td><button style="border: none; width: 10px; height: 10px; outline: 0; background: green; border-radius: 50%; bottom: 20px;" class="chatBtn" id="'.$row['id'].'lastActive" value="'.$row['username'].'">';
					$output .= '</button></td>';
				}
				else    {
					$output .= '<td><button style="border: none; width: 10px; height: 10px; outline: 0; background: red; border-radius: 50%; bottom: 20px;" class="chatBtn" id="'.$row['id'].'lastActive" value="'.$row['username'].'">';
					$output .= '</button></td>';
				}
			}
			$output .= '</tr>';
		}
		$output .= '</table>';
		echo $output;
		
		function fetchLastActive($id) {
			$con = mysqli_connect("", "", "", "");
			$query = "SELECT last_activity FROM logindetails WHERE user_id ='$id'";
			$statement = $con->prepare($query);
			$statement->execute();
			$result = $statement->get_result();
			$statement->close();
			while($row = mysqli_fetch_assoc($result)) {
				return $row['last_activity'];
			}
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
			return $result->num_rows;
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
	<h3 id="groupsWrapper" class="w3-bar-Groups">Groups <button class="btn" data-toggle="modal" data-target="#groupModal"><i class="fas fa-plus-circle"></i></button></h3> 
	<?php 
		$result = mysqli_query($con, "SELECT * FROM `studentgroups` WHERE `id` IN ( SELECT `group_id` FROM `usergroups` WHERE `user_id` = '" . getUserId($_SESSION['username']) . "' )");
		$output = '<table id="groupsTable">';
		while($row = $result->fetch_assoc()) {
            $output .= '<tr class="group">';
			$output .= '<td><button style="border: none; width: 40px; height: 40px; outline: 0; text-align: right; background: #f1f1f1;" id="'.$row['id'].'" class="groupInvite">Invite</button></td>';
            $output .= '<td><button style="border: none; width: 200px; height: 40px; outline: 0; text-align: right; background: #f1f1f1;" class="grpChatBtn" id="'.$row['id'].'" value="'.$row['group_name'].'">';
            $output .= '<span class="name" style="bottom: 5px">';
            $output .= $row['group_name'];
            $output .= '</span></button></td>';
            $output .= '<td><button style="border: none; width: 20px; height: 40px; outline: 0; text-align: right; background: #f1f1f1;" class="grpChatBtn" id="'.$row['id'].'" value="'.$row['group_name'].'">';
            $output .= '<span id='.$row['id'].'Notifications>'.countGroupUnseen($_SESSION['username'], $row['id']).'</span></button></td>';
		}
		$output .= '</table>';
		echo $output;
		
		function countGroupUnseen($user_id, $group_id) {
			$con = mysqli_connect("", "", "", "");
			if (!$con) { die("Could not connect: " . mysqli_error()); }
			$user_id = getUserId($user_id);
			$query = "SELECT groupchatmsg.chat_msg_id, groupchatstatus.chat_msg_id FROM groupchatstatus
				INNER JOIN groupchatmsg ON groupchatstatus.chat_msg_id = groupchatmsg.chat_msg_id
				WHERE user_id = '$user_id' AND status = '1' AND group_id = '$group_id'
				";
			
			$statement = $con->prepare($query);
			if($statement === false) {
				die('prepare() failed: ' . htmlspecialchars($con->error));
			}
			$statement->execute();
			$result = $statement->get_result();
			return $result->num_rows;
		}
	?>
</div>

<!-- Group modal -->
<div class="modal fade" id="groupModal" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Create a group</h4>
            </div>
            <div class="modal-body">
                <form role="form">
                    <div class="form-inline">
                        <label for="groupName">Group name:</label>
                        <input type="text" class="form-control mr-1" id="groupName" placeholder="Enter group name">
                    </div>
                    <br>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" onclick="createGroup()">Create new group</button>
            </div>
        </div>
    </div>
</div>

<!-- Friends modal -->
<div class="modal fade" id="friendModal" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Add friends</h4>
            </div>
            <div class="modal-body">
                <form role="form">
                    <div class="form-inline">
                        <input type="text" class="form-control mr-1" id="searchField" placeholder="Search username">
                        <button type="button" class="btn btn-primary" onclick="searchUsers()">Search</button>
                    </div>
                    <br>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Invite Modal -->
<div class="modal fade" id="inviteModal" role="dialog" style=".close {display: none;}"></div>

<!-- Invite to group Modal. -->
<div class="modal fade" id="inviteToGroupModal" role="dialog">
	<div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Invite user to group</h4>
            </div>
            <div class="modal-body">
                <form role="form">
                    <div class="form-inline">
                        <input type="text" class="form-control mr-1" id="searchFieldInv" placeholder="Search username">
                        <button type="button" class="btn btn-primary" onclick="searchUsersInv()">Search</button>
                    </div>
                    <br>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Dynamic Group Modal for viewing lectures -->
<div class="modal fade" id="group_lecture" role="dialog"></div>

<!-- Dynamic chat modal container -->
<div class="modal fade" id="user_model_details" role="dialog"></div>

<!-- Page Content -->
<main style="height:70%; width: 70%">
<?php
	$output = '<div>';
	$search = $_GET['search'];
	$search = filter_var($search, FILTER_SANITIZE_STRING);
	$search = str_replace(' ', '_', $search);
	$search = "%".$search."%";
	
	$statement = $con->prepare("SELECT * FROM users WHERE username LIKE ? OR first_name LIKE ? OR last_name LIKE ?");
	$statement->bind_param("sss", $search, $search, $search);
	$statement->execute();
	$result = $statement->get_result();
	$i = 0;
	while ($row = mysqli_fetch_assoc($result)) {
		if($i == 0) { $output = '<h2>Users</h2>'; }
		$output .= $row['first_name'].' '.$row['last_name'].'<button type="button" class="btn btn-primary btn-xs" id="'.$row['username'].'" onclick="addFriend(this.id)">Add friend</button><br><br>';
		$i++;
	}
	$statement->close();
	
	
	$statement = $con->prepare("SELECT * FROM modules WHERE module_id LIKE ? OR module_name LIKE ? OR module_desc LIKE ?");
	$statement->bind_param("sss", $search, $search, $search);
	$statement->execute();
	$result = $statement->get_result();
	$j = 0;
	while($row = $result->fetch_assoc()) {
		if($j == 0) {
			$output .= '<h2>Modules</h2>';
			$output .= '<table id="modulesTable" style="height:100%;width:100%;overflow-y:auto">
						<tr>
							<th>Modules</th>
							<th>Subscribe</th>
						</tr>
					';
		}
		$output .= '<tr class="module">';
		$output .= '<td><a href="modulepage.php?id='.$row['module_id'].'"><h2>'.$row['module_id'].': '.$row['module_name'].'</h2></a><br>'.$row['module_desc'].'</td>';
		$output .= '<td><button id="'.$row['module_id'].'" class="subBtn" value="'.getSubOrUnSub($_SESSION['username'], $row['module_id']).'">'.getSubOrUnSub($_SESSION['username'], $row['module_id']).'</button></td>';
		$output .= '</tr>';
		$j++;
	}
	if($j !== 0) { $output .= '</table>'; }
	$output .= '</div>';
	echo $output;
	
	function getSubOrUnSub($username, $module_id) {
		$con = mysqli_connect("", "", "", "");
		if (!$con) { die("Could not connect: " . mysqli_error()); }
		//$result = mysqli_query($con, "SELECT * FROM `usermodules` WHERE module_id=$module_id AND user_id=$username");
		$query = "SELECT * FROM `usermodules` WHERE module_id='$module_id' AND user_id='".getUserId($username)."'";
		$statement = $con->prepare($query);
		if($statement === false) {
			die('prepare() failed: ' . htmlspecialchars($con->error));
		}
		$statement->execute();
		$result = $statement->get_result();
		if($result->num_rows > 0) {
			return 'Unsubscribe';
		} else {
			return 'Subscribe';
		}
	}
?>
</main>

<div style="margin-left:25%">


<div style="padding-left:16px">
  
</div>
<script>
	$(document).ready(function() {
		setInterval(function() {
			updateLastActive();
			fetchChatHistory();
			updateFriendTable();
			updateGroupTable();
			getNewInvites();
		}, 5000); // 5000 -> 10000 due to possible problems with updateFriendTable if someone has a lot of friends...
	});
	
	function updateLastActive() {
		$.ajax({
			url:"php_scripts/update_login_details_handler.php",
			success:function(){}
		});
	}
	/**********************	FRIEND CHAT	**********************/
	$(document.body).on("click", ".chatBtn", function() {
		var to_user_id = this.id;
		var to_user_name = this.value;
		var modal_content = '<div class="modal-dialog">';
		modal_content += '<div id="user_dialog_'+to_user_id+'" class="user_dialog" title="'+to_user_name+'">';
		modal_content += '<div style="height:400px; border:1px solid #ccc; overflow-y: scroll; margin-bottom:24px; padding:16px;" class="chat_history" data-touserid="'+to_user_id+'" id="chat_history_'+to_user_id+'">';
		modal_content += '</div>';
		modal_content += '<div class="form-group">';
		modal_content += '<textarea name="chat_message_'+to_user_id+'" id="chat_message_'+to_user_id+'" class="form-control"></textarea>';
		modal_content += '</div><div class="form-group" align="right">';
		modal_content+= '<button type="button" name="send_chat" id="'+to_user_id+'" class="btn btn-info send_chat">Send</button></div></div>';
		modal_content += '</div>';
		$('#user_model_details').html(modal_content);
		$("#user_dialog_"+to_user_id).dialog({
			autoOpen:false,
			width:400
		});
		$('#user_dialog_'+to_user_id).dialog('open');
		fetchChatHistory(); // force this when the chat is opened so it's not blank for up to 5 secs..
		clearAllNotifications(to_user_id);
	});
	
	$(document.body).on("click", ".send_chat", function() {
		var to_user_id = this.id;
		var msg = $("#chat_message_"+to_user_id).val();
		//alert(to_user_id + ", " + msg);
		$.ajax({
			url: "php_scripts/chat_handler.php",
			method: "POST",
			data:{to_user_id:to_user_id, msg:msg},
			success:function(data) {
				$('#chat_message_'+to_user_id).val('');
				$('#chat_history_'+to_user_id).html(data);
			}
		});
	});
	
	function fetchChatHistory() {
		$('.chat_history').each(function() {
			var to_user_id = $(this).data('touserid');
			$.ajax({
				url:"php_scripts/fetch_chat_history_handler.php",
				method:"POST",
				data:{to_user_id:to_user_id},
				success:function(data) {
					$('#chat_history_'+to_user_id).html(data);
					$('#chat_history_'+to_user_id).scrollTop($("#chat_history_"+to_user_id)[0].scrollHeight);
				}
			});
		});
	}
	
	function clearAllNotifications(to_user_id) {
		$.ajax({
			url:"php_scripts/update_chat_status_handler.php",
			method:"POST",
			data:{to_user_id:to_user_id},
			success:function(data) {
				//$('#'+to_user_id+'Notifications').html(data);
				updateFriendTable();
			},
			//error: function (jqXHR, textStatus, errorThrown) { alert(errorThrown); }
		});
	}
	
	function updateFriendTable() {
		//var user_id = $("#friendsTable tr td").find(".chatBtn").attr('id');
		$("#sidebarWrapper").find('#friendsTable tr').each(function(i) {
			var $tds = $(this).find('td');
			var user_id = $(this).find(".chatBtn").attr('id');
			if(typeof user_id !== 'undefined') {
				//alert(user_id);
				$.ajax({
					url:"php_scripts/update_friend_table_handler.php",
					method:"POST",
					data:{user_id:user_id,notif:'notif'},
					success:function(data) {
						$('#'+user_id+'Notifications').html(data);
					},
					error: function (jqXHR, textStatus, errorThrown) { alert(errorThrown); }
				});
				$.ajax({
					url:"php_scripts/update_friend_table_handler.php",
					method:"POST",
					data:{user_id:user_id,active:'active'},
					success:function(data) {
						$('#'+user_id+'lastActive').replaceWith(data);
					},
					error: function (jqXHR, textStatus, errorThrown) { alert(errorThrown); }
				});
			}
		});
	}
	
	/**********************	GROUP CHAT/invite	**********************/
	
	$(document.body).on("click", ".grpChatBtn", function() {
		var to_user_id = this.id;
		var to_user_name = this.value;
		var modal_content = '<div class="modal-dialog">';
		modal_content += '<div id="user_dialog_'+to_user_id+'" class="user_dialog" title="'+to_user_name+'">';
		modal_content += '<div style="height:400px; border:1px solid #ccc; overflow-y: scroll; margin-bottom:24px; padding:16px;" class="group_chat_history" data-touserid="'+to_user_id+'" id="chat_history_'+to_user_id+'">';
		modal_content += '</div>';
		modal_content += '<div class="form-group">';
		modal_content += '<textarea name="chat_message_'+to_user_id+'" id="chat_message_'+to_user_id+'" class="form-control"></textarea>';
		modal_content += '</div><div class="form-group" align="right">';
		modal_content+= '<button type="button" name="grp_send_chat" id="'+to_user_id+'" class="btn btn-info grp_send_chat">Send</button></div></div>';
		modal_content += '</div>';
		$('#user_model_details').html(modal_content);
		$("#user_dialog_"+to_user_id).dialog({
			autoOpen:false,
			width:400
		});
		$('#user_dialog_'+to_user_id).dialog('open');
		fetchGroupChatHistory(); // force this when the chat is opened so it's not blank for up to 5 secs..
		clearAllGroupNotifications(to_user_id);
	});
	
	$(document.body).on("click", ".grp_send_chat", function() {
		var to_user_id = this.id;
		var msg = $("#chat_message_"+to_user_id).val();
		$.ajax({
			url: "php_scripts/group_chat_handler.php",
			method: "POST",
			data:{insert_true:'send',to_user_id:to_user_id, msg:msg},
			success:function(data) {
				$('#chat_message_'+to_user_id).val('');
				$('#chat_history_'+to_user_id).html(data);
			},
			//error: function (jqXHR, textStatus, errorThrown) { alert(errorThrown); }
		});
	});
	
	$(document.body).on("click", ".groupInvite", function() {
		var group_id = this.id;
		
		$("#inviteToGroupModal").modal();
		$("#inviteToGroupModal").find(".modal-body").attr("id", group_id);
	});
	
	function searchUsersInv() {
	   // Remove all p tags in the modal
	   $("p").remove(".modalP");
	   $("p").remove(".modalPResponse");
	   var xmlhttp1 = new XMLHttpRequest();
	   var username = $("#searchFieldInv").val();
	   xmlhttp1.onreadystatechange = function() {
		   if (this.readyState == 4 && this.status == 200) {
			   $("#inviteToGroupModal").find(".modal-body").append(
				   '<p class="modalP">'+this.responseText+'</p>'
			   );
		   }
	   }
	   xmlhttp1.open("GET", "php_scripts/invite_handler.php?username=" + username, true);
	   xmlhttp1.send();
   };

   // Send first and last name of a friend that the user wants to add
   // to social_handler.php
    function inviteToGroup(credentials) {
		var group_id = $("#inviteToGroupModal").find(".modal-body").attr("id");
		alert(credentials + ", " + group_id);
		$("p").remove(".modalPResponse");
		var xmlhttp2 = new XMLHttpRequest();
		xmlhttp2.onreadystatechange = function() {
			if (this.readyState == 4 && this.status == 200) {alert(this.responseText);
				$("#inviteToGroupModal").find(".modal-body").append(
				   '<p class="modalPResponse">'+this.responseText+'</p>'
				   
				);
			}
		}
		xmlhttp2.open("POST", "php_scripts/invite_handler.php", true);
		xmlhttp2.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
		xmlhttp2.send("invite_user=" + credentials + "&group_id=" + group_id);
	};
	
	/*$(document.body).on("click", ".groupInvite", function() {
		var group_id = this.id;
		/*$.ajax({
			url:"php_scripts/invite_handler.php",
			method:"POST",
			data:{invite_to_group:'inv',group_id:group_id},
			success:function(data) {
				
			}
		});*/
	//});
	
	function fetchGroupChatHistory() {
		$('.group_chat_history').each(function() {
			var to_user_id = $(this).data('touserid');
			$.ajax({
				url:"php_scripts/group_chat_handler.php",
				method:"POST",
				data:{to_user_id:to_user_id},
				success:function(data) {
					$('#chat_history_'+to_user_id).html(data);
					$('#chat_history_'+to_user_id).scrollTop($("#chat_history_"+to_user_id)[0].scrollHeight);
				}
			});
		});
	}
	
	function clearAllGroupNotifications(to_user_id) {
		$.ajax({
			url:"php_scripts/group_chat_handler.php",
			method:"POST",
			data:{clear:'clear',to_user_id:to_user_id},
			success:function(data) {
				//$('#'+to_user_id+'Notifications').html(data);
				updateGroupTable();
			},
			//error: function (jqXHR, textStatus, errorThrown) { alert(errorThrown); }
		});
	}
	
	function updateGroupTable() {
		//var group_id = $("#groupsTable tr td").find(".grpChatBtn").attr('id');
		$("#sidebarWrapper").find('#groupsTable tr').each(function(i, item) {
			var $tds = $(this).find('td');
			var group_id = $(this).find(".grpChatBtn").attr('id');
			if(typeof group_id !== 'undefined') {
				//alert(group_id);
				$.ajax({
					url:"php_scripts/group_chat_handler.php",
					method:"POST",
					data:{update:'update',group_id:group_id},
					success:function(data) {
						$('#'+group_id+'Notifications').html(data);
					},
					//error: function (jqXHR, textStatus, errorThrown) { alert(errorThrown); }
				});
			}
		});
	}
	
	$(document).on('click', '.ui-button-icon', function(){
		$('.user_dialog').dialog('destroy').remove();
	});
	
	/**********************	Watch lectures/invites	**********************/
	
	$(document.body).on("click", ".watchLecture", function() {
		var video_id = this.id;
		
		//modal_content += 'is this where stuff goes?';
		$.ajax({
			url:"php_scripts/social_handler.php",
			method:"POST",
			data:{get_all_groups:video_id},
			success:function(data) {
				//alert(data);
				var modal_content = '<div class="modal-dialog">';
				modal_content += '<div id="lecture_'+video_id+'" class="user_dialog" title="Select group">';
				modal_content += '<div style="height:400px; border:1px solid #ccc; overflow-y: scroll; margin-bottom:24px; padding:16px;" class="view_lecture" id="'+video_id+'">';
				modal_content += data;
				modal_content += '</div>';
				$('#group_lecture').html(modal_content);
				$("#lecture_"+video_id).dialog({
					autoOpen:false,
					width:400
				});
				$("#lecture_"+video_id).dialog('open');
			}
		});
		//alert(modal_content);
	
	});
	
	$(document.body).on("click", ".openVideo", function(e) {
		e.preventDefault();
		var address = $(this).attr("href");
		var group = this.text;
		var room = this.id;
		$.ajax({
			url:"php_scripts/invite_handler.php",
			method:"POST",
			data:{send_inv:'send_inv',group:group,room:room},
			success:function() {
				window.location.href = address;
			}
		});
	});
	
	function getNewInvites() {
		$.ajax({
			url:"php_scripts/invite_handler.php",
			method:"GET",
			data:{get_inv:'get_inv'},
			success:function(data) {
				var modal_content = data;
				
				$('#inviteModal').html(modal_content);
				$("#invite").dialog({
					autoOpen:false,
					width:400,
					open: function(event, ui) {
						$(".ui-dialog-titlebar-close", $(this).parent()).hide();
					}
				});
				$("#invite").dialog('open');
			}
		});
	}
	
	function acceptInv(room_id) {
		var address = 'videoplayer.php?room=' + room_id;
		$.ajax({
			url:"php_scripts/invite_handler.php",
			method:"POST",
			data:{remove_inv:'remove_inv',room:room_id},
			success:function() {
				window.location.href = address;
			}
		});
	}
	
	/**
	 *	Rooms are psuedorandom but since invites are purged every 10 minutes 
	 *	We can make the assumption they *SHOULD* be unique. 
	 *	Also if they're not unique then they'll end up in the wrong
	 *	video room. So this assumption basically needs to hold true
	 *	the room ids could be made longer+have variance in length
	 *	to make the odds of duplicates incredibly low. I can't think of a better
	 *	way of dealing with rooms though...
	 */
	function declineInv(room_id) {
		$.ajax({
			url:"php_scripts/invite_handler.php",
			method:"POST",
			data:{remove_inv:'remove_inv',room:room_id},
			success:function() {
				$("#invite").dialog('close');
			}
		});
	}
	
	/**********************	Add friends/create groups	**********************/
	
   // Search for users in the with the specified username
   // (maybe later decide on a better way to search rather than username)
    function searchUsers() {
	   // Remove all p tags in the modal
	   $("p").remove(".modalP");
	   var xmlhttp1 = new XMLHttpRequest();
	   var username = $("#searchField").val();
	   xmlhttp1.onreadystatechange = function() {
		   if (this.readyState == 4 && this.status == 200) {
			   $("#friendModal").find(".modal-body").append(
				   '<p class="modalP">'+this.responseText+'</p>'
			   );
		   }
	   }
	   xmlhttp1.open("GET", "php_scripts/social_handler.php?username=" + username, true);
	   xmlhttp1.send();
   };

   // Send first and last name of a friend that the user wants to add
   // to social_handler.php
    function addFriend(credentials) {
		$("p").remove(".modalPResponse");
		var xmlhttp2 = new XMLHttpRequest();
		xmlhttp2.onreadystatechange = function() {
			if (this.readyState == 4 && this.status == 200) {
				$("#friendModal").find(".modal-body").append(
				   '<p class="modalPResponse">'+this.responseText+'</p>'
				);
			}
		}
		xmlhttp2.open("POST", "php_scripts/social_handler.php", true);
		xmlhttp2.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
		xmlhttp2.send("adduser=" + credentials);
	};

   // Function to check whether a string is null or a whitespace
   // Code is taken from:
   // https://stackoverflow.com/questions/10232366/how-to-check-if-a-variable-is-null-or-empty-string-or-all-whitespace-in-javascri
   function isEmptyOrSpaces(str){
	   return str === null || str.match(/^ *$/) !== null;
   }

   // Send an entered group name to social_handler.php and create a new group
   // in the database. Also show the created group on the sidebar.
    function createGroup() {
	   // Remove previous warnings
	   $("p").remove(".modalP");
	   var groupName = $("#groupName").val();
	   // If the input is empty or white spaces
	   if (isEmptyOrSpaces(groupName))
	   {
		   $("#groupModal").find(".modal-body").append(
			   '<p class="modalP">The name cant be empty<p>'
		   );
		   $("#groupName").val('');
		   return;
	   }
	   // If the input contains a non-alphanumeric character
	   else if (!/^[a-z0-9]+$/i.test(groupName))
	   {
		   $("#groupModal").find(".modal-body").append(
			   '<p class="modalP">The name contains an invalid character<p>'
		   );
		   return;
	   }
	   var xmlhttp3 = new XMLHttpRequest();
	   xmlhttp3.onreadystatechange = function() {
			if (this.readyState == 4 && this.status == 200) {
				$("#groupModal").find(".modal-body").append(
				   '<p class="modalP">'+this.responseText+'</p>'
				);
			}
		}
	   xmlhttp3.open("POST", "php_scripts/social_handler.php", true);
	   xmlhttp3.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
	   xmlhttp3.send("grName=" + groupName);
    };
   
</script>
</body>
</html>