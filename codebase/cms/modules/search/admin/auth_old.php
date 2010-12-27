<?php 
if(!defined('__PRAGYAN_CMS'))
{ 
	header($_SERVER['SERVER_PROTOCOL'].' 403 Forbidden');
	echo "<h1>403 Forbidden<h1><h4>You are not authorized to access the page.</h4>";
	echo '<hr/>'.$_SERVER['SERVER_SIGNATURE'];
	exit(1);
}
	error_reporting(E_ERROR | E_PARSE);	
	$admin = "admin";
	$admin_pw = "admin";
	
	if (!isset($_SERVER['PHP_AUTH_USER']) && !(getenv("REMOTE_ADDR")==""))  {

		// If empty, send header causing dialog box to appear

		header('WWW-Authenticate: Basic realm="Sphider admin tools"');
		header('HTTP/1.0 401 Unauthorized');
		echo 'Authorization Required.';
		exit();

	} else if (!(getenv("REMOTE_ADDR")=="")){
		if (($_SERVER['PHP_AUTH_USER'] != $admin) || ($_SERVER['PHP_AUTH_PW'] != $admin_pw)) {
			header('WWW-Authenticate: Basic realm="Sphider admin tools"');
			header('HTTP/1.0 401 Unauthorized');
			echo 'Authorization Required.';
			exit();
		} 
	} 
	$include_dir = "../include";
    include "$include_dir/connect.php";

?>
