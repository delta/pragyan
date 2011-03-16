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
 * @author Sahil Ahuja
 * @copyright (c) 2010 Pragyan Team
 * @license http://www.gnu.org/licenses/ GNU Public License
 * For more details, see README
 */

/**
 *
 * Returns the encrypted session data of the user
 *
 * @param $user_id The user whose session data is required
 *
 * @return @sessionData The required session data, but in an encrypted form.
 *
 */
function getSessionData($user_id) {
	$user_id=escape($user_id);
	$query = "SELECT `user_name`,`user_email`,`user_lastlogin` FROM `" . MYSQL_DATABASE_PREFIX . "users` WHERE `user_id`='$user_id'";
	$data = mysql_query($query) or die(mysql_error());
	$temp = mysql_fetch_assoc($data);
	$user_name = $temp['user_name'];
	$user_email = $temp['user_email'];
	$lastlogin = $temp['user_lastlogin'];

	$sessionDataRaw = $user_id . $user_name . $user_email . $lastlogin;
	$sessionData = md5($sessionDataRaw);
	return $sessionData;
}

/**
 *
 * Sets the cookie and overwrites browser's cache of login information
 *
 * @param $user_id The user whose session is being set.
 *
 * @return $user_id 
 *
 */
function setAuth($user_id) {
	global $userId;
	$userId = $user_id;
	$_SESSION['userId'] = $userId;
	$_SESSION['data'] = getSessionData($user_id);
	header("location: ".$_SERVER["REQUEST_URI"]); // This is important to make sure that the login form is not resubmitted on clicking BACK
	return $user_id;
}

/**
 *
 * Check if the browser offers support for the server to set cookie.
 *
 * @return true is the server is able to set a cookie and false otherwise. 
 *
 */
function checkCookieSupport() {
	if(isset($_COOKIE['PHPSESSID']) || (isset($_COOKIE['cookie_support']) && $_COOKIE['cookie_support']=="enabled") ) {
		return true;
	} else
		return false;
}

/**
 *
 * Display warning and help regarding cookies as cookie is required for the proper working of the site.
 *
 * @return true if displayis successful and false otherwise.
 *
 */
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
 *
 *
 *
 */
function getGroupIds($userId) {
	$groups = array (
		0
	);
	if ($userId == 0)
		return $groups;
	else
		$groups[] = 1;
	$groupQuery = 'SELECT `group_id` FROM `' . MYSQL_DATABASE_PREFIX . 'usergroup` WHERE `user_id` = \'' . escape($userId)."'";
	$groupQueryResult = mysql_query($groupQuery) or die(mysql_error());
	while ($groupQueryResultRow = mysql_fetch_row($groupQueryResult))
		$groups[] = $groupQueryResultRow[0];
	return $groups;
}

/**Resets cookie info */
function resetAuth() {
	global $userId;
	if(isset($_SESSION))
	{
		unset($_SESSION['userId']);
		unset($_SESSION['data']);
		unset($_SESSION['forum_lastVisit']);
	}
	$userId = 0;
	return $userId;
}

/******** auth FUNCTIONS TO BE USED IN login.lib.php ***********/

function checkLogin($login_method,$user_name,$user_email,$user_passwd) {
  $login_status=false;
  global $authmethods;
  switch($login_method)	//get his login method, and chk credentials
    {
    case 'ads':
      if($authmethods[$login_method]['status'])
	$login_status = my_ads_auth($user_name, $user_passwd);
      break;
    case 'imap':
      if($authmethods[$login_method]['status'])
	{
	  $pos=strpos($user_email,'@');
	  $user_name1=substr($user_email,0,$pos);
	  //					displayinfo($user_name1,$user_passwd);
	  $login_status = my_imap_auth($user_name1, $user_passwd);

	}
      break;
    case 'ldap':
      if($authmethods[$login_method]['status'])
	$login_status = my_ldap_auth($user_name, $user_passwd);
      break;
      ///this prevents any OpenID dummy users (those which have login_method=openid) to  use conventional login method
    case 'openid':
      $login_status=False;
      break;
    default:
      $temp = getUserInfo($user_email);
      if(md5($user_passwd)==$temp['user_password']) {
	$login_status = true;
      }
    }

  return $login_status;

}

/***FUNCTIONS FOR IMAP AUTH: ***/
function quoteIMAP($str)
{
  return preg_replace('/'.addcslashes("([\"\\])",'/').'/', "\\1", $str);
}

function my_imap_auth ($username, $password)
{
	global $authmethods;
	if(!isset($authmethods['imap']['server_address']) || !isset($authmethods['imap']['port']))
		displayerror("Please specify IMAP authentication settings completely");

	$imap_server_address=$authmethods['imap']['server_address'];
	$imap_port=$authmethods['imap']['port'];
	  $imap_stream = fsockopen($imap_server_address,$imap_port);
	  if ( !$imap_stream ) {
	    return false;
	  }
	  $server_info = fgets ($imap_stream, 1024);

	  $query = 'b221 ' .  'LOGIN "' . quoteIMAP($username) .  '" "'  .quoteIMAP($password) . "\"\r\n";
	  $read = fputs ($imap_stream, $query);

	  $response = fgets ($imap_stream, 1024);
	  $query = 'b222 ' . 'LOGOUT';
	  $read = fputs ($imap_stream, $query);
	  fclose($imap_stream);

	  strtok($response, " ");
	  $result = strtok(" ");

	  if($result == "OK")
			return TRUE;
	  else
	    return FALSE;
}

/**FUNCTIONS FOR LDAP AUTH:***/
function my_ldap_auth($uid,$passwd) {
	global $authmethods;
	if(!isset($authmethods['ldap']['server_address']) || !isset($authmethods['ldap']['search_group']))
  		displayerror("Please specify LDAP authentication settings completely");

	$ds=@ldap_connect($authmethods['ldap']['server_address']);
	@ldap_bind($ds);
	$dn=get_dn($uid,$ds);
	@ldap_unbind($ds);
	$ds=@ldap_connect($authmethods['ldap']['server_address']);
	if($dn!=false && ldap_bind($ds,$dn,$passwd) && $passwd!='')
		return TRUE;
	else
		return FALSE;
}

function get_dn($uid,$ds) {
	$info=@search_user($uid,$ds);
	if ($info['count'] == 1)
		return $info[0]['dn'];
	else
		return false;
}

function search_user($uid,$ds) {
	global $authmethods;
	  $sr=@ldap_search($ds, $authmethods['ldap']['search_group'], "uid=$uid");
	  $info = @ldap_get_entries($ds, $sr);
	  return $info;
}


/**FUNCTION FOR ADS AUTH:***/
function my_ads_auth ($username, $password) {
	global $authmethods;
	if(!isset($authmethods['ads']['server_address']) || !isset($authmethods['ads']['network_name']))
		displayerror("Please specify ADS authentication settings completely");

  $ldapconn=@ldap_connect($authmethods['ads']['server_address']);
  if($ldapconn) {
      $ldap_bind=@ldap_bind($ldapconn, $authmethods['ads']['network_name'].$username, $password);
    }
  if($ldap_bind && $password!='')
  	return TRUE;
  else
    return FALSE;
}


