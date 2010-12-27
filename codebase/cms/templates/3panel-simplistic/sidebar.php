<?php
if(!defined('__PRAGYAN_CMS'))
{ 
	header($_SERVER['SERVER_PROTOCOL'].' 403 Forbidden');
	echo "<h1>403 Forbidden<h1><h4>You are not authorized to access the page.</h4>";
	echo '<hr/>'.$_SERVER['SERVER_SIGNATURE'];
	exit(1);
}

global $SIDEBARCONTENT;
$date = date("l d M , h:ia");
$SIDEBARCONTENT = "<center id=\"sidebarcontent\">$date</center>";
?>
