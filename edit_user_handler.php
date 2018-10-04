<?php
/*
 *******************************************************************************************************
*
* Name: edit_user_handler.php
* Edit user of this database - Note: all update for LDAP user will be erase when user login with LDAP
* Writer: Bach Nguyen (bnguye21@uncc.edu)
* Last Updated: 03/18/2015
*	03/18/2015
*		- Updated codes
*	03/01/2015
*		- Changed database class, session class
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
	$link = $db->get_link();
	$security_code = $SESSION_SECURITY;
	$session_lifetime = $SESSION_TIMEOUT;
	$session = new Zebra_Session($link, $security_code, $session_lifetime);
	//echo "Here session code: ".$security_code." - sectime: ".$session_lifetime;
	//print_r('<pre>');
	//print_r($session->get_settings());
	//print_r('</pre>');
	//exit();
	if (!isset($_SESSION['photo_access']['login']['authentication_type'])) {
		$reDirectLocation = "Location: ./index.php";
		header($reDirectLocation);
		exit();
	} //elseif (isset($_SESSION['photo_access']['login']['user_group']) and intval($_SESSION['photo_access']['login']['user_group']) > 2) {
	//	$warningMsg = "You don't have permission to create/edit User";
	//	$reDirectLocation = "Location: ./index.php?warningMsg=".$warningMsg;
	//	header($reDirectLocation);
	//	exit();
	//}

	$authentication_type =	$_SESSION['photo_access']['login']['authentication_type'];
	$allow_standard_access = $_SESSION['photo_access']['login']['allow_standard_access'];
	$self_approved = $_SESSION['photo_access']['login']['self_approved'];
	$default_group = intval($_SESSION['photo_access']['login']['default_group']);
	$store_password = $_SESSION['photo_access']['login']['store_password'];
	$policy_renew = $_SESSION['photo_access']['login']['policy_renew'];
	//echo "self approved: ".$self_approved." - policy renew: ".$policy_renew."<br>";
	
	if (isset($_POST["user_id"])) {
		$id = trim($_POST["id"]);
		//$user_group_id = trim($_POST["user_group_id"]);
		if (trim($_POST["user_group_id"]) == "") {
			if ($action == "Submit") {
				if ($self_approved == "Y") {
					$user_group_id = $default_group;
				} else {
					$user_group_id = 6;
				}
			}
		} else {
			$user_group_id = trim($_POST["user_group_id"]);
		}
		//echo "id: ".$id."<br>";
		$user_id = trim($_POST["user_id"]);
		$password = trim($_POST["password"]);
		$verify_password = trim($_POST["verify_password"]);
		if (!empty(trim($_POST["old_password"])))
			$old_password = trim($_POST["old_password"]);
		else
			$old_password = null;
		$first_name = trim($_POST["first_name"]);
		if (trim($_POST["middle_name"]))
			$middle_name = trim($_POST["middle_name"]);
		else
			$middle_name = null;
		$last_name = trim($_POST["last_name"]);
		if (trim($_POST["address_1"]))
			$address_1 = trim($_POST["address_1"]);
		else
			$address_1 = null;
		if (trim($_POST["address_2"]))
			$address_2 = trim($_POST["address_2"]);
		else
			$address_2 = null;
		if (trim($_POST["city"]))
			$city = trim($_POST["city"]);
		else
			$city = null;
		if (trim($_POST["state_province"]))
			$province = trim($_POST["state_province"]);
		else
			$province = null;
		if (trim($_POST["state"]))
			$state = trim($_POST["state"]);
		else
			$state = null;
		if (empty($province))
			if (!empty($state))
				$state_province = $state;
			else
				$state_province = null;
		else
			$state_province = $province;
		if (trim($_POST["postal_code"]))
			$postal_code = trim($_POST["postal_code"]);
		else
			$postal_code = null;
		$country_id = trim($_POST["country_id"]);
		$email = trim($_POST["email"]);
		if (trim($_POST["phone"]))
			$phone = trim($_POST["phone"]);
		else
			$phone = null;
		$get_policy_acceptance_date = trim($_POST["policy_acceptance_date"]);
		if (!empty ($get_policy_acceptance_date))
			$policy_acceptance_date = date("Y-m-d", strtotime($get_policy_acceptance_date));
		else
			$policy_acceptance_date = null;
		//$department = trim($_POST["department"]);
		//$department_backup = trim($_POST["department_backup"]);
		if (!empty(trim($_POST["department"])))
			$department = trim($_POST["department"]);
		else
			$department = null;
		if (!empty(trim($_POST["department_backup"])))	
			$department_backup = trim($_POST["department_backup"]);
		else	
			$department_backup = null;
		$authentication_method = trim($_POST["authentication_method"]);
		if (isset($_POST["active"]) and trim($_POST["active"]) == "Y") {
			$active = "Y";
		} else {
			$active = "N";
		}
		//echo "active: ".$active."<br>";
		$action =  trim($_POST["action"]);
	
		if ($action == "") {
			if (!empty($_POST["update"])) {
				$action = $_POST["update"];
			} elseif (!empty($_POST["delete"])) {
				$action = $_POST["delete"];
			} elseif (!empty($_POST["submit"])) {
				$action = $_POST["submit"];
			} elseif (!empty($_POST["photo_identification"])) {
				$action = $_POST["photo_identification"];
			} 
		}

		$dataQuery = array('user_group_id'=>$user_group_id,
						'user_id'=>$user_id,
						'password'=>$password,
						'first_name'=>$first_name,
						'middle_name'=>$middle_name,
						'last_name'=>$last_name,
						'address_1'=>$address_1,
						'address_2'=>$address_2,
						'city'=>$city,
						'state_province'=>$state_province,
						'postal_code'=>$postal_code,
						'country_id'=>$country_id,
						'email'=>$email,	
						'phone'=>$phone,
						'policy_acceptance_date'=>$policy_acceptance_date,
						'department'=>$department,
						'department_backup'=>$department_backup,
						'authentication_method'=>$authentication_method,
						'active'=>$active,
						'action'=>$action);
		//echo "<pre>";
		//	print_r($dataQuery);
		//echo "</pre>";
		//exit();
		
		$number_per_page = $_POST["number_per_page"];
		$time_zone = $_POST["time_zone"];
		$date_format = $_POST["date_format"];
		$time_format = $_POST["time_format"];
		$profileQuery = array ('number_per_page'=>$number_per_page,
							'time_zone'=>$time_zone,
							'date_format'=>$date_format,
							'time_format'=>$time_format);
		$completeArray = array_merge($dataQuery, $profileQuery);
		unset($completeArray['password']);
		//echo "timezone: ".$time_zone."<br>";	
		date_default_timezone_set($time_zone);
		
		if ($authentication_type == "LDAP") {
			$checkQuery = $db->query('
				SELECT
					*
				FROM
					photo_access_config
				WHERE
					left(`key`, 5) = ?
				ORDER BY
					`key`
				', array('ldap_'));	
			if ($checkQuery) {
				$arrayConfig = $db->fetch_assoc_all();
	
				$options = array();
				$ldapOptions = array();
				foreach ($arrayConfig as $row=>$arrayValue) {
					foreach ($arrayValue as $key=>$value) {
						//echo $key. " = " .$value."<br>";
						if ($key != "id") {
							if ($key == "key") {
								$key_name = substr($value,5);
							}
							if ($key == "value") {
								$real_value = trim($value);
							} else $real_value = "";
							$options[$key_name] = $real_value;
						}
					}
				}
				$ldapOptions = $options;
				unset($ldapOptions['dept_access']);
				unset($ldapOptions['member_of']);
				unset($ldapOptions['store_password']);
				unset($ldapOptions['allow_standard_access']);
				unset($ldapOptions['authentication_type']);
				unset($ldapOptions['default_group']);
				unset($ldapOptions['self_approved']);
				unset($ldapOptions['policy_renew']);
				unset($ldapOptions['policy_period']);
				unset($ldapOptions['default_policy']);	
				unset($ldapOptions['display_warning_msg']);	
				$domainArray = explode(",",$ldapOptions['domain_controllers']);
				$ldapOptions['domain_controllers'] = $domainArray;
				//echo "<pre>";
				//	print_r($ldapOptions);
				//echo "</pre>";
				//echo dirname(__FILE__);
				include ("./lib/adLDAP/adLDAP.php");
				$adldap = new adLDAP($ldapOptions);
				$connect = $adldap->connect();
		
				if ($connect['status'] == false) {
					$errorMsg = $connect["error"];
					echo "error Msg: ".$errorMsg."<br>";
					$completeArray['error'] = $errorMsg;
					//unset($completeArray['password']);
					header("location: ./edit_user.php?".http_build_query($completeArray));
					exit();
				}
			} else {
				$errorMsg = "Error! Couldn't load LDAP Profile. Please contact Application Admin!";
				echo "error Msg: ".$errorMsg."<br>";
				$completeArray['error'] = $errorMsg;
				//unset($completeArray['password']);
				header("location: ./edit_user.php?".http_build_query($dataQuery));
				exit();		
			}
		}
		//echo "here";
		if ($action=="Submit") {
			if ($authentication_type == "LDAP") {
				$existUser = $adldap->user()->infoCollection($user_id);
				if (!empty($existUser) and $authentication_method == "Database") {
					//echo $existUser->samaccountname;
					$errorMsg = "Error! \"User ID\" is existing in Active Directory.  Try another User ID.";
					$completeArray['error'] = $errorMsg;
					header("location: ./edit_user.php?".http_build_query($completeArray));
					exit();
				}
				if (empty($existUser) and $authentication_method == "LDAP") {
					//echo $existUser->samaccountname;
					$errorMsg = "Error! \"User ID\" is not found in Active Directory.  Try enter new User ID.";
					$completeArray['error'] = $errorMsg;
					header("location: ./edit_user.php?".http_build_query($completeArray));
					exit();
				}
			}
			$CheckExistStatus = $db->select('user_id','photo_access_users','user_id = ?', array($user_id));
			if ($CheckExistStatus) {
				$userArray = $db->fetch_assoc_all();
				if(!empty($userArray)) {
					$errorMsg = "Error: User ID is existing in user table.  Please enter new user id.";
					//echo "errorMsg: ".$errorMsg."<br>";
					$completeArray['error'] = $errorMsg;
					header("location: ./edit_user.php?".http_build_query($completeArray));
					exit();
				}
			}
			$CheckExistEmailStatus = $db->select('email','photo_access_users','email = ?', array($email));
			if ($CheckExistEmailStatus) {
				$userEmailArray = $db->fetch_assoc_all();
				if(!empty($userEmailArray)) {
					$errorMsg = "Error: email is existing in user table.  Please enter new email.";
					//echo "errorMsg: ".$errorMsg."<br>";
					$completeArray['error'] = $errorMsg;
					header("location: ./edit_user.php?".http_build_query($completeArray));
					exit();
				}
			}
		}
		
		if ($action=="Update") {
			if ($authentication_type == "LDAP") {
				$existUser = $adldap->user()->infoCollection($user_id);
				if (!empty($existUser) and $authentication_method == "Database") {
					//echo $existUser->samaccountname;
					$errorMsg = "Error! \"User ID\" is existing in Active Directory.  Try another User ID.";
					$completeArray['error'] = $errorMsg;
					header("location: ./edit_user.php?".http_build_query($completeArray));
					exit();
				}
			}	
		}	
	
		if ($action != "Delete") {	
			/*
			if (empty($authentication_method)) {
			$errorMsg = "Error! Please select 'Authentication Method' before clicking Submit/Update";
			$completeArray['error'] = $errorMsg;
			header("location: ./edit_user.php?".http_build_query($completeArray));
			exit();
			} */
			if (empty($user_id)) {
				$errorMsg = "Error! Please enter 'User ID' before clicking Submit/Update";
				if ($action == "Update") {
					$completeArray['id'] = $id;
				}
				$completeArray['error'] = $errorMsg;
				header("location: ./edit_user.php?".http_build_query($completeArray));
				exit();
			} 
			//if (strlen($user_id) < 5) {
			//	$errorMsg = "Error! \"User ID\" needs at least 5 characters";
			//	if ($action == "Update") {
			//		$completeArray['id'] = $id;
			//	}
			//	$completeArray['error'] = $errorMsg;
			//	header("location: ./edit_user.php?".http_build_query($completeArray));
			//	exit();
			//} 
			if (!ctype_alnum($user_id)) {
				$errorMsg = "Error! Please enter only letter and numeric characters in the \"User ID\" field";
				if ($action == "Update") {
					$completeArray['id'] = $id;
				}
				$completeArray['error'] = $errorMsg;
				header("location: ./edit_user.php?".http_build_query($completeArray));
				exit();
			} 
			// For Standard user
			if ($authentication_method == "Database" and empty($password) and $action=="Submit") {
				$errorMsg = "Error! Please enter 'Password' before clicking Submit/Update";
				if ($action == "Update") {
					$completeArray['id'] = $id;
				}
				$completeArray['error'] = $errorMsg;
				header("location: ./edit_user.php?".http_build_query($completeArray));
				exit();
			}
			if ($authentication_method == "Database" and (!empty($password) and strlen($password) < 5)) {
				$errorMsg = "Error! 'Password' needs at least 5 characters";
				if ($action == "Update") {
					$completeArray['id'] = $id;
				}
				$completeArray['error'] = $errorMsg;
				header("location: ./edit_user.php?".http_build_query($completeArray));
				exit();
			}
			if ($authentication_method == "Database" and empty($password) and $action=="Update" and empty($old_password)) {
				$errorMsg = "Error! Please enter 'Password' before clicking Update";
				if ($action == "Update") {
					$completeArray['id'] = $id;
				}
				$completeArray['error'] = $errorMsg;
				//unset($completeArray['password']);
				header("location: ./edit_user.php?".http_build_query($completeArray));
				exit();
			}
			if ($authentication_method == "Database" and (!empty($password) and !ctype_alnum($password))) {
				$errorMsg = "Error! Please enter only letter and numeric characters in the 'Password' field";
				if ($action == "Update") {
					$completeArray['id'] = $id;
				}
				$completeArray['error'] = $errorMsg;
				header("location: ./edit_user.php?".http_build_query($completeArray));
				exit();
			}
			if ($authentication_method == "Database" and empty($verify_password) and (!empty($password))) {
				$errorMsg = "Error! Please enter 'Verify Password' before clicking Submit/Update";
				if ($action == "Update") {
					$completeArray['id'] = $id;
				}
				$completeArray['error'] = $errorMsg;
				header("location: ./edit_user.php?".http_build_query($completeArray));
				exit();
			}
			if ($password != $verify_password and (!empty($password) or !empty($verify_password))) {
				if ($action == "Update") {
					$completeArray['id'] = $id;
				}
				$errorMsg = "Error! The two passwords are not the same";
				$completeArray['error'] = $errorMsg;
				header("location: ./edit_user.php?".http_build_query($completeArray));
				exit();
			}
			if (empty($first_name)) {
				$errorMsg = "Error! Please enter 'First Name' before clicking Submit/Update";
				if ($action == "Update") {
					$completeArray['id'] = $id;
				}
				$completeArray['error'] = $errorMsg;
				header("location: ./edit_user.php?".http_build_query($completeArray));
				exit();
			}
			if (empty($last_name)) {
				$errorMsg = "Error! Please enter 'Last Name' before clicking Submit/Update";
				if ($action == "Update") {
					$completeArray['id'] = $id;
				}
				$completeArray['error'] = $errorMsg;
				header("location: ./edit_user.php?".http_build_query($completeArray));
				exit();
			}
			if (empty($country_id) and $authentication_method == "Database") {
				$errorMsg = "Error! Please select 'Country' before clicking Submit/Update";
				if ($action == "Update") {
					$completeArray['id'] = $id;
				}
				$completeArray['error'] = $errorMsg;
				header("location: ./edit_user.php?".http_build_query($completeArray));
				exit();
			}
			if (empty($address_1) and $authentication_method == "Database") {
				$errorMsg = "Error! Please enter 'Address 1' before clicking Submit/Update";
				if ($action == "Update") {
					$completeArray['id'] = $id;
				}
				$completeArray['error'] = $errorMsg;
				header("location: ./edit_user.php?".http_build_query($completeArray));
				exit();
			}
			if (empty($city) and $authentication_method == "Database") {
				$errorMsg = "Error! Please enter 'City' before clicking Submit/Update";
				if ($action == "Update") {
					$completeArray['id'] = $id;
				}
				$completeArray['error'] = $errorMsg;
				header("location: ./edit_user.php?".http_build_query($completeArray));
				exit();
			}
			if (empty($state_province) and $country_id == 188 and $authentication_method == "Database") {
				$errorMsg = "Error! Please select 'State' before clicking Submit/Update";
				if ($action == "Update") {
					$completeArray['id'] = $id;
				}
				$completeArray['error'] = $errorMsg;
				header("location: ./edit_user.php?".http_build_query($completeArray));
				exit();
			}		
			if (empty($email)) {
				$errorMsg = "Error! Please enter 'Email' before clicking Submit/Update";
				if ($action == "Update") {
					$completeArray['id'] = $id;
				}
				$completeArray['error'] = $errorMsg;
				header("location: ./edit_user.php?".http_build_query($completeArray));
				exit();
			}
			if (empty($phone) and $authentication_method == "Database") {
				$errorMsg = "Error! Please enter 'Phone' before clicking Submit/Update";
				if ($action == "Update") {
					$completeArray['id'] = $id;
				}
				$completeArray['error'] = $errorMsg;
				header("location: ./edit_user.php?".http_build_query($completeArray));
				exit();
			}
			if (empty($user_group_id)) {
				$errorMsg = "Error! Please select 'Group' before clicking Submit/Update";
				if ($action == "Update") {
					$completeArray['id'] = $id;
				}
				$completeArray['error'] = $errorMsg;
				header("location: ./edit_user.php?".http_build_query($completeArray));
				exit();
			}
		}	

		function WriteToLoginLog ($db, $sessionArray) {
			global $remoteIP, $sessionID;
			$timeNow = date("Y-m-d H:i:s", strtotime("now"));
			//echo "time: ".$timeNow;
			//exit();
			$checkQuery = $db->insert_update('photo_access_user_login_log',
				array(
					'login_id'   =>  '',
					'login_name'   =>  $sessionArray['username'],
					'login_first_name'   =>  $sessionArray['first_name'],
					'login_last_name'   =>  $sessionArray['last_name'],
					'login_user_dept'   =>  $sessionArray['department'],
					'login_user_email'   =>  $sessionArray['email'],
					'date_time_in'   =>  date("Y-m-d H:i:s", strtotime("now")),
					'date_time_out'   =>  '',
					'internet_address'   =>  $remoteIP,
					'session_id'   =>  $sessionID
				)
			);
			if ($checkQuery) {
				$_SESSION['photo_access']['login']['login_id'] = $db->insert_id();
				return true;
			} else {
				return false;
			}
		}
		
		function SetSession ($db, $sessionArray, $isRenewPolicy) {
			global $remoteIP, $sessionID;
			$writeLogStatus = false;
		
			$_SESSION['photo_access']['login']['user_group'] = $sessionArray['user_group_id'];
			$_SESSION['photo_access']['login']['user_id'] = $sessionArray['user_id'];
			if ($isRenewPolicy == "N") {
				$_SESSION['photo_access']['login']['username'] = $sessionArray['username'];
				$writeLogStatus = WriteToLoginLog ($db, $sessionArray);
			}	
			$_SESSION['photo_access']['login']['first_name'] = $sessionArray['first_name'];	
			$_SESSION['photo_access']['login']['last_name'] = $sessionArray['last_name'];
			$_SESSION['photo_access']['login']['user_email'] = $sessionArray['email'];
			$_SESSION['photo_access']['login']['department'] = $sessionArray['department'];	
			$_SESSION['photo_access']['login']['department_backup'] = $sessionArray['department_backup'];
			$_SESSION['photo_access']['login']['number_per_page'] = $sessionArray['number_per_page'];
			$_SESSION['photo_access']['login']['time_zone'] = $sessionArray['time_zone'];
			$_SESSION['photo_access']['login']['date_format'] = $sessionArray['date_format'];	
			$_SESSION['photo_access']['login']['time_format'] = $sessionArray['time_format'];	
			$_SESSION['photo_access']['login']['remote_ip'] = $remoteIP;	
			$_SESSION['photo_access']['login']['session_id'] = $sessionID;	
			if ($isRenewPolicy == "N")
				if ($writeLogStatus)
					return true;
				else
					return false;
			else
				return true;
		}
		
		$createdDate = date("Y-m-d H:i:s", strtotime("now"));
		if (!empty($_SESSION['photo_access']['login']['username']))
			$createdBy = $_SESSION['photo_access']['login']['username'];
		else
			$createdBy = $user_id;
	
		if ($action == "Submit") {
			$insertQuery = $dataQuery;
			if ($self_approved == "Y") {
				$insertQuery['active'] = "Y";	
			}
			if ($authentication_method == "LDAP") {
				$existUser = $adldap->user()->infoCollection($user_id, array("*"));
				$insertQuery['first_name'] = $existUser->givenName;
				$insertQuery['middle_name'] = $existUser->initials;
				$insertQuery['last_name'] = $existUser->sn;
				$insertQuery['email'] = $existUser->mail;
				$insertQuery['phone'] = $existUser->telephoneNumber;
				$insertQuery['department'] = $existUser->physicaldeliveryofficename;
			}
			if ($authentication_type == "LDAP" and $self_approved == "Y" and $authentication_method == "LDAP") {
				$reDirectLocation = "Location: ./web/login.php?warningMsg=Please login here to access!";
				header($reDirectLocation);
				exit();
			}
						
			unset($insertQuery['action']);
		
			if ($authentication_method == "LDAP") {
				if ($store_password == "Y")
					$insertQuery['password'] = $DATABASE_PASSWORD_FUNCTION($password);
				else
					$insertQuery['password'] = null;
			} else {
				$insertQuery['password'] = $DATABASE_PASSWORD_FUNCTION($password);
			}	
			$insertQuery['created_by'] = $createdBy;
			$insertQuery['created_date'] = $createdDate;
			$insertQuery['updated_by'] = $createdBy;
			$insertQuery['updated_date'] = $createdDate;
			//$queryString = "INSERT INTO photo_access_users ($insertColumnList) VALUES ($insertString)";
			
			//echo "<pre>";
			//	print_r($insertQuery);
			//	print_r($profileQuery);
			//echo "</pre>";
			//exit();
			$db->transaction_start();
				$db->insert('photo_access_users', $insertQuery);
				$insertID = $db->insert_id();
				$profileQuery['photo_access_user_id'] = $insertID;
				$db->insert('photo_access_users_profile', $profileQuery);
			$insertStatus = $db->transaction_complete();	
			if ($insertStatus) {
				require './classes/remoteaddress.php';
				$remoteAddress = new RemoteAddress;
				$remoteIP = $remoteAddress->getIPAddress();
				
				$sessionID = session_id();
					
				$sessionArray = array();
				$sessionArray['user_group_id'] = $user_group_id;
				$sessionArray['user_id'] = $insertID;
				$sessionArray['username'] = $insertQuery['user_id'];
				$sessionArray['first_name'] = $insertQuery['first_name'];	
				$sessionArray['last_name'] = $insertQuery['last_name'];	
				$sessionArray['email'] = $insertQuery['email'];
				$sessionArray['department'] = $insertQuery['department'];
				$sessionArray['department_backup'] = $insertQuery['department_backup'];
				$sessionArray['number_per_page'] = $profileQuery['number_per_page'];
				$sessionArray['time_zone'] = $profileQuery['time_zone'];
				$sessionArray['date_format'] = $profileQuery ['date_format'];	
				$sessionArray['time_format'] = $profileQuery ['time_format'];	
				
				if (empty($_SESSION['photo_access']['login']['username'])) {
					if ($self_approved == "Y") {
						if (SetSession ($db, $sessionArray, "N")) {
							$errorMsg = "Success added this record to user table. ID=".$insertID;
							$successDataQuery = array();
							$successDataQuery['error'] = $errorMsg;
							$successDataQuery['id'] = $insertID;
							$successDataQuery['action'] = "Update";
							header("location: ./edit_user.php?".http_build_query($successDataQuery));
							exit();				
						} else {
							$errorMsg = "Error: Couldn't set session.  Please contact System Admin!";
							echo "errorMsg: ".$errorMsg."<br>";
							$completeArray['error'] = $errorMsg;
							header("location: ./edit_user.php?".http_build_query($completeArray));
							exit();
						}
					} else {
						header("location: ./thank_you.php");
						exit();	
					}
				} else {
					$errorMsg = "Success added this record to user table. ID=".$insertID;
					$successDataQuery = array();
					$successDataQuery['error'] = $errorMsg;
					$successDataQuery['id'] = $insertID;
					$successDataQuery['action'] = "Update";
					header("location: ./edit_user.php?".http_build_query($successDataQuery));
					exit();				
				} 	
			} else {
				$errorMsg = "Error: Couldn't insert this user into database";
				echo "errorMsg: ".$errorMsg."<br>";
				$completeArray['error'] = $errorMsg;
				header("location: ./edit_user.php?".http_build_query($completeArray));
				exit();
			}
		} elseif ($action == "Update") {
			$updateQuery = $dataQuery;

			unset($updateQuery['action']);
		
			//$password = sha1($passwordString);
			if (empty($password)) {
				//$queryString = "SELECT * FROM photo_access_users where active='Y' and id=".$id;
				//$columReturn = "password";
				$old_password = $db->dlookup('password', 'photo_access_users', 'id = ?', array($id));
				$updateQuery['password'] = $old_password;
			} else {
				$updateQuery['password'] = $DATABASE_PASSWORD_FUNCTION($password);
			}
			//echo "old password :".$old_password;
			//exit();
			$updateQuery['updated_by'] = $createdBy;
			$updateQuery['updated_date'] = $createdDate;
			$db->transaction_start();
				$updatetatus = $db->update('photo_access_users', $updateQuery, 'id = ?', array($id));
				$db->update('photo_access_users_profile', $profileQuery, 'photo_access_user_id = ?', array($id));
			$updateStatus = $db->transaction_complete();	
				
			if ($updateStatus) {
				$errorMsg = "Success update this record to user table";
				$successDataQuery = array();
				$successDataQuery['error'] = $errorMsg;
				$successDataQuery['id'] = $id;
				$successDataQuery['action'] = "Update";
				header("location: ./edit_user.php?".http_build_query($successDataQuery));
				exit();
			} else {
				$errorMsg = "Error: Couldn't update this user into database";
				//echo "errorMsg: ".$errorMsg."<br>";
				$completeArray['error'] = $errorMsg;
				//echo "<pre>";
				//	print_r($dataQuery);
				//echo "</pre>";
				header("location: ./edit_user.php?".http_build_query($completeArray));
				exit();
			}
		} elseif ($action == "Delete") {
			//$queryDeleteString = "DELETE FROM photo_access_users WHERE id=".$id;
			$deleteStatus = $db->delete('photo_access_users', 'id = ?', array($id));
			if ($deleteStatus) {
				$errorMsg = "Success deleted user ID=".$id;
				//echo "errorMsg: ".$errorMsg."<br>";
				//$dataQuery['warningMsg'] = $errorMsg;
				header("location: ./admin/user_management.php?warningMsg=".$errorMsg);
				exit();
			} else {
				$dataQuery['id'] = $id;
				$errorMsg = "couldn't delete this user";
				//echo "errorMsg: ".$errorMsg."<br>";
				$dataQuery['action'] = "Update";
				$dataQuery['error'] = $errorMsg;
				header("location: ./edit_user.php?".http_build_query($dataQuery));
				exit();
			}		
		} elseif ($action == "Photo Identification") {
			$updateQuery = $dataQuery;

			unset($updateQuery['action']);
		
			if (empty($password)) {
				//$queryString = "SELECT * FROM photo_access_users where active='Y' and id=".$id;
				//$columReturn = "password";
				$old_password = $db->dlookup('password', 'photo_access_users', 'id = ?', array($id));
				$updateQuery['password'] = $old_password[0];
			} else {
				$updateQuery['password'] = $DATABASE_PASSWORD_FUNCTION($password);
			}
		
			$updateQuery['updated_by'] = $createdBy;
			$updateQuery['updated_date'] = $createdDate;
		
			$photoQuery = array();
			$photoQuery['photo_access_user_id'] = $id;
			$photoQuery['checker_name'] = $createdBy;
			$photoQuery['checked_date'] = $createdDate;

			$db->transaction_start();
				$db->update('photo_access_users', $updateQuery, 'id = ?', array($id));	
				$db->insert('photo_access_identification', $photoQuery);
			$identifyStatus = $db->transaction_complete();
			if ($identifyStatus) {
				$errorMsg = "Success Updated this user into User table and Identification table";
				//echo "errorMsg: ".$errorMsg."<br>";
				//$dataQuery['warningMsg'] = $errorMsg;
				header("location: ./edit_user.php?warningMsg=".$errorMsg);
				exit();
			} else {
				$dataQuery['id'] = $id;
				$errorMsg = "couldn't update this user in both tables";
				//echo "errorMsg: ".$errorMsg."<br>";
				$dataQuery['action'] = "Update";
				$dataQuery['error'] = $errorMsg;
				header("location: ./edit_user.php?".http_build_query($dataQuery));
				exit();
			}
		}
	}
} else {
?>
	<?php include ("./web/header.html");?>
	<?php include ("./web/menu.html"); ?>
	<link href="./css/mainstyle.css" rel="stylesheet" type="text/css">
	<div style="width:80%; font-family:Arial, Helvetica, sans-serif;font-size:12px;line-height:18px; border: 0px solid black; margin: 0px auto;background-color:white">
		<br>
		<table  width=100% style="padding:5px; background-color:#EFEFEF;">
			<tr>
				<td colspan="2"><br><br><center>
					<div class="mainSmallTitle"><strong>Photo Access ID - Edit User</strong></div></center>
				</td>
			</tr>
			<tr>
				<td colspan="2"><br><center>
					<div style="font-family:Arial, Helvetica, sans-serif;font-size:12px;color:red">
						<?php
						echo $checkConnect['error']; 
						?>
					</div></center><br><br>
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