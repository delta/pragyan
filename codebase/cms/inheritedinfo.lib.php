<?php
if(!defined('__PRAGYAN_CMS'))
{ 
	header($_SERVER['SERVER_PROTOCOL'].' 403 Forbidden');
	echo "<h1>403 Forbidden<h1><h4>You are not authorized to access the page.</h4>";
	echo '<hr/>'.$_SERVER['SERVER_SIGNATURE'];
	exit(1);
}
/**
 * @package pragyan
 * @copyright (c) 2010 Pragyan Team
 * @license http://www.gnu.org/licenses/ GNU Public License
 * For more details, see README
 */

/**
 * inheritedinfo.lib.php: Returns the header of the page if it has been set by the user for some
 * 						  specific page in the page path. Inherits it from tree.
 */

/**
* Input : array of page id's which constitute the page path
* Output : header, inherited from the tree path.
*
* @param $array array Array containing the page id's, passed as input to the function.
*/

function inheritedinfo($array) {

	$query = "SELECT `page_inheritedinfoid` FROM `" . MYSQL_DATABASE_PREFIX . "pages` WHERE `page_id` IN(" . join($array, ",") . ")";
	$data = mysql_query($query);
	$inheritedinfoid = -1;
	while ($temp = mysql_fetch_assoc($data))
		if ($temp['page_inheritedinfoid'] != -1)
			$inheritedinfoid = $temp['page_inheritedinfoid'];
	if ($inheritedinfoid != -1) {
		$query = "SELECT `page_inheritedinfocontent` FROM `" . MYSQL_DATABASE_PREFIX . "inheritedinfo` WHERE `page_inheritedinfoid` = '$inheritedinfoid'";
		$data = mysql_query($query);
		$temp = mysql_fetch_assoc($data);
	}
	$inheritedinfocontent = $temp['page_inheritedinfocontent'];
	return $inheritedinfocontent;
}

