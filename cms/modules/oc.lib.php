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
 * @author shriram<vshriram93@gmail.com>
 * @author Abhishek Kaushik
 * @copyright (c) 2008 Pragyan Team
 * @license http://www.gnu.org/licenses/ GNU Public License
 * For more details, see README
 */
class oc implements module,fileuploadable	 {
  private $userId;
  private $moduleComponentId;
  private $action;
 
public function getHtml($gotuid, $gotmoduleComponentId, $gotaction) {
    $this->userId = $gotuid;
    $this->moduleComponentId = $gotmoduleComponentId;
    $this->action = $gotaction;
    
   if ($gotaction == 'ochead')
      return $this->actionOchead();
    if ($gotaction == 'octeam')
      return $this->actionOcteam();
    if ($gotaction == 'view')
      return $this->actionView();
    else return $this->actionView();
  }

public function actionView() {
//  displaywarning("Registration Closed");
 // return "";
  global $urlRequestRoot, $moduleFolder, $cmsFolder,$templateFolder,$sourceFolder,$STARTSCRIPTS;
  require_once($sourceFolder."/".$moduleFolder."/oc/oc_common.php");
  require_once($sourceFolder."/".$moduleFolder."/oc/oc_form.php");
  $mcId= $this->moduleComponentId;
  $userId = $this->userId;
  if(!handleRegistrationFormSubmit($userId,$mcId)) return;
  return displayRegistrationForm();
}

public function actionOcteam() {
  global $urlRequestRoot, $moduleFolder, $cmsFolder,$templateFolder,$sourceFolder,$STARTSCRIPTS;
  $mcId = $this->moduleComponentId;
  require_once($sourceFolder."/".$moduleFolder."/oc/oc_common.php");
  require_once($sourceFolder."/".$moduleFolder."/oc/oc_form.php");
  if((isset($_POST['passwordChangeOption']))) {
    $userChecked=$_POST['changeUserDetail'];
    if(empty($userChecked)) {
      displayerror("InValid Selection");
    }
    else {
      if($_POST['passwordChangeOption'] == 'sollamatten') {
	unset($_SESSION['availability_S']);
	unset($_SESSION['availability_M']);
	unset($_SESSION['availability_L']);
	unset($_SESSION['availability_XL']);
	unset($_SESSION['availability_XXL']);
	unset($_SESSION['availability_food_coupon']);
	unset($_SESSION['availability_extra']);
	for($i=0;$i<count($userChecked);$i++) {
	    if($userChecked[$i] == 'S' || $userChecked[$i] == 'M' || $userChecked[$i] == 'L' ||
             $userChecked[$i] == 'XL' || $userChecked[$i] == 'XXL' ||$userChecked[$i] == 'food_coupon' || $userChecked[$i] == 'extra') {
	       $_SESSION['availability_'.$userChecked[$i]]=1;
	  }     
	}
      }
      else {
	displayerror("Wrong Password");
      }
    }
  }
  if(isset($_SESSION['availability_S'])) displayinfo("Small Available");
  if(isset($_SESSION['availability_M'])) displayinfo("Medium Available");
  if(isset($_SESSION['availability_L'])) displayinfo("Large Available");
  if(isset($_SESSION['availability_XL'])) displayinfo("XL Available");
  if(isset($_SESSION['availability_XXL'])) displayinfo("XXL Available");
  if(isset($_SESSION['availability_food_coupon'])) displayinfo("Food Coupon Available");
  if(isset($_SESSION['availability_extra'])) displayinfo("Extra Available");


  if(isset($_POST['roll'])){
    $roll=escape($_POST['roll']);
    
    //changed to use userid instead of rollno
    $fetchUserRollQuery = "SELECT * FROM `oc_form_reg` WHERE `page_moduleComponentId`={$mcId} AND 
                                      `user_id`='{$roll}'";
    $fetchUserRollResult = mysqli_query($GLOBALS["___mysqli_ston"], $fetchUserRollQuery);
    $roll=mysqli_fetch_assoc($fetchUserRollResult)['oc_roll_no'];

    checkExisting($mcId,$roll,0,$this->userId); 
    $form =<<<FORM
      <form id="submitFormLatest" action="./+octeam" onsubmit="return submitLatest(this)" method="post">
        <input type="hidden" class="rolledValue" name="roll_latest_submit" value="{$roll}" />
        <input type="submit" />
      </form>
FORM;
    echo $form;
    echo final_submit();
    exit();
  }
  if(isset($_POST['roll_latest_submit'])){
    $roll=escape($_POST['roll_latest_submit']);
    checkExisting($mcId,$roll,1,$this->userId);
    exit();    
  }
  $ocDuty=handleDistribution();
  return $ocDuty;
}
 
public function actionOchead() {
  global $urlRequestRoot, $moduleFolder, $cmsFolder,$templateFolder,$sourceFolder,$STARTSCRIPTS;
  require_once($sourceFolder."/upload.lib.php");
  require_once($sourceFolder."/".$moduleFolder."/qaos1/excel.php");
  require_once($sourceFolder."/".$moduleFolder."/oc/oc_common.php");
  $mcId=$this->moduleComponentId;
  $userId = $this->userId;
  if(isset($_POST['downloadFormatExcel'])) {
    displayOCDownload();
  }
  if(isset($_FILES['fileUploadField']['name'])) {
    $date = date_create();
    $timeStamp = date_timestamp_get($date);
    $tempVar=$sourceFolder."/uploads/temp/".$timeStamp.$_FILES['fileUploadField']['name'][0];
    move_uploaded_file($_FILES["fileUploadField"]["tmp_name"][0],$tempVar);
    $excelData = readExcelSheet($tempVar);
    $success = 1;
    for($i=2;$i<=count($excelData);$i++)  {
      $email = $excelData[$i][2].'@nitt.edu';
   	 $query="INSERT IGNORE INTO `oc_valid_emails` (`page_moduleComponentId`,`oc_name`,`oc_valid_email`) 
                                            VALUES ($mcId,'{$excelData[$i][1]}','{$email}')";
      mysqli_query($GLOBALS["___mysqli_ston"], $query) or displayerror($email);
     
    }
    //echo $c." ".$d;
  }
  $retOcHead ="";
  $uploadValidEmail=getFileUploadForm($mcId,"oc",'./+ochead',UPLOAD_SIZE_LIMIT,1);		 
  $retOcHead .=<<<FORM
    <form action="./+ochead" method="post">
      <input type="submit" name="downloadFormatExcel" value="Download Event Sample Format"/>
    </form>
FORM;
  $retOcHead.=$uploadValidEmail;
  $displayTags=<<<TAG
    <table>
      <tr>
        <td><a href="./+ochead&subaction=view_whitelist_users"> <div>View Whitelist Registrants</div></a></td>
        <td><a href="./+ochead&subaction=view_registered_users"><div>Registred Users</div></a></td>
        <td><a href="./+ochead&subaction=add_whitelist_email"><div>Add Whitelist Email</div></a></td>
        <td><a href="./+ochead&subaction=availability"><div>Check Availability</div></a></td>
        <td><a href="./+ochead&subaction=reg_status"><div>Current Registration Status</div></a></td>
        <td><a href="./+ochead&subaction=reg_status"><div>Current Registration Status</div></a></td>
        <td><a href="./+ochead&subaction=upload_tshirt_list"><div>Upload TShirt List</div></a></td>
        <td><a href="./+ochead&subaction=download_black_list"><div>Download Black List</div></a></td>
      </tr>
    </table>
TAG;
  if(isset($_GET['subaction'])&&$_GET['subaction'] == 'view_registered_users')   {
    return $retOcHead.$displayTags.view_registered_users($mcId);
  }
  if(isset($_GET['subaction'])&&$_GET['subaction']=='view_whitelist_users'){
    return $retOcHead.$displayTags.view_whitelist_emails($mcId);
  }
  if(isset($_GET['subaction'])&&$_GET['subaction']=='add_whitelist_email'){
    return $retOcHead.$displayTags.add_whitelist_email($mcId);
  }
  if(isset($_GET['subaction'])&&$_GET['subaction']=='availability'){
    return $retOcHead.$displayTags.availability($mcId);
  }
  if(isset($_GET['subaction'])&&$_GET['subaction']=='reg_status'){
    return $retOcHead.$displayTags.reg_status($mcId);
  }
  if(isset($_GET['subaction'])&&$_GET['subaction']=='upload_tshirt_list'){
    return $retOcHead.$displayTags.upload_tshirt_list($mcId);
  }
  if(isset($_GET['subaction'])&&$_GET['subaction']=='download_black_list'){
    return $retOcHead.$displayTags.download_black_list($mcId);
  }
  return $retOcHead.$displayTags.view_registered_users($mcId);   
}

public static function getFileAccessPermission($pageId,$moduleComponentId,$userId, $fileName)  {
  return getPermissions($userId, $pageId, "view");
}

public static function getUploadableFileProperties(&$fileTypesArray,&$maxFileSizeInBytes)  {
  $fileTypesArray = array('jpg','jpeg','png','doc','pdf','gif','bmp','css','js','html','xml','ods','odt','oft','pps','ppt','tex','tiff','txt','chm','mp3','mp2','wave','wav','mpg','ogg','mpeg','wmv','wma','wmf','rm','avi','gzip','gz','rar','bmp','psd','bz2','tar','zip','swf','fla','flv','eps','xcf','xls','exe','7z');
  $maxFileSizeInBytes = 30*1024*1024;
}

public function deleteModule($moduleComponentId) {
  return true;
}
public function createModule($moduleComponentId) {
  ///No initialization
}
public function copyModule($moduleComponentId, $newId) {
  return true;
}

	public function moduleAdmin(){
		return "This is the Article module administration page. Options coming up soon!!!";
	}

}

