<?php

function addNewEvent(){
global $cmsFolder,$moduleFolder,$urlRequestRoot, $sourceFolder;
$scriptFolder = "$urlRequestRoot/$cmsFolder/$moduleFolder/events";
require_once("$sourceFolder/$moduleFolder/events/googleMapsConfig.php");
//displayinfo($scriptFolder);

$addForm=<<<FORM
		<link rel="stylesheet" type="text/css" href="$scriptFolder/jquery.datetimepicker.css"/ >
		<script src="$scriptFolder/jquery.js"></script>
		<script src="$scriptFolder/jquery.datetimepicker.js"></script>
		<script src="$scriptFolder/events.js"></script>
		<form method="post" id="addEventForm" enctype="multipart/form-data" action="./+eventshead">
		<table>
		<tbody>
				<tr><th><label for="eventName">Event name</label></th>
				<td><input type="text" decsription="eventName" name="eventName" id="eventName" </td></tr>

				<tr><th><label for="eventDesc">Description</label></th>
				<td><textarea type="text" rows="4" cols="50" decsription="eventDesc" name="eventDesc" id="eventDesc"></textarea></td></tr>

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
						console.log("checking");
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


function editEvent($eid, $pcmid){
global $cmsFolder,$moduleFolder,$urlRequestRoot, $sourceFolder;
$scriptFolder = "$urlRequestRoot/$cmsFolder/$moduleFolder/events";
require_once("$sourceFolder/$moduleFolder/events/googleMapsConfig.php");
//displayinfo($scriptFolder);
$query="SELECT * FROM `events_details` WHERE `event_Id`={$eid} AND `page_moduleComponentId={$pcmid}";
mysql_query($query);
$editForm=<<<FORM
		<link rel="stylesheet" type="text/css" href="$scriptFolder/jquery.datetimepicker.css"/ >
		<script src="$scriptFolder/jquery.js"></script>
		<script src="$scriptFolder/jquery.datetimepicker.js"></script>
		<script src="$scriptFolder/events.js"></script>
		<form method="post" id="addEventForm" enctype="multipart/form-data" action="./+eventshead">
		<table>
		<tbody>
				<tr><th><label for="eventName">Event name</label></th>
				<td><input type="text" decsription="eventName" name="eventName" id="eventName" </td></tr>

				<tr><th><label for="eventDesc">Description</label></th>
				<td><textarea type="text" rows="4" cols="50" decsription="eventDesc" name="eventDesc" id="eventDesc"></textarea></td></tr>

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