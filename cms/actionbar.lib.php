<?php
/**
 * @package pragyan
 * @copyright (c) 2010 Pragyan Team
 * @license http://www.gnu.org/licenses/ GNU Public License
 * For more details, see README
 */
if(!defined('__PRAGYAN_CMS'))
{ 
	header($_SERVER['SERVER_PROTOCOL'].' 403 Forbidden');
	echo "<h1>403 Forbidden<h1><h4>You are not authorized to access the page.</h4>";
	echo '<hr/>'.$_SERVER['SERVER_SIGNATURE'];
	exit(1);
}

/**
 * Generates page level actions
 * Admin(+admin), Settings(+settings), Permissions(+grant), 
 * Login(+login), Profile(+profile), and Logout(+logout)
 *
 * @param $userId The user for whom the list of permitted actions must be computed.
 * @param $pageId The page on which the permissible action for the user is computed
 *
 * @return $actionbar The list of permitted actions for the 'user' of 'page'. 
 */
function getActionbarPage($userId, $pageId) {

	$action_query = "SELECT perm_id, perm_action, perm_text FROM `".MYSQL_DATABASE_PREFIX."permissionlist` WHERE page_module = 'page'";
	$action_result = mysql_query($action_query);
	$allow_login_query = "SELECT `value` FROM `".MYSQL_DATABASE_PREFIX."global` WHERE `attribute` = 'allow_login'";
	$allow_login_result = mysql_query($allow_login_query);
	$allow_login_result = mysql_fetch_array($allow_login_result);
	$actionbarPage=array();
	while($action_row = mysql_fetch_assoc($action_result)) {
		if(getPermissions($userId, $pageId, $action_row['perm_action']))
			$actionbarPage[$action_row['perm_action']]=$action_row['perm_text'];
	}
	if($userId==0) {
	if($allow_login_result[0]) {
		$actionbarPage["login"]="Login";
		$actionbarPage["login&subaction=register"]="Register";
		}
	}
	else {
			$actionbarPage["logout"]="Logout";
	///profile has been changed to display the username.
		$actionbarPage["profile"]=getUserName($userId);
	}
	$actionbarPage["search"]="Search";
	$actionbar="<div id=\"cms-actionbarPage\">";
	
	foreach($actionbarPage as $action=>$actionname) {
	global $templateFolder;
	global $cmsFolder;
	$hostURLL = hostURL();
	$tuname=getUserName($userId);
	global $onlineSiteUrl;
	if($action == "profile")
		$actionbar.="<span class=\"cms-actionbarPageItem\"><a class=\"robots-nofollow cms-action{$action}\" rel=\"nofollow\" href=\"{$onlineSiteUrl}/../user:{$tuname}\"><img src=\"{$hostURLL}/{$cmsFolder}/{$templateFolder}/common/images/usericon.png\" \/> $actionname</a></span>\n";
	else if($action == "pdf")
		$actionbar.="<span class=\"cms-actionbarPageItem\"><a id=\"a\" onclick=\"javascript:var x=prompt('Enter Depth (0 for current page only, -1 for all child pages)','0');if(x)document.getElementById('a').href = document.getElementById('a').href + '&depth=' + x; else return false;\" class=\"robots-nofollow cms-action{$action}\" rel=\"nofollow\" href=\"./+$action\">$actionname</a></span>\n";
	else
		$actionbar.="<span class=\"cms-actionbarPageItem\"><a class=\"robots-nofollow cms-action{$action}\" rel=\"nofollow\" href=\"./+$action\">$actionname</a></span>\n";
	}
	$actionbar.="</div>";
	return $actionbar;
}

/**
 * Generates module specific actions 
 * 
 * @param $userId The user for whom the list of permitted actions must be computed.
 * @param $pageId The page on which the permissible action for the user is computed
 *
 * @return $actionbar The list of permitted module specific actions for the 'user' of 'page'.
 */
function getActionbarModule($userId, $pageId) {
	$action_query = "SELECT perm_id, perm_action, perm_text FROM `".MYSQL_DATABASE_PREFIX."permissionlist` WHERE perm_action != 'create' AND page_module = '".getEffectivePageModule($pageId)."'";
	$action_result = mysql_query($action_query);
	$allow_login_query = "SELECT `value` FROM `".MYSQL_DATABASE_PREFIX."global` WHERE `attribute` = 'allow_login'";
	$allow_login_result = mysql_query($allow_login_query);
	$allow_login_result = mysql_fetch_array($allow_login_result);
	$actionbarPage = array();
	while($action_row = mysql_fetch_assoc($action_result))
		if(getPermissions($userId, $pageId, $action_row['perm_action']))
			$actionbarPage[$action_row['perm_action']]=$action_row['perm_text'];
	$actionbar="<div id=\"cms-actionbarModule\">";
	if(is_array($actionbarPage)>0)
	foreach($actionbarPage as $action=>$actionname) {
		if((!$allow_login_result[0])&&($actionname=="View")&&!($userId))
			continue;		
		$actionbar.="<span class=\"cms-actionbarModuleItem\"><a class=\"robots-nofollow\" rel=\"nofollow\" href=\"./+$action\">$actionname</a></span>\n";
	}
	$actionbar.="</div>";
	return $actionbar;
}

