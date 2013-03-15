<?php
if(!defined('__PRAGYAN_CMS')) { 
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
class qaos1 implements module, fileuploadable {
  private $userId;
  private $moduleComponentId;
  private $action;
  public function getHtml($gotuid, $gotmoduleComponentId, $gotaction) {
	
    $this->userId = $gotuid;
    $this->moduleComponentId = $gotmoduleComponentId;
    $this->action = $gotaction;

    if ($this->action == "view")
      return $this->actionView();
    if ($this->action == "team")
      return $this->actionTeam();
    if ($this->action == "orgc")
      return $this->actionOrgc();
    if ($this->action == "head")
      return $this->actionHead();
    if ($this->action == "treasurer")
      return $this->actionTreasurer();
			
  }

  private function getFormSuggestions($input) {
    $mcid=$this->moduleComponentId;
    $emailQuery = "SELECT * FROM `qaos1_events` WHERE `events_name` LIKE '%{$input}%' and `page_modulecomponentid`={$mcid}";
    $emailResult = mysql_query($emailQuery);
    $suggestions = array($input);
    while($emailRow = mysql_fetch_row($emailResult)) {
      $suggestions[] = $emailRow[1];
    }
    return join($suggestions, ',');
  }




  public function actionView() {
    global $urlRequestRoot, $moduleFolder, $cmsFolder,$templateFolder,$sourceFolder,$cmsFolder,$STARTSCRIPTS;
    $scriptsFolder = "$urlRequestRoot/$cmsFolder/$templateFolder/common/scripts";
    $imagesFolder = "$urlRequestRoot/$cmsFolder/$templateFolder/common/images";
    $js=$urlRequestRoot."/".$cmsFolder."/".$moduleFolder."/qaos1/dpic.js";
    $css=$urlRequestRoot."/".$cmsFolder."/".$moduleFolder."/qaos1/jquery-ui-1.8.16.custom.css";
    $css1=$urlRequestRoot."/".$cmsFolder."/".$moduleFolder."/qaos1/styles/main.css";
    $mcid=$this->moduleComponentId;
    require_once($sourceFolder."/".$moduleFolder."/qaos1/qaos_common.php");
    require_once($sourceFolder."/upload.lib.php");
    if(isset($_GET['subaction'])&&$_GET['subaction'] == 'getsuggestions' && isset($_GET['forwhat']))   {
      echo $this->getFormSuggestions(escape($_GET['forwhat']));
      exit();
    }
    if(isset($_POST['uEventName'])) {
      if($_POST['uEventName']=="") {
	displayerror("Event Name Field is empty");
      }
      else {
	$uploadFile = upload($mcid,"qaos1",$this->userId,"uploadBill");
	$evtId = getEventIdFromName(escape($_POST['uEventName']),$mcid);
	if($evtId !=0) {
	  foreach($uploadFile as $image) {
	  	    addToBills($image,$evtId,$mcid,$this->userId,escape($_POST['evt_cluster']),escape($_POST['evt_corp']),escape($_POST['evt_bill']),escape($_POST['evt_bill_date']),escape($_POST['evt_bill_amt']),escape($_POST['evt_tin_no']));
	  }
	}
	else {
	  displayerror("Invalid Event Name.Contact QA Team");
	}
      }	    
    }
      $actionview1 =<<<AB
      <link href="{$css}" rel="stylesheet">
      <link href="{$css1}" rel="stylesheet">
      <script type="text/javascript" src="{$js}"></script>		
      <script type="text/javascript">
         $(document).ready(function() {
	     $(function() {
		 $("#evtdatepicker").datepicker();
		 $("#evt_bill_date").datepicker();
		 $("#funddatepicker").datepicker();
	       });
	     $(".forms").css({'display':'none'});
	     $("#dfundreq").css({'display':'block'});	
	     $(".buttons").css({'display':'block'});
	     $(".viewbuttons").css({'height':'25px'});
	   });
        function dispevtproc() {
	  $(".forms").css({'display':'none'});
	  $("#deventproc").css({'display':'block'});
	}
	function dispfundreq() {
	  $(".forms").css({'display':'none'});
	  $("#dfundreq").css({'display':'block'});
	}
	function dispevtform() {
	  $(".forms").css({'display':'none'});
	  $("#formevt").css({'display':'block'});
	}
	function dispfundform() {
	  $(".forms").css({'display':'none'});
	  $("#formfund").css({'display':'block'});
	}
	function dispUploadBill() {
	  $(".forms").css({'display':'none'});
	  $("#uploadBills").css({'display':'block'});
	}
     </script>
     <div id="buttonsDiv" class="viewButtonsDiv">
	 <input type="button" id="bevtproc" onclick="dispevtproc()" class="viewbuttons" value="Event procurement details"/>
	 <input type="button" id="bfundreq" onclick="dispfundreq()" class="viewbuttons" value="Fund details"/>
	 <input type="button" id="bformevt" onclick="dispevtform()" class="viewbuttons" value="Event procurement Request"/>
	 <input type="button" id="bformfund" onclick="dispfundform()" class="viewbuttons" value="Fund Request"/>
	 <input type="button" id="buploadBills" onclick="dispUploadBill()" class="viewbuttons" value="Upload Bills"/>

     </div>
AB;

      if(isset($_GET['subaction'])&&($_GET['subaction']=='evtproc')&&(isset($_POST["evtname"]))&&($_POST['evtname']!="")) {
	$isCorrect = true;
	foreach($_POST as $key => $value) {
 	  if(!(isset($_POST[$key])&&$_POST[$key]!="")) {
	    $isCorrect = false;
	    break;
	  } 
	  $_POST[$key] = addslashes($_POST[$key]);
	}
	if($isCorrect && (getEventIdFromName($_POST['evtname'],$mcid) == 0)) $isCorrect = false;
	if($isCorrect && (_date_is_valid($_POST['evtproc_time'])=="")) $isCorrect = false;
	if($isCorrect == false) displayerror("Invalid Fields");
	else {
	 $query="SELECT * FROM `qaos1_evtproc` 
                WHERE `modulecomponentid`='{$this->moduleComponentId}' AND `evtproc_Request`='{$_POST["request"]}'";
	 $query.=" AND `evtproc_Quantity`='{$_POST["qty"]}' AND `evtproc_name`='{$_POST["evtname"]}' AND `userid`='{$this->userId}' AND ";
	 $query.=" `evtproc_date`='{$_POST["evtproc_time"]}' AND `evtproc_Status`=0 AND `evtproc_reason`='{$_POST["evtprocreason"]}'";
	 $res=mysql_query($query) or displayerror(mysql_error());
	 if(!mysql_num_rows($res)) { 
	  $submit="INSERT INTO `qaos1_evtproc` (evtproc_Request,evtproc_Quantity,evtproc_name,modulecomponentid,userid,evtproc_reason,evtproc_date)
                  values ('{$_POST["request"]}','{$_POST["qty"]}','{$_POST["evtname"]}',$this->moduleComponentId,$this->userId,
                          '{$_POST["evtprocreason"]}','{$_POST["evtproc_time"]}')";  
	  mysql_query($submit) or displayerror(mysql_error());
	 }
	}
      }    
    if(isset($_GET['subaction'])&&($_GET['subaction']=='fund')&&isset($_POST["fevtname"])&&$_POST["fevtname"]!="") {
	$isCorrect = true;
	foreach($_POST as $key => $value) {
 	  if(!(isset($_POST[$key])&&$_POST[$key]!="")) {
	    $isCorrect = false;
	    break;
	  } 
	  $_POST[$key] = addslashes($_POST[$key]);
	}
	if($isCorrect && (getEventIdFromName($_POST['fevtname'],$mcid) == 0)) $isCorrect = false;
	if($isCorrect && (_date_is_valid($_POST['fundreq_time'])=="")) $isCorrect = false;
	if($isCorrect == false) displayerror("Invalid Fields");
	else {
	  $query="SELECT * FROM `qaos1_fundreq` 
                WHERE `modulecomponentid`='{$this->moduleComponentId}' AND `fundreq_Request`='{$_POST["request1"]}'AND 
                      `fundreq_Quantity`='{$_POST["qty1"]}' AND `fundreq_name`='{$_POST["fevtname"]}' AND `userid`='{$this->userId}' AND 
	              `fundreq_date`='{$_POST["fundreq_time"]}' AND `fundreq_Status`=0 AND `fundreq_Amount`='{$_POST["amt1"]}' AND 
                      `fundreq_reason`='{$_POST["fundreqreason"]}'";
	  $res=mysql_query($query) or displayerror(mysql_error());
	  if(!mysql_num_rows($res)) { 
	    $submit="INSERT INTO `qaos1_fundreq` (fundreq_Request,fundreq_Quantity,fundreq_name,fundreq_Amount,
                                                 modulecomponentid,userid,fundreq_reason,fundreq_date) 
                   VALUES ('{$_POST["request1"]}','{$_POST["qty1"]}','{$_POST["fevtname"]}','{$_POST["amt1"]}',$this->moduleComponentId,
                           $this->userId,'{$_POST["fundreqreason"]}','{$_POST["fundreq_time"]}')";
	    mysql_query($submit) or displayerror(mysql_error());
	  }
	}		
    }
    $txtBoxForDownload = displaySuggestionBox("fevtname","suggestionsBoxForDownload","view","userBox1");
    $txtBoxForUpload = displaySuggestionBox("evtname","suggestionsBox","view","userBox");

      $actionview = <<<AB
        <div class="forms" id="formevt"> 
         <form action="./+view&subaction=evtproc" onsubmit="evtproc" method="post" name="evtproc">
	   <h2>Event Procurement</h2>
	   <table>
	     <tr><td>Event</td><td>$txtBoxForUpload</td></tr>
  	     <tr><td>Item</td><td><input type="text" id="request" name="request"/></td></tr>
	     <tr><td>Quantity</td><td><input type="text" id="qty" name="qty" /></td></tr>
	     <tr><td>Reason</td><td><input type="text" id="evtprocreason" name="evtprocreason" /></td></tr>
	     <tr><td>Deadline</td><td><input id="evtdatepicker" type="text" name="evtproc_time"/></td></tr>
	     <tr><td colspan="2"><input type="submit" value="submit"></td></tr>
	   </table>
        </form>
      </div>
      <div class="forms"id="formfund">
	<form action="./+view&subaction=fund" onsubmit="evtproc" method="post" name="evtproc1">
	  <h2>Fund Request Form</h2>		   
	  <table>
	    <tr><td>Event</td><td>$txtBoxForDownload</td></tr>
  	    <tr><td>Item</td><td><input type="text" id="request1" name="request1"/></td></tr>
  	    <tr><td>Quantity:</td><td><input type="text" id="qty1" name="qty1" /></td></tr>
	    <tr><td>Amount:</td><td><input type="text" id="amt1" name="amt1" /></td></tr>
	    <tr><td>Reason</td><td><input type="text" id="fundreqreason" name="fundreqreason" /></td></tr>
	    <tr><td>Deadline</td><td><input id="funddatepicker" type="text" name="fundreq_time"/></td></tr>
 	    <tr><td colspan="2"><input type="submit" value="submit"></td></tr>
	  </table>
	</form>
      </div>
      <div class="display" id="displayevt">
AB;
      $smarttablestuff = smarttable::render(array('table_eventproc','table_fundreq'),null);
      $STARTSCRIPTS .="initSmartTable();";

      $actionview=$actionview1.$actionview;
      $hist1="SELECT * FROM qaos1_evtproc where modulecomponentid=$this->moduleComponentId AND userid=$this->userId";
      $res=mysql_query($hist1);
      $actionview.=<<<AB
        $smarttablestuff
	<div id="deventproc" class="forms">
	<h2>Event Procurement Status</h2>	       	    
	<table class="display" border="1" id="table_eventproc" width="100%">
	  <thead><tr><th>ITEM</th><th>QUANTITY</th><th>EVENT</th><th>REASON</th><th>STATUS</th><th>DEADLINE</th><th>DESCRIPTION</th></tr></thead>
AB;
      while($result=mysql_fetch_array($res)) {	
	if($result['evtproc_Status']==0)$status="Pending";
	elseif($result['evtproc_Status']==1)$status="Accepted By QA";
	elseif($result['evtproc_Status']==2) $status="Decline";
	elseif($result['evtproc_Status']==3)$status="Accepted By OC";

	$actionview.=<<<AB
	  <tr class="tr{$result['evtproc_Status']}">
  	    <td>{$result['evtproc_Request']}</td>
            <td>{$result['evtproc_Quantity']}</td>
	    <td>{$result['evtproc_name']}</td>	
	    <td>{$result['evtproc_reason']}</td>			
	    <td>{$status}</td>
	    <td>{$result['evtproc_date']}</td>			       
	    <td>{$result['evtproc_Desc']}</td>
	  </tr>
AB;
      }	 

      $actionview.=<<<AB
	 </table>
	</div>
	<div  class="forms" id="dfundreq">
	  <h2>Fund Request Status</h2>		      
	   <table class="display" border="1" id="table_fundreq" width="100%">
	    <thead><tr><th>ITEM</th><th>QUANTITY</th><th>EVENT NAME</th><th>REASON</th><th>STATUS</th><th>DEADLINE</th><th>DESCRIPTION</th></tr></thead>
AB;
      $hist2="SELECT * FROM qaos1_fundreq where modulecomponentid=$this->moduleComponentId AND userid=$this->userId";
      $res1=mysql_query($hist2);
      while($result1=mysql_fetch_array($res1)) {
	if($result1['fundreq_Status']==0) $status1="Pending";
	else if($result1['fundreq_Status']==1) $status1="Accepted by qaos";
	else if($result1['fundreq_Status']==2) $status1="Decline";
	else if($result1['fundreq_Status']==3) $status1="collect the amt from treasurer";
	$actionview.=<<<AB
	  <tr class="tr{$result1['fundreq_Status']}">
           <td>{$result1['fundreq_Request']}</td>
           <td>{$result1['fundreq_Quantity']}</td>
   	   <td>{$result1['fundreq_name']}</td>
	   <td>{$result1['fundreq_reason']}</td>
	   <td>{$status1}</td>
	   <td>{$result1['fundreq_date']}</td>
	   <td>{$result1['fundreq_Desc']}</td>
	 </tr>
AB;
      }
 
      $fileUploadForm = getMultipleFileUploadField("uploadBill","qaos1",UPLOAD_SIZE_LIMIT,'= "multiple"');
    $txtBoxForUpload = displaySuggestionBox("uEventName","uSuggestionsBox","view","userBox");
	    
      $actionview .=<<<UPLOADBILL
	</table></div>
	<div class="forms" id="uploadBills">
<form action="./+view" method="post" enctype="multipart/form-data">
      <table>
        <tr>
         <td>Event Name: </td>
	 <td>$txtBoxForUpload</td>
       </tr>
	<tr>
         <td>Event Cluster: </td>
         <td><input type="text" name="evt_cluster"/></td>
        </tr>
	<tr>
         <td>Corporation Name: </td>
         <td><input type="text" name="evt_corp"/></td>
        </tr>	<tr>
         <td>Bill No: </td>
         <td><input  type="text" name="evt_bill"/></td>
        </tr>
	<tr>
         <td>Bill Date: </td>
         <td><input type="text" id="evt_bill_date" name="evt_bill_date"/></td>
        </tr> 
	<tr>
         <td>Bill Amount: </td>
         <td><input type="text" name="evt_bill_amt"/></td>
        </tr> 
	<tr>
         <td>Tin No: </td>
         <td><input type="text" name="evt_tin_no"/></td>
        </tr> 
       <tr>
         <td>Upload File:</td>
         <td>$fileUploadForm</td>
       </tr>
       <tr>
        <td colspan="2"><input type="submit" value="submit" /></td>
       </tr>
     </table>
   </form>
	</div>
</div>
UPLOADBILL;
      return $actionview;
  }




  public function actionTeam() {
    global $urlRequestRoot,$moduleFolder,$sourceFolder,$templateFolder,$cmsFolder,$STARTSCRIPTS;
    $scriptsFolder = "$urlRequestRoot/$cmsFolder/$templateFolder/common/scripts";
    $imagesFolder = "$urlRequestRoot/$cmsFolder/$templateFolder/common/images";
    $mcid=$this->moduleComponentId;
    require_once($sourceFolder."/upload.lib.php");
    require_once($sourceFolder."/".$moduleFolder."/qaos1/qaos_common.php");
    $teamAction="";
    if(isset($_GET['subaction'])&&$_GET['subaction'] == 'getsuggestions' && isset($_GET['forwhat']))   {
      echo $this->getFormSuggestions(escape($_GET['forwhat']));
      exit();
    }
    if(isset($_POST['eventNameForDownload'])) {
      downloadAsZipFile($mcid,getEventIdFromName(escape($_POST['eventNameForDownload']),$mcid));
    }
    if(isset($_POST['eventName'])) {
      if($_POST['eventName']=="") {
	displayerror("Event Name Field is empty");
      }
      else {
	$uploadFile = upload($mcid,"qaos1",$this->userId,"uploadBill");
	$evtId = getEventIdFromName(escape($_POST['eventName']),$mcid);
	if($evtId !=0) {
	  foreach($uploadFile as $image) {
	    addToBills($image,$evtId,$mcid);
	  }
	}
	else {
	  displayerror("Invalid Event Name.Contact QA Team");
	}
      }	    
    }
    $teamAction.= displayBills($mcid);
    $fileUploadForm = getMultipleFileUploadField("uploadBill","qaos1",UPLOAD_SIZE_LIMIT,'= "multiple"');
    $txtBoxForDownload = displaySuggestionBox("eventNameForDownload","suggestionsBoxForDownload","team","userBox1");
    $txtBoxForUpload = displaySuggestionBox("eventName","suggestionsBox","team","userBox");
    $teamAction.= <<<FORMTOADD
      <form action="./+team" method="post" enctype="multipart/form-data">
      <table>
        <tr>
         <td>Event Name: </td>
	 <td>$txtBoxForUpload</td>
       </tr>
       <tr>
         <td>Upload File:</td>
         <td>$fileUploadForm</td>
       </tr>
       <tr>
        <td colspan="2"><input type="submit" value="submit" /></td>
       </tr>
     </table>
   </form>
   <form action="./+team" method="post" enctype="multipart/form-data">
     <table>
       <tr>
         <td>Event Name(Blank if u want to download all bills): </td>
         <td>$txtBoxForDownload</td>
      </tr>
      <tr><td colspan="2"><input type="submit" value="submit" /></td></tr>
    </table>
  </form>
FORMTOADD;
    return $teamAction;
  }

  public function actionOrgc() {
    global $urlRequestRoot, $moduleFolder, $cmsFolder,$templateFolder,$sourceFolder,$STARTSCRIPTS;
    require_once($sourceFolder."/".$moduleFolder."/qaos1/qaos_common.php");
    $mcid=$this->moduleComponentId;
   $orgCaction="";
    if(isset($_POST['CKEditor1'])) {
       disclaimerUpdate($mcid,"getOCAcceptance",escape($_POST['CKEditor1']));
    }
    if(isset($_POST['printthis']) && isset($_POST['printHiddenId'])) {
	if($_POST['printHiddenId']!="") {
 	 $pos = strpos($_POST['printHiddenId'],"printEvtProcBill");
	if($pos==0) 	$orgCaction=printDataForOCId($mcid,substr(escape($_POST['printHiddenId']),16));                                 
			  
			}
		
		}

    if((isset($_POST['hid']))) {
        print_r($_POST);
	$_POST["hid2"]=addslashes($_POST["hid2"]);
	$_POST["hid1"]=addslashes($_POST["hid1"]);
	$_POST["hid"]=addslashes($_POST["hid"]);
        if($_POST["hid"]<=3&&$_POST["hid"]>=0) { 
 
	 if(($_POST["hid"]!=2)||($_POST["hid1"]!=""))  {
	$query="update qaos1_evtproc set evtproc_Status = {$_POST["hid"]},evtproc_Desc ='{$_POST["hid1"]}' where evtproc_Id ='{$_POST["hid2"]}' AND modulecomponentid={$this->moduleComponentId} AND evtproc_Status=1";
        echo $query;        
	$res=mysql_query($query);		 }
		}		
	}
        $query="SELECT * FROM qaos1_evtproc WHERE modulecomponentid={$this->moduleComponentId}";
        $res=mysql_query($query);
       	$css1=$urlRequestRoot."/".$cmsFolder."/".$moduleFolder."/qaos1/styles/main.css";
	$smarttablestuff = smarttable::render(array('table_evtprocrequest','table_eventproc_head'),null);
	$STARTSCRIPTS .="initSmartTable();";
	$orgCaction.=<<<AB
	  $smarttablestuff
     	   <link href="{$css1}" rel="stylesheet">
  	    <script type="text/javascript">
     	       function qaosproc(a) {
		var k=document.getElementById("status"+a+"1");
		var k1=document.getElementById("status"+a+"2");
		if(k.checked) document.getElementById("hid"+a).value=3;
 			else if(k1.checked) document.getElementById("hid"+a).value=2;
     			else {alert("select any one of button");return false;}
     			document.getElementById("hi1d"+a).value=document.getElementById("description"+a).value;
                        $.ajax({
					type: "POST",
				  	url: "./+orgc&subaction=apEventProc",
				  	data: "hid="+$("#hid"+a).val()+"&hid1="+$("#hi1d"+a).val()+"&hid2="+$("#hi2d"+a).val()
			      		});
				
					$("#tr"+a).css({'display':'none'});      
	     				return false;
				}
 	        	   </script>
			   <script type="text/javascript">
  		 	   	   $(document).ready(function() 
	         		   	{
					$(".forms").css({'display':'none'});
					$(".buttons").css({'display':'block'});
					$("#bhead_evtproc").click(function()
						{
							dispevtproc();
						});
								
					$("#bformevt").click(function(){
						dispevtform();
					});
					$("#bdisplaydisclaimer").click(function()
						{
						  $(".forms").css({'display':'none'});
						  $("#displayBillDisclaimer").css({'display':'block'});

						});

     					function dispevtproc()
       		 		 	 {
						$(".forms").css({'display':'none'});
						$("#dheadeventproc").css({'display':'block'});
					 }
					function dispevtform()
				    	 {
						$(".forms").css({'display':'none'});
						$("#formevt").css({'display':'block'});
					 }
					     $("#dtreasurerfundreq").css({'width':'100%'});
					     $(".viewbuttons").css({'height':'25px'});
					     $(".viewbuttonsdiv").css({'width':'100%'});
					

					$("#dheadeventproc").css({'width':'100%'});
				$(".viewbuttons").css({'height':'25px'});

					});	 
       			</script>
			<div id="buttonsDiv" class="viewButtonsDiv">
      			<input type="button" id="bhead_evtproc" value="Event procurement details" class="viewbuttons" />
      			<input type="button" id="bformevt" value="Event Procurement" class="viewbuttons" />
                        <input type="button" id="bdisplaydisclaimer" class="viewbuttons" value="Disclaimer"/>            
 		</div>
			<div id="dheadeventproc" class="forms">
     				<h2>Event Procurement Request</h2>

      			<table class="qaostables display" id="table_evtprocrequest" width="100%" border="1">
				<thead><tr>
					<th>EVENT NAME</th>
					<th>ITEM</th>
					<th>QUANTITY</th>
					<th>REASON</th>
					<th>STATUS</th>
 					<th>DEADLINE</th>
					<th>ADDED BY</th>
					<th>DESCRIPTION</th>
					<th>SUBMIT</th>
				</tr></thead>
AB;
			while($result=mysql_fetch_array($res))
			{
				$event=$result['evtproc_Id'];
				$userName=getUserName($result['userid']);
				if($result['evtproc_Status']!=1) $status=0;
				else $status=1;
				if($status==1)
	  			$orgCaction.=<<<AB
	        			      <tr id="tr{$event}" >
	 			     	  <td>{$result["evtproc_name"]}</td>
	 					  <td>{$result['evtproc_Request']}</td>
	 					  <td>{$result['evtproc_Quantity']}</td>
						  <td>{$result['evtproc_reason']}</td>
						  <td>
							<input type="radio" name="status{$event}" id="status{$event}1" value="3">ACCEPT<br/ >
	 					  	<input type="radio" name="status{$event}" id="status{$event}2" value="2">Decline<br/>
						  </td>
						  <td>{$result['evtproc_date']}</td>
						   <td>$userName</td>				   
						  <td><textarea id="description{$event}"></textarea></td>
						  <td>
							<form action="./+orgc&subaction=apEventProc" method="post" onsubmit="return qaosproc({$event})">
							      <input type="hidden" value="" id="hid{$event}" name="hid">
							      <input type="hidden" value="{$event}" id="hi2d{$event}" name="hid2">
							      <input type="hidden" value="" id="hi1d{$event}" name="hid1">
							      <input type="submit" value="submit">
							</form>
						   </td>
					     </tr>
AB;
		        }
		     
		     $orgCaction.=<<<AB
		     	   </table>
				   </div>
				<div class="headdisplay" id="headdisplayevt">
AB;

	$hist1="SELECT *FROM qaos1_evtproc WHERE modulecomponentid={$this->moduleComponentId}";
        $res=mysql_query($hist1);
	 $orgCaction.=<<<AB
	 	    <div id="formevt" class="forms">
					<h2>Event Procurement Status</h2>	       	    	
	       	   <table id="table_eventproc_head" class="display"border="1" width="100%" class="qaostables">
	       		    <thead><tr>
						<th>ITEM</th>
						<th>QUANTITY</th>
						<th>EVENT NAME</th>
						<th>REASON</th>				  		
 				  		<th>STATUS</th>
		                                <th>ADDED BY</th>
				  		<th>DEADLINE</th>
				  		<th>DESCRIPTION</th>
                                                <th>PRINT BILL</th>
					 </tr></thead>
AB;
	while($result=mysql_fetch_array($res))
		{	
		  $userName=getUserName($result['userid']);
			if($result['evtproc_Status']==0) $status="Pending";
		        else 
			if($result['evtproc_Status']==1)$status="Accepted By QA";
		        else
			if($result['evtproc_Status']==2) $status="Decline";
		        else
			if($result['evtproc_Status']==3) $status="Accepted By OC";
			
			$orgCaction.=<<<AB
				   <tr class="tr{$result['evtproc_Status']}">
				       <td>{$result['evtproc_Request']}</td>
				       <td>{$result['evtproc_Quantity']}</td>
				        <td>{$result['evtproc_name']}</td>
				       <td>{$result['evtproc_reason']}</td>				       
				       <td>{$status}</td>
					<td>{$userName}</td>		
				       <td>{$result['evtproc_date']}</td>
				       <td>{$result['evtproc_Desc']}</td>
					<td>
					<form method="post" action="./+orgc">
                                           <input type="submit" name="printthis" value="PRINT"/>
                   		           <input type="hidden" name="printHiddenId" value="printEvtProcBill{$result['evtproc_Id']}" />					</form>
                                       </td>
			 	   </tr>
AB;
	        }	 
          $contentDisclaimer=getDisclaimer("getOCAcceptance", $mcid);
          $displayDisclaimerBill=getCkBody($contentDisclaimer,"orgc");
          
          $orgCaction.=<<<AB
          </table></div>
	  <div class="forms" id="displayBillDisclaimer">
				  $displayDisclaimerBill
				  </div>


AB;
	
return $orgCaction;

  }
  public function actionHead() {
    global $urlRequestRoot, $moduleFolder, $cmsFolder,$templateFolder,$sourceFolder,$STARTSCRIPTS;


    require_once($sourceFolder."/".$moduleFolder."/qaos1/qaos_common.php");
    require_once($sourceFolder."/upload.lib.php");
    require_once($sourceFolder."/".$moduleFolder."/qaos1/excel.php");
    $mcid=$this->moduleComponentId;
    if(isset($_POST['downloadFormatExcel'])) {
      displayEventFormatExcel();
    }
    if(isset($_FILES['fileUploadField']['name'])) {
      $date = date_create();
      $timeStamp = date_timestamp_get($date);
      $tempVar=$sourceFolder."/uploads/temp/".$timeStamp.$_FILES['fileUploadField']['name'][0];
      move_uploaded_file($_FILES["fileUploadField"]["tmp_name"][0],$tempVar);
      $excelData = readExcelSheet($tempVar);
      $success = 1;
      for($i=2;$i<=count($excelData);$i++) {
	if($excelData[$i][1] == NULL) continue;
	$checkIfExistQuery = "SELECT * FROM `qaos1_events` WHERE `events_name`='{$excelData[$i][1]}' AND `page_modulecomponentid`={$mcid}";
	$checkIfExistRes = mysql_query($checkIfExistQuery) or displayerror(mysql_error());
	if(mysql_num_rows($checkIfExistRes)) continue;
	$insertIntoEventTableQuery = "INSERT IGNORE INTO `qaos1_events` (events_name,page_modulecomponentid) VALUES ('{$excelData[$i][1]}',{$mcid})";  
	$res = mysql_query($insertIntoEventTableQuery) or displayerror(mysql_error());
	if($res == "") $success=0; 
      }
      if(!$success) displayerror("Datas are not inserted");
    }
    if(isset($_POST['uploadEventName'])) {
	     
	   }
	   if((isset($_POST['hid'])))
		{
			$_POST["hid2"]=addslashes($_POST["hid2"]);
			$_POST["hid1"]=addslashes($_POST["hid1"]);
			$_POST["hid"]=addslashes($_POST["hid"]);
			if($_POST["hid"]<3&&$_POST["hid"]>=0)
				{ 
				if(($_POST["hid"]!=2)||($_POST["hid1"]!=""))  {
			$query="update qaos1_evtproc set evtproc_Status = '{$_POST["hid"]}',evtproc_Desc ='{$_POST["hid1"]}' where evtproc_Id ='{$_POST["hid2"]}' AND modulecomponentid={$this->moduleComponentId}";
			$res=mysql_query($query);		 }
				}		
		}
		if(isset($_GET['subaction'])&&($_GET['subaction']=="apFundReq")&&(isset($_POST['qhid'])))
		{
			$_POST["qhid2"]=addslashes($_POST["qhid2"]);
			$_POST["qhid1"]=addslashes($_POST["qhid1"]);
			$_POST["qhid"]=addslashes($_POST["qhid"]);
			if($_POST["qhid"]<3&&$_POST["qhid"]>=0)
				{ 
				if(($_POST["qhid"]!=2)||($_POST["qhid1"]!=""))  {

			$query="update qaos1_fundreq set fundreq_Status = '{$_POST["qhid"]}',fundreq_Desc ='{$_POST["qhid1"]}' where fundreq_Id ='{$_POST["qhid2"]}' AND modulecomponentid={$this->moduleComponentId}";
	       		$res=mysql_query($query);}
					}
		}
                $query="SELECT * FROM qaos1_evtproc WHERE modulecomponentid={$this->moduleComponentId}";
		$res=mysql_query($query);
       	  	$query1="SELECT * FROM qaos1_fundreq WHERE modulecomponentid={$this->moduleComponentId}";
        	$res1=mysql_query($query1);
        $css1=$urlRequestRoot."/".$cmsFolder."/".$moduleFolder."/qaos1/styles/main.css";

	$smarttablestuff = smarttable::render(array('table_evtprocrequest','table_fundreqform','table_eventproc_head','table_funreq_head'),null);
	$STARTSCRIPTS .="initSmartTable();";
            
		$headaction=<<<AB
		  $smarttablestuff
				<link href="{$css1}" rel="stylesheet">

	     	  	    <script type="text/javascript">
	     	     	       function qaosproc(a)
                       		{
					var k=document.getElementById("status"+a+"1");
		     			var k1=document.getElementById("status"+a+"2");
		     			if(k.checked) document.getElementById("hid"+a).value=1;
		     			else if(k1.checked) document.getElementById("hid"+a).value=2;
		     			else {alert("select any one of button");return false;}
		     			document.getElementById("hi1d"+a).value=document.getElementById("description"+a).value;
				
					$.ajax({
					type: "POST",
				  	url: "./+head&subaction=apEventProc",
				  	data: "hid="+$("#hid"+a).val()+"&hid1="+$("#hi1d"+a).val()+"&hid2="+$("#hi2d"+a).val()
			      		});
				
					$("#tr"+a).css({'display':'none'});      
	     				return false;
				}
			       function qaosfund(a)
       		       		{
					var k=document.getElementById("qstatus"+a+"1");
       					var k1=document.getElementById("qstatus"+a+"2");
      					if(k.checked) document.getElementById("qhid"+a).value=1;
       					else if(k1.checked) document.getElementById("qhid"+a).value=2;
      					else {alert("select any one of button");return false;}
		       			document.getElementById("qhi1d"+a).value=document.getElementById("qdescription"+a).value;
					
					$.ajax({
					type: "POST",
				  	url: "./+head&subaction=apFundReq",
			  		data: "qhid="+$("#qhid"+a).val()+"&qhid1="+$("#qhi1d"+a).val()+"&qhid2="+$("#qhi2d"+a).val()
			      		});
					
					$("#trf"+a).css({'display':'none'});      
	     				return false;
			       }
 	        	   </script>
			   <script type="text/javascript">
  		 	   	   $(document).ready(function() 
	         		   	{
					$(".forms").css({'display':'none'});
					$(".buttons").css({'display':'block'});
					$("#dheadfundreq").css({'display':'block'});
					$("#bhead_evtproc").click(function()
						{
							dispevtproc();
						});
					$("#bhead_fundreq").click(function()
						{
							dispfundreq();
						});
								
					$("#bformevt").click(function(){
						dispevtform();
					});
	            		       $("#bformfund").click(function(){
						dispfundform();
				 		});
	            		       $("#bhead_evtUpload").click(function(){
					   dispEventUploadForm();
					 });

     					function dispevtproc()
       		 		 	 {
						$(".forms").css({'display':'none'});
						$("#dheadeventproc").css({'display':'block'});
					 }
					function dispfundreq()
		 		 	 {
						$(".forms").css({'display':'none'});
						$("#dheadfundreq").css({'display':'block'});
			       		 }
					function dispevtform()
				    	 {
						$(".forms").css({'display':'none'});
						$("#formevt").css({'display':'block'});
					 }
			            	 function dispfundform()
				    	  {
						$(".forms").css({'display':'none'});
						$("#formfund").css({'display':'block'});
					  }
					 function dispEventUploadForm() 
					 {
					   $(".forms").css({'display':'none'});
					   $("#formEvtName").css({'display':'block'});
					 }
					$("#dheadeventproc").css({'width':'100%'});
					$("#dheadfundreq").css({'width':'100%'});
				$(".viewbuttons").css({'height':'25px'});

					});	 
       			</script>
			<div id="buttonsDiv" class="viewButtonsDiv">
	   		<input type="button" id="bformevt" class="viewbuttons" value="Event procurement "/>
	    		<input type="button" id="bformfund" value="Fund" class="viewbuttons" />
      			<input type="button" id="bhead_evtproc" value="Event procurement details" class="viewbuttons" />
      			<input type="button" id="bhead_fundreq" value="Fund details" class="viewbuttons" />
			<input type="button" id="bhead_evtUpload" value="Upload Event Name" class="viewbuttons" />
    
      		</div>
			<div id="formevt" class="forms">
     				<h2>Event Procurement Request</h2>

      			<table class="qaostables display" id="table_evtprocrequest" width="100%" border="1">
				<thead><tr>
					<th>EVENT NAME</th>
					<th>ITEM</th>
					<th>QUANTITY</th>
					<th>REASON</th>
					<th>STATUS</th>
 					<th>DEADLINE</th>
					<th>ADDED BY</th>
					<th>DESCRIPTION</th>
					<th>SUBMIT</th>
				</tr></thead>
AB;
			while($result=mysql_fetch_array($res))
			{
				$event=$result['evtproc_Id'];
				$userName=getUserName($result['userid']);
				if($result['evtproc_Status']==0) $status=0;
				else $status=1;
				if($status==0)
	  			$headaction.=<<<AB
	        			      <tr id="tr{$event}" >
	 			     	  <td>{$result["evtproc_name"]}</td>
	 					  <td>{$result['evtproc_Request']}</td>
	 					  <td>{$result['evtproc_Quantity']}</td>
						  <td>{$result['evtproc_reason']}</td>
						  <td>
							<input type="radio" name="status{$event}" id="status{$event}1" value="1">ACCEPT<br/ >
	 					  	<input type="radio" name="status{$event}" id="status{$event}2" value="2">Decline<br/>
						  </td>
						  <td>{$result['evtproc_date']}</td>
						   <td>$userName</td>				   
						  <td><textarea id="description{$event}"></textarea></td>
						  <td>
							<form action="./+head&subaction=apEventProc" method="post" onsubmit="return qaosproc({$event})">
							      <input type="hidden" value="" id="hid{$event}" name="hid">
							      <input type="hidden" value="{$event}" id="hi2d{$event}" name="hid2">
							      <input type="hidden" value="" id="hi1d{$event}" name="hid1">
							      <input type="submit" value="submit">
							</form>
						   </td>
					     </tr>
AB;
		        }
		     
		     $headaction.=<<<AB
		     	   </table>
				   </div>
				   <div id="formfund" class="forms">
		   		<h2>Fund request</h2>
				   	<table class="qaostables display" id="table_fundreqform" border="1" width="100%">
					 <thead>   <tr>
						<th>EVENT NAME</th>
						<th>ITEM</th>
						<th>QUANTITY</th>
						<th>AMOUNT</th>
						<th>REASON</th>
						<th>STATUS</th>
						<th>DEADLINE</th>
		                                <th>ADDED BY</th>
						<th>DESCRIPTION</th>
		                                <th>SUBMIT</th>
					    </tr></thead>
AB;
		     while($result1=mysql_fetch_array($res1))
				{
					$event1=$result1['fundreq_Id'];
					$userName=getUserName($result1['userid']);
	    				if($result1['fundreq_Status']==0) $status1=0;
					else $status1=1;
					if($status1==0)
	 				$headaction.=<<<AB
	     					      <tr id="trf{$event1}">
	   					     <td>{$result1["fundreq_name"]}</td>
	    						  <td>{$result1['fundreq_Request']}</td>
	    						  <td>{$result1['fundreq_Quantity']}</td>
	   						  <td>{$result1["fundreq_Amount"]}</td>
	   						  <td>{$result1["fundreq_reason"]}</td>
	   						  <td>
								<input type="radio" name="qstatus{$event1}" id="qstatus{$event1}1" value="1">ACCEPT<br/ >
	  							<input type="radio" name="qstatus{$event1}" id="qstatus{$event1}2" value="2">Decline<br/>
							  </td>
								  <td>{$result1["fundreq_date"]}</td>
  							          <td>$userName</td>		
	   						  <td><textarea id="qdescription{$event1}"></textarea></td>
	  						  <td>
								<form action="./+head&&subaction=apFundReq" method="post" onsubmit="return qaosfund({$event1})">
							  	       <input type="hidden" value="" id="qhid{$event1}" name="qhid">
	 							       <input type="hidden" value="{$event1}" id="qhi2d{$event1}" name="qhid2">
	 							       <input type="hidden" value=""     id="qhi1d{$event1}" name="qhid1">
	 							       <input type="submit" value="submit">
							  	</form>
							  </td>
						       </tr>
AB;
		                 }
				 $headaction.=<<<AB
						</table>
					 	</div>
						<div class="headdisplay" id="headdisplayevt">
AB;

	$hist1="SELECT *FROM qaos1_evtproc WHERE modulecomponentid={$this->moduleComponentId}";
        $res=mysql_query($hist1);
	 $headaction.=<<<AB
	 	    <div id="dheadeventproc" class="forms">
					<h2>Event Procurement Status</h2>	       	    	
	       	   <table id="table_eventproc_head" class="display"border="1" width="100%" class="qaostables">
	       		    <thead><tr>
						<th>ITEM</th>
						<th>QUANTITY</th>
						<th>EVENT NAME</th>
						<th>REASON</th>				  		
 				  		<th>STATUS</th>
		                                <th>ADDED BY</th>
				  		<th>DEADLINE</th>
				  		<th>DESCRIPTION</th>
					 </tr></thead>
AB;
	while($result=mysql_fetch_array($res))
		{	
		  $userName=getUserName($result['userid']);
			if($result['evtproc_Status']==0) $status="Pending";
		        else 
			if($result['evtproc_Status']==1)$status="Accepted By QA";
		        else
			if($result['evtproc_Status']==2) $status="Decline";
		        else
			if($result['evtproc_Status']==3) $status="Accepted By OC";
			
			$headaction.=<<<AB
				   <tr class="tr{$result['evtproc_Status']}">
				       <td>{$result['evtproc_Request']}</td>
				       <td>{$result['evtproc_Quantity']}</td>
				        <td>{$result['evtproc_name']}</td>
				       <td>{$result['evtproc_reason']}</td>				       
				       <td>{$status}</td>
					<td>{$userName}</td>		
				       <td>{$result['evtproc_date']}</td>
				       <td>{$result['evtproc_Desc']}</td>
			 	   </tr>
AB;
	        }	 
          $headaction.=<<<AB
          </table></div>
			<div  class="forms" id="dheadfundreq">
			<h2>Fund Request Status</h2>
		      <table id="table_funreq_head" class="display"border="1" width="100%" class="qaostables">
			   <thead>
			    <tr>
				     <th>ITEM</th>
				     <th>QUANTITY</th>
				     <th>EVENT NAME</th>
				     <th>Reason</th>
				     <th>STATUS</th>
                                     <th>ADDED BY</th>
				     <th>DEADLINE</th>
				     <th>DESCRIPTION</th>
 			     </tr></thead>
AB;
	
	$hist2="SELECT *  FROM qaos1_fundreq WHERE modulecomponentid={$this->moduleComponentId}";
       	$res1=mysql_query($hist2);

     		 while($result1=mysql_fetch_array($res1))
		 {
		   $userName=getUserName($result1['userid']);
			if($result1['fundreq_Status']==0) $status1="Pending";
	 		else if($result1['fundreq_Status']==1) $status1="Accepted by QA";
			else if($result1['fundreq_Status']==2) $status1="Decline";
			else if($result1['fundreq_Status']==3) $status1="collect the amt from treasurer";
			$headaction.=<<<AB
				<tr class="tr{$result1['fundreq_Status']}">
        				<td>{$result1['fundreq_Request']}</td>
					<td>{$result1['fundreq_Quantity']}</td> 
					<td>{$result1['fundreq_name']}</td> 
					<td>{$result1['fundreq_reason']}</td> 
					<td>{$status1}</td>
					<td>{$userName}</td>		
					<td>{$result1['fundreq_date']}</td>
					<td>{$result1['fundreq_Desc']}</td>
				</tr>
AB;
		}
		 $uploadEventName=getFileUploadForm($this->moduleComponentId,"qaos1",'./+head',UPLOAD_SIZE_LIMIT,1);		 
 	$headaction.= <<<AB
	  </table></div>
	  <div  class="forms" id="formEvtName">
	         <h2>Upload Event Details</h2>
	   <form action="./+head" method="post">
	      <input type="submit" name="downloadFormatExcel" value="Download Event Sample Format"/>
	   </form>
	     $uploadEventName
          
	   </div>



AB;
	
return $headaction;
       }




	public function actionTreasurer()
       	       {
		 global $urlRequestRoot, $moduleFolder, $cmsFolder,$templateFolder,$sourceFolder,$STARTSCRIPTS;
		 require_once($sourceFolder."/".$moduleFolder."/qaos1/qaos_common.php");
		 $mcid=$this->moduleComponentId;
		 $treasureraction="";		
		 if(isset($_POST['CKEditor1'])) {
		   disclaimerUpdate($mcid,"getTreasurerAcceptance",escape($_POST['CKEditor1']));
		 }
		 if(isset($_GET['subaction'])&&$_GET['subaction'] == 'getsuggestions' && isset($_GET['forwhat']))   {
		   echo $this->getFormSuggestions(escape($_GET['forwhat']));
		   exit();
		 }
		 if(isset($_POST['eventNameForDownload'])) {
		   downloadAsZipFile($mcid,getEventIdFromName(escape($_POST['eventNameForDownload']),$mcid));
		 }
		if(isset($_POST['printthis']) && isset($_POST['printHiddenId'])) {
			if($_POST['printHiddenId']!="") {
 		          $pos = strpos($_POST['printHiddenId'],"printTreasurerBill");
			  if($pos==0) $treasureraction=printDataForTreasurerId($mcid,substr(escape($_POST['printHiddenId']),18));                                 
			  
			}
		
		}
		if(isset($_POST['qhid'])&&isset($_GET['subaction'])&&($_GET['subaction']=="finalApprove"))
			{
			$_POST["qhid2"]=addslashes($_POST["qhid2"]);
			$_POST["qhid1"]=addslashes($_POST["qhid1"]);
			$_POST["qhid"]=addslashes($_POST["qhid"]);
			if($_POST["qhid"]<4&&$_POST["qhid"]>=0)
				{ 
				if(($_POST["qhid"]!=2)||($_POST["qhid1"]!=""))  {
			$query="update qaos1_fundreq set fundreq_Status = '{$_POST["qhid"]}',fundreq_Desc ='{$_POST["qhid1"]}' where fundreq_Id ='{$_POST["qhid2"]}' AND modulecomponentid={$this->moduleComponentId}";
			$res=mysql_query($query);}}
			}
        	$query1="SELECT * FROM qaos1_fundreq WHERE modulecomponentid={$this->moduleComponentId}";
         	$res1=mysql_query($query1);
		$css1=$urlRequestRoot."/".$cmsFolder."/".$moduleFolder."/qaos1/styles/main.css";
		$smarttablestuff = smarttable::render(array('table_formstatus','table_funreq_treasurer','filestable'),null);
		$STARTSCRIPTS .="initSmartTable();";

   	     	$treasureraction.=<<<AB
		$smarttablestuff
			<link href="{$css1}" rel="stylesheet">
 			     <script type="text/javascript">
	     		     	     function qaosfund(a)
            			     	      {

							var k=document.getElementById("qstatus"+a+"1");
             						var k1=document.getElementById("qstatus"+a+"2");
	     						if(k.checked) document.getElementById("qhid"+a).value=3;
            						else if(k1.checked) document.getElementById("qhid"+a).value=2;
             						else {alert("select any one of button");return false;}
							document.getElementById("1qhid"+a).value=document.getElementById("qdescription"+a).value;
							$.ajax({
								type: "POST",
				  				url: "./+treasurer&subaction=finalApprove",
				  	data: "qhid="+$("#qhid"+a).val()+"&qhid1="+$("#1qhid"+a).val()+"&qhid2="+$("#2qhid"+a).val()      	
					      });
							$("#trt"+a).css({'display':'none'});      
	     						return false;
					     }
	        	     </script>
			     <script type="text/javascript">
  		 	   	   $(document).ready(function() 
	         		   	{
				
					$(".forms").css({'display':'none'});
					$(".buttons").css({'display':'block'});
						$("#dtreasurerfundreq").css({'display':'block'});
					$("#btreasurer_fundreq").click(function()
						{
							dispfundreq();
						});
			             	$("#bformfund").click(function()
						{
						dispfundform();
				 		});
			             	$("#bviewbills").click(function()
						{
						dispbills();
				 		});
					$("#bdisplaydisclaimer").click(function()
						{
						  $(".forms").css({'display':'none'});
						  $("#displayBillDisclaimer").css({'display':'block'});

						});
					function dispfundreq()
		 		 		 {
							$(".forms").css({'display':'none'});
							$("#dtreasurerfundreq").css({'display':'block'});
						}
					function dispfundform()
				    	     {
						$(".forms").css({'display':'none'});
						$("#formfund").css({'display':'block'});
					     }
					function dispbills()
				    	     {
						$(".forms").css({'display':'none'});
						$("#dviewbills").css({'display':'block'});
					     }
					     $("#dtreasurerfundreq").css({'width':'100%'});
					     $(".viewbuttons").css({'height':'25px'});
					     $(".viewbuttonsdiv").css({'width':'100%'});
					});
					</script>		
					<div id="buttonsDiv" class="buttonsClass">			
					<input type="button" id="bformfund" class="viewbuttons" value="Fund"/>					
					<input type="button" id="btreasurer_fundreq" class="viewbuttons" value="Fund Status"/>
					<input type="button" id="bviewbills" class="viewbuttons" value="Bills"/>       				
					<input type="button" id="bdisplaydisclaimer" class="viewbuttons" value="Disclaimer"/>       				
					</div>					
					<div id="formfund" class="forms">
					<h2>Form status</h2>					
					<table class="display" id="table_formstatus" border="1" width="100%" >
					<thead>	
						<tr>
							<th>EVENT NAME</th>
							<th>ITEM</th>
							<th>QUANTITY</th>
							<th>AMOUNT</th>
							<th>REASON</th>
							<th>STATUS</th>
 							<th>DEADLINE</th>
					                <th>ADDED BY</th>
							<th>DESCRIPTION</th>
					                <th>SUBMIT</th>

						</tr></thead>
AB;
		while($result1=mysql_fetch_array($res1))
		     			{
					  $userName=getUserName($result1['userid']);
						$event1=$result1['fundreq_Id'];
						if($result1['fundreq_Status']==1) $status1=3;
						else $status1=1;
						if($status1==3)
 						$treasureraction.=<<<AB
      					   			   <tr id="trt{$event1}">
   					       			       <td>{$result1["fundreq_name"]}</td>
   					       			       <td>{$result1['fundreq_Request']}</td>
			   					       <td>{$result1['fundreq_Quantity']}</td>
   					      			       <td>Rs.{$result1["fundreq_Amount"]}</td>
				      			       <td>{$result1["fundreq_reason"]}</td>
   										 <td>
												<input type="radio" name="qstatus{$event1}" id="qstatus{$event1}1" value="1">ACCEPT<br/ >
   						    				<input type="radio" name="qstatus{$event1}" id="qstatus{$event1}2" value="2">Decline<br/>
								       	</td>
								       <td>{$result1['fundreq_date']}</td>
													 <td>{$userName}</td>				 
  					       			       <td><textarea id="qdescription{$event1}"></textarea></td>
   					       			       <td>
								       <form action="./+treasurer&subaction=finalApprove" method="post" onsubmit=" return qaosfund({$event1})">
   							      	       	     <input type="hidden" value="" id="qhid{$event1}" name="qhid">
   							      		     <input type="hidden" value="{$event1}" id="2qhid{$event1}" name="qhid2">
   							      		     <input type="hidden" value=""     id="1qhid{$event1}" name="qhid1">
 							      		     <input type="submit" value="submit">
	 							       </form>
    								       </td>
     								   </tr>
AB;
      				}
				$treasureraction.=<<<AB
						</table>
						</div>
					<div  class="forms" id="dtreasurerfundreq">
			       <h2>Fund Request Status</h2>
			    			      	
 
		      			<table id="table_funreq_treasurer" class="display"  border="1" width="100%">
			    		      <thead> <tr>
							<th>ITEM</th>
				     			<th>QUANTITY</th>
				     			<th>EVENT NAME</th>
				     			<th>REASON</th>
				     			<th>STATUS</th>
					                <th>ADDED BY</th>
				     			<th>DEADLINE</th>
				     			<th>DESCRIPTION</th>
				     			<th>PRINT BILL</th>
 			     			</tr></thead>
AB;
				$hist2="SELECT *  FROM qaos1_fundreq WHERE modulecomponentid={$this->moduleComponentId}";
       				$res1=mysql_query($hist2);
				while($result1=mysql_fetch_array($res1))
		 		{
				  $userName = getUserName($result1['userid']);
				  if($result1['fundreq_Status']==0) $status1="Pending";
				  else if($result1['fundreq_Status']==1) $status1="Accepted by QA";
				  else if($result1['fundreq_Status']==2) $status1="Decline";
				  else if($result1['fundreq_Status']==3) $status1="collect the amt from treasurer";
					$treasureraction.=<<<AB
						      <tr class="tr{$result1['fundreq_Status']}">
					                  <td>{$result1['fundreq_Request']}</td>
							  <td>{$result1['fundreq_Quantity']}</td> 
							  <td>{$result1['fundreq_name']}</td> 
							  <td>{$result1['fundreq_reason']}</td> 
							  <td>{$status1}</td>
							  <td>{$userName}</td>
							  <td>{$result1['fundreq_date']}</td> 
  						          <td>{$result1['fundreq_Desc']}</td>
							  <td>
								<form method="post" action="./+treasurer">
                                                                    <input type="submit" name="printthis" value="PRINT"/>
								    <input type="hidden" name="printHiddenId" value="printTreasurerBill{$result1['fundreq_Id']}" />	
								</form>
                                                          </td> 	
						      </tr>
AB;
				}
			$bills = displayBills($mcid,1);
			$txtBoxForDownload = displaySuggestionBox("eventNameForDownload","suggestionsBoxForDownload","treasurer","userBox1");
			$contentDisclaimer=getDisclaimer("getTreasurerAcceptance", $mcid);
			$displayDisclaimerBill=getCkBody($contentDisclaimer,"treasurer");
				$treasureraction.=<<<BILLS
				  </table></div>
				  <div class='forms' id='dviewbills'>
				  $bills
				  <form action="./+treasurer" method="post" enctype="multipart/form-data">
				  <table>
				  <tr>
				  <td>Event Name(Blank if u want to download all bills): </td>
				  <td>$txtBoxForDownload</td>
				  </tr>
				  <tr><td colspan="2"><input type="submit" value="submit" /></td></tr>
				  </table>
				  </form>
				  </div>
				  <div class="forms" id="displayBillDisclaimer">
				  $displayDisclaimerBill
				  </div>
									
BILLS;
				return $treasureraction;
       		}



       public static function getFileAccessPermission($pageId,$moduleComponentId,$userId, $fileName) 
       	       {
			return getPermissions($userId, $pageId, "view");
	      	}

	 public static function getUploadableFileProperties(&$fileTypesArray,&$maxFileSizeInBytes) 
	 	{
			$fileTypesArray = array('jpg','jpeg','png','doc','pdf','gif','bmp','css','js','html','xml','ods','odt','oft','pps','ppt','tex','tiff','txt','chm','mp3','mp2','wave','wav','mpg','ogg','mpeg','wmv','wma','wmf','rm','avi','gzip','gz','rar','bmp','psd','bz2','tar','zip','swf','fla','flv','eps','xcf','xls','exe','7z');
		     	$maxFileSizeInBytes = 30*1024*1024;
		}

	public function createModule($compId) {
		return;
	}
	public function deleteModule($moduleComponentId) {
		/* Remove the indexing from sphider // Abhishek */
		return true;
		

	}
	public function copyModule($moduleComponentId, $newId) 
	       {
		return true;
		}

}

