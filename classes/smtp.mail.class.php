<?php

/* 
this class is useful to send email directly trough smtp server like smtp.gmail.com. enable for secure connection using ssl or tls
this class is inspired by and rewritten from PHPMailer (http://code.google.com/a/apache-extras.org/p/phpmailer/) and Simple SMTP Class for PHP (http://www.kidmoses.com/blog-article.php?bid=56)
writer: usman didi khamdani
writer's email: usmankhamdani@gmail.com
writer's phone: +6287883919293
last updated: June 18, 2012

 **********************************************************************************************************
* updater: Bach Nguyen (bnguye21@uncc.edu
* last updated: 11/18/2013
* what changed:  replaced if(substr($this->getResponse("STARTTLS"),0,3) !='220') or similar statements by:
*    $responseStatus = substr($this->getResponse("STARTTLS"),0,3);
* 	 if($responseStatus !=' 220')
* why: 	longer responding in both Windows and openSUSE linux
 **********************************************************************************************************
*/

class SMTPMail {
	
	private $transmissionID;
	private $Host;
	private $Port;
	private $Username;
	private $Password;
	private $Auth;

	private $SMTP;
	private $timeOut;
	private $Domain;

	private $uploadedFile = array();
	private $Attachment = array();
	private $isAttachment = false;

	private $isLogin = false;
	private $debugMessage = array();

	public function __construct($Domain,$Host,$Username,$Password,$Auth='',$Port=25,$timeOut=60) {

		/* ############################################################################################### /*
		#####																							#####
		####	$Domain		=> domain name where this app is running in, i.e localhost or mysite.com     ####
		###		$Host		=> smtp host server, i.e smtp.gmail.com                                       ###
		##		$Username	=> mail id, i.e mymail@gmail.com or another.mail@mysite.com                    ##
		##		$Password	=> mail password                                                               ##
		##		$Auth		=> authentication type, enable for ssl, tls and none (left it blank if none)   ##
		###		$Port		=> smtp port, i.e 587 for gmail.com                                           ###
		####	$timeOut	=> connection timeout, in seconds                                            ####
		#####																							#####
		*/ ############################################################################################### */

		$this->transmissionID = md5(date('r', time()));
		
		$this->Host = strtolower(trim($Host));
		$this->Port = $Port;
		$this->Username = trim($Username);
		$this->Password = trim($Password);
		$this->Auth = strtolower(trim($Auth));
		$this->timeOut = $timeOut;
		$this->Domain = trim($Domain);

		if($this->Connect()===false) {
			return false; 
		} else {
			if($this->Login()===false) {
				return false;
			} else {
				$this->isLogin = true;
			}
		}
	}

	private function Connect() {
		$this->startTransmission();
		$this->addDebug("[TRY] Attempting to connect ...\r\n");
		if($this->Auth == 'ssl') {
			if(extension_loaded('openssl')) {
				$this->Host = 'ssl://'.$this->Host;
			} else {
				$this->addDebug("[REJECTED] Connection has been rejected. This app needs the Open SSL PHP extension (SSL issue)\r\n");
				return false; 
			}
		}
		$this->SMTP = @fsockopen($this->Host, $this->Port, $errno, $errstr, $this->timeOut);
		//echo "<pre>";
		//	print_r($this);
		//echo "</pre>";	
		
		$responseStatus = substr($this->getResponse("fsockopen"),0,3);
		//echo "<br>---'".$responseStatus."'---<br>";
		//exit();
		if ($responseStatus != '220') {
			$this->addDebug("[REJECTED] Connection has been rejected by SMTP Host\r\n");
			//echo "Here 1";
			//exit();
			return false; 
		} else {
			$this->addDebug("[OPENED] Connection has been opened\r\n");
			//echo "Here 2";
			//exit();
			return true;
		}
	}

	private function Login() {
		$this->addDebug("[TRY] Attempting to login ...\r\n");
		$this->sendRequest("EHLO ".$this->Domain);
		//echo "domain: ".$this->Domain."<br>";
		$this->getResponse("SEND HELLO MESSAGE");

		if($this->Auth == 'tls') {
			//echo "tls<br>";
			if(extension_loaded('openssl')) {
				//echo "openssl<br>";
				$this->sendRequest("STARTTLS");
				$responseStatus = substr($this->getResponse("STARTTLS"),0,3);
				//echo "data: ".$responseStatus."<br>";
				//exit();
				if ($responseStatus !='220') {
					$this->addDebug("[REJECTED] Connection has been rejected. Can't start TLS\r\n");
					$this->Reset();
					return false; 
				} else {
					//echo "go 1<br>";
					
					@stream_socket_enable_crypto($this->SMTP, true,STREAM_CRYPTO_METHOD_TLS_CLIENT);
					$this->sendRequest("EHLO ".$this->Domain); //EHLO 
					//echo "go 2<br>";
					//$this->getResponse("SEND HELLO MESSAGE");
					$responseStatus = substr($this->getResponse("SEND HELLO MESSAGE"),0,3);
					//echo "data: ".$responseStatus."<br>";
					if ($responseStatus != '250') { 
						return false; 
					} else {
						if($this->Host == 'localhost') {
							$this->addDebug("[SUCCESS] Login accepted\r\n");
							return true;
						} else {
							//echo "here 2<br>";
							$this->sendRequest("AUTH LOGIN");
							$responseStatus = substr($this->getResponse("AUTH LOGIN"),0,3);
							if($responseStatus != '334') {
								$this->addDebug("[REJECTED] Connection has been rejected. Authentication failed\r\n");
								$this->Reset();
								return false; 
							} else {
								// hashing username & password
								$username = base64_encode($this->Username);
								$password = base64_encode($this->Password);

								$this->sendRequest($username);
								$responseStatus = substr($this->getResponse("AUTH Username"),0,3);
								if ($responseStatus != '334') { 
									$this->addDebug("[REJECTED] Connection has been rejected (SMTP Username issue)\r\n");
									$this->Reset();
									return false; 
								} else {
									$this->sendRequest($password);
									$responseStatus = substr($this->getResponse("AUTH Password"),0,3);
									if($responseStatus != '235') { 
										$this->addDebug("[REJECTED] Connection has been rejected (SMTP Password issue)\r\n");
										$this->Reset();
										return false; 
									} else {
										$this->addDebug("[SUCCESS] Login accepted\r\n");
										return true;
									}
								}
							}
						}
					}
				}
			} else {
				$this->addDebug("[REJECTED] Connection has been rejected. This app needs the Open SSL PHP extension (TLS issue)\r\n");
				$this->Reset();
				return false; 
			}
		} else {
			$this->addDebug("[SUCCESS] Login accepted\r\n");
			return true;
		}
    }

	public function Send($subject,$message,$to,$from,$replyto='',$cc='',$bcc='',$attachment='') {

		/* ######################################################################################################## /*
		#####                                                                                                    #####
		####	$subject		=> message subject (string)												          ####
		###		$message		=> message content (string)			                                               ###
		##		$to				=> message recipient (string or array)                                              ##
		##		$from			=> message sender (string)                                                          ##
		##		$replyto		=> reply message recipient (string)                                                 ##
		##		$cc				=> message "carbon copy" (cc) recipient (string or array)                           ##
		##		$bcc			=> message "blind carbon copy" (bcc) recipient (string or array)                    ##
		##		$attachment		=> attachment file(s), use include_path configuration directive (string or array)   ##
		###                                                                                                        ###
		####	note: use "User Name <user@domain.com>" or user@domain.com format for writing email address       ####
		#####                                                                                                    #####
		*/ ######################################################################################################## */

		if($this->transmissionID=='') {
			return false;
		}

		$this->addDebug("[TRY] Attempting to send message ".$this->transmissionID." ...\r\n");
		if($this->isLogin==false) {
			$this->addDebug("[FAILED] You need to login to SMTP Host\r\n");
			$this->addDebug("[MAIL STAT] Failed: Message ".$this->transmissionID." is not sent\r\n");
			return false;
		} 
		if($this->setFrom($from)==false) {
			$this->addDebug("[FAILED] Mail Sender is not accepted\r\n");
			$this->addDebug("[MAIL STAT] Failed: Message ".$this->transmissionID." is not sent\r\n");
			$this->Reset();
			return false;
		} else {
			if($this->setRecipient($to,"To")==false) {
				$this->addDebug("[FAILED] Recipient is not accepted\r\n");
				$this->addDebug("[MAIL STAT] Failed: Message ".$this->transmissionID." is not sent\r\n");
				$this->Reset();
				return false;
			} else {
				$this->setRecipient($cc,"Cc");
				$this->setRecipient($bcc,"Bcc");
				if($this->sendData($subject,$message,$to,$from,$replyto,$cc,$bcc,$attachment)==false) { 
					$this->addDebug("[FAILED] Data is not accepted\r\n");
					$this->addDebug("[MAIL STAT] Failed: Message ".$this->transmissionID." is not sent\r\n");
					$this->Reset();
					return false;
				} else {
					$responseStatus = substr($this->getResponse("DATA - Send"),0,3);
					if ($responseStatus != '250') {
						$this->addDebug("[MAIL STAT] Failed: Message ".$this->transmissionID." is not sent\r\n");
						//$this->getResponse("");
						$this->Disconnect();
						return false; 
					} else {
						$this->addDebug("[MAIL STAT] Success: Message ".$this->transmissionID." has been sent\r\n");
						//$this->getResponse("");
						$this->Disconnect();
						return true;
					}
				}
			}
		}
	}

	private function setFrom($email) {
		if($email=='' || $this->emailAddress($email)==false) {
			return false;
		} else {
			$this->sendRequest("MAIL FROM: <".$this->emailAddress($email).">");
			$this->getResponse("SET MAIL FROM: <".$this->emailAddress($email).">");
			return true;
		}
	}

	private function setRecipient($email,$kind) {
		if($email=='' || !preg_match('/^(To|Cc|Bcc)$/',$kind)) {
			return false;
		} else {
			$recipient = 0;
			if($kind=='To') {
				$rcpt = "RECIPIENT";
			} else {
				$rcpt = strtoupper($kind);
			}

			if(is_array($email)) {
				foreach($email as $email) {
					if($this->emailAddress($email)!=false) {
						$this->sendRequest("RCPT TO: <".$this->emailAddress($email).">");
						$this->getResponse("SET $rcpt TO: <".$this->emailAddress($email).">");
						$recipient += 1;
					}
				}
			} else {
				if($this->emailAddress($email)!=false) {
					$this->sendRequest("RCPT TO: <".$this->emailAddress($email).">");
					$this->getResponse("SET $rcpt TO: <".$this->emailAddress($email).">");
					$recipient += 1;
				}
			}
			if($recipient==0) {
				return false;
			} else {
				return true;
			}
		}
	}

	public function addAttachment($file) {
		if(!file_exists($file)) {
			$this->addDebug("[DATA - UPLOAD ATTACHMENT FILE] Error: File $file is not exists\r\n");
			return false;
		} else {
			if(count($this->uploadedFile)==0 || (count($this->uploadedFile)>0 && !in_array($file,$this->uploadedFile))) {
				array_push($this->uploadedFile, $file);
				$this->isAttachment = true;
				$this->addDebug("[DATA - UPLOAD ATTACHMENT FILE] Success: File $file has been uploaded\r\n");
			} else {
				$this->addDebug("[DATA - UPLOAD ATTACHMENT FILE] Error: File $file has been uploaded\r\n");
				return false;
			}
		}		
	}

	private function setAttachment() {
		if(count($this->uploadedFile)==0) {
			$this->addDebug("[DATA - GENERATE ATTACHMENT FILE] Error: There is no attachment file\r\n");
			return false;
		} else {
			$this->addDebug("[DATA - GENERATE ATTACHMENT FILE] Attempting to generate attachment file ...\r\n");
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

				if($file_size >= (1024*1024) ) {
					$size = round(($file_size / (1024*1024)),2)." MB";
				} elseif($file_size >= 1024 ) {
					$size = round(($file_size / 1024),2)." KB";
				} else {
					$size = $file_size." B";
				}
				
				$this->addDebug("[DATA - GENERATE ATTACHMENT FILE] Success: File $name ($size) has been generated\r\n");				
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
			$this->addDebug("[DATA - INCLUDE ATTACHMENT FILE] Attempting to include attachment file ...\r\n");
			$AttachmentPackage = '';
			foreach($this->Attachment as $name => $attachment) {
				$type = $attachment[0];
				$content = $attachment[1];
				
				$AttachmentPackage .= "\r\n--$boundary\r\n";
				$AttachmentPackage .= "Content-Type: $type; name=\"$name\"\r\n";
				$AttachmentPackage .= "Content-Disposition: attachment; filename=\"$name\"\r\n";
				$AttachmentPackage .= "Content-Transfer-Encoding: base64\r\n";
				$AttachmentPackage .= "\r\n$content\r\n";

				$this->addDebug("[DATA - INCLUDE ATTACHMENT FILE] Success: File $name has been included\r\n");
			}
			return $AttachmentPackage;
		} else {
			return false;
		}
	}

	private function sendData($subject,$message,$to,$from,$replyto,$cc,$bcc,$attachment) {
		$subject = trim($subject);
		$message = trim($message);

		if($from=='' || $to=='' || $subject=='' || $message=='') {
			return false;
		} else {
			// set message
			$TextMessage = nl2br(strip_tags($message));
			$HTMLMessage = "<!doctype html>\r\n<html>\r\n\t<head>\r\n\t\t<title>$subject</title>\r\n\t</head>\r\n\t<body>\r\n\t\t$message\r\n\t</body>\r\n</html>";

			// set attachment
			if($attachment!='') {
				if(is_array($attachment)) {
					$this->addDebug("[DATA - GET ATTACHMENT FILE] Attempting to get attachment file ...\r\n");
					if(count($attachment)>0) {
						foreach($attachment as $attachment) {
							$this->addAttachment($attachment);
						}
					}
				} else {
					$this->addDebug("[DATA - GET ATTACHMENT FILE] Attempting to get attachment file ...\r\n");
					$this->addAttachment($attachment);
				}
			}

			if(is_array($to)) {
				if(count($to)==1) {
					$to = $to[0];
				} else {
					$to = implode(", ",$to);
				}
			}
			if($replyto=='') {
				$replyto = $from;
			}

			if(is_array($cc) && count($cc)>0) {
				if(count($cc)==1) {
					$cc = $cc[0];
				} else {
					$cc = implode(", ",$cc);
				}
			} else {
				$cc = '';
			}

			if(is_array($bcc) && count($bcc)>0) {
				if(count($bcc)==1) {
					$bcc = $bcc[0];
				} else {
					$bcc = implode(", ",$bcc);
				}
			} else {
				$bcc = '';
			}

			$this->addDebug("[DATA - GENERATE] Attempting to generate mail data ...\r\n");
			ob_start();
			$message = "Date: ".date('r')."\r\n";
			$message .= "From: $from\r\n";
			$message .= "Reply-To: $replyto\r\n";
			$message .= "To: $to\r\n";
			if($cc!='') {
				$message .= "Cc: $cc\r\n";
			}
			if($bcc!='') {
				$message .= "Bcc: $bcc\r\n";
			}
			$message .= "Subject: $subject\r\n";
			$message .= "MIME-Version: 1.0\r\n";

			if($this->isAttachment==true) {
				$message .= "Content-Type: multipart/mixed; boundary=\"PHP-mixed-".$this->transmissionID."\"\r\n"; // start multipart/mixed

				$message .= "\r\n--PHP-mixed-".$this->transmissionID."\r\n"; // part of multipart/mixed
			}
			
				$message .= "Content-Type: multipart/alternative; boundary=\"PHP-alt-".$this->transmissionID."\"\r\n"; // start multipart/alternative
				
				// text message
				$message .= "\r\n--PHP-alt-".$this->transmissionID."\r\n"; // part of multipart/alternative
					$message .= "Content-Type: text/plain; charset=\"utf-8\"\r\n";
					$message .= "Content-Transfer-Encoding: 8bit\r\n";
					$message .= "\r\n$TextMessage\r\n";
				
				// html message
				$message .= "\r\n--PHP-alt-".$this->transmissionID."\r\n"; // part of multipart/alternative
					$message .= "Content-Type: text/html; charset=\"utf-8\"\r\n";
					$message .= "Content-Transfer-Encoding: 8bit\r\n";
					$message .= "\r\n$HTMLMessage\r\n";

				$message .= "\r\n--PHP-alt-".$this->transmissionID."--\r\n"; // finish multipart/alternative

			if($this->isAttachment==true) {
				$message .= $this->incAttachment("PHP-mixed-".$this->transmissionID);

				$message .= "\r\n--PHP-mixed-".$this->transmissionID."--\r\n"; // finish multipart/mixed
			}

			$message .= ".\r\n";
			ob_end_flush();

			$this->addDebug("[DATA - GENERATE] Success: Mail data has been generated\r\n");

			$this->sendRequest("DATA");
			$this->getResponse("DATA - CHECK");
			$this->sendRequest($message);

			return true;
		}
	}

	public function emailAddress($email) {
		if(substr_count($email,"<")>0) {
			$email = str_replace(">","",str_replace("<","",strstr($email,"<")));
		}
		if($this->validateEmailAddress($email)==false) {
			return false;
		} else {
			return $email;
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

	private function startTransmission() {
		$message = "Start Transmission\r\n";
		$message .= "ID: ".$this->transmissionID."\r\n";
		$message .= "Start Time: ".date('r')."\r\n\r\n";

		$this->writeLog($message);
	}

	private function closeTransmission() {
		$message = "\r\nEnd Transmission\r\n";
		$message .= "ID: ".$this->transmissionID."\r\n";
		$message .= "Finish Time: ".date('r')."\r\n\r\n";
		$message .= "--------------------------------------------------\r\n\r\n";

		$this->writeLog($message);
		
		$this->transmissionID = '';
	}

	private function Reset() {
		$this->sendRequest("RSET");
		$this->getResponse("RESET");
		$this->Disconnect();
	}

	private function Disconnect() {
		$this->sendRequest("QUIT");
		$this->getResponse("QUIT");
		@fclose($this->SMTP);
		$this->addDebug("[CLOSED] Connection has been closed\r\n");

		$this->closeTransmission();
	}

	private function sendRequest($request) {
		@fputs($this->SMTP, $request."\r\n");
		// $this->addDebug("[REQUEST] $request\r\n");
	}

	private function getResponse($request) {
		$request = strtoupper($request);
		$message = "";
		while($data = @fgets($this->SMTP)) {
			$message .= $data;
			if(substr($data,3,1) == " ") { 
				break; 
			}
		}
		if($message == "") { 
			$message = "Error Response\r\n";
		} 
		$this->addDebug("[$request] $message");
		return $message;
	}

	private function addDebug($message) {
		// $timestamp = time();
		$timestamp = date("r");
		$message = "$timestamp|$message";
		array_push($this->debugMessage, $message);

		$this->writeLog(str_replace("|","\t",$message));
	}

	public function debugReport() {
		return $this->debugMessage;
	}

	private function writeLog($data) {
		$open_file = @fopen("log/smtp-".date("Ymd").".log", "a");
		if($open_file) {
			@fwrite($open_file, $data);
			@fclose($open_file);
		} else {
			return false;
		}
	}

}

?>