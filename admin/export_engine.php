<?php 
/*
 *******************************************************************************************************
*
* Name: export_engine.php
* Export all records for table: patron_user, patron_pic
* Writer: Bach Nguyen (bnguye21@uncc.edu)
* Last Updated: 01/16/2015
*	01/16/2015
*		01/05/2015 - Created new file
*
 ********************************************************************************************************
 */
if (isset($_POST['export_patron_picture'])) {
	$export_patron_picture = $_POST['export_patron_picture'];
} else {
	$export_patron_picture = "Y";
}
	
include("./../install/database_credentials.inc");
include("./../photo_access_sql.php");
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
			if ($result = @$mysqli->query($queryExportString)) {
				$exportStatus['status'] = "True";
				$exportStatus['no_export'] = $mysqli->affected_rows;
				$exportStatus['error_msg'] = "Success";
				
				$recordArray = array();
				$outputArray = array();
				$pic_number = 0;
				while($obj = $result->fetch_object()) {
					echo 
					$new_exported_image = "./download/new_photo_pic/";
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
					$decoded_file_data = $recordArray['pic_image'];

					echo "new image name: ".$new_exported_image."<br>";
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
			$queryExportString = "SELECT a.*, b.* from `patron_user` a left join `patron_pic` b on a.patron_number = b.patron_id";
			$exportTable = array();
			$exportTable['query'] = $queryExportString;
			$exportTable['export_info'] = exportRecord($queryExportString);
			$exportArray[] = $exportTable;
		} else {
			$queryExportString = "SELECT * from `patron_user`";
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
