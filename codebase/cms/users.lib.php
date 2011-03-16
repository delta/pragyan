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
 * @author Abhishek Shrivastava
 * @description User management for Pragyan CMS : Ability to create, search, activate, delete and edit user information.
 * @copyright (c) 2010 Pragyan Team
 * @license http://www.gnu.org/licenses/ GNU Public License
 * For more details, see README
 */
//TODO : Implement Search based on user profile fields
function userManagementForm()
{
	global $ICONS;
	global $urlRequestRoot, $cmsFolder, $moduleFolder, $templateFolder,$sourceFolder;
	require_once("$sourceFolder/$moduleFolder/form/viewregistrants.php");
	$usermgmtform=<<<USERFORM
	<script type='text/javascript' language='javascript'>
	function checkAll(formobj)
	{
		for(i=0;i<formobj.elements.length;i++)
		{
			
			if(formobj.elements[i].type=='checkbox') formobj.elements[i].checked=true;
		}
	}
	function unCheckAll(formobj)
	{
		for(i=0;i<formobj.elements.length;i++)
		{
			
			if(formobj.elements[i].type=='checkbox') formobj.elements[i].checked=false;
		}
	}
	</script>
	<form name='user_mgmt_form' action='./+admin&subaction=useradmin' method='POST'>
	<fieldset>
	<legend>{$ICONS['User Management']['small']}User Management</legend>
	
	Select Fields to Display : <input type='button' onclick='return checkAll(this.form);' value='Check All' /><input type='button' onclick='return unCheckAll(this.form);' value='Uncheck All' />
	<table><tr><td>Field Name</td><td>Display ?</td><td>Field Name</td><td>Display ?</td><td>Field Name</td><td>Display ?</td></tr>
USERFORM;
	
	$xcolumnNames=array_keys(getColumnList(0, false, false, false, false, false));
	$xcolumnPrettyNames=array_values(getColumnList(0, false, false, false, false, false));
	$usertablefields=array_merge(getTableFieldsName('users'),$xcolumnNames);
	$userfieldprettynames=array_merge(array("User ID","Username","Email","Full Name","Password","Registration","Last Login","Activated","Login Method"),array_map('ucfirst',$xcolumnPrettyNames));
	$cols=3;
	for($i=0;$i<count($usertablefields);$i=$i+$cols)
	{	
		$usermgmtform.="<tr>";
		for($j=0;$j<$cols;$j++)
		{
			if($i+$j<count($usertablefields))
			{
				$checked="";
				if(isset($_POST['not_first_time']))
					$checked=isset($_POST[$usertablefields[$i+$j].'_sel'])?"checked":"";
				else if($usertablefields[$i+$j]=="user_fullname" || $usertablefields[$i+$j]=="user_email" || $usertablefields[$i+$j]=="user_activated")
					$checked="checked";
				
				$usermgmtform.="<td>{$userfieldprettynames[$i+$j]}</td><td><input type='checkbox' name='{$usertablefields[$i+$j]}_sel' $checked /></td>";
			}
		}
		$usermgmtform.="</tr>";
	}
	global $ICONS_SRC;
	$usermgmtform.=<<<USERFORM
	<input type='hidden' name='not_first_time' />
	</table>
	<fieldset style="float:left;">
	<legend>All Registered</legend>
	<input type='submit' value='View' name='view_reg_users'/>
	<input type='submit' value='Edit' name='edit_reg_users'/>
	<input type='submit' value='Save as Excel' name='save_reg_users_excel'/>
	</fieldset>&nbsp;
	<fieldset style="float:left;">
	<legend>Activated Users</legend>
	<input type='submit' value='View' name='view_activated_users'/>
	<input type='submit' value='Edit' name='edit_activated_users'/>
	<input type='submit' value='Save as Excel' name='save_activated_users_excel'/>
	</fieldset>&nbsp;
	<fieldset style="float:left;">
	<legend>Non-Activated Users</legend>
	<input type='submit' value='View' name='view_nonactivated_users'/>
	<input type='submit' value='Edit' name='edit_nonactivated_users'/>
	<input type='submit' value='Save as Excel' name='save_nonactivated_users_excel'/>
	</fieldset>
	<div style="clear:both"></div>
	<hr/>
	<table class='iconspanel'>
	<tr>
	<td>
	<input type="image" alt="Search User" src='{$ICONS_SRC['Search']['large']}' onclick="this.form.action+='&subsubaction=search'" value="Search User" /><br/>Search User
	</td>
	<td>
	<input type="image" alt="New User" src='{$ICONS_SRC['New User']['large']}' onclick="this.form.action+='&subsubaction=create'" value="New User" /><br/>New User
	</td>
	<td>
	<input type='image' alt="Deactivate All Users" src='{$ICONS_SRC['Deactivate']['large']}' value='Deactivate All' name='deactivate_all_users'/><br/>Deactivate All Users
	</td>
	<td>
	<input type='image' alt="Activate All Users" src='{$ICONS_SRC['Activate']['large']}' value='Activate All' name='activate_all_users'/><br/>Activate All Users
	</td>
	</tr>
	</table>
	</fieldset>
	
	
	</form>
USERFORM;
	return $usermgmtform;
}
function handleUserMgmt()
{
	global $urlRequestRoot, $cmsFolder, $moduleFolder, $templateFolder,$sourceFolder;
	require_once("$sourceFolder/$moduleFolder/form/viewregistrants.php");
	if(isset($_GET['userid']))
	 $_GET['userid']=escape($_GET['userid']);
	if(isset($_POST['editusertype'])) $_POST['editusertype']=escape($_POST['editusertype']);
	if(isset($_POST['user_selected_activate'])) {
		foreach($_POST as $key => $var)
			if(substr($key,0,9)=="selected_") {
				if(!mysql_query("UPDATE ".MYSQL_DATABASE_PREFIX."users SET user_activated=1 WHERE user_id='".substr($key,9)."'")) {
					$result = mysql_query("SELECT `user_fullname` FROM `".MYSQL_DATABASE_PREFIX."users` WHERE `user_id`='".substr($key,9)."'");
					if($result) {
						$row = mysql_fetch_assoc($result);
						displayerror("Couldn't activate user, {$row['user_fullname']}");
					}
				}
			}
		return registeredUsersList($_POST['editusertype'],"edit",false);
	}
	if(isset($_POST['user_selected_deactivate'])) {
		foreach($_POST as $key => $var)
			if(substr($key,0,9)=="selected_") {
				if((int)substr($key,9)==ADMIN_USERID) {
					displayerror("You cannot deactivate administrator!");
					continue;
				}
				if(!mysql_query("UPDATE ".MYSQL_DATABASE_PREFIX."users SET user_activated=0 WHERE user_id='".substr($key,9)."'")) {
					$result = mysql_query("SELECT `user_fullname` FROM `".MYSQL_DATABASE_PREFIX."users` WHERE `user_id`='".substr($key,9)."'");
					if($result) {
						$row = mysql_fetch_assoc($result);
						displayerror("Couldn't deactivate user, {$row['user_fullname']}");
					}
				}
			}
		return registeredUsersList($_POST['editusertype'],"edit",false);
	}
	if(isset($_POST['user_selected_delete'])) {
		$done = true;
		foreach($_POST as $key => $var)
			if(substr($key,0,9)=="selected_") {
				if((int)substr($key,9)==ADMIN_USERID) {
					displayerror("You cannot delete administrator!");
					continue;
				}
				$query="DELETE FROM `".MYSQL_DATABASE_PREFIX."users` WHERE `user_id` = '".substr($key,9)."'";
				if(mysql_query($query)) {
					$query="DELETE FROM `".MYSQL_DATABASE_PREFIX."openid_users` WHERE `user_id` = '".substr($key,9)."'";
					if(!mysql_query($query))
						$done = false;
				} else
					$done = false;
			}
		if(!$done)
			displayerror("Some problem in deleting selected users");
		return registeredUsersList($_POST['editusertype'],"edit",false);
	}
	if(isset($_POST['user_activate']))
	{
		$query="UPDATE ".MYSQL_DATABASE_PREFIX."users SET user_activated=1 WHERE user_id='{$_GET['userid']}'";
		if(mysql_query($query))
			displayInfo("User Successfully Activated!");
		else displayerror("User Not Activated!");
		return registeredUsersList($_POST['editusertype'],"edit",false);
	}
	else if(isset($_POST['activate_all_users']))
	{
		
		$query="UPDATE ".MYSQL_DATABASE_PREFIX."users SET user_activated=1";
		if(mysql_query($query))
			displayInfo("All users activated successfully!");
		else displayerror("Users Not Deactivated!");
		
		return;
	}
	else if(isset($_POST['user_deactivate']))
	{
		if($_GET['userid']==ADMIN_USERID)
		{
			displayError("You cannot deactivate administrator!");
			return registeredUsersList($_POST['editusertype'],"edit",false);
		}
		$query="UPDATE ".MYSQL_DATABASE_PREFIX."users SET user_activated=0 WHERE user_id='{$_GET['userid']}'";
		if(mysql_query($query))
			displayInfo("User Successfully Deactivated!");
		else displayerror("User Not Deactivated!");
		
		return registeredUsersList($_POST['editusertype'],"edit",false);
	}
	else if(isset($_POST['deactivate_all_users']))
	{
		
		$query="UPDATE ".MYSQL_DATABASE_PREFIX."users SET user_activated=0 WHERE user_id != ".ADMIN_USERID;
		if(mysql_query($query))
			displayInfo("All users deactivated successfully except Administrator!");
		else displayerror("Users Not Deactivated!");
		
		return;
	}
	else if(isset($_POST['user_delete']))
	{
		$userId=$_GET['userid'];
		if($userId==ADMIN_USERID)
		{
			displayError("You cannot delete administrator!");
			return registeredUsersList($_POST['editusertype'],"edit",false);
		}
		$query="DELETE FROM `".MYSQL_DATABASE_PREFIX."users` WHERE `user_id` = '$userId'";
		if(mysql_query($query))
		{
			$query="DELETE FROM `".MYSQL_DATABASE_PREFIX."openid_users` WHERE `user_id` = '$userId'";
			if(mysql_query($query))
			{
				displayinfo("User Successfully Deleted!");
			}
			else displayerror("User not deleted from OpenID database!");
		}
		else displayerror("User Not Deleted!");
		
		
		return registeredUsersList($_POST['editusertype'],"edit",false);
		
	}
	else if(isset($_POST['user_info']) || (isset($_POST['user_info_update'])))
	{	
		if(isset($_POST['user_info_update']))
		{
			$updates = array();
			$userId=$_GET['userid'];
			$query="SELECT * FROM `".MYSQL_DATABASE_PREFIX."users` WHERE `user_id`='{$userId}'";
			$row=mysql_fetch_assoc(mysql_query($query));
			$errors = false;
			
			if(isset($_POST['user_name']) && $row['user_name']!=$_POST['user_name'])
			{
				$chkquery="SELECT * FROM `".MYSQL_DATABASE_PREFIX."users` WHERE `user_name`='".escape($_POST['user_name'])."'";
				$result=mysql_query($chkquery) or die("failed  : $chkquery");
				if(mysql_num_rows($result)>0) 
				{
					displayerror("User Name already exists in database!");
					$errors=true;
				}
				
			}
			
			
			if (isset($_POST['user_name']) && $_POST['user_name'] != ''  && $_POST['user_name']!=$row['user_name']) {
				$updates[] = "`user_name` = '".escape($_POST['user_name'])."'";
				
			}
			if (isset($_POST['user_email']) && $_POST['user_email'] != ''  && $_POST['user_email']!=$row['user_email']) {
				$updates[] = "`user_email` = '".escape($_POST['user_email'])."'";
				
			}
			if (isset($_POST['user_fullname']) && $_POST['user_fullname'] != ''  && $_POST['user_fullname']!=$row['user_fullname']) {
				$updates[] = "`user_fullname` = '".escape($_POST['user_fullname'])."'";
				
			}
			
			if ($_POST['user_password'] != '') {
				
				if ($_POST['user_password'] != $_POST['user_password2']) {
					displayerror('Error! The New Password you entered does not match the password you typed in the Confirmation Box.');					$errors=true;
				}
				else if(md5($_POST['user_password']) != $row['user_password']) {
					$updates[] = "`user_password` = MD5('{$_POST['user_password']}')";
					
				}
			}
			if (isset($_POST['user_regdate']) && $_POST['user_regdate'] != ''  && $_POST['user_regdate']!=$row['user_regdate']) {
				$updates[] = "`user_regdate` = '".escape($_POST['user_regdate'])."'";
				
			}
			if (isset($_POST['user_lastlogin']) && $_POST['user_lastlogin'] != ''  && $_POST['user_lastlogin']!=$row['user_lastlogin']) {
				$updates[] = "`user_lastlogin` = '".escape($_POST['user_lastlogin'])."'";
				
			}
			if ($_GET['userid']!=ADMIN_USERID && (isset($_POST['user_activated'])?1:0)!=$row['user_activated']) {
				$checked=isset($_POST['user_activated'])?1:0;
				$updates[] = "`user_activated` = $checked";
				
			}
			if (isset($_POST['user_loginmethod']) && $_POST['user_loginmethod'] != ''  && $_POST['user_loginmethod']!=$row['user_loginmethod']) 	{
				$updates[] = "`user_loginmethod` = '".escape($_POST['user_loginmethod'])."'";
				if($_POST['user_loginmethod']!='db')
				displaywarning("Please make sure ".strtoupper(escape($_POST['user_loginmethod']))." is configured properly, otherwise the user will not be able to login to the website.");
			}

			if(!$errors) {
				if(count($updates) > 0)
				{
					$profileQuery = 'UPDATE `' . MYSQL_DATABASE_PREFIX . 'users` SET ' . join($updates, ', ') . " WHERE `user_id` = ".escape($_GET['userid'])."'";
					$profileResult = mysql_query($profileQuery);
					if(!$profileResult) {
					displayerror('An error was encountered while attempting to process your request.'.$profileQuery);
					$errors = true;
					}
				}
				global $sourceFolder,$moduleFolder;
		require_once("$sourceFolder/$moduleFolder/form/registrationformsubmit.php");
		require_once("$sourceFolder/$moduleFolder/form/registrationformgenerate.php");
				if(!$errors && !submitRegistrationForm(0, $userId, true, true)) {
					displayerror('An error was encountered while attempting to process your request.'.$profileQuery);
					$errors = true;
				}
				else displayinfo('All fields updated successfully!');
			}
			
				
				
			
		}
		
		$userid=$_GET['userid'];
		$query="SELECT * FROM `".MYSQL_DATABASE_PREFIX."users` WHERE `user_id`=$userid";
		$columnList=getColumnList(0,false,false,false,false,false);
		$xcolumnIds=array_keys($columnList);
		$xcolumnNames=array_values($columnList);
		
		$row=mysql_fetch_assoc(mysql_query($query));
		
		
		$userfieldprettynames=array("User ID","Username","Email","Full Name","Password","Registration","Last Login","Activated","Login Method");	
		
		$userinfo="<fieldset><legend>Edit User Information</legend><form name='user_info_edit' action='./+admin&subaction=useradmin&userid=$userid' method='post'>";
		
		
		
		
		$usertablefields=array_merge(getTableFieldsName('users'),$xcolumnNames);

		for($i=0;$i<count($usertablefields);$i++)
			if(isset($_POST[$usertablefields[$i].'_sel']))
				$userinfo.="<input type='hidden' name='{$usertablefields[$i]}_sel' value='checked'/>";
		$userinfo.="<input type='hidden' name='not_first_time' />";
		
	
		
		$userinfo.=userProfileForm($userfieldprettynames,$row,false,true);
		$userinfo.="<input type='submit' value='Update' name='user_info_update' />
		<input type='reset' value='Reset' /></form></fieldset>";
		return $userinfo;
	
	
	}
	else if(isset($_POST['view_reg_users']) || isset($_POST['save_reg_users_excel']))
	{
		return registeredUsersList("all","view",false);
	}
	else if(isset($_POST['edit_reg_users']))
	{
		return registeredUsersList("all","edit",false);
	}
	
	else if(isset($_POST['view_activated_users']) || isset($_POST['save_activated_users_excel']))
	{
		return registeredUsersList("activated","view",false);
	}
	else if(isset($_POST['edit_activated_users']))
	{
		return registeredUsersList("activated","edit",false);
	}
	else if(isset($_POST['view_nonactivated_users']) || isset($_POST['save_nonactivated_users_excel']))
	{
		return registeredUsersList("nonactivated","view",false);
	}
	else if(isset($_POST['edit_nonactivated_users']))
	{
		return registeredUsersList("nonactivated","edit",false);
	}
	else if(isset($_GET['subsubaction']) && $_GET['subsubaction']=='search')
	{
	
		$results="";
		
		
		$userfieldprettynames=array("User ID","Username","Email","Full Name","Password","Registration","Last Login","Activated","Login Method");	
		

		$usertablefields=getTableFieldsName('users');
		
		$first=true;
		
		$qstring="";
		foreach ($usertablefields as $field) {
			if(isset($_POST[$field]) && $_POST[$field]!='')
			{
				if ($first == false)
					$qstring .= ($_POST['user_search_op']=='and')?" AND ":" OR ";
				$val=escape($_POST[$field]);
				if($field=='user_activated') ${$field.'_lastval'}=$val=isset($_POST[$field])?1:0;
				else ${$field.'_lastval'}=$val;
				$qstring .= "`$field` LIKE CONVERT( _utf8 '%$val%'USING latin1 ) ";
				$first=false;
			}
		}
		if($qstring!="")
		{
			$query = "SELECT * FROM `" . MYSQL_DATABASE_PREFIX . "users` WHERE $qstring ";
			$resultSearch = mysql_query($query);
			if (mysql_num_rows($resultSearch) > 0) {
				$num = mysql_num_rows($resultSearch);
				
				$userInfo=array();
				
				
				while($row=mysql_fetch_assoc($resultSearch))
				{
					$userInfo['user_id'][]=$row['user_id'];
					$userInfo['user_name'][]=$row['user_name'];
					$userInfo['user_email'][]=$row['user_email'];
					$userInfo['user_fullname'][]=$row['user_fullname'];
					$userInfo['user_password'][]=$row['user_password'];
					$userInfo['user_lastlogin'][]=$row['user_lastlogin'];
					$userInfo['user_regdate'][]=$row['user_regdate'];
					$userInfo['user_activated'][]=$row['user_activated'];
					$userInfo['user_loginmethod'][]=$row['user_loginmethod'];	
				}
				$results=registeredUsersList("all","edit",false,$userInfo);
			} else
				displayerror("No users matched your query!");
			
		}
		
		$searchForm="<form name='user_search_form' action='./+admin&subaction=useradmin&subsubaction=search' method='POST'><h3>Search User</h3>";
		$xcolumnNames=array_keys(getColumnList(0, false, false, false, false, false));
		$usertablefields2=array_merge($usertablefields,$xcolumnNames);
		for($i=0;$i<count($usertablefields2);$i++)
			if(isset($_POST[$usertablefields2[$i].'_sel']))
				$searchForm.="<input type='hidden' name='{$usertablefields2[$i]}_sel' value='checked'/>";
		$searchForm.="<input type='hidden' name='not_first_time' />";
		
		$infoarray=array();
		foreach ($usertablefields as $field)
			if(isset(${$field.'_lastval'}))
				$infoarray[$field]=${$field.'_lastval'};
			else $infoarray[$field]="";
			
		$searchForm.=userProfileForm($userfieldprettynames,$infoarray,true,false);
		
		$searchForm.="Operation : <input type='radio' name='user_search_op' value='and'  />AND  <input type='radio' name='user_search_op' value='or' checked='true' />OR<br/><br/><input type='submit' onclick name='user_search_submit' value='Search' /><input type='reset' value='Clear' /></form>";
		return $results.$searchForm;
		
		
	}
	
	else if(isset($_GET['subsubaction']) && $_GET['subsubaction']=='create')
	{
		
		
		$userfieldprettynamesarray=array("User ID","Username","Email","Full Name","Password","Registration","Last Login","Activated","Login Method");	
		
		$usertablefields=getTableFieldsName('users');
		
		if(isset($_POST['create_user_submit']))
		{
			$incomplete=false;
			foreach($usertablefields as $field)
			{
				if(($field != 'user_regdate') && ($field != 'user_lastlogin') && ($field != 'user_activated') && (isset($_POST[$field]) && $_POST[$field]==""))
				{
					displayerror("New user could not be created. Some fields are missing!$field");
					$incomplete=true;
					break;
				}
				${$field}=escape($_POST[$field]);
			}
			if(!$incomplete)
			{
				$user_id=$_GET['userid'];
				$chkquery="SELECT COUNT(user_id) FROM `".MYSQL_DATABASE_PREFIX."users` WHERE `user_id`='$user_id' OR `user_name`='$user_name' OR `user_email`='$user_email'";
			
				$result=mysql_query($chkquery);
				$row=mysql_fetch_row($result);
			
				if($row[0]>0) displayerror("Another user with the same name or email already exists!");
				else if($user_password!=$_POST['user_password2']) displayerror("Passwords mismatch!");
				else 
				{
					if(isset($_POST['user_activated'])) $user_activated=1;
					$query = "INSERT INTO `" . MYSQL_DATABASE_PREFIX . "users` (`user_id` ,`user_name` ,`user_email` ,`user_fullname` ,`user_password` ,`user_regdate` ,`user_lastlogin` ,`user_activated`,`user_loginmethod`)VALUES ('$user_id' ,'$user_name' ,'$user_email' ,'$user_fullname' , MD5('$user_password') ,CURRENT_TIMESTAMP , '', '$user_activated','$user_loginmethod')";
					$result = mysql_query($query) or die(mysql_error());
					global $sourceFolder,$moduleFolder;
		require_once("$sourceFolder/$moduleFolder/form/registrationformsubmit.php");
		require_once("$sourceFolder/$moduleFolder/form/registrationformgenerate.php");
					if (mysql_affected_rows() && submitRegistrationForm(0, $user_id, true, true)) displayinfo("User $user_fullname Successfully Created!");
					else displayerror("Failed to create user");
				}
			}
		}
		
		$nextUserId=getNextUserId();
		$userForm="<form name='user_create_form' action='./+admin&subaction=useradmin&subsubaction=create&userid=$nextUserId' method='POST'><h3>Create New User</h3>";
		$xcolumnNames=array_values(getColumnList(0, false, false, false, false, false));
		$usertablefields2=array_merge($usertablefields,$xcolumnNames);
		$calpath = "$urlRequestRoot/$cmsFolder/$moduleFolder";
		$userForm .= '<link rel="stylesheet" type="text/css" media="all" href="'.$calpath.'/form/calendar/calendar.css" title="Aqua" />' .
						 '<script type="text/javascript" src="'.$calpath.'/form/calendar/calendar.js"></script>';
		for($i=0;$i<count($usertablefields2);$i++)
			if(isset($_POST[$usertablefields2[$i].'_sel']))
				$userForm.="<input type='hidden' name='{$usertablefields2[$i]}_sel' value='checked'/>";
		$userForm.="<input type='hidden' name='not_first_time' />";
		$infoarray=array();
		foreach ($usertablefields as $field)
			$infoarray[$field]="";
		$infoarray['user_id']=$nextUserId;
		
		$userForm.=userProfileForm($userfieldprettynamesarray,$infoarray,false,true);
		
		$userForm.="<input type='submit' onclick name='create_user_submit' value='Create' /><input type='reset' value='Clear' /></form>";
		return $userForm;
		
		
		
		

	}
	
}
function getAllUsersInfo(&$userId,&$userName,&$userEmail,&$userFullName,&$userPassword,&$userLastLogin,&$userRegDate,&$userActivated,&$userLoginMethod)
{
	$query="SELECT * FROM `".MYSQL_DATABASE_PREFIX."users` ORDER BY `user_id` ASC";
	$result=mysql_query($query);
	$userId=array();
	$userEmail=array();
	$userName=array();
	$userFullName=array();
	$userPassword=array();
	$userLastLogin=array();
	$userRegDate=array();
	$userActivated=array();
	$userLoginMethod=array();
	$i=0;
	while($row=mysql_fetch_assoc($result))
	{
		$userId[$i]=$row['user_id'];
		$userName[$i]=$row['user_name'];
		$userEmail[$i]=$row['user_email'];
	
		$userFullName[$i]=$row['user_fullname'];
		$userPassword[$i]=$row['user_password'];
		$userLastLogin[$i]=$row['user_lastlogin'];
		$userRegDate[$i]=$row['user_regdate'];
		$userActivated[$i]=$row['user_activated'];
		$userLoginMethod[$i]=$row['user_loginmethod'];
		$i++;
	}
	
}
function registeredUsersList($type,$act,$allfields,$userInfo=NULL)
{
	global $urlRequestRoot, $cmsFolder, $moduleFolder, $templateFolder,$sourceFolder;
	require_once("$sourceFolder/$moduleFolder/form/viewregistrants.php");
	$extraColumns=getColumnList(0, false, false, false, false, false);
	$xcolumnIds=array(); $xcolumnNames=array(); $xcolumnFieldVars=array();
	foreach($extraColumns as $columnid=>$colname)
	{
	 $xcolumnIds[]=$columnid;
	 $xcolumnNames[]=$colname;
	 $xcolumnFieldVars[]='user'.ucfirst($colname);
	 ${'user'.ucfirst($colname)}=array();
	}
	
	if($userInfo==NULL)
	{
	 getAllUsersInfo($userId,$userName,$userEmail,$userFullName,$userPassword,$userLastLogin,$userRegDate,$userActivated,$userLoginMethod); 
	}
	else 
	{
		$userId=$userInfo['user_id'];
		$userName=$userInfo['user_name'];
		$userEmail=$userInfo['user_email'];
	
		$userFullName=$userInfo['user_fullname'];
		$userPassword=$userInfo['user_password'];
		$userLastLogin=$userInfo['user_lastlogin'];
		$userRegDate=$userInfo['user_regdate'];
		$userActivated=$userInfo['user_activated'];
		$userLoginMethod=$userInfo['user_loginmethod'];
		
	}
	 foreach($userId as $userid)
		 {
		 	$xinfo=generateFormDataRow(0,$userid,$xcolumnIds);
			foreach($xinfo as $j=>$info)
			{
				${$xcolumnFieldVars[$j]}[]=$info;
			}
		}

	
	
	$userfieldprettynames=array_merge( array("User ID","Username","Email","Full Name","Password","Registration","Last Login","Activated","Login Method"), array_map('ucfirst',$xcolumnNames));
	
	function replace10byYesNo(&$value,$key)
	{ if($value=='1') $value="Yes"; else if ($value=='0') $value="No"; }
	array_walk($userActivated,'replace10byYesNo');
	
	
	$userlisttdids=array_merge(array("user_id","user_name","user_email","user_fullname","user_password","user_regdate","user_lastlogin","user_activated","user_loginmethod"), $xcolumnIds);
	$userfieldvars=array_merge(array("userId","userName","userEmail","userFullName","userPassword","userRegDate","userLastLogin","userActivated","userLoginMethod"), $xcolumnFieldVars);
	
	$userlist="";
	$columns=count($userfieldvars);
	if($act=="edit")
	{
		$userlist.="<form name='user_edit_form' method='POST' action='./+admin&subaction=useradmin&userid=' >\n";
		$userlist.="<input type='hidden' name='editusertype' value='$type' />";
		$columns+=3;
	}
	$userlist .= smarttable::render(array('userstable'),null);
	global $STARTSCRIPTS;
	$STARTSCRIPTS.="initSmartTable();";
	
	$userlist.=<<<USERLIST
	
	<script language="javascript">
	function checkDelete(butt,userDel,userId)
	{
		if(confirm('Are you sure you want to delete '+userDel+' (User ID='+userId+')?'))
		{
			butt.form.action+=userId;
		}
		else return false;
	}
	function checkDeleteAll(butt) {
		if(!confirm('Are you sure you want to delete all selected users?')) {
			return false;
		}
		butt.form.action+='-1';
		return true;
	}
	</script>
	<a name='userlist'></a>
USERLIST;
	global $ICONS_SRC;
	$userlisttable = "";
	if($act=="edit")
		$userlisttable =<<<TABLE
	<input title='Activate Selected Users' type='image' src='{$ICONS_SRC['Activate']['small']}' onclick=\"this.form.action+='-1'\" name='user_selected_activate' value='Activate'>\n
	<input  title='Deactivate Selected Users' type='image' src='{$ICONS_SRC['Deactivate']['small']}' onclick=\"this.form.action+='-1'\" name='user_selected_deactivate' value='Deactivate'>\n
	<input  title='Delete Selected Users' type='image' src='{$ICONS_SRC['Delete']['small']}' onclick=\"return checkDeleteAll(this)\" name='user_selected_delete' value='Delete'>\n
TABLE;
	$userlisttable.=<<<TABLE
	<table class="userlisttable display" border="1" id='userstable'>
	<thead>
	<tr><th colspan="$columns">Users Registered on the Website</th></tr>
	<tr>
TABLE;

		
	
	$defCols=getTableFieldsName('users');
	$usertablefields=array_merge($defCols,$xcolumnIds);
	$displayfieldsindex=array();
	$c=0;
	for($i=0;$i<count($usertablefields);$i++)
	{
		if(isset($_POST[$usertablefields[$i].'_sel']) || $allfields)
		{
			$userlisttable.="<th>".$userfieldprettynames[$i];
			if($act=="edit") $userlist.="<input type='hidden' name='{$usertablefields[$i]}_sel' value='checked'/>";
			$userlisttable.="</th>";
			$displayfieldsindex[$c++]=$i;
		}
	}
	
	
	$userlist.="<input type='hidden' name='not_first_time' />";
		
	
	if($act=="edit")
	{
		$userlisttable.="<th>Actions</th>";
	}
	$userlisttable.="</tr></thead><tbody>";
	$rowclass="oddrow";
	$flag=false;
	$usercount=0;
	for($i=0; $i<count($userId); $i++)
	{
		if($type=="activated" && $userActivated[$i]=="No")
			continue;
		if($type=="nonactivated" && $userActivated[$i]=="Yes")
			continue;
		$flag=true;
		$userlisttable.="<tr class='$rowclass'>";
		
		for($j=0; $j<count($displayfieldsindex); $j++)
		{
			$userlisttable.="<td class='{$userlisttdids[$j]}'>".${$userfieldvars[$displayfieldsindex[$j]]}[$i]."</td>";	
		}
		
		
		if($act=="edit")
		{
			$userlisttable.="<td id='user_editactions'>";
			$userlisttable.="<input type='checkbox' name='selected_{$userId[$i]}' />";
			if($userActivated[$i]=="No")
				$userlisttable.="<input title='Activate User' type='image' src='{$ICONS_SRC['Activate']['small']}' onclick=\"this.form.action+='{$userId[$i]}'\" name='user_activate' value='Activate'>\n";
			else $userlisttable.="<input  title='Deactivate User' type='image' src='{$ICONS_SRC['Deactivate']['small']}' onclick=\"this.form.action+='{$userId[$i]}'\" name='user_deactivate' value='Deactivate'>\n";
			$userlisttable.="<input  title='Edit User' type='image' src='{$ICONS_SRC['Edit']['small']}' onclick=\"this.form.action+='{$userId[$i]}'\" name='user_info' value='Edit'>\n";
			$userlisttable.="<input  title='Delete User' type='image' src='{$ICONS_SRC['Delete']['small']}' onclick=\"return checkDelete(this,'".$userName[$i]."','".$userId[$i]."')\" name='user_delete' value='Delete'>\n";
			$userlisttable.="</td>";
			
		}
		$userlisttable.="</tr>";
		$rowclass=$rowclass=="evenrow"?"oddrow":"evenrow";
		$usercount++;
	}
	$userlisttable.="</tbody></table>";
	
	///If users wants to download as excel sheet
	if(isset($_POST['save_reg_users_excel'])|| isset($_POST['save_activated_users_excel']) || isset($_POST['save_nonactivated_users_excel']))
	{
		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Cache-Control: private",false); 
		header("Content-Type: application/vnd.ms-excel");
		header("Content-Disposition: attachment; filename=\"users.xls\";" );
		header("Content-Transfer-Encoding: binary");
		echo $userlisttable;
		exit(1);
	}
	
	if($act=="edit") $userlist.=$userlisttable."</form>";
	else $userlist.=$userlisttable;
	
	
	
	return ($flag)?$userlist:"No Users Found!";
}
function userProfileForm($userfieldprettynames,$profileInfoRows,$editID=false,$showProfileInfo=true)
{
	$i=0;
	$userinfo="<table>";
	foreach ($profileInfoRows as $field => $value)
	{
		if($field=='user_password')
		{
			$userinfo.="<tr><td>{$userfieldprettynames[$i]}</td><td><input type='password' name='$field'/></td></tr>";
			$field.='2';
			$userinfo.="<tr><td>{$userfieldprettynames[$i++]} (Verify)</td><td><input type='password' name='$field'/></td></tr>";
		}
		else if($field=='user_activated')
		{
			$value=($value==1)?"checked":"";
			$userinfo.="<tr><td>{$userfieldprettynames[$i++]}</td><td><input type='checkbox' name='$field' $value /></td></tr>";
		}
		else if($field=='user_loginmethod')
		{
			$ldapsel=$imapsel=$adssel=$dbsel="";
			${$profileInfoRows[$field].'sel'}=" selected = 'selected' ";
			$userinfo.="<tr><td>{$userfieldprettynames[$i++]}</td><td><select id='$field' name='$field'>
			<option></option>
			<option $ldapsel>ldap</option>
			<option $imapsel>imap</option>
			<option $adssel>ads</option>
			<option $dbsel>db</option>
			</select>
			</td></tr>";
		}
		else if((!$editID && $field=='user_id') || (!$editID && $field=='user_regdate'))
			$userinfo.="<tr><td>{$userfieldprettynames[$i++]}</td><td>$value</td></tr>";
		
		else $userinfo.="<tr><td>{$userfieldprettynames[$i++]}</td><td><input type='text' name='$field' value='$value'/></td></tr>";
		
	}
	
	if($showProfileInfo)
	{
		global $sourceFolder,$moduleFolder;
		require_once("$sourceFolder/$moduleFolder/form/registrationformsubmit.php");
		require_once("$sourceFolder/$moduleFolder/form/registrationformgenerate.php");
		$containsFileUploadFields = false;
		$userId=$profileInfoRows['user_id'];
		$dynamicFields = getFormElementsHtmlAsArray(0, $userId, $jsValidationFunctions, $containsFileUploadFields);
		$dynamicFields = join($dynamicFields, "</tr>\n<tr>");
		if($dynamicFields != '') {
			$dynamicFields = "<tr>$dynamicFields</tr>";
		}
		$userinfo.=$dynamicFields;
	}
	
	return $userinfo."</table>";
}
?>
