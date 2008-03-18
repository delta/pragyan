<?php
/*
 * Created on Sep 25, 2007
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
/**
 * Error reporting level -
 * 0 - Turn off all error reporting
 * 1 - Report simple running errors
 * 2 - Reporting E_NOTICE can be good too (to report uninitialized variables or catch variable name misspellings ...)
 * 3 - Report all errors except E_NOTICE, This is the default value set in php.ini
 * 4 - Report all PHP errors (bitwise 63 may be used in PHP 3)
 */

$error_level = 0;
switch($error_level) {
	case 0 : $error_text = 0; break;
	case 1 : $error_text = E_ERROR | E_WARNING | E_PARSE; break;
	case 2 : $error_text = E_ERROR | E_WARNING | E_PARSE | E_NOTICE; $debugSet=on; break;
	case 3 : $error_text = E_ALL ^ E_NOTICE; $debugSet=on; break;
	case 4 : $error_text = E_ALL; $debugSet=on; break;
}
ini_set('error_reporting', $error_text);
ini_set('display_errors', 1);

/**
 * Settings for sessions :
 */
 // we deal with cookies her because the session id variable is stored in a client cookie
 ini_set("use_cookies",1);
 ini_set("use_only_cookies",1);

// path for session cookies
$cookie_path = "/";

// timeout value for the cookie
$cookie_timeout = 60 * 150; // in seconds//60 * 30

// timeout value for the garbage collector
//   we add 300 seconds, just in case the user's computer clock
//   was synchronized meanwhile; 300 secs (5 minutes) should be
//   enough - just to ensure there is session data until the
//   cookie expires
$garbage_timeout = $cookie_timeout + 300; // in seconds //300

// set the PHP session id (PHPSESSID) cookie to a custom value
ini_set('session.name',"PHPSESSID");
session_set_cookie_params($cookie_timeout, $cookie_path);

// set the garbage collector - who will clean the session files -
//   to our custom timeout
ini_set('session.gc_maxlifetime', $garbage_timeout);
/*ini_set('session.gc_probability',1); //defaults to 1
 *ini_set('session.gc_divisor',2); //defaults to 100
 * gc_probability / gc_divisor gives probability of the garbage collector
 * being started
 */
// we need a distinct directory for the session files,
//   otherwise another garbage collector with a lower gc_maxlifetime
//   will clean our files aswell - but in an own directory, we only
//   clean sessions with our "own" garbage collector (which has a
//   custom timeout/maxlifetime set each time one of our scripts is
//   executed)
$sessdir = "./".$sourceFolder."/".$uploadFolder."/sessions";
if (!is_dir($sessdir)) { mkdir($sessdir, 0777); }
ini_set('session.save_path', $sessdir);

// now we're ready to start the session
//Settings for sessions end

// cms title prefix
define("CMS_TITLE","Pragyan CMS");

// cms title prefix
/* -> default, prag08, prag08V2*/
define("TEMPLATE","prag08V2-black");

// set to "on" or "off"
define("DEBUG_MODE","off");

// defining the ip address of the mysql server.
define("MYSQL_SERVER","10.0.0.126");

// defining the username to connect to the database.
define("MYSQL_USERNAME","pragyan");

// defining the password used to connect to the database.
define("MYSQL_PASSWORD","username");

// defining the name of the database to connect to.
/* pragyan_v2 pragyan08*/
define("MYSQL_DATABASE","password");

define("MYSQL_DATABASE_PREFIX","pragyanV2_");

//define("MDP","pragyanV2_");//commented out by anshu. Please do not put abbrevated variables.

?>