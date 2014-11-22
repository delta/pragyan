<?php
require_once('config.inc.php');

for($i=30022;$i<=31000;$i+=1){
	$insertQuery="INSERT INTO `festemberV3_users`(`user_id`) VALUES($i)";
	$insertRes=mysql_query($insertQuery) or displayerror(mysql_error());
}

?>