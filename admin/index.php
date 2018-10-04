<?php
/*
 *******************************************************************************************************
*
* Name: index.php
* Index in admin folder. Will redirect to index.php front page
* Writer: Bach Nguyen (bnguye21@uncc.edu)
* Last Updated: 02/25/2015
*		- Created file
*
 ********************************************************************************************************
 */
	$page_link = "index.php";
	$reDirectLocation = "Location: ./../web/login.php?page_link=".$page_link;
	//echo "reDirection Location: '".$reDirectLocation."'<br>";
	header($reDirectLocation);
	exit();
?>