<?
if(!defined('__PRAGYAN_CMS'))
{ 
	header($_SERVER['SERVER_PROTOCOL'].' 403 Forbidden');
	echo "<h1>403 Forbidden<h1><h4>You are not authorized to access the page.</h4>";
	echo '<hr/>'.$_SERVER['SERVER_SIGNATURE'];
	exit(1);
}
global $sourceFolder;
global $moduleFolder;
global $urlRequestRoot;
$calpath="$sourceFolder/$moduleFolder";
?>
<link rel="stylesheet" type="text/css" media="all" href="<?=$calpath?>/calendar.css" title="Aqua"" />
<script type="text/javascript" src="<?=$calpath?>/calendar.js"></script>
<b>Date #1:</b> <input type="text" name="date1" id="sel1" size="30"><input type="reset" value=" ... " onclick="return showCalendar('sel1', '%a, %b %e, %Y [%I:%M %p]', '12', true);"> %a, %b %e, %Y [%I:%M %p]-- single click<br />
