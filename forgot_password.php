<?php
/*
 *******************************************************************************************************
*
* Name: forgot password.php
* Reset password page for 49ers ID and database ID
* Writer: Bach Nguyen (bnguye21@uncc.edu)
* Last Updated: 05/26/2014
*
 ********************************************************************************************************
 */
include ("./web/header.html");
$errorMsg = "";
$pos = 0;
if (!empty($_POST["email_address"])) {
	$email_address = $_POST["email_address"];
	$pos = stripos($email_address, "uncc.edu");
	//echo "find: ".$pos."<br>";
	if ($pos > 0)
		$errorMsg = "No reset password for 49ers account.  Click 49ers Account link for reset password";
} else {
	$errorMsg = "Please enter 'Email Address' before clicking Submit";
}
if (!empty($_POST["submit"])) {
	$submit = $_POST["submit"];
} else $submit = "";

function rand_string( $length ) {
    $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
    return substr(str_shuffle($chars),0,$length);
}
$randomPassword = rand_string(8);

function sendEmail($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_DB, $receiver_id, $replySubject, $message) {
	// Get email config
	$queryString = "select * from photo_access_config where left(`key`, 6) = 'email_'";
	$arrayMailConfig = queryAllInfo ($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_DB, $queryString);
	//echo "<pre>";
	//	print_r($arrayMailConfig);
	//echo "</pre>";
	$emailOptions = array();
	foreach ($arrayMailConfig as $row=>$arrayValue) {
		foreach ($arrayValue as $key=>$value) {
			//echo $key. " = " .$value."<br>";
			if ($key != "id") {
				if ($key == "key") {
					$key_name = substr($value,6);
				}
				if ($key == "value") {
					$real_value = $value;
				} else $real_value = "";
				$emailOptions[$key_name] = $real_value;
			}
		}
	}		
	//echo "<pre>";
	//	print_r($emailOptions);
	//echo "</pre>";
	if (!empty($emailOptions['from'])) {
		$queryString = "select * from photo_access_users where active='Y' and user_id='".$emailOptions['from']."'";
		$arraySendFrom = queryAllInfo ($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_DB, $queryString);
		//echo "<pre>";
		//	print_r($arraySendFrom);
		//echo "</pre>";
		$from = "{$arraySendFrom[0]['first_name']} {$arraySendFrom[0]['last_name']} <{$arraySendFrom[0]['email']}>";
	} else {
		$from = "No-Reply Email <system@".$emailOptions['site_domain'].">";
	}
	//echo "from: ".$from;
	// Get Patron ID
	$queryPatronString = "select * from `photo_access_users` where email='".$receiver_id."'";
	$arrayPatron = queryAllInfo ($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_DB, $queryPatronString);
	//echo "<pre>";
	//	print_r($arrayPatron);
	//echo "</pre>";
	$to = array();
	foreach ($arrayPatron as $contact) {
		$to[] = $contact['first_name']." ".$contact['last_name']." <".$contact['email'].">";
	}
	//echo "<pre>";
	//	print_r($to);
	//echo "</pre>";

	//$sendMail_type = "Database";
	if ($emailOptions['transport'] == "SMTP") {
		//echo "<br>Sending mail using SMTP<br>";
		date_default_timezone_set('America/New_York');
		include_once("./classes/smtp.mail.class.php");
		if ($emailOptions['enable_tls'] == "Y") {
			$smtp_Encryption = "ssl";
		} else {
			$smtp_Encryption = "tls";
		}
		//$email = new SMTPMail($emailOptions['site_domain'],"smtp.gmail.com","atkins.autoreply@gmail.com","DIA4MailReply","tls",587);  //"tls",587  "ssl",465
		$email = new SMTPMail($emailOptions['site_domain'],$emailOptions['smtp_host'],$emailOptions['smtp_username'],$emailOptions['smtp_password'],$smtp_Encryption,$emailOptions['smtp_port']);  
		$replyto = $from;
		$cc = "";
		$bcc = "";
		$subject = $replySubject;//"{$emailOptions['prefix']}-Special Collection - Mail System"; //"New Test Email ".date("r");
		$attachment = "";
		//$attachment = array(
		//				"./upload/ssebook-july-kbart.txt",
		//				"./upload/IEEE_Xplore_2013-kbart.txt"
		//			);
		$send = $email->Send($subject,$message,$to,$from,$replyto,$cc,$bcc,$attachment);
		if($send===false) {
			//echo "Email is not sent<br /><br />";
			include ("./classes/logging.class.php");
			$log = new Logging();
			// set path and name of log file (optional)
			//$log->lfile('C:/logs/mylog.txt');
			// write message to the log file
			$log->lwrite('Email did not send');
			// close log file
			$log->lclose();
			return false;
		} else {
			//echo "Email was sent!";
			return true;
		} 
	} else {
		//echo "<br>Sending mail using PHP Default<br>";
		include "./classes/libmail.php";
		$m = new Mail; // create the mail
		//$m->From ("Library Support ERMS<support.library@uncc.edu>");
		$m->From ($from);
		//$m->To ("Bach Nguyen <bnguye21@uncc.edu>");
		//$m->To ("Support Library <support.library@uncc.edu>");
		$m->To($to);
		//$m->Cc ("Support Library <support.library@uncc.edu>");
		//$m->Bcc ("atkins245@gmail.com");
		$m->Subject($replySubject) ;//("{$emailOptions['prefix']}-Special Collection - Mail System");
		//$text_content = "Hello";
		//$m->Body ($text_content);
		$m->Html ($message);
		// set the body
		$m->Priority (3) ;	// set the priority to normal - 1 is highest
		//$m->Attach ("./upload/ssebook-july-kbart.txt", "text/html", "inline") ;	// attach a file of type image/gif to be displayed in the message if possible
		//$m->Send ();	// send the mail
		$errorSend = $m->Send ();
		//echo "ErrorSend: ".$errorSend."<br>";
		if ($errorSend) {
			//echo "Mail was sent:<br><pre>", $m->Get (), "</pre>";
			return true;
		} else {
			//echo "Email was not send!";
			include ("./classes/logging.class.php");
			$log = new Logging();
			// set path and name of log file (optional)
			//$log->lfile('C:/logs/mylog.txt');
			// write message to the log file
			$log->lwrite('Email did not send');
			// close log file
			$log->lclose();
			return false;
		}
	}
}
if (!empty($email_address) and $pos === false and !empty($submit)) {
	include("./install/database_credentials.inc");
	include("./photo_access_sql.php");
	$databaseConnect = connectDatabase($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_DB);
	//echo "<pre>";
	//	print_r($databaseConnect);
	//echo "</pre>";	
	if ($databaseConnect["status"] == "false") {
		$errorMsg = $databaseConnect["errorMsg"];
	} else {
		if ($email_address != "" and ($errorMsg == "" or strstr($errorMsg, "Success"))) {
			$queryString = "SELECT * FROM photo_access_users where email='".$email_address."'";
			//echo "querySearchString: ".$querySearchString."<br>";
			$arrayUser = queryAllInfo ($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_DB, $queryString);
		
			if (!empty($arrayUser['errorMsg'])) {
				$errorMsg = $arrayUser['errorMsg'];
			} elseif (sizeof($arrayUser) <= 0) {
				$errorMsg = "Error!  Can't find email address you entered. Try again!";
			} else {
				$updateString = "password='".sha1($randomPassword)."'";
				$updatePatronString = "UPDATE photo_access_users set $updateString where email='".$email_address."'";

				$updateRecordStatus = addNewRecord($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_DB, $updatePatronString);
				//echo "<pre>";
				//	print_r($updateRecordStatus);
				//echo "</pre>";
				if ($updateRecordStatus['status'] == "true") {
					$replySubject = "Reset Password for email account: ".$email_address;
					$message = "
					<html>
						<head>
							<title>Photo Access ID - Reset Password</title>
						</head>
						<body>
							<p>Here is your temporary password: ".$randomPassword."</p>
							<div style=\"width:100%; font-family:Arial, Helvetica,sans-serif;font-size:12px;line-height:12px; margin: 0px auto;background-color:white\">
							<table>
								<tr>
									<td>If you have any questions, please contact us.</td>
								</tr>
							</table>
							</div>
						</body>
					</html>";	
					$sendResult = sendEmail($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_DB, $email_address, $replySubject, $message);
					//echo "send : '".$sendResult."'<br>";
					if ($sendResult)	
						$errorMsg = "Password reset was sent to you email. Check your email.";
					else
						$errorMsg = "Please contact Patron admin.  Can't send email.";
				} else {
					$errorMsg = $updateRecordStatus["errorMsg"];
				}
			}
		}	
	}
}

?>
<script Language="JavaScript">
	function trim(stringToTrim) {
		return stringToTrim.replace(/^\s+|\s+$/g,"");
	}
		
	function checkValidator(theForm) {
		var email_address = trim(document.getElementById('email_address').value);
		if (email_address == "") {
			document.getElementById("errorMsg").innerHTML = "Please enter 'Email Address' before clicking Submit";
			theForm.email_address.style.backgroundColor="#D0E2ED";
			theForm.email_address.focus();
			return (false);
		} else if (email_address.indexOf("uncc.edu") != -1) {
			document.getElementById("errorMsg").innerHTML = "No reset password for 49ers account.  Click 49ers Account link for reset password";
			theForm.email_address.style.backgroundColor="#D0E2ED";
			theForm.email_address.focus();
			return (false);		
		}
	}
	
</script>	
			
<link href="./css/mainstyle.css" rel="stylesheet" type="text/css">
<div style="width:80%; font-family:Arial, Helvetica, sans-serif;font-size:12px;line-height:18px; border: 0px solid black; margin: 0px auto;background-color:white">
	<form action="forgot_password.php" method="post" onsubmit="return checkValidator(this)" name="mainForm">
	<table  width=100% style="padding:5px; text-align:center; background-color:#E3E3E3;">
		<tr>
			<td colspan="2"><br><br>
				<div class="mainTitle"><strong><center>Photo Access ID - Reset Password</center></strong></div><br>
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
		<tr>
			<td colspan="2" style="padding-left: 15em"><br>
				For 49ers Account - click <a href="https://pwmanager.uncc.edu/idm/user/login.jsp" title="Reset Password">here</a><br><br>
			</td>
		</tr>
		<tr>
			<td colspan="2" style="padding-left: 15em">
				For Photo Access ID User - Enter the original e-mail address that you used to create your account
			</td>
		</tr>
		<tr>
			<td width=35% style="padding-left: 20em; padding-top: 1em">
				Email Address:
			</td>
			<td style="padding-top: 1em">
				<input type="text" size="64" maxlength="128" name="email_address" id="email_address" value="<?php if (!empty($email_address)) echo $email_address; ?>">
			</td>	
		</tr>
		<tr>
			<td colspan="2"><br><br><center>
				<input type="submit" name="submit" value="Submit" class="dark_green_button"></center><br><br>
			</td>
		</tr>		
	</table>
	</form>
</div>
<?php include ("./web/footer.html"); ?>