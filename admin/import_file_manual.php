<?php
/*
 *******************************************************************************************************
*
* Name: import_file_manual.php
* Import data into patron table
* Writer: Bach Nguyen (bnguye21@uncc.edu)
* Last Updated: 02/20/2015
*	02/20/2015
*		- Added ability not to save picture into database
*	02/16/2015
*		- Changed mysql class, session class
*		- Changed codes 
*	12/10/2014
*		- Created new file
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
		//echo "<br><br>user name: ".$username." - User group: ".$userGroup." - Department: ".$department." - Lastname, Firstname: ".$last_name.", ".$first_name."<br>";
		//echo "number per page: ".$number_per_page." - date format: ".$date_format."<br>";
		//echo "self approved: ".$self_approved." - policy renew: ".$policy_renew."<br>";
		date_default_timezone_set($time_zone);
		//echo date_default_timezone_get();
		
		
		if (!empty($_GET['uploadFileFormat'])) {
			$uploadFileFormat = $_GET['uploadFileFormat'];
			//echo "uploadFileFormat: ".$uploadFileFormat."<br>";
		}
	
		if (!empty($_GET['isHeader'])) {
			$isHeader = $_GET['isHeader'];
			//echo "isHeader: ".$isHeader."<br>";
		}
		
		if (!empty($_GET['savePictoDatabase'])) {
			$savePictoDatabase = $_GET['savePictoDatabase'];
			//echo "savePictoDatabase: ".$savePictoDatabase."<br>";
		}

		if (!empty($_GET['downloadFileFormat'])) {
			$downloadFileFormat = $_GET['downloadFileFormat'];
			//echo "downloadFileFormat: ".$downloadFileFormat."<br>";
		}
	
		if (!empty($_GET['import_To_Photo_Access'])) {
			$import_To_Photo_Access = $_GET['import_To_Photo_Access'];
			//echo "import_To_Photo_Access: ".$import_To_Photo_Access."<br>";
		}
	
		if (!empty($_GET["error"])) {
			$errorMsg = $_GET["error"];
			//echo "errorMsg: ".$errorMsg."<br>";
		}
	
		?>
		<script type="text/javascript">
			function ShowHide(divId) {
				if(document.getElementById(divId).style.display == 'none') {
					document.getElementById(divId).style.display='block';
				} else {
					document.getElementById(divId).style.display = 'none';
				}
			}

			function fileSelected() {
				var file = document.getElementById('uploadFilename').files[0];
				if (file) {
					var fileSize = 0;
					if (file.size > 1024 * 1024)
						fileSize = (Math.round(file.size * 100 / (1024 * 1024)) / 100).toString() + 'MB';
					else
						fileSize = (Math.round(file.size * 100 / 1024) / 100).toString() + 'KB';

					alert('Name: ' + file.name);
					var fname = file.name;
					alert('Size: ' + fileSize);
					alert('Type: ' + file.type);
					var extension = fname.substr((~-fname.lastIndexOf(".") >>> 0) + 2);
					alert('extention: ' +  extension);
				}
			}

			function checkValidator(theForm) {
				var input, file;
				// (Can't use `typeof FileReader === "function"` because apparently
				// it comes back as "object" on some browsers. So just see if it's there
				// at all.)
				if (!window.FileReader) {
					//alert( "The file API isn't supported on this browser yet.  Try other browser!");
					document.getElementById("errorMsg").innerHTML = "The file API isn't supported on this browser yet.  Try other browser!";
					return false;
				}

				input = document.getElementById('uploadFilename');
				if (!input) {
					//alert( "Couldn't find the fileinput element.");
					document.getElementById("errorMsg").innerHTML = "Couldn't find the file input element.";
					return false;
				} else if (!input.files) {
					//alert( "This browser doesn't seem to support the `files` property of file inputs.");
					document.getElementById("errorMsg").innerHTML = "This browser doesn't seem to support the `files` property of file inputs.";
					return false;
				} else if (!input.files[0]) {
					//alert( "Please select a Serials Solutions file before clicking 'Submit'");
					document.getElementById("errorMsg").innerHTML = "Please select an upload file before clicking 'Submit'";
					return false;
				} else {
					var fileSize = 0;
					file = input.files[0];
					//alert( "File " + file.name + " is " + file.size + " bytes in size");
					if (file.size > 1024 * 1024)
						fileSize = (Math.round(file.size * 100 / (1024 * 1024)) / 100);
					else
						fileSize = (Math.round(file.size * 100 / 1024) / 100);
						//alert ("file Size: " + fileSize);		
					if (fileSize > 127000) {
						//alert ("Please select a file which has size less than 128M");
						document.getElementById("errorMsg").innerHTML = "Please select a file which has size less than 128M";
						return false;
					}
					var fname = file.name;
					//alert('Size: ' + fileSize);
					//alert('Type: ' + file.type);
					var extension = fname.substr((~-fname.lastIndexOf(".") >>> 0) + 2);
					//alert('extention: ' +  extension);
					if (extension != "txt" & extension != "csv") {
						//alert ("Please select a file with extension: 'txt' or 'csv'");
						document.getElementById("errorMsg").innerHTML = "Please select a file with extension: 'txt' or 'csv'";
						return false;
					}
				}
	
				// require at least one radio button be selected
				var radioSelected = false;
				for (i = 0;  i < theForm.isHeader.length;  i++)	{
					if (theForm.isHeader[i].checked)
						radioSelected = true;
				}
	
				if (theForm.import_To_Photo_Access.checked & !radioSelected) {
					//alert("Please select 'Is upload file included header?'");
					document.getElementById("errorMsg").innerHTML = "Please select Yes or No in 'Is upload file included header?' section";
					return false;
				}
	
				if (!theForm.import_To_Photo_Access.checked & theForm.uploadFileFormat.selectedIndex == theForm.downloadFileFormat.selectedIndex) {
					//alert("You selected the same delimiter in Download");
					document.getElementById("errorMsg").innerHTML = "You selected the same delimiter in Download";
					return false;
				}
	
				return true;
			}
		</script>
		<?php include ("./../web/header.html"); ?>
		<?php include ("./../web/menu.html"); ?>
		<link href="./../css/mainstyle.css" rel="stylesheet" type="text/css">
		<div style="width:75%; font-family:Arial, Helvetica, sans-serif;font-size:12px;line-height:18px; border: 0px solid black; margin: 0px auto;background-color:white">
		<br>
		<br>
		<table align="center" width=100% style="padding:25px; text-align:center; background-color:white">
			<tr>
				<td><br>
					<div class="mainTitle"><strong><center>Photo Access ID - Manual Import File</center></strong></div>
					<div style="color:#FF0000; text-align:center; background-color:#FFFFFF" id="errorMsg">
						<?php if (!empty($errorMsg)) echo $errorMsg; ?>
					</div> 
					<br>
				</td>
			</tr>
		
			<tr>
				<!--
				<form action="import_file_manual_handler" method="post"	enctype="multipart/form-data" name"mainForm">
				<form action="import_file_manual_handler" method="post"	enctype="multipart/form-data" onsubmit="return checkValidator(this)" name"mainForm">
				-->
				<form action="import_file_manual_handler.php" method="post"	enctype="multipart/form-data" onsubmit="return checkValidator(this)" name"mainForm">
				<td><center>
					<table width=100% style="border-collapse: separate; border:2px #7A7777 solid; padding:10px; background-color:white" class="graybox">
						<tr>
							<td width=50%><div class="tableHeader"><center>Upload</center></div></td>
							<td width=5%></td>
							<td width=45%><div class="tableHeader"><center>Download</center></div></td>
						</tr>
						<tr>
							<td>
								<br>
								<label>File name:</label><br>
								<input type="file" name="uploadFilename" id="uploadFilename" class="upload_file_container"><br><br>
								<label>Select file upload delimiter format:</label>
								<select name="uploadFileFormat" id="uploadFileFormat">
									<option value="1" <?php if (!empty($uploadFileFormat) and $uploadFileFormat == 1) echo "selected"; ?>>Tab</option>
									<option value="2" <?php if (!empty($uploadFileFormat) and $uploadFileFormat == 2) echo "selected"; ?>>Comma</option>
									<option value="3" <?php if (!empty($uploadFileFormat) and $uploadFileFormat == 3) echo "selected"; ?>>Semicolon</option>
								</select><br><br>
								<label>Import to Photo Access ID? </label>
								<INPUT type="checkbox" NAME="import_To_Photo_Access" VALUE="Yes" <?php if (!empty($import_To_Photo_Access) and $import_To_Photo_Access == "Yes") echo "checked"; ?> onClick="ShowHide('hiddenDiv')"><br />
							</td>
							<td>
							</td>
							<td>
								<br>
								<label>File name:</label><br>
								<input type="text" name="downloadFilename" id="downloadFilename" size=50 value="<?php if (!empty($downloadFilename)) echo $downloadFilename; ?>"><br><br>
								<label>Select file download delimiter format:</label>
								<select name="downloadFileFormat" id="downloadFileFormat">
									<option value="1" <?php if (!empty($downloadFileFormat) and $downloadFileFormat == 1) echo "selected"; ?>>Tab</option>
									<option value="2" <?php if (!empty($downloadFileFormat) and $downloadFileFormat == 2) echo "selected"; ?>>Comma</option>
									<option value="3" <?php if (!empty($downloadFileFormat) and $downloadFileFormat == 3) echo "selected"; ?>>Semicolon</option>
								</select> <br><br>
								<div id="hiddenDiv" style="DISPLAY: <?php if (!empty($convertToKbart) and $convertToKbart == "Yes") echo "block"; else echo "none"; ?>">
								<label>Is upload file included header?</label>
									<input type="radio" name="isHeader" id="isHeader" value="Y" <?php if (!empty($isHeader) and $isHeader == "Y") echo "checked='true'"; ?>>Yes
									<input type="radio" name="isHeader" id="isHeader" value="N" <?php if (!empty($isHeader) and $isHeader == "N") echo "checked='true'"; ?>>No<br><br>	
								<label>Save Picture into Database?</label>
									<input type="radio" name="savePictoDatabase" id="savePictoDatabase" value="Y" <?php if (!empty($savePictoDatabase) and $savePictoDatabase == "Y") echo "checked='true'"; ?>>Yes
									<input type="radio" name="savePictoDatabase" id="savePictoDatabase" value="N" <?php if (!empty($savePictoDatabase) and $savePictoDatabase == "N") echo "checked='true'"; ?>>No<br><br>		
								</div>
							</td>
						<tr>
							<td colspan = "3" style="text-align:center"><br>
								<input type="submit" name="submit" value="Submit" class="dark_green_button">
								<!--
								<input type="submit" name="submit" value="Submit">
								-->
							</td>
						</tr>
					</table></center>
				</td>
				</form>	
			</tr>
		
			<tr>
				<td>
					<center>
					<table width=80%>
						<tr>
							<td><br>
								<p style="text-align:left"><br>
								You can use this application to:
								<ol class='ol'>
									<li>Convert a text delimiter file to another text delimiter file.</li>
									<li>Import a file into Photo Access ID database</li>
								</ol>
								<b><u>Note</u></b>: This applications only excepts:
								<ol class='ol'>
									<li>File extension: csv, txt.</li>
									<li>Text format using tab, comma, or semicolon separated values</li>
									<li>Supported Browsers: Internet Explorer, Firefox, Chrome, and Safari</li>
								</ol>
								</p>
								<br />
							</td>
						</tr>
					</table></center>
				</td>
			</tr>
			<tr>
				<td>
					<br>
				</td>
			</tr>
		</table>
		</div>
		<?php include ("./../web/footer.html"); ?>
	<?php	
		
		
	} else {
		$page_link = "./index.php";
		$reDirectLocation = "Location: ./web/login.php?page_link=".$page_link;
		//echo "reDirection Location: '".$reDirectLocation."'<br>";
		header($reDirectLocation);
		exit();	
	}
} else {
	include ("./web/header.html");
?>	
	<link href="./css/mainstyle.css" rel="stylesheet" type="text/css">
	<div style="width:80%; font-family:Arial, Helvetica, sans-serif;font-size:12px;line-height:18px; border: 0px solid black; margin: 0px auto;background-color:white">
	<table  width=100% style="padding:25px; text-align:center; background-color:white;">
		<tr>
			<td><br>
				<div class="mainTitle"><strong><center>Photo Access ID - Manual Import File</center></strong></div><br>
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
	include ("./web/footer.html");
}
?>