<?php
/*
 *******************************************************************************************************
*
* Name: photo_access_policy.php
* Display Policy
* Writer: Bach Nguyen (bnguye21@uncc.edu)
* Last Updated: 02/09/2015
* 	02/09/2015
*		- Added session for login ID
*	02/04/2015
*		- Updated codes for writing to login log
*		- Simplify the codes by using function
*	01/29/2015
*		- Updated session - Now using session class
		- Updated database - Now using database class
*
 ********************************************************************************************************
 */
include("./install/database_credentials.inc");
require './lib/Zebra_Database-master/Zebra_Database.php';
// create a new database wrapper object
$db = new Zebra_Database();
$db->debugger_ip = array('152.15.132.120');
// turn debugging on
$db->debug = false;
// connect to the MySQL server and select the database
//$DATABASE_DB = "hell";
$db->connect(
            $DATABASE_HOST,		// host
            $DATABASE_USER,		// user name
            $DATABASE_PASS,		// password
            $DATABASE_DB,		// database
			'',
			'',
			false
        );
$checkConnect = $db->check_connect();
if (isset($checkConnect['status']) and $checkConnect['status'] == "true") {		
	$db->set_charset($DATABASE_CHARSET);
	require './lib/Zebra_Session-master/Zebra_Session.php';
	$link = $db->get_link();
	$security_code = $SESSION_SECURITY;
	$session_lifetime = $SESSION_TIMEOUT;
	$session = new Zebra_Session($link, $security_code, $session_lifetime);
	$arrayUser = array();
	$policy_acceptance_date = date("Y-m-d", strtotime("now"));
	//echo $_SESSION['photo_access']['login']["userArray"];

	if (isset($_SESSION['photo_access']['login']["userArray"])) {
		$arrayUser = $_SESSION['photo_access']['login']["userArray"];
		$arrayUser["policy_acceptance_date"] = $policy_acceptance_date;
	} 

	if (isset($_GET['warningMsg'])) {
		$warningMsg = $_GET['warningMsg'];
	} else $warningMsg = "";

	$status = "";
	if (!empty($_GET["status"])) {
		$status = $_GET["status"];
	} else if (!empty($_POST["status"])){
		$status = $_POST["status"];
	}
	//echo "status: ".$status."<br>";

	$user_id = "";
	if (!empty($_GET['id'])) {
		$user_id = $_GET['id'];
	} else if (!empty($_POST["user_id"])){
		$user_id = $_POST["user_id"];
	}
	$self_approved = "";
	if (!empty($_SESSION['photo_access']['login']["self_approved"])){
		$self_approved = $_SESSION['photo_access']['login']["self_approved"];
	}
	$policy_renew = "";
	if (!empty($_SESSION['photo_access']['login']['policy_renew'])){
		$policy_renew = $_SESSION['photo_access']['login']["policy_renew"];
	}
	$authentication_type = "";
	if (!empty($_SESSION['photo_access']['login']["authentication_type"])){
		$authentication_type = $_SESSION['photo_access']['login']["authentication_type"];
	}
	$allow_standard_access = "";
	if (!empty($_SESSION['photo_access']['login']["allow_standard_access"])){
		$allow_standard_access = $_SESSION['photo_access']['login']["allow_standard_access"];
	}
	//echo "user_id: ".$user_id."<br>";
	//echo "policy_renew: ".$policy_renew."<br>";
	//echo "self_approved: ".$self_approved."<br>";
	//exit();
	if (empty($self_approved) or empty($policy_renew) or empty($authentication_type) or empty($allow_standard_access)) {
		$reDirectLocation = "Location: ./";
		//echo "reDirection Location: ".$reDirectLocation."<br>";
		header($reDirectLocation);
		exit();
	}
	$action = "";
	if (!empty($_POST["accepted"])) {
		$action = $_POST["accepted"];
	} elseif (!empty($_POST["cancel"])) {
		$action = $_POST["cancel"];
	} 
	//echo "action: ".$action."<br>";

	function WriteToLoginLog ($db, $loginLogArray) {
		global $remoteIP, $sessionID;
		$timeNow = date("Y-m-d H:i:s", strtotime("now"));
		//echo "time: ".$timeNow;
		//exit();
		$queryStatus = $db->insert_update(
			'photo_access_user_login_log',
			array(
				'login_id'   =>  '',
				'login_name'   =>  $loginLogArray['login_name'],
				'login_first_name'   =>  $loginLogArray['login_first_name'],
				'login_last_name'   =>  $loginLogArray['login_last_name'],
				'login_user_dept'   =>  $loginLogArray['department'],
				'login_user_email'   =>  $loginLogArray['email'],
				'date_time_in'   =>  date("Y-m-d H:i:s", strtotime("now")),
				'date_time_out'   =>  '',
				'internet_address'   =>  $loginLogArray['internet_address'],
				'session_id'   =>  $loginLogArray['session_id']
		));
		if ($queryStatus) {
			$_SESSION['photo_access']['login']['login_id'] = $db->insert_id();
			return true;
		} else {
			return false;
		}
	}
		
	if ($action == "Accepted") {
		if ($status == "LDAP_Submit") {
			$db->transaction_start();
		
			$db->insert('photo_access_users',$arrayUser);
			$user_profile = array();
			$insertID = $db->insert_id();
			$user_profile["photo_access_user_id"] = $insertID;
			$user_profile["number_per_page"] = $NUMBER_PER_PAGE;
			$user_profile["time_zone"] = $TIME_ZONE;
			$user_profile["date_format"] = $DATE_FORMAT; 
			$user_profile["time_format"] = $TIME_FORMAT;
			//array_walk($user_profile, 'addQuoteToValue', "'");
			$db->insert('photo_access_users_profile', $user_profile);
												
			if ($db->transaction_complete() === true) {
				$loginLogArray = array();
				$loginLogArray['login_name'] = $arrayUser['user_id'];
				$loginLogArray['login_first_name'] = $_SESSION['photo_access']['login']['first_name'];
				$loginLogArray['login_last_name'] = $_SESSION['photo_access']['login']['last_name'];
				$loginLogArray['department'] = $_SESSION['photo_access']['login']['department'];
				$loginLogArray['email'] = $_SESSION['photo_access']['login']['user_email'];
				$loginLogArray['internet_address'] = $_SESSION['photo_access']['login']['remote_ip'];
				$loginLogArray['session_id'] = $_SESSION['photo_access']['login']['session_id'];
				if (WriteToLoginLog ($db, $loginLogArray)) {
					$_SESSION['photo_access']['login']['user_id'] = $insertID;
					$_SESSION['photo_access']['login']['username'] = $arrayUser['user_id'];
					$reDirectLocation = "Location: ./";
					//echo "reDirection Location: ".$reDirectLocation."<br>";
					header($reDirectLocation);
					exit();
				} else {
					$warningMsg = "Error 1: Couldn't save to User Login Log. Please contact System Admin.";
				}
			} else {
				$warningMsg = "Error 2: Couldn't save to Photo Access User. Please contact System Admin.";
			}
		} else if ($status == "LDAP_Update") {
			$updateStatus = $db->update('photo_access_users',$arrayUser, 'id= ?', array($_SESSION['photo_access']['login']['user_id']));
			if ($updateStatus === true) {
				$loginLogArray = array();
				$loginLogArray['login_name'] = $arrayUser['user_id'];
				$loginLogArray['login_first_name'] = $_SESSION['photo_access']['login']['first_name'];
				$loginLogArray['login_last_name'] = $_SESSION['photo_access']['login']['last_name'];
				$loginLogArray['department'] = $_SESSION['photo_access']['login']['department'];
				$loginLogArray['email'] = $_SESSION['photo_access']['login']['user_email'];
				$loginLogArray['internet_address'] = $_SESSION['photo_access']['login']['remote_ip'];
				$loginLogArray['session_id'] = $_SESSION['photo_access']['login']['session_id'];
				if (WriteToLoginLog ($db, $loginLogArray)) {
					$_SESSION['photo_access']['login']['username'] = $arrayUser['user_id'];
					$reDirectLocation = "Location: ./";
					//echo "reDirection Location: ".$reDirectLocation."<br>";
					header($reDirectLocation);
					exit();
				} else {
					$warningMsg = "Error 3: Couldn't save to User Login Log. Please contact System Admin.";
				}
			} else {
				$warningMsg = "Error 4: Couldn't save to Photo Access User. Please contact System Admin.";
			}
		} else if ($status == "Database_Update") {
			$updateQuery = $arrayUser;
			$username = $updateQuery["username"];
			
			$createdDate = date("Y-m-d H:i:s", strtotime("now"));
			$checkQuery = $db->update('photo_access_users', array('policy_acceptance_date' => $policy_acceptance_date, 'updated_by' => $username, 'updated_date' => $createdDate), 'id= ?', array($user_id));
			if ($checkQuery === true) {
				$loginLogArray = array();
				$loginLogArray['login_name'] = $username;
				$loginLogArray['login_first_name'] = $_SESSION['photo_access']['login']['first_name'];
				$loginLogArray['login_last_name'] = $_SESSION['photo_access']['login']['last_name'];
				$loginLogArray['department'] = $_SESSION['photo_access']['login']['department'];
				$loginLogArray['email'] = $_SESSION['photo_access']['login']['user_email'];
				$loginLogArray['internet_address'] = $_SESSION['photo_access']['login']['remote_ip'];
				$loginLogArray['session_id'] = $_SESSION['photo_access']['login']['session_id'];
				if (WriteToLoginLog ($db, $loginLogArray)) {
					$_SESSION['photo_access']['login']['username'] = $username;
					$reDirectLocation = "Location: ./";
					//echo "reDirection Location: ".$reDirectLocation."<br>";
					header($reDirectLocation);
					exit();
				} else {
					$warningMsg = "Error 5: Couldn't save to User Login Log. Please contact System Admin.";
				}
			} else {
				$warningMsg = "Error 6: Couldn't save to Photo Access User. Please contact System Admin.";
			}
		} else if ($status == "Database_Submit") {
			//echo "Database New<br>";
			$reDirectLocation = "Location: edit_user.php?action=Submit&policy_acceptance_date=".$policy_acceptance_date;
			//echo "reDirection Location: ".$reDirectLocation."<br>";
			header($reDirectLocation);
		}
	} 
	if ($action == "Cancel") {
		$session->stop();
		header("Location: ./web/login.php");
	}

	if (empty($_SESSION['photo_access']['login']['default_policy'])) {
		$errorMsg = "Missing Default Policy! Please contact System Administrator.";
	} else {
		$checkQuery = $db->select('*', 'photo_access_policy', 'active = ? and id = ?', array('Y',$_SESSION['photo_access']['login']['default_policy']));
		if ($checkQuery) {
			$row_count = $db->returned_rows;
			//echo "<br>row count: ".$row_count;
			$recordArray = array();
			$outputArray = array();
			while($obj = $db->fetch_obj()) {
				$recordArray['id'] = $obj->id;
				$recordArray['policy_content'] = $obj->policy_content;
				$recordArray['created_user'] = $obj->created_user;
				$recordArray['created_date'] = $obj->created_date;
				$recordArray['updated_user'] = $obj->updated_user;
				$recordArray['updated_date'] = $obj->updated_date;
				$outputArray[] = $recordArray;		
			} // close while loop
		} else {
			$row_count = 0;
			$errorMsg = "Error 7: Couldn't load policy document.  Please contact System Admin."; //$mysqli->connect_error; 
			$session->stop();
		}
	}
} else {
	$errorMsg = $checkConnect["error"];
	$session->stop();
}
include ("./web/header.html");
//include ("./web/menu.html");
if (!isset($errorMsg) and $row_count >= 0) {
?>
	<link href="./css/mainstyle.css" rel="stylesheet" type="text/css">
	<div style="width:75%; font-family:Arial, Helvetica, sans-serif;font-size:12px;line-height:18px; border: 0px solid black; margin: 0px auto;background-color:white">
	<br>
		<form action="photo_access_policy.php" method="post" name="mainForm">
			<table width=100%>
				<tr>
					<td><br>
						<div class="mainTitle"><strong><center>Photo Access ID - Policy</center></strong></div>
					</td>
				</tr>
				<tr>
					<td>
						<center><?php if (isset($warningMsg) and strpos($warningMsg, "Success") !== false) {
							echo "<br><font color=blue>".$warningMsg."</font>"; 
							} else {
							echo "<br><font color=red>".$warningMsg."</font>"; 
							}
							?></center>
					</td>
				</tr>
				<tr>
					<td>
						<div class="displayPolicy">
						<textarea name="policy_content" rows="50" cols="150" wrap="physical" class="policy_textarea"><?php echo $outputArray[0]["policy_content"] ?></textarea>
						</div>
					</td>
				</tr>
				<tr>
					<td>
						<input type="submit" name="accepted" value="Accepted" class="dark_green_button">
						<input type="submit" name="cancel" value="Cancel" class="deleteButton">
						<input type="hidden" name="status" value="<?php echo $status; ?>">
						<input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
					</td>
				</tr>
				<tr>
					<td>
						<br><br><br>
					</td>
				</tr>
			</table>
		</form>
	</div>
	<?php include ("./web/footer.html"); ?>
	<?php
} else {
	?>
	<link href="./css/mainstyle.css" rel="stylesheet" type="text/css">
	<div style="width:75%; font-family:Arial, Helvetica, sans-serif;font-size:12px;line-height:18px; border: 0px solid black; margin: 0px auto;background-color:white">
		<br><br>
		<table width=100%>
			<tr>
				<td>
					<div class="mainTitle"><strong><center>Photo Access ID - Policy</center></strong></div>
				</td>
			</tr>
			<tr>
				<td>
					<center><br><br>
					<?php if (isset($errorMsg)) echo "<font color=red>".$errorMsg."</font>"; else echo "<font color=blue>Missing Policy Name!</font>"; ?>
					</center>
				</td>
			</tr>
			<tr>
				<td>
					<br><br><br>
				</td>
			</tr>
		</table>
	</div>
	<?php include ("./web/footer.html"); ?>
	<?php	
}
?>