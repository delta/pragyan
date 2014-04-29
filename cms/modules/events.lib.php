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
					getUserDetailsTable($moduleComponentId,escape($_GET['event_id']));
				}
				/*else if($_GET['subaction'] == "getDetails"){
					if(isset($_POST['eventId'])){
						$eventId = escape($_POST['eventId']);
						return 
					}
				}*/
			}hitl
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
					getUserDetailsTable($moduleComponentId,escape($_GET['event_id']));
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
						$sction = 'workshop';
					}
					$userId = escape($_POST['userId']);
					//error_log($eventId." ".$userId);
					return printIndividualCerti('pr',$action,$moduleComponentId,$userId,$eventId);
				}
			}
			else{
				return displayPR('pr',$moduleComponentId);
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
						$eventId = $_POST['workshopId'];
						$sction = 'workshop';
					}
					$userId = escape($_POST['userId']);
					//error_log($eventId." ".$userId);
					return printIndividualCerti('prhead',$action,$moduleComponentId,$userId,$eventId);
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
				return getPrHeadOptions($moduleComponentId);
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

	}
