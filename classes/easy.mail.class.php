<?php

/* 
this class is useful to send email with mail() function and or directly trough smtp server like smtp.gmail.com
author: usman didi khamdani
author's email: usmankhamdani@gmail.com
author's phone: +6287883919293
last updated: June 18, 2012
*/

class easyMail {

	// SMTP variables
	private $isSMTP = false;
	private $SMTPConn = false;
	private $SMTPDebug = false;
	private $SMTPDebugMessage = array();

	private $Host = 'localhost';				
	private $Port = 25;							
	private $Username = 'admin@localhost';		
	private $Password = 'admin';				
	private $Auth = '';							

	private $timeOut = 60;						
	private $Domain = 'localhost';				
	
	// mail variables
	private $To = array();
	private $Subject = '';

	private $From = '';
	private $ReplyTo = '';
	private $Cc = array();
	private $Bcc = array();

	private $Message = '';

	private $uploadedFile = array();
	private $Attachment = array();
	private $isAttachment = false;

	private $Header = '';

	private $errorMessage = array(); 

	// add smtp variables
	public function addSMTPHost($host) {			// smtp host server, i.e smtp.gmail.com for gmail
		$this->Host = $host;
	}

	public function addSMTPPort($port) {			// smtp port, i.e 587 for gmail.com
		$this->Port = $port;
	}

	public function addSMTPUsername($username) {	// email username
		$this->Username = $username;
	}

	public function addSMTPPassword($password) {	// email password
		$this->Password = $password;
	}

	public function addSMTPAuth($auth) {			// authentication type, enable for ssl, tls and none (i.e tls for gmail)
		$this->Auth = $auth;
	}

	public function addTimeOut($timeout) {			// connection timeout, in seconds
		$this->timeOut = $timeout;
	}

	public function addDomain($domain) {			// domain name where this app is running in, i.e localhost or mysite.com
		$this->Domain = $domain;
	}

	// add mail variables
	public function addTo($email_address,$name='') {
		return $this->addAddress('To',$email_address,$name);
	}

	public function addSubject($subject) {
		$this->Subject = strip_tags(trim($subject));
	}
	
	public function addFrom($email_address,$name='') {
		return $this->addAddress('From',$email_address,$name);
	}

	public function addReplyTo($email_address,$name='') {
		return $this->addAddress('ReplyTo',$email_address,$name);
	}

	public function addCc($email_address,$name='') {
		return $this->addAddress('Cc',$email_address,$name);
	}

	public function addBcc($email_address,$name='') {
		return $this->addAddress('Bcc',$email_address,$name);
	}

	private function addAddress($kind,$email_address,$name='') {
		// check kind of address
		if(!preg_match('/^(To|From|ReplyTo|Cc|Bcc)$/',$kind)) {
			$this->addError("Invalid recipient array: $kind");
			return false;
		}

		// check email address
		$email_address = strip_tags(trim($email_address));
		if($this->validateEmailAddress($email_address)===false) {
			$this->addError("Invalid email address: $email_address");
			return false;
		}

		$name = strip_tags(trim($name));

		if($name=='') {
			$address = $email_address;
		} else {
			$address = "$name <$email_address>";
		}

		if($kind=='From' || $kind=='ReplyTo') {
			$this->$kind = $address;
		} else {
			if(count($this->$kind)==0 || (count($this->$kind)>0 && !in_array($address,$this->$kind))) {
				array_push($this->$kind,$address);
			} else {
				return false;
			}
		}
	}

	public function validateEmailAddress($email_address) {
		if(function_exists('filter_var')) {
			if(filter_var($email_address,FILTER_VALIDATE_EMAIL)===false) {
				return false;
			} else {
				return true;
			}
		} else {
			return preg_match('/^[\.\w]+@([a-z\-]+\.)([a-z]{2,4})((\.[a-z]{2,4})*)$/',$email_address);
		}
	}

	public function addMessage($message) {
		if($message=="") {
			return false;
		}

		$this->Message .= $message;
	}

	public function addAttachment($file) {
		if(!file_exists($file)) {
			$this->addError("File: $file is not exist");
			return false;
		} else {
			if(count($this->uploadedFile)==0 || (count($this->uploadedFile)>0 && !in_array($file,$this->uploadedFile))) {
				array_push($this->uploadedFile, $file);
				$this->isAttachment = true;
			} else {
				return false;
			}
		}		
	}

	private function setAttachment() {
		if(count($this->uploadedFile)==0) {
			return false;
		} else {
			foreach($this->uploadedFile as $file) {
				$file_size = filesize($file);
				$handle = fopen($file, "r");
				$content = fread($handle, $file_size);
				fclose($handle);
				$content = chunk_split(base64_encode($content));
				$name = basename($file);

				if(function_exists('finfo_file')) {
					$finfo = finfo_open(FILEINFO_MIME_TYPE);
					$type = finfo_file($finfo, $file);
					finfo_close($finfo);
				} elseif(function_exists('mime_content_type')) {
					$type = mime_content_type($file);
				} else {
					$type = "application/octet-stream";
				}

				if(count($this->Attachment)==0 || (count($this->Attachment)>0 && !in_array($content,$this->Attachment))) {
					$this->addFile($name,$type,$content);
				}
			}
		}
	}

	private function addFile($name,$type,$content,$n=1) {
		if(array_key_exists($name,$this->Attachment)) {
			$n += 1;
			$info = pathinfo($name);
			// $file_name = $info['filename'].'-'.$n;
			$file_name = $info['filename'];
			if(preg_match('/\-[0-9]+$/',$file_name)) {
				$c = strlen(strrchr($file_name, "-"));
				$file_name = substr($file_name, 0, -$c);
			}
			$file_name .= "-$n";
			$file_ext = '.'.$info['extenstion'];

			$name = $file_name.$file_ext;
			$this->addFile($name,$type,$content,$n);
		} else {
			$this->Attachment[$name] = array($type,$content);
		}
	}

	private function incAttachment($boundary) {
		$this->setAttachment();

		if(count($this->Attachment)>0) {
			foreach($this->Attachment as $name => $attachment) {
				$type = $attachment[0];
				$content = $attachment[1];
				
				$this->Header .= "\r\n--$boundary\r\n";
				$this->Header .= "Content-Type: $type; name=\"$name\"\r\n";
				$this->Header .= "Content-Disposition: attachment; filename=\"$name\"\r\n";
				$this->Header .= "Content-Transfer-Encoding: base64\r\n";
				$this->Header .= "\r\n$content\r\n";
			}
			return $this->Header;
		} else {
			return false;
		}
	}

	// error handling
	private function addError($message) {
		// $timestamp = time();
		$timestamp = date("r");
		$message = "$timestamp|[ERROR] $message";
		array_push($this->errorMessage, $message);

		$file = @fopen("log/mail-error-".date("Ymd").".log", "a");
		@fwrite($file, str_replace("|","\t",$message)."\r\n");
		@fclose($file);
	}

	public function errorReport() {
		return $this->errorMessage;
	}

	public function SMTPDebugReport() {
		if($this->SMTPDebug==true) {
			$this->SMTPDebugMessage = $this->SMTPConn->debugReport();
		}

		return $this->SMTPDebugMessage;
	}
	
	public function Send($isSMTP=0) {
		if($this->From=='') { // check mail sender
			$this->addError("Message is not sent. Mail sender is not defined");
			return false;
		} elseif(count($this->To)==0) { // check mail recipient
			$this->addError("Message is not sent. Recipient is not defined");
			return false;
		} elseif($this->Subject=='') { // check mail subject
			$this->addError("Message is not sent. Subject is not defined");
			return false;
		} elseif($this->Message=='') { // check mail message
			$this->addError("Message is not sent. Message is not defined");
			return false;
		} else {
			// set sender
			$From = $this->From;

			if($this->ReplyTo=='') {
				$ReplyTo = $this->From;
			} else {
				$ReplyTo = $this->ReplyTo;
			}

			// set recipient
			if(count($this->To)==1) {
				$To = $this->To[0];
			} else {
				$To = implode(", ",$this->To);
			}

			// set cc
			if(count($this->Cc)>0) {
				if(count($this->Cc)==1) {
					$Cc = $this->Cc[0];
				} else {
					$Cc = implode(", ",$this->Cc);
				}
			} else {
				$Cc = '';
			}

			// set bcc
			if(count($this->Bcc)>0) {
				if(count($this->Bcc)==1) {
					$Bcc = $this->Bcc[0];
				} else {
					$Bcc = implode(", ",$this->Bcc);
				}
			} else {
				$Bcc = '';
			}

			// set subject
			$Subject = $this->Subject;

			// set message
			$TextMessage = nl2br(strip_tags($this->Message));
			$HTMLMessage = "<!doctype html>\r\n<html>\r\n\t<head>\r\n\t\t<title>$Subject</title>\r\n\t</head>\r\n\t<body>\r\n\t\t".$this->Message."\r\n\t</body>\r\n</html>";
			
			// set unique id
			$uid = md5(date('r', time()));

			// sending mail
			if($isSMTP==1) {
				include_once("./classes/smtp.mail.class.php");
				$this->SMTPConn = new SMTPMail($this->Domain,$this->Host,$this->Username,$this->Password,$this->Auth,$this->Port,$this->timeOut);

				if($this->SMTPConn === false) {
					return false;
				} else {
					$this->SMTPDebug = true;

					$send = $this->SMTPConn->Send($Subject,$this->Message,$this->To,$this->From,$this->ReplyTo,$this->Cc,$this->Bcc,$this->uploadedFile);
					if($send === false) {
						$this->addError("Message is not sent. Please try again");
						return false;
					}
				}
			} else {
				ob_start();
				$Headers = "From: $From\r\n";
				$Headers .= "Reply-To: $ReplyTo\r\n";

				if($Cc!='') {
					$Headers .= "Cc: $Cc\r\n";
				}

				if($Bcc!='') {
					$Headers .= "Bcc: $Bcc\r\n";
				}

				$Headers .= "MIME-Version: 1.0\r\n";
				
				if($this->isAttachment==true) {
					$Headers .= "Content-Type: multipart/mixed; boundary=\"PHP-mixed-$uid\"\r\n"; // start multipart/mixed

					$Headers .= "\r\n--PHP-mixed-$uid\r\n"; // part of multipart/mixed
				}
			
					$Headers .= "Content-Type: multipart/alternative; boundary=\"PHP-alt-$uid\"\r\n"; // start multipart/alternative
				
					// text message
					$Headers .= "\r\n--PHP-alt-$uid\r\n"; // part of multipart/alternative
						$Headers .= "Content-Type: text/plain; charset=\"utf-8\"\r\n";
						$Headers .= "Content-Transfer-Encoding: 8bit\r\n";
						$Headers .= "\r\n$TextMessage\r\n";
				
					// html message
					$Headers .= "\r\n--PHP-alt-$uid\r\n"; // part of multipart/alternative
						$Headers .= "Content-Type: text/html; charset=\"utf-8\"\r\n";
						$Headers .= "Content-Transfer-Encoding: 8bit\r\n";
						$Headers .= "\r\n$HTMLMessage\r\n";

					$Headers .= "\r\n--PHP-alt-$uid--\r\n"; // finish multipart/alternative

				if($this->isAttachment==true) {
					$Headers .= $this->incAttachment("PHP-mixed-$uid");

					$Headers .= "\r\n--PHP-mixed-$uid--\r\n"; // finish multipart/mixed
				}
				ob_end_flush();

				$send = @mail($To, $Subject, "", $Headers);
				if(!$send) {
					$this->addError("Message is not sent. Please try again");
					return false;
				}
			}
		}
	}
}

?>