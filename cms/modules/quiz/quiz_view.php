<?php
/*
 * Created on Jan 2, 2008, 8:56:49 PM
 *
 * abhilash
 */

/// GENERAL ALGORITHM:
///		Check for questions shown to a person in the `quiz_submittedanswers` table
///		by looking for rows with submittedanswer, and answersubmittime fields NULL
///		If such questions don't exist, disregard any POST information, just show
///		'quiz_questionsperpage' questions.
///		If such questions exist, check if the user is trying to submit answers to exactly
///		these questions.
///		If not, disregard any POST information, just show these questions.
///		If yes, continue with evaluation / submission process, and generate the next page
///		of questions or show `quiz_submittext` if the quiz is over.


///////////////////////////////////////////////////////////////////////////////
///                                                                         ///
/// General Quiz Module Functions                                           ///
///                                                                         ///
///////////////////////////////////////////////////////////////////////////////

/**
 * Return HTML for a given objective question
 * @param $moduleComponentId Id of the quiz to which the question belongs
 * @param $questionId Id of the question
 * @param $questionTitle Title of the question
 * @param $questionText Body of the question
 * @param $questionType String specifying whether the objective question is 'singleselectobjective' or 'multiselectobjective'
 * @return String containing Html for the question
 */
function getObjectiveQuestionHtml($moduleComponentId, $questionId, $questionTitle, $questionText, $questionType, $questionNumber) {
	$optionsQuery = 'SELECT `quiz_questionoptionid`, `quiz_questionoption` FROM `quiz_objectiveoptions` WHERE ' .
			"`page_modulecomponentid` = $moduleComponentId AND `quiz_questionid` = $questionId ORDER BY `quiz_questionoptionrank` ASC";
	$optionsResult = mysql_query($optionsQuery);
	global $questionNumber;
	$questionNumber++;
	$questionHtml = "$questionNumber) ";
	if($questionTitle != '') $questionHtml .= "<font size=\"+1\"><b>$questionTitle</b></font>\n<br />";
	$questionHtml .= $questionText;
	$inputType = $questionType == 'singleselectobjective' ? 'radio' : 'checkbox';
	$inputTypeSuffix = $inputType == 'radio' ? '' : '[]';

	while($optionsRow = mysql_fetch_assoc($optionsResult)) {
		$questionHtml .= "<br /><div class=\"quizSingleOptions\"><label><input type=\"$inputType\" name=\"answerField{$questionId}{$inputTypeSuffix}\" " .
				"value=\"{$optionsRow['quiz_questionoptionid']}\" />{$optionsRow['quiz_questionoption']}</label></div>\n";
	}
	return $questionHtml."<br /><hr />";
}

/**
 * Return HTML for a given subjective question
 * @param $questionId Id of the question
 * @param $questionTitle Title of the question
 * @param $questionText Body of the question
 * @param $answerMaxLength Maximum length of the answer that the user can type in
 * @return String containing Html for the question
 */
function getSubjectiveQuestionHtml($questionId, $questionTitle, $questionText, $answerMaxLength, $questionNumber) {
	/// TODO: Implement $answerMaxLength
	global $questionNumber;
	$questionNumber++;
	$questionHtml = "$questionNumber) ";
	if($questionTitle != '') $questionHtml .= "<h3>$questionTitle</h3>\n<br />";
	$questionHtml .= $questionText;
	$questionHtml .=<<<QUESTIONHTML
		<br /><br /><textarea name="answerField{$questionId}" rows="5" cols="50"></textarea>
QUESTIONHTML;
	return $questionHtml."<br /><hr />";

}

/**
 * Return HTML for a set of objective questions retrieved as the result of a mysql query
 * @param $moduleComponentId Id of the quiz
 * @param $resourceId The resource Id returned as a result of the query
 * @param array $selectedQuestionIds Array to be filled with the Ids of the questions selected
 * @return string Html generated for the questions retrieved
 */
function getQuestionsHtmlFromMysqlResource($moduleComponentId, $resourceId, &$selectedQuestionIds, $questionNumberOffset) {
	if(!is_array($selectedQuestionIds))
		$selectedQuestionIds = array();
	$htmlOut = '';

	$i = $questionNumberOffset;
	while($questionRow = mysql_fetch_assoc($resourceId)) {
		if($questionRow['quiz_questiontype'] == 'subjective') {
			$htmlOut .=
					'<br /><br />' .
					getSubjectiveQuestionHtml (
							$questionRow['quiz_questionid'], $questionRow['quiz_questiontitle'],
							$questionRow['quiz_question'], $questionRow['quiz_answermaxlength'], $i
					);
		}
		else {
			$htmlOut .=
					'<br /><br />' .
					getObjectiveQuestionHtml(
							$moduleComponentId,
							$questionRow['quiz_questionid'], $questionRow['quiz_questiontitle'],
							$questionRow['quiz_question'], $questionRow['quiz_questiontype'], $i
					);
		}

		$selectedQuestionIds[] = $questionRow['quiz_questionid'];
		$i++;
	}

	return $htmlOut;
}

/**
 * Return HTML for a set of objective questions provided as an array of question Ids
 * @param $moduleComponentId Id of the quiz
 * @param $questionIdArray Array containing the question Ids of the questions to be retrieved
 * @return string Html generated for the questions retrieved
 */
function getQuestionsHtmlFromIdArray($moduleComponentId, array $questionIdArray, $questionNumberOffset, $questionGrouping = '') {
	if(!is_array($questionIdArray) || count($questionIdArray) <= 0) return '';

	foreach( $questionIdArray as $qid) {
		$questionQuery = 'SELECT `quiz_questionid`, `quiz_questiontitle`, `quiz_question`, ' .
				'`quiz_questiontype`, `quiz_answermaxlength` FROM `quiz_questions` WHERE ' .
				"`page_modulecomponentid` = $moduleComponentId AND `quiz_questionid` = $qid";
		if($questionGrouping == 'subjectivefirst')
			$questionQuery .= ' ORDER BY `quiz_questiontype`';
		else if($questionGrouping == 'objectivefirst')
			$questionQuery .= ' ORDER BY `quiz_questiontype` DESC';

		$questionResult = mysql_query($questionQuery);
		$selectedQuestionIds = array();
		$returnHtml .=getQuestionsHtmlFromMysqlResource($moduleComponentId, $questionResult, $selectedQuestionIds, $questionNumberOffset);
	}
	return $returnHtml;
}

/**
 * Remove all information about a user's quiz attempt. To be used only in case of fatal errors
 * @param $moduleComponentId Id of the quiz
 * @param $userId Id of the user
 */
function removeQuizAttemptData($moduleComponentId, $userId) {
	/// 2 DELETE FROM queries to remove all references to the user w.r.t. the given quiz

	$deleteQuery = "DELETE FROM `quiz_submittedanswers` WHERE `page_modulecomponentid` = $moduleComponentId AND `user_id` = $userId";
	mysql_query($deleteQuery) or die(mysql_error());
	$deleteQuery = "DELETE FROM `quiz_quizattemptdata` WHERE `page_modulecomponentid` = $moduleComponentId AND `user_id` = $userId";
	mysql_query($deleteQuery) or die(mysql_error());
}

/**
 * Saves data from a page of a quiz submitted by the user
 * @param $moduleComponentId Id of the quiz
 * @param $userId Id of the user
 * @param $questionIds Array containing the Ids of the questions given to the user, the answers to which he has submitted
 * @return Boolean True indicating success, false indicating failure
 */
function submitQuizViewForm($moduleComponentId, $userId, array $questionIds) {
	$questionCount = count($questionIds);

	for($i = 0; $i < $questionCount; $i++) {
		$postVarName = 'answerField' . $questionIds[$i];
		$submittedAnswer = '';
		if(!isset($_POST[$postVarName])) $submittedAnswer = '';
		elseif(is_array($_POST[$postVarName])) {
			$submittedAnswer = join($_POST[$postVarName], ' | ');
		}
		else {
			$submittedAnswer = $_POST[$postVarName];
		}

		$updateQuery = "UPDATE `quiz_submittedanswers` SET `quiz_submittedanswer` = '$submittedAnswer', `quiz_answersubmittime` = NOW() " .
				"WHERE `page_modulecomponentid` = $moduleComponentId AND `quiz_questionid` = {$questionIds[$i]} AND `user_id` = $userId";

		if(!mysql_query($updateQuery)) {
			$rollbackQuery = "UPDATE `quiz_submittedanswers` SET `quiz_submittedanswer` = NULL, `quiz_answersubmittime` = NULL WHERE " .
					"`page_modulecomponentid` = $moduleComponentId AND `user_id` = $userId AND `quiz_questionid` IN (" . join($questionIds, ', ') . ')';
			mysql_query($rollbackQuery);

			displayerror('An unknown error occurred while trying to update the database. L179');
			return false;
		}
	}

	return true;
}

/**
 * Counts the number of questions attempted by a user in a given quiz so far
 * @param $moduleComponentId Id of the quiz
 * @param $userId Id of the user
 * @param $questionType String containing the type of the questions to be counted; if left blank, all questions are counted
 * @return Integer representing the number of questions attempted by the user
 */
function getUserAttemptedQuestionCount($moduleComponentId, $userId, $questionType = '') {
	if($questionType != '') {
		if($questionType == 'objective') {
			$questionType = 'singleselectobjective\' OR `quiz_questiontype`=\'multiselectobjective';
		}
		$questionQuery = 'SELECT COUNT(*) FROM `quiz_submittedanswers`, `quiz_questions` WHERE ' .
				'`quiz_submittedanswers`.`page_modulecomponentid` = ' . $moduleComponentId .
				" AND `user_id` = $userId AND (`quiz_questiontype` = '$questionType') AND " .
				"`quiz_questions`.`page_modulecomponentid` = `quiz_submittedanswers`.`page_modulecomponentid` AND " .
				"`quiz_questions`.`quiz_questionid` = `quiz_submittedanswers`.`quiz_questionid`";
	}
	else {
		$questionQuery = 'SELECT COUNT(*) FROM `quiz_submittedanswers` WHERE `page_modulecomponentid` = ' . $moduleComponentId .
				" AND `user_id` = $userId";
	}

	$questionResult = mysql_query($questionQuery) or die($questionQuery . ' <br /> ' . mysql_error());
	$questionRow = mysql_fetch_row($questionResult);
	return $questionRow[0];
}

function getQuizTimerStartValue($moduleComponentId, $userId, $timerType) {
	if($timerType == 'quiz') {
		$query = 'SELECT TIME_FORMAT(TIMEDIFF(NOW(), `quiz_starttime`), \'%k %i %s\') FROM `quiz_quizattemptdata` WHERE `page_modulecomponentid` = ' . $moduleComponentId . ' AND `user_id` = ' . $userId;
	}
	else if($timerType == 'page') {
		$query = 'SELECT TIME_FORMAT(TIMEDIFF(NOW(), MAX(`quiz_questionviewtime`)), \'%k %i %s\') FROM `quiz_submittedanswers` WHERE `page_modulecomponentid` = ' . $moduleComponentId . ' AND `user_id` = ' . $userId;
	}
	else return '';

	$result = mysql_query($query);
	$row = mysql_fetch_row($result);
	return $row[0];
}

function getQuizTimerStopValue($moduleComponentId) {
  $query = 'SELECT TIME_FORMAT(`quiz_testduration`, \'%k %i\') FROM `quiz_descriptions` WHERE `page_modulecomponentid` = ' . $moduleComponentId;
  $result = mysql_query($query);
  $row = mysql_fetch_row($result);

  return $row[0];
}

/**
 * Checks whether a quiz has been completed by a user
 * @param $moduleComponentId Id of the quiz
 * @param $userId Id of the user
 * @return Boolean, true indicating that the quiz has been completed, and false indicating otherwise
 */
function checkQuizCompleted($moduleComponentId, $userId) {
	$checkQuery = "SELECT `quiz_submittime` IS NULL FROM `quiz_quizattemptdata` WHERE `page_modulecomponentid` = $moduleComponentId AND `user_id` = $userId";
	$checkResult = mysql_query($checkQuery);
	if(!$checkResult) return false;
	$checkRow = mysql_fetch_row($checkResult);
	return $checkRow && $checkRow[0] == 0;
}

/**
 * Marks a quiz as completed by a user
 * @param $moduleComponentId Id of the quiz
 * @param $userId Id of the user
 */
function setQuizCompleted($moduleComponentId, $userId) {
	$updateQuery = "UPDATE `quiz_quizattemptdata` SET `quiz_submittime` = NOW() WHERE `page_modulecomponentid` = $moduleComponentId AND `user_id` = $userId";
	mysql_query($updateQuery);
}


///////////////////////////////////////////////////////////////////////////////
///                                                                         ///
/// General Functions End Here                                              ///
///                                                                         ///
///                                                                         ///
/// Quiz-type Specific Functions Follow                                     ///
///                                                                         ///
///////////////////////////////////////////////////////////////////////////////


/**
 * Generates HTML for a set of random questions
 * @param $moduleComponentId Id of the quiz
 * @param $userId Id of the user viewing the quiz
 * @param $questionCount Number of random questions to be generated
 * @param $objectiveMinCount Minimum number of objective questions to include in the set
 * @param $objectiveMaxCount Maximum number of objective questions to include in the set
 * @param $firstView Boolean indicating whether the user is viewing the quiz for the first time. If set to true, inserts appropriate rows into quiz_attemptdata
 * @return String containing HTML generated for the random questions selected
 */
function randomizeQuestionsForFirstAttempt($moduleComponentId, $userId, $questionCount, $questionNumberOffset, $objectiveMinCount = -1, $objectiveMaxCount = -1) {
	$selectedQuestionIds = array();
	if($objectiveMinCount > $objectiveMaxCount && $objectiveMaxCount != -1) {
		displayerror('Invalid inputs to function getRandomQuestionsHtml().');
		return '';
	}

	/// TODO: Document crazy math here!
	if($objectiveMinCount > -1) {
		$questionQuery = 'SELECT `quiz_questionid`, `quiz_questiontitle`, `quiz_question`, `quiz_questiontype`, ' .
				'`quiz_answermaxlength` FROM `quiz_questions` WHERE  ' .
				"`page_modulecomponentid` = $moduleComponentId AND " .
				"(`quiz_questiontype` = 'singleselectobjective' OR `quiz_questiontype` = 'multiselectobjective') AND " .
				"`quiz_questionid` NOT IN (SELECT `quiz_questionid` FROM `quiz_submittedanswers` WHERE " .
				"`page_modulecomponentid` = $moduleComponentId AND `user_id` = $userId) ORDER BY RAND() LIMIT $objectiveMinCount";
		$questionResult = mysql_query($questionQuery);
		while($questionRow = mysql_fetch_assoc($questionResult))
			$selectedQuestionIds[] = $questionRow['quiz_questionid'];
		if(mysql_num_rows($questionResult) != $objectiveMinCount) {
			displayerror('This quiz has not been properly set up with sufficient number of questions.');
			return '';
		}
		$questionNumberOffset += $objectiveMinCount;
		$questionCount -= $objectiveMinCount;
		$objectiveMaxCount -= $objectiveMinCount;
	}
	if($objectiveMaxCount > -1) {
		$questionQuery = 'SELECT `quiz_questionid`, `quiz_questiontitle`, `quiz_question`, `quiz_questiontype`, ' .
				'`quiz_answermaxlength` FROM `quiz_questions` WHERE ' .
				"`page_modulecomponentid` = $moduleComponentId AND `quiz_questiontype` = 'subjective' AND " .
				"`quiz_questionid` NOT IN (SELECT `quiz_questionid` FROM `quiz_submittedanswers` WHERE " .
				"`page_modulecomponentid` = $moduleComponentId AND `user_id` = $userId) ";
		if(count($selectedQuestionIds) > 0) {
			$questionQuery .= ' AND `quiz_questionid` NOT IN (' . join($selectedQuestionIds, ', ') . ') ';
		}
		$questionQuery .= "ORDER BY RAND() LIMIT " . ($questionCount - $objectiveMaxCount);
		$questionResult = mysql_query($questionQuery);
		while($questionRow = mysql_fetch_assoc($questionResult))
			$selectedQuestionIds[] = $questionRow['quiz_questionid'];
		if(mysql_num_rows($questionResult) != $questionCount - $objectiveMaxCount) {
			displayerror('This quiz has not been properly set up with sufficient number of questions.');
			return '';
		}
		$questionCount = $objectiveMaxCount;
		$questionNumberOffset += count($selectedQuestionIds);
	}

	$questionQuery = 'SELECT `quiz_questionid`, `quiz_questiontitle`, `quiz_question`, ' .
			'`quiz_questiontype`, `quiz_answermaxlength` FROM `quiz_questions` WHERE ' .
			"`page_modulecomponentid` = $moduleComponentId AND `quiz_questionid` NOT IN " .
			'(SELECT `quiz_questionid` FROM `quiz_submittedanswers` WHERE ' .
			"`page_modulecomponentid` = $moduleComponentId AND `user_id` = $userId) ";
	if(count($selectedQuestionIds) > 0) {
		$questionQuery .= ' AND `quiz_questionid` NOT IN (' . join($selectedQuestionIds, ', ') . ') ';
	}
	$questionQuery .= "ORDER BY RAND() LIMIT $questionCount";
	$questionResult = mysql_query($questionQuery);
	while($questionRow = mysql_fetch_assoc($questionResult))
		$selectedQuestionIds[] = $questionRow['quiz_questionid'];
	if(mysql_num_rows($questionResult) != $questionCount) {
		displayerror('This quiz has not been properly set up with sufficient number of questions.');
		return '';
	}

	for($i = 0; $i < count($selectedQuestionIds); $i++) {
		$selectedQuestionIds[$i] = "($moduleComponentId, {$selectedQuestionIds[$i]}, $userId)";
	}

	$insertQuery = 'INSERT INTO `quiz_submittedanswers` ' .
				'(`page_modulecomponentid`, `quiz_questionid`, `user_id`) ' .
				'VALUES ' . join($selectedQuestionIds, ', ');

	if(!mysql_query($insertQuery)) {
		displayerror('An unknown error was encountered while trying to update quiz information. L389.');
		removeQuizAttemptData($moduleComponentId, $userId);
		return '';
	}

	$insertQuery = 'INSERT INTO `quiz_quizattemptdata` (`page_modulecomponentid`, `user_id`, `quiz_starttime`) ' .
				"VALUES($moduleComponentId, $userId, NOW())";
	mysql_query($insertQuery);

	return true;
}

/**
 * Calculates the minimum and maximum number of objective questions to be included in a quiz
 * @param $questionsPerPage Number of questions to be shown per page in the quiz
 * @param $questionsRemaining Number of questions remaining in the test
 * @param $objectiveRemaining Number of objective questions remaining
 * @param $questionGrouping String containing either 'shuffle', 'objectivefirst', or 'subjectivefirst'; assumes shuffle if any other value is provided
 * @param $objectiveMaxCount Output parameter, to be filled with the maximum number of objective questions that can be included in the page
 * @param $objectiveMinCount Output parameter, to be filled with the minimum number of objective questions that can be included in the page
 */
function calculateObjectiveCount($questionsPerPage, $questionsRemaining, $objectiveRemaining, $questionGrouping, &$objectiveMaxCount, &$objectiveMinCount) {
	$subjectiveRemaining = $questionsRemaining - $objectiveRemaining;

	switch($questionGrouping) {

	case 'objectivefirst':
	  /// if objectives Remain, choose all objective
	  if($objectiveRemaining > 0) {
	  	$objectiveMinCount = min($questionsPerPage, $objectiveRemaining);
	  	$objectiveMaxCount = min($questionsPerPage, $objectiveRemaining);
	  }
	  else {
	  	$objectiveMinCount = $objectiveMaxCount = 0;
	  }
	break;

	case 'subjectivefirst':
		/// No more subjectives remaining
		if($subjectiveRemaining > 0) {
			$objectiveMinCount = $objectiveMaxCount = ($questionsPerPage - $subjectiveRemaining);
			if($objectiveMinCount < 0) {
				$objectiveMinCount = $objectiveMaxCount = 0;
			}
		}
		else {
			$objectiveMinCount = $objectiveMaxCount = min($questionsPerPage, $questionsRemaining);
		}
	break;

	default:
		if($objectiveRemaining <= 0) {
			$objectiveMinCount = $objectiveMaxCount = 0;
		}
		else {
			/// SELECT a minimum of objectives such that after this page, the number of objectives remaining will be no
			/// greater than the number of questions available
			$objectiveMinCount = max($objectiveRemaining - ($questionsRemaining - $questionsPerPage), -1);
			$objectiveMaxCount = min($objectiveRemaining, $questionsPerPage);
		}
	}
}

/**
 * Checks if the quiz has timed out for a particular user.
 * @param $moduleComponentId Id of the quiz
 * @param $userId Id of the user
 * @param $testDuration Duration of the quiz
 * @return Boolean, true indicating that the quiz has timed out, false indicating otherwise.
 */
function checkUserTimedOut($moduleComponentId, $userId, $testDuration) {
	if(is_null($testDuration)) {
		return false;
	}
	$timeOutQuery = "SELECT NOW() > DATE_ADD(`quiz_starttime`, INTERVAL '$testDuration' HOUR_SECOND) FROM `quiz_quizattemptdata` WHERE `page_modulecomponentid` = $moduleComponentId AND `user_id` = $userId";
	$timeOutResult = mysql_query($timeOutQuery);
	$timeOutRow = mysql_fetch_row($timeOutResult);
	if(!$timeOutRow) return false; /// if the user is viewing the quiz for the first time, he won't have a row in the table
	return $timeOutRow[0];
}

/**
 * Generate appropriate HTML to be served to a user trying to view a Simple Quiz
 * @param $moduleComponentId Id of the quiz
 * @param $userId Id of the user trying to view the quiz
 * @param $quizDescRow Row from `quiz_descriptions` corresponding to that particular quiz
 * @return String containing HTML for the quiz view
 */
function getSimpleQuizView($moduleComponentId, $userId, &$quizDescRow) {
	if(checkQuizCompleted($moduleComponentId, $userId)) {
		displayinfo('You have already attempted this quiz. You can only attempt a quiz once.');
		return '';
	}

	$hasTimedOut = checkUserTimedOut($moduleComponentId, $userId, $quizDescRow['quiz_testduration']);

	$questionQuery = 'SELECT `quiz_questionid` FROM `quiz_submittedanswers` WHERE `page_modulecomponentid` = '. $moduleComponentId .
					" AND `user_id` = $userId AND `quiz_submittedanswer` IS NULL AND `quiz_answersubmittime` IS NULL ORDER BY `quiz_submittedanswer`";
	$questionResult = mysql_query($questionQuery);

	$questionsPerPage = is_null($quizDescRow['quiz_questionsperpage']) ? $quizDescRow['quiz_questionspertest'] : $quizDescRow['quiz_questionsperpage'];
	if($questionsPerPage > $quizDescRow['quiz_questionspertest']) $questionsPerPage = $quizDescRow['quiz_questionspertest'];
	$attemptedCount = getUserAttemptedQuestionCount($moduleComponentId, $userId);

	$quizForm = '';

	if(mysql_num_rows($questionResult) == 0 && $attemptedCount == 0) {
		$objectiveMinCount = $objectiveMaxCount = -1;
		randomizeQuestionsForFirstAttempt($moduleComponentId, $userId, $questionsPerPage, 1, $objectiveMinCount, $objectiveMaxCount);
	}

	$questionResult2 = mysql_query($questionQuery);
	//$attemptedCount = getUserAttemptedQuestionCount($moduleComponentId, $userId);
	$questionIds = array();
	while($questionRow = mysql_fetch_row($questionResult2)) {
		$questionIds[] = $questionRow[0];
	}
	if(!isset($_POST['quiz_submitted'])) {
		/// The user left the quiz for some reason, and has come back to it
		/// Show the last seen set of questions.
		if($hasTimedOut) {
			displayinfo('Your quiz has timed out. The answers you have submitted so far have been recorded. Thank you for taking the quiz.');
			setQuizCompleted($moduleComponentId, $userId);
			return '';
		}
		$quizForm = getQuestionsHtmlFromIdArray($moduleComponentId, $questionIds, $attemptedCount - mysql_num_rows($questionResult) + 1, $quizDescRow['quiz_questiongrouping']);
		if($quizForm == '') {
			displayerror('Could not retrieve sufficient number of questions. L489');
			return '';
		}
	}
	else {
		/// The user is submitting a certain page, and trying to view the next page
		/// Check if the question ids in the submitted data match those in the table
		/// If not, the data has been tampered with
		/// Otherwise, submit the data, do necessary evaluation and show the next page
		$isSubmitValid = true;
		global $sourceFolder, $moduleFolder;
		include_once("$sourceFolder/$moduleFolder/quiz/question_edit.php");
		for($i = 0; $i < count($questionIds); $i++) {
			if(!isset($_POST['answerField' . $questionIds[$i]]) && getQuestionType($moduleComponentId, $questionIds[$i]) == 'subjective') {
				displayerror('Invalid submit data.');
				$isSubmitValid = false;
			}
		}

		/// Submit is valid, submit this form, retrieve next page, if it exists,
		/// Otherwise, show the quiz_submittext and return.
		if($isSubmitValid && submitQuizViewForm($moduleComponentId, $userId, $questionIds)) {
			$objectiveRemaining = $quizDescRow['quiz_objectivecount'] - getUserAttemptedQuestionCount($moduleComponentId, $userId, 'objective');
			if($objectiveRemaining < 0) $objectiveRemaining = 0;

			$objectiveMinCount = $objectiveMaxCount = -1;
			if($attemptedCount < $quizDescRow['quiz_questionspertest']) {
				if($hasTimedOut) {
					displayinfo('Your quiz has timed out. The answers you have submitted so far have been recorded. Thank you for taking the quiz.');
					setQuizCompleted($moduleComponentId, $userId);
					return '';
				}

				$questionCount =
					min($quizDescRow['quiz_questionspertest'] - $attemptedCount, $questionsPerPage);

				if(!is_null($quizDescRow['quiz_objectivecount'])) {
					calculateObjectiveCount(
							$questionsPerPage, $quizDescRow['quiz_questionspertest'] - $attemptedCount,
							$objectiveRemaining, $quizDescRow['quiz_questiongrouping'],
							$objectiveMaxCount, $objectiveMinCount
					);
				}
/**TODO: need to change this, random questions decided in the beginning only. Now only to show the remaining questions.
 *
 */
				$quizForm = getRandomQuestionsHtml($moduleComponentId, $userId, $questionCount, $attemptedCount + 1, $objectiveMinCount, $objectiveMaxCount);
				if($quizForm == '') return '';
			}
			else {
				setQuizCompleted($moduleComponentId, $userId);
				return $quizDescRow['quiz_submittext']; /// No need for the form action and stuff, just return the submit text
			}
		}
		/// Submit validation failed; show earlier questions
		else {
			if($hasTimedOut) {
				displayinfo('Your quiz has timed out. The answers you have submitted so far have been recorded. Thank you for taking the quiz.');
				setQuizCompleted($moduleComponentId, $userId);
				return '';
			}
			$quizForm = getQuestionsHtmlFromIdArray($moduleComponentId, $questionIds, $attemptedCount + 1, $quizDescRow['quiz_questiongrouping']);
		}
	}

	$quizFormFull = '<form name="quizform" method="POST" action="./+view">';
	$jsTimers = '';


	if($quizDescRow['quiz_showtesttimer'] == 1) {
		$timerStopValue = getQuizTimerStopValue($moduleComponentId);
		$timerStopValue = explode(' ', $timerStopValue);
		$quizFormFull .= '<br /><div class="quiztimer">Quiz time elapsed (out of '.join($timerStopValue,":").":00".'): <span id="quizTimerContainer"></span></div>';

		if(!is_null($quizDescRow['quiz_testduration'])) {
			$timerStartValue = getQuizTimerStartValue($moduleComponentId, $userId, 'quiz');
			$timerStartValue = explode(' ', $timerStartValue);

			$jsTimers = "quizTimer = new JSTimer(document.getElementById('quizTimerContainer'), {$timerStartValue[0]}, {$timerStartValue[1]}, {$timerStartValue[2]});\n";
			$jsTimers .= "quizTimer.onStopTimeReached = function() { showForceSubmitDiv(); }\n" .
				"quizTimer.setStopTime(" . (int)$timerStopValue[0] . ", " . (int)$timerStopValue[1] . ", 0);\n";
		}
	}
	elseif(!is_null($quizDescRow['quiz_testduration'])) {
		$timerStartValue = getQuizTimerStartValue($moduleComponentId, $userId, 'quiz');
		$timerStartValue = explode(' ', $timerStartValue);
		$timerStopValue = getQuizTimerStopValue($moduleComponentId);
		$timerStopValue = explode(' ', $timerStopValue);
		$jsTimers = "quizTimer = new JSTimer(document.createElement('span'), {$timerStartValue[0]}, {$timerStartValue[1]}, {$timerStartValue[2]});\n" .
			"quizTimer.onStopTimeReached = function() { showForceSubmitDiv(); }\n" .
			"quizTimer.setStopTime(" . (int)$timerStopValue[0] . ", " . (int)$timerStopValue[1] . ", 0);\n";
	}

	if($quizDescRow['quiz_showpagetimer'] == 1) {
		$quizFormFull .= '<br />Page time elapsed: <span class="quiztimer" id="pageTimerContainer"></span></br />';
		$timerStartValue = getQuizTimerStartValue($moduleComponentId, $userId, 'page');
		$timerStartValue = explode(' ', $timerStartValue);
		$jsTimers .= "pageTimer = new JSTimer(document.getElementById('pageTimerContainer'), {$timerStartValue[0]}, {$timerStartValue[1]}, {$timerStartValue[2]});\n";
	}

	if($jsTimers != '') {
		global $urlRequestRoot, $sourceFolder, $moduleFolder;
		$quizScriptUrl = "$urlRequestRoot/$sourceFolder/$moduleFolder/quiz/timer.js";
		$quizFormFull .= '<script language="javascript" type="text/javascript" src="' . $quizScriptUrl . '"></script>' .
					"<script language=\"javascript\" type=\"text/javascript\">\n".
					"$jsTimers\n</script>";
	}
	$quizFormFull .= $quizForm .
					'<br /><br /><input type="hidden" name="quiz_submitted" value="quiz_submitted" /><input type="submit" name="btnSubmitQuiz" value="Submit" /></form>';
	return $quizFormFull;
}





function getGreRandomQuestionRow($moduleComponentId, $userId, $questionWeight) {
	$questionQuery = 'SELECT * FROM `quiz_questions` WHERE `quiz_questionid` NOT IN (SELECT `quiz_questionid` FROM `quiz_submittedanswers` WHERE `page_modulecomponentid` = ' . $moduleComponentId . ' AND `user_id` = ' . $userId .
		') AND `quiz_questionweight` = ' . $questionWeight . ' ORDER BY RAND() LIMIT 1';
	$questionResult = mysql_query($questionQuery);
	$questionRow = mysql_fetch_assoc($questionRow);
	return $questionRow;
}

function isSubmittedAnswerCorrect($moduleComponentId, $userId, $questionId) {
	$rightAnswerQuery = 'SELECT `quiz_rightanswer` FROM `quiz_questions` WHERE `page_modulecomponentid` = ' . $moduleComponentId . ' AND `quiz_questionid` = ' . $questionId;
	$rightAnswerResult = mysql_query($rightAnswerQuery);
	$rightAnswer = mysql_fetch_row($rightAnswerQuery);
	$rightAnswers = explode('|', $rightAnswer[0]);

	$submittedAnswerQuery = 'SELECT `quiz_submittedanswer` FROM `quiz_submittedanswers` WHERE `page_modulecomponentid` = ' . $moduleComponentId . ' AND `quiz_questionid` = ' . $questionId . ' AND `user_id` = ' . $userId;
	$submittedAnswerResult = mysql_query($submittedAnswerQuery);
	$submittedAnswer = mysql_fetch_row($submittedAnswer);
	if($submittedAnswer) {
		$submittedAnswer = $submittedAnswer[0];

		$submittedAnswers = explode('|', $submittedAnswer[0]);
		if(count($submittedAnswers) == count($rightAnswers)) {
			for($i = 0; $i < count($submittedAnswers); $i++) {
				if(!in_array($submittedAnswers[$i], $rightAnswers))
					return false;
			}
			return true;
		}
	}

	return false;
}


function getQuestionWeight($moduleComponentId, $questionId) {
	$questionQuery = "SELECT `quiz_questionweight` FROM `quiz_questions` WHERE `page_modulecomponentid` = $moduleComponentId AND `quiz_questionid` = $questionId";
	$questionResult = mysql_query($questionQuery);
	$questionRow = mysql_fetch_row($questionResult);
	return $questionRow[0];
}

function getNextQuestionWeight($moduleComponentId, $questionWeight, $dir = 'up') {
	$func = 'MAX';
	$operator = '<';
	if($dir == 'up') {
		$func = 'MIN';
		$operator = '>';
	}
	$questionQuery = "SELECT $func(`quiz_questionweight`) FROM `quiz_questions` WHERE `page_modulecomponentid` = $moduleComponentId AND `quiz_questionweight` $operator $questionWeight";
	$questionResult = mysql_query($questionQuery);
	if($questionRow = mysql_fetch_row($questionQuery) && !is_null($questionRow[0])) {
		return $questionRow[0];
	}
	else return $questionWeight;
}

function getGreQuizView($moduleComponentId, $userId, &$quizDescRow) {
	if(checkQuizCompleted($moduleComponentId, $userId)) {
		displayinfo('You have already attempted this quiz. You can only attempt a quiz once.');
		return '';
	}

	$hasTimedOut = checkUserTimedOut($moduleComponentId, $userId);

	$questionQuery = 'SELECT `quiz_questionid` FROM `quiz_submittedanswers` WHERE `page_modulecomponentid` = '. $moduleComponentId .
			" AND `user_id` = $userId AND `quiz_submittedanswer` IS NULL AND `quiz_answersubmittime` IS NULL";
	$questionResult = mysql_query($questionQuery);

	$attemptedCount = getUserAttemptedQuestionCount($moduleComponentId, $userId);

	$quizForm = '';

	if(mysql_num_rows($questionResult) == 0 && $attemptedCount == 0) {
		/// The user is viewing the first page of the quiz for the first time
		/// Disregard all POST data, just select random questions, and show!
		/// INSERT a quiz_starttime in the quiz_attemptdata table
		$objectiveMinCount = $objectiveMaxCount = -1;

		if($hasTimedOut) {
			displayinfo('Your quiz has timed out. The answers you have submitted so far have been recorded. Thank you for taking the quiz.');
			return '';
		}

		$questionRow = getGreRandomQuestionRow($moduleComponentId, $userId, $quizDescRow['quiz_startweight']);
		if(!$questionRow) {
			displayerror('Error! This quiz has not been properly set up with sufficient number of questions.');
			return '';
		}
		$quizForm = getObjectiveQuestionHtml($moduleComponentId, $questionRow['quiz_questionid'], $questionRow['quiz_questiontitle'], $questionRow['quiz_question'], $questionRow['quiz_questiontype']);
		if($quizForm == '') return '';
	}
	else {
		$questionIds = array();
		if($questionRow = mysql_fetch_row($questionResult)) {
			$questionIds[] = $questionRow[0];
			$questionId = $questionIds[0];
		}

		if(!isset($_POST['quiz_submitted'])) {
			/// The user left the quiz for some reason, and has come back to it
			/// Show the last seen set of questions.
			if($hasTimedOut) {
				displayinfo('Your quiz has timed out. The answers you have submitted so far have been recorded. Thank you for taking the quiz.');
				return '';
			}

			$quizForm = getQuestionsHtmlFromIdArray($moduleComponentId, $questionIds);
			if($quizForm == '') {
				displayerror('Could not retrieve sufficient number of questions. L711');
				return '';
			}
		}
		else {
			/// The user is submitting a certain page, and trying to view the next page
			/// Check if the question ids in the submitted data match those in the table
			/// If not, the data has been tampered with
			/// Otherwise, submit the data, do necessary evaluation and show the next page
			$isSubmitValid = true;
			if(!isset($_POST['answerField' . $questionId])) {
				$isSubmitValid = false;
				displayerror('Invalid submit data');
			}

			/// Submit is valid, submit this form, retrieve next page, if it exists,
			/// Otherwise, show the quiz_submittext and return.
			if($isSubmitValid && submitQuizViewForm($moduleComponentId, $userId, $questionIds)) {
				if($attemptedCount < $quizDescRow['quiz_questionspertest']) {
					if($hasTimedOut) {
						displayinfo('Your quiz has timed out. The answers you have submitted so far have been recorded. Thank you for taking the quiz.');
						setQuizCompleted($moduleComponentId, $userId);
						return '';
					}

					$questionWeight = getQuestionWeight($moduleComponentId, $questionIds[0]);
					$dir = 'down';
					if(isSubmittedAnswerCorrect($moduleComponentId, $userId, $questionId)) {
						$dir = 'up';
					}
					$questionWeight = getNextQuestionWeight($moduleComponentId, $questionWeight, $dir);
					$questionRow = getGreRandomQuestionRow($moduleComponentId, $userId, $questionWeight);

					if(!$questionRow) {
						displayerror('This quiz has not been properly set up with sufficient number of questions.');
						return '';
					}
					$quizForm = getObjectiveQuestionHtml($moduleComponentId, $questionRow['quiz_questionid'], $questionRow['quiz_questiontitle'], $questionRow['quiz_question'], $questionRow['quiz_question']);
					if($quizForm == '') return '';
				}
				else {
					setQuizCompleted($moduleComponentId, $userId);
					return $quizDescRow['quiz_submittext']; /// No need for the form action and stuff, just return the submit text
				}
			}
			/// Submit validation failed; show earlier questions
			else {
				if($hasTimedOut) {
					displayinfo('Your quiz has timed out. The answers you have submitted so far have been recorded. Thank you for taking the quiz.');
					setQuizCompleted($moduleComponentId, $userId);
					return '';
				}
				$quizForm = getQuestionsHtmlFromIdArray($moduleComponentId, $questionIds);
			}
		}
	}

	$quizFormFull = '<form name="quizform" method="POST" action="./+view">';
	$jsTimers = '';

	if($quizDescRow['quiz_showtesttimer'] == 1) {
		$quizFormFull .= '<br />Time elapsed on this test: <span class="quiztimer" id="quizTimerContainer"></span>';
		$timerStartValue = getQuizTimerStartValue($moduleComponentId, $userId, 'quiz');
		$timerStartValue = explode(' ', $timerStartValue);
    $timerStopValue = getQuizTimerStopValue($moduleComponentId, $userId);
		$jsTimers = "quizTimer = new JSTimer(document.getElementById('quizTimerContainer'), {$timerStartValue[0]}, {$timerStartValue[1]}, {$timerStartValue[2]});\n" .
				"quizTimer.onStopTimeReached = showForceSubmitDiv;\n" .
				"quizTimer.setStopTime(0, 2, 0);\n";
	}
	if($quizDescRow['quiz_showpagetimer'] == 1) {
		$quizFormFull .= '<br />Time spent on this page: <span class="quiztimer" id="pageTimerContainer"></span></br />';
		$timerStartValue = getQuizTimerStartValue($moduleComponentId, $userId, 'page');
		$timerStartValue = explode(' ', $timerStartValue);
		$jsTimers .= "pageTimer = new JSTimer(document.getElementById('pageTimerContainer'), {$timerStartValue[0]}, {$timerStartValue[1]}, {$timerStartValue[2]});\n";
	}

	if($jsTimers != '') {
		global $urlRequestRoot, $sourceFolder, $moduleFolder;
		$quizScriptUrl = "$urlRequestRoot/$sourceFolder/$moduleFolder/quiz/timer.js";
		$quizFormFull .= '<script language="javascript" type="text/javascript" src="' . $quizScriptUrl . '"></script>' .
					"<script language=\"javascript\" type=\"text/javascript\">\n".
					"$jsTimers\n</script>";
	}
	$quizFormFull .= $quizForm .
					'<br /><br /><input type="hidden" name="quiz_submitted" value="quiz_submitted" /><input type="submit" name="btnSubmitQuiz" value="Submit" onclick="javascript: alert(submittedOnce)"/></form>';
	return $quizFormFull;
}

?>