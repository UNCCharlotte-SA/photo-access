<?php include ("./web/header.html");
/*
 *******************************************************************************************************
*
* Name: thank_you.php
* Display message after user submit the form
* Writer: Bach Nguyen (bnguye21@uncc.edu)
* Last Updated: 08/15/2014
*
 ********************************************************************************************************
 */
ini_set('session.bug_compat_warn', 0);
ini_set('session.bug_compat_42', 0);
?>
<?php
if(!isset($_SESSION)) {
	session_start();
} 
if (isset($_SESSION['user'])) {
	include ("./web/menu.html");
}
?>

<link href="./css/mainstyle.css" rel="stylesheet" type="text/css">
<table  width=100% style="padding:25px; text-align:center; background-color:white;">
	<tr>
		<td><br>
			<div class="mainTitle"><strong><center>Photo Access ID - Request Access</center></strong></div><br>
		</td>
	</tr>
	<tr>
		<td><br>
			<div style="color:#FF0000; text-align:center; background-color:#FFFFFF">
				Thank you for requesting access to Photo Access ID.  We will email soon.
			</div> 
		</td>
	</tr>
	<tr>
		<td>
			<br><br><br>
		</td>
	</tr>
</table>
<?php include ("./web/footer.html"); ?>

	