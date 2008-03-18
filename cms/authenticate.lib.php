<?php
function getSessionData($user_id) {
	$query = "SELECT `user_name`,`user_email`,`user_lastlogin` FROM `" . MYSQL_DATABASE_PREFIX . "users` WHERE `user_id`=$user_id";
	$data = mysql_query($query) or die(mysql_error());
	$temp = mysql_fetch_assoc($data);
	$user_name = $temp['user_name'];
	$user_email = $temp['user_email'];
	$lastlogin = $temp['user_lastlogin'];

	$sessionDataRaw = $user_id . $user_name . $user_email . $lastlogin;
	$sessionData = md5($sessionDataRaw);
	return $sessionData;
}

/**Sets the cookie*/
function setAuth($user_id) {
	global $userId;
	$userId = $user_id;
	$_SESSION['userId'] = $userId;
	$_SESSION['data'] = getSessionData($user_id);
	return $user_id;
}

function checkCookieSupport() {
	if(isset($_COOKIE['PHPSESSID']) || $_COOKIE['cookie_support']=="enabled" ) {
		return true;
	} else
		return false;
}

function showCookieWarning() {
	global $cookieSupported;
	if($cookieSupported==false) {
		displayerror("Cookie support is required beyond this point. <a href=\"http://www.google.com/cookies.html\">Click here</a> to find out " .
				"how to enable cookies.");
		return true;
	}
	else
		return false;
}

function getUserId() {
	global $userId;
	return $userId;
}

/**Checks if cookie is authentic
 * if yes, updates it. ---> not required now after sessions. (session vars don't expire like individual cookies)
 * if no, resets it.
 * If not logged in, user id = 0
 */
function firstTimeGetUserId() {
	global $cookieSupported;
	if($cookieSupported) {
		if (isset ($_SESSION['userId'])) {
			$user_id = $_SESSION['userId'];
			$sessionData = getSessionData($user_id);
			if ($_SESSION['data'] == $sessionData) {
				if(!isset($_GET['fileget'])) {
					global $cookie_timeout,$cookie_path;
					setcookie('PHPSESSID',$_COOKIE['PHPSESSID'],time()+$cookie_timeout, $cookie_path);
				}
				return $user_id;
			}
			else
				resetAuth();
			return 0;
		} else
			resetAuth();
		return 0;
	} else
		resetAuth();
	return 0;
}

/** To get the groups a user belongs to
* If not logged in, group id = 0
* If logged in, one definite group id = 1
*/
function getGroupIds($userId) {
	$groups = array (
		0
	);
	if ($userId == 0)
		return $groups;
	else
		$groups[] = 1;
	$usergroupTable = MYSQL_DATABASE_PREFIX . "usergroup";
	$groupsTable = MYSQL_DATABASE_PREFIX . "groups";
	$groupQuery =
			"SELECT `$usergroupTable`.`group_id` FROM `$usergroupTable`, `$groupsTable` " .
			"WHERE `$usergroupTable`.`user_id` = $userId AND `$usergroupTable`.`group_id` = `$groupsTable`.`group_id` " .
			"ORDER BY `$groupsTable`.`group_priority`";
	$groupQueryResult = mysql_query($groupQuery) or die(mysql_error());
	while ($groupQueryResultRow = mysql_fetch_row($groupQueryResult))
		$groups[] = $groupQueryResultRow[0];
	return $groups;
}

/**Resets cookie info */
function resetAuth() {
	global $userId;
	unset($_SESSION['userId']);
	unset($_SESSION['data']);
	$userId = 0;
	return $userId;
}

?>
