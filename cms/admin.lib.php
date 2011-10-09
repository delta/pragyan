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
 * @copyright (c) 2010 Pragyan Team
 * @license http://www.gnu.org/licenses/ GNU Public License
 * For more details, see README
 */

/**
 * First check if there is a "valid" user with verifyemail true having the admin permission.
 * If there is not, then make an enabled user with email=admin@cms.org and generate a password for him,
 * 		and echo (or return or show) the email and password.
 * If there is, and the current userId does not have permission for admin, return getContent(0,0,0,false);
 *
 * Then check if there is coressponding row in permlist for every module class action.
 * 		Also vice-versa check if there is function for every permsission in database.
 * Also check for page level permissions.
 *
 * Then check if the uploads directory is writable. If not give error.
 *
 * Then allow to change all perm_ranks.
 *
 *	function admin($userId) {
 }*/


	/*  Consistency check:
1) Check all available classes (modules) - to refine

2) See, if all their functions actionView(), actionEdit(), actionX exist
in the perms table or not (and create also). If
not, give option to create that permission. - to refine

3) See if any extra option exists in the database, if it does, warn the user. - to refine

4) See if any user with the name admin exists or not. If it does not,
create it and give it a random and display all required information, - to remove

5) See if the admin user has all perms at page 0 or not. If not, give him
all perms and inform him - to remove

6) See if all minimum rows n tables required for the cms to run exist or
not, if they do not, create them. - to remove

7) User management: List of all users, ability to edit everything about
them, ability to activate users, ability to create users - to refine

8) Ability to change perm ranks (like page move up and move ) - done
 *
 *
 * */

function globalSettingsForm()
{
	global $ICONS;
	$globalform=<<<globalform
	<style>
	#tabBar {
		display: none;
	}
	</style>
	<script type="text/javascript">
		total = 4;
		function showOption(num) {
			for(i=1;i<=total;i++)
			document.getElementById('globaloption'+i).style.display="none";
			document.getElementById('globaloption'+num).style.display="block";
		}
		window.onload=function() {
			for(i=1;i<=total;i++)
			document.getElementById('globaloption'+i).style.display="none";
			showOption(1);
			document.getElementById('tabBar').style.display="block";
		}
	</script>
	<fieldset>
	<legend>{$ICONS['Global Settings']['small']}Global Settings</legend>
	<div id="tabBar">
	<table style="width:100%">
	<tr>
	<td id="subaction" style="width:35%"><a onclick="showOption(1);"><Button>Website Information</Button></td>
	<td style="width:35%"><a onclick="showOption(2);"><Button>Template and Navigation</Button></td>
	<td style="width:35%"><a onclick="showOption(3);"><Button>Email and Registrations</Button></td>
	<td style="width:35%"><a onclick="showOption(4);"><Button>Security and Maintainence</Button></td>
	</tr>
	</table>
	</div>
globalform;
	return $globalform."<form method='POST' action='./+admin&subaction=global'><div id=\"globaloption1\">".websiteInfoSettingsForm()."</div><div id=\"globaloption2\">".templateSettingsForm()."</div><div id=\"globaloption3\">".registrationsSettingsForm()."</div><div id=\"globaloption4\">".securitySettingsForm()."</div><input type='hidden' name='update_global_settings' /><input type='submit' value='Update' /><input type='button' value='Cancel' onclick=\"window.open('./+view','_top')\" /></form></fieldset>";
}
	
function websiteInfoSettingsForm()
{
	global $pageFullPath;
	global $CMSTEMPLATE;
	global $urlRequestRoot,$templateFolder,$cmsFolder;
	$globals=getGlobalSettings();
	foreach($globals as $var=>$val) 
		$$var=$val;
	$globalform=<<<globalform
	<table style="width:100%">
	<tr>
	<td style="width:35%">Website Name :</td>
	<td style="width:65%"><input type="text" name='cms_title' value="$cms_title"></td>
	</tr>
	<tr>
	<td>Site Description :</td>
	<td><textarea style="width:98%" rows=10 cols=10 name='cms_desc' />$cms_desc</textarea></td>
	</tr>
	<tr>
	<td>Site Keywords (comma-separated) :</td>
	<td><input type="text" name='cms_keywords' value='$cms_keywords'></td>
	</tr>
	<tr>
	<td>Site Footer :</td>
	<td><textarea style="width:98%" rows=10 cols=10 name='cms_footer' />$cms_footer</textarea></td>
	</tr>
	</table>
globalform;
	return $globalform;
}
function templateSettingsForm()
{
global $pageFullPath;
	global $CMSTEMPLATE;
	global $urlRequestRoot,$templateFolder,$cmsFolder;
	$globals=getGlobalSettings();
	foreach($globals as $var=>$val) 
		$$var=$val;
	$templates = getAvailableTemplates();
	$allow_pagespecific_header=$allow_pagespecific_header==0?"":"checked";
	$allow_pagespecific_template=$allow_pagespecific_template==0?"":"checked";
	$allow_pageheadings_intitle=$allow_pageheadings_intitle==0?"":"checked";

	$globalform=<<<globalform
		<table style="width:100%">
		<tr>
		<td>Default template :</td>
		<td><select name='default_template'>
globalform;

	
	for($i=0; $i<count($templates); $i++)
	{
		if($templates[$i]==DEF_TEMPLATE)
		$globalform.="<option value='".$templates[$i]."' selected >".ucwords($templates[$i])."</option>";
		else
		$globalform.="<option value='".$templates[$i]."' >".ucwords($templates[$i])."</option>";
	}

	$globalform.=<<<globalform
	</select>
	</td>
	</tr>
	<tr>
	<td>Allow Page-specific Template ?</td>
	<td><input name='allow_page_template' type='checkbox' $allow_pagespecific_template></td>
	</tr>
	<tr>
	<td>Allow Page-specific Headers ?</td>
	<td><input name='allow_page_header' type='checkbox' $allow_pagespecific_header></td>
	</tr>
	<tr>
	<td>Allow Page Headings in Title ?</td>
	<td><input name='allow_pageheadings_intitle' type='checkbox' $allow_pageheadings_intitle></td>
	</tr>
	<tr>
	<td>Show Breadcrumbs Submenu ?</td>
	<td><input name='breadcrumb_submenu' type='checkbox' $breadcrumb_submenu></td>
	</tr>
	</table>
globalform;
return $globalform;
}

function registrationsSettingsForm()
{
global $pageFullPath;
	global $CMSTEMPLATE;
	global $urlRequestRoot,$templateFolder,$cmsFolder;
	$globals=getGlobalSettings();
	foreach($globals as $var=>$val) 
		$$var=$val;
$activate_useronreg=$default_user_activate==0?"":"checked";
$default_mailverify=$default_mail_verify==0?"":"checked";
$breadcrumb_submenu=$breadcrumb_submenu==0?"":"checked";
$allow_login=$allow_login==0?"":"checked";


$globalform=<<<globalform
	<table style="width:100%">
	<tr>
	<td>Send Mail on Registration ?</td>
	<td><input name='send_mail_on_reg' type='checkbox' $default_mailverify></td>
	</tr>
	<tr>
	<td>Website Email :</td>
	<td><input type="text" name='cms_email' value='$cms_email'></td>
	</tr>
	
	<tr>
	<td>Activate User On Registration ?</td>
	<td><input name='activate_useronreg' type='checkbox' $activate_useronreg></td>
	</tr>
	<tr>
	<td>Allow Users to Login/Register ?</td>
	<td><input name='allow_login' type='checkbox' $allow_login></td>
	</tr>
	<tr>
	<td>Notify Users about Form Deadline before (in days) ?</td>
	<td><input type="text" name='deadline_notify' value='$deadline_notify'></td>
	</tr>
	</table>
globalform;
return $globalform;
}

function getBlacklistTable()
{
	$black = "<fieldset><legend>Blacklisted Domains</legend><table><tr><td style='width:35%'>Domains</td><td style='width:65%'>IPs</td><td>Actions</td></tr>";	
	$query = "SELECT * FROM `".MYSQL_DATABASE_PREFIX."blacklist`";
	$result = mysql_query($query) or displayerror("Unable to load Blacklisted Information".mysql_error());
	while($row=mysql_fetch_array($result))
		$black .="<tr><td>$row[1]</td><td>$row[2]</td><td><a href='./+admin&subaction=global&del_black=$row[0]'>Delete</a></td></tr>";	
	$black .="</table><fieldset><legend>Add new blacklist</legend><table><tr><td>New Domain :<input type='text' name='blacklist_domain'></td><td>IP (optional) :<input type='text' name='blacklist_ip'></td></tr>";
	$black.="</table></fieldset></fieldset>";
	return $black;
}
function setblacklist($domain="",$ip="")
{
	$www = strstr($domain,'.',1);
	if($www=="www")
		$domain = substr($domain,4);
	if($ip=="")
		$ip=gethostbyname($domain);
	$chk_query = "SELECT * FROM `".MYSQL_DATABASE_PREFIX."blacklist` WHERE `domain` = '$domain' AND `ip`= '$ip'";
	$chk_result = mysql_num_rows(mysql_query($chk_query));
	if($chk_result<1)
	{
		$query="INSERT INTO `".MYSQL_DATABASE_PREFIX."blacklist` (`domain`,`ip`) VALUES ('$domain','$ip')";
		$result =mysql_query($query) or displayerror("Unable to update blacklist".mysql_error());
	}	
	return 1;
}
function delete_blacklist()
{
	$id = safe_html($_GET['del_black']);
	$query = "DELETE FROM `".MYSQL_DATABASE_PREFIX."blacklist` WHERE `id` = '$id'";
	$result =mysql_query($query) or displayerror("Unable to Delete blacklist". mysql_error());
	if(mysql_affected_rows()>0)	
			displayinfo("Blackilist Deleted Successfully");
	return 1;
}
function securitySettingsForm()
{
	global $pageFullPath;
	global $CMSTEMPLATE;
	global $urlRequestRoot,$templateFolder,$cmsFolder;
	$globals=getGlobalSettings();
	$blacklist = getBlacklistTable();
	foreach($globals as $var=>$val) 
		$$var=$val;
	$openidno_ischecked=($openid_enabled=='false')?'checked':'';
	$openidyes_ischecked=($openid_enabled=='false')?'':'checked';
	$recapt_ischecked=($recaptcha=='1')?'checked':'';
	$globalform=<<<globalform
	<table style="width:100%">
	<tr>
	<td style="width:35%">Upload Limit (bytes) </td>
	<td style="width:"65%"><input type="text" name='upload_limit' value='$upload_limit'></td>
	</tr>
	<tr>
	<td>Site Reindex Frequency (days) </td>
	<td><input type="text" name='reindex_frequency' value='$reindex_frequency'></td>
	</tr>
        <tr>
			<td><label for="optEnableOpenID">Enable OpenID?</label></td>
			<td>
			<labe><input type="radio" name="openid_enabled" id="optEnableOpenIDNo" value="false" $openidno_ischecked />No</label>
			<label><input type="radio" name="openid_enabled" id="optEnableOpenIDYes" value="true" $openidyes_ischecked />Yes</label>
			</td>
	</tr>
	<tr>
		<td>Censor Words (use | to seperate the words. Please dont use spaces) </td>
		<td><textarea style="width:98%" rows=10 cols=10 name='censor_words' />$censor_words</textarea></td>
	</tr>
	<tr>
	<td>Use ReCAPTCHA ?</td>
        <td>
				<label><input type="checkbox" name="recaptcha_enable" id="recaptcha_enable" value="Yes" $recapt_ischecked/>Yes</label>
			</td>
		</tr>
		<tr>
			<td><label for="public_key">ReCAPTCHA Public Key:</label></td>
			<td><input type="text" id="public_key" name="public_key" value='$recaptcha_public' /></td>
		</tr>
		<tr>
			<td><label for="private_key">ReCAPTCHA Private Key:</label></td>
			<td><input type="text" id="private_key" name="private_key" value='$recaptcha_private' /></td>
		</tr>
	</table>
	$blacklist
globalform;
	return $globalform;
}

function extension($file) {
	$start = strrpos($file,".");
	$len = strlen($file);
	return substr($file,$start,$len-$start);
}

function delDir($dirname) {
	if (is_dir($dirname))
		$dir_handle = opendir($dirname);
	if (!isset($dir_handle) || !$dir_handle)
		return false;
	while($file = readdir($dir_handle)) {
		if ($file != "." && $file != "..") {
			if (!is_dir($dirname."/".$file))
				unlink($dirname."/".$file);
			else
				delDir($dirname.'/'.$file); 		
		}
	}
	closedir($dir_handle);
	rmdir($dirname);
	return true;
}

function getSuggestions($pattern) {
	$suggestionsQuery = "SELECT IF(user_email LIKE \"$pattern%\", 1, " .
			"IF(`user_fullname` LIKE \"$pattern%\", 2, " .
			"IF(`user_fullname` LIKE \"% $pattern%\", 3, " .
			"IF(`user_email` LIKE \"%$pattern%\", 4, " .
			"IF(`user_fullname` LIKE \"%$pattern%\", 5, 6" .
			"))))) AS `relevance`,	`user_email`, `user_fullname` FROM `".MYSQL_DATABASE_PREFIX."users` WHERE `user_activated`=1 AND(`user_email` LIKE \"%$pattern%\" OR `user_fullname` LIKE \"%$pattern%\" ) ORDER BY `relevance`";
//			echo $suggestionsQuery;
	$suggestionsResult = mysql_query($suggestionsQuery);

	$suggestions = array($pattern);

	while($suggestionsRow = mysql_fetch_row($suggestionsResult)) {
		$suggestions[] = $suggestionsRow[1] . ' - ' . $suggestionsRow[2];
	}

	return join($suggestions, ',');
}

function admin($pageid, $userid) {
	
	if(isset($_GET['doaction']) && $_GET['doaction'] == 'getsuggestions' && isset($_GET['forwhat'])) {
		if(strlen($_GET['forwhat']) >= 3) {
			echo getSuggestions($_GET['forwhat']);
			disconnect();
			exit();
		}
	}
	global $urlRequestRoot,$templateFolder,$cmsFolder,$ICONS;
    if(isset($_GET['indexsite'])) {
		global $sourceFolder;
		require("$sourceFolder/modules/search/admin/spider.php");
		if($_GET['indexsite'] == 1) {
			$serveruri=isset($_SERVER['ORIG_SCRIPT_NAME'])?$_SERVER['ORIG_SCRIPT_NAME']:$_SERVER['SCRIPT_NAME'];
			$uri=substr($serveruri,0,stripos($serveruri,"index.php"));
			$site = "http://" . $_SERVER['HTTP_HOST'] . $uri . "home/";
			index_site($site, 0, -1, 'full', "", "+\n&", 0);
			displayinfo("Index for site created");
		} else {
			index_all();
		}
	}
	
	$result = mysql_fetch_array(mysql_query("SELECT `value` FROM `" . MYSQL_DATABASE_PREFIX . "global` WHERE `attribute` = 'reindex_frequency'"));
	if($result != NULL)
		$threshold = $result['value'];
	else
		$threshold = 30;
	$result = mysql_fetch_array(mysql_query("SELECT to_days(CURRENT_TIMESTAMP)-to_days(`indexdate`) AS 'diff' FROM `sites` WHERE `url` LIKE '%home%'"));
	
	if($result == NULL)
		displayinfo("It seems the site doesn't have index for the search to work. Click <a href='./+admin&indexsite=1'>here</a> to index the site.");
	else if($result['diff'] > $threshold)
		displayinfo("Your site index was created {$result['diff']} days before. Click <a href='./+admin&indexsite=2'>here</a> to reindex your site.");
	
	$quicklinks = <<<ADMINPAGE
	<fieldset>
	<legend>{$ICONS['Website Administration']['small']}Website Administration</legend>
	<a name='quicklinks'></a>
	<table class="iconspanel">
	<tr>
	<td><a href="./+admin&subaction=global"><div>{$ICONS['Global Settings']['large']}<br/>Global Settings</div></a></td>	
	<td><a href="./+admin&subaction=template"><div>{$ICONS['Templates Management']['large']}<br/>Templates Management</div></a></td>
	<td><a href="./+admin&subaction=module"><div>{$ICONS['Modules Management']['large']}<br/>Module Management</div></a></td>
	<td><a href="./+admin&subaction=widgets"><div>{$ICONS['Widgets']['large']}<br/>Widgets Management</div></a></td>
	</tr>
	<tr>
	<td><a href="./+admin&subaction=icon"><div>{$ICONS['Icons']['large']}<br/>Icons Management</div></a></td>
	<td><a href="./+admin&subaction=email"><div>{$ICONS['Email Registrants']['large']}<br/>Email Registrants</div></a></td>
	<td><a href="./+admin&subaction=editgroups"><div>{$ICONS['User Groups']['large']}<br/>Group Management</div></a></td>
	<td><a href="./+admin&subaction=expert"><div>{$ICONS['Site Maintenance']['large']}<br/>Site Maintenance</div></a></td>
	</tr>
	<tr>
	
	<td colspan=2><a href="./+admin&subaction=useradmin"><div>{$ICONS['User Management']['large']}<br/>User Management</div></a></td>
	<td colspan=2><a href="./+admin&subaction=editprofileform"><div>{$ICONS['User Profile']['large']}<br/>User Profiles</div></a></td>
	</tr>

	</table>
	</fieldset>
ADMINPAGE;
	if(isset($_GET['subaction'])) {
		require_once("email.lib.php");
		if($_GET['subaction'] == "email")
			return  displayEmail().$quicklinks;
		else if($_GET['subaction'] == "openemail")
			return displayEmail(escape($_GET['name'])).$quicklinks;
		else if($_GET['subaction'] == "emailsend") {
			sendEmail();
			return  displayEmail(escape($_POST['emailtemplates'])).$quicklinks;
		}
		else if($_GET['subaction'] == "emailsave") {
			saveEmail();
			return  displayEmail(escape($_POST['emailtemplates'])).$quicklinks ;
		}
	}
	if(isset($_GET['subaction']) && ($_GET['subaction']=='module'||$_GET['subaction']=='template')) {
		$type = escape($_GET['subaction']);
		if($type=='module')
			displaywarning("Module Installation/Uninstallation has the potential to completely bring down the CMS, so Install only modules from trusted source");
		require_once("module.lib.php");
		require_once("template.lib.php");
		$type = ucfirst($type);
		$function = "handle{$type}Management";
		$op = $function();
		if($op != "") return $op.$quicklinks;
		return managementForm($type).$quicklinks;
	}
	global $sourceFolder;	
	if(!isset($_GET['subaction']) && !isset($_GET['subsubaction'])) 
		return $quicklinks;
	require_once("users.lib.php");
	$op="";$ophead=""; $str="";

	if (isset($_GET['subaction'])||isset($_GET['subsubaction'])||isset ($_GET['id'])||isset ($_GET['movePermId'])||isset ($_GET['module'])) {
	
		if (isset($_GET['subaction']) && $_GET['subaction'] == 'global' && isset($_POST['update_global_settings'])) 
			updateGlobalSettings();
		else if (isset($_GET['subaction']) && $_GET['subaction'] == 'global' && isset($_GET['del_black']))
			delete_blacklist(); 
		else if (isset($_GET['subaction']) && $_GET['subaction'] == 'useradmin')
		{ 
			$op .= handleUserMgmt();
			$ophead="{$ICONS['User Management']['small']}User Management";
		}
		else if (isset($_GET['subaction']) &&  $_GET['subaction'] == 'widgets') 
		{ 
			$op .= handleWidgetAdmin($pageid); 
			$ophead="{$ICONS['Widgets']['small']}Widgets Management"; 
		}
		else if(isset($_GET['subaction']) && $_GET['subaction'] == 'icon')
		{
			require_once("iconmanagement.lib.php");
			$res = handleIconManagement();
			if(isset($_GET['iconURL']))
				return $res;
			
			$op .= $res;
			$ophead = "{$ICONS['Icons']['small']}Icons Management";
		}
		else if (isset($_GET['subaction']) &&  $_GET['subaction'] == 'editgroups') {
			require_once("permission.lib.php");
			$pagepath = array();
			parseUrlDereferenced($pageid, $pagepath);
			$virtue = '';
			$maxPriorityGroup = getMaxPriorityGroup($pagepath, $userid, array_reverse(getGroupIds($userid)), $virtue);
			$modifiableGroups = getModifiableGroups($userid, $maxPriorityGroup);
			$op .= groupManagementForm($userid, $modifiableGroups, $pagepath);
			$ophead="{$ICONS['Group Management']['small']}Group Management";
		}
		else if (isset($_GET['subaction']) && $_GET['subaction'] == 'reloadtemplates')
		{ 
			$op .= reloadTemplates(); 
			$ophead="{$ICONS['Templates Management']['small']}Reloading Templates"; 
		}
		else if (isset($_GET['subaction']) && $_GET['subaction'] == 'reloadmodules')
		{ 
			$op .= reloadModules(); 
			$ophead="{$ICONS['Modules Management']['small']}Reloading Modules"; 
		}
		else if (isset($_GET['subaction']) && $_GET['subaction'] == 'checkPerm')
		{ 
			$op .= admin_checkFunctionPerms(); 
			$ophead="{$ICONS['Access Permissions']['small']}Checking Permissions Consistency"; 
		}
		elseif (isset($_GET['subaction']) && $_GET['subaction'] == 'checkAdminUser')
		{ 
			$op .= admin_checkAdminUser(); 
			$ophead="Checking Administrator User"; 
		}
		elseif (isset($_GET['subaction']) && $_GET['subaction'] == 'checkAdminPerms')
		{
		 $op .= admin_checkAdminPerms(); 
		 $ophead="Checking Administrator Permissions"; 
		}
		elseif (isset($_GET['subaction']) && ($_GET['subaction'] == 'changePermRank'))
		{ 
			$op .= admin_changePermRank(); 
			$ophead="{$ICONS['Access Permissions']['small']}Changing Permissions Rank"; 
		}
		elseif ((isset($_GET['subaction']) && ($_GET['subaction'] == 'editprofileform')) ||
			(isset($_GET['subsubaction']) && $_GET['subsubaction'] == 'editprofileform'))
		{ 
			$op .= admin_editProfileForm(); 
			$ophead="{$ICONS['User Profile']['small']}Edit User Profile Form"; 
		}
		elseif (isset ($_GET['id'])) 
			$op .= admin_userAdmin();
		elseif (isset ($_GET['movePermId']))
		{ 
			$op .= admin_changePermRank(); 
			$ophead="{$ICONS['Access Permissions']['small']}Changing Permissions Rank"; 
		}
		elseif (isset ($_GET['module']))
		{ 
			$op .= admin_changePermRank(escape($_GET['module'])); 
			$ophead="{$ICONS['Access Permissions']['small']}Changing Permissions Rank for module '".escape($_GET['module'])."'"; 
		}
	}
	if($op!="")
	{
		$op ="<fieldset><legend>$ophead</legend>$op</fieldset>";
	}

	if(isset($_GET['subaction']) && $_GET['subaction']=='global')
	 $str .= globalSettingsForm();
	else if(isset($_GET['subaction']) && $_GET['subaction']=='editgroups') {
		//do nothing so that "expert only" doesn't comes up
	}
	else if(isset($_GET['subaction']) && $_GET['subaction']=='useradmin')
	{
		
		$op .= userManagementForm();
	}
	else if(isset($_GET['subaction']) && $_GET['subaction']=='expert')
	{
		$str .= "<fieldset><legend>{$ICONS['Site Maintenance']['small']}Experts Only</legend>";
		$str .= '<a href="./+admin&subaction=checkPerm">Check Permission List</a><br />';
		$str .= '<a href="./+admin&subaction=checkAdminUser">Check Admin User</a><br />';
		$str .= '<a href="./+admin&subaction=checkAdminPerms">Check Admin Perms</a><br />';
		$str .= '<a href="./+admin&subaction=changePermRank">Change Perm Ranks</a><br />';
		$str .= '<a href="./+admin&subaction=reloadtemplates">Reload Templates</a><br />';
		$str .= '<a href="./+admin&subaction=reloadmodules">Reload Modules</a><br />';
		$str .= '<a href="./+admin&indexsite=2">Reindex Site for Searching</a></br/></fieldset>';
	}
	
	return $str.$op.$quicklinks;

}

function managementForm($type) {
	$function = "getAvailable{$type}s";
	$modules = $function();
	$modulesList = "<select name='{$type}'>";
	foreach($modules as $module)
		$modulesList .= "<option value='" . $module . "'>" . $module . "</option>";
	$modulesList .= "</select>";
	global $ICONS;
	$smallIcon = $ICONS[$type.'s Management']['small'];
	$subaction = ($type=="Module")?'module':($type=="Template"?'template':"");
	$form=<<<FORM
	<script type="text/javascript">
	function delconfirm(obj) {
		return confirm("Are you sure want to delete '" + document.getElementById('modules').value + "' {$type}?");
	}
	</script>
	<fieldset>
	<legend>{$smallIcon}{$type} Management</legend>
	<form name='module' method='POST' action="./+admin&subaction={$subaction}&subsubaction=install" enctype="multipart/form-data">
	Add new {$type}: <input type='file' name='file' id='file' /><input type='submit' name='btn_install' value='Upload' />
	</form>
	<br/><br/>
	<form method='POST' action="./+admin&subaction={$subaction}&subsubaction=uninstall" enctype="multipart/form-data">
	Delete Existing {$type}: {$modulesList}<input type='submit' name='btn_uninstall' value='Uninstall' onclick='return delconfirm(this);' />
	</form>
	</fieldset>
FORM;
	return $form;
}

function updateGlobalSettings()
{
       
	$global=array();
	$global['allow_pagespecific_header']=isset($_POST['allow_page_header'])?1:0;
	$global['allow_pageheadings_intitle']=isset($_POST['allow_pageheadings_intitle'])?1:0;
	$global['allow_pagespecific_template']=isset($_POST['allow_page_template'])?1:0;
	$global['default_user_activate']=isset($_POST['activate_useronreg'])?1:0;
	$global['default_mail_verify']=isset($_POST['send_mail_on_reg'])?1:0;
	$global['breadcrumb_submenu']=isset($_POST['breadcrumb_submenu'])?1:0;

	$global['allow_login']=isset($_POST['allow_login'])?1:0;
	$global['deadline_notify']=$_POST['deadline_notify'];
	$global['cms_title']=escape($_POST['cms_title']);
	$global['default_template']=escape($_POST['default_template']);
	$global['cms_email']=escape($_POST['cms_email']);
	$global['upload_limit']=escape($_POST['upload_limit']);
	$global['reindex_frequency']=escape($_POST['reindex_frequency']);
	$global['cms_desc']=escape($_POST['cms_desc']);
	$global['cms_keywords']=escape($_POST['cms_keywords']);
	$global['cms_footer']=escape($_POST['cms_footer']);
	$global['blacklist_domain']=escape($_POST['blacklist_domain']);
	$global['blacklist_ip']=escape($_POST['blacklist_ip']);
	$global['censor_words']=safe_html($_POST['censor_words']);
	$blacklist_domain = safe_html($_POST['blacklist_domain']);
	$blacklist_ip = safe_html($_POST['blacklist_ip']);
	if(!(($blacklist_domain=="")&&($blacklist_ip=="")))
		setblacklist($blacklist_domain,$blacklist_ip);
	if(isset($_POST['openid_enabled']) && escape($_POST['openid_enabled']=='true')) //if user submitted true
	  { 
	    if (iscurlinstalled()) //check if curl is enabled
	      $global['openid_enabled']='true'; // enable openid
	    else
	      {
		global $curl_message;
		displaywarning($curl_message); //dispaly warnning that curl is not enabled
		$global['openid_enabled']='false'; //disable openid
	      }
	  }
	else  //if user submitted false
	  $global['openid_enabled']='false'; //disable openid
	if(isset($_POST['recaptcha_enable'])) //if user submitted true
	  { 
	    if (($_POST['public_key']!=NULL)&&($_POST['private_key']!=NULL))
		{	    
		  $global['recaptcha']='1'; // enable recaptcha
		  $global['recaptcha_public']=escape($_POST['public_key']);
		  $global['recaptcha_private']=escape($_POST['private_key']);
	    
		}
	else
	      {
		displaywarning("Public/Private Key is NULL. ReCAPTCHA could not be enabled"); //dispaly warning
		$global['recaptcha']='0'; //disable recaptcha
	      }
	  }
	else
	    $global['recaptcha']='0';
	setGlobalSettings($global);

	displayinfo("Global Settings successfully updated! Changes will come into effect on next page reload.");
	
}

function admin_checkFunctionPerms() {
	global $sourceFolder;
	$returnStr="";
	//1) Check all available classes (modules)
	if ($handle = opendir($sourceFolder . '/modules')) {
		while (false !== ($file = readdir($handle))) {
			$list[] = $file;
		}
		closedir($handle);
	}
	foreach ($list as $temp) {
		if (strpos($temp, '.lib.php')==strlen($temp)-8) {
			$moduleArray[] = str_replace('.lib.php', '', $temp);
		}
	}
	$moduleList = "";
	foreach ($moduleArray as $module) {
		$moduleList .= $module . ", ";
	}
	$moduleList .= "";	

	$returnStr.="<br/>The following modules/classes exist in the file system:<br>$moduleList";
	$moduleList = "";

	//	2) See, if all their functions actionView(), actionEdit(), actionX exist
	//in the perms table or not (and create also). If
	//not, give option to create that permission.

	global $sourceFolder;
	global $moduleFolder;
	foreach ($moduleArray as $module) {
		$perm = array ();
		reset($perm);
		$i = 0;
		if (($module != 'forum') && ($module != 'poll') && ($module != 'contest')/* && ($module != 'gallery')*/) {

	
			require_once ($sourceFolder . "/" . $moduleFolder . "/" . $module . ".lib.php");

			$functionArray = get_class_methods($module);
	
			if($functionArray==NULL)  //means something's wrong, probably the class is not defined properly
			{
				$returnStr.="<br/><b>Please check the Class definition of $module. It may have undefined functions. Please define the functions or declare the class as an abstract class</b>";
				continue;
			}
			foreach ($functionArray as $method) {
				if ((substr($method, 0, 6)) == 'action') {
					$permission = str_replace('action', "", $method);
					$permission = strtolower($permission);
					$perm[$i] = $permission;
					$i = $i +1;
				}
			}

			$permList = "";
			foreach ($perm as $permElements) {
				$permList .= $permElements . ", ";
			}
			$returnStr.="<br/>The following methods/functions/actions exist in the filesystem class for $module:<br> $permList";
			$perm[] = 'create';
			$permExists = "";
			$i = 0;

			foreach ($perm as $permission) {
				$query = "SELECT * FROM `" . MYSQL_DATABASE_PREFIX . "permissionlist` WHERE `page_module`='$module' AND `perm_action`='$permission'";
				$result = mysql_query($query);
				if (mysql_num_rows($result) > 0) {
					if ($i == 1)
						$permExists .= ", "; // Just to append ,(comma) after every perm but last
					$permExists .= $permission;
					$i = 1;
				} else {
					$returnStr.="<br/><b>$permission DOES NOT exist for $module but will be created</b><br>";
					$query = "SELECT MAX(perm_id) as MAX FROM `" . MYSQL_DATABASE_PREFIX . "permissionlist`";
					$result = mysql_query($query) or die(mysql_error());
					$row = mysql_fetch_assoc($result);
					$permid = $row['MAX'] + 1;
					$query = "SELECT MAX(perm_rank) as MAX FROM `" . MYSQL_DATABASE_PREFIX . "permissionlist` WHERE `page_module`='$module'";
					$result = mysql_query($query) or die(mysql_error());
					$row = mysql_fetch_assoc($result);
					$permrank = $row['MAX'] + 1;
					$desc = $permission . " the " . $module;
					$query = "INSERT INTO `" . MYSQL_DATABASE_PREFIX . "permissionlist`(`perm_id` ,`page_module` ,`perm_action` ,`perm_text` ,`perm_rank` ,`perm_description`)VALUES ('$permid', '$module', '$permission', '$permission', '$permrank', '$desc') ";
					$result = mysql_query($query) or die(mysql_error());
					if (mysql_affected_rows())
						displayinfo("$permission has been created for $module");
				}
			}

			$permExists .= ".";//Adding the last period.
			$returnStr.="<br/>The following permissions exist in database for $module :<br>$permExists";
			 
		}

	}

	//3) See if any extra option exists in the database, if it does, warn the user.

	foreach ($moduleArray as $module) {
		if (($module != 'forum') && ($module != 'poll') && ($module!='contest')/* && ($module != 'gallery')*/) {
			require_once ($sourceFolder . "/" . $moduleFolder . "/" . $module . ".lib.php");
			$class = new $module ();
			$query = "SELECT * FROM `" . MYSQL_DATABASE_PREFIX . "permissionlist` WHERE `page_module`='$module'";
			$result = mysql_query($query);
			while ($tempres = mysql_fetch_assoc($result)) {

				$permName = ucfirst($tempres['perm_action']);
				$method = "action" . $permName;

				if (!(method_exists($class, $method)))
					$returnStr.="<br/>Permission $method, perm id = $tempres[perm_id] exists in database but not in class $module";

			}

		}
	}
	return $returnStr;
}
//4) See if any user with the name admin exists or not. If it does not,
//create it and give it a random and display all required information,

function admin_checkAdminUser() {
	$query = "SELECT * FROM `" . MYSQL_DATABASE_PREFIX . "users` WHERE `user_name`='admin'";
	$result = mysql_query($query);
	if (mysql_num_rows($result) > 0) {
		displayinfo("User \"Admin\" exists in database.");
	} else {
		$query = "SELECT MAX(user_id) as MAX FROM `" . MYSQL_DATABASE_PREFIX . "users` ";
		$result = mysql_query($query) or die(mysql_error() . "check.lib L:141");
		$row = mysql_fetch_assoc($result);
		$uid = $row['MAX'] + 1;
		$passwd = rand();
		$adminPasswd = md5($passwd);
		$query = "INSERT INTO `" . MYSQL_DATABASE_PREFIX . "users`( `user_id` ,`user_name` ,`user_email` ,`user_fullname` ,`user_password`  ,`user_activated`)VALUES ( '$uid' , 'admin', 'admin@cms.org', 'Administrator', '$adminPasswd', '1')";
		
		$result = mysql_query($query) or die(mysql_error());
		if (mysql_affected_rows() > 0) {
			displayinfo("User Admin has been created with email admin@cms.org and password as $passwd");
		} else
			displayerror("Failed to create user Admin");
	}
}

function admin_checkAdminPerms()
/*
 *
 * 5) See if the admin user has all perms at page 0 or not. If not, give him
 *    all perms and inform him
 */
 {
	$returnStr="";
	$str="";
	$query = "SELECT * FROM `" . MYSQL_DATABASE_PREFIX . "users` WHERE `user_name`='admin' ";
	$result = mysql_query($query);
	if (mysql_num_rows($result) > 0) {
		$temp = mysql_fetch_array($result);
		$user_Id = $temp['user_id'];
		$query1 = "SELECT * FROM `" . MYSQL_DATABASE_PREFIX . "permissionlist`";
		$result1 = mysql_query($query1);
		while ($temp1 = mysql_fetch_assoc($result1)) {
			foreach ($temp1 as $var => $val) {
				if ($var == 'perm_id') {
					$query = "SELECT * FROM `" . MYSQL_DATABASE_PREFIX . "userpageperm` WHERE `perm_type`='user' AND `usergroup_id`='$user_Id' AND `page_id`=0 AND `perm_id`='$val' AND `perm_permission`='Y'";
					$result = mysql_query($query) or die(mysql_error());
					if (!mysql_num_rows($result)) {
						$query = "INSERT INTO `" . MYSQL_DATABASE_PREFIX . "userpageperm` (`perm_type`,`page_id`,`usergroup_id`,`perm_id`,`perm_permission`) VALUES ('user','0','$user_Id','$val','Y')";
						$result2 = mysql_query($query);
						if (mysql_affected_rows())
							$returnStr.="\n<br>User Admin userId=$user_Id has been allotted permission $temp1[perm_action] of module $temp1[page_module] over page 0";
						else
							$returnStr.="\n<br>Failed to create permission $temp1[perm_action] of module $temp1[page_module] over page 0 for User Admin userId=$user_Id";
					} else {
						$str .= "";
						$str .= "\n<tr><td>" . $temp1['page_module'] . "</td><td>" . $temp1['perm_action'] . "</td></tr>";
					}
				}
			}
		}
		if ($str != '')
			$returnStr.="The following permissions exist for user admin: <table border=\"1\"><tr><th>Module</th><th>Permission</th></tr>" .$str. "</table>";

	} else {
		$returnStr.=admin_checkAdminUser();
		$returnStr.=admin_checkAdminPerms();
	}
	return $returnStr;
}


/*
 * 8) Ability to change perm ranks (like page move up and move )
 *
 * */

function admin_changePermRank($module="") {
	require_once("tbman_executer.lib.php");

	//checking if this is the first time it is being called
	$pv = "";
	if(isset($_POST['querystring'])) {
		$pv = $_POST;
	} else {
		$pv = "SELECT * FROM `". MYSQL_DATABASE_PREFIX ."permissionlist`";
	}
	$table = new tbman_executer($pv);
	$table->formaction="./+admin&subaction=changePermRank";
	return $table->execute();
	
}


function admin_editProfileForm() {
	include_once('profile.lib.php');
	return getProfileFormEditForm();
}

function groupManagementForm($currentUserId, $modifiableGroups, &$pagePath) {
	require_once("group.lib.php");
	global $ICONS;
	global $urlRequestRoot, $cmsFolder, $templateFolder, $moduleFolder,$sourceFolder;
	$scriptsFolder = "$urlRequestRoot/$cmsFolder/$templateFolder/common/scripts";
	$imagesFolder = "$urlRequestRoot/$cmsFolder/$templateFolder/common/images";

	/// Parse any get variables, do necessary validation and stuff, so that we needn't check inside every if
	$groupRow = $groupId = $userId = null;
	$subAction = ''; //isset($_GET['subaction']) ? $_GET['subaction'] : '';
	if ((isset($_GET['subsubaction']) && $_GET['subsubaction'] == 'editgroup' && isset($_GET['groupname'])) || (isset($_POST['btnEditGroup']) && isset($_POST['selEditGroups'])))
		$subAction = 'showeditform';
	elseif(isset($_GET['subsubaction']) && $_GET['subsubaction'] == 'associateform')
		$subAction = 'associateform';
	elseif (isset($_GET['subsubaction']) && $_GET['subsubaction'] == 'deleteuser' && isset($_GET['groupname']) && isset($_GET['useremail']))
		$subAction = 'deleteuser';
	elseif (isset($_POST['btnAddUserToGroup']))
		$subAction = 'addusertogroup';
	elseif (isset($_POST['btnSaveGroupProperties']))
		$subAction = 'savegroupproperties';
	elseif (isset($_POST['btnEditGroupPriorities']) || (isset($_GET['subsubaction']) && $_GET['subsubaction'] == 'editgrouppriorities'))
		$subAction = 'editgrouppriorities';

	if(isset($_POST['selEditGroups']) || isset($_GET['groupname'])) {
		$groupRow = getGroupRow( isset($_POST['selEditGroups']) ? escape($_POST['selEditGroups']) : escape($_GET['groupname']) );
		$groupId = $groupRow['group_id'];
		if($subAction != 'editgrouppriorities' && (!$groupRow || !$groupId || $groupId < 2)) {
			displayerror('Error! Invalid group requested.');
			return ;
		}

		if(!is_null($groupId)) {
			if($modifiableGroups[count($modifiableGroups) - 1]['group_priority'] < $groupRow['group_priority']) {
				displayerror('You do not have the permission to modify the selected group.');
				return '';
			}
		}
	}
	if(isset($_GET['useremail'])) {
		$userId = getUserIdFromEmail($_GET['useremail']);
	}

	if($subAction != 'editgrouppriorities' && (isset($_GET['subaction']) && $_GET['subaction'] == 'editgroups' && !is_null($groupId))) {
		if ($subAction == 'deleteuser') {
			if($groupRow['form_id'] != 0) {
				displayerror('The group is associated with a form. To remove a user, use the edit registrants in the assoicated form.');
			}
			elseif (!$userId) {
				displayerror('Unknown E-mail. Could not find a registered user with the given E-mail Id');
			}
			else {
				$deleteQuery = 'DELETE FROM `' . MYSQL_DATABASE_PREFIX . 'usergroup` WHERE `user_id` = \'' . $userId . '\' AND `group_id` = ' . $groupId;
				$deleteResult = mysql_query($deleteQuery);
				if(!$deleteResult || mysql_affected_rows() != 1) {
					displayerror('Could not delete user with the given E-mail from the given group.');
				}
				else {
					displayinfo('Successfully removed user from the current group');

					if($userId == $currentUserId) {
						$virtue = '';
						$maxPriorityGroup = getMaxPriorityGroup($pagePath, $currentUserId, array_reverse(getGroupIds($currentUserId)), $virtue);
						$modifiableGroups = getModifiableGroups($currentUserId, $maxPriorityGroup, $ordering = 'asc');
					}
				}
			}
		}
		elseif ($subAction == 'savegroupproperties' && isset($_POST['txtGroupDescription'])) {
			$updateQuery = "UPDATE `" . MYSQL_DATABASE_PREFIX . "groups` SET `group_description` = '".escape($_POST['txtGroupDescription'])."' WHERE `group_id` = '$groupId'";
			$updateResult = mysql_query($updateQuery);
			if (!$updateResult) {
				displayerror('Could not update database.');
			}
			else {
				displayinfo('Changes to the group have been successfully saved.');
			}
			$groupRow = getGroupRow($groupRow['group_name']);
		}
		elseif ($subAction == 'addusertogroup' && isset($_POST['txtUserEmail']) && trim($_POST['txtUserEmail']) != '') {
			if($groupRow['form_id'] != 0) {
				displayerror('The selected group is associated with a form. To add a user, register the user to the form.');
			}
			else {
				$passedEmails = explode(',', escape($_POST['txtUserEmail']));

				for($i = 0; $i < count($passedEmails); $i++) {
					$hyphenPos = strpos($passedEmails[$i], '-');
					if ($hyphenPos >= 0) {
						$userEmail = trim(substr($passedEmails[$i], 0, $hyphenPos - 1));
					}
					else {
						$userEmail = escape($_POST['txtUserEmail']);
					}

					$userId = getUserIdFromEmail($userEmail);
					if(!$userId || $userId < 1) {
						displayerror('Unknown E-mail. Could not find a registered user with the given E-mail Id');
					}

					if(!addUserToGroupName($groupRow['group_name'], $userId)) {
						displayerror('Could not add the given user to the current group.');
					}
					else {
						displayinfo('User has been successfully inserted into the given group.');
					}
				}
			}
		}
		elseif ($subAction == 'associateform') {
			if(isset($_POST['btnAssociateGroup'])) {
				$pageIdArray = array();
				$formPageId = parseUrlReal(escape($_POST['selFormPath']), $pageIdArray);
				if($formPageId <= 0 || getPageModule($formPageId) != 'form') {
					displayerror('Invalid page selected! The page you selected is not a form.');
				}
				elseif (!getPermissions($currentUserId, $formPageId, 'editregistrants', 'form'))
					displayerror('You do not have the permissions to associate the selected form with a group.');
				else {
					$formModuleId = getModuleComponentIdFromPageId($formPageId, 'form');
					require_once("$sourceFolder/$moduleFolder/form.lib.php");

					if(isGroupEmpty($groupId) || form::getRegisteredUserCount($formModuleId) == 0) {
						associateGroupWithForm($groupId, $formModuleId);
						$groupRow = getGroupRow($groupRow['group_name']);
					}
					else
						displayerror('Both the group and the form already contain registered users, and the group cannot be associated with the selected form.');
				}
			}
			elseif(isset($_POST['btnUnassociateGroup'])) {
				if($groupRow['form_id'] <= 0) {
					displayerror('The selected group is currently not associated with any form.');
				}
				elseif(!getPermissions($currentUserId, getPageIdFromModuleComponentId('form', $groupRow['form_id']), 'editregistrants', 'form')) {
					displayerror('You do not have the permissions to unassociate the form from this group.');
				}
				else {
					unassociateFormFromGroup($groupId);
					$virtue = '';
					$maxPriorityGroup = getMaxPriorityGroup($pagePath, $currentUserId, array_reverse(getGroupIds($currentUserId)), $virtue);
					$modifiableGroups = getModifiableGroups($currentUserId, $maxPriorityGroup, $ordering = 'asc');
					$groupRow = getGroupRow($groupRow['group_name']);
				}
			}
		}

		if($modifiableGroups[count($modifiableGroups) - 1]['group_priority'] < $groupRow['group_priority']) {
			displayerror('You do not have the permission to modify the selected group.');
			return '';
		}

		$usersTable = '`' . MYSQL_DATABASE_PREFIX . 'users`';
		$usergroupTable = '`' . MYSQL_DATABASE_PREFIX . 'usergroup`';
		$userQuery = "SELECT `user_email`, `user_fullname` FROM $usergroupTable, $usersTable WHERE `group_id` =  '$groupId' AND $usersTable.`user_id` = $usergroupTable.`user_id` ORDER BY `user_email`";
		$userResult = mysql_query($userQuery);
		if(!$userResult) {
			displayerror('Error! Could not fetch group information.');
			return '';
		}
	
		$userEmails = array();
		$userFullnames = array();
		while($userRow = mysql_fetch_row($userResult)) {
			$userEmails[] = $userRow[0];
			$userFullnames[] = $userRow[1];
		}
		
		$groupEditForm = <<<GROUPEDITFORM
			<h2>Group '{$groupRow['group_name']}' - '{$groupRow['group_description']}'</h2><br />
			<fieldset style="padding: 8px">
				<legend>{$ICONS['User Groups']['small']}Group Properties</legend>
				<form name="groupeditform" method="POST" action="./+admin&subaction=editgroups&groupname={$groupRow['group_name']}">
					Group Description: <input type="text" name="txtGroupDescription" value="{$groupRow['group_description']}" />
					<input type="submit" name="btnSaveGroupProperties" value="Save Group Properties" />
				</form>
			</fieldset>

			<br />
			<fieldset style="padding: 8px">
				<legend>{$ICONS['User Groups']['small']}Existing Users in Group:</legend>
GROUPEDITFORM;

		$userCount = mysql_num_rows($userResult);
		global $urlRequestRoot, $cmsFolder, $templateFolder,$sourceFolder;
		$deleteImage = "<img src=\"$urlRequestRoot/$cmsFolder/$templateFolder/common/icons/16x16/actions/edit-delete.png\" alt=\"Remove user from the group\" title=\"Remove user from the group\" />";

		for($i = 0; $i < $userCount; $i++) {
			$isntAssociatedWithForm = ($groupRow['form_id'] == 0);
			if($isntAssociatedWithForm)
				$groupEditForm .= '<a onclick="return confirm(\'Are you sure you wish to remove this user from this group?\')" href="./+admin&subaction=editgroups&subsubaction=deleteuser&groupname=' . $groupRow['group_name'] . '&useremail=' . $userEmails[$i] . '">' . $deleteImage . "</a>";
			$groupEditForm .= " {$userEmails[$i]} - {$userFullnames[$i]}<br />\n";
		}

		$associateForm = '';
		if($groupRow['form_id'] == 0) {
			$associableForms = getAssociableFormsList($currentUserId, !isGroupEmpty($groupId));
			$associableFormCount = count($associableForms);
			$associableFormsBox = '<select name="selFormPath">';
			for($i = 0; $i < $associableFormCount; ++$i) {
				$associableFormsBox .= '<option value="' . $associableForms[$i][2] . '">' . $associableForms[$i][1] . ' - ' . $associableForms[$i][2] . '</option>';
			}
			$associableFormsBox .= '</select>';
			$associateForm = <<<GROUPASSOCIATEFORM

			Select a form to associate the group with: $associableFormsBox
			<input type="submit" name="btnAssociateGroup" value="Associate Group with Form" />
GROUPASSOCIATEFORM;
		}
		else {
			$associatedFormPageId = getPageIdFromModuleComponentId('form', $groupRow['form_id']);
			$associateForm = 'This group is currently associated with the form: ' . getPageTitle($associatedFormPageId) . ' (' . getPagePath($associatedFormPageId) . ')<br />' .
					'<input type="submit" name="btnUnassociateGroup" value="Unassociate" />';
		}

		$groupEditForm .= '</fieldset>';
		if($groupRow['form_id'] == 0) {
			$groupEditForm .= <<<GROUPEDITFORM
				<br />
				<fieldset style="padding: 8px">
					<legend>{$ICONS['Add']['small']}Add Users to Group</legend>
					<form name="addusertogroup" method="POST" action="./+admin&subaction=editgroups&groupname={$groupRow['group_name']}">
						Email ID: <input type="text" name="txtUserEmail" id="txtUserEmail" value="" style="width: 256px" autocomplete="off" />
						<div id="suggestionDiv" class="suggestionbox"></div>

						<script language="javascript" type="text/javascript" src="$scriptsFolder/ajaxsuggestionbox.js"></script>
						<script language="javascript" type="text/javascript">
						<!--
							var addUserBox = new SuggestionBox(document.getElementById('txtUserEmail'), document.getElementById('suggestionDiv'), "./+admin&doaction=getsuggestions&forwhat=%pattern%");
							addUserBox.loadingImageUrl = '$imagesFolder/ajaxloading.gif';
						-->
						</script>

						<input type="submit" name="btnAddUserToGroup" value="Add User to Group" />
					</form>
				</fieldset>
GROUPEDITFORM;
		}
		$groupEditForm .= <<<GROUPEDITFORM
			<br />
			<fieldset style="padding: 8px">
				<legend>{$ICONS['Group Associate Form']['small']}Associate With Form</legend>
				<form name="groupassociationform" action="./+admin&subaction=editgroups&subsubaction=associateform&groupname={$groupRow['group_name']}" method="POST">
					$associateForm
				</form>
			</fieldset>
GROUPEDITFORM;

		return $groupEditForm;
	}

	if ($subAction == 'editgrouppriorities') {
		$modifiableCount = count($modifiableGroups);
		$userMaxPriority = $maxPriorityGroup = 1;
		if($modifiableCount != 0) {
			$userMaxPriority = max($modifiableGroups[0]['group_priority'], $modifiableGroups[$modifiableCount - 1]['group_priority']);
			$maxPriorityGroup = $modifiableGroups[0]['group_priority'] > $modifiableGroups[$modifiableCount - 1]['group_priority'] ? $modifiableGroups[0]['group_id'] : $modifiableGroups[$modifiableCount - 1]['group_id'];
		}

		if(isset($_GET['dowhat']) && !is_null($groupId)) {
			if($_GET['dowhat'] == 'incrementpriority' || $_GET['dowhat'] == 'decrementpriority') {
				shiftGroupPriority($currentUserId, $groupRow['group_name'], $_GET['dowhat'] == 'incrementpriority' ? 'up' : 'down', $userMaxPriority, true);
			}
			elseif($_GET['dowhat'] == 'movegroupup' || $_GET['dowhat'] == 'movegroupdown') {
				shiftGroupPriority($currentUserId, $groupRow['group_name'], $_GET['dowhat'] == 'movegroupup' ? 'up' : 'down', $userMaxPriority, false);
			}
			elseif($_GET['dowhat'] == 'emptygroup') {
				emptyGroup($groupRow['group_name']);
			}
			elseif($_GET['dowhat'] == 'deletegroup') {
				if(deleteGroup($groupRow['group_name'])) {
					$virtue = '';
					$maxPriorityGroup = getMaxPriorityGroup($pagePath, $currentUserId, array_reverse(getGroupIds($currentUserId)), $virtue);
					$modifiableGroups = getModifiableGroups($currentUserId, $maxPriorityGroup, $ordering = 'asc');
				}
			}

			$modifiableGroups = reevaluateGroupPriorities($modifiableGroups);
		}
		elseif(isset($_GET['dowhat']) && $_GET['dowhat'] == 'addgroup') {
			if(isset($_POST['txtGroupName']) && isset($_POST['txtGroupDescription']) && isset($_POST['selGroupPriority'])) {
				$existsQuery = 'SELECT `group_id` FROM `' . MYSQL_DATABASE_PREFIX . "groups` WHERE `group_name` = '".escape($_POST['txtGroupName'])."'";
				$existsResult = mysql_query($existsQuery);
				if(trim($_POST['txtGroupName']) == '') {
					displayerror('Cannot create a group with an empty name. Please type in a name for the new group.');
				}
				elseif(mysql_num_rows($existsResult) >= 1) {
					displayerror('A group with the name you specified already exists.');
				}
				else {
					$idQuery = 'SELECT MAX(`group_id`) FROM `' . MYSQL_DATABASE_PREFIX . 'groups`';
					$idResult = mysql_query($idQuery);
					$idRow = mysql_fetch_row($idResult);
					$newGroupId = 2;
					if(!is_null($idRow[0])) {
						$newGroupId = $idRow[0] + 1;
					}

					$newGroupPriority = 1;
					if($_POST['selGroupPriority'] <= $userMaxPriority && $_POST['selGroupPriority'] > 0) {
						$newGroupPriority = escape($_POST['selGroupPriority']);
					}

					$addGroupQuery = 'INSERT INTO `' . MYSQL_DATABASE_PREFIX . 'groups` (`group_id`, `group_name`, `group_description`, `group_priority`) ' .
							"VALUES($newGroupId, '".escape($_POST['txtGroupName'])."', '".escape($_POST['txtGroupDescription'])."', '$newGroupPriority')";
					$addGroupResult = mysql_query($addGroupQuery);
					if($addGroupResult) {
						displayinfo('New group added successfully.');

						if(isset($_POST['chkAddMe'])) {
							$insertQuery = 'INSERT INTO `' . MYSQL_DATABASE_PREFIX . "usergroup`(`user_id`, `group_id`) VALUES ('$currentUserId', '$newGroupId')";
							if(!mysql_query($insertQuery)) {
								displayerror('Error adding user to newly created group: ' . $insertQuery . '<br />' . mysql_query());
							}
						}
						$virtue = '';
						$maxPriorityGroup = getMaxPriorityGroup($pagePath, $currentUserId, array_reverse(getGroupIds($currentUserId)), $virtue);
						$modifiableGroups = getModifiableGroups($currentUserId, $maxPriorityGroup, $ordering = 'asc');
					}
					else {
						displayerror('Could not run MySQL query. New group could not be added.');
					}
				}
			}

			$modifiableGroups = reevaluateGroupPriorities($modifiableGroups);
		}

		$modifiableCount = count($modifiableGroups);
		if($modifiableGroups[0]['group_priority'] < $modifiableGroups[$modifiableCount - 1]['group_priority']) {
			$modifiableGroups = array_reverse($modifiableGroups);
		}
		$previousPriority = $modifiableGroups[0]['group_priority'];
		global $cmsFolder, $urlRequestRoot, $moduleFolder, $templateFolder,$sourceFolder;
		$iconsFolderUrl = "$urlRequestRoot/$cmsFolder/$templateFolder/common/icons/16x16";
		$moveUpImage = '<img src="' . $iconsFolderUrl . '/actions/go-up.png" title="Increment Group Priority" alt="Increment Group Priority" />';
		$moveDownImage = '<img src="' . $iconsFolderUrl . '/actions/go-down.png" alt="Decrement Group Priority" title="Decrement Group Priority" />';
		$moveTopImage = '<img src="' . $iconsFolderUrl . '/actions/go-top.png" alt="Move to next higher priority level" title="Move to next higher priority level" />';
		$moveBottomImage = '<img src="' . $iconsFolderUrl . '/actions/go-bottom.png" alt="Move to next lower priority level" title="Move to next lower priority level" />';
		$emptyImage = '<img src="' . $iconsFolderUrl . '/actions/edit-clear.png" alt="Empty Group" title="Empty Group" />';
		$deleteImage = '<img src="' . $iconsFolderUrl . '/actions/edit-delete.png" alt="Delete Group" title="Delete Group" />';

		$groupsForm = '<h3>Edit Group Priorities</h3><br />';
		for($i = 0; $i < $modifiableCount; $i++) {
			if($modifiableGroups[$i]['group_priority'] != $previousPriority) {
				$groupsForm .= '<br /><br /><hr /><br />';
			}
			$groupsForm .=
					'<span style="margin: 4px;" title="' . $modifiableGroups[$i]['group_description'] . '">' .
					'<a href="./+admin&subaction=editgroups&subsubaction=editgrouppriorities&dowhat=incrementpriority&groupname=' . $modifiableGroups[$i]['group_name'] . '">' . $moveUpImage . '</a>' .
					'<a href="./+admin&subaction=editgroups&subsubaction=editgrouppriorities&dowhat=decrementpriority&groupname=' . $modifiableGroups[$i]['group_name'] . '">' . $moveDownImage . '</a>' .
					'<a href="./+admin&subaction=editgroups&subsubaction=editgrouppriorities&dowhat=movegroupup&groupname=' . $modifiableGroups[$i]['group_name'] . '">' . $moveTopImage . '</a>' .
					'<a href="./+admin&subaction=editgroups&subsubaction=editgrouppriorities&dowhat=movegroupdown&groupname=' . $modifiableGroups[$i]['group_name'] . '">' . $moveBottomImage . '</a>' .
					'<a onclick="return confirm(\'Are you sure you want to empty this group?\')" href="./+admin&subaction=editgroups&subsubaction=editgrouppriorities&dowhat=emptygroup&groupname=' . $modifiableGroups[$i]['group_name'] . '">' . $emptyImage . '</a>' .
					'<a onclick="return confirm(\'Are you sure you want to delete this group?\')" href="./+admin&subaction=editgroups&subsubaction=editgrouppriorities&dowhat=deletegroup&groupname=' . $modifiableGroups[$i]['group_name'] . '">' . $deleteImage . '</a>' .
					'<a href="./+admin&subaction=editgroups&groupname=' . $modifiableGroups[$i]['group_name'] . '">' . $modifiableGroups[$i]['group_name'] . "</a></span>\n";
			$previousPriority = $modifiableGroups[$i]['group_priority'];
		}

		$priorityBox = '<option value="1">1</option>';
		for($i = 2; $i <= $userMaxPriority; ++$i) {
			$priorityBox .= '<option value="' . $i . '">' . $i . '</option>';
		}
		$groupsForm .= <<<GROUPSFORM
		<br /><br />
		<fieldset style="padding: 8px">
			<legend>Create New Group:</legend>

			<form name="groupaddform" method="POST" action="./+admin&subaction=editgroups&subsubaction=editgrouppriorities&dowhat=addgroup">
				<label>Group Name: <input type="text" name="txtGroupName" value="" /></label><br />
				<label>Group Description: <input type="text" name="txtGroupDescription" value="" /></label><br />
				<label>Group Priority: <select name="selGroupPriority">$priorityBox</select><br />
				<label><input type="checkbox" name="chkAddMe" value="addme" /> Add me to group</label><br />
				<input type="submit" name="btnAddNewGroup" value="Add Group" />
			</form>
		</fieldset>
GROUPSFORM;

		return $groupsForm;
	}


	$modifiableCount = count($modifiableGroups);
	$groupsBox = '<select name="selEditGroups">';
	for($i = 0; $i < $modifiableCount; ++$i) {
		$groupsBox .= '<option value="' . $modifiableGroups[$i]['group_name'] . '">' . $modifiableGroups[$i]['group_name'] . ' - ' . $modifiableGroups[$i]['group_description'] . "</option>\n";
	}
	$groupsBox .= '</select>';

	$groupsForm = <<<GROUPSFORM
		<form name="groupeditform" method="POST" action="./+admin&subaction=editgroups">
			$groupsBox
			<input type="submit" name="btnEditGroup" value="Edit Selected Group" /><br /><br />
			<input type="submit" name="btnEditGroupPriorities" value="Add/Shuffle/Remove Groups" />
		</form>

GROUPSFORM;

	return $groupsForm;
}
