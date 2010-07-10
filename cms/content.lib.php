<?php
/**
 * @package pragyan
 * @copyright (c) 2008 Pragyan Team
 * @license http://www.gnu.org/licenses/ GNU Public License
 * For more details, see README
 */

/**
 * Find page type, and module component id. See if the module is consistent with standards.
 * Give the action and module component id to module.
 */

 /**TODO: Make sure a newly created page or renamed page does not have a . in its name. -> clashes with .php, .jpg etc
  *
  *Actions which are taken care of from here only : login, logout, profile
  *Actions in "page" module : login, logout, profile, admin, groupadmin, settings, grant
  */
function getContent($pageId, $action, $userId, $permission, $recursed=0) {
	if($action=="login") {
		if($userId==0) {
			require_once("login.lib.php");
			$newUserId = login();
			if(is_numeric($newUserId))
				return getContent($pageId, "view", $newUserId, getPermissions($newUserId,$pageId,"view"), 0);
			else
				return $newUserId; ///<The login page
		} else
			displayinfo("You are logged in as ".getUserName($userId)."! Click <a href=\"./+logout\">here</a> to logout.");
		return getContent($pageId, "view", $userId, getPermissions($userId,$pageId,"view"), $recursed=0);
	}
	if($action=="profile") {
		if($userId != 0) {
			require_once("profile.lib.php");
	 		return profile($userId);
		} else
			displayinfo("You need to <a href=\"./+login\">login</a> to view your profile.!");
	}
	if($action=="logout") {
		if($userId!=0) {
			$newUserId=resetAuth();
			displayinfo("You have been logged out!");
			return getContent($pageId, "view", $newUserId, getPermissions($newUserId,$pageId,"view"), 0);
		} else
			displayinfo("You need to <a href=\"./+login\">login</a> first to logout!");
	}
	if($action=="search") {
		require_once("search.lib.php");
		$ret = getSearchBox();
		if(isset($_POST['query'])) 
			$ret .= getSearchResultString($_POST['query']);
		elseif(isset($_GET['query'])) 
			$ret .= getSearchResultString($_GET['query']);
	
		return $ret;
	}
	if (isset($_GET['subaction']) && $_GET['subaction'] == 'getchildren') {
		if (isset($_GET['parentpath'])) {
			global $urlRequestRoot;
			require_once('menu.lib.php');
			$pidarr = Array();
			parseUrlReal(escape($_GET['parentpath']), $pidarr);
			$pid = $pidarr[count($pidarr) - 1];
			$children = getChildren($pid, $userId);
			if ($pid == 0)
				$children[] = array(5, 'links/accommodation', 'Accommodation');
			$response = array();
			$response['path'] = escape($_GET['parentpath']);
			$response['items'] = array();
			foreach ($children as $child)
				$response['items'][] = array($urlRequestRoot . '/home' . escape($_GET['parentpath']) . $child[1], $child[2]);
			echo json_encode($response);
			exit();
		}
	}

	if($permission!=true) {
		if($userId==0) $suggestion = "(Try <a href=\"./+login\">logging in?</a>)";
		else $suggestion = "";
		displayerror("You do not have the permissions to view this page. $suggestion<br /><input type=\"button\" onclick=\"history.go(-1)\" value=\"Go back\" />");
		return '';
	}

	if($action=="admin") {
		require_once("admin.lib.php");
		return admin($pageId,$userId);
	}
	///default actions also to be defined here (and not outside)
	/// Coz work to be done after these actions do involve the page

	$pagetype_query = "SELECT page_module, page_modulecomponentid FROM ".MYSQL_DATABASE_PREFIX."pages WHERE page_id=".escape($pageId);
	$pagetype_result = mysql_query($pagetype_query);
	$pagetype_values = mysql_fetch_assoc($pagetype_result);
	if(!$pagetype_values) {
		displayerror("The requested page does not exist.");
		return "";
	}
	$moduleType = $pagetype_values['page_module'];
	$moduleComponentId = $pagetype_values['page_modulecomponentid'];
	if($action=="settings") {///<done here because we needed to check if the page exists for sure.
		require_once("pagesettings.lib.php");
		return pagesettings($pageId,$userId);
	}
	if($recursed==0) {
		$pagetypeupdate_query = "UPDATE ".MYSQL_DATABASE_PREFIX."pages SET page_lastaccesstime=NOW() WHERE page_id=".escape($pageId);
		$pagetypeupdate_result = mysql_query($pagetypeupdate_query);
		if(!$pagetypeupdate_result)
			return '<div class="cms-error">Error No. 563 - An error has occured. Contact the site administators.</div>';
	}
	if($moduleType=="link")
		return getContent($moduleComponentId,$action,$userId,true,1);
	if($action=="grant") {
		return grantPermissions($userId, $pageId);
	}
	if($moduleType=="menu")
		return getContent(getParentPage($pageId),$action,$userId,true,1);
	if($moduleType=="external") {
		$query = "SELECT `page_extlink` FROM `".MYSQL_DATABASE_PREFIX."external` WHERE `page_modulecomponentid` =
					(SELECT `page_modulecomponentid` FROM `".MYSQL_DATABASE_PREFIX."pages` WHERE `page_id`= ".escape($pageId).")";
		$result = mysql_query($query);
		$values = mysql_fetch_array($result);
		$link=$values[0];
		header("Location: $link");
	}
	global $sourceFolder;
	global $moduleFolder;
	require_once($sourceFolder."/".$moduleFolder."/".$moduleType.".lib.php");
	$page = new $moduleType();
	if(!($page instanceof module)){
		displayerror("The module \"$moduleType\" does not implement the inteface module</div>");
		return "";
	}
	
	$createperms_query = " SELECT * FROM ".MYSQL_DATABASE_PREFIX."permissionlist where perm_action = 'create' AND page_module = '".$moduleType."'";
	$createperms_result = mysql_query($createperms_query);
	if(mysql_num_rows($createperms_result)<1) {
		displayerror("The action \"create\" does not exist in the module \"$moduleType\"</div>");
		return "";
	}

	$availableperms_query = "SELECT * FROM ".MYSQL_DATABASE_PREFIX."permissionlist where perm_action != 'create' AND page_module = '".$moduleType."'";
	$availableperms_result = mysql_query($availableperms_query);
	$permlist = array();
	while ($value=mysql_fetch_assoc($availableperms_result))	{
		array_push($permlist,$value['perm_action']);
	}
	array_push($permlist,"view");
	$class_methods = get_class_methods($moduleType);
	foreach($permlist as $perm) {
		if(!in_array("action".ucfirst($perm),$class_methods))
		{
			displayerror("The action \"$perm\" does not exist in the module \"$moduleType\"</div>");
			return "";
		}
	}
	
	if($action=="pdf")
	{
		global $TITLE;
		global $sourceFolder;
		require_once("$sourceFolder/modules/pdf/html2fpdf.php");
		$pdf=new HTML2FPDF();
		$pdf->setModuleComponentId($moduleComponentId);
		$pdf->AddPage();
		$pdf->WriteHTML($page->getHtml($userId,$moduleComponentId,"view"));
		$filePath = $sourceFolder . "/uploads/temp/" . $TITLE . ".pdf";
		while(file_exists($filePath))
			$filePath = $sourceFolder . "/uploads/temp/" . $TITLE."-".rand() . ".pdf";
		$pdf->Output($filePath);
		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Cache-Control: private",false); 
		header("Content-Type: application/pdf");
		header("Content-Disposition: attachment; filename=\"".basename($filePath)."\";" );
		header("Content-Transfer-Encoding: binary");
		header("Content-Length: ".filesize($filePath));
		@readfile("$filePath");
		unlink($filePath);
	}	
	return $page->getHtml($userId, $moduleComponentId, $action);
}

/**
 * To get title bar text
 */
function getTitle($pageId,$action, &$heading) {
	if($action=="login" || $action == "logout") {
		$heading = ucfirst($action);
		return true;
	}

	$pagetitle_query = "SELECT `page_title`, `page_module`, `page_modulecomponentid`, `page_displaypageheading` FROM `".MYSQL_DATABASE_PREFIX."pages` WHERE `page_id`=".$pageId;
	$pagetitle_result = mysql_query($pagetitle_query);
	if (!$pagetitle_result)
		return false;
	$pagetitle_values = mysql_fetch_assoc($pagetitle_result);

	if ($pagetitle_values['page_displaypageheading'] == 0)
		return false;
	//if($pagetitle_values['page_module']=="link")	return getTitle($pagetitle_values['page_modulecomponentid'],$action);
	//A link has its own page title, page menurank, display menubar property
	if ($action=="grant")	$heading = $pagetitle_values['page_title']." - Grant Permissions";
	if ($action=="settings")	$heading = $pagetitle_values['page_title']." - Page Settings";
	else $heading = $pagetitle_values['page_title'];
	return true;
}

/**
 * The interface to be followed by each module. In addition to this, each module needs to have
 * a function with the name actionAction for each action. (eg: actionView, actionEdit named functions)
 */
interface module {
	public function getHtml($userId, $moduleComponentId, $action);
	public function deleteModule($moduleComponentId);
	public function copyModule($moduleComponentId);
	public function createModule(&$moduleComponentId);
}

interface fileuploadable {
	/**
	 * Should return true in case file viewing allowed, false if not allowed
	 */
	public static function getFileAccessPermission($pageId,$moduleComponentId,$userId,$fileName);
}

