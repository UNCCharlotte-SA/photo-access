<?php
/*
 *******************************************************************************************************
*
* Name: import_file_manual_handler.php
* Import patron information such as id, last name, first name, gender....
* Writer: Bach Nguyen (bnguye21@uncc.edu)
* Last Updated: 02/20/2015
*	02/20/2015
*		- Changes database class, session class
*		- Added ability not to save picture into database
*	01/22/2015
*		- Added delect pictures file (query from patron_pic then unlink)
*	01/21/2015
*		- Fixed codes for importing image
*	01/08/2015
*		- Added pic_image into array
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

	//echo "Here session: ".$_SESSION['photo_access']['login']['session_id']." - scode: ".$security_code." - sectime: ".$session_lifetime;
	//print_r('<pre>');
	//print_r($session->get_settings());
	//print_r('</pre>'); 

	if (isset($_SESSION['photo_access']['login']['username']))  {
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
	
		ini_set('memory_limit', '-1'); 
		ini_set ('max_execution_time','-1');
		date_default_timezone_set($time_zone);
	
		$uploadFilename = $_FILES["uploadFilename"]["name"];
		$uploadFileFormat = $_POST["uploadFileFormat"];
		$upLoadFileHeader = (!empty($_POST["isHeader"]) ? $_POST["isHeader"]:"No");
		$import_To_Photo_Access = (!empty($_POST["import_To_Photo_Access"]) ? $_POST["import_To_Photo_Access"]:"No");
		$downloadFileNameOriginal = $_POST["downloadFilename"];
		$downloadFileFormat = $_POST["downloadFileFormat"];
		$savePictoDatabase = (!empty($_POST["savePictoDatabase"]) ? $_POST["savePictoDatabase"]:"No");
		$dataQuery = array('uploadFileFormat'=>$uploadFileFormat,
					'isHeader'=>$upLoadFileHeader,
					'import_To_Photo_Access'=>$import_To_Photo_Access,
					'downloadFilename'=>$downloadFileNameOriginal,
					'downloadFileFormat'=>$downloadFileFormat,
					'savePictoDatabase'=>$savePictoDatabase,
					'error'=>"");

		if (empty($_FILES["uploadFilename"]["name"])) {
			$errorMsg = "Please select an upload file before clicking 'Submit'";
			$dataQuery['error'] = $errorMsg;
			header("location: ./import_file_manual.php?".http_build_query($dataQuery));
			exit;
		} 

		//echo http_build_query($dataQuery) . "\n";					

		$checkUploadFile = pathinfo($uploadFilename);
		//echo "Dirname: ".$checkUploadFile['dirname']."<br>";
		//echo "Basename: ".$checkUploadFile['basename']."<br>";
		//echo "Extension: ".$checkUploadFile['extension']."<br>";
		//echo "Filename: ".$checkUploadFile['filename']."<br>";
	
		$allowedExts = array("txt", "csv");
		if (in_array($checkUploadFile['extension'], $allowedExts) == false) {
			$errorMsg = "Please select a file with extension: 'txt' or 'csv'";
			$dataQuery['error'] = $errorMsg;
			header("location: ./import_file_manual.php?".http_build_query($dataQuery));
			exit;
		} 

		if ($_FILES["uploadFilename"]["error"] > 0) {
			$errorMsg = "Could not upload file: ".$uploadFilename." - Return Code: ".$_FILES["uploadFilename"]["error"];
			$dataQuery['error'] = $errorMsg;
			header("location: ./import_file_manual.php?".http_build_query($dataQuery));
			exit;
		}

		if ($_FILES["uploadFilename"]["size"] > 1024 * 1024) {
			$fileSize = round(($_FILES["uploadFilename"]["size"] * 100 / (1024 * 1024)) / 100);
		} else {
			$fileSize = round(($_FILES["uploadFilename"]["size"] * 100 / 1024)  / 100);
		}

		if ($fileSize > 1270000) {
			$errorMsg = "Please select a file which has size less than 1280M";
			$dataQuery['error'] = $errorMsg;
			header("location: ./import_file_manual.php?".http_build_query($dataQuery));
			exit;
		}

		if (file_exists("upload/" . $_FILES["uploadFilename"]["name"])) {
			$errorMsg = $_FILES["uploadFilename"]["name"] . " already exists. ";
			$dataQuery['error'] = $errorMsg;
			header("location: ./import_file_manual.php?".http_build_query($dataQuery));
			exit;
		} //else {
			// move_uploaded_file($_FILES["uploadFilename"]["tmp_name"], "upload/" . $_FILES["uploadFilename"]["name"]);
			//echo "Stored in: " . "/upload/" . $_FILES["uploadFilename"]["name"];
		//}

		if ($upLoadFileHeader == "No" and $import_To_Photo_Access == "Yes") {
			$errorMsg = "Please select Yes or No in 'Is upload file included header?' section";
			$dataQuery['error'] = $errorMsg;
			header("location: ./import_file_manual.php?".http_build_query($dataQuery));
			exit;
		} 
	
		if ($savePictoDatabase == "No" and $import_To_Photo_Access == "Yes") {
			$errorMsg = "Please select Yes or No in 'Save Picture into Database?' section";
			$dataQuery['error'] = $errorMsg;
			header("location: ./import_file_manual.php?".http_build_query($dataQuery));
			exit;
		} 

		if ($import_To_Photo_Access == "No" and $uploadFileFormat == $downloadFileFormat) {
			$errorMsg = "You selected the same delimiter in Download";
			$dataQuery['error'] = $errorMsg;
			header("location: ./import_file_manual.php?".http_build_query($dataQuery));
			exit;
		} 

		if (empty($downloadFileNameOriginal)) {
			if (explode(' ',$checkUploadFile['filename'])) {
				if ($downloadFileFormat == 2 or $downloadFileFormat == 3) {
					if ($import_To_Photo_Access == "Yes") {
						$downloadFileName = str_replace(' ','_',$checkUploadFile['filename'])."-photo.csv";
					} else {
						$downloadFileName = str_replace(' ','_',$checkUploadFile['filename'])."-convert.csv";
					}
				} else {
					if ($import_To_Photo_Access == "Yes") {
						$downloadFileName = str_replace(' ','_',$checkUploadFile['filename'])."-photo.txt";
					} else {
						$downloadFileName = str_replace(' ','_',$checkUploadFile['filename'])."-convert.txt";
					}
				}
			} else {
				if ($downloadFileFormat == 2 or $downloadFileFormat == 3) {
					if ($import_To_Photo_Access == "Yes") {
						$downloadFileName = $checkUploadFile['filename']."-photo.csv";
					} else {
						$downloadFileName = $checkUploadFile['filename']."-photo.csv";
					}
				} else {
					if ($import_To_Photo_Access == "Yes") {
						$downloadFileName = $checkUploadFile['filename']."-photo.txt";
					} else {
						$downloadFileName = $checkUploadFile['filename']."-convert.txt";
					}
				}
			}
		} else {
			$checkDownloadFile = pathinfo($downloadFileNameOriginal);
			//echo "Dirname: ".$checkDownloadFile['dirname']."<br>";
			//echo "Basename: ".$checkDownloadFile['basename']."<br>";
			//echo "Extension: ".$checkDownloadFile['extension']."<br>";
			//echo "Filename: ".$checkDownloadFile['filename']."<br>";
			if (empty($checkDownloadFile['extension'])) {
				if (explode(' ',$downloadFileNameOriginal)) {
					if ($downloadFileFormat == 2 or $downloadFileFormat == 3) {
						if ($import_To_Photo_Access == "Yes") {
							$downloadFileName = str_replace(' ','_',$downloadFileNameOriginal)."-photo.csv";
						} else {
							$downloadFileName = str_replace(' ','_',$downloadFileNameOriginal)."-convert.csv";
						}
					} else {
						if ($import_To_Photo_Access == "Yes") {
							$downloadFileName = str_replace(' ','_',$downloadFileNameOriginal)."-photo.txt";
						} else {
							$downloadFileName = str_replace(' ','_',$downloadFileNameOriginal)."-convert.txt";
						}
					}
				} else {
					if ($downloadFileFormat == 2 or $downloadFileFormat == 3) {
						if ($import_To_Photo_Access == "Yes") {
							$downloadFileName = $downloadFileNameOriginal."-photo.csv";
						} else {
							$downloadFileName = $downloadFileNameOriginal."-convert.csv";
						}
					} else {
						if ($import_To_Photo_Access == "Yes") {
							$downloadFileName = $downloadFileNameOriginal."-photo.txt";
						} else {
							$downloadFileName = $downloadFileNameOriginal."-convert.txt";
						}
					}
				}
			} else {
				if (explode(' ',$checkDownloadFile['filename'])) {
					if ($downloadFileFormat == 2 or $downloadFileFormat == 3) {
						if ($import_To_Photo_Access == "Yes") {
							$downloadFileName = str_replace(' ','_',$checkDownloadFile['filename'])."-photo.csv";
						} else {
							$downloadFileName = str_replace(' ','_',$checkDownloadFile['filename'])."-convert.csv";
						}	
					} else {
						if ($import_To_Photo_Access == "Yes") {
							$downloadFileName = str_replace(' ','_',$checkDownloadFile['filename'])."-photo.txt";
						} else {
							$downloadFileName = str_replace(' ','_',$checkDownloadFile['filename'])."-convert.txt";
						}
					}
				} else {
					if ($downloadFileFormat == 2 or $downloadFileFormat == 3) {
						if ($import_To_Photo_Access == "Yes") {
							$downloadFileName = $checkDownloadFile['filename']."-photo.csv";
						} else {
							$downloadFileName = $checkDownloadFile['filename']."-convert.csv";
						}
					} else {
						if ($import_To_Photo_Access == "Yes") {
							$downloadFileName = $checkDownloadFile['filename']."-photo.txt";
						} else {
							$downloadFileName = $checkDownloadFile['filename']."-convert.txt";
						}
					}
				}
			}
		}

		switch ($uploadFileFormat) {
			case 1:
				$delimiterRead = "\t";
				$uploadFileFormatName = "Tab";
				break;
			case 2:
				$delimiterRead = ",";
				$uploadFileFormatName = "Comma";
				break;
			case 3:
				$delimiterRead = ";";
				$uploadFileFormatName = "Semicolon";
				break;		
		}

		switch ($downloadFileFormat) {
			case 1:
				$delimiterWrite = "\t";
				$downloadFileFormatName = "Tab";
				break;
			case 2:
				$delimiterWrite = ",";
				$downloadFileFormatName = "Comma";
				break;
			case 3:
				$delimiterWrite = ";";
				$downloadFileFormatName = "Semicolon";
				break;		
		}
	
		move_uploaded_file($_FILES["uploadFilename"]["tmp_name"], "upload/" . $_FILES["uploadFilename"]["name"]);
	
		$inputFileName = "upload/" . $_FILES["uploadFilename"]["name"];
		//echo "input FileName: ".$inputFileName."<br>";

		if (!file_exists($inputFileName)) {
			$errorMsg = "Please upload ".$inputFileName." first.";
			$dataQuery['error'] = $errorMsg;
			header("location: ./import_file_manual?".http_build_query($dataQuery));
			exit;
		}

		$checkErrorFile = pathinfo($downloadFileName);
		$errorDownloadFileName = $checkErrorFile['filename']."-error.".$checkErrorFile['extension'];
		//echo "Writing error array into file: ".$errorDownloadFileName;
		$errorOutputFileName = "upload/".$errorDownloadFileName;

		$startLoadFile = time();
		//echo $startLoadFile;
		//echo "<li>".date('d/m/Y h:i:s A', $startLoadFile)." - Reading ".$uploadFilename. " file and loading it into array</li>";
		$csv = array();
		$csv_error = array();
		
		$allow_mutiple_pics = "Y";
	
		function patronIDSearch ($patronID, $arraySearch) {
			$key = -1;
			$arrayMax = sizeof($arraySearch);
			for ($i=0; $i < $arrayMax; $i++) {
				//echo "arraySearch[".$i."]['patron_number'] = ".$arraySearch[$i]['patron_number']."<br>";
				if ($arraySearch[$i]['patron_number'] == $patronID) 
					$key = $i;
			}
			return $key;
		}
	
		if (($handle = fopen($inputFileName, 'r')) !== FALSE) {
			$noRecordRead = 0;
			while (($buffer = fgets($handle)) !== false) {
				$noRecordRead = $noRecordRead + 1;
				if ($uploadFileFormat == 1) {
					if (explode('"',$buffer)) {
						$newLine = str_replace('"','',$buffer);
					} else {
						$newLine = $buffer;
					}
				} else {
					$newLine = $buffer;
				}
				
				//if (explode('"',$buffer)) {
				//	$newLine = str_replace('"','',$buffer);
				//}
					
				$dataline = str_getcsv($newLine,$delimiterRead);
				//echo "<pre>";
				//	print_r($dataline);
				//echo "</pre>";	
				//echo "sizeof: ".sizeof($dataline)."<br>";
				if ($import_To_Photo_Access == "Yes") {
					if (sizeof($dataline) == 13) {
						$record = array();
						$pic = array();
						$record['patron_number'] = $dataline[0];
						$record['patron_first_name'] = $dataline[1];
						$record['patron_middle_name'] = $dataline[2];
						$record['patron_last_name'] = $dataline[3];
						$record['patron_gender'] = $dataline[4];
						$record['patron_is_active'] = $dataline[5];
						$record['patron_ferpa_flag'] = $dataline[6];
						$record['patron_hr_classification'] = $dataline[7];
						$record['patron_primary_classification'] = $dataline[8];
						$record['patron_last_mod_datetime'] = $dataline[9];
						$pic['pic_location'] = $dataline[10];
						$pic['default_pic'] = $dataline[11];
						$pic['pic_image'] = $dataline[12];
		
						$picArray = array();
			
						if ($allow_mutiple_pics == "N") {
							$picArray[] = $pic;
							$record['pic_link'] = $picArray;
							$csv[] = $record;
						} else {
							$key = patronIDSearch ($record['patron_number'], $csv);
							//echo "1. key: ".$key." - patronID: ".$record['patron_number']."<br>";
							if ($key >= 0) {
								//echo "2. key: ".$key."<br>";
								$picArray = $csv[$key]['pic_link'];
								//echo '<pre>';
								//	print_r($picArray);
								//echo '</pre>';
								$picArray[] = $pic;
								$csv[$key]['pic_link'] = $picArray;
							} else {
								$picArray[] = $pic;
								$record['pic_link'] = $picArray;
								$csv[] = $record;
							}
						}
					} else {
						//$csv_error[] = $dataline[0]." has Error: invalid column";
						$dataline[] = "has Error: invalid column or format";
						$csv_error[] = $dataline;
					}
				} else {
					$csv[] = $dataline;
				}
			}
			if (!feof($handle)) {
				$errorMsg = "Error: unexpected fgets() fail";
				$dataQuery['error'] = $errorMsg;
				header("location: ./import_file_manual.php?".http_build_query($dataQuery));
				unlink($inputFileName);
				exit;
			}
			fclose($handle);
		}
	
		$endLoadFile = time();
		//echo '<pre>';
		//	print_r($csv);
		//echo '</pre>';
		//exit();

		$highestRow = sizeof($csv);
		//echo "<li>Total rows in ".$inputFileName." file: ".$highestRow."</li>";
		
		function saveArrayToFile($csv, $downloadFileName, $delimiterWrite) {	
			$outputFileName = "upload/".$downloadFileName;
			//echo "outputFileName: ".$outputFileName."<br>";
			$fp = fopen($outputFileName, 'w');
				foreach ($csv as $fields) {
				//echo $fields;
				fputcsv($fp,$fields,$delimiterWrite);
			}
			//readfile($outputFileName);
			fclose($fp);
		}
	
		function timeConvert ($inputTime) {
			$inputTimeDay = 0;
			$inputTimeHour = 0;
			$inputTimeMinute = 0;
			$inputTimeSecond = 0;
			$arrayProcessTime = array();
			if ($inputTime > 59) {
				if ($inputTime > 86399) {
					$inputTimeDay = round ($inputTime / 86400);
					if (($inputTime % 86400) > 2599) {
						$inputTimeHour = round (($inputTime % 86400) / 3600);
						if (($inputTime % 86400) % 3600 > 59) {
							$inputTimeMinute = round((($inputTime % 86400) % 3600) / 60);
							$inputTimeSecond = (($inputTime % 86400) % 3600) % 60;
						} else {
							$inputTimeSecond = ($inputTime % 86400) % 3600;
						}
					} else {
						if (($inputTime % 86400) % 3600 > 59) {
							$inputTimeMinute = round((($inputTime % 86400) % 3600) / 60);
							$inputTimeSecond = (($inputTime % 86400) % 3600) % 60;
						} else {
							$inputTimeSecond = ($inputTime % 86400) % 3600;
						}
					}
				} else {
					if (($inputTime % 86400) > 2599) {
						$inputTimeHour = round (($inputTime % 86400) / 3600);
						if (($inputTime % 86400) % 3600 > 59) {
							$inputTimeMinute = round((($inputTime % 86400) % 3600) / 60);
							$inputTimeSecond = (($inputTime % 86400) % 3600) % 60;
						} else {
							$inputTimeSecond = ($inputTime % 86400) % 3600;
						}
					} else {
						if (($inputTime % 86400) % 3600 > 59) {
							$inputTimeMinute = round((($inputTime % 86400) % 3600) / 60);
							$inputTimeSecond = (($inputTime % 86400) % 3600) % 60;
						} else {
							$inputTimeSecond = ($inputTime % 86400) % 3600;
						}
					}
				}
				$arrayProcessTime["Day"] = $inputTimeDay;
				$arrayProcessTime["Hour"] = $inputTimeHour;
				$arrayProcessTime["Minute"] = $inputTimeMinute;
				$arrayProcessTime["Second"] = $inputTimeSecond;

				return $arrayProcessTime;
			} else {
				$arrayProcessTime["Day"] = $inputTimeDay;
				$arrayProcessTime["Hour"] = $inputTimeHour;
				$arrayProcessTime["Minute"] = $inputTimeMinute;
				$arrayProcessTime["Second"] = $inputTime;

				return $arrayProcessTime;
			}
		}
		//echo "Time Convert: ".timeConvert(500000)."<br>";

		if ($import_To_Photo_Access == "Yes") {
			$startWriteFile = time();
		
			if ($upLoadFileHeader == "Y") {
				$startPoint = 1;
			} else {
				$startPoint = 0;
			}
		
			// Import into database
			$updated_date = date("Y-m-d H:i:s", strtotime("now"));
			//$updated_user = $first_name." ".$last_name;
			
			function GetDeltaTime($dtTime1, $dtTime2) {
				$nUXDate1 = strtotime($dtTime1);
				$nUXDate2 = strtotime($dtTime2);

				$nUXDelta = $nUXDate1 - $nUXDate2;
				//$strDeltaTime = "" . $nUXDelta/60/60; // sec -> hour
           
				//$nPos = strpos($strDeltaTime, ".");
				//if (nPos !== false)
				//	$strDeltaTime = substr($strDeltaTime, 0, $nPos + 3);

				//return $strDeltaTime;
				return $nUXDelta;
			}
		
			//$highestRow = sizeof($csv);
			if ($highestRow > 0) {
				$totalPatronUpdate = 0;
				$totalPatronPicUpdate = 0;
				for ($row = $startPoint; $row < $highestRow; ++$row) {
					$queryString = "";
					//$querySearchString = "SELECT * FROM `patron_user` where patron_number='".$csv[$row]['patron_number']."'";
					//$checkExistingRecord = queryAllInfo($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_DB, $querySearchString);
					$checkQueryStatus = $db->select('*', 'patron_user', 'patron_number=?', array($csv[$row]['patron_number']));
					if ($checkQueryStatus !== false) {
						//echo '1.<pre>';
						//	print_r($checkExistingRecord);
						//echo '</pre>';
						$checkExistingRecord = $db->fetch_assoc_all();
						$arrayLine = array();
						$arrayLine['patron_number'] = $csv[$row]['patron_number'];
						$arrayLine['patron_first_name'] = $csv[$row]['patron_first_name'];
						$arrayLine['patron_middle_name'] = $csv[$row]['patron_middle_name'];
						$arrayLine['patron_last_name'] = $csv[$row]['patron_last_name'];
						$arrayLine['patron_gender'] = $csv[$row]['patron_gender'];
						$arrayLine['patron_is_active'] = $csv[$row]['patron_is_active'];
						$arrayLine['patron_ferpa_flag'] = $csv[$row]['patron_ferpa_flag'];
						$arrayLine['patron_hr_classification'] = $csv[$row]['patron_hr_classification'];
						$arrayLine['patron_primary_classification'] = $csv[$row]['patron_primary_classification'];
						$arrayLine['patron_last_mod_datetime'] = date("Y-m-d H:i:s", strtotime($csv[$row]['patron_last_mod_datetime']));
						$arrayError = array();
		
						$recordStatus = "";
						if (!empty($checkExistingRecord) and sizeof($checkExistingRecord) > 1) {
							$arrayError = $arrayLine;
							$arrayError['Error'] = "has more than one record!";
							//$csv_error[] = $csv[$row]['patron_number']." has more than one record!";
							$csv_error[] = $arrayError;
						} elseif (!empty($checkExistingRecord) and sizeof($checkExistingRecord) == 1) {
							//echo "Time different: ".GetDeltaTime($csv[$row]['patron_last_mod_datetime'], $checkExistingRecord[0]['patron_last_mod_datetime'])."<br>";
							if (GetDeltaTime($csv[$row]['patron_last_mod_datetime'], $checkExistingRecord[0]['patron_last_mod_datetime']) > 0) {
								$updateQuery = $arrayLine;
								$updateQuery['updated_user'] = $username;
								$updateQuery['updated_date'] = $updated_date;
								$db->transaction_start();
									$delelePicFileStatus = $db->select('pic_location', 'patron_pic', 'patron_id = ?', array($checkExistingRecord[0]['patron_number']));
									if ($delelePicFileStatus) {
										$delelePicFileArray = $db->fetch_assoc_all();
										$delelePicFileList = array();
										foreach ($delelePicFileArray as $key=>$arrayValue) {
											//print_r($arrayValue);
											foreach ($arrayValue as $keys=>$value) {
												//echo $keys. " = ".$value. "<br>";
												$delelePicFileList[] = $value;
											}
										}

										array_map('unlink', $delelePicFileList);
									}
									//Delete data from table
									$deleteRecordStatus = $db->delete('patron_pic', 'patron_id=?', array($checkExistingRecord[0]['patron_number']));
									$updateRecordStatus = $db->update('patron_user', $updateQuery, 'patron_number=?', array($checkExistingRecord[0]['patron_number']));
									
									$pictureArray = $csv[$row]['pic_link'];
									$maxPictures = sizeof($pictureArray);
									$pic_num = 0;
									for ($i = 0; $i < $maxPictures; $i++) {
										$pic_num = $pic_num + 1;
										$image = addslashes(base64_encode(file_get_contents($pictureArray[$i]['pic_location'])));		
										//$fileInfo = pathinfo($pictureArray[$i]['pic_location']);
										//print_r($fileInfo);
										$pic_name = str_replace("'","",$arrayLine['patron_number'])."-".$pic_num.".png";
										copy($pictureArray[$i]['pic_location'], "download/photo_pic/".$pic_name);		//$fileInfo['basename']);
										$pictureArray[$i]['pic_location'] = "download/photo_pic/".$pic_name;			//$fileInfo['basename'];
										$insertPicArray = array();
										$insertPicArray['pic_id'] = "";
										$insertPicArray['patron_id'] = $arrayLine['patron_number'];
										$insertPicArray['pic_location'] = $pictureArray[$i]['pic_location'];
										$insertPicArray['default_pic'] = $pictureArray[$i]['default_pic'];
										$insertPicArray['pic_image'] = "";

										if ($savePictoDatabase == "Y") {
											if ($deleteRecordStatus) {				
												if (!empty($pictureArray[$i]['pic_image']))
													$insertPicArray['pic_image'] = $pictureArray[$i]['pic_image'];	
												else
													$insertPicArray['pic_image'] = $image;	

												$updatePictureStatus = $db->insert('patron_pic',$insertPicArray);

												if ($updatePictureStatus) {
													$totalPatronPicUpdate = $totalPatronPicUpdate + 1;
												} else {
													$errorUpdate = "Error: Insert query for patron picture has error!";
												}
											} else {
													$errorUpdate = "Error: Delete query for patron picture has error!";
											}
										} else {
											$updatePictureStatus = $db->insert('patron_pic',$insertPicArray);
											if ($updatePictureStatus) {
												$totalPatronPicUpdate = $totalPatronPicUpdate + 1;
											} else {
												$errorUpdate = "Error: Insert query (no image) for patron picture has error!";
											}
										}
									}	
								$updatePatronStatus = $db->transaction_complete();
								if ($updatePatronStatus) {
									$totalPatronUpdate += 1;
								} else {
									if ($updateRecordStatus === false)
										$errorUpdate = "Error: update query for patron user has error!";
									$arrayError = $arrayLine;
									$arrayError['Error'] = $errorUpdate;								
									$csv_error[] = $arrayError;
								}
							} else {
								$arrayError = $arrayLine;
								$arrayError['Error'] = "is not update! Same information";
								//$csv_error[] = $csv[$row]['patron_number']." is not update! Same information";
								$csv_error[] = $arrayError;
							}
						} else {
							$db->transaction_start();
								$newQuery = $arrayLine;
								$newQuery['updated_user'] = $username;
								$newQuery['updated_date'] = $updated_date;
								$newRecordStatus = $db->insert('patron_user', $newQuery);
							
								$pictureArray = $csv[$row]['pic_link'];
								$maxPictures = sizeof($pictureArray);
								$pic_num = 0;
								for ($i = 0; $i < $maxPictures; $i++) {
									$pic_num = $pic_num + 1;
									$image = addslashes(base64_encode(file_get_contents($pictureArray[$i]['pic_location'])));		
									//$fileInfo = pathinfo($pictureArray[$i]['pic_location']);
									//print_r($fileInfo);
									$pic_name = str_replace("'","",$arrayLine['patron_number'])."-".$pic_num.".png";
									copy($pictureArray[$i]['pic_location'], "download/photo_pic/".$pic_name);		//$fileInfo['basename']);
									$pictureArray[$i]['pic_location'] = "download/photo_pic/".$pic_name;			//$fileInfo['basename'];
									$insertPicArray = array();
									$insertPicArray['pic_id'] = "";
									$insertPicArray['patron_id'] = $arrayLine['patron_number'];
									$insertPicArray['pic_location'] = $pictureArray[$i]['pic_location'];
									$insertPicArray['default_pic'] = $pictureArray[$i]['default_pic'];
									$insertPicArray['pic_image'] = "";

									if ($savePictoDatabase == "Y") {
										if (!empty($pictureArray[$i]['pic_image']))
											$insertPicArray['pic_image'] = $pictureArray[$i]['pic_image'];	
										else
											$insertPicArray['pic_image'] = $image;	

										$newPictureStatus = $db->insert('patron_pic',$insertPicArray);

										if ($newPictureStatus) {
											$totalPatronPicUpdate = $totalPatronPicUpdate + 1;
										} else {
											$errorUpdate = "Error: Insert query for patron picture has error!";
										}
									} else {
										$newPictureStatus = $db->insert('patron_pic',$insertPicArray);
										if ($newPictureStatus) {
											$totalPatronPicUpdate = $totalPatronPicUpdate + 1;
										} else {
											$errorUpdate = "Error: Insert query (no image) for patron picture has error!";
										}
									}
								}
							
							$newPatronStatus = $db->transaction_complete();
						
							if ($newPatronStatus) {
								$totalPatronUpdate += 1;
							} else {
								if ($newRecordStatus === false)
									$errorUpdate = "Error: insert query for patron user has error!";
								$arrayError = $arrayLine;
								$arrayError['Error'] = $errorUpdate;								
								$csv_error[] = $arrayError;
							}	
						}	
					} else {
						$arrayError = $arrayLine;
						$arrayError['Error'] = "Error: Query has error or not found!";
						$csv_error[] = $arrayError;
					}
				}
				$endWriteFile = time();	
			} 
			// Create Log
			$startWriteLog = time();
			$dateCreated = date("Y-m-d H:i:s", strtotime("now"));
			$insertLogQuery = array();
			$insertLogQuery['import_file_name'] = $uploadFilename;
			//if (count($csv) > 0) 
			//	$insertLogQuery['no_updated_records'] = $noRecordRead - count($csv_error);
			//else
			//	$insertLogQuery['no_updated_records'] = 0;
			$insertLogQuery['no_patron_updated'] = $totalPatronUpdate;
			$insertLogQuery['no_patron_pic_updated'] = $totalPatronPicUpdate;
			$insertLogQuery['no_error_records'] = count($csv_error);
			if (count($csv_error) == 0)
				$insertLogQuery['error_file_name'] = NULL;
			else
				$insertLogQuery['error_file_name'] = $errorDownloadFileName; //$downloadFileName;
			$insertLogQuery['updated_user'] = $username;
			$insertLogQuery['updated_date'] = $updated_date;

			$addLogStatus = $db->insert('patron_import_file_log', $insertLogQuery);

			if ($addLogStatus) {
				$statusMsg = "Success added this record to patron_import_file_log table.";
			} else {
				$errorMsg = "Error: Insert query to import file log has error!";
			}
		
			$endWriteLog = time();
			$processTime = $endWriteLog - $startLoadFile;
		} else {
			if (count($csv) > 0) {
				$startWriteFile = time();
				$newFile = saveArrayToFile($csv, $downloadFileName, $delimiterWrite);
				$endWriteFile = time();
				//echo date('d/m/Y h:i:s A', $endWriteFile). " Complete<br>";
				//$processTime = $endWriteFile - $startLoadFile;
				//echo "processTime: ".$processTime."<br>";
			} else {
				$startWriteFile = time();
				$endWriteFile = time();
				//$processTime = $endWriteFile - $startLoadFile;
			}
			$outputFileName = "upload/".$downloadFileName;
			$processTime = $endWriteFile - $startLoadFile;
		}

		if (count($csv_error) > 0) {
			//echo "<font color=red>Completed with error</font>";
			//echo "<pre>";
			//print_r($csv_error);
			//echo "</pre>";
			//$outputFileName = "upload/".$downloadFileName;
			//echo "Click <a href=\"download.php?filename=".$outputFileName."\">"."<button class=\"downloadButton\">Download</button></a> to get the file '<b>".$downloadFileName."</b>'";
				
			//$checkFile = pathinfo($downloadFileName);
			//$errorDownloadFileName = $checkFile['filename']."-error.".$checkFile['extension'];
			//echo "Writing error array into file: ".$errorDownloadFileName;
			//$errorOutputFileName = "upload/".$errorDownloadFileName;

			$fp = fopen($errorOutputFileName, 'w');
			foreach ($csv_error as $fields) {
				fputcsv($fp,$fields,$delimiterWrite);
			}
			fclose($fp);
			//echo "Click <a href=\"download.php?filename=".$errorOutputFileName."\">"."<button class=\"downloadButton\">Download</button></a> to get the error file '<b>".$errorDownloadFileName."</b>'";
		
		} //else {
		//	$outputFileName = "upload/".$downloadFileName;
			//echo "Completed without error";
			//echo "<a href=\"download.php?filename=".$outputFileName."\">"."<button class=\"downloadButton\">Download</button></a> to get the file '<b>".$downloadFileName."</b>'";
		//}

?>	
		<?php include ("./../web/header.html"); ?>
		<link href="./../css/mainstyle.css" rel="stylesheet" type="text/css">
		<?php include ("./../web/menu.html"); ?>
		<div style="width:80%; font-family:Arial, Helvetica, sans-serif;font-size:12px;line-height:18px; border: 0px solid black; margin: 0px auto;background-color:white">
		<table align="center" width=100% style="padding:25px; text-align:center; background-color:white">
			<tr>
				<td align="center"><br><br><br>
					<?php
					if ($import_To_Photo_Access == "Yes") {
					echo "<div class=\"mainTitle\"><strong><center>Photo Access ID - Import into Database</center></strong></div>";
					} else {
					echo "<div class=\"mainTitle\"><strong><center>Photo Access ID - Convert Text File</center></strong></div>";
					}
					?>
					<br>
				</td>
			</tr>
		
			<tr>
				<td align="center">
					<?php
					if (!empty($errorMsg)) {
						echo "<center><font color=\"red\">".$errorMsg."</font></center><br>";
					}
					if (!empty($statusMsg)) {
						echo "<center><font color=\"blue\">".$statusMsg."</font></center><br>";
					}
					?>
				</td>
			</tr>
		
			<tr>
				<td>
					<table width=100% style="border-collapse: separate; border:1px #7A7777 solid; padding:10px" cellspacing="10" class="graybox">
						<tr>
							<td width=50%><div class="tableHeader"><center>File Upload Information</center></div></td>
							<td width=50%><div class="tableHeader"><center>File Upload Process</center></div></td>
						</tr>
						<tr>
							<td style="padding-left:40px">
							<form method="post" enctype="multipart/form-data">
								<br>
								<?php echo "Upload file name: ".$uploadFilename."<br>"; ?>
								<?php echo "Upload file delimiter: ".$uploadFileFormatName."<br>"; ?>
								<?php echo "Upload file included header: ".$upLoadFileHeader."<br>"; ?>
								<?php echo "import To Photo Access: ".$import_To_Photo_Access."<br>"; ?>
								<?php echo "Download file name: ".$downloadFileName."<br>"; ?>
								<?php echo "Download file delimiter: ".$downloadFileFormatName."<br>"; ?>
							</td>
							<td style="padding-left:40px">
								<br>
								<?php echo date('m/d/Y h:i:s A', $startLoadFile)." - Reading file<br>"; ?>
								<?php echo date('m/d/Y h:i:s A', $endLoadFile)." - Done reading file<br>"; ?>
								<?php echo date('m/d/Y h:i:s A', $startWriteFile)." - Writing file<br>"; ?>
								<?php echo date('m/d/Y h:i:s A', $endWriteFile)." - Done writing file<br>"; ?>
								<?php $arrayProcess = timeConvert($processTime); ?>
								<?php echo "Process time: ".$processTime." second(s)<br>"; ?>
								<center>
								<table width=70% style="border: 1px solid black;">
									<tr>
										<td style="width:25%; border: 1px solid black; padding:3px;"><center>Day</center></td>
										<td style="width:25%; border: 1px solid black; padding:3px;"><center>Hour</center></td>
										<td style="width:25%; border: 1px solid black; padding:3px;"><center>Minute</center></td>
										<td style="width:25%; border: 1px solid black; padding:3px;"><center>Second</center></td>
									</tr>
									<tr>
										<td style="width:25%; border: 1px solid black; padding:3px;"><center><?php echo $arrayProcess["Day"]; ?></center></td>
										<td style="width:25%; border: 1px solid black; padding:3px;"><center><?php echo $arrayProcess["Hour"]; ?></center></td>
										<td style="width:25%; border: 1px solid black; padding:3px;"><center><?php echo $arrayProcess["Minute"]; ?></center></td>
										<td style="width:25%; border: 1px solid black; padding:3px;"><center><?php echo $arrayProcess["Second"]; ?></center></td>
									</tr>
								</table>
								</center>
							</td>
						<tr>
							<td colspan = "2">
								<br>
								<br><center><div class="tableHeader">File Upload Status</div></center>
								<table width=100% style="border: 1px solid black;">
									<tr>
										<td style="width:25%; border: 1px solid black; padding:10px;">
											<b>Total Reading Rows</b>
										</td>
										<td style="width:25%; border: 1px solid black; padding:10px;">
											<?php echo $noRecordRead; ?>
										</td>
									</tr>
									<?php
									if ($import_To_Photo_Access == "Yes") {
									echo "<tr>
										<td style=\"width:25%; border: 1px solid black; padding:10px;\">
											<b>Total Records Import to Patron User Table</b>
										</td>
										<td style=\"width:25%; border: 1px solid black; padding:10px;\">".$totalPatronUpdate;
											//if (count($csv) > 0) {
											//echo $noRecordRead - count($csv_error);
											//}
										echo "</td>
									</tr>";		
									echo "<tr>
										<td style=\"width:25%; border: 1px solid black; padding:10px;\">
											<b>Total Records Import to Patron Pic Table</b>
										</td>
										<td style=\"width:25%; border: 1px solid black; padding:10px;\">".$totalPatronPicUpdate."
										</td>
									</tr>";									
									} else {
									echo "<tr>
										<td style=\"width:25%; border: 1px solid black; padding:10px;\">
											<b>Download</b>
										</td>
										<td style=\"width:25%; border: 1px solid black; padding:10px;\">";
											if (count($csv) > 0) {
											echo "<a href=\"".$outputFileName."\" target=\"_blank\">Download</a>";
											}
										echo "</td>
									</tr>";
									}
	
									if (count($csv_error) > 0) {
										echo "
									<tr>
										<td style=\"width:25%; border: 1px solid black; padding:10px;\">
											<b>Total Error Records</b>
										</td>
										<td style=\"width:25%; border: 1px solid black; padding:10px;\">".
											sizeof($csv_error).
										"</td>
									</tr>
									<tr>
										<td style=\"width:25%; border: 1px solid black; padding:10px;\">
											<b>Error</b>
										</td>
										<td style=\"width:25%; border: 1px solid black; padding:10px;\">
											<a href=\"".$errorOutputFileName."\" target=\"_blank\">Download</a>
										</td>
									</tr>";
									}
									?>
								</table>
							</td>
						</tr>
					</table>
				</td>
				</form>	
			</tr>
		</table>
		<br><br>
		</div>
		<?php include ("./../web/footer.html"); ?>
<?php	
		unlink($inputFileName);
	} else {
		$page_link = "./../index.php";
		$reDirectLocation = "Location: ./../web/login.php?page_link=".$page_link;
		//echo "reDirection Location: '".$reDirectLocation."'<br>";
		header($reDirectLocation);
		exit();
	}
} else {
	$errorMsg = "Error: ".$checkConnect['error'];
	$dataQuery['error'] = $errorMsg;
	header("location: ./import_file_manual.php?".http_build_query($dataQuery));
	exit;	
}
?>
