<?php
/*
 *******************************************************************************************************
*
* Name: config_admin_handler.php
* Configuration page for admin such as LDAP, mail and all config fields
* Writer: Bach Nguyen (bnguye21@uncc.edu)
* Last Updated: 03/04/2015
*	02/27/2015
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
	$link = $db->get_link();
	$security_code = $SESSION_SECURITY;
	$session_lifetime = $SESSION_TIMEOUT;
	$session = new Zebra_Session($link, $security_code, $session_lifetime);
	//echo "Here session: ".$_SESSION['photo_access']['login']['session_id']." - scode: ".$security_code." - sectime: ".$session_lifetime;
	//print_r('<pre>');
	//print_r($session->get_settings());
	//print_r('</pre>');
	if (!isset($_SESSION['photo_access']['login']['username'])) {
		$reDirectLocation = "Location: ./../";
		header($reDirectLocation);
		exit();
	} elseif (isset($_SESSION['photo_access']['login']['user_group']) and intval($_SESSION['photo_access']['login']['user_group']) > 1) {
		$warningMsg = "You don't have permission to create/edit Admin Configuration";
		$reDirectLocation = "Location: ./../index.php?warningMsg=".$warningMsg;
		header($reDirectLocation);
		exit();
	}

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
	
	if (isset($_POST["ldap_account_suffix"])) {
		$ldap_account_suffix = trim($_POST['ldap_account_suffix']);
		$ldap_base_dn = trim($_POST['ldap_base_dn']);
		$ldap_domain_controllers = trim($_POST['ldap_domain_controllers']);
		$ldap_admin_username = trim($_POST['ldap_admin_username']);
		$ldap_admin_password = trim($_POST['ldap_admin_password']);
		$ldap_real_primarygroup = trim($_POST['ldap_real_primarygroup']);
		$ldap_use_ssl = trim($_POST['ldap_use_ssl']);
		$ldap_use_tls = trim($_POST['ldap_use_tls']);
		$ldap_recursive_groups = trim($_POST['ldap_recursive_groups']);
		$ldap_ad_port = trim($_POST['ldap_ad_port']);
		$ldap_sso = trim($_POST['ldap_sso']);
		$ldap_dept_access = trim($_POST['ldap_dept_access']);
		$ldap_member_of = trim($_POST['ldap_member_of']);
		$ldap_store_password = trim($_POST['ldap_store_password']);
		$ldap_self_approved = trim($_POST['ldap_self_approved']);
		$ldap_policy_renew = trim($_POST['ldap_policy_renew']);
		$policy_period_value = trim($_POST['policy_period_value']);
		$policy_period_period = trim($_POST['policy_period_period']);
		$ldap_policy_period = $policy_period_value." ".$policy_period_period;
		if (isset($_POST['ldap_allow_standard_access']))
			$ldap_allow_standard_access = trim($_POST['ldap_allow_standard_access']);
		else
			$ldap_allow_standard_access = "";
		$ldap_authentication_type = trim($_POST['ldap_authentication_type']);
		$ldap_default_group = trim($_POST['ldap_default_group']);
		$ldap_display_warning_msg = trim($_POST['ldap_display_warning_msg']);
		// Email
		$email_prefix = trim($_POST['email_prefix']);
		$email_transport = trim($_POST['email_transport']);
		$email_site_domain = trim($_POST['email_site_domain']);
		$email_smtp_host = trim($_POST['email_smtp_host']);
		$email_smtp_port = trim($_POST['email_smtp_port']);
		$email_host_requires_login = trim($_POST['email_host_requires_login']);
		$email_smtp_username = trim($_POST['email_smtp_username']);
		$email_smtp_password = trim($_POST['email_smtp_password']);
		$email_smtp_server_timeout = trim($_POST['email_smtp_server_timeout']);
		if (isset($_POST['email_enable_tls']))
			$email_enable_tls = trim($_POST['email_enable_tls']);
		else
			$email_enable_tls = "";
		$email_from = trim($_POST['email_from']);
		$email_send_type = trim($_POST['email_send_type']);

		$dataQuery = array('ldap_account_suffix'=>$ldap_account_suffix,
						'ldap_base_dn'=>$ldap_base_dn,
						'ldap_domain_controllers'=>$ldap_domain_controllers,
						'ldap_admin_username'=>$ldap_admin_username,	
						'ldap_admin_password'=>$ldap_admin_password,	
						'ldap_real_primarygroup'=>$ldap_real_primarygroup,							
						'ldap_use_ssl'=>$ldap_use_ssl,
						'ldap_use_tls'=>$ldap_use_tls,
						'ldap_recursive_groups'=>$ldap_recursive_groups,
						'ldap_ad_port'=>$ldap_ad_port,
						'ldap_sso'=>$ldap_sso,
						'ldap_dept_access'=>$ldap_dept_access,
						'ldap_member_of'=>$ldap_member_of,
						'ldap_store_password'=>$ldap_store_password,
						'ldap_self_approved'=>$ldap_self_approved,
						'ldap_policy_renew'=>$ldap_policy_renew,
						'ldap_policy_period'=>$ldap_policy_period,
						'ldap_allow_standard_access'=>$ldap_allow_standard_access,
						'ldap_authentication_type'=>$ldap_authentication_type,
						'ldap_default_group'=>$ldap_default_group,
						'ldap_display_warning_msg'=>$ldap_display_warning_msg,
						'email_prefix'=>$email_prefix,
						'email_transport'=>$email_transport,
						'email_site_domain'=>$email_site_domain,
						'email_smtp_host'=>$email_smtp_host,
						'email_smtp_port'=>$email_smtp_port,
						'email_host_requires_login'=>$email_host_requires_login,
						'email_smtp_username'=>$email_smtp_username,
						'email_smtp_password'=>$email_smtp_password,
						'email_smtp_server_timeout'=>$email_smtp_server_timeout,
						'email_enable_tls'=>$email_enable_tls,
						'email_from'=>$email_from,
						'email_send_type'=>$email_send_type);
		//echo "<pre>";
		//	print_r($dataQuery);
		//echo "</pre>";
		//exit();
		if (empty($ldap_authentication_type)) {
			$errorMsg = "Error! Please select 'Authentication Type' before clicking Submit";
			$dataQuery['error'] = $errorMsg;
			header("location: ./config_admin.php?".http_build_query($dataQuery));
			exit();
		} 
	
		if (empty($ldap_account_suffix)) {
			$errorMsg = "Error! Please enter 'LDAP Account Suffix' before clicking Submit";
			$dataQuery['error'] = $errorMsg;
			header("location: ./config_admin.php?".http_build_query($dataQuery));
			exit();
		} 
	
		if (empty($ldap_base_dn)) {
			$errorMsg = "Error! Please enter 'LDAP Base Domain Name' before clicking Submit";
			$dataQuery['error'] = $errorMsg;
			header("location: ./config_admin.php?".http_build_query($dataQuery));
			exit();
		}
	
		if (empty($ldap_domain_controllers)) {
			$errorMsg = "Error! Please enter 'LDAP Domain Controllers' before clicking Submit";
			$dataQuery['error'] = $errorMsg;
			header("location: ./config_admin.php?".http_build_query($dataQuery));
			exit();
		}
	
		if (empty($ldap_admin_username)) {
			$errorMsg = "Error! Please enter 'LDAP Admin Username' before clicking Submit";
			$dataQuery['error'] = $errorMsg;
			header("location: ./config_admin.php?".http_build_query($dataQuery));
			exit();
		}
	
		if (empty($ldap_admin_password)) {
			$errorMsg = "Error! Please enter 'LDAP Admin Password' before clicking Submit";
			$dataQuery['error'] = $errorMsg;
			header("location: ./config_admin.php?".http_build_query($dataQuery));
			exit();
		}
	
		if (empty($ldap_ad_port)) {
			$errorMsg = "Error! Please enter 'LDAP Port' before clicking Submit";
			$dataQuery['error'] = $errorMsg;
			header("location: ./config_admin.php?".http_build_query($dataQuery));
			exit();
		}
	
		// Email
		if ($email_from == "") {
			$errorMsg = "Error! Please select 'Email Sending From' before clicking Submit";
			$dataQuery['error'] = $errorMsg;
			header("location: ./config_admin.php?".http_build_query($dataQuery));
			exit();
		}
		if ($email_transport == "SMTP" and empty($email_smtp_host)) {
			$errorMsg = "Error! Please enter 'SMTP Host' before clicking Submit";
			$dataQuery['error'] = $errorMsg;
			header("location: ./config_admin.php?".http_build_query($dataQuery));
			exit();
		}
		if ($email_transport == "SMTP" and empty($email_smtp_port)) {
			$errorMsg = "Error! Please enter 'SMTP Port' before clicking Submit";
			$dataQuery['error'] = $errorMsg;
			header("location: ./config_admin.php?".http_build_query($dataQuery));
			exit();
		}
		if ($email_host_requires_login == "Y" and empty($email_smtp_username)) {
			$errorMsg = "Error! Please enter 'SMTP Username' before clicking Submit";
			$dataQuery['error'] = $errorMsg;
			header("location: ./config_admin.php?".http_build_query($dataQuery));
			exit();
		}
		if ($email_host_requires_login == "Y" and empty($email_smtp_password)) {
			$errorMsg = "Error! Please enter 'SMTP Password' before clicking Submit";
			$dataQuery['error'] = $errorMsg;
			header("location: ./config_admin.php?".http_build_query($dataQuery));
			exit();
		}	

		$updateQuery = $dataQuery;
		//function updateKeyToValue(&$item, $key, $prefix)
		//{
		//	if (empty($item)) {
		//		$item = "NULL";
		//	} else {
		//		$item = "$prefix$item$prefix";
		//	}
		//}
		//array_walk($updateQuery, 'updateKeyToValue', "'");
	
		$updateStatus = array();
		foreach ($updateQuery as $key => $value) {
			$keyArray = array();
			$keyArray[] = $key;
			$checkStatus = $db->update('photo_access_config', array('value' => $value), '`key` = ?', $keyArray);
			//echo "status update for ".$key."=".$value." is: '".$checkStatus."'<br>";
			if ($checkStatus) {
				$updateStatus[$key] = "Success";
			} else {
				$updateStatus[$key] = "False";
			}
		}
		//$updateStatus['ldap_account_suffix'] = "False";
		//$updateStatus['ldap_admin_password'] = "False";
		//echo "<pre>";
		//	print_r($updateStatus);
		//echo "</pre>";
		//exit();
		$falseKeyList = "";
		$falseKeyStatus = false;
		foreach ($updateStatus as $key => $value) {
			if ($value == "False") {
				if (empty($falseKeyList)) {
					$falseKeyList .= $key;
				} else {
					$falseKeyList .= ", ".$key;
				}
				$falseKeyStatus = true;
			}
		}
		if ($falseKeyStatus == false) {
			$errorMsg = "Success updated these fields to admin config table";
			$dataQuery['error'] = $errorMsg;
			header("location: ./config_admin.php?error=".$errorMsg);
			exit();
		} else {
			$errorMsg = "Error! ".$falseKeyList. " false to update to admin config table";
			$dataQuery['error'] = $errorMsg;
			header("location: ./config_admin.php?".http_build_query($dataQuery));
			exit();
		}
	}
} else {
	$errorMsg = $checkConnect['error'];
	//echo "errorMsg: ".$errorMsg."<br>";
	$dataQuery['error'] = $errorMsg;
	header("location: ./config_admin.php?".http_build_query($dataQuery));	
}
?>