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
                                        ."`event_venue`, `event_desc`, `event_last_update_time`, `event_image`, `page_moduleComponentId`, `event_loc_x`, `event_loc_y`) "
                                        ."VALUES ('{$_POST['eventName']}', '{$_POST['eventDate']}', "
                                        ."'{$_POST['eventStartTime']}', '{$_POST['eventEndTime']}', "
                                        ."'{$_POST['eventVenue']}', '{$_POST['eventDesc']}', CURRENT_TIME(), '', '{$pageModuleComponentId}', "
                                        ."'{$_POST['lng']}', '{$_POST['lat']}');";
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
        global $cmsFolder,$moduleFolder,$urlRequestRoot, $sourceFolder;
        $scriptFolder = "$urlRequestRoot/$cmsFolder/$moduleFolder/events";
        require_once("$sourceFolder/$moduleFolder/events/googleMapsConfig.php");
        $maps=<<<MAP
                <script src="$scriptFolder/jquery.js"></script>
                <script src="http://maps.googleapis.com/maps/api/js?sensor=false&key=".$googleMapsKey></script>
                <script src="$scriptFolder/googleMaps.js"></script>
                <div id="allEventGoogleMap" style="width:500px;height:380px;"></div>

MAP;
        return $maps;
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
//        $selectNameQuery="SELECT `events_form`.`form_id`,`events_form`.`event_id`,`form_elementdata`.`form_elementdata`,
//        `form_elementdata`.`page_moduleComponentId`,`pragyancms_users`.`user_id`,`pragyancms_users`.`user_name` FROM "
//        ."`form_elementdata`,`events_form`,`pragyancms_users` WHERE `form_elementdata`.`page_moduleComponentId`=`form_id`"
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

function validateNewProcurement($pageModuleComponentId){
		$isValid=true;
        if($_POST['newProc']==""){
                $isValid=false;
				exit();
        }
		else {
				$_POST['newProc']=escape(strtolower($_POST['newProc']));
				$selectQuery = "SELECT `procurement_name` FROM `events_procurements` WHERE `procurement_name`='{$_POST[newProc]}' ";
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
				<th></th>
				</tr>
        </thead>
TABLE;
$cnt=1;
while($res = mysql_fetch_assoc($selectRes)) {
$procurementDetails .=<<<TR
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
        $selectQuery="SELECT * FROM `events_event_procurement`;";
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
                </tr>
        </thead>
TABLE;
$cnt=1;
while($res = mysql_fetch_assoc($selectRes)) {
$procurementDetails .=<<<TR
          <tr>        
		   <td>{$cnt}</td>
           <td>{$res['event_name']}</td>
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