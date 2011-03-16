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
 * @author Abhilash R
 * @copyright (c) 2010 Pragyan Team
 * @license http://www.gnu.org/licenses/ GNU Public License
 * For more details, see README
 */

/**
 * permission.lib.php: Everything to do with page and module permissions
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
function renderArray($array) {
	$ret = '';
	foreach($array as $val)
		$ret .= "'{$val}', ";
	$ret = rtrim($ret, ", ");
	return $ret;
}

function inner($smallobj) {
	$ret = '';
	foreach($smallobj as $key => $val) {
		$temp = renderArray($val);
		$ret .= "'{$key}' : [{$temp}], ";
	}
	$ret = rtrim($ret, ", ");
	return $ret;
}

function customjson($objDesc) {
	return "{'Y' : {" . inner($objDesc['Y']) . "}, 'N' : {" . inner($objDesc['N']) . "}}";
}

/**
 * Returns permissions of all users and visible groups on a particular page
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
	for($i = 0; $i < $userCount; $i++) {
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
	
	$sortedGroupPerms = array('Y' => array(), 'N' => array());
	$sortedUserPerms = array('Y' => array(), 'N' => array());
	
	foreach($groupEffectivePermissions as $groupid => $data) {
		foreach($groupEffectivePermissions[$groupid] as $permid => $value) {
			if($value === true) {
				if(!isset($sortedGroupPerms['Y'][$groupid]))
					$sortedGroupPerms['Y'][$groupid] = array();
				$sortedGroupPerms['Y'][$groupid][] = $permid;
			} else {
				if(!isset($sortedGroupPerms['N'][$groupid]))
					$sortedGroupPerms['N'][$groupid] = array();
				$sortedGroupPerms['N'][$groupid][] = $permid;
			}
		}
	}
	
	foreach($userEffectivePermissions as $userid => $data) {
		foreach($userEffectivePermissions[$userid] as $permid => $value) {
			if($value === true) {
				if(!isset($sortedUserPerms['Y'][$userid]))
					$sortedUserPerms['Y'][$userid] = array();
				$sortedUserPerms['Y'][$userid][] = $permid;
			} else {
				if(!isset($sortedUserPerms['N'][$userid]))
					$sortedUserPerms['N'][$userid] = array();
				$sortedUserPerms['N'][$userid][] = $permid;
			}
		}
	}
	
	return array($sortedGroupPerms,$sortedUserPerms);
}

function formattedPermissions($pagepath, $modifiableGroups, $grantableActions) {

	list($sortedGroupPerms,$sortedUserPerms) = getAllPermissionsOnPage($pagepath, $modifiableGroups, $grantableActions);
	
	$groupReturnText = customjson($sortedGroupPerms);
	$userReturnText = customjson($sortedUserPerms);
	
	$ret = <<<RET
permGroups = {$groupReturnText};
permUsers = {$userReturnText};
RET;
	return $ret;
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
 //TODO : Make sure that when admin is granted, it gets granted only at pageid 0
function getPermissions($userid, $pageid, $action, $module="") {
	if($action!="admin" && getPermissions($userid,0,"admin"))
		return true;
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
			$idQuery = "SELECT `group_id` FROM `".MYSQL_DATABASE_PREFIX."groups` WHERE `group_name` = '".escape($_POST['modifiablegroups'])."'";
		}
	}
	else if($_POST['optusergroup'] == 'user') {
		$hyphenPos = strpos($_POST['useremail'], '-');
		if($hyphenPos >= 0) {
			$userEmail = escape(trim(substr($_POST['useremail'], 0, $hyphenPos - 1)));
		}
		else {
			$userEmail = escape($_POST['useremail']);
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
	//serving change permission requests
	if(isset($_GET['doaction']) && $_GET['doaction'] == "changePerm") {
		$permtype = escape($_GET['permtype']);
		$pageid = escape($_GET['pageid']);
		$usergroupid = escape($_GET['usergroupid']);
		$permid = escape($_GET['permid']);
		$perm = escape($_GET['perm']);
		$flag = true;
		if($perm == 'Y' || $perm == 'N') {
			if($permission = mysql_fetch_array(mysql_query("SELECT `perm_permission` FROM `" . MYSQL_DATABASE_PREFIX . "userpageperm` WHERE `perm_type` = '{$permtype}' AND `page_id` = '{$pageid}' AND `usergroup_id` = '{$usergroupid}' AND `perm_id` = '{$permid}'"))) {
				if($permission['perm_permission'] != $perm) {
					mysql_query("UPDATE `" . MYSQL_DATABASE_PREFIX . "userpageperm` SET `perm_permission` = '{$perm}' WHERE `perm_type` = '{$permtype}' AND `page_id` = '{$pageid}' AND `usergroup_id` = '{$usergroupid}' AND `perm_id` = '{$permid}'");
					if(mysql_affected_rows() == 0)
						$flag = false;
				}
			} else {
				mysql_query("INSERT `" . MYSQL_DATABASE_PREFIX . "userpageperm`(`perm_type`, `page_id`, `usergroup_id`, `perm_id`, `perm_permission`) VALUES('$permtype','$pageid','$usergroupid','$permid','$perm')");
				if(mysql_affected_rows() == 0)
					$flag = false;
			}
		} else {
			if($permission = mysql_fetch_array(mysql_query("SELECT `perm_permission` FROM `" . MYSQL_DATABASE_PREFIX . "userpageperm` WHERE `perm_type` = '{$permtype}' AND `page_id` = '{$pageid}' AND `usergroup_id` = '{$usergroupid}' AND `perm_id` = '{$permid}'"))) {
				mysql_query("DELETE FROM `" . MYSQL_DATABASE_PREFIX . "userpageperm` WHERE `perm_type` = '{$permtype}' AND `page_id` = '{$pageid}' AND `usergroup_id` = '{$usergroupid}' AND `perm_id` = '{$permid}'");
				if(mysql_affected_rows() == 0)
					$flag = false;
			}
		}
		
		if($flag)
			echo "1";
		else
			echo "0";
		disconnect();
		exit();
	}
	//serving refresh permissions request
	if(isset($_GET['doaction']) && $_GET['doaction'] == 'getpermvars' && isset($_GET['pageid'])) {
		global $cmsFolder,$urlRequestRoot, $templateFolder;
		$pageid = escape($_GET['pageid']);
		if(mysql_fetch_array(mysql_query("SELECT `page_name` FROM `" . MYSQL_DATABASE_PREFIX . "pages` WHERE `page_id` = '{$pageid}'"))) {
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
			list($modTemp, $actTemp) = explode('_', escape($_POST['permission'][$i]), 2);

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
		$permissions = formattedPermissions($pagepath, $modifiableGroupIds, $grantableActions);
			$ret =<<<RET
pageid = {$pageid};
{$permissions}
RET;
			echo $ret;
		} else {
			echo "Error: Invalid Pageid passed";
		}
		disconnect();
		exit();
	}
	
	global $cmsFolder,$urlRequestRoot;
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
	if(isset($_POST['permission']))
	$actionCount = count($_POST['permission']);
	else $actionCount="";
	$checkedActions = array();
	for($i = 0; $i < $actionCount; $i++) {
		list($modTemp, $actTemp) = explode('_', escape($_POST['permission'][$i]), 2);

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
	$perms = json_encode(formatPermissions($grantableActions));
	$permissions = formattedPermissions($pagepath, $modifiableGroupIds, $grantableActions);
	$groups = customGetGroups($maxPriorityGroup);
	$users = customGetAllUsers();
	global $templateFolder;
	$smarttableconfig = array (
			'permtable' => array(
				'sPaginationType' => 'two_button',
				'bAutoWidth' => 'false',
				'aoColumns' => '{ "sWidth": "100px" }'
			),
			'permtable2' => array(
				'sPaginationType' => 'two_button',
				'bAutoWidth' => 'false',
				'aoColumns' => '{ "sWidth": "100px" }'
			)
	);
	$ret = smarttable::render(array('permtable','permtable2'),$smarttableconfig);
	$globals = getGlobalSettings();
	$baseURL = "./+grant&doaction=changePerm";
	if($globals['url_rewrite']=='false')
		$baseURL = prettyurl($baseURL);
	$selected = "var selected = {'permissions' : [], 'users' : [], 'groups' : []};";
	if(isset($_GET['doaction']) && $_GET['doaction'] == 'getUserPerm') {
		$get_selectedPerms = array();
		$get_selectedGroups = array();
		$get_selectedUsers = array();
		foreach($_POST as $key => $var)
			if(substr($key,0,12)=="permissions_")
				$get_selectedPerms[] = (int)substr($key,12);
		list($get_sortedGroupPerms,$get_sortedUserPerms) = getAllPermissionsOnPage($pagepath, $modifiableGroupIds, $grantableActions);
		$save = 0;
		foreach($get_sortedGroupPerms['Y'] as $get_groupId => $get_data) {
			$found = false;
			foreach($get_sortedGroupPerms['Y'][$get_groupId] as $get_permId) {
				foreach($get_selectedPerms as $selected_perm)
					if($selected_perm == $get_permId) {
						$get_selectedGroups[] = (int)$get_groupId;
						$found = true;
					}
				if($found)
					break;
			}
			if($get_groupId==0&&$found)
				$save += 1;
			if($get_groupId==1&&$found)
				$save += 2;
		}
		foreach($get_sortedUserPerms['Y'] as $get_userId => $get_data) {
			$found = false;
			foreach($get_sortedUserPerms['Y'][$get_userId] as $get_permId) {
				foreach($get_selectedPerms as $selected_perm)
					if($selected_perm == $get_permId) {
						$get_selectedUsers[] = (int)$get_userId;
						$found = true;
					}
				if($found)
					break;
			}
		}
		$get_selectedGroups = filterByPriority($maxPriorityGroup,$get_selectedGroups);
		if($save%2==1)
			$get_selectedGroups[] = 0;
		if($save/2==1)
			$get_selectedGroups[] = 1;
		$selected = "var selected = {'permissions' : " . json_encode($get_selectedPerms) . ", 'users' : " . json_encode($get_selectedUsers) . ", 'groups' : " . json_encode($get_selectedGroups) . "};";
	}
	if(isset($_GET['doaction']) && $_GET['doaction'] == 'getPermUser') {
		
		$get_selectedPerms = array();
		$get_selectedGroups = array();
		$get_selectedUsers = array();
		foreach($_POST as $key => $var)
			if(substr($key,0,6)=="users_")
				$get_selectedUsers[] = (int)substr($key,6);
			else if(substr($key,0,7)=="groups_")
				$get_selectedGroups[] = (int)substr($key,7);
		list($get_sortedGroupPerms,$get_sortedUserPerms) = getAllPermissionsOnPage($pagepath, $modifiableGroupIds, $grantableActions);
		$save = 0;
		foreach($get_sortedGroupPerms['Y'] as $get_groupId => $get_data) {
			if(isPresent($get_groupId,$get_selectedGroups)) {
				foreach($get_sortedGroupPerms['Y'][$get_groupId] as $get_permId) {
					if(!isPresent($get_permId,$get_selectedPerms))
						$get_selectedPerms[] = $get_permId;
				}
			}
		}
		foreach($get_sortedUserPerms['Y'] as $get_userId => $get_data) {
			if(isPresent($get_userId,$get_selectedUsers)) {
				foreach($get_sortedUserPerms['Y'][$get_userId] as $get_permId) {
					if(!isPresent($get_permId,$get_selectedPerms))
						$get_selectedPerms[] = $get_permId;
				}
			}
		}
		$selected = "var selected = {'permissions' : " . json_encode($get_selectedPerms) . ", 'users' : " . json_encode($get_selectedUsers) . ", 'groups' : " . json_encode($get_selectedGroups) . "};";
	}
	$ret .= <<<RET
<style type="text/css" title="currentStyle">
	div#permtable_filter input { width: 90px; }
	div#permtable2_filter input { width: 90px; }
</style>
<script type="text/javascript" language="javascript" src="$urlRequestRoot/$cmsFolder/$templateFolder/common/scripts/permissionsTable.js"></script>
<script type="text/javascript">
var baseURL = "$baseURL";
var pageid = {$pageid};
var permissions = {$perms};
var permGroups;
var permUsers;
var groups = {{$groups}};
var users = {{$users}};
{$permissions}
{$selected}
</script>
<div id='info'></div>
<INPUT type=checkbox id='skipAlerts'> Skip Alerts <br>
<div id='permTable'>

</div>
<table width=100%>
<tr>
<td width=50%>
<a href='javascript:selectAll1()'>Select All</a> <a href='javascript:clearAll1()'>Clear All</a> <a href='javascript:toggle1()'>Toggle</a> <a href='javascript:getuserperm()'>Check Users having selected Permission</a><br>
<form action='./+grant&doaction=getUserPerm' method="POST" id='getuserperm'>
<table class="userlisttable display" id='permtable' name='permtable'><thead><tr><th>Permissions</th></thead><tbody id='actionsList'>

</tbody></table>
</form>
</td>
<td width=50%>
<a href='javascript:selectAll2()'>Select All</a> <a href='javascript:clearAll2()'>Clear All</a> <a href='javascript:toggle2()'>Toggle</a> <a href='javascript:getpermuser()'>Check Permissions selected User is having</a><br>
<form action='./+grant&doaction=getPermUser' method="POST" id='getpermuser'>
<table class="userlisttable display" id='permtable2' name='permtable2'><thead><tr><th>Users</th></thead><tbody id='usersList'>

</tbody></table>
</form>
</td>
</tr>
</table>

<a href='javascript:populateList()'>Click here if the lists are empty</a>
RET;
	global $STARTSCRIPTS;
	$STARTSCRIPTS .= " populateList();";
	return $ret;
}

function getPerms($pageId, $groupuser, $yesno) {
	$ret = "";
	$result = mysql_query("SELECT `usergroup_id`, `perm_id` FROM `" . MYSQL_DATABASE_PREFIX . "userpageperm` WHERE `page_id` = '{$pageId}' AND `perm_type` = '{$groupuser}' AND `perm_permission` = '{$yesno}'");
	while($row = mysql_fetch_array($result))
		$perms[$row['usergroup_id']][] = $row['perm_id'];
	if(isset($perms)) 
		foreach($perms as $group => $values) {
			$ret .= "'" . $group . "' : [";
			foreach($values as $value)
				$ret .= "'" . $value . "', ";
			$ret = rtrim($ret, ", ");
			$ret .= "], ";
		}
	$ret = rtrim($ret, ", ");
	return $ret;
}

function customGetAllUsers() {
	$ret = "";
	$result = mysql_query("SELECT `user_email`, `user_name`, `user_id` FROM `" . MYSQL_DATABASE_PREFIX . "users`");
	while($row = mysql_fetch_array($result))
		$ret .= "'{$row['user_id']}' : '{$row['user_name']} &lt;{$row['user_email']}&gt;', ";
	$ret = rtrim($ret,", ");
	return $ret;	
}

function customGetGroups($priority) {
	$ret = "'0' : 'Everyone', '1' : 'Logged in Users', ";
	$result = mysql_query("SELECT `group_name`,`group_id` FROM `" . MYSQL_DATABASE_PREFIX . "groups` WHERE `group_priority` < '{$priority}'");
	while($row = mysql_fetch_array($result))
		$ret .= "'{$row['group_id']}' : '{$row['group_name']}', ";
	$ret = rtrim($ret,", ");
	return $ret;
}

function filterByPriority($priority,$groups) {
	$return = array();
	$result = mysql_query("SELECT `group_id` FROM `" . MYSQL_DATABASE_PREFIX . "groups` WHERE `group_priority` < '{$priority}'");
	while($row = mysql_fetch_assoc($result))
		foreach($groups as $group)
			if($group == $row['group_id'])
				$return[] = $group;
	return $return;
}

function getAllPermissions() {
	$ret = "";
	$result = mysql_query("SELECT `perm_id`,`page_module`,`perm_action` FROM `" . MYSQL_DATABASE_PREFIX . "permissionlist`");
	while($row = mysql_fetch_array($result))
		$ret .= "'{$row['perm_id']}' : '{$row['page_module']} - {$row['perm_action']}', ";
	$ret = rtrim($ret,", ");
	return $ret;
}

function formatPermissions($perms) {
	$return = array();
	foreach($perms as $modulename => $array)
		foreach($array as $row)
			$return[$row[0]] = "{$modulename} - {$row[1]}";
	return $return;
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
								 "WHERE `usergroup_id` = '$usergroupid' AND `page_id` = '$pageid' AND `perm_id` = '$permid' AND " .
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
							 "`usergroup_id` = '$usergroupid' AND `page_id` = '$pageid' AND `perm_id` = '$permid' AND " .
							 "`perm_type` = '$permtype'";
	$permQueryResult = mysql_query($permQuery);

	if($permQueryResultRow = mysql_fetch_assoc($permQueryResult)) {
		if($permission != $permQueryResultRow['perm_permission']) {
			$updateQuery = "UPDATE `".MYSQL_DATABASE_PREFIX."userpageperm` SET `perm_permission` = '$permission' " .
										 "WHERE `usergroup_id` = '$usergroupid' AND `page_id` = '$pageid' AND `perm_id` = '$permid' AND " .
							 			 "`perm_type` = '$permtype' LIMIT 1";
		}
	}
	else {
		$updateQuery = "INSERT INTO `".MYSQL_DATABASE_PREFIX."userpageperm` (`perm_type`, `page_id`, `usergroup_id`, `perm_id`, `perm_permission`) " .
									 "VALUES('$permtype', '$pageid', '$usergroupid', '$permid', '$permission')";
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

	$groupPriority = "(SELECT `group_priority` FROM `$groupsTable` WHERE `group_id` = '$maxPriorityGroup')";
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

function isPresent($needle,$haystack) {
	foreach($haystack as $hay) {
		if($hay==$needle)
			return true;
	}
	return false;
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

