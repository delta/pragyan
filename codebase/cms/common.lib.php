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
 * @file common.lib.php
 * @brief Contains functions which are common to many tasks and very frequently used.
 * @author Abhishek <i.abhi27[at]gmail.com>.
 * @copyright (c) 2010 Pragyan Team.
 * @license http://www.gnu.org/licenses/ GNU Public License.
 * For more details, see README
 */


global $sourceFolder,$moduleFolder;

require_once("smarttable.class.php");

/** To connect to the database*/
function connect() {
	$dbase = mysql_connect(MYSQL_SERVER, MYSQL_USERNAME, MYSQL_PASSWORD) or die("Could not connect to server");
	mysql_select_db(MYSQL_DATABASE) or die("Could not connect to database");
	return $dbase;
}

/** To disconnect from the database once query is over*/
function disconnect() {
	mysql_close();
}
function prettyurl($str) {
	global $urlRequestRoot;
	$page = (isset($_GET['page']))?$_GET['page']:"/";
	$file="";
	if(strripos($str,"./")!=strripos($str,"../")) {
		$file= substr($str,strripos($str,"/")+1);
		$str = substr($str,0,strripos($str,"/")+1);
	}
	if(substr($str,0,3)=="../"){
		$page = substr($page,0,strripos($page,"/")-1);
		$page = substr($page,0,strripos($page,"/")+1);
	}
	if(strpos($str,"../")) {
		$pos = strpos($str,"../");
		$page = substr($page,0,strripos($page,"/")-1);
		$page = substr($page,0,strripos($page,"/")+1);
		$str = substr($str,0,$pos) . substr($str,$pos+3);
		//echo $page." -<br>";
	}
	$str = ereg_replace("^./",$urlRequestRoot."/?page=".$page,$str);
	$str = ereg_replace("^../",$urlRequestRoot."/?page=".$page,$str);
	$str = ereg_replace("\+","&action=",$str);
	$str = ereg_replace("^".hostURL()."/home",hostURL()."/?page=",$str);
	$str = ereg_replace("^".$urlRequestRoot."/home","./?page=",$str);
	if($file!="")
		$str .= "&fileget=".$file;
	return $str;
}

function convertUrif($x,$attr) {
	$y="";
	$z = $x;
	$len=strlen($attr);
	if($len!=0)
	while(1) {
		$z=$x;
		$count=0;
		if(strpos($x,$attr))
			$y .= substr($x,$count,strpos($x,$attr)+$len+2);
		else
			$y .= substr($x,$count);
		$count=strpos($x,$attr)+$len+2;
		if($count==$len+2) break;
		$x = substr($x,$count);
		//echo "<br>" . substr($x,0,strpos($x,"\"")) . " => " . prettyurl(substr($x,0,strpos($x,"\"")));
		$count1=(strpos($x,"\"")==-1||!strpos($x,"\""))?10000:strpos($x,"\"");
		$count2=(strpos($x,"'")==-1||!strpos($x,"'"))?10000:strpos($x,"'");
		$count=($count1<$count2)?$count1:$count2;
//		echo substr($x,0,$count) ." => ". prettyurl(substr($x,0,$count)). "<br>";
		$y .= prettyurl(substr($x,0,$count));
		$x = substr($x,$count);
	}
	return $y;
}
function convertUri($x) {
	$y="";
	$z = $x;
	$hsref=array("href","action","src");
	foreach($hsref as $href) {
	$len=strlen($href);
	if($len!=0)
	$z=convertUrif($z,$href);
	}
	return $z;
}

/** Security Functions Begin, by Abhishek (For Usage, read Security Guidelines)**/

/** To escape the database queries for avoiding SQL injection attacks */

function escape($query)
{
	if (!get_magic_quotes_gpc()) {
	    $query = addslashes($query);
	}
	return $query;
}

/** To protect against writing dangerous URLs, Returns true if it detects a risk, More improvement to be done */

function URLSecurityCheck($getvars)
{
	foreach($getvars as $var=>$val)
	{
		if(preg_match("/[<>]/",$var) || preg_match("/[<>]/",$val)) 
			return true;
	}
	return false;
}

/** To prevent XSS attacks  */

function safe_html($html)
{
	return htmlspecialchars(strip_tags($html));
}
/** Security Functions Ends **/

/** Load Templates into the database */

function reloadTemplates()
{
	global $sourceFolder;
	global $templateFolder;
	$templates=scandir($sourceFolder.'/'.$templateFolder);
	$res="<table>";
	$temparrr=array();
	foreach($templates as $tdir)
	{
		$tdir=escape($tdir);
		if(is_dir($sourceFolder.'/'.$templateFolder.'/'.$tdir) && $tdir[0]!='.' && $tdir!="common")
		{
			$query="INSERT IGNORE INTO `".MYSQL_DATABASE_PREFIX."templates` (`template_name`) VALUES ('$tdir')";
			mysql_query($query);
			if(mysql_affected_rows())
				$res.="<tr><td>$tdir</td><td><b>Found new template! Installed.</b></td></tr>";
			else $res.="<tr><td>$tdir</td><td>OK</td></tr>";
			$temparr[]=$tdir;
		}
		
	}
	$templist=join("','",$temparr);	
	$query="DELETE FROM `".MYSQL_DATABASE_PREFIX."templates` WHERE `template_name` NOT IN ('$templist')";
	mysql_query($query);
	if($delc=mysql_affected_rows()>0)
		$res.="<tr><td colspan=2>$delc template(s) removed from database</td></tr>";
	return $res."</table>";
}

function reloadModules()
{
	global $sourceFolder;
	global $moduleFolder;
	$modules=scandir($sourceFolder.'/'.$moduleFolder);
	$res="<table>";
	$modarr=array();
	foreach($modules as $module)
	{
		$module=escape($module);
		$ext=substr($module,-8);
		$module=substr($module,0,-8);
		if($ext==".lib.php")
		{
			$query="INSERT IGNORE INTO `".MYSQL_DATABASE_PREFIX."modules` (`module_name`) VALUES ('$module')";
			mysql_query($query);
			if(mysql_affected_rows())
				$res.="<tr><td>$module</td><td><b>Found new module! Installed.</b></td></tr>";
			else $res.="<tr><td>$module</td><td>OK</td></tr>";
			$modarr[]=$module;
		}
		
	}
	$modlist=join("','",$modarr);	
	$query="DELETE FROM `".MYSQL_DATABASE_PREFIX."modules` WHERE `module_name` NOT IN ('$modlist')";
	mysql_query($query);
	if($delc=mysql_affected_rows()>0)
		$res.="<tr><td colspan=2>$delc module(s) removed from database</td></tr>";
	return $res."</table>";
}


/** To retrieve Global Settings from Database */

function getGlobalSettings()
{
	$query="SELECT * FROM `".MYSQL_DATABASE_PREFIX."global`";
	$result=mysql_query($query);
	$globals=array();
	while($row=mysql_fetch_array($result))
		$globals[$row['attribute']]=$row['value'];
	return $globals;
}

/** To set Global Settings in Database */

function setGlobalSettings($globals)
{
	
	foreach($globals as $var => $val)
	{
		setGlobalSettingByAttribute($var,$val);
	}
}

/** To set Global Settings by attribute in Database */

function setGlobalSettingByAttribute($attribute,$value)
{
	if(mysql_num_rows(mysql_query("SELECT `value` FROM `" . MYSQL_DATABASE_PREFIX . "global` WHERE `attribute` = '$attribute'")) != 0)
		$query="UPDATE `".MYSQL_DATABASE_PREFIX."global` SET `value`='$value' WHERE `attribute`='$attribute'";
	else
		$query="INSERT INTO `" . MYSQL_DATABASE_PREFIX . "global`(`attribute`,`value`) VALUES('{$attribute}','{$value}')";
	mysql_query($query);	
}

/**Used for error handling */
function displayerror($error_desc) {
	global $ERRORSTRING;
	$ERRORSTRING .= "<div class=\"cms-error\">$error_desc</div>";
}

/**Used for giving info */
function displayinfo($error_desc) {
	global $INFOSTRING;
	$INFOSTRING .= "<div class=\"cms-info\">$error_desc</div>";
	
}

/**Used for giving warning*/
function displaywarning($error_desc) {
	global $WARNINGSTRING;
	$WARNINGSTRING .= "<div class=\"cms-warning\">$error_desc</div>";
}

/**
 * Convert an array to a string recursively
 * @param $array Array to convert
 * @return string containing the array information
 */
 function arraytostring($array) {
	$text = "array(";
	$count=count($array);
	$x=0;
	foreach ($array as $key=>$value) {
		$x++;
		if (is_array($value)) {
			if(substr($text,-1,1)==')')
				$text .= ',';
			$text.='"'.$key.'"'."=>".arraytostring($value);
			continue;
		}

		$text.="\"$key\"=>\"$value\"";
		if ($count!=$x)
			$text.=",";
	}

	$text.=")";

	if(substr($text, -4, 4)=='),),')$text.='))';
		return $text;
}

/**
 * Determines the User Name of a user, given his/her User Id
 * @param $userId User Id of the user, whose User Name is to be determined
 * @return string containing the User Name of the user, null representing failure
 */
function getUserName($userId) {
	if($userId <= 0) return "Anonymous";
	$query = "SELECT `user_name` FROM `".MYSQL_DATABASE_PREFIX."users` WHERE `user_id` = ".$userId;
	$result = mysql_query($query);
	$row = mysql_fetch_row($result);
	return $row[0];
}

/**
 * Determines the Full Name of a user, given his/her User Id
 * @param $userId User Id of the user, whose Full Name is to be determined
 * @return string containing the Full Name of the user, null representing failure
 */
function getUserFullName($userId) {
	if($userId <= 0) return "Anonymous";
	$query = "SELECT `user_fullname` FROM `".MYSQL_DATABASE_PREFIX."users` WHERE `user_id` = ".$userId;
	$result = mysql_query($query);
	$row = mysql_fetch_row($result);
	return $row[0];
}

/**
 * Determines the Full Name of a user, given his/her Email ID
 * @param $email Email Id of the user, whose Full Name is to be determined
 * @return string containing the Full Name of the user, null representing failure
 */
function getUserFullNameFromEmail($email) {
	$query = "SELECT `user_fullname` FROM `".MYSQL_DATABASE_PREFIX."users` WHERE `user_email` = '".$email."'";
	$result = mysql_query($query);
	
	$row = mysql_fetch_row($result);
	return $row[0];
}

/**
 * Determines the Email-Id of a user, given his/her User Id
 * @param $userid User Id of the user, whose E-mail address is to be determined
 * @return string containing the e-mail address of the user, null representing failure
 */
function getUserEmail($userId) {
	if($userId <= 0) return 'Anonymous';
	$query="SELECT `user_email` FROM `".MYSQL_DATABASE_PREFIX."users` WHERE `user_id` = ".$userId;
	$result = mysql_query($query);
	$row= mysql_fetch_row($result);
	return $row[0];
}

/**
 * Determines the User Id of a user, given his/her E-mail Id
 * @param $email E-mail address of the user, whose User Id is to be determined
 * @return Integer representing the User Id of the user, null representing failure
 */
function getUserIdFromEmail($email) {
	if(strtolower($email) == 'anonymous') return 0;
	$query = 'SELECT `user_id` FROM `'.MYSQL_DATABASE_PREFIX."users` WHERE `user_email` = '".$email."'";
	$result = mysql_query($query);
	$row = mysql_fetch_row($result);
	return $row[0];
}


/**
 * Determines the module type of a given page
 * @param $pageid Page id of the page, whose module name is to be determined
 * @return String containing the module name of the given page
 */
function getEffectivePageModule($pageId) {
	$pagemodule_query = "SELECT `page_module`, `page_modulecomponentid` FROM `".MYSQL_DATABASE_PREFIX."pages` WHERE `page_id`=".$pageId;
	$pagemodule_result = mysql_query($pagemodule_query);
	$pagemodule_row = mysql_fetch_assoc($pagemodule_result);
	if($pagemodule_row['page_module']=="link")	return (getEffectivePageModule($pagemodule_row['page_modulecomponentid']));
	return $pagemodule_row['page_module'];
}

/**
 * Gets the next module component id of a given module, which can be used for creating new instances of the same module.
 * @param $modulename Name of the module
 * @return Integer representing the new module component id
 */
function getNextModuleComponentId($modulename) {
		$moduleComponentIdQuery = "SELECT MAX(page_modulecomponentid) FROM `".MYSQL_DATABASE_PREFIX."_pages` WHERE `page_module`='$modulename'";
		$moduleComponentIdResult = mysql_query($moduleComponentIdQuery);
		if(!$moduleComponentIdResult)
			return 0;
		$moduleComponentIdRow = mysql_fetch_row($moduleComponentIdResult);
		if(!is_null($moduleComponentIdRow[0]))
			return $moduleComponentIdRow[0] + 1;
		return 1;
}


/**
 * Determines the dereferenced Page Id of a given page
 * @param $pageid Page id of the page (link) to be dereferenced
 * @return Integer indicating the dereferenced page id
 */
function getDereferencedPageId($pageId) {
	$pagemodule_query = "SELECT `page_module`, `page_modulecomponentid` FROM `".MYSQL_DATABASE_PREFIX."pages` WHERE `page_id`=".$pageId;
	$pagemodule_result = mysql_query($pagemodule_query);
	$pagemodule_row = mysql_fetch_assoc($pagemodule_result);
	if($pagemodule_row['page_module']=="link") {
		return getDereferencedPageId($pagemodule_row['page_modulecomponentid']);
	}
	return $pageId;
}



function getPagePath($pageid) {
	$pagepath = '';

	while($pageid != 0) {
		$pathQuery = "SELECT `page_parentid`, `page_name` FROM `".MYSQL_DATABASE_PREFIX."pages` WHERE `page_id` = ".$pageid;
		$pathResult = mysql_query($pathQuery);
		$pathResultRow = mysql_fetch_row($pathResult);

		$pageid = $pathResultRow[0];
		$pagepath = $pathResultRow[1]."/$pagepath";
	}

	return "/$pagepath";
}

function getPageModule($pageId) {
	$pagemodule_query = "SELECT `page_module` FROM `".MYSQL_DATABASE_PREFIX."pages` WHERE `page_id`=".$pageId;
	$pagemodule_result = mysql_query($pagemodule_query);
	$pagemodule_row = mysql_fetch_assoc($pagemodule_result);
	return $pagemodule_row['page_module'];
}
function getPageTitle($pageId) {
	$pagemodule_query = "SELECT `page_title` FROM `".MYSQL_DATABASE_PREFIX."pages` WHERE `page_id`=".$pageId;
	$pagemodule_result = mysql_query($pagemodule_query);
	$pagemodule_row = mysql_fetch_assoc($pagemodule_result);
	return $pagemodule_row['page_title'];
}



/**
 * Determines the page id of the parent of a given page
 * @param $pageid Page id of the page, whose parent is to be determined
 * @return Integer indicating the page id of the parent page
 */
function getParentPage($pageid) {
	$pageparent_query = "SELECT `page_parentid` FROM `".MYSQL_DATABASE_PREFIX."pages` WHERE `page_id`=".$pageid;
	$pageparent_result = mysql_query($pageparent_query);
	$pageparent_row = mysql_fetch_assoc($pageparent_result);
	return $pageparent_row['page_parentid'];
}
function getPageInfo($pageid) {
	$pageparent_query = "SELECT `page_id`, `page_name`, `page_parentid`, `page_title`, `page_module`, `page_modulecomponentid`, `page_menurank`, `page_inheritedinfoid`, `page_displayinmenu`, `page_displaymenu`, `page_displaysiblingmenu`, `page_menutype`, `page_menudepth`, `page_image`, `page_displayicon` FROM `".MYSQL_DATABASE_PREFIX."pages` WHERE `page_id`=".$pageid;
	$pageparent_result = mysql_query($pageparent_query);
	$pageparent_row = mysql_fetch_assoc($pageparent_result);
	return $pageparent_row;
}
function getPageModuleComponentId($pageid) {
	$pageparent_query = "SELECT `page_modulecomponentid` FROM `".MYSQL_DATABASE_PREFIX."pages` WHERE `page_id`=".$pageid;
	$pageparent_result = mysql_query($pageparent_query);
	$pageparent_row = mysql_fetch_assoc($pageparent_result);
	return $pageparent_row['page_modulecomponentid'];
}
function getPageIdFromModuleComponentId($moduleName,$moduleComponentId) {
	$moduleid_query = "SELECT `page_id` FROM `".MYSQL_DATABASE_PREFIX."pages` WHERE `page_module` = '".$moduleName."' AND `page_modulecomponentid` = ".$moduleComponentId;
	$moduleid_result = mysql_query($moduleid_query);
	$moduleid_row = mysql_fetch_assoc($moduleid_result);
	return $moduleid_row['page_id'];
}

function getModuleComponentIdFromPageId($pageId, $moduleName) {
	$moduleIdQuery = 'SELECT `page_modulecomponentid` FROM `' . MYSQL_DATABASE_PREFIX . "pages` WHERE `page_module` = '".$moduleName."' AND `page_id` = ".$pageId;
	$moduleIdResult = mysql_query($moduleIdQuery);
	$moduleIdRow = mysql_fetch_row($moduleIdResult);
	return $moduleIdRow[0];
}
/**
 *@author boopathi
 *@description returns the depth of the page - 0 if the page is a child of /home
 *@param pageId
 *@return pageDepth
 **/
function getPageDepth($pageId) {
	$depth = 1;
	if(getParentPage($pageId) == 0)
		return 0;
	else
		return $depth + getPageDepth(getParentPage($pageId));
}

function logInfo ($userEmail, $userId, $pageId, $pagePath, $permModule, $permAction, $accessIpAddress) {
	if(isRequiredMaintenance()) {
		require_once("maintenance.lib.php");
		runMaintenance();
	}
	if($pageId === false) $pageId = -1;
	if(isset($_GET['fileget']))	return false;

	$updateQuery = "SELECT `log_no` FROM `".MYSQL_DATABASE_PREFIX."log` WHERE `log_no` = 1";
	$result = mysql_query($updateQuery);
	
	if(!$result || mysql_num_rows($result) == 0)
		$updateQuery = "INSERT INTO `".MYSQL_DATABASE_PREFIX."log` (`log_no`, `user_email`, `user_id`, `page_id`, `page_path`, `perm_module`, `perm_action`, `user_accessipaddress`)
    	VALUES ( 1  , '".$userEmail."', ".$userId.", ".$pageId.", '".$pagePath."', '".$permModule."', '".$permAction."', '".$accessIpAddress."' );";
    else
    	$updateQuery = "INSERT INTO `".MYSQL_DATABASE_PREFIX."log` (`log_no`, `user_email`, `user_id`, `page_id`, `page_path`, `perm_module`, `perm_action`, `user_accessipaddress`)
    	( SELECT (MAX(log_no)+1)  , '".$userEmail."', ".$userId.", ".$pageId.", '".$pagePath."', '".$permModule."', '".$permAction."', '".$accessIpAddress."' FROM  `".MYSQL_DATABASE_PREFIX."log`);";
    
    if(!mysql_query($updateQuery))
    	displayerror ("Error in logging info.");
    return true;
}

#returns true for first access of every 10 day slab
#select date > sub(now, diff(now,first)%10)
function isRequiredMaintenance() {
	$requiredQuery = "SELECT log_datetime FROM `".MYSQL_DATABASE_PREFIX."log` WHERE
log_datetime >
SUBDATE( SUBTIME(NOW(),CURTIME()),(
		DATEDIFF( 
			NOW(), ( 
				SELECT MIN(log_datetime) FROM `".MYSQL_DATABASE_PREFIX."log`
				) 
			)
		)%10 
		)
LIMIT 0,1";
	$requiredResult = mysql_query($requiredQuery);
	if($requiredResult!=NULL && mysql_num_rows($requiredResult) == 0) { 
		return true;
	}
	return false;
}

/**
 * Replaces the protocol in a url with https://
 * @param $url Url to be converted
 * @return Converted Url
 */
function convertToHttps($url){
	if(!strncasecmp("https://",$url,8))
		return $url;
	else
		return str_replace("http://","https://",$url);
}

/**
 * Replaces the protocol in a url with http://
 * @param $url Url to be converted
 * @return Converted Url
 */
function convertToHttp($url){
	if(!strncasecmp("http://",$url,7))
		return $url;
	else {
		$pos = strpos($url, '://');
		if($pos >= 0) {
			return 'http://' . substr($url, $pos + 3);
		}
		else return $url;
	}
}

function verifyHttps($url){
	if(!strncasecmp("https://",$url,7))
		return true;
	else 
		return false;
}

function selfURI() {
    $s = empty($_SERVER["HTTPS"]) ? '' : ($_SERVER["HTTPS"] == "on") ? "s" : "";
    $protocol = strleft(strtolower($_SERVER["SERVER_PROTOCOL"]), "/").$s;
    $port = ($_SERVER["SERVER_PORT"] == "80") ? "" : (":".$_SERVER["SERVER_PORT"]);
	return $protocol."://".$_SERVER['SERVER_NAME'].$port.$_SERVER['REQUEST_URI'];
}

function hostURL() {
    $s = empty($_SERVER["HTTPS"]) ? '' : ($_SERVER["HTTPS"] == "on") ? "s" : "";
    $protocol = strleft(strtolower($_SERVER["SERVER_PROTOCOL"]), "/").$s;
    $location = substr($_SERVER['SCRIPT_NAME'],0,strpos($_SERVER['SCRIPT_NAME'],"/index.php"));
    $port = ($_SERVER["SERVER_PORT"] == "80") ? "" : (":".$_SERVER["SERVER_PORT"]);
	return $protocol."://".$_SERVER['SERVER_NAME'].$port.$location;
}

/**
 * Replaces the action in the url to a new action
 *
 * @param $url Initial URL
 * @param $old Old Action
 * @param $new New Action
 *
 * @return the URL with the new action
 * @TODO check for rewrite enabled and handle +action as well as &action=action kind of URLs
 * @warning Whats the guarantee it won't convert some word in the URL which matches the Old Action ?
 */
function replaceAction($url,$old,$new) {
   $offset = strpos($url,"action=$old");
   $url = substr_replace($url,$new,$offset+7,strlen($old));
   return $url;
}

function strleft($s1, $s2) {
    return substr($s1, 0, strpos($s1, $s2));
}

function updateUserPassword($user_email,$user_passwd) {
	$query = "UPDATE `" . MYSQL_DATABASE_PREFIX . "users` SET `user_password`= '".md5($user_passwd)."' WHERE `" . MYSQL_DATABASE_PREFIX . "users`.`user_email` = '" . $user_email . "'";
							mysql_query($query) or die(mysql_error() . " in function updateUserPassword");
}

function getUserInfo($user_email) {
	$query = "SELECT `user_id`,`user_password`,`user_name`,`user_activated`,`user_lastlogin`,`user_loginmethod` FROM `" . MYSQL_DATABASE_PREFIX . "users` WHERE `user_email` = '" . $user_email . "'";
	$result = mysql_query($query) or die(mysql_error() . " in function getUserInfo : common.lib.php");
	return mysql_fetch_assoc($result);
}

/*
@todo should be moved to email.lib.php ,or is it really used ?
*/
class messenger {
		var $vars;
		
			
		function assign_vars($vars) {
				$this->vars = (empty($this->vars)) ? $vars : $this->vars + $vars;
				}
				
		function mailer($to,$mailtype,$key,$from) {
				
				if(empty($from)) $from="from: ".CMS_TITLE." <".CMS_EMAIL.">";
				
				//init mail template file path
				$mail_filepath= MAILPATH."/".LANGUAGE."/email/$mailtype.txt"; 
				$drop_header = '';
				
				if(!file_exists($mail_filepath)) {displayerror(safe_html("NO FILE called $mail_filepath FOUND !"));} //check file
				if(($data = @file_get_contents($mail_filepath)) === false) {displayerror("$mail_filepath FILE READ ERROR !");} //read contents
				
				//escape quotes
				$body = str_replace ("'", "\'", $data); 
				//replace the vars in file content with those defined
				$body = preg_replace('#\{([a-z0-9\-_]*?)\}#is', "' . ((isset(\$this->vars['\\1'])) ? \$this->vars['\\1'] : '') . '", $body);
				//Make the content parseable
				eval("\$body = '$body';");
				
				//Extract the SUBJECT from mail content
				$match=array();
				if (preg_match('#^(Subject:(.*?))$#m', $body, $match)) {
					//Find SUBJECT
					$subject = (trim($match[2]) != '') ? trim($match[2]) :  $subject ;
					$drop_header .= '[\r\n]*?' . preg_quote($match[1], '#');
				}
				if ($drop_header) {
					//Remove SUBJECT from BODY of mail
					$body = trim(preg_replace('#' . $drop_header . '#s', '', $body));
				}
				
				//Debug info
				//echo displayinfo($from.' <br> '.$to.' <br> '.$subject.' <br> '.$body);
				
				//Send mail 
				global $debugSet;
				if($debugSet=="on")
				{
					displayinfo("Vars :".arraytostring($this->vars));
					displayinfo("Mail sent to $to from $from with subject $subject and body $body");
					
				}
				return mail($to, $subject, $body, $from);
			}
				
	}

function getAvailableTemplates()
{
	$query="SELECT template_name FROM `".MYSQL_DATABASE_PREFIX."templates`";
	$result=mysql_query($query);
	$templates=array();
	$i=0;
	while($row=mysql_fetch_row($result))
	{
		$templates[$i]=$row[0];
		$i++;
	}
	
	return $templates;
}

function getAvailableModules()
{
	$query="SELECT `module_name` FROM `".MYSQL_DATABASE_PREFIX."modules`";
	$result=mysql_query($query);
	$templates=array();
	$i=0;
	while($row=mysql_fetch_row($result))
	{
		$templates[$i]=$row[0];
		$i++;
	}
	
	return $templates;
}

function getTableFieldsName($tablename,$exclude="user_profilepic")
{
	$query="SELECT * FROM ".MYSQL_DATABASE_PREFIX.$tablename;
	$result=mysql_query($query);
	$numfields=mysql_num_fields($result);
	$fields=array();
	$i=0;
	$exclist=explode(",",$exclude);
	while($i<$numfields)
	{
		$meta=mysql_fetch_field($result,$i);
		if($meta && array_search($meta->name,$exclist)===FALSE)
		{
			$fields[$i]=$meta->name;
		}
		$i++;
	}
	return $fields;
}

function getNextUserId()
{
	$query="SELECT max(user_id) FROM ".MYSQL_DATABASE_PREFIX."users";
	$result=mysql_query($query);
	$row=mysql_fetch_row($result);
	return $row[0]+1;
}

function showBreadcrumbSubmenu()
{
	$query="SELECT `value` FROM `".MYSQL_DATABASE_PREFIX."global` WHERE `attribute`='breadcrumb_submenu'";
	$result = mysql_fetch_row(mysql_query($query));
	return $result[0];
}

function getFileActualPath($moduleType,$moduleComponentId,$fileName)
{
	$query = "SELECT * FROM `" . MYSQL_DATABASE_PREFIX . "uploads` WHERE  `upload_filename`= '". escape($fileName). "' AND `page_module` = '".escape($moduleType)."' AND `page_modulecomponentid` = '".escape($moduleComponentId)."'";
	$result = mysql_query($query) or die(mysql_error() . "upload L:85");
	$row = mysql_fetch_assoc($result);
	/**
	 * Not checking if filetype adheres to uploadable filetype list beacuse this check can be
	 * performed in $moduleInstance->getFileAccessPermission.
	 */

	global $sourceFolder,$uploadFolder;
	$upload_fileid = $row['upload_fileid'];
	
	$filename = str_repeat("0", (10 - strlen((string) $upload_fileid))) . $upload_fileid . "_" . $fileName;
	
	$file = $sourceFolder . "/" . $uploadFolder . "/" . $moduleType . "/" . $filename;
	return $file;
}
/**
 *  Checks for presence of the cURL extension for OpenID.
*/
function iscurlinstalled() {
  if  (in_array  ('curl', get_loaded_extensions())) {
    return true;
  }
  else{
    return false;
  }
}
$curl_message="cURL extention is not enabled/installed on your system. OpenID requires this extention to be loaded. Please enable cURL extention. (This can be done by uncommenting the line \"extension=curl.so\" in your php.ini file). OpenID can't be enabled until you enable cURL.";
function censor_words($text)
{
	$query = "SELECT `value` FROM `".MYSQL_DATABASE_PREFIX."global` WHERE `attribute` = 'censor_words'";
	$words = mysql_query($query);
	$words = mysql_fetch_row($words);
	$replace = "<b>CENSORED</b>";
	if($words[0]=='')
		return $text;
	else
		$res = preg_replace("/$words[0]/i",$replace,$text);
	return $res;
}
