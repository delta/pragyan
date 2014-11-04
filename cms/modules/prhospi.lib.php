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
 * +view=> the hospi person can see all the available actions
 * +accomodate => the hospi desk gets to see the status of various rooms and their availability
 * 	subaction
 * +view&mode=hostel		=> Hostel wise view(default +accomodate view)
 * +view&mode=all			=> All rooms in all hostels.
 * check in					=> Clicks on a room in one of the view formats and then enters the guest mail id
 * check out				=> puts in the mail id of the user and then checks him out
 * addRoom					=> adds room to the hostel.+ capacity
 * addHostel				=> add hostel
 *
 * +(Not in hospi module) to get the list of people registered for workshop/prelim selected ppl/ paper presentation etc)

 Hospi charges paid/ how much/caution deposit refundable/
 reimbursement.


 user Id of the person manning the hospi desk.
 The user Id of the person refunding the caution/reimbursement.

 Print the details of the user along with the name of the hospi person manning the desk

*/
class prhospi implements module,fileuploadable {
  private $userId;
  private $moduleComponentId;
  public function getHtml($gotuid, $gotmoduleComponentId, $gotaction) {
    $this->userId = $gotuid;
    $this->moduleComponentId = $gotmoduleComponentId;
    if ($gotaction == 'csg')
      return $this->actionCsg();
    if ($gotaction == 'hospihead')
      return $this->actionHospihead();
    if ($gotaction == 'prhead')
      return $this->actionPrhead();
    if ($gotaction == 'prview')
      return $this->actionPrview();
    if ($gotaction == 'view')
      return $this->actionView();
    /*    if ($gotaction == '')
	  return $this->actionAddroom();
    */
    else return $this->actionView();
  }
  
  
  
  public function getCkBody($content="",$team){
    global $sourceFolder;
    global $cmsFolder;
    global $moduleFolder;
    global $urlRequestRoot;
    global $ICONS;
    require_once ("$sourceFolder/$moduleFolder/article/ckeditor3.5/ckeditor.php");
    require_once ("$sourceFolder/$moduleFolder/prhospi/prhospi_common.php");
    $editor='ckeditor';
    $amt=getAmount($team, $this->moduleComponentId);
    $amt1="";
    if($team=="hospihead") $amt1.=getAmount($team."1", $this->moduleComponentId);
    $content=getDescription($team, $this->moduleComponentId);
    $CkForm=<<<Ck
      <form action="./+{$team}" method="post">
         <label>Enter the Amount:&nbsp;&nbsp;&nbsp;</label><input type="text" value="{$amt}" name="amountDetail"/>
         <input type="submit" value="Submit"><br/>
      </form>
Ck;

    if($team=="hospihead") {
      $CkForm.=<<<CK

      <form action="./+{$team}" method="post">
         <label>Enter the Amount:&nbsp;&nbsp;&nbsp;</label><input type="text" value="{$amt1}" name="amountDetail1"/>
         <input type="submit" value="Submit"><br/>
      </form>
CK;
    }
    $CkForm .=<<<Ck

      <br/><br/>
      <form action="./+{$team}" method="post">
         <a name="editor"></a>
         <input type="button" id="show_plain" value="Plain Source" onclick="$('#show_plain').hide();$('#show_ckeditor').show();CKEDITOR.instances.CKEditor1.updateElement();CKEDITOR.instances.CKEditor1.destroy();document.getElementById('editor').value='plain';">
         <input type="button" id="show_ckeditor" value="CKEditor" style="display:none" onclick="$('#show_plain').show();$('#show_ckeditor').hide();CKEDITOR.add(CKEDITOR.editor.replace(document.getElementsByName('CKEditor1')[0]));document.getElementById('editor').value='ckeditor';">
         <input type="button" value="Cancel" onclick="submitarticleformCancel(this);"><input type="submit" value="Save"><input type="button" value="Preview" onclick="submitarticleformPreview(this)"><input type="button" value="Draft" onclick="submitarticleformDraft(this);">
         To upload files and images, go to the <a href="#files">files section</a>.<br/>

Ck;
    $top ="<a href='#topquicklinks'>Top</a>";
    $oCKEditor = new CKeditor();
    $oCKEditor->basePath = "$urlRequestRoot/$cmsFolder/$moduleFolder/article/ckeditor3.5/";
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


  private function getFormSuggestions($input) {
    $emailQuery ="SELECT * FROM `".MYSQL_DATABASE_PREFIX."pages` WHERE `page_name` LIKE '%$input%' AND `page_module`='form'";
    $emailResult = mysql_query($emailQuery);
    $suggestions = array($input);
    while($emailRow = mysql_fetch_row($emailResult)) {
      $suggestions[] = $emailRow[1]. ' - ' . $emailRow[7];
    }                                                                                                                                           
    return join($suggestions, ',');                                                                                                             
  }
 
  
  public function actionCsg() {
    $moduleComponentId=$this->moduleComponentId;
    if(isset($_GET['subaction'])&&$_GET['subaction'] == 'getsuggestions' && isset($_GET['forwhat']))   {
      echo $this->getFormSuggestions(escape($_GET['forwhat']));                                                          
      exit();                                                                                                             
    }
    if(isset($_GET['subaction'])&&$_GET['subaction'] == 'formDetailsrequired') {
      $cnt=0;
      while(isset($_POST['FormDetail-'.$cnt])&&isset($_POST['hiddenFormDetail-'.$cnt])) {
	$_POST['hiddenFormDetail-'.$cnt]=mysql_real_escape_string($_POST['hiddenFormDetail-'.$cnt]);
	$isRequired=(mysql_real_escape_string($_POST['FormDetail-'.$cnt])==='No')?0:1;
	$detailsGiven=explode("-",$_POST['hiddenFormDetail-'.$cnt]);
	$checkForQuery="SELECT * FROM `prhospi_admin` WHERE `page_modulecomponentid`={$moduleComponentId} AND ";
	$checkForQuery.="`page_getform_modulecomponentid`={$detailsGiven[1]} AND `details_to_display`='{$detailsGiven[2]}'";
	if(mysql_num_rows(mysql_query($checkForQuery))) {
	  $updateRequiredDetailQuery="UPDATE `prhospi_admin` SET `detail_required`='{$isRequired}' WHERE `page_modulecomponentid`=";
	  $updateRequiredDetailQuery.="{$moduleComponentId} AND `page_getform_modulecomponentid`={$detailsGiven[1]} AND `details_to_display`='";
	  $updateRequiredDetailQuery.="{$detailsGiven[2]}'";
	  $updateRequiredDetailRes=mysql_query($updateRequiredDetailQuery);
	}
	else {
	  $insertRequiredDetails="INSERT INTO `prhospi_admin` VALUES({$moduleComponentId},{$detailsGiven[1]},{$detailsGiven[2]},'{$isRequired}')";
	  $insertRequiredResult=mysql_query($insertRequiredDetails);
	}
	$cnt++;
      }
    }                                           
    global $urlRequestRoot,$sourceFolder,$templateFolder,$cmsFolder;
    $scriptsFolder = "$urlRequestRoot/$cmsFolder/$templateFolder/common/scripts";
    $imagesFolder = "$urlRequestRoot/$cmsFolder/$templateFolder/common/images";
    $find=<<<USER
      <form method="POST" action="./+csg">
      Enter Form:<input type="text" name="txtFormName" id="txtFormName"  autocomplete="off" style="width: 256px" />
      <div id="suggestionsBox" style="background-color: white; width: 260px; border: 1px solid black; position: absolute; overflow-y: scroll; max-height: 180px; display: none"></div>
      <input type="submit" Value="Find User"/>
      <script type="text/javascript" language="javascript" src="$scriptsFolder/ajaxsuggestionbox.js">
      </script>
      <script language="javascript">
      var userBox = new SuggestionBox(document.getElementById('txtFormName'), document.getElementById('suggestionsBox'), "./+csg&subaction=getsuggestions&forwhat=%pattern%");
    userBox.loadingImageUrl = '$imagesFolder/ajaxloading.gif';
    </script>
   </form>
USER;
    if(isset($_POST['txtFormName'])) {
      $inputData=$_POST['txtFormName'];
      $findHypenPos = strrpos($inputData,"-");
      if($findHypenPos !== false) {
	$formModuleComponentId = substr($inputData,$findHypenPos+2); 
	$getRequiredQuery = "SELECT * FROM `form_elementdesc` WHERE `page_modulecomponentid` IN (".$formModuleComponentId.",0)";
	$getRequiredRes = mysql_query($getRequiredQuery);
	$tableField="<form action='./+csg&subaction=formDetailsrequired' method='POST'><table border='1'>";
	$cnt=0;
	while($result=mysql_fetch_row($getRequiredRes)) {
	  $tableField.=<<<REQUIRED
	  <tr>
	    <td>{$result[2]}</td>
	    <td>{$result[3]}</td>
	    <td>{$result[4]}</td>
	    <td>
		<select name="FormDetail-{$cnt}">
	        	<option>No</option>
		        <option>Yes</option>
		</select>
		<input type="hidden" value='required-{$result[0]}-{$result[1]}' name='hiddenFormDetail-{$cnt}'/>		    
            </td>
	  </tr>
REQUIRED;
	  $cnt++;
	}
	$tableField.="<tr><td colspan='4'><input type='submit' value='confirm'/></td></tr></table></form>";
	$find.= $tableField;
      }
    }
		      
    return $find;

  }


  public function actionView() {
    global $urlRequestRoot,$sourceFolder,$templateFolder,$cmsFolder,$moduleFolder;
    $moduleComponentId=$this->moduleComponentId;
    $scriptsFolder = "$urlRequestRoot/$cmsFolder/$templateFolder/common/scripts";
    $imagesFolder = "$urlRequestRoot/$cmsFolder/$templateFolder/common/images";
    require_once("$sourceFolder/$moduleFolder/prhospi/prhospi_common.php");
    require_once("$sourceFolder/$moduleFolder/prhospi/accommodation.php");
    if(isset($_POST['subaction'])&&$_POST['subaction']=='accoRegUser') {
      echo ajaxSuggestions($moduleComponentId,0,$this->userId);
      exit();
    }
    if(isset($_POST['subaction'])&&$_POST['subaction']=='accoRegUserUpdate') {
      echo ajaxSuggestions($moduleComponentId,1);
      exit();
    }
    if(isset($_GET['subaction'])&&$_GET['subaction'] == 'getsuggestions' && isset($_GET['forwhat']))   {
      echo getSuggestionsForIdOrEmail(escape($_GET['forwhat']));                                                          
      exit();                                                                                                             
    }
    if(isset($_POST['printthis']) && isset($_POST['printHiddenId'])) {
      if($_POST['printHiddenId']!="") {
	$pos = strpos($_POST['printHiddenId'],"printHostelAllotmentBill");
	
	if($pos==0) return printDisclaimer($moduleComponentId,substr(escape($_POST['printHiddenId']),24),"hospihead");                                 
	  
      }
    }if(isset($_GET['subaction'])) {
      if($_GET['subaction']=='AddRoom') {
	if(isset($_POST['addHostels'])) {
	  $displayActions=addHostels($_POST,$moduleComponentId);
	}
	return $displayActions=displayAddNewRooms("view",$moduleComponentId);
      }      
    }
    $displayTags=<<<TAG
      <script type="text/javascript" src="$urlRequestRoot/$cmsFolder/$moduleFolder/prhospi/accoregister.js"></script> 
       <h2> Accommodation </h2>
	<table>
         <tr>
           <td><a href="./+view&subaction=viewRegisteredUser"> <div>View Registrants</div></a></td>
           <td><a href="./+view"><div>Add User</div></a></td>
         </tr>
        </table>
                                    
TAG;
  


    if(isset($_GET['subaction'])&&$_GET['subaction'] == 'viewRegisteredUser')   {
      $excel ="<a href='./+view&subaction=viewRegisteredUser&saveAsExcel'>Save as Excel</a>";
      if(isset($_GET['saveAsExcel'])) {
	require_once("$sourceFolder/$moduleFolder/qaos1/excel.php");
	displayExcelForTable(displayUsersRegisteredToAcco($moduleComponentId));
	
      }
      return $displayTags.$excel.displayUsersRegisteredToAcco($moduleComponentId);
    }


    $inputUser=<<<USER
      
    <h2> CHECK IN FORM </h2>
      <form method="POST" action="./+view">
      Enter UserId or Email:<input type="text" name="txtFormUserId" id="txtFormUserId"  autocomplete="off" style="width: 256px" />
      <div id="suggestionsBox" style="background-color: white; width: 260px; border: 1px solid black; position: absolute; overflow-y: scroll; max-height: 180px; display: none"></div>
      <input type="submit" Value="Find User"/>
  <!--    <script type="text/javascript" language="javascript" src="$scriptsFolder/ajaxsuggestionbox.js">
      </script>
      <script language="javascript">
      
      var userBox = new SuggestionBox(document.getElementById('txtFormUserId'), document.getElementById('suggestionsBox'), "./+view&subaction=getsuggestions&forwhat=%pattern%");
    userBox.loadingImageUrl = '$imagesFolder/ajaxloading.gif';
    </script>
  --> </form>


USER;
    $userDetails="";
    $displayActions="";
    if(isset($_POST['txtFormUserId'])&&$_POST['txtFormUserId'] != '') {
      //      $detailsGiven=explode("- ",escape($_POST['txtFormUserId']));
      //      $userDetails.=getUserDetailsForHospi($detailsGiven[1],$moduleComponentId);
      $userDetails.=getUserDetailsForHospi(escape($_POST['txtFormUserId']),$moduleComponentId);
    }
    if(isset($_POST['txtFormUserId1'])&&$_POST['txtFormUserId1'] != '') {
      if(!isset($_POST['refundAmt'])) {
	displayerror("Refund Amount Not Defined");
	
      }
      else if(!is_numeric($_POST['refundAmt'])) {
	displayerror("Refund Amount is not a Valid Number");
      } 
      else {
	//        $detailsGiven=explode("- ",escape($_POST['txtFormUserId1']));
        //        checkOut($detailsGiven[1],escape($_POST['refundAmt']),$moduleComponentId);
	checkOut(escape($_POST['txtFormUserId1']),escape($_POST['refundAmt']),$moduleComponentId);
      }
    }
    if(isset($_GET['subaction'])&&($_GET['subaction'] == 'accommodation') && isset($_GET['userId'])&&(is_numeric($_GET['userId']))) {
      $userDetails.=displayAccommodationForm(escape($_GET['userId']),$moduleComponentId,$this->userId);
    }

    $amtToCollect = getAmount("hospihead",$moduleComponentId);
    $checkOutFORM=<<<checkOut
   <hr/>
   <h2> CHECK OUT FORM </h2>
    <form method="POST" action="./+view">
    <table border="1">
      <tr>
       <td>Enter UserId or Email:</td>
       <td><input type="text" name="txtFormUserId1" id="txtFormUserId1"  autocomplete="off" style="width: 256px" />
        <div id="suggestionsBox1" style="background-color: white; width: 260px; border: 1px solid black; position: absolute; overflow-y: scroll; max-height: 180px; display: none"></div><br/>
        </td>
      </tr>
      <tr>
        <td>Refund Amount:</td>
        <td><input type="text" name="refundAmt" value="{$amtToCollect}"/></td>
      </tr>
      <tr>  
        <td colspan="2"><input type="submit" Value="Find User"/></td>
      </tr>
      </table>
<!--      <script type="text/javascript" language="javascript" src="$scriptsFolder/ajaxsuggestionbox.js">
      </script>
      <script language="javascript">
      var userBox = new SuggestionBox(document.getElementById('txtFormUserId1'), document.getElementById('suggestionsBox1'), "./+view&subaction=getsuggestions&forwhat=%pattern%");
    userBox.loadingImageUrl = '$imagesFolder/ajaxloading.gif';
    </script> -->
   </form>


checkOut;
    return $displayTags.$inputUser.$userDetails.$checkOutFORM;
    
  }
  
  public function actionHospihead() {
    global $urlRequestRoot,$sourceFolder,$templateFolder,$cmsFolder,$moduleFolder;
    $moduleComponentId=$this->moduleComponentId;
    $scriptsFolder = "$urlRequestRoot/$cmsFolder/$templateFolder/common/scripts";
    $imagesFolder = "$urlRequestRoot/$cmsFolder/$templateFolder/common/images";
    require_once("$sourceFolder/$moduleFolder/prhospi/prhospi_common.php");
    require_once("$sourceFolder/$moduleFolder/prhospi/accommodation.php");
    require_once($sourceFolder."/".$moduleFolder."/qaos1/excel.php");
    require_once($sourceFolder."/upload.lib.php");
    if(isset($_GET['subaction'])&&$_GET['subaction'] == 'getsuggestions' && isset($_GET['forwhat']))   {
      echo getSuggestionsForIdOrEmail(escape($_GET['forwhat']));                                                          
      exit();                                                                                                             
    }
    if(isset($_POST['txtFormUserId'])&&$_POST['txtFormUserId'] != '') {
      $detailsGiven=explode("- ",escape($_POST['txtFormUserId']));
      deleteAccomodatedUser($detailsGiven[1],$moduleComponentId);
    }
    displayinfo(print_r(assignVars($this->userId,$moduleComponentId),true));

    if(isset($_POST['amountDetail'])) {
      $amt=mysql_real_escape_string($_POST['amountDetail']);
      $insertQuery="UPDATE `prhospi_disclaimer` SET `team_cost`={$amt} WHERE `page_modulecomponentid`={$this->moduleComponentId} AND ";
      $insertQuery.="`disclaimer_team`='hospihead'";
      $updateRes=mysql_query($insertQuery) or displayerror(mysql_error());
      if($updateRes!='') displayinfo("Amount Updated to Rs. $amt");
    }

    if(isset($_POST['amountDetail1'])) {
      $amt=mysql_real_escape_string($_POST['amountDetail1']);
      $insertQuery="UPDATE `prhospi_disclaimer` SET `team_cost`={$amt} WHERE `page_modulecomponentid`={$this->moduleComponentId} AND ";
      $insertQuery.="`disclaimer_team`='hospihead1'";
      $updateRes=mysql_query($insertQuery) or displayerror(mysql_error());
      if($updateRes!='') displayinfo("Amount Updated to Rs. $amt");
    }
    
    if (isset($_POST['CKEditor1'])) {
      $editorData=escape($_POST['CKEditor1']);
      $insertQuery="UPDATE `prhospi_disclaimer` SET `disclaimer_desc`='{$editorData}' WHERE `page_modulecomponentid`={$this->moduleComponentId} ";
      $insertQuery.="AND `disclaimer_team`='hospihead'";
      $updateRes=mysql_query($insertQuery) or displayerror(mysql_error());
      if($updateRes!='') displayinfo("Details Successfully updated !!!");
    }
    if(isset($_POST['downloadSampleFormat'])) {
      downloadSampleFormatForRoomUpload();
    } 



    if(isset($_FILES['fileUploadField']['name'])) {
      $excelData = readExcelSheet($_FILES['fileUploadField']['tmp_name'][0]);
      $success = 1;
      for($i=2;$i<=count($excelData);$i++) {
	for($j=$excelData[$i][2];$j<=$excelData[$i][3];$j++) {
	  if($excelData[$i][1] == NULL) continue;
	  $checkIfExistQuery = "SELECT * FROM `prhospi_hostel` 
                                WHERE `hospi_hostel_name`='{$excelData[$i][1]}' AND 
                                      `hospi_room_no`={$j} AND `page_modulecomponentid`={$moduleComponentId}";
	  $checkIfExistRes = mysql_query($checkIfExistQuery) or displayerror(mysql_error());
	  if(mysql_num_rows($checkIfExistRes)) {
	    $updateFieldQuery = "UPDATE `prhospi_hostel` 
                                 SET `hospi_room_capacity`={$excelData[$i][4]} , `hospi_floor` =  {$excelData[$i][5]}
                                 WHERE `page_modulecomponentid`={$moduleComponentId} AND 
                                       `hospi_hostel_name`='{$excelData[$i][1]}' AND `hospi_room_no`={$j}";
	    $updateResult = mysql_query($updateFieldQuery) or displayerror(mysql_error());
	    continue;
	  }
	  $insertIntoHospiQuery = "INSERT INTO `prhospi_hostel` (page_modulecomponentid,hospi_hostel_name,hospi_room_capacity,
                                                                      hospi_room_no,hospi_floor)                                                                                            VALUES ({$moduleComponentId},'{$excelData[$i][1]}',{$excelData[$i][4]},{$j},{$excelData[$i][5]})";
	  $res = mysql_query($insertIntoHospiQuery) or displayerror(mysql_error());
	  if($res == "") $success=0;
	}
      }
      if(!$success) displayerror("Datas are not inserted");
    }


    $hospiview = "";
    $hospiview.=<<<VIEW
	<table>
         <a onClick="history.go(-1)">BACK</a><br/>
         <tr>
           <td><a href="./+hospihead&subaction=addRoom"> <div>Add Rooms</div></a></td>
           <td><a href="./+hospihead&subaction=viewStatus"><div>View All Rooms</div></a></td>
           <td><a href="./+hospihead&subaction=ckEditor"><div>Update Disclaimer</div></a></td>
           <td><a href="./+hospihead&subaction=deleteUsers"><div>Delete User in Accomodation</div></a></td>
           <td><a href="./+hospihead&subaction=blockRooms"><div>Block Rooms</div></a></td>
         </tr>
        </table>

VIEW;
    if(isset($_GET['subaction'])) {
      if($_GET['subaction']=='blockRooms') $hospiview.=blockRoom($this->moduleComponentId);
      if($_GET['subaction']=='ckEditor') $hospiview.=$this->getCkBody("","hospihead");
      else if($_GET['subaction']=='addRoom') {
	$fileUploadableForm=getFileUploadForm($this->moduleComponentId,"prhospi",'./+hospihead',UPLOAD_SIZE_LIMIT,1);

	$hospiview.=<<<ADDROOMFORM
           <br/><br/>
           <form action="./+hospihead" method="post">
               <input type="submit" name="downloadSampleFormat" value="Download Sample Form"><br/>
           </form>
       $fileUploadableForm
ADDROOMFORM;
      }
      else if($_GET['subaction']=='viewStatus') $hospiview.=displayRooms($moduleComponentId);
      else if($_GET['subaction']=='deleteUsers') {
	$deleteUserForm = displayUsersRegisteredToAccoWithDelete($moduleComponentId);
	$hospiview.=<<<HOSPI
	  $deleteUserForm
<form method="POST" action="./+hospihead">
	  Enter UserId or Email:<input type="text" name="txtFormUserId" id="txtFormUserId"  autocomplete="off" style="width: 256px" />
	  <div id="suggestionsBox" style="background-color: white; width: 260px; border: 1px solid black; position: absolute; overflow-y: scroll; max-height: 180px; display: none"></div>
	  <input type="submit" Value="Find User"/>
	  <script type="text/javascript" language="javascript" src="$scriptsFolder/ajaxsuggestionbox.js">
	  </script>
	  <script language="javascript">
	  var userBox = new SuggestionBox(document.getElementById('txtFormUserId'), document.getElementById('suggestionsBox'), "./+hospihead&subaction=getsuggestions&forwhat=%pattern%");
	userBox.loadingImageUrl = '$imagesFolder/ajaxloading.gif';
	</script>
	</form>
HOSPI;
      }
    }
    return $hospiview; 
  }

  
 public function actionPrview() {
   global $urlRequestRoot,$sourceFolder,$templateFolder,$cmsFolder,$moduleFolder;
    $moduleComponentId=$this->moduleComponentId;
    $scriptsFolder = "$urlRequestRoot/$cmsFolder/$templateFolder/common/scripts";
    $imagesFolder = "$urlRequestRoot/$cmsFolder/$templateFolder/common/images";
    require_once("$sourceFolder/$moduleFolder/prhospi/prhospi_common.php");
    require_once("$sourceFolder/$moduleFolder/prhospi/accommodation.php");
    if(isset($_GET['subaction'])&&$_GET['subaction'] == 'getsuggestions' && isset($_GET['forwhat']))   {
      //      echo getSuggestionsForIdOrEmail(escape($_GET['forwhat']));                                                          
      exit(0);                                                                                                             
    }
    if(isset($_POST['printthis']) && isset($_POST['printHiddenId'])) {
      if($_POST['printHiddenId']!="") {
	$pos = strpos($_POST['printHiddenId'],"printHostelAllotmentBill");
	if($pos==0) return printDisclaimer($moduleComponentId,substr(escape($_POST['printHiddenId']),24),"prhead");
      }
    }
    if(isset($_POST['txtFormUserId1'])&&$_POST['txtFormUserId1'] != '') {
      //        $detailsGiven=explode("- ",escape($_POST['txtFormUserId1']));
        $detailsGiven=escape($_POST['txtFormUserId1']);
	if(!isset($_POST['refundAmt'])) {
	    displaywarning("Refund Amount not declared");
	  }
	  else {
	    //	    if(isset($detailsGiven[1])) checkOutPr($detailsGiven[1],escape($_POST['refundAmt']),$moduleComponentId);
	    if(isset($detailsGiven)) checkOutPr($detailsGiven,escape($_POST['refundAmt']),$moduleComponentId);
	    else displaywarning("Invalid Pragyan Id");
	  }
    }
    $displayTags=<<<TAG
	<table>
         <tr>
           <td><a href="./+prview&subaction=viewRegisteredUser"> <div>View Registrants</div></a></td>
           <td><a href="./+prview"><div>Add User</div></a></td>
         </tr>
        </table>
                                    
TAG;
  


    if(isset($_GET['subaction'])&&$_GET['subaction'] == 'viewRegisteredUser')   {
      return $displayTags.displayUsersRegisteredToPr($moduleComponentId);
    }

  $inputUser=<<<USER
    <h2> CHECK IN FORM </h2>
      <form method="POST" id="prCheckInForm" action="./+Prview">
     Enter UserId or Email:<input type="text" name="txtFormUserId" id="txtFormUserId"  autofocus autocomplete="off" style="width: 256px" />
      <div id="suggestionsBox" style="background-color: white; width: 260px; border: 1px solid black; position: absolute; overflow-y: scroll; max-height: 180px; display: none"></div>
      <input type="submit" Value="Find User"/>
      <script type="text/javascript" src="$urlRequestRoot/$cmsFolder/$moduleFolder/prhospi/prregister.js"></script> 

USER;
    $userDetails="";
    $displayActions="";
    if(isset($_POST['txtFormUserId'])&&$_POST['txtFormUserId'] != '') {
      $detailsGiven=escape($_POST['txtFormUserId']);
      if(isset($detailsGiven)) $userDetails.=submitDetailsForPr($detailsGiven,$moduleComponentId,$this->userId);
      else displaywarning("Invalid Pragyan Id");
    }
    $amtToCollect = getAmount("prhead",$moduleComponentId);

    $checkOutFORM=<<<checkOut
   <hr/>
   <h2> CHECK OUT FORM </h2>
    <form method="POST" action="./+prview">
    <table border="1">
      <tr>
       <td>Enter UserId or Email:</td>
       <td><input type="text" name="txtFormUserId1" id="txtFormUserId1"  autocomplete="off" style="width: 256px" />
        <div id="suggestionsBox1" style="background-color: white; width: 260px; border: 1px solid black; position: absolute; overflow-y: scroll; max-height: 180px; display: none"></div><br/>
        </td>
      </tr>
      <tr>
        <td>Refund Amount:</td>
        <td><input type="text" disabled="disabled" name="refundAmt1" value="{$amtToCollect}"/>
        <input type="hidden"  name="refundAmt" value="{$amtToCollect}"/></td>
      </tr>
      <tr>  
        <td colspan="2"><input type="submit" Value="Find User"/></td>
      </tr>
      </table>
<!--      <script type="text/javascript" language="javascript" src="$scriptsFolder/ajaxsuggestionbox.js">
      </script>
      <script language="javascript">
      var userBox = new SuggestionBox(document.getElementById('txtFormUserId1'), document.getElementById('suggestionsBox1'), "./+prview&subaction=getsuggestions&forwhat=%pattern%");
    userBox.loadingImageUrl = '$imagesFolder/ajaxloading.gif';
    </script>-->
   </form>


checkOut;
  return $displayTags.$inputUser.$userDetails.$checkOutFORM;
  }
 

  public function actionPrhead() {
    global $urlRequestRoot,$sourceFolder,$templateFolder,$cmsFolder,$moduleFolder;
    $moduleComponentId=$this->moduleComponentId;
    require_once("$sourceFolder/$moduleFolder/prhospi/prhospi_common.php");
    if(isset($_POST['txtFormUserId'])&&$_POST['txtFormUserId'] != '') {
      $detailsGiven=explode("- ",escape($_POST['txtFormUserId']));
      deletePrUser($detailsGiven[1],$moduleComponentId);
    }
    if(isset($_POST['amountDetail'])) {
      $amt=mysql_real_escape_string($_POST['amountDetail']);
      $insertQuery="UPDATE `prhospi_disclaimer` SET `team_cost`={$amt} WHERE `page_modulecomponentid`={$this->moduleComponentId} AND ";
      $insertQuery.="`disclaimer_team`='prhead'";
      $updateRes=mysql_query($insertQuery) or displayerror(mysql_error());
      if($updateRes!='') displayinfo("Amount Updated to Rs. $amt");
    }
    
    if (isset($_POST['CKEditor1'])) {
      $editorData=escape($_POST['CKEditor1']);
      $insertQuery="UPDATE `prhospi_disclaimer` SET `disclaimer_desc`='{$editorData}' WHERE `page_modulecomponentid`={$this->moduleComponentId} ";
      $insertQuery.="AND `disclaimer_team`='prhead'";
      $updateRes=mysql_query($insertQuery) or displayerror(mysql_error());
      if($updateRes!='') displayinfo("Details Successfully updated !!!");
    }

    $prview=<<<VIEW
	<table>
         <a onClick="history.go(-1)">BACK</a><br/>
         <tr>
           <td><a href="./+prhead&subaction=ckEditor"><div>Update Disclaimer</div></a></td>
           <td><a href="./+prhead&subaction=deleteUsers"><div>Delete User in Accomodation</div></a></td>
         </tr>
        </table>

VIEW;
    if(isset($_GET['subaction'])) {
      if($_GET['subaction']=='deleteUsers') {
	$deleteUser = displayUsersRegisteredToPrDelete($this->moduleComponentId);
	return $prView.$deleteUser;
      }
      if($_GET['subaction']=='ckEditor') {
	$find=$this->getCkBody("","prhead");
	return $prview.$find;
      }
    }
    return $prview;
  }

  public static function getFileAccessPermission($pageId,$moduleComponentId,$userId, $fileName)
  {
    return getPermissions($userId, $pageId, "view");
  }

  public static function getUploadableFileProperties(&$fileTypesArray,&$maxFileSizeInBytes)
  {
    $fileTypesArray = array('jpg','jpeg','png','doc','pdf','gif','bmp','css','js','html','xml','ods','odt','oft','pps','ppt','t\
ex','tiff','txt','chm','mp3','mp2','wave','wav','mpg','ogg','mpeg','wmv','wma','wmf','rm','avi','gzip','gz','rar','bmp','psd','bz2','tar','zip','swf','fla','flv','eps','xcf','xls','exe','7z');
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
		return "This is the PR and Hospi module administration page. Options coming up soon!!!";
	}
	

}
