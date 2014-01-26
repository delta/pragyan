<?php

function addNewEvent(){
global $cmsFolder,$moduleFolder,$urlRequestRoot, $sourceFolder;
$scriptFolder = "$urlRequestRoot/$cmsFolder/$moduleFolder/events";
require_once("$sourceFolder/$moduleFolder/events/googleMapsConfig.php");
//displayinfo($scriptFolder);

$addForm=<<<FORM
		<link rel="stylesheet" type="text/css" href="$scriptFolder/datetimepicker/jquery.datetimepicker.css"/ >
		<script src="$scriptFolder/jquery.js"></script>
		<script src="$scriptFolder/datetimepicker/jquery.datetimepicker.js"></script>
		<script src="$scriptFolder/events.js"></script>
		<form method="post" id="addEventForm" enctype="multipart/form-data" action="./+eventshead">
		<table>
		<tbody>
				<tr><th><label for="eventName">Event name</label></th>
				<td><input type="text" decsription="eventName" name="eventName" id="eventName" </td></tr>

				<tr><th><label for="eventDesc">Description</label></th>
				<td><textarea type="text" rows="4" cols="50" decsription="eventDesc" name="eventDesc" id="eventDesc"></textarea></td></tr>

				<tr><th><label for="eventCluster">Event cluster</label></th>
				<td><input type="text" decsription="eventCluster" name="eventCluster" id="eventCluster" </td></tr>

				<tr><th><label for="eventFormId">Event form</label></th>
				<td><input type="text" decsription="eventFormId" name="eventFormId" id="eventFormId" </td></tr>

				<tr><th><label for"eventDate">Event date</label></th>
				<td><input type="text" decsription="day" name="eventDate" id="eventDate"></td></tr>

				<tr><th><label for="eventStartTime">Event start time</label></th>
				<td><input type="text" decsription="eventStartTime" name="eventStartTime" id="eventStartTime" size='5' value=></td></tr>

				<tr><th><label for="eventEndTime">Event end time</label></th>
				<td><input type="text" decsription="eventEndTime" name="eventEndTime" id="eventEndTime" size='5' value=></td></tr>

				<tr><th><label for="eventVenue">Event venue</label></th>
				<td><input type="text" decsription="eventVenue" name="eventVenue" id="eventVenue" value=></td></tr>

				<tr><th><label>Select the coordinates</label></th>
				<input type="text" decsription="lat" name="lat" id="lat" value="" style="display:none;">
				<input type="text" decsription="lng" name="lng" id="lng" value="" style="display:none;">
				<script src="http://maps.googleapis.com/maps/api/js?sensor=false&key=".$googleMapsKey></script>
				<script src="$scriptFolder/googleMaps.js"></script>
				<td><div id="addEventGoogleMap" style="width:500px;height:380px;"></div></td>
				</tr>
		</tbody>
		</table>
				<script>
						if (typeof isValid != 'undefined') {
								console.log("DEFINED");
								cmsShow("info", "Event successfully added");
						}
						$('#eventDate').datetimepicker({
								timepicker:false,
								format:'d.m.Y',
						});
						$('#eventStartTime').datetimepicker({
								datepicker:false,
								format:'H:i',
								step:10,

						});
						$('#eventEndTime').datetimepicker({
								datepicker:false,
								format:'H:i',
								step:10,
						});
				</script>
				<input type='button' onclick="submitAddEventData();" id="Add" name="Add" value='Add'><br />
		</form>
FORM;
return $addForm;
}


function editEvent($eid, $pmcid){
global $cmsFolder,$moduleFolder,$urlRequestRoot, $sourceFolder;
$scriptFolder = "$urlRequestRoot/$cmsFolder/$moduleFolder/events";
require_once("$sourceFolder/$moduleFolder/events/googleMapsConfig.php");

//query to find event
$findQuery="SELECT * FROM `events_details` WHERE `event_id`={$eid} AND `page_moduleComponentId`={$pmcid};";
$insertRes=mysql_query($findQuery) or displayerror(mysql_error());
$row=mysql_fetch_array($insertRes);

$query="SELECT * FROM `events_details` WHERE `event_Id`={$eid} AND `page_moduleComponentId={$pcmid}";
mysql_query($query);

$startTime=substr($row[event_start_time], 0, 5);
$endTime=substr($row[event_end_time], 0, 5);

$editForm=<<<FORM
		<link rel="stylesheet" type="text/css" href="$scriptFolder/datetimepicker/jquery.datetimepicker.css"/ >
		<script src="$scriptFolder/jquery.js"></script>
		<script src="$scriptFolder/datetimepicker/jquery.datetimepicker.js"></script>
		<script src="$scriptFolder/events.js"></script>
		<form method="post" id="editEventForm" enctype="multipart/form-data" action="./+eventshead">
		<table>
		<tbody>
			<tr><th><label for="eventName">Event name</label></th>
			<td><input type="text" decsription="eventName" value="{$row[event_name]}" name="eventName" id="eventName" </td></tr>

			<tr><th><label for="eventDesc">Description</label></th>
			<td><textarea type="text" rows="4" cols="50" decsription="eventDesc" name="eventDesc" id="eventDesc">{$row[event_desc]}</textarea></td></tr>

			<tr><th><label for="eventCluster">Event cluster</label></th>
			<td><input type="text" decsription="eventCluster" value="{$row[event_cluster]}" name="eventCluster" id="eventCluster" </td></tr>

			<tr><th><label for="eventFormId">Event form</label></th>
			<td><input type="text" decsription="eventFormId" name="eventFormId" id="eventFormId" </td></tr>

			<tr><th><label for"eventDate">Event date</label></th>
			<td><input type="text" decsription="day" name="eventDate" id="eventDate" value="{$row[event_date]}"></td></tr>

			<tr><th><label for="eventStartTime">Event start time</label></th>
			<td><input type="text" decsription="eventStartTime" name="eventStartTime" id="eventStartTime" size='5' value="{$startTime}"></td></tr>

			<tr><th><label for="eventEndTime">Event end time</label></th>
			<td><input type="text" decsription="eventEndTime" name="eventEndTime" id="eventEndTime" size='5' value="{$endTime}"></td></tr>

			<tr><th><label for="eventVenue">Event venue</label></th>
			<td><input type="text" decsription="eventVenue" name="eventVenue" id="eventVenue" value="{$row[event_venue]}"></td></tr>

			<tr><th><label>Select the coordinates</label></th>
			<input type="text" decsription="lat" name="lat"	 id="lat" style="display:none;" value="{$row[event_loc_y]}">
			<input type="text" decsription="lng" name="lng" id="lng" style="display:none;" value="{$row[event_loc_x]}">
			<script src="http://maps.googleapis.com/maps/api/js?sensor=false&key=".$googleMapsKey></script>
			<script src="$scriptFolder/googleMaps.js"></script>
			<td><div id="editEventGoogleMap" style="width:500px;height:380px;"></div></td>
			</tr>
		</tbody>
		</table>
				<script>
						if (typeof isValid != 'undefined') {
								console.log("DEFINED");
								cmsShow("info", "Event successfully added");
						}
						$('#eventDate').datetimepicker({
								timepicker:false,
								format:'d.m.Y',
						});
						$('#eventStartTime').datetimepicker({
								datepicker:false,
								format:'H:i',
								step:10,

						});
						$('#eventEndTime').datetimepicker({
								datepicker:false,
								format:'H:i',
								step:10,
						});
				</script>
				<input type='button' onclick="submitEditEventData({$eid});" id="Edit" name="Edit" value='Edit'><br />
		</form>
FORM;
return $editForm;
}

function addNewProcurement(){
global $cmsFolder,$moduleFolder,$urlRequestRoot, $sourceFolder;
$scriptFolder = "$urlRequestRoot/$cmsFolder/$moduleFolder/events";

//displayinfo($scriptFolder);                                        

$addForm=<<<FORM
		<script src="$scriptFolder/jquery.js"></script>
		<script src="$scriptFolder/events.js"></script>
		<form method="post" id="addProcurementForm" enctype="multipart/form-data" action="./+ochead">
		<table>
		<tbody>
				<tr><th><label for="eventName">Event name</label></th>        
				<td><select id="eventName">
FORM;
				$array=mysql_query("SELECT `event_name` FROM `events_details`");                
				while($row = mysql_fetch_assoc($array)) {
				$addForm.="<option value={$row['event_name']}>{$row['event_name']}</option>";
				}
$addForm.=<<<FORM
				</select>

				<tr><th><label for="procurementName">Procurement name</label></th>        
				<td><select id="procurementName">
FORM;
				$array=mysql_query("SELECT `procurement_name` FROM `events_procurements`");
				while($row = mysql_fetch_assoc($array)) {
				$addForm.="<option value={$row['procurement_name']}>{$row['procurement_name']}</option>";
				}
$addForm.=<<<FORM
				</select>
				
				<tr><th><label for="Quantity">Quantity</label></th>                
				<td><input type="text" decsription="quantity" name="quantity" id="quantity">

		</tbody>
		</table>
				<script>
						console.log("checking");
						if (typeof isValid != 'undefined') {
								console.log("DEFINED");
								cmsShow("info", "Procurement successfully added");
						}

				</script>
				<input type='button' onclick="submitAddProcurementData();" id="Add" name="Add" value='Add'><br />
		</form>
FORM;
return $addForm;
}

function addNewProc(){
global $cmsFolder,$moduleFolder,$urlRequestRoot, $sourceFolder;
$scriptFolder = "$urlRequestRoot/$cmsFolder/$moduleFolder/events";

$addForm=<<<FORM
		<script src="$scriptFolder/jquery.js"></script>
		<script src="$scriptFolder/events.js"></script>
		<form method="post" id="addProcForm" enctype="multipart/form-data" action="./+ochead">
				<tr><th><label for="procurement_name">Procurement name</label></th>        
				<td><input type="text" description="newProc" name="newProc" id="newProc" /></td>
		<input type='button' onclick="submitAddProc();" id="Add" name="Add" value='Add'><br />
		</form>
FORM;
return $addForm;
}

function editProcurement($eventnum){
global $cmsFolder,$moduleFolder,$urlRequestRoot, $sourceFolder;
$scriptFolder = "$urlRequestRoot/$cmsFolder/$moduleFolder/events";

//displayinfo($scriptFolder);                                        

$addForm=<<<FORM
        <script src="$scriptFolder/jquery.js"></script>
        <script src="$scriptFolder/events.js"></script>
        <form method="post" id="addProcurementForm" enctype="multipart/form-data" action="./+ochead">
        <table>
        <tbody>
                <tr><th><label for="eventName">Event name</label></th>        
                <td><select id="eventName">
FORM;
                $array=mysql_query("SELECT `event_name` FROM `events_details`");                
                while($row = mysql_fetch_assoc($array)) {
                $addForm.="<option value={$row['event_name']}>{$row['event_name']}</option>";
                }
$addForm.=<<<FORM
                </select>

                <tr><th><label for="procurementName">Procurement name</label></th>        
                <td><select id="procurementName">
FORM;
                $array=mysql_query("SELECT `procurement_name` FROM `events_procurements`");
                while($row = mysql_fetch_assoc($array)) {
                $addForm.="<option value={$row['procurement_name']}>{$row['procurement_name']}</option>";
                }
$addForm.=<<<FORM
                </select>
                
                <tr><th><label for="Quantity">Quantity</label></th>                
                <td><input type="text" decsription="quantity" name="quantity" id="quantity">

        </tbody>
        </table>
                <script>
                        console.log("checking");
                        if (typeof isValid != 'undefined') {
                                console.log("DEFINED");
                                cmsShow("info", "Procurement successfully added");
                        }

                </script>
                <input type='button' onclick="submitEditProcurementData({$eventnum})" id="Edit" name="Edit" value='Edit'><br />
        </form>
FORM;
return $addForm;
}



?>