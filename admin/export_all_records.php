<?php 
/*
 *******************************************************************************************************
*
* Name: export_all_records.php
* Export all records for table: patron_user, patron_pic
* Writer: Bach Nguyen (bnguye21@uncc.edu)
* Last Updated: 01/05/2015
*	01/05/2015
*		01/05/2015 - Created new file
*
 ********************************************************************************************************
 */
if (isset($_SESSION['user'])) {
	$username = $_SESSION['user'];
	$userGroup = $_SESSION['user_group'];
	$department = $_SESSION['department'];
	$first_name	= $_SESSION['first_name'];
	$last_name = $_SESSION['last_name'];
	$number_per_page = $_SESSION['number_per_page'];
	$date_format = $_SESSION['date_format'];
	$datetime_format = $date_format." h:i:s A";
	$self_approved = $_SESSION['self_approved'];
	$policy_renew = $_SESSION['policy_renew'];
	//echo "<br><br>user name: ".$username." - User group: ".$userGroup." - Department: ".$department." - Lastname, Firstname: ".$last_name.", ".$first_name."<br>";
	//echo "number per page: ".$number_per_page." - date format: ".$date_format."<br>";
	//echo "self approved: ".$self_approved." - policy renew: ".$policy_renew."<br>";

	if (isset($_GET['warningMsg'])) {
		$warningMsg = $_GET['warningMsg'];
	} else {
		$warningMsg = "";
	}	
	if (intval($_SESSION['user_group']) > 1) {
		if (!empty($warningMsg)) {
			$errorMsg = $warningMsg;
		} else {
			$errorMsg = "You don't have permission to view this page";
		}
	}

	if (isset($_POST['export_patron_picture'])) {
		$export_patron_picture = $_POST['export_patron_picture'];
	} else {
		$export_patron_picture = "Y";
	}
	ini_set('memory_limit', '-1');
	ini_set ('max_execution_time','-1');
	ini_set('post_max_size', '600M');
	ini_set('upload_max_filesize', '500M');
	
	include("./../install/database_credentials.inc");
	include("./../photo_access_sql.php");
	
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
		
	$startExport = time();
	$databaseConnect = connectDatabase($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_DB);
	$exportArray = array();
	if ($databaseConnect["status"] == "true") {
		if (!empty($_POST["export"])) {
			function saveArrayToFile($csv, $downloadFileName, $delimiterWrite) {	
				$outputFileName = $downloadFileName;
				//echo "outputFileName: ".$outputFileName."<br>";
				$fp = fopen($outputFileName, 'w');
				foreach ($csv as $fields) {
					//echo $fields;
					fputcsv($fp,$fields,$delimiterWrite);
				}
				//readfile($outputFileName);
				fclose($fp);
			}
			
			function exportRecord ($queryExportString) {	
				global $DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_DB;
				$downloadFileName = "./download/export_photo_id.txt";
				$delimiterWrite = ";";
				
				$exportStatus = array();
				$mysqli = new mysqli($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_DB);
				$mysqli->set_charset('utf8');
				if ($result = @$mysqli->query($queryExportString)) {
					$exportStatus['status'] = "True";
					$exportStatus['no_export'] = $mysqli->affected_rows;
					$exportStatus['error_msg'] = "Success";
					
					$recordArray = array();
					$outputArray = array();
					$pic_number = 0;
					while($obj = $result->fetch_object()) {
						//echo "<pre>";
						//	print_r($obj);
						//echo "</pre>";
						
						$new_exported_image = "./download/export_photo_pic/";
						//$recordArray['id'] = $obj->id;
						$pic_number = $pic_number + 1;
						$recordArray['patron_number'] = $obj->patron_number;
						$recordArray['first_name'] = $obj->patron_first_name;
						$recordArray['middle_name'] = $obj->patron_middle_name;
						$recordArray['last_name'] = $obj->patron_last_name;
						$recordArray['gender'] = $obj->patron_gender;
						$recordArray['active'] = $obj->patron_is_active;
						$recordArray['ferpa_flag'] = $obj->patron_ferpa_flag;
						$recordArray['hr_classification'] = $obj->patron_hr_classification;
						$recordArray['primary_classification'] = $obj->patron_primary_classification;
						$recordArray['last_mod_datetime'] = $obj->patron_last_mod_datetime;
						//$recordArray['updated_user_id'] = $obj->updated_user_id;
						//$recordArray['updated_date'] = $obj->updated_date;
						//$recordArray['pic_id'] = $obj->pic_id;
						//$recordArray['pic_location'] = $obj->pic_location;
						$new_exported_image .= $recordArray['patron_number']."-".$pic_number.".png";
						$recordArray['pic_location'] = $new_exported_image;
						$recordArray['default_pic'] = $obj->default_pic;
						$recordArray['pic_image'] = $obj->pic_image;
						$outputArray[] = $recordArray;	

						$decoded_file_data = base64_decode($obj->pic_image);

						//echo "new image name: ".$new_exported_image."<br>";
						$new_handle = fopen($new_exported_image, 'w') or die("Can't create file");
						fwrite($new_handle, $decoded_file_data);
						//echo "Image data has been read from the database and a new file created\n";
						fclose($new_handle);
						
					} // close while loop
					
					$newFile = saveArrayToFile($outputArray, $downloadFileName, $delimiterWrite);				
				} else {
					$exportStatus['status'] = "False";
					$exportStatus['no_export'] = 0;
					$exportStatus['error_msg'] = $mysqli->error;
				}
				return $exportStatus;
			}
			
			if ($export_patron_picture == "Y") {
				$queryExportString = "SELECT a.*, b.* from `patron_user` a left join `patron_pic` b on a.patron_number = b.patron_id where patron_last_name like 'n%' order by patron_number";
				$exportTable = array();
				$exportTable['query'] = $queryExportString;
				$exportTable['export_info'] = exportRecord($queryExportString);
				$exportArray[] = $exportTable;
			} else {
				$queryExportString = "SELECT * from `patron_user` order by patron_number";
				$exportTable = array();
				$exportTable['query'] = $queryExportString;
				$exportTable['export_info'] = exportRecord($queryExportString);
				$exportArray[] = $exportTable;				
			}
			//echo "<pre>";
			//	print_r($deleteArray);
			//echo "</pre>";
		}
	} else {
		$errorMsg = $databaseConnect["errorMsg"];
	}
	$endExport = time();
	$processTime = $endExport - $startExport;
	include ("./../web/header.html");
	include ("./../web/menu.html");	
	if (!isset($errorMsg)) {
	?>
		<link href="./../css/mainstyle.css" rel="stylesheet" type="text/css">
		<!--
		<div style="margin-left:58px; margin-right:58px; padding:6px; background-color:#FFF; border:#999 1px solid;"><?php echo $paginationDisplay; ?></div>
		-->
		<div style="width:80%; font-family:Arial, Helvetica, sans-serif;font-size:12px;line-height:18px; border: 0px solid black; margin: 0px auto;background-color:white">
			<br><br>
			<form action="export_all_records.php" method="post">
				<table width=100% style="background-color:white">
					<tr>
						<td colspan=2><br>
							<div class="mainTitle"><strong><center>Photo Access ID - Export Patron Records</center></strong></div>
						</td>
					</tr>
					<tr>
						<td colspan=2>
							<center><?php if (isset($warningMsg)) echo "<font color=red id=\"warningMsg\">".$warningMsg."</font>"; ?></center>
						</td>
					</tr>	
					<tr>
						<td colspan=2 style="text-align:center"><br><center>
							<table width=60% style="background-color:white">
								<tr>
									<td width=70% style="padding-left:20%; padding-bottom:10px">Do you want export patron picture records?</td>
									<td width=30% style="padding-bottom:10px">
										<select name="export_patron_picture" id="export_patron_picture">
											<option value="Y" <?php if ($export_patron_picture == "Y") echo " selected";?>>Yes</option>
											<option value="N" <?php if ($export_patron_picture == "N") echo " selected";?>>No</option>
										</select>
									</td>
								</tr>
								<tr>
									<td colspan=2 style="padding-bottom:20px; text-align:center"><br>
										<input type="submit" name="export" value="Export" class="dark_green_button">
									</td>
								</tr>
							</table>
							</center>
						</td>
					</tr>
					
					<?php
					if (sizeof($exportArray) > 0) {
						for ($row=0; $row < sizeof($exportArray); $row++) {
							echo "\n<tr>
								<td colspan=2><center>
									<table width=60% style=\"background-color:white\">
										<tr>
											<td clospan=\"2\" width=30%><b>Result:</b></td>
										<tr>
											<td colspan=\"2\" style=\"padding-left:40px\">"
												.date('m/d/Y h:i:s A', $startExport)." - Writing file<br>"
												.date('m/d/Y h:i:s A', $endExport)." - Done writing file<br>";
												$arrayProcess = timeConvert($processTime);
												echo "Process time: ".$processTime." second(s)<br>";
												echo "<center>
												<table width=70% style=\"border: 1px solid black;\">
													<tr>
														<td style=\"width:25%; border: 1px solid black; padding:3px;\"><center>Day</center></td>
														<td style=\"width:25%; border: 1px solid black; padding:3px;\"><center>Hour</center></td>
														<td style=\"width:25%; border: 1px solid black; padding:3px;\"><center>Minute</center></td>
														<td style=\"width:25%; border: 1px solid black; padding:3px;\"><center>Second</center></td>
													</tr>
													<tr>
														<td style=\"width:25%; border: 1px solid black; padding:3px;\"><center>".$arrayProcess["Day"]."</center></td>
														<td style=\"width:25%; border: 1px solid black; padding:3px;\"><center>".$arrayProcess["Hour"]."</center></td>
														<td style=\"width:25%; border: 1px solid black; padding:3px;\"><center>".$arrayProcess["Minute"]."</center></td>
														<td style=\"width:25%; border: 1px solid black; padding:3px;\"><center>".$arrayProcess["Second"]."</center></td>
													</tr>
												</table>
												</center><br>
											</td>
										</tr>			
										<tr>
											<td width=30%><b>Query:</b></td>
											<td width=70%>".$exportArray[$row]['query']."</td>
										</tr>
										<tr>
											<td width=30% style=\"padding-left:20px\">Export Status:</td>
											<td width=70%>".$exportArray[$row]['export_info']['status']."</td>
										</tr>	
										<tr>
											<td width=30% style=\"padding-left:20px\">Number Export:</td>
											<td width=70%>".$exportArray[$row]['export_info']['no_export']."</td>
										</tr>	
										<tr>
											<td width=30% style=\"padding-left:20px\">Error Message:</td>
											<td width=70%>".$exportArray[$row]['export_info']['error_msg']."</td>	
										</tr>
										<tr>
											<td colspan=2 style=\"padding-top:5px;padding-bottom:10px;\">
												<hr style=\"height:2px;background-color:green;\">
											</td>
										</tr>
									</table></center>
								</td>
							</tr>\n";	
						}	
					}
					?>
				</table>
			</form>
		</div>
		<BR><BR><BR>
		<?php include ("./../web/footer.html"); 
		?>
		<?php
	} else {
		?>
			<link href="./../css/mainstyle.css" rel="stylesheet" type="text/css">
			<div style="width:80%; font-family:Arial, Helvetica, sans-serif;font-size:12px;line-height:18px; border: 0px solid black; margin: 0px auto;background-color:white">
			<br><br>
			<table width=100%>
				<tr>
				<td><br>
					<div class="mainTitle"><strong><center>Photo Access ID - Export Patron Records</center></strong></div>
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
?>