<?php
/*
 *******************************************************************************************************
*
* Name: policy_editor.php
* Edit policy - it will store policy in photo Access policy table
* Writer: Bach Nguyen (bnguye21@uncc.edu)
* Last Updated: 07/07/2014
*	03/05/2015
*		- Change database, session class
*	07/07/2014
*		- Created file	
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
	
	if (isset($_SESSION['photo_access']['login']['username'])) {
		$time_zone = $_SESSION['photo_access']['login']['time_zone'];
		$date_format = $_SESSION['photo_access']['login']['date_format'];	
		$time_format = $_SESSION['photo_access']['login']['time_format'];
	} else {
		$time_zone = $TIME_ZONE;
		$date_format = $DATE_FORMAT;
		$time_format = $TIME_FORMAT;
	}
	$datetime_format = $date_format." ".$time_format;
	date_default_timezone_set($time_zone); 
 
	if (!isset($_SESSION['photo_access']['login']['username'])) {
		if (isset($_GET["id"])) {
			$page_link = "./admin/policy_editor.php?action=Update&id=".$_GET["id"];
		} else {
			$page_link = "./admin/policy_editor.php";
		}
		$reDirectLocation = "Location: ./../web/login.php?page_link=".urlencode($page_link);
		//echo "1.reDirection Location: '".$reDirectLocation."'<br>";
		header($reDirectLocation);
		exit();
	} elseif (isset($_SESSION['photo_access']['login']['user_group']) and intval($_SESSION['photo_access']['login']['user_group']) >= 3) {
		$warningMsg = "Error. You don't have permission to edit policy";
		$reDirectLocation = "Location: ./../admin/index.php?warningMsg=".$warningMsg;
		//echo "2.reDirection Location: '".$reDirectLocation."'<br>";
		//header($reDirectLocation);
		exit();
	}
	$errorMsg = "";
	if (!empty($_GET['action'])) {
		$action = trim($_GET["action"]);
	} else {
		$action = "";
	}
	if (!empty($_GET['id'])) {
		$id = trim($_GET["id"]);
	} else {
		$id = "";
	}
	if (!empty($_GET['policy_name'])) {
		$policy_name = trim($_GET["policy_name"]);
	} else {
		$policy_name = "";
	}
	if (!empty($_GET['policy_content'])) {
		$policy_content = trim($_GET["policy_content"]);
	} else {
		$policy_content = "";
	}
	if (!empty($_GET['active'])) {
		$active = trim($_GET["active"]);
	} else {
		$active = "";
	}
	if (!empty($_GET["error"])) {
		$warningMsg = $_GET["error"];
	} else {
		$warningMsg = "";
	}
	//echo "policy content: ".$policy_content."<br>";
	echo "action: ".$action."<br>";
	//echo "id: ".$id."<br>";
	//echo "policy name: ".$policy_name."<br>";

	if ($action == "Submit") {
		$title = "Create New Policy";
		//echo "title: ".$title."<br>";	
	} elseif ($action == "Update") {
		//if ($active == "Y") {
		$title = "Policy ID = ".$id;
		if ($id != "") {
			$arrayPolicyStatus = $db->query('SELECT * FROM photo_access_policy WHERE active = ? AND id = ?', array('Y',$id));
		} else {
			$arrayPolicyStatus = $db->query('SELECT * FROM photo_access_policy WHERE active = ? AND policy_name = ?', array('Y',$policy_name));
		}
		if ($arrayPolicyStatus) {
			$arrayPolicy = $db->fetch_assoc_all();
			$id = $arrayPolicy[0]['id'];
			$policy_name = $arrayPolicy[0]['policy_name'];
			$policy_content = $arrayPolicy[0]['policy_content'];
			$active = $arrayPolicy[0]['active'];
		
			$created_date = date($datetime_format, strtotime($arrayPolicy[0]['created_date']));
			//$created_user = $arrayPolicys[0]['created_user'];
			$createdName = getName($db, $arrayPolicy[0]['created_user']);
			$updated_date = date($datetime_format, strtotime($arrayPolicy[0]['updated_date']));
			//$updated_user = $arrayPolicys[0]['updated_user'];
			$updatedName = getName($db, $arrayPolicy[0]['updated_user']);

		} else {
			$warningMsg = "Error: Query has error!";
		}
	} else {
		$errorMsg = "Error: Missing action";
	}
} else {
	$errorMsg = $checkConnect['error'];
}

function getName ($db, $nameLookup) {
	$queryUserInfoString = "SELECT * FROM photo_access_users where user_id=?";
	$queryUserInfoStatus = $db->query($queryUserInfoString, array($nameLookup));
	if ($queryUserInfoStatus !== false) {
		$nameArray = $db->fetch_assoc_all();
		$nameString = $nameArray[0]['first_name']." ".$nameArray[0]['last_name'];
	} else {
		$nameString = $nameLookup;
	}
	return $nameString;
}

//echo "Error: ".$errorMsg."<br>";
//echo "Warning: ".$warningMsg."<br>";
if (empty($errorMsg)) {
?>
	<?php include ("./../web/header.html");?>
	<?php include ("./../web/menu.html"); ?>
	<script Language="JavaScript">
		function Close() {  
			opener.Reload();  
			self.close();  
		}  

		function trim(stringToTrim) {
			return stringToTrim.replace(/^\s+|\s+$/g,"");
		}

		function removeError() {
			document.getElementById("errorMsg").innerHTML = "";
		}
	
		function checkValidator(theForm) {
			if (theForm.action.value != "Delete") {
				var intRegex = /^\d+$/;
				var policy_name = trim(document.getElementById('policy_name').value);
				var policy_content = trim(document.getElementById('policy_content').value);
				if (policy_name == "") {
					document.getElementById("errorMsg").innerHTML = "Please enter 'Policy Name' before clicking Submit/Update";
					theForm.policy_name.style.backgroundColor="#D0E2ED";
					theForm.policy_name.focus();
					return (false);
				}
				if (policy_content == "") {
					document.getElementById("errorMsg").innerHTML = "Please enter 'Policy' before clicking Submit/Update";
					theForm.policy_content.style.backgroundColor="#D0E2ED";
					theForm.policy_content.focus();
					return (false);
				}
			} 
			if (theForm.action.value == "Delete") {
				var reply = confirm("Are you sure to delete this record?");
				if (reply == false) {
					return (false);
				}
			}
		}
		function updateAction(action) {
			//alert ("action: " + action);
			document.getElementById("action").value = action;
		}
	</script>
	<link href="./../css/mainstyle.css" rel="stylesheet" type="text/css">
	<div style="width:80%; font-family:Arial, Helvetica, sans-serif;font-size:12px;line-height:18px; border: 0px solid black; margin: 0px auto;background-color:white">
	<br>
	<!--
	<form action="policy_editor_handler.php" method="post" onsubmit="return checkValidator(this)" name="mainForm">
	<form action="policy_editor_handler.php" method="post" name="mainForm">
	-->
	<form action="policy_editor_handler.php" method="post" onsubmit="return checkValidator(this)" name="mainForm">
		<table width=100% style="padding:25px; text-align:center; background-color:#EFEFEF;">
			<tr>
				<td colspan="2"><br><br>
					<div class="mainTitle"><strong><center>Photo Access ID - Policy Editor</center></strong></div><br>
				</td>
			</tr>
			<tr>
				<td colspan="2"><center>
					<div style="font-family:Arial, Helvetica, sans-serif; font-size:12px; color:red;" id="errorMsg">
						<?php 
						if (strpos($warningMsg, "Error") === false) {
							echo "<div style=\"color:#0011FF; text-align:center; background-color:#EFEFEF\" id=\"warningMsg\">";
							if (!empty($warningMsg)) echo $warningMsg; 
							echo "</div>";
						} else {
							echo "<div style=\"color:#FF0000; text-align:center; background-color:#EFEFEF\" id=\"warningMsg\">";
							if (!empty($warningMsg)) echo $warningMsg; 
							echo "</div>";
						}
						//if (isset($warningMsg)) echo "<font color=red id=\"warningMsg\">".$warningMsg."</font>";
						?>
					</div></center>
				</td>
			</tr>
			<tr>
				<td style="padding-top: .5em; padding-bottom: .5em; padding-left: 1.5em;">
					<b>Policy Name</b>
				</td>
				<td style="padding-top: .5em; padding-bottom: .5em;">
					<input type="text" size="140" maxlength="128" name="policy_name" id="policy_name" value="<?php if (!empty($policy_name)) echo $policy_name;?>">
				</td>
			</tr>
			<tr>
				<td colspan="2" style="padding-left: 1.5em; padding-top: .5em; padding-bottom: .5em;">
					<textarea style="padding-top: 2em; padding-left: 1em;padding-right: 1em;" name="policy_content" rows="50" cols="145" wrap="physical" class="mytextarea"><?php if (!empty($policy_content)) echo $policy_content; ?></textarea>
				</td>
			</tr>
			<tr>
				<td style="padding-left: 1.5em;">
					<b>Active</b>
				</td>
				<td>
					<input type="checkbox" name="active" id="active" value="Y" <?php if ($active=="Y") echo " checked";?>>
				</td>
			</tr>
			<?php
			if ($action=="Update") {
			echo "<tr>
				<td style=\"padding-left: 1.5em;\" >
					<b>Created Date</b>
				</td>
				<td>";
					if (!empty($created_date)) echo $created_date." by ".$createdName;  
				echo "</td>
			</tr>
			
			<tr>
				<td style=\"padding-left: 1.5em;\">
					<b>Modified Date</b>
				</td>
				<td>";
					if (!empty($updated_date)) echo $updated_date." by ".$updatedName;
				echo "</td>
			</tr>\n";
			}
			?>	
			
			<tr>
				<td><br>
					<center>
					<?php
					if ($action=="Update") {
						echo "<input type=\"submit\" name=\"update\" value=\"Update\" class=\"dark_green_button\" onClick=\"updateAction('Update')\">";
						if ($_SESSION['photo_access']['login']['default_policy'] != $policy_name)
							echo " <input type=\"submit\" name=\"default\" value=\"Set Default\" class=\"blue_button\" onClick=\"updateAction('Set Default')\">";
					} else { 
						echo "<input type=\"submit\" name=\"submit\" value=\"Submit\" class=\"dark_green_button\" onClick=\"updateAction('Submit')\">";
					}
					?>
					</center>
					<?php
						if (isset($_GET["id"])) {
							echo "<input type=\"hidden\" name=\"id\" id=\"id\" value=\"".$id."\">\n";
						}
					?>
					<input type="hidden" name="action" id="action" value="<?php echo $action;?>">
				</td>
				<td><br>
					<center>
					<?php
						if ($action=="Update") {
							if (isset($_SESSION['photo_access']['login']['user_group']) and intval($_SESSION['photo_access']['login']['user_group']) < 2) {
								echo "&nbsp<input type=\"submit\" name=\"delete\" value=\"Delete\" class=\"deleteButton\" onClick=\"updateAction('Delete')\">";
							}
						}
					?>
					<br><br>
					</center>
				</td>
			</tr>
			<tr>
				<td><br><br><br>
				</td>
			</tr>
		</table>
		<input type="hidden" name="id" value="<?php echo $id; ?>">
	</form>
</div>
	<?php include ("./../web/footer.html"); ?>	
<?php
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
