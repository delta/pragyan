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
Admin page should be able to move perm_rank up and down and check if create and view exists
 for everything and if the admin has all premissions or not. If the admin does not then it
  gives him the permission.
OR this could also be implemented through the getPermissions function


Groups ->
This is what grant will have :
	Ability to create a group and give its description and a priority.
	Change priority of other groups below one's own priority
	Ability to associate an empty group with a form.
		During this association make sure that only those forms are listed whose form_loginrequired is 1
		and to which the guy has editregistrants permission. Also copy all users from the form to
		the group. Call a function in form which gives list of all registered users to a form.
	Ability to unassociate with a form.
		Empty the group then.
	Ability to empty a group -> only those forms whose form_id is 0 (for other forms
	 	give a link to the form edit page.)
	Ability to add remove people from a group -> only those forms whose form_id is 0
*/



/**
 * Returns all information about a particular group from the `groups` TABLE
 * @param $groupName Name of the group, whose information is to be returned
 * @return Associative array containing the fields in that particular row
 */
function getGroupRow($groupName) {
	$groupQuery = "SELECT * FROM `".MYSQL_DATABASE_PREFIX."groups` WHERE `group_name` = '".escape($groupName)."'";
	$groupResult = mysql_query($groupQuery);
	return mysql_fetch_assoc($groupResult);
}

function getGroupIdFromName($groupName) {
	$groupRow = getGroupRow($groupName);
	return $groupRow['group_id'];
}
/**
 * getGroupIdFromFormId
 * Returns the group id of the given form id
 */
 function getGroupIdFromFormId($formId){
 	if($formId == 0) {
 	  return false;
 	}
	$query = "SELECT `group_id` FROM `".MYSQL_DATABASE_PREFIX."groups` WHERE `form_id`='".escape($formId)."'";
	$result = mysql_query($query);
	if(mysql_num_rows($result)>0){
		$array = mysql_fetch_assoc($result);
		$groupId = $array['group_id'];
		return $groupId;
	}
	else
		return false;
 }
/**
 * getFormIdFromGroupId
 * Returns the form id of the given group id
 */
function getFormIdFromGroupId($groupId){
	$query = "SELECT `form_id` FROM `".MYSQL_DATABASE_PREFIX."groups` WHERE `group_id`='".escape($groupId)."'";
	$result = mysql_query($query);
	if(mysql_num_rows($result)>0){
		$array = mysql_fetch_assoc($result);
		$formId = $array['form_id'];
		return $formId;
	}
	else
		return false;
 }

/**
 * Moves a group up one step by priority
 * @param $groupName The name that identifies the group
 * @param $shiftUp Boolean, indicating whether groups of higher priority should be shifted up,
 * 								 or whether this group should be moved beside the next higher priority group
 * @return Boolean, true indicating success, false indicating failure
 */
function shiftGroupPriority($userId, $groupName, $direction = 'up', $userMaxPriority, $shiftNeighbours = true) {
	$userId=escape($userId);
	$direction=escape($direction);
	$userMaxPriority=escape($userMaxPriority);
	$groupRow = getGroupRow($groupName);
	if(!$groupRow) {
		return false;
	}

	$groupId = $groupRow['group_id'];
	$groupPriority = $groupRow['group_priority'];

	$op = ($direction == 'up' ? '+' : '-');
	$rel = ($direction == 'up' ? '>' : '<');
	$order = ($direction == 'up' ? 'asc' : 'desc');
	
	$groupsTable = MYSQL_DATABASE_PREFIX . 'groups';
	$usergroupTable = MYSQL_DATABASE_PREFIX . 'usergroup';

/// Check if the user is shifting a group with priority = maxprioritygroup, and if he belongs to that group, stop him!
	if($groupRow['group_priority'] == $userMaxPriority) {
		// SELECT `group_id` FROM .. WHERE `group_priority` = maxPriority AND `user_id` = $userId AND `group_id` = `group_id`
		$memberQuery = "SELECT `$usergroupTable`.`group_id` FROM `$usergroupTable`, `$groupsTable` WHERE `group_priority` = '{$groupRow['group_priority']}' AND `user_id` = '$userId' AND `$usergroupTable`.`group_id` = `$groupsTable`.`group_id`";
		$memberResult = mysql_query($memberQuery);
		if(!$memberResult) {
			displayerror($memberQuery . '<br />' . mysql_error());
			return false;
		}
		if(mysql_num_rows($memberResult) == 1) {
			$memberRow = mysql_fetch_row($memberResult);
			if($memberRow[0] == $groupId) {
				displayerror('Error. Cannot shift the group that gives you grant permissions at this level.');
				return false;
			}
		}
	}

/// No shifting to a priority less than 1. No shifting to a priority greater than the user's maximum priority group.
/// Shift neighbours:
///		if there are other groups with priority same as the given group,
///			we want the group to go from its current priority place to just before the next higher priority
///				For this, check if there are groups with priority = curpriority + 1 or curpriority - 1 for moving down. if yes, shift everything up by 1
///				update curpriority to curpriority + 1
///		else
///			set group's priority to next higher existing priority
/// No shift neighbours:
///		Take group from current priority to next higher existing priority, if the priority is less than user's max priority group's priority
	$newPriority = -1;

	if($shiftNeighbours) {
		$groupQuery = 'SELECT `group_id` FROM `' . MYSQL_DATABASE_PREFIX . 'groups` WHERE `group_priority` =\'' . $groupPriority."'";
		$groupResult = mysql_query($groupQuery);
		if(mysql_num_rows($groupResult) > 1) {
			$groupQuery = 'SELECT `group_id` FROM `' . MYSQL_DATABASE_PREFIX . 'groups` WHERE `group_priority` = ' . $groupPriority . " $op 1";
			$groupResult = mysql_query($groupQuery);
			if (mysql_num_rows($groupResult) > 0) {
				$shiftQuery = "UPDATE `" . MYSQL_DATABASE_PREFIX . "groups` SET `group_priority` = `group_priority` + 1 WHERE `group_priority` " . ($direction == 'up' ? '>' : '>=') . " $groupPriority";
				$shiftResult = mysql_query($shiftQuery);
				$groupPriority++;
			}

			if($direction == 'up')
				$newPriority = $groupPriority + 1;
			else
				$newPriority = $groupPriority - 1;
		}
		else {
			/// no other groups on same level. find next higher existing priority level
			$groupQuery = 'SELECT `group_priority` FROM `' . MYSQL_DATABASE_PREFIX . "groups` WHERE `group_priority` $rel $groupPriority ORDER BY `group_priority` $order LIMIT 0, 1";
			$groupResult = mysql_query($groupQuery);
			if(mysql_num_rows($groupResult) == 1) {
				$groupRow = mysql_fetch_row($groupResult);
				$newPriority = $groupRow[0];
			}
			else {
				if($direction == 'up')
					$newPriority = $groupPriority + 1;
				else
					$newPriority = $groupPriority - 1;
			}
		}
	}
	else {
		$groupQuery = 'SELECT `group_priority` FROM `' . MYSQL_DATABASE_PREFIX . "groups` WHERE `group_priority` $rel $groupPriority ORDER BY `group_priority` $order LIMIT 0, 1";
		$groupResult = mysql_query($groupQuery);
		if(mysql_num_rows($groupResult) == 1) {
			$groupRow = mysql_fetch_row($groupResult);
			$newPriority = $groupRow[0];
		}
		else {
			if($direction == 'up')
				$newPriority = $groupPriority + 1;
			else
				$newPriority = $groupPriority - 1;
		}
	}


	if($newPriority <= 0) {
		displayinfo('You cannot decrease the priority of a group below the current priority.');
		return false;
	}
	elseif($newPriority > $userMaxPriority) {
		displayinfo('You cannot increase the priority of the group above the current priority.');
		return false;
	}

	$groupQuery = "UPDATE `".MYSQL_DATABASE_PREFIX."groups` SET `group_priority` = '$newPriority' WHERE `group_id` = '$groupId'";
	if(mysql_query($groupQuery)) {
		return true;
	}
	else {
		return false;
	}
}

function getUsersRegisteredToGroup($groupId) {
	$userQuery = 'SELECT `user_id` FROM `' . MYSQL_DATABASE_PREFIX . 'usergroup` WHERE `group_id` = \'' . $groupId."'";
	$userResult = mysql_query($userQuery);
	$registeredUserIds = array();
	while($userRow = mysql_fetch_row($userResult)) {
		$registeredUserIds[] = $userRow[0];
	}

	return $registeredUserIds;
}

function associateGroupWithForm($groupId, $formId) {
	global $sourceFolder, $moduleFolder;
	require_once("$sourceFolder/$moduleFolder/form.lib.php");

	$existsQuery = 'SELECT `group_id` FROM `' . MYSQL_DATABASE_PREFIX . 'groups` WHERE `form_id` = \'' . $formId."'";
	$existsResult = mysql_query($existsQuery);
	if(!$existsResult) displayerror($existsQuery . ' ' . mysql_error());
	if(mysql_num_rows($existsResult)) {
		displayerror('The given form is already associated with another group.');
		return false;
	}
	$isFormEmpty = (form::getRegisteredUserCount($formId) == 0);
	if(!isGroupEmpty($groupId) && !$isFormEmpty) {
		displayerror('The group cannot be associated with the form because neither the given group, nor the selected form is empty.');
		return false;
	}
	if(!form::isGroupAssociable($formId)) {
		displayerror('The selected form cannot be associated with a group because it either allows anonymous users to register, and does not allow users to unregister.');
		return false;
	}

	if($isFormEmpty) {
		/// Copy group users to form
		$groupUsers = getUsersRegisteredToGroup($groupId);
		$groupUsersCount = count($groupUsers);

		require_once("$sourceFolder/$moduleFolder/form/registrationformsubmit.php");

		for($i = 0; $i < $groupUsersCount; $i++) {
			registerUser($formId, $groupUsers[$i]);
		}
	}
	else {
		$registeredUsers = form::getRegisteredUserArray($formId);

		if(count($registeredUsers) > 0) {
			$insertQuery = 'INSERT INTO `' . MYSQL_DATABASE_PREFIX . 'usergroup` (`user_id`, `group_id`) VALUES ';
			$registeredUserCount = count($registeredUsers);
			for($i = 0; $i < $registeredUserCount; $i++) {
				$registeredUsers[$i] = "($registeredUsers[$i], $groupId)";
			}
			$insertQuery .= implode($registeredUsers, ', ');
			if(!mysql_query($insertQuery)) {
				displayerror('Could not move registered users to group.');
				return false;
			}
		}
	}

	/// Update group table, copy all users to group
	$updateQuery = 'UPDATE `' . MYSQL_DATABASE_PREFIX . "groups` SET `form_id` = '$formId' WHERE `group_id` = '$groupId'";
	if(!mysql_query($updateQuery)) {
		displayerror('Could not associate the given group with the selected form.');
		return false;
	};

	return true;
}

function unassociateFormFromGroup($groupId) {
	$updateQuery = 'UPDATE `' . MYSQL_DATABASE_PREFIX . 'groups` SET `form_id` = 0 WHERE `group_id` = \'' . $groupId."'";
	$updateResult = mysql_query($updateQuery);
	if(!$updateResult) {
		displayerror('MySQL error! Could not unassociate the form from the given group.');
	}

	$deleteQuery = 'DELETE FROM `' . MYSQL_DATABASE_PREFIX . 'usergroup` WHERE `group_id` = \'' . $groupId."'";
	$deleteResult = mysql_query($deleteQuery);
	if(!$deleteResult) {
		displayerror('MySQL error! Could not remove users from the given group.');
	}
}

function getAssociableFormsList($userId, $emptyFormsOnly = false) {
	/// List containing form id, page title, page path
	$formIdQuery = 'SELECT `page_id`, `form_desc`.`page_modulecomponentid`, `page_title` FROM `' . MYSQL_DATABASE_PREFIX . "pages`, `form_desc` " .
			'WHERE `page_module` = \'form\' AND `form_loginrequired` = 1 AND `' .
			'form_desc`.`page_modulecomponentid` = `' . MYSQL_DATABASE_PREFIX . 'pages`.`page_modulecomponentid`';
	$formIdResult = mysql_query($formIdQuery);
	if(!$formIdResult) displayerror($formIdQuery . ' ' . mysql_error());
	$associableForms = array();

	global $sourceFolder, $moduleFolder;
	require_once("$sourceFolder/$moduleFolder/form.lib.php");

	while($formIdRow = mysql_fetch_row($formIdResult)) {
//		displayerror($userId . ' ' . $formIdRow[0] . ' ' . getPermissions($userId, $formIdRow[0], 'editform'));
		if(getPermissions($userId, $formIdRow[0], 'editregistrants')) {
			if($emptyFormsOnly) {
				if(form::getRegisteredUserCount($formIdRow[1]) == 0) {
					$associableForms[] = array($formIdRow[1], $formIdRow[2], getPagePath($formIdRow[0]));
				}
			}
			else {
				$associableForms[] = array($formIdRow[1], $formIdRow[2], getPagePath($formIdRow[0]));
			}
		}
	}

	return $associableForms;
}

function emptyGroup($groupName, $silent = false) {
	$groupRow = getGroupRow($groupName);
	if(!$groupRow) {
		return false;
	}

	$groupId = $groupRow['group_id'];
	$formId = $groupRow['form_id'];

	if($formId == 0) {
		$groupQuery = 'DELETE FROM `'.MYSQL_DATABASE_PREFIX.'usergroup` WHERE `group_id` = \''.$groupId."'";
		if(!mysql_query($groupQuery)) {
			displayerror('Error running MySQL query. The given group could not be emptied.');
			return false;
		}
		if(!$silent) displayinfo("Group '$groupName' Emptied Successfully");
	}
	else {
		displayinfo(
			'This group is associated with a form. You must unassociate the group from the form before you can empty it.' .
			'<a href="' . getPagePath(getPageIdFromModuleComponentId('form', $groupRow['form_id'])) . '">Click Here</a> to visit the form\'s edit page.'
		);
		return false;
	}
	return true;
}

function deleteGroup($groupName) {
	if(emptyGroup($groupName, true)) {
		$deleteQuery = 'DELETE FROM `' . MYSQL_DATABASE_PREFIX . 'groups` WHERE `group_name` = \'' . $groupName . '\'';
		if(mysql_query($deleteQuery)) {
			displayinfo("Group '$groupName' Deleted Successfully");
			return true;
		}
	}
	return false;
}


function isGroupEmpty($groupId) {
	$groupQuery = 'SELECT COUNT(`user_id`) FROM `' . MYSQL_DATABASE_PREFIX . 'usergroup` WHERE `group_id` = \'' . $groupId."'";
	$groupResult = mysql_query($groupQuery);
	$groupRow = mysql_fetch_row($groupResult);
	return ($groupRow[0] == 0);
}

function addUserToGroupName($groupName, $userId) {
	$groupRow = getGroupRow($groupName);
	if(!$groupRow) {
		return false;
	}
	$groupId = $groupRow['group_id'];

	$groupQuery = "SELECT `user_id` FROM `".MYSQL_DATABASE_PREFIX."usergroup` WHERE `group_id` = '$groupId' AND `user_id` = '$userId'";
	$groupResult = mysql_query($groupQuery);
	if($groupRow = mysql_fetch_assoc($groupResult)) {
		return true;
	}

	$groupQuery = "INSERT INTO `".MYSQL_DATABASE_PREFIX."usergroup`(`group_id`, `user_id`) VALUES('$groupId', '$userId')";
	mysql_query($groupQuery);
	return true;
}

function addUserToGroupId($groupId, $userId) {
	$groupQuery = "SELECT `user_id` FROM `".MYSQL_DATABASE_PREFIX."usergroup` WHERE `group_id` = '$groupId' AND `user_id` = '$userId'";
	$groupResult = mysql_query($groupQuery);
	if($groupRow = mysql_fetch_assoc($groupResult)) {
		displayerror("User already registered to the group.");
		return false;
	}

	$groupQuery = "INSERT INTO `".MYSQL_DATABASE_PREFIX."usergroup`(`group_id`, `user_id`) VALUES('$groupId', '$userId')";
	$groupResult = mysql_query($groupQuery);
	if(mysql_affected_rows() == 0) {
		return false;
	}
	return true;
}
function removeUserFromGroupId($groupId, $userId) {
	$groupQuery = "SELECT `user_id` FROM `".MYSQL_DATABASE_PREFIX."usergroup` WHERE `group_id` = '$groupId' AND `user_id` = '$userId'";
	$groupResult = mysql_query($groupQuery);
	if(mysql_num_fields($groupResult)==0) {
		return false;
	}
	$groupQuery = "DELETE FROM `".MYSQL_DATABASE_PREFIX."usergroup` WHERE `user_id`='$userId' and `group_id` = '$groupId'";
	$groupResult = mysql_query($groupQuery);
	if(mysql_affected_rows() > 0) {
		return true;
	}
	else
		return false;
}

function reevaluateGroupPriorities($modifiableGroups) {
	$groupIdList = array();
	$modifiableCount = count($modifiableGroups);
	for($i = 0; $i < $modifiableCount; $i++) {
		$groupIdList[] = $modifiableGroups[$i]['group_id'];
	}

	$modifiableGroups = array();
	if($modifiableCount) {
		$groupQuery = 'SELECT `group_id`, `group_name`, `group_description`, `group_priority` FROM `' . MYSQL_DATABASE_PREFIX . 'groups` WHERE `group_id` IN (' . join($groupIdList, ', ') . ') ORDER BY `group_priority` DESC';
		$groupResult = mysql_query($groupQuery) or die($groupQuery);
		while($groupRow = mysql_fetch_assoc($groupResult)) {
			$modifiableGroups[] = $groupRow;
		}
	}

	return $modifiableGroups;
}

function getGroupAssociatedWithForm($formId) {
	$groupQuery = "SELECT `group_id` FROM `" . MYSQL_DATABASE_PREFIX . "groups` WHERE `form_id` = '$formId'";
	$groupResult = mysql_query($groupQuery);
	if(mysql_num_rows($groupResult) != 0) {
		$groupRow = mysql_fetch_row($groupResult);
		return $groupRow[0];
	}

	return -1;
}


function getGroupsFromUserId($userId) {
	$groupQuery = 'SELECT `' . MYSQL_DATABASE_PREFIX . 'groups`.`group_id`, `group_name`, `group_description`, `form_id` FROM `' . MYSQL_DATABASE_PREFIX .
			'groups`, `'. MYSQL_DATABASE_PREFIX . 'usergroup` WHERE `user_id` = \'' . $userId . '\' AND `' .
			MYSQL_DATABASE_PREFIX . 'groups`.`group_id` = `' . MYSQL_DATABASE_PREFIX . 'usergroup`.`group_id`';
	$groupResult = mysql_query($groupQuery);
	if(!$groupResult) displayerror($groupQuery . '<br />' . mysql_error());

	$groupRows = array();
	while($groupRow = mysql_fetch_assoc($groupResult)) {
		$groupRows[] = $groupRow;
	}
	return $groupRows;
}

