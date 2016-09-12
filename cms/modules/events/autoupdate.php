<?php
require_once('config.inc.php');

for($i=30022;$i<=31000;$i+=1){
	$insertQuery="INSERT INTO `festemberV3_users`(`user_id`) VALUES($i)";
	$insertRes=mysqli_query($GLOBALS["___mysqli_ston"], $insertQuery) or displayerror(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
}

?>