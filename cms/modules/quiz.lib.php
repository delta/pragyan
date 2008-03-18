<?php
/*
 * Created on Oct 17, 2007
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */

/**
 * First and foremost you ll have to decide the DB structure.
 * You'll have two (or more) types of quizzes.
 * We ll take 2 now - the simplest and the most complex of them.
 * You could have A - one set of tables suited for the complex tables, which,
 * many columns when not used, will also take care of the simple quiz
 * or B - different sets of tables for the simple quiz and the complex quiz.
 *
 * The disadvantage of B is that you will have to write deleteModule()
 * codes for each of the types of quiz. Also call all the
 * deleteModule codes while deleting. That is, clear all tables of that modulecomponent id.
 * When the guy deletes a quiz of type simple, run delete queries in the
 * table complex also.
 *
 * The advantage of B is that having a simple DB structure for the simple quiz
 * will enable you to complete the simple quiz quite fast, which is quite important,
 * given that the complex quiz is so complex, I am not sure we can even complete it.
 *
 * A or B ... decide first, then proceed. The decision is yours.
 *
 * Quiz Module
    * Actions : Editquiz, Givequiz, Viewresults (anything else?)
    * The will be some properties which will be common to all quizes (regardless of their type - simple or complex) - all these properties will get edited in the main editquiz page. One of these properties will be the quiz type : simple complex etc. Total time is not a common parameter. There might a different type quiz later which has limits other than "time".
    * This can be handled by page permissions, so no need for this --->(((One of the common properties for quizzes will be the ability to associate the quiz with a group.)))
    * What happens when the guy changes the quiz type? My suggestion :
          o A : Do nothing. If the quiz type is upgraded, all questions will just have some parameters missing which ll just make them act as if they contain 0 or null, so the quiz should work. If the quiz is downgraded, the extra fields are never read. So it doesn't matter. This is in case the DB structure is of type A.
          o B: DB structure of type B : Do nothing. This means the old quiz still exists, but will never be read.
    * There should be another link pointing to a subaction that enables adding of questions. (how this subaction looks, will be quiz type dependent)
 *
 * Simple quiz :
    * Objective questions and subjective questions.
    * Order of the questions decided by a rank parameter also settable in editquiz. (let the user put it in manually). If more than one questions have the same rank, let the order be whatever order mysql provides u the questions in ( i.e. let it depend on luck, or, don't waste time on this issue).
    * Givequiz will have all questions in one page.
    * total time to be specified in editquiz.
    * No of options given for objective questions may be variable. For the editquiz form, have a practical high number like 7 or 8. In the DB store it as separate entries. Keep this in mind while designing the db. Because no matter what delimiter we choose, it will get used up in a maths quiz.
    * Make sure that both questions and answers are TEX enabled. It will be needed heavily in maths quiz.
 *
 */


class quiz implements module, fileuploadable {
	private $userId;
	private $moduleComponentId;
	private $action;
	private $quizFolder;
	private $quizFolderUrl;
	private $iconsFolderUrl;

	private static $quizTypes =
				array('simple' => 'Simple Quiz', 'gre' => 'GRE Type Quiz');

	/// THE ORDER OF THE ENUM IN THE MYSQL TABLE IS IMPORTANT
	/// WHEN IT COMES TO SELECT STATEMENTS THAT CONTAIN AN ORDER BY CLAUSE
	private static $questionTypes =
				array('subjective' => 'Subjective', 'singleselectobjective' => 'Single Select Objective', 'multiselectobjective' => 'Multiselect Objective');


	public static function getFileAccessPermission($pageId, $moduleComponentId, $userId, $fileName) {
		return getPermissions($userId, $pageId, "view", "quiz");
	}

/*
	public static function getUploadableFileProperties(&$fileTypesArray,&$maxFileSizeInBytes) {
		$fileTypesArray = array('jpg', 'bmp', 'pdf', 'swf', 'flv', 'wma', 'wmv', 'gif');
		$maxFileSizeInBytes = 30*1024*1024;
	}
*/

	public function __construct() {
		global $sourceFolder;
		global $moduleFolder;
		global $templateFolder;
		global $urlRequestRoot;

		$this->quizFolder = "$sourceFolder/$moduleFolder/quiz";
		$this->quizFolderUrl = "$urlRequestRoot/$sourceFolder/$moduleFolder/quiz";
		$this->iconsFolderUrl = "$urlRequestRoot/$sourceFolder/$templateFolder/common/icons";
	}

	public function getHtml($userId, $moduleComponentId, $action) {
		$this->userId = $userId;
		$this->moduleComponentId = $moduleComponentId;
		$this->action = $action;

		if($action == "view")
			return $this->actionView();
		if($action == "edit")
			return $this->actionEdit();
		if($action == "correct")
			return $this->actionCorrect();
	}

	public function actionView() {
		if($this->userId == 0) {
			displayerror('You must log in before you can take the quiz. <a href="./+login">Click here</a> to login.');
			return '';
		}
		$moderator=getPermissions($this->userId, getPageIdFromModuleComponentId("quiz", $this->moduleComponentId), "edit");

		$quizDescQuery = 'SELECT *, (NOW() > `quiz_startdatetime`) AS `quiz_started`, (NOW() >= `quiz_enddatetime`) AS `quiz_ended` FROM `quiz_descriptions` WHERE `page_modulecomponentid` = ' . $this->moduleComponentId;
		$quizDescResult = mysql_query($quizDescQuery);

		if($quizDescRow = mysql_fetch_assoc($quizDescResult)) {



			if((!$quizDescRow['quiz_started'])&&(!$moderator)) {
				displayinfo('This quiz is scheduled to start on ' . $quizDescRow['quiz_startdatetime'] . '. Please check back later.');
			}
			else if(($quizDescRow['quiz_ended'])&&(!$moderator)) {
				displayerror('Sorry, this quiz has ended on ' . $quizDescRow['quiz_enddatetime']);
			}
			else {
				$quizViewFunctionName = 'get' . ucfirst($quizDescRow['quiz_quiztype']) . 'QuizView';

				include_once($this->quizFolder . '/quiz_view.php');
				if($moderator)
				{
					displayinfo("You are the moderator of this quiz and hence you can give this quiz again and again. Participants will be able to give this quiz only once");
					removeQuizAttemptData($this->moduleComponentId, $this->userId);
				}


				if(function_exists($quizViewFunctionName)) {
					global $sourceFolder;
					require_once($sourceFolder."/latexRender.php");
					$render = new render();
					return $render->transform($quizViewFunctionName($this->moduleComponentId, $this->userId, $quizDescRow));
				}
				else {
					displayerror('Error! Unknown quiz type.');
					return '';
				}
			}
		}
		else {
			displayerror('Error! The requested page was not found.');
			return '';
		}
	}

	public function actionEdit() {
			global $sourceFolder;
			include_once("$sourceFolder/upload.lib.php");
		submitFileUploadForm($this->moduleComponentId, 'quiz', $this->userId, 15*1024*1024);

		if(isset($_GET['subaction'])) {
			$subaction = $_GET['subaction'];

			if($subaction == 'typespecoptions') {
				include_once('quiz/quiz_edit.php');
				$quizType = getQuizType($this->moduleComponentId);
				if(isset($_POST['btnSubmitQuizEditForm'])) {
					$quizTypeFunction = 'submit' . $quizType . 'QuizEditForm';
					if(function_exists($quizTypeFunction)) {
						$quizTypeFunction($this->moduleComponentId);
					}
					else {
						displayerror('Invalid quiz type. Quizzes of the specified type have not been completely set up.');
					}
				}
				$quizTypeFunction = 'get' . $quizType . 'QuizEditForm';
				if(function_exists($quizTypeFunction)) {
					return $quizTypeFunction($this->moduleComponentId);
				}
				else {
					displayerror('Invalid quiz type. Quizzes of the specified type have not been completely set up.');
					return '';
				}
			}
			elseif (isset($_GET['questionid']) && ctype_digit($_GET['questionid'])) {
				if($subaction == 'editquestion') {
					include_once($this->quizFolder . '/question_edit.php');
					if(isset($_POST['btnSubmitQuestionEditForm'])) {
						submitQuestionEditForm($this->moduleComponentId, $_GET['questionid'], quiz::$questionTypes);
					}
					return getQuestionEditForm($this->moduleComponentId, $_GET['questionid'], quiz::$questionTypes);
				}
				elseif ($subaction == 'deletequestion') {
					include_once($this->quizFolder . '/quiz_edit.php');
					deleteQuestion($this->moduleComponentId, $_GET['questionid']);
				}
			}
			elseif ($subaction == 'addquestions') {
				include_once($this->quizFolder . '/question_edit.php');
				if(isset($_POST['btnAddQuestions'])) {
					submitQuestionAddForm($this->moduleComponentId);
				}
				return getQuestionAddForm($this->moduleComponentId, quiz::$questionTypes);
			}
		}
		elseif(isset($_POST['btnSubmitQuizEditForm'])) {
			include_once($this->quizFolder . '/quiz_edit.php');
			submitQuizEditForm($this->moduleComponentId, quiz::$quizTypes);
		}

		include_once($this->quizFolder . '/quiz_edit.php');
		return getQuizEditForm($this->moduleComponentId, quiz::$quizTypes);
	}

	public function actionCorrect() {
		if(isset($_GET['subaction']))
		{
			$subaction=$_GET['subaction'];
			if($subaction=='viewuserdetails')
			{
				$useremail=$_GET['useremail'];
				include_once($this->quizFolder . '/quiz_correct.php');
				return getUseranswers($this->moduleComponentId,$useremail);
			}
			elseif($subaction=='correctquestion')
			{
				include_once($this->quizFolder . '/quiz_correct.php');
				if(isset($_POST['btnSubmitQuestionCorrectForm'])) {
					$useremail=$_GET['useremail'];
					submitQuestionCorrectForm($this->moduleComponentId, $_GET['questionid'], $useremail);
					return getUseranswers($this->moduleComponentId,$useremail);
				}

				return getQuestionCorrectForm($this->moduleComponentId, $_GET['questionid'], $_GET['useremail']);
			}
			elseif($subaction=='viewuserids')
			{
				include_once($this->quizFolder . '/quiz_correct.php');
				return getUserIds($this->moduleComponentId);
			}
			elseif($subaction == 'deleteuserdetails')
			{
				include_once($this->quizFolder . '/quiz_view.php');
				include_once($this->quizFolder . '/quiz_correct.php');
				removeQuizAttemptData($this->moduleComponentId, getUserIdFromEmail($_GET['useremail']));
				return getUserIds($this->moduleComponentId);
			}
			elseif($subaction=='deletedata')
			{
				include_once($this->quizFolder . '/quiz_view.php');
				removeQuizAttemptData($this->moduleComponentId, getUserIdFromEmail($_GET[useremail]));
				include_once($this->quizFolder . '/quiz_correct.php');
				return getUserIds($this->moduleComponentId);
			}
		}
		else
		{
			include_once($this->quizFolder . '/quiz_correct.php');
			return getUserIds($this->moduleComponentId);
		}
		/**

		if(isset($_GET['subaction'])) {
			if($_GET['subaction'] == 'viewuseranswers') {
				if($_POST['btnSubmitCorrectedForm']) {
					save the data
				}

				show the correct form for that user
			}

		}


			<a href="./+correct&subaction=viewuseranswers&useremail=<email>"
		 return list of all the users who attempted the quiz

	*/
	}

	public function createModule(&$moduleComponentId) {
		$compIdQuery = 'SELECT MAX(`page_modulecomponentid`) FROM `quiz_descriptions`';
		if(!($compIdResult = mysql_query($compIdQuery))) {
			displayerror('Could not retrieve information from database.');
			return '';
		}
		$compIdRow = mysql_fetch_row($compIdResult);
		$newModuleComponentId = 1;
		if(!is_null($compIdRow[0]))
			$newModuleComponentId = $compIdRow[0] + 1;
		$insertQuery = "INSERT INTO `quiz_descriptions` (`page_modulecomponentid`) VALUES($newModuleComponentId)";
		if(!($insertResult = mysql_query($insertQuery))) {
			displayerror('Could not add new quiz.');
			return '';
		}
		if (mysql_affected_rows()) {
			$moduleComponentId = $newModuleComponentId;
// to add an entry in the quiz_weightmarks table for the default value of the
// weight positive answers and negative answers. postive=3 and negative=1
			$weightQuery = "INSERT INTO `quiz_weightmarks` (`page_modulecomponentid` ,`quiz_questionweight` ,`quiz_weightpositivemarks` ,`quiz_weightnegativemarks`) VALUES ($moduleComponentId, '1', '3.00', '1.00')";
			if(!($weightResult = mysql_query($weightQuery))){
				displayerror("The default weights for each question could not be updated.");
				return '';
			}
			else{
				displayinfo("The defaults weight were added successfully.");
				return true;
				}
		}
		return false;
	}

	public function deleteModule($moduleComponentId){
		$deleteQuery = "DELETE FROM `quiz_descriptions` WHERE `page_modulecomponentid` = $moduleComponentId";
		$result1 = mysql_query($deleteQuery);
		$deleteQuery = "DELETE FROM `quiz_objectiveoptions` WHERE `page_modulecomponentid` = $moduleComponentId";
		$result2 = mysql_query($deleteQuery);
		$deleteQuery = "DELETE FROM `quiz_questions` WHERE `page_modulecomponentid` = $moduleComponentId";
		$result3 = mysql_query($deleteQuery);
		$deleteQuery = "DELETE FROM `quiz_quizattemptdata` WHERE `page_modulecomponentid` = $moduleComponentId";
		$result4 = mysql_query($deleteQuery);
		$deleteQuery = "DELETE FROM `quiz_submittedanswers` WHERE `page_modulecomponentid` = $moduleComponentId";
		$result5 = mysql_query($deleteQuery);
		$deleteQuery = "DELETE FROM `quiz_weightmarks` WHERE `page_modulecomponentid` = $moduleComponentId";
		$result6 = mysql_query($deleteQuery);

		if($result1 && $result2 && $result3 && $result4 && $result5 && $result6) {
			return true;
		}
		else {
			displayerror('There was some error removing the module.');
			return false;
		}
	}

	public function copyModule($moduleComponentId){

	}

}

?>