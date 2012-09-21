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
		if ($this->action == "head")
			return $this->actionHead();
		if ($this->action == "treasurer")
			return $this->actionTreasurer();
			
	}





	public function actionView()
	       {
	        global $urlRequestRoot, $moduleFolder, $cmsFolder,$templateFolder,$sourceFolder;
                $js=$urlRequestRoot."/".$cmsFolder."/".$moduleFolder."/qaos1/dpic.js";
                $css=$urlRequestRoot."/".$cmsFolder."/".$moduleFolder."/qaos1/jquery-ui-1.8.16.custom.css";
            	$css1=$urlRequestRoot."/".$cmsFolder."/".$moduleFolder."/qaos1/styles/main.css";
            
		$actionview1 =<<<AB
			<link href="{$css}" rel="stylesheet">
			<link href="{$css1}" rel="stylesheet">
			<script type="text/javascript" src="{$js}"></script>		
			<script type="text/javascript">
			      $(document).ready(function() 
		                  {
					 $(function() {
 						$("#evtdatepicker").datepicker();
  						});
					 $(function() {
 						$("#funddatepicker").datepicker();
  						});
						
		    		  	$(".forms").css({'display':'none'});
					$("#dfundreq").css({'display':'block'});	
					$(".buttons").css({'display':'block'});
					$("#bevtproc").click(function()
						{
						dispevtproc();
						});
					$("#bfundreq").click(function()
						{
						dispfundreq();
						});
					$("#bformevt").click(function()
						{
						dispevtform();
						});
    					$("#bformfund").click(function()
						{
						dispfundform();
	   					});
     					function dispevtproc()
       		 		 		 {
						$(".forms").css({'display':'none'});
						$("#deventproc").css({'display':'block'});
						}
					function dispfundreq()
		 		 		 {
						$(".forms").css({'display':'none'});
						$("#dfundreq").css({'display':'block'});
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
					$(".viewbuttons").css({'height':'25px'});
		    			});								     
			</script>
			<head><script type="text/css" rel="stylesheet"></script></head>


			
			<div id="buttonsDiv" class="viewButtonsDiv">
			    <input type="button" id="bevtproc" class="viewbuttons" value="Event procurement details"/>
      			    <input type="button" id="bfundreq" class="viewbuttons" value="Fund details"/>
      			    <input type="button" id="bformevt" class="viewbuttons" value="Event procurement Request"/>
      			    <input type="button" id="bformfund" class="viewbuttons" value="Fund Request"/>
			</div>
AB;
	
			if(isset($_GET['subaction'])&&($_GET['subaction']=='evtproc'))
			   {
			   if(isset($_POST["evtname"]))
			      {
			      $_POST["request"]=addslashes($_POST["request"]);
			      $_POST["qty"]=addslashes($_POST["qty"]);
			      $_POST["evtname"]=addslashes($_POST["evtname"]);
			      $_POST["evtprocreason"]=addslashes($_POST["evtprocreason"]);
			      $_POST["evtproc_time"]=addslashes($_POST["evtproc_time"]);
		      $query="SELECT * FROM `qaos1_evtproc` WHERE `modulecomponentid`='{$this->moduleComponentId}' AND `evtproc_Request`='{$_POST["request"]}'";
			      $query.=" AND `evtproc_Quantity`='{$_POST["qty"]}' AND `evtproc_name`='{$_POST["evtname"]}' AND `userid`='{$this->userId}' AND ";
			      $query.=" `evtproc_date`='{$_POST["evtproc_time"]}' AND `evtproc_Status`=0 AND `evtproc_reason`='{$_POST["evtprocreason"]}'";
			      $res=mysql_query($query);
			      if(!mysql_num_rows($res))
			      { 
			     $submit="INSERT INTO `qaos1_evtproc` (evtproc_Request,evtproc_Quantity,evtproc_name,modulecomponentid,userid,evtproc_reason,evtproc_date"; 
			      $submit.=") values ('{$_POST["request"]}','{$_POST["qty"]}','{$_POST["evtname"]}',$this->moduleComponentId,$this->userId,";
			      $submit.="'{$_POST["evtprocreason"]}','{$_POST["evtproc_time"]}')";  
			      mysql_query($submit);
			      }
			      }
			   }
	 		if(isset($_GET['subaction'])&&($_GET['subaction']=='fund'))
			   {
			   if(isset($_POST["fevtname"]))
			      {
			      $_POST["request1"]=addslashes($_POST["request1"]);
			      $_POST["qty1"]=addslashes($_POST["qty1"]);
			      $_POST["fevtname"]=addslashes($_POST["fevtname"]);
			      $_POST["amt1"]=addslashes($_POST["amt1"]);
			      $_POST["fundreqreason"]=addslashes($_POST["fundreqreason"]);
			      $_POST["fundreq_time"]=addslashes($_POST["fundreq_time"]);
		      $query="SELECT * FROM `qaos1_fundreq` WHERE `modulecomponentid`='{$this->moduleComponentId}' AND `fundreq_Request`='{$_POST["request1"]}'";
			      $query.=" AND `fundreq_Quantity`='{$_POST["qty1"]}' AND `fundreq_name`='{$_POST["fevtname"]}' AND `userid`='{$this->userId}' AND ";
			      $query.=" `fundreq_date`='{$_POST["fundreq_time"]}' AND `fundreq_Status`=0 AND `fundreq_Amount`='{$_POST["amt1"]}' AND `fundreq_reason`='{$_POST["fundreqreason"]}'";
			      $res=mysql_query($query);
			      echo  "<script>console.log({$query})</script>";
			      if(!mysql_num_rows($res))
			      { 
		      $submit="insert into qaos1_fundreq (fundreq_Request,fundreq_Quantity,fundreq_name,fundreq_Amount,modulecomponentid,userid,fundreq_reason,";
		      $submit.="fundreq_date) values ('{$_POST["request1"]}','{$_POST["qty1"]}','{$_POST["fevtname"]}','{$_POST["amt1"]}',$this->moduleComponentId,$this->userId,'{$_POST["fundreqreason"]}','{$_POST["fundreq_time"]}')";
			      mysql_query($submit);
			      }
			      }		
			}
			$actionview = <<<AB
			   <div class="forms" id="formevt">	
	     	  	     <form action="./+view&subaction=evtproc" onsubmit="evtproc" method="post" name="evtproc">
			        <h2>Event Procurement</h2>
			        <table>
			          <tr>
					<td>Event</td>
					<td><input type="text" id="evtname" name="evtname" /></td>
				  </tr>
				  <tr>
					<td>Item</td>
					<td><input type="text" id="request" name="request"/></td>
				  </tr>
				  <tr>
					<td>Quantity</td>
					<td><input type="text" id="qty" name="qty" /></td>
				  </tr>
				  <tr>
					<td>Reason</td>
					<td><input type="text" id="evtprocreason" name="evtprocreason" /></td>
				   </tr>
				   <tr>
					<td>Deadline</td>
					<td><input id="evtdatepicker" type="text" name="evtproc_time"/></td>
				    </tr>
				    <tr>
					<td colspan="2"><input type="submit" value="submit"></td>
				    </tr>
	     			   </table>
		    	   </form>
	    	    	</div>
	    	    	<div class="forms"id="formfund">
	    	    	    <form action="./+view&subaction=fund" onsubmit="evtproc" method="post" name="evtproc1">
			     <h2>Fund Request Form</h2>		   
		       	       <table>
					<tr>
						<td>Event</td>
						<td><input type="text" id="fevtname" name="fevtname" /></td>
					</tr>
					<tr>
						<td>Item</td>
						<td><input type="text" id="request1" name="request1"/></td>
					</tr>
					<tr>
						<td>Quantity:</td>
						<td><input type="text" id="qty1" name="qty1" /></td>
					</tr>
					<tr>
						<td>Amount:</td>
						<td><input type="text" id="amt1" name="amt1" /></td>
					</tr>
					<tr>
						<td>Reason</td>
						<td><input type="text" id="fundreqreason" name="fundreqreason" /></td>
					</tr>
					<tr>
						<td>Deadline</td>
						<td><input id="funddatepicker" type="text" name="fundreq_time"/></td>
					</tr>

					<tr>
						<td colspan="2"><input type="submit" value="submit"></td>
					</tr>
			      </table>
			 </form>
		      </div>
		  <div class="display" id="displayevt">
AB;
		$actionview=$actionview1.$actionview;
		$hist1="SELECT * FROM qaos1_evtproc where modulecomponentid=$this->moduleComponentId AND userid=$this->userId";
        	$res=mysql_query($hist1);
		 $actionview.=<<<AB
			<div id="deventproc" class="forms">
						<h2>Event Procurement Status</h2>	       	    
	       	    	     <table id="table_eventproc" width="100%">
	       		 
			       	<tr>
				   <th>ITEM</th>
				   <th>QUANTITY</th>
				   <th>EVENT</th>
				   <th>REASON</th>				  		
				   <th>STATUS</th>
				   <th>DEADLINE</th>
				   <th>DESCRIPTION</th>
				 </tr>
AB;
		 //		 echo $res;
	while($result=mysql_fetch_array($res))
		{	
		  if($result['evtproc_Status']==0)$status="Pending";
		  elseif($result['evtproc_Status']==1)$status="Accepted";
		  elseif($result['evtproc_Status']==2) $status="Decline";
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
		      <table id="table_fundreq" width="100%">
			     
			    <tr>
				     <th>ITEM</th>
				     <th>QUANTITY</th>
				     <th>EVENT NAME</th>
				     <th>REASON</th>				     
				     <th>STATUS</th>
				     <th>DEADLINE</th>
				     <th>DESCRIPTION</th>
 			     </tr>
AB;
	
	$hist2="SELECT * FROM qaos1_fundreq where modulecomponentid=$this->moduleComponentId AND userid=$this->userId";
       	$res1=mysql_query($hist2);
     	while($result1=mysql_fetch_array($res1))
		 {
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
 	$actionview=$actionview. "</table></div></div>";
	 return $actionview;
	 }





	public function actionTeam()
       	       {
       	       global $sourceFolder;
       	       	require_once($sourceFolder."/upload.lib.php");
     		submitFileUploadForm($this->moduleComponentId,"qaos1",$this->userId,UPLOAD_SIZE_LIMIT);
		  	       
		$var = getUploadedFilePreviewDeleteForm($this->moduleComponentId,"qaos1",'./+team');
		$var .= '<br />Upload files : <br />'.getFileUploadForm($this->moduleComponentId,"qaos1",'./+team',UPLOAD_SIZE_LIMIT,5).'</fieldset>';		
		return $var;
       	       }



	public function actionHead()
       	       {
  	   global $urlRequestRoot, $moduleFolder, $cmsFolder,$templateFolder,$sourceFolder;
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
            
		$headaction=<<<AB
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
      		</div>
			<div id="formevt" class="forms">
     				<h2>Event Procurement Request</h2>

      			<table class="qaostables">
				<tr>
					<th>EVENT NAME</th>
					<th>ITEM</th>
					<th>QUANTITY</th>
					<th>REASON</th>
					<th>STATUS</th>
					<th>DEADLINE</th>
					<th>DESCRIPTION</th>
				</tr>
AB;
			while($result=mysql_fetch_array($res))
			{
				$event=$result['evtproc_Id'];
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
				   	<table class="qaostables">
					    <tr>
						<th>EVENT NAME</th>
						<th>ITEM</th>
						<th>QUANTITY</th>
						<th>AMOUNT</th>
						<th>REASON</th>
						<th>STATUS</th>
						<th>DEADLINE</th>
						<th>DESCRIPTION</th>
					    </tr>
AB;
		     while($result1=mysql_fetch_array($res1))
				{
					$event1=$result1['fundreq_Id'];
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
	       	   <table id="table_eventproc_head" width="100%" class="qaostables">
	       		    <tr>
						<th>ITEM</th>
						<th>QUANTITY</th>
						<th>EVENT NAME</th>
						<th>REASON</th>				  		
				  		<th>STATUS</th>
				  		<th>DEADLINE</th>
				  		<th>DESCRIPTION</th>
					 </tr>
AB;
	while($result=mysql_fetch_array($res))
		{	
			if($result['evtproc_Status']==0) $status="Pending";
		        else 
			if($result['evtproc_Status']==1)$status="Accepted";
		        else
			if($result['evtproc_Status']==2) $status="Decline";
			
			$headaction.=<<<AB
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
          $headaction.=<<<AB
          </table></div>
			<div  class="forms" id="dheadfundreq">
			<h2>Fund Request Status</h2>
		      <table id="table_funreq_head" width="100%" class="qaostables">
			   
			    <tr>
				     <th>ITEM</th>
				     <th>QUANTITY</th>
				     <th>EVENT NAME</th>
				     <th>Reason</th>
				     <th>STATUS</th>
				     <th>DEADLINE</th>
				     <th>DESCRIPTION</th>
 			     </tr>
AB;
	
	$hist2="SELECT *  FROM qaos1_fundreq WHERE modulecomponentid={$this->moduleComponentId}";
       	$res1=mysql_query($hist2);

     		 while($result1=mysql_fetch_array($res1))
		 {
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
					<td>{$result1['fundreq_date']}</td>
					<td>{$result1['fundreq_Desc']}</td>
				</tr>
AB;
		}
 	$headaction.= "</table></div></div>";
	
return $headaction;
       }




	public function actionTreasurer()
       	       {
          global $urlRequestRoot, $moduleFolder, $cmsFolder,$templateFolder,$sourceFolder;
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

   	     	$treasureraction=<<<AB
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
					     $("#dtreasurerfundreq").css({'width':'100%'});
					     $(".viewbuttons").css({'height':'25px'});
					     $(".viewbuttonsdiv").css({'width':'100%'});
					});
					</script>		
					<div id="buttonsDiv" class="buttonsClass">			
					<input type="button" id="bformfund" class="viewbuttons" value="Fund"/>					
					<input type="button" id="btreasurer_fundreq" class="viewbuttons" value="Fund Status"/>
					</div>					
					<div id="formfund" class="forms">
					<h2>Form status</h2>					
					<table>
						
						<tr>
							<th>EVENT NAME</th>
							<th>ITEM</th>
							<th>QUANTITY</th>
							<th>AMOUNT</th>
							<th>REASON</th>
							<th>STATUS</th>
							<th>DEADLINE</th>
							
							<th>DESCRIPTION</th>
						</tr>
AB;
		while($result1=mysql_fetch_array($res1))
		     			{
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
			    			      	
		      			<table id="table_funreq_treasurer" width="100%">
			    		       <tr>
							<th>ITEM</th>
				     			<th>QUANTITY</th>
				     			<th>EVENT NAME</th>
				     			<th>REASON</th>
				     			<th>STATUS</th>
				     			<th>DEADLINE</th>
				     			<th>DESCRIPTION</th>
 			     			</tr>
AB;
				$hist2="SELECT *  FROM qaos1_fundreq WHERE modulecomponentid={$this->moduleComponentId}";
       				$res1=mysql_query($hist2);
				while($result1=mysql_fetch_array($res1))
		 		{
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
							<td>{$result1['fundreq_date']}</td> 
							
							  <td>{$result1['fundreq_Desc']}</td>
						      </tr>
AB;
				}
 				$treasureraction.= "</table></div>";									
			

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
//		$query = "INSERT INTO `article_content` (`page_modulecomponentid` ,`article_content`, `allowComments`)VALUES ('$compId', 'Coming up Soon!!!','0')";
//		$result = mysql_query($query) or die(mysql_error()."article.lib L:76");
		return;
	}
	public function deleteModule($moduleComponentId) {
		/* Remove the indexing from sphider // Abhishek */
		$pageId=getPageIdFromModuleComponentId("article",$moduleComponentId);
		$path=getPagePath($pageId);
		global $urlRequestRoot;
		$delurl = "http://".$_SERVER['HTTP_HOST'].$urlRequestRoot."/home".$path;
		$query="SELECT link_id FROM `links` WHERE url='$delurl'";
		
		$result=mysql_query($query);
		if(mysql_num_rows($result)==0) return true; //Nothing to delete 
		$delids="";
		while($row=mysql_fetch_row($result))
			$delids.=$row[0].",";
		
		$delids=rtrim($delids,",");
		
		$query="DELETE FROM `links` WHERE url='$delurl'";
		
		mysql_query($query);
		for ($i=0;$i<=15; $i++) 
		{
			$char = dechex($i);
			$query="DELETE FROM `link_keyword$char` WHERE link_id IN ($delids)";
			
			mysql_query($query) or die(mysql_error()." article.lib.php L:441");
			
		}
		return true;
		

	}
	public function copyModule($moduleComponentId, $newId) 
	       {
		return true;
		}

}

