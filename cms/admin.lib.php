<?php

exit();
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


	/*  Consistency check :
1) Check all available classes (modules)

2) See, if all their functions actionView(), actionEdit(), actionX exist
in the perms table or not (and create also). If
not, give option to create that permission.

3) See if any extra option exists in the database, if it does, warn the user.

4) See if any user with the name admin exists or not. If it does not,
create it and give it a random and display all required information,

5) See if the admin user has all perms at page 0 or not. If not, give him
all perms and inform him

6) See if all minimum rows n tables required for the cms to run exist or
not, if they do not, create them.

7) User management: List of all users, ability to edit everything about
them, ability to activate users, ability to create users

8) Ability to change perm ranks (like page move up and move )
 *
 *
 * */

function admin() {
//	print_r($_GET);
//	print_r($_POST);
	$str = "";
	if ((($_GET['action'] == 'admin') && (isset($_GET['subaction']) || isset($_GET['subsubaction']))) || (isset ($_GET['id'])) || (isset ($_GET['movePermId']))||(isset ($_GET['module']))) {
		if ($_GET['subaction'] == 'checkPerm')
			$str .= admin_checkFunctionPerms();
		elseif ($_GET['subaction'] == 'checkAdminUser') $str .= admin_checkAdminUser();
		elseif ($_GET['subaction'] == 'checkAdminPerms') $str .= admin_checkAdminPerms();
		elseif ($_GET['subaction'] == 'useradmin') $str .= admin_userAdmin();
		elseif (($_GET['subaction'] == 'changePermRank')) $str .= admin_changePermRank();
		elseif (($_GET['subaction'] == 'editprofileform') ||
			(isset($_GET['subsubaction']) && $_GET['subsubaction'] == 'editprofileform')) $str .= admin_editProfileForm();
		elseif (($_GET['subaction']) == 'viewsiteregistrants' || $_GET['subaction'] == 'editsiteregistrants') $str .= admin_editRegistrants();
		elseif (isset ($_GET['id'])) $str .= admin_userAdmin();
		elseif (isset ($_GET['movePermId'])) $str .= admin_changePermRank();
		elseif (isset ($_GET['module'])) $str .= admin_changePermRank($_GET['module']);
	}

	$str .= "<hr />";
	$str .= '<br><a href="./+admin&subaction=checkPerm">Check Permission List</a><br />';
	$str .= '<a href="./+admin&subaction=checkAdminUser">Check Admin User</a><br />';
	$str .= '<a href="./+admin&subaction=checkAdminPerms">Check Admin Perms</a><br />';
	$str .= '<a href="./+admin&subaction=useradmin">User Administration</a><br />';
	$str .= '<a href="./+admin&subaction=changePermRank">Change Perm Ranks</a><br />';
	$str .= '<a href="./+admin&subaction=editprofileform">Edit User Profile Form</a><br />';
	$str .= '<a href="./+admin&subaction=viewsiteregistrants">View Users Registered to the Website</a><br />';
	$str .= '<a href="./+admin&subaction=editsiteregistrants">Edit Registrants</a><br />';
	return $str;

}

function admin_checkFunctionPerms() {
	global $sourceFolder;
	//1) Check all available classes (modules)
	if ($handle = opendir($sourceFolder . '/modules')) {
		while (false !== ($file = readdir($handle))) {
			$list[] = $file;
		}
		closedir($handle);
	}
	foreach ($list as $temp) {
		if (strpos($temp, '.lib.php')) {
			$moduleArray[] = str_replace('.lib.php', '', $temp);
		}
	}
	$moduleList = "";
	foreach ($moduleArray as $module) {
		$moduleList .= $module . ", ";
	}
	$moduleList .= "";
	displayinfo("The following modules/classes exist in the file system:<br>$moduleList");
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
		if (($module != 'forum') && ($module != 'poll')/* && ($module != 'gallery')*/) {
			require_once ($sourceFolder . "/" . $moduleFolder . "/" . $module . ".lib.php");
			$functionArray = get_class_methods($module);
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
			displayinfo("The following methods/functions/actions exist in the filesystem class for $module:<br> $permList");
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
					displaywarning("<b>$permission DOES NOT exist for $module but will be created</b><br>");
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
			displayinfo("The following permissions exist in database for $module :<br>$permExists");
		}
	}
	//3) See if any extra option exists in the database, if it does, warn the user.

	foreach ($moduleArray as $module) {
		if (($module != 'forum') && ($module != 'poll')/* && ($module != 'gallery')*/) {
			require_once ($sourceFolder . "/" . $moduleFolder . "/" . $module . ".lib.php");
			$class = new $module ();
			$query = "SELECT * FROM `" . MYSQL_DATABASE_PREFIX . "permissionlist` WHERE `page_module`='$module'";
			$result = mysql_query($query);
			while ($tempres = mysql_fetch_assoc($result)) {

				$permName = ucfirst($tempres['perm_action']);
				$method = "action" . $permName;

				if (!(method_exists($class, $method)))
					displaywarning("Permission $method, perm id = $tempres[perm_id] exists in database but not in class $module");

			}

		}
	}
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
		echo $query;
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
							displayinfo("User Admin userId=$user_Id has been allotted permission $temp1[perm_action] of module $temp1[page_module] over page 0");
						else
							displayerror("failed to createpermission $temp1[perm_action] of module $temp1[page_module] over page 0 for User Admin userId=$user_Id  ");
					} else {
						$str .= "";
						$str .= "<br>" . $temp1[perm_action] . " of module " . $temp1[page_module];
					}
				}
			}
		}
		if ($str != '')
			displayinfo("The following permissions exist for user admin<br>" .$str);

	} else {
		admin_checkAdminUser();
		admin_checkAdminPerms();
	}
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
<a href="./+admin&id=search">Search</a><br>
<a href="./+admin&id=new">New User?</a><br>
<a href="./+admin&subaction=useradmin">User List</a>
LINKS;
	$count = count($_GET);
	if ((!(isset ($_GET['id'])))) {
		while ($temp = mysql_fetch_assoc($result)) {
			$table .= "<tr>";
			foreach ($temp as $var => $val) {
				$table .= "<td><a style=\"cursor:pointer;\" onclick=\"window.location='./+admin&id=$temp[user_id]'\"> $val</a></td>";
			}
						$table.="<td><input type=\"Button\" name=\"deleteUser\" value=\"Delete\" onclick=\"return checkDelete(this,'".$temp[user_name]."','".$temp[user_id]."');\"></td>";
			$table .= "</tr> ";
		}
		$table .= "</table>";
		return $table . $links;
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
		$query = "INSERT INTO `" . MYSQL_DATABASE_PREFIX . "users` (`user_id` ,`user_name` ,`user_email` ,`user_fullname` ,`user_password` ,`user_regdate` ,`user_lastlogin` ,`user_activated`)VALUES ('$user_id' ,'$user_name' ,'$user_email' ,'$user_fullname' ,'$user_password' ,CURRENT_TIMESTAMP , '', '$user_activated')";
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
		$userId = $_GET['id'];
		$userName = $_GET['userDel'];
		$query="DELETE FROM `".MYSQL_DATABASE_PREFIX."users` WHERE `user_id` = $userId AND `user_name`='$userName'";
		$resultDel=mysql_query($query) or displayerror(mysql_error());
		if($resultDel)displayinfo("User $userId $userName deleted");
		else displayerror("$resultDel Failed to delete user $userName");
//		admin_userAdmin();
		return null;


	}
	if ((isset ($_GET['id']))) {
		if (($_GET['id'] != 'new') && ($_GET['id'] != 'search') && ($_POST['userAdminAction'] != 'Search')&&(!isset($_GET['userDel']))) {

			$user_Id = $_GET['id'];
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
				$querySave = "UPDATE `" . MYSQL_DATABASE_PREFIX . "users` SET `user_name`='$user_name',`user_email`='$user_email',`user_fullname`='$user_fullname',$chngPasswd`user_activated`='$user_activated' WHERE `user_id`=$user_id";
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

function admin_changePermRank($module) {
//	echo "KJASJKJAHSKJHASK";
//echo $module;
	if(isset($_GET['subaction'])){
		$query="SELECT DISTINCT `page_module` FROM `".MYSQL_DATABASE_PREFIX."permissionlist`" or die(mysql_error());
		$resultModArray=mysql_query($query);
		$selectModule.="<DIV style=\"padding-left:50px; \">SELECT MODULE TYPE:<br>";
		while($resultMod=mysql_fetch_assoc($resultModArray)){
		foreach ($resultMod as $permMod){
		$selectModule.=<<<MOD

		<a href="+admin&module=$permMod" style="text-decoration:none;">  $permMod      </a><br>

MOD;
	}}
	$selectModule.="</div>";
return $selectModule;
	}
	else{

		$module = $_GET['module'];
//		echo $module;



{


	if ((isset ($_POST['moveDn'])) || ((isset ($_POST['moveUp'])))) {
		if (isset ($_POST['moveDn'])) {
			$compare = ">=";
			$order = "ASC";
		} else {
			$compare = "<=";
			$order = "DESC";
		}

		$permId = $_GET['movePermId'];
		$query = "SELECT * FROM `" . MYSQL_DATABASE_PREFIX . "permissionlist` WHERE `perm_rank`$compare(SELECT `perm_rank` FROM `" . MYSQL_DATABASE_PREFIX . "permissionlist` WHERE `perm_id`=$permId) AND `page_module`=(SELECT `page_module` FROM `" . MYSQL_DATABASE_PREFIX . "permissionlist` WHERE `perm_id`=$permId) AND `perm_id`!='$permId' ORDER BY `perm_rank` $order LIMIT 0,1";
		//		echo "<br>" . $query . "<br>";
		$result = mysql_query($query) or die(mysql_query());
		if (mysql_num_rows($result) == 0) {
			displayerror("You cannot move up/down the first/last page in menu");

		} else {
			$tempTarg = mysql_fetch_assoc($result);
			$query = "SELECT `perm_rank` FROM `" . MYSQL_DATABASE_PREFIX . "permissionlist` WHERE `perm_id`=$permId";
			$result = mysql_query($query) or die(mysql_query());
			$tempSrc = mysql_fetch_assoc($result);
			//		echo "ANSHU".mysql_num_rows($result);

			if ($tempTarg['perm_rank'] == $tempSrc['perm_rank']) {
				$query = "UPDATE `" . MYSQL_DATABASE_PREFIX . "permissionlist` SET `perm_rank` = `perm_id` WHERE `page_module`='$tempTarg[page_module]'";
				//				echo "<br>" . $query;
				$result = mysql_query($query) or die(mysql_error());
				if (mysql_affected_rows() > 0)
					displayinfo("Error in perm rank corrected. Please reorder the perm ranks");
				else
					displayerror("Failed to correct error in Perm ranks!");
			} else {
				$query = "UPDATE `" . MYSQL_DATABASE_PREFIX . "permissionlist` SET `perm_rank` = '$tempSrc[perm_rank]' WHERE `perm_id`='$tempTarg[perm_id]'";
				$result = mysql_query($query) or die(mysql_error());
				$query = "UPDATE `" . MYSQL_DATABASE_PREFIX . "permissionlist` SET `perm_rank` = '$tempTarg[perm_rank]' WHERE `perm_id`='$permId'";
				$result = mysql_query($query) or die(mysql_error());

			}

		}
	}
	if ($_GET['permId'] == 'all') {

		$query = "SELECT * FROM `" . MYSQL_DATABASE_PREFIX . "permissionlist` WHERE `page_module`=(SELECT `page_module` FROM`" . MYSQL_DATABASE_PREFIX . "permissionlist` WHERE `perm_id`= $_GET[movePermId])";
		$temp = mysql_query($query) or die(mysql_error() . "admin,libL:595");
		while ($result = mysql_fetch_assoc($temp)) {
			foreach ($result as $var => $val) {
				if ($var == 'perm_text') {
					$permText = $_POST['perm_text' . $result['perm_id']];
				}
				elseif ($var == 'perm_description') {
					$permDescription = $_POST['perm_description' . $result['perm_id']];
				}
			}
			$permText = $permText;
			$permDescription = $permDescription;
			$query = "UPDATE `" . MYSQL_DATABASE_PREFIX . "permissionlist` SET `perm_text`='$permText',`perm_description`='$permDescription' WHERE `perm_id`=$result[perm_id]";
			$resultSave = mysql_query($query) or die(mysql_error()); //UPDATE, DELETE, DROP, etc, mysql_query() returns TRUE on success or FALSE on error.
			if ($resultSave)
				displayinfo("Saved perm text and description for perm id = $result[perm_id]");
			else
				displayerror("Failed to save perm text and description for perm id = $result[perm_id]");
		}
	}
	elseif(isset($_POST['savePermDetails']))
	{
		$permText=$_POST['perm_text'.$_GET['movePermId']];
		$permDescription=$_POST['perm_description'.$_GET['movePermId']];
		$permId = $_GET['movePermId'];
		$query = "UPDATE `" . MYSQL_DATABASE_PREFIX . "permissionlist` SET `perm_text`='$permText',`perm_description`='$permDescription' WHERE `perm_id`=$permId";
			$resultSave = mysql_query($query) or die(mysql_error()); //UPDATE, DELETE, DROP, etc, mysql_query() returns TRUE on success or FALSE on error.
			if ($resultSave)
				displayinfo("Saved perm text and description for perm id = $permId");
			else
				displayerror("Failed to save perm text and description for perm id = $permId");


	}

	$permString .=<<<PERM
<form method="POST" action="./+admin&module=$module&movePermId=" >
<table border="1">
<tr>
<td><b>Perm Id</b></td>
<td><b>Page Module</b></td>
<td><b>Perm Action</b></td>
<td><b>Perm text</b></td>
<td><b>Perm Rank</b></td>
<td><b>Perm Description</b></td>
<td><b>Move Up</b></td>
<td><b>Move Down</b></td>
<td><b>Save</b></td>

</tr>
PERM;

	$page_module = $module;
//	$page_module = "article"; //HARDCODED FOR THE TIME BEING> TO BE PASSED IN TO THE FUNCTION WHILE CALLING IT
	$query = "SELECT * FROM `" . MYSQL_DATABASE_PREFIX . "permissionlist` WHERE `page_module`='$page_module' ORDER BY `perm_rank` ASC";
	$result = mysql_query($query) or die(mysql_error());
	static $permId;
	while ($temp = mysql_fetch_assoc($result)) {
		$permString .= "<tr>";
		foreach ($temp as $var => $val) {
			if (($var == 'perm_text') || ($var == 'perm_description'))
				$permString .= '<td><input type="text" size="15" name="' .
				$var . $temp[perm_id] . '" value="' . $val . '"></td>';
			else
				$permString .= "<td><center>$val</center></td>";
		}
		$permString .= '<td align="center"><input type="submit" name="moveUp" onclick="this.form.action+=\'' . $temp['perm_id'] . '\'" value="Move Up" /></td>' .
		'<td align="center"><input type="submit" name="moveDn" onclick="this.form.action+=\'' . $temp['perm_id'] . '\'" value="Move Down" /></td>' .
		'<td><input type="submit" name="savePermDetails" onclick="this.form.action+=\'' . $temp[perm_id] . '\'" value="Save">' .
		'</tr>';
		$permId = $temp['perm_id'];
	}
	$permString .= '<tr><td><input type="submit" name="savePermDetailsAll" onclick="this.form.action+=\'' . $permId . '&permId=all\'" value="Save All"></td></tr>' . "</table></form>";

	return $permString;

}}}


function admin_editProfileForm() {
	include_once('profile.lib.php');
	return getProfileFormEditForm();
}

function admin_editRegistrants() {
	include_once('profile.lib.php');
	return getProfileViewRegistrantsForm();
}

?>