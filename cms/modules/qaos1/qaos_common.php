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
 * @copyright (c) 2012 Pragyan Team
 * @author shriram<vshriram93@gmail.com>
 * @license http://www.gnu.org/licenses/ GNU Public License
 * For more details, see README
 */

/**
   Fix The bug is width of exsel page
*/

function assignVars($userId) {
  $query = "SELECT * FROM `".MYSQL_DATABASE_PREFIX."users` WHERE `user_id` = '".$userId."'";
  $result = mysqli_query($GLOBALS["___mysqli_ston"], $query);
  $row = mysqli_fetch_assoc($result);
  return $arr = array ('PRAGYAN_ID'=> $row['user_id'] , 'NAME' => $row['user_name'] , 'EMAIL' => $row['user_email']);
}

function getEvtProc($evtProcId,$mcId) {
 $query = "SELECT * FROM `qaos1_evtproc` WHERE `modulecomponentid` = {$mcId} AND `evtproc_Id` = {$evtProcId}";		
 $result = mysqli_query($GLOBALS["___mysqli_ston"], $query);
 $row = mysqli_fetch_array($result);
 $columnDetail = mysqli_query($GLOBALS["___mysqli_ston"], "SHOW COLUMNS FROM `qaos1_evtproc`");	
 $ret = array();
 $cnt = 0;
 while($res = mysqli_fetch_array($columnDetail)){
  $ret[strtoupper($res[0])] = $row[$cnt++];

 } 
  return array_merge(assignVars($row[7]),$ret);	
}

function getFundReq($fundReqId,$mcId) {
 $query = "SELECT * FROM `qaos1_fundreq` WHERE `modulecomponentid` = {$mcId} AND `fundreq_Id` = {$fundReqId}";		
 $result = mysqli_query($GLOBALS["___mysqli_ston"], $query);
 $row = mysqli_fetch_array($result);
 $columnDetail = mysqli_query($GLOBALS["___mysqli_ston"], "SHOW COLUMNS FROM `qaos1_fundreq`");	
 $ret = array();
 $cnt = 0;
 while($res = mysqli_fetch_array($columnDetail)){
  $ret[strtoupper($res[0])] = $row[$cnt++];

 } 
  return array_merge(assignVars($row[8]),$ret);	
}

 function getCkBody($content="",$team){
    global $sourceFolder;
    global $cmsFolder;
    global $moduleFolder;
    global $urlRequestRoot;
    global $ICONS;
    require_once ("$sourceFolder/$moduleFolder/article/ckeditor4.4/ckeditor.php");
    $editor='ckeditor';
    $CkForm =<<<Ck
      <form action="./+{$team}" method="post">
         <a name="editor"></a>
         <input type="button" id="show_plain" value="Plain Source" onclick="$('#show_plain').hide();$('#show_ckeditor').show();CKEDITOR.instances.CKEditor1.updateElement();CKEDITOR.instances.CKEditor1.destroy();document.getElementById('editor').value='plain';">
         <input type="button" id="show_ckeditor" value="CKEditor" style="display:none" onclick="$('#show_plain').show();$('#show_ckeditor').hide();CKEDITOR.add(CKEDITOR.editor.replace(document.getElementsByName('CKEditor1')[0]));document.getElementById('editor').value='ckeditor';">
         <input type="button" value="Cancel" onclick="submitarticleformCancel(this);"><input type="submit" value="Save"><input type="button" value="Preview" onclick="submitarticleformPreview(this)"><input type="button" value="Draft" onclick="submitarticleformDraft(this);">
         To upload files and images, go to the <a href="#files">files section</a>.<br/>

Ck;
    $top ="<a href='#topquicklinks'>Top</a>";
    $oCKEditor = new CKeditor();
    $oCKEditor->basePath = "$urlRequestRoot/$cmsFolder/$moduleFolder/article/ckeditor4.4/";
    $oCKEditor->config['width'] = '100%';
    $oCKEditor->config['height'] = '300';
    $oCKEditor->returnOutput = true;
    if($editor=='ckeditor'){
      $Ckbody = $oCKEditor->editor('CKEditor1',$content);
    }
    else{
      $Ckbody = $oCKEditor->editor('ne',"");  //make a auxilary Ckeditor
      ///following destroys the the ckeditor instance as soon as it is initialized. Also hides the Plain Source button
      $Ckbody.="<script>CKEDITOR.instances.ne.on('instanceReady',function(){ CKEDITOR.instances.ne.destroy()});$('#show_plain').hide();$('#show_ckeditor').show();</script>";
      $Ckbody.= '<textarea rows="20" cols="60" style="width:100%" name="CKEditor1" style="display: inline;">'.$content.'</textarea>';
    }  			    
    $CkFooter =<<<Ck1
      <br/>
        <input type='hidden' name='editor' id='editor' value='$editor'/>
        <input type="button" value="Cancel" onclick="submitarticleformCancel(this);"><input type="submit" value="Save"><input type="button" value="Preview" onclick="submitarticleformPreview(this)"><input type="button" value="Draft" onclick="submitarticleformDraft(this);">
      </form>
      <script language="javascript">
         function submitarticleformPreview(butt) {
           butt.form.action = "./+{$team}&preview=yes#preview";
	   butt.form.submit();
         }   
         function submitarticleformCancel(butt) {
	   butt.form.action="./+view";
	   butt.form.submit();
	 }
	 function submitarticleformDraft(butt) {
	   butt.form.action="./+view&draft=yes";
	   butt.form.submit();
	 }
    </script>							
Ck1;
    return  $CkForm . $Ckbody . $CkFooter;//.$draftTable.$top.$revisionTable.$top;
  }


function displayEventFormatExcel() {
  global $sourceFolder,$moduleFolder;
  require_once($sourceFolder."/".$moduleFolder."/qaos1/excel.php");
  $table=<<<TABLE
    <table>
      <thead>
        <td width="1000px"><b>Event Name(Start Adding From row 2)</b></td>
      </thead>
    </table>



TABLE;
  displayExcelForTable($table);  
}


function getEventNameFromId($evtId,$mcid) {
  $query = "SELECT `events_name` FROM `qaos1_events` WHERE `page_modulecomponentid`=$mcid AND `events_id` = {$evtId}"; 
  $result = mysqli_query($GLOBALS["___mysqli_ston"], $query) or displayerror(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
  if(!mysqli_num_rows($result)) return 0;
  $res = mysqli_fetch_array($result);
  return $res[0];
}

function getEventIdFromName($evtName,$mcid) {
  $query = "SELECT `events_id` FROM `qaos1_events` WHERE `page_modulecomponentid`=$mcid AND `events_name` = '{$evtName}'"; 
  $result = mysqli_query($GLOBALS["___mysqli_ston"], $query) or displayerror(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
  if(!mysqli_num_rows($result)) return 0;
  $res = mysqli_fetch_array($result);
  return $res[0];
}

function _date_is_valid($str) {
  if (substr_count($str, '/') == 2) {
    list($m, $d, $y) = explode('/', $str);
    return checkdate($m, $d, sprintf('%04u', $y));
  }

  return false;
}
function addToBills($imgName,$evtId,$mcid,$userId,$cluster,$corp,$bill,$billdate,$billamt,$tin) {
  $checkImageExist = "SELECT * FROM  `qaos1_bills` WHERE `qaos1_eventid` = {$evtId} AND `page_modulecomponentid` = {$mcid} AND `qaos1_imgname` = '{$imgName}'";
  $checkImageExistResult=mysqli_query($GLOBALS["___mysqli_ston"], $checkImageExist) or displayerror(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
  if(mysqli_num_rows($checkImageExistResult)) {
    displayerror("Image Already Exist");
    return 0;
  }
    $insertNewBillQuery = "INSERT INTO `qaos1_bills` (`qaos1_eventid`,`page_modulecomponentid`,`qaos1_imgname`,`userid`,`qaos1_cluster`,`qaos1_corp`,`qaos1_bill`,`qaos1_bill_date`,`qaos1_amt`,`qaos1_tin`) ";
  $insertNewBillQuery.= "VALUES ({$evtId},{$mcid},'{$imgName}',{$userId},'{$cluster}','{$corp}','{$bill}','{$billdate}','{$billamt}','{$tin}') ";
  $insertNewBillResult = mysqli_query($GLOBALS["___mysqli_ston"], $insertNewBillQuery) or displayerror(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
  return 1;
}

function displayBills($mcid,$active = false) {
  global $urlRequestRoot,$cmsFolder,$STARTSCRIPTS,$sourceFolder,$smarttablestuff;
  $billQuery = "SELECT * FROM `qaos1_bills` WHERE `page_modulecomponentid`={$mcid}";
  $billResult = mysqli_query($GLOBALS["___mysqli_ston"], $billQuery) or displayerror(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
  if($billQuery == "") return "";
  require_once($sourceFolder."/upload.lib.php");
  $uploadedFilesString = "";
  while($result = mysqli_fetch_assoc($billResult)) {
    $evtName=getEventNameFromId($result['qaos1_eventid'],$mcid);
    $billAddedBy = getUserName($result['userid']);
    $uploadedFilesString.=<<<TABLEROW
      <tr>
        <td>{$result['bill_no']}</td>
        <td><a href="./{$result['qaos1_imgname']}">{$result['qaos1_imgname']}</a></td> 
        <td>{$evtName}</td>
	<td>$billAddedBy</td>										 
      </tr>					  
TABLEROW;

  }
  if(!$active) {$smarttablestuff = smarttable::render(array('filestable'),null);
    $STARTSCRIPTS .= "initSmartTable();";}
  $displayBills = <<<BILLS
    $smarttablestuff
    <table class="display" id="filestable" border="1" width="100%">
       <thead>
             <tr>
               <th>Bill No.</th>
               <th>File</th>
               <th>Event Name</th> 
               <th>Added By</th>
             </tr>
        </thead>
        <tbody>
          $uploadedFilesString
        </tbody>


    </table>
   

BILLS;
  return $displayBills;
}

function createFolder($uploadDir) {
  if (!file_exists($uploadDir)) {
    displaywarning("The folder $uploadDir does not exist. Trying to creating it.");
    mkdir($uploadDir, 0755);
    if (!file_exists($uploadDir)) {
      displayerror("Creation of directory failed");
      return false;
    }
    else
      displayinfo("Created $uploadDir.");
  }
  return true;

}


function downloadAsZipFile($mcid,$evtId = 0) {
  global $sourceFolder,$uploadFolder;
  $uploadDir = $sourceFolder . "/" . $uploadFolder;
  if(!createFolder($uploadDir)) return false;
  $uploadDir .= "/qaos1";
  if(!createFolder($uploadDir)) return false;
  $uploadDir .= "/tmp/";
  if(!createFolder($uploadDir)) return false;
  $date = date_create();
  $timeStamp = date_timestamp_get($date);
  $uploadDir .= "events_".$timeStamp.".zip";
  $zip = new ZipArchive;
  if ($zip->open($uploadDir,ZipArchive::OVERWRITE) !== TRUE) {
    displaywarning("zip file not created");
    return false;
  }
  $getFileData = "SELECT events.* , uploads.upload_fileid  FROM `qaos1_bills` AS events , ".MYSQL_DATABASE_PREFIX."uploads AS uploads ";
  $getFileData .= "      WHERE events.qaos1_imgname = uploads.upload_filename AND events.page_modulecomponentid = {$mcid} AND";
  $getFileData .= "            uploads.page_modulecomponentid = {$mcid} AND uploads.page_module = 'qaos1' ";
  $getFileData.=($evtId != 0)?"AND events.qaos1_eventid= {$evtId}":"";
  $getFileData .= "      ORDER BY events.qaos1_eventid ";
  //  displayinfo($getFileData);
  $getFileDataRes = mysqli_query($GLOBALS["___mysqli_ston"], $getFileData) or displayerror(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
  if($getFileDataRes == "") return false;
  $billNo = array();
  while($result = mysqli_fetch_assoc($getFileDataRes)) {
    $upload_fileid = $result['upload_fileid'];
    $fileName = $result['qaos1_imgname'];
    $filename = str_repeat("0", (10 - strlen((string) $upload_fileid))) . $upload_fileid . "_" . $fileName;
    $file = $sourceFolder . "/" . $uploadFolder . "/qaos1/" . $filename;
    if(!file_exists($file)) {
      displaywarning("Biil No - #".$result['bill_no']."does not exist");
      continue;
    }
    $evtName = getEventNameFromId($result['qaos1_eventid'],$mcid);
    if(!isset($billNo[$evtName])) $billNo[$evtName]=1;
    $newFileName="Pragyan13_".$result['qaos1_cluster']."_".$evtName."_bill".($billNo[$evtName]++);//."_".$fileName;

    //    $newFileName=$evtName."_bill".$result['bill_no']."_".$fileName;
    $tmpFolder = $evtName;//getEventNameFromId($result['qaos1_eventid'],$mcid);
    if(!$zip->addEmptyDir($tmpFolder)) {
      displaywarning("Biil No - #".$result['bill_no']."not copied");
    }
  
    $zip->addFile($file, $tmpFolder."/".$newFileName);
  }
  $zip->close();
  header('Content-Type: application/zip');
  header('Content-disposition: attachment; filename=events.zip');
  header('Content-Length: ' . filesize($uploadDir));
  readfile($uploadDir);
  unlink($uploadDir);
  exit(0);
}

function displaySuggestionBox($txtField,$divId,$action,$userBoxName) {
  global $urlRequestRoot, $moduleFolder, $cmsFolder,$templateFolder,$sourceFolder,$cmsFolder,$STARTSCRIPTS;
  $scriptsFolder = "$urlRequestRoot/$cmsFolder/$templateFolder/common/scripts";
  $imagesFolder = "$urlRequestRoot/$cmsFolder/$templateFolder/common/images";

  $suggestionsBox =<<<FIELD
   <input type="text" name={$txtField} id="{$txtField}"  autocomplete="off" style="width: 256px" />
   <div id="{$divId}" style="background-color: white; width: 260px; border: 1px solid black; position: inherit; overflow-y: scroll; max-height: 180px;z-index:100; display: none"></div>
   <script type="text/javascript" language="javascript" src="$scriptsFolder/ajaxsuggestionbox.js"></script>
   <script language="javascript">
      var {$userBoxName} = new SuggestionBox(document.getElementById('{$txtField}'), document.getElementById('{$divId}'), "./+{$action}&subaction=getsuggestions&forwhat=%pattern%");
      {$userBoxName}.loadingImageUrl = '$imagesFolder/ajaxloading.gif';
   </script>

     


FIELD;
      return $suggestionsBox;
}


function disclaimerUpdate($mcid,$disclaimerTeam,$editorData) {
    $mcid = ((isset($GLOBALS["___mysqli_ston"]) && is_object($GLOBALS["___mysqli_ston"])) ? mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $mcid) : ((trigger_error("[MySQLConverterToo] Fix the mysql_escape_string() call! This code does not work.", E_USER_ERROR)) ? "" : ""));
    $disclaimerTeam = escape($disclaimerTeam);	

    $checkIfBillDisclaimerExist = "SELECT * FROM `qaos1_disclaimer`                                                                  
                                   WHERE `page_modulecomponentid`={$mcid} AND  `disclaimer_team`='{$disclaimerTeam}'";
    $checkQuery = mysqli_query($GLOBALS["___mysqli_ston"], $checkIfBillDisclaimerExist) or displayerror(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
    if(!$checkQuery) return;
    if(!mysqli_num_rows($checkQuery)) {
	$insertDisclaimer = "INSERT INTO `qaos1_disclaimer` VALUES($mcid,'{$disclaimerTeam}','')"; 	
        $insertDIsclaimerQuery = mysqli_query($GLOBALS["___mysqli_ston"], $insertDisclaimer) or displayerror(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));	
	if(!$insertDIsclaimerQuery) return ;
	}
    $insertQuery="UPDATE `qaos1_disclaimer` SET `disclaimer_desc`='{$editorData}' WHERE `page_modulecomponentid`={$mcid} ";
    $insertQuery.="AND `disclaimer_team`='{$disclaimerTeam}'";
    $updateRes=mysqli_query($GLOBALS["___mysqli_ston"], $insertQuery) or displayerror(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
    if($updateRes!='') displayinfo("Details Successfully updated !!!");
}

function getDisclaimer($team,$mcid) {
  $team = escape($team);
  $mcid = escape($mcid);

  $getQuery="SELECT * FROM `qaos1_disclaimer` WHERE `page_modulecomponentid`={$mcid} AND `disclaimer_team`='{$team}'"; 
  $runQuery=mysqli_query($GLOBALS["___mysqli_ston"], $getQuery) or displayerror(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
  if(!mysqli_num_rows($runQuery)) {
    
    $insertQuery=mysqli_query($GLOBALS["___mysqli_ston"], "INSERT INTO `qaos1_disclaimer` VALUES({$mcid},'{$team}','')") or displayerror(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
    if($insertQuery!="") displayinfo("Field {$team} Inserted TO Table");
    return "";
  }
  $res=mysqli_fetch_assoc($runQuery);
  
  return $res['disclaimer_desc'];
}

function printDataForTreasurerId($mcid,$trId) {
  global $sourceFolder,$moduleFolder;
  require_once($sourceFolder."/".$moduleFolder."/qaos1/excel.php");
  $evtDetails = getFundReq($trId,$mcid);
  displayinfo(print_r($evtDetails,true));
  $content = getDisclaimer("getTreasurerAcceptance",$mcid);  
  //escape quotes
  $content = str_replace ("'", "\'", $content); 
  //replace the vars in file content with those defined
  $content = preg_replace('#\{([a-z0-9\-_]*?)\}#is', "' . ((isset(\$evtDetails['\\1'])) ? \$evtDetails['\\1'] : '') . '", $content);
  //Make the content parseable
  eval("\$content = '$content';");
  //  displayinfo($content);
  //get parser done		
  return printContent($content);
}

function printDataForOCId($mcid,$trId) {
  global $sourceFolder,$moduleFolder;
  require_once($sourceFolder."/".$moduleFolder."/qaos1/excel.php");
  $evtDetails = getEvtProc($trId,$mcid);
  displayinfo(print_r($evtDetails,true));
  $content = getDisclaimer("getOCAcceptance",$mcid);  
  //escape quotes
  $content = str_replace ("'", "\'", $content); 
  //replace the vars in file content with those defined
  $content = preg_replace('#\{([a-z0-9\-_]*?)\}#is', "' . ((isset(\$evtDetails['\\1'])) ? \$evtDetails['\\1'] : '') . '", $content);
  //Make the content parseable
  eval("\$content = '$content';");
  //  displayinfo($content);
  //get parser done		
  return printContent($content);
}
?>
