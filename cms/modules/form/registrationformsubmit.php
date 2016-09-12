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

/*
 * Make all POST variables name formid and elementid dependent (form_<form_id>_element_<elementid>)
 *
 * But, make all javascript name variables, formid, elementid AND one more random number dependent - to
 * 				allow more than one forms in the same page...
 *
 *
 *
 */

	function submitRegistrationForm($moduleCompId, $userId, $silent = false, $disableCaptcha = false) {
		///-------------------------Get anonymous unique negative user id---------------
		if($userId==0) {
			$useridQuery = "SELECT MIN(`user_id`) - 1 AS MIN FROM `form_regdata` WHERE 1";
			$useridResult = mysqli_query($GLOBALS["___mysqli_ston"], $useridQuery);
			if(mysqli_num_rows($useridResult)>0) {
				$useridRow = mysqli_fetch_assoc($useridResult);
				$userId = $useridRow['MIN'];
			}
			else
				$userId = -1;
		}
		///-----------------------------Anonymous user id ends-------------------------------
		///---------------------------- CAPTCHA Validation ----------------------------------
		if(!$disableCaptcha) {
			$captchaQuery = 'SELECT `form_usecaptcha` FROM `form_desc` WHERE `page_modulecomponentid` = \'' . $moduleCompId."'";
			$captchaResult = mysqli_query($GLOBALS["___mysqli_ston"], $captchaQuery);
			$captchaRow = mysqli_fetch_row($captchaResult);
			if($captchaRow[0] == 1)
				if(!submitCaptcha())
					return false;

		}
		///------------------------ CAPTCHA Validation Ends Here ----------------------------

		$query="SELECT `form_elementid`,`form_elementtype` FROM `form_elementdesc` WHERE `page_modulecomponentid`='$moduleCompId'";
		$result=mysqli_query($GLOBALS["___mysqli_ston"], $query);
		$allFieldsUpdated = true;
		while($elementRow=mysqli_fetch_assoc($result)) {
			$type = $elementRow['form_elementtype'];
			$elementId = $elementRow['form_elementid'];
			$postVarName = "form_".$moduleCompId."_element_".$elementRow['form_elementid'];
			$functionName = "submitRegistrationForm".ucfirst(strtolower($type));

			$elementDescQuery="SELECT `form_elementname`,`form_elementsize`,`form_elementtypeoptions`,`form_elementmorethan`," .
					"`form_elementlessthan`,`form_elementcheckint`,`form_elementisrequired` FROM `form_elementdesc` " .
					"WHERE `page_modulecomponentid`='$moduleCompId' AND `form_elementid` ='$elementId'";
			$elementDescResult=mysqli_query($GLOBALS["___mysqli_ston"], $elementDescQuery);
			if (!$elementDescResult) {	displayerror('E69 : Invalid query: ' . ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false))); 	return false; 	}

			$elementDescRow = mysqli_fetch_assoc($elementDescResult);

			$elementName = $elementDescRow['form_elementname'];
			$elementSize = $elementDescRow['form_elementsize'];
			$elementTypeOptions = $elementDescRow['form_elementtypeoptions'];
			$elementMoreThan = $elementDescRow['form_elementmorethan'];
			$elementLessThan = $elementDescRow['form_elementlessthan'];
			$elementCheckInt = ($elementDescRow['form_elementcheckint'])==1?true:false;
			$elementIsRequired = ($elementDescRow['form_elementisrequired'])==1?true:false;

			if($functionName($moduleCompId, $elementId, $userId, $postVarName, $elementName, $elementSize, $elementTypeOptions, $elementMoreThan, $elementLessThan, $elementCheckInt, $elementIsRequired)==false)	{
			//	displayerror("Error in inputting data in function $functionName.");
				$allFieldsUpdated = false;
				break;
			}
		}
		$update = 0;
		if(!$allFieldsUpdated) {
			if($userId < 0)
				unregisterUser($moduleCompId,$userId);
			else {
				if (!verifyUserRegistered($moduleCompId, $userId)) {
					$deleteelementdata_query = "DELETE FROM `form_elementdata` WHERE `user_id` = '$userId' AND `page_modulecomponentid` ='$moduleCompId' ";
					$deleteelementdata_result = mysqli_query($GLOBALS["___mysqli_ston"], $deleteelementdata_query);
				}
				return false;
			}
		}
		else {
			if(!verifyUserRegistered($moduleCompId,$userId)) {
				registerUser($moduleCompId,$userId);
			}
			else{
				updateUser($moduleCompId,$userId);
				$update = 1;
			}
			if(!$silent)
			{
				$footerQuery = "SELECT `form_footertext`, `form_sendconfirmation`, `form_registrantslimit`, `form_closelimit` FROM `form_desc` WHERE `page_modulecomponentid` = '$moduleCompId'";
				$footerResult = mysqli_query($GLOBALS["___mysqli_ston"], $footerQuery);
				$footerRow = mysqli_fetch_row($footerResult);

				$footerText = $footerRow[0];
				$footerTextLength = strlen($footerText);

				if ($footerTextLength > 7) {
					if (substr($footerText, 0, 4) == '<!--' && substr($footerText, $footerTextLength - 3) == '-->')
						$footerText = substr($footerText, 4, $footerTextLength - 7);
					else
						$footerText = '';
				}
				else
					$footerText = '';
				$waitlisted = '';
				displayinfo($footerText == '' ? "User successfully registered!." : $footerText);
				if(!$update){
					if($footerRow[2]!='-1'){
						$usersRegisteredQuery = " SELECT COUNT( * ) FROM `form_regdata` WHERE `page_modulecomponentid` ='".$moduleCompId."'";
						$usersRegisteredResult = mysqli_fetch_array(mysqli_query($GLOBALS["___mysqli_ston"], $usersRegisteredQuery));
						if($usersRegisteredResult[0]==$footerRow[3])
							displayerror('Form registration limit has been reached.');
						else 
						if($usersRegisteredResult[0]>$footerRow[2]){
							$waitlist = 'You registration has been waitlisted. We will contact you through mail if we could accommodate you';	
							displayinfo("Thanks for registering. $waitlist");	
						}
					}
				}
				// send mail code starts here - see common.lib.php for more
				if ($footerRow[1]=='1') {
				  $from = "from: Pragyan Events Team <no-reply@pragyan.org>"; // Default CMS email will be added automatically if this is left blank
					$to = getUserEmail($userId);
					$pageId = getPageIdFromModuleComponentId('form',$moduleCompId);
					$parentPage = getParentPage($pageId);
					$formname = getPageTitle($parentPage);
					$keyid = $finalName = str_pad($userId, 5,'0', STR_PAD_LEFT);
					$key = '';
					$mailtype = "form_registration_mail";
					$messenger = new messenger(false);

					global $onlineSiteUrl;				
					$pos = strrpos($_SERVER['REQUEST_URI'],'/');
					$webAdd = substr('http://www.pragyan.org'.$_SERVER['REQUEST_URI'],0,22+$pos);;
					$pos = strrpos($webAdd,'/');
					$webAdd = substr('http://www.pragyan.org'.$_SERVER['REQUEST_URI'],0,$pos);
				$messenger->assign_vars(array('FORMNAME'=>"$formname",'KEY'=>"$key",'WEBSITE'=>CMS_TITLE,'DOMAIN'=>$onlineSiteUrl,	'NAME'=>getUserFullName($userId),'PRID'=>$userId,'EVENTWEB' => $webAdd, 'INFO' =>$waitlisted));
					if ($messenger->mailer($to,$mailtype,$key,$from))
							displayinfo("You have been succesfully registered to $formname and a registration confirmation mail has been sent. Kindly check your e-mail.");
						else 
							displayerror("Registration confirmation mail sending failure. Kindly contact webadmin@pragyan.org");
					
				}

				// send mail code ends here
			}
		}
		return true;
	}
	/** Checks if user entered correct captcha generated from getCaptchaHtml()
	 */
	function submitCaptcha(){
		if($_POST['captcha'])
			{
			global $sourceFolder, $moduleFolder, $cmsFolder;
			require_once("$sourceFolder/$moduleFolder/form/captcha/recaptcha/recaptchalib.php");
			$query = "SELECT `value` FROM `". MYSQL_DATABASE_PREFIX ."global` WHERE `attribute`='recaptcha_private'";
			$res = mysqli_fetch_assoc(mysqli_query($GLOBALS["___mysqli_ston"], $query));
			$private_key = $res['value'];
			if ($_POST["recaptcha_response_field"]) {
       					$resp = recaptcha_check_answer ($private_key,
                                        $_SERVER["REMOTE_ADDR"],
                                        $_POST["recaptcha_challenge_field"],
                                        $_POST["recaptcha_response_field"]);

       			 if ($resp->is_valid) 
		                return true;
				}
			}
			else
			{
		  if(isset($_SESSION['CAPTCHAString']) && isset($_POST['txtCaptcha']))
			if(strtolower($_SESSION['CAPTCHAString']) == strtolower($_POST['txtCaptcha']))
				return true;
			}
		displayerror('The text did not match the letters in the image. Please try again.');
		return false;
	}

	function submitRegistrationFormText($moduleCompId, $elementId, $userId, $postVarName, $elementName, $elementSize, $elementTypeOptions, $elementMoreThan, $elementLessThan, $elementCheckInt, $elementIsRequired) {
		if($elementIsRequired && ( !isset($_POST[$postVarName]) || $_POST[$postVarName] == NULL || trim($_POST[$postVarName]) == "")) {
			displayerror("Essential field $elementName is missing");
			return false;
		}

		$submitData = escape(trim($_POST[$postVarName]));
		$textQuery = "SELECT 1 FROM `form_elementdata` " .
						"WHERE `user_id` ='$userId' AND `page_modulecomponentid` ='$moduleCompId' AND `form_elementid` ='$elementId'";
		$textResult = mysqli_query($GLOBALS["___mysqli_ston"], $textQuery);
		if (!$textResult) {	displayerror('E46 : Invalid query: ' . ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false))); 	return false; 	}

		$query="SELECT * FROM `form_elementdesc` WHERE `page_modulecomponentid`='$moduleCompId' AND `form_elementid` ='$elementId'";
		$result=mysqli_query($GLOBALS["___mysqli_ston"], $query);
		$fetch=mysqli_fetch_assoc($result);
		if($elementSize>0)
		{
			if(strlen($submitData) > $elementSize) {
				displayerror("$elementName is more than element size");
				return false;
			}
		}
		if($submitData==""&&!$elementIsRequired);
		else if($elementMoreThan!=0)
		{
			if($elementMoreThan > $submitData) {
				displayerror("$elementName is less than element minimum value");
				return false;
			}
		}
		if($submitData==""&&!$elementIsRequired);
		else if($elementLessThan!=0)
		{
			if($elementLessThan < $submitData) {
				displayerror("$elementName is more than element maximum value");
				return false;
			}
		}
		if($submitData==""&&!$elementIsRequired);
		else if($elementCheckInt)
		{
			if(!is_numeric($submitData)) {
				if($submitData != '') {
					displayerror("$elementName is not of type int");
					return false;
				}
			}
		}
		if(mysqli_num_rows($textResult)>0) {
			$textUpdateQuery = "UPDATE `form_elementdata` SET `form_elementdata` = '".$submitData."' ".
								"WHERE `user_id` = '$userId' AND `page_modulecomponentid` = '$moduleCompId' AND `form_elementid` = $elementId";
			$textUpdateResult = mysqli_query($GLOBALS["___mysqli_ston"], $textUpdateQuery);
			if (!$textUpdateResult) {	displayerror('E67 : Invalid query: ' . ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false))); 	return false; 	}
		} else {
			$textInsertQuery = "INSERT INTO `form_elementdata` ( `user_id` , `page_modulecomponentid` , `form_elementid` , `form_elementdata` ) ".
								"VALUES ( '$userId', '$moduleCompId', '$elementId', '". $submitData ."')";
			$textInsertResult = mysqli_query($GLOBALS["___mysqli_ston"], $textInsertQuery);
			if (!$textInsertResult) {	displayerror('E13 : Invalid query: ' . ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false))); 	return false; 	}
		}
		return true;
	}

	function submitRegistrationFormTextarea($moduleCompId, $elementId, $userId, $postVarName, $elementName, $elementSize, $elementTypeOptions, $elementMoreThan, $elementLessThan, $elementCheckInt, $elementIsRequired) {
		if($elementIsRequired && ( !isset($_POST[$postVarName]) || $_POST[$postVarName] == NULL || trim($_POST[$postVarName] == "" ))) {
			displayerror("Essential field $elementName is missing");
			return false;
		}
		elseif(!isset($_POST[$postVarName]) && !$elementIsRequired) {
			return true;
		}
		$submitData = escape(trim($_POST[$postVarName]));

		$textQuery = "SELECT 1 FROM `form_elementdata` " .
						"WHERE `user_id` ='$userId' AND `page_modulecomponentid` ='$moduleCompId' AND `form_elementid` ='$elementId'";
		$textResult = mysqli_query($GLOBALS["___mysqli_ston"], $textQuery);
		if (!$textResult) {	displayerror('E34 : Invalid query: ' . ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false))); 	return false; 	}

		if(mysqli_num_rows($textResult)>0) {
			$textUpdateQuery = "UPDATE `form_elementdata` SET `form_elementdata` = '$submitData' ".
								"WHERE `user_id` = '$userId' AND `page_modulecomponentid` = '$moduleCompId' AND `form_elementid` = $elementId";
			$textUpdateResult = mysqli_query($GLOBALS["___mysqli_ston"], $textUpdateQuery);
			if (!$textUpdateResult) {	displayerror('E12 : Invalid query: ' . ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false))); 	return false; 	}
		} else {
			$textInsertQuery = "INSERT INTO `form_elementdata` ( `user_id` , `page_modulecomponentid` , `form_elementid` , `form_elementdata` ) ".
								"VALUES ( '$userId', '$moduleCompId', '$elementId', '$submitData')";
			$textInsertResult = mysqli_query($GLOBALS["___mysqli_ston"], $textInsertQuery);
			if (!$textInsertResult) {	displayerror('E89 : Invalid query: ' . ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false))); 	return false; 	}
		}
		return true;

	}

	function submitRegistrationFormRadio($moduleCompId, $elementId, $userId, $postVarName, $elementName, $elementSize, $elementTypeOptions, $elementMoreThan, $elementLessThan, $elementCheckInt, $elementIsRequired) {
		if($elementIsRequired && ( !isset($_POST[$postVarName]) || $_POST[$postVarName] == NULL || $_POST[$postVarName] == "")) {
			displayerror("Essential field ".$elementName." is missing");
			return false;
		}

		$textQuery = "SELECT 1 FROM `form_elementdata` " .
						"WHERE `user_id` ='$userId' AND `page_modulecomponentid` ='$moduleCompId' AND `form_elementid` ='$elementId'";
		$textResult = mysqli_query($GLOBALS["___mysqli_ston"], $textQuery);
		if (!$textResult) {	displayerror('E73 : Invalid query: ' . ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false))); 	return false; 	}

		$optionNumber = escape($_POST[$postVarName]);
		$options = explode("|",$elementTypeOptions);

		if(count($options)<($optionNumber+1)){
			displayerror("$elementName is out of bounds of the available number of options.");
			return false;
		}

		if(mysqli_num_rows($textResult)>0) {
			$textUpdateQuery = "UPDATE `form_elementdata` SET `form_elementdata` = '" . $options[$optionNumber] . "' ".
								"WHERE `user_id` = '$userId' AND `page_modulecomponentid` = '$moduleCompId' AND `form_elementid` = '$elementId'";
			$textUpdateResult = mysqli_query($GLOBALS["___mysqli_ston"], $textUpdateQuery);
			if (!$textUpdateResult) {	displayerror('E28 : Invalid query: ' . ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false))); 	return false; 	}
		} else {
			$textInsertQuery = "INSERT INTO `form_elementdata` ( `user_id` , `page_modulecomponentid` , `form_elementid` , `form_elementdata` ) ".
								"VALUES ( '$userId', '$moduleCompId', '$elementId', '" . $options[$optionNumber] . "')";
			$textInsertResult = mysqli_query($GLOBALS["___mysqli_ston"], $textInsertQuery);
			if (!$textInsertResult) {	displayerror('E90 : Invalid query: ' . ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false))); 	return false; 	}
		}
		return true;


	}

	function submitRegistrationFormCheckbox($moduleCompId, $elementId, $userId, $postVarName, $elementName, $elementSize, $elementTypeOptions, $elementMoreThan, $elementLessThan, $elementCheckInt, $elementIsRequired) {

		$options = explode("|",$elementTypeOptions);
		$i=-1;
		$values = array();
		foreach($options as $value) {
			$i++;
			if(!isset($_POST[$postVarName."_".$i]))
				continue;

			$values[] = $value;
		}

		$valuesString = join($values,"|");

		if($elementIsRequired &&  $valuesString == "") {
			displayerror("Essential field ".$elementName." is missing");
			return false;
		}

		$textQuery = "SELECT 1 FROM `form_elementdata` " .
							"WHERE `user_id` ='$userId' AND `page_modulecomponentid` ='$moduleCompId' AND `form_elementid` ='$elementId'";
		$textResult = mysqli_query($GLOBALS["___mysqli_ston"], $textQuery);
		if (!$textResult) {	displayerror('E91 : Invalid query: '.$textQuery . ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false))); 	return false; 	}


		if(mysqli_num_rows($textResult)>0) {
			$textUpdateQuery = "UPDATE `form_elementdata` SET `form_elementdata` = '$valuesString' ".
								"WHERE `user_id` = '$userId' AND `page_modulecomponentid` = '$moduleCompId' AND `form_elementid` = '$elementId'";
			$textUpdateResult = mysqli_query($GLOBALS["___mysqli_ston"], $textUpdateQuery);
			if (!$textUpdateResult) {	displayerror('E78 : Invalid query: ' . ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false))); 	return false; 	}
		} else {
			$textInsertQuery = "INSERT INTO `form_elementdata` ( `user_id` , `page_modulecomponentid` , `form_elementid` , `form_elementdata` ) ".
								"VALUES ( '$userId', '$moduleCompId', '$elementId', '$valuesString')";
			$textInsertResult = mysqli_query($GLOBALS["___mysqli_ston"], $textInsertQuery);
			if (!$textInsertResult) {	displayerror('E55 : Invalid query: ' . ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false))); 	return false; 	}
		}

		return true;

	}

	function submitRegistrationFormSelect($moduleCompId, $elementId, $userId, $postVarName, $elementName, $elementSize, $elementTypeOptions, $elementMoreThan, $elementLessThan, $elementCheckInt, $elementIsRequired) {
		if($elementIsRequired && ( !is_numeric($_POST[$postVarName]) || !isset($_POST[$postVarName]) || $_POST[$postVarName] == "" || $_POST[$postVarName] == NULL )) {
			displayerror("Essential field ".$elementName." is missing");
			return false;
		}
		$textQuery = "SELECT 1 FROM `form_elementdata` " .
						"WHERE `user_id` ='$userId' AND `page_modulecomponentid` ='$moduleCompId' AND `form_elementid` ='$elementId'";
		$textResult = mysqli_query($GLOBALS["___mysqli_ston"], $textQuery);
		if (!$textResult) {	displayerror('E64 : Invalid query: ' . ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false))); 	return false; 	}
		$optionNumber = escape($_POST[$postVarName]);
		$options = explode("|",escape($elementTypeOptions));

		if(count($options)<($optionNumber+1)){
			displayerror("$elementName is out of bounds of the available number of options.");
			return false;
		}

		if(mysqli_num_rows($textResult)>0) {
			$textUpdateQuery = "UPDATE `form_elementdata` SET `form_elementdata` = '" . $options[$optionNumber] ."' ".
								"WHERE `user_id` = '$userId' AND `page_modulecomponentid` = '$moduleCompId' AND `form_elementid` = '$elementId'";
			$textUpdateResult = mysqli_query($GLOBALS["___mysqli_ston"], $textUpdateQuery);
			if (!$textUpdateResult) {	displayerror('E102 : Invalid query: ' . ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false))); 	return false; 	}
		} else {
			$textInsertQuery = "INSERT INTO `form_elementdata` ( `user_id` , `page_modulecomponentid` , `form_elementid` , `form_elementdata` ) ".
								"VALUES ( '$userId', '$moduleCompId', '$elementId', '" . $options[$optionNumber] . "')";
			$textInsertResult = mysqli_query($GLOBALS["___mysqli_ston"], $textInsertQuery);
			if (!$textInsertResult) {	displayerror('E121 : Invalid query: ' . ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false))); 	return false; 	}
		}
		return true;

	}

	function submitRegistrationFormPassword($moduleCompId, $elementId, $userId, $postVarName, $elementName, $elementSize, $elementTypeOptions, $elementMoreThan, $elementLessThan, $elementCheckInt, $elementIsRequired) {
		if($elementIsRequired && ( !isset($_POST[$postVarName]) || $_POST[$postVarName] == "" || $_POST[$postVarName] == NULL )) {
			displayerror("Essential field ".$elementName." is missing");
			return false;
		}
		$textQuery = "SELECT 1 FROM `form_elementdata` " .
						"WHERE `user_id` =$userId AND `page_modulecomponentid` ='$moduleCompId' AND `form_elementid` ='$elementId'";
		$textResult = mysqli_query($GLOBALS["___mysqli_ston"], $textQuery);
		if (!$textResult) {	displayerror('E234 : Invalid query: ' . ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false))); 	return false; 	}

		if(mysqli_num_rows($textResult)>0) {
			$textUpdateQuery = "UPDATE `form_elementdata` SET `form_elementdata` = '".escape($_POST[$postVarName])."' ".
								"WHERE `user_id` = '$userId' AND `page_modulecomponentid` = '$moduleCompId' AND `form_elementid` = $elementId";
			$textUpdateResult = mysqli_query($GLOBALS["___mysqli_ston"], $textUpdateQuery);
			if (!$textUpdateResult) {	displayerror('E39 : Invalid query: ' . ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false))); 	return false; 	}
		} else {
			$textInsertQuery = "INSERT INTO `form_elementdata` ( `user_id` , `page_modulecomponentid` , `form_elementid` , `form_elementdata` ) ".
								"VALUES ( '$userId', '$moduleCompId', '$elementId', '" . escape($_POST[$postVarName]) . "')";
			$textInsertResult = mysqli_query($GLOBALS["___mysqli_ston"], $textInsertQuery);
			if (!$textInsertResult) {	displayerror('E42 : Invalid query: ' . ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false))); 	return false; 	}
		}
		return true;
	}

	function submitRegistrationFormFile($moduleCompId, $elementId, $userId, $postVarName, $elementName, $elementSize, $elementTypeOptions, $elementMoreThan, $elementLessThan, $elementCheckInt, $elementIsRequired) {
		if($elementIsRequired && !isset($_FILES[$postVarName])) {
			displayerror('Required file ' . $elementName . ' not uploaded.');
			return false;
		}

		$existsQuery = "SELECT `form_elementdata` from `form_elementdata` WHERE `user_id` = $userId AND " .
					"`page_modulecomponentid` = '$moduleCompId' AND `form_elementid` = '$elementId'";
		$existsResult = mysqli_query($GLOBALS["___mysqli_ston"], $existsQuery);

		global $sourceFolder;
		require_once("$sourceFolder/upload.lib.php");
		/// if the user is uploading a file with any name again, delete existing file
		if($_FILES[$postVarName]['error'][0] != UPLOAD_ERR_NO_FILE) {
			if(mysqli_num_rows($existsResult)>0) {
				$existsRow = mysqli_fetch_array($existsResult);
				if(deleteFile( $moduleCompId,'form', $existsRow[0])) {
					$deleteQuery = "DELETE FROM `form_elementdata` WHERE `form_elementid` = '$elementId' AND `page_modulecomponentid` = '$moduleCompId' AND `user_id`=$userId";
					mysqli_query($GLOBALS["___mysqli_ston"], $deleteQuery);
				}
			}
		}
		$maxFileSizeInBytes = $elementLessThan;
		if($maxFileSizeInBytes == NULL || $maxFileSizeInBytes == "" || $maxFileSizeInBytes == 0) $maxFileSizeInBytes = 2*1024*1024;
		if(trim($elementTypeOptions)=="") $uploadableFileTypes = false;
		else {
			$uploadableFileTypes = explode( "|" , $elementTypeOptions );
			if(count($uploadableFileTypes)==0) $uploadableFileTypes = false;
		}
		$uploadFileName = submitFileUploadForm($moduleCompId, "form", $userId, $maxFileSizeInBytes , $uploadableFileTypes, $postVarName);
		if(!isset($uploadFileName[0])) {
			return !$elementIsRequired;
		}
		$uploadFileName = $uploadFileName[0];

		$submitQuery = 'INSERT INTO `form_elementdata`(`user_id`, `page_modulecomponentid`, `form_elementid`, `form_elementdata`) ' .
									"VALUES('$userId', '$moduleCompId', '$elementId', '$uploadFileName')";
		if(!mysqli_query($GLOBALS["___mysqli_ston"], $submitQuery) || mysqli_affected_rows($GLOBALS["___mysqli_ston"]) != 1) {
			displayerror('Error updating information in the database.'.((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
			return false;
		}
		return true;
	}


	function submitRegistrationFormMember($moduleCompId, $elementId, $userId, $postVarName, $elementName, $elementSize, $elementTypeOptions, $elementMoreThan, $elementLessThan, $elementCheckInt, $elementIsRequired) {
		if($elementIsRequired && ( !isset($_POST[$postVarName]) || $_POST[$postVarName] == NULL || trim($_POST[$postVarName]) == "")) {
			displayerror("Essential field $elementName is missing");
			return false;
		}
		
		$submitData = escape(trim($_POST[$postVarName]));
		
		if($submitData != "") {
		  if(!is_numeric($submitData)) {
		    if(!filter_var( $submitData, FILTER_VALIDATE_EMAIL )) {
		      displayerror("Not a Valid Email Or Pragyan Id");
		      return false;
		    }
		    $query = 'SELECT `user_id` FROM `'.MYSQL_DATABASE_PREFIX."users` WHERE `user_email` = '".$submitData."'";
		    $result = mysqli_query($GLOBALS["___mysqli_ston"], $query);
		    $row = mysqli_fetch_row($result);
		    if(!mysqli_num_rows($result)) {
		      displayerror("The User with this Email Id is not registered in pragyan site.");
		      return false;
		    }
		    $submitData = $row[0];
		  }
		  $query = 'SELECT * FROM `'.MYSQL_DATABASE_PREFIX."users` WHERE `user_id` = '".$submitData."'";
		  $result = mysqli_query($GLOBALS["___mysqli_ston"], $query);
		  if(!mysqli_num_rows($result)) {
		    displayerror("Not a Valid Pragyan Id.");
		    return false;
		  }
		    
		  $query="SELECT * FROM `form_elementdata` WHERE `page_modulecomponentid`='$moduleCompId' AND `form_elementdata` ='{$submitData}'";
		  $result=mysqli_query($GLOBALS["___mysqli_ston"], $query);
		  if(mysqli_num_rows($result)>1) {
		    displayerror("User ".$submitData." has already enrolled for this event");
		    return false;
		  }    
		  
		}
		$textQuery = "SELECT 1 FROM `form_elementdata` " .
						"WHERE `user_id` ='$userId' AND `page_modulecomponentid` ='$moduleCompId' AND `form_elementid` ='$elementId'";
		$textResult = mysqli_query($GLOBALS["___mysqli_ston"], $textQuery);
		if (!$textResult) {	displayerror('E46 : Invalid query: ' . ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false))); 	return false; 	}
		
		
		$query="SELECT * FROM `form_elementdesc` WHERE `page_modulecomponentid`='$moduleCompId' AND `form_elementid` ='$elementId'";
		$result=mysqli_query($GLOBALS["___mysqli_ston"], $query);
		$fetch=mysqli_fetch_assoc($textResult);
		
		if(mysqli_num_rows($textResult)>0) {
			$textUpdateQuery = "UPDATE `form_elementdata` SET `form_elementdata` = '".$submitData."' ".
								"WHERE `user_id` = '$userId' AND `page_modulecomponentid` = '$moduleCompId' AND `form_elementid` = $elementId";
			$textUpdateResult = mysqli_query($GLOBALS["___mysqli_ston"], $textUpdateQuery);
			if (!$textUpdateResult) {	displayerror('E67 : Invalid query: ' . ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false))); 	return false; 	}
		} else {
		  
			$textInsertQuery = "INSERT INTO `form_elementdata` ( `user_id` , `page_modulecomponentid` , `form_elementid` , `form_elementdata` ) ".
								"VALUES ( '$userId', '$moduleCompId', '$elementId', '". $submitData ."')";
		$textInsertResult = mysqli_query($GLOBALS["___mysqli_ston"], $textInsertQuery);
			if (!$textInsertResult) {	displayerror('E13 : Invalid query: ' . ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false))); 	return false; 	}
		}
		
	
		  return true;
	}

	function submitRegistrationFormDate($moduleCompId, $elementId, $userId, $postVarName, $elementName, $elementSize, $elementTypeOptions, $elementMoreThan, $elementLessThan, $elementCheckInt, $elementIsRequired) {
		if($elementIsRequired && ( !isset($_POST[$postVarName]) || $_POST[$postVarName] == "" || $_POST[$postVarName] == NULL )) {
			displayerror("Essential field ".$elementName." is missing");
			return false;
		}
		if(!$elementIsRequired && $_POST[$postVarName]=="") return true;
		if(!verifyDate(escape($_POST[$postVarName]))) return false;
		$textQuery = "SELECT 1 FROM `form_elementdata` " .
							"WHERE `user_id` ='$userId' AND `page_modulecomponentid` ='$moduleCompId' AND `form_elementid` ='$elementId'";
		$textResult = mysqli_query($GLOBALS["___mysqli_ston"], $textQuery);
		if (!$textResult) {	displayerror('E134 : Invalid query: ' . ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false))); 	return false; 	}

		if(mysqli_num_rows($textResult)>0) {
			$textUpdateQuery = "UPDATE `form_elementdata` SET `form_elementdata` = '".escape($_POST[$postVarName])."' ".
									"WHERE `user_id` = '$userId' AND `page_modulecomponentid` = '$moduleCompId' AND `form_elementid` = '$elementId'";
			$textUpdateResult = mysqli_query($GLOBALS["___mysqli_ston"], $textUpdateQuery);
			if (!$textUpdateResult) {	displayerror('E12 : Invalid query: ' . ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false))); 	return false; 	}
		} else {
				$textInsertQuery = "INSERT INTO `form_elementdata` ( `user_id` , `page_modulecomponentid` , `form_elementid` , `form_elementdata` ) ".
									"VALUES ( '$userId', '$moduleCompId', '$elementId', '" . escape($_POST[$postVarName]) . "')";
				$textInsertResult = mysqli_query($GLOBALS["___mysqli_ston"], $textInsertQuery);
				if (!$textInsertResult) {	displayerror('E89 : Invalid query: ' . ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false))); 	return false; 	}
		}
			return true;

	}

	function submitRegistrationFormDatetime($moduleCompId, $elementId, $userId, $postVarName, $elementName, $elementSize, $elementTypeOptions, $elementMoreThan, $elementLessThan, $elementCheckInt, $elementIsRequired) {
		if($elementIsRequired && ( !isset($_POST[$postVarName]) || $_POST[$postVarName] == "" || $_POST[$postVarName] == NULL )) {
			displayerror("Essential field ".$elementName." is missing");
			return false;
		}
		if(!$elementIsRequired && $_POST[$postVarName]=="") return true;
		$strdatetime=escape($_POST[$postVarName]);
		$pos=strpos($strdatetime," ");
		$date=substr($strdatetime,0,($pos));
		$time=substr($strdatetime,$pos+1,strlen($strdatetime));
		if(!verifyDate($date))
				return false;
		if(!verifyTime($time))
				return false;
		$textQuery = "SELECT 1 FROM `form_elementdata` " .
							"WHERE `user_id` ='$userId' AND `page_modulecomponentid` ='$moduleCompId' AND `form_elementid` ='$elementId'";
		$textResult = mysqli_query($GLOBALS["___mysqli_ston"], $textQuery);
		if (!$textResult) {	displayerror('E234 : Invalid query: ' . ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false))); 	return false; 	}

		if(mysqli_num_rows($textResult)>0) {
			$textUpdateQuery = "UPDATE `form_elementdata` SET `form_elementdata` = '".escape($_POST[$postVarName])."' ".
									"WHERE `user_id` = '$userId' AND `page_modulecomponentid` = '$moduleCompId' AND `form_elementid` = '$elementId'";
			$textUpdateResult = mysqli_query($GLOBALS["___mysqli_ston"], $textUpdateQuery);
			if (!$textUpdateResult) {	displayerror('E12 : Invalid query: ' . ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false))); 	return false; 	}
		} else {
			$textInsertQuery = "INSERT INTO `form_elementdata` ( `user_id` , `page_modulecomponentid` , `form_elementid` , `form_elementdata` ) ".
								"VALUES ( '$userId', '$moduleCompId', '$elementId', '" . escape($_POST[$postVarName]) . "')";
			$textInsertResult = mysqli_query($GLOBALS["___mysqli_ston"], $textInsertQuery);
			if (!$textInsertResult) {	displayerror('E89 : Invalid query: ' . ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false))); 	return false; 	}
		}
			return true;
	}

	function verifyDate($inputDate){
		$datePattern = '/^(?P<year>19[5-9][0-9]|20[0-4][0-9]|2050)-(?P<month>0[1-9]|1[0-2])-(?P<date>0[1-9]|[12][0-9]|3[01])$/';
		$matches = array();
		$isMatch = preg_match($datePattern, $inputDate, $matches);
		if(!$isMatch) {
			displayerror("Enter the date in 'YYYY-MM-DD' format");
			return false;
		}

		$year = $matches['year'];
		$month = $matches['month'];
		$date = $matches['date'];

		if($year < 1950 || $year > 2050) {
			displayerror($year . ' Enter a valid year.');
			return false;
		}
		if($month > 12) {
			displayerror('Enter a valid month.');
			return false;
		}
		if($date > 31) {
			displayerror('Enter a valid date.');
			return false;
		}
		switch($month) {
			case 2:
				if(($year % 4 && $date > 28) || (!($year % 4) && $date > 29)) {
					displayerror('Enter a valid date for February.');
					return false;
				}
			break;

			case 4:
			case 6:
			case 9:
			case 11:
				if($date > 30) {
					displayerror('Enter a valid date for the specified month.');
					return false;
				}
		}

		return true;
	}

	function verifyTime($inputTime){
		$timePattern = '/^([01][0-9]|2[0-3]):[0-5][0-9]$/';
		if(!preg_match($timePattern, $inputTime)) {
			displayerror("Enter the time in 'HH:MM' format in 24 hours clock");
			return false;
		}
		return true;
	}

	function insertFormView($moduleComponentId, $userId) {
		$existsQuery = "SELECT COUNT(*) FROM `form_visits` WHERE `page_modulecomponentid` = '$moduleComponentId' AND `user_id` = '$userId'";
		$existsResult = mysqli_query($GLOBALS["___mysqli_ston"], $existsQuery);
		$existsRow = mysqli_fetch_row($existsResult);

		if ($existsRow[0] == 0) {
			$insertQuery = "INSERT INTO `form_visits`(`page_modulecomponentid`, `user_id`, `user_submitcount`, `user_firstvisit`) VALUES " .
					"('$moduleComponentId', '$userId', 0, NOW())";
			mysqli_query($GLOBALS["___mysqli_ston"], $insertQuery);
		}
	}

	function updateFormSubmitCount($moduleComponentId, $userId) {
		$existsQuery = "SELECT COUNT(*) FROM `form_visits` WHERE `page_modulecomponentid` = '$moduleComponentId' AND `user_id` = '$userId'";
		$existsResult = mysqli_query($GLOBALS["___mysqli_ston"], $existsQuery);
		$existsRow = mysqli_fetch_row($existsResult);

		if ($existsRow[0] == 1)
			$updateQuery = "UPDATE `form_visits` SET `user_submitcount` = `user_submitcount` + 1 WHERE `page_modulecomponentid` = $moduleComponentId AND `user_id` = $userId";
		else
			$updateQuery = "INSERT INTO `form_visits`(`page_modulecomponentid`, `user_id`, `user_submitcount`, `user_firstvisit`) VALUES " .
					"('$moduleComponentId', '$userId', 1, NOW())";
		mysqli_query($GLOBALS["___mysqli_ston"], $updateQuery);
	}

	/** Register a user in form_regdata table*/
	function registerUser($moduleCompId,$userId) {
		$registeruser_query = "INSERT INTO `form_regdata` (`user_id` ,`page_modulecomponentid` ,`form_firstupdated` ,`form_lastupdated`) " .
				"VALUES ('$userId', '$moduleCompId', CURRENT_TIMESTAMP , CURRENT_TIMESTAMP)";
		$registeruser_result = mysqli_query($GLOBALS["___mysqli_ston"], $registeruser_query);
		if(mysqli_affected_rows($GLOBALS["___mysqli_ston"])>0){


			global $sourceFolder;
			require_once($sourceFolder."/group.lib.php");
			$groupId = getGroupIdFromFormId($moduleCompId);
			if($groupId!=false) {
				if(addUserToGroupId($groupId, $userId))
					return true;
				else {
					displayerror("Error in registering user to group.");
					return false;
				}
			}
			return true;
		}
		else {
			displayerror("Error in registering user to form.");
			return false;
		}
	}
	/** Update the lastupdated date */
	function updateUser($moduleCompId,$userId) {
		

		$updateuser_query = "UPDATE `form_regdata` SET `form_lastupdated` = CURRENT_TIMESTAMP WHERE `user_id` ='$userId' AND `page_modulecomponentid` ='$moduleCompId'";
		$updateuser_result = mysqli_query($GLOBALS["___mysqli_ston"], $updateuser_query);
		if(mysqli_affected_rows($GLOBALS["___mysqli_ston"])>0)
			return true;
		else
			return false;
	}
	/** Return true if user registered, otherwise false*/
	function verifyUserRegistered($moduleCompId,$userId) {
		if($userId == 0)	return false;
		$verifyuser_query = " SELECT 1 FROM `form_regdata` WHERE `user_id` ='$userId' AND `page_modulecomponentid` = '$moduleCompId'";
		$verifyuser_result = mysqli_query($GLOBALS["___mysqli_ston"], $verifyuser_query);
		if (!$verifyuser_result) {
			displayerror('E39 : Invalid query: '.$verifyuser_query . ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
			return false;
		}
		/** NOT SURE IF THIS WILL WORK. it was if(mysql_affected_rows()>0))*/
		if(mysqli_num_rows($verifyuser_result)>0)
			return true;
		else
			return false;
	}
	/**Checks if the user filled his profile fully. The form id is 0.*/
	function verifyUserProfileFilled($userId) {
		$verifyprofile_query = 'SELECT s.form_elementname ' .
				'FROM `form_elementdesc` s LEFT JOIN `form_elementdata` d ' .
				'	ON s.form_elementid = d.form_elementid AND s.page_modulecomponentid = d.page_modulecomponentid AND d.user_id=\''.$userId.'\' ' .
				'   WHERE s.form_elementisrequired = 1 AND s.page_modulecomponentid = 0 ' .
				'	AND (d.form_elementdata IS NULL OR d.form_elementdata = "")';
		$verifyprofile_result = mysqli_query($GLOBALS["___mysqli_ston"], $verifyprofile_query);
		if(!$verifyprofile_result)
			return false;
		if(mysqli_num_rows($verifyprofile_result)>0)
			return false;
		else
			return true;
	}
	/** Unegister a user in form_regdata table and remove his data from elementdata table*/
	function unregisterUser($moduleCompId, $userId, $silentOnSuccess = false) {
		if(verifyUserRegistered($moduleCompId,$userId)){
			$unregisteruser_query = "DELETE FROM `form_regdata` WHERE `user_id` = '$userId' AND `page_modulecomponentid` = '$moduleCompId'";
			$unregisteruser_result = mysqli_query($GLOBALS["___mysqli_ston"], $unregisteruser_query);

			/// Remove any files uploaded by the user
			$fileFieldQuery = 'SELECT `form_elementdata` FROM `form_elementdata`, `form_elementdesc` WHERE ' .
						"`form_elementdata`.`page_modulecomponentid` = '$moduleCompId' AND `form_elementtype` = 'file' AND " .
						"`form_elementdata`.`user_id` = '$userId' AND `form_elementdesc`.`page_modulecomponentid` = `form_elementdata`.`page_modulecomponentid` AND " .
						"`form_elementdata`.`form_elementid` = `form_elementdesc`.`form_elementid`";
			$fileFieldResult = mysqli_query($GLOBALS["___mysqli_ston"], $fileFieldQuery);

			global $sourceFolder;
			require_once("$sourceFolder/upload.lib.php");
			while($fileFieldRow = mysqli_fetch_row($fileFieldResult)) {
				deleteFile($moduleCompId, 'form', $fileFieldRow[0]);
			}

			$deleteelementdata_query = "DELETE FROM `form_elementdata` WHERE `user_id` = '$userId' AND `page_modulecomponentid` = '$moduleCompId' ";
			$deleteelementdata_result = mysqli_query($GLOBALS["___mysqli_ston"], $deleteelementdata_query);

			if($deleteelementdata_result) {
				global $sourceFolder;
				require_once($sourceFolder."/group.lib.php");
				$groupId = getGroupIdFromFormId($moduleCompId);
				if($groupId!=false) {
					if(removeUserFromGroupId($groupId, $userId)) {
						if(!$silentOnSuccess)
							displayinfo("User successfully unregistered");
						return true;
					}
					else {
						displayerror("Unable to unregister user from group.");
						return false;
					}
				}
				else {
					if(!$silentOnSuccess)
						displayinfo("User successfully unregistered");
					return true;
				}
			}
			else {
				displayerror("Error in unregistering user.");
				return false;
			}
		}
		else {
			displaywarning("User not registered!");
			return false;
		}
	}

?>
