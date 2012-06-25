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
 * @author balanivash<balanivash@gmail.com>
 * @author shriram<vshriram93@gmail.com>
 * @license http://www.gnu.org/licenses/ GNU Public License
 * For more details, see README
 */
class faculty implements module, fileuploadable {
	private $userId;
	private $moduleComponentId;
	private $action;

	public function getHtml($gotuid, $gotmoduleComponentId, $gotaction) {
	
		$this->userId = $gotuid;
		$this->moduleComponentId = $gotmoduleComponentId;
		$this->action = $gotaction;

		if ($this->action == "view")
			return $this->actionView();
		if ($this->action == "faculty")
			return $this->actionFaculty();
		if ($this->action == "edit")
			return $this->actionEdit();
		else
			return $this->actionView();
	}

	/**
	 * Funtion which tells the cms uploaded file access is defined by which action
	 */
	public static function getFileAccessPermission($pageId,$moduleComponentId,$userId, $fileName) {
	  return getPermissions($userId, $pageId, "view");
	}

	public static function getUploadableFileProperties(&$fileTypesArray,&$maxFileSizeInBytes) {
		$fileTypesArray = array('jpg','jpeg','png','gif');
		$maxFileSizeInBytes = 30*1024*1024;
	}
	public function actionEdit(){	
		global $ICONS;
		global $sourceFolder,$cmsFolder,$templateFolder,$moduleFolder,$urlRequestRoot;
		$editTemplateForm = "";
		if(isset($_POST['templateChange'])){
		  $newTemplate=escape($_POST['template']);
			$chkTemplateExistsQuery = "SELECT `template_name` FROM `faculty_template` WHERE `template_id`='$newTemplate'";
			$chkTemplateExistsResult = mysql_query($chkTemplateExistsQuery);
			if(mysql_num_rows($chkTemplateExistsResult)>0){
				$changeQuery = "Update `faculty_module` SET `templateId`=$newTemplate";
				$changeResult = mysql_query($changeQuery);
				if (mysql_affected_rows() != 1)
					displayerror("Unable to update. Try again after some time.");
				else
					displayinfo("Successfully updated template");
			}
			else
				displayerror("Selected template doesnot exit.");			
			$abc="hi";
			return $abc;	

		}
		if(isset($_POST['templateEdit'])||isset($_GET['templateEdit'])){
			if(isset($_POST['templateEdit']))
				$template=escape($_POST['template']);
			if(isset($_GET['templateEdit']))
				$template=escape($_GET['template']);
			$chkTemplateExistsQuery = "SELECT `template_name` FROM `faculty_template` WHERE `template_id`='$template'";
			$chkTemplateExistsResult = mysql_query($chkTemplateExistsQuery);
			if(mysql_num_rows($chkTemplateExistsResult)>0){
				$templateName = mysql_fetch_array($chkTemplateExistsResult);
				require_once("$sourceFolder/$moduleFolder/faculty/template_edit.php");
				$editTemplateForm = templateDesc($template,$templateName[0]);
			}
			else
				displayerror("Selected template doesnot exit.");						
		}
		// Get Selected Template for Page Start
		$selectedTemplateQuery = "SELECT `templateId` FROM `faculty_module` WHERE `page_modulecomponentid`='$this->moduleComponentId'";
		$selectedTemplateResult = mysql_query($selectedTemplateQuery) or displayerror("Error in getting Faculty Settings");
		$selectedTemplate = mysql_fetch_row($selectedTemplateResult);	
		// Get Selected Template for Page Finish

		$chkDataQuery = "SELECT * FROM `faculty_data` WHERE `faculty_sectionId` IN (SELECT `template_sectionId` FROM `faculty_template` WHERE `template_id`=$selectedTemplate[0])";
		$chkDataResult = mysql_query($chkDataQuery) or displayerror("Error in checking for data");	
		if(mysql_num_rows($chkDataResult)>0)
			displaywarning("This page contains some data. If you change the template, all the data will be lost!!!");
		// Get list of templates start
		$options="";	
		$templateQuery = "SELECT `template_id`,`template_name` FROM `faculty_template` GROUP BY `template_id`";
		$templateResult = mysql_query($templateQuery) or displayerror("Error in selecting Templates");
		if(mysql_num_rows($templateResult)>0){
		  while($templateRow=mysql_fetch_array($templateResult)){
		    if($templateRow[0]==$selectedTemplate[0])
				  $selected = 'selected="selected"';
				else 		
					$selected = '';
				$options .="<option value='$templateRow[0]' $selected > $templateRow[1]</option>";
			}	
		}
		// Get list of templates start
		$settingFormHtml =<<<PRE
		<fieldset>
		<legend>{$ICONS['Forum Settings']['small']}Faculty Settings</legend>
		<form method="post" name="faculty_settings" action="./+edit">
			<table>
				<tr>
					<td>
						Faculty Templates
					</td>
					<td>
						<select name="template" style="width:100px;">
							$options
						</select>
					</td>
				</tr>
				<tr>
					<td>
						<input type="submit" name="templateChange" value="Change Template">
					</td>
					<td>
						<input type="submit" name="templateEdit" value="Edit Template">
					</td>

				</tr>
			</table>
		</form>
		</fieldset>
PRE;
	return $settingFormHtml.$editTemplateForm;

	}




	public function actionFaculty(){	
	 
	  if((isset($_POST["updateDetail"]))&&(isset($_POST["facultyId"]))&&($_POST["updateDetail"]!="")&&($_POST["facultyId"]!="")){
	    $facultyId=intval($_POST["facultyId"]);
	    if(!(is_int($facultyId)))return;
	    $facultyDetail=addslashes($_POST["updateDetail"]);
	    $facultyId=addslashes($facultyId);
	    $updateFacultyDataQuery="UPDATE `faculty_data` SET `faculty_data`='{$facultyDetail}' WHERE `faculty_dataId`={$facultyId} AND ";
	    $upDateFacultyDataQuery.="`page_moduleComponentId`={$this->moduleComponentId}";
	    $updateFacultyDataData=mysql_query($updateFacultyDataQuery);
	  }	
	
	  if((isset($_POST["updateSectionDetail"]))&&(isset($_POST["SectionDetail"])))
	    if(($_POST["updateSectionDetail"]!="")&&($_POST["SectionDetail"]!="")){
	      $facultyId=intval($_POST["SectionDetail"]);
	      if(!(is_int($facultyId)))return;
	      $facultyDetail=addslashes($_POST["SectionDetail"]);
	      $facultyId=addslashes($facultyId);
	      $updateFacultyDataQuery="UPDATE `faculty_template` SET `template_sectionName`='{$facultyDetail}' WHERE ";
	      $updateFacultyDataQuery.="`template_sectionId`={$facultyId} AND `page_moduleComponentId`={$this->moduleComponentId}";
	      $updateFacultyDataData=mysql_query($updateFacultyDataQuery);	
	    }
	
	  if((isset($_POST["addFacultyData"]))&&($_POST["addFacultyData"]!="")&&(isset($_POST["sectionId"]))&&($_POST["sectionId"]!="")){
	    $sectionId=intval($_POST["sectionId"]);
	    if(!(is_int($sectionId)))return;
	    $addDetail=addslashes($_POST["addFacultyData"]);
	    $sectionId=addslashes($sectionId);
	    $checkMaxValReached="SELECT * FROM `faculty_template` WHERE `template_sectionId`={$sectionId}";
	    $checkMaxValReachedQuery=mysql_query($checkMaxValReached);
	    $maxSectionLimit=mysql_fetch_assoc($checkMaxValReachedQuery);
	    $maxSection="SELECT * FROM `faculty_data` WHERE `faculty_sectionId`={$sectionId} AND ";
	    $maxSection.="`page_moduleComponentId`={$this->moduleComponentId}";
	    $maxSectionQuery=mysql_query($maxSection);
	    
	    if(mysql_num_rows($maxSectionQuery)<intval($maxSectionLimit['template_sectionLimit'])){
	      $addFacultyDetail="INSERT INTO `faculty_data` (`faculty_sectionId`,`faculty_data`,`page_moduleComponentId`) VALUES ";
	      $addFacultyDetail.="({$sectionId},'{$addDetail}',{$this->moduleComponentId})";
	      $addFacultyDetailQuery=mysql_query($addFacultyDetail);	
	    }
	    else {echo "Limit Exceeded";exit;}	
	  }
		
	  if((isset($_POST["DeleteFacultyId"]))&&($_POST["DeleteFacultyId"]!="")){
	    $facultyId=intval($_POST["DeleteFacultyId"]);
	    $facultyId=addslashes($facultyId);
	    $deleteData="DELETE FROM `faculty_data` WHERE `page_moduleComponentId`={$this->moduleComponentId} AND `faculty_dataId`={$facultyId}";
	    $deleteQuery=mysql_query($deleteData);
	  }	
	
	  if((isset($_POST["facultyName"]))&&(isset($_POST["facultyEmail"]))){
	    if($_POST["facultyName"]!=""){
	      $facultyName=addslashes($_POST["facultyName"]);
	      $updateFacultyNameQuery="UPDATE `".MYSQL_DATABASE_PREFIX."pages` SET `page_title`='{$facultyName}' WHERE ";
	      $updateFacultyNameQuery.="`page_modulecomponentid`={$this->moduleComponentId} AND `page_module`='faculty'";
	      $updateFacultyNameData=mysql_query($updateFacultyNameQuery);	
	    }
	
	    if($_POST["facultyEmail"]!=""){
	      $facultyEmail=addslashes($_POST["facultyEmail"]);
	      $updateFacultyEmailQuery="UPDATE `faculty_module` SET `email`='{$facultyEmail}' WHERE ";
	      $updateFacultyEmailQuery.="`page_moduleComponentId`={$this->moduleComponentId}";
	      $updateFacultyEmailData=mysql_query($updateFacultyEmailQuery) or displayerror(mysql_error());
	    }
	  }
	
	  global $urlRequestRoot,$sourceFolder,$cmsFolder,$templateFolder,$moduleFolder,$urlRequestRoot;
	  require_once("$sourceFolder/$moduleFolder/faculty/template_edit.php");
	  require_once($sourceFolder."/upload.lib.php");
	  $facultyDetail="";
	  $getImage="SELECT * FROM `faculty_module` WHERE `page_moduleComponentId`={$this->moduleComponentId}";
	  $getImageQuery=mysql_query($getImage);
	  $isExistPh=mysql_fetch_assoc($getImageQuery);
	  $facultyDetail.=<<<IMG
		<img src="{$isExistPh['photo']}" />
IMG;
    	
	  $facultyDetail.= '<br />Upload files : <br />';
	  $facultyDetail.=getFileUploadForm($this->moduleComponentId,"faculty",'./+faculty',UPLOAD_SIZE_LIMIT,1,"facultyProfilePic").'</fieldset>';
	  if(isset($_FILES["facultyProfilePic"])){	
	    $checkImageExist="SELECT * FROM `faculty_module` WHERE `page_moduleComponentId`={$this->moduleComponentId}";
	    $checkImageExistQuery=mysql_query($checkImageExist);
	    $isExistPh=mysql_fetch_assoc($checkImageExistQuery);
	    if($isExistPh["photo"]!=NULL) {
	      if(!(deleteFile($this->moduleComponentId,'faculty', $isExistPh["photo"]))) {
		displayerror("Unable to Update");
		return false;	
	      }
	    }
	    $allowableTypes = array (
				     'jpeg',
				     'jpg',
				     'png',
				     'gif'
				     );
	    
	  $fileUpload=submitFileUploadForm($this->moduleComponentId,"faculty",$this->userId,UPLOAD_SIZE_LIMIT,$allowableTypes,'facultyProfilePic');
	  $updatePhoto="UPDATE `faculty_module` SET `photo`='{$fileUpload[0]}' WHERE `page_moduleComponentId`={$this->moduleComponentId}";
	  $updatePhotoQuery=mysql_query($updatePhoto) or displayerror(mysql_error());
	  }
	  
	  $pageName=getPageTitle(getPageIdFromModuleComponentId("faculty",$this->moduleComponentId));
	  $emailId=getEmailForFaculty($this->moduleComponentId);
	  $facultyDetail.=<<<ChangeName
	    <form action="./+faculty" method="POST">
	       <table border="1">
	         <tr>
	           <td>Faculty Name:</td><td><input type="text" name="facultyName" value="{$pageName}"/></td>
	         </tr>
	         <tr>
	           <td>Email:</td><td> <input type="text" name="facultyEmail" value="{$emailId}"/></td>
	         </tr>
	         <tr>
	           <td colspan="2"><input type="submit"/></td>
	         </tr>
               </table>
	    </form>
ChangeName;
	
	  $folder="$urlRequestRoot/$cmsFolder/$moduleFolder/faculty/main.js";
	  $facultyDetail.="<script type='text/javascript' src='{$folder}'></script>";
	  $templateId=getTemplateId($this->moduleComponentId);
	  $sectionDetail=getTemplateDataFromModuleComponentId($this->moduleComponentId);
	  
	  while($sectionDetailArray=mysql_fetch_assoc($sectionDetail)) {
	    $sectionId=$sectionDetailArray['template_sectionId'];
	    $facultyDetail.=<<<facultyName
	      <h2>{$sectionDetailArray['template_sectionName']}
facultyName;
         $facultyDetail.="</h2><hr/>";
	 $facultyDetail.=printFacultyDataWithLiFaculty($sectionId,$this->moduleComponentId,0);
	 $sectionChildNode1DetailQuery="SELECT * FROM `faculty_template` WHERE `template_id`=$templateId AND ";
	 $sectionChildNode1DetailQuery.="`template_sectionParentId`={$sectionDetailArray['template_sectionId']}";
	 $sectionChildNode1DetailResult=mysql_query($sectionChildNode1DetailQuery);
	 while($sectionChildNode1DetailArray=mysql_fetch_assoc($sectionChildNode1DetailResult)) {
	   $facultyDetail.=printFacultyDataWithLiFaculty($sectionChildNode1DetailArray['template_sectionId'],$this->moduleComponentId,1);
	   $sectionChildNode2DetailQuery="SELECT * FROM `faculty_template` WHERE `template_id`=$templateId AND ";
	   $sectionChildNode2DetailQuery.="`template_sectionParentId`={$sectionChildNode1DetailArray['template_sectionId']}";
	   $sectionChildNode2DetailResult=mysql_query($sectionChildNode2DetailQuery);
	   while($sectionChildNode2DetailArray=mysql_fetch_assoc($sectionChildNode2DetailResult)) {
	     $facultyDataChild=printFacultyDataWithLi($sectionChildNode2DetailArray['template_sectionId'],$this->moduleComponentId,1);
	     $facultyDetail.=<<<facultyName
	       <h4>{$facultyDataChild}</h4>
facultyName;
	   }
	 }
	 
	  }
	  return $facultyDetail;
	}




	public function actionView() {
	  global $sourceFolder,$cmsFolder,$templateFolder,$moduleFolder,$urlRequestRoot;
	  require_once("$sourceFolder/$moduleFolder/faculty/template_edit.php");
	  $viewDetail="";
	  $templateId=getTemplateId($this->moduleComponentId);
	  $sectionDetail=getTemplateDataFromModuleComponentId($this->moduleComponentId);
	  $title=getPageTitle(getPageIdFromModuleComponentId("faculty",$this->moduleComponentId));
	  $getImage="SELECT * FROM `faculty_module` WHERE `page_moduleComponentId`={$this->moduleComponentId}";
	  $getImageQuery=mysql_query($getImage);
	  $isExistPh=mysql_fetch_assoc($getImageQuery);
	  $viewDetail.=<<<IMG
	    <div style="text-align:center;">
	    <img src="{$isExistPh['photo']}" />
	    </div>
IMG;
	  require_once($sourceFolder."/pngRender.class.php");
	  $render = new pngrender();
	  $emailId=getEmailForFaculty($this->moduleComponentId);
	  $ret = $render->transform("[tex]".$emailId."[/tex]");
	  $viewDetail.="<h3 style='text-align:center;'>Email:{$ret}</h3>";
	  while($sectionDetailArray=mysql_fetch_assoc($sectionDetail)) {
	    $sectionId=$sectionDetailArray['template_sectionId'];
	    $printFacData=printFacultyData($sectionId,$this->moduleComponentId,0);
	    if($printFacData!="")
	      $viewDetail.=<<<facultyName
		<h2>{$sectionDetailArray['template_sectionName']}</h2><hr>
facultyName;
	    $viewDetail.="<br/><br/>";
	    $sectionChildNode1DetailQuery="SELECT * FROM `faculty_template` WHERE `template_id`=$templateId AND ";
	    $sectionChildNode1DetailQuery.="`template_sectionParentId`={$sectionDetailArray['template_sectionId']}";
	    $sectionChildNode1DetailResult=mysql_query($sectionChildNode1DetailQuery);
	    $viewDetail.=$printFacData;
	    while($sectionChildNode1DetailArray=mysql_fetch_assoc($sectionChildNode1DetailResult)) {
	      $facultyData=printFacultyData($sectionChildNode1DetailArray['template_sectionId'],$this->moduleComponentId,1);
	      $viewDetail.=<<<facultyName
		<h3>{$facultyData}</h3>
facultyName;
	      $sectionChildNode2DetailQuery="SELECT * FROM `faculty_template` WHERE `template_id`=$templateId AND ";
	      $sectionChildNode2DetailQuery.="`template_sectionParentId`={$sectionChildNode1DetailArray['template_sectionId']}";
	      $sectionChildNode2DetailResult=mysql_query($sectionChildNode2DetailQuery);
	      while($sectionChildNode2DetailArray=mysql_fetch_assoc($sectionChildNode2DetailResult)) {
		$facultyDataChild=printFacultyData($sectionChildNode2DetailArray['template_sectionId'],$this->moduleComponentId,1);
		$viewDetail.=<<<facultyName
		  <h4>{$facultyDataChild}</h4>
facultyName;
	      }
	      $viewDetail.="<br/>";
	    }					
	  }
	  return $viewDetail;
	}


	public function createModule($compId) {
	  $query = "INSERT INTO `faculty_module` (`page_modulecomponentid`,`photo`,`email`,`templateId` )VALUES ('$compId',NULL,NULL,'1')";
	  $result = mysql_query($query) or die(mysql_error() . " faculty.lib.php");
	}
	
	public function deleteModule($moduleComponentId) {
	  $deleteQuery="DELETE FROM `faculty_module` WHERE `page_moduleComponentId` = {$this->moduleComponentId}";
	  $deleteResult=mysql_query($deleteQuery);
	  $deleteQuery="DELETE FROM `faculty_data` WHERE `page_moduleComponentId` = {$this->moduleComponentId}";
	  $deleteResult=mysql_query($deleteQuery);
	  return true;
	}

	public function copyModule($moduleComponentId,$newId) {
	  return true;
	}
}
