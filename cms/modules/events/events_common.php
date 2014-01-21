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
				<div id="allEventGoogleMap" style="width:100%;height:100%;"></div>

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
								."WHERE '{$lastdate}'<=`event_last_update_time` AND `page_moduleComponentId`='{$pmcid}'" //event_date>='{$date1}' AND  <---add to query later
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

function displayQA($pmcid){
	$selectEventQuery = "SELECT `event_id`,`event_name` FROM `events_details` WHERE `page_moduleComponentId`='{$pmcid}' AND `event_id` NOT IN (SELECT `event_id` FROM `events_locked` WHERE `page_moduleComponentId` = '{$pmcid}') ORDER BY `event_name`";
	$selectEventRes = mysql_query($selectEventQuery) or displayerror(mysql_error());
	if(mysql_num_rows($selectEventRes) > 0){
		$selectEvent = <<<FORM
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

function eventParticipants($gotoaction,$pmcId,$eventId){
	global $cmsFolder,$moduleFolder,$urlRequestRoot, $sourceFolder;
	$scriptFolder = "$urlRequestRoot/$cmsFolder/$moduleFolder/events";
//	$selectNameQuery="SELECT `events_form`.`form_id`,`events_form`.`event_id`,`form_elementdata`.`form_elementdata`,
//	`form_elementdata`.`page_moduleComponentId`,`pragyancms_users`.`user_id`,`pragyancms_users`.`user_name` FROM `form_elementdata`,`events_form`,`pragyancms_users` WHERE `form_elementdata`.`page_moduleComponentId`=`form_id`"
	global $cmsFolder,$moduleFolder,$urlRequestRoot, $sourceFolder;
	global $STARTSCRIPTS;
	$smarttable = smarttable::render(array('reg_users_table'),null);
	$STARTSCRIPTS.="initSmartTable();";

	if($gotoaction == 'qa')
		$participantsList=displayQA($pmcId);
	else if($gotoaction == 'qahead'){
		$participantsList=qaHeadOptions($pmcId);
	}

	$participantsList.=<<<INCLUDE
			<script src="$scriptFolder/events.js"></script>
			<script src="$scriptFolder/jquery.js"></script>
INCLUDE;

	$eventNameQuery = "SELECT `event_name` FROM `events_details` WHERE `event_id` = '{$eventId}'";
	$eventNameRes = mysql_query($eventNameQuery) or displayerror(mysql_error());

	while($eventName = mysql_fetch_assoc($eventNameRes))
		$participantsList.="<strong>{$eventName['event_name']}</strong>";
	
	if($gotoaction== 'qahead'){
		//Option to Unlocked if event locked.
		$checkLockedQuery = "SELECT `event_id` FROM `events_locked` WHERE `page_moduleComponentId`='{$pmcId}' AND `event_id`='{$eventId}'";
		$checkLockedRes = mysql_query($checkLockedQuery) or displayerror(mysql_error());
		if(mysql_num_rows($checkLockedRes) == 1){
			$participantsList.=<<<CONFIRMED
				<p>Event Locked.</p>
				<form method='POST' action='./+qahead&subaction=unlockEvent' onsubmit='return unlockConfirm();'>
				<input type='hidden' value='{$eventId}' name='eventId'>
				<input type='submit' id='lockButton' value='UNLOCK EVENT'>
				</form>
CONFIRMED;
			return $participantsList;
		}
	}

	$participantsList.=<<<CONFIRMED
		<form method='POST' action='./+{$gotoaction}&subaction=viewConfirmed'>
		<input type='hidden' name='eventId' value='{$eventId}'/>
		<input type='submit' value='View Confirmed List'/></form>
CONFIRMED;
	

	$selectTableHeadQuery = "SELECT `form_elementdesc`.`form_elementdisplaytext` FROM `form_elementdesc`
			INNER JOIN `events_form` ON `form_elementdesc`.`page_moduleComponentId` = `events_form`.`form_id`
			AND `events_form`.`event_id` = '{$eventId}' AND `events_form`.`page_moduleComponentId`='{$pmcId}'";

	$selectTableHeadRes = mysql_query($selectTableHeadQuery) or displayerror(mysql_error());

	if(mysql_num_rows($selectTableHeadRes) > 0){
		$participantsList .=<<<TABLE
			$smarttable<table id='reg_users_table' class='display' width='100%' border='1'><thead><tr>
TABLE;
		while($tableHead = mysql_fetch_assoc($selectTableHeadRes)){
			$participantsList.="<th>{$tableHead['form_elementdisplaytext']}</th>";
		}

		$participantsList.="<th>Options</th></tr></thead>";

		$selectIdQuery = "SELECT DISTINCT `form_elementdata`.`user_id`,`form_elementdata`.`page_moduleComponentId` FROM `form_elementdata` 
			INNER JOIN `events_form` ON `form_elementdata`.`page_moduleComponentId` = `events_form`.`form_id`
			AND `events_form`.`event_id` = '{$eventId}' AND `events_form`.`page_moduleComponentId`='{$pmcId}'
			WHERE `form_elementdata`.`user_id` NOT IN (SELECT `events_confirmed_participants`.`user_id` FROM `events_confirmed_participants` WHERE `events_confirmed_participants`.`page_moduleComponentId` =  '{$pmcId}')
			ORDER BY `form_elementdata`.`user_id`";

		$selectIdRes = mysql_query($selectIdQuery) or displayerror(mysql_error());

		while($regId = mysql_fetch_assoc($selectIdRes)){

			//Check if edited form values exist in events_edited_form for the user

			$selectModifiedDetailsQuery = "SELECT `events_edited_form`.`form_elementdata`,`events_edited_form`.`form_elementid`,`events_edited_form`.`user_id`
				FROM `events_edited_form` INNER JOIN `events_form` ON `events_edited_form`.`form_id`=`events_form`.`form_id`
				AND `events_form`.`event_id`='{$eventId}' AND `events_edited_form`.`page_moduleComponentId`='{$pmcId}' AND
				`events_edited_form`.`user_id`='".$regId['user_id']."'";

			$selectDetailsRes = mysql_query($selectModifiedDetailsQuery) or displayerror(mysql_error());

			if(mysql_num_rows($selectDetailsRes) == 0){

				//If not modified, select original data from form_elementdata
		
				$selectDetailsQuery = "SELECT `form_elementdata`.`form_elementdata`,`form_elementdata`.`form_elementid` 
					FROM `form_elementdata` INNER JOIN `events_form` ON `form_elementdata`.`page_moduleComponentId` = `events_form`.`form_id`
					AND `events_form`.`event_id` = '{$eventId}' AND `events_form`.`page_moduleComponentId`='{$pmcId}' AND 
					`form_elementdata`.`user_id` = '".$regId['user_id']."'";

				$selectDetailsRes = mysql_query($selectDetailsQuery) or displayerror(mysql_error());
			}
			$participantsList.="<tr id='partRow{$regId['user_id']}'>";

			//$editRowValue="";
			$editRowId="";
			//$editRowId=array();

			while($userDetails = mysql_fetch_assoc($selectDetailsRes)){
				$participantsList.="<td><span class='userDataDisp{$regId['user_id']}'>".$userDetails['form_elementdata']."</span><input type='text' class='userDataEditVal{$regId['user_id']}' value='{$userDetails['form_elementdata']}' style='display:none'></td>";
				//$editRowValue.=$userDetails['form_elementdata'].",";
				//array_push($editRowId, $userDetail['form_elementid']);
				$editRowId.=$userDetails['form_elementid'].",";
			}

		//	$editRowValue = rtrim($editRowValue,",");
			$editRowId = rtrim($editRowId,",");
			//return $editRowId;
			$participantsList.=<<<BUTTON
				<td>

				<form method='POST' class='userDataDisp{$regId['user_id']}' action='./+{$gotoaction}&subaction=confirmParticipant' onsubmit='return confirmParticipant();'>
				<input type='hidden' value='{$regId['user_id']}' name='userId'>
				<input type='hidden' value='$eventId' name='eventId'>
				<input type='submit' value='Confirm'>
				</form>

				<button class='userDataDisp{$regId['user_id']}' onclick="editParticipant({$regId['user_id']},$eventId)" value="Edit">Edit</button>
				<button class='userDataEdit{$regId['user_id']}' onclick="updateParticipant('$gotoaction',{$regId['user_id']},{$regId['page_moduleComponentId']},'$editRowId',$eventId)" style='display:none' value='Update'>Update</button>
				<button class='userDataEdit{$regId['user_id']}' onclick="cancelEditParticipant({$regId['user_id']},$eventId)" style='display:none' value='Cancel'>Cancel</button>

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

function unconfirmParticipation($gotoaction,$pmcid,$eventId,$userId){
	$unconfirmQuery = "DELETE FROM `events_confirmed_participants` WHERE `page_moduleComponentId`='{$pmcid}' AND `event_id`='{$eventId}' AND `user_id`='{$userId}'";
	$unconfirmRes = mysql_query($unconfirmQuery) or displayerror(mysql_error());
	$userRankDelQuery = "DELETE FROM `events_result` WHERE `page_moduleComponentId`='{$pmcid}' AND `event_id`='{$eventId}' AND `user_id`='{$userId}'";
	$userRankDelRes = mysql_query($userRankDelQuery) or displayerror(mysql_error());
	//return "Successfully deleted.";
	$redirectData = <<<POSTDATA
		<form method='POST' action='./+{$gotoaction}&subaction=viewConfirmed' name='postDataForm'>
		<input type='hidden' name='eventId' value='{$eventId}'/>
		</form>
		<script type='text/javascript'>
			document.postDataForm.submit();
		</script>
POSTDATA;
	return $redirectData;
}


function editParticipant($gotoaction,$pmcId,$eventId,$formId,$userId,$rowValue,$rowId){
	$rowValueArray = explode(",",$rowValue);
	$rowIdArray = explode(",",$rowId);
	//$rowValueArray = $rowValue;
	//$rowIdArray = $rowId;
	//return $rowValue." AND ".$rowId;
	$updatedRow = "";
	for($i=0;$i<sizeof($rowValueArray);$i++){
		$insertEditedRowQuery = "INSERT INTO `events_edited_form`(`page_moduleComponentId`,`form_id`,`user_id`,`form_elementid`,`form_elementdata`) 
			VALUES('{$pmcId}','{$formId}','{$userId}','{$rowIdArray[$i]}','{$rowValueArray[$i]}') ON DUPLICATE KEY UPDATE `form_elementdata`='{$rowValueArray[$i]}'";
		$insertEditedRowRes = mysql_query($insertEditedRowQuery) or displayerror(mysql_error());
		$updatedRow.="<td><span class='userDataDisp{$userId}'>".$rowValueArray[$i]."</span><input type='text' class='userDataEditVal{$userId}' value='{$rowValueArray[$i]}' style='display:none'></td>";
	}
	$updatedRow.=<<<BUTTON
				<td>

				<form method='POST' class='userDataDisp{$userId}' action='./+qa&subaction=confirmParticipant' onsubmit='return confirmParticipant();'>
				<input type='hidden' value='{$userId}' name='userId'>
				<input type='hidden' value='{$eventId}' name='eventId'>
				<input type='submit' value='Confirm'>
				</form>

				<button class='userDataDisp{$userId}' onclick="editParticipant({$userId},$eventId)" value="Edit">Edit</button>
				<button class='userDataEdit{$userId}' onclick="updateParticipant({$userId},{$pmcId},{$rowId})" style='display:none' value='Update'>Update</button>
				<button class='userDataEdit{$userId}' onclick="cancelEditParticipant({$userId},{$eventId})" style='display:none' value='Cancel'>Cancel</button>
				</td>
BUTTON;
	/*$redirectData = <<<POSTDATA
		<form method='POST' action='./+qa&subaction=viewEvent' name='postDataForm'>
		<input type='hidden' name='eventId' value='{$eventId}' />
		</dorm>
		<script type='text/javascript'>
			document.postDataForm.submit();
		</script>
POSTDATA;*/
	return $updatedRow;
}

function viewConfirmedParticipants($gotoaction,$pmcId,$eventId){
	global $cmsFolder,$moduleFolder,$urlRequestRoot, $sourceFolder;
	$scriptFolder = "$urlRequestRoot/$cmsFolder/$moduleFolder/events";
	global $STARTSCRIPTS;
	$smarttable = smarttable::render(array('reg_users_table'),null);
	$STARTSCRIPTS.="initSmartTable();";

	$confirmedList=displayQA($pmcId);

	$eventNameQuery = "SELECT `event_name` FROM `events_details` WHERE `event_id` = '{$eventId}'";
	$eventNameRes = mysql_query($eventNameQuery) or displayerror(mysql_error());

	while($eventName = mysql_fetch_assoc($eventNameRes))
		$confirmedList.="<strong>{$eventName['event_name']}</strong>";


	$confirmedList.=<<<FORM
		<form method='POST' action='./+{$gotoaction}&subaction=viewEvent'>
		<input type='hidden' value='{$eventId}' name='eventId'>
		<input type='submit' value='View Unconfirmed List'>
		</form>
		<form method='POST' action='./+{$gotoaction}&subaction=lockEvent' onsubmit='return lockConfirm();'>
		<input type='hidden' value='{$eventId}' name='eventId'>
		<input type='submit' id='lockButton' value='LOCK EVENT'>
		</form>
FORM;

	
	$selectTableHeadQuery = "SELECT `form_elementdesc`.`form_elementdisplaytext` FROM `form_elementdesc`
			INNER JOIN `events_form` ON `form_elementdesc`.`page_moduleComponentId` = `events_form`.`form_id`
			AND `events_form`.`event_id` = '{$eventId}' AND `events_form`.`page_moduleComponentId`='{$pmcId}'";

	$selectTableHeadRes = mysql_query($selectTableHeadQuery) or displayerror(mysql_error());

	if(mysql_num_rows($selectTableHeadRes) > 0){
		$confirmedList .=<<<TABLE
			<script src="$scriptFolder/events.js"></script>
			<script src="$scriptFolder/jquery.js"></script>
			$smarttable<table id='reg_users_table' class='display' width='100%' border='1'><thead><tr>
TABLE;
		while($tableHead = mysql_fetch_assoc($selectTableHeadRes)){
			$confirmedList.="<th>{$tableHead['form_elementdisplaytext']}</th>";
		}

		$confirmedList.="<th>Rank</th><th>Options</th></tr></thead>";

		$selectIdQuery = "SELECT DISTINCT `form_elementdata`.`user_id` FROM `form_elementdata` 
			INNER JOIN `events_form` ON `form_elementdata`.`page_moduleComponentId` = `events_form`.`form_id`
			AND `events_form`.`event_id` = '{$eventId}' AND `events_form`.`page_moduleComponentId`='{$pmcId}'
			WHERE `form_elementdata`.`user_id` IN (SELECT `events_confirmed_participants`.`user_id` FROM `events_confirmed_participants` WHERE `events_confirmed_participants`.`page_moduleComponentId` =  '{$pmcId}')
			ORDER BY `form_elementdata`.`user_id`";


		$selectIdRes = mysql_query($selectIdQuery) or displayerror(mysql_error());

		while($regId = mysql_fetch_assoc($selectIdRes)){
		
			$selectDetailsQuery = "SELECT `form_elementdata`.`form_elementdata`,`form_elementdata`.`form_elementid` 
				FROM `form_elementdata` INNER JOIN `events_form` ON `form_elementdata`.`page_moduleComponentId` = `events_form`.`form_id`
				AND `events_form`.`event_id` = '{$eventId}' AND `events_form`.`page_moduleComponentId`='{$pmcId}' AND 
				`form_elementdata`.`user_id` = '".$regId['user_id']."'";

			$selectDetailsRes = mysql_query($selectDetailsQuery) or displayerror(mysql_error());
			$confirmedList.="<tr>";


			while($userDetails = mysql_fetch_assoc($selectDetailsRes)){
				$confirmedList.="<td>".$userDetails['form_elementdata']."</td>";
			}

			$selectRankQuery = "SELECT `user_rank` FROM `events_result` WHERE `user_id` = '{$regId['user_id']}' AND `page_moduleComponentId`='{$pmcId}' AND `event_id`='{$eventId}'";
			$selectRankRes = mysql_query($selectRankQuery) or displayerror(mysql_error());
			$userRank = mysql_result($selectRankRes,0);
			$confirmedList.=<<<BUTTONS
			<td><span id="userId{$regId['user_id']}"">$userRank</span>
			<input type="text" id="userIdEdit{$regId['user_id']}" value="{$userRank}" name="userRank" style="display:none" /></td>
			<td>
			<button class="editRankButtons{$regId['user_id']}" onclick="editParticipantRank({$regId['user_id']},$eventId)" value="Edit">Edit</button>
BUTTONS;
			if($gotoaction == 'qahead')
				$confirmedList.=<<<BUTTONS
					<form method='POST' class='userDataDisp{$userId}' action='./+qahead&subaction=unconfirmParticipant' onsubmit='return confirmParticipant();'>
					<input type='hidden' value='{$regId['user_id']}' name='userId'>
					<input type='hidden' value='{$eventId}' name='eventId'>
					<input type='submit' value='Unconfirm'>
					</form>

BUTTONS;
			$confirmedList.=<<<BUTTONS
			<button value="Update" class="editRankOptionButtons{$regId['user_id']}" onclick="confirmEditRank('$gotoaction',{$regId['user_id']},$eventId)" style="display:none">Update</button>
			<button value="Cancel" class="editRankOptionButtons{$regId['user_id']}" onclick="cancelEditRank({$regId['user_id']},$eventId)" style="display:none">Cancel</button>
			</td></tr>
BUTTONS;
		}
		$confirmedList.="</table>";
	}

	return $confirmedList;
}

function editParticipantRank($gotoaction,$pmcId,$eventId,$userId,$newRank){
	$updateRankQuery = "UPDATE `events_result` SET `user_rank` = '{$newRank}' WHERE `page_moduleComponentId`='{$pmcId}' AND `user_id`='{$userId}' AND `event_id`='{$eventId}'";
	$updateRankRes = mysql_query($updateRankQuery) or displayerror(mysql_error());
	return $newRank;
}

function lockEvent($gotoaction,$pmcId,$eventId){

	$lockEventQuery = "INSERT INTO `events_locked`(`page_moduleComponentId`,`event_id`) VALUES('{$pmcId}','{$eventId}')";
	$lockEventRes = mysql_query($lockEventQuery) or displayerror(mysql_error());
	return "Event Locked";
}

function unlockEvent($pmcId,$eventId){

	$unlockEventQuery = "DELETE FROM `events_locked` WHERE `page_moduleComponentId`='{$pmcId}' AND `event_id`='{$eventId}'";
	$unlockEventRes = mysql_query($unlockEventQuery) or displayerror(mysql_error());
	return "Event Unlocked";
}


// ~~~~~~~~~~~~~~~~ QA Head  ~~~~~~~~~~~~~~~~~~~`

function qaHeadOptions($pmcId){
	$selectEventQuery = "SELECT `event_id`,`event_name` FROM `events_details` WHERE `page_moduleComponentId`='{$pmcId}' ORDER BY `event_name`";
	$selectEventRes = mysql_query($selectEventQuery) or displayerror(mysql_error());
	if(mysql_num_rows($selectEventRes) > 0){
		$selectEvent = <<<FORM
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

function displayPR($pmcId){
	$selectLockedEventsQuery = "SELECT `events_details`.`event_id`, `events_details`.`event_name` FROM `events_details` INNER JOIN `events_locked` 
		ON `events_locked`.`event_id` = `events_details`.`event_id` AND `events_locked`.`page_moduleComponentId` = `events_details`.`page_moduleComponentId` 
		AND `events_details`.`page_moduleComponentId` = '{$pmcId}'";
	$selectLockedEventsRes = mysql_query($selectLockedEventsQuery) or displayerror(mysql_error());
	if(mysql_num_rows($selectLockedEventsRes) > 0){
		$selectEvent = <<<FORM
			<form method="POST" action="./+pr&subaction=viewEvent">
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

function viewEventResult($pmcId,$eventId){
	global $cmsFolder,$moduleFolder,$urlRequestRoot, $sourceFolder;
	$scriptFolder = "$urlRequestRoot/$cmsFolder/$moduleFolder/events";
	global $cmsFolder,$moduleFolder,$urlRequestRoot, $sourceFolder;
	global $STARTSCRIPTS;
	$resultList="";

	//Print the details
	$resultList.=<<<PRINT
		<form method='POST' action='./+pr&subaction=printCerti'>
		<input type='hidden' name='eventId' value='{$eventId}'>
		<input type='submit' value='Print PDF'>
		</form>
PRINT;

	$smarttable = smarttable::render(array('event_result_table'),null);
	$STARTSCRIPTS.="initSmartTable();";

	$selectTableHeadQuery = "SELECT `form_elementdesc`.`form_elementdisplaytext` FROM `form_elementdesc`
			INNER JOIN `events_form` ON `form_elementdesc`.`page_moduleComponentId` = `events_form`.`form_id`
			AND `events_form`.`event_id` = '{$eventId}' AND `events_form`.`page_moduleComponentId`='{$pmcId}'";

	$selectTableHeadRes = mysql_query($selectTableHeadQuery) or displayerror(mysql_error());
	if(mysql_num_rows($selectTableHeadRes) > 0){
		$resultList .=<<<TABLE
			<script src="$scriptFolder/events.js"></script>
			<script src="$scriptFolder/jquery.js"></script>
			$smarttable<table id='event_result_table' class='display' width='100%' border='1'><thead><tr>
TABLE;
		while($tableHead = mysql_fetch_assoc($selectTableHeadRes)){
			$resultList.="<th>{$tableHead['form_elementdisplaytext']}</th>";
		}

		$resultList.="<th>Rank</th></tr></thead>";

		$selectIdQuery = "SELECT DISTINCT `form_elementdata`.`user_id` FROM `form_elementdata` 
			INNER JOIN `events_form` ON `form_elementdata`.`page_moduleComponentId` = `events_form`.`form_id`
			AND `events_form`.`event_id` = '{$eventId}' AND `events_form`.`page_moduleComponentId`='{$pmcId}'
			WHERE `form_elementdata`.`user_id` IN (SELECT `events_confirmed_participants`.`user_id` FROM `events_confirmed_participants` WHERE `events_confirmed_participants`.`page_moduleComponentId` =  '{$pmcId}')
			ORDER BY `form_elementdata`.`user_id`";


		$selectIdRes = mysql_query($selectIdQuery) or displayerror(mysql_error());

		while($regId = mysql_fetch_assoc($selectIdRes)){
			$selectResultQuery = "SELECT `form_elementdata`.`form_elementdata`,`form_elementdata`.`user_id` 
				FROM `form_elementdata` INNER JOIN `events_form` ON `form_elementdata`.`page_moduleComponentId` = `events_form`.`form_id`
				AND `events_form`.`event_id` = '{$eventId}' AND `events_form`.`page_moduleComponentId`='{$pmcId}' AND 
				`form_elementdata`.`user_id` = '".$regId['user_id']."'";

			$selectResultRes = mysql_query($selectResultQuery) or displayerror(mysql_error());

			$resultList.="<tr>";

			while($userDetails = mysql_fetch_assoc($selectResultRes)){
				$resultList.="<td>".$userDetails['form_elementdata']."</td>";
			}

			$selectRankQuery = "SELECT `user_rank` FROM `events_result` WHERE `user_id` = '{$regId['user_id']}' AND `page_moduleComponentId`='{$pmcId}' AND `event_id`='{$eventId}'";
			$selectRankRes = mysql_query($selectRankQuery) or displayerror(mysql_error());

			$resultList.="<td>".mysql_result($selectRankRes,0)."</td>";
		}
		$resultList.="</table>";
	}

	return $resultList;
}

function printCertificates($pmcId,$eventId){
	global $urlRequestRoot,$sourceFolder,$templateFolder,$cmsFolder,$moduleFolder;
	//Get User Id and User Rank, Generate Image by passing these values
	//Select user id and user rank where event id and pmc id and user rank not -1 confirmed participants
	//for each participant print image and put it in a diff page
	require_once("$sourceFolder/$moduleFolder/events/html2pdf/html2pdf.class.php");	
	require_once("$sourceFolder/$moduleFolder/events/events_certi_image2.php");
	$selectIdQuery = "SELECT DISTINCT `form_elementdata`.`user_id`,`events_result`.`user_rank` FROM `form_elementdata`
			INNER JOIN `events_form` ON `form_elementdata`.`page_moduleComponentId` = `events_form`.`form_id`
			AND `events_form`.`event_id` = '{$eventId}' AND `events_form`.`page_moduleComponentId`='{$pmcId}'
			INNER JOIN `events_result` ON `events_result`.`user_id` = `form_elementdata`.`user_id` AND `events_result`.`page_moduleComponentId`='{$pmcId}'
			WHERE `form_elementdata`.`user_id` IN (SELECT `events_confirmed_participants`.`user_id` FROM `events_confirmed_participants` WHERE `events_confirmed_participants`.`page_moduleComponentId` =  '{$pmcId}')
			AND `events_result`.`user_rank` IN ('0','1','2','3','5') ORDER BY `events_result`.`user_rank`";


	$selectIdRes = mysql_query($selectIdQuery) or displayerror(mysql_error());
	$certiImagePage = "";
	while($printParticipant = mysql_fetch_assoc($selectIdRes)){
	//	$name = urlencode("AMAL SYRIAC");
		//return "$sourceFolder/$moduleFolder/events/events_certi_image.php?userId=".$printParticipant['user_id']."&pmcId=".$pmcId."&eventId=".$eventId."&userRank=".$printParticipant['user_rank'];
		//?userId=".$printParticiprintprintpant['user_id']."&pmcId=".$pmcId."&eventId=".$eventId."&userRank=".$printParticipant['user_rank']."
	//	/*				TESTING


	$getCertiImgQuery = "SELECT `certificate_id`,`certificate_image` FROM `events_certificate` WHERE `user_rank` = '{$printParticipant['user_rank']}' AND `page_moduleComponentId`='{$pmcId}' AND `event_id` = '{$eventId}'";
	$getCertiImgRes = mysql_query($getCertiImgQuery);// or displayerror(mysql_error());
	while($certiDetails = mysql_fetch_assoc($getCertiImgRes)){
		$certiImage = $certiDetails['certificate_image'];
		$certiId = $certiDetails['certificate_id'];
		//Get Certificate Details From evets_certficate_details

		$getCertiDetailsQuery = "SELECT `certificate_posx`,`certificate_posy`,`form_value_id` FROM `events_certificate_details` WHERE `page_moduleComponentId`='{$pmcId}' AND `certificate_id`='{$certiId}'";
		$getCertiDetailsRes = mysql_query($getCertiDetailsQuery) or displayerror(mysql_error());
		$posXString="";
		$posYString="";
		$certiValueString="";

		//Get Form Values From form_elementdesc
		//Form_value_id=-1 -> Rank
		//Form_value_id=-2 -> Event Name
		while($getValues = mysql_fetch_assoc($getCertiDetailsRes)){
			//User Rank
			if($getValues['form_value_id'] == -1){
				//imagettftext($rotatedImage, 20, 90, $getValues['certificate_posx'], $getValues['certificate_posy'], $color, $font, $userRank);
				$posXString.=$getValues['certificate_posx']."::";
				$posYString.=$getValues['certificate_posy']."::";
				$certiValueString.=$printParticipant['user_rank']."::";
			}
			//Event Name
			else if($getValues['form_value_id'] == -2){
				//Get Event Name
				$getEventNameQuery = "SELECT `event_name` FROM `events_details` WHERE `event_id` = '{$eventId}' AND `page_moduleComponentId`='{$pmcId}'";
				$getEventNameRes = mysql_query($getEventNameQuery) or displayerror(mysql_error());
				$eventName = mysql_result($getEventNameRes,0);
			//	imagettftext($rotatedImage, 20, 90, $getValues['certificate_posx'], $getValues['certificate_posy'], $color, $font, $eventName);
				$posXString.=$getValues['certificate_posx']."::";
				$posYString.=$getValues['certificate_posy']."::";
				$certiValueString.=$eventName."::";	
			}
			else{
				//Check if modified value exists in events_edited_form
				$getFormValuesQuery = "SELECT `events_edited_form`.`form_elementdata` FROM `events_edited_form` INNER JOIN `events_form` ON `events_edited_form`.`form_id`=`events_form`.`form_id`
				AND `events_form`.`event_id`='{$eventId}' AND `events_form`.`page_moduleComponentId`='{$pmcId}' AND `events_edited_form`.`user_id`='{$printParticipant['user_id']}' AND `events_edited_form`.`page_moduleComponentId`='{$pmcId}' AND 
				`events_edited_form`.`form_elementid`='{$getValues['form_value_id']}'";
				$getFormValuesRes = mysql_query($getFormValuesQuery) or displayerror(mysql_error());
				if(mysql_num_rows($getFormValuesRes) == 0){
					//Else get value from form_elementdata
					$getFormValuesQuery = "SELECT `form_elementdata`.`form_elementdata` FROM `form_elementdata` INNER JOIN `events_form` ON `form_elementdata`.`page_moduleComponentId`=`events_form`.`form_id` 
					AND `events_form`.`event_id`='{$eventId}' AND `events_form`.`page_moduleComponentId`='{$pmcId}' AND `form_elementdata`.`user_id`='{$printParticipant['user_id']}' AND `form_elementdata`.`form_elementid`='{$getValues['form_value_id']}'";
					$getFormValuesRes = mysql_query($getFormValuesQuery) or displayerror(mysql_error());
				}
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

	//					END OF TESTING*/
	//	$certiImage.="<page><img src='http://localhost/$urlRequestRoot/$cmsFolder/$moduleFolder/events/events_certi_image.php?userId=".$printParticipant['user_id']."&pmcId=".$pmcId."&eventId=".$eventId."&userRank=".$printParticipant['user_rank']."'></page>";
		//$folderPath = 'http://localhost/'.$urlRequestRoot."/".$cmsFolder."/".$moduleFolder."/events";
		$certiImagePage.="<page><img src='".generateImage($certiImage,$posXString,$posYString,$certiValueString)."'></page>";
	//	$certiImage.="<img src='".generateImage()."'>";
	}
	//return $certiImagePage;
	//return $certiImagePage;
	//Make Certi Size Dynamic
	$html2pdf = new HTML2PDF('P','A4','en',true, 'UTF-8',array(0, 0, 0, 0));
	$html2pdf->WriteHTML($certiImagePage);
	$html2pdf->Output('test.pdf');
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

?>