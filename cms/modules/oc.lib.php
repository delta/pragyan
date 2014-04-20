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
class oc implements module, fileuploadable	 {
  private $userId;
  private $moduleComponentId;
  
public function getHtml($gotuid, $gotmoduleComponentId, $gotaction) {
    $this->userId = $gotuid;
    $this->moduleComponentId = $gotmoduleComponentId;
    if ($gotaction == 'ochead')
      return $this->actionOchead();
    if ($gotaction == 'octeam')
      return $this->actionOcteam();
    if ($gotaction == 'view')
      return $this->actionView();
    else return $this->actionView();
  }

public function actionView() {
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
  if(isset($_POST['roll'])){
    $roll=escape($_POST['roll']);
    checkExisting($mcId,$roll);
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
      $query="INSERT IGNORE INTO `oc_valid_emails` (`page_moduleComponentId`,`oc_name`,`oc_valid_email`) 
                                            VALUES ($mcId,'{$excelData[$i][1]}','{$excelData[$i][2]}')";
      mysql_query($query) or displayerror(mysql_error());
    }
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
}

