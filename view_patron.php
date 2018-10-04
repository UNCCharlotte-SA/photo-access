<?php
/*
 *******************************************************************************************************
*
* Name: view_patron.php
* View detail of a user
* Writer: Bach Nguyen (bnguye21@uncc.edu)
* Last Updated: 02/10/2015
*	02/10/2015
*		- Update session class, database class
*
 ********************************************************************************************************
 */
//ini_set('session.bug_compat_warn', 0);
//ini_set('session.bug_compat_42', 0);

include("./install/database_credentials.inc"); 
// include the wrapper class
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
	
	$time_zone = $_SESSION['photo_access']['login']['time_zone'];
	$date_format = $_SESSION['photo_access']['login']['date_format'];
	$time_format = $_SESSION['photo_access']['login']['time_format'];
	$datetime_format = $date_format." ".$time_format;
	date_default_timezone_set($time_zone);
	
	if (isset($_SESSION['photo_access']['login']['username'])) {	
		if (!empty($_GET["id"])) {
			$id = $_GET["id"];
		} else $errorMsg = "Missing Patron ID";

		if (!empty($_GET["error"])) {
			$errorMsg = $_GET["error"];
		} else $errorMsg = "";

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
		
		if ($id != "" and ($errorMsg == "" or strstr($errorMsg, "Success"))) {
			$queryString = "SELECT a.*, b.pic_id, b.pic_location, b.default_pic, b.pic_image FROM patron_user a left join patron_pic b on a.patron_number=b.patron_id where b.default_pic='Y' and a.id=".$id;
			//echo "queryString: ".$queryString."<br>";
			$queryStatus = $db->query($queryString);
				
			if (!empty($queryStatus)) {
				$arrayUser = $db->fetch_assoc_all();
				//echo "<pre>";
				//	echo print_r($arrayUser);
				//echo "</pre>";
				$patron_user_id = $arrayUser[0]['id'];
				$patron_number = $arrayUser[0]['patron_number'];
				$patron_first_name = $arrayUser[0]['patron_first_name'];
				$patron_middle_name = $arrayUser[0]['patron_middle_name'];
				$patron_last_name = $arrayUser[0]['patron_last_name'];
				$patron_gender = $arrayUser[0]['patron_gender'];
				$patron_is_active = $arrayUser[0]['patron_is_active'];
				$patron_ferpa_flag = $arrayUser[0]['patron_ferpa_flag'];
				$patron_hr_classification = $arrayUser[0]['patron_hr_classification'];
				$patron_primary_classification = $arrayUser[0]['patron_primary_classification'];
				$patron_last_mod_datetime = $arrayUser[0]['patron_last_mod_datetime'];
				$updated_user = getName ($db,$arrayUser[0]['updated_user']);
				$updated_date = date($datetime_format, strtotime($arrayUser[0]['updated_date']));
				$pic_location = $arrayUser[0]['pic_location'];
				$default_pic = $arrayUser[0]['default_pic'];
				$pic_image = base64_decode($arrayUser[0]['pic_image']);
			
				$createdDate = date("Y-m-d H:i:s", strtotime("now"));
				$insertArray = array();
				$insertArray['id'] = '';
				//$insertArray['user_first_name'] = $_SESSION['photo_access']['login']['first_name'];
				//$insertArray['user_last_name'] = $_SESSION['photo_access']['login']['last_name'];
				//$insertArray['user_email'] = $_SESSION['photo_access']['login']['user_email'];
				//$insertArray['user_dept'] = $_SESSION['photo_access']['login']['department'];
				$insertArray['date_searching'] = $createdDate;
				$lookup_value = $patron_last_name.", ".$patron_first_name." (".$patron_number.")";
				$insertArray['searching_string'] = $lookup_value;
				$insertArray['patron_id'] = $patron_number;
				$insertArray['patron_first_name'] = $patron_first_name;
				$insertArray['patron_last_name'] = $patron_last_name;
				$insertArray['login_id'] = $_SESSION['photo_access']['login']['login_id'];;

				$insertQueryLogStatus = $db->insert('photo_access_searching_log',$insertArray);
				//echo $insertQueryLogStatus."<br>";

				if ($insertQueryLogStatus) {
					$warningMsg = "Success added this record to search log. Search Log ID#: ".$db->insert_id();
				} else {
					$warningMsg = "Error: Couldn't insert search log into table!";
				}	
			} else {
				$warningMsg = "Error: Couldn't load patron ID: ".$id;
			}	
			include ("./web/header.html");
			include ("./web/menu.html");				
		} else {
			$warningMsg = "Error: Missing patron ID!";
		}
	} else {
		$page_link = "./index.php";
		$reDirectLocation = "Location: ./web/login.php?page_link=".$page_link;
		//echo "reDirection Location: '".$reDirectLocation."'<br>";
		header($reDirectLocation);
		exit();	
	}

?>
	<link href="./css/mainstyle.css" rel="stylesheet" type="text/css">
	<div style="width:80%; font-family:Arial, Helvetica, sans-serif;font-size:12px;line-height:18px; border: 0px solid black; margin: 0px auto;background-color:white">

	<table  width=100% style="padding:25px; text-align:center; background-color:#E3E3E3;">
		<tr>
			<td colspan="2"><br><br><br>
				<div class="mainTitle"><strong><center>Photo Access ID - Patron ID: <?php echo $patron_number; ?></center></strong></div><br>
			</td>
		</tr>
			<tr>
				<td colspan="2">
					<?php
						if (strpos($warningMsg, "Error") === false) {
							echo "<div style=\"color:#0011FF; text-align:center; background-color:#E3E3E3\" id=\"warningMsg\">";
							if (!empty($warningMsg)) echo $warningMsg; 
							echo "</div>";
						} else {
							echo "<div style=\"color:#FF0000; text-align:center; background-color:#E3E3E3\" id=\"warningMsg\">";
							if (!empty($warningMsg)) echo $warningMsg; 
							echo "</div>";
						}
					?>
				</td>
			</tr>
			<tr>
				<td width=75%>
					<table width=100%>
						<tr>
							<td width=25% style="padding-top: .5em; padding-bottom: .5em; padding-left: .5em">
								<b>First Name:</b>				
							</td>
							<td width=75% style="padding-top: .5em; padding-bottom: .5em;">
								<input type="text" size="64" maxlength="32" readonly="readonly" name="first_name" id="first_name" value="<?php if (!empty($patron_first_name)) echo $patron_first_name; ?>">
							</td>
						</tr>
						<tr>
							<td width=25% style="padding-top: .5em; padding-bottom: .5em; padding-left: .5em">
								<b>Middle Name:</b>				
							</td>
							<td width=75% style="padding-top: .5em; padding-bottom: .5em;">
								<input type="text" size="64" maxlength="32" readonly="readonly" name="middle_name" id="middle_name" value="<?php if (!empty($patron_middle_name)) echo $patron_middle_name; ?>">
							</td>
						</tr>
						<tr>
							<td width=25% style="padding-top: .5em; padding-bottom: .5em; padding-left: .5em">
								<b>Last Name:</b>				
							</td>
							<td width=75% style="padding-top: .5em; padding-bottom: .5em;">
								<input type="text" size="64" maxlength="32" readonly="readonly" name="last_name" id="last_name" value="<?php if (!empty($patron_last_name)) echo $patron_last_name; ?>">
						</td>
						</tr>
						<tr>
							<td width=25% style="padding-top: .5em; padding-bottom: .5em; padding-left: .5em">
								<b>Gender:</b>				
							</td>
							<td width=75% style="padding-top: .5em; padding-bottom: .5em;">
								<input type="text" size="32" maxlength="15" readonly="readonly" name="gender" id="gender" value="<?php if (!empty($patron_gender)) echo $patron_gender; ?>">
							</td>
						</tr>
						<tr>
							<td width=25% style="padding-top: .5em; padding-bottom: .5em; padding-left: .5em">
								<b>Active:</b>				
							</td>
							<td width=75% style="padding-top: .5em; padding-bottom: .5em;">
								<input type="text" size="15" maxlength="15" readonly="readonly" name="active" id="active" value="<?php if (!empty($patron_is_active)) echo $patron_is_active; ?>">
							</td>
						</tr>
						<tr>
							<td width=25% style="padding-top: .5em; padding-bottom: .5em; padding-left: .5em">
								<b>Ferpa Flag:</b>				
							</td>
							<td width=75% style="padding-top: .5em; padding-bottom: .5em;">
								<input type="text" size="15" maxlength="15"  readonly="readonly" name="ferpa_flag" id="ferpa_flag" value="<?php if (!empty($patron_ferpa_flag)) echo $patron_ferpa_flag; ?>">
							</td>
						</tr>
						<tr>
							<td width=25% style="padding-top: .5em; padding-bottom: .5em; padding-left: .5em">
								<b>HR Classification:</b>				
							</td>
							<td width=75% style="padding-top: .5em; padding-bottom: .5em;">
								<input type="text" size="64" maxlength="32" readonly="readonly" name="hr_classification" id="hr_classification" value="<?php if (!empty($patron_hr_classification)) echo $patron_hr_classification; ?>">
							</td>
						</tr>
						<tr>
							<td width=25% style="padding-top: .5em; padding-bottom: .5em; padding-left: .5em">
								<b>Primary Classification:</b>				
							</td>
							<td width=75% style="padding-top: .5em; padding-bottom: .5em;">
								<input type="text" size="64" maxlength="32" readonly="readonly" name="primary_classification" id="primary_classification" value="<?php if (!empty($patron_primary_classification)) echo $patron_primary_classification; ?>">
							</td>
						</tr>	
						<tr>
							<td width=25% style="padding-top: .5em; padding-bottom: .5em; padding-left: .5em">
								<b>Last Modified Date:</b>				
							</td>
							<td width=75% style="padding-top: .5em; padding-bottom: .5em;">
								<input type="text" size="32" maxlength="32" readonly="readonly" name="last_mod_datetime" id="last_mod_datetime" value="<?php if (!empty($patron_last_mod_datetime)) echo date($datetime_format, strtotime($patron_last_mod_datetime)); ?>">
							</td>
						</tr>				
						<tr>
							<td width=25% style="padding-top: .1em; padding-bottom: .1em; padding-left: .5em">
								<b>Updated Date:</b>
							</td>
							<td width=75% style="padding-top: .1em; padding-bottom: .1em;">
								<?php if (!empty($updated_date)) echo $updated_date." by ".$updated_user; ?>
							</td>
						</tr>
					</table>
				</td>
				<td width=25% style="vertical-align:middle !important;">
					<?php
					if (empty($pic_image)) {
						echo "<img src=\"./admin/".$pic_location."\" alt=\"".$patron_number."\" width=\"200\" height=\"240\">\n";
					} else {
						echo "<img src=\"data:image/png;base64,".base64_encode($pic_image)."\" alt=\"".$patron_number."\" width=\"200\" height=\"240\">\n";
					}
					?>
				</td>
			</tr>
			<tr>
				<td colspan="2"><br><center>
					<input type="button" name="Back" value="Back" class=dark_green_button onClick="parent.location='./index.php'"></center>
				</td>
			</tr>
			<tr>
				<td colspan="2">
					<br><br><br>
				</td>
			</tr>
		</table>
	</div>
<?php
	include ("./web/footer.html");
} else {
	include ("./web/header.html");
?>	
	<link href="./css/mainstyle.css" rel="stylesheet" type="text/css">
	<div style="width:80%; font-family:Arial, Helvetica, sans-serif;font-size:12px;line-height:18px; border: 0px solid black; margin: 0px auto;background-color:white">
	<table  width=100% style="padding:25px; text-align:center; background-color:white;">
		<tr>
			<td><br>
				<div class="mainTitle"><strong><center>Photo Access ID - View Patron Record</center></strong></div><br>
			</td>
		</tr>
		<tr>
			<td><br>
				<div style="color:#FF0000; text-align:center; background-color:#FFFFFF" id="errorMsg">
					<?php echo $checkConnect['error']; ?>
				</div> 
			</td>
		</tr>
		<tr>
			<td>
				<br><br><br>
			</td>
		</tr>
	</table>
<?php
	include ("./web/footer.html");
}
?>
	