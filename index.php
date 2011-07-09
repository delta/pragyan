<?php
/**
 * @package pragyan
 * @brief Pragyan CMS v3.0 Project
 * @author Abhilash R
 * @author Sahil Ahuja
 * @author Anshu Prateek
 * @author Ankit Srivastav
 * @author Abhishek Shrivastava
 * @author Chakradar Raju
 * @author Balanivash
 * @author Boopathi Rajaa
 * @copyright (c) 2010 Pragyan Team
 * @license http://www.gnu.org/licenses/ GNU Public License
 * For more details, see README
 * @mainpage Pragyan CMS
 * @section Introduction
 * Pragyan CMS is a simple and fast multiuser CMS(Content Management System) to organize collaborative web-content. 
 * This CMS allows very fine user & group permissions and generating pages like articles, forms, quizzes, forums, gallery, etc.
 * The internal search engine is powered by Sphider and it comes with many third-party plugins like PDF, Google Maps,  etc.
 * 
 * @section For License, Credits and other details
 * Please see README.html in docs folder.
 * For more details, contact Abhishek Shrivastava abhishekdelta [at] integriti.org.in .
 * 
 */

///Very important variable for detecting direct script access to any other .php file
define('__PRAGYAN_CMS',')$!%^!%#^@');

///Folder containing all library files
$cmsFolder="cms";

///Folder containing all the modules
$moduleFolder = "modules"; 

///Folder containing all the modules
$templateFolder = "templates"; 

///Folder containing the upload files, temporary files and session files
$uploadFolder = "uploads"; 

///Folder containing all the widgets.
$widgetFolder = "widgets";

///Initial value of debug enabler, will get overridden by the config value
$debugSet = "off";

///Complete location of the source folder
$sourceFolder = substr($_SERVER['SCRIPT_FILENAME'], 0, strrpos($_SERVER['SCRIPT_FILENAME'], '/'))."/".$cmsFolder;

///Can be used to update the last updated time
$PAGELASTUPDATED="";

///Defined here. Will get appended by displayerror() in common.lib.php
$ERRORSTRING = ""; 

///Defined here. Will get appended by displayinfo() in common.lib.php
$INFOSTRING = ""; 

///Defined here. Will get appended by displaywarning() in common.lib.php
$WARNINGSTRING = ""; 

///Will contain a string containing all that has to be executed on window load
$STARTSCRIPTS = "";

///For Apache + Rewrite Mod + phpSUexec, SCRIPT_NAME is WRONG and ORIG_SCRIPT_NAME is correct. So we prioritise ORIG_SCRIPT_NAME. Its unset for any other environment.
$scriptname = isset($_SERVER['ORIG_SCRIPT_NAME'])?$_SERVER['ORIG_SCRIPT_NAME']:$_SERVER['SCRIPT_NAME'];

///Root of the request - that path to cms base
$urlRequestRoot = substr($scriptname, 0, strrpos($scriptname, '/')); 

///Full path to template folder as seen from the browser (defined in template.lib.php)
$TEMPLATEBROWSERPATH = ""; 

///Full path to template folder as seen by httpd while parsing (defined in template.lib.php)
$TEMPLATECODEPATH = "";

///Site description to be used in the HTML <meta> tag 
$SITEDESCRIPTION = ""; 

///Site keywords to be used in the HTML <meta> tag
$SITEKEYWORDS = ""; 

///Login form to be used in template
$LOGINFORM = ""; 

///Debugging information
$DEBUGINFO = ""; 

///is cookie supported by the client's browser ?
$cookieSupported = false; 

///Stores all the icons locations along with <img> tag, indexed by the icon name
$ICONS = ""; 

///Stores all the icons locations without the <img> tag, indexed by the icon name
$ICONS_SRC = ""; 

///Variables for storing widgets.
$WIDGETS = array();

//User Public profile module
$publicPageRequest = false;

///For example, if hosted on pragyan.org/10, $onlineSiteUrl = http://pragyan.org/10/home
$onlineSiteUrl = "http://" . $_SERVER['HTTP_HOST'] . substr($scriptname,0,stripos($scriptname,"index.php")) . "home";

///If config.inc.php doesn't exists, assume CMS hasn't been installed.
@include_once($sourceFolder."/config.inc.php"); 

///If config.inc.php doesn't exists, ADMIN_USERID won't be defined, so assume CMS is not installed.
if(!defined("ADMIN_USERID") )
{
	echo "Welcome to Pragyan CMS v3.0. <a href='./INSTALL/'>Click Here</a> to goto installation page.<br/><br/>
	<b>NOTE:</b>If you're not using the <a href='http://sourceforge.net/projects/pragyan'>official package</a> of the Pragyan CMS or you're installing for the second time, then please make sure that the 'RewriteEngine' property is set to 'Off' in the .htaccess file present in the root folder of Pragyan for the above link to work correctly.";
	exit();
}

///Contains functions which are common to many tasks and very frequently used.
require_once($sourceFolder."/common.lib.php");

///Only works in case Magic Quotes and Register Globals are ENABLED by chance or mistake.
disable_magic_quotes();
unregister_globals();

require_once($sourceFolder."/icons.lib.php");

///Defined here to set its access as global to the project
$dbase; 

///To connect to server
connect(); 

///Authentication process begins here
require_once($sourceFolder."/authenticate.lib.php");
$cookieSupported = checkCookieSupport();
if($cookieSupported==true)	session_start();
$userId=firstTimeGetUserId();
///Case 1 : request a page
if(isset($_GET['page']))
	$pageFullPath = strtolower($_GET['page']);
///Case 2 : request for a user profile page
else if(isset($_GET['user'])) {
	$publicPageRequest = true;
	$userProfileName = $_GET['user'];
	//This is just to prevent parsing a NULL url when someone misplaces the code for User profile parser
	$pageFullPath = "home";
}
else $pageFullPath = "home";

///Retrieve the action, default is "view"
if(isset($_GET['action']))
	$action = strtolower(escape($_GET['action']));
else	$action = "view";

///Just to check if server is alive, an alternative of Ping
if ($action == 'keepalive') 
	die("OK: " . rand());

///Get all the global settings from the database and convert into variables
$globals=getGlobalSettings();
foreach($globals as $var=>$val) 
	$$var=$val;


if($openid_enabled=='true'){                                                                                                                                                 
  set_include_path('cms/openid/');
  require_once 'cms/openid/class.dopeopenid.php';
}
///Check the status of URL rewriting taken from database
$rewriteEngineEnabled=$url_rewrite;

///Some of the previously defined global settings variables are converted into constants

///Title of the Website
define("CMS_TITLE", $cms_title);

///Default template name
define("DEF_TEMPLATE",$default_template);

///Upload size limit for the CMS. All the modules use this constant as the upload limit.
define("UPLOAD_SIZE_LIMIT", $upload_limit);

///Whether to send a mail when a new user registers
define("SEND_MAIL_ON_REGISTRATION",($default_mail_verify==0)?false:true);

///Email address to be used by CMS when sending mails to users
define("CMS_EMAIL",$cms_email);

///Whether to activate the user on registration
define("ACTIVATE_USER_ON_REG",$default_user_activate);

$SITEDESCRIPTION=$cms_desc;
$SITEKEYWORDS=$cms_keywords;
$FOOTER=$cms_footer;

///Include all the required libraries

require_once($sourceFolder."/parseurl.lib.php");
require_once($sourceFolder."/template.lib.php");
require_once($sourceFolder."/menu.lib.php");
require_once($sourceFolder."/breadcrumbs.lib.php");
require_once($sourceFolder."/permission.lib.php");
require_once($sourceFolder."/content.lib.php");
require_once($sourceFolder."/inheritedinfo.lib.php");
require_once($sourceFolder."/actionbar.lib.php");
require_once($sourceFolder."/registration.lib.php");
require_once($sourceFolder."/widget.lib.php");
require_once($sourceFolder."/login.lib.php");


///If requesting for a userpage donot goto parse. Note that this code is before the URL parse

///Check if request is made
if($publicPageRequest) {
	require_once($sourceFolder."/userprofile.lib.php");
	define("TEMPLATE", getPageTemplate(0));
	$TITLE = CMS_TITLE . " | User : " .$userProfileName;
	$CONTENT = generatePublicProfile($userId);
	//$CONTENT = "You are currently viewing a Public Profile of ". htmlentities($userProfileName);
	$MENUBAR = getMenu($userId, $pageIdArray);
	templateReplace($TITLE,$MENUBAR,$ACTIONBARMODULE,$ACTIONBARPAGE,$BREADCRUMB,$INHERITEDINFO,$CONTENT,$FOOTER,$DEBUGINFO,$ERRORSTRING,$WARNINGSTRING,$INFOSTRING,$STARTSCRIPTS,$LOGINFORM);
	exit(1);
}

///Parse the URL and retrieve the PageID of the request page if its valid
$pageId = parseUrlReal($pageFullPath, $pageIdArray);

///Means that the requested URL is not valid.
if ($pageId === false) { 
	define("TEMPLATE", getPageTemplate(0));
	$pageId = parseUrlReal("home", $pageIdArray);
	$TITLE = CMS_TITLE;
	$MENUBAR = '';
	$CONTENT = "The requested URL was not found on this server.<br />$_SERVER[SERVER_SIGNATURE]".
		"<br /><br />Click <a href='".$urlRequestRoot."'>here </a> to return to the home page";
	templateReplace($TITLE,$MENUBAR,$ACTIONBARMODULE,$ACTIONBARPAGE,$BREADCRUMB,$INHERITEDINFO,$CONTENT,$FOOTER,$DEBUGINFO,$ERRORSTRING,$WARNINGSTRING,$INFOSTRING,$STARTSCRIPTS,$LOGINFORM);
	exit();
}

///If it reaches here, means the page requested is valid. Log the information for future use.
logInfo (getUserEmail($userId),$userId, $pageId, $pageFullPath, getPageModule($pageId), $action, $_SERVER['REMOTE_ADDR']);

///The URL may contain some harmful GET variables, so filter and block such URLs.
if(URLSecurityCheck($_GET))
{
	define("TEMPLATE", getPageTemplate(0));
	$pageId = parseUrlReal("home", $pageIdArray);
	$TITLE = CMS_TITLE;
	$MENUBAR = '';
	$CONTENT = "The requested URL was found to have invalid syntax and cannot be processed for security reasons.<br/> If you believe its a". 				"correct URL, please contact the administrator immediately..<br />$_SERVER[SERVER_SIGNATURE]".
			"<br /><br />Click <a href='".$urlRequestRoot."'>here </a> to return to the home page";
	templateReplace($TITLE,$MENUBAR,$ACTIONBARMODULE,$ACTIONBARPAGE,$BREADCRUMB,$INHERITEDINFO,$CONTENT,$FOOTER,$DEBUGINFO,$ERRORSTRING,$WARNINGSTRING,$INFOSTRING,$STARTSCRIPTS,$LOGINFORM);
	exit();
}

///The URL points to a file. Download permissions for the file are handled inside the download() function in download.lib.php
if(isset($_GET['fileget'])) {
	require_once($sourceFolder."/download.lib.php");
	$action="";
	if(isset($_GET['action']))
	 $action=$_GET['action'];
	download($pageId,$userId,$_GET['fileget'],$action);
	exit();
}

///Check whether the user has the permission to use that action on the requested page.
$permission = getPermissions($userId, $pageId, $action);

///Gets the page-specific template for that requested page
define("TEMPLATE", getPageTemplate($pageId));

///Gets the page title of the requested page
if (getTitle($pageId, $action, $TITLE))
	$TITLE = CMS_TITLE . " - $TITLE";
else
	$TITLE = CMS_TITLE;

///Gets the content according to the user's permissions
$CONTENT = getContent($pageId, $action, $userId, $permission);

///Gets the inherited code (if any) from the parent page
$INHERITEDINFO = inheritedinfo($pageIdArray);

///Gets the breadcrumb
$BREADCRUMB = breadcrumbs($pageIdArray,"&nbsp;»&nbsp;");

///Gets the menubar consisting of the child pages from the current location upto a certain depth
$MENUBAR = getMenu($userId, $pageIdArray); 

///The Login form to be displayed from login.lib.php
if($userId == 0)
	$LOGINFORM = loginForm();
else
{
	$userNameFromId = getUserName($userId);
	$LOGINFORM = "Welcome {$userNameFromId}.";
}

///Gets the list of allowed actions for the current page
$ACTIONBARPAGE = getActionbarPage($userId, $pageId);

///Gets the list of allowed actions for the current module on the page
$ACTIONBARMODULE = getActionbarModule($userId, $pageId);

///Initializes the widgets in the page
populateWidgetVariables($pageId);

///If its disabled, then all the links in the generated page are converted into non-pretty URLs using regex
if($rewriteEngineEnabled=='false') {
	$TITLE = convertUri($TITLE);
	$MENUBAR = convertUri($MENUBAR);
	$CONTENT = convertUri($CONTENT);
	$INHERITEDINFO = convertUri($INHERITEDINFO);
	$BREADCRUMB = convertUri($BREADCRUMB);
	$ACTIONBARPAGE = convertUri($ACTIONBARPAGE);
	$ACTIONBARMODULE = convertUri($ACTIONBARMODULE);
	$INFOSTRING = convertUri($INFOSTRING);
	$ERRORSTRING = convertUri($ERRORSTRING);
	$WARNINGSTRING = convertUri($WARNINGSTRING);
	$LOGINFORM = convertUri($LOGINFORM);
}

///Some extra debugging information if debugSet is enabled
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

///Used to check in subsequent requests if cookies are supported or not
setcookie("cookie_support", "enabled", 0, "/"); 
	
///Apply the template on the generated content and display the page
templateReplace($TITLE,$MENUBAR,$ACTIONBARMODULE,$ACTIONBARPAGE,$BREADCRUMB,$INHERITEDINFO,$CONTENT,$FOOTER,$DEBUGINFO,$ERRORSTRING,$WARNINGSTRING,$INFOSTRING,$STARTSCRIPTS,$LOGINFORM);

disconnect();
exit();

/** Additional notes :

authenticate.lib.php -> Find out who requested it
	output: one int -> uid

parseurl.lib.php -> Find out the page id and action requested
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




