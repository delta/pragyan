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
 * @author Boopathi Rajaa, balanivash
 * @copyright (c) 2011 Pragyan Team
 * @license http://www.gnu.org/licenses/ GNU Public License
 * For more details, see README
 */


function generatePublicProfile($userProfileId,$accessUserId) {
	$userId=$userProfileId;
	global $urlRequestRoot, $moduleFolder, $cmsFolder,$sourceFolder, $templateFolder;
	require_once("$sourceFolder/$moduleFolder/form/registrationformsubmit.php");
	require_once("$sourceFolder/$moduleFolder/form/viewregistrants.php");
	require_once("$sourceFolder/upload.lib.php");
	require_once ("$sourceFolder/profile.lib.php");
	$profileQuery = 'SELECT `user_name`, `user_fullname`, `user_email` FROM `' . MYSQL_DATABASE_PREFIX . 'users` WHERE `user_id` = \'' . $userId."'";
	$profileResult = mysqli_query($GLOBALS["___mysqli_ston"], $profileQuery);
	if(!$profileResult) {
		displayerror('An error occurred while trying to process your request.<br />' . ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)) . '<br />' . $profileQuery);
		return '';
	}
	if(mysqli_num_rows($profileResult)==0){
		displayerror("The Requested user is not found." );
		return "Click <a href='".$urlRequestRoot."'>here </a> to return to the home page";
	}
	$profileRow = mysqli_fetch_row($profileResult);
	$userName = $profileRow[0];
	$userFullname = $profileRow[1];
	$userEmail = $profileRow[2];
	$fakeModuleComponentId=$userId;	
	$profileimgname = getUploadedFiles($fakeModuleComponentId,'profile');
	if($profileimgname==NULL) 
	{ 
	 	$profileimgname = "$urlRequestRoot/$cmsFolder/$templateFolder/common/images/no-img.jpg";
	}
	else
	{
		$profileimgname = "./+profile&fileget={$profileimgname[0]['upload_filename']}&mcid={$userId}";
	}

	
	$profileimg= "<img id=profileimg src='$profileimgname' alt='Profile Image' title='Profile Image' height=120 width=100><br/>";
	
	$dynamicFields = getFormElementsHtmlAsArrayForView(0, $userId);
	$dynamicFields = join($dynamicFields, "</tr>\n<tr>");
	if($dynamicFields != '') {
		$dynamicFields = "<tr>$dynamicFields</tr>";
	}

	global $ICONS;
	$profileForm =<<<PREF


<div class="cms-profile">
		<fieldset>
			<legend>{$ICONS['User Profile']['small']}  User Profile</legend>

			<table style="width:75%;">
				<tr>
				<td colspan=2 style="text-align:center">$profileimg</td>
				</tr>
				<tr>
					<td><label for="user_name" class="labelrequired">Name</label></td>
					<td>$userName</td>
				</tr>
				<tr>
					<td><label for="user_fullname" class="labelrequired">Full Name</label></td>
					<td>$userFullname</td>
				</tr>

					$dynamicFields
PREF;
	if($userId==$accessUserId){
		$profileForm .= "<tr>
					<td colspan=2 style='text-align:center'><a href=./+profile>{$ICONS['Edit']['small']} Edit Profile</a></td>
				</tr>";
	}
		$profileForm .= <<<PREF
			</table>
		</fieldset>
	</form>
</div>
PREF;

	return  $profileForm."<br />".getProfileGroupsAndFormsList($userId); 
}
