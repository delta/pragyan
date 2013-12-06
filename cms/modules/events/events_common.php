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
			$postValue=mysqli_real_escape_string($postValue);
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

function selectSubaction(){
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

function deleteEvent($eventid, $pmcid){
	$deleteQuery="DELETE FROM `events_details` WHERE `event_id`='{$eventid}' AND `page_moduleComponentId`='{$pmcid}';";
	$deletetRes=mysql_query($deleteQuery) or displayerror(mysql_error());
	if ($deletetRes==1) {
		echo("Success");
	}
	else echo("error");
	exit();
}


//~~~~~~~~~~~~~~~~   QA   ~~~~~~~~~~~~~~~~~

function displayQA($pmcid){
	$selectEventQuery = "SELECT `event_id`,`event_name` FROM `events_details` WHERE `page_moduleComponentId`='{$pmcid}' ORDER BY `event_name`";
	$selectEventRes = mysql_query($selectEventQuery) or displayerror(mysql_error());
	if(mysql_num_rows($selectEventRes) > 0){
		$selectEvent = <<<FORM
			<form method="POST" action="./+qa&subaction=viewEvent">
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

function eventParticipants($pmcId,$eventId){
	global $cmsFolder,$moduleFolder,$urlRequestRoot, $sourceFolder;
	$scriptFolder = "$urlRequestRoot/$cmsFolder/$moduleFolder/events";
//	$selectNameQuery="SELECT `events_form`.`form_id`,`events_form`.`event_id`,`form_elementdata`.`form_elementdata`,
//	`form_elementdata`.`page_moduleComponentId`,`pragyancms_users`.`user_id`,`pragyancms_users`.`user_name` FROM `form_elementdata`,`events_form`,`pragyancms_users` WHERE `form_elementdata`.`page_moduleComponentId`=`form_id`"
	global $cmsFolder,$moduleFolder,$urlRequestRoot, $sourceFolder;
	global $STARTSCRIPTS;
	$smarttable = smarttable::render(array('reg_users_table'),null);
	$STARTSCRIPTS.="initSmartTable();";

	$selectTableHeadQuery = "SELECT `form_elementdesc`.`form_elementdisplaytext` FROM `form_elementdesc`
			INNER JOIN `events_form` ON `form_elementdesc`.`page_moduleComponentId` = `events_form`.`form_id`
			AND `events_form`.`event_id` = '{$eventId}' AND `events_form`.`page_moduleComponentId`='{$pmcId}'";

	$selectTableHeadRes = mysql_query($selectTableHeadQuery) or displayerror(mysql_error());

	if(mysql_num_rows($selectTableHeadRes) > 0){
		$participantsList =<<<TABLE
			<script src="$scriptFolder/events.js"></script>
			<script src="$scriptFolder/jquery.js"></script>
			$smarttable<table id='reg_users_table' class='display' width='100%' border='1'><thead><tr>
TABLE;
		while($tableHead = mysql_fetch_assoc($selectTableHeadRes)){
			$participantsList.="<th>{$tableHead['form_elementdisplaytext']}</th>";
		}

		$participantsList.="<th>Confirm</th></tr></thead>";

		$selectIdQuery = "SELECT DISTINCT `form_elementdata`.`user_id` FROM `form_elementdata` 
			INNER JOIN `events_form` ON `form_elementdata`.`page_moduleComponentId` = `events_form`.`form_id`
			AND `events_form`.`event_id` = '{$eventId}' AND `events_form`.`page_moduleComponentId`='{$pmcId}'
			WHERE `form_elementdata`.`user_id` NOT IN (SELECT `events_confirmed_participants`.`user_id` FROM `events_confirmed_participants`)
			ORDER BY `form_elementdata`.`user_id`";

		$selectIdRes = mysql_query($selectIdQuery) or displayerror(mysql_error());

		while($regId = mysql_fetch_assoc($selectIdRes)){
		
			$selectDetailsQuery = "SELECT `form_elementdata`.`form_elementdata`,`form_elementdata`.`user_id` 
				FROM `form_elementdata` INNER JOIN `events_form` ON `form_elementdata`.`page_moduleComponentId` = `events_form`.`form_id`
				AND `events_form`.`event_id` = '{$eventId}' AND `events_form`.`page_moduleComponentId`='{$pmcId}' AND 
				`form_elementdata`.`user_id` = '".$regId['user_id']."'";

			$selectDetailsRes = mysql_query($selectDetailsQuery) or displayerror(mysql_error());
			$participantsList.="<tr>";

			while($userDetails = mysql_fetch_assoc($selectDetailsRes)){
				$participantsList.="<td>".$userDetails['form_elementdata']."</td>";
			}
			$participantsList.=<<<BUTTON
				<td>
				<form method='POST' action='./+qa&subaction=confirmParticipation'>
				<input type='hidden' value='{$regid['user_id']}' name='userid'>
				<input type='hidden' value='$eventId' name='eventid'>
				<input type='submit' value='Confirm'>
				</form>
				<!--<button onclick="confirmParticipant({$regId['user_id']},$eventId)" value="Confirm">Confirm</button>-->
				</td>
				</tr>
BUTTON;
		}
		$participantsList.="</table>";
	}
	else
		$participantsList = displayerror("Event Form Doesn't Exist.");
	return $participantsList;
}

function confirmParticipation($pmcid,$eventId,$userId){
	$confirmQuery = "INSERT INTO `events_confirmed_participants`(`page_moduleComponentId`,`event_id`,`user_id`) VALUES('{$pmcid}','{$eventId}','{$userId}')";
	$confirmRes = mysql_query($confirmQuery) or displayerror(mysql_error());
	return "Successfully deleted.";
}


?>
