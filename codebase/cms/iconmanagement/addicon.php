<?php
/**
 * @package pragyan
 * @copyright (c) 2010 Pragyan Team
 * @author boopathi
 * @license http://www.gnu.org/licenses/ GNU Public License
 * For more details, see README
 */

$cmsFolder="cms";
$sourceFolder = substr($_SERVER['SCRIPT_FILENAME'], 0, strrpos($_SERVER['SCRIPT_FILENAME'], '/'));
$templateFolder = "templates";

require_once("$sourceFolder/../config.inc.php");
require_once("$sourceFolder/../common.lib.php");

$rootUri = selfURI();	

if(isset($_GET["iconURL"]) && isset($_GET['targetId'])) {
	$iconURL = escape($_GET["iconURL"]);
	$target = escape($_GET["targetId"]);
	
	connect();
	mysql_query("UPDATE `".MYSQL_DATABASE_PREFIX."pages` SET `page_image`='$iconURL' WHERE `page_id`='$target'");
	$pageDetails = getPageInfo($target);
	echo "<img src=\"../$cmsFolder/$templateFolder/common/icons/16x16/status/weather-clear.png\" /> ";
	echo $pageDetails["page_name"];
	
}
else if(isset($_GET['iconAction'])) {
	$action = $_GET['iconAction'];
	
}
else
{
	die("Restricted access");
}
