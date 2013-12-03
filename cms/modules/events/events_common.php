<?php

function validateEventData($pageModuleComponentId){
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
	if($isValid){
		//insert data
		foreach ($_POST as $postValue){
			$postValue=escape($postValue);
		}
		//Query to insert into the db
		$insertQuery="INSERT INTO `events_details` (`event_name`, `event_date`, `event_start_time`, `event_end_time`, "
					."`event_venue`, `event_desc`, `event_last_update_time`, `event_image`, `page_moduleComponentId`, "
					."`event_loc_x`, `event_loc_y`) VALUES ('{$_POST['eventName']}', '{$_POST['eventDate']}', '{$_POST['eventStartTime']}', '{$_POST['eventEndTime']}', "
					."'{$_POST['eventVenue']}', '{$_POST['eventDesc']}', CURRENT_TIME(), '', '{$pageModuleComponentId}', '{$_POST['lng']}', '{$_POST['lat']}');";
		$insertRes=mysql_query($insertQuery) or displayerror(mysql_error());
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
		<form method="POST"  action="./+eventshead&subaction=editEvent">
			<input type="submit" name="" value="EDIT"/>
			<input type="hidden" name="eventId" value="{$res['event_id']}" />
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

function selectEventsHeadSubaction(){
	//form to select the subaction
	$subactionForm=<<<SFORM
	<p>
		Select an option:
	</p>
	<form method="GET"  action="./+eventshead&subaction=viewAll">
		<input type="submit" name="" value="VIEW ALL"/>
	</form>
	<form method="GET"  action="./+eventshead&subaction=addEvent">
		<input type="submit" name="" value="ADD EVENT"/>
	</form>
SFORM;
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
	return "MAPS";
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
	$eventsQuery="SELECT * FROM `events_details` WHERE '{$lastdate}'<=`event_last_update_time` AND `page_moduleComponentId`='{$pmcid}'" //event_date>='{$date1}' AND  <---add to query later
				."ORDER BY event_date ASC LIMIT {$prod}, {$ipp};";
	$eventsRes=mysql_query($eventsQuery) or displayerror(mysql_error());
	while($row=mysql_fetch_array($eventsRes)){
		$event=array(
			"event_id"=> $row['event_id'], 
			"event_name"=> $row['event_name'],
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

?>