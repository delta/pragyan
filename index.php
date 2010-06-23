<?php
/**
 * @package pragyan
 * @copyright (c) 2008 Pragyan Team
 * @license http://www.gnu.org/licenses/ GNU Public License
 * For more details, see README
 */

$cmsFolder="cms";///<Folder containing all library files
$moduleFolder = "modules"; ///<Folder containing all the modules
$templateFolder = "templates"; ///<Folder containing all the modules
$uploadFolder = "uploads";
$debugSet = "off";///<Will get overridden by the config value
$sourceFolder = substr($_SERVER['SCRIPT_FILENAME'], 0, strrpos($_SERVER['SCRIPT_FILENAME'], '/'))."/".$cmsFolder;
$PAGELASTUPDATED=""; ///<Can be used to update the last updated time
$ERRORSTRING; ///<Defined here. Will get appended by displayerror() in common.lib.php
$INFOSTRING; ///<Defined here. Will get appended by displayinfo() in common.lib.php
$WARNINGSTRING; ///<Defined here. Will get appended by displaywarning() in common.lib.php
$STARTSCRIPTS; ///<Will contain a string containing all that has to be executed on window load
$urlRequestRoot = substr($_SERVER['SCRIPT_NAME'], 0, strrpos($_SERVER['SCRIPT_NAME'], '/')); ///<Root of the request - that path to cms base
$TEMPLATEBROWSERPATH; ///<Full path to template folder as seen from the browser (defined in template.lib.php)
$TEMPLATECODEPATH; ///<Full path to template folder as seen by httpd while parsing (defined in template.lib.php)
$SITEDESCRIPTION;
$SITEKEYWORDS;
$DEBUGINFO = "";
$cookieSupported = false;
$ICONS;
$ICONS_SRC;


require_once($sourceFolder."/config.inc.php");
require_once($sourceFolder."/common.lib.php");
require_once($sourceFolder."/icons.lib.php");

if(!defined("ADMIN_USERID") )
{
	echo "Welcome to Pragyan CMS v3.0. <a href='./INSTALL/'>Click Here</a> to goto installation page.<br/><br/>
	<b>NOTE:</b>If you're not using the <a href='http://sourceforge.net/projects/pragyan'>official package</a> of the Pragyan CMS or you're installing for the second time, then please make sure that the 'RewriteEngine' property is set to 'Off' in the .htaccess file present in the root folder of Pragyan for the above link to work correctly.";
	exit();
}

$dbase; ///< Defined here to set its access as global to the project
connect(); ///< To connect to server

require_once($sourceFolder."/authenticate.lib.php");
$cookieSupported = checkCookieSupport();
if($cookieSupported==true)	session_start();
$userId=firstTimeGetUserId();
if(isset($_GET['page']))
	$pageFullPath = strtolower($_GET['page']);///<The page requested by the user
else $pageFullPath = "home";

if(isset($_GET['action']))
	$action = strtolower(escape($_GET['action']));
else	$action = "view";

if ($action == 'keepalive')
	die("OK: " . rand());

checkInstallation($pageFullPath,$action);

$globals=getGlobalSettings();
foreach($globals as $var=>$val) 
	$$var=$val;

define("CMS_TITLE", $cms_title);
define("DEF_TEMPLATE",$default_template);
define("UPLOAD_SIZE_LIMIT", $upload_limit);
define("SEND_MAIL_ON_REGISTRATION",($default_mail_verify==0)?false:true);
define("CMS_EMAIL",$cms_email);
define("ACTIVATE_USER_ON_REG",$default_user_activate);
$SITEDESCRIPTION=$cms_desc;
$SITEKEYWORDS=$cms_keywords;


require_once($sourceFolder."/parseurl.lib.php");
$pageId = parseUrlReal($pageFullPath, $pageIdArray);

require_once($sourceFolder."/permission.lib.php");
logInfo (getUserEmail($userId),$userId, $pageId, $pageFullPath, getPageModule($pageId), $action, $_SERVER['REMOTE_ADDR']);

if ($pageId === false) { ///<Following also used in download.lib.php
	header("http/1.0 404 Not Found" );
	echo "<html><head><title>404 Not Found</title></head><body><h1>Not Found</h1>" .
		 "<p>The requested URL ".$_SERVER['SCRIPT_URL']." was not found on this server.</p><hr>" .
		 "$_SERVER[SERVER_SIGNATURE]</body></html>";
	exit();
}

if(isset($_GET['fileget'])) {
	require_once($sourceFolder."/download.lib.php");
	download($pageId,$userId,$_GET['fileget']);
	exit();
}

$permission = getPermissions($userId, $pageId, $action);
require_once($sourceFolder."/template.lib.php");
define("TEMPLATE", getPageTemplate($pageId));
require_once($sourceFolder."/content.lib.php");
if (getTitle($pageId, $action, $TITLE))
	$TITLE = CMS_TITLE . " - $TITLE";
else
	$TITLE = CMS_TITLE;
$CONTENT = getContent($pageId, $action, $userId, $permission);

require_once($sourceFolder."/inheritedinfo.lib.php");
$INHERITEDINFO = inheritedinfo($pageIdArray);

require_once($sourceFolder."/menu.lib.php");
require_once($sourceFolder."/breadcrumbs.lib.php");
$BREADCRUMB = breadcrumbs($pageIdArray,"&nbsp;»&nbsp;");

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
	print_r($_SERVER);

	if($DEBUGINFO!="")	displayinfo($DEBUGINFO);
}

	setcookie("cookie_support", "enabled", 0, "/"); ///<used to check in subsequent requests if cookies are supported or not
	
	
	templateReplace($TITLE,$MENUBAR,$ACTIONBARMODULE,$ACTIONBARPAGE,$BREADCRUMB,$INHERITEDINFO,$CONTENT,$DEBUGINFO,$ERRORSTRING,$WARNINGSTRING,$INFOSTRING,$STARTSCRIPTS);

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


