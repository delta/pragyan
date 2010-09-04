<?php
/**
 * @package pragyan
 * @copyright (c) 2008 Pragyan Team
 * @license http://www.gnu.org/licenses/ GNU Public License
 * For more details, see README
 */

global $sourceFolder;
$sourceFolder = 'cms';
$installFolder = '.';
$cmsFolder = "../$sourceFolder";
$templateFolder = "$cmsFolder/templates/crystalx";

require_once($cmsFolder."/common.lib.php");
define('CMS_SETUP', true);


$installPageNumber = 2;
$prerequisiteText = CheckPrerequisites();
if ($prerequisiteText != '')
	$installPageNumber = 1;
elseif (isset($_POST['btnSubmitSettings']))
	$installPageNumber = 3;

if ($installPageNumber == 1) {
	$installPageContent = $prerequisiteText;
}
else if ($installPageNumber == 2) {
	include_once('settingsform.php');
	$installPageContent = $settingsForm;
			
}
else if ($installPageNumber == 3) {
	$successImage = "<img src=\"$installFolder/images/instsuccess.png\" alt=\"Successful\" />";
	$errorImage = "<img src=\"$installFolder/images/insterror.png\" alt=\"Aborted\" />";
	$installationProgress = installCMS();
	$installationErrors = '';
	$installPageContent = '';
	for ($i = 0; $i < count($installationProgress); ++$i) {
		$installPageContent .= "<tr><td width=\"100%\">{$installationProgress[$i][1]}</td><td>";
		if ($installationProgress[$i][2] !== true) {
			$installPageContent .= $errorImage;
			if (isset($installationProgress[$i][3]))
				$installationErrors = $installationProgress[$i][3] . "<a href=\"javascript: history.back()\">Click here</a> to go back.</a>";
		}
		else
			$installPageContent .= $successImage;
		$installPageContent .= "</td></tr>\n";
	}

	$scriptPathWithFolder = substr($_SERVER['SCRIPT_FILENAME'], 0, strrpos($_SERVER['SCRIPT_FILENAME'], '/'));
	$scriptPath = substr($scriptPathWithFolder , 0, strrpos($scriptPathWithFolder , '/'));

	$installPageContent = "<br /><table width=\"100%\" border=\"0\">\n $installPageContent </table>\n $installationErrors";
	if ($installationErrors == '') {
		$installPageContent .= <<<HTTPDCONF
			For Pragyan CMS to work, .htaccess needs to be supported your webserver. <br />
			For this, the <b>AllowOverride</b> setting in the httpd.conf has to be made <i>Options FileInfo Limit</i> under the relevant <mono>&lt;Directory&gt;</mono> section.<br />
			The default location of httpd.conf is <mono>/etc/httpd/conf/httpd.conf</mono>, but may be different for you according to your installation.
			<br /><br />
			Add the following lines in the httpd.conf of your webserver :
			<pre><xmp>
				<Directory "$scriptPath">
					AllowOverride All
				</Directory>
			</xmp></pre>
			<p>If you have done this, <a href="../">click here</a> to go to the CMS.</p>
HTTPDCONF;
	}
}

include_once('template.php');


/**
 * Install the CMS
 * @return array A list of steps that were carried out, with a field against each step indicating whether it succeeded or failed.
 */
function installCMS() {
	$installationSteps = 
			array(
					array('saveConfigurationSettings', 'Saving Configuration Settings', false),
					array('checkDatabaseAccess', 'Checking Database Access', false),
					array('importDatabase', 'Importing Database', false),
					array('saveHtaccess', 'Checking .htaccess Settings', false)
					//array('indexSite', 'Indexing site', false)
			);

	for ($i = 0; $i < count($installationSteps); ++$i) {
		$installationProcedure = $installationSteps[$i][0];
		$stepResult = $installationProcedure();
		if ($stepResult != '') {
			$installationSteps[$i][] = $stepResult;
			return $installationSteps;
		}
		$installationSteps[$i][2] = true;
	}
	return $installationSteps;
}
/*
function indexSite() {
	global $cmsFolder;
	include("$cmsFolder/modules/search/admin/spider.php");
	$serveruri=$_SERVER['REQUEST_URI'];
	$uri=substr($serveruri,0,stripos($serveruri,"INSTALL/install.php"));
	$site = "http://" . $_SERVER['HTTP_HOST'] . $uri . "home/";
	index_site($site, 0, -1, 'full', "", "+\n&", 0);
	return '';
}
*/

/**
 * Save configuration settings submitted from the form.
 * @return bool Boolean value indicating whether the method was successful.
 */
function saveConfigurationSettings() {
	$configurationMap = array(
			'txtMySQLServerHost' => 'MYSQL_SERVER',
			'txtMySQLUsername' => 'MYSQL_USERNAME',
			'txtMySQLPassword' => 'MYSQL_PASSWORD',
			'txtMySQLDatabase' => 'MYSQL_DATABASE',
			'txtMySQLTablePrefix' => 'MYSQL_DATABASE_PREFIX',
			'txtAdminUsername' => 'ADMIN_USERNAME',
			'txtAdminEmail' => 'ADMIN_EMAIL',
			'txtAdminFullname' => 'ADMIN_FULLNAME',
			'txtAdminPassword' => 'ADMIN_PASSWORD',
			'optSendVerification' => 'SEND_MAIL_ON_REGISTRATION',
			'optDefaultUserActive' => 'DEFAULT_USER_ACTIVATE',
			'txtCMSMailId' => 'CMS_EMAIL',
			'txtCMSTitle' => 'CMS_TITLE',
			'selTemplate' => 'CMS_TEMPLATE',
			'txtUploadLimit' => 'UPLOAD_LIMIT',
			'txtCookieTimeout' => 'cookie_timeout',
			'selErrorReporting' => 'error_level',
			'optEnableIMAP' => 'AUTH_IMAP_STATUS',
			'txtIMAPServerAddress' => 'AUTH_IMAP_SERVER',
			'txtIMAPPort' => 'AUTH_IMAP_PORT',
			'txtIMAPUserDomain' => 'AUTH_IMAP_DOMAIN',
			'optEnableLDAP' => 'AUTH_LDAP_STATUS',
			'txtLDAPServerAddress' => 'AUTH_LDAP_SERVER',
			'txtLDAPSearchGroup' => 'AUTH_LDAP_SEARCHGROUP',
			'txtLDAPUserDomain' => 'AUTH_LDAP_DOMAIN',
			'optEnableADS' => 'AUTH_ADS_STATUS',
			'txtADSServerAddress' => 'AUTH_ADS_SERVER',
			'txtADSNetworkName' => 'AUTH_ADS_NETWORK',
			'txtADSUserDomain' => 'AUTH_ADS_DOMAIN',
			'txtMySQLServerPort' => 'MYSQL_PORT'
	);
	
	foreach ($configurationMap as $postVariableName => $configVariableName) {
		if (substr($postVariableName, 0, 3) == "opt") {
			${$configVariableName} = (isset($_POST[$postVariableName]) && $_POST[$postVariableName] == "Yes") ? 'true' : 'false';
		}
		else {
			${$configVariableName} = isset($_POST[$postVariableName]) ? $_POST[$postVariableName] : '';
		}
			
	}
	if($MYSQL_PORT!="") $MYSQL_SERVER.=":$MYSQL_PORT";
	
	global $cmsFolder;

	$configFileText = '';
	require_once('config.inc-dist.php');
	$writeHandle = @fopen("$cmsFolder/config.inc.php", 'w');
	if (!$writeHandle)
		return 'Could not write to config.inc.php. Please make sure that the file is writable.';

	fwrite($writeHandle, $configFileText);
	fclose($writeHandle);
	
	$writeHandle = @fopen("$cmsFolder/modules/search/settings/database.php",'w');
	if(!$writeHandle)
		return "Could not write to $cmsFolder/modules/search/settings/database.php. Please make sure that the file is writable.";
	
	fwrite($writeHandle, $searchConfigFileText);
	fclose($writeHandle);

	$c = 0;
	foreach ($configurationMap as $postVariableName => $configVariableName) {

		define ($configVariableName, ${$configVariableName});
		if (++$c == 15)
			break;
	}
	
	return '';
}

function checkDatabaseAccess() {
	$dbaccessInfo = '';
	$dbaccessErrorTip = <<<WHATEVER
			<p>To create a database and a user with all priviliges to that database, run the following queries after replacing <b>pragyandatabase</b>, <b>localhost</b>, <b>pragyanuser</b> and <b>pragyanpassword</b> as required. </p>
			<pre>CREATE DATABASE `pragyandatabase`;
CREATE USER 'pragyanuser'@'localhost' IDENTIFIED BY 'pragyanpassword';
GRANT ALL PRIVILEGES ON `pragyan\_v3` . * TO 'pragyanuser'@'localhost';</pre>
			<p>After you run these queries successfully in your MySQL client, please run this install script again.</p>
WHATEVER;

	$dbhost=MYSQL_SERVER;
	$dbname=MYSQL_DATABASE;

	$dbuser=MYSQL_USERNAME;
	$dbpasswd=MYSQL_PASSWORD;
	$dblink=mysql_connect($dbhost,$dbuser,$dbpasswd);
	if($dblink==false)
	{
		$dbaccessInfo.="<p><b>Error:</b> Pragyan CMS could not connect to database on '$dbhost' using username '$dbuser'. Please check the username/password you provided.</p>$dbpasswd";
		return $dbaccessInfo . $dbaccessErrorTip;
	}
	$db=mysql_select_db($dbname);
	if($db==false)
	{
		$dbaccessInfo.="<p><b>Error:</b> Pragyan CMS could not select the database '$dbname'.<br/> Please make sure the database exists and the user $dbuser has permissions over it.</p>";
			return $dbaccessInfo . $dbaccessErrorTip;
	}
	$res=mysql_query("CREATE TABLE IF NOT EXISTS `testtable948823` ( `testuserid` int(10) )");
	if($res==false)
	{
		$dbaccessInfo.="<p><b>Error:</b> The User '$dbuser' does not have permissions to CREATE tables in '$dbname'.</p>";
		return $dbaccessInfo . $dbaccessErrorTip;
	}
	$res=mysql_query("INSERT INTO `testtable948823` VALUES (123)");
	if($res==false)
	{
		$dbaccessInfo.="<p><b>Error:</b> The User '$dbuser' does not have permissions to INSERT values in tables of database '$dbname'.</p>";
		return $dbaccessInfo . $dbaccessErrorTip;
	}
	$res=mysql_query("UPDATE `testtable948823` SET testuserid=0");
	if($res==false)
	{
		$dbaccessInfo.="<p><b>Error:</b> The User '$dbuser' does not have permissions to UPDATE values in tables of database '$dbname'.</p>";
		return $dbaccessInfo . $dbaccessErrorTip;
	}
	$res=mysql_query("SELECT * FROM `testtable948823`");
	if($res==false)
	{
		$dbaccessInfo.="<p><b>Error:</b> The User '$dbuser' does not have permissions to SELECT values in tables of database '$dbname'.</p>";
		return $dbaccessInfo . $dbaccessErrorTip;
	}
	$res=mysql_query("DROP TABLE `testtable948823`");
	if($res==false)
	{
		$dbaccessInfo.="<p><b>Error:</b> The User '$dbuser' does not have permissions to DROP tables of database '$dbname'.</p>";
		return $dbaccessInfo . $dbaccessErrorTip;
	}
	return '';
}

function importDatabase() {
	global $installFolder;

	mysql_connect(MYSQL_SERVER, MYSQL_USERNAME, MYSQL_PASSWORD);
	mysql_select_db(MYSQL_DATABASE);

	$handle = @fopen($installFolder."/pragyan_structure.sql", "r");
	$query = '';
	if ($handle) {
	  while (!feof($handle)) {
	    $buffer = fgets($handle, 4096);
	    if (strpos($buffer,"--") !== 0)
	      $query.=$buffer;
	  }
	  fclose($handle);
	}
	$query = str_replace("pragyanV3_",MYSQL_DATABASE_PREFIX,$query);
	$singlequeries = explode(";\n",$query);
	foreach ($singlequeries as $singlequery) {
		if (trim($singlequery)!="") {
			$result1 = mysql_query($singlequery);
			if (!$result1) {
				$output = "<h3>Error:</h3><pre>".$singlequery."</pre>\n<br/>Unable to create structure. ".mysql_error();
				return $output;
			}
		}
	}
	$error = include 'searchStructure.php';
	if($error != '')
		return $error;
	$handle = @fopen($installFolder."/pragyan_inserts.sql", "r");
	if ($handle) {
		while (!feof($handle)) {
			$buffer = fgets($handle, 4096);
			if (strpos($buffer,"--")!==0)
				$query.=$buffer;
		}
		fclose($handle);
	}
	$query = str_replace("pragyanV3_",MYSQL_DATABASE_PREFIX,$query);
	$singlequeries = explode(";\n",$query);
	foreach ($singlequeries as $singlequery) {
		if (trim($singlequery)!="") {
			$result1 = mysql_query($singlequery);
			if (!$result1) {
  			$output = "<h3>Error:</h3><pre>".$singlequery."</pre>\n<br/>Unable to import the rows. " . mysql_error();
  			return $output;
			}
		}
	}
	$DEFAULT_USER_ACTIVATE=(DEFAULT_USER_ACTIVATE=="true"?1:0);
	$SEND_MAIL_ON_REGISTRATION=(SEND_MAIL_ON_REGISTRATION=="true"?1:0);
	
	setGlobalSettingByAttribute("cms_title",CMS_TITLE);
	setGlobalSettingByAttribute("cms_email",CMS_EMAIL);
	setGlobalSettingByAttribute("default_template",CMS_TEMPLATE);
	setGlobalSettingByAttribute("default_user_activate",$DEFAULT_USER_ACTIVATE);
	setGlobalSettingByAttribute("default_mail_verify",$SEND_MAIL_ON_REGISTRATION);
	setGlobalSettingByAttribute("upload_limit",UPLOAD_LIMIT);
	setGlobalSettingByAttribute("cms_desc",CMS_TITLE);
	setGlobalSettingByAttribute("cms_keywords",CMS_TITLE);
	setGlobalSettingByAttribute("reindex_frequency","2");
	setGlobalSettingByAttribute("allow_login","1");
	setGlobalSettingByAttribute("cms_footer","&copy; 2010 - powered by <a href=\"http://sourceforge.net/projects/pragyan\" title=\"Praygan CMS\">Pragyan CMS v3.0</a>");
	
	$query="INSERT IGNORE INTO `".MYSQL_DATABASE_PREFIX."users` (`user_id`,`user_name`,`user_email`,`user_fullname`,`user_password`,`user_regdate`,`user_lastlogin`,`user_activated`,`user_loginmethod`) VALUES (
	1,'".ADMIN_USERNAME."','".ADMIN_EMAIL."','".ADMIN_FULLNAME."','".md5(ADMIN_PASSWORD)."',NOW(),'',1,'db')";
	mysql_query($query);
	global $cmsFolder;
	$templates=scandir($cmsFolder.'/templates');

	foreach($templates as $tdir)
	{

		if(is_dir($cmsFolder.'/templates/'.$tdir) && $tdir[0]!='.' && $tdir!="common")
		{

			$query="INSERT IGNORE INTO `".MYSQL_DATABASE_PREFIX."templates` (`template_name`) VALUES ('$tdir')";
			mysql_query($query);
		}
	}
	
	return '';
}

/**
 * Checks prerequisites.
 * @return string A string with a report of the problems, if any.
 */
function CheckPrerequisites() {
	global $sourceFolder;
	$cmsfolder = "./../$sourceFolder";
	if(is_dir($cmsfolder."/uploads"))
	{
		$testFolder=@fopen($cmsfolder."/uploads/testperms", 'w');
		if(!$testFolder)
		{
			$prereq="<li>Please check the permissions of the <b>$sourceFolder/uploads/</b> folder. It should be writable by your webserver user<br>";
			$prereq.="On a <i>linux</i> server, after going inside the $sourceFolder folder run the following commands as root<br>";
			$prereq.="<pre>chown -R &lt;httpd-process-user&gt; uploads</pre>";
			$prereq.="<b>OR</b><br /><pre>chmod -R 777 uploads</pre></li>";
			$prereq.="<br>NOTE: &lt;httpd-process-user&gt; is the default user for your webserver process. In most cases, it is 'www-data' or 'apache'.";
		}
		else {
			fclose($testFolder);
			unlink("$cmsfolder/uploads/testperms");
			$prereq = '';
		}
	}
	else
	{
			$prereq="<li>Kindly create the <b>$sourceFolder/uploads</b> folder. It should be writable by your webserver user<br>";
		 	$prereq.="On a <i>linux</i> server,after going inside the $sourceFolder run the following commands as root<br>";
		 	$prereq.="<pre>mkdir uploads</pre>";
		 	$prereq.="<pre>chown -R <httpd process user> uploads;</pre>";
		 	$prereq.="<b>OR</b><br /><pre>chmod -R 777 uploads</pre></li>";
	}
	
	
	$testFolder=@fopen($cmsfolder."/templates/testperms", 'w');
	if(!$testFolder)
	{
		$prereq.="<li>Please check the permissions of the <b>$sourceFolder/templates/</b> folder. It should be writable by your webserver user<br>";
		$prereq.="On a <i>linux</i> server, after going inside the $sourceFolder folder run the following commands as root<br>";
		$prereq.="<pre>chown -R &lt;httpd-process-user&gt; templates</pre>";
		$prereq.="<b>OR</b><br /><pre>chmod -R 777 templates</pre></li>";
		$prereq.="<br>NOTE: &lt;httpd-process-user&gt; is the default user for your webserver process. In most cases, it is 'www-data' or 'apache'.";
	}
	else {
		fclose($testFolder);
		unlink("$cmsfolder/templates/testperms");
		
	}
	
	$testFolder=@fopen($cmsfolder."/languages/testperms", 'w');
	if(!$testFolder)
	{
		$prereq.="<li>Please check the permissions of the <b>$sourceFolder/languages/</b> folder. It should be writable by your webserver user<br>";
		$prereq.="On a <i>linux</i> server, after going inside the $sourceFolder folder run the following commands as root<br>";
		$prereq.="<pre>chown -R &lt;httpd-process-user&gt; languages</pre>";
		$prereq.="<b>OR</b><br /><pre>chmod -R 777 languages</pre></li>";
		$prereq.="<br>NOTE: &lt;httpd-process-user&gt; is the default user for your webserver process. In most cases, it is 'www-data' or 'apache'.";
	}
	else {
		fclose($testFolder);
		unlink("$cmsfolder/languages/testperms");
		
	}
	
	
	

	if (!is_writable('../.htaccess')) {
		$prereq .= <<<HTACCESS
		<li>
			<p>Please make sure that the file named .htaccess in the Pragyan CMS root directory has write permissions during the install process.</p>
			<p>You can change permissions back to the way it was, after the installation completes.</p>
		</li>
HTACCESS;
	}

	if (!is_writable("../$sourceFolder/config.inc.php")) {
		$prereq .= <<<CONFIGFILE
		<li>
			<p>Please make sure that the file named config.inc.php in the Pragyan CMS root directory has write permissions during the install process.</p>
			<p>You can change permissions back to the way it was, after the installation completes.</p>
		</li>
CONFIGFILE;
	}
	
	if (!is_writable("../$sourceFolder/modules/search/settings/database.php")) {
		$prereq .= <<<SEARCHCONFIGFILE
		<li>
			<p>Please make sure that the file named database.php in the cms/modules/search/settings/ directory has write permissions during the install process.</p>
			<p>You can change permissions back to the way it was, after the installation completes.</p>
		</li>
SEARCHCONFIGFILE;
	}

	if ($prereq != '') {
		$prereq = "<p>The following prerequisite(s) need to be resolved before Pragyan CMS can continue installation.</p>\n<ul>\n$prereq\n</ul>";
		$prereq .= '<p>Please make the necessary changes, and <a href="javascript: location.reload(true)">click here</a> to refresh this page.</p>';
	}

	clearstatcache();
	return $prereq;
}

/**
 * Save .htaccess
 */
function saveHtaccess() {
	$urlRequestRootWithFolder = substr($_SERVER['SCRIPT_NAME'], 0, strrpos($_SERVER['SCRIPT_NAME'], '/'));
	$urlRequestRoot = substr($urlRequestRootWithFolder , 0, strrpos($urlRequestRootWithFolder , '/'));
	$urlRequestRoot = ($urlRequestRoot==""?"/":$urlRequestRoot);
	$scriptPathWithFolder = substr($_SERVER['SCRIPT_FILENAME'], 0, strrpos($_SERVER['SCRIPT_FILENAME'], '/'));
	$scriptPath = substr($scriptPathWithFolder , 0, strrpos($scriptPathWithFolder , '/'));

	$errorMessage = <<<HTACCESSERROR
			<p>Could not save .htaccess file.</p>
			<p>Please ensure that the .htaccess file in the Pragyan CMS installation folder exists and is writable.</p>
			<p>You can change permissions back to the way it was, after the installation completes.</p>
			<p><a href="javascript: location.reload(true)">Click here</a> to refresh this page.</p>
HTACCESSERROR;

	$htaccessFile = "../.htaccess";
	$htaccessHandle = fopen($htaccessFile, 'r');
	if (!$htaccessHandle)
		return $errorMessage;

	$lines = file('../.htaccess');
	$htaccessHandle = fopen($htaccessFile, 'w');

	if (!$lines || !$htaccessHandle) {
		return $errorMessage;
	}

	for ($i = 0; $i < count($lines); ++$i) {
		if (trim($lines[$i]) == '#BASEDIRECTORY') {
			fwrite($htaccessHandle, "#BASEDIRECTORY\n");
			fwrite($htaccessHandle, "RewriteBase $urlRequestRoot\n");
			fwrite($htaccessHandle, "RewriteCond %{REQUEST_URI} ^$urlRequestRoot/\$\n");
			$i += 2;
		}
		if (trim($lines[$i]) == '#REWRITEENGINE') {
			fwrite($htaccessHandle, "#REWRITEENGINE\n");
			fwrite($htaccessHandle, "RewriteEngine On\n");
			$i += 1;
		}
		else
			fwrite($htaccessHandle, $lines[$i]);
	}
	fclose($htaccessHandle);

	return '';
}
