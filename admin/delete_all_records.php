<?php 
/*
 *******************************************************************************************************
*
* Name: delete_all_records.php
* Delete all records for table: patron_user, patron_pic, photo_access_searching_log, photo_access_login_log
* Writer: Bach Nguyen (bnguye21@uncc.edu)
* Last Updated: 02/18/2015
*	02/16/2015
*		- Changed mysql class, session class
*	12/10/2014
*		- Created new file
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
	if (isset($_SESSION['photo_access']['login']['username'])) {
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
 
		if (isset($_GET['warningMsg'])) {
			$warningMsg = $_GET['warningMsg'];
		} else {
			$warningMsg = "";
		}	
		if (intval($userGroup) > 1) {
			if (!empty($warningMsg)) {
				$errorMsg = $warningMsg;
			} else {
				$errorMsg = "You don't have permission to view this page";
			}
		}

		if (isset($_POST['delete_patron_user'])) {
			$delete_patron_user = $_POST['delete_patron_user'];
		} else {
			$delete_patron_user = "N";
		}
		if (isset($_POST['delete_import_file_log'])) {
			$delete_import_file_log = $_POST['delete_import_file_log'];
		} else {
			$delete_import_file_log = "N";
		}
		if (isset($_POST['delete_searching_log'])) {
			$delete_searching_log = $_POST['delete_searching_log'];
		} else {
			$delete_searching_log = "N";
		}
		if (isset($_POST['delete_login_log'])) {
			$delete_login_log = $_POST['delete_login_log'];
		} else {
			$delete_login_log = "N";
		}
	
		$deleteArray = array();

		if (!empty($_POST["delete"])) {
			function deleteRecord ($db, $tablename) {	
				global $DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_DB;
				$deleteStatus = array();
				$queryDeleteString = "DELETE FROM `".$tablename."`";
				//echo "<br>queryDeleteString: ".$queryDeleteString."<br>";
				$db->transaction_start();
				
				$db->query('SELECT * FROM '.$tablename);
				$deleteRow = $db->returned_rows;
				$db->delete($tablename);
				$queryResetString = "ALTER TABLE `".$tablename."` AUTO_INCREMENT = 1";
				$db->query($queryResetString);
				if ($tablename == "patron_user") {
					$queryResetString = "ALTER TABLE `patron_pic` AUTO_INCREMENT = 1";
					$db->query($queryResetString);
				}
				if ($tablename == "photo_access_user_login_log") {
					$queryResetString = "ALTER TABLE `photo_access_user_login_log` AUTO_INCREMENT = 1";
					$db->query($queryResetString);
				}
				$result = $db->transaction_complete();
				
				if ($result === true) {
					$deleteStatus['status'] = "True";
					$deleteStatus['no_delete'] = $deleteRow;
					$deleteStatus['error_msg'] = "Success";
				} else {
					$deleteStatus['status'] = "False";
					$deleteStatus['no_delete'] = 0;
					$deleteStatus['error_msg'] = "Couldn't delete this table '".$tablename."'";
				}
				return $deleteStatus;
			}
			
			if ($delete_patron_user == "Y") {
				$tablename = "patron_user";
				$deleteTable = array();
				$deleteTable['tablename'] = $tablename;
				$deleteTable['delete_info'] = deleteRecord($db,$tablename);
				array_map('unlink', glob("download/photo_pic/*.png"));
				$deleteArray[] = $deleteTable;
			} 
			if ($delete_import_file_log == "Y") {
				$deleteTable = array();
				$tablename = "patron_import_file_log";
				$deleteTable['tablename'] = $tablename;
				$deleteTable['delete_info'] = deleteRecord($db,$tablename);
				$deleteArray[] = $deleteTable;
			} 
			if ($delete_searching_log == "Y") {
				$deleteTable = array();
				$tablename = "photo_access_searching_log";
				$deleteTable['tablename'] = $tablename;
				$deleteTable['delete_info'] = deleteRecord($db,$tablename);
				$deleteArray[] = $deleteTable;
			} 
			if ($delete_login_log == "Y") {
				$deleteTable = array();
				$tablename = "photo_access_user_login_log";
				$deleteTable['tablename'] = $tablename;
				$deleteTable['delete_info'] = deleteRecord($db,$tablename);
				$deleteArray[] = $deleteTable;
			} 
			//echo "<pre>";
			//	print_r($deleteArray);
			//echo "</pre>";
		}

		include ("./../web/header.html");
		include ("./../web/menu.html");	
		if (!isset($errorMsg)) {
		?>
			<link href="./../css/mainstyle.css" rel="stylesheet" type="text/css">
			<!--
			<div style="margin-left:58px; margin-right:58px; padding:6px; background-color:#FFF; border:#999 1px solid;"><?php echo $paginationDisplay; ?></div>
			-->
			<div style="width:80%; font-family:Arial, Helvetica, sans-serif;font-size:12px;line-height:18px; border: 0px solid black; margin: 0px auto;background-color:white">
			<br><br>
			<form action="delete_all_records.php" method="post">
				<table width=100% style="background-color:white">
					<tr>
						<td colspan=2><br>
							<div class="mainTitle"><strong><center>Photo Access ID - Delete Table Records</center></strong></div>
						</td>
					</tr>
					<tr>
						<td colspan=2>
							<center><?php if (isset($warningMsg)) echo "<font color=red id=\"warningMsg\">".$warningMsg."</font>"; ?></center>
						</td>
					</tr>	
					<tr>
						<td colspan=2 style="text-align:center"><br><center>
							<table width=60% style="background-color:white">
								<tr>
									<td width=70% style="padding-left:20%; padding-bottom:10px">Do you want delete patron user record?</td>
									<td width=30% style="padding-bottom:10px">
										<select name="delete_patron_user" id="delete_patron_user">
											<option value="Y" <?php if ($delete_patron_user == "Y") echo " selected";?>>Yes</option>
											<option value="N" <?php if ($delete_patron_user == "N") echo " selected";?>>No</option>
										</select>
									</td>
								</tr>
								<tr>
									<td width=70% style="padding-left:20%; padding-bottom:10px">Do you want delete import file log record?</td>
									<td width=30% style="padding-bottom:10px">
										<select name="delete_import_file_log" id="delete_import_file_log">
											<option value="Y" <?php if ($delete_import_file_log == "Y") echo " selected";?>>Yes</option>
											<option value="N" <?php if ($delete_import_file_log == "N") echo " selected";?>>No</option>
										</select>
									</td>
								</tr>
								<tr>
									<td width=70% style="padding-left:20%; padding-bottom:10px">Do you want delete searching log record?</td>
									<td width=30% style="padding-bottom:10px">
										<select name="delete_searching_log" id="delete_searching_log">
											<option value="Y" <?php if ($delete_searching_log == "Y") echo " selected";?>>Yes</option>
											<option value="N" <?php if ($delete_searching_log == "N") echo " selected";?>>No</option>
										</select>
									</td>
								</tr>
								<tr>
									<td width=70% style="padding-left:20%; padding-bottom:10px">Do you want delete login log record?</td>
									<td width=30% style="padding-bottom:10px">
										<select name="delete_login_log" id="delete_login_log">
											<option value="Y" <?php if ($delete_login_log == "Y") echo " selected";?>>Yes</option>
											<option value="N" <?php if ($delete_login_log == "N") echo " selected";?>>No</option>
										</select>
									</td>
								</tr>
								<tr>
									<td colspan=2 style="padding-bottom:20px; text-align:center"><br>
										<input type="submit" name="delete" value="Delete" class="deleteButton">
									</td>
								</tr>
							</table>
							</center>
						</td>
					</tr>
					
					<?php
					if (sizeof($deleteArray) > 0) {
						for ($row=0; $row < sizeof($deleteArray); $row++) {
							echo "\n<tr>
								<td colspan=2><center>
									<table width=60% style=\"background-color:white\">
										<tr>
											<td width=30%><b>Table Name:</b></td>
											<td width=70%>".$deleteArray[$row]['tablename']."</td>
										</tr>
										<tr>
											<td width=30% style=\"padding-left:20px\">Delete Status:</td>
											<td width=70%>".$deleteArray[$row]['delete_info']['status']."</td>
										</tr>	
										<tr>
											<td width=30% style=\"padding-left:20px\">Number Delete:</td>
											<td width=70%>".$deleteArray[$row]['delete_info']['no_delete']."</td>
										</tr>	
										<tr>
											<td width=30% style=\"padding-left:20px\">Error Message:</td>
											<td width=70%>".$deleteArray[$row]['delete_info']['error_msg']."</td>	
										</tr>
										<tr>
											<td colspan=2 style=\"padding-top:5px;padding-bottom:10px;\">
												<hr style=\"height:2px;background-color:green;\">
											</td>
										</tr>
									</table></center>
								</td>
							</tr>\n";	
						}	
					}
					?>
				</table>
			</form>
			</div>
			<BR><BR><BR>
			<?php include ("./../web/footer.html"); 
			?>
			<?php
		} else {
			?>
			<link href="./../css/mainstyle.css" rel="stylesheet" type="text/css">
			<div style="width:80%; font-family:Arial, Helvetica, sans-serif;font-size:12px;line-height:18px; border: 0px solid black; margin: 0px auto;background-color:white">
			<br><br>
			<table width=100%>
				<tr>
					<td><br>
						<div class="mainTitle"><strong><center>Photo Access ID - Delete Table Records</center></strong></div>
					</td>
				</tr>
				<tr>
					<td>
						<center><br><br>
						<?php if (isset($errorMsg)) echo "<font color=red>".$errorMsg."</font>"; else echo "<font color=blue>There is no record!</font>"; ?>
						</center>
					</td>
				</tr>
			</table>
			</div>
			<BR><BR><BR>
			<?php include ("./../web/footer.html"); ?>
			<?php	
		}
	} else {
		$page_link = "./../index.php";
		$reDirectLocation = "Location: ./../web/login.php?page_link=".$page_link;
		//echo "reDirection Location: '".$reDirectLocation."'<br>";
		header($reDirectLocation);
		exit();
	}
} else {
	include ("./web/header.html");
?>	
	<link href="./css/mainstyle.css" rel="stylesheet" type="text/css">
	<div style="width:80%; font-family:Arial, Helvetica, sans-serif;font-size:12px;line-height:18px; border: 0px solid black; margin: 0px auto;background-color:white">
	<table  width=100% style="padding:25px; text-align:center; background-color:white;">
		<tr>
			<td><br>
				<div class="mainTitle"><strong><center>Photo Access ID - Delete Table Records</center></strong></div><br>
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