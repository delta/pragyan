<?php
/** College information management suite
 *
 * COMMONLY USED FUNCTIONS
 * connect,disconnect,displayerror.
*/

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

/**Used for error handling */
function displayerror($error_desc) {
	global $ERRORSTRING;
	$ERRORSTRING .= "<div class=\"error\">$error_desc</div>";
}

/**Used for giving info */
function displayinfo($error_desc) {
	global $INFOSTRING;
	$INFOSTRING .= "<div class=\"info\">$error_desc</div>";
}

/**Used for giving warning*/
function displaywarning($error_desc) {
	global $WARNINGSTRING;
	$WARNINGSTRING .= "<div class=\"warning\">$error_desc</div>";
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
	$query = "SELECT `user_name` FROM `".MYSQL_DATABASE_PREFIX."users` WHERE `user_id` = $userId";
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
	$query = "SELECT `user_fullname` FROM `".MYSQL_DATABASE_PREFIX."users` WHERE `user_id` = $userId";
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
	$query="SELECT `user_email` FROM `".MYSQL_DATABASE_PREFIX."users` WHERE `user_id` = $userId";
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
	$query = 'SELECT `user_id` FROM `'.MYSQL_DATABASE_PREFIX."users` WHERE `user_email` = '$email'";
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
		$pathQuery = "SELECT `page_parentid`, `page_name` FROM `".MYSQL_DATABASE_PREFIX."pages` WHERE `page_id` = $pageid";
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
	$pageparent_query = "SELECT `page_id`,`page_name`,`page_parentid`,`page_title`,`page_module`,`page_modulecomponentid`,`page_menurank`,`page_inheritedinfoid`,`page_displayinmenu`,`page_displaymenu`,`page_displaysiblingmenu` FROM `".MYSQL_DATABASE_PREFIX."pages` WHERE `page_id`=".$pageid;
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
	$moduleid_query = "SELECT `page_id` FROM `".MYSQL_DATABASE_PREFIX."pages` WHERE `page_module` = '$moduleName' AND `page_modulecomponentid` = $moduleComponentId";
	$moduleid_result = mysql_query($moduleid_query);
	$moduleid_row = mysql_fetch_assoc($moduleid_result);
	return $moduleid_row['page_id'];
}

function getModuleComponentIdFromPageId($pageId, $moduleName) {
	$moduleIdQuery = 'SELECT `page_modulecomponentid` FROM `' . MYSQL_DATABASE_PREFIX . "pages` WHERE `page_module` = '$moduleName' AND `page_id` = $pageId";
	$moduleIdResult = mysql_query($moduleIdQuery);
	$moduleIdRow = mysql_fetch_row($moduleIdResult);
	return $moduleIdRow[0];
}

function logInfo ($userEmail, $userId, $pageId, $pagePath, $permModule, $permAction, $accessIpAddress) {
	if(isRequiredMaintenance()) {
		global $sourceFolder;
		require_once("$sourceFolder/maintenance.lib.php");
		runMaintenance();
	}
	if($pageId === false) $pageId = -1;
	if(isset($_GET['fileget']))	return false;

	$updateQuery = "SELECT `log_no` FROM `".MYSQL_DATABASE_PREFIX."log` WHERE `log_no` = 1";
	$result = mysql_query($updateQuery);
	if(!$result || mysql_num_rows($result) == 0) $updateText = "1";
	else $updateText = "SELECT ( MAX(log_no) + 1 )";
	$updateQuery = "INSERT INTO `".MYSQL_DATABASE_PREFIX."log` (`log_no`, `user_email`, `user_id`, `page_id`, `page_path`, `perm_module`, `perm_action`, `user_accessipaddress`)
    ( ".$updateText."  , '$userEmail', $userId, $pageId, '$pagePath', '$permModule', '$permAction', '$accessIpAddress' FROM `".MYSQL_DATABASE_PREFIX."log`);";
    if(!mysql_query($updateQuery))
    	displayerror ("Error in logging info.");
    return true;
}

#returns true for first access of every 10 day slab
#select date > sub(now, diff(now,first)%10)
function isRequiredMaintenance() {
	$requiredQuery = "SELECT log_datetime FROM ".MYSQL_DATABASE_PREFIX."log WHERE
log_datetime >
SUBDATE( NOW(),(SELECT DATEDIFF( NOW(), ( SELECT MIN(log_datetime) FROM ".MYSQL_DATABASE_PREFIX."log) ) LIMIT 0,1 )%10 )
LIMIT 0,1";
	$requiredResult = mysql_query($requiredQuery);
	if(mysql_num_rows($requiredResult) == 0)
		return true;
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

function selfURL() {
    $s = empty($_SERVER["HTTPS"]) ? '' : ($_SERVER["HTTPS"] == "on") ? "s" : "";
    $protocol = strleft(strtolower($_SERVER["SERVER_PROTOCOL"]), "/").$s;
    $port = ($_SERVER["SERVER_PORT"] == "80") ? "" : (":".$_SERVER["SERVER_PORT"]);
	return $protocol."://".$_SERVER['SERVER_NAME'].$port.$_SERVER['REQUEST_URI'];
}

function strleft($s1, $s2) {
    return substr($s1, 0, strpos($s1, $s2));
}

?>