<?php
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
	}
	else {
		$actionbarPage["profile"]="Preferences";
		$actionbarPage["logout"]="Logout";
	}
	$actionbar="<div id=\"actionbarPage\">";
	foreach($actionbarPage as $action=>$actionname) {
		$actionbar.="<span class=\"actionbarPageItem\"><a href=\"./+$action\">$actionname</a></span>";
	}
	$actionbar.="</div>";
	return $actionbar;
}

function getActionbarModule($userId, $pageId) {
	$action_query = "SELECT perm_id, perm_action, perm_text FROM `".MYSQL_DATABASE_PREFIX."permissionlist` WHERE perm_action != 'create' AND page_module = '".getEffectivePageModule($pageId)."'";
	$action_result = mysql_query($action_query);
	while($action_row = mysql_fetch_assoc($action_result))
		if(getPermissions($userId, $pageId, $action_row['perm_action']))
			$actionbarPage[$action_row['perm_action']]=$action_row['perm_text'];
	$actionbar="<div id=\"actionbarModule\">";
	if(is_array($actionbarPage)>0)
	foreach($actionbarPage as $action=>$actionname) {
		$actionbar.="<span class=\"actionbarModuleItem\"><a href=\"./+$action\">$actionname</a></span>";
	}
	$actionbar.="</div>";
	return $actionbar;
}

?>