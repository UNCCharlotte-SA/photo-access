<?php
/*
 *******************************************************************************************************
*
* Name: index.php
* Display searching patron user
* Writer: Bach Nguyen (bnguye21@uncc.edu)
* Last Updated: 02/09/2015
*	02/09/2015
*		- Added database class, session class and page class
*		- Change searching log: add login ID
*	01/09/2015
*		- Fixed unicode
*
 ********************************************************************************************************
 */
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
	require './lib/Zebra_Pagination-master/Zebra_Pagination.php';
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
		if (intval($_SESSION['photo_access']['login']['user_group']) > 5) {
			$errorMsg = "You don't have permission to view this page";
			$session->stop();
		}

		if (!empty($_POST["lookup_field"])) {
			$lookup_field = $_POST["lookup_field"];
		} else {
			if (isset($_SESSION['photo_access']['index']['lookup_field'])) {
				$lookup_field =	$_SESSION['photo_access']['index']['lookup_field'];
			} else {
				$lookup_field = "";
			}
		}
	
		$patron_last_name = "";
		$patron_first_name = "";
		if (!empty($_POST["lookup_value"])) {
			//$lookup_value = strtolower(trim(preg_replace('/\s+/', ' ',$_POST["lookup_value"])));
			//$lookup_value = mb_strtoupper(trim($_POST["lookup_value"]), "UTF-8");
			//$lookup_value = mb_convert_case(trim($_POST["lookup_value"]),MB_CASE_TITLE,"UTF-8");
			$lookup_value = trim($_POST["lookup_value"]);
			if (!empty($lookup_field) and $lookup_field == "patron_last_name") {
				$lookupName = explode(' ', $lookup_value);
				//echo "<pre>";
				//	print_r($lookupName);
				//echo "</pre>";	
				//echo "1.sizeof: ".sizeof($lookupName);
				if (sizeof($lookupName) > 2) {
					$warningMsg = "Please enter either Lastname or Lastname Firstname format";
				} elseif (sizeof($lookupName) == 2) {
					$patron_last_name = $lookupName[0];
					$patron_first_name = $lookupName[1];
				} else {
					$patron_last_name = $lookupName[0];
				}
			}
		} else {
			if (isset($_SESSION['photo_access']['index']['lookup_value'])) {
				$lookup_value =	$_SESSION['photo_access']['index']['lookup_value'];
				if (!empty($lookup_field) and $lookup_field == "patron_last_name") {
					$lookupName = explode(' ', $lookup_value);
					//echo "<pre>";
					//	print_r($lookupName);
					//echo "</pre>";	
					//echo "2. sizeof: ".sizeof($lookupName)."<br>";
					if (sizeof($lookupName) > 2) {
						$warningMsg = "Please enter either Lastname or Lastname Firstname format";
					} elseif (sizeof($lookupName) == 2) {
						$patron_last_name = $lookupName[0];
						$patron_first_name = $lookupName[1];
					} else {
						$patron_last_name = $lookupName[0];
					}
				}
			} else {
				$lookup_value = "";
			}
		}
		//echo "<br><br>lookup_field : ".$lookup_field."<br>";
		//echo "lookup_value : ".$lookup_value."<br>";
		//echo "lastname : ".$patron_last_name."<br>";
		//echo "firstname : ".$patron_first_name."<br>";
		if (empty($_POST["lookup_field"]) and (!empty($_POST["lookup_value"]))) {
			$warningMsg = "Please select lookup field";
		} //else {
		//	$lookup_field = "";
		//}
		if (!empty($_POST["lookup_field"]) and (empty($_POST["lookup_value"]))) {
			$warningMsg = "Please enter lookup value";
		} //else {
		//	$lookup_value = "";
		//}
		if (!empty($_POST["lookup_field"]) and $_POST["lookup_field"] == "patron_number" and !empty($_POST["lookup_value"]) and strlen($_POST["lookup_value"]) != 9) {
			$warningMsg = "Please enter full UNC Charlotte ID";
		}// else {
		//	$lookup_value = "";
		//}
	
		if (!empty($_POST["reset"])) {
			$lookup_field = "";
			$lookup_value = "";
			unset($_SESSION['photo_access']['index']['text_query']);
			unset($_SESSION['photo_access']['index']['lookup_field']);
			unset($_SESSION['photo_access']['index']['lookup_value']);
		}
		
		//$text_query = "";
		if (isset($_SESSION['photo_access']['index']['text_query'])) {
			$text_query = $_SESSION['photo_access']['index']['text_query'];
		} else {
			$text_query = "";
		}
		if ($warningMsg == "" or strpos($warningMsg,"Success") !== false) {
			if ($lookup_field != "" and $lookup_value != "") {
				if ($lookup_value != "null") {
					if ($lookup_value != "*") {
						if (!empty($patron_last_name) and !empty($patron_first_name)) {
							$text_query = "SELECT a.*, b.pic_id, b.pic_location, b.default_pic, b.pic_image FROM patron_user a left join patron_pic b on a.patron_number=b.patron_id WHERE b.default_pic='Y' and a.patron_last_name like '".str_replace("'","\'",$patron_last_name)."%' and a.patron_first_name like '".str_replace("'","\'",$patron_first_name). "%'";
						} elseif (!empty($patron_last_name)) {
							$text_query = "SELECT a.*, b.pic_id, b.pic_location, b.default_pic, b.pic_image FROM patron_user a left join patron_pic b on a.patron_number=b.patron_id WHERE b.default_pic='Y' and a.patron_last_name like '".str_replace("'","\'",$patron_last_name)."%'";
						} else {
							$text_query = "SELECT a.*, b.pic_id, b.pic_location, b.default_pic, b.pic_image FROM patron_user a left join patron_pic b on a.patron_number=b.patron_id WHERE b.default_pic='Y' and a.".$lookup_field." like '".str_replace("'","\'",$lookup_value)."%'";
						}	
					} else {
						$text_query = "SELECT a.*, b.pic_id, b.pic_location, b.default_pic, b.pic_image FROM patron_user a left join patron_pic b on a.patron_number=b.patron_id WHERE b.default_pic='Y' and a.".$lookup_field." is not NULL";
					}
				} else {
					$text_query = "SELECT a.*, b.pic_id, b.pic_location, b.default_pic, b.pic_image FROM patron_user a left join patron_pic b on a.patron_number=b.patron_id WHERE b.default_pic='Y' and a.".$lookup_field." is NULL";
				}
			}
		}// else {
		//	$text_query = "SELECT * FROM patron_user WHERE patron_is_active='W'";
		//}
		//echo "<br>Text_query : ".$text_query."<br>";
		//echo "lookup_field : ".$lookup_field."<br>";
		//echo "lookup_value : ".$lookup_value."<br>";
		//exit();
		$_SESSION['photo_access']['index']['text_query'] = $text_query;
		$_SESSION['photo_access']['index']['lookup_field'] = $lookup_field;
		$_SESSION['photo_access']['index']['lookup_value'] = $lookup_value;
		//$row_count = 0;
		
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
				
				//echo "count: ".$row_count."<br>";
				// Build the Output Section Here
				$recordArray = array();
				$outputArray = array();
				if ($row_count > 0) {
					while($obj = $db->fetch_obj()) {
						$recordArray['id'] = $obj->id;
						$recordArray['patron_number'] = $obj->patron_number;
						$recordArray['first_name'] = $obj->patron_first_name;
						$recordArray['middle_name'] = $obj->patron_middle_name;
						$recordArray['last_name'] = $obj->patron_last_name;
						$recordArray['gender'] = $obj->patron_gender;
						$recordArray['active'] = $obj->patron_is_active;
						$recordArray['ferpa_flag'] = $obj->patron_ferpa_flag;
						$recordArray['hr_classification'] = $obj->patron_hr_classification;
						$recordArray['primary_classification'] = $obj->patron_primary_classification;
						$recordArray['last_mod_datetime'] = $obj->patron_last_mod_datetime;
						$recordArray['updated_user'] = $obj->updated_user;
						$recordArray['updated_date'] = $obj->updated_date;
						$recordArray['pic_id'] = $obj->pic_id;
						$recordArray['pic_location'] = $obj->pic_location;
						$recordArray['pic_image'] = base64_decode($obj->pic_image);
						$outputArray[] = $recordArray;		
					} // close while loop
					//echo "<pre>";
					//	print_r($outputArray);
					//echo "</pre>";	
				} else {
					if ($warningMsg == "") {
						if (!empty($text_query)) {
							$warningMsg = "No record found!";
						}	
					} else {
						$warningMsg .= ", and No record found!";	
					}
				}
			} else {
				$row_count = 0;
				$errorMsg = "Error: Query has error!";
			}
		} else {
			$row_count = 0;
		}
		
		if (!empty($_POST["search"]) and empty($errorMsg)) {
			//echo "<br>Searching...<br>";
			if (!empty($_POST["lookup_field"]) and (!empty($_POST["lookup_value"]))) {
				$createdDate = date("Y-m-d H:i:s", strtotime("now"));
				$insertArray = array();
				$insertArray['id'] = '';
				//$insertArray['user_first_name'] = $_SESSION['photo_access']['login']['first_name'];
				//$insertArray['user_last_name'] = $_SESSION['photo_access']['login']['last_name'];
				//$insertArray['user_email'] = $_SESSION['photo_access']['login']['user_email'];
				//$insertArray['user_dept'] = $_SESSION['photo_access']['login']['department'];
				$insertArray['date_searching'] = $createdDate;
				$insertArray['searching_string'] = str_replace("'","\'",$lookup_value);
				$insertArray['patron_id'] = NULL;
				$insertArray['patron_first_name'] = NULL;
				$insertArray['patron_last_name'] = NULL;
				$insertArray['login_id'] = $_SESSION['photo_access']['login']['login_id'];;

				$insertQueryLogStatus = $db->insert('photo_access_searching_log',$insertArray);
				//echo $addQueryLogString."<br>";

				if ($insertQueryLogStatus) {
					$warningMsg = "Success added this record to search log. Search Log ID#: ".$db->insert_id();
				} else {
					$warningMsg = "Error: Couldn't insert search log into table!";
				}
			} else {
				$warningMsg = "You have to select field and enter value before click search button!";
			}
		}	
	}  else {
		$page_link = "./index.php";
		$reDirectLocation = "Location: ./web/login.php?page_link=".$page_link;
		//echo "reDirection Location: '".$reDirectLocation."'<br>";
		header($reDirectLocation);
		exit();
	}
} else {
	$errorMsg = $checkConnect['error'];
}

if (!isset($errorMsg) and $row_count >= 0) {

?>
	<?php include ("./web/header.html");?>
	<?php include ("./web/menu.html"); ?>
	<script Language="JavaScript">	
		function trim(stringToTrim) {
			return stringToTrim.replace(/^\s+|\s+$/g,"");
		}
		function checkValidator(theForm) {
			var txtFieldLookup = trim(document.getElementById('lookup_field').value);
			var txtValueLookup = trim(document.getElementById('lookup_value').value);
			//alert ("txtFieldLookup: " + txtFieldLookup);
			//alert ("txtValueLookup: " + txtValueLookup + ", length: " + txtValueLookup.length);
			
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
			
			if (txtFieldLookup != "" && txtFieldLookup == "patron_number" && txtValueLookup.length != 9) {
				document.getElementById("warningMsg").innerHTML = "Please enter full UNC Charlotte ID";
				theForm.lookup_value.style.backgroundColor="#D0E2ED";
				theForm.lookup_value.focus();
				return (false);				
			}	
		}
	</script>
	<link href="./css/mainstyle.css" rel="stylesheet" type="text/css">
	<link rel="stylesheet" href="./lib/Zebra_Pagination-master/public/css/zebra_pagination.css" type="text/css">
	<!--
	<div style="margin-left:58px; margin-right:58px; padding:6px; background-color:#FFF; border:#999 1px solid;"><?php echo $paginationDisplay; ?></div>
	-->
	<div style="width:80%; font-family:Arial, Helvetica, sans-serif;font-size:12px;line-height:18px; border: 0px solid black; margin: 0px auto;background-color:white">
		<br><br>
		<form action="index.php" method="post" onsubmit="return checkValidator(this)" name="mainForm">
			<table width=100% style="background-color:white">
				<tr>
					<td colspan=2><br>
						<div class="mainTitle"><strong><center>Photo Access ID - Lookup</center></strong></div>
					</td>
				</tr>
				<tr>
					<td colspan=2><br>
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
						//if (isset($warningMsg)) echo "<font color=red id=\"warningMsg\">".$warningMsg."</font>";
						?></center>
					</td>
				</tr>	
				<tr>
					<td colspan=3 style="text-align:center">
						<select name="lookup_field" id="lookup_field">
							<option value=""></option>
							<option value="patron_number" <?php if ($lookup_field == "patron_number") echo " selected";?>>UNC Charlotte ID</option>
							<option value="patron_last_name" <?php if ($lookup_field == "patron_last_name") echo " selected";?>>LastName FirstName</option>
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
						<br>
					</td>
				</tr>
				<?php
				if (!isset($errorMsg) and $row_count > 0) {
				?>
				<tr>
					<td>
						<table width=100% style="background-color:white" >
							<?php
							
							for ($row=0; $row<count($outputArray); $row++) {
								if ($outputArray[$row]['active'] == "Y") {
									$colorStatus = "green";
								} else {	
									$colorStatus = "red";
								}
							echo "<tr>\n
								<td width=70% style=\"vertical-align:middle !important;\">
								<table width=100%>\n
									<tr>
										<td colspan=2 class=\"mainSmallTitle\">";
											if (!empty($outputArray[$row]['middle_name'])) {
												echo "<b>".$outputArray[$row]['first_name']."</b> ".$outputArray[$row]['middle_name']." <b>".$outputArray[$row]['last_name']."</b>";
											} else {
												echo "<b>".$outputArray[$row]['first_name']." ".$outputArray[$row]['last_name']."</b>";
											}
										echo "</td>
									</tr>
									<tr>
										<td>
											<b>UNC Charlotte ID</b>: <font color=blue>".$outputArray[$row]['patron_number']."</font>".
										"</td>
										<td>
											<b>HR Classification</b>: ".$outputArray[$row]['hr_classification'].
										"</td>	
									</tr>
									<tr>
										<td>
											<b>Gender</b>: ".$outputArray[$row]['gender'].
										"</td>
										<td>
											<b>Primary Classification</b>: ".$outputArray[$row]['primary_classification'].
										"</td>
									</tr>	
									<tr>
										<td>
											<b>Ferpa Flag</b>: ".$outputArray[$row]['ferpa_flag'].
										"</td>
										<td>
											<b>Last Modified Date</b>: ".date($datetime_format, strtotime($outputArray[$row]['last_mod_datetime'])).
										"</td>
									</tr>									
								</table>
								</td>";
								if (empty($outputArray[$row]['pic_image'])) {
								echo "<td width=10% style=\"vertical-align:middle !important;\"><img src=\"./admin/".$outputArray[$row]['pic_location']."\" alt=\"Edit\" width=\"100\" height=\"120\"></td>\n";
								} else {
								echo "<td width=10% style=\"vertical-align:middle !important;\"><img src=\"data:image/png;base64,".base64_encode($outputArray[$row]['pic_image'])."\" alt=\"Edit\" width=\"100\" height=\"120\"></td>\n";									
								}
								echo "<td width=10% style=\"vertical-align:middle !important;\"><center><div style=\"width:20px; height:20px; border-radius:10px; background-color:".$colorStatus."\"></div></center></td>\n					
								<td width=5% style=\"vertical-align:middle !important;\"><a href=\"./view_patron.php?id=".$outputArray[$row]['id']."\" class=\"blue_button\"><font color=white>View</font></a></td>\n
							</tr>";
							echo "<tr>
								<td colspan=\"5\" style=\"padding-top:5px;padding-bottom:10px;\">
									<hr style=\"height:2px;background-color:green;\">
								</td>
								</tr>";
							}
							?>
						</table>
					</td>
				</tr>
				<?php
				}
				?>
			</table>
			<?php
			if ($row_count > $number_per_page) {
			echo "<br><br>\n";
			$pagination->render();
			}
			?>
		</form>
	</div>
		<BR><BR><BR>
		<?php include ("./web/footer.html"); 
		if ($display_warning_msg == "Y") {
			echo "<script type=\"text/javascript\" src=\"./js/custom_warning_alert.js\"></script>";
			echo "<script Language=\"JavaScript\">";
				echo "window.alert(\"<center>Please use this application carefully!<br>We are monitor and record everything which you are searching!<br>Please log out when you done!</center>\",\"Warning\",\"OK\")";
			echo "</script>";
			$_SESSION['photo_access']['login']['display_warning_msg'] = "N";
		}
		?>
<?php
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
				<div class="mainTitle"><strong><center>Photo Access ID - Lookup</center></strong></div>
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