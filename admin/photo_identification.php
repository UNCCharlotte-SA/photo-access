<?php
/*
 *******************************************************************************************************
*
* Name: photo_identification.php
* Display only user which has database authorization and ability to verify their identify
* Writer: Bach Nguyen (bnguye21@uncc.edu)
* Last Updated: 03/06/2015
*	03/06/2015
*		- Changed database, session, paging class
*		- Updated codes
*	10/05/2014
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
	require './../lib/Zebra_Pagination-master/Zebra_Pagination.php';
	$link = $db->get_link();
	$security_code = $SESSION_SECURITY;
	$session_lifetime = $SESSION_TIMEOUT;
	$session = new Zebra_Session($link, $security_code, $session_lifetime);
	$pagination = new Zebra_Pagination();
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
		$display_warning_msg = $_SESSION['photo_access']['login']['display_warning_msg'];
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
		if (intval($userGroup) > 4) {
			if (!empty($warningMsg)) {
				$errorMsg = $warningMsg;
			} else {
				$errorMsg = "You don't have permission to view this page";
			}
		}
	
		if (!empty($_POST["lookup_field"])) {
			$lookup_field = $_POST["lookup_field"];
		} else {
			if (isset($_SESSION['photo_access']['admin']['photo_identification']['lookup_field'])) {
				$lookup_field =	$_SESSION['photo_access']['admin']['photo_identification']['lookup_field'];
			} else {
				$lookup_field = "";
			}
		}
		//$user_last_name = "";
		//$user_first_name = "";
		if (!empty($_POST["lookup_value"])) {
			$lookup_value = strtolower(trim(preg_replace('/\s+/', ' ',$_POST["lookup_value"])));
		} else {
			if (isset($_SESSION['photo_access']['admin']['photo_identification']['lookup_value'])) {
				$lookup_value =	$_SESSION['photo_access']['admin']['photo_identification']['lookup_value'];
			} else {
				$lookup_value = "";
			}
		}
		//echo "<br><br>lookup_field : ".$lookup_field."<br>";
		//echo "lookup_value : ".$lookup_value."<br>";
		//echo "user lastname : ".$user_last_name."<br>";
		//echo "user firstname : ".$user_first_name."<br>";
		//echo "user dept : ".$department."<br>";
	
		if (empty($_POST["lookup_field"]) and (!empty($_POST["lookup_value"]))) {
			$warningMsg = "Please select lookup field";
		}
		if (!empty($_POST["lookup_field"]) and (empty($_POST["lookup_value"]))) {
			$warningMsg = "Please enter lookup value";
		}
	
		if (!empty($_POST["reset"])) {
			$lookup_field = "";
			$lookup_value = "";
			unset($_SESSION['photo_access']['admin']['photo_identification']['text_query']);
			unset($_SESSION['photo_access']['admin']['photo_identification']['lookup_field']);
			unset($_SESSION['photo_access']['admin']['photo_identification']['lookup_value']);
		}
	
		$text_query = "";
		if ($warningMsg == "" or strpos($warningMsg,"Success") !== false) {
			//echo "1<br>";
			if ($lookup_field != "" and $lookup_value != "") {
				//echo "2<br>";
				if ($lookup_field !== "group_name") {
					//echo "3<br>";
					if ($lookup_value != "null") {
						//echo "4<br>";
						if ($lookup_value != "*") {
							//echo "5<br>";
							$text_query = "SELECT a.*, b.group_name FROM photo_access_users a, photo_access_user_group b where a.user_group_id = b.id and a.authentication_method='Database' and ".$lookup_field." like '%".$lookup_value. "%'";
						} else {
							//echo "6<br>";
							$text_query = "SELECT a.*, b.group_name FROM photo_access_users a, photo_access_user_group b where a.user_group_id = b.id and a.authentication_method='Database' and ".$lookup_field." is not NULL";
						}
					} else {
						//echo "7<br>";
						$text_query = "SELECT a.*, b.group_name FROM photo_access_users a, photo_access_user_group b where a.user_group_id = b.id and a.authentication_method='Database' and ".$lookup_field." is NULL";
					}
				} else {
					//echo "8<br>";
					$text_query = "SELECT a.*, b.group_name FROM photo_access_users a, photo_access_user_group b where  a.user_group_id = b.id and a.authentication_method='Database' and b.".$lookup_field." like '%".$lookup_value. "%'";
				}
			} else {
				//echo "9<br>";
				$text_query = "SELECT a.*, b.group_name FROM photo_access_users a, photo_access_user_group b where a.user_group_id = b.id and a.authentication_method='Database'";
			}		
		} else {
			//echo "10<br>";
			$text_query = "SELECT a.*, b.group_name FROM photo_access_users a, photo_access_user_group b where a.user_group_id = b.id and a.authentication_method='Database'";
		}
		//echo "<br>Text_query : ".$text_query."<br>";
		//echo "lookup_field : ".$lookup_field."<br>";
		//echo "lookup_value : ".$lookup_value."<br>";
		
		$_SESSION['photo_access']['admin']['photo_identification']['text_query'] = $text_query;
		$_SESSION['photo_access']['admin']['photo_identification']['lookup_field'] = $lookup_field;
		$_SESSION['photo_access']['admin']['photo_identification']['lookup_value'] = $lookup_value;
	
		if (!empty($text_query)) {
			$querySearchString = $text_query.' LIMIT ' . (($pagination->get_page() - 1) * $number_per_page) . ', ' . $number_per_page . '';
			//echo $querySearchString; 
			$checkQuery = $db->query($querySearchString,'',false,true);
			//echo $db->returned_rows;
			if ($checkQuery) {
				$row_count = $db->found_rows;
				// pass the total number of records to the pagination class
				$pagination->records($row_count);

				// records per page
				$pagination->records_per_page($number_per_page);
		
				// Build the Output Section Here
				$recordArray = array();
				$outputArray = array();
				if ($row_count > 0) {
					while($obj = $db->fetch_obj()) {
						$recordArray['id'] = $obj->id;
						$recordArray['authentication_method'] = $obj->authentication_method;
						$recordArray['user_group_id'] = $obj->user_group_id;
						$recordArray['group_name'] = $obj->group_name;
						$recordArray['user_id'] = $obj->user_id;
						$recordArray['password'] = $obj->password;
						$recordArray['first_name'] = $obj->first_name;
						$recordArray['last_name'] = $obj->last_name;
						$recordArray['email'] = $obj->email;
						$recordArray['phone'] = $obj->phone;
						$recordArray['department'] = $obj->department;
						$recordArray['policy_acceptance_date'] = $obj->policy_acceptance_date;
						$recordArray['active'] = $obj->active;

						$outputArray[] = $recordArray;		
					} // close while loop
					//echo "<pre>";
					//	print_r($outputArray);
					//echo "</pre>";	
				} else {
					if ($warningMsg == "") {
						$warningMsg = "No record found!";
					}
				}
			} else {
				$row_count = 0;
				$errorMsg = "Error: Query has error!";
			}
		}
		
		if (!isset($errorMsg) and $row_count >= 0) {

?>
			<?php include ("./../web/header.html");?>
			<?php include ("./../web/menu.html"); ?>
			<link href="./../css/mainstyle.css" rel="stylesheet" type="text/css">		
			<script Language="JavaScript">	
				function checkValidator(theForm) {
					var txtFieldLookup = trim(document.getElementById('lookup_field').value);
					var txtValueLookup = trim(document.getElementById('lookup_value').value);
					//alert ("txtFieldLookup: " + txtFieldLookup);
					//alert ("txtValueLookup: " + txtValueLookup);
			
					if (txtFieldLookup == "" && txtValueLookup != "") {
						document.getElementById("warningMsg").innerHTML = "Please select lookup field!";
						theForm.lookup_field.style.backgroundColor="#D0E2ED";
						theForm.lookup_field.focus();
						return (false);				
					}
				
					if (txtFieldLookup != "" && txtValueLookup == "") {
						document.getElementById("warningMsg").innerHTML = "Please enter lookup value!";
						theForm.lookup_value.style.backgroundColor="#D0E2ED";
						theForm.lookup_value.focus();
						return (false);				
					}
				}
			</script>

			<!--
			<div style="margin-left:58px; margin-right:58px; padding:6px; background-color:#FFF; border:#999 1px solid;"><?php echo $paginationDisplay; ?></div>
			-->
			<div style="width:80%; font-family:Arial, Helvetica, sans-serif;font-size:12px;line-height:18px; border: 0px solid black; margin: 0px auto;background-color:white">
				<br><br>
				<form action="photo_identification.php" method="post" onsubmit="return checkValidator(this)" name="mainForm">
				<table width=100% style="background-color:white">
					<tr>
						<td colspan=2><br>
							<div class="mainTitle"><strong><center>Photo Access ID - User Management</center></strong></div>
						</td>
					</tr>
					<tr>
						<td colspan=2>
							<center><?php
							if (strpos($warningMsg, "Error") === false) {
								echo "<div style=\"color:#0011FF; text-align:center; background-color:#FFFFFF\" id=\"warningMsg\">";
								if (!empty($warningMsg)) echo $warningMsg; 
									echo "</div>";
							} else {
								echo "<div style=\"color:#FF0000; text-align:center; background-color:#FFFFFF\" id=\"warningMsg\">";
								if (!empty($warningMsg)) echo $warningMsg; 
									echo "</div>";
							}
							?></center>
						</td>
					</tr>	
					<tr>
						<td colspan=3 style="text-align:center"><br>
							<select name="lookup_field" id="lookup_field">
								<option value="user_id" <?php if ($lookup_field == "user_id") echo " selected";?>>User ID</option>
								<option value="first_name" <?php if ($lookup_field == "first_name") echo " selected";?>>First Name</option>
								<option value="last_name" <?php if ($lookup_field == "last_name") echo " selected";?>>Last Name</option>
								<option value="email" <?php if ($lookup_field == "email") echo " selected";?>>Email</option>
								<option value="phone" <?php if ($lookup_field == "phone") echo " selected";?>>Phone</option>
								<option value="department" <?php if ($lookup_field == "department") echo " selected";?>>Department</option>
								<option value="group_name" <?php if ($lookup_field == "group_name") echo " selected";?>>Group Name</option>
								<option value="active" <?php if ($lookup_field == "active") echo " selected";?>>Active</option>
							</select>
							<input type="text" name="lookup_value" id="lookup_value" size=60 value="<?php if (!empty($lookup_value)) echo $lookup_value; ?>">
							<input type="submit" name="search" value="Search" class=dark_green_button>
							<input type="submit" name="reset" value="Reset" class=deleteButton>
						</td>
					</tr>
					<tr>
						<td colspan=2><br>
							<center><div class="displayNumberTitles">Total Items: <?php echo $row_count; ?></div></center>
						</td>	
					</tr>
					<tr>
						<td>
							<table width=100% style="background-color:white" class="TFtable">
								<tr>
									<td width=5% style="padding-left:0.5em"><b>ID</b></td>
									<td width=12%><b>First Name</b></td>
									<td width=12%><b>Last Name</b></td>
									<td width=12%><b>Phone</b></td>
									<td width=16%><b>Email</b></td>
									<td width=16%><b>Group</b></td>
									<td width=15%><b>Photo Checked Date</b></td>
									<td width=3%></td>
									<td width=3%></td>
								</tr>
								<?php
								for ($row=0; $row < count($outputArray); $row++) {
									if ($outputArray[$row]['active'] == "Y") {
										$colorStatus = "green";
									} else {	
										$colorStatus = "red";
									}
								$photoQuery = "SELECT checked_date FROM photo_access_identification WHERE photo_access_user_id='".$outputArray[$row]['id']."' ORDER BY checked_date DESC";
								$photoDateStatus = $db->query($photoQuery);
								if ($photoDateStatus) {
									$photoDateArray = $db->fetch_assoc();
									if (isset($photoDateArray)) 
										$photoCheckedDate = date($datetime_format, strtotime($photoDateArray['checked_date']));
									else
										$photoCheckedDate = "";
								} else {
									$photoCheckedDate = "";
								}
							
								echo "<tr>\n
									<td style=\"padding-left:0.5em\">".$outputArray[$row]['id']."</td>\n
									<td>".$outputArray[$row]['first_name']."</td>\n
									<td>".$outputArray[$row]['last_name']."</id>\n
									<td>".$outputArray[$row]['phone']."</td>\n
									<td>".$outputArray[$row]['email']."</td>\n
									<td>".$outputArray[$row]['group_name']."</td>\n
									<td>".$photoCheckedDate."</td>\n
									<td style=\"vertical-align:middle !important;\"><div style=\"width:10px; height:10px; border-radius:8px; background-color:".$colorStatus."\"></div></td>\n					
									<td style=\"vertical-align:middle !important;\"><a href=\"./../edit_user.php?action=Update&id=".$outputArray[$row]['id']."\"><img src=\"./../images/pencil.gif\" alt=\"Edit\" width=\"10\" height=\"10\"></a></td>\n
								</tr>";
								}
								?>
							</table>
						</td>
					</tr>
				</table>
		
				<!-- Bach Nguyen - 09/09/2013 - Fix display -->
				<?php
				if ($row_count > $number_per_page) {
					echo "<br><br>\n";
					$pagination->render();
				}
				?>
				</form>
			</div>
			<BR><BR><BR>
			<?php include ("./../web/footer.html"); ?>
		<?php	
		} else {
			include ("./../web/header.html");
			include ("./../web/menu.html");
			?>
			<link href="./../css/mainstyle.css" rel="stylesheet" type="text/css">
			<div style="width:80%; font-family:Arial, Helvetica, sans-serif;font-size:12px;line-height:18px; border: 0px solid black; margin: 0px auto;background-color:white">
			<br><br>
			<table width=100%>
				<tr>
					<td><br>
						<div class="mainTitle"><strong><center>Photo Access ID - Photo Identification</center></strong></div>
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
		?>
<?php
	} else {
		$page_link = "./admin/photo_identification.php";
		$reDirectLocation = "Location: ./../web/login.php?page_link=".$page_link;
		//echo "reDirection Location: '".$reDirectLocation."'<br>";
		header($reDirectLocation);
		exit();
	}
} else {
?>
	<?php include ("./web/header.html");?>
	<link href="./css/mainstyle.css" rel="stylesheet" type="text/css">
	<link rel="stylesheet" href="./lib/Zebra_Pagination-master/public/css/zebra_pagination.css" type="text/css">
	<div style="width:80%; font-family:Arial, Helvetica, sans-serif;font-size:12px;line-height:18px; border: 0px solid black; margin: 0px auto;background-color:white">
	<br>
	<table width=100%>
		<tr>
			<td><br>
				<div class="mainTitle"><strong><center>Photo Access ID - Photo Identification</center></strong></div>
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
	<?php include ("./web/footer.html"); ?>
<?php	
}
?>	
?>