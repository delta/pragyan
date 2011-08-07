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

//TODO: needs complete rewrite........ of the form part. And also integration with forms for a variable no of fields.
//print_r($_POST);

function isProfileFormCaptchaEnabled() {
	$captchaQuery = 'SELECT `form_usecaptcha` FROM `form_desc` WHERE `page_modulecomponentid` = 0';
	$captchaResult = mysql_query($captchaQuery);
	$captchaRow = mysql_fetch_row($captchaResult);
	if($captchaRow && isset($captchaRow[0])) {
		return $captchaRow[0] == 1;
	}
	return false;
}

function profile($userId, $forEditRegistrant = false) {
	global $sourceFolder, $moduleFolder;


	if(isset($_POST['profileimgaction']) && $_POST['profileimgaction']=='uploadnew')
	{
		require_once("$sourceFolder/upload.lib.php");
		//Upload profile image
		$allowableTypes = array (
				'jpeg',
				'jpg',
				'png',
				'gif'
			);
		$fakeModuleComponentId=$userId;
		$uploadSuccess = submitFileUploadForm($fakeModuleComponentId, "profile", $userId, 512*1024, $allowableTypes, 'profileimage');
	
		if(!is_array($uploadSuccess) && $uploadSuccess===false) displayerror("Profile image could not be uploaded. Maximum size should be 512 KB.");
		else if(is_array($uploadSuccess))
		{
			//Deleting old profile image
			$profileimgnames = getUploadedFiles($fakeModuleComponentId,'profile');
		
			foreach($profileimgnames as $img)
			{
			 if($img['upload_filename']!=$uploadSuccess[0])
			 	deleteFile($fakeModuleComponentId,'profile',$img['upload_filename']);
			}
		}
	}
	else if(isset($_POST['profileimgaction']) && $_POST['profileimgaction']=='noimage')
	{
		require_once("$sourceFolder/upload.lib.php");
		$fakeModuleComponentId=$userId;
		$profileimgnames = getUploadedFiles($fakeModuleComponentId,'profile');
		
		foreach($profileimgnames as $img)
		 	deleteFile($fakeModuleComponentId,'profile',$img['upload_filename']);
	}
		
		/// Retrieve existing information
	$profileQuery = 'SELECT `user_name`, `user_fullname`, `user_password` FROM `' . MYSQL_DATABASE_PREFIX . 'users` WHERE `user_id` = \'' . $userId."'";
	$profileResult = mysql_query($profileQuery);
	if(!$profileResult) {
		displayerror('An error occurred while trying to process your request.<br />' . mysql_error() . '<br />' . $profileQuery);
		return '';
	}
	$profileRow = mysql_fetch_row($profileResult);
	$newUserName = $userName = $profileRow[0];
	$newUserFullname = $userFullname = $profileRow[1];
	$userPassword = $profileRow[2];

	require_once("$sourceFolder/$moduleFolder/form/registrationformsubmit.php");
	require_once("$sourceFolder/$moduleFolder/form/registrationformgenerate.php");
	/// Check if the user is trying to see the profile form, or has already submitted it
	if(isset($_POST['btnSubmitProfile'])) {
		if($forEditRegistrant || !isProfileFormCaptchaEnabled() || submitCaptcha()) {
			if(!$forEditRegistrant) {
				$passwordValidated = false;
				if(isset($_POST['user_password']) && $_POST['user_password'] != '' && md5($_POST['user_password']) == $userPassword) {
					$passwordValidated = true;
				}
			}

			$updates = array();

			if (isset($_POST['user_name']) && $_POST['user_name'] != '' && $_POST['user_name'] != $userName) {
				$updates[] = "`user_name` = '".escape($_POST['user_name'])."'";
				$newUserName = escape($_POST['user_name']);
			}
			if (isset($_POST['user_fullname']) && $_POST['user_fullname'] != '' && $_POST['user_fullname'] != $userFullname) {
				$updates[] = "`user_fullname` = '".escape($_POST['user_fullname'])."'";
				$newUserFullname = escape($_POST['user_fullname']);
			}
			$errors = true;
			if (!$forEditRegistrant && $_POST['user_newpassword'] != '') {
				if(!$passwordValidated) {
					displayerror('Error! The current password you entered was incorrect.');
				}
				elseif ($_POST['user_newpassword'] != $_POST['user_newrepassword']) {
					displayerror('Error! The New Password you entered does not match the password you typed in the Confirmation Box.');
				}
				elseif ($_POST['user_newpassword'] == $_POST['user_password']) {
					displayerror('Error! The old and new passwords are the same.');
				}
				else {
					$updates[] = "`user_password` = MD5('".escape($_POST['user_newpassword'])."')";
					$errors = false;
				}
			}
			else {
				$errors = false;
			}

			if(count($updates) > 0) {
				$profileQuery = 'UPDATE `' . MYSQL_DATABASE_PREFIX . 'users` SET ' . join($updates, ', ') . " WHERE `user_id` = '$userId'";
				$profileResult = mysql_query($profileQuery);
				if(!$profileResult) {
					displayerror('An error was encountered while attempting to process your request.');
					$errors = true;
				}
				$userName = $newUserName;
				$userFullname = $newUserFullname;

				if(!$forEditRegistrant)
					setAuth($userId);
			}

			$errors = !submitRegistrationForm(0, $userId, true, true) || $errors;
			if(!$errors) {
				displayinfo('All fields updated successfully!<br />' .
						'<input type="button" onclick="history.go(-2)" value="Go back" />');
			}
		}
	}
	return getProfileForm($userId, $userName, $userFullname, $forEditRegistrant);
}


function getProfileForm($userId, $userName, $userFullname, $forEditRegistrant = false) {
	global $urlRequestRoot, $moduleFolder, $cmsFolder,$sourceFolder, $templateFolder;
	require_once("$sourceFolder/$moduleFolder/form/registrationformsubmit.php");
	require_once("$sourceFolder/$moduleFolder/form/registrationformgenerate.php");
	require_once("$sourceFolder/upload.lib.php");
	
	$fakeModuleComponentId=$userId;
	
	$profileimgname = getUploadedFiles($fakeModuleComponentId,'profile');
	if($profileimgname==NULL) 
	{ 
	 	$profileimgname = "$urlRequestRoot/$cmsFolder/$templateFolder/common/images/no-img.jpg";
	}
	else
	{
		$profileimgname = "./+profile&fileget={$profileimgname[0]['upload_filename']}";
	}

	
	$profileimg= "<img id=profileimg src='$profileimgname' alt='Profile Image' title='Profile Image' height=120 width=100><br/>";
	
	$profileimgupload = getFileUploadField('profileimage','profile',512*1024);
	
	$jsValidationFunctions = array();
	$containsFileUploadFields = false;
	$dynamicFields = getFormElementsHtmlAsArray(0, $userId, $jsValidationFunctions, $containsFileUploadFields);
	$dynamicFields = join($dynamicFields, "</tr>\n<tr>");
	if($dynamicFields != '') {
		$dynamicFields = "<tr>$dynamicFields</tr>";
	}
	$jsValidationFunctions = join($jsValidationFunctions, ' && ');

	$captchaValidation = '';
	if(!$forEditRegistrant) {
		$captchaQuery = 'SELECT `form_usecaptcha` FROM `form_desc` WHERE `page_modulecomponentid` = 0';
		$captchaResult = mysql_query($captchaQuery);
		$captchaRow = mysql_fetch_row($captchaResult);
		if(isset($captchaRow[0]) && $captchaRow[0] == 1) {
			$captchaValidation = getCaptchaHtml();
		} 
	}

	$fValidatorPath = "$urlRequestRoot/$cmsFolder/$templateFolder/common/scripts/formValidator.js";
	$ValidatorPath = "$urlRequestRoot/$cmsFolder/$moduleFolder/form/validation.js";
	$calpath = "$urlRequestRoot/$cmsFolder/$moduleFolder";
	$formAction = './+profile';
	if($forEditRegistrant) {
		$formAction = './+admin&subaction=editsiteregistrants&subsubaction=editregistrant';
	}
global $ICONS;
global $STARTSCRIPTS;
$STARTSCRIPTS.="document.getElementsByName('profileimage[]')[0].disabled=true;";
	$profileForm =<<<PREF

<script language="javscript" type="text/javascript" src="$ValidatorPath"></script>
<script language="javascript" type="text/javascript" src="$fValidatorPath"></script>
<link rel="stylesheet" type="text/css" media="all" href="$calpath/form/calendar/calendar.css" title="Aqua" />
<script language="javascript" type="text/javascript" src="$calpath/form/calendar/calendar.js"></script>
<script language="javascript" type="text/javascript">
	window.addEvent("domready", function() {
		var exValidatorA = new fValidator("registrationform");
	});

	function checkPassword(inputhandler) {
		inputhandler2=document.getElementById("user_newpassword");
		if(inputhandler.value!=inputhandler2.value)	{
			alert("The password you typed in the New Password field does not match the one in the Confirmation Box.");
			inputhandler.value="";
			inputhandler2.value="";
			inputhandler2.focus();
			return false;
		}
		return true;
	}

	function checkProfileForm(inputhandler) {
		if(inputhandler.user_newpassword.value.length!=0) {
			if(inputhandler.user_password.value.length==0) {
				alert("Please enter your current password in order to change to a new one.");
				return false;
			}
		}

		if(checkPassword(inputhandler.user_newrepassword)==false)
			return false;

		return $jsValidationFunctions;
	}
	
	function toggle_img_upform()
	{
		var obj1=document.getElementsByName('profileimage[]')[0];
		var obj2=document.getElementById('upnewradio');
		obj1.disabled=(obj2.checked==true?false:true); 
	}
	

</script>
<div class="cms-registrationform">
	<form id="cms-registrationform" class="fValidator-form" method="POST" name="user_profile_usrFrm" onsubmit="return checkProfileForm(this)" action="$formAction" enctype="multipart/form-data">
		<fieldset style="width:80%">
			<legend>{$ICONS['User Profile']['small']}Profile Preferences</legend>

			<table>
				<tr>
				<td colspan=2 style="text-align:center">$profileimg</td>
				</tr>
				<tr>
					<td><label for="user_name" class="labelrequired">Name</label></td>
					<td><input name="user_name" id="user_name" class="fValidate['required']" type="text" value="$userName"></td>
				</tr>
				<tr>
					<td><label for="user_fullname" class="labelrequired">Full Name</label></td>
					<td><input name="user_fullname" id="user_fullname" class="fValidate['required']" type="text" value="$userFullname"></td>
				</tr>
				<tr>
					<td>Profile image</td>
					<td>
					<input type="radio" name="profileimgaction" value="usecurrent" checked onclick="toggle_img_upform()"> Use existing image<br/>
					<input id='upnewradio' type="radio" name="profileimgaction" value="uploadnew" onclick="toggle_img_upform()"> Upload new image<br/>
					<input type="radio" name="profileimgaction" value="noimage" onclick="toggle_img_upform()"> Remove your image
					</td>
				<tr>
					<td><label for="profileimage">Upload new profile image (maximum size is 512 KB)</td>
					<td>$profileimgupload</td>
				</tr>
PREF;

		if(!$forEditRegistrant) {
			$profileForm .= <<<PREF
				<tr>
					<td><label for="user_password" class="labelrequired">Current Password (Only for changing password)</label></td>
					<td><input name="user_password" id="user_password" class="" type="password"></td>
				</tr>
				<tr>
					<td><label for="user_newpassword" class="labelrequired">New Password</label></td>
					<td>  <input name="user_newpassword" id="user_newpassword" class="fValidate['']" type="password"></td>
				</tr>
				<tr> <td><label for="user_newrepassword" class="labelrequired">Re-enter New Password</label></td>
					<td> <input name="user_newrepassword" id="user_newrepassword" class="fValidate['=user_newpassword']" type="password"></td>
				</tr>
PREF;

		}

		$profileForm .= <<<PREF
					$dynamicFields
					$captchaValidation
				<tr>
					<td colspan="2">&nbsp;</td>
				</tr>
				<tr>
					<td><input type="submit" name="btnSubmitProfile" id="submitbutton" value="Save Profile"></td>
					<td></td>
				</tr>
			</table>
PREF;

		if($forEditRegistrant) {
			$profileForm .= '<input type="hidden" name="useremail" value="'.getUserEmail($userId).'" />';
		}

		$profileForm .= <<<PREF
		</fieldset>
	</form>
</div>
PREF;

	// TODO: implement getProfileNewsletterList completely. return $profileForm . getProfileGroupsAndFormsList($userId) . getProfileNewsletterList($userId);
	return  $profileForm . getProfileForms($userId).getProfileGroupsAndFormsList($userId).getFormDeadlines($userId); 
}


function getProfileFormEditForm() {
	global $sourceFolder, $moduleFolder;
	$moduleComponentId = 0;

	require_once("$sourceFolder/$moduleFolder/form/editformelement.php");
	require_once("$sourceFolder/$moduleFolder/form/editform.php");

	/// $_GET['subsubaction'] would be set to editprofileform whenever this function is called
	/// Check $_GET['subaction'] to determine whether something must be done to the profile form
	/// if there is no subaction, and a $_POST['submittedform_desc'] is set, submit the profile form
	if (isset($_GET['subaction'])) {
		$subAction = escape($_GET['subaction']);
		require_once("$sourceFolder/$moduleFolder/form/editformelement.php");

		if (
			$_GET['subaction'] == 'editformelement' &&
			isset($_POST['elementid']) && ctype_digit($_POST['elementid']) &&
			isset($_POST['txtElementDesc']) && isset($_POST['selElementType']) &&
			isset($_POST['txtToolTip']) && isset($_POST['txtElementName'])
		) {
			submitEditFormElementDescData($moduleComponentId, escape($_POST['elementid']));
		}
		elseif ( isset($_GET['elementid']) && ctype_digit($_GET['elementid']) ) {
			if ($_GET['subaction'] == 'editformelement') {
				return generateEditFormElementDescBody($moduleComponentId, escape($_GET['elementid']), 'admin&subsubaction=editprofileform');
			}
			elseif ($_GET['subaction'] == 'deleteformelement') {
				deleteFormElement($moduleComponentId, escape($_GET['elementid']));
			}
			elseif ($_GET['subaction'] == 'moveUp' || $_GET['subaction'] == 'moveDown') {
				moveFormElement($moduleComponentId, escape($_GET['subaction']), escape($_GET['elementid']));
			}
		}
	}
	if (isset($_POST['addformelement_descsubmit'])) {
		addDefaultFormElement($moduleComponentId);
	}

	return generateFormElementDescBody($moduleComponentId, 'admin&subsubaction=editprofileform');
}

// Is the below function required ??? User account can be deleted via user mgmt, then why this ?? Should be confirmed & removed.
function deleteUserAccount($userId) {
	/// $deleteQuery = 'DELETE FROM `' . MYSQL_DATABASE_PREFIX . 'users` WHERE `user_id` = ' . $userId;
	displayinfo('To be implemented');
}

function getProfileViewRegistrantsForm() {
	if(isset($_GET['subsubaction'])) {
		if($_GET['subsubaction'] == 'editregistrant' && (isset($_GET['useremail']) || isset($_POST['useremail']))) {
			$email = isset($_GET['useremail']) ? escape($_GET['useremail']) : escape($_POST['useremail']);
			return profile(getUserIdFromEmail($email), true);
		}
		elseif($_GET['subsubaction'] == 'deleteregistrant' && isset($_GET['useremail'])) {
			deleteUserAccount(getUserIdFromEmail(escape($_GET['useremail'])));
		}
	}

	return getProfileRegistrantsList($_GET['subaction'] == 'editsiteregistrants');
}


function getProfileRegistrantsList($showEditButtons = false) {
	global $urlRequestRoot, $cmsFolder, $moduleFolder, $templateFolder,$sourceFolder;
	require_once("$sourceFolder/$moduleFolder/form/viewregistrants.php");

	$sortField = 'useremail';
	$sortOrder = 'asc';
	if(isset($_GET['sortfield'])) {
		$sortField = escape($_GET['sortfield']);
	}
	if(isset($_GET['sortorder']) && ($_GET['sortorder'] == 'asc' || $_GET['sortorder'] == 'desc')) {
		$sortOrder = escape($_GET['sortorder']);
	}

	$action = './+admin&subaction=' . escape($_GET['subaction']);

	$columnList['useremail'] = 'User Email';
	$columnList['username'] = 'Username';
	$columnList['userfullname'] = 'User Full Name';
	$columnList['registrationdate'] = 'Registration Date';
	$columnList['lastupdated'] = 'Last Updated';

	$columnList = array_merge($columnList, getColumnList(0, false, false, false, false));

	$normalImage = "<img alt=\"Sort by this field\" height=\"12\" width=\"12\" style=\"padding:0px\" src=\"$urlRequestRoot/$cmsFolder/$templateFolder/common/icons/16x16/actions/view-refresh.png\" />";
	$orderedImage = "<img alt=\"Sort by this field\" height=\"12\" width=\"12\" style=\"padding:0px\" src=\"$urlRequestRoot/$cmsFolder/$templateFolder/common/icons/16x16/actions/go-" . ($sortOrder == 'asc' ? 'up' : 'down') . ".png\" />";

	$tableCaptions = "<tr>\n<th nowrap=\"nowrap\">S. No.</th>\n";
	if($showEditButtons) {
		$tableCaptions .= '<th nowrap="nowrap">Edit</th><th nowrap="nowrap">Delete</th>';
	}
	foreach($columnList as $columnName => $columnTitle) {
		$tableCaptions .= "<th nowrap=\"nowrap\">$columnTitle<a href=\"$action&sortfield=$columnName";
		if($sortField == $columnName) {
			$tableCaptions .= '&sortorder=' . ($sortOrder == 'asc' ? 'desc' : 'asc') . '">'.$orderedImage.'</a>';
		}
		else {
			$tableCaptions .= '">' . $normalImage. '</a>' ;
		}
		$tableCaptions .= "</th>\n";
		$columnNames[] = $columnName;
	}
	$tableCaptions .= "</tr>\n";

	$userIds = getDistinctRegistrants(0, $sortField, $sortOrder);
	$userCount = count($userIds);

	$editImage = "<img style=\"padding:0px\" src=\"$urlRequestRoot/$cmsFolder/$templateFolder/common/icons/16x16/apps/accessories-text-editor.png\" alt=\"Edit\" />";
	$deleteImage = "<img style=\"padding:0px\" src=\"$urlRequestRoot/$cmsFolder/$templateFolder/common/icons/16x16/actions/edit-delete.png\" alt=\"Delete\" />";

	$tableBody = '';
	for($i = 0; $i < $userCount; $i++) {
		$tableBody .= '<tr><td>'.($i + 1).'</td>';
		if($showEditButtons) {
			$tableBody .= '<td align="center"><a href="./+admin&subaction=editsiteregistrants&subsubaction=editregistrant&useremail='.getUserEmail($userIds[$i]).'" />' . $editImage . '</a></td>';
			$tableBody .= '<td align="center"><a href="./+admin&subaction=editsiteregistrants&subsubaction=deleteregistrant&useremail='.getUserEmail($userIds[$i]).'" />' . $deleteImage . '</a></td>';
		}
		$tableBody .= '<td>' . join(generateFormDataRow(0, $userIds[$i], $columnNames), '</td><td>') . "</td></tr>\n";
	}

	return '<br /><br /><br /><table border="1">' . $tableCaptions . $tableBody . '</table>';
}

function getProfileForms($userId) {
	global $ICONS,$urlRequestRoot;
	$regforms ="<fieldset style=\"padding: 8px\"><legend>{$ICONS['User Groups']['small']}Forms I Have Registered To</legend>";
	$regforms .= '<ol>';
	$query = "SELECT DISTINCT `page_modulecomponentid` FROM `form_elementdata` WHERE `user_id` = '$userId'";
	$result2 = mysql_query($query);
	while($result = mysql_fetch_row($result2)) {
		if($result[0]!=0){
		$formPath = getPagePath(getPageIdFromModuleComponentId('form', $result[0]));
		$formPathLink = $urlRequestRoot . $formPath;
		$query1 = "SELECT `form_heading` FROM `form_desc` WHERE `page_modulecomponentid` ='". $result[0]."'";		
		$result1 = mysql_query($query1);
		$result1 = mysql_fetch_row($result1);
		$regforms .= '<li> <a href="'.$formPathLink.'">'.$result1[0].'</a></li>';
		}
	}
	$regforms .= '</ol></fieldset> ';
	return $regforms;
}
function getFormDeadlines($userId) {
	global $ICONS,$urlRequestRoot;
	$regforms ="<fieldset style=\"padding: 8px\"><legend>{$ICONS['User Groups']['small']}Forms Nearing Deadline</legend>";
	$regforms .= '<ol>';
	$query = "SELECT * FROM `".MYSQL_DATABASE_PREFIX."global`";
	$result = mysql_query($query);
	while($res = mysql_fetch_row($result)) {
		if($res[0] == 'deadline_notify')
		{
			$deadline = $res[1] * 24 * 3600;
		}
			
	}	
	$query = "SELECT DISTINCT `page_modulecomponentid` FROM `form_desc` WHERE HOUR(TIMEDIFF(`form_expirydatetime`,NOW( )))*3600+MINUTE(TIMEDIFF(`form_expirydatetime`,NOW( )))*60+SECOND(TIMEDIFF(`form_expirydatetime`,NOW( )))*60 <= '".$deadline."'";
	$result2 = mysql_query($query);
	while($result = mysql_fetch_row($result2)) {
		if($result[0]!=0){
		$formPath = getPagePath(getPageIdFromModuleComponentId('form', $result[0]));
		$formPathLink = $urlRequestRoot . $formPath;
		$query1 = "SELECT `form_heading` FROM `form_desc` WHERE `page_modulecomponentid` =". $result[0];		
		$result1 = mysql_query($query1);
		$result1 = mysql_fetch_row($result1);
		$regforms .= '<li> <a href="'.$formPathLink.'">'.$result1[0].'</a></li>';
		}
	}
	$regforms .= '</ol></fieldset> ';
	return $regforms;
}
function getProfileGroupsAndFormsList($userId) {
	global $sourceFolder;
	require_once("$sourceFolder/group.lib.php");

	$groupRows = getGroupsFromUserId($userId);
	$groupRowsCount = count($groupRows);

	$associatedGroups = array();
	$unassociatedGroups = array();

	for($i = 0; $i < $groupRowsCount; $i++) {
		if($groupRows[$i]['form_id'] == 0) {
			$unassociatedGroups[] = '<tr><td>' . $groupRows[$i]['group_name'] . '</td><td>' . $groupRows[$i]['group_description'] . '</td></tr>';
		}
		else {
			$formPath = getPagePath(getPageIdFromModuleComponentId('form', $groupRows[$i]['form_id']));
			global $urlRequestRoot;
			$formPathLink = $urlRequestRoot . $formPath;
			$associatedGroups[] = '<tr><td><a href="' . $formPathLink . '">' . $formPath . '</a></td><td>' . $groupRows[$i]['group_name'] . '</td><td><a href="' . $formPathLink . '&subaction=unregister" onclick="return confirm(\'Are you sure you wish to unregister from this form?\')">Unregister</a></td></tr>';
		}
	}

	if(count($associatedGroups) == 0 && count($unassociatedGroups) == 0)
		return false;
	global $ICONS;
	$retVal = "<fieldset style=\"padding: 8px\"><legend>{$ICONS['User Groups']['small']}Groups I Belong To</legend>";
	if(count($associatedGroups) > 0) {
		$retVal .= '<strong>Groups associated with forms:</strong><br /><br /><table style="margin-left: 8px" border="1" cellpadding="4px" cellspacing="4px">' .
						'<tr><th>Form Path</th><th>Group Name</th><th>Unregister</th></tr>' .
						implode("\n", $associatedGroups) . '</table><br /><br />';
	}
	if(count($unassociatedGroups) > 0) {
		$retVal .= '<strong>Groups not associated with any form:</strong><br /><table style="margin-left: 8px" border="1" cellpadding="4px" cellspacing="4px">' . '<tr><th>Group Name</th><th>Group Description</th></tr>' . implode("\n", $unassociatedGroups) . '</table><br />';
	}
	$retVal .= '</fieldset>';
	return $retVal;
}

function getProfileNewsletterList($userId) {
	$retVal = '<fieldset style="padding: 8px"><legend>My Newsletters</legend>';
	global $urlRequestRoot, $cmsFolder, $sourceFolder, $moduleFolder, $templateFolder;
	include_once("$sourceFolder/$moduleFolder/newsletter.lib.php");
	$subscribableLists = newsletter::getSubscribableLists($userId);
	$subscribedLists = '';
	$unsubscribedLists = '';

	for ($i = 0; $i < count($subscribableLists); ++$i) {
		if ($subscribableLists[$i][2] === true)
			$subscribedLists .= '<span class="newsletterlistitem"><a href="' . $subscribableLists[$i][1] . '" />' .  $subscribableLists[$i][0] . '</a></span>';
		else
			$unsubscribedLists .= '<span class="newsletterlistitem"><a href="' . $subscribableLists[$i][1] . '" />' .  $subscribableLists[$i][0] . '</a></span>';
	}

	$imageDir = "$urlRequestRoot/$cmsFolder/$templateFolder/common/icons/16x16/actions/";
	$retVal .= '<table border="0" cellpadding="4" cellspacing="4"><tr><th>Available Lists</th><th></th><th>Lists I\'ve subscribed to</th><tr><td width="45%">';
	$retVal .= '<span class="newsletterlist" style="float: left" id="unsubscribedLists">' . $unsubscribedLists . '</span>';
	$retVal .= '</td><td style="vertical-align: center; text-align: center"><img src="' . $imageDir .'go-next.gif" /><br /><br /><img src="' . $imageDir . 'go-previous.gif" /></td><td width="45%">';
	$retVal .= '<span class="newsletterlist" style="float: right" id="subscribedLists">' . $subscribedLists . '</span>';
	$retVal .= '</td></tr></table>';
	$retVal .= '</fieldset>';

	return $retVal;
}
