<?php 
/*
 *******************************************************************************************************
*
* Name: edit_user.php
* Edit user of this database - Note: all update for LDAP user will be erase when user login with LDAP
* Writer: Bach Nguyen (bnguye21@uncc.edu)
* Last Updated: 03/09/2015
*	03/09/2015
*		- Fixed bugs: compare current user group ID with the edit' user group ID
*	01/12/2015
*		- Changed database class, session class
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
	
	if (empty($_SESSION['photo_access']['login']['database_name'])) {
		$reDirectLocation = "Location: ./";
		//echo "reDirection Location: ".$reDirectLocation."<br>";
		header($reDirectLocation);
		exit();
	}
	$error = "";
	$policy_renew = $_SESSION['photo_access']['login']['policy_renew'];	
	$allow_standard_access = $_SESSION['photo_access']['login']['allow_standard_access'];
	$authentication_type = $_SESSION['photo_access']['login']['authentication_type'];
	if (empty($authentication_type))
		$error = "You can't directly access to this page.  Please try access in login page.";
	
	if (isset($_SESSION['photo_access']['login']['username'])) {
		$username = $_SESSION['photo_access']['login']['username'];
		$userGroup = $_SESSION['photo_access']['login']['user_group'];
		$department = $_SESSION['photo_access']['login']['department'];
		$first_name	= $_SESSION['photo_access']['login']['first_name'];
		$last_name = $_SESSION['photo_access']['login']['last_name'];
		$login_id = $_SESSION['photo_access']['login']['login_id'];
	}
	/*
	if (isset($_SESSION['photo_access']['login']['user_group']) and intval($_SESSION['photo_access']['login']['user_group'] > 5)) {
		if (isset($_GET["id"])) {
			$page_link = "edit_user.php?action=Update&id=".$_GET["id"];
		} else {
			$page_link = "edit_user.php?action=Submit";
		}
		$reDirectLocation = "Location: ./../web/login.php?page_link=".urlencode($page_link);
		//echo "1.reDirection Location: '".$reDirectLocation."'<br>";
		header($reDirectLocation);
		exit();
	} 
	*/
	if (!empty($_GET["id"])) {
		$id = $_GET["id"];
	} else $id = "";
	if (!empty($_GET["user_id"])) {
		$user_id = $_GET["user_id"];
	} else $user_id = "";
	if (!empty($_GET["password"])) {
		$password = $_GET["password"];
	} else {
		$password = "";
		$old_password = "";
	}
	if (!empty($_GET["first_name"])) {
		$first_name = $_GET["first_name"];
	} else $first_name = "";
	if (!empty($_GET["middle_name"])) {
		$middle_name = $_GET["middle_name"];
	} else $middle_name = "";
	if (!empty($_GET["last_name"])) {
		$last_name = $_GET["last_name"];
	} else $last_name = "";
	if (!empty($_GET["address_1"])) {
		$address_1 = $_GET["address_1"];
	} else $adress_1 = "";
	if (!empty($_GET["address_2"])) {
		$address_2 = $_GET["address_2"];
	} else $adress_2 = "";
	if (!empty($_GET["city"])) {
		$city = $_GET["city"];
	} else $city = "";
	if (!empty($_GET["state_province"])) {
		$state_province = $_GET["state_province"];
	} else $state_province = "";
	if (!empty($_GET["postal_code"])) {
		$postal_code = $_GET["postal_code"];
	} else $postal_code = "";		
	if (!empty($_GET["country_id"])) {
		$country_id = $_GET["country_id"];
	} else $country_id = "";		
	if (!empty($_GET["email"])) {
		$email = $_GET["email"];
	} else $email = "";
	if (!empty($_GET["phone"])) {
		$phone = $_GET["phone"];
	} else $phone = "";
	if (!empty($_GET["policy_acceptance_date"])) {
		$policy_acceptance_date = $_GET["policy_acceptance_date"];
	} else $policy_acceptance_date = "";		
	if (!empty($_GET["department"])) {
		$department = $_GET["department"];
	} else $department = "";
	if (!empty($_GET["department_backup"])) {
		$department_backup = $_GET["department_backup"];
	} else $department_backup = "";	
	//if (!empty($_GET["user_group_id"])) {
	//	$user_group_id = $_GET["user_group_id"];
	//} else $user_group_id = "";
	if (!empty($_GET['authentication_method'])) {
		$authentication_method = trim($_GET["authentication_method"]);
	} else $authentication_method = $authentication_type;
	if (!empty($_GET['active'])) {
		$active = trim($_GET["active"]);
	} else $active = "";
	if (!empty($_GET['action'])) {
		$action = trim($_GET["action"]);
	} else {
		$action = "";
	}
	$user_group_id = "";
	if (!empty($_GET["user_group_id"])) {
		$user_group_id = $_GET["user_group_id"];
	} elseif ($action == "Submit") {
		if ($_SESSION['photo_access']['login']['self_approved'] == "N") {
			$user_group_id = 6;
		} else {
			$user_group_id = $_SESSION['photo_access']['login']['default_group'];
		}
	}
	
	if (!empty($_GET['number_per_page'])) {
		$number_per_page = trim($_GET["number_per_page"]);
	} else {
		$number_per_page = $NUMBER_PER_PAGE;
	}
	if (!empty($_GET['time_zone'])) {
		$time_zone = trim($_GET["time_zone"]);
	} else {
		$time_zone = $TIME_ZONE;
	}
	if (!empty($_GET['date_format'])) {
		$date_format = trim($_GET["date_format"]);
	} else {
		$date_format = $DATE_FORMAT;
	}
	if (!empty($_GET['time_format'])) {
		$time_format = trim($_GET["time_format"]);
	} else {
		$time_format = $TIME_FORMAT;
	}
	
	$datetime_format = $date_format." ".$time_format;
	date_default_timezone_set($time_zone);
	
	if (!empty($_GET["error"])) {
		$errorMsg = $_GET["error"];
	} else $errorMsg = "";
	
	// Query to get country
	$queryCountryString = "select id, short_name from countries order by `short_name`";
	$queryCountryStatus = $db->query($queryCountryString);
	if ($queryCountryStatus !== false)
		$countryArray = $db->fetch_assoc_all();
	else
		if (empty($error))
			$error = "Couldn't load Country table into this page. Check with Application Admin!";
		else
			$error .= "<br>Couldn't load Country table into this page. Check with Application Admin!";
	// Query to get country - state
	$queryCountryStateString = "select id, state_name from country_state_province where country_id=188 order by `state_name`";
	$queryCountryStateStatus = $db->query($queryCountryStateString);
	if ($queryCountryStateStatus !== false)
		$countryStateArray = $db->fetch_assoc_all();
	else
		if (empty($error))
			$error = "Couldn't load US State table into this page. Check with Application Admin!";
		else
			$error .= "<br>Couldn't load US State table into this page. Check with Application Admin!";
	// Query to get user group
	$queryUserGroupString = "select * from photo_access_user_group";
	$queryUserGroupStatus = $db->query($queryUserGroupString);
	if ($queryUserGroupStatus !== false)
		$arrayUserGroup = $db->fetch_assoc_all();
	else
		if (empty($error))
			$error = "Couldn't load User Group table into this page. Check with Application Admin!";
		else
			$error .= "<br>Couldn't load User Group table into this page. Check with Application Admin!";
	
	if ($id != "" and ($errorMsg == "" or strstr($errorMsg, "Success"))) {	
		if (!isset($_SESSION['photo_access']['login']['username'])) {
			if (isset($_GET["id"])) {
				$page_link = "edit_user.php?action=Update&id=".$_GET["id"];
			} else {
				$page_link = "edit_user.php?action=Submit";
			}
			$reDirectLocation = "Location: ./../web/login.php?page_link=".urlencode($page_link);
			//echo "reDirection Location: '".$reDirectLocation."'<br>";
			header($reDirectLocation);
			exit();
		} 

		$queryUserString = "SELECT a.*, b.* FROM photo_access_users a left join photo_access_users_profile b on a.id = b.photo_access_user_id WHERE id=".$id;
		$queryUserStatus = $db->query($queryUserString);
		if ($queryUserStatus !== false) 
			$arrayUser = $db->fetch_assoc_all();
		else
			if (empty($error))
				$error = "Couldn't load User Info into this page. Check with Application Admin!";
			else
				$error .= "<br>Couldn't load User Info into this page. Check with Application Admin!";
		
		if (!empty($arrayUser)) {
			//echo "<pre>";
			//	echo print_r($arrayUser);
			//echo "</pre>";
			$user_group_id = $arrayUser[0]['user_group_id'];
			
			//echo "user group ID: ".$user_group_id."<br>";
			//echo "Session group ID: ".$_SESSION['photo_access']['login']['user_group']."<br>";
			if (intval($userGroup) > intval($user_group_id)) {
				$errorMsg = "Error: You don't have permission to edit a person who has more priority than you!";
				$errorQuery = array();
				$errorQuery['warningMsg'] = $errorMsg;
				header("location: ./admin/user_management.php?".http_build_query($errorQuery));
				exit();
			}	
			
			if ($arrayUser[0]['user_id'] != $username and intval($user_group_id) < intval($userGroup)) {
				$errorMsg = "Error: You don't have permission to edit another  person!";
				$errorQuery = array();
				$errorQuery['warningMsg'] = $errorMsg;
				header("location: ./admin/user_management.php?".http_build_query($errorQuery));
				exit();
			}	
						
			$user_id = $arrayUser[0]['user_id'];
			$old_password = $arrayUser[0]['password'];
			$password = ""; //$arrayUser[0]['password'];
			$verify_password = ""; //$password;
			$first_name = $arrayUser[0]['first_name'];
			$middle_name = $arrayUser[0]['middle_name'];
			$last_name = $arrayUser[0]['last_name'];
			$address_1 = $arrayUser[0]['address_1'];
			$address_2 = $arrayUser[0]['address_2'];
			$city = $arrayUser[0]['city'];
			$state_province = $arrayUser[0]['state_province'];
			$postal_code = $arrayUser[0]['postal_code'];
			$country_id = $arrayUser[0]['country_id'];
			$email = $arrayUser[0]['email'];
			$phone = $arrayUser[0]['phone'];

			if (!empty($arrayUser[0]['policy_acceptance_date']) and $arrayUser[0]['policy_acceptance_date'] != "0000-00-00") {
				$policy_acceptance_date = $arrayUser[0]['policy_acceptance_date'];
			} else {
				$policy_acceptance_date = "";
			}
			$department = $arrayUser[0]['department'];
			$department_backup = $arrayUser[0]['department_backup'];
			$authentication_method = $arrayUser[0]['authentication_method'];
			$active = $arrayUser[0]['active'];
			$created_by = $arrayUser[0]['created_by'];
			$created_date = $arrayUser[0]['created_date'];
			$updated_by = $arrayUser[0]['updated_by'];
			$updated_date = $arrayUser[0]['updated_date'];
			
			$number_per_page = $arrayUser[0]['number_per_page'];
			$time_zone = $arrayUser[0]['time_zone'];
			$date_format = $arrayUser[0]['date_format'];
			$time_format = $arrayUser[0]['time_format'];
			
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
			$createdBy = getName ($db, $created_by);
			$updatedBy = getName ($db, $updated_by);	
		} else {
			if (empty($error))
				$error = "Couldn't load User ID: ".$id." into this page. Check with Application Admin!";
			else
				$error .= "<br>Couldn't load User ID: ".$id." into this page. Check with Application Admin!";
		}
	} 
	if ($authentication_method == "LDAP") {
		$display_UI = "none";
	} else {
		$display_UI = "block";
	}
	//echo "authentication_method: ".$authentication_method."<br>";
	//echo "display_UI: ".$display_UI."<br>";
	include ("./web/header.html");
	if (isset($_SESSION['photo_access']['login']['username']))
		include ("./web/menu.html");
	if (empty($error)) {
?>
	<script Language="JavaScript">
		function trim(stringToTrim) {
			return stringToTrim.replace(/^\s+|\s+$/g,"");
		}
	
		function checkValidator(theForm) {
			if (theForm.action.value == "Update" || theForm.action.value == "Photo Identification") {
				var id = trim(document.getElementById('id').value);
			} else {
				var id = "";
			}
			var authentication_method = trim(document.getElementById('authentication_method').value);
			var first_name = trim(document.getElementById('first_name').value);
			var last_name = trim(document.getElementById('last_name').value);
			var address_1 = trim(document.getElementById('address_1').value);
			var city = trim(document.getElementById('city').value);
			var state_province = trim(document.getElementById('state_province').value);
			var state = trim(document.getElementById('state').value);
			var postal_code = trim(document.getElementById('postal_code').value);
			var email = trim(document.getElementById('email').value);	
			var phone = trim(document.getElementById('phone').value);
			var user_id = trim(document.getElementById('user_id').value);
			var password = trim(document.getElementById('password').value);
			var verify_password = trim(document.getElementById('verify_password').value);
			var old_password = trim(document.getElementById('old_password').value);
			var user_group_id = trim(document.getElementById('user_group_id').value);
			var checkOK = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";

			if (theForm.action.value != "Delete") {
				if (first_name == "") {
					//alert("Please enter 'First Name' before clicking Submit/Update");
					document.getElementById("errorMsg").innerHTML = "Please enter 'First Name' before clicking Submit/Update";
					theForm.first_name.style.backgroundColor="#D0E2ED";
					theForm.first_name.focus();
					return (false);
				}
				if (last_name == "") {
					//alert("Please enter 'Last Name' before clicking Submit/Update");
					document.getElementById("errorMsg").innerHTML = "Please enter 'Last Name' before clicking Submit/Update";
					theForm.last_name.style.backgroundColor="#D0E2ED";
					theForm.last_name.focus();
					return (false);
				}					
				if (theForm.country_id.selectedIndex == 0 && authentication_method == "Database") {
					//alert("Please select 'Country' before clicking Submit/Update");
					document.getElementById("errorMsg").innerHTML = "Please select 'Country' before clicking Submit/Update";
					theForm.country_id.style.backgroundColor="#D0E2ED";
					theForm.country_id.focus();
					return (false);
				}	
				if (address_1 == "" && authentication_method == "Database") {
					//alert("Please enter 'Address 1' before clicking Submit/Update");
					document.getElementById("errorMsg").innerHTML = "Please enter 'Address 1' before clicking Submit/Update";
					theForm.address_1.style.backgroundColor="#D0E2ED";
					theForm.address_1.focus();
					return (false);
				}
				if (city == "" && authentication_method == "Database") {
					//alert("Please enter 'city' before clicking Submit/Update");
					document.getElementById("errorMsg").innerHTML = "Please enter 'City' before clicking Submit/Update";
					theForm.city.style.backgroundColor="#D0E2ED";
					theForm.city.focus();
					return (false);
				}						
				if (theForm.country_id.selectedIndex == 188 && state == "" && authentication_method == "Database") {
					//alert("Please select 'state' before clicking Submit/Update");
					document.getElementById("errorMsg").innerHTML = "Please select 'State' before clicking Submit/Update";
					theForm.state.style.backgroundColor="#D0E2ED";
					theForm.state.focus();
					return (false);
				}
				if (theForm.country_id.selectedIndex == 188 && postal_code == "" && authentication_method == "Database") {
					//alert("Please enter 'Postal Code' before clicking Submit/Update");
					document.getElementById("errorMsg").innerHTML = "Please enter 'Postal Code' before clicking Submit/Update";
					theForm.postal_code.style.backgroundColor="#D0E2ED";
					theForm.postal_code.focus();
					return (false);
				}
				if (email == "") {
					//alert("Please enter 'Email' before clicking Submit/Update");
					document.getElementById("errorMsg").innerHTML = "Please enter 'Email' before clicking Submit/Update";
					theForm.email.style.backgroundColor="#D0E2ED";
					theForm.email.focus();
					return (false);
				}
				if (phone == "" && authentication_method == "Database") {
					//alert("Please enter 'Phone' before clicking Submit/Update");
					document.getElementById("errorMsg").innerHTML = "Please enter 'Phone' before clicking Submit/Update";
					theForm.phone.style.backgroundColor="#D0E2ED";
					theForm.phone.focus();
					return (false);
				}
				if (user_id == "") {
					//alert("Please enter 'User ID' before clicking Submit/Update");
					document.getElementById("errorMsg").innerHTML = "Please enter 'User ID' before clicking Submit/Update";
					theForm.user_id.style.backgroundColor="#D0E2ED";
					theForm.user_id.focus();
					return (false);
				}
				
				if (theForm.authentication_method.selectedIndex == 1 && user_id.length < 5) {
					alert("User ID needs at least 5 characters before clicking Submit/Update ");
					document.getElementById("errorMsg").innerHTML = "User ID needs at least 5 characters";
					theForm.user_id.style.backgroundColor="#D0E2ED";
					theForm.user_id.focus();
					return (false);
				}
				var allValid = true;
				for (i = 0;  i < user_id.length;  i++)	{
					ch = user_id.charAt(i);
					for (j = 0;  j < checkOK.length;  j++)
						if (ch == checkOK.charAt(j))
							break;
						if (j == checkOK.length)
						{
							allValid = false;
							break;
						}
				}
				if (!allValid) {
					document.getElementById("errorMsg").innerHTML = "Please enter only letter and numeric characters in the \"User ID\" field";
					theForm.user_id.style.backgroundColor="#D0E2ED";
					theForm.user_id.focus();
					return (false);
				}
				if (theForm.authentication_method.selectedIndex == 1 && password == "" && id == "") {
					//alert("Please enter 'Password' before clicking Submit/Update");
					document.getElementById("errorMsg").innerHTML = "Please enter 'Password' before clicking Submit/Update";
					theForm.password.style.backgroundColor="#D0E2ED";
					theForm.password.focus();
					return (false);
				}	
				if (authentication_method == "Database" && password == "" && old_password == "") {
					//alert("Please enter 'Password' before clicking Submit/Update");
					document.getElementById("errorMsg").innerHTML = "Please enter 'Password' before clicking Update";
					theForm.password.style.backgroundColor="#D0E2ED";
					theForm.password.focus();
					return (false);
				}				
				if (theForm.authentication_method.selectedIndex == 1 && password != "" && password.length < 5) {
					//alert("User ID needs at least 5 characters before clicking Submit/Update");
					document.getElementById("errorMsg").innerHTML = "Password needs at least 5 characters";
					theForm.password.style.backgroundColor="#D0E2ED";
					theForm.password.focus();
					return (false);
				}
				var allValid = true;
				for (i = 0;  i < password.length;  i++)	{
					ch = password.charAt(i);
					for (j = 0;  j < checkOK.length;  j++)
						if (ch == checkOK.charAt(j))
							break;
						if (j == checkOK.length)
						{
							allValid = false;
							break;
						}
				}
				if (!allValid) {
					document.getElementById("errorMsg").innerHTML = "Please enter only letter and numeric characters in the \"Password\" field";
					theForm.password.style.backgroundColor="#D0E2ED";
					theForm.password.focus();
					return (false);
				}					
				if (theForm.authentication_method.selectedIndex == 1 && password != "" && verify_password == "") {
					//alert("User ID needs at least 5 characters before clicking Submit/Update");
					document.getElementById("errorMsg").innerHTML = "Please enter 'Verify Password' before clicking Submit/Update";
					theForm.verify_password.style.backgroundColor="#D0E2ED";
					theForm.verify_password.focus();
					return (false);
				}
				if (password != "" && password != verify_password) {
					//alert("The two passwords ae not the same " + password + " verify: " + verify_password);
					document.getElementById("errorMsg").innerHTML = "The two passwords are not the same";
					theForm.verify_password.style.backgroundColor="#D0E2ED";
					theForm.verify_password.focus();
					return (false);
				}
				// End For Standard Database
				if (user_group_id == "") {
					//alert("Please select 'Group' before clicking Submit/Update");
					document.getElementById("errorMsg").innerHTML = "Please select 'Group' before clicking Submit/Update";
					theForm.user_group_id.style.backgroundColor="#D0E2ED";
					theForm.user_group_id.focus();
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
		
		function authenticationMethodDisplayRequiredText() {
			//alert("action: " + document.getElementById("action").value);
			if (document.getElementById("action").value == "Submit") {
				var div_Password_Label = document.getElementById("showOrHideDiv_Password_Label");
				var div_Verify_Password_Label = document.getElementById("showOrHideDiv_Verify_Password_Label");
				//alert ("New: " + div_Password_Label);
			} else if (document.getElementById("action").value == "Update") {
				var div_Change_Password_Label = document.getElementById("showOrHideDiv_Change_Password_Label");
				var div_Verify_Change_Password_Label = document.getElementById("showOrHideDiv_Verify_Change_Password_Label");
				//alert ("Update: " + div_Change_Password_Label);
			}
			var div_Password_Value = document.getElementById("showOrHideDiv_Password_Value");
			var div_Verify_Password_Value = document.getElementById("showOrHideDiv_Verify_Password_Value");
		
			if (document.getElementById('authentication_method').value == "Database") { 
				//alert ("Database");
				document.getElementById("required_country").innerHTML = "*";
				document.getElementById("required_address_1").innerHTML = "*";
				document.getElementById("required_city").innerHTML = "*";
				document.getElementById("required_state").innerHTML = "*";
				document.getElementById("required_postal").innerHTML = "*";
				document.getElementById("required_phone").innerHTML = "*";
				if (document.getElementById("action").value == "Submit") {
					document.getElementById("required_password").innerHTML = "*";
					document.getElementById("required_verify_password").innerHTML = "*";
				}
				//alert ("'" + document.getElementById("old_password").value + "'");
				if (document.getElementById("action").value == "Update" && document.getElementById("old_password").value == "") {
					document.getElementById("required_change_password").innerHTML = "*";
					document.getElementById("required_verify_change_password").innerHTML = "*";
				}
				//alert ("here");
				if (document.getElementById("action").value == "Submit") {
					//alert ("New 1");
					div_Password_Label.style.display = "block";
					div_Verify_Password_Label.style.display = "block";
				} else if (document.getElementById("action").value == "Update") {
					//alert ("Update 1");
					div_Change_Password_Label.style.display = "block";
					div_Verify_Change_Password_Label.style.display = "block";	
				}
				//alert ("here 1");
				if (div_Password_Value.style.display == "block") {
					div_Password_Value.style.display = "none";
					div_Verify_Password_Value.style.display = "none";
					//alert ("3 div_Password_Value: " + div_Password_Value.style.display);
				} else {
					div_Password_Value.style.display = "block";
					div_Verify_Password_Value.style.display = "block";
					//alert ("4 div_Password_Value: " + div_Password_Value.style.display);
				}									
			} else {
				//alert ("LDAP");
				document.getElementById("required_country").innerHTML = "&nbsp";
				document.getElementById("required_address_1").innerHTML = "&nbsp";
				document.getElementById("required_city").innerHTML = "&nbsp";
				document.getElementById("required_state").innerHTML = "&nbsp";
				document.getElementById("required_postal").innerHTML = "&nbsp";
				document.getElementById("required_phone").innerHTML = "&nbsp";
				if (document.getElementById("action").value == "Submit") {
					document.getElementById("required_password").innerHTML = "&nbsp";
					document.getElementById("required_verify_password").innerHTML = "&nbsp";
				}
				//alert ("here 2");
				if (document.getElementById("action").value == "Submit") {
					//alert ("New 2");
					div_Password_Label.style.display = "none";
					div_Verify_Password_Label.style.display = "none";
				} else if (document.getElementById("action").value == "Update") {
					//alert ("Update 2");
					div_Change_Password_Label.style.display = "none";
					div_Verify_Change_Password_Label.style.display = "none";	
				}
				//alert ("here 3");
				if (div_Password_Value.style.display == "none") {
					//alert ("5 div_Password_Value: " + div_Password_Value.style.display);
					div_Password_Value.style.display = "block";
					div_Verify_Password_Value.style.display = "block";
				} else {
					//alert ("6 div_Password_Value: " + div_Password_Value.style.display);
					div_Password_Value.style.display = "none";
					div_Verify_Password_Value.style.display = "none";
				}
			}
		}
		function displayRequiredText() {
			var div_Province_Label = document.getElementById("showOrHideDiv_Province_Label");
			var div_State_Label = document.getElementById("showOrHideDiv_State_Label");
			var div_Province_Value = document.getElementById("showOrHideDiv_Province_Value");
			var div_State_Value = document.getElementById("showOrHideDiv_State_Value");
			if (document.getElementById('country_id').value == 188) { 
				//alert ("<?php echo 'I give you permission to show my location'; ?>");
				if (document.getElementById('authentication_method').value == "LDAP") {
					document.getElementById("required_state").innerHTML = "&nbsp";
					document.getElementById("required_postal").innerHTML = "&nbsp";
				} else {
					document.getElementById("required_state").innerHTML = "*";
					document.getElementById("required_postal").innerHTML = "*";
				}
				div_Province_Label.style.display = "none";
				div_State_Label.style.display = "block";
				div_Province_Value.style.display = "none";
				div_State_Value.style.display = "block";
			} else {
				//alert ("<?php echo 'I withdraw my permission to show my location'; ?>");
				if (document.getElementById('authentication_method').value == "LDAP") {
					document.getElementById("required_state").innerHTML = "&nbsp";
					document.getElementById("required_postal").innerHTML = "&nbsp";
				} else {
					document.getElementById("required_state").innerHTML = "&nbsp";
					document.getElementById("required_postal").innerHTML = "&nbsp";
				}
				div_Province_Label.style.display = "block";
				div_State_Label.style.display = "none";
				div_Province_Value.style.display = "block";
				div_State_Value.style.display = "none";
			}
		}
			
		function updateAction(action) {
			//alert ("action: " + action);
			document.getElementById("action").value = action;
		}
	
		function removeError() {
			document.getElementById("errorMsg").innerHTML = "";
		}	
	</script>
	<link href="./css/mainstyle.css" rel="stylesheet" type="text/css">		
	<div style="width:80%; font-family:Arial, Helvetica, sans-serif;font-size:12px;line-height:18px; border: 0px solid black; margin: 0px auto;background-color:white">
	<!--
		<form action="edit_user_handler.php" method="post" onsubmit="return checkValidator(this)" name="mainForm">
		<form action="edit_user_handler.php" method="post" name="mainForm">
	-->
	<form action="edit_user_handler.php" method="post" onsubmit="return checkValidator(this)" name="mainForm">
		<table width=100% style="padding:25px; text-align:center; background-color:#E3E3E3;">
			<tr>
				<td colspan="2"><br><br>
					<?php
					if (isset($_SESSION['photo_access']['login']['username'])) echo "<br>";
					?>
					<div class="mainTitle"><strong><center>Photo Access ID - <?php if (isset($_GET['id'])) echo "Edit User for ID: ".$_GET['id']; else echo "New User" ?></center></strong></div><br>
				</td>
			</tr>
			<tr>
				<td colspan="2">
					<?php
						if (strpos($errorMsg, "Error") === false) {
							echo "<div style=\"color:#0011FF; text-align:center; background-color:#E3E3E3\" id=\"errorMsg\">";
							if (!empty($errorMsg)) echo $errorMsg; 
							echo "</div>";
						} else {
							echo "<div style=\"color:#FF0000; text-align:center; background-color:#E3E3E3\" id=\"errorMsg\">";
							if (!empty($errorMsg)) echo $errorMsg; 
							echo "</div>";
						}
					?>
				</td>
			</tr>
			<?php
			if ($authentication_type == "Database") {
				echo "<tr>
					<td><input type=\"hidden\" name=\"authentication_method\" id=\"authentication_method\" value=\"Database\">
					</td>
				</tr>";
			} elseif ($authentication_type == "LDAP" and $allow_standard_access == "N") {
				echo "<tr>
					<td><input type=\"hidden\" name=\"authentication_method\" id=\"authentication_method\" value=\"LDAP\">
					</td>
				</tr>";
			} else {
				echo "<tr>
					<td width=25% style=\"padding-top: .5em; padding-bottom: .5em; padding-left: .5em\">
						<b>Are you UNC Charlotte Staff/Faculty? <font color=\"red\">*</font></b>				
					</td>
					<td width=75% style=\"padding-top: .5em; padding-bottom: .5em;\">
						<select name=\"authentication_method\" id=\"authentication_method\" onChange=\"authenticationMethodDisplayRequiredText()\">
							<option value=\"LDAP\""; if ($authentication_method == "LDAP") echo " selected"; echo">Yes</option>
							<option value=\"Database\""; if ($authentication_method == "Database") echo " selected"; echo">No</option>
						</select>
					</td>
				</tr>";
			}
			?>
			<tr>
				<td width=25% style="padding-top: .5em; padding-bottom: .5em; padding-left: .5em">
					<b>First Name: <font color="red">*</font></b>				
				</td>
				<td width=75% style="padding-top: .5em; padding-bottom: .5em;">
					<input type="text" size="32" maxlength="32" name="first_name" id="first_name" value="<?php if (!empty($first_name)) echo $first_name; ?>">
				</td>
			</tr>
			<tr>
				<td width=25% style="padding-top: .5em; padding-bottom: .5em; padding-left: .5em">
					<b>Middle Name:</b>				
				</td>
				<td width=75% style="padding-top: .5em; padding-bottom: .5em;">
					<input type="text" size="32" maxlength="32" name="middle_name" id="middle_name" value="<?php if (!empty($middle_name)) echo $middle_name; ?>">
				</td>
			</tr>
			<tr>
				<td width=25% style="padding-top: .5em; padding-bottom: .5em; padding-left: .5em">
					<b>Last Name: <font color="red">*</font></b>				
				</td>
				<td width=75% style="padding-top: .5em; padding-bottom: .5em;">
					<input type="text" size="32" maxlength="32" name="last_name" id="last_name" value="<?php if (!empty($last_name)) echo $last_name; ?>">
				</td>
			</tr>
			<tr>
				<td width=25% style="padding-top: .5em; padding-bottom: .5em; padding-left: .5em">
					<b>Country: <?php if ($authentication_method == "Database") echo "<font color=\"red\" id=\"required_country\">*</font>"; else echo "<font color=\"red\" id=\"required_country\"></font>"; ?></b>
				</td>
				<td width=75% style="padding-top: .5em; padding-bottom: .5em;">
					<select name="country_id" id="country_id" onChange="displayRequiredText()">
						<option value=""></option>
						<?php
							foreach ($countryArray as $value) {
								$countryArray_id = $value['id'];
								$countryArray_short_name = $value['short_name'];
						?>
						<option value="<?php echo $countryArray_id; ?>" <?php if ($country_id == $countryArray_id) echo " selected"; else if ($value['id'] == 188) echo " selected";?> ><?php echo $countryArray_short_name; ?></option>
						<?php echo "\n";
							}
						?>
					</select>
				</td>
			</tr>
			<tr>
				<td width=25% style="padding-top: .5em; padding-bottom: .5em; padding-left: .5em">
					<b>Address 1: <?php if ($authentication_method == "Database") echo "<font color=\"red\" id=\"required_address_1\">*</font>"; else echo "<font color=\"red\" id=\"required_address_1\"></font>"; ?></b>			
				</td>
				<td width=75% style="padding-top: .5em; padding-bottom: .5em;">
					<input type="text" size="64" maxlength="128" name="address_1" id="address_1" value="<?php if (!empty($address_1)) echo $address_1; ?>">
				</td>
			</tr>				
			<tr>
				<td width=25% style="padding-top: .5em; padding-bottom: .5em; padding-left: .5em">
					<b>Address 2:</b>				
				</td>
				<td width=75% style="padding-top: .5em; padding-bottom: .5em;">
					<input type="text" size="64" maxlength="128" name="address_2" id="address_2" value="<?php if (!empty($address_2)) echo $address_2; ?>">
				</td>
			</tr>				
			<tr>
				<td width=25% style="padding-top: .5em; padding-bottom: .5em; padding-left: .5em">
					<b>City: <?php if ($authentication_method == "Database") echo "<font color=\"red\" id=\"required_city\">*</font>"; else echo "<font color=\"red\" id=\"required_city\"></font>"; ?></b>				
					</td>
					<td width=75% style="padding-top: .5em; padding-bottom: .5em;">
						<input type="text" size="64" maxlength="128" name="city" id="city" value="<?php if (!empty($city)) echo $city; ?>">
				</td>
			</tr>
			<tr>
				<td width=25% style="padding-top: .5em; padding-bottom: .5em; padding-left: .5em">
					<div id="showOrHideDiv_Province_Label" style="display:none;"><b>State/Province:</b></div>
					<div id="showOrHideDiv_State_Label" style="display:block;"><b>State:</b><?php if ($authentication_method == "Database") echo "<font color=\"red\" id=\"required_state\">*</font>"; else echo "<font color=\"red\" id=\"required_state\"></font>"; ?></div>							
				</td>
				<td width=75% style="padding-top: .5em; padding-bottom: .5em;">
					<div id="showOrHideDiv_Province_Value" style="display:none;">
						<input type="text" size="32" maxlength="32" name="state_province" id="state_province" value="<?php if (!empty($state_province)) echo $state_province; ?>">
					</div>
					<div id="showOrHideDiv_State_Value" style="display:block;">
						<select name="state" id="state">
							<option value=""></option>
							<?php
								foreach ($countryStateArray as $value) {
									$state_name = $value['state_name'];
							?>
							<option value="<?php echo $state_name; ?>" <?php if ($state_province == $state_name) echo " selected";?>><?php echo $state_name; ?></option>
							<?php echo "\n";
								}
							?>
						</select>
					</div>
				</td>
			</tr>
			<tr>
				<td width=25% style="padding-top: .5em; padding-bottom: .5em; padding-left: .5em">
					<b>Postal code: <?php if ($authentication_method == "Database") echo "<font color=\"red\" id=\"required_postal\">*</font>"; else echo "<font color=\"red\" id=\"required_postal\"></font>"; ?></b>		
				</td>
				<td width=75% style="padding-top: .5em; padding-bottom: .5em;">
					<input type="text" size="5" maxlength="5" name="postal_code" id="postal_code" value="<?php if (!empty($postal_code)) echo $postal_code; ?>">
				</td>
			</tr>				
			<tr>
				<td width=25% style="padding-top: .5em; padding-bottom: .5em; padding-left: .5em">
					<b>Email: <font color="red">*</font></b>				
				</td>
				<td width=75% style="padding-top: .5em; padding-bottom: .5em;">
					<input type="text" size="64" maxlength="64" name="email" id="email" value="<?php if (!empty($email)) echo $email; ?>">
				</td>
			</tr>
			<tr>
				<td width=25% style="padding-top: .5em; padding-bottom: .5em; padding-left: .5em">
					<b>Phone: <?php if ($authentication_method == "Database") echo "<font color=\"red\" id=\"required_phone\">*</font>"; else echo "<font color=\"red\" id=\"required_phone\"></font>"; ?></b>			
				</td>
				<td width=75% style="padding-top: .5em; padding-bottom: .5em;">
					<input type="text" size="15" maxlength="15" name="phone" id="phone" value="<?php if (!empty($phone)) echo $phone; ?>">
				</td>
			</tr>
			<?php
			if ($policy_renew == "Y") {
				echo "<tr>
					<td width=25% style=\"padding-top: .5em; padding-bottom: .5em; padding-left: .5em\">
						<b>Policy Acceptance Date:</b>				
					</td>
					<td width=75% style=\"padding-top: .5em; padding-bottom: .5em;\">
						<input type=\"text\" size=\"32\" maxlength=\"32\"  readonly=\"readonly\" name=\"policy_acceptance_date\" id=\"policy_acceptance_date\" value=\""; if (!empty($policy_acceptance_date)) echo date($date_format, strtotime($policy_acceptance_date)); echo "\">
					</td>
				</tr>";
			}
			?>
			<tr>
				<td width=25% style="padding-top: .5em; padding-bottom: .5em; padding-left: .5em">
					<b>User ID: <font color="red">*</font></b>
				</td>
				<td width=75% style="padding-top: .5em; padding-bottom: .5em;">
					<input type="text" size="32" maxlength="32" <?php if ($action=="Update") echo "readonly=\"readonly\" " ?>name="user_id" id="user_id" value="<?php if (!empty($user_id)) echo $user_id; ?>">
				</td>
			</tr>
			<tr>
				<td width=25% style="padding-top: .5em; padding-bottom: .5em; padding-left: .5em">
					<?php
					if ($action=="Update") {
						echo "<div id=\"showOrHideDiv_Change_Password_Label\" style=\"display:".$display_UI.";\"><b>Change Password: </b>"; if ($authentication_method == "Database" and $old_password == "") echo "<font color=\"red\" id=\"required_change_password\">*</font>"; else echo "<font color=\"red\" id=\"required_change_password\"></font>"; echo "</div>";
					} else {
						echo "<div id=\"showOrHideDiv_Password_Label\" style=\"display:".$display_UI.";\"><b>Password: "; if ($authentication_method == "Database") echo "<font color=\"red\" id=\"required_password\">*</font>"; else echo "<font color=\"red\" id=\"required_password\"></font>"; echo "</b></div>";
					}
					?>
				</td>
				<td width=75% style="padding-top: .5em; padding-bottom: .5em;">
					<div id="showOrHideDiv_Password_Value" style="display:<?php echo $display_UI;?>;"><input type="password" size="32" maxlength="32" name="password" id="password" value="<?php if (!empty($password)) echo $password;?>"></div>
				</td>
			</tr>
			<tr>
				<td width=25% style="padding-top: .5em; padding-bottom: .5em; padding-left: .5em">
					<?php
					if ($action=="Update") {
						echo "<div id=\"showOrHideDiv_Verify_Change_Password_Label\" style=\"display:".$display_UI.";\"><b>Verify Change Password: </b>"; if ($authentication_method == "Database" and $old_password == "") echo "<font color=\"red\" id=\"required_verify_change_password\">*</font>"; else echo "<font color=\"red\" id=\"required_verify_change_password\"></font>"; echo "</div>";
					} else {
						echo "<div id=\"showOrHideDiv_Verify_Password_Label\" style=\"display:".$display_UI.";\"><b>Verify Password: "; if ($authentication_method == "Database") echo "<font color=\"red\" id=\"required_verify_password\">*</font>"; else echo "<font color=\"red\" id=\"required_verify_password\"></font>"; echo "</b></div>";
					}
					?>
				</td>
				<td width=75% style="padding-top: .5em; padding-bottom: .5em;">
					<div id="showOrHideDiv_Verify_Password_Value" style="display:<?php echo $display_UI;?>;"><input type="password" size="32" maxlength="32" name="verify_password" id="verify_password" value="<?php if (!empty($verify_password)) echo $verify_password;?>"></div>
				</td>
			</tr>	
			<?php
			if (isset($userGroup) and intval($userGroup) <= 2) {
				echo "<tr>
					<td width=25% style=\"padding-top: .5em; padding-bottom: .5em; padding-left: .5em\">
						<b>Department:</b>				
					</td>
					<td width=75% style=\"padding-top: .5em; padding-bottom: .5em;\">
						<input type=\"text\" size=\"32\" maxlength=\"64\" name=\"department\" id=\"department\" value=\""; if (!empty($department)) echo $department; echo"\">
					</td>
				</tr>
				<tr>
					<td width=25% style=\"padding-top: .5em; padding-bottom: .5em; padding-left: .5em\">
						<b>Department Backup:</b>				
					</td>
					<td width=75% style=\"padding-top: .5em; padding-bottom: .5em;\">
						<input type=\"text\" size=\"64\" maxlength=\"255\" name=\"department_backup\" id=\"department_back_up\" value=\""; if (!empty($department_backup)) echo $department_backup; echo"\">
					</td>
				</tr>
				<tr>
					<td width=25% style=\"padding-top: .5em; padding-bottom: .5em; padding-left: .5em\">
						<b>Group: <font color=\"red\">*</font></b>				
					</td>
					<td width=75% style=\"padding-top: .5em; padding-bottom: .5em;\">
						<select name=\"user_group_id\" id=\"user_group_id\">
							<option value=\"\"></option>";
							
								foreach ($arrayUserGroup as $value) {
									$userGroup_id = $value['id'];
									$userGroup_name = $value['group_name'];
									if ($userGroup_id >= intval($userGroup)) {
							
							echo "<option value=\"".$userGroup_id."\""; if ($user_group_id == $userGroup_id) echo " selected"; echo ">".$userGroup_name."</option>\n";
									}
								}
						
						echo "</select>
					</td>
				</tr>
				<tr>
					<td width=25% style=\"padding-top: .5em; padding-bottom: .1em; padding-left: .5em\">
						<b>Active</b>
					</td>
					<td width=75% style=\"padding-top: .5em; padding-bottom: .1em;\">
						<input type=\"checkbox\" name=\"active\" id=\"active\" value=\"Y\""; if ($active=="Y") echo " checked"; echo ">
					</td>
				</tr>";
			} else {
				echo "<tr>\n";
					echo "<td width=25% style=\"padding-top: .5em; padding-bottom: .1em; padding-left: .5em\">\n";
					echo "</td>\n";
					echo "<td width=75% style=\"padding-top: .5em; padding-bottom: .1em;\">\n";
						echo "<input type=\"hidden\" name=\"user_group_id\" id=\"user_group_id\" value=\"".$user_group_id."\">\n";
						echo "<input type=\"hidden\" name=\"department\" id=\"department\" value=\"".$department."\">\n";
						echo "<input type=\"hidden\" name=\"department_backup\" id=\"department_backup\" value=\"".$department_backup."\">\n";		
						echo "<input type=\"hidden\" name=\"active\" id=\"active\" value=\"".$active."\">\n";							
					echo "</td>\n";
				echo "</tr>\n";
			}
			?>
			<?php
			if ($authentication_method == "Database" and $action=="Update") {	
				$queryPhotoIdentificationString = "SELECT a.*, b.first_name, b.last_name FROM `photo_access_identification` a LEFT JOIN `photo_access_users` b on a.checker_name = b.user_id WHERE a.photo_access_user_id=".$id." order by a.checked_date DESC";
				//echo "queryString: ".$queryPhotoIdentificationString."<br>";
				$queryPhotoIdentificationStatus = $db->query($queryPhotoIdentificationString);
				if ($queryPhotoIdentificationStatus) {
					$arrayPhotoIdentification = $db->fetch_assoc_all();
					if (!empty($arrayPhotoIdentification) and sizeof($arrayPhotoIdentification) > 0) {
						echo "<tr>
							<td width=25% style=\"padding-top: .5em; padding-bottom: .1em; padding-left: .5em\">
								<b>Photo Identification</b>
							</td>
							<td width=75% style=\"padding-top: .5em; padding-bottom: .1em;\">
								<table width=50% style=\"padding:5px; text-align:center; background-color:#E3E3E3;\">
									<tr>
										<td width=60% style=\"padding-left: 1em\"><b>Photo Checked Date</b></td>
										<td width=40%><b>Check By</b></td>
									</tr>
								</table>
								<div class=\"displayPhotoIdentification\">
								<table width=100% style=\"padding:5px; text-align:center; background-color:white;\">";
									for ($i=0; $i < sizeof($arrayPhotoIdentification); $i++) {
									echo "<tr>
										<td style=\"padding-left: .5em;\">".date($datetime_format, strtotime($arrayPhotoIdentification[$i]['checked_date']))."</td>
										<td>".$arrayPhotoIdentification[$i]['first_name']." ".$arrayPhotoIdentification[$i]['last_name']."</td>
									</tr>\n";
									}
								echo "</table>
								</div>
							</td>
						</tr>";	
					}
				}
			}
			?>
			<?php
			if ($action=="Update" and $active=="Y") {
			echo "<tr>
				<td width=25% style=\"padding-top: .5em; padding-bottom: .1em; padding-left: .5em\">
					<b>Created Date:</b>
				</td>
				<td width=75% style=\"padding-top: .5em; padding-bottom: .1em;\">";
			if (!empty($created_date)) echo date($datetime_format, strtotime($created_date))." by ".$createdBy;  
			echo "</td>
			</tr>
			
			<tr>
				<td width=25% style=\"padding-top: .1em; padding-bottom: .1em; padding-left: .5em\">
					<b>Updated Date:</b>
				</td>
				<td width=75% style=\"padding-top: .1em; padding-bottom: .1em;\">";
					if (!empty($updated_date)) echo date($datetime_format, strtotime($updated_date))." by ".$updatedBy;
				echo "</td>
			</tr>\n";
				}
			?>	
			<tr>
				<td width=25% style="padding-top: .5em; padding-bottom: 1em;"><br>
					<center>
					<?php
					if ($action=="Update" or $action=="Photo Identification") { // and $active=="Y"
						echo "<input type=\"submit\" name=\"update\" value=\"Update\" class=\"dark_green_button\" onClick=\"updateAction('Update')\">";
					} else { //if ($active=="")
						echo "<input type=\"submit\" name=\"submit\" value=\"Submit\" class=\"dark_green_button\" onClick=\"updateAction('Submit')\">";
					}
					?>
					</center>
					<input type="hidden" name="action" id="action" value="<?php echo $action ?>">
					<input type="hidden" name="id" id="id" value="<?php echo $id; ?>">
					<input type="hidden" name="old_password" id="old_password" value="<?php echo $old_password ?>">
					
					<input type="hidden" name="number_per_page" id="number_per_page" value="<?php echo $number_per_page ?>">
					<input type="hidden" name="time_zone" id="time_zone" value="<?php echo $time_zone ?>">
					<input type="hidden" name="date_format" id="date_format" value="<?php echo $date_format ?>">
					<input type="hidden" name="time_format" id="time_format" value="<?php echo $time_format ?>">
				</td>
				<td width=75% style="padding-top: .5em; padding-bottom: 1em;"><br>
					<center>
					<?php
					if ($action=="Update" or $action=="Photo Identification") { // and $active=="Y"
						if ($authentication_method == "Database") {
							echo "&nbsp<input type=\"submit\" name=\"photo_identification\" value=\"Photo Identification\" class=\"blue_button\" onClick=\"updateAction('Photo Identification')\">";
						}
						if (isset($userGroup) and intval($userGroup) < 2) {
							echo "&nbsp<input type=\"submit\" name=\"delete\" value=\"Delete\" class=\"deleteButton\" onClick=\"updateAction('Delete')\">";
						}
					} 
					?>
					</center>
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
	<?php	
	include ("./web/footer.html"); 	
	} else {
		include ("./web/header.html"); 
	?>
		<link href="./../css/mainstyle.css" rel="stylesheet" type="text/css">
		<div style="width:80%; font-family:Arial, Helvetica, sans-serif;font-size:12px;line-height:18px; border: 0px solid black; margin: 0px auto;background-color:white">
		<table  width=100% style="padding:25px; text-align:center; background-color:white;">
			<tr>
				<td><br>
					<div class="mainTitle"><strong><center>Photo Access ID - Edit User</center></strong></div><br>
				</td>
			</tr>
			<tr>
				<td><br>
					<div style="color:#FF0000; text-align:center; background-color:#FFFFFF" id="errorMsg">
						<?php echo $error; ?>
					</div> 
				</td>
			</tr>
			<tr>
				<td>
					<br><br><br>
				</td>
			</tr>
		</table>
		<?php include ("./web/footer.html"); 
	}
} else {
	include ("./web/header.html");
?>	
	<link href="./../css/mainstyle.css" rel="stylesheet" type="text/css">
	<div style="width:80%; font-family:Arial, Helvetica, sans-serif;font-size:12px;line-height:18px; border: 0px solid black; margin: 0px auto;background-color:white">
	<table  width=100% style="padding:25px; text-align:center; background-color:white;">
		<tr>
			<td><br>
				<div class="mainTitle"><strong><center>Photo Access ID - Edit User</center></strong></div><br>
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
 
 
 
 

	