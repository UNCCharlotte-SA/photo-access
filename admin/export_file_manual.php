<?php
/*
 *******************************************************************************************************
*
* Name: export_file_manual.php
* Export patron user
* Writer: Bach Nguyen (bnguye21@uncc.edu)
* Last Updated: 03/26/2015
* 	03/26/2015
*		Created file
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
//$EXPORT_DATABASE_DB = "hell";
$EXPORT_DATABASE_HOST = "sa_development.uncc.edu";
$EXPORT_DATABASE_USER = "photo_user";
$EXPORT_DATABASE_PASS = "photoAccess4SA";
$EXPORT_DATABASE_DB = "photo_access";
$db->connect(
            $EXPORT_DATABASE_HOST,		// host
            $EXPORT_DATABASE_USER,		// user name
            $EXPORT_DATABASE_PASS,		// password
            $EXPORT_DATABASE_DB,		// database
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
		if (intval($userGroup) > 2) {
			if (!empty($warningMsg)) {
				$errorMsg = $warningMsg;
			} else {
				$errorMsg = "You don't have permission to view this page";
			}
		}

		if (!empty($_POST["lookup_field"])) {
			$lookup_field = $_POST["lookup_field"];
		} else {
			if (!empty($_POST["search"])) {
				$lookup_field = "";
			} else {
				if (isset($_SESSION['photo_access']['admin']['patron_management']['lookup_field'])) {
					$lookup_field =	$_SESSION['photo_access']['admin']['patron_management']['lookup_field'];
				} else {
					$lookup_field = "";
				}
			}
		}
		$patron_last_name = "";
		$patron_first_name = "";
		$user_last_name = "";
		$user_first_name = "";
		if (!empty($_POST["lookup_value"])) {
			//$lookup_value = strtolower(trim(preg_replace('/\s+/', ' ',$_POST["lookup_value"])));
			//$lookup_value = mb_convert_case(trim($_POST["lookup_value"]),MB_CASE_TITLE,"UTF-8");
			$lookup_value = trim($_POST["lookup_value"]);
			if (!empty($lookup_field) and $lookup_field == "patron_last_name") {
				$lookupName = explode(' ', $lookup_value);
				//echo "<pre>";
				//	print_r($lookupName);
				//echo "</pre>";	
				//echo sizeof($lookupName);
				if (sizeof($lookupName) > 2) {
					$warningMsg = "Please enter either Lastname or Lastname Firstname format";
				} elseif (sizeof($lookupName) == 2) {
					$patron_last_name = $lookupName[0];
					$patron_first_name = $lookupName[1];
				} else {
					$patron_last_name = $lookupName[0];
				}
			}
			if (!empty($lookup_field) and $lookup_field == "user_last_name") {
				$lookupName = explode(' ', $lookup_value);
				//echo "<pre>";
				//	print_r($lookupName);
				//echo "</pre>";	
				//echo sizeof($lookupName);
				if (sizeof($lookupName) > 2) {
					$warningMsg = "Please enter either Lastname or Lastname Firstname format";
				} elseif (sizeof($lookupName) == 2) {
					$user_last_name = $lookupName[0];
					$user_first_name = $lookupName[1];
				} else {
					$user_last_name = $lookupName[0];
				}
			}
		} else {
			if (!empty($_POST["search"])) {
				$lookup_value = "";
			} else {
				if (isset($_SESSION['photo_access']['admin']['patron_management']['lookup_value'])) {
					$lookup_value =	$_SESSION['photo_access']['admin']['patron_management']['lookup_value'];
					$lookupName = explode(' ', $lookup_value);
					//echo "<pre>";
					//	print_r($lookupName);
					//echo "</pre>";	
					//echo sizeof($lookupName)."<br>";
					if (sizeof($lookupName) > 2) {
						$warningMsg = "Please enter either Lastname or Lastname Firstname format";
					} elseif (!empty($lookup_field) and $lookup_field == "patron_last_name") {
						if (sizeof($lookupName) == 2) {
							$patron_last_name = $lookupName[0];
							$patron_first_name = $lookupName[1];
						} else {
							$patron_last_name = $lookupName[0];
						}
					} elseif (!empty($lookup_field) and $lookup_field == "user_last_name") {
						if (sizeof($lookupName) == 2) {
							$user_last_name = $lookupName[0];
							$user_first_name = $lookupName[1];
						} else {
							$user_last_name = $lookupName[0];
						}
					}
				} else {
					$lookup_value = "";
				}
			}
		}	
		//echo "<br><br>lookup_field : ".$lookup_field."<br>";
		//echo "lookup_value : ".$lookup_value."<br>";
		//echo "patron lastname : ".$patron_last_name."<br>";
		//echo "patron firstname : ".$patron_first_name."<br>";
		//echo "user lastname : ".$user_last_name."<br>";
		//echo "user firstname : ".$user_first_name."<br>";
		//echo "user dept : ".$department."<br>";
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
	
		include ("./../classes/checkdate.class.php");
		$classCheckDate = new Checkdate;
		$todayDate = date("Y-m-d", strtotime("today"));
		$tomorrowDate = mktime(0, 0, 0, date("m")  , date("d")+1, date("Y"));
		$lastTenYear = mktime(0, 0, 0, date("m"), date("d"),   date("Y")-10);

		if (!empty($_POST["from_date"])) {
			$from_date = $_POST["from_date"];
			$fromDateStatus = $classCheckDate->myCheckDate($from_date);
			//echo "<pre>";
			//	print_r($fromDateStatus);
			//echo "</pre>";
			if ($fromDateStatus['status'] == "error") {
				$warningMsg = $fromDateStatus['msg'];
			} else {
				$parts=date_parse($from_date);
				$dayCheck = mktime(0, 0, 0, $parts['month'], $parts['day'], $parts['year']);
				//echo "daycheck: ".$dayCheck."<br>";
				//echo "tomorrow: ".$tomorrow."<br>";
				//echo "lastsixtyday: ".$lastSixtyDay."<br>";
				if ($dayCheck >= $tomorrowDate or $dayCheck <= $lastTenYear) {
					$warningMsg = "Please select 'From Date' in range (Last 10 years) before clicking Submit";
				}
			}	
		} else $from_date = "";	
		if (!empty($_POST["to_date"])) {
			$to_date = $_POST["to_date"];
			$toDateStatus = $classCheckDate->myCheckDate($to_date);
			//echo "<pre>";
			//	print_r($toDateStatus);
			//echo "</pre>";
			if ($toDateStatus['status'] == "error") {
				$warningMsg = $toDateStatus['msg'];
			} else {
				$parts=date_parse($to_date);
				$dayCheck = mktime(0, 0, 0, $parts['month'], $parts['day'], $parts['year']);
				//echo "daycheck: ".$dayCheck."<br>";
				//echo "tomorrow: ".$tomorrow."<br>";
				//echo "lastsixtyday: ".$lastSixtyDay."<br>";
				if ($dayCheck >= $tomorrowDate or $dayCheck <= $lastTenYear) {
					$warningMsg = "Please select 'To Date' in range (Last 10 years) before clicking Submit";
				}
			}
		} else $to_date = "";	

		//$text_query = "";
		if (isset($_SESSION['photo_access']['admin']['patron_management']['text_query'])) {
			$text_query = $_SESSION['photo_access']['admin']['patron_management']['text_query'];
		} else {
			$text_query = "";
		}
		//echo "text_query: ".$text_query."<br>";
		
		if (!empty($_POST["reset"])) {
			$text_query = "";
			$lookup_field = "";
			$lookup_value = "";
			$from_date = "";
			$to_date = "";
			unset($_SESSION['photo_access']['admin']['patron_management']['text_query']);
			unset($_SESSION['photo_access']['admin']['patron_management']['lookup_field']);
			unset($_SESSION['photo_access']['admin']['patron_management']['lookup_value']);
			unset($_SESSION['photo_access']['admin']['patron_management']['from_date']);
			unset($_SESSION['photo_access']['admin']['patron_management']['to_date']);
		}

		if ($warningMsg == "" or strpos($warningMsg,"Success") !== false) {
			//echo "1<br>";
			if ($lookup_field != "" and $lookup_value != "" and empty($from_date) and empty($to_date)) {
				//echo "3<br>";
				if ($lookup_value != "null") {
					//echo "4<br>";
					if ($lookup_value != "*") {
						//echo "5<br>";
						if (!empty($patron_last_name) and !empty($patron_first_name)) {
							//echo "6<br>";
							$text_query = "SELECT a.*, b.pic_id, b.pic_location, b.default_pic, b.pic_image FROM patron_user a left join patron_pic b on a.patron_number=b.patron_id where b.default_pic='Y' and a.patron_last_name like '".str_replace("'","\'",$patron_last_name)."%' and a.patron_first_name like '".str_replace("'","\'",$patron_first_name). "%'";
						} elseif (!empty($patron_last_name)) {
							//echo "7<br>";
							$text_query = "SELECT a.*, b.pic_id, b.pic_location, b.default_pic, b.pic_image FROM patron_user a left join patron_pic b on a.patron_number=b.patron_id where b.default_pic='Y' and a.patron_last_name like '".str_replace("'","\'",$patron_last_name)."%'";
						} else {
							//echo "10<br>";
							$text_query = "SELECT a.*, b.pic_id, b.pic_location, b.default_pic, b.pic_image FROM patron_user a left join patron_pic b on a.patron_number=b.patron_id where b.default_pic='Y' and a.".$lookup_field." like '%".str_replace("'","\'",$lookup_value). "%'";
						}	
					} else {
						//echo "11<br>";
						$text_query = "SELECT a.*, b.pic_id, b.pic_location, b.default_pic, b.pic_image FROM patron_user a left join patron_pic b on a.patron_number=b.patron_id where b.default_pic='Y' and a.".$lookup_field." is not NULL";
					}
				} else {
					//echo "12<br>";
					$text_query = "SELECT a.*, b.pic_id, b.pic_location, b.default_pic, b.pic_image FROM patron_user a left join patron_pic b on a.patron_number=b.patron_id where b.default_pic='Y' and a.".$lookup_field." is NULL";
				}
			} elseif ($lookup_field != "" and $lookup_value != "" and !empty($from_date) and empty($to_date)) {
				//echo "13<br>";
				if ($lookup_value != "null") {
					//echo "14<br>";
					if ($lookup_value != "*") {
						//echo "15<br>";
						if (!empty($patron_last_name) and !empty($patron_first_name)) {
							//echo "16<br>";
							$text_query = "SELECT a.*, b.pic_id, b.pic_location, b.default_pic, b.pic_image FROM patron_user a left join patron_pic b on a.patron_number=b.patron_id where b.default_pic='Y' and a.patron_last_name like '".str_replace("'","\'",$patron_last_name)."%' and a.patron_first_name like '".str_replace("'","\'",$patron_first_name). "%' and date(a.patron_last_mod_datetime) between '".date('Y-m-d',strtotime($from_date))."' and '".$todayDate."'";
						} elseif (!empty($patron_last_name)) {
							//echo "17<br>";
							$text_query = "SELECT a.*, b.pic_id, b.pic_location, b.default_pic, b.pic_image FROM patron_user a left join patron_pic b on a.patron_number=b.patron_id where b.default_pic='Y' and a.patron_last_name like '".str_replace("'","\'",$patron_last_name)."%' and date(a.patron_last_mod_datetime) between '".date('Y-m-d',strtotime($from_date))."' and '".$todayDate."'";
						} else {
							//echo "20<br>";
							$text_query = "SELECT a.*, b.pic_id, b.pic_location, b.default_pic, b.pic_image FROM patron_user a left join patron_pic b on a.patron_number=b.patron_id where b.default_pic='Y' and ".$lookup_field." like '%".str_replace("'","\'",$lookup_value). "%' and date(a.patron_last_mod_datetime) between '".date('Y-m-d',strtotime($from_date))."' and '".$todayDate."'";
						}	
					} else {
						//echo "21<br>";
						$text_query = "SELECT a.*, b.pic_id, b.pic_location, b.default_pic, b.pic_image FROM patron_user a left join patron_pic b on a.patron_number=b.patron_id where b.default_pic='Y' and a.".$lookup_field." is not NULL and date(a.patron_last_mod_datetime) between '".date('Y-m-d',strtotime($from_date))."' and '".$todayDate."'";
					}
				} else {
					//echo "22<br>";
					$text_query = "SELECT a.*, b.pic_id, b.pic_location, b.default_pic, b.pic_image FROM patron_user a left join patron_pic b on a.patron_number=b.patron_id where b.default_pic='Y' and a.".$lookup_field." is NULL and date(a.patron_last_mod_datetime) between '".date('Y-m-d',strtotime($from_date))."' and '".$todayDate."'";
				}
			} elseif ($lookup_field != "" and $lookup_value != "" and empty($from_date) and !empty($to_date)) {
				//echo "23<br>";
				if ($lookup_value != "null") {
					//echo "24<br>";
					if ($lookup_value != "*") {
						//echo "25<br>";
						if (!empty($patron_last_name) and !empty($patron_first_name)) {
							//echo "26<br>";
							$text_query = "SELECT a.*, b.pic_id, b.pic_location, b.default_pic, b.pic_image FROM patron_user a left join patron_pic b on a.patron_number=b.patron_id where b.default_pic='Y' and a.patron_last_name like '".str_replace("'","\'",$patron_last_name)."%' and a.patron_first_name like '".str_replace("'","\'",$patron_first_name). "%' and date(a.patron_last_mod_datetime) <='".date('Y-m-d',strtotime($to_date))."'";
						} elseif (!empty($patron_last_name)) {
							//echo "27<br>";
							$text_query = "SELECT a.*, b.pic_id, b.pic_location, b.default_pic, b.pic_image FROM patron_user a left join patron_pic b on a.patron_number=b.patron_id where b.default_pic='Y' and a.patron_last_name like '".str_replace("'","\'",$patron_last_name)."%' and date(a.patron_last_mod_datetime) <='".date('Y-m-d',strtotime($to_date))."'";
						} else {
							//echo "30<br>";
							$text_query = "SELECT a.*, b.pic_id, b.pic_location, b.default_pic, b.pic_image FROM patron_user a left join patron_pic b on a.patron_number=b.patron_id where b.default_pic='Y' and a.".$lookup_field." like '%".str_replace("'","\'",$lookup_value). "%' and date(a.patron_last_mod_datetime) <='".date('Y-m-d',strtotime($to_date))."'";
						}	
					} else {
						//echo "31<br>";
						$text_query = "SELECT a.*, b.pic_id, b.pic_location, b.default_pic, b.pic_image FROM patron_user a left join patron_pic b on a.patron_number=b.patron_id where b.default_pic='Y' and a.".$lookup_field." is not NULL and date(a.patron_last_mod_datetime) <='".date('Y-m-d',strtotime($to_date))."'";
					}
				} else {
					//echo "32<br>";
					$text_query = "SELECT a.*, b.pic_id, b.pic_location, b.default_pic, b.pic_image FROM patron_user a left join patron_pic b on a.patron_number=b.patron_id where b.default_pic='Y' and a.".$lookup_field." is NULL and date(a.patron_last_mod_datetime) <='".date('Y-m-d',strtotime($to_date))."'";
				}	
			} elseif ($lookup_field != "" and $lookup_value != "" and !empty($from_date) and !empty($to_date)) {
				//echo "33<br>";
				if ($lookup_value != "null") {
					//echo "34<br>";
					if ($lookup_value != "*") {
						//echo "35<br>";
						if (!empty($patron_last_name) and !empty($patron_first_name)) {
							//echo "36<br>";
							$text_query = "SELECT a.*, b.pic_id, b.pic_location, b.default_pic, b.pic_image FROM patron_user a left join patron_pic b on a.patron_number=b.patron_id where b.default_pic='Y' and a.patron_last_name like '".str_replace("'","\'",$patron_last_name)."%' and a.patron_first_name like '".str_replace("'","\'",$patron_first_name). "%' and date(a.patron_last_mod_datetime) between '".date('Y-m-d',strtotime($from_date))."' and '".date('Y-m-d',strtotime($to_date))."'";
						} elseif (!empty($patron_last_name)) {
							//echo "37<br>";
							$text_query = "SELECT a.*, b.pic_id, b.pic_location, b.default_pic, b.pic_image FROM patron_user a left join patron_pic b on a.patron_number=b.patron_id where b.default_pic='Y' and a.patron_last_name like '".str_replace("'","\'",$patron_last_name)."%' and date(a.patron_last_mod_datetime) between '".date('Y-m-d',strtotime($from_date))."' and '".date('Y-m-d',strtotime($to_date))."'";
						} else {
							//echo "40<br>";
							$text_query = "SELECT a.*, b.pic_id, b.pic_location, b.default_pic, b.pic_image FROM patron_user a left join patron_pic b on a.patron_number=b.patron_id where b.default_pic='Y' and a.".$lookup_field." like '%".str_replace("'","\'",$lookup_value). "%' and date(a.patron_last_mod_datetime) between '".date('Y-m-d',strtotime($from_date))."' and '".date('Y-m-d',strtotime($to_date))."'";
						}	
					} else {
						//echo "41<br>";
						$text_query = "SELECT a.*, b.pic_id, b.pic_location, b.default_pic, b.pic_image FROM patron_user a left join patron_pic b on a.patron_number=b.patron_id where b.default_pic='Y' and a.".$lookup_field." is not NULL and date(a.patron_last_mod_datetime) between '".date('Y-m-d',strtotime($from_date))."' and '".date('Y-m-d',strtotime($to_date))."'";
					}
				} else {
					//echo "42<br>";
					$text_query = "SELECT a.*, b.pic_id, b.pic_location, b.default_pic, b.pic_image FROM patron_user a left join patron_pic b on a.patron_number=b.patron_id where b.default_pic='Y' and a.".$lookup_field." is NULL and date(a.patron_last_mod_datetime) between '".date('Y-m-d',strtotime($from_date))."' and '".date('Y-m-d',strtotime($to_date))."'";
				}
			} elseif ($lookup_field == "" and $lookup_value == "" and !empty($from_date) and empty($to_date)) {
				//echo "43<br>";
				$text_query = "SELECT a.*, b.pic_id, b.pic_location, b.default_pic, b.pic_image FROM patron_user a left join patron_pic b on a.patron_number=b.patron_id where b.default_pic='Y' and date(a.patron_last_mod_datetime) between '".date('Y-m-d',strtotime($from_date))."' and '".$todayDate."'";
			} elseif ($lookup_field == "" and $lookup_value == "" and empty($from_date) and !empty($to_date)) {
				//echo "44<br>";
				$text_query = "SELECT a.*, b.pic_id, b.pic_location, b.default_pic, b.pic_image FROM patron_user a left join patron_pic b on a.patron_number=b.patron_id where b.default_pic='Y' and date(a.patron_last_mod_datetime) <='".date('Y-m-d',strtotime($to_date))."'";
			} elseif ($lookup_field == "" and $lookup_value == "" and !empty($from_date) and !empty($to_date)) {
				//echo "45<br>";
				$text_query = "SELECT a.*, b.pic_id, b.pic_location, b.default_pic, b.pic_image FROM patron_user a left join patron_pic b on a.patron_number=b.patron_id where b.default_pic='Y' and date(a.patron_last_mod_datetime) between '".date('Y-m-d',strtotime($from_date))."' and '".date('Y-m-d',strtotime($to_date))."'";
			} else {
				//echo "46<br>";
				$text_query = "SELECT a.*, b.pic_id, b.pic_location, b.default_pic, b.pic_image FROM patron_user a left join patron_pic b on a.patron_number=b.patron_id where b.default_pic='Y'";
			}
		//} else {
		//	$text_query = "SELECT a.*, b.pic_id, b.pic_location, b.default_pic, b.pic_image FROM patron_user a left join patron_pic b on a.patron_number=b.patron_id where b.default_pic='Y'";
		}
		//echo "<br>Text_query : ".$text_query."<br>";
		//echo "lookup_field : ".$lookup_field."<br>";
		//echo "lookup_value : ".$lookup_value."<br>";

		$_SESSION['photo_access']['admin']['patron_management']['text_query'] = $text_query;
		$_SESSION['photo_access']['admin']['patron_management']['lookup_field'] = $lookup_field;
		$_SESSION['photo_access']['admin']['patron_management']['lookup_value'] = $lookup_value;
		$_SESSION['photo_access']['admin']['patron_management']['from_date'] = $from_date;
		$_SESSION['photo_access']['admin']['patron_management']['to_date'] = $to_date;
		//$row_count = 0;

		$querySearchString = $text_query.' LIMIT ' . (($pagination->get_page() - 1) * $number_per_page) . ', ' . $number_per_page . '';
		//echo $querySearchString;
		$checkQuery = $db->query($querySearchString,'',false,true);
		
		if ($checkQuery) {
			$row_count = $db->found_rows;	
			// pass the total number of records to the pagination class
			$pagination->records($row_count);

			// records per page
			$pagination->records_per_page($number_per_page);
			
			if ($row_count > 0) {
				$recordArray = array();
				$outputArray = array();
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
				}
			}
		} else {
			$warningMsg = "Error: Query has error!";
			$row_count = 0;
		}
				
		if (!isset($errorMsg) and $row_count >= 0) {		
?>
			<?php include ("./../web/header.html");?>
			<?php include ("./../web/menu.html"); ?>
			<!-- For date -->
			<link rel="stylesheet" href="./../lib/jquery-ui-1.10.3/themes/base/jquery.ui.all.css">
			<script src="./../lib/jquery-ui-1.10.3/jquery-1.9.1.js"></script>
			<script src="./../lib/jquery-ui-1.10.3/ui/jquery.ui.core.js"></script>
			<script src="./../lib/jquery-ui-1.10.3/ui/jquery.ui.widget.js"></script>
			<script src="./../lib/jquery-ui-1.10.3/ui/jquery.ui.datepicker.js"></script>
	
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
				}
		
				function removeError() {
					document.getElementById("warningMsg").innerHTML = "";
				}
		
				$(function() {
					$( "#from_date" ).datepicker({ minDate: "-10y", maxDate: "+0d" });
					$( "#to_date" ).datepicker({ minDate: "-10y", maxDate: "+0d" });
				});
			</script>

			<!--
			<div style="margin-left:58px; margin-right:58px; padding:6px; background-color:#FFF; border:#999 1px solid;"><?php echo $paginationDisplay; ?></div>
			-->
			<link href="./../css/mainstyle.css" rel="stylesheet" type="text/css">
			<link rel="stylesheet" href="./../lib/Zebra_Pagination-master/public/css/zebra_pagination.css" type="text/css">
			<div style="width:80%; font-family:Arial, Helvetica, sans-serif;font-size:12px;line-height:18px; border: 0px solid black; margin: 0px auto;background-color:white">
				<br><br>
				<form action="patron_management.php" method="post" onsubmit="return checkValidator(this)" name="mainForm">
					<table width=100% style="background-color:white">
						<tr>
							<td colspan=2><br>
								<div class="mainTitle"><strong><center>Photo Access ID - Patron Management</center></strong></div>
							</td>
						</tr>
						<tr>
							<td colspan=2>
								<center><?php if (isset($warningMsg)) echo "<font color=red id=\"warningMsg\">".$warningMsg."</font>"; ?></center>
							</td>
						</tr>	
						<tr>
							<td colspan=3 style="text-align:center"><br>
								<select name="lookup_field" id="lookup_field">
									<option value=""></option>
									<option value="patron_number" <?php if ($lookup_field == "patron_number") echo " selected";?>>UNC Charlotte ID</option>
									<option value="patron_last_name" <?php if ($lookup_field == "patron_last_name") echo " selected";?>>LastName FirstName</option>
									<option value="patron_is_active" <?php if ($lookup_field == "patron_is_active") echo " selected";?>>Active</option>
								</select>
								<input type="text" name="lookup_value" id="lookup_value" size=60 value="<?php if (!empty($lookup_value)) echo $lookup_value; ?>">
								<input type="submit" name="search" value="Search" class=dark_green_button>
								<input type="submit" name="reset" value="Reset" class=deleteButton>
								<div style="padding-top:.5em"></div>
								From <input type="text" name="from_date" id="from_date" value="<?php if (!empty($from_date)) { if ($warningMsg == "") echo date("m/d/Y", strtotime($from_date)); else echo $from_date; } ?>" onChange="removeError()">
								To <input type="text" name="to_date" id="to_date" value="<?php if (!empty($to_date)) { if ($warningMsg == "") echo date("m/d/Y", strtotime($to_date)); else echo $to_date; } ?>" onChange="removeError()">
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
										echo "<td width=10% style=\"vertical-align:middle !important;\"><img src=\"./".$outputArray[$row]['pic_location']."\" alt=\"Edit\" width=\"100\" height=\"120\"></td>\n";
										} else {
										echo "<td width=10% style=\"vertical-align:middle !important;\"><img src=\"data:image/png;base64,".base64_encode($outputArray[$row]['pic_image'])."\" alt=\"Edit\" width=\"100\" height=\"120\"></td>\n";									
										}
										echo "<td width=10% style=\"vertical-align:middle !important;\"><center><div style=\"width:20px; height:20px; border-radius:10px; background-color:".$colorStatus."\"></div></center></td>\n					
										<td width=5% style=\"vertical-align:middle !important;\"><a href=\"./edit_patron.php?id=".$outputArray[$row]['id']."\" class=\"blue_button\"><font color=white>Edit</font></a></td>\n
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
					<div class="mainTitle"><strong><center>Photo Access ID - Patron Management</center></strong></div>
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
		$reDirectLocation = "Location: ./../web/admin_login.php?page_link=".$page_link;
		//echo "reDirection Location: '".$reDirectLocation."'<br>";
		header($reDirectLocation);
		exit();
	}
} else {
	include ("./../web/header.html");
?>
	<link href="./../css/mainstyle.css" rel="stylesheet" type="text/css">
	<div style="width:80%; font-family:Arial, Helvetica, sans-serif;font-size:12px;line-height:18px; border: 0px solid black; margin: 0px auto;background-color:white">
		<table width=100%>
			<tr>
				<td><br>
					<div class="mainTitle"><strong><center>Photo Access ID - Patron Management</center></strong></div>
				</td>
			</tr>
			<tr>
				<td>
					<center><br><br>
					<div style="color:#FF0000; text-align:center; background-color:#FFFFFF" id="errorMsg">
						<?php echo $checkConnect['error']; ?>
					</div> 
					</center>
				</td>
			</tr>
		</table>
	</div>
	<BR><BR><BR>
	<?php include ("./../web/footer.html"); 
}	
?>