<?php
/*
 *******************************************************************************************************
*
* Name: login.php
* Display login form - work for both LDAP and standard mysql database access
*    Limit by department, active or inactive access
*    LDAP - password and all information will updated whenever he/she login
*		02/09/2015
*			- Added member of in checking LDAP
*			- Added session for login ID
*		02/04/2015
*			- Added session class
*			- Added database class
*			- Added more fields in user profile time_format, time_zone
*			- Modified codes - using function for duplication tasks such as setSession, DatabaseLogin....
*			- Rewrite the codes - easy to read, group same task to function and write to log
*		08/27/2014 - Add codes for loading profile
*		08/26/2014 - Add codes for checking policy date
*		08/20/2014 - Add codes for pending approval
*		08/12/2014 - Add codes for self_approved - Have to register before access to database
*		03/11/2014 - Added codes for using SSL
*		10/22/2013 - Added codes for checking inactive user
* Writer: Bach Nguyen (bnguye21@uncc.edu)
* Last Updated: 02/09/2015
*
 ********************************************************************************************************
 */
if ($_SERVER["HTTPS"] != "on") {
	$pageURL = "Location: https://";
	if ($_SERVER["SERVER_PORT"] != "80") {
		$pageURL .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"] . $_SERVER["REQUEST_URI"];
	} else {
		$pageURL .= $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
	}
	header($pageURL);
}
include("../install/database_credentials.inc");

if (isset($_GET['warningMsg'])) {
	$errorMsg = $_GET['warningMsg'];
} elseif (isset($_GET['errorMsg'])) {
	$errorMsg = $_GET['errorMsg'];
} else {
	$errorMsg = "";
}
$user = "";
//if (isset($_POST["submit"]) and $_POST["submit"] == "Login") {
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
	
	if (isset($checkConnect['status']) and $checkConnect['status'] == "false") {
		$errorMsg = $checkConnect['error'];
		$user = "";
		//echo "errorMsg: ".$errorMsg."<br>";
	} else {
		$db->set_charset($DATABASE_CHARSET);

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

			//print_r('<pre><strong>Current session settings:</strong><br><br>');
			//	print_r($ldapOptions);
			//print_r('</pre>');
			
			require './../classes/remoteaddress.php';
			require './../lib/Zebra_Session-master/Zebra_Session.php';
	
			$remoteAddress = new RemoteAddress;
			$remoteIP = $remoteAddress->getIPAddress();
			//echo $remoteIP;

			$link = $db->get_link();
			$security_code = $SESSION_SECURITY;
			$session_lifetime = $SESSION_TIMEOUT;
			$session = new Zebra_Session($link, $security_code, $session_lifetime);
	
			// current session settings
			//print_r('<pre><strong>Current session settings:</strong><br><br>');
			//print_r($session->get_settings());
			//print_r('</pre>');
	
			//print_r('<pre>PRINT SESSION START<br>');
			//print_r($_SESSION);
			//print_r('</pre>');

			// current active session
			//print_r('<pre><strong>after session settings:</strong><br><br>');
			//print_r($session->get_active_sessions());
			//print_r('</pre>');
	
			$_SESSION['photo_access']['login']['database_name'] = $DATABASE_NAME;
			$_SESSION['photo_access']['login']['policy_renew'] = $options['policy_renew'];
			$_SESSION['photo_access']['login']['default_policy'] = $options['default_policy'];
			$_SESSION['photo_access']['login']['self_approved'] = $options['self_approved'];
			$_SESSION['photo_access']['login']['store_password'] = $options['store_password'];
			$_SESSION['photo_access']['login']['authentication_type'] = $options['authentication_type'];
			$_SESSION['photo_access']['login']['allow_standard_access'] = $options['allow_standard_access'];
			$_SESSION['photo_access']['login']['default_group'] = intval($options['default_group']);
			$_SESSION['photo_access']['login']['display_warning_msg'] = $options['display_warning_msg'];
			//echo "<br>dataname: ".$_SESSION['photo_access']['login']['database_name'];
			//echo "<br>policy_renew: ".$_SESSION['photo_access']['login']['policy_renew'];
			$sessionID = session_id();

			//$session->stop();
			//exit();
	
			if (!empty($_SESSION['photo_access']['login']['username'])) {
				echo "You are login already<br>";
				$user = "";
				$errorMsg = "You are login already!";
				// BN - 2013/08/29 - Just added these lines below to forward to index if user click back button
				$reDirectLocation = "Location: ../";
				header($reDirectLocation);
			} else {
				function search_substring($array, $arraySubstring) {
					//echo $substring."<br>";
					foreach ($array as $index => $string) {
						//echo "string: ".$string."<br>";
						foreach ($arraySubstring as $subIndex => $substring) { 
							//echo "substring: ".$substring."<br>";
							if (false !== stripos($string, $substring)) {
								return $index;
							}
						}
					}
					return -1;
				} 
				
				function WriteToLoginLog ($db, $sessionArray) {
					global $remoteIP, $sessionID;
					$timeNow = date("Y-m-d H:i:s", strtotime("now"));
					$checkQuery = $db->insert_update(
						'photo_access_user_login_log',
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
		
				function DatabaseLogin($db, $username, $security_password) {
					global $errorMsg;
					global $user;
					global $options;
					global $session;
					$db->query('SELECT a.*, b.* FROM photo_access_users a left join photo_access_users_profile b on b.photo_access_user_id=a.id WHERE a.user_id=? and a.password=?',array($username ,array(substr($security_password,0,-8))));
					$arrayAuthentication = $db->fetch_assoc_all();
					//echo "<pre>";
					//	print_r($arrayAuthentication);
					//echo "</pre>";
					//echo "sizeof: ".sizeof($arrayAuthentication)."<br>";
					//exit();
					if (sizeof($arrayAuthentication) == 1) {
						if ($arrayAuthentication[0]['user_group_id'] < 6) {
							if ($arrayAuthentication[0]['active'] == "Y") {
								$sessionArray = array();
								$sessionArray['user_group_id'] = $arrayAuthentication[0]['user_group_id'];
								$sessionArray['user_id'] = $arrayAuthentication[0]['id'];
								$sessionArray['username'] = $arrayAuthentication[0]['user_id'];
								$sessionArray['first_name'] = $arrayAuthentication[0]['first_name'];	
								$sessionArray['last_name'] = $arrayAuthentication[0]['last_name'];	
								$sessionArray['email'] = $arrayAuthentication[0]['email'];
								$sessionArray['department'] = $arrayAuthentication[0]['department'];
								$sessionArray['department_backup'] = $arrayAuthentication[0]['department_backup'];
								$sessionArray['number_per_page'] = $arrayAuthentication[0]['number_per_page'];
								$sessionArray['time_zone'] = $arrayAuthentication[0]['time_zone'];
								$sessionArray['date_format'] = $arrayAuthentication[0]['date_format'];	
								$sessionArray['time_format'] = $arrayAuthentication[0]['time_format'];	
								if ($options['policy_renew'] == "Y") {
									$policy_acceptance_unix_date = strtotime($arrayAuthentication[0]['policy_acceptance_date']);
									//$expired_policy_unix_date = strtotime("+1 year", $policy_acceptance_unix_date);
									$expired_policy_unix_date = strtotime($options['policy_period'], $policy_acceptance_unix_date);
									$today_unix_date = strtotime("today");
									//echo "policy_acceptance_unix_date: ".$policy_acceptance_unix_date."<br>";
									//echo "expired_policy_unix_date: ".$expired_policy_unix_date."<br>";
									//echo "today_unix_date: ".$today_unix_date."<br>";
									//exit();
									if ($today_unix_date <= $expired_policy_unix_date) {
										if (SetSession ($db, $sessionArray, "N")) {
											$page_link = $_POST["page_link"];
											$reDirectLocation = "Location: ./../".$page_link;
											//echo "reDirection Location: ".$reDirectLocation."<br>";
											header($reDirectLocation);
											exit();
										} else {
											//$page_link = "web/login.php?errorMsg=Error: Couldn't save to login log";
											$errorMsg = "Error: Couldn't save to login log!";
											$user = $username;
											$session->stop();
											//$reDirectLocation = "Location: ./../".$page_link;
											//echo "reDirection Location: ".$reDirectLocation."<br>";
											//header($reDirectLocation);
											//exit();
										}
									} else {
										//$_SESSION['photo_access']['login']['user'] = $username;
										$updateArray = array();
										$updateArray['username'] = $username;
										$_SESSION['photo_access']['login']['userArray'] = $updateArray;
										if (SetSession ($db, $sessionArray, "Y")) {
											$page_link = "photo_access_policy.php?status=Database_Update&id=".$arrayAuthentication[0]['id'];
											$reDirectLocation = "Location: ./../".$page_link;
											//echo "reDirection Location: '".$reDirectLocation."'<br>";
											header($reDirectLocation);
											exit();
										} else {
											$page_link = "web/login.php?errorMsg=Error: Couldn't set session";
											$errorMsg = "Error: Couldn't set session!";
											$user = $username;
											$session->stop();
											//$reDirectLocation = "Location: ./../".$page_link;
											//echo "reDirection Location: ".$reDirectLocation."<br>";
											//header($reDirectLocation);
											//exit();										
										}
									}
								} else {
									SetSession ($db, $sessionArray, "N");
									$page_link = $_POST["page_link"];
									$reDirectLocation = "Location: ./../".$page_link;
									//echo "reDirection Location: ".$reDirectLocation."<br>";
									header($reDirectLocation);
									exit();
								}
							} else {
								$errorMsg = "Your account is inactive. Please contact Application Admin!";
								$user = $username;
								$session->stop();
							}
						} else {
							$errorMsg = "Your account is pending. Please contact Application Admin!";
							$user = $username;
							$session->stop();
						}
					} elseif (sizeof($arrayAuthentication) > 1) {
						$errorMsg = "Error 3: More than 1 username returned";
						$user = $username;
						$session->stop();
					} else {
						$errorMsg = "Error 4: username/password pair not found";
						$user = $username;
						$session->stop();
					}	
				}	
					
				if ($options['authentication_type'] == "Database") {
					//echo "Database<br>";
					if (isset($_POST["user"])) {			
						$username = $db->escape(trim($_POST["user"]));
					}
					if (isset($_POST["pass"])) {
						$password = $db->escape(trim($_POST["pass"]));
						$security_password = $DATABASE_PASSWORD_FUNCTION($password);
					}
					if (!empty($_POST["user"]) and !empty($_POST["pass"])) {
						DatabaseLogin($db, $username, $security_password);
					} else {
						//echo "missing<br>";
						if (isset($_POST["user"]) and empty($_POST["user"]) and isset($_POST["pass"]) and empty($_POST["pass"])) {
							$errorMsg = "Enter username and password";
							$user = "";
							$session->stop();
						} elseif (isset($_POST["user"]) and empty($_POST["user"])) {
							$errorMsg = "Error 1: Missing username";
							$user = "";
							$session->stop();
						} elseif (isset($_POST["pass"]) and empty($_POST["pass"])) {
							$errorMsg = "Error 2: Missing Password";
							$user = $username;
							$session->stop();
						}
					}
				} else {
					//echo "LDAP<br>";
					$domainArray = explode(",",$ldapOptions['domain_controllers']);
					$ldapOptions['domain_controllers'] = $domainArray;
					$deptArray = explode(";",$options['dept_access']);
					$deptArray = array_filter($deptArray);
					$memberofArray = explode(";",$options['member_of']);
					$memberofArray = array_filter($memberofArray);
					include ("./../lib/adLDAP/adLDAP.php");
					$adldap = new adLDAP($ldapOptions);
					$connect = $adldap->connect();
					if ($connect['status'] == false) {
						$errorMsg = $connect["error"];
						//echo $errorMsg;
						$user = "";
					} else {
						//echo "I am here";
						//exit();
						if (isset($_POST["user"])) {			
							//$username = trim($_POST["user"]);
							$username = $db->escape(trim($_POST["user"]));
						}
						if (isset($_POST["pass"])) {
							//$password = trim($_POST["pass"]);
							$password = $db->escape(trim($_POST["pass"]));
						}
						if (!empty($_POST["user"]) and !empty($_POST["pass"])) {
							$authUser = $adldap->user()->authenticate($username, $password);
							if ($authUser['status'] == true) {
								$user = "";
								$result=$adldap->user()->infoCollection($username, array("*"));
								//echo "<pre>";
								//	print_r ($result);
								//echo "</pre>";
								//exit();
								
								/* Set array for insert */
								$arrayUser = array();
								$arrayUser['user_group_id'] = intval($options['default_group']);
								$arrayUser['user_id'] = $username;
								if ($options['store_password'] == "Y") {
									$arrayUser['password'] = $DATABASE_PASSWORD_FUNCTION($password);
								} else {
									$arrayUser['password'] = null;
								}
								$arrayUser['first_name'] = $result->givenName;
								$arrayUser['middle_name'] = $result->initials;
								$arrayUser['last_name'] = $result->sn;
								$arrayUser['address_1'] = null;
								$arrayUser['address_2'] = null;
								$arrayUser['city'] = null;
								$arrayUser['state_province'] = null;
								$arrayUser['postal_code'] = null;
								$arrayUser['country_id'] = null;
								$arrayUser['email'] = $result->mail;
								if(!empty($result->telephoneNumber))
									$arrayUser['phone'] = $result->telephoneNumber;
								else
									$arrayUser['phone'] = null;
								$arrayUser['policy_acceptance_date'] = null;
								$arrayUser['department'] = $result->physicaldeliveryofficename;
								$arrayUser['department_backup'] = null;
								$arrayUser['authentication_method'] = "LDAP";
								$arrayUser['active'] = "Y";
								$dateCreated = date("Y-m-d H:i:s", strtotime("now"));
								$arrayUser['created_by'] = $username;
								$arrayUser['created_date'] = $dateCreated;
								$arrayUser['updated_by'] = $username;
								$arrayUser['updated_date'] = $dateCreated;
								
								/* Set array for update */
								$updateQuery = $arrayUser;
								unset ($updateQuery['user_group_id']);
								unset ($updateQuery['address_1']);
								unset ($updateQuery['address_2']);
								unset ($updateQuery['city']);
								unset ($updateQuery['state_province']);
								unset ($updateQuery['postal_code']);
								unset ($updateQuery['country_id']);
								unset ($updateQuery['department_backup']);
								unset ($updateQuery['policy_acceptance_date']);
								unset ($updateQuery['created_by']);
								unset ($updateQuery['created_date']);
								//echo "search substring: ".search_substring($result->memberof,$memberofArray);
								/* Check for Dept in LDAP */
								if (!empty($deptArray) and !in_array($result->physicalDeliveryOfficeName,$deptArray)) {
									if (sizeof($deptArray) > 1) {
										$errorMsg = "Only staff in ".$deptArray[0]." ... departments can access";
									} else {
										$errorMsg = "Only staff in ".$options['dept_access']." department can access";
									}
									$user = $username;
								/* Check for member of in LDAP */
								} elseif (!empty($memberofArray) and search_substring($result->memberof,$memberofArray) < 0) {
									if (sizeof($memberofArray) > 1) {
										$errorMsg = "Only member of ".$memberofArray[0]." ... can access";
									} else {
										$errorMsg = "Only member of ".$options['member_of']." can access";
									}
									$user = $username;	
								} else {
									$queryUser = "SELECT a.*, b.* FROM photo_access_users a left join photo_access_users_profile b on a.id=b.photo_access_user_id WHERE a.user_id='".$db->escape($username)."'";
									//echo $queryUser;
									$resultStatus = $db->query($queryUser);
									$searchResult = $db->fetch_assoc_all();
									if (!empty($searchResult)) {
										//echo "<pre>here";
										//	print_r ($searchResult);
										//echo "</pre>";					
										//exit();
										if (intval($searchResult[0]['user_group_id']) > 5) {
											$errorMsg = "Your account is pending. Please contact Application Admin!";
											$user = $username;
										} elseif ($searchResult[0]['active'] == "N") {
											$errorMsg = "Your account is inactive. Please contact Application Admin!";
											$user = $username;
										} else {
											/* Set session */
											$sessionArray = array();
											$sessionArray['user_group_id'] = intval($searchResult[0]['user_group_id']);
											$sessionArray['user_id'] = intval($searchResult[0]['id']);
											$sessionArray['username'] = $username;
											$sessionArray['first_name'] = $result->givenName;
											$sessionArray['last_name'] = $result->sn;	
											$sessionArray['email'] = $result->mail;
											$sessionArray['department'] = $result->physicaldeliveryofficename;
											$sessionArray['department_backup'] = $searchResult[0]['department_backup'];
											$sessionArray['number_per_page'] = $searchResult[0]['number_per_page'];
											$sessionArray['time_zone'] = $searchResult[0]['time_zone'];
											$sessionArray['date_format'] = $searchResult[0]['date_format'];
											$sessionArray['time_format'] = $searchResult[0]['time_format'];

											$policy_acceptance_unix_date = strtotime($searchResult[0]['policy_acceptance_date']);
											//$expired_policy_unix_date = strtotime("+1 year", $policy_acceptance_unix_date);
											$expired_policy_unix_date = strtotime($options['policy_period'], $policy_acceptance_unix_date);
											$today_unix_date = strtotime("today");
											//echo "policy_acceptance_unix_date: ".$policy_acceptance_unix_date."<br>";
											//echo "expired_policy_unix_date: ".$expired_policy_unix_date."<br>";
											//echo "today_unix_date: ".$today_unix_date."<br>";

											if ($options['policy_renew'] == "Y") {
												if ($today_unix_date <= $expired_policy_unix_date) {
													$updateStatus = $db->update('photo_access_users',$updateQuery, 'id=?', array(intval($searchResult[0]['id'])));
													if ($updateStatus === true) {
														if (SetSession ($db, $sessionArray, "N")) {
															$page_link = $_POST["page_link"];
															$reDirectLocation = "Location: ./../".$page_link;
															//echo "reDirection Location: ".$reDirectLocation."<br>";
															header($reDirectLocation);
															exit();		
														} else {
															//$page_link = "web/login.php?errorMsg=Error 7: Couldn't set session!";
															$errorMsg = "Error 7: Couldn't set session!";
															$user = $username;
															$session->stop();
															//$reDirectLocation = "Location: ./../".$page_link;
															//echo "reDirection Location: ".$reDirectLocation."<br>";
															//header($reDirectLocation);
															//exit();	
														}
													} else {
														$errorMsg = "Error 8: Couldn't update user!";
														$user = $username;
														$session->stop();
													}
												} else {
													if (SetSession ($db, $sessionArray, "Y")) {
														$_SESSION['photo_access']['login']['userArray'] = $updateQuery;
														$page_link = "photo_access_policy.php?status=LDAP_Update";
														$reDirectLocation = "Location: ./../".$page_link;
														//echo "reDirection Location: ".$reDirectLocation."<br>";
														header($reDirectLocation);
														exit();		
													} else {
														$errorMsg = "Error 9: Couldn't set session!";
														$user = $username;
														$session->stop();
													}
												}
											} else {
												$updateStatus = $db->update('photo_access_users',$updateQuery, 'id=?', array(intval($searchResult[0]['id'])));
												if ($updateStatus === true) {
													if (SetSession ($db, $sessionArray, "N")) {
														$page_link = $_POST["page_link"];
														$reDirectLocation = "Location: ./../".$page_link;
														//echo "reDirection Location: ".$reDirectLocation."<br>";
														header($reDirectLocation);
														exit();		
													} else {
														$errorMsg = "Error 10: Couldn't set session!";
														$user = $username;
														$session->stop();
													}
												} else {
													$errorMsg = "Error 11: Couldn't update user!";
													$user = $username;	
													$session->stop();
												}
											}	
										}										
									} else {
										/* Set session */
										//echo "<br>New LAP user";
										//exit();
										$sessionArray = array();
										$sessionArray['user_group_id'] = intval($options['default_group']);
										$sessionArray['user_id'] = null;
										$sessionArray['username'] = $username;
										$sessionArray['first_name'] = $result->givenName;
										$sessionArray['last_name'] = $result->sn;	
										$sessionArray['email'] = $result->mail;
										$sessionArray['department'] = $result->physicaldeliveryofficename;
										$sessionArray['department_backup'] = null;
										$sessionArray['number_per_page'] = $NUMBER_PER_PAGE;
										$sessionArray['time_zone'] = $TIME_ZONE;
										$sessionArray['date_format'] = $DATE_FORMAT;	
										$sessionArray['time_format'] = $TIME_FORMAT;
									
										if ($options['self_approved'] == "Y") {  // 08/12/2014 - Bach Nguyen added 
											if ($options['policy_renew'] == "Y") {
												if (SetSession ($db, $sessionArray, "Y")) {
													$_SESSION['photo_access']['login']['userArray'] = $arrayUser;
													$page_link = "photo_access_policy.php?status=LDAP_Submit";
													$reDirectLocation = "Location: ./../".$page_link;
													//echo "reDirection Location: '".$reDirectLocation."'<br>";
													header($reDirectLocation);
													exit();
												} else {
													$errorMsg = "Error 12: Couldn't set session!";
													$user = $username;	
													$session->stop();
												}
											} else {
												//echo "<br>policy_renew = N";
												// start transactions
												$db->transaction_start();
												
												$db->insert('photo_access_users',$arrayUser);
												$user_profile = array();
												//echo "insertID: ".$db->insert_id();
												$user_profile["photo_access_user_id"] = $db->insert_id();
												$user_profile["number_per_page"] = $NUMBER_PER_PAGE;
												$user_profile["time_zone"] = $TIME_ZONE;
												$user_profile["date_format"] = $DATE_FORMAT; 
												$user_profile["time_format"] = $TIME_FORMAT;

												$db->insert('photo_access_users_profile', $user_profile);
												
												if ($db->transaction_complete() === true) {
													if (SetSession ($db, $sessionArray, "N") === true) {
														$page_link = $_POST["page_link"];
														$reDirectLocation = "Location: ./../".$page_link;
														//echo "reDirection Location: ".$reDirectLocation."<br>";
														header($reDirectLocation);
														exit();		
													} else {
														$errorMsg = "Error 13: New User saved. However couldn't set session";
														$user = $username;	
														$session->stop();
													}
												} else {
													$errorMsg = "Error 14: Couldn't save new user into user table";
													$user = $username;	
													$session->stop();
												}
											}
										} else {
											$errorMsg = "Error 6: User not found in Photo Access database. Please register!";
											$user = $username;	
											$session->stop();
										}
									}
								}	
							} else {
								$connect = $adldap->connect();
								if ($connect['status'] == false) {
									$errorMsg = $connect["error"];
									$user = $username;
								} else {
									$existUser = $adldap->user()->infoCollection($username);
									if (empty($existUser)) {
										//echo "No existing";
										if ($options['allow_standard_access'] == "Y") {
											$security_password = $DATABASE_PASSWORD_FUNCTION($password);
											//echo "1. username: '".$username. "' - ".$security_password;
											DatabaseLogin($db, $username, $security_password);
										} else {
											$errorMsg = $authUser['error'];
											$user = $username;
											$session->stop();
										}						
									} else {
										//echo "Existing";
										$errorMsg = "Error 5: password is not match";
										$user = $username;
										$session->stop();
									}
								}
							}
						} else {
							//echo "missing<br>";
							if (isset($_POST["user"]) and empty($_POST["user"]) and isset($_POST["pass"]) and empty($_POST["pass"])) {
								$errorMsg = "Enter username and password";
								$user = "";
							} elseif (isset($_POST["user"]) and empty($_POST["user"])) {
								$errorMsg = "Error 1: Missing username";
								$user = "";
							} elseif (isset($_POST["pass"]) and empty($_POST["pass"])) {
								$errorMsg = "Error 2: Missing Password";
								$user = $username;
							}
						}
					}
				}
			}
		} else {
			$errorMsg = "Error : Couldn't load LDAP profile. Please contact Application Admin";
			$user = "";
		}
	} 
//}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/1999/REC-html401-19991224/loose.dtd">
<html>
	<head>
		<title>49er Login</title>
	</head>
	<link href="./../css/reset.css" rel="stylesheet" type="text/css">
	<link href="./../css/mainstyle.css" rel="stylesheet" type="text/css">
	<body style="background-color:#EEF3FA;">
	<div id="userlogin">
		<div class="loginbox">
			<div class="displayLogo" style="padding-left:1em";><center>
				<table width=95%>
					<tr>
						<td width=40%>
							<img src="./../images/logo-unc-charlotte-transparent.png" alt="UNC Charlotte Logo" >
						</td>
						<td width=60% style="vertical-align: middle; padding-left:2em;">
							Student Affairs
						</td>
					</tr>
				</table></center>
			</div>
			<form name="login" action="login.php" method="post">
				<table width=94%>
					<tr>
						<td style="padding-top: 0em; padding-bottom: .2em;">
						<div class="displayError">
						<?php
							if ($errorMsg != "") {
								echo "<center><font color=\"#ffc477\">".$errorMsg."</font></center>";
							}
						?>
						</div>
						</td>
					</tr>
					<tr>
						<td style="padding-top: 0.1em; padding-bottom: .1em; padding-left: 7em;"><font color="white">NinerNET username:</font></td>
					</tr>
					<tr>
						<td style="padding-top: .2em; padding-bottom: .1em; padding-left: 7em;">
							<input type="text" name="user" size=35 value="<?php if (isset($_POST["user"])) echo $user; ?>">
						</td>
					</tr>
					<tr>
						<td style="padding-top: .5em; padding-bottom: .1em; padding-left: 7em;"><font color="white">Password:</font></td>
					</tr>
					<tr>
						<td style="padding-top: .2em; padding-bottom: .1em; padding-left: 7em;">
							<input type="password" name="pass" size=35>
						</td>
					</tr>
					<tr>
						<td style="padding-top: 1em; padding-bottom: .3em; padding-left: 7em;">
							<input type="hidden" name="page_link" value="<?php if (isset($_GET["page_link"])) { echo $_GET["page_link"]; } elseif (isset($_POST["page_link"])) { echo $_POST["page_link"]; } ?>">
							<input type="submit" name="submit" value="Login" class="dark_green_button">
						</td>
					</tr>
					<tr>
						<td style="padding-top: 1em; padding-bottom: .3em; padding-left: 5em;">
							<table width=100%>
								<tr>
									<td width=50%>
										<?php
										if (isset($options) and $options['policy_renew'] == "Y")
											//echo "<a href=\"./../photo_access_policy.php?status=Database_Submit&self_approved=".$options['self_approved']."&policy_renew=".$options['policy_renew']."&authentication_type=".$options['authentication_type']."&allow_standard_access=".$options['allow_standard_access']."\" title=\"New User\"><span><font color=white>New User</font></span></a>";
											echo "<a href=\"./../photo_access_policy.php?status=Database_Submit\" title=\"New User\"><span><font color=white>New User</font></span></a>";

										else
											//echo "<a href=\"./../edit_user.php?status=Submit&self_approved=".$options['self_approved']."&policy_renew=".$options['policy_renew']."&authentication_type=".$options['authentication_type']."&allow_standard_access=".$options['allow_standard_access']."\" title=\"New User\"><span><font color=white>New User</font></span></a>";
											echo "<a href=\"./../admin/edit_user.php?action=Submit\" title=\"New User\"><span><font color=white>New User</font></span></a>";

										?>
									</td>
									<td style="text-align:right">
										<a href="./../forgot_password.php?" title="Forgot Password?"><span><font color=white>Forgot Password?</font></span></a>
									</td>
								</tr>
							</table>
						</td>
					</tr>
				</table>
			</form>
		</div>
	</div>
	</body>
</html>