<?php
	$dirFileName=$_GET["filename"];
	//echo "dirFileName: ".$dirFileName."<br>";
	$treeFilename = explode("upload/",$dirFileName);
	if (explode("upload/",$dirFileName)) {
		$filename = $treeFilename[sizeof($treeFilename)-1];
	} else {
		$filename = $dirFileName;
	}
	//echo "Filename: ".$filename."<br>";
	//exit;
	header('Content-Type: text/plain');
	header("Content-disposition: attachment;filename=".$filename);
	readfile($dirFileName);
?>