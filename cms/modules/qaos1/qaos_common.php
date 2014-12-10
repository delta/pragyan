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
  $result = mysql_query($query);
  $row = mysql_fetch_assoc($result);
  return $arr = array ('PRAGYAN_ID'=> $row['user_id'] , 'NAME' => $row['user_name'] , 'EMAIL' => $row['user_email']);
}

function getEvtProc($evtProcId,$mcId) {
 $query = "SELECT * FROM `qaos1_evtproc` WHERE `modulecomponentid` = {$mcId} AND `evtproc_Id` = {$evtProcId}";		
 $result = mysql_query($query);
 $row = mysql_fetch_array($result);
 $columnDetail = mysql_query("SHOW COLUMNS FROM `qaos1_evtproc`");	
 $ret = array();
 $cnt = 0;
 while($res = mysql_fetch_array($columnDetail)){
  $ret[strtoupper($res[0])] = $row[$cnt++];

 } 
  return array_merge(assignVars($row[7]),$ret);	
}

function getFundReq($fundReqId,$mcId) {
 $query = "SELECT * FROM `qaos1_fundreq` WHERE `modulecomponentid` = {$mcId} AND `fundreq_Id` = {$fundReqId}";		
 $result = mysql_query($query);
 $row = mysql_fetch_array($result);
 $columnDetail = mysql_query("SHOW COLUMNS FROM `qaos1_fundreq`");	
 $ret = array();
 $cnt = 0;
 while($res = mysql_fetch_array($columnDetail)){
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
  $result = mysql_query($query) or displayerror(mysql_error());
  if(!mysql_num_rows($result)) return 0;
  $res = mysql_fetch_array($result);
  return $res[0];
}

function getEventIdFromName($evtName,$mcid) {
  $query = "SELECT `events_id` FROM `qaos1_events` WHERE `page_modulecomponentid`=$mcid AND `events_name` = '{$evtName}'"; 
  $result = mysql_query($query) or displayerror(mysql_error());
  if(!mysql_num_rows($result)) return 0;
  $res = mysql_fetch_array($result);
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
  $checkImageExistResult=mysql_query($checkImageExist) or displayerror(mysql_error());
  if(mysql_num_rows($checkImageExistResult)) {
    displayerror("Image Already Exist");
    return 0;
  }
    $insertNewBillQuery = "INSERT INTO `qaos1_bills` (`qaos1_eventid`,`page_modulecomponentid`,`qaos1_imgname`,`userid`,`qaos1_cluster`,`qaos1_corp`,`qaos1_bill`,`qaos1_bill_date`,`qaos1_amt`,`qaos1_tin`) ";
  $insertNewBillQuery.= "VALUES ({$evtId},{$mcid},'{$imgName}',{$userId},'{$cluster}','{$corp}','{$bill}','{$billdate}','{$billamt}','{$tin}') ";
  $insertNewBillResult = mysql_query($insertNewBillQuery) or displayerror(mysql_error());
  return 1;
}

function displayBills($mcid,$active = false) {
  global $urlRequestRoot,$cmsFolder,$STARTSCRIPTS,$sourceFolder,$smarttablestuff;
  $billQuery = "SELECT * FROM `qaos1_bills` WHERE `page_modulecomponentid`={$mcid}";
  $billResult = mysql_query($billQuery) or displayerror(mysql_error());
  if($billQuery == "") return "";
  require_once($sourceFolder."/upload.lib.php");
  $uploadedFilesString = "";
  while($result = mysql_fetch_assoc($billResult)) {
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
  $getFileDataRes = mysql_query($getFileData) or displayerror(mysql_error());
  if($getFileDataRes == "") return false;
  $billNo = array();
  while($result = mysql_fetch_assoc($getFileDataRes)) {
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
    $mcid = mysql_real_escape_string($mcid);
    $disclaimerTeam = escape($disclaimerTeam);	

    $checkIfBillDisclaimerExist = "SELECT * FROM `qaos1_disclaimer`                                                                  
                                   WHERE `page_modulecomponentid`={$mcid} AND  `disclaimer_team`='{$disclaimerTeam}'";
    $checkQuery = mysql_query($checkIfBillDisclaimerExist) or displayerror(mysql_error());
    if(!$checkQuery) return;
    if(!mysql_num_rows($checkQuery)) {
	$insertDisclaimer = "INSERT INTO `qaos1_disclaimer` VALUES($mcid,'{$disclaimerTeam}','')"; 	
        $insertDIsclaimerQuery = mysql_query($insertDisclaimer) or displayerror(mysql_error());	
	if(!$insertDIsclaimerQuery) return ;
	}
    $insertQuery="UPDATE `qaos1_disclaimer` SET `disclaimer_desc`='{$editorData}' WHERE `page_modulecomponentid`={$mcid} ";
    $insertQuery.="AND `disclaimer_team`='{$disclaimerTeam}'";
    $updateRes=mysql_query($insertQuery) or displayerror(mysql_error());
    if($updateRes!='') displayinfo("Details Successfully updated !!!");
}

function getDisclaimer($team,$mcid) {
  $team = escape($team);
  $mcid = escape($mcid);

  $getQuery="SELECT * FROM `qaos1_disclaimer` WHERE `page_modulecomponentid`={$mcid} AND `disclaimer_team`='{$team}'"; 
  $runQuery=mysql_query($getQuery) or displayerror(mysql_error());
  if(!mysql_num_rows($runQuery)) {
    
    $insertQuery=mysql_query("INSERT INTO `qaos1_disclaimer` VALUES({$mcid},'{$team}','')") or displayerror(mysql_error());
    if($insertQuery!="") displayinfo("Field {$team} Inserted TO Table");
    return "";
  }
  $res=mysql_fetch_assoc($runQuery);
  
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
