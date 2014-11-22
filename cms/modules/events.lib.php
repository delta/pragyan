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


class events implements module,fileuploadable {
	private $userId;
	private $moduleComponentId;
	public function getHtml($gotuid, $gotmoduleComponentId, $gotaction) {
		$this->userId = $gotuid;
		$this->moduleComponentId = $gotmoduleComponentId;
		if ($gotaction == 'csg')
			return $this->actionCsg();
		if ($gotaction == 'eventshead')
			return $this->actionEventshead();
		if ($gotaction == 'ochead')
			return $this->actionOchead();
		if ($gotaction == 'octeam')
			return $this->actionOcteam();
		if ($gotaction == 'qa')
			return $this->actionQa();
		if ($gotaction == 'qahead')
			return $this->actionQahead();
		if ($gotaction == 'pr')
			return $this->actionPr();
		if ($gotaction == 'prhead')
			return $this->actionPrhead();
		if ($gotaction == 'view')
			return $this->actionView();
	/*    if ($gotaction == '')
		  return $this->actionAddroom();
	*/
		  else return $this->actionView();
		}

		public function actionView(){
			global $urlRequestRoot,$sourceFolder,$templateFolder,$cmsFolder,$moduleFolder;
			$moduleComponentId=$this->moduleComponentId;
			$userId=$this->userId;
			$scriptFolder = "$sourceFolder/$moduleFolder/events/";
			require_once("$sourceFolder/$moduleFolder/events/events_common.php");
			if(isset($_GET['subaction'])){
				if($_GET['subaction']=="map"){
					return showEventMap();
				}
				if($_GET['subaction']=="mobile"){
					return getEventsJSON($moduleComponentId);
					exit;
				}
				if($_GET['subaction']=="schedule"){
					return getSchedule($moduleComponentId);
				}
			}
			else{
				return selectViewSubaction();
			}
		}
		public function actionCsg(){
			global $urlRequestRoot,$sourceFolder,$templateFolder,$cmsFolder,$moduleFolder;
			$moduleComponentId=$this->moduleComponentId;
			$userId=$this->userId;
			return "hello";
		}
		public function actionEventshead(){
			global $urlRequestRoot,$sourceFolder,$templateFolder,$cmsFolder,$moduleFolder;
			$moduleComponentId=$this->moduleComponentId;
			$userId=$this->userId;
			require_once("$sourceFolder/$moduleFolder/events/events_common.php");
			require_once("$sourceFolder/$moduleFolder/events/events_forms.php");
			if(isset($_POST['type'])){
				if($_POST['type']=='add'){
					validateAddEventData($moduleComponentId);
				}
				else if($_POST['type']=='edit'){
					validateEditEventData($moduleComponentId);
				}
				else if($_POST['type']=="notif"){
				  $query="INSERT INTO `events_notifications` VALUES (NULL, '{$_POST['content']}', CURRENT_TIMESTAMP);";
				  //echo NOW();
				  mysql_query($query);
				  //				  header('Location: ./+eventsHead');
				  //
				}
				exit();
			}
			if(isset($_GET['subaction'])){
				if($_GET['subaction']=="addEvent"){
					return addNewEvent();
				}
				if($_GET['subaction']=="deleteEvent"){
					return deleteEvent($_POST['eventId'], $moduleComponentId);
					exit();
				}
				if($_GET['subaction']=="editEvent"){
					return editEvent($_GET['eventId'], $moduleComponentId);
				}
				if($_GET['subaction']=="notif"){
				  //return ":LL";
				  return getEventsForm();
				}
			}
			else{
				return selectEventsHeadSubaction($moduleComponentId);
			}
		}
		public function actionOchead(){
			global $urlRequestRoot,$sourceFolder,$templateFolder,$cmsFolder,$moduleFolder;
			$moduleComponentId=$this->moduleComponentId;
			$userId=$this->userId;
			require_once("$sourceFolder/$moduleFolder/events/events_common.php");
			require_once("$sourceFolder/$moduleFolder/events/events_forms.php");
			if(isset($_POST['quantity'])){
				validateProcurementData($moduleComponentId);
				exit();
			}
			if(isset($_POST['editquantity'])){
				validateEditProcurementData($moduleComponentId);
				exit();
			}
			if(isset($_POST['newProc'])){
				validateNewProcurement($moduleComponentId);
				exit();
			}
			if(isset($_POST['eventnum'])){
				return editProcurement($_POST['eventnum']);
				exit();
			}
			if(isset($_GET['subaction'])){
				if($_GET['subaction']=="viewAll"){
					return getAllProcurements($moduleComponentId);
				}
				if($_GET['subaction']=="addEventProcurement"){
					return addNewProcurement();
				}
				if($_GET['subaction']=="addProcurement"){
					return addNewProc();
				}
				if($_GET['subaction']=="deleteProcurement"){
					return deleteProcurement($_POST['eventname'], $moduleComponentId);
				}
			}
			else{
				return selectSubactionProcurement();
			}
		}
		public function actionOcteam(){
			global $urlRequestRoot,$sourceFolder,$templateFolder,$cmsFolder,$moduleFolder;
			$moduleComponentId=$this->moduleComponentId;
			$userId=$this->userId;
			require_once("$sourceFolder/$moduleFolder/events/events_common.php");

			if(isset($_GET['subaction'])){
				if($_GET['subaction']=="viewEventWise"){
					return viewEventWise();
				}
				if($_GET['subaction']=="viewProcurementWise"){
					return viewProcurementWise();
				}
			}
			else{
				return selectSubactionOcTeam();
			}
		}
		
public function actionQa(){
			global $urlRequestRoot,$sourceFolder,$templateFolder,$cmsFolder,$moduleFolder;
			$moduleComponentId=$this->moduleComponentId;
			$userId=$this->userId;
			require_once("$sourceFolder/$moduleFolder/events/events_common.php");
			require_once("$sourceFolder/$moduleFolder/events/events_forms.php");
			require_once("$sourceFolder/$moduleFolder/events/events.config.php");
			require_once($sourceFolder."/".$moduleFolder."/qaos1/excel.php");
			require_once($sourceFolder."/upload.lib.php");
			if(isset($_GET['subaction'])){
				if($_GET['subaction'] == "viewEvent"){
					if(isset($_POST['eventId'])){
						$eventId=escape($_POST['eventId']);
						return displayEventOptions('qa',$moduleComponentId,$eventId);
					}
                }
                 else if($_GET['subaction']=='getParticipant')
                    {
                                        
                   if(isset($_POST['eventId'])){
                    $eventId = escape($_POST['eventId']);
                    $userId=escape($_POST['userId']);
                      $eventAdd="<p>SEARCH RESULTS </p>";
                 if($userId[0]=='F' || $userId[1]=='f'){
                     $bookletId=$userId;
                     $userId=getUserIdFromBookletId($moduleComponentId,$userId);  
                 }
                 else{
                     if($userId > 200000 && $userId < 30000)
                         $userId -= 180000;
                     $bookletId = getBookletIdFromUserId($userId,$moduleComponentId);
                }
  $eventAdd.=searchParticipant('qa',$pmcId,1);
    $eventAdd.="<h2>Profile</h2>";
    $eventAdd.=returnUserProfileDetails($userId);
    $eventAdd.="<h2>PR & Hospi Details</h2>";
    $eventAdd.="<table><tr style='font-size:10px'>"; 
    $eventAdd.="<th>PR CHECK IN TIME</th>";
    $eventAdd.="<th>PR CHECK OUT TIME</th>";
    $eventAdd.="<th>AMOUNT RECIEVED AT PR</th>";
    $eventAdd.="<th>AMOUNT REFUNDED AT PR</th>";
    $eventAdd.="<th>HOSPI CHECK IN TIME</th>";
    $eventAdd.="<th>HOSPI CHECK OUT TIME</th>";
    $eventAdd.="<th>AMOUNT RECIEVED AT HOSPI</th>";
    $eventAdd.="<th>AMOUNT REFUNDED AT HOSPI</th>";
    $eventAdd.="<th>No. of days of stay</th></tr>";
 $prStatus="SELECT * FROM `prhospi_pr_status` WHERE `user_id`='{$userId}' and `page_moduleComponentId`={$moduleComponentId}";
$prQuery=mysql_query($prStatus) or displayerror(mysql_error());
$prRows=mysql_fetch_array($prQuery);
$checkintime_pr=$prRows['hospi_checkin_time'];
$checkoutime_pr=$prRows['hospi_checkpout_time'];
$amount_recieved_pr=$prRows['amount_recieved'];
$amount_refunded_pr=$prRows['amount_refunded'];
 $HospiStatus="SELECT * FROM `prhospi_accomodation_status` WHERE `user_id`='{$userId}' and `page_modulecomponentid`={$moduleComponentId}";
$HospiQuery=mysql_query($HospiStatus) or displayerror(mysql_error());
$HospiRows=mysql_fetch_array($HospiQuery);
$checkintime_hospi=$HospiRows['hospi_actual_checkin'];
$checkoutime_hospi=$HospiRows['hospi_actual_checkout'];
$amount_recieved_hospi=$HospiRows['hospi_cash_recieved'];
$amount_refunded_hospi=$HospiRows['hospi_cash_refunded'];
$no_of_days=$HospiRows['no_of_days'];
$hospi_room_id=$HospiRows['hospi_room_id'];
$eventAdd.="<td>".$checkintime_pr."</td>";
$eventAdd.="<td>".$checkoutime_pr."</td>";
$eventAdd.="<td>".$amount_recieved_pr."</td>";
$eventAdd.="<td>".$amount_refunded_pr."</td>";
$eventAdd.="<td>".$checkintime_hospi."</td>";
$eventAdd.="<td>".$checkoutime_hospi."</td>";
$eventAdd.="<td>".$amount_recieved_hospi."</td>";
$eventAdd.="<td>".$amount_refunded_hospi."</td>";
$eventAdd.="<td>{$no_of_days}</td>";
    $eventAdd.="</tr></table>";
    $hostelQuery = "SELECT * FROM `prhospi_hostel` WHERE `hospi_room_id`={$hospi_room_id} and `page_modulecomponentid`={$moduleComponentId}";
    $hostelQueryResult = mysql_query($hostelQuery) or displayerror(mysql_error());
    $hostelDetails=mysql_fetch_array($hostelQueryResult);
    $eventAdd.="<h2>Hostel Details</h2>";
    $eventAdd.="<table>";
    $eventAdd.="<th>HOSTEL</th>";
    $eventAdd.="<th>FLOOR</th>";
    $eventAdd.="<th>ROOM</th>";
    $eventAdd.="<tr>";
    $eventAdd.="<td>{$hostelDetails['hospi_hostel_name']}</td>";
    $eventAdd.="<td>{$hostelDetails['hospi_floor']}</td>";
    $eventAdd.="<td>{$hostelDetails['hospi_room_no']}</td>";
    $eventAdd.="</tr>";
    $eventAdd.="</table>";
    $eventAdd.="<h2>Event Details</h2>";
    
    $eventAdd.="<table><tr>";
    $eventAdd.="<th>EVENT</th>";
    $eventAdd.="<th>EVENT RANK</th>";
    $eventAdd.="<th>PRIZE MONEY</th>";

$userDetails = "SELECT * FROM `events_result`  WHERE `user_id`='{$userId}' and `page_moduleComponentId`={$moduleComponentId}";
    $userDetailsRows= mysql_query($userDetails) or displayerror(mysql_error());
while($row=mysql_fetch_array($userDetailsRows))
    {
    $eventAdd.="<tr>";
    $eventDetails="SELECT * FROM `events_details` WHERE `event_id`='{$row['event_id']}'";
    $eventResults=mysql_query($eventDetails)  or displayerror(mysql_error());
    $eventsResults=mysql_fetch_array($eventResults);
  $eventAdd.="<td>".$eventsResults['event_name']."</td>";
    $eventAdd.="<td>".$row['user_rank']."</td>";
    $userPrizeDetails = "SELECT * FROM `events_participants` WHERE `user_pid`='{$userId}' ";
    $userPrizeQuery= mysql_query($userPrizeDetails) or displayerror(mysql_error());
   $userPrizeRows=mysql_fetch_array($userPrizeQuery);
    $eventAdd.="<td>".$userPrizeRows['prize_money']."</td>";
 //   $eventAdd.="<td>".$eventsResults['
    


   }

    $eventAdd.="</table>";


                   return $eventAdd;
                    }
                     }

                else if($_GET['subaction']=='addParticipant')
                    {
                    if(isset($_POST['eventId'])){
                    $eventId = escape($_POST['eventId']);
                    $fileUploadableField=getFileUploadField('fileUploadFieldPart',"events");
                    $eventAdd=<<<FORM
                        <p>Upload Event Excel File:</p>
           <form action="./+qa&subaction=viewEvent" method="post" enctype='multipart/form-data'>
           $fileUploadableField
           <input type='hidden' name='eventId' value='{$eventId}'>
           <input type='submit' name='submit' value='Upload'>
           </form>
FORM;
                return $eventAdd;
                    }
				}
				else if($_GET['subaction'] == "editParticipant"){
					$editFormId=escape($_POST['formId']);
					$editUserId=escape($_POST['userId']);
					$rowValue = escape($_POST['rowValue']);
					$teamId = escape($_POST['teamId']);
					$rowId = escape($_POST['rowId']);
					$eventId = escape($_POST['eventId']);
					if(!empty($userId)){
						//return $rowId;
						echo editParticipant('qa',$moduleComponentId,$eventId,$editFormId,$editUserId,$teamId,$rowValue,$rowId);
						die();
					}
				}
				else if($_GET['subaction'] == "lockEvent"){
					$eventId = trim(escape($_POST['eventId']));
					if(!empty($eventId)){
						return lockEvent($moduleComponentId,$eventId);
					}
				}
				else if($_GET['subaction'] == "downloadExcel"){
					//$eventId = escape($_POST['eventId']);
					//error_log($eventId);
					//getUserDetailsTable($moduleComponentId,$eventId);

					getUserDetailsTable('qa',$moduleComponentId,escape($_GET['event_id']));
				}
				/*else if($_GET['subaction'] == "getDetails"){
					if(isset($_POST['eventId'])){
						$eventId = escape($_POST['eventId']);
						return 
					}
				}*/
			}
			else
				return displayQa($moduleComponentId);
		}

		public function actionQahead(){
			global $urlRequestRoot,$sourceFolder,$templateFolder,$cmsFolder,$moduleFolder;
			$moduleComponentId=$this->moduleComponentId;
			$userId=$this->userId;
			require_once("$sourceFolder/$moduleFolder/events/events_common.php");
			require_once("$sourceFolder/$moduleFolder/events/events_forms.php");
			require_once("$sourceFolder/$moduleFolder/events/events.config.php");
			require_once($sourceFolder."/".$moduleFolder."/qaos1/excel.php");
			require_once($sourceFolder."/upload.lib.php");
			if(isset($_GET['subaction'])){
				if($_GET['subaction'] == "viewEvent"){
					if(isset($_POST['eventId'])){
						$eventId=escape($_POST['eventId']);
						return displayEventOptions('qahead',$moduleComponentId,$eventId);
					}
               
				}
               else if($_GET['subaction']=='getParticipant')
		{
	  				
                                        
                   if(isset($_POST['eventId'])){
                    $eventId = escape($_POST['eventId']);
                    $userId=escape($_POST['userId']);
                      $eventAdd="<p>SEARCH RESULTS </p>";
                 if($userId[0]=='F' || $userId[1]=='f'){
                     $bookletId=$userId;
                     $userId=getUserIdFromBookletId($moduleComponentId,$userId);  
                 }
                 else{
                     if($userId > 200000 && $userId < 30000)
                         $userId -= 180000;
                     $bookletId = getBookletIdFromUserId($userId,$moduleComponentId);
                }
 $eventAdd.=searchParticipant('qahead',$pmcId,1);
    $eventAdd.="<h2>Profile</h2>";
    $eventAdd.=returnUserProfileDetails($userId);
    $eventAdd.="<h2>PR & Hospi Details</h2>";
    $eventAdd.="<table><tr style='font-size:10px'>"; 
    $eventAdd.="<th>PR CHECK IN TIME</th>";
    $eventAdd.="<th>PR CHECK OUT TIME</th>";
    $eventAdd.="<th>AMOUNT RECIEVED AT PR</th>";
    $eventAdd.="<th>AMOUNT REFUNDED AT PR</th>";
    $eventAdd.="<th>HOSPI CHECK IN TIME</th>";
    $eventAdd.="<th>HOSPI CHECK OUT TIME</th>";
    $eventAdd.="<th>AMOUNT RECIEVED AT HOSPI</th>";
    $eventAdd.="<th>AMOUNT REFUNDED AT HOSPI</th>";
    $eventAdd.="<th>No. of days of stay</th></tr>";
 $prStatus="SELECT * FROM `prhospi_pr_status` WHERE `user_id`='{$userId}' and `page_moduleComponentId`={$moduleComponentId}";
$prQuery=mysql_query($prStatus) or displayerror(mysql_error());
$prRows=mysql_fetch_array($prQuery);
$checkintime_pr=$prRows['hospi_checkin_time'];
$checkoutime_pr=$prRows['hospi_checkpout_time'];
$amount_recieved_pr=$prRows['amount_recieved'];
$amount_refunded_pr=$prRows['amount_refunded'];
 $HospiStatus="SELECT * FROM `prhospi_accomodation_status` WHERE `user_id`='{$userId}' and `page_modulecomponentid`={$moduleComponentId}";
$HospiQuery=mysql_query($HospiStatus) or displayerror(mysql_error());
$HospiRows=mysql_fetch_array($HospiQuery);
$checkintime_hospi=$HospiRows['hospi_actual_checkin'];
$checkoutime_hospi=$HospiRows['hospi_actual_checkout'];
$amount_recieved_hospi=$HospiRows['hospi_cash_recieved'];
$amount_refunded_hospi=$HospiRows['hospi_cash_refunded'];
$no_of_days=$HospiRows['no_of_days'];
$hospi_room_id=$HospiRows['hospi_room_id'];
$eventAdd.="<td>".$checkintime_pr."</td>";
$eventAdd.="<td>".$checkoutime_pr."</td>";
$eventAdd.="<td>".$amount_recieved_pr."</td>";
$eventAdd.="<td>".$amount_refunded_pr."</td>";
$eventAdd.="<td>".$checkintime_hospi."</td>";
$eventAdd.="<td>".$checkoutime_hospi."</td>";
$eventAdd.="<td>".$amount_recieved_hospi."</td>";
$eventAdd.="<td>".$amount_refunded_hospi."</td>";
$eventAdd.="<td>{$no_of_days}</td>";
    $eventAdd.="</tr></table>";
    $hostelQuery = "SELECT * FROM `prhospi_hostel` WHERE `hospi_room_id`={$hospi_room_id} and `page_modulecomponentid`={$moduleComponentId}";
    $hostelQueryResult = mysql_query($hostelQuery) or displayerror(mysql_error());
    $hostelDetails=mysql_fetch_array($hostelQueryResult);
    $eventAdd.="<h2>Hostel Details</h2>";
    $eventAdd.="<table>";
    $eventAdd.="<th>HOSTEL</th>";
    $eventAdd.="<th>FLOOR</th>";
    $eventAdd.="<th>ROOM</th>";
    $eventAdd.="<tr>";
    $eventAdd.="<td>{$hostelDetails['hospi_hostel_name']}</td>";
    $eventAdd.="<td>{$hostelDetails['hospi_floor']}</td>";
    $eventAdd.="<td>{$hostelDetails['hospi_room_no']}</td>";
    $eventAdd.="</tr>";
    $eventAdd.="</table>";
    $eventAdd.="<h2>Event Details</h2>";
    

 $eventAdd.="<table><tr>";
    $eventAdd.="<th>EVENT</th>";
    $eventAdd.="<th>EVENT RANK</th>";
    $eventAdd.="<th>PRIZE MONEY</th>";
   $eventAdd.="<th>TEAMMATES </th>";

$userDetails = "SELECT * FROM `events_result`  WHERE `user_id`='{$userId}' and `page_moduleComponentId`={$moduleComponentId}";
    $userDetailsRows= mysql_query($userDetails) or displayerror(mysql_error());
 while($row=mysql_fetch_array($userDetailsRows))

    {
    $eventAdd.="<tr>";
    $eventDetails="SELECT * FROM `events_details` WHERE `event_id`='{$row['event_id']}'";
    $eventResults=mysql_query($eventDetails)  or displayerror(mysql_error());
    $eventsResults=mysql_fetch_array($eventResults);
  $eventAdd.="<td>".$eventsResults['event_name']."</td>";
    $eventAdd.="<td>".$row['user_rank']."</td>";
    $userPrizeDetails = "SELECT * FROM `events_participants` WHERE `user_pid`='{$userId}' ";
    $userPrizeQuery= mysql_query($userPrizeDetails) or displayerror(mysql_error());
   $userPrizeRows=mysql_fetch_array($userPrizeQuery);
    $eventAdd.="<td>".$userPrizeRows['prize_money']."</td>";

    $teamMateDetails="SELECT * FROM `events_participants` WHERE `user_pid`='{$userId}' and `event_id` ='{$row['event_id']}'";
   displayerror($teamMateDetails);
   $teamMateQuery=mysql_query($teamMateDetails) or displayerror(mysql_error);
   $teamMateDetails=mysql_fetch_assoc($teamMateQuery);
  $teamMates=$teamMateDetails['user_team_id'];
 $teamMateDetails="SELECT * FROM `events_participants` WHERE `user_team_id`=$teamMates  and `event_id` ='{$row['event_id']}'";
 $teamMateQuery=mysql_query($teamMateDetails) or displayerror(mysql_error);
 $eventAdd.="<td>";
while($newRow=mysql_fetch_array($teamMateQuery))
 {
 $eventAdd.=$newRow['user_pid']."  ";
 }  

  $eventAdd.="</td>";

   }




    $eventAdd.="</table>";



























                   return $eventAdd;
                    }
	}	
                    else if($_GET['subaction']=='addParticipant')
                    {
                    if(isset($_POST['eventId'])){
                    $eventId = escape($_POST['eventId']);
                    $fileUploadableField=getFileUploadField('fileUploadFieldPart',"events");
                    $eventAdd=<<<FORM
                        <p>Upload Event Excel File:</p>
           <form action="./+qahead&subaction=viewEvent" method="post" enctype='multipart/form-data'>
           $fileUploadableField
           <input type='hidden' name='eventId' value='{$eventId}'>
           <input type='submit' name='submit' value='Upload'>
           </form>
FORM;
                return $eventAdd;
                }
                    }
				else if($_GET['subaction'] == "editParticipant"){

					$editFormId=escape($_POST['formId']);
					$editUserId=escape($_POST['userId']);
					$teamId = escape($_POST['teamId']);
					$rowValue = escape($_POST['rowValue']);
					$rowId = escape($_POST['rowId']);
					$eventId = escape($_POST['eventId']);
					if(!empty($userId)){
						//return $rowId;
						echo editParticipant('qahead',$moduleComponentId,$eventId,$editFormId,$editUserId,$teamId,$rowValue,$rowId);
						die();
					}
				}
				else if($_GET['subaction'] == "lockEvent"){
					$eventId = trim(escape($_POST['eventId']));
					if(!empty($eventId)){
						return lockEvent($moduleComponentId,$eventId);
					}
				}
				else if($_GET['subaction'] == 'unlockEvent'){
					$eventId = trim(escape($_POST['eventId']));
					if(!empty($eventId)){
						return unlockEvent($moduleComponentId,$eventId);
					}
				}
				else if($_GET['subaction'] == "downloadExcel"){
					//$eventId = escape($_POST['eventId']);
					//error_log($eventId);
					//getUserDetailsTable($moduleComponentId,$eventId);
				  getUserDetailsTable('qahead',$moduleComponentId,escape($_GET['event_id']));
				}
				else if($_GET['subaction'] == "deleteParticipant"){
				  $userId = escape($_POST['userId']);
				  $eventId = escape($_POST['eventId']);
				  return deleteParticipant($moduleComponentId,$userId,$eventId);
				}
				else if($_GET['subaction'] == "deleteEvent"){
				  $eventId = escape($_POST['eventId']);
				  return deleteEventQa($moduleComponentId,$eventId);
				}
				/*else if($_GET['subaction'] == "getDetails"){
					if(isset($_POST['eventId'])){
						$eventId = escape($_POST['eventId']);
						return 
					}
				}*/
			}
			else
			  //return displayQa($moduleComponentId);
			  				return qaHeadOptions($moduleComponentId);
		}


		public function actionPr(){
			global $urlRequestRoot,$sourceFolder,$templateFolder,$cmsFolder,$moduleFolder;
			$moduleComponentId=$this->moduleComponentId;
			$userId=$this->userId;
			require_once("$sourceFolder/$moduleFolder/events/events_common.php");
			require_once("$sourceFolder/$moduleFolder/events/events_forms.php");
			require_once("$sourceFolder/$moduleFolder/events/events.config.php");
			require_once($sourceFolder."/".$moduleFolder."/qaos1/excel.php");
			
			if(isset($_GET['subaction'])){
				if($_GET['subaction'] == "viewEvent"){
					$eventId = trim(escape($_POST['eventId']));
					if(!empty($eventId)){
						return viewEventResult('pr',$moduleComponentId,$eventId);
					}
				}
				else if($_GET['subaction'] == "printCerti"){
					if(isset($_POST['eventId'])){
						$eventId = escape($_POST['eventId']);
						$action = 'event';
					}
					else if(isset($_POST['workshopId'])){
						$eventId = escape($_POST['workshopId']);
						$action = 'workshop';
					}
					return printCertificates($action,$moduleComponentId,$eventId);
				}
				else if($_GET['subaction'] == "downloadExcel"){
					//$eventId = escape($_POST['eventId']);
					//error_log($eventId);
					//getUserDetailsTable($moduleComponentId,$eventId);
					return getUserDetailsTable('pr',$moduleComponentId,escape($_GET['event_id']));
				}
				else if($_GET['subaction'] == "printIndividualCerti"){
					if(isset($_POST['eventId'])){
						$action = 'event';
						$eventId = escape($_POST['eventId']);
					}
					else if(isset($_POST['workshopId'])){
						$eventId = $_POST['workshopId'];
						$action = 'workshop';
					}
					$userId = escape($_POST['userId']);
					//error_log($eventId." ".$userId);
					return printIndividualCerti('pr',$action,$moduleComponentId,$userId,$eventId);
				}
				else if($_GET['subaction'] == "userDetailForm"){
					return searchByUserId('pr',$moduleComponentId);
				}
				else if($_GET['subaction'] == "userEventDetails"){
					$userBookletId = escape($_POST['userId']);
					return getUserDetails('pr',$moduleComponentId,$userBookletId);
				}
				else if($_GET['subaction'] == "printUserCerti"){
					$userId = escape($_POST['userId']);
					printUserCerti($moduleComponentId,$userId);
				}
				else if($_GET['subaction'] == "viewEventOptions"){
					return displayPR('pr',$moduleComponentId);
				}
			}
			else{
				return prMain('pr',$moduleComponentId);
				//return displayPR('pr',$moduleComponentId);
			}
		}

		public function actionPrhead(){
			global $urlRequestRoot,$sourceFolder,$templateFolder,$cmsFolder,$moduleFolder;
			$moduleComponentId=$this->moduleComponentId;
			$userId=$this->userId;
			require_once("$sourceFolder/$moduleFolder/events/events_common.php");
			require_once("$sourceFolder/$moduleFolder/events/events_forms.php");
			require_once("$sourceFolder/$moduleFolder/events/events.config.php");
			require_once($sourceFolder."/".$moduleFolder."/qaos1/excel.php");
			require_once($sourceFolder."/upload.lib.php");
			if(isset($_GET['subaction'])){
				if($_GET['subaction'] == 'viewEventList'){
					return displayPr('prhead',$moduleComponentId);
				}
				if($_GET['subaction'] == "viewEvent"){
					$eventId = trim(escape($_POST['eventId']));
					if(!empty($eventId)){
						return viewEventResult('prhead',$moduleComponentId,$eventId);
					}
				}
				else if($_GET['subaction'] == "downloadExcel"){
					//$eventId = escape($_POST['eventId']);
					//error_log($eventId);
					//getUserDetailsTable($moduleComponentId,$eventId);
					return getUserDetailsTable('prhead',$moduleComponentId,escape($_GET['event_id']));
				}
				else if($_GET['subaction'] == 'unlockEvent'){
					$eventId = trim(escape($_POST['eventId']));
					if(!empty($eventId)){
						return unlockEvent($moduleComponentId,$eventId);
					}
				}
				else if($_GET['subaction'] == 'viewWorkshopList'){
					return getWorkshopsList($moduleComponentId);
				}
				else if($_GET['subaction'] == 'viewWorkshopDetails'){
					$workshopId = escape($_POST['workshopId']);
					return viewWorkshopDetails($workshopId,$moduleComponentId);
				}
				else if($_GET['subaction'] == "downloadExcel"){
					//$eventId = escape($_POST['eventId']);
					//error_log($eventId);
					//getUserDetailsTable($moduleComponentId,$eventId);
					return getWorkshopDetailsTable($moduleComponentId,escape($_GET['workshop_id']));
				}
				else if($_GET['subaction'] == "editParticipant"){
					$editFormId=escape($_POST['formId']);
					$editUserId=escape($_POST['userId']);
					$rowValue = escape($_POST['rowValue']);
					$teamId = escape($_POST['teamId']);
					$rowId = escape($_POST['rowId']);
					$workshopId = escape($_POST['eventId']);
					if(!empty($userId)){
						//return $rowId;
						echo editWorkshopParticipant('prhead',$moduleComponentId,$workshopId,$editFormId,$editUserId,$teamId,$rowValue,$rowId);
						die();
					}
				}
				else if($_GET['subaction'] == "printCerti"){
					if(isset($_POST['eventId'])){
						$eventAction = 'event';
						$eventId = trim(escape($_POST['eventId']));
					}
					else if(isset($_POST['workshopId'])){
						$eventAction = 'workshop';
						$eventId = trim(escape($_POST['workshopId']));
					}
					return printCertificates($eventAction,$moduleComponentId,$eventId);
				}
				else if($_GET['subaction'] == "printIndividualCerti"){
					if(isset($_POST['eventId'])){
						$action = 'event';
						$eventId = escape($_POST['eventId']);
					}
					else if(isset($_POST['workshopId'])){
					  if(isset($_POST['workshopId']))
					    //return "sadasd";
						$eventId = $_POST['workshopId'];
						$action = 'workshop';
					}
					$userId = escape($_POST['userId']);
					//error_log($eventId." ".$userId);
					return printIndividualCerti('prhead',$action,$moduleComponentId,$userId,$eventId);
				}
				else if($_GET['subaction'] == "userDetailForm"){
					return searchByUserId('prhead',$moduleComponentId);
				}
				else if($_GET['subaction'] == "userEventDetails"){
					$userBookletId = escape($_POST['userId']);
					return getUserDetails('prhead',$moduleComponentId,$userBookletId);
				}
				else if($_GET['subaction'] == "printUserCerti"){
					$userId = escape($_POST['userId']);
					printUserCerti($moduleComponentId,$userId);
				}
				else if($_GET['subaction'] == "viewEventOptions"){
					return getPrHeadOptions($moduleComponentId);
				}
				/*else if($_GET['subaction'] == "viewEvent"){
					$eventId = trim(escape($_POST['eventId']));
					if(!empty($eventId)){
						return viewEventResult($moduleComponentId,$eventId);
					}
				}
				else if($_GET['subaction'] == "printCerti"){
					$eventId = trim(escape($_POST['eventId']));
					return printCertificates($moduleComponentId,$eventId);
				}

				else if($_GET['subaction'] == 'unlockEvent'){
					$eventId = trim(escape($_POST['eventId']));
					if(!empty($eventId)){
						return unlockEvent($moduleComponentId,$eventId);
					}
				}*/
			}
			else
				return prMain('prhead',$moduleComponentId);
		}

		public static function getFileAccessPermission($pageId,$moduleComponentId,$userId, $fileName)
		{
			return getPermissions($userId, $pageId, "view");
		}
		public static function getUploadableFileProperties(&$fileTypesArray,&$maxFileSizeInBytes)
		{
			$fileTypesArray = array('jpg','jpeg','png','doc','pdf','gif','bmp','css','js','html','xml','ods','odt','oft','pps','ppt','t\
				ex','tiff','txt','chm','mp3','mp2','wave','wav','mpg','ogg','mpeg','wmv','wma','wmf','rm','avi','gzip','gz','rar','bmp','psd','bz2','tar','zip','swf','fla','flv','eps','xcf','xls','exe','7z');
			$maxFileSizeInBytes = 30*1024*1024;
		}
		public function deleteModule($moduleComponentId) {
			return true;
		}
		public function createModule($moduleComponentId) {
	///No initialization
		}
		public function copyModule($moduleComponentId, $newId) {
			return true;
		}
public function moduleAdmin(){
        return "This is the Article module administration page. Options coming up soon!!!";
    }
	}
