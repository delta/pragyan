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
/**
 * Form doesnt have ability to associate or unassociate with a group.
 * That is done through groups.
 *
 * If it is not associated with a group
 * 		When associated, copy all regiestered users to groups table -> done by groups
 * 		Have a function which returns userids of all users registered in a form.
 * *
 * If login required is changed from off to on at any time,
 * 		then give warning, and delete all anonymous entries.
 *
 * If loginrequired is turned from on to off, allow only if it is not associted with a group
 * give error msg with the group namem and don't allow him
 *
 * To put deleteall registrants in edit registrants
 *
 *TODO: one maintenance script : If user doesn't exist in form_regdata, remove from form_elementdata
 *
 */
 
 /*
  * TODO:: 
  * 
  * 
  * URGENT:: Send user a confirmation mail on registration
  * 
  * 
  * 
  * */
global $sourceFolder;
global $moduleFolder;
require_once("$sourceFolder/$moduleFolder/form/editform.php");
require_once("$sourceFolder/$moduleFolder/form/editformelement.php");
require_once("$sourceFolder/$moduleFolder/form/registrationformgenerate.php");
require_once("$sourceFolder/$moduleFolder/form/registrationformsubmit.php");
require_once("$sourceFolder/$moduleFolder/form/viewregistrants.php");

 class form implements module, fileuploadable {
	private $userId;
	private $moduleComponentId;
	private $action;

	public function getHtml($gotuid, $gotmoduleComponentId, $gotaction) {
		$this->userId = $gotuid;
		$this->moduleComponentId = $gotmoduleComponentId;
		$this->action = $gotaction;

		if($this->action=="view")
			return $this->actionView();
		if($this->action=="editform")
			return $this->actionEditform();
		if($this->action=="viewregistrants")
			return $this->actionViewregistrants();
		if($this->action=="editregistrants")
			return $this->actionEditregistrants();
		if($this->action=="reports")
			return $this->actionReports();
		

		/* Options to be displayed :
		 *
		 * View registrants				View the ppl who hav registered (without permission to edit)
		 * Edit registrants				View the ppl who hav registered (with edit permission)
		 * Edit form structure			Edit the structure of fields and their attributes
		 * Register						Show registration page (View function)
		 */
	}


	public static function getFileAccessPermission($pageId,$moduleComponentId,$userId, $fileName) {
		/* in view, if its one of his files, show him */
		if(getPermissions($userId,$pageId,"editregistrants")||getPermissions($userId,$pageId,"viewregistrants")) {
			return true;
		}
		$uploadedQuery = "SELECT `d.form_elementdata`
FROM `form_elementdata` d
	JOIN `form_elementdesc` e ON (`d.page_modulecomponentid` = `e.page_modulecomponentid`
		AND d.form_elementid = e.form_elementid )
WHERE `d.page_modulecomponentid` = $moduleComponentId AND `d.user_id` = $userId AND `d.form_elementdata` = \"$fileName\"";
		$uploadedResult = mysql_query($uploadedQuery) or displayerror(mysql_error() . "form.lib L:181");
		if(mysql_num_rows($uploadedResult)>0 && getPermissions($userId, $pageId, "view"))
			return true;
		else return false;
	}

	public static function getUploadableFileProperties(&$fileTypesArray,&$maxFileSizeInBytes) {
		$fileTypesArray = array('jpg','jpeg','png','doc','pdf','gif','bmp','css','js','html','xml','ods','odt','oft','pps','ppt','tex','tiff','txt','chm','mp3','mp2','wave','wav','mpg','ogg','mpeg','wmv','wma','wmf','rm','avi','gzip','gz','rar','bmp','psd','bz2','tar','zip','swf','fla','flv','eps','xcf','xls','exe','7z');
		$maxFileSizeInBytes = 30*1024*1024;
	}

	public function actionView() {
		/**
		 * Find from db if registration form is editable,
		 *
		 * that is 1) Expiry date has not passed and 2) form_allowuseredit is true
		 * 3) If form is of type allow logged in users only then make sure user is logged in
		 *
		 * We will do captcha and send confirmation once everything else is done.
		 */
		global $sourceFolder;		global $moduleFolder;

		$formDescQuery='SELECT `form_loginrequired`, `form_expirydatetime`, (NOW() >= `form_expirydatetime`) AS `form_expired`, `form_sendconfirmation`, ' .
				'`form_usecaptcha`, `form_allowuseredit`, `form_allowuserunregister` ' .
				'FROM `form_desc` WHERE `page_modulecomponentid`='.$this->moduleComponentId;
		$formDescResult=mysql_query($formDescQuery);
		if (!$formDescResult) {
			displayerror('E69 : Invalid query: ' . mysql_error());
			return '';
		}
		$formDescRow = mysql_fetch_assoc($formDescResult);

		if($formDescRow['form_loginrequired'] == 1) {
			if($this->userId <= 0) {
				displayerror('You must be logged in to fill this form. <a href="./+login">Click here</a> to login.');
				return '';
			}
			/// Check if the user has a completed profile. Otherwise, prompt to complete.
			else if(!verifyUserProfileFilled($this->userId)) {
				displayinfo('Your profile information is incomplete. Please complete your profile information before filling this form. <a href="./+profile">Click here</a> to complete your profile.');
				return '';
			}
		}

		if($formDescRow['form_expired'] != 0 && $formDescRow['form_expirydatetime']!="0000-00-00 00:00:00") {
			displayerror('The last date to register to this form ('.$formDescRow['form_expirydatetime'].') is over.');
			return '';
		}

		if($formDescRow['form_allowuseredit']==0 &&  verifyUserRegistered($this->moduleComponentId,$this->userId)) {
			displayerror('You have already registered to this form once. You cannot register again. Contact the administrator for further queries.');
			return '';
		}


		if(isset($_POST['submitreg_form_'.$this->moduleComponentId]))
			submitRegistrationForm($this->moduleComponentId,$this->userId);

		if($formDescRow['form_allowuserunregister'] == 1 && isset($_GET['subaction'])&&($_GET['subaction']=="unregister"))
			unregisterUser($this->moduleComponentId,$this->userId);

		$unregisterBody = '';
		if($formDescRow['form_allowuserunregister'] == 1 && verifyUserRegistered($this->moduleComponentId, $this->userId)) {
			$unregisterBody =
						'<br /><p>If you wish to unregister from this form, click here : <input type="button" ' .
						'value = "Unregister" onclick="if(confirm(\'Are you sure you want to unregister from this form?\')) window.location=\'./&subaction=unregister\';" />';
		}

		return generateRegistrationForm($this->moduleComponentId,$this->userId).$unregisterBody;
	}

	/**
	 * Determines User Ids of all users registered to a form
	 * @param $moduleComponentId Module Component Id of the form
	 * @return Array of User Ids of all the users registered to the form with the given Module Component Id
	 */
	public static function getRegisteredUserArray($moduleComponentId) {
		$userQuery = "SELECT `user_id` FROM `form_regdata` WHERE `page_modulecomponentid` = $moduleComponentId";
		$userResult = mysql_query($userQuery);
		$registeredUsers = array();
		while($userRow = mysql_fetch_row($userResult))
			$registeredUsers[] = $userRow[0];
		return $registeredUsers;
	}

	public static function getRegisteredUserCount($moduleComponentId) {
		$userQuery = "SELECT COUNT(`user_id`) FROM `form_regdata` WHERE `page_modulecomponentid` = $moduleComponentId";
		$userResult = mysql_query($userQuery);
		$userRow = mysql_fetch_row($userResult);
		return $userRow[0];
	}

	public static function isGroupAssociable($moduleComponentId) {
		$validQuery = 'SELECT `form_loginrequired`, `form_allowuserunregister` FROM `form_desc` WHERE `page_modulecomponentid` = ' . $moduleComponentId;
		$validResult = mysql_query($validQuery);
		$validRow = mysql_fetch_row($validResult);

		if(!$validResult || !$validRow) {
			displayerror('Error trying to retrieve data from the database: form.lib.php:L163');
			return false;
		}

		return $validRow[0];
	}

	/**
	 * Performs the action "Edit Form" on a form
	 */
	public function actionEditform() {
		global $sourceFolder;		global $moduleFolder;
		if(
			isset($_GET['subaction']) && $_GET['subaction'] == 'editformelement' &&
			isset($_POST['elementid']) && ctype_digit($_POST['elementid']) &&
			isset($_POST['txtElementDesc']) && isset($_POST['selElementType']) &&
			isset($_POST['txtToolTip']) && isset($_POST['txtElementName'])
			)
			submitEditFormElementDescData($this->moduleComponentId,escape($_POST['elementid']));
		if(
			isset($_GET['subaction']) && ($_GET['subaction']=='editformelement')&&
			isset($_GET['elementid']) && ctype_digit($_GET['elementid'])
			)
			return generateEditFormElementDescBody($this->moduleComponentId,escape($_GET['elementid']));
		if(isset($_POST['addformelement_descsubmit']))
			addDefaultFormElement($this->moduleComponentId);
		if(isset($_GET['subaction'])&&($_GET['subaction']=='deleteformelement')&&isset($_GET['elementid']))
			deleteFormElement($this->moduleComponentId,escape($_GET['elementid']));
		if(isset($_GET['subaction'])&&(($_GET['subaction']=='moveUp')||($_GET['subaction']=='moveDown'))&&isset($_GET['elementid']))
			moveFormElement($this->moduleComponentId,escape($_GET['subaction']),escape($_GET['elementid']));

		$html = generateFormDescBody($this->moduleComponentId).generateFormElementDescBody($this->moduleComponentId);
		global $ICONS;
		return "<fieldset><legend>{$ICONS['Form Edit']['small']}Edit Form</legend>$html</fieldset>";
	}

	public function actionViewregistrants() {
		global $sourceFolder, $moduleFolder;

		$sortField = 'registrationdate'; /// Default Values
		$sortOrder = 'asc';
		if(isset($_GET['sortfield']))
			$sortField = escape($_GET['sortfield']);
		if(isset($_GET['sortorder']) && ($_GET['sortorder'] == 'asc' || $_GET['sortorder'] == 'desc'))
			$sortOrder = escape($_GET['sortorder']);
		global $ICONS;
		$html= generateFormDataTable($this->moduleComponentId, $sortField, $sortOrder);
		return "<fieldset><legend>{$ICONS['Form Registrants']['small']}View Form Registrants</legend>
		<form action='./+viewregistrants' method='POST'>
		<input type='submit' name='save_as_excel' value='Save as Excel'/>
		</form>
		$html</fieldset>";
	}

	public function actionEditregistrants() {
		/**
		 * After view registrants completes, generateFormDataRow() will be used here also
		 * then manually prepend the "delete" button and "edit" button columns
		 *
		 * After  generateFormDataTable() completes, move the code to generate the top header row (with the sort
		 * 			by this column images) to a new function and call it here also and manually prepened two <th>s of
		 * 			delete and edit
		 *
		 * The delete button should point to a subaction through get vars
		 *
		 * Clicking edit button, should do something like edit in 2nd form in "editform" does with a twist :
		 *	to generate the edit form simply call generateRegistrationForm($moduleCompId,$userId,$action=)
		 *  with action ./+editregistrants&subaction=editregistrant&useremail=<useremail>
		 *
		 * and when submitted call submitRegistrationForm()
		 */
		global $sourceFolder, $moduleFolder;

		if(isset($_GET['subaction']) && isset($_GET['useremail'])) {
			if($_GET['subaction'] == 'edit') {
				if(isset($_POST['submitreg_form_' . $this->moduleComponentId])) {
					submitRegistrationForm($this->moduleComponentId, getUserIdFromEmail(escape($_GET['useremail'])), true, true);
				}

				return (
						'<a href="./+editregistrants">&laquo; Back</a><br />' .
						generateRegistrationForm($this->moduleComponentId, $this->userId, './+editregistrants&subaction=edit&useremail=' . escape($_GET['useremail']), true) .
						'<br /><a href="./+editregistrants">&laquo; Back</a><br />'
				);
			}
			elseif($_GET['subaction'] == 'delete') {
				if($_GET['useremail']=="Anonymous")
					$userIdTemp = escape($_GET['registrantid']);
				else
					$userIdTemp = getUserIdFromEmail(escape($_GET['useremail']));
				if(!unregisterUser($this->moduleComponentId, $userIdTemp))
					displayerror('Error! User with the given e-mail ' . escape($_GET['useremail']) . ' was not found.');
			}
		}
		elseif(isset($_GET['subaction']) && $_GET['subaction'] == 'getsuggestions' && isset($_GET['forwhat'])) {
			echo $this->getUnregisteredUsersFromPattern(escape($_GET['forwhat']));
			disconnect();
			exit();
		}
		elseif(isset($_POST['btnAddUserToForm']) && isset($_POST['useremail'])) {
			$hyphenPos = strpos($_POST['useremail'], '-');
			if($hyphenPos >= 0) {
				$userEmail = escape(trim(substr($_POST['useremail'], 0, $hyphenPos - 1)));
			}
			else {
				$userEmail = escape($_POST['useremail']);
			}

			$targetUserId = getUserIdFromEmail($userEmail);
			if($targetUserId > 0) {
				if(verifyUserRegistered($this->moduleComponentId, $targetUserId)) {
					displayerror('The given user is already registered to this form.');
				}
				else {
					registerUser($this->moduleComponentId, $targetUserId);
				}
			}
			else {
				displayerror('A user registered with the e-mail ID you entered was not found.');
			}
		}
		elseif(isset($_POST['btnEmptyRegistrants'])) {
			$registeredUsers = form::getRegisteredUserArray($this->moduleComponentId);
			$registeredUserCount = count($registeredUsers);
			for($i = 0; $i < $registeredUserCount; $i++) {
				unregisterUser($this->moduleComponentId, $registeredUsers[$i], true);
			}
			displayinfo('All registrations to this form have been deleted.');
		}

		$sortField = 'registrationdate'; /// Default Values
		$sortOrder = 'asc';
		if(isset($_GET['sortfield']))
			$sortField = escape($_GET['sortfield']);
		if(isset($_GET['sortorder']) && ($_GET['sortorder'] == 'asc' || $_GET['sortorder'] == 'desc'))
			$sortOrder = escape($_GET['sortorder']);
		global $ICONS;
		$html= generateFormDataTable($this->moduleComponentId, $sortField, $sortOrder, 'editregistrants');
		return "<fieldset><legend>{$ICONS['Form Registrants']['small']}Edit Form Registrants</legend>$html</fieldset>";
	}
	
	public function actionReports() {
		 global $userId,$urlRequestRoot;
		 $query = "SELECT `page_id`, `page_modulecomponentid` FROM `".MYSQL_DATABASE_PREFIX."pages` WHERE `page_module`='form'";
		 $resource = mysql_query($query);
		 $report=<<<CSS
		  <style type="text/css">
		  
    #reports tbody tr.even td {
      background-color: #f0f8ff;
      color: #000;
    }
    #reports tbody tr td a, a:link, a:visited {
    	color: #000;
    }
    #reports tbody tr.odd  td {
      background-color: #fff;color: #000;
    }
  </style>
CSS;
			$report .='<table id="reports"><tbody><tr><td>Form</td><td>No. of registrants</td></tr>'; 
			$class = 'even';
		 while($result = mysql_fetch_assoc($resource)) {
		 	$permission = getPermissions($userId,$result[page_id],'viewRegistrant','form');
			if($permission) {
				$pageId = $result['page_id'];
				$parentPageId = getParentPage($pageId);
				$parentTitle = getPageTitle($parentPageId);
				$formTitle = getPageTitle($pageId);
				$formInfo = $parentTitle.'_'.$formTitle;
				$formPath = getPagePath($pageId);
				$query = "SELECT count(distinct(`user_id`)) FROM `form_regdata` WHERE `page_modulecomponentid`=$result[page_modulecomponentid]";
				$resource2 = mysql_query($query) ;//or die(mysql_error());
				$result2 = mysql_fetch_row($resource2);
				
				if(!strpos($formPath,'qaos'))
				{
					if($class=='even')
						{
							$class='odd';
						}
					else {
						$class = 'even';
					}
				$report .= "<tr class=\"$class\"><td><a href=\"$urlRequestRoot$formPath\">$formInfo</a></td><td>$result2[0]</td></tr>";
				}
			}
		 }
			$report .='</tbody></table>';
		 return $report;
	}

	private function getUnregisteredUsersFromPattern($pattern) {
		$registeredUserArray = form::getRegisteredUserArray($this->moduleComponentId);
		if(count($registeredUserArray) > 0) {
			$registeredUserArray = implode(',', $registeredUserArray);
		}
		else {
			$registeredUserArray = '0';
		}
		$suggestionsQuery = "SELECT IF(`user_email` LIKE \"$pattern%\", 1, " .
			"IF(`user_fullname` LIKE \"$pattern%\", 2, " .
			"IF(`user_fullname` LIKE \"% $pattern%\", 3, " .
			"IF(`user_email` LIKE \"%$pattern%\", 4, " .
			"IF(`user_fullname` LIKE \"%$pattern%\", 5, 6" .
			"))))) AS `relevance`,	`user_email`, `user_fullname` FROM `".MYSQL_DATABASE_PREFIX."users` WHERE " .
			"`user_activated` = 1 AND (`user_email` LIKE \"%$pattern%\" OR `user_fullname` LIKE \"%$pattern%\") " .
			"AND `user_id` NOT IN ($registeredUserArray) ORDER BY `relevance`";
		$suggestionsResult = mysql_query($suggestionsQuery);
		if(!$suggestionsResult) return $pattern;

		$suggestions = array($pattern);
		while($suggestionsRow = mysql_fetch_row($suggestionsResult)) {
			$suggestions[] = $suggestionsRow[1] . ' - ' . $suggestionsRow[2];
		}

		return join($suggestions, ',');
	}

	public function createModule(&$moduleComponentId) {
		global $sourceFolder, $moduleFolder;
		$query = 'SELECT MAX(`page_modulecomponentid`) FROM `form_desc`';
		$result = mysql_query($query) or die(mysql_error() . 'form.lib L:149');
		$row = mysql_fetch_row($result);
		$compId = $row[0] + 1;
		$query = "INSERT INTO `form_desc` (`page_modulecomponentid`, `form_heading`,`form_loginrequired`,`form_headertext`)
					VALUES ('".$compId."', '',1,'Coming up Soon');";
		$result = mysql_query($query) or die(mysql_error()."form.lib L:157");
		if (mysql_affected_rows()) {
			$moduleComponentId = $compId;
			addDefaultFormElement($moduleComponentId);
			return true;
		} else
			return false;
	}

	public function deleteModule($moduleComponentId){
		$query = "DELETE FROM `form_elementdata` WHERE `form_elementdata`.`page_modulecomponentid` =$moduleComponentId";
		$result = mysql_query($query);
		$query = "DELETE FROM `form_regdata` WHERE `form_regdata`.`page_modulecomponentid` =$moduleComponentId";
		$result = mysql_query($query);
		$query = "DELETE FROM `form_elementdesc` WHERE `form_elementdesc`.`page_modulecomponentid` =$moduleComponentId";
		$result = mysql_query($query);
		$query = "DELETE FROM `form_desc` WHERE `form_desc`.`page_modulecomponentid` =$moduleComponentId";
		$result = mysql_query($query);
		if ((mysql_affected_rows()) >= 1)
			return true;
		else{
			displayerror("There was some error in deleting the module");
			return false;
		}
	}

	public function copyModule($moduleComponentId){
		// Select the new module component id
		$query = "SELECT MAX(`page_modulecomponentid`) as MAX FROM `form_desc` ";
		$result = mysql_query($query) or displayerror(mysql_error() . "form.lib L:181");
		$row = mysql_fetch_assoc($result);
		$compId = $row['MAX'] + 1;
//changing
		//insert a new row in form_desc
		$query = "SELECT * FROM `form_desc` WHERE `page_modulecomponentid`=$moduleComponentId";
		$result = mysql_query($query);
		while($formdesc_content = mysql_fetch_assoc($result)){
			$formdesc_query="INSERT INTO `form_desc` (`page_modulecomponentid` ,`form_heading` ,`form_loginrequired` ,`form_headertext` ,`form_footertext` ,`form_expirydatetime` ,`form_sendconfirmation` ,`form_usecaptcha` ,`form_allowuseredit` ,`form_allowuserunregister` ,`form_showuseremail` ,`form_showuserfullname` ,`form_showuserprofiledata`,`form_showregistrationdate` ,`form_showlastupdatedate`) VALUES ($compId, '".mysql_escape_string($formdesc_content['form_heading'])."', '".mysql_escape_string($formdesc_content['form_loginrequired'])."', '".mysql_escape_string($formdesc_content['form_headertext'])."', '".mysql_escape_string($formdesc_content['form_footertext'])."' , '".mysql_escape_string($formdesc_content['form_expirydatetime'])."' , '".mysql_escape_string($formdesc_content['form_sendconfirmation'])."', '".mysql_escape_string($formdesc_content['form_usecaptcha'])."', '".mysql_escape_string($formdesc_content['form_allowuseredit'])."', '".mysql_escape_string($formdesc_content['form_allowuserunregister'])."', '".mysql_escape_string($formdesc_content['form_showuseremail'])."', '".mysql_escape_string($formdesc_content['form_showuserfullname'])."', '".mysql_escape_string($formdesc_content['form_showuserprofiledata'])."', '".mysql_escape_string($formdesc_content['form_showregistrationdate'])."', '".mysql_escape_string($formdesc_content['form_showlastupdatedate'])."')";
			mysql_query($formdesc_query) or displayerror(mysql_error()."form.lib L:183");
		}

		//insert all new elementdesc rows for the new module
		$query = "SELECT * FROM `form_elementdesc` WHERE `page_modulecomponentid`=$moduleComponentId";
		$result = mysql_query($query);
		$rows = mysql_num_rows($result);

		while($formelementdesc_content = mysql_fetch_assoc($result)){
			$elementdesc_query = "INSERT INTO `form_elementdesc` (`page_modulecomponentid` ,`form_elementid` ,`form_elementname` ,`form_elementdisplaytext` ,`form_elementtype` ,`form_elementsize` ,`form_elementtypeoptions` ,`form_elementdefaultvalue` ,`form_elementmorethan` ,`form_elementlessthan` ,`form_elementcheckint` ,`form_elementtooltiptext` ,`form_elementisrequired` ,`form_elementrank`)VALUES ('$compId', '".mysql_escape_string($formelementdesc_content['form_elementid'])."', '".mysql_escape_string($formelementdesc_content['form_elementname'])."', '".mysql_escape_string($formelementdesc_content['form_elementdisplaytext'])."', '".mysql_escape_string($formelementdesc_content['form_elementtype'])."', '".mysql_escape_string($formelementdesc_content['form_elementsize'])."', '".mysql_escape_string($formelementdesc_content['form_elementtypeoptions'])."' , '".mysql_escape_string($formelementdesc_content['form_elementdefaultvalue'])."' , '".mysql_escape_string($formelementdesc_content['form_elementmorethan'])."' , '".mysql_escape_string($formelementdesc_content['form_elementlessthan'])."' , '".mysql_escape_string($formelementdesc_content['form_elementcheckint'])."', '".mysql_escape_string($formelementdesc_content['form_elementtooltiptext'])."', '".mysql_escape_string($formelementdesc_content['form_elementisrequired'])."', '".mysql_escape_string($formelementdesc_content['form_elementrank'])."')";
			mysql_query($elementdesc_query) or displayerror(mysql_error()."form.lib L:196");
			$rows -= mysql_affected_rows();
		}
		if($rows!=0)
			return false;

		/// insert all new elementdata in the element data table
		/// Right now commented coz don't want to copy data
/*		$query = "SELECT * FROM `form_elementdata` WHERE `page_modulecomponentid`=$moduleComponentId";
		$result = mysql_query($query);
		$rows = mysql_num_rows($result);
		while($formelementdata_content = mysql_fetch_assoc($result)){
			$elementdata_query = "INSERT INTO `pragyan_v2`.`form_elementdata` (`user_id` ,`page_modulecomponentid` ,`form_elementid` ,`form_elementdata`) VALUES ('".mysql_escape_string($formelementdata_content['user_id'])."', '$compId', '".mysql_escape_string($formelementdata_content['form_elementid'])."', '".mysql_escape_string($formelementdata_content['form_elementdata'])."')";
			mysql_query($elementdata_query) or displayerror(mysql_error()."form.lib L:207");
			$rows -= mysql_affected_rows();
		}
		if($rows!=0)
			return false;

		$query = "SELECT * FROM `form_regdata` WHERE `page_modulecomponentid`=$moduleComponentId";
		$result = mysql_query($query);
		$rows = mysql_num_rows($result);
		while($formregdata_content = mysql_fetch_assoc($result)){
			$formregdata_query =
					"INSERT INTO `form_regdata` " .
					"(`user_id` ,`page_modulecomponentid` ,`form_firstupdated` ,`form_lastupdated` ,`form_verified`) " .
					"VALUES ('".mysql_escape_string($formregdata_content['user_id'])."', $compId, '".$formregdata_content['form_firstupdated']."', '".$formregdata_content['form_lastupdated']."' , '".$formregdata_content['form_verified']."')";
			mysql_query($formregdata_query) or displayerror(mysql_error()."form.lib L:215");
			$rows -= mysql_affected_rows();
		}
		if($rows!=0)
			return false;
*/
		return $compId;
	}
 }

