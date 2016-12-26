<?php
// Polyfill for mysql_result
function mysqli_result($res, $row, $field=0) { 
    if($res === false) return null;
    try {
        $res->data_seek($row); 
        $datarow = $res->fetch_array(); 
        return $datarow[$field]; 
    } catch(\Exception $e) {
        return null;
    }
} 

//Required For QMT
function returnUserProfileDetails($userId){
    
    $profileDetailQuery = "SELECT descr.form_elementdisplaytext AS dispText,
                         data.form_elementdata AS dispData,descr.form_elementisrequired AS required
                         FROM form_elementdesc AS descr
                         LEFT JOIN `form_elementdata` AS data
                         ON descr.form_elementid = data.form_elementid AND
                            descr.form_elementid !=11 AND
                            descr.page_modulecomponentid = data.page_modulecomponentid AND
                            data.user_id = $userId
                         WHERE descr.page_modulecomponentid = 0
                         ";
  $profileDetailRes = mysqli_query($GLOBALS["___mysqli_ston"], $profileDetailQuery) or displayerror(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
  if(!$profileDetailRes) {
    displayerror("check the error in getProfileDetailsForPr function in prhospi_common.php");
    return '';
  }
  $checkRequired=0;
  $userDetails .= <<<TABLE
    <table id="tableUserDetailsHospi" border="1">
       <tr>
         <th>Pr Id</th>
         <th>Field</th>
         <th>User Details</th>
       </tr>

TABLE;
    $count = 0;
    if($userId>20000 && $userId<30000)
        $userId +=180000;
$userNameCons="SELECT * FROM `festemberV3_users` WHERE `user_id`='{$userId}'";
$userNameQuery=mysqli_query($GLOBALS["___mysqli_ston"], $userNameCons) or displayerror(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
$userNameDetails=mysqli_fetch_assoc($userNameQuery);
$userDetails.=<<<TR
     <tr>
        <td>$userId</td>
        <td>Name</td>
        <td>{$userNameDetails['user_fullname']}</td>
    </tr>
TR;
$userDetails.=<<<TR
     <tr>
        <td>$userId</td>
        <td>Email</td>
        <td>{$userNameDetails['user_email']}</td>
    </tr>
TR;

  while($result=mysqli_fetch_assoc($profileDetailRes)) {
    if($count == 11) { $count ++ ; continue; }
    $dispText = $result['dispText'];
    $dispData = $result['dispData'];
    $count ++;

    $userDetails .=<<<TR
      <tr>
        <td>$userId</td>
        <td>{$dispText}</td>
        <td>{$dispData}</td>
     </tr>
TR;
  }
    return $userDetails."</table>";
}
function getBookletIdFromUserId($userId,$mcId) {
  $bookletId = escape($bookletId);
  $checkUserRegisteredToPr = "SELECT * FROM `prhospi_pr_status` WHERE `page_modulecomponentid` = {$mcId} AND `user_id` = '{$userId}'";
  $checkUserRegisteredToPrQuery = mysqli_query($GLOBALS["___mysqli_ston"], $checkUserRegisteredToPr) or displayerror(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
  $row = mysqli_fetch_assoc($checkUserRegisteredToPrQuery);
  return $row['booklet_id'];

}
//END

function getEventsForm(){
$form1=" Notification:<form method='post' action='./+eventsHead'> <input type='textarea' name='content'></input>";
$form1.="<input type='submit'></input>";
$form1.="<input style='display:none;' type='text' value='notif' name='type'></input> </form>";
return $form1;

}

function validateEventData(){
$isValid=true;
$day=substr($_POST['eventDate'], 0, 2);
$month=substr($_POST['eventDate'], 3, 2);
$year=substr($_POST['eventDate'], 6, 4);
if($_POST['eventName']==""){
    displainfo("name");
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
            $insertRes=mysqli_query($GLOBALS["___mysqli_ston"], $insertQuery) or displayerror(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
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
            $editRes=mysqli_query($GLOBALS["___mysqli_ston"], $editQuery) or displayerror(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
            echo "Valid";
    }
    else echo "Invalid";
    exit();
}	

function getAllEvents($pmcid){
    //Query to select all entries
    global $cmsFolder,$moduleFolder,$urlRequestRoot, $sourceFolder;
    $scriptFolder = "$urlRequestRoot/$cmsFolder/$moduleFolder/events";
    //displaywarning($scriptFolder);
    $selectQuery="SELECT * FROM `events_details` WHERE `page_moduleComponentId`={$pmcid};";
    $insertRes=mysqli_query($GLOBALS["___mysqli_ston"], $selectQuery) or displayerror(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
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

while($res = mysqli_fetch_assoc($insertRes)) {
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
      <a href="./+eventshead&subaction=notif">Notification</a>
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
    $maps=<<<MAP1
            <script src="$scriptFolder/jquery.js"></script>
            <script src="https://maps.googleapis.com/maps/api/js?sensor=false&key=".$googleMapsKey></script>
            <script src="$scriptFolder/googleMaps.js"></script>
      <style>@font-face{ font-family: myriadpro;src: url("myriadpro.woff");}  body{overflow:hidden;background:#C3AC7A;font-weight:bolder;font-size:1.6em;font-family:myriadpro}</style>
            <script src="$scriptFolder/events.js"></script>
      <div id="upcomingEventDiv" style="padding:3px;color:#C3AC7A;border-radius:5px;width:48%;height:99%;overflow:hidden;float:right;background:rgba(64,34,36,1)">
     <div style="height:70.5%;overflow:hidden;margin-top:2%;"> <center style="font-size:2.1em;border-radius:5px;align:center;background:#C3AC7A;color:rgba(64,34,36,1)"> <b>Events</b></center>
                        <table id="upcomingEventTable" style="font-family: myriadpro;width:100%;font-size: large;color:#C3AC7A;">
                              
                           </table></div>
      <div style="height:30%;overflow:hidden;margin-top:3.5%;">
                               <center style="font-size:2.1em;border-radius:5px;align:center;background:#C3AC7A;color:rgba(64,34,36,1)"> <b>Notifications</b></center>
                        <table id="upcomingEventTable" style="font-family: myriadpro;width:100%;color:#C3AC7A;">
MAP1;
            
    $fpmcid=1;
    $q="SELECT * from `events_notifications` WHERE NOW()-`timeadded` <= 3600 * 20 AND  NOW()-`timeadded`>=0  ORDER BY `timeadded` DESC";
    $res=mysqli_query($GLOBALS["___mysqli_ston"], $q); 
    while($row=mysqli_fetch_array($res)){
     $maps.="<tr  style='margin-bottom:5px;'><td style='margin-bottom:5px;border:1px solid #C3AC7A; border-radius: 5px;font-size:2em;'> {$row['notif_content']}  </td></tr>";
    }
      
$maps.=<<<MAP2
                            </table>
    
                            </div></div>				
                            <div id="allEventGoogleMap" style="margin-left:2%;width:47%;height:100%;background:f9ecc3;border:2px solid rgba(64,34,36,1);border-radius:5px;"></div>
            <script>
                window.onload=function(){
                    console.log("Loaded.");
                    getUpcomingEventsTable();
                    setInterval(function(){
                        initAllEventsMap();getUpcomingEventsTable();
                        },300000);
                }
            </script>
MAP2;
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
    if(isset($_GET['pageno'])){
      $page=$_GET['pageno'];
    }
    if(isset($_GET['ipp'])) {
      $ipp=$_GET['ipp'];
    }
    if(isset($_GET['lud'])){//lud=last updated date
      $lastdate=$_GET['lud'];
    }
    
    $prod=$page*$ipp;
    //Query to select all events
    $eventsQuery="SELECT * FROM `events_details` "
                            ."WHERE '{$lastdate}'<=`event_last_update_time` AND event_date>='{$date1}' AND `page_moduleComponentId`='{$pmcid}'" 
                            ."ORDER BY event_date ASC LIMIT {$prod}, {$ipp};";
    $eventsRes=mysqli_query($GLOBALS["___mysqli_ston"], $eventsQuery) or displayerror(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
    while($row=mysqli_fetch_array($eventsRes)){
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
    $eventsRes=mysqli_query($GLOBALS["___mysqli_ston"], $eventsQuery) or displayerror(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));

    $schedule=<<<SCHEDULE
    <script src="$scriptFolder/jquery.js"></script>
    <script src="$scriptFolder/events.js"></script>
    <table>
    <th width='10%;'>Event</th>
    <th width='10%;'>Venue</th>
    <th width='10%;'>Date</th>
    <th width='10%;'>Time</th>
SCHEDULE;

    while($row=mysqli_fetch_array($eventsRes)){
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
    $deletetRes=mysqli_query($GLOBALS["___mysqli_ston"], $deleteQuery) or displayerror(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
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
            $selectRes=mysqli_query($GLOBALS["___mysqli_ston"], $selectQuery);
            if(mysqli_num_rows($selectRes)==1){
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
            $insertRes=mysqli_query($GLOBALS["___mysqli_ston"], $insertQuery) or displayerror(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));

            $updateQuery="UPDATE `events_procurements` SET `quantity`=`quantity`+ {$_POST['quantity']} WHERE `procurement_name`='{$_POST['procurementName']}'";
            $updateRes=mysqli_query($GLOBALS["___mysqli_ston"], $updateQuery) or displayerror(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
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
            $selectRes=mysqli_query($GLOBALS["___mysqli_ston"], $selectQuery);
            $cnt=1;
            while($res = mysqli_fetch_assoc($selectRes)) {
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
            $selectRes=mysqli_query($GLOBALS["___mysqli_ston"], $selectQuery);
            $cnt=1;
            while($res = mysqli_fetch_assoc($selectRes)) {		
            if($cnt==$_POST['eventnum']){
                $updateQuery="UPDATE `events_procurements` SET `quantity`=`quantity`-{$res['quantity']} WHERE `procurement_name`='{$res['procurement_name']}'";	
                $updateRes=mysqli_query($GLOBALS["___mysqli_ston"], $updateQuery) or displayerror(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));

                $updateQuery="UPDATE `events_procurements` SET `quantity`=`quantity`+ {$_POST['editquantity']} WHERE `procurement_name`='{$_POST['procurementName']}'";
                $updateRes=mysqli_query($GLOBALS["___mysqli_ston"], $updateQuery) or displayerror(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
                
                $deleteQuery="DELETE FROM `events_event_procurement` WHERE `event_name`='{$res['event_name']}' AND `procurement_name`='{$res['procurement_name']}' ";
                $deleteRes=mysqli_query($GLOBALS["___mysqli_ston"], $deleteQuery) or displayerror(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));

                $insertQuery="INSERT INTO `events_event_procurement` (`event_name`, `procurement_name`, `quantity`, `page_moduleComponentId`) "
                         ."VALUES ('{$_POST['eventName']}', '{$_POST['procurementName']}','{$_POST['editquantity']}', '{$pageModuleComponentId}')";
                $insertRes=mysqli_query($GLOBALS["___mysqli_ston"], $insertQuery) or displayerror(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
                
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
            $selectRes=mysqli_query($GLOBALS["___mysqli_ston"], $selectQuery);
            if(mysqli_num_rows($selectRes)==1){
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
            $insertRes=mysqli_query($GLOBALS["___mysqli_ston"], $insertQuery) or displayerror(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
            echo "Valid";
    }
    else echo "Invalid";
    exit();
}

function selectSubactionProcurement(){
    $subactionForm=<<<SFORM
    <p>
            Select an option:OA
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
    $selectRes=mysqli_query($GLOBALS["___mysqli_ston"], $selectQuery) or displayerror(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
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
while($res = mysqli_fetch_assoc($selectRes)) {
$selQuery="SELECT * FROM `events_details` WHERE `event_name`='{$res['event_name']}'";
$selRes=mysqli_query($GLOBALS["___mysqli_ston"], $selQuery) or displayerror(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
$selRes = mysqli_fetch_assoc($selRes);
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
$selectRes=mysqli_query($GLOBALS["___mysqli_ston"], $selectQuery) or displayerror(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
$cnt=1;
while($res = mysqli_fetch_assoc($selectRes)) {		
if($cnt==$eventname){
$updateQuery="UPDATE `events_procurements` SET `quantity`=`quantity`-{$res['quantity']} WHERE `procurement_name`='{$res['procurement_name']}'";	
$updateRes=mysqli_query($GLOBALS["___mysqli_ston"], $updateQuery) or displayerror(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
$deleteQuery="DELETE FROM `events_event_procurement` WHERE `event_name`='{$res['event_name']}' AND `procurement_name`='{$res['procurement_name']}'";
$deletetRes=mysqli_query($GLOBALS["___mysqli_ston"], $deleteQuery) or displayerror(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
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
    $selectRes=mysqli_query($GLOBALS["___mysqli_ston"], $selectQuery) or displayerror(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
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
while($event=mysqli_fetch_assoc($selectRes)) {	
$result=mysqli_query($GLOBALS["___mysqli_ston"], "SELECT * FROM `events_event_procurement` WHERE `event_name`='{$event['event_name']}'");
while($res=mysqli_fetch_assoc($result))
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
    $selectRes=mysqli_query($GLOBALS["___mysqli_ston"], $selectQuery) or displayerror(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
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
while($res = mysqli_fetch_assoc($selectRes)) {
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
$getEventNameRes = mysqli_query($GLOBALS["___mysqli_ston"], $getEventNameQuery) or displayerror(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
return mysqli_result($getEventNameRes, 0);
}


function displayEventOptions($gotoaction,$pmcId,$eventId){
global $cmsFolder,$moduleFolder,$urlRequestRoot, $sourceFolder;
$scriptFolder = "$urlRequestRoot/$cmsFolder/$moduleFolder/events";
$eventDetails =<<<SCRIPT
    <script src="$scriptFolder/events.js"></script>
    <script src="$scriptFolder/jquery.js"></script>
SCRIPT;
if(isset($_FILES['fileUploadFieldPart']['name'])){
    displaywarning("Query Here");
  syncExcelFile($pmcId,$eventId,$_FILES['fileUploadFieldPart']['tmp_name'][0]);
}
if(isset($_FILES['fileUploadField']['name'])){
  syncExcelFile($pmcId,$eventId,$_FILES['fileUploadField']['tmp_name'][0]);
}

if($gotoaction == 'qa'){
    $eventDetails .= displayQa($pmcId).'<br/><br/><h2>'.getEventName($pmcId,$eventId).'</h2>';
    $eventDetails.=searchParticipant($gotoaction,$pmcId,$eventId);
}
else if($gotoaction == 'qahead'){
    $eventDetails .= qaHeadOptions($pmcId).'<br/><br/>asdasdasd<h2>'.getEventName($pmcId,$eventId).'</h2>';
    $checkLockedQuery  = "SELECT `event_id` FROM `events_locked` WHERE `page_moduleComponentId`='{$pmcId}' AND `event_id`='{$eventId}'";
    $checkLockedRes = mysqli_query($GLOBALS["___mysqli_ston"], $checkLockedQuery) or displayerror(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
    if(mysqli_num_rows($checkLockedRes) > 0){
        $eventDetails.=<<<FORM
            <br/><p>Event Locked</p>
            <table><tr><td>
            <a href='./+{$gotoaction}&subaction=downloadExcel&event_id=$eventId'>Download Details</a></td><td>
            <form method='POST' action='./+qahead&subaction=unlockEvent' onsubmit='return unlockConfirm();'>
            <input type='hidden' value='{$eventId}' name='eventId'>
            <input type='submit' id='lockButton' value='UNLOCK EVENT'>
            </form></td></tr></table>
FORM;
        return $eventDetails;
    }
    
}
/*if(isset($_FILES['fileUploadField']['name'])){
  syncExcelFile($pmcId,$eventId,$_FILES['fileUploadField']['tmp_name'][0]);
}*/

$checkParticipantsQuery = "SELECT `user_pid` FROM `events_participants` WHERE `page_moduleComponentId`='{$pmcId}' AND `event_id`='{$eventId}' LIMIT 1";
$checkParticipantsRes = mysqli_query($GLOBALS["___mysqli_ston"], $checkParticipantsQuery) or displayerror(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
if(mysqli_num_rows($checkParticipantsRes) == 0){
    //Show FileUpload Details
    $fileUploadableField=getFileUploadField('fileUploadField',"events");

$eventDetails.=<<<ADDROOMFORM
       <!--<br/><br/>
       <form action="./+{$gotoaction}&subaction=viewEvent" method="post">
            <input type="submit" name="downloadSampleFormat" value="Download Sample Form"><br/>
       </form>-->
       <p>Upload Event Excel File:</p>
       <form action="./+{$gotoaction}&subaction=viewEvent" method="post" enctype='multipart/form-data'>
       $fileUploadableField
       <input type='hidden' name='eventId' value='{$eventId}'>
       <input type='submit' name='submit' value='Upload'>
       </form>
ADDROOMFORM;
}
else{
    $eventDetails.=<<<PRINTTABLE
    <table><tr><td>
    <a href='./+{$gotoaction}&subaction=downloadExcel&event_id=$eventId'>Download Details</a>
    </td><td>
    <form method="POST" action='./+{$gotoaction}&subaction=lockEvent' onsubmit='return lockConfirm();'>
    <input type='hidden' value='{$eventId}' name='eventId'>
    <input type='submit' id='lockButton' value='LOCK EVENT'>
    </form></td></tr>
    <!--<button onclick="downloadDetails('$gotoaction',{$eventId})" value='Download Details'>Download Details</button>-->
PRINTTABLE;
    //$downloadTable = getUserDetailsTable($pmcId,$eventId);
    //$eventDetails.=displayExcelForTable($downloadTable);
    if(($gotoaction=='qahead') && mysqli_num_rows($checkLockedRes) == 0)
     {
      $eventDetails.=deleteEventForm($pmcID,$eventId);
    $eventDetails.=addParticipant($gotoaction,$pmcId,$eventId);
   $eventDetails.=searchParticipant($gotoaction,$pmcId,$eventId);
    }
    if(($gotoaction=='qa') && mysqli_num_rows($checkLockedRes) == 0)
     {
    $eventDetails.=addParticipant($gotoaction,$pmcId,$eventId);
    }

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
$selectParticipantsRes = mysqli_query($GLOBALS["___mysqli_ston"], $selectParticipantsQuery) or displayerror(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
$participantsTable.=<<<TABLE

  $smarttable<table id='participants_table' class='display' width='100%' border='1'><thead><tr><th>Team Id</th><th>FID</th><th>Booklet Id</th><th>Name</th><th>College</th><th>Phone No.</th><th>Rank</th><th>Prize Money</th><th>Options</th>

TABLE;
if($gotoaction=='qahead')
  $participantsTable.="<th>Delete</th>";
$participantsTable.="</thead>";
while($participant = mysqli_fetch_assoc($selectParticipantsRes)){
    $editRowId = "";
    $participantNameQuery = "SELECT `".MYSQL_DATABASE_PREFIX."users`.`user_fullname` FROM `".MYSQL_DATABASE_PREFIX."users` WHERE `user_id`='{$participant['user_pid']}'";
    $participantNameRes = mysqli_query($GLOBALS["___mysqli_ston"], $participantNameQuery) or displayerror(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
    $getBookletIdQuery = "SELECT `booklet_id` FROM `prhospi_pr_status` WHERE `user_id`='{$participant['user_pid']}' AND `page_moduleComponentId`='{$pmcId}'";
    $getBookletIdRes = mysqli_query($GLOBALS["___mysqli_ston"], $getBookletIdQuery) or displayerror(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
    if(mysqli_num_rows($getBookletIdRes == 0))
       $bookletId = " ";
    else
      $bookletId = mysqli_result($getBookletIdRes, 0);
    if($participant['user_pid']<30000&&$participant['user_pid']>20000) 
   $rep = $participant['user_pid']+180000;
    else
    $rep = $participant['user_pid'];
    $participantsTable.="<tr id='partRow{$participant['user_pid']}'><td>".$participant['user_team_id']."</td><td>".$rep."</td><td>".$bookletId."<td><span class='userDataDisp{$participant['user_pid']}'>".mysqli_result($participantNameRes,  0)."</span><input type='text' class='userDataEditVal{$participant['user_pid']}' value='".mysqli_result($participantNameRes,  0)."' style='display:none'></td>";
    $editRowId.='-1,';
    $phNoFormId = retPnoneNoFormId();
    $collFormId = retCollFormId();
    $otherCollFormId = retOtherCollFormId();
    $globalPmcId = retglobalPmcId();
    $participantsDetailsQuery = "SELECT `form_elementid`,`form_elementdata` FROM `form_elementdata` WHERE `page_moduleComponentId`='{$globalPmcId}' AND `user_id`='{$participant['user_pid']}' AND `form_elementid` IN ($phNoFormId,$collFormId) ORDER BY `form_elementid`";
    //displayinfo($participantsDetailsQuery);
    //1
    $participantsDetailsRes = mysqli_query($GLOBALS["___mysqli_ston"], $participantsDetailsQuery) or displayerror(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
    //displayinfo(mysql_num_rows($participantsDetailsRes));
    if(mysqli_num_rows($participantsDetailsRes) == 0 || mysqli_num_rows($participantsDetailsRes) == 1){
      $participantsDetailsQuery = "SELECT `form_elementid`,`form_elementdata` FROM `form_elementdata` WHERE `page_moduleComponentId`='{$globalPmcId}' AND `user_id`='{$participant['user_pid']}' AND `form_elementid`='{$otherCollFormId}'";
      $participantsDetailsRes = mysqli_query($GLOBALS["___mysqli_ston"], $participantsDetailsQuery) or displayerror(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
      //echo $participantsDetailsQuery;
      //die();
      $collNameId = $otherCollFormId;
      if(mysqli_num_rows($participantsDetailsRes) == 0)
        $collName = " ";
      else{
        while($userDetails = mysqli_fetch_assoc($participantsDetailsRes)){
          //echo $userDetails['form_elementdata'];
          //die();
          $collName = $userDetails['form_elementdata'];
          //echo $collName;
          //die();
        }
        //$collName = mysql_result($participantsDetailsRes,1);
      }
      //displayinfo($collName);
    }
    else{
    while($userDetails = mysqli_fetch_assoc($participantsDetailsRes)){
        if($userDetails['form_elementid'] == $phNoFormId){
            $phNoId = $userDetails['form_elementid'];
            $phNo = $userDetails['form_elementdata'];
        }
        else if($userDetails['form_elementid'] == $collFormId){
          //	  displayinfo($userDetails['form_elementdata']);
            $collNameId = $userDetails['form_elementid'];
            $collName = $userDetails['form_elementdata'];
            if($collName == "" || $collName == "Others"){
                $getCollNameQuery = "SELECT `form_elementdata`,`form_elementid` FROM `form_elementdata` WHERE `form_elementid`='{$otherCollFormId}' AND `page_modulecomponentid`='{retglobalPmcId()}' AND `user_id`='{$participant['user_pid']}'";
                $getCollNameRes = mysqli_query($GLOBALS["___mysqli_ston"], $getCollNameQuery) or displayerror(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
                while($otherCollDetails = mysqli_fetch_assoc($getCollNameRes)){
                    $collNameId = $otherCollDetails['form_elementid'];
                    $collName = $otherCollDetails['form_elementdata'];
                }
            }
        }
    }}
    if(!isset($phNo)){
        $phNoId=$phNoFormId;
        $phNo = "";
    }
    if(!isset($collName)){
        $collNameId=$collFormId;
        $collName = "";
    }
    $editRowId.=$collNameId.','.$phNoId.',';
    $participantsTable.="<td><span class='userDataDisp{$participant['user_pid']}'>".$collName."</span>";
    
    
    /////////////////////////////////////////////////GETTING A DROPDOWN FOR THE LIST OF COLLEGES FROM REGISTRATION FORM 
        $selectTag="<select name='college' class='userDataEditVal{$participant['user_pid']}' style='display:none '>";
            $getCollegeListQuery="SELECT `form_elementtypeoptions` FROM `form_elementdesc` WHERE `page_modulecomponentid`=0 AND `form_elementid`=10";
            $getCollegeListRes=mysqli_query($GLOBALS["___mysqli_ston"], $getCollegeListQuery) or displayerror(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
            if($fullCollegeList=mysqli_fetch_array($getCollegeListRes))
            {
            
            $fullCollegeArray=explode('|',$fullCollegeList['form_elementtypeoptions']);
         //   displayWarning($fullCollegeList['form_elementtypeoptions']);               
       //     displayWarning('asdasd');
            for($i=0;$i<count($fullCollegeArray);$i=$i+1){
                if($fullCollegeArray[$i]==$collName)
                    $selectTag.="<option  value='{$fullCollegeArray[$i]}' selected>$fullCollegeArray[$i]</option>";
                else
                    $selectTag.="<option  value='{$fullCollegeArray[$i]}'>$fullCollegeArray[$i]</option>";   
}
                   
            }
        
        ///////////////////////////////////////////////////////////////////DROPDOWN DONE
        $selectTag.="</select>";
    $newCollege=""; 
 //  $newCollege="<input type='text' name='otherCollege'  class='userDataEditVal{$participant['user_pid']}' style='display:none'  value='other colleges'>";
    $participantsTable.=$selectTag.$newCollege."</td>";
    


    /*     <input type='text' class='userDataEditVal{$participant['user_pid']}' value='{$collName}' style='display:none'></td>";
	*/	
    $participantsTable.="<td><span class='userDataDisp{$participant['user_pid']}'>".$phNo."</span><input type='text' class='userDataEditVal{$participant['user_pid']}' value='{$phNo}' style='display:none'></td>";
		$participantsRankQuery = "SELECT `user_rank` FROM `events_result` WHERE `page_moduleComponentId`='{$pmcId}' AND `user_id`='{$participant['user_pid']}' AND `event_id`='{$eventId}'";
		$participantsRankRes = mysqli_query($GLOBALS["___mysqli_ston"], $participantsRankQuery) or displayerror(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
		$userRank = mysqli_result($participantsRankRes, 0);
		$participantsTable.="<td><span class='userDataDisp{$participant['user_pid']}'>".$userRank."</span><input type='text' class='userDataEditVal{$participant['user_pid']}' value='{$userRank}' style='display:none'></td>";
		$editRowId.='-2,';
		$getPrizeMoneyQuery = "SELECT `prize_money` FROM `events_participants` WHERE `user_pid`='{$participant['user_pid']}' AND `event_id`='{$eventId}' AND `page_moduleComponentId`='{$pmcId}'";
		$getPrizeMoneyRes = mysqli_query($GLOBALS["___mysqli_ston"], $getPrizeMoneyQuery) or displayerror(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
		$prizeMoney = mysqli_result($getPrizeMoneyRes, 0);
		$participantsTable.="<td><span class='userDataDisp{$participant['user_pid']}'>".$prizeMoney."</span><input type='text' class='userDataEditVal{$participant['user_pid']}' value='{$prizeMoney}' style='display:none'></td>";
		$editRowId.='-3,';
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
			</td>
BUTTON;
		if($gotoaction=='qahead'){
		  $scriptFolder = "$urlRequestRoot/$cmsFolder/$moduleFolder/events";
		  $participantsTable.=<<<DELETE
		    <td>
		    <script src="$scriptFolder/events.js"></script>
		    <script src="$scriptFolder/jquery.js"></script>
		    <form method='POST' action='./+qahead&subaction=deleteParticipant' onsubmit='return confirmDelete();'>
		    <input type='hidden' name='eventId' value='{$eventId}'/>
		    <input type='hidden' name='userId' value='{$participant['user_pid']}'/>
		    <input type='submit' name='submit' value='Delete'/>
		    </form>
		    </td>
DELETE;
		}
		$participantsTable.="</tr>";
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
				<table id='participants_table' class='display' width='100%' border='1'><thead><tr><th>Team Id</th><th>FID</th><th>Booklet ID</th><th>Name</th><th>College</th><th>Phone No.</th><th>Rank</th><th>Prize Money</th></tr></thead>
TABLE;
	$selectTeamQuery = "SELECT `user_team_id`,COUNT(*) AS `count` FROM `events_participants` WHERE `page_moduleComponentId`='{$pmcId}' AND `event_id`='{$eventId}' GROUP BY `user_team_id`";
	$selectTeamRes = mysqli_query($GLOBALS["___mysqli_ston"], $selectTeamQuery) or displayerror(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
	while($team = mysqli_fetch_assoc($selectTeamRes)){
		$participantsTable.="<tr><td rowspan='{$team['count']}'>".$team['user_team_id']."</td>";
		$selectParticipantsQuery = "SELECT `user_pid` FROM `events_participants` WHERE `page_moduleComponentId`='{$pmcId}' AND `event_id`='{$eventId}' AND `user_team_id`='{$team['user_team_id']}'";
		$selectParticipantsRes = mysqli_query($GLOBALS["___mysqli_ston"], $selectParticipantsQuery) or displayerror(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
		while($participant = mysqli_fetch_assoc($selectParticipantsRes)){
			$participantNameQuery = "SELECT `user_fullname` FROM `".MYSQL_DATABASE_PREFIX."users` WHERE `user_id`='{$participant['user_pid']}'";
			$participantNameRes = mysqli_query($GLOBALS["___mysqli_ston"], $participantNameQuery) or displayerror(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
		$getBookletIdQuery = "SELECT `booklet_id` FROM `prhospi_pr_status` WHERE `user_id`='{$participant['user_pid']}' AND `page_moduleComponentId`='1'";
		$getBookletIdRes = mysqli_query($GLOBALS["___mysqli_ston"], $getBookletIdQuery) or displayerror(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
		if(mysqli_num_rows($getBookletIdRes == 0))
		   $bookletId = " ";
		else
		  $bookletId = mysqli_result($getBookletIdRes, 0);
            //$getBookeltIdQuery = "SELECT `booklet_id` FROM `prhospi_pr_status` WHERE `page_moduleComponentId`='{$pmcId}' and `user_id`='{$participant['user_pid']}'";
			//$getBookletIdRes = mysql_query($getBookletIdQuery) or displayerror(mysql_error());
			//$bookletId = mysql_result($getBookletIdRes,0);
            if($participant['user_pid']>20000&&$participant['user_pid']<30000)
            $rep = $participant['user_pid']+180000;
        else
            $rep=$participant['user_pid'];
			$participantsTable.="<td>".$rep."</td><td>".$bookletId."</td><td>".mysqli_result($participantNameRes,  0)."</td>";
			$phNoFormId = retPnoneNoFormId();
			$collFormId = retCollFormId();
			$otherCollFormId = retOtherCollFormId();
			$participantsDetailsQuery = "SELECT `form_elementdata`,`form_elementid` FROM `form_elementdata` WHERE `page_moduleComponentId`='{retglobalPmcId()}' AND `user_id`='{$participant['user_pid']}' AND `form_elementid` IN ($phNoFormId,$collFormId)";
			//2
			$participantsDetailsRes = mysqli_query($GLOBALS["___mysqli_ston"], $participantsDetailsQuery) or displayerror(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
			while($userDetails = mysqli_fetch_assoc($participantsDetailsRes)){
				if($userDetails['form_elementid'] == $phNoFormId){
					$phNoId = $userDetails['form_elementid'];
					$phNo = $userDetails['form_elementdata'];
				}
				else if($userDetails['form_elementid'] == $collFormId){
					$collNameId = $userDetails['form_elementid'];
					$collName = $userDetails['form_elementdata'];
					if($collName == "Others"){
						$getCollNameQuery = "SELECT `form_elementdata`,`form_elementid` FROM `form_elementdata` WHERE `form_elementid`='{$otherCollFormId}' AND `page_modulecomponentid`='{retglobalPmcId()}' AND `user_id`='{$participant['user_pid']}'";
						$getCollNameRes = mysqli_query($GLOBALS["___mysqli_ston"], $getCollNameQuery) or displayerror(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
						while($otherCollDetails = mysqli_fetch_assoc($getCollNameRes)){
							$collNameId = $otherCollDetails['form_elementid'];
							$collName = $otherCollDetails['form_elementdata'];
						}
					}
				}
			}
			$participantsTable.="<td>".$collName."</td><td>".$phNo."</td>";
			$getUserRankQuery = "SELECT `user_rank` FROM `events_result` WHERE `page_moduleComponentId`='{$pmcId}' AND `event_id`='{$eventId}' AND `user_id`='{$participant['user_pid']}'";
			$getUserRankRes = mysqli_query($GLOBALS["___mysqli_ston"], $getUserRankQuery) or displayerror(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
			$userRank = mysqli_result($getUserRankRes, 0);
			$participantsTable.="<td>".$userRank."</td>";
			$getPrizeMoneyQuery = "SELECT `prize_money` FROM `events_participants` WHERE `user_pid`='{$participant['user_pid']}' AND `event_id`='{$eventId}' AND `page_moduleComponentId`='{$pmcId}'";
			$getPrizeMoneyRes = mysqli_query($GLOBALS["___mysqli_ston"], $getPrizeMoneyQuery) or displayerror(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
			$prizeMoney = mysqli_result($getPrizeMoneyRes, 0);
			$participantsTable.="<td>".$prizeMoney."</td>";
			$participantsTable.="</tr>";
		}
	}
	$eventNameQuery = "SELECT `event_name` FROM `events_details` WHERE `page_moduleComponentId`='{$pmcId}' AND `event_id`='{$eventId}'";
	$eventNameRes = mysqli_query($GLOBALS["___mysqli_ston"], $eventNameQuery) or displayerror(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
	$eventName = mysqli_result($eventNameRes,  0);
	$participantsTable.="</table>";
	displayExcelForTable($participantsTable,$eventName);
	//return $participantsTable;
}

function confirmParticipation($gotoaction,$pmcid,$eventId,$userId){
	$confirmQuery = "INSERT INTO `events_confirmed_participants`(`page_moduleComponentId`,`event_id`,`user_id`) VALUES('{$pmcid}','{$eventId}','{$userId}')";
	$confirmRes = mysqli_query($GLOBALS["___mysqli_ston"], $confirmQuery) or displayerror(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
	$userRankQuery = "INSERT INTO `events_result`(`page_moduleComponentId`,`event_id`,`user_id`) VALUES('{$pmcid}','{$eventId}','{$userId}')";
	$userRankRes = mysqli_query($GLOBALS["___mysqli_ston"], $userRankQuery) or displayerror(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
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
  //  return $rowValue
	$rowValueArray = explode("::",$rowValue);
	$rowIdArray = explode(",",$rowId);
	$updatedRow = "";
	$getBookletIdQuery = "SELECT `booklet_id` FROM `prhospi_pr_status` WHERE `user_id`='{$userId}' AND `page_moduleComponentId`='1'";
	$getBookletIdRes = mysqli_query($GLOBALS["___mysqli_ston"], $getBookletIdQuery) or displayerror(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
	if(mysqli_num_rows($getBookletIdRes) == 0)
	  $bookletId = " ";
	else
	  $bookletId = mysqli_result($getBookletIdRes, 0);
	$updatedRow.="<td>".$teamId."</td><td>".$userId."</td><td>".$bookletId."</td>";
       	for($i=0;$i<sizeof($rowValueArray);$i++){
/*
		$insertEditedRowQuery = "INSERT INTO `events_edited_form`(`page_moduleComponentId`,`form_id`,`user_id`,`form_elementid`,`form_elementdata`) 
			VALUES('{$pmcId}','{$formId}','{$userId}','{$rowIdArray[$i]}','{$rowValueArray[$i]}') ON DUPLICATE KEY UPDATE `form_elementdata`='{$rowValueArray[$i]}'";
		$insertEditedRowRes = mysql_query($insertEditedRowQuery) or displayerror(mysql_error());
*/
    $formDataQuery = "SELECT * FROM `form_elementdata` WHERE `user_id`={$userId}";
    $formDataRes = mysqli_query($GLOBALS["___mysqli_ston"], $formDataQuery) or displayerror(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
    if(mysqli_num_rows($formDataRes)==0){
        for($k=0;$k<13;$k++){
            $insQuery="INSERT INTO `form_elementdata` VALUES({$userId},0,{$k},'')";
            $inses=mysqli_query($GLOBALS["___mysqli_ston"], $insQuery) or displayerror(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
        }
    }
			if($rowIdArray[$i] == -1){
			$editNameQuery = "UPDATE `".MYSQL_DATABASE_PREFIX."users` SET `user_fullname`='{$rowValueArray[$i]}' WHERE `user_id`='{$userId}'";
			$editNameRes = mysqli_query($GLOBALS["___mysqli_ston"], $editNameQuery) or displayerror(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));

            $updatedRow.="<td><span class='userDataDisp{$userId}'>".$rowValueArray[$i]."</span><input type='text' class='userDataEditVal{$userId}' value='".$rowValueArray[$i]."' style='display:none' /></td>";
		}
		else if($rowIdArray[$i] == -2){
			$editRankQuery = "UPDATE `events_result` SET `user_rank`='{$rowValueArray[$i]}' WHERE `user_id`='{$userId}' AND `page_moduleComponentId`='{$pmcId}' AND `event_id`='{$eventId}'";
			$editRankRes = mysqli_query($GLOBALS["___mysqli_ston"], $editRankQuery) or displayerror(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));


            $updatedRow.="<td><span class='userDataDisp{$userId}'>".$rowValueArray[$i]."</span><input type='text' class='userDataEditVal{$userId}' value='".$rowValueArray[$i]."' style='display:none' /></td>";
		}
		else if($rowIdArray[$i] == -3){
                  $editPrizeMoneyQuery = "UPDATE `events_participants` SET `prize_money`='{$rowValueArray[$i]}' WHERE `user_pid`='{$userId}' AND `page_moduleComponentId`='{$pmcId}' AND `event_id`='{$eventId}'";

		  //		  return $editPrizeMoneyQuery;
                  $editPrizeMoneyRes = mysqli_query($GLOBALS["___mysqli_ston"], $editPrizeMoneyQuery) or displayerror(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));


                  $updatedRow.="<td><span class='userDataDisp{$userId}'>".$rowValueArray[$i]."</span><input type='text' class='userDataEditVal{$userId}' value='".$rowValueArray[$i]."' style='display:none' /></td>";
                }
	/*	else{
			$editValuesQuery = "UPDATE `form_elementdata` SET `form_elementdata`='{$rowValueArray[$i]}' WHERE `user_id`='{$userId}' AND `form_elementid`='{$rowIdArray[$i]}' AND `page_moduleComponentId`='{retglobalPmcId()}'";
					//3
			$editValuesRes = mysql_query($editValuesQuery) or displayerror(mysql_error());


		    $updatedRow.="<td><span class='userDataDisp{$userId}'>".$rowValueArray[$i]."</span><input type='text' class='userDataEditVal{$userId}' value='".$rowValueArray[$i]."' style='display:none' /></td>";
        }*/
           else if($rowIdArray[$i]==10){
 //         if($rowValueArray[$i]=='Others')      
   //             {
                  
     //           $rowIdArray[$i]=12;
       //          }
            $editValuesQuery = "UPDATE `form_elementdata` SET `form_elementdata`='{$rowValueArray[$i]}' WHERE `user_id`='{$userId}' AND `form_elementid`=10 AND `page_moduleComponentId`='{retglobalPmcId()}'";
            $editValuesRes = mysqli_query($GLOBALS["___mysqli_ston"], $editValuesQuery) or displayerror(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
       
     
            $selectTag="<td><span class='userDataDisp{$userId}'>".$rowValueArray[$i]."</span>";
//<select class='userdataeditval{$userid}' style='display:none' >";
            $getCollegeListQuery="SELECT `form_elementtypeoptions` FROM `form_elementdesc` WHERE `page_modulecomponentid`=0 AND `form_elementid`=10";
            $getCollegeListRes=mysqli_query($GLOBALS["___mysqli_ston"], $getCollegeListQuery) or displayerror(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
            if($fullCollegeList=mysqli_fetch_array($getCollegeListRes))
            {
               $selectTag.="<select class='userDataEditVal{$userId}' style='display:none' >";
      
            $fullCollegeArray=explode('|',$fullCollegeList['form_elementtypeoptions']);
         //   displayWarning($fullCollegeList['form_elementtypeoptions']);               
       //     displayWarning('asdasd');
            for($j=0;$j<count($fullCollegeArray);$j=$j+1)
            {
        if($fullCollegeArray[$j]==$rowValueArray[$i])
            $selectTag.="<option  value='{$fullCollegeArray[$j]}' selected>".$fullCollegeArray[$j]."</option>";
        else
            $selectTag.="<option  value='{$fullCollegeArray[$j]}'>".$fullCollegeArray[$j]."</option>";
        
                }   
            }
    $selectTag.="</select>";
    $updatedRow.=$selectTag."</td>";
//$updatedRow.="<input type='text' class='userDataEditVal{$userId}' value='{$rowValueArray[$i]}' style='display:none' /></td>";

//<input type='text' class='userDataEditVal{$userId}' value='{$rowValueArray[$i]}' style='display:none'></td>";
            }
            else
            {
            $editValuesQuery = "UPDATE `form_elementdata` SET `form_elementdata`='{$rowValueArray[$i]}' WHERE `user_id`='{$userId}' AND `form_elementid`='{$rowIdArray[$i]}' AND `page_moduleComponentId`='{retglobalPmcId()}'";
                    //3
            $editValuesRes = mysqli_query($GLOBALS["___mysqli_ston"], $editValuesQuery) or displayerror(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
        //if($rowIdArray[$i]==12){
    
       //$updatedRow.="<input type='text' class='userDataEditVal{$userId}' value='{$rowValueArray[$i]}' style='display:none' /></td>";   
       // }
        //else{
         $updatedRow.="<td><span class='userDataDisp{$userId}'>".$rowValueArray[$i]."</span>";
       $updatedRow.="<input type='text' class='userDataEditVal{$userId}' value='{$rowValueArray[$i]}' style='display:none' /></td>";   
        //}
    
               }	
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
	if($gotoaction=='qahead'){
	  $scriptFolder = "$urlRequestRoot/$cmsFolder/$moduleFolder/events";
	  $updatedRow.=<<<DELETE
		    <td>
	    <script src="$scriptFolder/events.js"></script>
	    <script src="$scriptFolder/jquery.js"></script>
	    <form method='POST' action='./+qahead&subaction=deleteParticipant' onsubmit='return confirmDelete();'>
	    <input type='hidden' name='eventId' value='{$eventId}'/>
	    <input type='hidden' name='userId' value='{$userId}'/>
	    <input type='submit' name='submit' value='Delete'/>
	    </form>
	    </td>
DELETE;
	}

return $updatedRow;
}

function lockEvent($pmcId,$eventId){

	$lockEventQuery = "INSERT INTO `events_locked`(`page_moduleComponentId`,`event_id`) VALUES('{$pmcId}','{$eventId}')";
	$lockEventRes = mysqli_query($GLOBALS["___mysqli_ston"], $lockEventQuery) or displayerror(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
	return displayinfo("Event Locked");
}


function unlockEvent($pmcId,$eventId){

	$unlockEventQuery = "DELETE FROM `events_locked` WHERE `page_moduleComponentId`='{$pmcId}' AND `event_id`='{$eventId}'";
	$unlockEventRes = mysqli_query($GLOBALS["___mysqli_ston"], $unlockEventQuery) or displayerror(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
	return displayinfo("Event Unlocked");
}

function syncExcelFile($pmcId,$eventId,$fileLoc){
    displaywarning($pmcId,$eventId);
	$excelData = readExcelSheet($fileLoc);
    displaywarning(print_r($excelData));
	for($i=1;$i<=count($excelData);$i++){
		for($j=1;$j<=count($excelData[$i]);$j++){
            ;
			$userPid = $excelData[$i][$j];
			if($userPid[0] == 'F' || $userPid[0] == 'f'){
				$userPid = getUserIdFromBookletId($userPid,$pmcId);
			}
			if(!empty($excelData[$i][$j])){
				$checkDuplicateQuery = "SELECT `user_pid` FROM `events_participants` WHERE `page_moduleComponentId`='{$pmcId}' AND `event_id`='{$eventId}' AND `user_pid`='{$userPid}'";
				$checkDuplicateRes = mysqli_query($GLOBALS["___mysqli_ston"], $checkDuplicateQuery) or displayerror(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
				if(mysqli_num_rows($checkDuplicateRes) == 0){
                                        
                     $getBookletIdQuery = "SELECT `booklet_id` FROM `prhospi_pr_status` WHERE `user_id`='{$userPid}' AND `page_moduleComponentId`='{$pmcId}'";
        $getBookletIdRes = mysqli_query($GLOBALS["___mysqli_ston"], $getBookletIdQuery) or displayerror(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
        if(mysqli_num_rows($getBookletIdRes)>0 || 1)
          {
    //displaywarning("Am here");
          $bookletId = mysqli_result($getBookletIdRes, 0);
          $saveUserIdQuery = "INSERT INTO `events_participants`(`page_moduleComponentId`,`event_id`,`user_pid`,`user_team_id`) VALUES('{$pmcId}','{$eventId}','{$userPid}','{$i}')";
                    $saveUserIdRes = mysqli_query($GLOBALS["___mysqli_ston"], $saveUserIdQuery) or displayerror(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));

                    $userInitRankQuery = "INSERT INTO `events_result`(`page_moduleComponentId`,`user_id`,`user_rank`,`event_id`) VALUES('{$pmcId}','{$userPid}','-1','{$eventId}')";
                    displaywarning($userInitQuery);
                    $userInitRankRes = mysqli_query($GLOBALS["___mysqli_ston"], $userInitRankQuery) or displayerror(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
          }
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

	$eventDetails .= displayPR($gotoaction,$pmcId).'<br/><br/><h2>'.getEventName($pmcId,$eventId).'</h2>';

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
	$checkParticipantsRes = mysqli_query($GLOBALS["___mysqli_ston"], $checkParticipantsQuery) or displayerror(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
	
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
	$selectParticipantsQuery = "SELECT `events_participants`.`user_pid`,`events_participants`.`user_team_id` FROM `events_participants` WHERE `events_participants`.`page_moduleComponentId`='{$pmcId}' 
				AND `events_participants`.`event_id`='{$eventId}' AND `events_participants`.`user_pid` NOT IN (SELECT `events_confirmed_participants`.`user_id` 
				FROM `events_confirmed_participants` WHERE `events_confirmed_participants`.`page_moduleComponentId` =  '{$pmcId}') ORDER BY `events_participants`.`user_team_id`";
//	return $selectParticipantsQuery;
	$selectParticipantsRes = mysqli_query($GLOBALS["___mysqli_ston"], $selectParticipantsQuery) or displayerror(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
	$participantsTable.=<<<TABLE
				$smarttable<table id='participants_table' class='display' width='100%' border='1'><thead><tr><th>Team Id</th><th>FID</th><th>Name</th><th>College</th><th>Phone No.</th><th>Rank</th><th>Prize Money</th><th>Options</th></tr></thead>
TABLE;
	while($participant = mysqli_fetch_assoc($selectParticipantsRes)){
	  $participantsTable.="<tr id='partRow{$participant['user_pid']}'><td>".$participant['user_team_id']."</td>";
		$participantNameQuery = "SELECT `".MYSQL_DATABASE_PREFIX."users`.`user_fullname` FROM `".MYSQL_DATABASE_PREFIX."users` WHERE `user_id`='{$participant['user_pid']}'";
		$participantNameRes = mysqli_query($GLOBALS["___mysqli_ston"], $participantNameQuery) or displayerror(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
        if($participant['user_pid']>20000&&$participant['user_pid']<30000)
            $rep = $participant['user_pid']+180000;
        else
            $rep=$participant['user_pid'];
		$participantsTable.="<td>".$rep."</td><td>".mysqli_result($participantNameRes,  0)."</td>";
		$phNoFormId = retPnoneNoFormId();
		$collFormId = retCollFormId();
		$otherCollFormId = retOtherCollFormId();
		$participantsDetailsQuery = "SELECT `form_elementid`,`form_elementdata` FROM `form_elementdata` WHERE `page_moduleComponentId`='{retglobalPmcId()}' AND `user_id`='{$participant['user_pid']}' AND `form_elementid` IN ($phNoFormId,$collFormId) ORDER BY `form_elementid`";
		//4
		$participantsDetailsRes = mysqli_query($GLOBALS["___mysqli_ston"], $participantsDetailsQuery) or displayerror(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
		if(mysqli_num_rows($participantsDetailsRes)>0){
		while($userDetails = mysqli_fetch_assoc($participantsDetailsRes)){
			if($userDetails['form_elementid'] == $phNoFormId){
				$phNoId = $userDetails['form_elementid'];
				$phNo = $userDetails['form_elementdata'];
			}
			else if($userDetails['form_elementid'] == $collFormId){
				$collNameId = $userDetails['form_elementid'];
				$collName = $userDetails['form_elementdata'];
				if($collName == "Others"){
					$getCollNameQuery = "SELECT `form_elementdata`,`form_elementid` FROM `form_elementdata` WHERE `form_elementid`='{$otherCollFormId}' AND `page_modulecomponentid`='{retglobalPmcId()}' AND `user_id`='{$participant['user_pid']}'";
					$getCollNameRes = mysqli_query($GLOBALS["___mysqli_ston"], $getCollNameQuery) or displayerror(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
					while($otherCollDetails = mysqli_fetch_assoc($getCollNameRes)){
						$collNameId = $otherCollDetails['form_elementid'];
						$collName = $otherCollDetails['form_elementdata'];
					}
				}
			}
		}
		}
		else{
			$collNameId="";
			$collName="";
			$phNoId="";
			$phNo="";
		}
		$participantsTable.="<td>".$collName."</td><td>".$phNo."</td>";
		$participantsRankQuery = "SELECT `user_rank` FROM `events_result` WHERE `page_moduleComponentId`='{$pmcId}' AND `user_id`='{$participant['user_pid']}' AND `event_id`='{$eventId}'";
		$participantsRankRes = mysqli_query($GLOBALS["___mysqli_ston"], $participantsRankQuery) or displayerror(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
		$userRank = mysqli_result($participantsRankRes, 0);
		$participantsTable.="<td>".$userRank."</td>";
		$getPrizeMoneyQuery = "SELECT `prize_money` FROM `events_participants` WHERE `event_id`='{$eventId}' AND `user_pid`='{$participant['user_pid']}'";
		$getPrizeMoneyRes = mysqli_query($GLOBALS["___mysqli_ston"], $getPrizeMoneyQuery) or displayerror(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
		$prizeMoney = mysqli_result($getPrizeMoneyRes, 0);
		$participantsTable.="<td>".$prizeMoney."</td>";
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
	//displayerror($gotoaction." ".$eventAction." ".$pmcId." ".$userId." ".$eventId);
	
	if($eventAction == 'event'){
		$eventName = getEventName($pmcId,$eventId);
		$getUserRankQuery = "SELECT `user_rank` FROM `events_result` WHERE `page_moduleComponentId`='{$pmcId}' AND `event_id`='{$eventId}' AND `user_id`='{$userId}'";
		$getUserRankRes = mysqli_query($GLOBALS["___mysqli_ston"], $getUserRankQuery) or displayerror(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
		$userRank = mysqli_result($getUserRankRes, 0);

	}

	else if($eventAction == 'workshop'){
	      displayerror($eventId);
	      return "";
		$getWorkshopNameQuery = "SELECT `workshop_name` FROM `events_workshop_details` WHERE `workshop_id`='{$eventId}' AND `page_moduleComponentId`='{$pmcId}'";
		//		return $getWorkshopNameQuery;
		$getWorkshopNameRes = mysqli_query($GLOBALS["___mysqli_ston"], $getWorkshopNameQuery) or displayerror(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
		$eventName = mysqli_result($getWorkshopNameRes, 0);
		$userRank="-1";
	}
	//displayerror($eventAction." ".$eventId." ".$pmcId." ".$userId." ".$userRank);
	//die();
	$certiImagePage=generateCerti($eventAction,$eventId,$pmcId,$userId,$userRank);
//	return $certiImagePage;

	ob_clean();
	$html2pdf = new HTML2PDF('P','A4','en',true, 'UTF-8',array(0, 0, 0, 0));
    $html2pdf->WriteHTML($certiImagePage);
    $html2pdf->Output($eventName.'_certificates.pdf','D');
}

function printCertificates($eventAction,$pmcId,$eventId){
	global $urlRequestRoot,$sourceFolder,$templateFolder,$cmsFolder,$moduleFolder;
	require_once("$sourceFolder/$moduleFolder/events/html2pdf/html2pdf.class.php");	
	require_once("$sourceFolder/$moduleFolder/events/events_certi_image2.php");
	
	$eventCountQuery = "SELECT COUNT(*) FROM `events_participants` WHERE `event_id` ='{$eventId}' AND `page_moduleComponentId`='{$pmcId}'";
	$eventCountRes = mysqli_query($GLOBALS["___mysqli_ston"], $eventCountQuery) or displayerror(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
	$eventCount = mysqli_result($eventCountRes, 0);
	//	for($i=0;$i<=ceil(
	//	for($i=0;$i < ceil($eventCount/30);$i++){
	if($eventAction == 'event'){
	  
		$getEventNameQuery = "SELECT `event_name` FROM `events_details` WHERE `event_id` = '{$eventId}' AND `page_moduleComponentId`='{$pmcId}'";
		$getEventNameRes = mysqli_query($GLOBALS["___mysqli_ston"], $getEventNameQuery) or displayerror(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
		$eventName = mysqli_result($getEventNameRes, 0);
		//		$limitStart = ($i*30)+1;
		$selectIdQuery = "SELECT DISTINCT `events_participants`.`user_pid`,`events_result`.`user_rank` FROM `events_participants`,`events_result`
				WHERE `events_participants`.`event_id`='{$eventId}' AND `events_result`.`event_id`='{$eventId}' AND `events_result`.`user_id`=`events_participants`.`user_pid` AND `events_participants`.`page_moduleComponentId`='{$pmcId}' AND
				`events_result`.`page_moduleComponentId`='{$pmcId}'  ORDER BY `events_participants`.`user_team_id`,`events_result`.`user_rank`";
		$selectIdRes = mysqli_query($GLOBALS["___mysqli_ston"], $selectIdQuery) or displayerror(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
	}

	else if($eventAction == 'workshop'){
		$getEventNameQuery = "SELECT `workshop_name` FROM `events_workshop_details` WHERE `workshop_id` = '{$eventId}' AND `page_moduleComponentId`='{$pmcId}'";
		$getEventNameRes = mysqli_query($GLOBALS["___mysqli_ston"], $getEventNameQuery) or displayerror(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
		$eventName = mysqli_result($getEventNameRes, 0);

		$selectIdQuery = "SELECT DISTINCT `user_id` FROM `events_workshop_participants`,`events_result` WHERE `page_modulecomponentid`='{$pmcId}' AND `workshop_id`='{$eventId}'";
		$selectIdRes = mysqli_query($GLOBALS["___mysqli_ston"], $selectIdQuery) or displayerror(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
	}
//return $selectIdQuery;
	$certiImagePage = "";
	$count=1;
    global $urlRequestRoot, $moduleFolder, $cmsFolder,$templateFolder,$sourceFolder,$cmsFolder,$STARTSCRIPTS;
    $newFolder = "/var/www/html/14/".$cmsFolder."/uploads/temp/".$eventName."Certis3";
    $newFolder = str_replace(" ","_",$newFolder);
    createFolder($newFolder);
 //zip = new ZipArchive;
    $eventName = str_replace(" ","_",$eventName);
   /* 
   if($zip->open($newFolder."/".$eventName.'.zip',ZipArchive::OVERWRITE) !== TRUE) {
      displaywarning("zip file not created");
      return false;
    }*/

	while($printParticipant = mysqli_fetch_assoc($selectIdRes)){
		if($eventAction == 'event'){
			$userId = $printParticipant['user_pid'];
			$userRank = $printParticipant['user_rank'];
		}
		else if($eventAction == 'workshop'){
			$userId = $printParticipant['user_id'];
			$userRank = $eventId;
		}
		$certiImagePage.=generateCerti($eventAction,$eventId,$pmcId,$userId,$userRank);
		if($count%30 == 0){
		  ob_clean();		
		  $html2pdf = new HTML2PDF('P','A4','en',true, 'UTF-8',array(0, 0, 0, 0));
		  $html2pdf->WriteHTML($certiImagePage);
//		  $html2pdf->Output($newFolder."/".$eventName.'_certificatesupto'.$count.'.pdf','D');  format for zip file "F" instead of "D"
		  $html2pdf->Output($eventName.'_certificatesupto'.$count.'.pdf','D');
		  $certiImagePage="";
//		  $zip->addFile($newFolder."/".$eventName.'_certificatesupto'.$count.'.pdf',$eventName.'_certificatesupto'.$count.'.pdf');
		}
		$count=$count+1;
	}	
  ob_clean();		
		  $html2pdf = new HTML2PDF('P','A4','en',true, 'UTF-8',array(0, 0, 0, 0));
		  $html2pdf->WriteHTML($certiImagePage);
		  $html2pdf->Output($eventName.'_certificatesupto'.$count.'.pdf','D');
		  $certiImagePage="";
//		  echo $newFolder."/".$eventName.'_certificatesupto'.$count.'.pdf';
//		  die();
//		  $zip->addFile($newFolder."/".$eventName.'_certificatesupto'.$count.'.pdf',$eventName.'_certificatesupto'.$count.'.pdf');
/*	
  $zip->close();
  header('Content-Type: application/zip');
  header('Content-disposition: attachment; filename='.$eventName.'.zip');
  header('Content-Length: ' . filesize($newFolder."/".$eventName.'.zip'));
  readfile($newFolder."/".$eventName.'.zip');
  unlink($newFolder."/".$eventName.'.zip');
  exit(0);*/
	//	return $certiImagePage;
    //}
}
  

function generateCerti($eventAction,$eventId,$pmcId,$userId,$userRank){
	if($eventAction == 'event'){
	  if(strcmp($userRank,'-1') == 0)
	    $userCertiRank = -1;
	  else
	    $userCertiRank = -2;
	  $getCertiImgQuery = "SELECT `certificate_id`,`certificate_image` FROM `events_certificate` WHERE `user_rank` = '{$userCertiRank}' AND `page_moduleComponentId`='{$pmcId}' ";
	  $eventName = getEventName($pmcId,$eventId);
	}
	else if($eventAction == 'workshop')
		$getCertiImgQuery = "SELECT `certificate_id`,`certificate_image` FROM `events_certificate` WHERE `user_rank` = '{$eventId}' AND `page_moduleComponentId`='{$pmcId}' AND `event_id` = '-1'";
	$getCertiImgRes = mysqli_query($GLOBALS["___mysqli_ston"], $getCertiImgQuery) or displayerror(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
	if(mysqli_num_rows($getCertiImgRes) == 0){
	  $getEventsCertiQuery = "SELECT `certificate_id`,`certificate_image` FROM `events_certificate` WHERE `user_rank` = '{$userCertiRank}' AND `page_moduleComponentId`='{$pmcId}' AND `event_id` = '-1'";
	  //	  return $getEventsCertiQuery;
	  $getCertiImgRes = mysqli_query($GLOBALS["___mysqli_ston"], $getEventsCertiQuery) or displayerror(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
	}

	$certiImage = "";
	$posXString="";
	$posYString="";
	$posX2String="";
	$posY2String="";
	$certiValueString="";
	
	while($certiDetails = mysqli_fetch_assoc($getCertiImgRes)){
	  $certiImage = $certiDetails['certificate_image'];
		$certiId = $certiDetails['certificate_id'];
		//	return $certiImage;
		//		return $certiId;
		//Get Certificate Details From evets_certficate_details
		$getCertiDetailsQuery = "SELECT `certificate_posx`,`certificate_posy`,`certificate_posx2`,`certificate_posy2`,`form_value_id` FROM `events_certificate_details` WHERE `page_moduleComponentId`='{$pmcId}' AND `certificate_id`='{$certiId}'";
		//		return $getCertiDetailsQuery;
		$getCertiDetailsRes = mysqli_query($GLOBALS["___mysqli_ston"], $getCertiDetailsQuery) or displayerror(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
		//		return mysql_num_rows($getCertiDetailsRes);
		//Get Form Values From form_elementdesc
		//Form_value_id=1 -> Rank
		//Form_value_id=2 -> Event Name
		//Form_value_id=3 -> PArticipant Name
		//Form_value_id=4 -> Coll
		while($getValues = mysqli_fetch_assoc($getCertiDetailsRes)){
			//User Rank

		  // echo $getValues['form_value_id'];
		  //	  die();
			if($getValues['form_value_id'] == 1){
				//imagettftext($rotatedImage, 20, 90, $getValues['certificate_posx'], $getValues['certificate_posy'], $color, $font, $userRank);
			  //	  return $userRank;
				$posXString.=$getValues['certificate_posx']."::";
				$posYString.=$getValues['certificate_posy']."::";
				$posX2String.=$getValues['certificate_posx2']."::";
				$posY2String.=$getValues['certificate_posy2']."::";
				$participantRank="";
				if(strcmp($userRank ,"1") == 0)
				  $participantRank="1st";
				else if(strcmp($userRank,"2")==0)
				  $participantRank="2nd";
				else if(strcmp($userRank,"3")==0)
                                  $participantRank="3rd";
				else if(strcmp($userRank,"4")==0)
                                  $participantRank="4th";
				else if(strcmp($userRank,"5")==0)
                                  $participantRank="5th";
				else if(strcmp($userRank,"6")==0)
                                  $participantRank="6th";
				else if(strcmp($userRank,"2")==0)
                                  $participantRank="2nd";
				else if(strcmp($userRank,"7")==0)
                                  $participantRank="7th";
				else if(strcmp($userRank,"8")==0)
                                  $participantRank="8th";
				else if(strcmp($userRank,"9")==0)
                                  $participantRank="9th";
				else if(strcmp($userRank,"10")==0)
                                  $participantRank="10th";
				else if(strcmp($userRank,"11")==0)
				  $participantRank="11th";
				else if(strcmp($userRank,"12")==0)
				  $participantRank="12th";
				else if(strcmp($userRank,"13")==0)
				  $participantRank="13th";
				else if(strcmp($userRank,"14")==0)
				  $participantRank="14th";
				else if(strcmp($userRank,"15")==0)
				  $participantRank="15th";
				else if(strcmp($userRank,"16")==0)
				  $participantRank="16th";
				  /*
				//switch($userRank){
				  /*case 1:$participantRank = "1st";
				  break;
				case 2:$participantRank = "2nd";
				  break;
				case 3:$participantRank = "3rd";
				  break;
				  	case 4:$participantRank="4th";
				  break;
				  	case 5:$$participantRank="5th";
				  break;
				case 6:$participantRank="7th";
				  break;
				case 8:$participantRank="8th";
				  break;
				case 9:$participantRank="9th";
				  break;
				case 10:$participantRank="10th";
				break;*/
				//}
				$certiValueString.=$participantRank."::";
			}
			//Event Name
				else if($getValues['form_value_id'] == 2){
				//Get Event Name
				
			//	imagettftext($rotatedImage, 20, 90, $getValues['certificate_posx'], $getValues['certificate_posy'], $color, $font, $eventName);
				$posXString.=$getValues['certificate_posx']."::";
				$posYString.=$getValues['certificate_posy']."::";
				$posX2String.=$getValues['certificate_posx2']."::";
				$posY2String.=$getValues['certificate_posy2']."::";
				$eventName = str_replace(" Workshop","",$eventName);
				$certiValueString.=$eventName."::";	
				}
			else if($getValues['form_value_id'] == 3){
				$getUserNameQuery = "SELECT `user_fullname` FROM `".MYSQL_DATABASE_PREFIX."users` WHERE `user_id`='{$userId}'";
				$getUserNameRes = mysqli_query($GLOBALS["___mysqli_ston"], $getUserNameQuery) or displayerror(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
				$userName = ucwords_specific(strtolower(mysqli_result($getUserNameRes,  0)),".");
				$posXString.=$getValues['certificate_posx']."::";
				$posYString.=$getValues['certificate_posy']."::";
				$posX2String.=$getValues['certificate_posx2']."::";
				$posY2String.=$getValues['certificate_posy2']."::";
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
				$getFormValuesRes = mysqli_query($GLOBALS["___mysqli_ston"], $getFormValuesQuery) or displayerror(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
			//	return mysql_num_rows($getFormValuesQuery);
				while($formData = mysqli_fetch_assoc($getFormValuesRes)){
				//	imagettftext($rotatedImage, 20, 90, $getValues['certificate_posx'], $getValues['certificate_posy'], $color, $font, $formData['form_elementdata']);
					if($formData['form_elementdata'] == "Others"){
						$collFormId = retOtherCollFormId();
						$getCollNameQuery = "SELECT `form_elementdata` FROM `form_elementdata` WHERE `page_moduleComponentId`='{$formPmcId}' AND `user_id`='{$userId}' AND `form_elementid`='{$collFormId}'";
						$getCollNameRes = mysqli_query($GLOBALS["___mysqli_ston"], $getCollNameQuery) or displayerror(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
						$collName = ucwords_specific(strtolower(mysqli_result($getCollNameRes, 0)),".");
					} 
					else
					  $collName = ucwords_specific(strtolower($formData['form_elementdata']),".");
					$posXString.=$getValues['certificate_posx']."::";
					$posYString.=$getValues['certificate_posy']."::";
					$posX2String.=$getValues['certificate_posx2']."::";
					$posY2String.=$getValues['certificate_posy2']."::";
					$certiValueString.=$collName."::";
				}
			}
			}
		}

		$posXString=rtrim($posXString,"::");
		$posYString=rtrim($posYString,"::");
		$posX2String=rtrim($posX2String,"::");
		$posY2String=rtrim($posY2String,"::");
		$certiValueString=rtrim($certiValueString,"::");
		//$certiImagePage = "Hello".$certiImage;
				
		$certiImagePage="<img style='width:100%;height:100%' src='".generateImage($certiImage,$posXString,$posYString,$posX2String,$posY2String,$certiValueString)."'>";
		return $certiImagePage;
}

function getUserIdFromBookletId($bookletId,$mcId) {
  $bookletId = escape($bookletId);
  $checkUserRegisteredToPr = "SELECT * FROM `prhospi_pr_status` WHERE `page_modulecomponentid` = {$mcId} AND `booklet_id` = '{$bookletId}'";
  $checkUserRegisteredToPrQuery = mysqli_query($GLOBALS["___mysqli_ston"], $checkUserRegisteredToPr) or displayerror(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
  $row = mysqli_fetch_assoc($checkUserRegisteredToPrQuery);
  return $row['user_id'];

}

function getPrHeadOptions($pmcId){
	$options = <<<OPTIONS
		
<h3>Choose Option:</h3>
		<table><tr><td>
		<form method='POST' action='./+prhead&subaction=viewEventList'>
			<input type='submit' value='Events' name='viewEvents'/>
		</form></td><td>
		<form method='POST' action='./+prhead&subaction=viewWorkshopList'>
			<input type='submit' value='Workshops' name='viewWorkshops'/>
		</form></td></tr></table>
OPTIONS;
	return $options;
}

function getWorkshopsList($pmcId){
	$selectWorkshopQuery = "SELECT `workshop_id`,`workshop_name` FROM `events_workshop_details` WHERE `page_moduleComponentId`='{$pmcId}' ORDER BY `workshop_name`";
	$selectWorkshopRes = mysqli_query($GLOBALS["___mysqli_ston"], $selectWorkshopQuery) or displayerror(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
	if(mysqli_num_rows($selectWorkshopRes) > 0){
		$selectWorkshop = <<<FORM
			<h3>Choose Workshop</h3>
			<form method="POST" action="./+prhead&subaction=viewWorkshopDetails">
				<select name='workshopId' id='workshopId'>
FORM;
		while($workshopList = mysqli_fetch_assoc($selectWorkshopRes)){
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
	$checkParticipantsRes = mysqli_query($GLOBALS["___mysqli_ston"], $checkParticipantsQuery) or displayerror(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
	if(mysqli_num_rows($checkParticipantsRes) == 0){
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
		<table><tr><td>
		<a href='./+prhead&subaction=downloadExcel&workshop_id=$workshopId'>Download Details</a>
		</td><td>
		<form method='POST' action='./+prhead&subaction=printCerti'>
		<input type='hidden' name='workshopId' value='{$workshopId}'>
		<input type='submit' value='Print Certificates PDF'>
		</form></td></tr></table>
	
PRINTTABLE;
		//$downloadTable = getUserDetailsTable($pmcId,$eventId);
		//$eventDetails.=displayExcelForTable($downloadTable);
	
		$workshopParticipants=displayWorkshopParticipants($pmcId,$workshopId);
		//displayExcelForTable($eventParticipants);
		$workshopDetails .= $workshopParticipants;
	}
	return $workshopDetails;	
}

function displayWorkshopParticipants($pmcId,$workshopId){
	global $STARTSCRIPTS;
	$smarttable = smarttable::render(array('participants_table'),null);
	$STARTSCRIPTS.="initSmartTable();";
	$participantsTable = "";
	$selectParticipantsQuery = "SELECT `events_workshop_participants`.`user_id`,`events_workshop_participants`.`user_team_id` FROM `events_workshop_participants` WHERE `events_workshop_participants`.`page_moduleComponentId`='{$pmcId}' 
				AND `events_workshop_participants`.`workshop_id`='{$workshopId}' AND `events_workshop_participants`.`user_id` ORDER BY `events_workshop_participants`.`user_team_id`";
//	return $selectParticipantsQuery;
	$selectParticipantsRes = mysqli_query($GLOBALS["___mysqli_ston"], $selectParticipantsQuery) or displayerror(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
	$participantsTable.=<<<TABLE
				$smarttable<table id='participants_table' class='display' width='100%' border='1'><thead><tr><th>Team Id</th><th>FID</th><th>Name</th><th>College</th><th>Phone No.</th><th>Options</th><th>Print</th></tr></thead>
TABLE;
	while($participant = mysqli_fetch_assoc($selectParticipantsRes)){
		$editRowId = "";
		$participantNameQuery = "SELECT `".MYSQL_DATABASE_PREFIX."users`.`user_fullname` FROM `".MYSQL_DATABASE_PREFIX."users` WHERE `user_id`='{$participant['user_id']}'";
		$participantNameRes = mysqli_query($GLOBALS["___mysqli_ston"], $participantNameQuery) or displayerror(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
        if($participant['user_id']>20000&&$participant['user_id']<30000)
            $rep = $participant['user_id']+180000;
        else
            $rep=$participant['user_id'];
		$participantsTable.="<tr id='partRow{$participant['user_id']}'><td>".$participant['user_team_id']."</td><td>".$rep."</td><td><span class='userDataDisp{$participant['user_id']}'>".mysqli_result($participantNameRes,  0)."</span><input type='text' class='userDataEditVal{$participant['user_id']}' value='".mysqli_result($participantNameRes,  0)."' style='display:none'></td>";
		$editRowId.='-1,';
		$phNoFormId = retPnoneNoFormId();
		$collFormId = retCollFormId();
		$otherCollFormId = retOtherCollFormId();
		$participantsDetailsQuery = "SELECT `form_elementid`,`form_elementdata` FROM `form_elementdata` WHERE `page_moduleComponentId`='{retglobalPmcId()}' AND `user_id`='{$participant['user_id']}' AND `form_elementid` IN ($phNoFormId,$collFormId) ORDER BY `form_elementid`";
		//1
		$participantsDetailsRes = mysqli_query($GLOBALS["___mysqli_ston"], $participantsDetailsQuery) or displayerror(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
		while($userDetails = mysqli_fetch_assoc($participantsDetailsRes)){
			if($userDetails['form_elementid'] == $phNoFormId){
				$phNoId = $userDetails['form_elementid'];
				$phNo = $userDetails['form_elementdata'];
			}
			else if($userDetails['form_elementid'] == $collFormId){
				$collNameId = $userDetails['form_elementid'];
				$collName = $userDetails['form_elementdata'];
				if($collName == "Others"){
					$getCollNameQuery = "SELECT `form_elementdata`,`form_elementid` FROM `form_elementdata` WHERE `form_elementid`='{$otherCollFormId}' AND `page_modulecomponentid`='{retglobalPmcId()}' AND `user_id`='{$participant['user_id']}'";
					$getCollNameRes = mysqli_query($GLOBALS["___mysqli_ston"], $getCollNameQuery) or displayerror(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
					while($otherCollDetails = mysqli_fetch_assoc($getCollNameRes)){
						$collNameId = $otherCollDetails['form_elementid'];
						$collName = $otherCollDetails['form_elementdata'];
					}
				}
			}
		}
		$editRowId.=$collNameId.','.$phNoId.',';
		$participantsTable.="<td><span class='userDataDisp{$participant['user_id']}'>".$collName."</span><input type='text' class='userDataEditVal{$participant['user_id']}' value='{$collName}' style='display:none'></td>";
		$participantsTable.="<td><span class='userDataDisp{$participant['user_id']}'>".$phNo."</span><input type='text' class='userDataEditVal{$participant['user_id']}' value='{$phNo}' style='display:none'></td>";
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

function syncExcelFileWorkshop($pmcId,$workshopId,$fileLoc){
	$excelData = readExcelSheet($fileLoc);
	for($i=1;$i<=count($excelData);$i++){
		for($j=1;$j<=count($excelData[$i]);$j++){
			$userPid = $excelData[$i][$j];
			if($userPid[0] == 'F' || $userPid[0] == 'f'){
				$userPid = getUserIdFromBookletId($userPid,$pmcId);
			}
			if(!empty($excelData[$i][$j])){
				$checkDuplicateQuery = "SELECT `user_id` FROM `events_workshop_participants` WHERE `page_moduleComponentId`='{$pmcId}' AND `workshop_id`='{$workshopId}' AND `user_id`='{$userPid}'";
				$checkDuplicateRes = mysqli_query($GLOBALS["___mysqli_ston"], $checkDuplicateQuery) or displayerror(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
				if(mysqli_num_rows($checkDuplicateRes) == 0){
					$saveUserIdQuery = "INSERT INTO `events_workshop_participants`(`page_moduleComponentId`,`workshop_id`,`user_id`,`user_team_id`) VALUES('{$pmcId}','{$workshopId}','{$userPid}','{$i}')";
					$saveUserIdRes = mysqli_query($GLOBALS["___mysqli_ston"], $saveUserIdQuery) or displayerror(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
				}
			}
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
			$editNameQuery = "UPDATE `".MYSQL_DATABASE_PREFIX."users` SET `user_fullname`='{$rowValueArray[$i]}' WHERE `user_id`='{$userId}'";
			$editNameRes = mysqli_query($GLOBALS["___mysqli_ston"], $editNameQuery) or displayerror(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
		}
		else{
			$editValuesQuery = "UPDATE `form_elementdata` SET `form_elementdata`='{$rowValueArray[$i]}' 
					WHERE `user_id`='{$userId}' AND `form_elementid`='{$rowIdArray[$i]}' AND `page_moduleComponentId`='{retglobalPmcId()}'";
					//3
			$editValuesRes = mysqli_query($GLOBALS["___mysqli_ston"], $editValuesQuery) or displayerror(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
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



function displayQA($pmcid){
	$selectEventQuery = "SELECT DISTINCT `event_name` FROM `events_details` WHERE `page_moduleComponentId`='{$pmcid}' AND `event_id` NOT IN (SELECT `event_id` FROM `events_locked` WHERE `page_moduleComponentId` = '{$pmcid}') ORDER BY `event_name`";
	$selectEventRes = mysqli_query($GLOBALS["___mysqli_ston"], $selectEventQuery) or displayerror(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
	if(mysqli_num_rows($selectEventRes) > 0){
	$gotoaction='qa';
	$selectEvent=searchParticipant($gotoaction,$pmcid,1);
		$selectEvent.= <<<FORM
			<h3>Choose Event:</h3>
			<form method="POST" action="./+qa&subaction=viewEvent">
				<select name='eventId' id='eventId'>
FORM;
		while($eventList = mysqli_fetch_assoc($selectEventRes)){
		  $selectIdQuery = "SELECT `event_id` FROM `events_details` WHERE `page_moduleComponentId`='{$pmcid}' AND `event_name`='{$eventList['event_name']}' LIMIT 1";
		  $selectIdRes = mysqli_query($GLOBALS["___mysqli_ston"], $selectIdQuery) or displayerror(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
		  $eventId = mysqli_result($selectIdRes, 0);
			$selectEvent.=<<<DROPDOWN
			<option value="{$eventId}">{$eventList['event_name']}</option>	
DROPDOWN;
		}
		$selectEvent.="</select><input type='submit'value='Select'></form>";
	}
	else
		{
               $gotoaction='qa';
		$selectEvent=searchParticipant($gotoaction,$pmcId,1);
		$selectEvent.= displayerror("No Events Found");
		}	
return $selectEvent;
}


function editParticipantRank($gotoaction,$pmcId,$eventId,$userId,$newRank){
	$updateRankQuery = "UPDATE `events_result` SET `user_rank` = '{$newRank}' WHERE `page_moduleComponentId`='{$pmcId}' AND `user_id`='{$userId}' AND `event_id`='{$eventId}'";
	$updateRankRes = mysqli_query($GLOBALS["___mysqli_ston"], $updateRankQuery) or displayerror(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
	return $newRank;
}




// ~~~~~~~~~~~~~~~~ QA Head  ~~~~~~~~~~~~~~~~~~~`

function qaHeadOptions($pmcId){
	$selectEventQuery = "SELECT DISTINCT `event_name` FROM `events_details` WHERE `page_moduleComponentId`='{$pmcId}' ORDER BY `event_name`";
	$selectEventRes = mysqli_query($GLOBALS["___mysqli_ston"], $selectEventQuery) or displayerror(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
	if(mysqli_num_rows($selectEventRes) > 0){
	
 $gotoaction='qahead';
$selectEvent=searchParticipant($gotoaction,$pmcId,1);	
$selectEvent .= <<<FORM
		 	<h3>Chooseasdasdsdasd Event</h3>
			<form method="POST" action="./+qahead&subaction=viewEvent">
				<select name='eventId'>
FORM;
		while($eventList = mysqli_fetch_assoc($selectEventRes)){
		  $selectEventIdQuery = "SELECT `event_id` FROM `events_details` WHERE `page_moduleComponentId`='{$pmcId}' AND `event_name`='{$eventList['event_name']}' LIMIT 1";
		  $selectEventIdRes = mysqli_query($GLOBALS["___mysqli_ston"], $selectEventIdQuery) or displayerror(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
		  $eventId = mysqli_result($selectEventIdRes, 0);
			$selectEvent.=<<<DROPDOWN
			<option value="{$eventId}">{$eventList['event_name']}</option>	
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
	$selectLockedEventsQuery = "SELECT DISTINCT `events_details`.`event_name` FROM `events_details` INNER JOIN `events_locked` 
		ON `events_locked`.`event_id` = `events_details`.`event_id` AND `events_locked`.`page_moduleComponentId` = `events_details`.`page_moduleComponentId` 
		AND `events_details`.`page_moduleComponentId` = '{$pmcId}' ORDER BY `event_name`";
	$selectLockedEventsRes = mysqli_query($GLOBALS["___mysqli_ston"], $selectLockedEventsQuery) or displayerror(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
	if(mysqli_num_rows($selectLockedEventsRes) > 0){
		$selectEvent = <<<FORM
			<h3>Choose Event</h3>
			<form method="POST" action="./+{$gotoaction}&subaction=viewEvent">
				<select name='eventId'>
FORM;
		while($eventList = mysqli_fetch_assoc($selectLockedEventsRes)){
		  $selectEventIdQuery = "SELECT `event_id` FROM `events_details` WHERE `page_moduleComponentId`='{$pmcId}' AND `event_name`='{$eventList['event_name']}' LIMIT 1";
		  $selectEventIdRes = mysqli_query($GLOBALS["___mysqli_ston"], $selectEventIdQuery) or displayerror(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
		  $eventId = mysqli_result($selectEventIdRes, 0);
			$selectEvent.=<<<DROPDOWN
			<option value="{$eventId}">{$eventList['event_name']}</option>	
DROPDOWN;
		}
		$selectEvent.="</select><input type='submit'value='Select'></form>";
	}
	else
		$selectEvent = displayerror("No Events Found");
	return $selectEvent;
}

function searchByUserId($gotoaction,$pmcId){
	$searchForm = <<<FORM
		<h2>Enter FID:</h2>
		<form method='POST' action='./+{$gotoaction}&subaction=userEventDetails'>
			<input type='text' name='userId'/>
			<input type='submit' name='submit' value='Submit'/>
		</form>
FORM;
	return $searchForm;
}

function getUserDetails($gotoaction,$pmcId,$userBookletId){
	global $STARTSCRIPTS;
	$smarttable = smarttable::render(array('user_table'),null);
	$STARTSCRIPTS.="initSmartTable();";
	$userEventListTable="";
	$userBookletId = escape($userBookletId);
	$pmcId = escape($pmcId);
	$userEventListTable.=searchByUserId($gotoaction,$pmcId);
	if($userBookletId[0] == 'P'){
	  $userId = getUserIdFromBookletId($userBookletId,$pmcId);
	}
	else
		$userId = $userBookletId;
	$getUserDetailsQuery = "SELECT * FROM `events_participants` WHERE `user_pid`='{$userId}' AND `page_moduleComponentId`='{$pmcId}' AND `event_id` IN(SELECT `event_id` FROM `events_locked` WHERE `page_moduleComponentId`='{$pmcId}')";
	$getUserDetailsRes = mysqli_query($GLOBALS["___mysqli_ston"], $getUserDetailsQuery) or displayerror(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
	if(mysqli_num_rows($getUserDetailsRes) == 0)
		displayerror("No info found.");
	else{

		$getUserNameQuery = "SELECT `user_fullname` FROM `".MYSQL_DATABASE_PREFIX."users` WHERE `user_id`='{$userId}'";
		$getUserNameRes = mysqli_query($GLOBALS["___mysqli_ston"], $getUserNameQuery) or displayerror(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
		$userEventListTable.="<br/><h4>".mysqli_result($getUserNameRes, 0)."</h4><br/>";
		$userEventListTable.=<<<FORM
			<form method='post' action='./+{$gotoaction}&subaction=printUserCerti'>
				<input type='hidden' value='{$userId}' name='userId'/>
				<input type='submit' name='submit' value='Print User Certificates'/>
			</form>
FORM;
		$userEventListTable.="$smarttable<table id='user_table' class='display' width='100%' border='1'><thead><th>User FID</th><th>Event Name</th><th>User Rank</th></thead>";
		while($userEvent = mysqli_fetch_assoc($getUserDetailsRes)){
			$eventName = getEventName($pmcId,$userEvent['event_id']);
			$getUserRankQuery = "SELECT `user_rank` FROM `events_result` WHERE `page_moduleComponentId`='{$pmcId}' AND `user_id`='{$userId}' AND `event_id`='{$userEvent['event_id']}'";
			$getUserRankRes = mysqli_query($GLOBALS["___mysqli_ston"], $getUserRankQuery) or displayerror(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
			$userRank = mysqli_result($getUserRankRes, 0);
			$userEventListTable .= "<tr><td>".$userId."</td><td>".$eventName."</td><td>".$userRank."</td></tr>";
		}
		$userEventListTable.="</table>";
		return $userEventListTable;
	}
}

function printUserCerti($pmcId,$userId){
	//eventid,pmcid,userid,userrank
	//$eventAction,$eventId,$pmcId,$userId,$userRank
	global $urlRequestRoot,$sourceFolder,$templateFolder,$cmsFolder,$moduleFolder;
	require_once("$sourceFolder/$moduleFolder/events/html2pdf/html2pdf.class.php");	
	require_once("$sourceFolder/$moduleFolder/events/events_certi_image2.php");
	$getUserDetailsQuery = "SELECT * FROM `events_participants` WHERE `user_pid`='{$userId}' AND `page_moduleComponentId`='{$pmcId}'";
	$getUserDetailsRes = mysqli_query($GLOBALS["___mysqli_ston"], $getUserDetailsQuery) or displayerror(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
	$certiImagePage="";
	if(mysqli_num_rows($getUserDetailsRes) != 0){
		while($userEvent = mysqli_fetch_assoc($getUserDetailsRes)){
			$getUserRankQuery = "SELECT `user_rank` FROM `events_result` WHERE `page_moduleComponentId`='{$pmcId}' AND `user_id`='{$userId}' AND `event_id`='{$userEvent['event_id']}'";
			$getUserRankRes = mysqli_query($GLOBALS["___mysqli_ston"], $getUserRankQuery) or displayerror(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
			$userRank = mysqli_result($getUserRankRes, 0);
			$certiImagePage.=generateCerti('event',$userEvent['event_id'],$pmcId,$userId,$userRank);
		}
	}
	ob_clean();
	$html2pdf = new HTML2PDF('P','A4','en',true, 'UTF-8',array(0, 0, 0, 0));
    $html2pdf->WriteHTML($certiImagePage);
    $html2pdf->Output($userId.'_certificates.pdf','D');
    
}

function prMain($gotoaction,$pmcId){
	$getOption = "<h3>Choose Option:</h3>";
	$getOption .= <<<OPTION
		<table><tr><td><a href='./+{$gotoaction}&subaction=userDetailForm'>View By User</a></td></tr><tr><td><a href='./+{$gotoaction}&subaction=viewEventOptions'>View By Event</a></td></tr></table>
OPTION;
	return $getOption;
}

function deleteParticipant($pmcId,$userId,$eventId){
  $deleteParticipantQuery = "DELETE FROM `events_participants` WHERE `user_pid`='{$userId}' AND `event_id`='{$eventId}' AND `page_moduleComponentId`='{$pmcId}'";
  $deleteParticipantRes = mysqli_query($GLOBALS["___mysqli_ston"], $deleteParticipantQuery) or displayerror(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
  $deleteParticipantResultQuery = "DELETE FROM `events_result` WHERE `event_id`='{$eventId}' AND `user_id`='{$userId}' AND `page_moduleComponentId`='{$pmcId}'";
  $deleteParticipantResultRes = mysqli_query($GLOBALS["___mysqli_ston"], $deleteParticipantResultQuery) or displayerror(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
  $scriptFolder = "$urlRequestRoot/$cmsFolder/$moduleFolder/events";
  $eventForm = <<<FORM
    <script src="$scriptFolder/events.js"></script>
    <script src="$scriptFolder/jquery.js"></script>
    <form method='POST' action='./+qahead&subaction=viewEvent' id='delform'>
    <input type='hidden' name='eventId' value='{$eventId}'/>
    <input type='submit' name='Submit' value='Submit'/>
    </form>
    <script type='text/javascript'>
    document.getElementById('delform').submit();
  </script>
FORM;
  return $eventForm;
}

function deleteEventForm($pmcId,$eventId){
  $deleteEventForm = <<<FORM
    <form method="POST" action='./+qahead&subaction=deleteEvent'>
    <input type='hidden' name='eventId' value='{$eventId}'>
    <input type='submit' name='submit' value='Delete Event'>
    </form>
FORM;
  return $deleteEventForm;
}
function addParticipant($gotoaction,$pmcId,$eventId){
    $addParticipantForm=<<<FORM
    <form method='POST' action='./+{$gotoaction}&subaction=addParticipant'>
    <input type='hidden' name='eventId' value='{$eventId}'>
    <input type='submit' name='submit' value='add participant'>
    </form>
FORM;
return $addParticipantForm;
}

function searchParticipantModified($gotoaction,$pmcId){
    $searchParticipantForm=<<<FORM
    <form method='POST' action='./+{$gotoaction}&subaction=getParticipant'>
 
    <input type='text' name='userId'>
    <input type='submit' name='submit' value='search participant'>
    </form>
FORM;
return $searchParticipantForm;
}

function searchParticipant($gotoaction,$pmcId,$eventId){
    $searchParticipantForm=<<<FORM
    <form method='POST' action='./+{$gotoaction}&subaction=getParticipant'>
    <input type='hidden' name='eventId' value='{$eventId}'>
    <input type='text' name='userId'>
    <input type='submit' name='submit' value='search participant'>
    </form>
FORM;
return $searchParticipantForm;
}

function deleteEventQa($pmcId,$eventId){
  $scriptFolder = "$urlRequestRoot/$cmsFolder/$moduleFolder/events";
  $deleteEventParticipantsQuery = "DELETE FROM `events_participants` WHERE `event_id`='{$eventId}' AND `page_moduleComponentId`='{$pmcId}'";
  $deleteEventParticipantsRes = mysqli_query($GLOBALS["___mysqli_ston"], $deleteEventParticipantsQuery) or displayerror(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
  $deleteEventResultQuery = "DELETE FROM `events_result`WHERE `event_id`='{$eventId}' AND `page_moduleComponentId`='{$pmcId}'";
  $deleteEventResultRes = mysqli_query($GLOBALS["___mysqli_ston"], $deleteEventResultQuery) or displayerror(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
  $eventForm = <<<FORM
    <script src="$scriptFolder/events.js"></script>
    <script src="$scriptFolder/jquery.js"></script>
    <form method='POST' action='./+qahead&subaction=viewEvent' id='delform' onsubmit='alert("FSDF");'>
    <input type='hidden' name='eventId' value='{$eventId}'/>
    <input type='submit' name='Submit' value='Submit'/>
    </form>
    <script type='text/javascript'>
    document.getElementById('delform').submit();
  </script>
FORM;
  return $eventForm;
}


function createFolder($uploadDir) {
if (!file_exists($uploadDir)) {
    //displaywarning("The folder $uploadDir does not exist. Trying to creating it.");
    mkdir($uploadDir, 0755);
    if (!file_exists($uploadDir)) {
      displayerror("Creation of directory failed");
      return false;
    }
    else
      displayinfo("Created $uploadDir.");
  }
  return true;

}




function ucwords_specific ($string, $delimiters = '', $encoding = NULL)
{
  if ($encoding === NULL) { $encoding = mb_internal_encoding();}

  if (is_string($delimiters))
    {
      $delimiters =  str_split( str_replace(' ', '', $delimiters));
    }

  $delimiters_pattern1 = array();
  $delimiters_replace1 = array();
  $delimiters_pattern2 = array();
  $delimiters_replace2 = array();
  foreach ($delimiters as $delimiter)
    {
      $uniqid = uniqid();
      $delimiters_pattern1[]   = '/'. preg_quote($delimiter) .'/';
      $delimiters_replace1[]   = $delimiter.$uniqid.' ';
      $delimiters_pattern2[]   = '/'. preg_quote($delimiter.$uniqid.' ') .'/';
      $delimiters_replace2[]   = $delimiter;
    }

  // $return_string = mb_strtolower($string, $encoding);
  $return_string = $string;
  $return_string = preg_replace($delimiters_pattern1, $delimiters_replace1, $return_string);

  $words = explode(' ', $return_string);

  foreach ($words as $index => $word)
    {
      $words[$index] = mb_strtoupper(mb_substr($word, 0, 1, $encoding), $encoding).mb_substr($word, 1, mb_strlen($word, $encoding), $encoding);
    }

  $return_string = implode(' ', $words);

  $return_string = preg_replace($delimiters_pattern2, $delimiters_replace2, $return_string);

  return $return_string;
} 











 

?>
