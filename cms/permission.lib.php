<?php
/**
 * permission.lib.php: Everything to do with page and module permissions
 *
 * Created on Sep 28, 2007, 9:13:53 PM
 * abhilash #:-S
 *
 *
 * TODO: One should also be able to View a resultant "group" permission table. Like we have for users now.
 * 		Coz one needs to be able to see the resultant permission set for a not logged in user right?.
 * DONE: call getPermissionTable passing a negative userid and as many group ids as you need as the groupids array
 */



/**
 * Generates a list of usernames, and email ids that match a given pattern
 * @param $pattern Pattern for which suggestions must be found
 * @return string A comma separated string ready to be sent back to the client
 */
function getSuggestions($pattern) {
	$suggestionsQuery = "SELECT IF(user_email LIKE \"$pattern%\", 1, " .
			"IF(`user_fullname` LIKE \"$pattern%\", 2, " .
			"IF(`user_fullname` LIKE \"% $pattern%\", 3, " .
			"IF(`user_email` LIKE \"%$pattern%\", 4, " .
			"IF(`user_fullname` LIKE \"%$pattern%\", 5, 6" .
			"))))) AS `relevance`,	`user_email`, `user_fullname` FROM `".MYSQL_DATABASE_PREFIX."users` WHERE " .
			"  `user_activated`=1 AND(`user_email` LIKE \"%$pattern%\" OR `user_fullname` LIKE \"%$pattern%\" ) ORDER BY `relevance`";
//			echo $suggestionsQuery;
	$suggestionsResult = mysql_query($suggestionsQuery);

	$suggestions = array($pattern);

	while($suggestionsRow = mysql_fetch_row($suggestionsResult)) {
		$suggestions[] = $suggestionsRow[1] . ' - ' . $suggestionsRow[2];
	}

	return join($suggestions, ',');
}



/**
 * Generates a table showing the permissions of all users and visible groups on a particular page
 * @param $pagepath Array containing the path to the current page
 * @param $modifiableGroups Array containing the groups for which permissions are to be viewed
 * @param $grantableActions Array containing the module => actions for which permissions must be shown
 */
function getAllPermissionsOnPage($pagepath, $modifiableGroups, $grantableActions) {
	/// $grantableActions is of the form:
	///   $grantableActions['moduleName_i'] =
	///			array(
	///				array1(permid, actionname, actiondescription),
	///				array2(permid, actionname, actiondescription)
	///			)

	/// Retrieve Ids and Names of all groups
	$groupIds = array(0, 1);
	$groupNames = array('0' => 'Everyone', '1' => 'Logged In Users'); ///< Associative array relative group ids to group names
	$groupCount = 2;
	$groupsQuery = 'SELECT `group_id`, `group_name` FROM `' . MYSQL_DATABASE_PREFIX . 'groups`';
	$groupsResult = mysql_query($groupsQuery);
	while($groupsRow = mysql_fetch_row($groupsResult)) {
		$groupIds[] = $groupsRow[0];
		$groupNames[$groupsRow[0]] = $groupsRow[1];
		$groupCount++;
	}
	mysql_free_result($groupsResult);

	/// Retrieve Ids and Names of all users
	$userIds = array(0);
	$userNames = array('0' => 'Anonymous');
	$userCount = 1;
	$usersQuery = 'SELECT `user_id`, `user_name` FROM `' . MYSQL_DATABASE_PREFIX . 'users`';
	$usersResult = mysql_query($usersQuery);
	while($usersRow = mysql_fetch_row($usersResult)) {
		$userNames[$usersRow[0]] = $usersRow[1];
		$userIds[] = $usersRow[0];
		$userCount++;
	}
	mysql_free_result($usersResult);

	/// $permList: Array of the form
	///		$permList[$permId] = array($moduleName, $actionName, $actionDescription)
	$permIds = array();
	$permCount = 0;
	$permList = array();
	foreach($grantableActions as $moduleName => $actionData) {
		if(is_array($actionData) && ($actionCount = count($actionData)) > 0) {
			for($i = 0; $i < $actionCount; $i++) {
				$permList[$actionData[$i][0]] = array($moduleName, $actionData[$i][1], $actionData[$i][2]);
				$permIds[] = $actionData[$i][0];
				$permCount++;
			}
		}
	}

	if(count($permList) <= 0 || count($pagepath) <= 0) {
		displayerror('Fatal Error: Missing arguments to function.');
		return;
	}

	/// Retrieve all the permissions set on the page path
	/// $groupSetPermissions and $userSetPermissions are arrays of the form
	///   $<user/group>SetPermissions[pageid][<user/group>id][permid] = true / false / unset
	/// This array will be used later to compute $groupEffectivePermissions and $userEffectivePermission
	$groupSetPermissions = array();
	$userSetPermissions = array();

	$userPermTable = '`' . MYSQL_DATABASE_PREFIX . 'userpageperm`';
	$permListTable = '`' . MYSQL_DATABASE_PREFIX . 'permissionlist`';
	$permQuery = "SELECT `perm_type`, $userPermTable.`perm_id` AS `perm_id`, `page_id`, `usergroup_id`, `perm_permission` " .
	             "FROM $userPermTable, $permListTable WHERE `page_id` IN (" . join($pagepath, ', ') . ") AND " .
	             "$userPermTable.`perm_id` IN (" . join($permIds, ', ') .
	             ") AND $userPermTable.`perm_id` = $permListTable.`perm_id`";
	$permResult = mysql_query($permQuery);

	while($permRow = mysql_fetch_assoc($permResult)) {
		$pageId = $permRow['page_id'];
		$permId = $permRow['perm_id'];
		$usergroupId = $permRow['usergroup_id'];

		$setPermissions = &$groupSetPermissions;
		if($permRow['perm_type'] == 'user') {
			$setPermissions = &$userSetPermissions;
		}

		if(!isset($setPermissions[$pageId])) {
			$setPermissions[$pageId] = array();
		}
		if(!isset($setPermissions[$pageId][$usergroupId])) {
			$setPermissions[$pageId][$usergroupId] = array();
		}
		$setPermissions[$pageId][$usergroupId][$permId] = $permRow['perm_permission'] == 'Y' ? true : false;
	}

	/// Now, compute effective permissions for all groups.
	/// Computing for groups first will make things easier for users (yeah, right!)
	$groupEffectivePermissions = array();
	/// Loop 1 counts down through page numbers.
	/// Loop 2 takes each group
	/// Loop 3 takes each permission
	/// Inside loop three, if the groupSetPermissions for pageid, groupid, permid is set,
	///  check if groupEffectivePermissions has been set for that groupid and permid
	///    Yes: If groupEffectivePermissions is false, leave it as such. Otherwise, copy setPerm
	///    No:  copySetPermissions
	///
	/// $pSP stands for SetPermissions for that particular pageId and
	/// $gSP stands for SetPermissions for that particular groupId on that pageId.
	/// $pSP is a 2D array, and $gSP is a 1D array, respectively (see their initializations)
	/// $gEP stands for Effective Permissions for a group on the current page
	///      as calculated so far
	/// pSP, gSP and gEP are aimed at reducing the number of times the 3D array needs to be indexed
	///	and at making the code a little easier to read
	for($i = count($pagepath) - 1; $i >= 0; $i--) {
		if(!isset($groupSetPermissions[$pagepath[$i]])) continue;
		$pSP = &$groupSetPermissions[$pagepath[$i]];

		for($j = 0; $j < $groupCount; $j++) {
			if(!isset($pSP[$groupIds[$j]])) continue;
			$gSP = &$pSP[$groupIds[$j]];
			if(!isset($groupEffectivePermissions[$groupIds[$j]]))
				$groupEffectivePermissions[$groupIds[$j]] = array();
			$gEP = &$groupEffectivePermissions[$groupIds[$j]];

			for($k = 0; $k < $permCount; $k++) {
				if(isset($gSP[$permIds[$k]])) {
					if(!isset($gEP[$permIds[$k]]) || $gEP[$permIds[$k]] !== false) {
						$gEP[$permIds[$k]] = $gSP[$permIds[$k]];
					}
				}
			}
		}
	}

	/// Now to compute the effective permissions for the users
	$userEffectivePermissions = array();

	for($i = count($pagepath) - 1; $i >= 0; $i--) {
		if(!isset($userSetPermissions[$pagepath[$i]])) continue;
		$pSP = &$userSetPermissions[$pagepath[$i]];

		for($j = 0; $j < $userCount; $j++) {
			if(!isset($pSP[$userIds[$j]])) continue;
			$uSP = &$pSP[$userIds[$j]];
			if(!isset($userEffectivePermissions[$userIds[$j]]))
				$userEffectivePermissions[$userIds[$j]] = array();
			$uEP = &$userEffectivePermissions[$userIds[$j]];

			for($k = 0; $k < $permCount; $k++) {
				if(isset($uSP[$permIds[$k]])) {
					if(!isset($uEP[$permIds[$k]]) || $uEP[$permIds[$k]] !== false) {
						$uEP[$permIds[$k]] = $uSP[$permIds[$k]];
					}
				}
			}
		}
	}

	/// Get all the groups each user belongs to
	$userGroups = array();
	$groupsQuery = 'SELECT `user_id`, `group_id` FROM `'.MYSQL_DATABASE_PREFIX.'usergroup` ' .
	               'ORDER BY `user_id`';
	$groupsResult = mysql_query($groupsQuery);
	while($groupsRow = mysql_fetch_row($groupsResult)) {
		if(!isset($userGroups[$groupsRow[0]])) $userGroups[$groupsRow[0]] = array();
		$userGroups[$groupsRow[0]][] = $groupsRow[1];
	}
	mysql_free_result($groupsResult);


	/// Calculate permissions as far as groups are concerned.
	for($i = 0; $i <= $userCount; $i++) {
		if(!isset($userGroups[$userIds[$i]])) {
			if($userIds[$i] == 0)
				continue;
			else
				$userGroups[$userIds[$i]] = array(0, 1);
		}
		if(!isset($userEffectivePermissions[$userIds[$i]]))
			$userEffectivePermissions[$userIds[$i]] = array();

		for($j = 0; $j < $permCount; $j++) {
			$userGroupCount = count($userGroups[$userIds[$i]]);

			for($k = 0; $k < $userGroupCount; $k++) {
				if (
						isset($groupEffectivePermissions[$userGroups[$userIds[$i]][$k]]) &&
						isset($groupEffectivePermissions[$userGroups[$userIds[$i]][$k]][$permIds[$j]])
					) {

					if(!isset($userEffectivePermissions[$userIds[$i]][$permIds[$j]]))
						$userEffectivePermissions[$userIds[$i]][$permIds[$j]] = false;

					$userEffectivePermissions[$userIds[$i]][$permIds[$j]] =
													$userEffectivePermissions[$userIds[$i]][$permIds[$j]] ||
													$groupEffectivePermissions[$userGroups[$userIds[$i]][$k]][$permIds[$j]];

				}
			}
		}
	}


	/// Retrieve all that work as HTML
	$htmlOutput = '<table border="1"><th nowrap="nowrap">User/Group Name</th>';
	/// The column headers
	for($i = 0; $i < $permCount; $i++) {
		$htmlOutput .= '<th nowrap="nowrap">' . $permList[$permIds[$i]][0] . ' - ' . $permList[$permIds[$i]][1] . '</th>';
	}

	$htmlOutput .= '<tr><th colspan="'. ($permCount + 1) . '" align="left">Groups</th></tr>';

	/// First the groups
	for($i = 0; $i < count($modifiableGroups); $i++) {
		$htmlOutput .= '<tr><td>' . $groupNames[$modifiableGroups[$i]] . '</td>';

		for($j = 0; $j < $permCount; $j++) {
			$htmlOutput .= '<td>';
			if(isset($groupEffectivePermissions[$modifiableGroups[$i]][$permIds[$j]])) {
				$htmlOutput .= $groupEffectivePermissions[$modifiableGroups[$i]][$permIds[$j]] === true ? 'Yes' : 'No';
			}
			else {
				$htmlOutput .= 'Unset';
			}
			$htmlOutput .= "</td>\n";
		}
		$htmlOutput .= "</tr>\n";
	}

	$htmlOutput .= '<tr><th colspan="'. ($permCount + 1) . '" align="left">Users</th></tr>';

	/// Then the users
	for($i = 0; $i < $userCount; $i++) {
		$htmlOutput .= '<tr><td>' . $userNames[$userIds[$i]] . '</td>';

		for($j = 0; $j < $permCount; $j++) {
			$htmlOutput .= '<td>';
			if(isset($userEffectivePermissions[$userIds[$i]][$permIds[$j]])) {
				$htmlOutput .= $userEffectivePermissions[$userIds[$i]][$permIds[$j]] === true ? 'Yes' : 'No';
			}
			else {
				$htmlOutput .= 'Unset';
			}
			$htmlOutput .= "</td>\n";
		}
		$htmlOutput .= "</tr>\n";
	}
	$htmlOutput .= "</table>\n";

	return $htmlOutput;
}



/**
 * Generates a table showing the permissions of a user at a particular level
 * @param $userid User id of the person whose permission table is to be retrieved
 * @param $groupids Array containing the groups to which the person belongs, which have a priority lesser
 * 									than the current user's priority
 * @param $pagepath Array containing the path to the current page
 * @param $permAssocList Associative array containing the actions for which the permission table must be generated
 * @return string The HTML generated for the permissions table
 */
function getPermissionTable($userid, $groupids, $pagepath, $permAssocList) {
	/// For the set of actions given by $permList, generate an SQL query to pull out the corresponding permission
	/// ids for each of the actions
	$groupidsAssoc = array_flip($groupids);
	$pagepathAssoc = array_flip($pagepath);

	$actionString = array();

	/// Generate an associative array of the form:
	/// 	$permList[permId] -> [id in table, module name, action name]
	$permList = array();
	$i = 0;
	foreach($permAssocList as $moduleName => $moduleActions) {
		if(is_array($moduleActions) && count($moduleActions) > 0) {
			for($j = 0; $j < count($moduleActions); $j++) {
				$permList[$moduleActions[$j][0]] = array($i, $moduleName, $moduleActions[$j][1]);
				$i++;
			}
		}
	}

	/// Table names for convenience
	$pagesTable = '`'.MYSQL_DATABASE_PREFIX.'pages`';
	$uppermTable = '`'.MYSQL_DATABASE_PREFIX.'userpageperm`';
	$groupsTable = '`'.MYSQL_DATABASE_PREFIX.'groups`';

	/// Pull all the set permissions, for all the groups, for all the page ids in the path to the current page
	$permQuery = "SELECT $pagesTable.`page_id`, $uppermTable.`usergroup_id`, $uppermTable.`perm_permission`, " .
							 "$uppermTable.`perm_type`, $uppermTable.`perm_id` " .
							 "FROM $pagesTable, $uppermTable WHERE (";
	if(count($groupids) > 0) {
		$permQuery .= "(`usergroup_id` IN (".join($groupids, ', ').") AND `perm_type` = 'group') OR ";
	}
	$permQuery .= "(`usergroup_id` = $userid AND `perm_type` = 'user')) AND " .
							 "$pagesTable.`page_id` IN (".join($pagepath, ', ').") AND $pagesTable.`page_id` = $uppermTable.`page_id` AND " .
							 "`perm_id` IN (". join(array_keys($permList), ', ') . ")";


	$permissionTable = array(); ///< Holds the permission table, as a 3d array: 1st index pageid, 2nd groupid and 3rd perm id
	$pageTitles = array();			///< Holds the page titles in the path to the current page
	$pageNames = array();				///< Holds the page names in the path to the current page
	$groupNames = array();			///< Holds the names of groups the user belongs to

	/// Find all the page names and titles in the path to the current page
	$pagesQuery = "SELECT `page_id`, `page_name`, `page_title` FROM $pagesTable WHERE `page_id` IN (".join($pagepath, ', ').")";
	$pagesResult = mysql_query($pagesQuery);
	while($pagesResultRow = mysql_fetch_assoc($pagesResult)) {
		$pageTitles[$pagepathAssoc[$pagesResultRow['page_id']]] = $pagesResultRow['page_title'];
		$pageNames[$pagepathAssoc[$pagesResultRow['page_id']]] = $pagesResultRow['page_name'];
	}
	$pageNames[0] = 'home';

	/// Find the names of all the groups the user belongs to
	if(count($groupids) > 0) {
		$groupsQuery = "SELECT `group_id`, `group_name` FROM $groupsTable WHERE `group_id` IN (" . join($groupids, ', ') . ")";
		$groupsResult = mysql_query($groupsQuery);
		while($groupsResultRow = mysql_fetch_assoc($groupsResult)) {
			$groupNames[$groupidsAssoc[$groupsResultRow['group_id']]] = $groupsResultRow['group_name'];
		}

		/// Push these in, because sahil refuses to put them in the db
		if(isset($groupidsAssoc[1])) { $groupNames[$groupidsAssoc[1]] = 'Logged In'; }
		if(isset($groupidsAssoc[0])) { $groupNames[$groupidsAssoc[0]] = 'Guest'; }
	}

	/// Retrieve results for the query generated earlier
	$permResult = mysql_query($permQuery);

	if(!$permResult) {
		displayerror("An error occurred while trying to process your Request!<br />" . $permQuery);
		return '';
	}

	$userOffset = count($groupids);
	while($permResultRow = mysql_fetch_assoc($permResult)) {
		if($permResultRow['perm_type'] == 'user') {
			$permissionTable[$pagepathAssoc[$permResultRow['page_id']]][$userOffset][$permList[$permResultRow['perm_id']][0]] =
						$permResultRow['perm_permission'] == 'Y' ? true : false;
		}
		else {
			// Decipher This!!!
			$permissionTable[$pagepathAssoc[$permResultRow['page_id']]][$groupidsAssoc[$permResultRow['usergroup_id']]][$permList[$permResultRow['perm_id']][0]] =
						$permResultRow['perm_permission'] == 'Y' ? true : false;
		}
	}

	$secondRow = '';
	foreach($permList as $permId => $actionArray) {
		$secondRow .= '<th>'.$actionArray[1]." - ".$actionArray[2] . '</th>';
	}

	$permTableString = '<br /><h2>Showing permission settings for ';
	if($userid >= 0) {
		$permTableString .= getUserName($userid);
	}
	else {
		$permTableString .= 'group(s) ' . join($groupNames, ', ');
	}
	$permTableString .= ' </h2><a href="./+grant">&laquo; Back</a><br /><br />';

	/// The breadcrumbs sort of thing
	for($i = 0; $i < count($pagepath); $i++) {
		$permTableString .= "\n/<a href=\"#{$pageNames[$i]}$i\">$pageNames[$i]</a> ";
	}

	for($i = 0; $i < count($pagepath); $i++) {
		$permTableString .= "\n<a name=\"{$pageNames[$i]}$i\"></a>\n<br /><br />\n<table border=\"1px\" cellpadding=\"4px\" cellspacing=\"4px\">\n<tr>\n<th>Page Title:</th><th colspan=\"".count($permList)."\">{$pageTitles[$i]}</th></tr>\n";
		$permTableString .= "<tr><th>Permission:</th>$secondRow</tr>\n";

		for($j = 0; $j < count($groupids); $j++) {
			$permTableString .= "<tr><td>{$groupNames[$j]}</td>";

			for($k = 0; $k < count($permList); $k++) {
				$permission = 'Unset';
				if(isset($permissionTable[$i]) && isset($permissionTable[$i][$j]) && isset($permissionTable[$i][$j][$k])) {
					$permission = ($permissionTable[$i][$j][$k] ? 'Yes' : 'No');
				}
				$permTableString .= "<td>$permission</td>";
			}
			$permTableString .= "</tr>\n";
		}

		if($userid >= 0) {
			$permTableString .= "<tr><td>User:</td>";
			for($k = 0; $k < count($permList); $k++) {
				$permission = 'Unset';
				if(isset($permissionTable[$i]) && isset($permissionTable[$i][$j]) && isset($permissionTable[$i][$j][$k])) {
					$permission = ($permissionTable[$i][$j][$k] ? 'Yes' : 'No');
				}
				$permTableString .= "<td>$permission</td>";
			}
			$permTableString .= "</tr>\n";
		}

		$permTableString .= "</table>";
	}

	return $permTableString . '<br />';
}

function getPermissionId($module, $action) {
	$permQuery = "SELECT `perm_id` FROM `".MYSQL_DATABASE_PREFIX."permissionlist` WHERE " .
								"`page_module` = '$module' AND `perm_action` = '$action'";
	$permResult = mysql_query($permQuery);

	if($permResult && ($permResultRow = mysql_fetch_array($permResult))) {
		return $permResultRow[0];
	}
	else {
		return -1;
	}
}



/**
 * Checks whether a given group has a given permission over a given page
 * @param $pagePath Array of integers, containing the pageids of each node in the path to the current page
 * @param $usergroupid Id of the group or user
 * @param $action String containing the name of the action requested
 * @param $module Name of the module
 * @param $permtype String indicating whether $usergroupid contains a group id or a user id
 * @return Boolean indicating whether the user or group has the requested permission
 */
function getPagePermission(array $pagePath, $usergroupid, $action, $module, $permtype = 'group') {
	$userpermTable = MYSQL_DATABASE_PREFIX . "userpageperm";
	$permissionlistTable = MYSQL_DATABASE_PREFIX . "permissionlist";

	$pageids = join($pagePath, ', ');

	$permQuery = "SELECT $userpermTable.perm_permission, $userpermTable.page_id FROM $userpermTable, $permissionlistTable ";
	$permQuery .= "WHERE $userpermTable.perm_type = '$permtype' AND $userpermTable.page_id IN ($pageids) AND ";
	$permQuery .= "$userpermTable.usergroup_id = $usergroupid AND $permissionlistTable.page_module = '$module' AND ";
	$permQuery .= "$permissionlistTable.perm_action = '$action' AND $permissionlistTable.perm_id = $userpermTable.perm_id";
	$permissionsArray = array ();
	if ($permQueryResult = mysql_query($permQuery)) {
		while ($permQueryResultRow = mysql_fetch_assoc($permQueryResult)) {
			$permissionsArray[$permQueryResultRow['page_id']] = $permQueryResultRow['perm_permission'] == 'Y' ? true : false;
		}
	}

	/**
	 * For the group under consideration, find permission for the page:
	 * 		From leaf node upto root, return false on finding first 'no'
	 * 		If permission is unset even after going till the root, return false
	 */
	$permission = -1; ///< -1 for unset
	for ($i = count($pagePath) - 1; $i >= 0; $i--) {
		if (isset ($permissionsArray[$pagePath[$i]])) {
			$permission = $permissionsArray[$pagePath[$i]];
			if($permission === false) break;
		}
	}

	if($permission === -1) {
		$permission = false;
	}
	return $permission;
}



/**
 * Finds out if a given user has the permission to perform a given action on a given page
 * @param $userid User Id of the user
 * @param $pagePath Array of integers, containing the pageids of each node in the path to the current page
 * @param $action String representing the requested action
 * @param $module String containing the name of the requested module
 * @return Boolean indicating whether the user has the requested permission or not
 */
function getPermissions($userid, $pageid, $action, $module="") {
	if($module=="") {
		$query = "SELECT 1 FROM `".MYSQL_DATABASE_PREFIX."permissionlist` WHERE page_module=\"page\" AND perm_action=\"$action\"";
		$result = mysql_query($query);
		if(mysql_num_rows($result)>=1)
			$module = 'page';
		else
			$module = getEffectivePageModule($pageid);
	}
	$permission = false;

	if($module=="menu" || $module=="external")	return getPermissions($userid,getParentPage($pageid),$action);
	/// Find all groups the user belongs to, ordered by priority
	/// For each group, starting with lowest priority, get permission for the page

	$pagePath=array();
	parseUrlDereferenced($pageid, $pagePath);
	foreach(getGroupIds($userid) as $groupid) {
		if($permission === true)	break;
		$permission = getPagePermission($pagePath, $groupid, $action, $module);
	}

	if($permission === false) {
		$permission = getPagePermission($pagePath, $userid, $action, $module, 'user');
	}
	return $permission;
}


/**
 * Determines the id of the user or group for which permissions are being set from a form generated by getGrantForm()
 * @return Integer, containing the id of the user or group, a value less than 0 indicating failure
 */
function determineGrantTargetId(&$targettype) {
	$targetId = -1;
	$targettype = 'group';
	$idQuery = '';

	if($_POST['optusergroup'] == 'group') {
		if($_POST['optgroup012'] == 'group0') {
			$targetId = 0;
		}
		else if($_POST['optgroup012'] == 'group1') {
			$targetId = 1;
		}
		else if($_POST['optgroup012'] == 'group3') {
			$targettype = 'user';
			$targetId = 0;
		}
		else {
			$idQuery = "SELECT `group_id` FROM `".MYSQL_DATABASE_PREFIX."groups` WHERE `group_name` = '".$_POST['modifiablegroups']."'";
		}
	}
	else if($_POST['optusergroup'] == 'user') {
		$hyphenPos = strpos($_POST['useremail'], '-');
		if($hyphenPos >= 0) {
			$userEmail = trim(substr($_POST['useremail'], 0, $hyphenPos - 1));
		}
		else {
			$userEmail = $_POST['useremail'];
		}

		$idQuery = "SELECT `user_id` FROM `" . MYSQL_DATABASE_PREFIX . "users` WHERE `user_email` = '$userEmail'";
		$targettype = 'user';
	}

	if($targetId == -1 && $idQuery != '') {
		$idResult = mysql_query($idQuery);

		if($idResult) {
			if($idResultRow = mysql_fetch_row($idResult)) {
				$targetId = $idResultRow[0];
			}
		}
	}

	return $targetId;
}



/**
 * Determines which permissions a user can grant, and to which groups and users on a given page
 * @param $userid User id of the user attempting to grant permissions
 * @param $pagepath Array containing the page ids of the nodes on the path to the given page
 * @param $modifiableGroups Buffer to store the groups the user can grant permissions to
 * @param $grantableActions Buffer to store the list of actions the user can grant permissions for
 * @return Boolean, indicating whether the function was successful
 */
function grantPermissions($userid, $pageid) {
	/// An ajaxed request for suggestions for a user's name or email is being entertained
	if(isset($_GET['doaction']) && $_GET['doaction'] == 'getsuggestions' && isset($_GET['forwhat'])) {
		if(strlen($_GET['forwhat']) >= 3) {
			echo getSuggestions($_GET['forwhat']);
			disconnect();
			exit();
		}
	}

	/// The user just clicked Grant Permissions on a Perm Grant form
	elseif(
			isset($_POST['optusergroup']) && isset($_POST['btnSubmit']) && isset($_POST['permission']) &&
			is_array($_POST['permission']) && isset($_POST['optpermtype'])
		)
	{
		$errorString = 'Some errors were encountered while attempting to process your request. ';  /// Define a very general error string for easy returns!

		/// Check whether the permissions are being set for a user or a group
		/// Obtain the Id of the user or the group for which the permission is being set
		$targettype = '';
		$targetid = determineGrantTargetId($targettype);
		if($targetid < 0) {
			displayerror('Some errors were encountered while attempting to process your request.');
			return '';
		}

		/// Check whether the user has the rights to grant permissions at this level
		$pagepath = array();
		parseUrlDereferenced($pageid, $pagepath);
		$pageid = $pagepath[count($pagepath) - 1];

		$groups = array_reverse(getGroupIds($userid));
		$virtue = '';
		$maxPriorityGroup = getMaxPriorityGroup($pagepath, $userid, $groups, $virtue);

		if($maxPriorityGroup == -1) {
			displayerror('You do not have the required permissions to view this page.');
			return '';
		}

		/// Check whether the user is granting permissions to a group having lower priority than
		/// the group with the highest priority that he belongs to
		$canGrant = true;			// Can grant permissions to any user, but not to any group
		if($targettype == 'group' && $targetid >= 2) {
			$maxPriority = 1;
			if($maxPriorityGroup > 1) {
				$maxPriority = "(SELECT `group_priority` FROM `".MYSQL_DATABASE_PREFIX."groups` WHERE " .
											"`group_id` = $maxPriorityGroup)";
			}
			$groups_query = "SELECT `group_id` FROM `".MYSQL_DATABASE_PREFIX."groups` WHERE " .
											"`group_id` = $targetid AND `group_priority` <= $maxPriority";

			$groups_query_result = mysql_query($groups_query);
			if(!$groups_query_result || mysql_num_rows($groups_query_result) != 1) {
				displayerror($groups_query . ' ' . mysql_num_rows($groups_query_result) . ' ' . mysql_error());
				$canGrant = false;
			}
		}

		if((!$canGrant && $targetid != 0 && $targetid != 1) || ($targetid == 1 && $maxPriorityGroup == 0)) {
			displayerror('You cannot grant permissions to the selected user or group.');
			return '';
		}

		if($virtue == 'user') {
			$permList = getGroupPermissions($groups, $pagepath, $userid);
		}
		else {
			$permList = getGroupPermissions($groups, $pagepath);
		}

		/// All checks passed, start granting permissions, one by one, for each of the given module+action combinations
		$actionCount = count($_POST['permission']);
		$permissionsGoneWrong = array();
		$permission = false;
		if($_POST['optpermtype'] == 'allowed') {
			$permission = true;
		}
		if($_POST['optpermtype'] == 'unset') {
			$permission = -1;
		}
		for($i = 0; $i < $actionCount; $i++) {
			list($module, $action) = explode('_', $_POST['permission'][$i], 2);

			if(!isset($_POST[$module.$action])) {
				continue;
			}

			$canGrant = false;
			if(isset($permList[$module])) {
				for($j = 0; $j < count($permList[$module]); $j++) {
					if($permList[$module][$j][1] == $action) {
						$canGrant = true;
						break;
					}
				}
			}

			if(!$canGrant) {
				$permissionsGoneWrong[] = "$module - $action";
				continue;
			}

			if($permission === -1) {
				if(!unsetPagePermission($targetid, $pageid, $action, $module, $targettype)) {
					$permissionsGoneWrong[] = "$module - $action";
				}
			}
			else {
				if(!setPagePermission($targetid, $pageid, $action, $module, $permission, $targettype)) {
					$permissionsGoneWrong[] = "$module - $action";
				}
			}
		}

		/// Display errors if any
		if(count($permissionsGoneWrong) > 0) {
			displayerror('Permissions could not be updated for the following: '.join($permissionsGoneWrong, "<br />"));
		}
		else {
			displayinfo('All permissions updated successfully.');
		}
	}

	elseif(isset($_POST['btnEveryonePermTable']) || isset($_POST['btnUserPermTable']) || isset($_POST['btnGroupPermTable'])) {
		$pagepath = array();
		parseUrlDereferenced($pageid, $pagepath);
		$pageid = $pagepath[count($pagepath) - 1];

		$groups = array_reverse(getGroupIds($userid));
		$virtue = '';
		$maxPriorityGroup = getMaxPriorityGroup($pagepath, $userid, $groups, $virtue);
		if($maxPriorityGroup == -1) {
			return 'You do not have the required permissions to view this page.';
		}

		if($virtue == 'user') {
			$grantableActions = getGroupPermissions($groups, $pagepath, $userid);
		}
		else {
			$grantableActions = getGroupPermissions($groups, $pagepath);
		}

		$actionCount = count($_POST['permission']);
		$checkedActions = array();
		for($i = 0; $i < $actionCount; $i++) {
			list($modTemp, $actTemp) = explode('_', $_POST['permission'][$i], 2);

			if(isset($_POST[$modTemp.$actTemp])) {
				if(isset($grantableActions[$modTemp])) {
					for($j = 0; $j < count($grantableActions[$modTemp]); $j++) {
						if($grantableActions[$modTemp][$j][1] == $actTemp) {
							$checkedActions[$modTemp][] = $grantableActions[$modTemp][$j];
							break;
						}
					}
				}
			}
		}
		if(count($checkedActions) > 0) {
			$grantableActions = $checkedActions;
		}

		$modifiableGroups = getModifiableGroups($userid, $maxPriorityGroup);
		$modifiableGroupIds = array(0, 1);
		for($i = 0; $i < count($modifiableGroups); $i++) {
			$modifiableGroupIds[] = $modifiableGroups[$i]['group_id'];
		}

		/// The user is trying to see everyone's permissions on the current page
		if(isset($_POST['btnEveryonePermTable'])) {
			return getAllPermissionsOnPage($pagepath, $modifiableGroupIds, $grantableActions);
		}

		/// The user is trying to see the permission table for a group
		elseif(isset($_POST['btnGroupPermTable'])) {
			/// Check whether the permissions are being set for a user or a group
			/// Obtain the Id of the user or the group for which the permission is being set
			$targettype = '';
			$targetid = determineGrantTargetId($targettype);
			if($targetid < 0 || ($targettype != 'group' && $targetid != 0)) {
				displayerror('An unknown error was encountered while processing the request.');
				return '';
			}

			if($targetid > 1) {
				$maxPriority = 1;
				if($maxPriorityGroup > 1) {
					$maxPriority = '(SELECT `group_priority` ' .
												 'FROM `'.MYSQL_DATABASE_PREFIX.'groups` WHERE `group_id` = ' . $maxPriorityGroup . ')';
				}
				$priorityQuery = 'SELECT `group_id` FROM `'.MYSQL_DATABASE_PREFIX.'groups` WHERE ' .
												 '`group_id` = '.$targetid.' AND `group_priority` <= ' . $maxPriority;
				$priorityResult = mysql_query($priorityQuery);
				if(!$priorityResult || mysql_num_rows($priorityResult) != 1) {
					displayerror('You do not have the rights to view the permissions table of the selected group.');
					return '';
				}
			}

			if($targettype == 'group') {
				return getPermissionTable(-1, array($targetid), $pagepath, $grantableActions);
			}
			else {
				return getPermissionTable(0, array(), $pagepath, $grantableActions);
			}
		}

		elseif(isset($_POST['btnUserPermTable']) && isset($_POST['useremail'])) {
			$hyphenPos = strpos($_POST['useremail'], '-');
			if($hyphenPos >= 0) {
				$userEmail = trim(substr($_POST['useremail'], 0, $hyphenPos - 1));
			}
			else {
				$userEmail = $_POST['useremail'];
			}

			$targetUserId = getUserIdFromEmail($userEmail);
			if($targetUserId > 0) {
				return getPermissionTable($targetUserId, $modifiableGroupIds, $pagepath, $grantableActions);
			}
			else {
				displayerror('A user registered with the e-mail ID you entered was not found.');
				return '';
			}
		}
	}

	/// The User is trying to edit groups
	elseif(isset($_GET['subaction']) && $_GET['subaction'] == 'editgroups') {
		$pagepath = array();
		parseUrlDereferenced($pageid, $pagepath);
		$virtue = '';
		$maxPriorityGroup = getMaxPriorityGroup($pagepath, $userid, array_reverse(getGroupIds($userid)), $virtue);
		$modifiableGroups = getModifiableGroups($userid, $maxPriorityGroup);
		return getGroupsForm($userid, $modifiableGroups, $pagepath);
	}
	/// Nothing was sent; show the grant form
	return getGrantForm($userid, $pageid);
}



/**
 * Unsets permissions for a given user or group for a given page
 * @param $usergroupid Id of the user or group, whose permission is to be unset
 * @param $pageid Page id of the current page
 * @param $action Action for which the permission is to be unset
 * @param $module Name of the module to which the action belongs
 * @param $permtype A string indicating whether $usergroupid refers to a user or a group
 * @return Boolean, true indicating success, and false indicating failure
 */
function unsetPagePermission($usergroupid, $pageid, $action, $module, $permtype = 'group') {
	$permQuery = "SELECT `perm_id` FROM `".MYSQL_DATABASE_PREFIX."permissionlist` WHERE " .
							 "`perm_action` = '$action' AND `page_module` = '$module'";
	$permQueryResult = mysql_query($permQuery);

	if(!$permQueryResult || !($permQueryResultRow = mysql_fetch_assoc($permQueryResult))) {
		return false;
	}

	$permid = $permQueryResultRow['perm_id'];

	$removeQuery = "DELETE FROM `".MYSQL_DATABASE_PREFIX."userpageperm` " .
								 "WHERE `usergroup_id` = $usergroupid AND `page_id` = $pageid AND `perm_id` = $permid AND " .
								 "`perm_type` = '$permtype' LIMIT 1";
	if(mysql_query($removeQuery)) {
		return true;
	}
	else {
		return false;
	}
}



/**
 * Sets permission for a user or group at a particular level, for a given action and module
 * @param $usergroupid Id of the user or group for which the permission is to be set
 * @param $pageid Id of the page at which level the permission is to be set
 * @param $action Action for which the permission is being set
 * @param $module Name of the module for which the action is defined
 * @param $permission Boolean indicating whether the permission is to be given, or taken away
 * @param $permtype String indicating whether $usergroupid represents a user or a group
 * @return Boolean True indicating success, false indicating failure
 */
function setPagePermission($usergroupid, $pageid, $action, $module, $permission, $permtype = 'group') {
	$permQuery = "SELECT `perm_id` FROM `".MYSQL_DATABASE_PREFIX."permissionlist` WHERE " .
								 "`perm_action` = '$action' AND `page_module` = '$module'";
	$permQueryResult = mysql_query($permQuery);

	if(!$permQueryResult || !($permQueryResultRow = mysql_fetch_assoc($permQueryResult))) {
		return false;
	}

	$permid = $permQueryResultRow['perm_id'];

	$updateQuery = '';
	$permission = ($permission === true ? 'Y' : 'N');
	$permQuery = "SELECT `perm_permission` FROM `".MYSQL_DATABASE_PREFIX."userpageperm` WHERE " .
							 "`usergroup_id` = $usergroupid AND `page_id` = $pageid AND `perm_id` = $permid AND " .
							 "`perm_type` = '$permtype'";
	$permQueryResult = mysql_query($permQuery);

	if($permQueryResultRow = mysql_fetch_assoc($permQueryResult)) {
		if($permission != $permQueryResultRow['perm_permission']) {
			$updateQuery = "UPDATE `".MYSQL_DATABASE_PREFIX."userpageperm` SET `perm_permission` = '$permission' " .
										 "WHERE `usergroup_id` = $usergroupid AND `page_id` = $pageid AND `perm_id` = $permid AND " .
							 			 "`perm_type` = '$permtype' LIMIT 1";
		}
	}
	else {
		$updateQuery = "INSERT INTO `".MYSQL_DATABASE_PREFIX."userpageperm` (`perm_type`, `page_id`, `usergroup_id`, `perm_id`, `perm_permission`) " .
									 "VALUES('$permtype', $pageid, $usergroupid, $permid, '$permission')";
	}

	if($updateQuery != '') {
		$updateResult = mysql_query($updateQuery);
		if(!$updateResult) {
			return false;
		}
	}

	return true;
}



/**
 * Generate an HTML form to allow the user to set permissions for other users or groups
 * @param $userid The id of the user trying to modify permissions
 * @param $pageid Page id of the concerned page
 * @return String containing the generated HTML form
 */
function getGrantForm($userid, $pageid) {
	global $urlRequestRoot, $sourceFolder, $templateFolder;
	$scriptsFolder = "$urlRequestRoot/$sourceFolder/$templateFolder/common/scripts";
	$imagesFolder = "$urlRequestRoot/$sourceFolder/$templateFolder/common/images";

	$pagepath = array();
	parseUrlDereferenced($pageid, $pagepath);
	$pageid = $pagepath[count($pagepath) - 1];
	$groups = array_reverse(getGroupIds($userid));

	$virtue = '';
	$maxPriorityGroup = getMaxPriorityGroup($pagepath, $userid, $groups, $virtue);
	if($maxPriorityGroup == -1) {
		return 'You do not have the required permissions to view this page.';
	}

	/// Find all the groups the user can modify
	$modifiableGroups = getModifiableGroups($userid, $maxPriorityGroup, 'asc');
	$groupsBox = '';
	for($i = 0; $i < count($modifiableGroups); $i++) {
		$groupsBox .= '<option value="' . $modifiableGroups[$i]['group_name'] . '">' . $modifiableGroups[$i]['group_name'] . ' - ' . $modifiableGroups[$i]['group_description'] . '</option>';
	}
	$groupsForm = getGroupsForm($userid, $modifiableGroups, $pagepath);
	if($groupsForm == '') {
		$groupsForm =
				"<select name=\"selEditGroups\" id=\"editableGroupsList\">\n<option>$groupsBox</option>\n</select>" .
				'<input type="submit" name="btnEditGroup" value="Edit Selected Group" /><br /><br />' .
				'<input type="submit" name="btnEditGroupPriorities" value="Add/Shuffle/Remove Groups" />';
	}
	$groupsBox = "<select name=\"modifiablegroups\" id=\"modifiableGroupsList\" disabled=\"disabled\">\n<option>$groupsBox</option>\n</select>";

	/// Get all the actions for which the user has the right to grant permissions
	if($virtue == 'user') {
		$grantableActions = getGroupPermissions($groups, $pagepath, $userid);
	}
	else {
		$grantableActions = getGroupPermissions($groups, $pagepath);
	}

	$actionsBox = '<table>';
	foreach($grantableActions as $module => $actions) {
		$actionsBox .= '<tr><td>' . ucfirst($module).":</td>\n<td>";

		for($i = 0; $i < count($actions); $i++) {
			$actionsBox .= "<label title=\"{$actions[$i][2]}\"><input type=\"checkbox\" name=\"".$module.$actions[$i][1]."\" /> ".ucfirst($actions[$i][1])."\n" .
										 "<input type=\"hidden\" name=\"permission[]\" value=\"".$module."_".$actions[$i][1]."\" />\n";
		}

		$actionsBox .= "</td></tr>\n";
	}
	$actionsBox .= '</table>';

	$usersBox = '<input type="text" name="useremail" id="userEmail" disabled="disabled" autocomplete="off" style="width: 256px" />' .
							'<div id="suggestionsBox" class="suggestionbox"></div>';

	global $pageFullPath;
	$actualPagePath = array();
	parseUrlReal($pageFullPath, $actualPagePath);
	$actualPageId = $actualPagePath[count($actualPagePath) - 1];
	$actualPageTitle = getPageTitle($actualPageId);

	$displayForm = <<<FORMHTML
		<script type="text/javascript" language="javascript">
		<!--
			imgAjaxLoading = new Image();
			imgAjaxLoading.src = '$imagesFolder/ajaxloading.gif';
		-->
		</script>
		<script type="text/javascript" language="javascript" src="$scriptsFolder/ajaxsuggestionbox.js">
		</script>
		<script type="text/javascript" language="javascript" src="$scriptsFolder/permgrant.js">
		</script>
		<br />
		<fieldset style="padding: 8px">
			<legend>Permissions</legend>

			<h2>'$actualPageTitle' at path '$pageFullPath'</h2>

			<form name="grantpermissions" method="POST" action="./+grant" style="padding: 8px">
	  		<fieldset style="padding: 8px"><legend>Select Action</legend>
					$actionsBox
				</fieldset>

				<br />
	  		<fieldset style="padding: 8px"><legend>Select User/Group</legend>
				<label><input type="radio" name="optusergroup" value="group" onclick="enableGroups()" checked />Group:</label>
				<label><input type="radio" name="optgroup012" value="group0" onclick="document.getElementById('modifiableGroupsList').disabled = true" />Everyone</label>
				<label><input type="radio" name="optgroup012" value="group3" onclick="document.getElementById('modifiableGroupsList').disabled = true" />Non-logged in users *only*</label>
				<label><input type="radio" name="optgroup012" value="group1" onclick="document.getElementById('modifiableGroupsList').disabled = true" />Logged in users</label>
				<label><input type="radio" name="optgroup012" value="group2" onclick="document.getElementById('modifiableGroupsList').disabled = false" />Others:</label> $groupsBox
				<input type="submit" name="btnGroupPermTable" id="btnGroupPermTable" value="View Group's Permission Table" />

				<br />
				<label><input type="radio" name="optusergroup" value="user" onclick="enableUsers()" />User:</label>
					$usersBox
				<input type="submit" name="btnUserPermTable" id="btnUserPermTable" value="View User's Permission Table" disabled="disabled" />
				<br />
		  		<input type="submit" name="btnEveryonePermTable" id="btnEveryonePermTable" value="View Everyone's Permission Table" />
	  		</fieldset>

				<br />
				<script language="javascript" type="text/javascript">
				<!--
					var userBox = new SuggestionBox(document.getElementById('userEmail'), document.getElementById('suggestionsBox'), "./+grant&doaction=getsuggestions&forwhat=%pattern%");
					userBox.loadingImageUrl = '$imagesFolder/ajaxloading.gif';
				-->
				</script>
	  		<fieldset style="padding: 8px"><legend>Permission</legend>
		  		<label><input type="radio" name="optpermtype" value="allowed" />Allowed</label>
		  		<label><input type="radio" name="optpermtype" value="denied" />Denied</label>
		  		<label><input type="radio" name="optpermtype" value="unset" />Unset</label>
	  		</fieldset>

				<br />
				<input type="submit" name="btnSubmit" value="Grant Permissions" />
			</form>
		</fieldset>
		<br />
		<fieldset style="padding: 8px">
			<legend>Groups</legend>
			$groupsForm
		</fieldset>
FORMHTML;

	return $displayForm;
}



/**
 * Retrieves the group of maximum priority that gives the concerned user the grant permission at a given page
 * @param $pagepath An array containing the page ids of the nodes leading to the current page
 * @param $userid User id of the current user
 * @param $groupids The list of ids of the groups to which the user belongs, ordered by priority
 * @param $virtue String indicating whether the user obtained grant individually or through a group
 * @return Integer representing the id of the group with the maxmimum priority, -1 in case no such group was found
 */
function getMaxPriorityGroup(&$pagepath, $userid, &$groupids, &$virtue) {
	if(getPagePermission($pagepath, $userid, 'grant', 'page', 'user')) {
		$virtue = 'user';
		return $groupids[0];
	}
	else {
		$l = count($groupids);
		for($i = 0; $i < $l; $i++) {
			if(getPagePermission($pagepath, $groupids[$i], 'grant', 'page')) {
				$virtue = 'group';
				return $groupids[$i];
			}
		}
	}

	return -1;
}

function getModifiableGroups($userId, $maxPriorityGroup, $ordering = 'asc') {
	if($ordering != 'asc') $ordering = 'desc';
	$modifiableGroups = array(
//		array('group_id' => 0, 'group_name' => 'Guest', 'group_description' => 'All users who visit the site', 'group_priority' => 0),
//		array('group_id' => 1, 'group_name' => 'Logged In', 'group_description' => 'All logged in users', 'group_priority' => 1)
	);

	$groupsTable = MYSQL_DATABASE_PREFIX.'groups';
	$usergroupTable = MYSQL_DATABASE_PREFIX.'usergroup';

	/// "SELECT `$groupsTable`.`group_id`, `$groupsTable`.`group_name`, `$groupsTable`.`group_description`, `$groupsTable`.`group_priority` " .
	///		"FROM `$groupsTable` WHERE `group_priority <= (SELECT `group_priority` FROM `$groupsTable` WHERE `group_id` = $maxPriorityGroup)

	$groupPriority = "(SELECT `group_priority` FROM `$groupsTable` WHERE `group_id` = $maxPriorityGroup)";
	if($maxPriorityGroup == 1) $groupPriority = 1;
	$groupsQuery = "SELECT `$groupsTable`.`group_id`, `$groupsTable`.`group_name`, `$groupsTable`.`group_description`, `$groupsTable`.`group_priority` " .
			"FROM `$groupsTable` WHERE `group_priority` <= $groupPriority ORDER BY `group_priority` $ordering";
	/** OLD QUERY: ***
		"SELECT `$groupsTable`.`group_id`, `$groupsTable`.`group_name`, `$groupsTable`.`group_description`, `$groupsTable`.`group_priority` " .
			"FROM `$groupsTable`, `$usergroupTable` " .
			"WHERE `$usergroupTable`.`user_id` = $userId AND `$usergroupTable`.`group_id` = `$groupsTable`.`group_id` " .
			"AND `$groupsTable`.`group_priority` <= " .
			"(SELECT `group_priority` FROM `$groupsTable` WHERE `group_id` = $maxPriorityGroup) " .
			"ORDER BY `$groupsTable`.`group_priority` $ordering";
	*/
	$groupsResult = mysql_query($groupsQuery) or die($groupsQuery . '<br />' . mysql_error());

	while($groupsRow = mysql_fetch_assoc($groupsResult)) {
		$modifiableGroups[] = $groupsRow;
	}

	return $modifiableGroups;
}

/**
 * Retrieves a list of actions a user has on a given page
 * @param groupids The groups to which the person belongs
 * @param pagepath Array containing page ids representing the nodes on the path to the page
 * @return Array containing the modules, pointing to list of actions the user can perform on that module
 */
function getGroupPermissions($groupids, $pagepath, $userid = -1) {
	// For a given user, return the set of modules and actions he has at that level
	$permQuery = "SELECT `perm_id`, `perm_action`, `page_module`, `perm_description` FROM `".MYSQL_DATABASE_PREFIX."permissionlist`";
	$permResult = mysql_query($permQuery);
	if(!$permResult) {
		return '';
	}

	$permList = array();
	$groupCount = count($groupids);

	while($permResultRow = mysql_fetch_assoc($permResult)) {
		$moduleName = $permResultRow['page_module'];
		$actionName = $permResultRow['perm_action'];
		$actionDescription = $permResultRow['perm_description'];
		$permissionId = $permResultRow['perm_id'];

		$permissionSet = false;

		for($i = 0; $i < $groupCount; $i++) {
			if(getPagePermission($pagepath, $groupids[$i], $actionName, $moduleName)) {
				$permList[$moduleName][] = array($permissionId, $actionName, $actionDescription);
				$permissionSet = true;
				break;
			}
		}

		if(!$permissionSet && $userid > -1) {
			if(getPagePermission($pagepath, $userid, $actionName, $moduleName, 'user')) {
				$permList[$moduleName][] = array($permissionId, $actionName, $actionDescription);
			}
		}
	}

	return $permList;
}


function getGroupsForm($currentUserId, $modifiableGroups, &$pagePath) {
	require_once("group.lib.php");

	global $urlRequestRoot, $sourceFolder, $templateFolder, $moduleFolder;
	$scriptsFolder = "$urlRequestRoot/$sourceFolder/$templateFolder/common/scripts";
	$imagesFolder = "$urlRequestRoot/$sourceFolder/$templateFolder/common/images";

	/// Parse any get variables, do necessary validation and stuff, so that we needn't check inside every if
	$groupRow = $groupId = $userId = null;
	$subAction = ''; //isset($_GET['subaction']) ? $_GET['subaction'] : '';
	if ((isset($_GET['subsubaction']) && $_GET['subsubaction'] == 'editgroup' && isset($_GET['groupname'])) || (isset($_POST['btnEditGroup']) && isset($_POST['selEditGroups'])))
		$subAction = 'showeditform';
	elseif(isset($_GET['subsubaction']) && $_GET['subsubaction'] == 'associateform')
		$subAction = 'associateform';
	elseif (isset($_GET['subsubaction']) && $_GET['subsubaction'] == 'deleteuser' && isset($_GET['groupname']) && isset($_GET['useremail']))
		$subAction = 'deleteuser';
	elseif (isset($_POST['btnAddUserToGroup']))
		$subAction = 'addusertogroup';
	elseif (isset($_POST['btnSaveGroupProperties']))
		$subAction = 'savegroupproperties';
	elseif (isset($_POST['btnEditGroupPriorities']) || (isset($_GET['subsubaction']) && $_GET['subsubaction'] == 'editgrouppriorities'))
		$subAction = 'editgrouppriorities';

	if(isset($_POST['selEditGroups']) || isset($_GET['groupname'])) {
		$groupRow = getGroupRow( isset($_POST['selEditGroups']) ? $_POST['selEditGroups'] : $_GET['groupname'] );
		$groupId = $groupRow['group_id'];
		if($subAction != 'editgrouppriorities' && (!$groupRow || !$groupId || $groupId < 2)) {
			displayerror('Error! Invalid group requested.');
			return ;
		}

		if(!is_null($groupId)) {
			if($modifiableGroups[count($modifiableGroups) - 1]['group_priority'] < $groupRow['group_priority']) {
				displayerror('You do not have the permission to modify the selected group.');
				return '';
			}
		}
	}
	if(isset($_GET['useremail'])) {
		$userId = getUserIdFromEmail($_GET['useremail']);
	}

	if($subAction != 'editgrouppriorities' && (isset($_GET['subaction']) && $_GET['subaction'] == 'editgroups' && !is_null($groupId))) {
		if ($subAction == 'deleteuser') {
			if($groupRow['form_id'] != 0) {
				displayerror('The group is associated with a form. To remove a user, use the edit registrants in the assoicated form.');
			}
			elseif (!$userId) {
				displayerror('Unknown E-mail. Could not find a registered user with the given E-mail Id');
			}
			else {
				$deleteQuery = 'DELETE FROM `' . MYSQL_DATABASE_PREFIX . 'usergroup` WHERE `user_id` = ' . $userId . ' AND `group_id` = ' . $groupId;
				$deleteResult = mysql_query($deleteQuery);
				if(!$deleteResult || mysql_affected_rows() != 1) {
					displayerror('Could not delete user with the given E-mail from the given group.');
				}
				else {
					displayinfo('Successfully removed user from the current group');

					if($userId == $currentUserId) {
						$virtue = '';
						$maxPriorityGroup = getMaxPriorityGroup($pagePath, $currentUserId, array_reverse(getGroupIds($currentUserId)), $virtue);
						$modifiableGroups = getModifiableGroups($currentUserId, $maxPriorityGroup, $ordering = 'asc');
					}
				}
			}
		}
		elseif ($subAction == 'savegroupproperties' && isset($_POST['txtGroupDescription'])) {
			$updateQuery = "UPDATE `" . MYSQL_DATABASE_PREFIX . "groups` SET `group_description` = '{$_POST['txtGroupDescription']}' WHERE `group_id` = $groupId";
			$updateResult = mysql_query($updateQuery);
			if (!$updateResult) {
				displayerror('Could not update database.');
			}
			else {
				displayinfo('Changes to the group have been successfully saved.');
			}
			$groupRow = getGroupRow($groupRow['group_name']);
		}
		elseif ($subAction == 'addusertogroup' && isset($_POST['txtUserEmail']) && trim($_POST['txtUserEmail']) != '') {
			if($groupRow['form_id'] != 0) {
				displayerror('The selected group is associated with a form. To add a user, register the user to the form.');
			}
			else {
				$passedEmails = explode(',', $_POST['txtUserEmail']);

				for($i = 0; $i < count($passedEmails); $i++) {
					$hyphenPos = strpos($passedEmails[$i], '-');
					if ($hyphenPos >= 0) {
						$userEmail = trim(substr($passedEmails[$i], 0, $hyphenPos - 1));
					}
					else {
						$userEmail = $_POST['txtUserEmail'];
					}

					$userId = getUserIdFromEmail($userEmail);
					if(!$userId || $userId < 1) {
						displayerror('Unknown E-mail. Could not find a registered user with the given E-mail Id');
					}

					if(!addUserToGroupName($groupRow['group_name'], $userId)) {
						displayerror('Could not add the given user to the current group.');
					}
					else {
						displayinfo('User has been successfully inserted into the given group.');
					}
				}
			}
		}
		elseif ($subAction == 'associateform') {
			if(isset($_POST['btnAssociateGroup'])) {
				$pageIdArray = array();
				$formPageId = parseUrlReal($_POST['selFormPath'], $pageIdArray);
				if($formPageId <= 0 || getPageModule($formPageId) != 'form') {
					displayerror('Invalid page selected! The page you selected is not a form.');
				}
				elseif (!getPermissions($currentUserId, $formPageId, 'editregistrants', 'form'))
					displayerror('You do not have the permissions to associate the selected form with a group.');
				else {
					$formModuleId = getModuleComponentIdFromPageId($formPageId, 'form');
					require_once("$sourceFolder/$moduleFolder/form.lib.php");

					if(isGroupEmpty($groupId) || form::getRegisteredUserCount($formModuleId) == 0) {
						associateGroupWithForm($groupId, $formModuleId);
						$groupRow = getGroupRow($groupRow['group_name']);
					}
					else
						displayerror('Both the group and the form already contain registered users, and the group cannot be associated with the selected form.');
				}
			}
			elseif(isset($_POST['btnUnassociateGroup'])) {
				if($groupRow['form_id'] <= 0) {
					displayerror('The selected group is currently not associated with any form.');
				}
				elseif(!getPermissions($currentUserId, getPageIdFromModuleComponentId('form', $groupRow['form_id']), 'editregistrants', 'form')) {
					displayerror('You do not have the permissions to unassociate the form from this group.');
				}
				else {
					unassociateFormFromGroup($groupId);
					$virtue = '';
					$maxPriorityGroup = getMaxPriorityGroup($pagePath, $currentUserId, array_reverse(getGroupIds($currentUserId)), $virtue);
					$modifiableGroups = getModifiableGroups($currentUserId, $maxPriorityGroup, $ordering = 'asc');
					$groupRow = getGroupRow($groupRow['group_name']);
				}
			}
		}

		if($modifiableGroups[count($modifiableGroups) - 1]['group_priority'] < $groupRow['group_priority']) {
			displayerror('You do not have the permission to modify the selected group.');
			return '';
		}

		$usersTable = '`' . MYSQL_DATABASE_PREFIX . 'users`';
		$usergroupTable = '`' . MYSQL_DATABASE_PREFIX . 'usergroup`';
		$userQuery = "SELECT `user_email`, `user_fullname` FROM $usergroupTable, $usersTable WHERE `group_id` =  $groupId AND $usersTable.`user_id` = $usergroupTable.`user_id` ORDER BY `user_email`";
		$userResult = mysql_query($userQuery);
		if(!$userResult) {
			displayerror('Error! Could not fetch group information.');
			return '';
		}

		$userEmails = array();
		$userFullnames = array();
		while($userRow = mysql_fetch_row($userResult)) {
			$userEmails[] = $userRow[0];
			$userFullnames[] = $userRow[1];
		}

		$groupEditForm = <<<GROUPEDITFORM
			<h2>Group '{$groupRow['group_name']}' - '{$groupRow['group_description']}'</h2><br />
			<fieldset style="padding: 8px">
				<legend>Group Properties</legend>
				<form name="groupeditform" method="POST" action="./+grant&subaction=editgroups&groupname={$groupRow['group_name']}">
					Group Description: <input type="text" name="txtGroupDescription" value="{$groupRow['group_description']}" />
					<input type="submit" name="btnSaveGroupProperties" value="Save Group Properties" />
				</form>
			</fieldset>

			<br />
			<fieldset style="padding: 8px">
				<legend>Existing Users in Group:</legend>
GROUPEDITFORM;

		$userCount = mysql_num_rows($userResult);
		global $urlRequestRoot, $sourceFolder, $templateFolder;
		$deleteImage = "<img src=\"$urlRequestRoot/$sourceFolder/$templateFolder/common/icons/16x16/actions/edit-delete.png\" alt=\"Remove user from the group\" />";

		for($i = 0; $i < $userCount; $i++) {
			$isntAssociatedWithForm = ($groupRow['form_id'] == 0);
			if($isntAssociatedWithForm)
				$groupEditForm .= '<a onclick="return confirm(\'Are you sure you wish to remove this user from this group?\')" href="./+grant&subaction=editgroups&subsubaction=deleteuser&groupname=' . $groupRow['group_name'] . '&useremail=' . $userEmails[$i] . '">' . $deleteImage . "</a>";
			$groupEditForm .= "{$userEmails[$i]} - {$userFullnames[$i]}<br />\n";
		}

		$associateForm = '';
		if($groupRow['form_id'] == 0) {
			$associableForms = getAssociableFormsList($currentUserId, !isGroupEmpty($groupId));
			$associableFormCount = count($associableForms);
			$associableFormsBox = '<select name="selFormPath">';
			for($i = 0; $i < $associableFormCount; ++$i) {
				$associableFormsBox .= '<option value="' . $associableForms[$i][2] . '">' . $associableForms[$i][1] . ' - ' . $associableForms[$i][2] . '</option>';
			}
			$associableFormsBox .= '</select>';
			$associateForm = <<<GROUPASSOCIATEFORM

			Select a form to associate the group with: $associableFormsBox
			<input type="submit" name="btnAssociateGroup" value="Associate Group with Form" />
GROUPASSOCIATEFORM;
		}
		else {
			$associatedFormPageId = getPageIdFromModuleComponentId('form', $groupRow['form_id']);
			$associateForm = 'This group is currently associated with the form: ' . getPageTitle($associatedFormPageId) . ' (' . getPagePath($associatedFormPageId) . ')<br />' .
					'<input type="submit" name="btnUnassociateGroup" value="Unassociate" />';
		}

		$groupEditForm .= '</fieldset>';
		if($groupRow['form_id'] == 0) {
			$groupEditForm .= <<<GROUPEDITFORM
				<br />
				<fieldset style="padding: 8px">
					<legend>Add Users to Group</legend>
					<form name="addusertogroup" method="POST" action="./+grant&subaction=editgroups&groupname={$groupRow['group_name']}">
						Email ID: <input type="text" name="txtUserEmail" id="txtUserEmail" value="" style="width: 256px" autocomplete="off" />
						<div id="suggestionDiv" class="suggestionbox"></div>

						<script language="javascript" type="text/javascript" src="$scriptsFolder/ajaxsuggestionbox.js"></script>
						<script language="javascript" type="text/javascript">
						<!--
							var addUserBox = new SuggestionBox(document.getElementById('txtUserEmail'), document.getElementById('suggestionDiv'), "./+grant&doaction=getsuggestions&forwhat=%pattern%");
							addUserBox.loadingImageUrl = '$imagesFolder/ajaxloading.gif';
						-->
						</script>

						<input type="submit" name="btnAddUserToGroup" value="Add User to Group" />
					</form>
				</fieldset>
GROUPEDITFORM;
		}
		$groupEditForm .= <<<GROUPEDITFORM
			<br />
			<fieldset style="padding: 8px">
				<legend>Associate With Form</legend>
				<form name="groupassociationform" action="./+grant&subaction=editgroups&subsubaction=associateform&groupname={$groupRow['group_name']}" method="POST">
					$associateForm
				</form>
			</fieldset>
GROUPEDITFORM;

		return $groupEditForm;
	}

	if ($subAction == 'editgrouppriorities') {
		$modifiableCount = count($modifiableGroups);
		$userMaxPriority = $maxPriorityGroup = 1;
		if($modifiableCount != 0) {
			$userMaxPriority = max($modifiableGroups[0]['group_priority'], $modifiableGroups[$modifiableCount - 1]['group_priority']);
			$maxPriorityGroup = $modifiableGroups[0]['group_priority'] > $modifiableGroups[$modifiableCount - 1]['group_priority'] ? $modifiableGroups[0]['group_id'] : $modifiableGroups[$modifiableCount - 1]['group_id'];
		}

		if(isset($_GET['dowhat']) && !is_null($groupId)) {
			if($_GET['dowhat'] == 'incrementpriority' || $_GET['dowhat'] == 'decrementpriority') {
				shiftGroupPriority($currentUserId, $groupRow['group_name'], $_GET['dowhat'] == 'incrementpriority' ? 'up' : 'down', $userMaxPriority, true);
			}
			elseif($_GET['dowhat'] == 'movegroupup' || $_GET['dowhat'] == 'movegroupdown') {
				shiftGroupPriority($currentUserId, $groupRow['group_name'], $_GET['dowhat'] == 'movegroupup' ? 'up' : 'down', $userMaxPriority, false);
			}
			elseif($_GET['dowhat'] == 'emptygroup') {
				emptyGroup($groupRow['group_name']);
			}
			elseif($_GET['dowhat'] == 'deletegroup') {
				if(deleteGroup($groupRow['group_name'])) {
					$virtue = '';
					$maxPriorityGroup = getMaxPriorityGroup($pagePath, $currentUserId, array_reverse(getGroupIds($currentUserId)), $virtue);
					$modifiableGroups = getModifiableGroups($currentUserId, $maxPriorityGroup, $ordering = 'asc');
				}
			}

			$modifiableGroups = reevaluateGroupPriorities($modifiableGroups);
		}
		elseif(isset($_GET['dowhat']) && $_GET['dowhat'] == 'addgroup') {
			if(isset($_POST['txtGroupName']) && isset($_POST['txtGroupDescription']) && isset($_POST['selGroupPriority'])) {
				$existsQuery = 'SELECT `group_id` FROM `' . MYSQL_DATABASE_PREFIX . "groups` WHERE `group_name` = '{$_POST['txtGroupName']}'";
				$existsResult = mysql_query($existsQuery);
				if(trim($_POST['txtGroupName']) == '') {
					displayerror('Cannot create a group with an empty name. Please type in a name for the new group.');
				}
				elseif(mysql_num_rows($existsResult) >= 1) {
					displayerror('A group with the name you specified already exists.');
				}
				else {
					$idQuery = 'SELECT MAX(`group_id`) FROM `' . MYSQL_DATABASE_PREFIX . 'groups`';
					$idResult = mysql_query($idQuery);
					$idRow = mysql_fetch_row($idResult);
					$newGroupId = 2;
					if(!is_null($idRow[0])) {
						$newGroupId = $idRow[0] + 1;
					}

					$newGroupPriority = 1;
					if($_POST['selGroupPriority'] <= $userMaxPriority && $_POST['selGroupPriority'] > 0) {
						$newGroupPriority = $_POST['selGroupPriority'];
					}

					$addGroupQuery = 'INSERT INTO `' . MYSQL_DATABASE_PREFIX . 'groups` (`group_id`, `group_name`, `group_description`, `group_priority`) ' .
							"VALUES($newGroupId, '{$_POST['txtGroupName']}', '{$_POST['txtGroupDescription']}', $newGroupPriority)";
					$addGroupResult = mysql_query($addGroupQuery);
					if($addGroupResult) {
						displayinfo('New group added successfully.');

						if(isset($_POST['chkAddMe'])) {
							$insertQuery = 'INSERT INTO `' . MYSQL_DATABASE_PREFIX . "usergroup`(`user_id`, `group_id`) VALUES ($currentUserId, $newGroupId)";
							if(!mysql_query($insertQuery)) {
								displayerror('Error adding user to newly created group: ' . $insertQuery . '<br />' . mysql_query());
							}
						}
						$virtue = '';
						$maxPriorityGroup = getMaxPriorityGroup($pagePath, $currentUserId, array_reverse(getGroupIds($currentUserId)), $virtue);
						$modifiableGroups = getModifiableGroups($currentUserId, $maxPriorityGroup, $ordering = 'asc');
					}
					else {
						displayerror('Could not run MySQL query. New group could not be added.');
					}
				}
			}

			$modifiableGroups = reevaluateGroupPriorities($modifiableGroups);
		}

		$modifiableCount = count($modifiableGroups);
		if($modifiableGroups[0]['group_priority'] < $modifiableGroups[$modifiableCount - 1]['group_priority']) {
			$modifiableGroups = array_reverse($modifiableGroups);
		}
		$previousPriority = $modifiableGroups[0]['group_priority'];
		global $sourceFolder, $urlRequestRoot, $moduleFolder, $templateFolder;
		$iconsFolderUrl = "$urlRequestRoot/$sourceFolder/$templateFolder/common/icons/16x16";
		$moveUpImage = '<img src="' . $iconsFolderUrl . '/actions/go-up.png" title="Increment Group Priority" alt="Increment Group Priority" />';
		$moveDownImage = '<img src="' . $iconsFolderUrl . '/actions/go-down.png" alt="Decrement Group Priority" title="Decrement Group Priority" />';
		$moveTopImage = '<img src="' . $iconsFolderUrl . '/actions/go-top.png" alt="Move to next higher priority level" title="Move to next higher priority level" />';
		$moveBottomImage = '<img src="' . $iconsFolderUrl . '/actions/go-bottom.png" alt="Move to next lower priority level" title="Move to next lower priority level" />';
		$emptyImage = '<img src="' . $iconsFolderUrl . '/actions/edit-clear.png" alt="Empty Group" title="Empty Group" />';
		$deleteImage = '<img src="' . $iconsFolderUrl . '/actions/edit-delete.png" alt="Delete Group" title="Delete Group" />';

		$groupsForm = '<h3>Edit Group Priorities</h3><br />';
		for($i = 0; $i < $modifiableCount; $i++) {
			if($modifiableGroups[$i]['group_priority'] != $previousPriority) {
				$groupsForm .= '<br /><br /><hr /><br />';
			}
			$groupsForm .=
					'<span style="margin: 4px;" title="' . $modifiableGroups[$i]['group_description'] . '">' .
					'<a href="./+grant&subaction=editgroups&subsubaction=editgrouppriorities&dowhat=incrementpriority&groupname=' . $modifiableGroups[$i]['group_name'] . '">' . $moveUpImage . '</a>' .
					'<a href="./+grant&subaction=editgroups&subsubaction=editgrouppriorities&dowhat=decrementpriority&groupname=' . $modifiableGroups[$i]['group_name'] . '">' . $moveDownImage . '</a>' .
					'<a href="./+grant&subaction=editgroups&subsubaction=editgrouppriorities&dowhat=movegroupup&groupname=' . $modifiableGroups[$i]['group_name'] . '">' . $moveTopImage . '</a>' .
					'<a href="./+grant&subaction=editgroups&subsubaction=editgrouppriorities&dowhat=movegroupdown&groupname=' . $modifiableGroups[$i]['group_name'] . '">' . $moveBottomImage . '</a>' .
					'<a onclick="return confirm(\'Are you sure you want to empty this group?\')" href="./+grant&subaction=editgroups&subsubaction=editgrouppriorities&dowhat=emptygroup&groupname=' . $modifiableGroups[$i]['group_name'] . '">' . $emptyImage . '</a>' .
					'<a onclick="return confirm(\'Are you sure you want to delete this group?\')" href="./+grant&subaction=editgroups&subsubaction=editgrouppriorities&dowhat=deletegroup&groupname=' . $modifiableGroups[$i]['group_name'] . '">' . $deleteImage . '</a>' .
					'<a href="./+grant&subaction=editgroups&groupname=' . $modifiableGroups[$i]['group_name'] . '">' . $modifiableGroups[$i]['group_name'] . "</a></span>\n";
			$previousPriority = $modifiableGroups[$i]['group_priority'];
		}

		$priorityBox = '<option value="1">1</option>';
		for($i = 2; $i <= $userMaxPriority; ++$i) {
			$priorityBox .= '<option value="' . $i . '">' . $i . '</option>';
		}
		$groupsForm .= <<<GROUPSFORM
		<br /><br />
		<fieldset style="padding: 8px">
			<legend>Create New Group:</legend>

			<form name="groupaddform" method="POST" action="./+grant&subaction=editgroups&subsubaction=editgrouppriorities&dowhat=addgroup">
				<label>Group Name: <input type="text" name="txtGroupName" value="" /></label><br />
				<label>Group Description: <input type="text" name="txtGroupDescription" value="" /></label><br />
				<label>Group Priority: <select name="selGroupPriority">$priorityBox</select><br />
				<label><input type="checkbox" name="chkAddMe" value="addme" /> Add me to group</label><br />
				<input type="submit" name="btnAddNewGroup" value="Add Group" />
			</form>
		</fieldset>
GROUPSFORM;

		return $groupsForm;
	}


	$modifiableCount = count($modifiableGroups);
	$groupsBox = '<select name="selEditGroups">';
	for($i = 0; $i < $modifiableCount; ++$i) {
		$groupsBox .= '<option value="' . $modifiableGroups[$i]['group_name'] . '">' . $modifiableGroups[$i]['group_name'] . ' - ' . $modifiableGroups[$i]['group_description'] . "</option>\n";
	}
	$groupsBox .= '</select>';

	$groupsForm = <<<GROUPSFORM
		<form name="groupeditform" method="POST" action="./+grant&subaction=editgroups">
			$groupsBox
			<input type="submit" name="btnEditGroup" value="Edit Selected Group" /><br /><br />
			<input type="submit" name="btnEditGroupPriorities" value="Add/Shuffle/Remove Groups" />
		</form>

GROUPSFORM;

	return $groupsForm;
}

/**
 * What different group ids and user ids mean :
 *
 * Group 0 : Everyone
 * Group 1 : Only logged in users are in this group
 * User 0 : Whoever is not logged in, gets this user id
 * User 1 to inf : Different Users
 * Group 2 to inf : Different Groups
 *
 * A user may be in more than groups.
 * For example
 * userid 8 maybe a member of the groups 8 and 12.
 * When he views the site anonymously, his uid is 0, his gid is 0.
 * When he logs in, his uid is 8, hid gids are 0, 1, 8 and 12
 *
 *
 * add() is defined as :
 * 	start from top, if you find a no on the way, answer is no,
 *				if you find no nos and atleast one yes, answer is yes
 *				if you find nothing, (all unsets) answer is no
 *
 * All groups have a certain priority assigned to them.
 * Possible permissions are  : Y, N, and unset (i.e. no value in the table)
 * To find the permission for a particular action on a particular page:
 * 		Find all groups that he belongs to.
 *  	Take all the groups priority wise and start from the lowest priority group and do this for each group:
 * 				In the page path, starting from the root node going towards the leaf node, do add and get the result
 * 		Group all results of same priority, and make it yes, if even a single yes is available (OR)
 * 		Now, we ll have an array of yeses and nos arranged by priority.
 *    +-----------+---+---+---+---+
 * 		| Priority: | 0 | 1 | 2 | 3 |
 *    +-----------+---+---+---+---+
 * 		| Perm    : | N | N | Y | N |
 *    ------------+---+---+---+---+
 * 		i.e Do an OR to get result (regardless of priority).
 *
 * -- Condition: To give a user the perm_grant permission, he has to belong to a group. --
 *
 * While giving permission: (grant)
 * 		For one module, for one page:
 * 		Find all groups that he belongs to.
 * 		If he gets perm_grant individually : find the highest priority group that he belongs to . save it in A
 * 		If he get perm_grant from group(s) :
 * 				Find those groups which give him perm_grant on that page
 * 				Find the highest priority among those. save it in B
 * 				Allow him to give or take permission only from those groups with priority <= max(A,B)
 * 				Allow him to give or take permission from all individual users.
 */

?>