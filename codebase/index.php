<?php
/**
 * @package pragyan
 * @author Abhishek Shrivastava, Abhilash R, Sahil Ahuja, Anshu Prateek, Ankit Srivastav, Chakradar Raju,
 * @brief Pragyan CMS v3.0 Project
 * @copyright (c) 2010 Pragyan Team
 * @license http://www.gnu.org/licenses/ GNU Public License
 * For more details, see README
 * @mainpage Pragyan CMS
 * @section intro_sec Introduction
 * Pragyan CMS is a simple and fast multiuser CMS(Content Management System) to organize collaborative web-content. 
 * This CMS allows very fine user & group permissions and generating pages like articles, forms, quizzes, forums, gallery, etc.
 * The internal search engine is powered by Sphider and it comes with many third-party plugins like PDF, Google Maps, Latex, etc.
 * 
 * @section credits License, Credits and other details
 * Please see README.
 * 
 * For more details, contact Abhishek Shrivastava i.abhi27 [at] gmail [dot] com
 */

$cmsFolder="cms";///Folder containing all library files
$moduleFolder = "modules"; ///Folder containing all the modules
$templateFolder = "templates"; ///Folder containing all the modules
$uploadFolder = "uploads";
$debugSet = "off";///Will get overridden by the config value
$sourceFolder = substr($_SERVER['SCRIPT_FILENAME'], 0, strrpos($_SERVER['SCRIPT_FILENAME'], '/'))."/".$cmsFolder;
$PAGELASTUPDATED=""; ///Can be used to update the last updated time
$ERRORSTRING; ///Defined here. Will get appended by displayerror() in common.lib.php
$INFOSTRING; ///Defined here. Will get appended by displayinfo() in common.lib.php
$WARNINGSTRING; ///Defined here. Will get appended by displaywarning() in common.lib.php
$STARTSCRIPTS; ///Will contain a string containing all that has to be executed on window load
$urlRequestRoot = substr($_SERVER['SCRIPT_NAME'], 0, strrpos($_SERVER['SCRIPT_NAME'], '/')); ///Root of the request - that path to cms base
$TEMPLATEBROWSERPATH; ///Full path to template folder as seen from the browser (defined in template.lib.php)
$TEMPLATECODEPATH; ///Full path to template folder as seen by httpd while parsing (defined in template.lib.php)
$SITEDESCRIPTION;
$SITEKEYWORDS;
$DEBUGINFO = "";
$cookieSupported = false;
$ICONS;
$ICONS_SRC;
$onlineSiteUrl = "http://" . $_SERVER['HTTP_HOST'] . substr($_SERVER['SCRIPT_NAME'],0,stripos($_SERVER['SCRIPT_NAME'],"index.php")) . "home";
// For example, if hosted on pragyan.org/10, $onlineSiteUrl = http://pragyan.org/10/home

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

if ($action == 'keepalive') //just to check if server is alive, an alternative of Ping
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
$FOOTER=$cms_footer;


require_once($sourceFolder."/parseurl.lib.php");
require_once($sourceFolder."/template.lib.php");
require_once($sourceFolder."/menu.lib.php");
require_once($sourceFolder."/breadcrumbs.lib.php");
require_once($sourceFolder."/permission.lib.php");
require_once($sourceFolder."/content.lib.php");
require_once($sourceFolder."/inheritedinfo.lib.php");
require_once($sourceFolder."/actionbar.lib.php");
require_once($sourceFolder."/registration.lib.php");


$pageId = parseUrlReal($pageFullPath, $pageIdArray);

if ($pageId === false) { 
	define("TEMPLATE", getPageTemplate(0));
	$pageId = parseUrlReal("home", $pageIdArray);
	$TITLE = CMS_TITLE;
	$MENUBAR = '';
	$CONTENT = "The requested URL was not found on this server.<br />$_SERVER[SERVER_SIGNATURE]".
		"<br /><br />Click <a href='".$urlRequestRoot."'>here </a> to return to the home page";
	templateReplace($TITLE,$MENUBAR,$ACTIONBARMODULE,$ACTIONBARPAGE,$BREADCRUMB,$INHERITEDINFO,$CONTENT,$FOOTER,$DEBUGINFO,$ERRORSTRING,$WARNINGSTRING,$INFOSTRING,$STARTSCRIPTS,$COMPLETEMENU);
	exit();
}
logInfo (getUserEmail($userId),$userId, $pageId, $pageFullPath, getPageModule($pageId), $action, $_SERVER['REMOTE_ADDR']);

if(URLSecurityCheck($_GET))
{
	define("TEMPLATE", getPageTemplate(0));
	$pageId = parseUrlReal("home", $pageIdArray);
	$TITLE = CMS_TITLE;
	$MENUBAR = '';
	$CONTENT = "The requested URL was found to have invalid syntax and cannot be processed for security reasons.<br/> If you believe its a". 				"correct URL, please contact the administrator immediately..<br />$_SERVER[SERVER_SIGNATURE]".
			"<br /><br />Click <a href='".$urlRequestRoot."'>here </a> to return to the home page";
	templateReplace($TITLE,$MENUBAR,$ACTIONBARMODULE,$ACTIONBARPAGE,$BREADCRUMB,$INHERITEDINFO,$CONTENT,$FOOTER,$DEBUGINFO,$ERRORSTRING,$WARNINGSTRING,$INFOSTRING,$STARTSCRIPTS,$COMPLETEMENU);
	exit();
}

if(isset($_GET['fileget'])) {
	require_once($sourceFolder."/download.lib.php");
	$action="";
	if(isset($_GET['action']))
	 $action=$_GET['action'];
	download($pageId,$userId,$_GET['fileget'],$action);
	exit();
}

$permission = getPermissions($userId, $pageId, $action);

define("TEMPLATE", getPageTemplate($pageId));

if (getTitle($pageId, $action, $TITLE))
	$TITLE = CMS_TITLE . " - $TITLE";
else
	$TITLE = CMS_TITLE;


$CONTENT = getContent($pageId, $action, $userId, $permission);

$INHERITEDINFO = inheritedinfo($pageIdArray);


$BREADCRUMB = breadcrumbs($pageIdArray,"&nbsp;»&nbsp;");

$MENUBAR = getMenu($userId, $pageIdArray); 
$COMPLETEMENU = getMenu($userId, $pageIdArray, true);
// The third parameter indicates whether menu is obtained from / or the current page.
// true --> generate from / till depth
// false --> generate from current page till depth relatively.
$COMPLETEMENU = getMenu($userId, $pageIdArray, true);

$ACTIONBARPAGE = getActionbarPage($userId, $pageId);
$ACTIONBARMODULE = getActionbarModule($userId, $pageId);



if($debugSet == "on") {
	$DEBUGINFO .= "Page Full text path : ".$pageFullPath."<br /><br />\n";
	$DEBUGINFO .= "UID : ".getUserId()."<br /><br />\n";
	$DEBUGINFO .= "GIDS : ".arraytostring(getGroupIds($userId))."<br /><br />\n";
	$DEBUGINFO .= "Action : ".$action."<br /><br />\n";
	$DEBUGINFO .= "Get Vars : ".arraytostring($_GET)."<br /><br />\n";
	$DEBUGINFO .= "Page Id : ".$pageId."<br /><br />\n";
	$DEBUGINFO .= "Page id path : ".arraytostring($pageIdArray)."\n<br /><br />";
	$DEBUGINFO .= "Title : ".$TITLE."\n<br /><br />";
	$DEBUGINFO .= "SERVER info : ".arraytostring($_SERVER)."\n<br /><br />";
	$DEBUGINFO .= "POST info : ".arraytostring($_POST)."\n<br /><br />";
	$DEBUGINFO .= "FILES info : ".arraytostring($_FILES)."\n<br /><br />";
	$DEBUGINFO .= "SESSION info : ".arraytostring($_SESSION)."\n<br /><br />";
	$DEBUGINFO .= "STARTSCRIPTS : ".$STARTSCRIPTS."\n<br/><br/>";
	if($DEBUGINFO!="")	displayinfo($DEBUGINFO);
}

	setcookie("cookie_support", "enabled", 0, "/"); ///<used to check in subsequent requests if cookies are supported or not
	
		

	templateReplace($TITLE,$MENUBAR,$ACTIONBARMODULE,$ACTIONBARPAGE,$BREADCRUMB,$INHERITEDINFO,$CONTENT,$FOOTER,$DEBUGINFO,$ERRORSTRING,$WARNINGSTRING,$INFOSTRING,$STARTSCRIPTS,$COMPLETEMENU);

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


