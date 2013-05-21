<?php

function validateEventData(){
	$isValid=true;
	$day=substr($_POST['eventDate'], 0, 2);
	$month=substr($_POST['eventDate'], 3, 2);
	$year=substr($_POST['eventDate'], 6, 4);
	if($_POST['eventName']==""){
			$isValid=false;
	}
	if(checkdate($month, $day, $year)==false){
			$isValid=false;
	}
	if(!preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/', $_POST['eventStartTime'])){
			$isValid=false;
	}
	if(!preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/', $_POST['eventEndTime'])){
			$isValid=false;
	}
	if($_POST['eventVenue']==""){
			$isValid=false;
	}
	if($_POST['lat']==""){
			$isValid=false;
	}
	if($_POST['lng']==""){
			$isValid=false;
	}
	return $isValid;
}

function validateAddEventData($pageModuleComponentId){
		if(validateEventData()){
				//insert data
				foreach ($_POST as $postValue){
						$postValue=escape($postValue);
				}
				//Query to insert into the db
				$insertQuery="INSERT INTO `events_details` (`event_name`, `event_date`, `event_cluster`, `event_form_id`, `event_start_time`, `event_end_time`, "
										."`event_venue`, `event_desc`, `event_last_update_time`, `event_image`, `page_moduleComponentId`, `event_loc_x`, `event_loc_y`) "
										."VALUES ('{$_POST['eventName']}', '{$_POST['eventDate']}', '{$_POST['eventCluster']}', '{$_POST['eventFormId']}', "
										."'{$_POST['eventStartTime']}', '{$_POST['eventEndTime']}', "
										."'{$_POST['eventVenue']}', '{$_POST['eventDesc']}', CURRENT_TIME(), '', '{$pageModuleComponentId}', "
										."'{$_POST['lng']}', '{$_POST['lat']}');";
				$insertRes=mysql_query($insertQuery) or displayerror(mysql_error());
				echo "Valid";
		}
		else echo "Invalid";
		exit();
}

function validateEditEventData($pageModuleComponentId){
		if(validateEventData()){
				//insert data
				foreach ($_POST as $postValue){
						$postValue=escape($postValue);
				}
				//Query to insert into the db
				$editQuery="UPDATE `events_details` SET `event_name`='{$_POST['eventName']}', 
														`event_date`='{$_POST['eventDate']}',
														`event_cluster`='{$_POST['eventCluster']}',
														`event_form_id`='{$_POST['eventFormId']}',
														`event_start_time`='{$_POST['eventStartTime']}',
														`event_end_time`='{$_POST['eventEndTime']}',
														`event_venue`='{$_POST['eventVenue']}',
														`event_desc`='{$_POST['eventDesc']}',
														`event_last_update_time`=CURRENT_TIME(),
														`event_loc_x`='{$_POST['lng']}',
														`event_loc_y`='{$_POST['lat']}' "
							." WHERE `page_moduleComponentId`='{$pageModuleComponentId}'"
							."AND `event_id`={$_POST['eventId']}";
				$editRes=mysql_query($editQuery) or displayerror(mysql_error());
				echo "Valid";
		}
		else echo "Invalid";
		exit();
}	

function getAllEvents($pmcid){
		//Query to select all entries
		global $cmsFolder,$moduleFolder,$urlRequestRoot, $sourceFolder;
		$scriptFolder = "$urlRequestRoot/$cmsFolder/$moduleFolder/events";
		$selectQuery="SELECT * FROM `events_details` WHERE `page_moduleComponentId`={$pmcid};";
		$insertRes=mysql_query($selectQuery) or displayerror(mysql_error());
		global $STARTSCRIPTS;
		$smarttablestuff = smarttable::render(array('table_event_details'),null);
		$STARTSCRIPTS .="initSmartTable();";
		$eventDetails =<<<TABLE
		<script src="$scriptFolder/events.js"></script>
		<script src="$scriptFolder/jquery.js"></script>
		$smarttablestuff
		<table class="display" id="table_event_details" width="100%" border="1">
		<thead>
				<tr>
				<th>Event ID</th>
				<th>Name</th>
				<th>Venue</th>
				<th>Date</th>
				<th>Start Time</th>
				<th>End Time</th>
				<th>Action</th>
				</tr>
		</thead>
TABLE;

	while($res = mysql_fetch_assoc($insertRes)) {
		$eventDetails .=<<<TR
		<tr>        
		<td>{$res['event_id']}</td>
		<td>{$res['event_name']}</td>
		<td>{$res['event_venue']}</td>
		<td>{$res['event_date']}</td>
		<td>{$res['event_start_time']}</td>
		<td>{$res['event_end_time']}</td>
		<td>
		<button onclick="deleteEvent({$res['event_id']});" value="DELETE" />DELETE</button>
		<form method="GET"  action="./+eventshead&subaction=editEvent&eventId={$res['event_id']}">
		<input type="submit" name="" value="EDIT"/>
		</form>
		</td>
		</tr>
TR;
}
	$eventDetails .=<<<TABLEEND
		</table>
TABLEEND;
	return $eventDetails;

}

function selectEventsHeadSubaction($pmcid){
		//form to select the subaction
		$subactionForm=<<<SFORM
		<form method="GET"  action="./+eventshead&subaction=addEvent">
				<input type="submit" name="" value="ADD EVENT"/>
		</form>
SFORM;
		$subactionForm.=getAllEvents($pmcid);
return $subactionForm;
}

function selectViewSubaction(){
		$subactionForm=<<<SFORM
		<p>
				Select an option:
		</p>
		<form method="GET"  action="./+view&subaction=mobile">
				<input type="submit" name="" value="MOBILE"/>
		</form>
		<form method="GET"  action="./+view&subaction=map">
				<input type="submit" name="" value="MAP"/>
		</form>
		<form method="GET"  action="./+view&subaction=schedule">
				<input type="submit" name="" value="SCHEDULE"/>
		</form>
SFORM;
return $subactionForm;
}

function showEventMap(){
		global $cmsFolder,$moduleFolder,$urlRequestRoot, $sourceFolder;
		$scriptFolder = "$urlRequestRoot/$cmsFolder/$moduleFolder/events";
		require_once("$sourceFolder/$moduleFolder/events/googleMapsConfig.php");
		$maps=<<<MAP
				<script src="$scriptFolder/jquery.js"></script>
				<script src="http://maps.googleapis.com/maps/api/js?sensor=false&key=".$googleMapsKey></script>
				<script src="$scriptFolder/googleMaps.js"></script>
				<script src="$scriptFolder/events.js"></script>
				<div id="upcomingEventDiv" style="width:33%;height:100%;float:right;">
					<table id="upcomingEventTable" border='1' style="width:100%;">
					</table>
				</div>				
				<div id="allEventGoogleMap" style="width:66%;height:100%;"></div>
				<script>
					window.onload=function(){
						console.log("Loaded.");
						getUpcomingEventsTable();
						setInterval(function(){
							initAllEventsMap();getUpcomingEventsTable();
							},30000);
					}
				</script>
MAP;
	echo $maps;
	exit();
}

function getEventsJSON($pmcid){
		date_default_timezone_set('Asia/Calcutta');
		$date1=date("Y-m-d");
		$events=array();
		$page=0;
		$ipp=20;
		$lastdate="0000-00-00 00:00:00";
		if(isset($_GET['pageno']))
				$page=$_GET['pageno'];
		if(isset($_GET['ipp'])) 
				$ipp=$_GET['ipp'];
		if(isset($_GET['lud']))//lud=last updated date
				$lastdate=$_GET['lud'];
		$prod=$page*$ipp;
		//Query to select all events
		$eventsQuery="SELECT * FROM `events_details` "
								."WHERE '{$lastdate}'<=`event_last_update_time` AND event_date>='{$date1}' AND `page_moduleComponentId`='{$pmcid}'" 
								."ORDER BY event_date ASC LIMIT {$prod}, {$ipp};";
		$eventsRes=mysql_query($eventsQuery) or displayerror(mysql_error());
		while($row=mysql_fetch_array($eventsRes)){
				$event=array(
						"event_id"=> $row['event_id'], 
						"event_name"=> $row['event_name'],
						"event_cluster"=> $row['event_cluster'],
						"event_form_id"=> $row['event_form_id'],
						"event_date"=> $row['event_date'], 
						"event_start_time"=>$row['event_start_time'], 
						"event_end_time"=>$row['event_end_time'],
						"event_venue"=>$row['event_venue'],
						"event_desc"=>$row['event_desc'],
						"event_last_update_time"=>$row['event_last_update_time'], 
						"event_image"=>$row['event_image'], 
						"event_loc_x"=>$row['event_loc_x'],
						"event_loc_y"=>$row['event_loc_y'], 
				);
				array_push($events, $event);//array with event
		}
		$obj=array("status"=>'success', "data"=>$events);//array with status and events
		echo json_encode($obj);
		exit;
}

function getSchedule($pmcid){
		global $cmsFolder,$moduleFolder,$urlRequestRoot, $sourceFolder;
		$scriptFolder = "$urlRequestRoot/$cmsFolder/$moduleFolder/events";

		$eventsQuery="SELECT * FROM `events_details` "
					."WHERE `page_moduleComponentId`='{$pmcid}'"
					."ORDER BY event_date ASC;";
		$eventsRes=mysql_query($eventsQuery) or displayerror(mysql_error());

		$schedule=<<<SCHEDULE
		<script src="$scriptFolder/jquery.js"></script>
		<script src="$scriptFolder/events.js"></script>
		<table>
		<th width='10%;'>Event</th>
		<th width='10%;'>Venue</th>
		<th width='10%;'>Date</th>
		<th width='10%;'>Time</th>
SCHEDULE;

		while($row=mysql_fetch_array($eventsRes)){
			$edate=$row['event_name'];
			$schedule.="<tr> <td>{$row['event_name']}</td> <td>{$row['event_venue']}</td><td>";
			$schedule.="{$row['event_date']}</td><td>";
			$schedule.=substr($row['event_start_time'], 0, 5);
			$schedule.=" to ";                
			$schedule.=substr($row['event_end_time'], 0, 5);
			$schedule.="</td></tr>";
		}
		$schedule.="</table>";
		return $schedule;
}

function deleteEvent($eventid, $pmcid){
		//query to delete event
		$deleteQuery="DELETE FROM `events_details` WHERE `event_id`='{$eventid}' AND `page_moduleComponentId`='{$pmcid}';";
		$deletetRes=mysql_query($deleteQuery) or displayerror(mysql_error());
		if ($deletetRes==1) {
				echo("Success");
		}
		else echo("error");
		exit();
}


function validateProcurementData($pageModuleComponentId){

		$isValid=true;
		
		if($_POST['quantity']=="" || !(is_numeric($_POST['quantity']))){
				$isValid=false;
				echo "Invalid";
				exit();
		}
		else {
				$_POST['eventName']=escape($_POST['eventName']);
				$_POST['procurementName']=escape($_POST['procurementName']);
				$selectQuery = "SELECT * FROM `events_event_procurement` WHERE `event_name`='{$_POST[eventName]}' AND `procurement_name`='{$_POST[procurementName]}' ";
				$selectRes=mysql_query($selectQuery);
				if(mysql_num_rows($selectRes)==1){
					$isValid=false;
					echo "Procurement ".$_POST['procurementName']." already exists for event ".$_POST['eventName'].". Kindly edit in VIEW ALL if you want to change.";
					exit();
				}
			 }
		
		if($isValid){
				//insert data
				foreach ($_POST as $postValue){
						$postValue=escape($postValue);
				}
				//Query to insert into the db
				$insertQuery="INSERT INTO `events_event_procurement` (`event_name`, `procurement_name`, `quantity`, `page_moduleComponentId`) "
							 ."VALUES ('{$_POST['eventName']}', '{$_POST['procurementName']}','{$_POST['quantity']}', '{$pageModuleComponentId}')";
				$insertRes=mysql_query($insertQuery) or displayerror(mysql_error());
  
				$updateQuery="UPDATE `events_procurements` SET `quantity`=`quantity`+ {$_POST['quantity']} WHERE `procurement_name`='{$_POST['procurementName']}'";
				$updateRes=mysql_query($updateQuery) or displayerror(mysql_error());
				echo "Valid";
		}
		exit();
}

function validateEditProcurementData($pageModuleComponentId){
		$isValid=true;
		
		if($_POST['editquantity']=="" || !(is_numeric($_POST['editquantity']))){
				$isValid=false;
				exit();
		}
		else {
				$selectQuery="SELECT * FROM `events_event_procurement`";
				$selectRes=mysql_query($selectQuery);
				$cnt=1;
				while($res = mysql_fetch_assoc($selectRes)) {
				if($cnt!=$_POST['eventnum'] &&	($_POST['procurementName']==$res['procurement_name'] && $_POST['eventName']==$res['event_name']) )
					$isValid=false;
				$cnt++;
				}
			 }
			 
		if($isValid){
				//insert data
				foreach ($_POST as $postValue){
						$postValue=escape($postValue);
				}
				//Query to insert into the db
				$selectQuery="SELECT * FROM `events_event_procurement`";
				$selectRes=mysql_query($selectQuery);
				$cnt=1;
				while($res = mysql_fetch_assoc($selectRes)) {		
				if($cnt==$_POST['eventnum']){
					$updateQuery="UPDATE `events_procurements` SET `quantity`=`quantity`-{$res['quantity']} WHERE `procurement_name`='{$res['procurement_name']}'";	
					$updateRes=mysql_query($updateQuery) or displayerror(mysql_error());
	
					$updateQuery="UPDATE `events_procurements` SET `quantity`=`quantity`+ {$_POST['editquantity']} WHERE `procurement_name`='{$_POST['procurementName']}'";
					$updateRes=mysql_query($updateQuery) or displayerror(mysql_error());
					
					$deleteQuery="DELETE FROM `events_event_procurement` WHERE `event_name`='{$res['event_name']}' AND `procurement_name`='{$res['procurement_name']}' ";
					$deleteRes=mysql_query($deleteQuery) or displayerror(mysql_error());
  
					$insertQuery="INSERT INTO `events_event_procurement` (`event_name`, `procurement_name`, `quantity`, `page_moduleComponentId`) "
							 ."VALUES ('{$_POST['eventName']}', '{$_POST['procurementName']}','{$_POST['editquantity']}', '{$pageModuleComponentId}')";
					$insertRes=mysql_query($insertQuery) or displayerror(mysql_error());
					
					echo '<script>window.location = ("./+ochead&subaction=viewAll");</script>';
					echo '<script>cmsShow("info", "Procurement edited");</script>';
					exit();
				}
				$cnt++;
				}
		}
		echo '<script>cmsShow("info", "Procurement '.$_POST["procurementName"].' for event '.$_POST["eventName"].' already exists");</script>';
		exit();
}

function validateNewProcurement($pageModuleComponentId){
		$isValid=true;

		if($_POST['newProc']==""){
				$isValid=false;
				exit();
		}
		else {
				$_POST['newProc']=escape(strtolower($_POST['newProc']));
				$selectQuery = "SELECT `procurement_name` FROM `events_procurements` WHERE `procurement_name`='{$_POST['newProc']}' ";
				$selectRes=mysql_query($selectQuery);
				if(mysql_num_rows($selectRes)==1){
					$isValid=false;
					echo "Exists";
					exit();
				}
		}
		
		if($isValid){
				//insert data
				foreach ($_POST as $postValue){
						$postValue=escape($postValue);
				}
				//Query to insert into the db
				$insertQuery="INSERT INTO `events_procurements` (`procurement_id`, `procurement_name`, `quantity`, `page_moduleComponentId`) "
							 ."VALUES (NULL, '{$_POST['newProc']}', 0, '{$pageModuleComponentId}')";
				$insertRes=mysql_query($insertQuery) or displayerror(mysql_error());
				echo "Valid";
		}
		else echo "Invalid";
		exit();
}

function selectSubactionProcurement(){
		$subactionForm=<<<SFORM
		<p>
				Select an option:
		</p>
		<form method="GET"  action="./+ochead&subaction=addProcurement">
				<input type="submit" name="" value="ADD PROCUREMENT"/>
		</form>
		<form method="GET"  action="./+ochead&subaction=addEventProcurement">
				<input type="submit" name="" value="ADD EVENT PROCUREMENTS"/>
		</form>
		<form method="GET"  action="./+ochead&subaction=viewAll">
				<input type="submit" name="" value="VIEW ALL"/>
		</form>
SFORM;
return $subactionForm;
}

function getAllProcurements($pmcid){
		//Query to select all entries
		global $cmsFolder,$moduleFolder,$urlRequestRoot, $sourceFolder;
		$scriptFolder = "$urlRequestRoot/$cmsFolder/$moduleFolder/events";
		$selectQuery="SELECT * FROM `events_event_procurement` WHERE `page_moduleComponentId`={$pmcid};";
		$selectRes=mysql_query($selectQuery) or displayerror(mysql_error());
		global $STARTSCRIPTS;
		$smarttablestuff = smarttable::render(array('table_procurement_details'),null);
		$STARTSCRIPTS .="initSmartTable();";

$procurementDetails =<<<TABLE

		<script src="$scriptFolder/events.js"></script>
		<script src="$scriptFolder/jquery.js"></script>
		$smarttablestuff
		<table class="display" id="table_procurement_details" width="100%" border="1">
		<thead>
				<tr>
				<th>Serial no.</th>
				<th>Event Name</th>
				<th>Procurement</th>
				<th>Quantity</th>
				<th>Date</th>
				<th>Start time</th>
				<th>End time</th>  
				<th></th>
				</tr>
		</thead>
TABLE;
$cnt=1;
while($res = mysql_fetch_assoc($selectRes)) {
$selQuery="SELECT * FROM `events_details` WHERE `event_name`='{$res['event_name']}'";
$selRes=mysql_query($selQuery) or displayerror(mysql_error());
$selRes = mysql_fetch_assoc($selRes);
$procurementDetails .=<<<TR

		  <tr>        
		   <td>{$cnt}</td>
		   <td>{$res['event_name']}</td>
		   <td>{$res['procurement_name']}</td>
		   <td>{$res['quantity']}</td>
		   <td>{$selRes['event_date']}</td>
		   <td>{$selRes['event_start_time']}</td>
		   <td>{$selRes['event_end_time']}</td>
		   <td>
				<button onclick="deleteProcurement({$cnt});" value="DELETE">DELETE</button>
				
				<form method="POST"  action="./+ochead">
				<input type="submit" name="" value="EDIT"/>
				<input style="visibility:hidden;" name="eventnum" id="eventnum" value="{$cnt}" />
				</form> 
		   </td>
		  </tr>
TR;
  $cnt++;
  }
$procurementDetails .=<<<TABLEEND
		</table>
TABLEEND;
  return $procurementDetails;

}

function deleteProcurement($eventname, $pmcid){
	$selectQuery="SELECT * FROM `events_event_procurement` ";
	$selectRes=mysql_query($selectQuery) or displayerror(mysql_error());
	$cnt=1;
	while($res = mysql_fetch_assoc($selectRes)) {		
	if($cnt==$eventname){
	$updateQuery="UPDATE `events_procurements` SET `quantity`=`quantity`-{$res['quantity']} WHERE `procurement_name`='{$res['procurement_name']}'";	
	$updateRes=mysql_query($updateQuery) or displayerror(mysql_error());
	$deleteQuery="DELETE FROM `events_event_procurement` WHERE `event_name`='{$res['event_name']}' AND `procurement_name`='{$res['procurement_name']}'";
	$deletetRes=mysql_query($deleteQuery) or displayerror(mysql_error());
	}
	$cnt++;
	}
	if ($deletetRes==1) {
			echo("Success");
	}
	else echo("error");
	exit();
}

function selectSubactionOcTeam(){
		$subactionForm=<<<SFORM
		<p>
				Select an option:
		</p>
		<form method="GET"  action="./+octeam&subaction=viewEventWise">
				<input type="submit" name="" value="VIEW EVENT WISE"/>
		</form>
		<form method="GET"  action="./+octeam&subaction=viewProcurementWise">
				<input type="submit" name="" value="VIEW PROCUREMENT WISE"/>
		</form>
SFORM;
return $subactionForm;
}

function viewEventWise(){
		//Query to select all entries
		global $cmsFolder,$moduleFolder,$urlRequestRoot, $sourceFolder;
		$scriptFolder = "$urlRequestRoot/$cmsFolder/$moduleFolder/events";
		$selectQuery="SELECT * FROM `events_details` ORDER BY STR_TO_DATE(`event_date`, '%d.%m.%y'),`event_start_time` ";
		$selectRes=mysql_query($selectQuery) or displayerror(mysql_error());
		global $STARTSCRIPTS;
		$smarttablestuff = smarttable::render(array('show_event_wise'),null);
		$STARTSCRIPTS .="initSmartTable();";

$procurementDetails =<<<TABLE
		<script src="$scriptFolder/events.js"></script>
		<script src="$scriptFolder/jquery.js"></script>
		$smarttablestuff
		<table class="display" id="show_event_wise" width="100%" border="1">
		<thead>
				<tr>
				<th>Serial no.</th>
				<th>Event Name</th>
				<th>Procurement</th>
				<th>Quantity</th>
				<th>Date</th>
				<th>Start time</th>
				<th>End time</th>
				</tr>
		</thead>
TABLE;
$cnt=1;
while($event=mysql_fetch_assoc($selectRes)) {	
	$result=mysql_query("SELECT * FROM `events_event_procurement` WHERE `event_name`='{$event['event_name']}'");
	while($res=mysql_fetch_assoc($result))
	{
			$procurementDetails .=<<<TR
			<tr>        
			<td>{$cnt}</td>
			<td>{$res['event_name']}</td>
			<td>{$res['procurement_name']}</td>
			<td>{$res['quantity']}</td>
			<td>{$event['event_date']}</td>
			<td>{$event['event_start_time']}</td>
			<td>{$event['event_end_time']}</td>
			</tr>
TR;
	$cnt++;
	}
  }
$procurementDetails .=<<<TABLEEND
		</table>
TABLEEND;
  return $procurementDetails;
}	

function viewProcurementWise(){
		//Query to select all entries
		global $cmsFolder,$moduleFolder,$urlRequestRoot, $sourceFolder;
		$scriptFolder = "$urlRequestRoot/$cmsFolder/$moduleFolder/events";
		$selectQuery="SELECT * FROM `events_procurements`;";
		$selectRes=mysql_query($selectQuery) or displayerror(mysql_error());
		global $STARTSCRIPTS;
		$smarttablestuff = smarttable::render(array('show_procurement_wise'),null);
		$STARTSCRIPTS .="initSmartTable();";

$procurementDetails =<<<TABLE
		<script src="$scriptFolder/events.js"></script>
		<script src="$scriptFolder/jquery.js"></script>
		$smarttablestuff
		<table class="display" id="show_procurement_wise" width="100%" border="1">
		<thead>
				<tr>
				<th>Serial no.</th>
				<th>Procurement Id</th>
				<th>Procurement name</th>
				<th>Quantity</th>
				</tr>
		</thead>
TABLE;
$cnt=1;
while($res = mysql_fetch_assoc($selectRes)) {
$procurementDetails .=<<<TR
		  <tr>        
		   <td>{$cnt}</td>
		   <td>{$res['procurement_id']}</td>
		   <td>{$res['procurement_name']}</td>
		   <td>{$res['quantity']}</td>
		  </tr>
TR;
  $cnt++;
  }
$procurementDetails .=<<<TABLEEND
		</table>
TABLEEND;
  return $procurementDetails;

}


function getEventName($pmcId,$eventId){
	$getEventNameQuery = "SELECT `event_name` FROM `events_details` WHERE `page_moduleComponentId`='{$pmcId}' AND `event_id` = '{$eventId}'";
	$getEventNameRes = mysql_query($getEventNameQuery) or displayerror(mysql_error);
	return mysql_result($getEventNameRes,0);
}


function displayEventOptions($gotoaction,$pmcId,$eventId){
	global $cmsFolder,$moduleFolder,$urlRequestRoot, $sourceFolder;
    $scriptFolder = "$urlRequestRoot/$cmsFolder/$moduleFolder/events";
    $eventDetails =<<<SCRIPT
		<script src="$scriptFolder/events.js"></script>
        <script src="$scriptFolder/jquery.js"></script>
SCRIPT;
    if($gotoaction == 'qa'){
		$eventDetails .= displayQa($pmcId).'<br/><br/><strong>'.getEventName($pmcId,$eventId).'</strong>';
	}
	else if($gotoaction == 'qahead'){
		$eventDetails .= qaHeadOptions($pmcId).'<br/><br/><strong>'.getEventName($pmcId,$eventId).'</strong>';
		$checkLockedQuery  = "SELECT `event_id` FROM `events_locked` WHERE `page_moduleComponentId`='{$pmcId}' AND `event_id`='{$eventId}'";
		$checkLockedRes = mysql_query($checkLockedQuery) or displayerror(mysql_error());
		if(mysql_num_rows($checkLockedRes) > 0){
			$eventDetails.=<<<FORM
				<br/><p>Event Locked</p>
				<a href='./+{$gotoaction}&subaction=downloadExcel&event_id=$eventId'>Download Details</a><br/>
				<form method='POST' action='./+qahead&subaction=unlockEvent' onsubmit='return unlockConfirm();'>
				<input type='hidden' value='{$eventId}' name='eventId'>
				<input type='submit' id='lockButton' value='UNLOCK EVENT'>
				</form>
FORM;
			return $eventDetails;
		}
	}
	if(isset($_FILES['fileUploadField']['name'])){
		syncExcelFile($pmcId,$eventId,$_FILES['fileUploadField']['tmp_name'][0]);
	}

	$checkParticipantsQuery = "SELECT `user_pid` FROM `events_participants` WHERE `page_moduleComponentId`='{$pmcId}' AND `event_id`='{$eventId}' LIMIT 1";
	$checkParticipantsRes = mysql_query($checkParticipantsQuery) or displayerror(mysql_error());
	if(mysql_num_rows($checkParticipantsRes) == 0){
		//Show FileUpload Details
		$fileUploadableField=getFileUploadField('fileUploadField',"events");

	$eventDetails.=<<<ADDROOMFORM
           <br/><br/>
           <form action="./+{$gotoaction}&subaction=viewEvent" method="post">
            	<input type="submit" name="downloadSampleFormat" value="Download Sample Form"><br/>
           </form>
           <form action="./+{$gotoaction}&subaction=viewEvent" method="post" enctype='multipart/form-data'>
	       $fileUploadableField
	       <input type='hidden' name='eventId' value='{$eventId}'>
	       <input type='submit' name='submit' value='Upload'>
	       </form>
ADDROOMFORM;
	}
	else{
		$eventDetails.=<<<PRINTTABLE
		<br/><br/>
		<a href='./+{$gotoaction}&subaction=downloadExcel&event_id=$eventId'>Download Details</a>
		<br/><br/>
		<form method='POST' action='./+{$gotoaction}&subaction=lockEvent' onsubmit='return lockConfirm();'>
		<input type='hidden' value='{$eventId}' name='eventId'>
		<input type='submit' id='lockButton' value='LOCK EVENT'>
		</form>
		<!--<button onclick="downloadDetails('$gotoaction',{$eventId})" value='Download Details'>Download Details</button>-->
PRINTTABLE;
		//$downloadTable = getUserDetailsTable($pmcId,$eventId);
		//$eventDetails.=displayExcelForTable($downloadTable);
	
		$eventParticipants=displayEventParticipants($gotoaction,$pmcId,$eventId);
		//displayExcelForTable($eventParticipants);
		$eventDetails .= $eventParticipants;
	}
	return $eventDetails;
}

function displayEventParticipants($gotoaction,$pmcId,$eventId){
	/*
		Edit Row Id

		-1 ->Name
		form_elementId ->Coll,Ph No
	*/
	global $STARTSCRIPTS;
	$smarttable = smarttable::render(array('participants_table'),null);
	$STARTSCRIPTS.="initSmartTable();";
	$participantsTable = "";
	$selectParticipantsQuery = "SELECT `events_participants`.`user_pid`,`events_participants`.`user_team_id` FROM `events_participants` WHERE `events_participants`.`page_moduleComponentId`='{$pmcId}' 
				AND `events_participants`.`event_id`='{$eventId}' AND `events_participants`.`user_pid` NOT IN (SELECT `events_confirmed_participants`.`user_id` 
				FROM `events_confirmed_participants` WHERE `events_confirmed_participants`.`page_moduleComponentId` =  '{$pmcId}') ORDER BY `events_participants`.`user_team_id`";
//	return $selectParticipantsQuery;
	$selectParticipantsRes = mysql_query($selectParticipantsQuery) or displayerror(mysql_error());
	$participantsTable.=<<<TABLE
				$smarttable<table id='participants_table' class='display' width='100%' border='1'><thead><tr><th>Team Id</th><th>PID</th><th>Name</th><th>College</th><th>Phone No.</th><th>Rank</th><th>Options</th></tr></thead>
TABLE;
	while($participant = mysql_fetch_assoc($selectParticipantsRes)){
		$editRowId = "";
		$participantNameQuery = "SELECT `pragyanV3_users`.`user_fullname` FROM `pragyanV3_users` WHERE `user_id`='{$participant['user_pid']}'";
		$participantNameRes = mysql_query($participantNameQuery) or displayerror(mysql_error());
		$participantsTable.="<tr id='partRow{$participant['user_pid']}'><td>".$participant['user_team_id']."</td><td>".$participant['user_pid']."</td><td><span class='userDataDisp{$participant['user_pid']}'>".mysql_result($participantNameRes, 0)."</span><input type='text' class='userDataEditVal{$participant['user_pid']}' value='".mysql_result($participantNameRes, 0)."' style='display:none'></td>";
		$editRowId.='-1,';
		$phNoFormId = retPnoneNoFormId();
		$collFormId = retCollFormId();
		$participantsDetailsQuery = "SELECT `form_elementid`,`form_elementdata` FROM `form_elementdata` WHERE `page_moduleComponentId`='{retglobalPmcId()}' AND `user_id`='{$participant['user_pid']}' AND `form_elementid` IN ($phNoFormId,$collFormId) ORDER BY `form_elementid`";
		//1
		$participantsDetailsRes = mysql_query($participantsDetailsQuery) or displayerror(mysql_error());
		while($userDetails = mysql_fetch_assoc($participantsDetailsRes)){
			$editRowId.=$userDetails['form_elementid'].',';
			$participantsTable.="<td><span class='userDataDisp{$participant['user_pid']}'>".$userDetails['form_elementdata']."</span><input type='text' class='userDataEditVal{$participant['user_pid']}' value='{$userDetails['form_elementdata']}' style='display:none'></td>";
		}
		$participantsRankQuery = "SELECT `user_rank` FROM `events_result` WHERE `page_moduleComponentId`='{$pmcId}' AND `user_id`='{$participant['user_pid']}' AND `event_id`='{$eventId}'";
		$participantsRankRes = mysql_query($participantsRankQuery) or displayerror(mysql_error());
		$userRank = mysql_result($participantsRankRes,0);
		$participantsTable.="<td><span class='userDataDisp{$participant['user_pid']}'>".$userRank."</span><input type='text' class='userDataEditVal{$participant['user_pid']}' value='{$userRank}' style='display:none'></td>";
		$editRowId.='-2';
		$editRowId=trim($editRowId,',');
		$participantsTable.=<<<BUTTON
			<td>
				<!--
				<form method='POST' class='userDataDisp{$participant['user_pid']}' action='./+{$gotoaction}&subaction=confirmParticipant' onsubmit='return confirmParticipant();'>
				<input type='hidden' value='{$participant['user_pid']}' name='userId'>
				<input type='hidden' value='$eventId' name='eventId'>
				<input type='submit' value='Confirm'>
				</form>
				-->

				<button class='userDataDisp{$participant['user_pid']}' onclick="editParticipant({$participant['user_pid']},$eventId)" value="Edit">Edit</button>
				<button class='userDataEdit{$participant['user_pid']}' onclick="updateParticipant('$gotoaction',{$participant['user_pid']},{$participant['user_team_id']},0,'$editRowId',$eventId)" style='display:none' value='Update'>Update</button>
				<button class='userDataEdit{$participant['user_pid']}' onclick="cancelEditParticipant({$participant['user_pid']},$eventId)" style='display:none' value='Cancel'>Cancel</button>

				<!--<button onclick="confirmParticipant({$participant['user_pid']},$eventId)" value="Confirm">Confirm</button>-->
			</td></tr>
BUTTON;
	}
	$participantsTable.="</table>";
	return $participantsTable;
}

function getUserDetailsTable($gotoaction,$pmcId,$eventId){
	global $STARTSCRIPTS;
	$smarttable = smarttable::render(array('participants_table'),null);
	$STARTSCRIPTS.="initSmartTable();";
	$participantsTable = "";
	$participantsTable.=<<<TABLE
				$smarttable<table id='participants_table' class='display' width='100%' border='1'><thead><tr><th>Team Id</th><th>PID</th><th>Name</th><th>College</th><th>Phone No.</th></tr></thead>
TABLE;
	$selectTeamQuery = "SELECT `user_team_id`,COUNT(*) AS `count` FROM `events_participants` WHERE `page_moduleComponentId`='{$pmcId}' AND `event_id`='{$eventId}' GROUP BY `user_team_id`";
	$selectTeamRes = mysql_query($selectTeamQuery) or displayerror(mysql_error());
	while($team = mysql_fetch_assoc($selectTeamRes)){
		$participantsTable.="<tr><td rowspan='{$team['count']}'>".$team['user_team_id']."</td>";
		$selectParticipantsQuery = "SELECT `user_pid` FROM `events_participants` WHERE `page_moduleComponentId`='{$pmcId}' AND `event_id`='{$eventId}' AND `user_team_id`='{$team['user_team_id']}'";
		$selectParticipantsRes = mysql_query($selectParticipantsQuery) or displayerror(mysql_error());
		while($participant = mysql_fetch_assoc($selectParticipantsRes)){
			$participantNameQuery = "SELECT `pragyanV3_users`.`user_fullname` FROM `pragyanV3_users` WHERE `user_id`='{$participant['user_pid']}'";
			$participantNameRes = mysql_query($participantNameQuery) or displayerror(mysql_error());
			$participantsTable.="<td>".$participant['user_pid']."</td><td>".mysql_result($participantNameRes, 0)."</td>";
			$phNoFormId = retPnoneNoFormId();
			$collFormId = retCollFormId();
			$participantsDetailsQuery = "SELECT `form_elementdata` FROM `form_elementdata` WHERE `page_moduleComponentId`='{retglobalPmcId()}' AND `user_id`='{$participant['user_pid']}' AND `form_elementid` IN ($phNoFormId,$collFormId)";
			//2
			$participantsDetailsRes = mysql_query($participantsDetailsQuery) or displayerror(mysql_error());
			while($userDetails = mysql_fetch_assoc($participantsDetailsRes)){
				$participantsTable.="<td>".$userDetails['form_elementdata']."</td>";
			}
			$participantsTable.="</tr>";
		}
	}
	$eventNameQuery = "SELECT `event_name` FROM `events_details` WHERE `page_moduleComponentId`='{$pmcId}' AND `event_id`='{$eventId}'";
	$eventNameRes = mysql_query($eventNameQuery) or displayerror(mysql_error());
	$eventName = mysql_result($eventNameRes, 0);
	$participantsTable.="</table>";
	displayExcelForTable($participantsTable,$eventName);
	//return $participantsTable;
}

function confirmParticipation($gotoaction,$pmcid,$eventId,$userId){
	$confirmQuery = "INSERT INTO `events_confirmed_participants`(`page_moduleComponentId`,`event_id`,`user_id`) VALUES('{$pmcid}','{$eventId}','{$userId}')";
	$confirmRes = mysql_query($confirmQuery) or displayerror(mysql_error());
	$userRankQuery = "INSERT INTO `events_result`(`page_moduleComponentId`,`event_id`,`user_id`) VALUES('{$pmcid}','{$eventId}','{$userId}')";
	$userRankRes = mysql_query($userRankQuery) or displayerror(mysql_error());
	//return "Successfully deleted.";
	$redirectData = <<<POSTDATA
		<form method='POST' action='./+{$gotoaction}&subaction=viewEvent' name='postDataForm'>
		<input type='hidden' name='eventId' value='{$eventId}'/>
		</form>
		<script type='text/javascript'>
			document.postDataForm.submit();
		</script>
POSTDATA;
	return $redirectData;
}

function editParticipant($gotoaction,$pmcId,$eventId,$formId,$userId,$teamId,$rowValue,$rowId){
	$rowValueArray = explode(",",$rowValue);
	$rowIdArray = explode(",",$rowId);
	$updatedRow = "";
	$updatedRow.="<td>".$teamId."</td><td>".$userId."</td>";
	for($i=0;$i<sizeof($rowValueArray);$i++){
/*
		$insertEditedRowQuery = "INSERT INTO `events_edited_form`(`page_moduleComponentId`,`form_id`,`user_id`,`form_elementid`,`form_elementdata`) 
			VALUES('{$pmcId}','{$formId}','{$userId}','{$rowIdArray[$i]}','{$rowValueArray[$i]}') ON DUPLICATE KEY UPDATE `form_elementdata`='{$rowValueArray[$i]}'";
		$insertEditedRowRes = mysql_query($insertEditedRowQuery) or displayerror(mysql_error());*/
		if($rowIdArray[$i] == -1){
			$editNameQuery = "UPDATE `pragyanV3_users` SET `user_fullname`='{$rowValueArray[$i]}' WHERE `user_id`='{$userId}'";
			$editNameRes = mysql_query($editNameQuery) or displayerror(mysql_error());
		}
		else if($rowIdArray[$i] == -2){
			$editRankQuery = "UPDATE `events_result` SET `user_rank`='{$rowValueArray[$i]}' WHERE `user_id`='{$userId}' AND `page_moduleComponentId`='{$pmcId}' AND `event_id`='{$eventId}'";
			$editRankRes = mysql_query($editRankQuery) or displayerror(mysql_error());
		}
		else{
			$editValuesQuery = "UPDATE `form_elementdata` SET `form_elementdata`='{$rowValueArray[$i]}' 
					WHERE `user_id`='{$userId}' AND `form_elementid`='{$rowIdArray[$i]}' AND `page_moduleComponentId`='{retglobalPmcId()}'";
					//3
			$editValuesRes = mysql_query($editValuesQuery) or displayerror(mysql_error());
		}
		$updatedRow.="<td><span class='userDataDisp{$userId}'>".$rowValueArray[$i]."</span><input type='text' class='userDataEditVal{$userId}' value='{$rowValueArray[$i]}' style='display:none'></td>";
	}
	$updatedRow.=<<<BUTTON
				<td>
				<!--
				<form method='POST' class='userDataDisp{$userId}' action='./+{$gotoaction}&subaction=confirmParticipant' onsubmit='return confirmParticipant();'>
				<input type='hidden' value='{$userId}' name='userId'>
				<input type='hidden' value='{$eventId}' name='eventId'>
				<input type='submit' value='Confirm'>
				</form>
				-->

				<button class='userDataDisp{$userId}' onclick="editParticipant({$userId},{$eventId})" value="Edit">Edit</button>
				<button class='userDataEdit{$userId}' onclick="updateParticipant('$gotoaction',{$userId},{$teamId},{$formId},'{$rowId}',{$eventId})" style='display:none' value='Update'>Update</button>
				<button class='userDataEdit{$userId}' onclick="cancelEditParticipant({$userId},{$eventId})" style='display:none' value='Cancel'>Cancel</button>
				</td>
BUTTON;
	return $updatedRow;
}

function lockEvent($pmcId,$eventId){

	$lockEventQuery = "INSERT INTO `events_locked`(`page_moduleComponentId`,`event_id`) VALUES('{$pmcId}','{$eventId}')";
	$lockEventRes = mysql_query($lockEventQuery) or displayerror(mysql_error());
	return "Event Locked";
}


function unlockEvent($pmcId,$eventId){

	$unlockEventQuery = "DELETE FROM `events_locked` WHERE `page_moduleComponentId`='{$pmcId}' AND `event_id`='{$eventId}'";
	$unlockEventRes = mysql_query($unlockEventQuery) or displayerror(mysql_error());
	return "Event Unlocked";
}

function syncExcelFile($pmcId,$eventId,$fileLoc	){
	$excelData = readExcelSheet($fileLoc);
	for($i=1;$i<=count($excelData);$i++){
		for($j=1;$j<=count($excelData[$i]);$j++){
			$userPid = $excelData[$i][$j];
			if($userPid[0] == 'P' || $userPid[0] == 'p'){
				$userPid = getUserIdFromBookletId($userPid,$pmcId);
			}
			if(!empty($excelData[$i][$j])){
				$checkDuplicateQuery = "SELECT `user_pid` FROM `events_participants` WHERE `page_moduleComponentId`='{$pmcId}' AND `event_id`='{$eventId}' AND `user_pid`='{$userPid}'";
				$checkDuplicateRes = mysql_query($checkDuplicateQuery) or displayerror(mysql_error());
				if(mysql_num_rows($checkDuplicateRes) == 0){
					$saveUserIdQuery = "INSERT INTO `events_participants`(`page_moduleComponentId`,`event_id`,`user_pid`,`user_team_id`) VALUES('{$pmcId}','{$eventId}','{$userPid}','{$i}')";
					$saveUserIdRes = mysql_query($saveUserIdQuery) or displayerror(mysql_error());

					$userInitRankQuery = "INSERT INTO `events_result`(`page_moduleComponentId`,`user_id`,`user_rank`,`event_id`) VALUES('{$pmcId}','{$userPid}','-1','{$eventId}')";
					$userInitRankRes = mysql_query($userInitRankQuery) or displayerror(mysql_error());
				}
			}
		}
	}
}


function viewEventResult($gotoaction,$pmcId,$eventId){
	global $cmsFolder,$moduleFolder,$urlRequestRoot, $sourceFolder;
    $scriptFolder = "$urlRequestRoot/$cmsFolder/$moduleFolder/events";
    $eventDetails =<<<SCRIPT
		<script src="$scriptFolder/events.js"></script>
        <script src="$scriptFolder/jquery.js"></script>
SCRIPT;

	$eventDetails .= displayPR($gotoaction,$pmcId).'<br/><br/><strong>'.getEventName($pmcId,$eventId).'</strong>';

	$eventDetails.=<<<PRINT
		<br/><br/>
		<form method='POST' action='./+{$gotoaction}&subaction=printCerti'>
		<input type='hidden' name='eventId' value='{$eventId}'>
		<input type='submit' value='Print Certificates PDF'>
		</form>
		<br/>
PRINT;
	if($gotoaction == 'prhead'){
		$eventDetails.=<<<UNLOCK
		<form method='POST' action='./+{$gotoaction}&subaction=unlockEvent' onsubmit='return unlockConfirm();'>
		<input type='hidden' value='{$eventId}' name='eventId'>
		<input type='submit' id='lockButton' value='UNLOCK EVENT'>
		</form>
		<br/>
		<a href='./+{$gotoaction}&subaction=downloadExcel&event_id=$eventId'>Download Details</a><br/>
UNLOCK;
	}

	$checkParticipantsQuery = "SELECT `user_pid` FROM `events_participants` WHERE `page_moduleComponentId`='{$pmcId}' AND `event_id`='{$eventId}' LIMIT 1";
	$checkParticipantsRes = mysql_query($checkParticipantsQuery) or displayerror(mysql_error());
	
	$eventParticipants=displayParticipantRank($gotoaction,$pmcId,$eventId);
		//displayExcelForTable($eventParticipants);
	$eventDetails .= $eventParticipants;
	return $eventDetails;
}

function displayParticipantRank($gotoaction,$pmcId,$eventId){
	global $STARTSCRIPTS;
	$smarttable = smarttable::render(array('participants_table'),null);
	$STARTSCRIPTS.="initSmartTable();";
	$participantsTable = "";
	$selectParticipantsQuery = "SELECT `events_participants`.`user_pid` FROM `events_participants` WHERE `events_participants`.`page_moduleComponentId`='{$pmcId}' 
				AND `events_participants`.`event_id`='{$eventId}' AND `events_participants`.`user_pid` NOT IN (SELECT `events_confirmed_participants`.`user_id` 
				FROM `events_confirmed_participants` WHERE `events_confirmed_participants`.`page_moduleComponentId` =  '{$pmcId}') ORDER BY `events_participants`.`user_team_id`";
//	return $selectParticipantsQuery;
	$selectParticipantsRes = mysql_query($selectParticipantsQuery) or displayerror(mysql_error());
	$participantsTable.=<<<TABLE
				$smarttable<table id='participants_table' class='display' width='100%' border='1'><thead><tr><th>PID</th><th>Name</th><th>College</th><th>Phone No.</th><th>Rank</th><th>Options</th></tr></thead>
TABLE;
	while($participant = mysql_fetch_assoc($selectParticipantsRes)){
		$participantNameQuery = "SELECT `pragyanV3_users`.`user_fullname` FROM `pragyanV3_users` WHERE `user_id`='{$participant['user_pid']}'";
		$participantNameRes = mysql_query($participantNameQuery) or displayerror(mysql_error());
		$participantsTable.="<tr id='partRow{$participant['user_pid']}'><td>".$participant['user_pid']."</td><td>".mysql_result($participantNameRes, 0)."</td>";
		$phNoFormId = retPnoneNoFormId();
		$collFormId = retCollFormId();
		$participantsDetailsQuery = "SELECT `form_elementid`,`form_elementdata` FROM `form_elementdata` WHERE `page_moduleComponentId`='{retglobalPmcId()}' AND `user_id`='{$participant['user_pid']}' AND `form_elementid` IN ($phNoFormId,$collFormId) ORDER BY `form_elementid`";
		//4
		$participantsDetailsRes = mysql_query($participantsDetailsQuery) or displayerror(mysql_error());
		while($userDetails = mysql_fetch_assoc($participantsDetailsRes)){
			$participantsTable.="<td>".$userDetails['form_elementdata']."</td>";
		}
		$participantsRankQuery = "SELECT `user_rank` FROM `events_result` WHERE `page_moduleComponentId`='{$pmcId}' AND `user_id`='{$participant['user_pid']}' AND `event_id`='{$eventId}'";
		$participantsRankRes = mysql_query($participantsRankQuery) or displayerror(mysql_error());
		$userRank = mysql_result($participantsRankRes,0);
		$participantsTable.="<td>".$userRank."</td>";
		$participantsTable.=<<<FORM
			<td>
			<form method='POST' action='./+{$gotoaction}&subaction=printIndividualCerti'>
			<input type='hidden' name='userId' value='{$participant['user_pid']}'>
			<input type='hidden' name='eventId' value='{$eventId}'>
			<input type='submit' value='Print Certificate PDF'>
			</form>
			</td></tr>
FORM;
	}
	$participantsTable.="</table>";
	return $participantsTable;
}

function printIndividualCerti($gotoaction,$eventAction,$pmcId,$userId,$eventId){
	global $urlRequestRoot,$sourceFolder,$templateFolder,$cmsFolder,$moduleFolder;
	require_once("$sourceFolder/$moduleFolder/events/html2pdf/html2pdf.class.php");	
	require_once("$sourceFolder/$moduleFolder/events/events_certi_image2.php");
	if($eventAction == 'event'){
		$eventName = getEventName($pmcId,$eventId);
		$getUserRankQuery = "SELECT `user_rank` FROM `events_result` WHERE `page_moduleComponentId`='{$pmcId}' AND `event_id`='{$eventId}' AND `user_id`='{$userId}'";
		$getUserRankRes = mysql_query($getUserRankQuery) or displayerror(mysql_error());
		$userRank = mysql_result($getUserRankRes,0);

	}

	else if($eventAction == 'workshop'){
		$getWorkshopNameQuery = "SELECT `workshop_name` FROM `events_workshop_details` WHERE `workshop_id`='{$eventId}' AND `page_moduleComponentId`='{$pmcId}'";
		$getWorkshopNameRes = mysql_result($getWorkshopNameQuery) or displayerror(mysql_error());
		$eventName = mysql_result($getWorkshopNameRes,0);
	}
	$certiImagePage=generateCerti($eventAction,$eventId,$pmcId,$userId,$userRank);
	$html2pdf = new HTML2PDF('P','A4','en',true, 'UTF-8',array(0, 0, 0, 0));
    $html2pdf->WriteHTML($certiImagePage);
    $html2pdf->Output($eventName.'_certificates.pdf','D');
}

function printCertificates($eventAction,$pmcId,$eventId){
	global $urlRequestRoot,$sourceFolder,$templateFolder,$cmsFolder,$moduleFolder;
	require_once("$sourceFolder/$moduleFolder/events/html2pdf/html2pdf.class.php");	
	require_once("$sourceFolder/$moduleFolder/events/events_certi_image2.php");


	if($eventAction == 'event'){
		$getEventNameQuery = "SELECT `event_name` FROM `events_details` WHERE `event_id` = '{$eventId}' AND `page_moduleComponentId`='{$pmcId}'";
		$getEventNameRes = mysql_query($getEventNameQuery) or displayerror(mysql_error());
		$eventName = mysql_result($getEventNameRes,0);

		$selectIdQuery = "SELECT DISTINCT `events_participants`.`user_pid`,`events_result`.`user_rank` FROM `events_participants`,`events_result`
				WHERE `events_participants`.`event_id`='{$eventId}' AND `events_result`.`event_id`='{$eventId}' AND `events_result`.`user_id`=`events_participants`.`user_pid` AND `events_participants`.`page_moduleComponentId`='{$pmcId}' AND
				`events_result`.`page_moduleComponentId`='{$pmcId}' ORDER BY `events_participants`.`user_team_id`,`events_result`.`user_rank`";
		$selectIdRes = mysql_query($selectIdQuery) or displayerror(mysql_error());
	}

	else if($eventAction == 'workshop'){
		$getEventNameQuery = "SELECT `workshop_name` FROM `events_workshop_details` WHERE `workshop_id` = '{$eventId}' AND `page_moduleComponentId`='{$pmcId}'";
		$getEventNameRes = mysql_query($getEventNameQuery) or displayerror(mysql_error());
		$eventName = mysql_result($getEventNameRes,0);

		$selectIdQuery = "SELECT DISTINCT `user_id` FROM `events_workshop_participants`,`events_result` WHERE `page_modulecomponentid`='{$pmcId}' AND `workshop_id`='{$eventId}'";
		$selectIdRes = mysql_query($selectIdQuery) or displayerror(mysql_error());
	}
//return $selectIdQuery;
	$certiImagePage = "";
	while($printParticipant = mysql_fetch_assoc($selectIdRes)){
		if($eventAction == 'event'){
			$userId = $printParticipant['user_pid'];
			$userRank = $printParticipant['user_rank'];
		}
		else if($eventAction == 'workshop'){
			$userId = $printParticipant['user_id'];
			$userRank = $eventId;
		}
		$certiImagePage.=generateCerti($eventAction,$eventId,$pmcId,$userId,$userRank);
	}
	$html2pdf = new HTML2PDF('P','A4','en',true, 'UTF-8',array(0, 0, 0, 0));
    $html2pdf->WriteHTML($certiImagePage);
    $html2pdf->Output($eventName.'_certificates.pdf','D');
}

function generateCerti($eventAction,$eventId,$pmcId,$userId,$userRank){
	if($eventAction == 'event'){
		$getCertiImgQuery = "SELECT `certificate_id`,`certificate_image` FROM `events_certificate` WHERE `user_rank` = '{$userRank}' AND `page_moduleComponentId`='{$pmcId}' AND `event_id` = '{$eventId}'";
		$eventName = getEventName($pmcId,$eventId);
	}
	else if($eventAction == 'workshop')
		$getCertiImgQuery = "SELECT `certificate_id`,`certificate_image` FROM `events_certificate` WHERE `user_rank` = '{$eventId}' AND `page_moduleComponentId`='{$pmcId}' AND `event_id` = '-1'";
	$getCertiImgRes = mysql_query($getCertiImgQuery) or displayerror(mysql_error());

	$certiImage = "";
	$posXString="";
	$posYString="";
	$certiValueString="";

	while($certiDetails = mysql_fetch_assoc($getCertiImgRes)){
		$certiImage = $certiDetails['certificate_image'];
		$certiId = $certiDetails['certificate_id'];
		//Get Certificate Details From evets_certficate_details

		$getCertiDetailsQuery = "SELECT `certificate_posx`,`certificate_posy`,`form_value_id` FROM `events_certificate_details` WHERE `page_moduleComponentId`='{$pmcId}' AND `certificate_id`='{$certiId}'";
		$getCertiDetailsRes = mysql_query($getCertiDetailsQuery) or displayerror(mysql_error());
	
		//Get Form Values From form_elementdesc
		//Form_value_id=1 -> Rank
		//Form_value_id=2 -> Event Name
		//Form_value_id=3 -> PArticipant Name
		//Form_value_id=4 -> Coll
		while($getValues = mysql_fetch_assoc($getCertiDetailsRes)){
			//User Rank
			if($getValues['form_value_id'] == 1){
				//imagettftext($rotatedImage, 20, 90, $getValues['certificate_posx'], $getValues['certificate_posy'], $color, $font, $userRank);
				$posXString.=$getValues['certificate_posx']."::";
				$posYString.=$getValues['certificate_posy']."::";
				switch($userRank){
					case 1:$participantRank = "First";
							break;
					case 2:$participantRank = "Second";
							break;
					case 3:$participantRank = "Third";
							break;
				}
				$certiValueString.=$participantRank."::";
			}
			//Event Name
			else if($getValues['form_value_id'] == 2){
				//Get Event Name
				
			//	imagettftext($rotatedImage, 20, 90, $getValues['certificate_posx'], $getValues['certificate_posy'], $color, $font, $eventName);
				$posXString.=$getValues['certificate_posx']."::";
				$posYString.=$getValues['certificate_posy']."::";
				$certiValueString.=$eventName."::";	
			}
			else if($getValues['form_value_id'] == 3){
				$getUserNameQuery = "SELECT `user_fullname` FROM `pragyanV3_users` WHERE `user_id`='{$userId}'";
				$getUserNameRes = mysql_query($getUserNameQuery) or displayerror(mysql_error());
				$userName = mysql_result($getUserNameRes, 0);
				$posXString.=$getValues['certificate_posx']."::";
				$posYString.=$getValues['certificate_posy']."::";
				$certiValueString.=$userName."::";
			}
			else if($getValues['form_value_id'] == 4){
				//Check if modified value exists in events_edited_form
					//Else get value from form_elementdata
					//Change1
				$collId = retCollFormId();
				$formPmcId = retglobalPmcId();
				//$getFormValuesQuery = "SELECT `form_elementdata`.`form_elementdata` FROM `form_elementdata` INNER JOIN `events_form` ON `form_elementdata`.`page_moduleComponentId`=`events_form`.`form_id` 
				//	AND `events_form`.`event_id`='{$eventId}' AND `events_form`.`page_moduleComponentId`='{$pmcId}' AND `form_elementdata`.`user_id`='{$printParticipant['user_id']}' AND `form_elementdata`.`form_elementid`='{$getValues['form_value_id']}'";
				$getFormValuesQuery = "SELECT `form_elementdata` FROM `form_elementdata` WHERE `page_moduleComponentId`='{$formPmcId}' AND `form_elementid`='{$collId}' AND `user_id`='{$userId}'";
				$getFormValuesRes = mysql_query($getFormValuesQuery) or displayerror(mysql_error());
			//	return mysql_num_rows($getFormValuesQuery);
				while($formData = mysql_fetch_assoc($getFormValuesRes)){
				//	imagettftext($rotatedImage, 20, 90, $getValues['certificate_posx'], $getValues['certificate_posy'], $color, $font, $formData['form_elementdata']);
					$posXString.=$getValues['certificate_posx']."::";
					$posYString.=$getValues['certificate_posy']."::";
					$certiValueString.=$formData['form_elementdata']."::";
				}
			}
			}
		}

		$posXString=rtrim($posXString,"::");
		$posYString=rtrim($posYString,"::");
		$certiValueString=rtrim($certiValueString,"::");
		$certiImagePage="<page><img style='width:100%;height:100%' src='".generateImage($certiImage,$posXString,$posYString,$certiValueString)."'></page>";
		return $certiImagePage;
}

function getUserIdFromBookletId($bookletId,$mcId) {
  $bookletId = escape($bookletId);
  $checkUserRegisteredToPr = "SELECT * FROM `prhospi_pr_status` WHERE `page_modulecomponentid` = {$mcId} AND `booklet_id` = '{$bookletId}'";
  $checkUserRegisteredToPrQuery = mysql_query($checkUserRegisteredToPr) or displayerror(mysql_error());
  $row = mysql_fetch_assoc($checkUserRegisteredToPrQuery);
  return $row['user_id'];

}

function getPrHeadOptions($pmcId){
	$options = <<<OPTIONS
		<form method='POST' action='./+prhead&subaction=viewEventList'>
			<input type='submit' value='Events' name='viewEvents'/>
		</form>
		<form method='POST' action='./+prhead&subaction=viewWorkshopList'>
			<input type='submit' value='Workshops' name='viewWorkshops'/>
		</form>
OPTIONS;
	return $options;
}

function getWorkshopsList($pmcId){
	$selectWorkshopQuery = "SELECT `workshop_id`,`workshop_name` FROM `events_workshop_details` WHERE `page_moduleComponentId`='{$pmcId}' ORDER BY `workshop_name`";
	$selectWorkshopRes = mysql_query($selectWorkshopQuery) or displayerror(mysql_error());
	if(mysql_num_rows($selectWorkshopRes) > 0){
		$selectWorkshop = <<<FORM
			<p>Choose Workshop</p>
			<form method="POST" action="./+prhead&subaction=viewWorkshopDetails">
				<select name='workshopId' id='workshopId'>
FORM;
		while($workshopList = mysql_fetch_assoc($selectWorkshopRes)){
			$selectWorkshop.=<<<DROPDOWN
			<option value="{$workshopList['workshop_id']}">{$workshopList['workshop_name']}</option>	
DROPDOWN;
		}
		$selectWorkshop.="</select><input type='submit'value='Select'></form>";
	}
	else
		$selectWorkshop = displayerror("No Workshops Found");
	return $selectWorkshop;
}

function viewWorkshopDetails($workshopId,$pmcId){
global $cmsFolder,$moduleFolder,$urlRequestRoot, $sourceFolder;
    $scriptFolder = "$urlRequestRoot/$cmsFolder/$moduleFolder/events";
    $workshopDetails =<<<SCRIPT
		<script src="$scriptFolder/events.js"></script>
        <script src="$scriptFolder/jquery.js"></script>
SCRIPT;
	$workshopDetails .= getWorkshopsList($pmcId);
	if(isset($_FILES['fileUploadField']['name'])){
		syncExcelFileWorkshop($pmcId,$workshopId,$_FILES['fileUploadField']['tmp_name'][0]);
	}

	$checkParticipantsQuery = "SELECT `user_id` FROM `events_workshop_participants` WHERE `page_moduleComponentId`='{$pmcId}' AND `workshop_id`='{$workshopId}' LIMIT 1";
	$checkParticipantsRes = mysql_query($checkParticipantsQuery) or displayerror(mysql_error());
	if(mysql_num_rows($checkParticipantsRes) == 0){
		//Show FileUpload Details
		$fileUploadableField=getFileUploadField('fileUploadField',"events");

	$workshopDetails.=<<<ADDROOMFORM
           <br/><br/>
           <form action="./+prhead&subaction=viewWorkshopDetails" method="post">
            	<input type="submit" name="downloadSampleFormat" value="Download Sample Form"><br/>
           </form>
           <form action="./+prhead&subaction=viewWorkshopDetails" method="post" enctype='multipart/form-data'>
	       $fileUploadableField
	       <input type='hidden' name='workshopId' value='{$workshopId}'>
	       <input type='submit' name='submit' value='Upload'>
	       </form>
ADDROOMFORM;
	}
	else{
		$workshopDetails.=<<<PRINTTABLE
		<br/><br/>
		<a href='./+prhead&subaction=downloadExcel&workshop_id=$workshopId'>Download Details</a>
		<br/><br/>
		<form method='POST' action='./+prhead&subaction=printCerti'>
		<input type='hidden' name='workshopId' value='{$workshopId}'>
		<input type='submit' value='Print Certificates PDF'>
		</form>
	
PRINTTABLE;
		//$downloadTable = getUserDetailsTable($pmcId,$eventId);
		//$eventDetails.=displayExcelForTable($downloadTable);
	
		$workshopParticipants=displayWorkshopParticipants($pmcId,$workshopId);
		//displayExcelForTable($eventParticipants);
		$workshopDetails .= $workshopParticipants;
	}
	return $workshopDetails;	
}

function validateNewProcurement($pageModuleComponentId){
		$isValid=true;

		if($_POST['newProc']==""){
				$isValid=false;
				exit();
function displayWorkshopParticipants($pmcId,$workshopId){
global $STARTSCRIPTS;
	$smarttable = smarttable::render(array('participants_table'),null);
	$STARTSCRIPTS.="initSmartTable();";
	$participantsTable = "";
	$selectParticipantsQuery = "SELECT `events_workshop_participants`.`user_id`,`events_workshop_participants`.`user_team_id` FROM `events_workshop_participants` WHERE `events_workshop_participants`.`page_moduleComponentId`='{$pmcId}' 
				AND `events_workshop_participants`.`workshop_id`='{$workshopId}' AND `events_workshop_participants`.`user_id` ORDER BY `events_workshop_participants`.`user_team_id`";
//	return $selectParticipantsQuery;
	$selectParticipantsRes = mysql_query($selectParticipantsQuery) or displayerror(mysql_error());
	$participantsTable.=<<<TABLE
				$smarttable<table id='participants_table' class='display' width='100%' border='1'><thead><tr><th>Team Id</th><th>PID</th><th>Name</th><th>College</th><th>Phone No.</th><th>Options</th><th>Print</th></tr></thead>
TABLE;
	while($participant = mysql_fetch_assoc($selectParticipantsRes)){
		$editRowId = "";
		$participantNameQuery = "SELECT `pragyanV3_users`.`user_fullname` FROM `pragyanV3_users` WHERE `user_id`='{$participant['user_id']}'";
		$participantNameRes = mysql_query($participantNameQuery) or displayerror(mysql_error());
		$participantsTable.="<tr id='partRow{$participant['user_id']}'><td>".$participant['user_team_id']."</td><td>".$participant['user_id']."</td><td><span class='userDataDisp{$participant['user_id']}'>".mysql_result($participantNameRes, 0)."</span><input type='text' class='userDataEditVal{$participant['user_id']}' value='".mysql_result($participantNameRes, 0)."' style='display:none'></td>";
		$editRowId.='-1,';
		$phNoFormId = retPnoneNoFormId();
		$collFormId = retCollFormId();
		$participantsDetailsQuery = "SELECT `form_elementid`,`form_elementdata` FROM `form_elementdata` WHERE `page_moduleComponentId`='{retglobalPmcId()}' AND `user_id`='{$participant['user_id']}' AND `form_elementid` IN ($phNoFormId,$collFormId) ORDER BY `form_elementid`";
		//1
		$participantsDetailsRes = mysql_query($participantsDetailsQuery) or displayerror(mysql_error());
		while($userDetails = mysql_fetch_assoc($participantsDetailsRes)){
			$editRowId.=$userDetails['form_elementid'].',';
			$participantsTable.="<td><span class='userDataDisp{$participant['user_id']}'>".$userDetails['form_elementdata']."</span><input type='text' class='userDataEditVal{$participant['user_id']}' value='{$userDetails['form_elementdata']}' style='display:none'></td>";
		}
		$editRowId=trim($editRowId,',');
		$participantsTable.=<<<BUTTON
			<td>
				<!--
				<form method='POST' class='userDataDisp{$participant['user_id']}' action='./+prhead&subaction=confirmParticipant' onsubmit='return confirmParticipant();'>
				<input type='hidden' value='{$participant['user_id']}' name='userId'>
				<input type='hidden' value='$workshopId' name='workshopId'>
				<input type='submit' value='Confirm'>
				</form>
				-->

				<button class='userDataDisp{$participant['user_id']}' onclick="editParticipant({$participant['user_id']},$workshopId)" value="Edit">Edit</button>
				<button class='userDataEdit{$participant['user_id']}' onclick="updateParticipant('prhead',{$participant['user_id']},{$participant['user_team_id']},0,'$editRowId',$workshopId)" style='display:none' value='Update'>Update</button>
				<button class='userDataEdit{$participant['user_id']}' onclick="cancelEditParticipant({$participant['user_id']},$workshopId)" style='display:none' value='Cancel'>Cancel</button>

				<!--<button onclick="confirmParticipant({$participant['user_id']},$workshopId)" value="Confirm">Confirm</button>-->
			</td>
			<td>
			<form method='POST' action='./+prhead&subaction=printIndividualCerti'>
			<input type='hidden' name='userId' value='{$participant['user_pid']}'>
			<input type='hidden' name='workshopId' value='{$eventId}'>
			<input type='submit' value='Print Certificate PDF'>
			</form>
			</td></tr>
BUTTON;
	}
	$participantsTable.="</table>";
	return $participantsTable;	
}

function syncExcelFileWorkshop($pmcId,$workshopId,$fileLoc	){
	$excelData = readExcelSheet($fileLoc);
	for($i=1;$i<=count($excelData);$i++){
		for($j=1;$j<=count($excelData[$i]);$j++){
			$userPid = $excelData[$i][$j];
			if($userPid[0] == 'P' || $userPid[0] == 'p'){
				$userPid = getUserIdFromBookletId($userPid,$pmcId);
			}
			if(!empty($excelData[$i][$j])){
				$checkDuplicateQuery = "SELECT `user_id` FROM `events_workshop_participants` WHERE `page_moduleComponentId`='{$pmcId}' AND `workshop_id`='{$workshopId}' AND `user_id`='{$userPid}'";
				$checkDuplicateRes = mysql_query($checkDuplicateQuery) or displayerror(mysql_error());
				if(mysql_num_rows($checkDuplicateRes) == 0){
					$saveUserIdQuery = "INSERT INTO `events_workshop_participants`(`page_moduleComponentId`,`workshop_id`,`user_id`,`user_team_id`) VALUES('{$pmcId}','{$workshopId}','{$userPid}','{$i}')";
					$saveUserIdRes = mysql_query($saveUserIdQuery) or displayerror(mysql_error());
				}
			}
>>>>>>> 2a99033... added workshop details
		}
	}
}

function editWorkshopParticipant($gotoaction,$pmcId,$workshopId,$formId,$userId,$teamId,$rowValue,$rowId){
	$rowValueArray = explode(",",$rowValue);
	$rowIdArray = explode(",",$rowId);
	$updatedRow = "";
	$updatedRow.="<td>".$teamId."</td><td>".$userId."</td>";
	for($i=0;$i<sizeof($rowValueArray);$i++){
/*
		$insertEditedRowQuery = "INSERT INTO `events_edited_form`(`page_moduleComponentId`,`form_id`,`user_id`,`form_elementid`,`form_elementdata`) 
			VALUES('{$pmcId}','{$formId}','{$userId}','{$rowIdArray[$i]}','{$rowValueArray[$i]}') ON DUPLICATE KEY UPDATE `form_elementdata`='{$rowValueArray[$i]}'";
		$insertEditedRowRes = mysql_query($insertEditedRowQuery) or displayerror(mysql_error());*/
		if($rowIdArray[$i] == -1){
			$editNameQuery = "UPDATE `pragyanV3_users` SET `user_fullname`='{$rowValueArray[$i]}' WHERE `user_id`='{$userId}'";
			$editNameRes = mysql_query($editNameQuery) or displayerror(mysql_error());
		}
		else{
			$editValuesQuery = "UPDATE `form_elementdata` SET `form_elementdata`='{$rowValueArray[$i]}' 
					WHERE `user_id`='{$userId}' AND `form_elementid`='{$rowIdArray[$i]}' AND `page_moduleComponentId`='{retglobalPmcId()}'";
					//3
			$editValuesRes = mysql_query($editValuesQuery) or displayerror(mysql_error());
		}
		$updatedRow.="<td><span class='userDataDisp{$userId}'>".$rowValueArray[$i]."</span><input type='text' class='userDataEditVal{$userId}' value='{$rowValueArray[$i]}' style='display:none'></td>";
	}
	$updatedRow.=<<<BUTTON
				<td>
				<button class='userDataDisp{$userId}' onclick="editParticipant({$userId},{$workshopId})" value="Edit">Edit</button>
				<button class='userDataEdit{$userId}' onclick="updateParticipant('$gotoaction',{$userId},{$teamId},{$formId},'{$rowId}',{$workshopId})" style='display:none' value='Update'>Update</button>
				<button class='userDataEdit{$userId}' onclick="cancelEditParticipant({$userId},{$workshopId})" style='display:none' value='Cancel'>Cancel</button>
				</td>
				<td>
				<form method='POST' action='./+{$gotoaction}&subaction=printIndividualCerti'>
				<input type='hidden' name='userId' value='{$participant['user_id']}'>
				<input type='hidden' name='eventId' value='{$workshopId}'>
				<input type='submit' value='Print Certificate PDF'>
				</form>
				</td>
				</tr>
BUTTON;
	return $updatedRow;
}


<<<<<<< HEAD
$procurementDetails =<<<TABLE
<<<<<<< HEAD
        <script src="$scriptFolder/events.js"></script>
        <script src="$scriptFolder/jquery.js"></script>
=======

		<script src="$scriptFolder/events.js"></script>
		<script src="$scriptFolder/jquery.js"></script>
>>>>>>> 148e2c1... Fixed schedule merge error.
		$smarttablestuff
		<table class="display" id="table_procurement_details" width="100%" border="1">
		<thead>
				<tr>
				<th>Serial no.</th>
				<th>Event Name</th>
				<th>Procurement</th>
				<th>Quantity</th>
				<th>Date</th>
				<th>Start time</th>
				<th>End time</th>  
				<th></th>
				</tr>
		</thead>
TABLE;
$cnt=1;
while($res = mysql_fetch_assoc($selectRes)) {
$selQuery="SELECT * FROM `events_details` WHERE `event_name`='{$res['event_name']}'";
$selRes=mysql_query($selQuery) or displayerror(mysql_error());
$selRes = mysql_fetch_assoc($selRes);
$procurementDetails .=<<<TR
<<<<<<< HEAD
<<<<<<< HEAD
	  <tr>        
	   <td>{$cnt}</td>
	   <td>{$res['event_name']}</td>
	   <td>{$res['procurement_name']}</td>
	   <td>{$res['quantity']}</td>
	   <td>
			<button onclick="deleteProcurement({$cnt});" value="DELETE" />DELETE</button>
			
			<form method="POST"  action="./+ochead&subaction=editProcurement">
			<input type="submit" name="" value="EDIT"/>
			</form>
	   </td>
	  </tr>
=======
          <tr>        
=======

		  <tr>        
>>>>>>> 148e2c1... Fixed schedule merge error.
		   <td>{$cnt}</td>
		   <td>{$res['event_name']}</td>
		   <td>{$res['procurement_name']}</td>
		   <td>{$res['quantity']}</td>
		   <td>{$selRes['event_date']}</td>
		   <td>{$selRes['event_start_time']}</td>
		   <td>{$selRes['event_end_time']}</td>
		   <td>
				<button onclick="deleteProcurement({$cnt});" value="DELETE">DELETE</button>
				
				<form method="POST"  action="./+ochead">
				<input type="submit" name="" value="EDIT"/>
				<input style="visibility:hidden;" name="eventnum" id="eventnum" value="{$cnt}" />
				</form> 
<<<<<<< HEAD
           </td>
          </tr>
>>>>>>> 18bb9da... edit facility added (ochead)
=======
		   </td>
		  </tr>
>>>>>>> 148e2c1... Fixed schedule merge error.
TR;
  $cnt++;
  }
$procurementDetails .=<<<TABLEEND
		</table>
TABLEEND;
  return $procurementDetails;
=======

function displayQA($pmcid){
	$selectEventQuery = "SELECT `event_id`,`event_name` FROM `events_details` WHERE `page_moduleComponentId`='{$pmcid}' AND `event_id` NOT IN (SELECT `event_id` FROM `events_locked` WHERE `page_moduleComponentId` = '{$pmcid}') ORDER BY `event_name`";
	$selectEventRes = mysql_query($selectEventQuery) or displayerror(mysql_error());
	if(mysql_num_rows($selectEventRes) > 0){
		$selectEvent = <<<FORM
			<p>Choose Event:</p>
			<form method="POST" action="./+qa&subaction=viewEvent">
				<select name='eventId' id='eventId'>
FORM;
		while($eventList = mysql_fetch_assoc($selectEventRes)){
			$selectEvent.=<<<DROPDOWN
			<option value="{$eventList['event_id']}">{$eventList['event_name']}</option>	
DROPDOWN;
		}
		$selectEvent.="</select><input type='submit'value='Select'></form>";
	}
	else
		$selectEvent = displayerror("No Events Found");
	return $selectEvent;
}

>>>>>>> 2a99033... added workshop details

function editParticipantRank($gotoaction,$pmcId,$eventId,$userId,$newRank){
	$updateRankQuery = "UPDATE `events_result` SET `user_rank` = '{$newRank}' WHERE `page_moduleComponentId`='{$pmcId}' AND `user_id`='{$userId}' AND `event_id`='{$eventId}'";
	$updateRankRes = mysql_query($updateRankQuery) or displayerror(mysql_error());
	return $newRank;
}




// ~~~~~~~~~~~~~~~~ QA Head  ~~~~~~~~~~~~~~~~~~~`

function qaHeadOptions($pmcId){
	$selectEventQuery = "SELECT `event_id`,`event_name` FROM `events_details` WHERE `page_moduleComponentId`='{$pmcId}' ORDER BY `event_name`";
	$selectEventRes = mysql_query($selectEventQuery) or displayerror(mysql_error());
	if(mysql_num_rows($selectEventRes) > 0){
		$selectEvent = <<<FORM
		 	<p>Choose Event</p>
			<form method="POST" action="./+qahead&subaction=viewEvent">
				<select name='eventId'>
FORM;
		while($eventList = mysql_fetch_assoc($selectEventRes)){
			$selectEvent.=<<<DROPDOWN
			<option value="{$eventList['event_id']}">{$eventList['event_name']}</option>	
DROPDOWN;
		}
		$selectEvent.="</select><input type='submit'value='Select'></form>";
	}
	else
		$selectEvent = displayerror("No Events Found");
	return $selectEvent;
}


//~~~~~~~~~~~~~~~    PR   ~~~~~~~~~~~~~~~~

function displayPR($gotoaction,$pmcId){
	$selectLockedEventsQuery = "SELECT `events_details`.`event_id`, `events_details`.`event_name` FROM `events_details` INNER JOIN `events_locked` 
		ON `events_locked`.`event_id` = `events_details`.`event_id` AND `events_locked`.`page_moduleComponentId` = `events_details`.`page_moduleComponentId` 
		AND `events_details`.`page_moduleComponentId` = '{$pmcId}' ORDER BY `event_name`";
	$selectLockedEventsRes = mysql_query($selectLockedEventsQuery) or displayerror(mysql_error());
	if(mysql_num_rows($selectLockedEventsRes) > 0){
		$selectEvent = <<<FORM
			<p>Choose Event</p>
			<form method="POST" action="./+{$gotoaction}&subaction=viewEvent">
				<select name='eventId'>
FORM;
		while($eventList = mysql_fetch_assoc($selectLockedEventsRes)){
			$selectEvent.=<<<DROPDOWN
			<option value="{$eventList['event_id']}">{$eventList['event_name']}</option>	
DROPDOWN;
		}
		$selectEvent.="</select><input type='submit'value='Select'></form>";
	}
	else
		$selectEvent = displayerror("No Events Found");
	return $selectEvent;
}

?>
