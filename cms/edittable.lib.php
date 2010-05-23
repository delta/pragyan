<?php
/**
 * @package pragyan
 * @copyright (c) 2008 Pragyan Team
 * @license http://www.gnu.org/licenses/ GNU Public License
 * For more details, see README
 */

/**
 * @param array $primaryField Array of names of primary fields
 */
function editDb($tablename, $page_modulecomponentid, $primaryField,$allowDelete, $allowEdit) {
	$query = "SELECT * FROM $tablename LIMIT 0,1 ";
	$result = mysql_query($query);
	$table =<<<TAB
			<html>
			<head><link rel="stylesheet" type="text/css"
			href="./templates/common/pma.css" /></head>
			<body>
			<table id="table_results" class="data">
			<thead><tr>
			    <th colspan="3">
			</th>
TAB;

	while ($temp = mysql_fetch_array($result, MYSQL_ASSOC)) {
		foreach ($temp as $var => $val) {
			if ($var != page_modulecomponentid)
				$table .= "<th>" .
				$var . "</th>";
		}
	}
	$table .= "</tr>";

	$query = "SELECT * FROM $tablename WHERE
	page_modulecomponentid=$page_modulecomponentid ";
	$result = mysql_query($query);
	$even = "even";
	while ($temp = mysql_fetch_array($result, MYSQL_ASSOC)) {
		$table .= "<tr class=\"$even\">";
		$table .=<<<TAB
    <td align="center">
        <input type="checkbox" id="id_rows_to_delete3" name="del"/>
    </td>
    <td align="center">
	    <img src="./templates/common/images/b_edit.png">
    </td>
    <td align="center">
    	<img src="./templates/common/images/b_drop.png">
    </td>
TAB;
		foreach ($temp as $var => $val) {
			if ($var != page_modulecomponentid)
				$table .= "<td>" .
				$val . "</td>";
		}
		$table .= "</tr>";
		if ($even == "even")
			$even = "odd";
		else
			$even = "even";
	}
	
	echo hi;
	return $table;
}

