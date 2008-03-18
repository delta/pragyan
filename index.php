<?php
$sourceFolder="cms";///<Folder containing all library files
$moduleFolder = "modules"; ///<Folder containing all the modules
$templateFolder = "templates"; ///<Folder containing all the modules
$uploadFolder = "uploads";
$debugSet = "off";///<Will get overridden by the config value
$PAGELASTUPDATED=""; ///<Can be used to update the last updated time
$ERRORSTRING; ///<Defined here. Will get appended by displayerror() in common.lib.php
$INFOSTRING; ///<Defined here. Will get appended by displayinfo() in common.lib.php
$WARNINGSTRING; ///<Defined here. Will get appended by displaywarning() in common.lib.php
$STARTSCRIPTS; ///<Will contain a string containing all that has to be executed on window load
$urlRequestRoot = substr($_SERVER['SCRIPT_NAME'], 0, strrpos($_SERVER['SCRIPT_NAME'], '/')); ///<Root of the request - that path to cms base
$TEMPLATEBROWSERPATH; ///<Full path to template folder as seen from the browser (defined in template.lib.php)
$TEMPLATECODEPATH; ///<Full path to template folder as seen by httpd while parsing (defined in template.lib.php)
$DEBUGINFO = "";
$cookieSupported = false;

require_once($sourceFolder."/config.inc.php");
require_once($sourceFolder."/common.lib.php");

$dbase; ///< Defined here to set its access as global to the project
connect(); ///< To connect to server

require_once($sourceFolder."/authenticate.lib.php");
$cookieSupported = checkCookieSupport();
if($cookieSupported==true)	session_start();
$userId=firstTimeGetUserId();
$pageFullPath = strtolower($_GET['page']);///<The page requested by the user
$action = strtolower($_GET['action']);
if($action == "")	$action = "view";

require_once($sourceFolder."/parseurl.lib.php");
$pageId = parseUrlReal($pageFullPath, $pageIdArray);

require_once($sourceFolder."/permission.lib.php");
logInfo (getUserEmail($userId),$userId, $pageId, $pageFullPath, getPageModule($pageId), $action, $_SERVER['REMOTE_ADDR']);

if ($pageId === false) { ///<Following also used in download.lib.php
	header("http/1.0 404 Not Found" );
	echo "<html><head><title>404 Not Found</title></head><body><h1>Not Found</h1>" .
		 "<p>The requested URL ".$_SERVER['SCRIPT_URL']." was not found on this server.</p><hr>" .
		 "<address>Apache/2.2.4 (Fedora) Server at localhost Port 80</address></body></html>";
	exit;
}

if(isset($_GET['fileget'])) {
	require_once($sourceFolder."/download.lib.php");
	download($pageId,$userId,$_GET['fileget']);
	exit();
}

$permission = getPermissions($userId, $pageId, $action);

require_once($sourceFolder."/content.lib.php");
$CONTENT = getContent($pageId, $action, $userId, $permission);
$TITLE = getTitle($pageId,$action)==""?CMS_TITLE:CMS_TITLE." - ".getTitle($pageId,$action);

require_once($sourceFolder."/breadcrumbs.lib.php");
$BREADCRUMB = breadcrumbs($pageIdArray,">>");

require_once($sourceFolder."/menu.lib.php");
$MENUBAR = getMenu($userId, $pageIdArray);

require_once($sourceFolder."/actionbar.lib.php");
$ACTIONBARPAGE = getActionbarPage($userId, $pageId);
$ACTIONBARMODULE = getActionbarModule($userId, $pageId);

require_once($sourceFolder."/registration.lib.php");

if($debugSet == "on") {
	$DEBUGINFO .= "Page Full text path : ".$pageFullPath."<br />\n";
	$DEBUGINFO .= "UID : ".getUserId()."<br />\n";
	$DEBUGINFO .= "GIDS : ".arraytostring(getGroupIds($userId))."<br />\n";
	$DEBUGINFO .= "Action : ".$action."<br />\n";
	$DEBUGINFO .= "Get Vars : ".arraytostring($_GET)."<br />\n";
	$DEBUGINFO .= "Page Id : ".$pageId."<br />\n";
	$DEBUGINFO .= "Page id path : ".arraytostring($pageIdArray)."\n<br />";
//	$DEBUGINFO .= "Title : ".$TITLE."\n<br />";
//	$DEBUGINFO .= "Breadcrumbs : ".$BREADCRUMB."\n";
//	$DEBUGINFO .= "Menu Bar : ".$MENUBAR."\n<br />";
//	$DEBUGINFO .= "Action Bar for page : ".$ACTIONBARPAGE."\n<br />";
//	$DEBUGINFO .= "Action Bar for module : ".$ACTIONBARMODULE."\n<br />";
//	$DEBUGINFO .= "SERVER info : ".arraytostring($_SERVER)."\n<br />";
	$DEBUGINFO .= "POST info : ".arraytostring($_POST)."\n<br />";
	$DEBUGINFO .= "FILES info : ".arraytostring($_FILES)."\n<br />";
//	$DEBUGINFO .= "SESSION info : ".arraytostring($_SESSION)."\n<br />";

	if($DEBUGINFO!="")	displayinfo($DEBUGINFO);
}

	setcookie("cookie_support", "enabled", 0, "/"); ///<used to check in subsequent requests if cookies are supported or not
	require_once($sourceFolder."/template.lib.php");
	templateReplace($TITLE,$MENUBAR,$ACTIONBARMODULE,$ACTIONBARPAGE,$BREADCRUMB,$MENUBAR,$CONTENT,$DEBUGINFO,$ERRORSTRING,$WARNINGSTRING,$INFOSTRING,$STARTSCRIPTS);

	//include ("./$sourceFolder/$templateFolder/index.php");
/*	require_once("$sourceFolder/FastTemplateClass.php3");
	$tpl = new FastTemplate("./$sourceFolder/$templateFolder/".TEMPLATE);
	$tpl->define(array (
		bodyFileName => "index.php"
	));

	if(getEffectivePageModule($pageId)=="article" && ($action=="view" || $action=="login"||$action=="logout"))
		$sidebarcontent=file_get_contents("$sourceFolder/$templateFolder/common/sidebar.php");
	else $sidebarcontent='';

	$pageStyle="";
	if($menubar!="")	$pageStyle=" <link rel=\"stylesheet\" href=\"PAGEPATH/../common/style-leftbar.css\" />";
	if($sidebarcontent!="")
		if($pageStyle=="") $pageStyle=" <link rel=\"stylesheet\" href=\"PAGEPATH/../common/style-rightbar.css\" />";
		else	$pageStyle=" <link rel=\"stylesheet\" href=\"PAGEPATH/../common/style-bothbars.css\" />";
	$replacementArray = array (
		PAGESTYLE => $pageStyle,
		PAGEPATH => "$urlRequestRoot/$sourceFolder/$templateFolder/".TEMPLATE,
		TITLE => $title,
		MENU => $menubar,
		BREADCRUMB => $breadcrumb,
		ACTIONBARPAGE => $actionbarPage,
		ACTIONBARMODULE => $actionbarModule,
		ERRORSTRING => $errorString,
		INFOSTRING => $infoString,
		WARNINGSTRING => $warningString,
		CONTENTOFPAGE => &$content,
		SIDEBAR => &$sidebarcontent

	);
	$tpl->assign($replacementArray);
	$tpl->parse(PAGEVARNAME, "bodyFileName");
	$tpl->FastPrint("PAGEVARNAME");
*/
disconnect();
exit();

/** ALGO:

authenticate.lib.php -> Find out who requested it
	output: one int -> uid

uil.lib.php -> Find out the page id and action requested
	input:	url
 	output : pageid, action, actionparameters (variables passed as parameters for the action)

permission.lib.php -> Find out if he has the permission of the particular action on that page
	input : pageid, uid, action
	output : true, false

content.lib.php -> Generate the output of the page -> has nothing to do with the uid.
	The only inputs will be -> permission output, pageid, action, parameters for action (might include uid)
	outputs : 	javascript to be run on page load
 				page content
				bread crumbs -> breadcrumbs.lib.php

breadcrumbs.lib.php
	input: pageid
	output: div containing breadcrumbs

header.lib.php
	input : pageid
	output: header div

menu.lib.php : input : uid, pageid
	this in turn will use
		menuitems.lib.php : input : uid, pageid : output: pageid's children
	output: divs for the menu

right sidebar will be generated through template only

Types of outputs :
 Both menu bar, page content, along with template.
 Menu bar content
 Page content. (that comes from modules)


Constants :
uids : unauthenticated -> 0, loggedin -> his own uid
gids : Groups available by default (to which permissions can be given) - unauthenticated users -> 0, logged in users -> 1
*/

/**
 ******************************************************************************
 * 										CODE DOCUMENTATION GUIDELINES
 ******************************************************************************
 * When writing code, make sure the documentation (comments, so to speak)
 * follows the guidelines given below. This helps in generating documentation
 * using tools like Doxygen. Note the slashes, the stars and anything else
 * typed here.
 */

/// GENERAL COMMENTS:
///
/// Start comments using /** rather than /*, or if you prefer the C style single
/// line comments, use three slashes on two consecutive lines, as in
///	/// instead of //
///
/// Comments, in general, must look like
/// /**
///  * Comment goes here (the * on this line is optional, but it looks good)
///  */
///
/// Or
///
/// ///
/// /// Comment goes here (two /// lines, see? At least two ///s are necessary to
///	///	to qualify them as documentation)

/// VARIABLES or DATA MEMBERS:
///
/// /**
///	* A brief description here. (< note the period (full stop))
///	* Mode detailed description goes here.
///	*/
/// private $myPrivateMember;
///
/// When it comes to variables or data members, you can also put documentation
/// after the declaration. Do:
///
/// private $myPrivateMember; ///< Description goes here (node the less than symbol)

/// FUNCTIONS:
///
/// /**
/// * A description of the function goes here. Give a description of
/// * each of the arguments that the function requires after this, followed
/// * by an indication of what the return values represent, as shown below:
/// * @param $param1 $param1 is the first parameter, and a description of the parameter goes here.
/// * @param $param2 The second parameter, and so on for as many parameters as the function takes
/// * @return Describe what the function returns here
/// */
/// function functionName($param1, $param2) {
///
/// }

?>