<?php
/*
 *******************************************************************************************************
*
* Name: config_admin.php
* Configuration page for admin such as LDAP, mail and all config fields
* Writer: Bach Nguyen (bnguye21@uncc.edu)
* Last Updated: 03/04/2015
*	02/25/2015
*		- Changed database class, session class and paging class
*		- Updated codes
*   10/27/2014
*		- Added self approval, policy renew and policy period
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
 
		if (isset($_SESSION['user_group']) and intval($_SESSION['user_group']) > 1) {
			$warningMsg = "You don't have permission to edit Patron Registration - Admin Configuration";
			$reDirectLocation = "Location: ./../index.php?warningMsg=".$warningMsg;
			//echo "reDirection Location: '".$reDirectLocation."'<br>";
			header($reDirectLocation);
			exit();
		}
			
		// Getting LDAP
		if (!empty($_GET["ldap_authentication_type"])) {
			$ldap_authentication_type = $_GET["ldap_authentication_type"];
		} else $ldap_authentication_type = "";
		if (!empty($_GET["ldap_account_suffix"])) {
			$ldap_account_suffix = $_GET["ldap_account_suffix"];
		} else $ldap_account_suffix = "";
		if (!empty($_GET["ldap_base_dn"])) {
			$ldap_base_dn = $_GET["ldap_base_dn"];
		} else $ldap_base_dn = "";
		if (!empty($_GET["ldap_domain_controllers"])) {
			$ldap_domain_controllers = $_GET["ldap_domain_controllers"];
		} else $ldap_domain_controllers = "";
		if (!empty($_GET["ldap_admin_username"])) {
			$ldap_admin_username = $_GET["ldap_admin_username"];
		} else $ldap_admin_username = "";
		if (!empty($_GET["ldap_admin_password"])) {
			$ldap_admin_password = $_GET["ldap_admin_password"];
		} else $ldap_admin_password = "";
		if (!empty($_GET["ldap_real_primarygroup"])) {
			$ldap_real_primarygroup = $_GET["ldap_real_primarygroup"];
		} else $ldap_real_primarygroup = "true";
		if (!empty($_GET['ldap_use_ssl'])) {
			$ldap_use_ssl = trim($_GET["ldap_use_ssl"]);
		} else $ldap_use_ssl = "";
		if (!empty($_GET['ldap_use_tls'])) {
			$ldap_use_tls = trim($_GET["ldap_use_tls"]);
		} else $ldap_use_tls = "";
		if (!empty($_GET["ldap_recursive_groups"])) {
			$ldap_recursive_groups = $_GET["ldap_recursive_groups"];
		} else $ldap_recursive_groups = "true";
		if (!empty($_GET["ldap_ad_port"])) {
			$ldap_ad_port = $_GET["ldap_ad_port"];
		} else $ldap_ad_port = "";
		if (!empty($_GET["ldap_sso"])) {
			$ldap_sso = $_GET["ldap_sso"];
		} else $ldap_sso = "";
		if (!empty($_GET["ldap_dept_access"])) {
			$ldap_dept_access = $_GET["ldap_dept_access"];
		} else $ldap_dept_access = "";
		if (!empty($_GET["ldap_store_password"])) {
			$ldap_store_password = $_GET["ldap_store_password"];
		} else $ldap_store_password = "N";	
		if (!empty($_GET["ldap_self_approved"])) {
			$ldap_self_approved = $_GET["ldap_self_approved"];
		} else $ldap_self_approved = "N";	
		if (!empty($_GET["ldap_policy_renew"])) {
			$ldap_policy_renew = $_GET["ldap_policy_renew"];
		} else $ldap_policy_renew = "Y";	
		if (!empty($_GET["ldap_policy_period"])) {
			$ldap_policy_period = $_GET["ldap_policy_period"];
		} else $ldap_policy_period = "";
		if (empty($ldap_policy_period)) {
			$policy_period_value = 1;
			$policy_period_period = "year";		
		} else {
			$policy_period_array = explode (" ",$ldap_policy_period);
			$policy_period_value = trim($policy_period_array[0]);
			$policy_period_period = trim($policy_period_array[1]);	
		}
		if (!empty($_GET["ldap_allow_standard_access"])) {
			$ldap_allow_standard_access = $_GET["ldap_allow_standard_access"];
		} else $ldap_allow_standard_access = "";
		if (!empty($_GET["ldap_default_group"])) {
			$ldap_default_group = $_GET["ldap_default_group"];
		} else $ldap_default_group = "";
		if (!empty($_GET["ldap_display_warning_msg"])) {
			$ldap_display_warning_msg = $_GET["ldap_display_warning_msg"];
		} else $ldap_display_warning_msg = "Y";
		if (!empty($_GET["ldap_member_of"])) {
			$ldap_member_of = $_GET["ldap_member_of"];
		} else $ldap_member_of = "";		
		// Getting Email Setting
		if (!empty($_GET["email_prefix"])) {
			$email_prefix = $_GET["email_prefix"];
		} else $email_prefix = "";	
		if (!empty($_GET["email_transport"])) {
			$email_transport = $_GET["email_transport"];
		} else $email_transport = "";	
		if (!empty($_GET["email_site_domain"])) {
			$email_site_domain = $_GET["email_site_domain"];
		} else $email_site_domain = "";		
		if (!empty($_GET["email_smtp_host"])) {
			$email_smtp_host = $_GET["email_smtp_host"];
		} else $email_smtp_host = "";
		if (!empty($_GET["email_smtp_port"])) {
			$email_smtp_port = $_GET["email_smtp_port"];
		} else $email_smtp_port = "";
		if (!empty($_GET["email_host_requires_login"])) {
			$email_host_requires_login = $_GET["email_host_requires_login"];
		} else $email_host_requires_login = "";	
		if (!empty($_GET["email_smtp_username"])) {
			$email_smtp_username = $_GET["email_smtp_username"];
		} else $email_smtp_username = "";
		if (!empty($_GET["email_smtp_password"])) {
			$email_smtp_password = $_GET["email_smtp_password"];
		} else $email_smtp_password = "";
		if (!empty($_GET["email_smtp_server_timeout"])) {
			$email_smtp_server_timeout = $_GET["email_smtp_server_timeout"];
		} else $email_smtp_server_timeout = "";	
		if (!empty($_GET["email_enable_tls"])) {
			$email_enable_tls = $_GET["email_enable_tls"];
		} else $email_enable_tls = "";	
		if (!empty($_GET["email_from"])) {
			$email_from = $_GET["email_from"];
		} else $email_from = "";	
		if (!empty($_GET["email_send_type"])) {
			$email_send_type = $_GET["email_send_type"];
		} else $email_send_type = "";			
		if (!empty($_GET["error"])) {
			$warningMsg = $_GET["error"];
		} else $warningMsg = "";
		
		// Collect User
		$queryUserIDString = "select user_id from photo_access_users where active='Y' and user_group_id<=3 order by `user_id` ASC";
		$queryUserIDStatus = $db->query($queryUserIDString);
		if ($queryUserIDStatus) {
			$userIDArray = $db->fetch_assoc_all();
		}

		if (isset($userIDArray) and sizeof($userIDArray) >0) {
			foreach ($userIDArray as $key) {
				foreach ($key as $value) {
					$user_IDs[] = $value;
				}
			}
		}

		$queryUserGroupString = "select * from photo_access_user_group";
		//echo "query: ".$queryString."<br>";
		$queryUserGroupStatus = $db->query($queryUserGroupString);
		if ($queryUserGroupStatus) {
			$arrayUserGroup = $db->fetch_assoc_all();
		}
		///echo "<pre>";
		//	echo print_r($arrayUserGroup);
		//echo "</pre>";
		//exit();
		
		if (empty($warningMsg) or strstr($warningMsg, "Success")) {

			$queryConfigString = "select * from photo_access_config";
			$queryConfigStatus = $db->query($queryConfigString);
			if ($queryConfigStatus) {
				$arrayConfig = $db->fetch_assoc_all();	
			}
			//echo "<pre>";
			//	print_r($arrayConfig);
			//echo "</pre>";
		
			foreach ($arrayConfig as $value) {
				//echo "<pre>";
				//	print_r($value);
				//echo "</pre>";
				$arrayConfigSet[$value['key']] = $value['value'];
			}
			//echo "<pre>";
			//	print_r($arrayConfigSet);
			//echo "</pre>";
			
			if (!empty($arrayConfig)) {
				// LDAP
				$ldap_account_suffix = $arrayConfigSet['ldap_account_suffix'];
				$ldap_base_dn = $arrayConfigSet['ldap_base_dn'];
				$ldap_domain_controllers = $arrayConfigSet['ldap_domain_controllers'];
				$ldap_admin_username = $arrayConfigSet['ldap_admin_username'];
				$ldap_admin_password = $arrayConfigSet['ldap_admin_password'];
				$ldap_real_primarygroup = $arrayConfigSet['ldap_real_primarygroup'];
				$ldap_use_ssl = $arrayConfigSet['ldap_use_ssl'];
				$ldap_use_tls = $arrayConfigSet['ldap_use_tls'];
				$ldap_recursive_groups = $arrayConfigSet['ldap_recursive_groups'];
				$ldap_ad_port = $arrayConfigSet['ldap_ad_port'];
				$ldap_sso = $arrayConfigSet['ldap_sso'];
				$ldap_dept_access = $arrayConfigSet['ldap_dept_access'];
				$ldap_store_password = $arrayConfigSet['ldap_store_password'];
				$ldap_self_approved = $arrayConfigSet['ldap_self_approved'];
				$ldap_policy_renew = $arrayConfigSet['ldap_policy_renew'];
				$ldap_policy_period = $arrayConfigSet['ldap_policy_period'];
				if (empty($ldap_policy_period)) {
					$policy_period_value = 1;
					$policy_period_period = "year";		
				} else {
					$policy_period_array = explode (" ",$ldap_policy_period);
					$policy_period_value = trim($policy_period_array[0]);
					$policy_period_period = trim($policy_period_array[1]);	
				}
				$ldap_allow_standard_access = $arrayConfigSet['ldap_allow_standard_access'];
				$ldap_authentication_type = $arrayConfigSet['ldap_authentication_type'];
				$ldap_default_group = $arrayConfigSet['ldap_default_group'];
				$ldap_display_warning_msg = $arrayConfigSet['ldap_display_warning_msg'];
				$ldap_member_of = $arrayConfigSet['ldap_member_of'];
				// email
				$email_prefix = $arrayConfigSet['email_prefix'];
				$email_transport = $arrayConfigSet['email_transport'];
				$email_site_domain = $arrayConfigSet['email_site_domain'];
				$email_smtp_host = $arrayConfigSet['email_smtp_host'];
				$email_smtp_port = $arrayConfigSet['email_smtp_port'];
				$email_host_requires_login = $arrayConfigSet['email_host_requires_login'];
				$email_smtp_username = $arrayConfigSet['email_smtp_username'];
				$email_smtp_password = $arrayConfigSet['email_smtp_password'];
				$email_smtp_server_timeout = $arrayConfigSet['email_smtp_server_timeout'];
				$email_enable_tls = $arrayConfigSet['email_enable_tls'];
				$email_from = $arrayConfigSet['email_from'];
				$email_send_type = $arrayConfigSet['email_send_type'];
			}
		}
?>
		<?php include ("./../web/header.html");?>
		<?php include ("./../web/menu.html");?>
		<link href="./../css/mainstyle.css" rel="stylesheet" type="text/css">
		<script Language="JavaScript">
			function trim(stringToTrim) {
				return stringToTrim.replace(/^\s+|\s+$/g,"");
			}
		
			function checkValidator(theForm) {
				// LDAP
				var ldap_account_suffix = trim(document.getElementById('ldap_account_suffix').value);
				var ldap_base_dn = trim(document.getElementById('ldap_base_dn').value);
				var ldap_domain_controllers = trim(document.getElementById('ldap_domain_controllers').value);
				var ldap_admin_username = trim(document.getElementById('ldap_admin_username').value);
				var ldap_admin_password = trim(document.getElementById('ldap_admin_password').value);
				var ldap_ad_port = trim(document.getElementById('ldap_ad_port').value);
				var ldap_authentication_type = trim(document.getElementById('ldap_authentication_type').value);
				// Email
				var email_transport = trim(document.getElementById('email_transport').value);
				var email_smtp_host = trim(document.getElementById('email_smtp_host').value);
				var email_smtp_port = trim(document.getElementById('email_smtp_port').value);
				var email_smtp_username = trim(document.getElementById('email_smtp_username').value);
				var email_smtp_password = trim(document.getElementById('email_smtp_password').value);
				
				// LDAP
				if (ldap_authentication_type == "") {
					//alert("Please enter 'ldap_account_suffix' before clicking Submit");
					document.getElementById("errorMsg").innerHTML = "Please select 'Authentication Type' before clicking Submit";
					theForm.ldap_authentication_type.style.backgroundColor="#D0E2ED";
					theForm.ldap_authentication_type.focus();
					return (false);
				}
				if (ldap_account_suffix == "") {
					//alert("Please enter 'ldap_account_suffix' before clicking Submit");
					document.getElementById("errorMsg").innerHTML = "Please enter 'LDAP Account Suffix' before clicking Submit";
					theForm.ldap_account_suffix.style.backgroundColor="#D0E2ED";
					theForm.ldap_account_suffix.focus();
					return (false);
				}
				if (ldap_base_dn == "") {
					//alert("Please enter 'ldap_base_dn' before clicking Submit");
					document.getElementById("errorMsg").innerHTML = "Please enter 'LDAP Base Domain Name' before clicking Submit";
					theForm.ldap_base_dn.style.backgroundColor="#D0E2ED";
					theForm.ldap_base_dn.focus();
					return (false);
				}
				if (ldap_domain_controllers == "") {
					//alert("Please enter 'ldap_domain_controllers' before clicking Submit");
					document.getElementById("errorMsg").innerHTML = "Please enter 'LDAP Domain Controllers' before clicking Submit";
					theForm.ldap_domain_controllers.style.backgroundColor="#D0E2ED";
					theForm.ldap_domain_controllers.focus();
					return (false);
				}
				if (ldap_admin_username == "") {
					//alert("Please enter 'ldap_admin_username' before clicking Submit");
					document.getElementById("errorMsg").innerHTML = "Please enter 'LDAP Admin Username' before clicking Submit";
					theForm.ldap_admin_username.style.backgroundColor="#D0E2ED";
					theForm.ldap_admin_username.focus();
					return (false);
				}
				if (ldap_admin_password == "") {
					//alert("Please enter 'ldap_admin_password' before clicking Submit");
					document.getElementById("errorMsg").innerHTML = "Please enter 'LDAP Admin Password' before clicking Submit";
					theForm.ldap_admin_password.style.backgroundColor="#D0E2ED";
					theForm.ldap_admin_password.focus();
					return (false);
				}
				if (ldap_ad_port == "") {
					//alert("Please enter 'ldap_ad_port' before clicking Submit");
					document.getElementById("errorMsg").innerHTML = "Please enter 'LDAP Port' before clicking Submit";
					theForm.ldap_ad_port.style.backgroundColor="#D0E2ED";
					theForm.ldap_ad_port.focus();
					return (false);
				}
				
				// Email
				if (email_transport == "SMTP" && email_smtp_host == "") {
					//alert("Please enter 'SMTP Host' before clicking Submit");
					document.getElementById("errorMsg").innerHTML = "Please enter 'SMTP Host' before clicking Submit";
					theForm.email_smtp_host.style.backgroundColor="#D0E2ED";
					theForm.email_smtp_host.focus();
					return (false);
				}
				if (email_transport == "SMTP" && email_smtp_port == "") {
					//alert("Please enter 'SMTP Port' before clicking Submit");
					document.getElementById("errorMsg").innerHTML = "Please enter 'SMTP Port' before clicking Submit";
					theForm.email_smtp_port.style.backgroundColor="#D0E2ED";
					theForm.email_smtp_port.focus();
					return (false);
				}
				if (theForm.email_host_requires_login.checked && email_smtp_username == "") {
					//alert("Please enter 'SMTP Username' before clicking Submit");
					document.getElementById("errorMsg").innerHTML = "Please enter 'SMTP Username' before clicking Submit";
					theForm.email_smtp_username.style.backgroundColor="#D0E2ED";
					theForm.email_smtp_username.focus();
					return (false);
				}
				if (theForm.email_host_requires_login.checked && email_smtp_password == "") {
					//alert("Please enter 'SMTP Password' before clicking Submit");
					document.getElementById("errorMsg").innerHTML = "Please enter 'SMTP Password' before clicking Submit";
					theForm.email_smtp_password.style.backgroundColor="#D0E2ED";
					theForm.email_smtp_password.focus();
					return (false);
				}
				if (theForm.email_from == "") {
					//alert("Please select 'Send From' before clicking Submit");
					document.getElementById("errorMsg").innerHTML = "Please select 'Send From' before clicking Submit";
					theForm.email_from.style.backgroundColor="#D0E2ED";
					theForm.email_from.focus();
					return (false);
				}
			}
			
		</script>
		<div style="width:80%; font-family:Arial, Helvetica, sans-serif;font-size:12px;line-height:18px; border: 0px solid black; margin: 0px auto;background-color:white">
		<br>
		<!--
			<form action="config_admin_handler.php" method="post" onsubmit="return checkValidator(this)" name="mainForm">
			<form action="config_admin_handler.php" method="post" name="mainForm">
		-->
		<form action="config_admin_handler.php" method="post" onsubmit="return checkValidator(this)" name="mainForm">
			<table  width=100% style="padding:25px; text-align:center; background-color:#EFEFEF;">
				<tr>
					<td colspan="2"><br><br>
						<div class="mainTitle"><strong><center>Photo Access ID - Administration Configuration</center></strong></div><br>
					</td>
				</tr>
				<tr>
					<td colspan="2">
						<?php
							if (strpos($warningMsg, "Error") === false) {
								echo "<div style=\"color:#0011FF; text-align:center; background-color:#EFEFEF\" id=\"errorMsg\">";
								if (!empty($warningMsg)) echo $warningMsg; 
								echo "</div>";
							} else {
								echo "<div style=\"color:#FF0000; text-align:center; background-color:#EFEFEF\" id=\"errorMsg\">";
								if (!empty($warningMsg)) echo $warningMsg; 
								echo "</div>";
							}
						?>
					</td>
				</tr>
				<tr>
					<td colspan="2" style="padding-top: .5em; padding-bottom: 0em; padding-left: .5em">
						<b>LDAP Setting</b>
					</td>
				</tr>
				<tr>
					<td width=30% style="padding-top: .5em; padding-bottom: .5em; padding-left: 1.5em">
						Authentication Type: *				
					</td>
					<td width=70% style="padding-top: .5em; padding-bottom: .5em;">
						<select name="ldap_authentication_type" id="ldap_authentication_type">
							<option value=""></option>
							<option value="LDAP" <?php if ($ldap_authentication_type == "LDAP") echo " selected";?>>LDAP</option>
							<option value="Database" <?php if ($ldap_authentication_type == "Database") echo " selected";?>>Database</option>
						</select>
					</td>
				</tr>
				<tr>
					<td width=30% style="padding-top: .5em; padding-bottom: .5em; padding-left: 1.5em">
						LDAP Account Suffix: *				
					</td>
					<td width=70% style="padding-top: .5em; padding-bottom: .5em;">
						<input type="text" size="64" maxlength="255" name="ldap_account_suffix" id="ldap_account_suffix" value="<?php if (!empty($ldap_account_suffix)) echo $ldap_account_suffix; ?>">
					</td>
				</tr>
				<tr>
					<td width=30% style="padding-top: .5em; padding-bottom: .5em; padding-left: 1.5em">
						LDAP Base Domain Name: *				
					</td>
					<td width=70% style="padding-top: .5em; padding-bottom: .5em;">
						<input type="text" size="84" maxlength="255" name="ldap_base_dn" id="ldap_base_dn" value="<?php if (!empty($ldap_base_dn)) echo $ldap_base_dn; ?>">
					</td>
				</tr>
				<tr>
					<td width=30% style="padding-top: .5em; padding-bottom: .5em; padding-left: 1.5em">
						LDAP Domain Controllers: *				
					</td>
					<td width=70% style="padding-top: .5em; padding-bottom: .5em;">
						<input type="text" size="64" maxlength="255" name="ldap_domain_controllers" id="ldap_domain_controllers" value="<?php if (!empty($ldap_domain_controllers)) echo $ldap_domain_controllers; ?>">
					</td>
				</tr>
				<tr>
					<td width=30% style="padding-top: .5em; padding-bottom: .5em; padding-left: 1.5em">
						LDAP Admin Username: *				
					</td>
					<td width=70% style="padding-top: .5em; padding-bottom: .5em;">
						<input type="text" size="64" maxlength="255" name="ldap_admin_username" id="ldap_admin_username" value="<?php if (!empty($ldap_admin_username)) echo $ldap_admin_username; ?>">
					</td>
				</tr>
				<tr>
					<td width=30% style="padding-top: .5em; padding-bottom: .5em; padding-left: 1.5em">
						LDAP Admin Password: *				
					</td>
					<td width=70% style="padding-top: .5em; padding-bottom: .5em;">
						<input type="text" size="64" maxlength="255" name="ldap_admin_password" id="ldap_admin_password" value="<?php if (!empty($ldap_admin_password)) echo $ldap_admin_password; ?>">
					</td>
				</tr>
				<tr>
					<td width=30% style="padding-top: .5em; padding-bottom: .5em; padding-left: 1.5em">
						LDAP Real Primary Group:
					</td>
					<td width=70% style="padding-top: .5em; padding-bottom: .5em;">
						<select name="ldap_real_primarygroup" id="ldap_primarygroup">
							<option value=""></option>
							<option value="true" <?php if ($ldap_real_primarygroup == "true") echo " selected";?>>True</option>
							<option value="false" <?php if ($ldap_real_primarygroup == "false") echo " selected";?>>False</option>
						</select>
					</td>
				</tr>
				<tr>
					<td width=30% style="padding-top: .5em; padding-bottom: .5em; padding-left: 1.5em">
						LDAP Use SSL:			
					</td>
					<td width=70% style="padding-top: .5em; padding-bottom: .5em;">
						<select name="ldap_use_ssl" id="ldap_use_ssl">
							<option value=""></option>
							<option value="true" <?php if ($ldap_use_ssl == "true") echo " selected";?>>True</option>
							<option value="false" <?php if ($ldap_use_ssl == "false") echo " selected";?>>False</option>
						</select>
					</td>
				</tr>
				<tr>
					<td width=30% style="padding-top: .5em; padding-bottom: .5em; padding-left: 1.5em">
						LDAP Use TLS:
					</td>
					<td width=70% style="padding-top: .5em; padding-bottom: .5em;">
						<select name="ldap_use_tls" id="ldap_use_tls">
							<option value=""></option>
							<option value="true" <?php if ($ldap_use_tls == "true") echo " selected";?>>True</option>
							<option value="false" <?php if ($ldap_use_tls == "false") echo " selected";?>>False</option>
						</select>
					</td>
				</tr>
				<tr>
					<td width=30% style="padding-top: .5em; padding-bottom: .5em; padding-left: 1.5em">
						LDAP Recursive Groups:
					</td>
					<td width=70% style="padding-top: .5em; padding-bottom: .5em;">
						<select name="ldap_recursive_groups" id="ldap_recursive_groups">
							<option value=""></option>
							<option value="true" <?php if ($ldap_recursive_groups == "true") echo " selected";?>>True</option>
							<option value="false" <?php if ($ldap_recursive_groups == "false") echo " selected";?>>False</option>
						</select>
					</td>
				</tr>
				<tr>
					<td width=30% style="padding-top: .5em; padding-bottom: .5em; padding-left: 1.5em">
						LDAP Port: *		
					</td>
					<td width=70% style="padding-top: .5em; padding-bottom: .5em;">
						<input type="text" size="64" maxlength="64" name="ldap_ad_port" id="ldap_ad_port" value="<?php if (!empty($ldap_ad_port)) echo $ldap_ad_port; ?>">
					</td>
				</tr>
				<tr>
					<td width=30% style="padding-top: .5em; padding-bottom: .5em; padding-left: 1.5em">
						LDAP SSO:
					</td>
					<td width=70% style="padding-top: .5em; padding-bottom: .5em;">
						<select name="ldap_sso" id="ldap_sso">
							<option value=""></option>
							<option value="true" <?php if ($ldap_sso == "true") echo " selected";?>>True</option>
							<option value="false" <?php if ($ldap_sso == "false") echo " selected";?>>False</option>
						</select>
					</td>
				</tr>
				<tr>
					<td width=30% style="padding-top: .5em; padding-bottom: .5em; padding-left: 1.5em">
						LDAP Dept Access:		
					</td>
					<td width=70% style="padding-top: .5em; padding-bottom: .5em;">
						<input type="text" size="84" maxlength="255" name="ldap_dept_access" id="ldap_dept_access" value="<?php if (!empty($ldap_dept_access)) echo $ldap_dept_access; ?>">
					</td>
				</tr>
				<tr>
					<td width=30% style="padding-top: .5em; padding-bottom: .5em; padding-left: 1.5em">
						LDAP Member Of:		
					</td>
					<td width=70% style="padding-top: .5em; padding-bottom: .5em;">
						<input type="text" size="104" maxlength="255" name="ldap_member_of" id="ldap_member_of" value="<?php if (!empty($ldap_member_of)) echo $ldap_member_of; ?>">
					</td>
				</tr>
				<tr>
					<td width=30% style="padding-top: .5em; padding-bottom: .5em; padding-left: 1.5em">
						LDAP Store Password:		
					</td>
					<td width=70% style="padding-top: .5em; padding-bottom: .5em;">
						<select name="ldap_store_password" id="ldap_store_password">
							<option value="Y" <?php if ($ldap_store_password == "Y") echo " selected";?>>Yes</option>
							<option value="N" <?php if ($ldap_store_password == "N") echo " selected";?>>No</option>
						</select>
					</td>
				</tr>
				<tr>
					<td width=30% style="padding-top: .5em; padding-bottom: .5em; padding-left: 1.5em">
						LDAP Self Approved:		
					</td>
					<td width=70% style="padding-top: .5em; padding-bottom: .5em;">
						<select name="ldap_self_approved" id="ldap_self_approved">
							<option value="Y" <?php if ($ldap_self_approved == "Y") echo " selected";?>>Yes</option>
							<option value="N" <?php if ($ldap_self_approved == "N") echo " selected";?>>No</option>
						</select>
					</td>
				</tr>
				<tr>
					<td width=30% style="padding-top: .5em; padding-bottom: .5em; padding-left: 1.5em">
						Policy Renew:		
					</td>
					<td width=70% style="padding-top: .5em; padding-bottom: .5em;">
						<select name="ldap_policy_renew" id="ldap_policy_renew">
							<option value="Y" <?php if ($ldap_policy_renew == "Y") echo " selected";?>>Yes</option>
							<option value="N" <?php if ($ldap_policy_renew == "N") echo " selected";?>>No</option>
						</select>
					</td>
				</tr>
				<tr>
					<td width=30% style="padding-top: .5em; padding-bottom: .5em; padding-left: 1.5em">
						LDAP Policy Period:		
					</td>
					<td width=70% style="padding-top: .5em; padding-bottom: .5em;">
						<input type="text" size="10" maxlength="4" name="policy_period_value" id="policy_period_value" value="<?php if (!empty($policy_period_value)) echo $policy_period_value; ?>">
						<select name="policy_period_period" id="policy_period_period">
							<option value="day" <?php if ($policy_period_period == "day") echo " selected";?>>day</option>
							<option value="week" <?php if ($policy_period_period == "week") echo " selected";?>>week</option>
							<option value="month" <?php if ($policy_period_period == "month") echo " selected";?>>month</option>
							<option value="year" <?php if ($policy_period_period == "year") echo " selected";?>>year</option>
						</select>
					</td>
				</tr>				
				<tr>
					<td width=30% style="padding-top: .5em; padding-bottom: .5em; padding-left: 1.5em">
						Allow Standard Login:		
					</td>
					<td width=70% style="padding-top: .5em; padding-bottom: .5em;">
						<input type="checkbox" name="ldap_allow_standard_access" id="ldap_allow_standard_access" value="Y" <?php if ($ldap_allow_standard_access=="Y") echo " checked";?>>
					</td>
				</tr>	
				<tr>
					<td width=30% style="padding-top: .5em; padding-bottom: .5em; padding-left: 1.5em">
						Default Group:		
					</td>
					<td width=70% style="padding-top: .5em; padding-bottom: .5em;">
						<select name="ldap_default_group" id="ldap_default_group">
							<?php
								foreach ($arrayUserGroup as $value) {
									if (empty($ldap_default_group)) {
										if ($value['id'] == "5") {
											$valueSelect = " selected";
										} else {
											$valueSelect = "";
										}
									} else {
										if ($value['id'] == $ldap_default_group) {
											$valueSelect = " selected";
										} else {
											$valueSelect = "";
										}
									}
									echo "<option value=\"".$value['id']."\"".$valueSelect.">".$value['group_name']."</option>\n";
								}
							?>
						</select>
					</td>
				</tr>
				<tr>
					<td width=30% style="padding-top: .5em; padding-bottom: .5em; padding-left: 1.5em">
						LDAP Display Warning Message:		
					</td>
					<td width=70% style="padding-top: .5em; padding-bottom: .5em;">
						<select name="ldap_display_warning_msg" id="ldap_display_warning_msg">
							<option value="Y" <?php if ($ldap_display_warning_msg == "Y") echo " selected";?>>Yes</option>
							<option value="N" <?php if ($ldap_display_warning_msg == "N") echo " selected";?>>No</option>
						</select>
					</td>
				</tr>
				
				<!-- Email Setting -->
				<tr>
					<td colspan="2" style="padding-top: .5em; padding-bottom: .5em; padding-left: .5em">
						<b>Email Setting</b>
					</td>
				</tr>
				<tr>
					<td width=30% style="padding-top: .5em; padding-bottom: .5em; padding-left: 1.5em">
						Email Sending Type:		
					</td>
					<td width=70% style="padding-top: .5em; padding-bottom: .5em;">
						<select name="email_send_type" id="email_send_type">
							<option value="Separated" <?php if ($email_send_type=="Separated") echo "selected"; ?>>Seprated</option>
							<option value="Combined" <?php if ($email_send_type=="Combined") echo "selected"; ?>>Combined</option>
						</select>
					</td>
				</tr>
				<tr>
					<td width=30% style="padding-top: .5em; padding-bottom: .5em; padding-left: 1.5em">
						Email Prefix:		
					</td>
					<td width=70% style="padding-top: .5em; padding-bottom: .5em;">
						<input type="text" size="64" maxlength="64" name="email_prefix" id="email_prefix" value="<?php if (!empty($email_prefix)) echo $email_prefix; ?>">
					</td>
				</tr>	
				<tr>
					<td width=30% style="padding-top: .5em; padding-bottom: .5em; padding-left: 1.5em">
						Email Sending From:		
					</td>
					<td width=70% style="padding-top: .5em; padding-bottom: .5em;">
						<select name="email_from" id="email_from">
							<option value=""></option>
							<?php
								foreach ($user_IDs as $value) { ?>
									<option value="<?php echo $value; ?>" <?php if ($email_from == $value) echo " selected";?>><?php echo $value; ?></option><?php echo "\n";
								}
							?>
						</select>
					</td>
				</tr>
				<tr>
					<td width=30% style="padding-top: .5em; padding-bottom: .5em; padding-left: 1.5em">
						Email Transport: 		
					</td>
					<td width=70% style="padding-top: .5em; padding-bottom: .5em;">
						<select name="email_transport" id="email_transport">
							<option value="PHP Default" <?php if ($email_transport == "PHP Default") echo " selected";?>>PHP Default</option>
							<option value="SMTP" <?php if ($email_transport == "SMTP") echo " selected";?>>SMTP</option>
						</select>
					</td>
				</tr>
				<tr>
					<td width=30% style="padding-top: .5em; padding-bottom: .5em; padding-left: 1.5em">
						Site Domain:		
					</td>
					<td width=70% style="padding-top: .5em; padding-bottom: .5em;">
						<input type="text" size="64" maxlength="64" name="email_site_domain" id="email_site_domain" value="<?php if (!empty($email_site_domain)) echo $email_site_domain; ?>">
					</td>
				</tr>			
				<tr>
					<td width=30% style="padding-top: .5em; padding-bottom: .5em; padding-left: 1.5em">
						SMTP Host:		
					</td>
					<td width=70% style="padding-top: .5em; padding-bottom: .5em;">
						<input type="text" size="64" maxlength="64" name="email_smtp_host" id="email_smtp_host" value="<?php if (!empty($email_smtp_host)) echo $email_smtp_host; ?>">
					</td>
				</tr>					
				<tr>
				<td width=30% style="padding-top: .5em; padding-bottom: .5em; padding-left: 1.5em">
						SMTP Port:		
					</td>
					<td width=70% style="padding-top: .5em; padding-bottom: .5em;">
						<input type="text" size="64" maxlength="64" name="email_smtp_port" id="email_smtp_port" value="<?php if (!empty($email_smtp_port)) echo $email_smtp_port; ?>">
					</td>
				</tr>					
				<tr>
					<td width=30% style="padding-top: .5em; padding-bottom: .5em; padding-left: 1.5em">
						Host Requires Login:		
					</td>
					<td width=70% style="padding-top: .5em; padding-bottom: .5em;">
						<input type="checkbox" name="email_host_requires_login" id="email_host_requires_login" value="Y" <?php if ($email_host_requires_login=="Y") echo " checked";?>>
					</td>
				</tr>	
				<tr>
				<td width=30% style="padding-top: .5em; padding-bottom: .5em; padding-left: 1.5em">
						SMTP Username:		
					</td>
					<td width=70% style="padding-top: .5em; padding-bottom: .5em;">
						<input type="text" size="64" maxlength="64" name="email_smtp_username" id="email_smtp_username" value="<?php if (!empty($email_smtp_username)) echo $email_smtp_username; ?>">
					</td>
				</tr>	
				<tr>
				<td width=30% style="padding-top: .5em; padding-bottom: .5em; padding-left: 1.5em">
						SMTP Password:		
					</td>
					<td width=70% style="padding-top: .5em; padding-bottom: .5em;">
						<input type="text" size="64" maxlength="64" name="email_smtp_password" id="email_smtp_password" value="<?php if (!empty($email_smtp_password)) echo $email_smtp_password; ?>">
					</td>
				</tr>
				<tr>
				<td width=30% style="padding-top: .5em; padding-bottom: .5em; padding-left: 1.5em">
						SMTP Server Timeout:		
					</td>
					<td width=70% style="padding-top: .5em; padding-bottom: .5em;">
						<input type="text" size="64" maxlength="64" name="email_smtp_server_timeout" id="email_smtp_server_timeout" value="<?php if (!empty($email_smtp_server_timeout)) echo $email_smtp_server_timeout; ?>">
					</td>
				</tr>	
				<tr>
				<td width=30% style="padding-top: .5em; padding-bottom: .5em; padding-left: 1.5em">
						Enable TLS for SMTP:		
					</td>
					<td width=70% style="padding-top: .5em; padding-bottom: .5em;">
						<input type="checkbox" name="email_enable_tls" id="email_enable_tls" value="Y" <?php if ($email_enable_tls=="Y") echo " checked";?>>
					</td>
				</tr>					
				<tr>
					<td colspan="2" style="padding-top: .5em; padding-bottom: 1em;"><br>
						<center>
						<input type="submit" name="submit" value="Submit" class="dark_green_button">
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
		<?php include ("./../web/footer.html"); ?>	
<?php	
	} else {
		if (!isset($_SESSION['user'])) {
			$page_link = "./";
			$reDirectLocation = "Location: ./../web/login.php?page_link=".urlencode($page_link);
			//echo "reDirection Location: '".$reDirectLocation."'<br>";
			header($reDirectLocation);
			exit();
		}
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
				<div class="mainTitle"><strong><center>Photo Access ID - Administration Configuration</center></strong></div>
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