<?php
if(!defined('__PRAGYAN_CMS'))
{ 
	header($_SERVER['SERVER_PROTOCOL'].' 403 Forbidden');
	echo "<h1>403 Forbidden<h1><h4>You are not authorized to access the page.</h4>";
	echo '<hr/>'.$_SERVER['SERVER_SIGNATURE'];
	exit(1);
}
/*
 * Created on Jan 15, 2009
 */

// SSO: optAnswer{SectionId}_{QuestionId} => value (OptionId)
// MSO: chkAnswer{SectionId}_{QuestionId}_{OptionId}
// Subj: txtAnswer{SectionId}_{QuestionId}


define('QUIZ_COMPLETED', 1);
define('QUIZ_SUBMISSIONFAILED', false);
define('QUIZ_SUBMISSIONSUCCESSFUL', true);

define('QUIZ_TIMEOUT_ERRORMSG', 'You have run out of time for this quiz. Your test will be evaluated only for the answers you have submitted previously.');
define('QUIZ_SECTION_TIMEOUT_ERRORMSG', 'You have run out of time for this section. Only the answers that you have submitted previously will be evaluated. You can still view sections for which you have time left from the <a href="./+view">Quiz main page</a>.');

class SimpleQuiz implements IQuiz {
	private $quizId;
	private $quizRow;

	public function __construct($quizId) {
		$this->quizId = $quizId;
		$this->quizRow = getQuizRow($quizId);
	}
	
	/**
	 * function getPropertiesForm:
	 * will be called from quiz edit, here no quiz specific properties
	 */
	public function getPropertiesForm($dataSource) {
		return 'No quiz specific properties.';
	}

	public function submitPropertiesForm() {
		return true;
	}
	
	/**
	 * function getSectionStartForm:
	 * return HTML Section start form
	 * form with a button and a hidden field specifying section id
	 */
	private function getSectionStartForm($sectionId) {
		return <<<SECTIONSTARTFORM
			<form name="sectionstartform" method="POST" action="./+view" style="padding:0;margin:0;display:inline">
				<input type="hidden" name="hdnSectionId" id="hdnSectionId" value="$sectionId" />
				<input type="submit" name="btnStartSection" id="btnStartSection" value="Start" />
			</form>
SECTIONSTARTFORM;
	}
	
	/**
	 * function getFrontPage:
	 * if random access is set, front page displays list of available sections and lets user select section
	 */
	public function getFrontPage($userId) {
		$frontPage = "<h2>{$this->quizRow['quiz_title']}</h2>\n";
		$frontPage .= "<div class=\"quiz_headertext\">{$this->quizRow['quiz_headertext']}</div><br /><br />\n";
		if ($this->quizRow['quiz_allowsectionrandomaccess']) {
			$sectionList = getSectionList($this->quizId);
			for ($i = 0; $i < count($sectionList); ++$i) {
				$frontPage .= '<strong>' . $sectionList[$i]['quiz_sectiontitle'] . '</strong> ';
				$attemptRow = getAttemptRow($this->quizId, $sectionList[$i]['quiz_sectionid'], $userId);
				if (!$attemptRow || is_null($attemptRow['quiz_attemptstarttime'])) {
					// User hasn't started this section yet.
					$frontPage .= $this->getSectionStartForm($sectionList[$i]['quiz_sectionid']);
				}
				elseif (is_null($attemptRow['quiz_submissiontime'])) {
					// User hasn't finished this section yet.
					$frontPage .= ' <a href="./+view&sectionid=' . $sectionList[$i]['quiz_sectionid'] . '">Go to questions</a>';
				}
				else {
					// User has finished the section already.
					$frontPage .= " Section Completed.";
				}
				$frontPage .= '<br /><br />';
			}
		}
		else {
			$frontPage .= <<<QUIZSTARTFORM
			<form name="quizstartform" method="POST" action="./+view" style="padding:0;margin:0;display:inline">
				<input type="submit" name="btnStartQuiz" id="btnStartQuiz" value="Start" />
			</form>
QUIZSTARTFORM;
		}

		return $frontPage;
	}

	/**
	 * function getQuizPage:
	 * Retrieves the next page for the user.
	 * Use this function from outside the class.
	 * @param Integer $userId User ID.
	 * @return String HTML for the next page.
	 */
	public function getQuizPage($userId) {
		if ($this->checkQuizCompleted($userId)) {
			displayinfo('You seem to have completed this quiz already. You can only take this quiz once.');
			return '';
		}

		if ($this->quizRow['quiz_allowsectionrandomaccess']) {
			// if btnStartSection and hdnSectionId are set
			if (isset($_POST['btnStartSection']) && $this->isValidId($_POST['hdnSectionId']) && sectionBelongsToQuiz($this->quizId, $_POST['hdnSectionId']))
				$sectionId = intval($_POST['hdnSectionId']);
			elseif (isset($_GET['sectionid']) && $this->isValidId($_GET['sectionid']))
				$sectionId = intval($_GET['sectionid']);

			if (!isset($sectionId))
				return $this->getFrontPage($userId);

			$attemptRow = getAttemptRow($this->quizId, $sectionId, $userId);
			$sectionStarted = $attemptRow ? true : false;
			$sectionCompleted = !is_null($attemptRow['quiz_submissiontime']);

			if (!$sectionStarted) {
				if (!isset($_POST['btnStartSection'])) {
					displayerror('Error. You have not started this section yet. Please go to the quiz main page, and click on the Start Section button to view this section.');
					return '';
				}
				if (!startSection($this->quizId, $sectionId, $userId))
					return '';
			}
			elseif ($sectionCompleted) {
				displayinfo("You have completed this section.");
				return '';
			}

			if (isset($_POST['btnSubmit'])) {
				if ($this->submitQuizPage($userId) === true) {
					if ($this->markSectionCompleted($userId, $sectionId)) {
						// This section has been completed. See if the quiz also got completed
						if ($this->checkQuizCompleted($userId)) {
							return $this->quizRow['quiz_submittext'];
						}
						else {
							displayinfo('You have completed this section. You can move to another section.');
							return $this->getFrontPage($userId);
						}
					}
					else
						displayinfo('Your previous page was submitted successfully.');
				}
			}

			// TODO: Put in time check here
			if ($this->checkUserTimedOut($userId)) {
				displayerror(QUIZ_TIMEOUT_ERRORMSG);
				$this->forceQuizCompleted($userId);
				return '';
			}
			elseif ($this->checkUserTimedOut($userId, $sectionId)) {
				displayerror(QUIZ_SECTION_TIMEOUT_ERRORMSG);
				$this->forceQuizCompleted($userId, $sectionId);
				return '';
			}

			return $this->formatNextPage($userId, $sectionId);
		}
		else {
			// if quiz is already started, show next page
			// else, see if btnStartQuiz is set, if yes, mark quiz as started, and show next page

			// to mark a user's quiz as started, we insert one entry for each section in the quiz into user_attempts
			// to see if the user's quiz has been started, we see if there is a row in user_attempts with section id 1
			$minSectionId = getFirstSectionId($this->quizId);
			$attemptRow = getAttemptRow($this->quizId, $minSectionId, $userId);

			if (!$attemptRow) {
				if (!isset($_POST['btnStartQuiz']))
					return $this->getFrontPage($userId);

				// ok, btnStartQuiz was set, and the quiz wasn't started already,
				// start it by inserting a row for each section in the quiz into quiz_userattempts.
				$attemptQuery = "INSERT INTO `quiz_userattempts`(`page_modulecomponentid`, `quiz_sectionid`, `user_id`, `quiz_attemptstarttime`) " .
						"SELECT {$this->quizId}, `quiz_sectionid`, $userId, NOW() FROM `quiz_sections` WHERE `page_modulecomponentid` = '{$this->quizId}'";
				if (!mysql_query($attemptQuery)) {
					displayerror('Database Error. Could not update quiz information.');
					return '';
				}
			}

			if (isset($_POST['btnSubmit'])) {
				if ($this->submitQuizPage($userId) == true) {
					if ($this->markSectionCompleted($userId, -1)) {
						if ($this->checkQuizCompleted($userId))
							return $this->quizRow['quiz_submittext'];
					}
					else
						displayinfo('Your previous page was submitted successfully.');
				}
			}

			// TODO: Put in time check here
			if ($this->checkUserTimedOut($userId)) {
				displayerror(QUIZ_TIMEOUT_ERRORMSG);
				$this->forceQuizCompleted($userId);
				return '';
			}

			return $this->formatNextPage($userId);
		}
	}

	/**
	 * function submitQuizPage:
	 * Submits a page worth of questions.
	 * @param Integer $userId User ID of the user taking the quiz.
	 * @return Boolean True indicating successful submission, and false indicating errors.
	 */
	public function submitQuizPage($userId) {
		// get all the questions that have been shown to the user
		// get the submitted answer for all of these questions, and insert them into the db
		$questionQuery = "SELECT `quiz_questions`.`quiz_sectionid` AS `quiz_sectionid`, " .
				"`quiz_questions`.`quiz_questionid` AS `quiz_questionid`, " .
				"`quiz_questions`.`quiz_questiontype` AS `quiz_questiontype`, " .
				"`quiz_questions`.`quiz_answermaxlength` AS `quiz_answermaxlength` " .
				"FROM `quiz_answersubmissions`, `quiz_questions` WHERE " .
				"`quiz_questions`.`page_modulecomponentid` = `quiz_answersubmissions`.`page_modulecomponentid` AND " .
				"`quiz_questions`.`quiz_sectionid` = `quiz_answersubmissions`.`quiz_sectionid` AND " .
				"`quiz_questions`.`quiz_questionid` = `quiz_answersubmissions`.`quiz_questionid` AND " .
				"`quiz_questions`.`page_modulecomponentid` = '{$this->quizId}' AND `user_id` = '$userId' AND " .
				"`quiz_questionviewtime` IS NOT NULL AND `quiz_answersubmittime` IS NULL ";
		if($this->quizRow['quiz_allowsectionrandomaccess'] == 1)
			$questionQuery .= "AND `quiz_answersubmissions`.`quiz_sectionid` = '".escape($_GET['sectionid']) ."'";
		$questionQuery .= "ORDER BY `quiz_answersubmissions`.`quiz_questionrank` LIMIT {$this->quizRow['quiz_questionsperpage']}";
		$questionResult = mysql_query($questionQuery);
		if (!$questionResult) {
			displayerror('Invalid query. ' . $questionQuery . ' ' . mysql_error());
			return false;
		}

		// Put in check about user's time elapsed here
		if ($this->checkUserTimedOut($userId, -1, '1 MINUTE')) {
			displayerror('Sorry, you have exceeded your time limit for the quiz. Your latest submission cannot be evaluated.');
			return false;
		}

		if ($this->quizRow['quiz_allowsectionrandomaccess']) {
			$sectionId = intval($_GET['sectionid']);
			if ($this->checkUserTimedOut($userId, $sectionId, '1 MINUTE')) {
				displayerror('Sorry, you have exceeded your time limit for this section. Your latest submission cannot be evaluated.');
				return false;
			}
		}

		$submittedAnswers = array();
		$rollbackQuery = array();
		while ($questionRow = mysql_fetch_assoc($questionResult)) {
			$rollbackQuery[] = "(`quiz_sectionid` = {$questionRow['quiz_sectionid']} AND `quiz_questionid` = {$questionRow['quiz_questionid']})";
			$questionType = $questionRow['quiz_questiontype'];

			if (!isset($_POST['hdnQuestion' . $questionRow['quiz_sectionid'] . '_' . $questionRow['quiz_questionid']])) {
				displayerror(
					'Error. The answers that you submitted do not match the list of questions you were shown. You may have refreshed the page, and resubmitted your previous page\'s answers. ' .
					'Please do not use the navigation buttons on your browser while taking the quiz.'
				);
				return false;
			}

			if ($questionType == 'sso' || $questionType == 'mso') {
				$options = getQuestionOptionList($this->quizId, $questionRow['quiz_sectionid'], $questionRow['quiz_questionid']);
				if ($questionType == 'sso') {
					$fieldName = 'optAnswer' . $questionRow['quiz_sectionid'] . '_' . $questionRow['quiz_questionid'];
					$submittedAnswer = isset($_POST[$fieldName]) && is_numeric($_POST[$fieldName]) ? intval($_POST[$fieldName]) : '';
					$optionFound = false;
					for ($i = 0; $i < count($options); ++$i) {
						if ($options[$i]['quiz_optionid'] == $submittedAnswer) {
							$submittedAnswers[] = array($questionRow['quiz_sectionid'], $questionRow['quiz_questionid'], $questionRow['quiz_questiontype'], $submittedAnswer);
							$optionFound = true;
							break;
						}
					}

					if (!$optionFound)
						$submittedAnswers[] = array($questionRow['quiz_sectionid'], $questionRow['quiz_questionid'], $questionRow['quiz_questiontype'], '');
				}
				else {
					$submittedAnswer = array();
					for ($i = 0; $i < count($options); ++$i) {
						$fieldName = 'chkAnswer' . $questionRow['quiz_sectionid'] . '_' . $questionRow['quiz_questionid'] . '_' . $options[$i]['quiz_optionid'];
						if (isset($_POST[$fieldName]) && is_numeric($_POST[$fieldName]))
							$submittedAnswer[] = intval($options[$i]['quiz_optionid']);
					}
					sort($submittedAnswer);
					$submittedAnswers[] = array($questionRow['quiz_sectionid'], $questionRow['quiz_questionid'], $questionRow['quiz_questiontype'], implode('|', $submittedAnswer));
				}
			}
			elseif ($questionType == 'subjective') {
				$fieldName = 'txtAnswer' . $questionRow['quiz_sectionid'] . '_' . $questionRow['quiz_questionid'];
				$submittedAnswers[] = array($questionRow['quiz_sectionid'], $questionRow['quiz_questionid'], $questionRow['quiz_questiontype'], isset($_POST[$fieldName]) ? escape($_POST[$fieldName]) : '');
			}
		}

		$rollbackQuery = "UPDATE `quiz_answersubmissions` SET `quiz_answersubmittime` = NULL WHERE `page_modulecomponentid` = {$this->quizId} AND `user_id` = $userId AND (" . implode(' OR ', $rollbackQuery) . ")";
		for ($i = 0; $i < count($submittedAnswers); ++$i) {
			$updateQuery = "UPDATE `quiz_answersubmissions` SET `quiz_submittedanswer` = '{$submittedAnswers[$i][3]}', `quiz_answersubmittime` = NOW() WHERE " .
					"`page_modulecomponentid` = {$this->quizId} AND `quiz_sectionid` = '{$submittedAnswers[$i][0]}' AND " .
					"`quiz_questionid` = '{$submittedAnswers[$i][1]}' AND `user_id` = '$userId'";
			if (!mysql_query($updateQuery)) {
				displayerror('Invalid Query. Could not save answers.');
				mysql_query($rollbackQuery);
				return false;
			}
		}

		return true;
	}

	/**
	 * function checkQuizInitialized:
	 * Checks if a quiz has been initialized.
	 * @param Integer $userId User ID of the user.
	 * @return Boolean True or false to indicate whether the quiz has been initialized.
	 */
	private function checkQuizInitialized($userId) {
		$countQuery = "SELECT COUNT(*) FROM `quiz_answersubmissions` WHERE `page_modulecomponentid` = '{$this->quizId}' AND `user_id` = '$userId'";
		$countResult = mysql_query($countQuery);
		
		$countRow = mysql_fetch_row($countResult);
		
		return $countRow[0] == $this->quizRow['quiz_questionspertest'];
	}

	/**
	 * function initQuiz:
	 * Performs necessary operations before a user starts a quiz.
	 * @param Integer $userId User ID.
	 * @return Boolean True indicating success, false indicating errors.
	 */
	public function initQuiz($userId) {
		// a user is about to start the quiz
		// generate a list of questions, insert into quiz_answersubmissions, with answersubmittime = NULL
		if ($this->checkQuizInitialized($userId))
			return true;

		$this->deleteEntries($userId);
		$sectionList = getSectionList($this->quizId);
		$questionList = array();
		$sections = array();
		for ($i = 0; $i < count($sectionList); ++$i) {
			$questionList[$i] = $this->getSectionQuestions($sectionList[$i]);
			for ($j = 0; $j < count($questionList[$i]); ++$j)
				$sections[] = $i;
		}

		if ($this->quizRow['quiz_allowsectionrandomaccess'] == 0 && $this->quizRow['quiz_mixsections']) 
			shuffle($sections);

		$offsets = array_fill(0, count($questionList), 0);
		for ($i = 0; $i < count($sections); ++$i) {
			$insertQuery = "INSERT INTO `quiz_answersubmissions`(`page_modulecomponentid`, `quiz_sectionid`, `quiz_questionid`, `user_id`, `quiz_questionrank`) VALUES" .
					"({$this->quizId}, {$sectionList[$sections[$i]]['quiz_sectionid']}, {$questionList[$sections[$i]][$offsets[$sections[$i]]]}, $userId, $i)";
			if (!mysql_query($insertQuery)) {
				displayerror('Database Error. Could not initialize quiz.');
				return false;
			}
			$offsets[$sections[$i]]++;
		}
		return true;
	}

	/**
	 * function deleteEntries:
	 * Deletes all entries for a particular user.
	 */
	public function deleteEntries($userId) {
		$tableNames = array('quiz_userattempts', 'quiz_answersubmissions');
		$affectedRows = array();
		return deleteItem($tableNames, "`page_modulecomponentid` = {$this->quizId} AND `user_id` = $userId", $affectedRows);
	}

	/**
	 * function getPageQuestions:
	 * returns questions to be displayed in this page, ie questions for which answer has not been submitted yet
	 */
	private function getPageQuestions($userId, $sectionId = -1) {
		$questionsPerPage = $this->quizRow['quiz_questionsperpage'];
		$questionQuery = "SELECT `quiz_sectionid`, `quiz_questionid` FROM `quiz_answersubmissions` WHERE `user_id` = '$userId' AND `page_modulecomponentid` = '{$this->quizId}' AND `quiz_answersubmittime` IS NULL ";
		if ($this->quizRow['quiz_allowsectionrandomaccess'] == 1)
			$questionQuery .= " AND `quiz_sectionid` = '$sectionId' ";
		$questionQuery .= " ORDER BY `quiz_questionrank` LIMIT $questionsPerPage";
		$questionResult = mysql_query($questionQuery);
		if (!$questionResult) {
			displayerror('Database Error. Could not fetch questions.');
			return null;
		}
		$questionIds = array();
		while ($questionRow = mysql_fetch_row($questionResult))
			$questionIds[] = $questionRow;
		return $questionIds;
	}
	
	/**
	 * function getTimerHtml:
	 * returns HTML timer code and invokes JSTimer to take care of running timer in browser
	 * @see ./timer.js
	 */
	private function getTimerHtml($userId, $sectionId = -1) {
		$testElapsedTime = $this->getElapsedTime($userId);
		$testElapsedTime = explode(':', $testElapsedTime);
		$testElapsedTime = implode(', ', $testElapsedTime);
		$sectionElapsedTime = $this->getElapsedTime($userId,$sectionId);
		$sectionElapsedTime = explode(':', $sectionElapsedTime);
		$sectionElapsedTime = implode(', ', $sectionElapsedTime);

		$testTime = $this->quizRow['quiz_testduration'];
		$testTime = explode(':', $testTime);
		$testTime = implode(', ', $testTime);
		
		if ($this->quizRow['quiz_allowsectionrandomaccess']) {
		    $sectionTime = mysql_fetch_array(mysql_query("SELECT `quiz_sectiontimelimit` FROM `quiz_sections` WHERE `page_modulecomponentid` = '{$this->quizId}' AND `quiz_sectionid` = '$sectionId'"));
		
		    $sectionTime = $sectionTime[0];
		    $sectionTime = explode(':', $sectionTime);
		    $sectionTime = implode(', ', $sectionTime);		    
    		$scripts[] = "var sectionTimer = new JSTimer('sectionTimerContainer', $sectionElapsedTime);\nsectionTimer.addTickHandler($sectionTime, forceQuizSubmit)";
		}


		$scripts[] = "var testTimer = new JSTimer('testTimerContainer', $testElapsedTime);\ntestTimer.addTickHandler($testTime, forceQuizSubmit)";
		
		$divs = array();
		if ($this->quizRow['quiz_showquiztimer']) {

			$divs[] = '<div id="testTimerContainer" class="quiz_testtimer">Total Quiz Time Elapsed: </div>';

		}

		if ($this->quizRow['quiz_showpagetimer']) {
			$divs[] = '<div id="pageTimerContainer" class="quiz_pagetimer"></div>';
			$scripts[] = "var pageTimer = new JSTimer('pageTimerContainer', 0, 0, 0);\n";
		}

		$sectionRow = getSectionRow($this->quizId, $sectionId);
		if ($sectionRow['quiz_sectionshowlimit']) {
			$sectionRow = getSectionRow($this->quizId,$sectionId);
			$limit = $sectionRow['quiz_sectiontimelimit'];
			$divs[] = '<div id="pageTimerlimit" class="quiz_limit">Section Limit: ' . $limit . '</div>';
			$divs[] = '<div id="sectionTimerContainer" 
class="quiz_testtimer">Section Time Elapsed: </div><br /><br />';
		}

		global $urlRequestRoot, $cmsFolder, $moduleFolder;
		$timerScriptSrc = "$urlRequestRoot/$cmsFolder/$moduleFolder/quiz/timer.js";

		if (count($divs)) {
			$divs = implode("\n", $divs);
			$scripts = implode("\n", $scripts);

			$timerScript = <<<TIMERSCRIPT
				<script type="text/javascript" src="$timerScriptSrc"></script>
				$divs
				<script type="text/javascript">
					function forceQuizSubmit() {
						alert("Your time is up. Please click Ok to submit the quiz. If you do not submit within 30 seconds, your quiz will expire, and your answers to this page will not be recorded.");
						var quizForm = document.getElementById('quizForm');
						var submitButton = document.getElementById('btnSubmit');
						submitButton.type = 'hidden';
						quizForm.submit();
					}

					$scripts
				</script>
TIMERSCRIPT;
		}

		return $timerScript;
	}

	/**
	 * function formatQuestion:
	 * Given a question row, return HTML for the question.
	 * @param $questionRow
	 * @return string Question in HTML.
	 */
	private function formatQuestion($questionRow, $questionNumber = -1) {
		$questionType = $questionRow['quiz_questiontype'];
		if ($questionType == 'subjective') {
			$fieldName = 'txtAnswer' . $questionRow['quiz_sectionid'] . '_' . $questionRow['quiz_questionid'];
			$answer = '<textarea 
style="width:95%;height:100px;" name="' . 
$fieldName . '" id="' . $fieldName . '"></textarea>';
		}
		else {
			$optionList = getQuestionOptionList($this->quizId, $questionRow['quiz_sectionid'], $questionRow['quiz_questionid']);

			$answer = '<table class="objectivecontainer" width="100%">';
			for ($i = 0; $i < count($optionList); ++$i) {
				$fieldType = ($questionType == 'sso' ? 'radio' : 'checkbox');
				$fieldName = '';
				$fieldId = '';
				if ($questionType == 'sso') {
					$fieldName = 'optAnswer' . $questionRow['quiz_sectionid'] . '_' . $questionRow['quiz_questionid'];
					$fieldId = $fieldName . '_' . $optionList[$i]['quiz_optionid'];
				}
				elseif ($questionType == 'mso') {
					$fieldName = 'chkAnswer' . $questionRow['quiz_sectionid'] . '_' . $questionRow['quiz_questionid'] . '_' . $optionList[$i]['quiz_optionid'];
					$fieldId = $fieldName;
				}
				$answer .= "<tr><td width=\"24\"><input type=\"$fieldType\" name=\"$fieldName\" id=\"$fieldId\" value=\"{$optionList[$i]['quiz_optionid']}\" /> </td><td><label for=\"$fieldId\"> {$optionList[$i]['quiz_optiontext']}</label></td></tr>\n";
			}
			$answer .= '</table>';
		}

		$hiddenFieldName = "hdnQuestion{$questionRow['quiz_sectionid']}_{$questionRow['quiz_questionid']}";

		$questionDesc = $questionRow['quiz_question'];
		if ($questionNumber > 0) $questionDesc = $questionNumber . ') ' . $questionDesc;

		global $sourceFolder, $moduleFolder;
		require_once($sourceFolder."/pngRender.class.php");
		$render = new pngrender();
		$questionDesc = $render->transform($questionDesc);
		$answer = $render->transform($answer);

		return <<<QUESTIONFORM
			<input type="hidden" name="$hiddenFieldName" id="$hiddenFieldName" value="" />
			<div class="quiz_questioncontainer">
				<br /><b>{$questionDesc}</b><br /><br />
			</div>
			<div class="quiz_answercontainer">
				$answer
			</div>
QUESTIONFORM;
	}

	/**
	 * function formatNextPage:
	 * Returns an HTML page containing the next set of questions for the user.
	 */
	private function formatNextPage($userId, $sectionId = -1) {
		$questionCount = $this->quizRow['quiz_questionsperpage'];
		$questionQuery = "SELECT `quiz_questions`.`quiz_sectionid` AS `quiz_sectionid`, `quiz_questions`.`quiz_questionid` AS `quiz_questionid`, `quiz_question`, `quiz_questiontype`, `quiz_questionweight`, `quiz_answermaxlength`, `quiz_rightanswer`, `quiz_questionviewtime`, `quiz_answersubmittime` " .
				"FROM `quiz_questions`, `quiz_answersubmissions` WHERE " .
				"`quiz_questions`.`page_modulecomponentid` = '{$this->quizId}' AND " .
				"`quiz_answersubmissions`.`user_id` = '$userId' AND " .
				"`quiz_questions`.`page_modulecomponentid` = `quiz_answersubmissions`.`page_modulecomponentid` AND " .
				"`quiz_questions`.`quiz_sectionid` = `quiz_answersubmissions`.`quiz_sectionid` AND " .
				"`quiz_questions`.`quiz_questionid` = `quiz_answersubmissions`.`quiz_questionid` AND " .
				"`quiz_answersubmissions`.`quiz_answersubmittime` IS NULL ";
		if ($this->quizRow['quiz_allowsectionrandomaccess'] == 1)
			$questionQuery .= "AND `quiz_answersubmissions`.`quiz_sectionid` = '$sectionId' ";
		$questionQuery .= "ORDER BY `quiz_answersubmissions`.`quiz_questionrank` " .
				"LIMIT $questionCount";

		$questionResult = mysql_query($questionQuery);

		$questionNumber = 1;
		$questionPage = $this->getTimerHtml($userId, $sectionId);
		$questionPage .= '<form name="quizquestions" id="quizForm" method="POST" action="./+view' . ($sectionId == -1 ? '' : '&sectionid=' . $sectionId) . '" onsubmit="return confirm(\'Are you sure you wish to submit this page?\')">';
		while ($questionRow = mysql_fetch_assoc($questionResult)) {
			if (is_null($questionRow['quiz_questionviewtime']))
				mysql_query("UPDATE `quiz_answersubmissions` SET `quiz_questionviewtime` = NOW() WHERE `page_modulecomponentid` = '{$this->quizId}' AND `quiz_sectionid` = '{$questionRow['quiz_sectionid']}' AND `quiz_questionid` = '{$questionRow['quiz_questionid']}'");
			$questionPage .= $this->formatQuestion($questionRow, $questionNumber);
			++$questionNumber;
		}
		$questionPage .= '<input type="submit" name="btnSubmit" id="btnSubmit" value="Submit" />';
		$questionPage .= '</form>';

		$questionPage .= <<<QUESTIONPAGESCRIPT
		<script type="text/javascript">
			// make opt buttons uncheckable
			var inputFields = document.getElementById('quizForm').getElementsByTagName('input');
			for (var i = 0; i < inputFields.length; ++i) {
				if (inputFields[i].type == 'radio')
					inputFields[i].onclick = function(e) {
						if (this.rel == 'checked') {
							this.checked = false;
							this.rel = '';
						}
						else {
							var elements = document.getElementsByName(this.name);
							for (var i = 0; i < elements.length; ++i) {
								elements[i].rel = '';
								elements[i].checked = false;
							}
							this.checked = true;
							this.rel = 'checked';
						}
					};
			}
		</script>
QUESTIONPAGESCRIPT;
		return $questionPage;
	}

	/**
	 * function countAttemptedQuestions:
	 * Counts the number of questions a user has submitted in a given section.
	 * @param Integer $userId User ID of the user.
	 * @param Integer $sectionId Section ID of the user. If omitted, total number of questions attempted in the quiz are counted.
	 * @return Integer Number of questions attempted. False in case of errors.
	 */
	private function countAttemptedQuestions($userId, $sectionId = -1) {
		$countQuery = "SELECT COUNT(*) FROM `quiz_submittedanswers` WHERE `page_modulecomponentid` = '{$this->quizId}'";
		if ($sectionId != -1)
			$countQuery .= " AND `quiz_sectionid` = '$sectionId'";
		$countQuery .= " `user_id` = $userId AND `quiz_answersubmittime` IS NOT NULL";
		$countResult = mysql_query($countQuery);
		if (!$countResult) {
			displayerror('Database Error. Could not retrieve user attempt information.');
			return false;
		}
		$countRow = mysql_fetch_row($countResult);
		return $countRow[0];
	}
	
	/**
	 * function getSectionQuestions:
	 * gets list of questionId in this section considering, whether quiz is randomized and number of questions per section
	 */
	private function getSectionQuestions($sectionRow) {
		$questionTypes = array_keys(getQuestionTypes());
		$sectionId = $sectionRow['quiz_sectionid'];

		if ($sectionRow['quiz_sectionquestionshuffled'] == 0) {
			$limit = 0;
			for ($i = 0; $i < count($questionTypes); ++$i)
				$limit += $sectionRow["quiz_section{$questionTypes[$i]}count"];
			$questionQuery = "SELECT `quiz_questionid` FROM `quiz_questions` WHERE `page_modulecomponentid` = '{$this->quizId}' AND `quiz_sectionid` = '$sectionId' ORDER BY `quiz_questionrank` LIMIT $limit";
		}
		else {
			$questionIdQueries = array();
			for ($i = 0; $i < count($questionTypes); ++$i) {
				$limit = $sectionRow["quiz_section{$questionTypes[$i]}count"];
				if ($limit) {
					$questionIdQueries[] = 
						"(SELECT `quiz_questionid` FROM `quiz_questions` WHERE `page_modulecomponentid` = '{$this->quizId}' AND `quiz_sectionid` = '$sectionId' AND `quiz_questiontype` = '{$questionTypes[$i]}' ORDER BY RAND() LIMIT $limit)";
				}
			}

			$questionQuery = "SELECT `quiz_questionid` FROM (" . implode(' UNION ', $questionIdQueries) . ") AS `questions` ORDER BY RAND()";
		}

		$questionIds = array();
		$questionResult = mysql_query($questionQuery) or die(mysql_error());
		while ($questionRow = mysql_fetch_row($questionResult))
			$questionIds[] = $questionRow[0];
		return $questionIds;
	}

	/**
	 * function checkQuizCompleted:
	 * Checks whether a user has completed a quiz, by checking whether the user has completed
	 * all sections under that quiz.
	 * @param Integer $userId User ID.
	 * @return Boolean True or false indicating whether the quiz has been completed.
	 */
	private function checkQuizCompleted($userId) {
		$countQuery = "SELECT COUNT(*) FROM `quiz_userattempts`, `quiz_sections` WHERE " .
				"`quiz_sections`.`page_modulecomponentid` = `quiz_userattempts`.`page_modulecomponentid` AND " .
				"`quiz_sections`.`quiz_sectionid` = `quiz_userattempts`.`quiz_sectionid` AND " .
				"`quiz_sections`.`page_modulecomponentid` = '{$this->quizId}' AND " .
				"`quiz_userattempts`.`user_id` = '$userId' AND " .
				"`quiz_submissiontime` IS NOT NULL";
		$countResult = mysql_query($countQuery);
		if (!$countResult) {
			displayerror('Database Error. Could not fetch section information.');
			return false;
		}
		$countRow = mysql_fetch_row($countResult);
		$completedCount = $countRow[0];
		$countQuery = "SELECT COUNT(*) FROM `quiz_sections` WHERE `page_modulecomponentid` = '{$this->quizId}'";
		$countResult = mysql_query($countQuery);
		$countRow = mysql_fetch_row($countResult);
		return $countRow[0] == $completedCount;
	}

	/**
	 * function isValidId:
	 * Checks whether an ID is valid.
	 * @param Integer $id The ID to test.
	 * @return Boolean Whether the ID is valid or not.
	 */
	private function isValidId($id) {
		return isset($id) && is_numeric($id) && $id > 0;
	}

	/**
	 * function markSectionCompleted:
	 * Marks a section as completed.
	 * @return Boolean True if the section is (or was) completed. False if the section is not complete.
	 */
	private function markSectionCompleted($userId, $sectionId = -1) {
		if ($sectionId == -1) {
			$sections = getSectionList($this->quizId);
			$allOk = true;
			for ($i = 0; $i < count($sections); ++$i)
				$allOk = $this->markSectionCompleted($userId, $sections[$i]['quiz_sectionid']) && $allOk;
			return $allOk;
		}

		$attemptRow = getAttemptRow($this->quizId, $sectionId, $userId);
		if (is_null($attemptRow['quiz_submissiontime'])) {
			// Check if all questions for this section have been completed, if yes, set quiz_submissiontime and return true
			$questionQuery = "SELECT COUNT(*) FROM `quiz_answersubmissions` WHERE " .
					"`page_modulecomponentid` = '{$this->quizId}' AND `quiz_sectionid` = '$sectionId' AND `user_id` = '$userId' AND `quiz_answersubmittime` IS NULL";
			$questionResult = mysql_query($questionQuery);
			$questionRow = mysql_fetch_row($questionResult);

			if ($questionRow[0] != 0)
				return false;

			$updateQuery = "UPDATE `quiz_userattempts` SET `quiz_submissiontime` = NOW() WHERE `page_modulecomponentid` = '$this->quizId' AND `quiz_sectionid` = '$sectionId' AND `user_id` = '$userId'";
			if (mysql_query($updateQuery))
				return true;
			else {
				displayerror('Database Error. Could not mark section as completed.');
				return -1;
			}
		}
		else
			return true;
	}

	/**
	 * function markQuizCompleted:
	 * Mark a Quiz as completed. Fill all unsubmitted answers with '', and set quiz as completed.
	 * To be used only when a user's quiz times out.
	 * @param Integer $userId User ID.
	 */
	private function markQuizCompleted($userId) {
		$updateQueries = array(
			"UPDATE `quiz_answersubmissions` SET `quiz_submittedanswer` = '', `quiz_answersubmittime` = NOW() WHERE `page_modulecomponentid` = {$this->quizId} AND `user_id` = '$userId' AND `quiz_answersubmittime` IS NULL",
			"UPDATE `quiz_userattempts` SET `quiz_submissiontime` = NOW() WHERE `page_modulecomponentid` = '{$this->quizId}' AND `user_id` = '$userId' AND `quiz_submissiontime` IS NULL"
		);

		if (!mysql_query($updateQueries[0]) || !mysql_query($updateQueries[1])) {
			displayerror('Error. Could not mark quiz as completed.');
			return false;
		}

		return true;
	}

	/**
	 * function getElapsedTime:
	 * Returns the time a user has spent on a section or a quiz.
	 * @param Integer $userId User ID.
	 * @param Integer $sectionId Section ID. Optional. 
	 */
	private function getElapsedTime($userId, $sectionId = -1) {
		if ($sectionId < 0)
			$elapsedQuery = "SELECT TIMEDIFF(NOW(), MIN(`quiz_attemptstarttime`)) FROM `quiz_userattempts` WHERE " .
					"`page_modulecomponentid` = '{$this->quizId}' AND `user_id` = '$userId'";
		else
			$elapsedQuery = "SELECT TIMEDIFF(NOW(), `quiz_attemptstarttime`) FROM `quiz_userattempts` WHERE " .
					"`page_modulecomponentid` = '{$this->quizId}' AND `quiz_sectionid` = '$sectionId' AND `user_id` = '$userId'";

		$elapsedResult = mysql_query($elapsedQuery);
		if (!$elapsedResult)
			displayerror('Error. ' . $elapsedQuery . '<br />' . mysql_error());
		$elapsedRow = mysql_fetch_row($elapsedResult);
		return $elapsedRow[0];
	}

	private function getRemainingTime($userId, $sectionId = -1) {
		if ($sectionId < 0) {
			$remainingQuery = "SELECT TIMEDIFF(NOW(), ADDTIME(MIN(`quiz_attemptstarttime`), '{$this->quizRow['quiz_testduration']}')) FROM `quiz_userattempts` WHERE " .
					"`page_modulecomponentid` = '{$this->quizId}' AND `user_id` = '$userId'";
		}
		else {
			$remainingQuery = "SELECT TIMEDIFF(NOW(), ADDTIME(`quiz_attemptstarttime`, '{$this->quizRow['quiz_testduration']}')) FROM `quiz_userattempts` WHERE " .
					"`page_modulecomponentid` = '{$this->quizId}' AND `user_id` = '$userId'";
		}

		$remainingResult = mysql_query($remainingQuery);
		$remainingRow = mysql_fetch_row($remainingResult);
		return $remainingRow[0];
	}

	/**
	 * function checkUserTimedOut:
	 * Returns a string denoting the amount of time the user has to complete the section or quiz.
	 * @param Integer $userId User ID of the user.
	 * @param Integer $sectionId Section ID. If omitted, the time remaining for the entire quiz is shown.
	 * @param String $offset Amount of time to add as grace period for the user. To be used only to check if a page can be submitted.
	 * @return Boolean indicating whether the user has run out of time or not. -1 indicating errors.
	 */
	private function checkUserTimedOut($userId, $sectionId = -1, $offset = '0 SECOND') {
		if ($sectionId < 0) {
			// Check if the quiz has timed out:
			//  Find the earliest attempt start time, add quiz duration to it
			//	add offset to now, and compare
			$timeoutQuery = "SELECT IF(DATE_SUB(NOW(), INTERVAL $offset) > ADDTIME(MIN(`quiz_attemptstarttime`), '{$this->quizRow['quiz_testduration']}'), 1, 0) AS `quiz_expired` FROM " .
					"`quiz_userattempts` WHERE `page_modulecomponentid` = {$this->quizId} AND `user_id` = $userId";
		}
		else {
			$sectionRow = getSectionRow($this->quizId, $sectionId);

			if ($sectionRow['quiz_sectiontimelimit'] == '00:00:00')
				return false;

			$timeoutQuery = "SELECT IF(DATE_SUB(NOW(), INTERVAL $offset) > ADDTIME(`quiz_attemptstarttime`, '{$sectionRow['quiz_sectiontimelimit']}'), 1, 0) AS `quiz_expired` FROM " .
					"`quiz_userattempts` WHERE `page_modulecomponentid` = '{$this->quizId}' AND `quiz_sectionid` = '$sectionId' AND `user_id` = '$userId'";
		}

		$timeoutResult = mysql_query($timeoutQuery);
		if (!$timeoutResult) {
			displayerror('Database Error. Could not retrieve time information.');
			return -1;
		}

		$timeoutRow = mysql_fetch_row($timeoutResult);
		if (is_null($timeoutRow[0])) {
			// An invalid Section ID was passed => we could not find a row for the user for that
			// Section ID. assume he timed out
			return true;
		}

		return $timeoutRow[0];
	}

	/**
	 * function forceQuizCompleted:
	 * Forcefully marks a quiz or a section as completed.
	 * @param Integer $userId User ID.
	 * @param Integer $sectionId Section ID.
	 * @return Boolean True indicating success, false indicating failure.
	 */
	private function forceQuizCompleted($userId, $sectionId = -1) {
		$updateQuery = "UPDATE `quiz_userattempts` SET `quiz_submissiontime` = NOW() WHERE `quiz_submissiontime` IS NULL AND `page_modulecomponentid` = '{$this->quizId}' AND `user_id` = '$userId'";
		if ($sectionId >= 0)
			$updateQuery .= " AND `quiz_sectionid` = '$sectionId'";
		if (!mysql_query($updateQuery)) {
			displayerror('Database Error. Could not mark quiz as completed.');
			return false;
		}
		return true;
	}
};
