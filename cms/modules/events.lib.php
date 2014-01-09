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
		public function actionQahead(){
		global $urlRequestRoot,$sourceFolder,$templateFolder,$cmsFolder,$moduleFolder;
		$moduleComponentId=$this->moduleComponentId;
		$userId=$this->userId;
		require_once("$sourceFolder/$moduleFolder/events/events_common.php");
		require_once("$sourceFolder/$moduleFolder/events/events_forms.php");
		return qaHeadOptions($moduleComponentId);
	}

	public function actionQa(){
		global $urlRequestRoot,$sourceFolder,$templateFolder,$cmsFolder,$moduleFolder;
		$moduleComponentId=$this->moduleComponentId;
		$userId=$this->userId;
		require_once("$sourceFolder/$moduleFolder/events/events_common.php");
		require_once("$sourceFolder/$moduleFolder/events/events_forms.php");
		if(isset($_GET['subaction'])){
			if($_GET['subaction']=="viewEvent"){
				$eventId=trim(escape($_POST['eventId']));
				if(!empty($eventId)){
					return eventParticipants($moduleComponentId,$eventId);
				}
			}
			else if($_GET['subaction'] == "viewConfirmed"){
				$eventId=trim(escape($_POST['eventId']));
				if(!empty($eventId)){
					return viewConfirmedParticipants($moduleComponentId,$eventId);
				}
			}
			else if($_GET['subaction'] == "confirmParticipant"){
				$confirmUserid = trim(escape($_POST['userId']));
				$confirmEventId = trim(escape($_POST['eventId']));
				if(!empty($userId)){
					return confirmParticipation($moduleComponentId,$confirmEventId,$confirmUserid);
				}
			}
			else if($_GET['subaction'] == "editParticipant"){
				$editFormId=trim(escape($_POST['formId']));
				$editUserId=trim(escape($_POST['userId']));
				$rowValue = trim(escape($_POST['rowValue']));
				$rowId = trim(escape($_POST['rowId']));
				$evnetId = trim(escape($_POST['eventId']));
				if(!empty($userId)){
					echo editParticipant($moduleComponentId,$eventId,$editFormId,$editUserId,$rowValue,$rowId);
					die();
				}
			}
			else if($_GET['subaction'] == "editParticipantRank"){
				$eventId = trim(escape($_POST['eventId']));
				$userId = trim(escape($_POST['userId']));
				$newRank = trim(escape($_POST['newRank']));
				echo editParticipantRank($moduleComponentId,$eventId,$userId,$newRank);
				die();
			}
			else if($_GET['subaction'] == "lockEvent"){
				$eventId = trim(escape($_POST['eventId']));
				if(!empty($eventId)){
					return lockEvent($moduleComponentId,$eventId);
				}
			}
		}
		else{
			//return smartTableTest($moduleComponentId);
			return displayQA($moduleComponentId);
		}
	}
	public function actionPr(){
		global $urlRequestRoot,$sourceFolder,$templateFolder,$cmsFolder,$moduleFolder;
		$moduleComponentId=$this->moduleComponentId;
		$userId=$this->userId;
		require_once("$sourceFolder/$moduleFolder/events/events_common.php");
		require_once("$sourceFolder/$moduleFolder/events/events_forms.php");
		if(isset($_GET['subaction'])){
			if($_GET['subaction'] == "viewEvent"){
				$eventId = trim(escape($_POST['eventId']));
				if(!empty($eventId)){
					return viewEventResult($moduleComponentId,$eventId);
				}
			}
			else if($_GET['subaction'] == "printCerti"){
				$eventId = trim(escape($_POST['eventId']));
				return printCertificates($moduleComponentId,$eventId);
			}
		}
		else{
			return displayPR($moduleComponentId);
		}
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
