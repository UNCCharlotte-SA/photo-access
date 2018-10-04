<?php
/*
 *******************************************************************************************************
*
* Name: search_log.php
* Display searching patron user
* Writer: Bach Nguyen (bnguye21@uncc.edu)
* Last Updated: 02/23/2015
*	02/23/2015
*		- Changed backup departments and changed query to list all departments which a person is handle
*	02/20/2015
*		- Changed databse class, session class, paging class
*	01/15/2015
*		Changed file name, session name, and fix bug
* 	10/05/2014
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
		$department_backup = explode(";",$_SESSION['photo_access']['login']['department_backup']);
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

		function addQuoteToValue(&$item, $key, $prefix)	{
			if (empty($item)) {
				$item = "NULL";
			} else {
				$item = trim(str_replace("'","\'",$item));
				$item = "$prefix$item$prefix";
			}
		}
		
		$viewDepartment = array();
		$viewDepartment = $department_backup;
		$viewDepartment[] = $department;
		
		array_walk($viewDepartment, 'addQuoteToValue', "'");
		$listDepartment = implode(",",$viewDepartment);
		
		if (isset($_GET['warningMsg'])) {
			$warningMsg = $_GET['warningMsg'];
		} else {
			$warningMsg = "";
		}	
		if (intval($userGroup) > 3) {
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
				if (isset($_SESSION['photo_access']['admin']['search_log']['lookup_field'])) {
					$lookup_field =	$_SESSION['photo_access']['admin']['search_log']['lookup_field'];
				} else {
					$lookup_field = "";
				}
			}
		}
		$patron_last_name = "";
		$patron_first_name = "";
		$login_last_name = "";
		$login_first_name = "";
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
			if (!empty($lookup_field) and $lookup_field == "login_last_name") {
				$lookupName = explode(' ', $lookup_value);
				//echo "<pre>";
				//	print_r($lookupName);
				//echo "</pre>";	
				//echo sizeof($lookupName);
				if (sizeof($lookupName) > 2) {
					$warningMsg = "Please enter either Lastname or Lastname Firstname format";
				} elseif (sizeof($lookupName) == 2) {
					$login_last_name = $lookupName[0];
					$login_first_name = $lookupName[1];
				} else {
					$login_last_name = $lookupName[0];
				}
			}
		} else {
			if (!empty($_POST["search"])) {
				$lookup_value = "";
			} else {
				if (isset($_SESSION['photo_access']['admin']['search_log']['lookup_value'])) {
					$lookup_value =	$_SESSION['photo_access']['admin']['search_log']['lookup_value'];
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
					} elseif (!empty($lookup_field) and $lookup_field == "login_last_name") {
						if (sizeof($lookupName) == 2) {
							$login_last_name = $lookupName[0];
							$login_first_name = $lookupName[1];
						} else {
							$login_last_name = $lookupName[0];
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
		//echo "user lastname : ".$login_last_name."<br>";
		//echo "user firstname : ".$login_first_name."<br>";
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
		if (isset($_SESSION['photo_access']['admin']['search_log']['text_query'])) {
			$text_query = $_SESSION['photo_access']['admin']['search_log']['text_query'];
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
			unset($_SESSION['photo_access']['admin']['search_log']['text_query']);
			unset($_SESSION['photo_access']['admin']['search_log']['lookup_field']);
			unset($_SESSION['photo_access']['admin']['search_log']['lookup_value']);
			unset($_SESSION['photo_access']['admin']['search_log']['from_date']);
			unset($_SESSION['photo_access']['admin']['search_log']['to_date']);
		}

		if ($warningMsg == "" or strpos($warningMsg,"Success") !== false) {
			//echo "1<br>";
			if ($userGroup == 1) {
				//echo "2<br>";
				if ($lookup_field != "" and $lookup_value != "" and empty($from_date) and empty($to_date)) {
					//echo "3<br>";
					if ($lookup_value != "null") {
						//echo "4<br>";
						if ($lookup_value != "*") {
							//echo "5<br>";
							if (!empty($patron_last_name) and !empty($patron_first_name)) {
								//echo "6<br>";
								$text_query = "SELECT a.*, b.login_name, b.login_first_name, b.login_last_name, b.login_user_dept, b.login_user_email, b.internet_address FROM photo_access_searching_log a left join photo_access_user_login_log b on a.login_id=b.login_id WHERE patron_last_name like '".str_replace("'","\'",$patron_last_name)."%' and patron_first_name like '".str_replace("'","\'",$patron_first_name). "%'";
							} elseif (!empty($patron_last_name)) {
								//echo "7<br>";
								$text_query = "SELECT a.*, b.login_name, b.login_first_name, b.login_last_name, b.login_user_dept, b.login_user_email, b.internet_address FROM photo_access_searching_log a left join photo_access_user_login_log b on a.login_id=b.login_id WHERE patron_last_name like '".str_replace("'","\'",$patron_last_name)."%'";
							} elseif (!empty($login_last_name) and !empty($login_first_name)) {
								//echo "8<br>";
								$text_query = "SELECT a.*, b.login_name, b.login_first_name, b.login_last_name, b.login_user_dept, b.login_user_email, b.internet_address FROM photo_access_searching_log a left join photo_access_user_login_log b on a.login_id=b.login_id WHERE login_last_name like '".str_replace("'","\'",$login_last_name)."%' and login_first_name like '".str_replace("'","\'",$login_first_name). "%'";
							} elseif (!empty($login_last_name)) {
								//echo "9<br>";
								$text_query = "SELECT a.*, b.login_name, b.login_first_name, b.login_last_name, b.login_user_dept, b.login_user_email, b.internet_address FROM photo_access_searching_log a left join photo_access_user_login_log b on a.login_id=b.login_id WHERE login_last_name like '".str_replace("'","\'",$login_last_name)."%'";
							} else {
								//echo "10<br>";
								$text_query = "SELECT a.*, b.login_name, b.login_first_name, b.login_last_name, b.login_user_dept, b.login_user_email, b.internet_address FROM photo_access_searching_log a left join photo_access_user_login_log b on a.login_id=b.login_id WHERE ".$lookup_field." like '%".str_replace("'","\'",$lookup_value). "%'";
							}	
						} else {
							//echo "11<br>";
							$text_query = "SELECT a.*, b.login_name, b.login_first_name, b.login_last_name, b.login_user_dept, b.login_user_email, b.internet_address FROM photo_access_searching_log a left join photo_access_user_login_log b on a.login_id=b.login_id WHERE ".$lookup_field." is not NULL";
						}
					} else {
						//echo "12<br>";
						$text_query = "SELECT a.*, b.login_name, b.login_first_name, b.login_last_name, b.login_user_dept, b.login_user_email, b.internet_address FROM photo_access_searching_log a left join photo_access_user_login_log b on a.login_id=b.login_id WHERE ".$lookup_field." is NULL";
					}
				} elseif ($lookup_field != "" and $lookup_value != "" and !empty($from_date) and empty($to_date)) {
					//echo "13<br>";
					if ($lookup_value != "null") {
						//echo "14<br>";
						if ($lookup_value != "*") {
							//echo "15<br>";
							if (!empty($patron_last_name) and !empty($patron_first_name)) {
								//echo "16<br>";
								$text_query = "SELECT a.*, b.login_name, b.login_first_name, b.login_last_name, b.login_user_dept, b.login_user_email, b.internet_address FROM photo_access_searching_log a left join photo_access_user_login_log b on a.login_id=b.login_id WHERE patron_last_name like '".str_replace("'","\'",$patron_last_name)."%' and patron_first_name like '".str_replace("'","\'",$patron_first_name). "%' and date(date_searching) between '".date('Y-m-d',strtotime($from_date))."' and '".$todayDate."'";
							} elseif (!empty($patron_last_name)) {
								//echo "17<br>";
								$text_query = "SELECT a.*, b.login_name, b.login_first_name, b.login_last_name, b.login_user_dept, b.login_user_email, b.internet_address FROM photo_access_searching_log a left join photo_access_user_login_log b on a.login_id=b.login_id WHERE patron_last_name like '".str_replace("'","\'",$patron_last_name)."%' and date(date_searching) between '".date('Y-m-d',strtotime($from_date))."' and '".$todayDate."'";
							} elseif (!empty($login_last_name) and !empty($login_first_name)) {
								//echo "18<br>";
								$text_query = "SELECT a.*, b.login_name, b.login_first_name, b.login_last_name, b.login_user_dept, b.login_user_email, b.internet_address FROM photo_access_searching_log a left join photo_access_user_login_log b on a.login_id=b.login_id WHERE login_last_name like '".str_replace("'","\'",$login_last_name)."%' and login_first_name like '".str_replace("'","\'",$login_first_name). "%' and date(date_searching) between '".date('Y-m-d',strtotime($from_date))."' and '".$todayDate."'";
							} elseif (!empty($login_last_name)) {
								//echo "19<br>";
								$text_query = "SELECT a.*, b.login_name, b.login_first_name, b.login_last_name, b.login_user_dept, b.login_user_email, b.internet_address FROM photo_access_searching_log a left join photo_access_user_login_log b on a.login_id=b.login_id WHERE login_last_name like '".str_replace("'","\'",$login_last_name)."%' and date(date_searching) between '".date('Y-m-d',strtotime($from_date))."' and '".$todayDate."'";
							} else {
								//echo "20<br>";
								$text_query = "SELECT a.*, b.login_name, b.login_first_name, b.login_last_name, b.login_user_dept, b.login_user_email, b.internet_address FROM photo_access_searching_log a left join photo_access_user_login_log b on a.login_id=b.login_id WHERE ".$lookup_field." like '%".str_replace("'","\'",$lookup_value). "%' and date(date_searching) between '".date('Y-m-d',strtotime($from_date))."' and '".$todayDate."'";
							}	
						} else {
							//echo "21<br>";
							$text_query = "SELECT a.*, b.login_name, b.login_first_name, b.login_last_name, b.login_user_dept, b.login_user_email, b.internet_address FROM photo_access_searching_log a left join photo_access_user_login_log b on a.login_id=b.login_id WHERE ".$lookup_field." is not NULL and date(date_searching) between '".date('Y-m-d',strtotime($from_date))."' and '".$todayDate."'";
						}
					} else {
						//echo "22<br>";
						$text_query = "SELECT a.*, b.login_name, b.login_first_name, b.login_last_name, b.login_user_dept, b.login_user_email, b.internet_address FROM photo_access_searching_log a left join photo_access_user_login_log b on a.login_id=b.login_id WHERE ".$lookup_field." is NULL and date(date_searching) between '".date('Y-m-d',strtotime($from_date))."' and '".$todayDate."'";
					}
				} elseif ($lookup_field != "" and $lookup_value != "" and empty($from_date) and !empty($to_date)) {
					//echo "23<br>";
					if ($lookup_value != "null") {
						//echo "24<br>";
						if ($lookup_value != "*") {
							//echo "25<br>";
							if (!empty($patron_last_name) and !empty($patron_first_name)) {
								//echo "26<br>";
								$text_query = "SELECT a.*, b.login_name, b.login_first_name, b.login_last_name, b.login_user_dept, b.login_user_email, b.internet_address FROM photo_access_searching_log a left join photo_access_user_login_log b on a.login_id=b.login_id WHERE patron_last_name like '".str_replace("'","\'",$patron_last_name)."%' and patron_first_name like '".str_replace("'","\'",$patron_first_name). "%' and date(date_searching) <='".date('Y-m-d',strtotime($to_date))."'";
							} elseif (!empty($patron_last_name)) {
								//echo "27<br>";
								$text_query = "SELECT a.*, b.login_name, b.login_first_name, b.login_last_name, b.login_user_dept, b.login_user_email, b.internet_address FROM photo_access_searching_log a left join photo_access_user_login_log b on a.login_id=b.login_id WHERE patron_last_name like '".str_replace("'","\'",$patron_last_name)."%' and date(date_searching) <='".date('Y-m-d',strtotime($to_date))."'";
							} elseif (!empty($login_last_name) and !empty($login_first_name)) {
								//echo "28<br>";
								$text_query = "SELECT a.*, b.login_name, b.login_first_name, b.login_last_name, b.login_user_dept, b.login_user_email, b.internet_address FROM photo_access_searching_log a left join photo_access_user_login_log b on a.login_id=b.login_id WHERE login_last_name like '".str_replace("'","\'",$login_last_name)."%' and login_first_name like '".str_replace("'","\'",$login_first_name). "%' and date(date_searching) <='".date('Y-m-d',strtotime($to_date))."'";
							} elseif (!empty($login_last_name)) {
								//echo "29<br>";
								$text_query = "SELECT a.*, b.login_name, b.login_first_name, b.login_last_name, b.login_user_dept, b.login_user_email, b.internet_address FROM photo_access_searching_log a left join photo_access_user_login_log b on a.login_id=b.login_id WHERE login_last_name like '".str_replace("'","\'",$login_last_name)."%' and date(date_searching) <='".date('Y-m-d',strtotime($to_date))."'";
							} else {
								//echo "30<br>";
								$text_query = "SELECT a.*, b.login_name, b.login_first_name, b.login_last_name, b.login_user_dept, b.login_user_email, b.internet_address FROM photo_access_searching_log a left join photo_access_user_login_log b on a.login_id=b.login_id WHERE ".$lookup_field." like '%".str_replace("'","\'",$lookup_value). "%' and date(date_searching) <='".date('Y-m-d',strtotime($to_date))."'";
							}	
						} else {
							//echo "31<br>";
							$text_query = "SELECT a.*, b.login_name, b.login_first_name, b.login_last_name, b.login_user_dept, b.login_user_email, b.internet_address FROM photo_access_searching_log a left join photo_access_user_login_log b on a.login_id=b.login_id WHERE ".$lookup_field." is not NULL and date(date_searching) <='".date('Y-m-d',strtotime($to_date))."'";
						}	
					} else {
						//echo "32<br>";
						$text_query = "SELECT a.*, b.login_name, b.login_first_name, b.login_last_name, b.login_user_dept, b.login_user_email, b.internet_address FROM photo_access_searching_log a left join photo_access_user_login_log b on a.login_id=b.login_id WHERE ".$lookup_field." is NULL and date(date_searching) <='".date('Y-m-d',strtotime($to_date))."'";
					}
				} elseif ($lookup_field != "" and $lookup_value != "" and !empty($from_date) and !empty($to_date)) {
					//echo "33<br>";
					if ($lookup_value != "null") {
						//echo "34<br>";
						if ($lookup_value != "*") {
							//echo "35<br>";
							if (!empty($patron_last_name) and !empty($patron_first_name)) {
								//echo "36<br>";
								$text_query = "SELECT a.*, b.login_name, b.login_first_name, b.login_last_name, b.login_user_dept, b.login_user_email, b.internet_address FROM photo_access_searching_log a left join photo_access_user_login_log b on a.login_id=b.login_id WHERE patron_last_name like '".str_replace("'","\'",$patron_last_name)."%' and patron_first_name like '".str_replace("'","\'",$patron_first_name). "%' and date(date_searching) between '".date('Y-m-d',strtotime($from_date))."' and '".date('Y-m-d',strtotime($to_date))."'";
							} elseif (!empty($patron_last_name)) {
								//echo "37<br>";
								$text_query = "SELECT a.*, b.login_name, b.login_first_name, b.login_last_name, b.login_user_dept, b.login_user_email, b.internet_address FROM photo_access_searching_log a left join photo_access_user_login_log b on a.login_id=b.login_id WHERE patron_last_name like '".str_replace("'","\'",$patron_last_name)."%' and date(date_searching) between '".date('Y-m-d',strtotime($from_date))."' and '".date('Y-m-d',strtotime($to_date))."'";
							} elseif (!empty($login_last_name) and !empty($login_first_name)) {
								//echo "38<br>";
								$text_query = "SELECT a.*, b.login_name, b.login_first_name, b.login_last_name, b.login_user_dept, b.login_user_email, b.internet_address FROM photo_access_searching_log a left join photo_access_user_login_log b on a.login_id=b.login_id WHERE login_last_name like '".str_replace("'","\'",login_last_name)."%' and login_first_name like '".str_replace("'","\'",$login_first_name). "%' and date(date_searching) between '".date('Y-m-d',strtotime($from_date))."' and '".date('Y-m-d',strtotime($to_date))."'";
							} elseif (!empty($login_last_name)) {
								//echo "39<br>";
								$text_query = "SELECT a.*, b.login_name, b.login_first_name, b.login_last_name, b.login_user_dept, b.login_user_email, b.internet_address FROM photo_access_searching_log a left join photo_access_user_login_log b on a.login_id=b.login_id WHERE login_last_name like '".str_replace("'","\'",$login_last_name)."%' and date(date_searching) between '".date('Y-m-d',strtotime($from_date))."' and '".date('Y-m-d',strtotime($to_date))."'";
							} else {
								//echo "40<br>";
								$text_query = "SELECT a.*, b.login_name, b.login_first_name, b.login_last_name, b.login_user_dept, b.login_user_email, b.internet_address FROM photo_access_searching_log a left join photo_access_user_login_log on b a.login_id=b.login_id WHERE ".$lookup_field." like '%".str_replace("'","\'",$lookup_value). "%' and date(date_searching) between '".date('Y-m-d',strtotime($from_date))."' and '".date('Y-m-d',strtotime($to_date))."'";
							}	
						} else {
							//echo "41<br>";
							$text_query = "SELECT a.*, b.login_name, b.login_first_name, b.login_last_name, b.login_user_dept, b.login_user_email, b.internet_address FROM photo_access_searching_log a left join photo_access_user_login_log b on a.login_id=b.login_id WHERE ".$lookup_field." is not NULL and date(date_searching) between '".date('Y-m-d',strtotime($from_date))."' and '".date('Y-m-d',strtotime($to_date))."'";
						}
					} else {
						//echo "42<br>";
						$text_query = "SELECT a.*, b.login_name, b.login_first_name, b.login_last_name, b.login_user_dept, b.login_user_email, b.internet_address FROM photo_access_searching_log a left join photo_access_user_login_log b on a.login_id=b.login_id WHERE ".$lookup_field." is NULL and date(date_searching) between '".date('Y-m-d',strtotime($from_date))."' and '".date('Y-m-d',strtotime($to_date))."'";
					}
				} elseif ($lookup_field == "" and $lookup_value == "" and !empty($from_date) and empty($to_date)) {
					//echo "43<br>";
					$text_query = "SELECT a.*, b.login_name, b.login_first_name, b.login_last_name, b.login_user_dept, b.login_user_email, b.internet_address FROM photo_access_searching_log a left join photo_access_user_login_log b on a.login_id=b.login_id WHERE date(date_searching) between '".date('Y-m-d',strtotime($from_date))."' and '".$todayDate."'";
				} elseif ($lookup_field == "" and $lookup_value == "" and empty($from_date) and !empty($to_date)) {
					//echo "44<br>";
					$text_query = "SELECT a.*, b.login_name, b.login_first_name, b.login_last_name, b.login_user_dept, b.login_user_email, b.internet_address FROM photo_access_searching_log a left join photo_access_user_login_log b on a.login_id=b.login_id WHERE date(date_searching) <='".date('Y-m-d',strtotime($to_date))."'";
				} elseif ($lookup_field == "" and $lookup_value == "" and !empty($from_date) and !empty($to_date)) {
					//echo "45<br>";
					$text_query = "SELECT a.*, b.login_name, b.login_first_name, b.login_last_name, b.login_user_dept, b.login_user_email, b.internet_address FROM photo_access_searching_log a left join photo_access_user_login_log b on a.login_id=b.login_id WHERE date(date_searching) between '".date('Y-m-d',strtotime($from_date))."' and '".date('Y-m-d',strtotime($to_date))."'";
				} else {
					//echo "46<br>";
					$text_query = "SELECT a.*, b.login_name, b.login_first_name, b.login_last_name, b.login_user_dept, b.login_user_email, b.internet_address FROM photo_access_searching_log a left join photo_access_user_login_log b on a.login_id=b.login_id";
				}
			} else {
				//echo "48<br>";
				if ($lookup_field != "" and $lookup_value != "" and empty($from_date) and empty($to_date)) {
					//echo "49<br>";
					if ($lookup_value != "null") {
						//echo "50<br>";
						if ($lookup_value != "*") {
							//echo "51<br>";
							if (!empty($patron_last_name) and !empty($patron_first_name)) {
								//echo "52<br>";
								$text_query = "SELECT a.*, b.login_name, b.login_first_name, b.login_last_name, b.login_user_dept, b.login_user_email, b.internet_address FROM photo_access_searching_log a left join photo_access_user_login_log b on a.login_id=b.login_id WHERE login_user_dept IN (".$listDepartment.") and patron_last_name like '".str_replace("'","\'",$patron_last_name)."%' and patron_first_name like '".str_replace("'","\'",$patron_first_name). "%'";
							} elseif (!empty($patron_last_name)) {
								//echo "53<br>";
								$text_query = "SELECT a.*, b.login_name, b.login_first_name, b.login_last_name, b.login_user_dept, b.login_user_email, b.internet_address FROM photo_access_searching_log a left join photo_access_user_login_log b on a.login_id=b.login_id WHERE login_user_dept IN (".$listDepartment.") and patron_last_name like '".str_replace("'","\'",$patron_last_name). "%'";
							} elseif (!empty($login_last_name) and !empty($login_first_name)) {
								//echo "54<br>";
								$text_query = "SELECT a.*, b.login_name, b.login_first_name, b.login_last_name, b.login_user_dept, b.login_user_email, b.internet_address FROM photo_access_searching_log a left join photo_access_user_login_log b on a.login_id=b.login_id WHERE login_user_dept IN (".$listDepartment.") and login_last_name like '".str_replace("'","\'",$login_last_name)."%' and login_first_name like '".str_replace("'","\'",$login_first_name). "%'";
							} elseif (!empty($patron_last_name)) {
								//echo "55<br>";
								$text_query = "SELECT a.*, b.login_name, b.login_first_name, b.login_last_name, b.login_user_dept, b.login_user_email, b.internet_address FROM photo_access_searching_log a left join photo_access_user_login_log b on a.login_id=b.login_id WHERE login_user_dept IN (".$listDepartment.") and login_last_name like '".str_replace("'","\'",$login_last_name). "%'";
							} else {
								//echo "56<br>";
								$text_query = "SELECT a.*, b.login_name, b.login_first_name, b.login_last_name, b.login_user_dept, b.login_user_email, b.internet_address FROM photo_access_searching_log a left join photo_access_user_login_log b on a.login_id=b.login_id WHERE login_user_dept IN (".$listDepartment.") and ".$lookup_field." like '%".str_replace("'","\'",$lookup_value). "%'";
							}	
						} else {
							//echo "57<br>";
							$text_query = "SELECT a.*, b.login_name, b.login_first_name, b.login_last_name, b.login_user_dept, b.login_user_email, b.internet_address FROM photo_access_searching_log a left join photo_access_user_login_log b on a.login_id=b.login_id WHERE login_user_dept IN(".$listDepartment.") and ".$lookup_field." is not NULL";
						}
					} else {
						//echo "58<br>";
						$text_query = "SELECT a.*, b.login_name, b.login_first_name, b.login_last_name, b.login_user_dept, b.login_user_email, b.internet_address FROM photo_access_searching_log a left join photo_access_user_login_log b on a.login_id=b.login_id WHERE login_user_dept IN (".$listDepartment.") and ".$lookup_field." is NULL";
					}
				} elseif ($lookup_field != "" and $lookup_value != "" and !empty($from_date) and empty($to_date)) {
					//echo "59<br>";
					if ($lookup_value != "null") {
						//echo "60<br>";
						if ($lookup_value != "*") {
							//echo "61<br>";
							if (!empty($patron_last_name) and !empty($patron_first_name)) {
								//echo "62<br>";
								$text_query = "SELECT a.*, b.login_name, b.login_first_name, b.login_last_name, b.login_user_dept, b.login_user_email, b.internet_address FROM photo_access_searching_log a left join photo_access_user_login_log b on a.login_id=b.login_id WHERE login_user_dept IN(".$listDepartment.") and patron_last_name like '".str_replace("'","\'",$patron_last_name)."%' and patron_first_name like '".str_replace("'","\'",$patron_first_name). "%' and date(date_searching) between '".date('Y-m-d',strtotime($from_date))."' and '".$todayDate."'";
							} elseif (!empty($patron_last_name)) {
								//echo "63<br>";
								$text_query = "SELECT a.*, b.login_name, b.login_first_name, b.login_last_name, b.login_user_dept, b.login_user_email, b.internet_address FROM photo_access_searching_log a left join photo_access_user_login_log b on a.login_id=b.login_id WHERE login_user_dept IN (".$listDepartment.") and patron_last_name like '".str_replace("'","\'",$patron_last_name). "%' and date(date_searching) between '".date('Y-m-d',strtotime($from_date))."' and '".$todayDate."'";
							} elseif (!empty($login_last_name) and !empty($login_first_name)) {
								//echo "64<br>";
								$text_query = "SELECT a.*, b.login_name, b.login_first_name, b.login_last_name, b.login_user_dept, b.login_user_email, b.internet_address FROM photo_access_searching_log a left join photo_access_user_login_log b on a.login_id=b.login_id WHERE login_user_dept IN (".$listDepartment.") and login_last_name like '".str_replace("'","\'",$login_last_name)."%' and login_first_name like '".str_replace("'","\'",$login_first_name). "%' and date(date_searching) between '".date('Y-m-d',strtotime($from_date))."' and '".$todayDate."'";
							} elseif (!empty($patron_last_name)) {
								//echo "65<br>";
								$text_query = "SELECT a.*, b.login_name, b.login_first_name, b.login_last_name, b.login_user_dept, b.login_user_email, b.internet_address FROM photo_access_searching_log a left join photo_access_user_login_log b on a.login_id=b.login_id WHERE login_user_dept IN (".$listDepartment.") and login_last_name like '".str_replace("'","\'",$login_last_name). "%' and date(date_searching) between '".date('Y-m-d',strtotime($from_date))."' and '".$todayDate."'";
							} else {
								//echo "66<br>";
								$text_query = "SELECT a.*, b.login_name, b.login_first_name, b.login_last_name, b.login_user_dept, b.login_user_email, b.internet_address FROM photo_access_searching_log a left join photo_access_user_login_log b on a.login_id=b.login_id WHERE login_user_dept IN (".$listDepartment.") and ".$lookup_field." like '%".str_replace("'","\'",$lookup_value). "%' and date(date_searching) between '".date('Y-m-d',strtotime($from_date))."' and '".$todayDate."'";
							}	
						} else {
							//echo "67<br>";
							$text_query = "SELECT a.*, b.login_name, b.login_first_name, b.login_last_name, b.login_user_dept, b.login_user_email, b.internet_address FROM photo_access_searching_log a left join photo_access_user_login_log on b a.login_id=b.login_id WHERE login_user_dept IN (".$listDepartment.") and ".$lookup_field." is not NULL and date(date_searching) between '".date('Y-m-d',strtotime($from_date))."' and '".$todayDate."'";
						}
					} else {
						//echo "68<br>";
						$text_query = "SELECT a.*, b.login_name, b.login_first_name, b.login_last_name, b.login_user_dept, b.login_user_email, b.internet_address FROM photo_access_searching_log a left join photo_access_user_login_log b on a.login_id=b.login_id WHERE login_user_dept IN (".$listDepartment.") and ".$lookup_field." is NULL and date(date_searching) between '".date('Y-m-d',strtotime($from_date))."' and '".$todayDate."'";
					}
				} elseif ($lookup_field != "" and $lookup_value != "" and empty($from_date) and !empty($to_date)) {
					//echo "69<br>";
					if ($lookup_value != "null") {
						//echo "70<br>";
						if ($lookup_value != "*") {
							//echo "71<br>";
							if (!empty($patron_last_name) and !empty($patron_first_name)) {
								//echo "72<br>";
								$text_query = "SELECT a.*, b.login_name, b.login_first_name, b.login_last_name, b.login_user_dept, b.login_user_email, b.internet_address FROM photo_access_searching_log a left join photo_access_user_login_log b on a.login_id=b.login_id WHERE login_user_dept IN (".$listDepartment.") and patron_last_name like '".str_replace("'","\'",$patron_last_name)."%' and patron_first_name like '".str_replace("'","\'",$patron_first_name). "%' and date(date_searching) <='".date('Y-m-d',strtotime($to_date))."'";
							} elseif (!empty($patron_last_name)) {
								//echo "73<br>";
								$text_query = "SELECT a.*, b.login_name, b.login_first_name, b.login_last_name, b.login_user_dept, b.login_user_email, b.internet_address FROM photo_access_searching_log a left join photo_access_user_login_log b on a.login_id=b.login_id WHERE login_user_dept IN (".$listDepartment.") and patron_last_name like '".str_replace("'","\'",$patron_last_name). "%' and date(date_searching) <='".date('Y-m-d',strtotime($to_date))."'";
							} elseif (!empty($login_last_name) and !empty($login_first_name)) {
								//echo "74<br>";
								$text_query = "SELECT a.*, b.login_name, b.login_first_name, b.login_last_name, b.login_user_dept, b.login_user_email, b.internet_address FROM photo_access_searching_log a left join photo_access_user_login_log b on a.login_id=b.login_id WHERE login_user_dept IN (".$listDepartment.") and login_last_name like '".str_replace("'","\'",$login_last_name)."%' and login_first_name like '".str_replace("'","\'",$login_first_name). "%' and date(date_searching) <='".date('Y-m-d',strtotime($to_date))."'";
							} elseif (!empty($patron_last_name)) {
								//echo "75<br>";
								$text_query = "SELECT a.*, b.login_name, b.login_first_name, b.login_last_name, b.login_user_dept, b.login_user_email, b.internet_address FROM photo_access_searching_log a left join photo_access_user_login_log b on a.login_id=b.login_id WHERE login_user_dept IN (".$listDepartment.") and login_last_name like '".str_replace("'","\'",$login_last_name). "%' and date(date_searching) <='".date('Y-m-d',strtotime($to_date))."'";
							} else {
								//echo "76<br>";
								$text_query = "SELECT a.*, b.login_name, b.login_first_name, b.login_last_name, b.login_user_dept, b.login_user_email, b.internet_address FROM photo_access_searching_log a left join photo_access_user_login_log b on a.login_id=b.login_id WHERE login_user_dept IN (".$listDepartment.") and ".$lookup_field." like '%".str_replace("'","\'",$lookup_value). "%' and date(date_searching) <='".date('Y-m-d',strtotime($to_date))."'";
							}	
						} else {
							//echo "77<br>";
							$text_query = "SELECT a.*, b.login_name, b.login_first_name, b.login_last_name, b.login_user_dept, b.login_user_email, b.internet_address FROM photo_access_searching_log a left join photo_access_user_login_log b on a.login_id=b.login_id WHERE login_user_dept IN (".$listDepartment.") and ".$lookup_field." is not NULL and date(date_searching) <='".date('Y-m-d',strtotime($to_date))."'";
						}
					} else {
						//echo "78<br>";
						$text_query = "SELECT a.*, b.login_name, b.login_first_name, b.login_last_name, b.login_user_dept, b.login_user_email, b.internet_address FROM photo_access_searching_log a left join photo_access_user_login_log b on a.login_id=b.login_id WHERE login_user_dept IN (".$listDepartment.") and ".$lookup_field." is NULL and date(date_searching) <='".date('Y-m-d',strtotime($to_date))."'";
					}
				} elseif ($lookup_field != "" and $lookup_value != "" and !empty($from_date) and !empty($to_date)) {
					//echo "79<br>";
					if ($lookup_value != "null") {
						//echo "80<br>";
						if ($lookup_value != "*") {
							//echo "81<br>";
							if (!empty($patron_last_name) and !empty($patron_first_name)) {
								//echo "82<br>";
								$text_query = "SELECT a.*, b.login_name, b.login_first_name, b.login_last_name, b.login_user_dept, b.login_user_email, b.internet_address FROM photo_access_searching_log a left join photo_access_user_login_log b on a.login_id=b.login_id WHERE login_user_dept IN (".$listDepartment.") and patron_last_name like '".str_replace("'","\'",$patron_last_name)."%' and patron_first_name like '".str_replace("'","\'",$patron_first_name). "%' and date(date_searching) between '".date('Y-m-d',strtotime($from_date))."' and '".date('Y-m-d',strtotime($to_date))."'";
							} elseif (!empty($patron_last_name)) {
								//echo "83<br>";
								$text_query = "SELECT a.*, b.login_name, b.login_first_name, b.login_last_name, b.login_user_dept, b.login_user_email, b.internet_address FROM photo_access_searching_log a left join photo_access_user_login_log b on a.login_id=b.login_id WHERE login_user_dept IN (".$listDepartment.") and patron_last_name like '".str_replace("'","\'",$patron_last_name). "%' and date(date_searching) between '".date('Y-m-d',strtotime($from_date))."' and '".date('Y-m-d',strtotime($to_date))."'";
							} elseif (!empty($login_last_name) and !empty($login_first_name)) {
								//echo "84<br>";
								$text_query = "SELECT a.*, b.login_name, b.login_first_name, b.login_last_name, b.login_user_dept, b.login_user_email, b.internet_address FROM photo_access_searching_log a left join photo_access_user_login_log b on a.login_id=b.login_id WHERE login_user_dept IN (".$listDepartment.") and login_last_name like '".str_replace("'","\'",$login_last_name)."%' and login_first_name like '".str_replace("'","\'",$login_first_name). "%' and date(date_searching) between '".date('Y-m-d',strtotime($from_date))."' and '".date('Y-m-d',strtotime($to_date))."'";
							} elseif (!empty($patron_last_name)) {
								//echo "85<br>";
								$text_query = "SELECT a.*, b.login_name, b.login_first_name, b.login_last_name, b.login_user_dept, b.login_user_email, b.internet_address FROM photo_access_searching_log a left join photo_access_user_login_log b on a.login_id=b.login_id WHERE login_user_dept IN (".$listDepartment.") and login_last_name like '".str_replace("'","\'",$login_last_name). "%' and date(date_searching) between '".date('Y-m-d',strtotime($from_date))."' and '".date('Y-m-d',strtotime($to_date))."'";
							} else {
								//echo "86<br>";
								$text_query = "SELECT a.*, b.login_name, b.login_first_name, b.login_last_name, b.login_user_dept, b.login_user_email, b.internet_address FROM photo_access_searching_log a left join photo_access_user_login_log b on a.login_id=b.login_id WHERE login_user_dept IN (".$listDepartment.") and ".$lookup_field." like '%".str_replace("'","\'",$lookup_value). "%' and date(date_searching) between '".date('Y-m-d',strtotime($from_date))."' and '".date('Y-m-d',strtotime($to_date))."'";
							}	
						} else {
							//echo "87<br>";
							$text_query = "SELECT a.*, b.login_name, b.login_first_name, b.login_last_name, b.login_user_dept, b.login_user_email, b.internet_address FROM photo_access_searching_log a left join photo_access_user_login_log b on a.login_id=b.login_id WHERE login_user_dept IN (".$listDepartment.") and ".$lookup_field." is not NULL and date(date_searching) between '".date('Y-m-d',strtotime($from_date))."' and '".date('Y-m-d',strtotime($to_date))."'";
						}
					} else {
						//echo "88<br>";
						$text_query = "SELECT a.*, b.login_name, b.login_first_name, b.login_last_name, b.login_user_dept, b.login_user_email, b.internet_address FROM photo_access_searching_log a left join photo_access_user_login_log b on a.login_id=b.login_id WHERE login_user_dept IN (".$listDepartment.") and ".$lookup_field." is NULL and date(date_searching) between '".date('Y-m-d',strtotime($from_date))."' and '".date('Y-m-d',strtotime($to_date))."'";
					}
				} elseif ($lookup_field == "" and $lookup_value == "" and !empty($from_date) and empty($to_date)) {
					//echo "89<br>";
					$text_query = "SELECT a.*, b.login_name, b.login_first_name, b.login_last_name, b.login_user_dept, b.login_user_email, b.internet_address FROM photo_access_searching_log a left join photo_access_user_login_log b on a.login_id=b.login_id WHERE login_user_dept IN (".$listDepartment.") and date(date_searching) between '".date('Y-m-d',strtotime($from_date))."' and '".$todayDate."'";
				} elseif ($lookup_field == "" and $lookup_value == "" and empty($from_date) and !empty($to_date)) {
					//echo "90<br>";
					$text_query = "SELECT a.*, b.login_name, b.login_first_name, b.login_last_name, b.login_user_dept, b.login_user_email, b.internet_address FROM photo_access_searching_log a left join photo_access_user_login_log b on a.login_id=b.login_id WHERE login_user_dept IN (".$listDepartment.") and date(date_searching) <='".date('Y-m-d',strtotime($to_date))."'";
				} elseif ($lookup_field == "" and $lookup_value == "" and !empty($from_date) and !empty($to_date)) {
					//echo "91<br>";
					$text_query = "SELECT a.*, b.login_name, b.login_first_name, b.login_last_name, b.login_user_dept, b.login_user_email, b.internet_address FROM photo_access_searching_log a left join photo_access_user_login_log b on a.login_id=b.login_id WHERE login_user_dept IN (".$listDepartment.") and date(date_searching) between '".date('Y-m-d',strtotime($from_date))."' and '".date('Y-m-d',strtotime($to_date))."'";
				} else {
					//echo "92<br>";
					$text_query = "SELECT a.*, b.login_name, b.login_first_name, b.login_last_name, b.login_user_dept, b.login_user_email, b.internet_address FROM photo_access_searching_log a left join photo_access_user_login_log b on a.login_id=b.login_id WHERE login_user_dept IN (".$listDepartment.")";
				}		
			}
		//} else {
		//	$text_query = "SELECT * FROM photo_access_searching_log";
		}
		//echo "<br>Text_query : ".$text_query."<br>";
		//echo "lookup_field : ".$lookup_field."<br>";
		//echo "lookup_value : ".$lookup_value."<br>";

		$_SESSION['photo_access']['admin']['search_log']['text_query'] = $text_query;
		$_SESSION['photo_access']['admin']['search_log']['lookup_field'] = $lookup_field;
		$_SESSION['photo_access']['admin']['search_log']['lookup_value'] = $lookup_value;
		$_SESSION['photo_access']['admin']['search_log']['from_date'] = $from_date;
		$_SESSION['photo_access']['admin']['search_log']['to_date'] = $to_date;
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
				//echo "count: ".$row_count."<br>";
				// Build the Output Section Here
				$recordArray = array();
				$outputArray = array();
				while($obj = $db->fetch_obj()) {
					$recordArray['id'] = $obj->id;
					$recordArray['login_first_name'] = $obj->login_first_name;
					$recordArray['login_last_name'] = $obj->login_last_name;
					$recordArray['login_user_email'] = $obj->login_user_email;
					$recordArray['login_user_dept'] = $obj->login_user_dept;
					$recordArray['internet_address'] = $obj->internet_address;
					$recordArray['date_searching'] = $obj->date_searching;
					$recordArray['searching_string'] = $obj->searching_string;
					$recordArray['patron_id'] = $obj->patron_id;
					$recordArray['patron_first_name'] = $obj->patron_first_name;
					$recordArray['patron_last_name'] = $obj->patron_last_name;
					$recordArray['searching_name'] = $obj->login_first_name." ".$obj->login_last_name;
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
			<form action="search_log.php" method="post" onsubmit="return checkValidator(this)" name="mainForm">
			<table width=100% style="background-color:white">
				<tr>
					<td colspan=2><br>
						<div class="mainTitle"><strong><center>Photo Access ID - Search Log</center></strong></div>
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
							<option value="login_last_name" <?php if ($lookup_field == "login_last_name") echo " selected";?>>Searcher Lastname Firstname</option>
							<option value="searching_string" <?php if ($lookup_field == "searching_string") echo " selected";?>>Search Value</option>
							<option value="patron_last_name" <?php if ($lookup_field == "patron_last_name") echo " selected";?>>Student LastName FirstName</option>
							<option value="patron_id" <?php if ($lookup_field == "patron_id") echo " selected";?>>Student ID</option>
							<?php
							if ($userGroup == 1) {
							echo "<option value=\"login_user_dept\""; if ($lookup_field == "login_user_dept") echo " selected"; echo ">Department</option>";
							}
							?>
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
						<table width=100% style="background-color:white" class="TFtable">
							<tr>
								<td width=15%><b>Searcher Name</b></td>
								<td width=15%><b>IP Address</b></td>
								<td width=7%><b>Department</b></td>
								<td width=15%><b>Searched Date</b></td>
								<td width=25%><b>Search Value</b></td>
								<td width=7%><b>Student ID</b></td>
								<td width=16%><b>Student Name</b></td>
							</tr>
							<?php
							for ($row=0; $row<count($outputArray); $row++) {
							echo "<tr>\n
								<td>".$outputArray[$row]['searching_name']."</td>\n
								<td>".$outputArray[$row]['internet_address']."</td>\n
								<td>".$outputArray[$row]['login_user_dept']."</td>\n
								<td>".date($datetime_format, strtotime($outputArray[$row]['date_searching']))."</td>\n
								<td>".$outputArray[$row]['searching_string']."</td>\n
								<td>".$outputArray[$row]['patron_id']."</td>\n
								<td>".$outputArray[$row]['patron_first_name']." ".$outputArray[$row]['patron_last_name']."</td>\n
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
						<div class="mainTitle"><strong><center>Photo Access ID - Search Log</center></strong></div>
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
?>
	<?php include ("./web/header.html");?>
	<link href="./css/mainstyle.css" rel="stylesheet" type="text/css">
	<link rel="stylesheet" href="./lib/Zebra_Pagination-master/public/css/zebra_pagination.css" type="text/css">
	<div style="width:80%; font-family:Arial, Helvetica, sans-serif;font-size:12px;line-height:18px; border: 0px solid black; margin: 0px auto;background-color:white">
	<br>
	<table width=100%>
		<tr>
			<td><br>
				<div class="mainTitle"><strong><center>Photo Access ID - Search Log</center></strong></div>
			</td>
		</tr>
		<tr>
			<td>
				<center><br><br>
				<?php echo $checkConnect['error']; ?>
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