<?php
if(!defined('__PRAGYAN_CMS'))
{ 
	header($_SERVER['SERVER_PROTOCOL'].' 403 Forbidden');
	echo "<h1>403 Forbidden<h1><h4>You are not authorized to access the page.</h4>";
	echo '<hr/>'.$_SERVER['SERVER_SIGNATURE'];
	exit(1);
}
/*
 * Created on Sep 28, 2008
 * Updated on May 23, 2010 by Abhishek
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */


$configFileText = <<<CONFIGFILETEXT
<?php
/**
 * @package pragyan
 * @copyright (c) 2010 Pragyan Team
 * @license http://www.gnu.org/licenses/ GNU Public License
 * For more details, see README
 */

/*******MYSQL SETTINGS************************/
// defining the ip address of the mysql server.
define("MYSQL_SERVER","$MYSQL_SERVER");

// defining the username to connect to the database.
define("MYSQL_USERNAME","$MYSQL_USERNAME");

// defining the password used to connect to the database.
define("MYSQL_PASSWORD","$MYSQL_PASSWORD");

// defining the name of the database to connect to.
define("MYSQL_DATABASE","$MYSQL_DATABASE");

// defining the prefix which is appended to every table of Pragyan CMS.
// this feature allows you to have multiple websites using the same database.
define("MYSQL_DATABASE_PREFIX","$MYSQL_DATABASE_PREFIX");

// defining the user id of the administrator. WARNING: Only experts should alter this.
define("ADMIN_USERID",1);

/*******CONFIGURATION SETTINGS************************/
/**
 * Error reporting level -
 * 0 - Turn off all error reporting
 * 1 - Report simple running errors
 * 2 - Reporting E_NOTICE can be good too (to report uninitialized variables or catch variable name misspellings ...)
 * 3 - Report all errors except E_NOTICE, This is the default value set in php.ini
 * 4 - Report all PHP errors (bitwise 63 may be used in PHP 3)
 * 5 - Report all PHP errors (bitwise 63 may be used in PHP 3) + Pragyan CMS Debugging Mode
 */
\$error_level = $error_level;
switch(\$error_level) {
	case 0 : \$error_text = 0; break;
	case 1 : \$error_text = E_ERROR | E_WARNING | E_PARSE; break;
	case 2 : \$error_text = E_ERROR | E_WARNING | E_PARSE | E_NOTICE; break;
	case 3 : \$error_text = E_ALL ^ E_NOTICE;break;
	case 4 : \$error_text = E_ALL; break;
	case 5 : \$error_text = E_ALL; \$debugSet='on'; break;
}
ini_set('error_reporting', \$error_text);
ini_set('display_errors', 0);

//The language folder to be imported
define("LANGUAGE","en");

/*****MAIL MESSAGES SETTINGS************************************/

define("MAILPATH","./cms/languages");

// By default .cms/languages/en must exist

/*****AUTHENTICATION SETTINGS**************************/

//IMAP settings
\$authmethods['imap']['status']=$AUTH_IMAP_STATUS;
\$authmethods['imap']['server_address']="$AUTH_IMAP_SERVER";
\$authmethods['imap']['port']="$AUTH_IMAP_PORT";
\$authmethods['imap']['user_domain']="$AUTH_IMAP_DOMAIN"; // i.e. user must login with username@nitt.edu
//LDAP settings
\$authmethods['ldap']['status']=$AUTH_LDAP_STATUS;
\$authmethods['ldap']['server_address']="$AUTH_LDAP_SERVER";
\$authmethods['ldap']['search_group']="$AUTH_LDAP_SEARCHGROUP";
\$authmethods['ldap']['user_domain']="$AUTH_LDAP_DOMAIN";
//ADS settings
\$authmethods['ads']['status']=$AUTH_ADS_STATUS;
\$authmethods['ads']['server_address']="$AUTH_ADS_SERVER";
\$authmethods['ads']['network_name']="$AUTH_ADS_NETWORK";
\$authmethods['ads']['user_domain']="$AUTH_ADS_DOMAIN";

/*****SESSION SETTINGS*********************************/
if(!defined('CMS_SETUP')) {
 // we deal with cookies here because the session id variable is stored in a client cookie
 ini_set("use_cookies",1);
 ini_set("use_only_cookies",1);

// path for session cookies
\$cookie_path = "/";

// timeout value for the cookie
\$cookie_timeout = $cookie_timeout; // in seconds//60 * 30

// timeout value for the garbage collector
//   we add 300 seconds, just in case the user's computer clock
//   was synchronized meanwhile; 300 secs (5 minutes) should be
//   enough - just to ensure there is session data until the
//   cookie expires
\$garbage_timeout = \$cookie_timeout + 300; // in seconds //300

// set the PHP session id (PHPSESSID) cookie to a custom value
ini_set('session.name',"PHPSESSID");
session_set_cookie_params(\$cookie_timeout, \$cookie_path);

// set the garbage collector - who will clean the session files -
//   to our custom timeout
ini_set('session.gc_maxlifetime', \$garbage_timeout);
/*ini_set('session.gc_probability',1); //defaults to 1
 *ini_set('session.gc_divisor',2); //defaults to 100
 * gc_probability / gc_divisor gives probability of the garbage collector
 * being started
 */
// we need a distinct directory for the session files,
//   otherwise another garbage collector with a lower gc_maxlifetime
//   will clean our files aswell - but in our own directory, we only
//   clean sessions with our "own" garbage collector (which has a
//   custom timeout/maxlifetime set each time one of our scripts is
//   executed)
\$sessdir = \$sourceFolder."/".\$uploadFolder."/sessions";
if (!is_dir(\$sessdir)) { mkdir(\$sessdir, 0777); }
ini_set('session.save_path', \$sessdir);

}
?>
CONFIGFILETEXT;

$searchConfigFileText =<<<SEARCHCONFIGFILETEXT
<?php
	\$database="$MYSQL_DATABASE";
	\$mysql_user = "$MYSQL_USERNAME";
	\$mysql_password = "$MYSQL_PASSWORD"; 
	\$mysql_host = "$MYSQL_SERVER";
	\$mysql_table_prefix = ""; // This doesn't work in sphider



	\$success = mysql_pconnect (\$mysql_host, \$mysql_user, \$mysql_password);
	if (!\$success)
		die ("<b>Cannot connect to database, check if username, password and host are correct.</b>");
    \$success = mysql_select_db (\$database);
	if (!\$success) {
		print "<b>Cannot choose database, check if database name is correct.";
		die();
	}
?>
SEARCHCONFIGFILETEXT;

?>
