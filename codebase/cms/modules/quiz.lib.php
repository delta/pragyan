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
 * @copyright (c) 2010 Pragyan Team
 * @license http://www.gnu.org/licenses/ GNU Public License
 *
 * Quiz Module in Pragyan CMS makes quiz setup easier
 * This module generates the user-friendly edit and correct interface for quiz
 * 
 * Quiz Module corrects objective answers submitted by users,
 * and lets quiz admin set mark for subjective answers, and ranks users based on their marks.
 * 
 * Edit interface lets user to edit the quiz,
 * by editing its properties, adding sections, adding questions, etc.
 * Important Quiz Properties include:
 *     quiz opening time - Time after which users will be able to view the quiz
 *     quiz closing time - Time after which users wont be able to view or submit quiz answers
 *     quiz duration - Time duration of the quiz, within which user has to submit his/her answers
 * 
 * Quiz is organised as Sections and each section can contain any number of questions.
 * 
 * Book module uses seven table to stores its data:
 * quiz_descriptions: Every quiz page has a record in this table and it stored the quiz's properties
 *     page_modulecomponentid, quiz_title, quiz_headertext, quiz_submittext, quiz_quiztype, quiz_testduration,
 *     quiz_questionspertest, quiz_questionsperpage, quiz_timeperpage, quiz_,quiz_enddatetime,
 *     quiz_allowsectionrandomaccess, quiz_mixsections, quiz_showquiztimer, quiz_showpagetimer
 * 
 * quiz_sections: Every section has a row in this table, each record is uniquely identified with page_modulecomponentid, quiz_sectionid
 *     page_modulecomponentid, quiz_sectionid, quiz_sectiontitle, quiz_sectionssocount, quiz_sectionmsocount,
 *     quiz_sectionsubjectivecount, quiz_sectiontimelimit, quiz_sectionquestionshuffled, quiz_sectionrank, quiz_sectionshowlimit
 * 
 * quiz_questions: Every question is a record in this table, each record is identified with
 *                 (page_modulecomponentid, quiz_sectionid, quiz_questionid)
 *     page_modulecomponentid, quiz_sectionid, quiz_questionid, quiz_question, quiz_questiontype, quiz_questionrank
 *     quiz_questionweight, quiz_answermaxlength, quiz_rightanswer
 * 
 * quiz_objectiveoptions: Options for objective questions are stored here, identified by
 *                        (page_modulecomponentid, quiz_sectionid, quiz_questionid, quiz_optionid)
 *     page_modulecomponentid, quiz_sectionid, quiz_questionid, quiz_optionid, quiz_optiontext, quiz_optionrank
 * 
 * quiz_weightmarks: Mark for each question is determined from this table, weight-mark association is specific to a quiz,
 *                   So uniquely identified by (page_modulecomponentid, question_weight)
 *     page_modulecomponentid, question_weight, question_positivemarks, question_negativemarks
 * 
 * quiz_userattempts: Record of each user attempting each section, identified by
 *                    (page_modulecomponentid, quiz_sectionid, quiz_userid)
 *     page_modulecomponentid, quiz_sectionid, user_id, quiz_attemptstarttime, quiz_submissiontime, quiz_marksallotted
 * 
 * quiz_answersubmissions: A Record of each question submitted for each quiz for each user, identified by
 *                         (page_modulecomponentid, quiz_section, quiz_questionid, quiz_userid)
 *     page_modulecomponentid, quiz_sectionid, quiz_questionid, user_id, quiz_questionrank, quiz_submittedanswer,
 *     quiz_questionviewtime, quiz_answersubmittime, quiz_marksallotted
 */


/**
 * Including parts of quiz module
 */
require_once('quiz/quizedit.php');
require_once('quiz/quizview.php');
require_once('quiz/quizcorrect.php');
require_once('quiz/iquiz.php');

$quizTypes = getQuizTypes();
for ($i = 0; $i < count($quizTypes); ++$i)
	require_once('quiz/' . $quizTypes[$i] . 'quiz.php');

class quiz implements module {
	private $moduleComponentId;
	private $userId;
	private $action;

	/**
	 * function getHtml:
	 * Gateway through which CMS interacts with module
	 * This function will be called from getContent function of cms/content.lib.php
	 */
	public function getHtml($userId, $moduleComponentId, $action) {
		$this->userId = $userId;
		$this->moduleComponentId = $moduleComponentId;
		$this->action = $action;

		switch ($action) {
			case 'view':
				return $this->actionView();
			case 'edit':
				return $this->actionEdit();
			case 'correct':
				return $this->actionCorrect();
		}
	}
	
	/**
	 * function isValidId:
	 * Checks if input parameter is a positive integer
	 */
	private function isValidId($id) {
		return isset($id) && is_numeric($id) && $id > 0;
	}
	
	/**
	 * function actionView:
	 * Takes care of rendering quiz to users
	 * Makes preliminary checks and calls getQuizPage function in ./quiz/iquiz.php
	 */
	public function actionView() {
		$quizOpen = checkQuizOpen($this->moduleComponentId);
		if ($quizOpen != 0) {
			displayerror($quizOpen < 0 ? 'This quiz has not opened yet. Please check back later.' : 'This quiz has expired.');
			return '';
		}

		if (!checkQuizSetup($this->moduleComponentId)) {
			displayerror('This quiz has not been properly set up with the required number of questions. The quiz module cannot continue.');
			return '';
		}

		$quizRow = getQuizRow($this->moduleComponentId);
		$quizType = ucfirst($quizRow['quiz_quiztype']) . 'Quiz';
		
		$quizObject = new $quizType($this->moduleComponentId);
		if (!($quizObject instanceof IQuiz)) {
			displayerror('Error. This type of quiz has not been implemented correctly.');
			return '';
		}

		if (checkUserFirstAttempt($this->moduleComponentId, $this->userId)) {
			// user is attempting for the first time
			$quizObject->initQuiz($this->userId);
		}

		if (isset($_GET['subaction']) && $_GET['subaction'] == 'keepalive') {
			echo "Ok";
			exit();
		}

		// Quiz is initialized. Call getQuizPage.
		return $quizObject->getQuizPage($this->userId);
	}
	
	/**
	 * function actionEdit:
	 * processes subaction and calls getQuizEditForm function which renders edit interface
	 */
	public function actionEdit() {
		// dataSource: the $dataSource argument to get*Form() functions specifies where to persist data for the form from.
		// if a submit was in progress, and the submit was successful, we set dataSource to db.
		// else, we set dataSource to POST, because we need to present the user's entered values, rather than existing values
		// so that he/she may make changes and submit again, with least hassle.

		if (isset($_GET['subaction'])) {
			switch ($_GET['subaction']) {
				case 'addsections':
					if (!$this->isValidId($_POST['txtSectionCount'])) {
						displayerror('Error. No count specified.');
					}
					else {
						$count = escape($_POST['txtSectionCount']);
						if (addSections($this->moduleComponentId, $count) !== false)
							displayinfo('Section(s) added successfully.');
					}
				break;

				case 'editsection':
					$dataSource = 'db';
					if (!$this->isValidId($_GET['sectionid'])) {
						displayerror('Error. Invalid section id specified.');
					}
					elseif (isset($_POST['btnSubmit'])) {
						$dataSource = 'POST';
						if (submitSectionEditForm($this->moduleComponentId, intval($_GET['sectionid']))) {
							displayinfo('Section properties saved successfully.');
							$dataSource = 'db';
						}
					}
					return getSectionEditForm($this->moduleComponentId, intval($_GET['sectionid']), $dataSource);
				break;

				case 'deletesection':
					if (!$this->isValidId($_POST['hdnSectionId'])) {
						displayerror('Error. Invalid section id specified.');
					}
					elseif (deleteSection($this->moduleComponentId, intval($_POST['hdnSectionId']))) {
						displayinfo('The specified section was successfully deleted.');
					}
				break;

				case 'movesection':
					if (!$this->isValidId($_GET['sectionid'])) {
						displayerror('Error. Invalid section id specified.');
					}
					elseif (!isset($_GET['direction']) || ($_GET['direction'] != 'up' && $_GET['direction'] != 'down')) {
						displayerror('Error. No or invalid direction specified. Could not move section.');
					}
					elseif (moveSection($this->moduleComponentId, intval($_GET['sectionid']))) {
						displayinfo('The specified section was successfully moved.');
					}
				break;

				case 'addquestions':
					if (!$this->isValidId($_GET['sectionid'])) {
						displayerror('Error. No or invalid section id specified. Could not add question.');
					}
					elseif (!$this->isValidId($_POST['txtQuestionCount'])) {
						displayerror('Error. No or invalid count specified. Could not add question.');
					}
					else {
						$count = intval($_POST['txtQuestionCount']);
						$insertIds = addQuestions($this->moduleComponentId, intval($_GET['sectionid']), $count);
						if ($insertIds !== false)
							displayinfo('New question(s) added successfully.');
					}
				break;

				case 'editquestion':
					$dataSource = 'db';
					if (!$this->isValidId($_GET['sectionid']) || !$this->isValidId($_GET['questionid'])) {
						displayerror('Error. Invalid section or question specified.');
					}
					elseif (isset($_POST['btnSubmit'])) {
						$dataSource = 'POST';
						if (submitQuestionEditForm($this->moduleComponentId, intval($_GET['sectionid']), intval($_GET['questionid']))) {
							displayinfo('Question properties saved successfully.');
							$dataSource = 'db';
						}
					}
					return getQuestionEditForm($this->moduleComponentId, intval($_GET['sectionid']), intval($_GET['questionid']), $dataSource);
				break;

				case 'deletequestion':
					if (!$this->isValidId($_POST['hdnSectionId']) || !$this->isValidId($_POST['hdnQuestionId'])) {
						displayerror('Error. Invalid section or question specified.');
					}
					elseif (deleteQuestion($this->moduleComponentId, intval($_POST['hdnSectionId']), intval($_POST['hdnQuestionId']))) {
						displayinfo('Question successfully deleted.');
					}
				break;

				case 'movequestion':
					if (!$this->isValidId($_GET['sectionid'])) {
						displayerror('Error. Invalid section id specified.');
					}
					elseif (!$this->isValidId($_GET['questionid'])) {
						displayerror('Error. Invalid question id specified.');
					}
					elseif (!isset($_GET['direction']) || ($_GET['direction'] != 'up' && $_GET['direction'] != 'down')) {
						displayerror('Error. No or invalid direction specified. Could not move section.');
					}
					elseif (moveQuestion($this->moduleComponentId, intval($_GET['sectionid']), intval($_GET['questionid']), $_GET['direction'])) {
						displayinfo('The specified question was successfully moved.');
					}
				break;
			}
		}

		if (isset($_POST['btnSetWeightMarks'])) {
			if(setWeightMark(intval($_POST['quizId']), intval($_POST['weight']), intval($_POST['pos']), intval($_POST['neg']))) {
				displayinfo('Weight - Marks saved.');
			} else {
				displayerror('Error in changing weight mark');
			}
		}
		$dataSource = 'db';
		if (isset($_POST['btnSubmit'])) {
			$dataSource = 'POST';
			if (submitQuizEditForm($this->moduleComponentId))
				$dataSource = 'db';
		}

		return getQuizEditForm($this->moduleComponentId, $dataSource);
	}
	
	/**
	 * function actionCorrect:
	 * handles all actions in Correct
	 * Corrects user submission and displays userList with their Marks
	 */
	public function actionCorrect() {

		if (isset($_POST['btnSetMark'])) {
			$quizid = escape($_POST['quizid']);
			$sectionid = escape($_POST['sectionid']);
			$questionid = escape($_POST['questionid']);
			$userid = escape($_POST['userid']);
			$mark = escape($_POST['mark']);
			$condition = "`page_modulecomponentid` = '$quizid' AND `quiz_sectionid` = '$sectionid' AND `quiz_questionid` = '$questionid' AND `user_id` = '$userid'";
			$result = mysql_query("SELECT `quiz_submittedanswer` FROM `quiz_answersubmissions` WHERE $condition");
			if($row = mysql_fetch_array($result)) {
				$result = mysql_fetch_array(mysql_query("SELECT `question_positivemarks`, `question_negativemarks` FROM `quiz_weightmarks` WHERE `page_modulecomponentid` = '$quizid' AND `question_weight` = (SELECT `quiz_questionweight` FROM `quiz_questions` WHERE `page_modulecomponentid` = '$quizid' AND `quiz_sectionid` = '$sectionid' AND `quiz_questionid` = '$questionid')"));
				if($_POST['mark'] > $result['question_positivemarks'] || $_POST['mark'] < -1 * $result['question_negativemarks'])
					displaywarning('Mark out of range for this question, so mark not set');
				else {
					mysql_query("UPDATE `quiz_answersubmissions` SET `quiz_marksallotted` = $mark WHERE $condition");
					updateSectionMarks($quizid);
					displayinfo('Mark set');
				}
			}
			else
				displayerror('Unable to set value');
		}

		if (isset($_GET['useremail'])) {
			$userId = getUserIdFromEmail($_GET['useremail']);
			if ($userId)
				return getQuizCorrectForm($this->moduleComponentId, $userId);
			else
				displayerror('Error. Could not find user.');
		}
		elseif (isset($_POST['btnDeleteUser']) && isset($_POST['hdnUserId']) && is_numeric($_POST['hdnUserId'])) {
			$quizObject = $this->getNewQuizObject();
			if ($quizObject !== false)
				$quizObject->deleteEntries(intval($_POST['hdnUserId']));
		}

		return getQuizUserListHtml($this->moduleComponentId);
	}

	/**
	 * function createModule:
	 * will be called when quiz module instance is created.
	 * A row will be inserted into quiz_descriptions
	 */
	public function createModule($moduleComponentId) {
		$insertQuery = "INSERT INTO `quiz_descriptions`(`page_modulecomponentid`,`quiz_title`, `quiz_headertext`, `quiz_submittext`, `quiz_quiztype`, `quiz_testduration`, `quiz_questionspertest`, `quiz_questionsperpage`, `quiz_timeperpage`, `quiz_allowsectionrandomaccess`, `quiz_mixsections`, `quiz_showquiztimer`, `quiz_showpagetimer`) VALUES" . "('{$moduleComponentId}','New Quiz', 'Quiz under construction', 'Quiz under construction', 'simple', '00:30', '20', '10', 0, 1, 0, 1, 0)"; 
		if (!mysql_query($insertQuery)) {
			displayerror('Database Error. Could not create quiz. ' . $insertQuery . ' ' . mysql_error());
		}
	}

	/**
	 * function copyModule:
	 * to be implemented
	 * has to copy everything related to this quiz instance to another instance
	 */
	public function copyModule($moduleComponentId,$newId) {
		return true;
	}

	/**
	 * function deleteModule:
	 * delete all quiz data related to this quiz instance
	 * will be called when safedit module instance is getting deleted.
	 */
	public function deleteModule($moduleComponentId) {
		return true;
	}

	/**
	 * function getNewQuizObject:
	 * returns a object of this quiztype
	 */
	private function getNewQuizObject() {
		$quizRow = getQuizRow($this->moduleComponentId);
		$quizType = $quizRow['quiz_quiztype'];
		$quizObjectType = ucfirst($quizType) . 'Quiz';
		if (!class_exists($quizObjectType)) {
			displayerror('Error. This type of quiz has not been implemented yet.');
			return false;
		}

		$quizObject = new $quizObjectType($this->moduleComponentId);
		return $quizObject;
	}
}
