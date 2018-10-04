<?php
include("../install/database_credentials.inc");
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

$db->set_charset($DATABASE_CHARSET);		
require './../lib/Zebra_Session-master/Zebra_Session.php';
	
$link = $db->get_link();
$security_code = $SESSION_SECURITY;
$session_lifetime = $SESSION_TIMEOUT;
$session = new Zebra_Session($link, $security_code, $session_lifetime);
//echo "session: ".$_SESSION['photo_access']['login']['session_id'];
//exit();

$timeNow = date("Y-m-d H:i:s", strtotime("now"));
//echo "time: ".$timeNow;
//exit();
$db->update(
	'photo_access_user_login_log',
	array(
		'date_time_out'   =>  date("Y-m-d H:i:s", strtotime("now")),
		'session_id'   =>  ''
	),
    'session_id = ?',
    array($_SESSION['photo_access']['login']['session_id'])
	);

$session->stop();
//session_start();
//session_unset();
//session_destroy();
header("location: ./../");
?>
