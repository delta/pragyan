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
		///Commented the requirement of login.lib.php because it is already included in /index.php
			//require_once("login.lib.php");
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
			global $openid_enabled;
			if($openid_enabled=='true')
				displaywarning("If you logged in via Open ID, make sure you also log out from your Open ID service provider's website. Until then your session in this website will remain active !");
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
			$response = array();
			$response['path'] = escape($_GET['parentpath']);
			$response['items'] = array();
			foreach ($children as $child)
				$response['items'][] = array($urlRequestRoot . '/home' . escape($_GET['parentpath']) . $child[1], $child[2]);
			//echo json_encode($response);
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

	$pagetype_query = "SELECT page_module, page_modulecomponentid FROM ".MYSQL_DATABASE_PREFIX."pages WHERE page_id='".escape($pageId)."'";
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
	if($action=="widgets")
	{
		return handleWidgetPageSettings($pageId);
	}
	if($recursed==0) {
		$pagetypeupdate_query = "UPDATE ".MYSQL_DATABASE_PREFIX."pages SET page_lastaccesstime=NOW() WHERE page_id='".escape($pageId)."'";
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
					(SELECT `page_modulecomponentid` FROM `".MYSQL_DATABASE_PREFIX."pages` WHERE `page_id`= '".escape($pageId)."')";
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

		if(isset($_GET['depth']))
		 $depth=$_GET['depth'];
		else $depth=0;
		
		if(!is_numeric($depth))
		{
			$depth=0;
		}

		global $TITLE;
		global $sourceFolder;
		require_once("$sourceFolder/modules/pdf/html2fpdf.php");
		$pdf=new HTML2FPDF();
		$pdf->setModuleComponentId($moduleComponentId);
		$pdf->AddPage();
		$pdf->WriteHTML($page->getHtml($userId,$moduleComponentId,"view"));
		
		$cp=array();
		$j=0;
		
		if($depth == -1)
		{
			$cp=child($pageId,$userId,$depth);
		
			if($cp[0][0])
				{
					for($i=0 ; $cp[$i][0] != NULL ; $i++)
					{
						require_once($sourceFolder."/".$moduleFolder."/".$cp[$i][2].".lib.php");						
						$page1 = new $cp[$i][2]();					
						$modCompId = $cp[$i][5];
						$pdf->setModuleComponentId($modCompId);
						$pdf->AddPage();
						$pdf->WriteHTML($page1->getHtml($userId,$modCompId,"view"));
					}
				}
		}
		
		else if ($depth>0)
		{
			$cp=child($pageId,$userId,$depth);
			--$depth;
			while($depth>0)
			{
				$count = count($cp);
				for($j; $j<$count; $j++)
				{
					$cp=array_merge((array)$cp,(array)child($cp[$j][0],$userId,$depth));
				}
				--$depth;
			}
		
			if($cp[0][0])
			{
				for($i=0 ; isset($cp[$i]) ; $i++)
				{
					require_once($sourceFolder."/".$moduleFolder."/".$cp[$i][2].".lib.php");						
						$page1 = new $cp[$i][2]();	
					$modCompId = $cp[$i][5];
					$pdf->setModuleComponentId($modCompId);
					$pdf->AddPage();
					$pdf->WriteHTML($page1->getHtml($userId,$modCompId,"view"));
				}
			}
						
		}
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
	global $allow_pageheadings_intitle;
	if($allow_pageheadings_intitle == 0)
		return false;
		
	$pagetitle_query = "SELECT `page_title`, `page_module`, `page_modulecomponentid`, `page_displaypageheading` FROM `".MYSQL_DATABASE_PREFIX."pages` WHERE `page_id`='".$pageId."'";
	$pagetitle_result = mysql_query($pagetitle_query);
	if (!$pagetitle_result)
		return false;
	$pagetitle_values = mysql_fetch_assoc($pagetitle_result);

	if ($pagetitle_values['page_displaypageheading'] == 0)
		return false;
	//if($pagetitle_values['page_module']=="link")	return getTitle($pagetitle_values['page_modulecomponentid'],$action);
	//A link has its own page title, page menurank, display menubar property
	if ($action=="grant")	$heading = $pagetitle_values['page_title']." - Grant Permissions";
	else if ($action=="settings")	$heading = $pagetitle_values['page_title']." - Page Settings";
	else if ($action=="admin")	$heading = $pagetitle_values['page_title']." - Website Administration";
	else if ($action=="widget")	$heading = $pagetitle_values['page_title']." - Page Widgets";
	else if ($action=="profile")	$heading = $pagetitle_values['page_title']." - User Profile";
	else $heading = $pagetitle_values['page_title'];
	return true;
}

function child($pageId, $userId,$depth) {
	$pageId=escape($pageId);
	if($depth < 0)
	{
	$childrenQuery = 'SELECT `page_id`, `page_name`, `page_title`, `page_module`, `page_modulecomponentid`, `page_displayinmenu`, `page_image` , `page_displayicon` FROM `' . MYSQL_DATABASE_PREFIX . 'pages` WHERE `page_id` != \'' . $pageId . '\' AND `page_displayinmenu` = 1 ORDER BY `page_menurank`';

	}
	else
	{
	$childrenQuery = 'SELECT `page_id`, `page_name`, `page_title`, `page_module`, `page_modulecomponentid`, `page_displayinmenu`, `page_image` , `page_displayicon` FROM `' . MYSQL_DATABASE_PREFIX . 'pages` WHERE `page_parentid` = \'' . $pageId . '\' AND `page_id` != \'' . $pageId . '\' AND `page_displayinmenu` = 1 ORDER BY `page_menurank`';
	}
	
	
	$childrenResult = mysql_query($childrenQuery);
	$children = array();
	while ($childrenRow = mysql_fetch_assoc($childrenResult))
		if ($childrenRow['page_displayinmenu'] == true && getPermissions($userId, $childrenRow['page_id'], 'view', $childrenRow['page_module']) == true)
			$children[] = array($childrenRow['page_id'], $childrenRow['page_name'], $childrenRow['page_module'], $childrenRow['page_image'],$childrenRow['page_displayicon'],$childrenRow['page_modulecomponentid']);
			
		
	return $children;
}

/**
 * The interface to be followed by each module. In addition to this, each module needs to have
 * a function with the name actionAction for each action. (eg: actionView, actionEdit named functions)
 */
interface module {
	public function getHtml($userId, $moduleComponentId, $action);
	public function deleteModule($moduleComponentId);
	public function copyModule($moduleComponentId,$newModuleComponentId);
	public function createModule($moduleComponentId);
}

interface fileuploadable {
	/**
	 * Should return true in case file viewing allowed, false if not allowed
	 */
	public static function getFileAccessPermission($pageId,$moduleComponentId,$userId,$fileName);
}

