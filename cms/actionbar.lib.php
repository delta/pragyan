<?php
/**
 * @package pragyan
 * @copyright (c) 2008 Pragyan Team
 * @license http://www.gnu.org/licenses/ GNU Public License
 * For more details, see README
 */
/**
 * Generates page level actions
 * Admin(+admin), Settings(+settings), Permissions(+grant), 
 * Login(+login), Profile(+profile), and Logout(+logout)
 */
function getActionbarPage($userId, $pageId) {
	$action_query = "SELECT perm_id, perm_action, perm_text FROM `".MYSQL_DATABASE_PREFIX."permissionlist` WHERE page_module = 'page'";
	$action_result = mysql_query($action_query);
	$actionbarPage=array();
	while($action_row = mysql_fetch_assoc($action_result)) {
		if(getPermissions($userId, $pageId, $action_row['perm_action']))
			$actionbarPage[$action_row['perm_action']]=$action_row['perm_text'];
	}
	if($userId==0) {
		$actionbarPage["login"]="Login";
		$actionbarPage["login&subaction=register"]="Register";
	}
	else {
		$actionbarPage["profile"]="Profile";
		$actionbarPage["logout"]="Logout";
	}
	$actionbar="<div id=\"cms-actionbarPage\">";
	foreach($actionbarPage as $action=>$actionname) {
		$actionbar.="<span class=\"cms-actionbarPageItem\"><a class=\"robots-nofollow\" rel=\"nofollow\" href=\"./+$action\">$actionname</a></span>";
	}
	$actionbar.="</div>";
	return $actionbar;
}
/**
 * Generates module specific actions 
 */
function getActionbarModule($userId, $pageId) {
	$action_query = "SELECT perm_id, perm_action, perm_text FROM `".MYSQL_DATABASE_PREFIX."permissionlist` WHERE perm_action != 'create' AND page_module = '".getEffectivePageModule($pageId)."'";
	$action_result = mysql_query($action_query);
	$actionbarPage = array();
	while($action_row = mysql_fetch_assoc($action_result))
		if(getPermissions($userId, $pageId, $action_row['perm_action']))
			$actionbarPage[$action_row['perm_action']]=$action_row['perm_text'];
	$actionbar="<div id=\"cms-actionbarModule\">";
	if(is_array($actionbarPage)>0)
	foreach($actionbarPage as $action=>$actionname) {
		$actionbar.="<span class=\"cms-actionbarModuleItem\"><a class=\"robots-nofollow\" rel=\"nofollow\" href=\"./+$action\">$actionname</a></span>";
	}
	$actionbar.="</div>";
	return $actionbar;
}

