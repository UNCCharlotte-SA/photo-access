<?php 
/*
 *******************************************************************************************************
*
* Name: generate_sample_file.php
* Create temp file for 26,000 records
* Writer: Bach Nguyen (bnguye21@uncc.edu)
* Last Updated: 02/16/2015
*	02/16/2015
*		- Added save picture into database
*	02/13/2015
*		- Updated mysql class, session class
*	01/22/2015
*		- Added paging into array
*	01/08/2015
*		- Changed sample Last Name, Middle Name, First Name location
*		- Added pic_image item into array
*	01/06/2015
*		- Changed photo_pic sample from /photo_pic/men to /photo_pic/sample/men - same for women folder
*	12/08/2014
*		- Added scanDir class
*		- Change file to add picture link to file: mutiple records
*	11/21/2014 - Added link to picture table	
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
	//require './../lib/Zebra_Pagination-master/Zebra_Pagination.php';
	$link = $db->get_link();
	$security_code = $SESSION_SECURITY;
	$session_lifetime = $SESSION_TIMEOUT;
	$session = new Zebra_Session($link, $security_code, $session_lifetime);
	//$pagination = new Zebra_Pagination();
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
		date_default_timezone_set($time_zone);
 
 		if (isset($_GET['warningMsg'])) {
			$warningMsg = $_GET['warningMsg'];
		} else {
			$warningMsg = "";
		}	
		if (intval($userGroup) > 1) {
			if (!empty($warningMsg)) {
				$errorMsg = $warningMsg;
			} else {
				$errorMsg = "You don't have permission to view this page";
			}	
		}
		ini_set('memory_limit', '-1'); 
		ini_set ('max_execution_time','-1');
		require_once('./../classes/scandir.php');
		
		function rand_Gender_Letter() {
			$int = rand(0,1);
			$a_z = "FM";
			$rand_Gender_Letter = $a_z[$int];
			//echo "rand sex letter: ".$rand_Gender_Letter."<br>";
			return $rand_Gender_Letter;
		}
	
		function rand_Active_Letter() {
			$int = rand(0,1);
			$a_z = "YN";
			$rand_Active_Letter = $a_z[$int];
			//echo "rand active letter: ".$rand_Active_Letter."<br>";
			return $rand_Active_Letter;
		}

		function getName ($requireName) {
			if ($requireName == "LastName") {
				$data = file_get_contents("./download/sample/patron_info/Last_Name_list.csv"); //read the file
			} elseif ($requireName == "FirstName") {
				$data = file_get_contents("./download/sample/patron_info/First_Name_list.csv"); //read the file
			} elseif ($requireName == "MiddleName") {
				$data = file_get_contents("./download/sample/patron_info/Middle_Name_list.csv"); //read the file
			}
			$convert = explode("\n", $data); //create array separate by new line
			// echo "<pre>";
			//  print_r($convert);
			// echo "</pre>";
			//for ($i=0;$i<count($convert);$i++)
			//{
			//    echo $convert[$i].', '; //write value by index
			//}
			//echo "sizeof ".sizeof($convert);
			$maxNo = sizeof($convert) - 1;
			//$rand_key = array_rand($convert, 10);
			$rand_key = rand(1, $maxNo);
			//echo $rand_key."<br>";
			$getNameValue = $convert[$rand_key];
			return $getNameValue;
		}

		function getrandomstring($length) {
			global $template;
			settype($template, "string");

			$template = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
			/* this line can include numbers or not */

			settype($length, "integer");
			settype($rndstring, "string");
			settype($a, "integer");
			settype($b, "integer");

			for ($a = 0; $a <= $length; $a++) {
				$b = rand(0, strlen($template) - 1);
				$rndstring .= $template[$b];
			}
			return $rndstring; 
		}

		function saveArrayToFile($csv, $downloadFileName, $delimiterWrite) {	
			$outputFileName = $downloadFileName;
			//echo "outputFileName: ".$outputFileName."<br>";
			$headerArray = array('patron_number','first_name','middle_name','last_name','gender','active','ferpa_flag','hr_classification','primary_classification','last_mod_datetime','pic_location','default_pic','pic_image');
			$fp = fopen($outputFileName, 'w');
			fputcsv($fp,$headerArray,$delimiterWrite);
			foreach ($csv as $fields) {
				fputcsv($fp,$fields,$delimiterWrite);
			}
			//readfile($outputFileName);
			fclose($fp);
		}

		$file_ext = array(
			"jpg",
			"bmp",
			"png"
		);
		$men_pic_dirs = "./download/sample/men/";
		$women_pic_dirs = "./download/sample/women/";

		$downloadFileName = "./download/photo_id.txt";
		$delimiterWrite = ";";

		$scandir = new scanDir;
		// Multiple dirs, with specified extensions, include sub-dir files
		$men_pic_files = $scandir->scan($men_pic_dirs, $file_ext, false);
		$women_pic_files = $scandir->scan($women_pic_dirs, $file_ext, false);
	
		$men_pics_maxNo = sizeof($men_pic_files) - 1;
		$women_pics_maxNo = sizeof($women_pic_files) - 1;

		if (isset($_POST['number_record'])) {
			$number_record = $_POST['number_record'];
		} else {
			if (isset($_SESSION['photo_access']['admin']['generate_sample_file']['number_record'])) {
				$number_record = $_SESSION['photo_access']['admin']['generate_sample_file']['number_record'];
			} else {
				$number_record = 50;
			}
		}
		if (isset($_POST['allow_mutiple_pics'])) {
			$allow_mutiple_pics = $_POST['allow_mutiple_pics'];
		} else {
			if (isset($_SESSION['photo_access']['admin']['generate_sample_file']['allow_mutiple_pics'])) {
				$allow_mutiple_pics = $_SESSION['photo_access']['admin']['generate_sample_file']['allow_mutiple_pics'];
			} else {
				$allow_mutiple_pics = "N";
			}
		}
		if (isset($_POST['save_pic_to_database'])) {
			$save_pic_to_database = $_POST['save_pic_to_database'];
		} else {
			if (isset($_SESSION['photo_access']['admin']['generate_sample_file']['save_pic_to_database'])) {
				$save_pic_to_database = $_SESSION['photo_access']['admin']['generate_sample_file']['save_pic_to_database'];
			} else {
				$save_pic_to_database = "N";
			}
		}		
		if (isset($_POST['num_pics'])) {
			$num_pics = $_POST['num_pics'];
		} else {
			if (isset($_SESSION['photo_access']['admin']['generate_sample_file']['num_pics'])) {
				$num_pics = $_SESSION['photo_access']['admin']['generate_sample_file']['num_pics'];
			} else {
				$num_pics = 0;
			}
		}
		//echo "Number records: ".$number_record."<br>";
		//echo "Number picture: ".$num_pics."<br>";
		//$allow_mutiple_pics = "Y";
		//$number_record = 10;

		function randomDate($start_date, $end_date) {
			// Convert to timetamps
			$min = strtotime($start_date);
			$max = strtotime($end_date);

			// Generate random number using above bounds
			$val = rand($min, $max);

			// Convert back to desired date format
			return date('Y-m-d H:i:s', $val);
		}
	
		function generateRecord ($studentID) {
			$record = array();
	
			$patron_number = 800000000 + $studentID;
			//$patron_first_name = getrandomstring(rand(1,30));
			//$patron_middle_name = getrandomstring(rand(1,15));
			//$patron_last_name = getrandomstring(rand(1,30));
			$patron_first_name = trim(getName("FirstName"));
			$patron_middle_name = trim(getName("MiddleName"));
			$patron_last_name = trim(getName("LastName"));
			$patron_gender = rand_Gender_Letter();
			$patron_is_active = rand_Active_Letter();
			$patron_ferpa_flag = rand_Active_Letter();
			$j = rand(1, 2);
			//echo $j."<br>";
			if ($j == 1) {
				$patron_hr_classification = "Faculty/Staff";
			} else {
				$patron_hr_classification = "Student";
			}
			$k = rand(1, 2);
			if ($k == 1) {
				$patron_primary_classification = "Faculty/Staff";
			} else {
				$patron_primary_classification = "Student";
			}
			
			$start_date = date("Y-m-d H:i:s", strtotime("-4 year"));
			$end_date = date("Y-m-d H:i:s", strtotime("now"));
			$patron_last_mod_datetime = randomDate($start_date, $end_date);
	
			$record['patron_number'] = $patron_number;
			$record['first_name'] = $patron_first_name;
			$record['middle_name'] = $patron_middle_name;
			$record['last_name'] = $patron_last_name;
			$record['gender'] = $patron_gender;
			$record['active'] = $patron_is_active;
			$record['ferpa_flag'] = $patron_ferpa_flag;
			$record['hr_classification'] = $patron_hr_classification;
			$record['primary_classification'] = $patron_primary_classification;
			$record['last_mod_datetime'] = $patron_last_mod_datetime;
			return $record;
		}

		function generate_pic_link($record) {
			global $men_pic_files, $women_pic_files;
			global $men_pics_maxNo,$women_pics_maxNo;
			if ($record['gender'] == "M") {
				//$rand_key = array_rand($convert, 10);
				$rand_key = rand(0, $men_pics_maxNo);
				$getPicFileNameValue = $men_pic_files[$rand_key];
				//echo "1. Sex: ".$record['gender']." and filename: ".$getPicFileNameValue."<br>";
			} elseif ($record['gender'] == "F") {
				//$rand_key = array_rand($convert, 10);
				$rand_key = rand(0, $women_pics_maxNo);
				$getPicFileNameValue = $women_pic_files[$rand_key];	
				//echo "2. Sex: ".$record['gender']." and filename: ".$getPicFileNameValue."<br>";
			}
			//echo "3. Sex: ".$record['gender']." and filename: ".$getPicFileNameValue."<br>";
			return str_replace('/\\', '/', $getPicFileNameValue);
		}
	
		//echo "number record: ".$number_record."<br>";
		//echo "allow_mutiple_pics: ".$allow_mutiple_pics."<br>";
		$row_count = 0;
		if (!empty($_POST["generate"])) {
			if (!empty($number_record)) {
				$mydata = array();
				$record = array();

				for ($i=0; $i <= $number_record; $i++) {
					if ($allow_mutiple_pics == "N") {
						$record = generateRecord ($i);
						$record['pic_location'] = generate_pic_link($record);
						$record['default_pic'] = "Y";
						if ($save_pic_to_database == "Y") {
							$image = addslashes(base64_encode(file_get_contents($record['pic_location'])));
						} else {
							$image = null;
						}
						$record['pic_image'] = $image;
						$mydata[] = $record;
					} else {
						$pic_random = rand(0,$num_pics);
						$pic_default = "N";
						if ($pic_random > 0) {
							$same_record = generateRecord ($i);
							for ($x=0; $x <= $pic_random; $x++) {
								$new_record = array();
								$new_record = $same_record;
								$new_record['pic_location'] = generate_pic_link($same_record);
								if ($pic_default == "N") {
									$new_record['default_pic'] = "Y";
									$pic_default = "Y";
								} else {
									$new_record['default_pic'] = "N";
								}
								if ($save_pic_to_database == "Y") {
									$image = addslashes(base64_encode(file_get_contents($new_record['pic_location'])));
								} else {
									$image = null;
								}
								$new_record['pic_image'] = $image;
								$mydata[] = $new_record;
							}
						} else {
							$record = generateRecord ($i);
							$record['pic_location'] = generate_pic_link($record);
							$record['default_pic'] = "Y";
							if ($save_pic_to_database == "Y") {
								$image = addslashes(base64_encode(file_get_contents($record['pic_location'])));
							} else {
								$image = null;
							}
							$record['pic_image'] = $image;
							$mydata[] = $record;
						}
					}
				}
				$_SESSION['photo_access']['admin']['generate_sample_file']['number_record'] = $number_record;
				$_SESSION['photo_access']['admin']['generate_sample_file']['allow_mutiple_pics'] = $allow_mutiple_pics;
				$_SESSION['photo_access']['admin']['generate_sample_file']['save_pic_to_database'] = $save_pic_to_database;
				$_SESSION['photo_access']['admin']['generate_sample_file']['num_pics'] = $num_pics;
				$_SESSION['photo_access']['admin']['generate_sample_file']['mydata'] = $mydata;
				
				$newFile = saveArrayToFile($mydata, $downloadFileName, $delimiterWrite);
				//echo "<pre>";
				//	print_r($mydata);
				//echo "</pre>";
			}
		}
		require './../lib/Zebra_Pagination-master/Zebra_Pagination.php';
		$pagination = new Zebra_Pagination();
		// instantiate the pagination object
		if (isset($_SESSION['photo_access']['admin']['generate_sample_file']['mydata'])) {
			$mydata = $_SESSION['photo_access']['admin']['generate_sample_file']['mydata'];
			$row_count = sizeof($mydata);
			//echo "row: ".$row_count."<br>";
			$pagination->records(count($mydata));
			$pagination->records_per_page($number_per_page);
		
			$mydata = array_slice($mydata,(($pagination->get_page() - 1) * $number_per_page), $number_per_page);
		}

		if (!isset($errorMsg) and $row_count >= 0) {
		?>
			<?php include ("./../web/header.html");?>
			<?php include ("./../web/menu.html"); ?>
			<link href="./../css/mainstyle.css" rel="stylesheet" type="text/css">
			<link rel="stylesheet" href="./../lib/Zebra_Pagination-master/public/css/zebra_pagination.css" type="text/css">
			<script Language="JavaScript">	
				function trim(stringToTrim) {
					return stringToTrim.replace(/^\s+|\s+$/g,"");
				}
				
				function checkValidator(theForm) {
					var txtNumber_record = trim(document.getElementById('number_record').value);
			
					if (txtNumber_record == "" && txtNumber_record != "") {
						document.getElementById("warningMsg").innerHTML = "Please enter how many records you want to generate!";
						theForm.number_record.style.backgroundColor="#D0E2ED";
						theForm.number_record.focus();
						return (false);				
					}
				}
			</script>
			<!--
			<div style="margin-left:58px; margin-right:58px; padding:6px; background-color:#FFF; border:#999 1px solid;"><?php echo $paginationDisplay; ?></div>
			-->
			<div style="width:80%; font-family:Arial, Helvetica, sans-serif;font-size:12px;line-height:18px; border: 0px solid black; margin: 0px auto;background-color:white">
			<br><br>
			<form action="generate_sample_file.php" method="post" onsubmit="return checkValidator(this)" name="mainForm">
				<table width=100% style="background-color:white">
					<tr>
						<td colspan=2><br>
							<div class="mainTitle"><strong><center>Photo Access ID - Generate Sample File</center></strong></div>
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
									<td width=70% style="padding-left:20%">How many record you want to generate?</td>
									<td width=30%><input type="text" name="number_record" id="number_record" size=20 value="<?php if (!empty($number_record)) echo $number_record; ?>"></td>
								</tr>
								<tr>
									<td width=70% style="padding-left:20%">Maximum random pictures you want to generate?</td>
									<td width=30%><input type="text" name="num_pics" id="num_pics" size=20 value="<?php if (isset($num_pics)) echo $num_pics; ?>"></td>
								</tr>
								<tr>
									<td width=70% style="padding-left:20%">Do you want mutiple pictures per record?</td>
									<td width=30%>
										<select name="allow_mutiple_pics" id="allow_mutiple_pics">
											<option value="Y" <?php if ($allow_mutiple_pics == "Y") echo " selected";?>>Yes</option>
											<option value="N" <?php if ($allow_mutiple_pics == "N") echo " selected";?>>No</option>
										</select>
									</td>
								</tr>
								<tr>
									<td width=70% style="padding-left:20%">Do you want to save picture into database?</td>
									<td width=30%>
										<select name="save_pic_to_database" id="save_pic_to_database">
											<option value="Y" <?php if ($save_pic_to_database == "Y") echo " selected";?>>Yes</option>
											<option value="N" <?php if ($save_pic_to_database == "N") echo " selected";?>>No</option>
										</select>
									</td>
								</tr>								
								<tr>
									<td colspan=2 style="text-align:center"><br>
										<input type="submit" name="generate" value="Generate File" class=dark_green_button>
									</td>
								</tr>
							</table>
							</center>
						</td>
					</tr>
					<tr>
						<td colspan=2><br>
							<center><div class="displayNumberTitles">Total Records: <?php echo $row_count; ?></div></center>
						</td>
					</tr>
					<tr>
						<td>
							<br>
						</td>
					</tr>
					<?php
					if (!isset($errorMsg) and $row_count > 0) {
					?>
					<tr>
						<td>
							<table width=100% style="background-color:white" >
								<?php
								for ($row=0; $row < count($mydata); $row++) {
									if ($mydata[$row]['active'] == "Y") {
										$colorStatus = "green";
									} else {	
										$colorStatus = "red";
									}
								echo "<tr>\n
									<td width=70% style=\"vertical-align:middle !important;\">
										<table width=100%>\n
											<tr>
												<td colspan=2 class=\"mainSmallTitle\">";
													if (!empty($mydata[$row]['middle_name'])) {
														echo "<b>".$mydata[$row]['first_name']."</b> ".$mydata[$row]['middle_name']." <b>".$mydata[$row]['last_name']."</b>";
													} else {
														echo "<b>".$mydata[$row]['first_name']." ".$mydata[$row]['last_name']."</b>";
													}
												echo "</td>
											</tr>
											<tr>
												<td>
													<b>UNC Charlotte ID</b>: <font color=blue>".$mydata[$row]['patron_number']."</font>".
												"</td>
												<td>
													<b>HR Classification</b>: ".$mydata[$row]['hr_classification'].
												"</td>	
											</tr>
											<tr>
												<td>
													<b>Gender</b>: ".$mydata[$row]['gender'].
												"</td>
												<td>
													<b>Primary Classification</b>: ".$mydata[$row]['primary_classification'].
												"</td>
											</tr>	
											<tr>
												<td>
													<b>Ferpa Flag</b>: ".$mydata[$row]['ferpa_flag'].
												"</td>
												<td>
													<b>Last Modified Date</b>: ".date($datetime_format, strtotime($mydata[$row]['last_mod_datetime'])).
												"</td>
											</tr>									
										</table>
									</td>
									<td width=10% style=\"vertical-align:middle !important;\"><center><div style=\"width:20px; height:20px; border-radius:10px; background-color:".$colorStatus."\"></div></center></td>\n
									<td width=10% style=\"vertical-align:middle !important;\"><img src=\"".$mydata[$row]['pic_location']."\" alt=\"Edit\" width=\"100\" height=\"120\"></td>\n
								</tr>";
								echo "<tr>
									<td colspan=\"5\" style=\"padding-top:5px;padding-bottom:10px;\">
										<hr style=\"height:2px;background-color:green;\">
									</td>
								</tr>";
								}
								$pagination->render();
								?>	
							</table>
						</td>
					</tr>
					<?php
					//$pagination->render();
					}
					?>
				</table>
				<?php $pagination->render(); ?>
			</form>
			</div>
			<BR><BR><BR>
			<?php include ("./../web/footer.html"); ?>
			<?php
		} else {
			include ("./../web/header.html");
			include ("./../web/menu.html");
			?>
			<link href="./../css/mainstyle.css" rel="stylesheet" type="text/css">
			<div style="width:80%; font-family:Arial, Helvetica, sans-serif;font-size:12px;line-height:18px; border: 0px solid black; margin: 0px auto;background-color:white">
			<br><br>
			<table width=100%>
				<tr>
					<td><br>
						<div class="mainTitle"><strong><center>Photo Access ID - Generate Sample File</center></strong></div>
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
} else {
	include ("./../web/header.html");
?>	
	<link href="./../css/mainstyle.css" rel="stylesheet" type="text/css">
	<div style="width:80%; font-family:Arial, Helvetica, sans-serif;font-size:12px;line-height:18px; border: 0px solid black; margin: 0px auto;background-color:white">
	<table  width=100% style="padding:25px; text-align:center; background-color:white;">
		<tr>
			<td><br>
				<div class="mainTitle"><strong><center>Photo Access ID - Generate Sample File</center></strong></div><br>
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
	include ("./../web/footer.html");
}
?>