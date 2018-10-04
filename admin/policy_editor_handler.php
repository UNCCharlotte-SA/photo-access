<?php
/*
 *******************************************************************************************************
*
* Name: policy_editor_handler.php
* Edit policy - it will store policy in photo Access policy table
* Writer: Bach Nguyen (bnguye21@uncc.edu)
* Last Updated: 03/05/2015
*	03/05/2015
*		- Change database, session class
*	07/07/2014
*		- Created file	
*
 ********************************************************************************************************
 */
include("./../install/database_credentials.inc"); 
// include the wrapper class
require './../lib/Zebra_Database-master/Zebra_Database.php';
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
	require './../lib/Zebra_Session-master/Zebra_Session.php';
	$link = $db->get_link();
	$security_code = $SESSION_SECURITY;
	$session_lifetime = $SESSION_TIMEOUT;
	$session = new Zebra_Session($link, $security_code, $session_lifetime);
	//echo "Here session: ".$_SESSION['photo_access']['login']['session_id']." - scode: ".$security_code." - sectime: ".$session_lifetime;
	//print_r('<pre>');
	//print_r($session->get_settings());
	//print_r('</pre>');
	if (!isset($_SESSION['photo_access']['login']['username'])) {
		$reDirectLocation = "Location: ./index.php";
		header($reDirectLocation);
		exit();
	} elseif (isset($_SESSION['photo_access']['login']['user_group']) and intval($_SESSION['photo_access']['login']['user_group']) >= 3) {
		$warningMsg = "You don't have permission to create/edit Photo Access - Policy Editor";
		$reDirectLocation = "Location: ./../index.php?warningMsg=".$warningMsg;
		header($reDirectLocation);
		exit();
	}

	$username = $_SESSION['photo_access']['login']['username'];
	$userGroup = $_SESSION['photo_access']['login']['user_group'];
	$department = $_SESSION['photo_access']['login']['department'];
	$first_name	= $_SESSION['photo_access']['login']['first_name'];
	$last_name = $_SESSION['photo_access']['login']['last_name'];
	$login_id = $_SESSION['photo_access']['login']['login_id'];
	$number_per_page = $_SESSION['photo_access']['login']['number_per_page'];
	$time_zone = $_SESSION['photo_access']['login']['time_zone'];
	$date_format = $_SESSION['photo_access']['login']['date_format'];
	$time_format = $_SESSION['photo_access']['login']['time_format'];
	$datetime_format = $date_format." ".$time_format;
	$self_approved = $_SESSION['photo_access']['login']['self_approved'];
	$policy_renew = $_SESSION['photo_access']['login']['policy_renew'];
	//echo "<br><br>user name: ".$username." - User group: ".$userGroup." - Department: ".$department." - Lastname, Firstname: ".$last_name.", ".$first_name."<br>";
	//echo "number per page: ".$number_per_page." - date format: ".$date_format."<br>";
	//echo "self approved: ".$self_approved." - policy renew: ".$policy_renew."<br>";
	date_default_timezone_set($time_zone);
	//echo date_default_timezone_get(); 

	if (isset($_POST["action"])) {
		if (isset($_POST["active"]) and trim($_POST["active"]) == "Y") {
			$active = "Y";
		} else {
			$active = "N";
		}
		$policy_name = trim($_POST["policy_name"]);
		$policy_content = trim($_POST["policy_content"]);

		if (!empty($_POST["update"])) {
			$action = $_POST["update"];
		} elseif (!empty($_POST["delete"])) {
			$action = $_POST["delete"];
		} elseif (!empty($_POST["submit"])) {
			$action = $_POST["submit"];
		} elseif (!empty($_POST["default"])) {
			$action = $_POST["default"];
		} 

		if ($action != "Submit") {
			$id = trim($_POST["id"]);
		} 
		
		$dataQuery = array('policy_name'=>$policy_name,
						'policy_content'=>$policy_content,
						'active'=>$active,
						'action'=>$action);
		//echo "<pre>";
		//	print_r($dataQuery);
		//echo "</pre>";
		//exit;
		if ($action != "Delete") {	
			if (empty($policy_name)) {
				$errorMsg = "Error! Please enter 'Policy Name' before clicking Submit/Update";
				if ($action == "Update") {
					$dataQuery['id'] = $id;
				}
				$dataQuery['error'] = $errorMsg;
				header("location: ./policy_editor.php?".http_build_query($dataQuery));
				exit();
			} 
			if (empty($policy_content)) {
				$errorMsg = "Error! Please enter 'Policy' before clicking Submit/Update";
				if ($action == "Update") {
					$dataQuery['id'] = $id;
				}
				$dataQuery['error'] = $errorMsg;
				header("location: ./policy_editor.php?".http_build_query($dataQuery));
				exit();
			} 
		}
	
		$createdDate = date("Y-m-d H:i:s", strtotime("now"));
	
		if ($action == "Submit") {
			$insertQuery = $dataQuery;

			$insertQuery['created_user'] = $username;
			$insertQuery['created_date'] = $createdDate;
			$insertQuery['updated_user'] = $username;
			$insertQuery['updated_date'] = $createdDate;
			
			unset($insertQuery['action']);
		} elseif ($action == "Update") {
			$updateQuery = $dataQuery;
		
			$updateQuery['updated_user'] = $username;
			$updateQuery['updated_date'] = $createdDate;
			
			unset($updateQuery['action']);
		}

		if ($action == "Delete") {
			$deleteRecordStatus = $db->delete('photo_access_policy', 'id = ?', array($id));
			if ($deleteRecordStatus) {
				$errorMsg = "Success deleted Course ID=".$id;
				//echo "errorMsg: ".$errorMsg."<br>";
				$dataQuery['warningMsg'] = $errorMsg;
				header("location: ./policy_management.php?warningMsg=".$errorMsg);
				exit();
			} else {
				$dataQuery['id'] = $id;
				$errorMsg = "Error: Couldn't delete this record.";
				//echo "errorMsg: ".$errorMsg."<br>";
				$dataQuery['action'] = "Delete";
				$dataQuery['error'] = $errorMsg;
				header("location: ./policy_editor.php?".http_build_query($dataQuery));
				exit();
			}
		} elseif ($action == "Set Default") {
			$keyArray = array();
			$keyArray[] = 'ldap_default_policy';
			$updateDefaultPolicyStatus = $db->update('photo_access_config', array('value' => $id), '`key` = ?', $keyArray);
			//echo "status update for ".$key."=".$value." is: '".$checkStatus."'<br>";
			if ($updateDefaultPolicyStatus) {
				$_SESSION['photo_access']['login']['default_policy'] = $id;
				$dataQuery['id'] = $id;
				$errorMsg = "Success set this policy name to Default Policy";
				//echo "errorMsg: ".$errorMsg."<br>";
				$dataQuery['action'] = "Update";
				$dataQuery['error'] = $errorMsg;
				header("location: ./policy_editor.php?".http_build_query($dataQuery));
				exit();
			} else {
				$dataQuery['id'] = $id;
				$errorMsg = "Error: Couldn't set this policy to Default Policy";
				//echo "errorMsg: ".$errorMsg."<br>";
				$dataQuery['action'] = "Update";
				$dataQuery['error'] = $errorMsg;
				header("location: ./policy_editor.php?".http_build_query($dataQuery));
				exit();
			}	
		} else {
			if ($action == "Submit") {
				$addNewRecordStatus = $db->insert('photo_access_policy',$insertQuery);
			} elseif ($action == "Update") {
				$addNewRecordStatus = $db->update('photo_access_policy', $updateQuery, 'id = ?', array($id)) ;
			}

			if ($addNewRecordStatus) {
				if ($action == "Submit") {
					$insertID = $db->insert_id();
					$errorMsg = "Success added this record to Policy table. ID=".$insertID;
					//echo "errorMsg: ".$errorMsg."<br>";
					//$dataQuery['error'] = $errorMsg;
					//$dataQuery['id'] = $addNewRecordStatus["id"];
					//$dataQuery['action'] = "Update";
					$successDataQuery = array();
					$successDataQuery['error'] = $errorMsg;
					$successDataQuery['id'] = $insertID;
					$successDataQuery['active'] = $active;
					$successDataQuery['action'] = "Update";
					header("location: ./policy_editor.php?".http_build_query($successDataQuery));
					exit();
				} elseif ($action == "Update") {
					//$dataQuery['id'] = $id;
					//$dataQuery['action'] = $action;
					$errorMsg = "Success updated this record to Policy table. ID=".$id;
					//echo "errorMsg: ".$errorMsg."<br>";
					//$dataQuery['error'] = $errorMsg;
					$successDataQuery = array();
					$successDataQuery['error'] = $errorMsg;
					$successDataQuery['id'] = $id;
					$successDataQuery['active'] = $active;
					$successDataQuery['action'] = "Update";
					header("location: ./policy_editor.php?".http_build_query($successDataQuery));
					exit();
				}
			} else {
				if ($action == "Update") {
					$dataQuery['id'] = $id;
					$dataQuery['action'] = $action;
				}
				$errorMsg = "Error: Query has error!";
				//echo "errorMsg: ".$errorMsg."<br>";
				$dataQuery['error'] = $errorMsg;
				//echo "<pre>";
				//	print_r($dataQuery);
				//echo "</pre>";
				header("location: ./policy_editor.php?".http_build_query($dataQuery));
				exit();
			}
		}
	} else {
		$errorMsg = "Error: Can't access direct to this page";
		//echo "errorMsg: ".$errorMsg."<br>";
		$dataQuery['error'] = $errorMsg;
		//echo "<pre>";
		//	print_r($dataQuery);
		//echo "</pre>";
		header("location: ./policy_editor.php?".http_build_query($dataQuery));
		exit();
	}	
} else {
?>
	<?php include ("./../web/header.html");?>
	<?php include ("./../web/menu.html"); ?>
	<link href="./../css/mainstyle.css" rel="stylesheet" type="text/css">
	<div style="width:80%; font-family:Arial, Helvetica, sans-serif;font-size:12px;line-height:18px; border: 0px solid black; margin: 0px auto;background-color:white">
		<br>
		<table  width=100% style="padding:5px; background-color:#EFEFEF;">
			<tr>
				<td colspan="2"><br><br><center>
					<div class="mainSmallTitle"><strong>Photo Access ID - Policy Editor</strong></div></center>
				</td>
			</tr>
			<tr>
				<td colspan="2"><br><center>
					<div style="font-family:Arial, Helvetica, sans-serif;font-size:12px;color:red">
						<?php
						if (!empty($errorMsg)) echo $errorMsg; 
						?>
					</div></center><br><br>
				</td>
			</tr>
	</table>
	</div>
	<BR><BR><BR>
	<?php include ("./../web/footer.html"); ?>	
<?php	
}	
?>