<?php
/**
 * @package pragyan
 * @copyright (c) 2008 Pragyan Team
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
	global $pageFullPath;
	global $CMSTEMPLATE;
	$globals=getGlobalSettings();
	foreach($globals as $var=>$val) 
		$$var=$val;
	$allow_pagespecific_header=$allow_pagespecific_header==0?"":"checked";
	$allow_pagespecific_template=$allow_pagespecific_template==0?"":"checked";
	$activate_useronreg=$default_user_activate==0?"":"checked";
	$default_mailverify=$default_mail_verify==0?"":"checked";
	$breadcrumb_submenu=$breadcrumb_submenu==0?"":"checked";
	

	$globalform=<<<globalform
	<form name='admin_page_form' method='POST' action='./+admin&subaction=global'>
	<fieldset>
	<legend>Global Settings</legend>
	<table>
	<tr>
	<td>Website Name :</td>
	<td><input type="text" name='cms_title' value="$cms_title"></td>
	</tr>
	<tr>
	<td>Website Email :</td>
	<td><input type="text" name='cms_email' value='$cms_email'></td>
	</tr>
	<tr>
	<td>Upload Limit (bytes) :</td>
	<td><input type="text" name='upload_limit' value='$upload_limit'></td>
	</tr>
	<tr>
	<td>Allow Page-specific Headers ?</td>
	<td><input name='allow_page_header' type='checkbox' $allow_pagespecific_header></td>
	</tr>
	<tr>
	<td>Allow Page-specific Template ?</td>
	<td><input name='allow_page_template' type='checkbox' $allow_pagespecific_template></td>
	</tr>
	<tr>
	<td>Send Mail on Registration ?</td>
	<td><input name='send_mail_on_reg' type='checkbox' $default_mailverify></td>
	</tr>
	<tr>
	<td>Show Breadcrumbs Submenu ?</td>
	<td><input name='breadcrumb_submenu' type='checkbox' $breadcrumb_submenu></td>
	</tr>
	<tr>
	<td>Default template :</td>
	<td><select name='default_template' >
globalform;

	$templates=getAvailableTemplates();
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
	<td>Activate User On Registration ?</td>
	<td><input name='activate_useronreg' type='checkbox' $activate_useronreg></td>
	</tr>
	<tr>
	<td><input type='hidden' name='update_global_settings' /><input type='submit' value='Update' />
	<input type='button' value='Cancel' onclick="window.open('./+view','_top')" /></td>
	</tr>
	</table>
	</fieldset>
	</form>
globalform;
	return $globalform;
}

function admin() {
	$str = <<<ADMINPAGE
	<fieldset>
	<legend>Website Administration</legend>
	<input type='button' onclick='window.open("./+admin&subaction=global","_top")' value='Global Settings' />
	<input type='button' onclick='window.open("./+admin&subaction=useradmin","_top")' value='User Management' />
	<input type='button' onclick='window.open("./+admin&subaction=expert","_top")' value='Experts Only' />
	</fieldset>
ADMINPAGE;
	
	if(!isset($_GET['subaction'])) return $str;
	require_once("users.lib.php");
	$op="";$ophead="";
	if (((isset($_GET['subaction']) || isset($_GET['subsubaction']))) || (isset ($_GET['id'])) || (isset ($_GET['movePermId']))||(isset ($_GET['module']))) {
		if ($_GET['subaction'] == 'global' && isset($_POST['update_global_settings'])) updateGlobalSettings();
		else if ($_GET['subaction'] == 'useradmin'){ $op .= handleUserMgmt(); $ophead="User Management"; }
		else if ($_GET['subaction'] == 'checkPerm'){ $op .= admin_checkFunctionPerms(); $ophead="Checking Permissions Consistency"; }
		elseif ($_GET['subaction'] == 'checkAdminUser'){ $op .= admin_checkAdminUser(); $ophead="Checking Administrator User"; }
		elseif ($_GET['subaction'] == 'checkAdminPerms'){ $op .= admin_checkAdminPerms(); $ophead="Checking Administrator Permissions"; }
		elseif (($_GET['subaction'] == 'changePermRank')){ $op .= admin_changePermRank(); $ophead="Changing Permissions Rank"; }
		elseif (($_GET['subaction'] == 'editprofileform') ||
			(isset($_GET['subsubaction']) && $_GET['subsubaction'] == 'editprofileform'))
			{ $op .= admin_editProfileForm(); $ophead="Edit User Profile Form"; }
		elseif (($_GET['subaction']) == 'viewsiteregistrants' || $_GET['subaction'] == 'editsiteregistrants') 
			$op .= admin_editRegistrants(); 
		elseif (isset ($_GET['id'])) $op .= admin_userAdmin();
		elseif (isset ($_GET['movePermId'])){ $op .= admin_changePermRank(); $ophead="Changing Permissions Rank"; }
		elseif (isset ($_GET['module'])){ $op .= admin_changePermRank(escape($_GET['module'])); $ophead="Changing Permissions Rank for module '".escape($_GET['module'])."'"; }
	}
	if($op!="")
	{
		$op ="<fieldset><legend>$ophead</legend>$op</fieldset>";
	}
	
	if($_GET['subaction']=='global')
	 $str .= globalSettingsForm();
	else if($_GET['subaction']=='useradmin')
	{
		
		$str .= userManagementForm();
	}
	else
	{
		$str .= "<fieldset><legend>Experts Only</legend>";
		$str .= '<a href="./+admin&subaction=checkPerm">Check Permission List</a><br />';
		$str .= '<a href="./+admin&subaction=checkAdminUser">Check Admin User</a><br />';
		$str .= '<a href="./+admin&subaction=checkAdminPerms">Check Admin Perms</a><br />';
		$str .= '<a href="./+admin&subaction=useradmin">User Administration</a><br />';
		$str .= '<a href="./+admin&subaction=changePermRank">Change Perm Ranks</a><br />';
		$str .= '<a href="./+admin&subaction=editprofileform">Edit User Profile Form</a><br />';
		$str .= '<a href="./+admin&subaction=viewsiteregistrants">View Users Registered to the Website</a><br />';
		$str .= '<a href="./+admin&subaction=editsiteregistrants">Edit Registrants</a><br /></fieldset>';
		
	}
	
	
	return $str.$op;

}

function updateGlobalSettings()
{
	$global=array();
	$global['allow_pagespecific_header']=isset($_POST['allow_page_header'])?1:0;
	$global['allow_pagespecific_template']=isset($_POST['allow_page_template'])?1:0;
	$global['default_user_activate']=isset($_POST['activate_useronreg'])?1:0;
	$global['default_mail_verify']=isset($_POST['send_mail_on_reg'])?1:0;
	$global['breadcrumb_submenu']=isset($_POST['breadcrumb_submenu'])?1:0;

	$global['cms_title']=escape($_POST['cms_title']);
	$global['default_template']=escape($_POST['default_template']);
	$global['cms_email']=escape($_POST['cms_email']);
	$global['upload_limit']=escape($_POST['upload_limit']);

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
		$query = "INSERT INTO `" . MYSQL_DATABASE_PREFIX . "users`( `user_id` ,`user_name` ,`user_email` ,`user_fullname` ,`user_password`  ,`user_activated`)VALUES ( $uid , 'admin', 'admin@cms.org', 'Administrator', '$adminPasswd', '1')";
		
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
					$query = "SELECT * FROM `" . MYSQL_DATABASE_PREFIX . "userpageperm` WHERE `perm_type`='user' AND `usergroup_id`=$user_Id AND `page_id`=0 AND `perm_id`=$val AND `perm_permission`='Y'";
					$result = mysql_query($query) or die(mysql_error());
					if (!mysql_num_rows($result)) {
						$query = "INSERT INTO `" . MYSQL_DATABASE_PREFIX . "userpageperm` (`perm_type`,`page_id`,`usergroup_id`,`perm_id`,`perm_permission`) VALUES ('user','0','$user_Id','$val','Y')";
						$result2 = mysql_query($query);
						if (mysql_affected_rows())
							$returnStr.="User Admin userId=$user_Id has been allotted permission $temp1[perm_action] of module $temp1[page_module] over page 0";
						else
							$returnStr.="Failed to create permission $temp1[perm_action] of module $temp1[page_module] over page 0 for User Admin userId=$user_Id";
					} else {
						$str .= "";
						$str .= "<br>" . $temp1[perm_action] . " of module " . $temp1[page_module];
					}
				}
			}
		}
		if ($str != '')
			$returnStr.="The following permissions exist for user admin<br>" .$str;

	} else {
		$returnStr.=admin_checkAdminUser();
		$returnStr.=admin_checkAdminPerms();
	}
	return $returnStr;
}


/*
 * 7) User management: List of all users, ability to edit everything about them,
 *  ability to activate users, ability to create users
 *
 */

function admin_userAdmin() {
	$query = "SELECT * FROM `" . MYSQL_DATABASE_PREFIX . "users` ORDER BY `user_id` ASC";
	$result = mysql_query($query) or die(mysql_error() . "admin.lib L192");
	$table .=<<<HEAD
	<script language="javascript">
	function checkDelete(butt,userDel,userId)
							{
								 if(confirm('Are you sure you want to delete '+userDel+'?'))
								{


									window.location+="&id="+userId+"&userDel="+userDel;

								}
								else return false;
							}
					    </script>
	<table border="1">
	<td id="user_id">User Id</td>
	<td id="user_name">User Name</td>
	<td id="user_email">User Email</td>
	<td id="user_fullname">Full Name</td>
	<td id="user_password">Password Hash(MD5)</td>
	<td id="user_regdate">Registration date</td>
	<td id="user_lastlogin">Last Login</td>
	<td id="user_activated">Active</td>
	<td id="user_delete">Delete</td>
HEAD;
	$links =<<<LINKS
	<input type="button" onclick="window.open('./+admin&id=search','_top')" value="Search User" />
	<input type="button" onclick="window.open('./+admin&id=new','_top')" value="New User" /><hr/>

LINKS;
	$count = count($_GET);
	if ((!(isset ($_GET['id'])))) {
		while ($temp = mysql_fetch_assoc($result)) {
			$table .= "<tr>";
			foreach ($temp as $var => $val) {
				$table .= "<td><a style=\"cursor:pointer;\" onclick=\"window.location='./+admin&id=$temp[user_id]'\"> $val</a></td>";
			}
						$table.="<td><input type=\"Button\" name=\"deleteUser\" value=\"Delete\" onclick=\"return checkDelete(this,'".$temp['user_name']."','".$temp['user_id']."');\"></td>";
			$table .= "</tr> ";
		}
		$table .= "</table>";
		return  $links.$table;
	}
	if ($_POST['userAdminAction'] == 'Create') {
		foreach ($_POST as $var => $val) {
			$$var = $val;
			{
				if ($val == '') {
					if ((($var == 'user_regdate')) || (($var == 'user_lastlogin')) || ($var == 'user_activated'));
					else {
						displayerror('Please enter ' . $var . ' <a href="./+admin&id=new">Go Back</a>');
						$err = 1;
					}
				}
			}
		}
		if ($err) {
			return null;
		}
		$query = "INSERT INTO `" . MYSQL_DATABASE_PREFIX . "users` (`user_id` ,`user_name` ,`user_email` ,`user_fullname` ,`user_password` ,`user_regdate` ,`user_lastlogin` ,`user_activated`)VALUES ('$user_id' ,'$user_name' ,'$user_email' ,'$user_fullname' , MD5('$user_password') ,CURRENT_TIMESTAMP , '', '$user_activated')";
		$result = mysql_query($query) or die(mysql_error());
		if (mysql_affected_rows()) {
			displayinfo("User Created");
			$user_Id = $_POST['user_id'];
		} else {
			displayerror("Failed to create user");
			return null;
		}

	}
	if(isset($_GET['userDel'])){

//		displaywarning("You are going to delete user $_GET[userDel] <a href=\"./+admin&userDel=$_GET[userDel]&id=$_GET[id]&confirmed\"><I>continue</I></a> Cancel");
		$userId = escape($_GET['id']);
		$userName = escape($_GET['userDel']);
		$query="DELETE FROM `".MYSQL_DATABASE_PREFIX."users` WHERE `user_id` = $userId AND `user_name`='$userName'";
		$resultDel=mysql_query($query) or displayerror(mysql_error());
		if($resultDel)displayinfo("User $userId $userName deleted");
		else displayerror("$resultDel Failed to delete user $userName");
		return null;


	}
	if ((isset ($_GET['id']))) {
		if (($_GET['id'] != 'new') && ($_GET['id'] != 'search') && ($_POST['userAdminAction'] != 'Search')&&(!isset($_GET['userDel']))) {

			$user_Id = escape($_GET['id']);
			if ($user_Id == '')
				$user_Id = $_POST['user_id'];
			$user_Id = $user_Id;
			$query = "SELECT * FROM `" . MYSQL_DATABASE_PREFIX . "users` WHERE `user_id`=$user_Id";
			{
				$result = mysql_query($query);
				if (mysql_num_rows($result) > 0) {
					$temp = mysql_fetch_assoc($result);
					$readonly = "readonly";
					$submitValue = "Save";
					$chngPass =<<<CHG
<td><INPUT type="checkbox" name="changePassword" value="">Change Password?</td>
CHG;
					foreach ($temp as $var => $val) {
						$$var = $val;
					}
				} else {
					displayerror("User id $user_Id Does not exist");
					return null;
				}
			}
			if (isset ($_POST['userAdminAction'])) {
				foreach ($_POST as $var => $val) {
					$$var = $val;
				}
				if (isset ($_POST['changePassword'])) {
					$user_password = md5($user_password);
					$chngPasswd = "`user_password`='$user_password',";
				}
				$querySave = "UPDATE `" . MYSQL_DATABASE_PREFIX . "users` SET `user_name`='$user_name',`user_email`='$user_email',`user_fullname`='$user_fullname',$chngPasswd`user_activated`='$user_activated',`user_loginmethod`='$user_loginmethod' WHERE `user_id`=$user_id";
				$resultSave = mysql_query($querySave) or die(mysql_error());
				if (!mysql_error())
					displayinfo("User data saved");
				else
					displayerror("Failed to save data");
			}
		}
		elseif ($_GET['id'] == 'search') {
			$readonly = "";
			$submitValue = "Search";
			$user_Id = "search";
			$user_activated = 1;
			displayinfo("Search uses AND operator");

		}
		elseif ($_GET['id'] == 'new') {
			$readonly = "readonly";
			$submitValue = "Create";
			$query = "SELECT MAX(user_id) AS MAX FROM `" . MYSQL_DATABASE_PREFIX . "users`";
			$result = mysql_query($query) or die(mysql_error() . "check.lib L:266");
			$row = mysql_fetch_assoc($result);
			$user_id = $row['MAX'] + 1;
			$user_activated = 0;
		}
		if ($_POST['userAdminAction'] == 'Search') {

			$i = 0;
			$readonly = "";
			$submitValue = "Search";
			foreach ($_POST as $var => $val) {
				if ($val == 'Search');
				else {
					if ($val != '') {
						if ($i == 1)
							$string .= " AND ";
						$val = $val;
						$$var = $val;
						$string .= "`$var` LIKE CONVERT( _utf8 '%$val%'USING latin1 ) ";
						$i = 1;
					}
				}
			}
			$query = "SELECT * FROM `" . MYSQL_DATABASE_PREFIX . "users` WHERE $string ";
			$resultSearch = mysql_query($query);
			if (mysql_num_rows($resultSearch) > 0) {
				$num = mysql_num_rows($resultSearch);
				displayinfo("$num results found");
				while ($temp = mysql_fetch_assoc($resultSearch)) {
					$table .= "<tr>";
					foreach ($temp as $var => $val) {
						$table .= "<td><a style=\"cursor:pointer;\" onclick=\"window.location='./+admin&id=$temp[user_id]'\"> $val</a></td>";

					}
					$table.="<td><input type=\"Button\" name=\"deleteUser\" value=\"Delete\" onclick=\"return checkDelete(this,'".$temp[user_name]."','".$temp[user_id]."');\"></td>";
					$table .= "</tr> ";
				}
				$table .= "</table>";
				return $table . $links;
			} else
				displayerror("User not found!");
			$user_Id = "search";
		}
		if ($user_activated == 0)
			$user_active = 1;
		else
			$user_active = 0;
		$editUserForm =<<<FORM
<form method="POST" action="./+admin&id=$user_Id">
<br />
<table>
<input type="hidden" id="userAdminAction" name="userAdminAction" value="$submitValue">

<tr><td>User Id </td><td><input type="text" maxlength="11" id="user_id" name="user_id" value="$user_id" $readonly></td></tr>
<tr><td>User Name </td><td><input type="text" maxlength="100" id="user_name" name="user_name" value="$user_name"></td></tr>
<tr><td>User Email </td><td><input type="text" maxlength="100" id="user_email" name="user_email" value="$user_email"></td></tr>
<tr><td>User FullName </td><td><input type="text" maxlength="100" id="user_fullname" name="user_fullname" value="$user_fullname"></td></tr>
<tr><td>User Password </td><td><input type="password" maxlength="100" id="user_password" name="user_password" value="$user_password"></td>$chngPass</tr>
<tr><td>User Reg Date </td><td><input type="text" maxlength="100" id="user_regdate" name="user_regdate" value="$user_regdate" $readonly></td></tr>
<tr><td>Last Login </td><td><input type="text" maxlength="100" id="user_lastlogin" name="user_lastlogin" value="$user_lastlogin" $readonly></td></tr>
<tr><td>User Activated </td><td><select id="user_activated" name="user_activated" style="width: 10mm" >
<option selected="selected">$user_activated</option>
<option>$user_active</option>
</select>
</td></tr>
<tr><td>User Login Method </td><td><select id="user_loginmethod" name="user_loginmethod" style="width: 60mm" >
<option selected="selected">ldap</option>
<option>imap</option>
<option>ads</option>
<option>db</option>
</select>
</td></tr>
<tr><td><input type="submit" value="$submitValue" > <input type="reset"></td></tr></table>
</form>
FORM;
		return $editUserForm . $links;
	}
}


/*
 * 8) Ability to change perm ranks (like page move up and move )
 *
 * */

function admin_changePermRank($module="") {
	require_once("tbman_executer.lib.php");

	$table = new tbman_executer("SELECT * FROM pragyanV2_permissionlist");
	$table->formaction="./+admin&subaction=changePermRank";
	return $table->execute();
	
}


function admin_editProfileForm() {
	include_once('profile.lib.php');
	return getProfileFormEditForm();
}

function admin_editRegistrants() {
	include_once('profile.lib.php');
	return getProfileViewRegistrantsForm();
}


