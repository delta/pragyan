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
 * @copyright (c) 2008 Pragyan Team
 * @license http://www.gnu.org/licenses/ GNU Public License
 * For more details, see README
 */

//TODO : fix form upload bug where it screws up when multiple uploads are made.
//TODO: write FormElement::toHtmlForm() and FormElement::fromHtmlForm() to suit needs
//TODO : If the form is associated with a group, the form HAS to give the user the option to unregister from it.

	function generateFormDescBody($moduleCompId, $action = 'editform') {
		global $cmsFolder,$sourceFolder;
		global $templateFolder;global $moduleFolder;
		global $urlRequestRoot;
		$imagePath = "$urlRequestRoot/$cmsFolder/$templateFolder";$calpath="$urlRequestRoot/$cmsFolder/$moduleFolder";

		require_once("$sourceFolder/group.lib.php");
		$associatedGroupId = getGroupAssociatedWithForm($moduleCompId);

		/**SERVER SIDE UPDATION CODE*/
		if(isset($_POST['submittedform_desc'])) {
			$updates = array();

			if(isset($_POST['txtFormHeading'])) {
				$updates[] = "`form_heading` = '".escape($_POST['txtFormHeading'])."'";
			}
			if(isset($_POST['optLoginRequired'])) {
				if($associatedGroupId > 0) {
					$updates[] = '`form_loginrequired` = 1';
					if($_POST['optLoginRequired'] != 'yes') {
						displayerror('Error. You cannot allow anonymous users to register to this form because it is associated with a group.');
					}
				}
				else {
					$updates[] = '`form_loginrequired` = ' . ($_POST['optLoginRequired'] == 'yes' ? 1 : 0);
				}
			}
			if(isset($_POST['txtHeaderText'])) {
				$updates[] = "`form_headertext` = '".escape($_POST['txtHeaderText'])."'";
			}
			if(isset($_POST['txtFormExpiry'])) {
				$updates[] = "`form_expirydatetime` = '".escape($_POST['txtFormExpiry'])."'";
			}
			if(isset($_POST['optSendConfirmation'])) {
				$updates[] = '`form_sendconfirmation` = ' . ($_POST['optSendConfirmation'] == 'yes' ? 1 : 0);
			}
			if(isset($_POST['optUseCaptcha'])) {
				$updates[] = '`form_usecaptcha` = ' . ($_POST['optUseCaptcha'] == 'yes' ? 1 : 0);
			}
			if(isset($_POST['optUserEdit'])) {
				$updates[] = '`form_allowuseredit` = ' . ($_POST['optUserEdit'] == 'yes' ? 1 : 0);
			}
			if(isset($_POST['optUserUnregister'])) {
				if($associatedGroupId > 0) {
					$updates[] = '`form_allowuserunregister` = 1';
					if($_POST['optUserUnregister'] != 'yes') {
						displayerror('Error. You cannot prevent a user from unregistering from this form because it is associated with a group.');
					}
				}
				else {
					$updates[] = '`form_allowuserunregister` = ' . ($_POST['optUserUnregister'] == 'yes' ? 1 : 0);
				}
			}
			if(isset($_POST['optUserEmail'])) {
				$updates[] = '`form_showuseremail` = ' . ($_POST['optUserEmail'] == 'yes' ? 1 : 0);
			}
			if(isset($_POST['optUserFullname'])) {
				$updates[] = '`form_showuserfullname` = ' . ($_POST['optUserFullname'] == 'yes' ? 1 : 0);
			}
			if(isset($_POST['optUserProfiledata'])) {
				$updates[] = '`form_showuserprofiledata` = ' . ($_POST['optUserProfiledata'] == 'yes' ? 1 : 0);
			}
			if(isset($_POST['optRegDate'])) {
				$updates[] = '`form_showregistrationdate` = ' . ($_POST['optRegDate'] == 'yes' ? 1 : 0);
			}
			if(isset($_POST['optLastUpdate'])) {
				$updates[] = '`form_showlastupdatedate` = ' . ($_POST['optLastUpdate'] == 'yes' ? 1 : 0);
			}
			if(isset($_POST['txtFooterText'])) {
				$updates[] = "`form_footertext` = '".escape($_POST['txtFooterText'])."'";
			}
			if(count($updates) > 0) {
				$updateQuery = 'UPDATE `form_desc` SET ' . join($updates, ', ') .
				               ' WHERE `page_modulecomponentid` = \'' . $moduleCompId."'";
				if(mysql_query($updateQuery)) {
					displayinfo("All changes in the form have been successfully saved!");

				}
				else {
					displayerror('Some errors were encountered while trying to save changes.<br />' .
					        'The changes may not have been completely saved.');

				}
			}
		}

		/**Form desc : Getting data*/
		$formQuery = 'SELECT page_modulecomponentid, form_heading, form_loginrequired, form_headertext,	form_footertext, ' .
				'form_expirydatetime, form_sendconfirmation, form_usecaptcha, form_allowuseredit, '. 
				'form_allowuserunregister,form_showuseremail, form_showuserfullname, form_showuserprofiledata, '. 
				'form_showregistrationdate, form_showlastupdatedate ' .
				'FROM `form_desc` WHERE `page_modulecomponentid` = \'' . $moduleCompId."'";
		$formResult = mysql_query($formQuery);

		$userEdit = $formHeading = $headerText = $expiryDate = $requireLogin =
		$sendConfirmation = $useCaptcha = $userProfiledata = $userEmail = $userUnregister = 
		$userFullname = $regDate = $lastUpdate = $footerText = '';

		if($formResult) {
			if($formResultRow = mysql_fetch_assoc($formResult)) {
				$formHeading = $formResultRow['form_heading'];
				$requireLogin = $formResultRow['form_loginrequired'] ? 'checked="checked"' : '';
				$headerText = $formResultRow['form_headertext'];
				$expiryDate = $formResultRow['form_expirydatetime'];
				$sendConfirmation = $formResultRow['form_sendconfirmation'] ? 'checked="checked"' : '';
				$useCaptcha = $formResultRow['form_usecaptcha'] ? 'checked="checked"' : '';
				$userEdit = $formResultRow['form_allowuseredit'] ? 'checked="checked"' : '';
				$userUnregister = $formResultRow['form_allowuserunregister'] ? 'checked="checked"' : '';
				$userEmail = $formResultRow['form_showuseremail'] ? 'checked="checked"' : '';
				$userFullname = $formResultRow['form_showuserfullname'] ? 'checked="checked"' : '';
				$userProfiledata = $formResultRow['form_showuserprofiledata'] ? 'checked="checked"' : '';
				$regDate = $formResultRow['form_showregistrationdate'] ? 'checked="checked"' : '';
				$lastUpdate = $formResultRow['form_showlastupdatedate'] ? 'checked="checked"' : '';
				$footerText = $formResultRow['form_footertext'];
			}
		}

		$requireLoginN = $requireLogin == '' ? 'checked="checked"' : '';
		$sendConfirmationN = $sendConfirmation == '' ? 'checked="checked"' : '';
		$useCaptchaN = $useCaptcha == '' ? 'checked="checked"' : '';
		$userEditN = $userEdit == '' ? 'checked="checked"' : '';
		$userUnregisterN = $userUnregister == '' ? 'checked="checked"' : '';
		$userEmailN = $userEmail == '' ? 'checked="checked"' : '';
		$userFullnameN = $userFullname == '' ? 'checked="checked"' : '';
		$userProfiledataN = $userProfiledata == '' ? 'checked="checked"' : '';
		$regDateN = $regDate == '' ? 'checked="checked"' : '';
		$lastUpdateN = $lastUpdate == '' ? 'checked="checked"' : '';

		$disableBecauseAssociated = '';
		if($associatedGroupId > 0) {
			$requireLogin = $userUnregister = 'checked="checked"';
			$requireLoginN = $userUnregisterN = '';
			$disableBecauseAssociated = 'disabled="disabled"';
		}

		/** Form Desc Generating content */
		$formDescBody =<<<BODY

		<link rel="stylesheet" type="text/css" media="all" href="$calpath/form/calendar/calendar.css" title="Aqua" />
		<script type="text/javascript" src="$calpath/form/calendar/calendar.js"></script>

		<form id="formdetails" action="./+$action" method="post">
			<table width="100%" cellpadding="1" cellspacing="1" border="1">
				<tr>
					<td width="20%">Form Heading:</td><td><input type="text" name="txtFormHeading" value="$formHeading" onblur=check(this); /></td>
					
					<script type=text/javascript>
							function check(field) {
								val = field.value;
								if(field.value.length == 0) {
								
									alert("Enter a Form name");
									field.focus();
								
								}
							}
						</script>
				</tr>
				<tr>
					<td>Require Login?</td>
					<td>
						<label><input type="radio" name="optLoginRequired" value="yes" $requireLogin $disableBecauseAssociated />Yes</label>
						<label><input type="radio" name="optLoginRequired" value="no" $requireLoginN $disableBecauseAssociated />No</label>
					</td>
				</tr>
				<tr>
					<td>Form Header:</td>
					<td>
						<textarea style="width:98%"  name="txtHeaderText" rows="10" cols="60">$headerText</textarea>
					</td>
				</tr>

				<tr>
					<td>Expiry Date (YYYY-MM-DD 24Hrs clock): (blank to disable)</td><td> <input type="text" name="txtFormExpiry" id="sel1" size="25" value="$expiryDate" /><input type="reset" value=" ... " onclick="return showCalendar('sel1', '%Y-%m-%d %H:%M', '24', true);" /></td>
				</tr>

				<tr>
					<td>Send Confirmation?</td>
					<td>
						<label><input type="radio" name="optSendConfirmation" value="yes" $sendConfirmation />Yes</label>
						<label><input type="radio" name="optSendConfirmation" value="no" $sendConfirmationN />No</label>
					</td>
				</tr>
				<tr>
					<td>Use CAPTCHA Validation?</td>
					<td>
						<label><input type="radio" name="optUseCaptcha" value="yes" $useCaptcha />Yes</label>
						<label><input type="radio" name="optUseCaptcha" value="no" $useCaptchaN />No</label>
					</td>
				</tr>
				<tr>
					<td>Allow user to edit his entries again once registered?</td>
					<td>
						<label><input type="radio" name="optUserEdit" value="yes" $userEdit />Yes</label>
						<label><input type="radio" name="optUserEdit" value="no" $userEditN />No</label>
					</td>
				</tr>
				<tr>
					<td>Give the user the option to unregister?</td>
					<td>
						<label><input type="radio" name="optUserUnregister" value="yes" $userUnregister $disableBecauseAssociated />Yes</label>
						<label><input type="radio" name="optUserUnregister" value="no" $userUnregisterN $disableBecauseAssociated />No</label>
					</td>
				</tr>

				<tr>
					<td>Form Footer:</td>
					<td>
						<textarea style="width:98%" name="txtFooterText" rows="8" cols="60">$footerText</textarea>
					</td>
				</tr>
				<tr ><td colspan="2"><b>Settings for viewing registrants :</b> </td></tr>
				 <tr>
					<td>Show user e-mail?</td>
					<td>
						<label><input type="radio" name="optUserEmail" value="yes" $userEmail />Yes</label>
						<label><input type="radio" name="optUserEmail" value="no" $userEmailN />No</label>
					</td>
				</tr>
				<tr>
					<td>Show user fullname?</td>
					<td>
						<label><input type="radio" name="optUserFullname" value="yes" $userFullname />Yes</label>
						<label><input type="radio" name="optUserFullname" value="no" $userFullnameN />No</label>
					</td>
				</tr>
				<tr>
					<td>Show user profile information?</td>
					<td>
						<label><input type="radio" name="optUserProfiledata" value="yes" $userProfiledata />Yes</label>
						<label><input type="radio" name="optUserProfiledata" value="no" $userProfiledataN />No</label>
					</td>
				</tr>
				<tr>
					<td>Show Registration Date?</td>
					<td>
						<label><input type="radio" name="optRegDate" value="yes" $regDate />Yes</label>
						<label><input type="radio" name="optRegDate" value="no" $regDateN />No</label>
					</td>
				</tr>
				<tr>
					<td>Show Last Update Date?</td>
					<td>
						<label><input type="radio" name="optLastUpdate" value="yes" $lastUpdate />Yes</label>
						<label><input type="radio" name="optLastUpdate" value="no" $lastUpdateN />No</label>
					</td>
				</tr>
				</table>
			<input type="submit" name="submittedform_desc" value="Update Form" />
		</form><br/>
BODY;
		return $formDescBody;
	}



	function generateFormElementDescBody($moduleCompId, $action = 'editform') {
		global $sourceFolder,$cmsFolder;
		global $templateFolder;global $moduleFolder;
		global $urlRequestRoot;
		$imagePath = "$urlRequestRoot/$cmsFolder/$templateFolder";$calpath="$urlRequestRoot/$cmsFolder/$moduleFolder";

		$elementsQuery = "SELECT * FROM `form_elementdesc` WHERE `page_modulecomponentid` =  '$moduleCompId' ORDER BY `form_elementrank` ASC";
		$elementsResult = mysql_query($elementsQuery) or die(mysql_error());
		$elementData = '';
		while($elementsRow = mysql_fetch_assoc($elementsResult)) {
			$tmpElement = new FormElement();
			$tmpElement->fromMysqlTableRow($elementsRow);

			$elementData .= $tmpElement->toHtmlTableRow($imagePath, $action) . "\n";
		}
		$formElementDescBody =<<<BODY
		<h2>Fields:</h2>
		<form id="formentries" action="./+$action" method="POST">
			<table cellpadding="1" cellspacing="1" border="1">
				<tr>
					<th>Actions</th>
					
					<th>Name</th>
					<th>Description</th>
					<th>Type</th>
					<th>Tooltip</th>
					<th>Other Information</th>
					<th title="Only in the case of radio, check or select element type">Extra options*</th>
				
				</tr>
					$elementData
				</tr>
			</table>

		<input type="submit" name="addformelement_descsubmit" value="Add Element">

		</form>
BODY;
		return $formElementDescBody;
	}


	function moveFormElement($moduleCompId,$subaction,$elementId) {
		if ($subaction=='moveDown') {
			$compare = ">=";
			$order = "ASC";
		}
		else if($subaction=='moveUp') {
			$compare = "<=";
			$order = "DESC";
		}



		$query = "SELECT * FROM `form_elementdesc` WHERE `form_elementrank` $compare(SELECT `form_elementrank` FROM `form_elementdesc` WHERE `page_modulecomponentid`='$moduleCompId' AND `form_elementid`='$elementId') AND `page_modulecomponentid`='$moduleCompId' AND `form_elementid`!='$elementId' ORDER BY `form_elementrank` $order LIMIT 0,1";
		$result = mysql_query($query) or die(mysql_query());
		if (mysql_num_rows($result) == 0) {
			displayerror("You cannot move up/down the first/last element in form");

		} else {
			$tempTarg = mysql_fetch_assoc($result);
			$query = "SELECT `form_elementrank` FROM `form_elementdesc` WHERE `page_modulecomponentid`='$moduleCompId' AND `form_elementid`='$elementId'";
			$result = mysql_query($query) or die(mysql_query());
			$tempSrc = mysql_fetch_assoc($result);

			if ($tempTarg['form_elementrank'] == $tempSrc['form_elementrank']) {
				$query = "UPDATE `form_elementdesc` SET `form_elementrank` = `form_elementid` WHERE `page_modulecomponentid`='$tempTarg[page_modulecomponentid]'";
				$result = mysql_query($query) or die(mysql_error());
				if (mysql_affected_rows() > 0)
					displayinfo("Error in form element rank corrected. Please reorder them");
				else
					displayerror("Failed to correct error in form element ranks!");
			} else {
				$query = "UPDATE `form_elementdesc` SET `form_elementrank` = '$tempSrc[form_elementrank]' WHERE `page_modulecomponentid`='$tempTarg[page_modulecomponentid]' AND `form_elementid`='$tempTarg[form_elementid]'";
				$result = mysql_query($query) or die(mysql_error());
				$query = "UPDATE `form_elementdesc` SET `form_elementrank` = '$tempTarg[form_elementrank]' WHERE `page_modulecomponentid`='$moduleCompId' AND `form_elementid`='$elementId'";
				$result = mysql_query($query) or die(mysql_error());
			}
		}

	}


	/**Also take care to remove all data associated with that particular element.
	 * Make sure alert box warning is given on clicking delete button
	 */
	function deleteFormElement($moduleCompId,$elementId) {
		$query="DELETE FROM `form_elementdesc` WHERE `page_modulecomponentid` = '$moduleCompId' AND `form_elementid`='$elementId'";
		$resultDel=mysql_query($query);
		if(mysql_affected_rows()>0)
		$query1=1;
		else $query1=0;
		$queryDelData="DELETE FROM `form_elementdata` WHERE `page_modulecomponentid` = '$moduleCompId' AND `form_elementid`='$elementId'";
		$resultDelData=mysql_query($queryDelData);
		if(!$resultDelData)	{ displayerror('Invalid query: ' . mysql_error()); 	return false; }
		$queryAffectedRows=mysql_affected_rows();
		if($queryAffectedRows>0)
		$query2=1;
		else $query2=0;
		if($query1&&$query2)
		return true;
		else return false;
	}


	/**Adds an empty form element */
	function addDefaultFormElement($moduleCompId) {
		$query="SELECT MAX(`form_elementid`) FROM `form_elementdesc` WHERE `page_modulecomponentid`='$moduleCompId'";
		$result=mysql_query($query);
		$row = mysql_fetch_row($result);

		$elementId = 0;
		if(!is_null($row[0])) {
			$elementId = $row[0] + 1;
		}

		$queryInsert="INSERT INTO `form_elementdesc` " .
				"(`page_modulecomponentid`, `form_elementid`, `form_elementname`, `form_elementdisplaytext`, " .
				"`form_elementtype`, `form_elementsize`, `form_elementtypeoptions`, `form_elementdefaultvalue`, " .
				"`form_elementmorethan`, `form_elementlessthan`, `form_elementcheckint`, `form_elementtooltiptext`," .
				"`form_elementisrequired` ,`form_elementrank`) VALUES " .
				"('$moduleCompId', '$elementId', 'register', 'Are you sure you want to register ?', 'radio', 100, 'Yes|No' , NULL , NULL , NULL , 0, '', 0, '$elementId')";
		$resultAdd=mysql_query($queryInsert);





		if(mysql_affected_rows()>0)
			return true;
		else return false;
	}

