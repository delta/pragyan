<?php

/**
 * Created on Sep 29, 2007
 * breadcrumbs.lib.php
 * To generate the bread crumbs required to show the location of the page with links to each level.

 * ankit
 * Generate breadcrumbs for a given page
 *
 * @param $pageIdArray Array of integers holding the page ids of the pages constituting the path to the current page
 * @param $separator The string with which Items in the generated breadcrumbs should be separated
 * @return HTML string representing the breadcrumbs to be displayed for the given page
 */
function breadcrumbs($pageIdArray, $separator = '&raquo;') {
	$sqlOutputArray = array();
	$pageIdList = join($pageIdArray, ",");
	$str = '<div id="breadcrumb">';
	$query = "SELECT `page_id`, `page_title` FROM `".MYSQL_DATABASE_PREFIX."pages` WHERE `page_id` IN ($pageIdList)";
	$resultId = mysql_query($query);

	while($temp = mysql_fetch_assoc($resultId)) {
		$sqlOutputArray[$temp['page_id']] = $temp['page_title'];
	}

	$hrefString = "";
	$breadCrumbs = array();

	for($i = count($pageIdArray) - 1; $i >= 0; $i--) {
		$breadCrumbs[] = ' <span class="breadcrumbItem"><a href="'.$hrefString.'+view">'.$sqlOutputArray[$pageIdArray[$i]].'</a></span> ';
    $hrefString = '../'.$hrefString;
	}

	$str .= join(array_reverse($breadCrumbs), $separator);
	$str .= '</div>';

	return $str;
}

?>