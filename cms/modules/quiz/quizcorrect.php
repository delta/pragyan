<?php
/*
 * Created on Jan 23, 2009
 */

function isQuizEvaluated($quizId) {
	$countQuery = "SELECT COUNT(*) FROM `quiz_userattempts` WHERE `quiz_marksallotted` IS NULL AND `page_modulecomponentid` = $quizId";
	$countResult = mysql_query($countQuery);
	$countRow = mysql_fetch_row($countResult);
	return $countRow[0] == 0;
}

/**
 * For every user who has taken a quiz, updates marks allotted for all questions the user has submitted (except subjective questions) in quiz_answersubmissions
 * and updates total scores in quiz_userattempts.
 * @return Boolean True indicating success, and false indicating errors.
 */
function evaluateQuiz($quizId) {
	$updateQuery = <<<UPDATEQUERY
		UPDATE `quiz_answersubmissions` SET `quiz_marksallotted` = (
			SELECT 
				IF(`quiz_submittedanswer` IS NULL OR `quiz_submittedanswer` = '', 0, 
					IF(`ques`.`quiz_questiontype` = 'subjective', `quiz_answersubmissions`.`quiz_marksallotted`, 
						IF(`quiz_submittedanswer` = `quiz_rightanswer`, `question_positivemarks`, -`question_negativemarks`)
					)
				) AS `mark`
			FROM `quiz_questions` AS `ques`, (SELECT * FROM `quiz_answersubmissions`) AS `ans`, `quiz_weightmarks` AS `weights`
			WHERE
				`ques`.`page_modulecomponentid` = $quizId AND
				`ans`.`page_modulecomponentid` = $quizId AND
				`weights`.`page_modulecomponentid` = $quizId AND
				`weights`.`question_weight` = `ques`.`quiz_questionweight` AND
				`ques`.`quiz_sectionid` = `ans`.`quiz_sectionid` AND 
				`ques`.`quiz_questionid` = `ans`.`quiz_questionid` AND 
				`ques`.`quiz_questiontype` IN ('sso', 'mso') AND
				`ques`.`quiz_sectionid` = `quiz_answersubmissions`.`quiz_sectionid` AND 
				`ques`.`quiz_questionid` = `quiz_answersubmissions`.`quiz_questionid` AND
				`ans`.`user_id` = `quiz_answersubmissions`.`user_id`
		)
		WHERE `page_modulecomponentid` = $quizId
UPDATEQUERY;

	$updateResult = mysql_query($updateQuery);
	if (!$updateResult) {
		displayerror('Database Error. Could not correct questions.');
		return false;
	}

	$updateQuery = "UPDATE `quiz_userattempts` SET `quiz_marksallotted` = (" .
			"SELECT SUM(`quiz_marksallotted`) FROM `quiz_answersubmissions` WHERE " .
			"`quiz_answersubmissions`.`user_id` = `quiz_userattempts`.`user_id` AND " .
			"`quiz_answersubmissions`.`quiz_sectionid` = `quiz_userattempts`.`quiz_sectionid` AND " .
			"`quiz_answersubmissions`.`page_modulecomponentid` = $quizId" .
			") WHERE `page_modulecomponentid` = $quizId";
	$updateResult = mysql_query($updateQuery);

	if (!$updateResult) {
		displayerror('Database Error. Could not update section marks. ' . $updateQuery . ' ' . mysql_error());
		return false;
	}

	return true;
}

function getQuizUserListHtml($quizId) {
	// Evaluate the quiz,
	// retrieve list of users and their total marks, and display
	$quizRow = getQuizRow($quizId);

	if (!isQuizEvaluated($quizId))
		evaluateQuiz($quizId);

	$userTable = MYSQL_DATABASE_PREFIX . 'users';
	$markQuery = "SELECT `$userTable`.`user_email` AS `email`, `$userTable`.`user_id` AS `user_id`, SUM(`quiz_marksallotted`) AS `total`, MIN(`quiz_attemptstarttime`) AS `starttime`, MAX(`quiz_submissiontime`) AS `finishtime`, TIMEDIFF(MAX(`quiz_submissiontime`), MIN(`quiz_attemptstarttime`)) AS `timetaken` FROM `$userTable`, `quiz_userattempts` WHERE " .
			"`$userTable`.`user_id` = `quiz_userattempts`.`user_id` AND " .
			"`quiz_userattempts`.`page_modulecomponentid` = $quizId " .
			"GROUP BY `quiz_userattempts`.`user_id` ORDER BY `total` DESC, `timetaken`, `starttime`, `finishtime`, `email`";
	$markResult = mysql_query($markQuery);
	if (!$markResult) {
		displayerror($markQuery . '  ' . mysql_error());
	}

	$userListHtml = "<table border=\"0\" cellpadding=\"4\" cellspacing=\"4\">\n" .
		"<tr><th>User Email</th><th>Marks</th><th>Time Taken</th><th>Started</th><th>Finished</th><th></th></tr>\n";
	while ($markRow = mysql_fetch_assoc($markResult)) {
		if (is_null($markRow['finishtime'])) {
			$markRow['finished'] = 0;
			$markRow['finishtime'] = 'NULL';
		}
		elseif ($markRow['timetaken'] > $quizRow['quiz_testduration'])
			$markRow['finished'] = 1;

		$userListHtml .= "<tr><td><a href=\"./+correct&useremail={$markRow['email']}\">{$markRow['email']}</a></td><td>{$markRow['total']}</td><td>{$markRow['timetaken']}</td><td>{$markRow['starttime']}</td><td>{$markRow['finishtime']}</td><td>";
		if ($markRow['finished'])
			$userListHtml .= '<form name="userclearform" method="POST" action=""><input type="hidden" name="hdnUserId" id="hdnUserId" value="' . $markRow['user_id'] . '" /><input type="submit" name="btnDeleteUser" id="btnDeleteUser" value="Clear Entries" /></form>';
		$userListHtml .= "</td></tr>\n";
	}
	$userListHtml .= "</table>\n";

	return $userListHtml;
}

function getQuizCorrectForm($quizId, $userId) {
	$questionQuery = "SELECT `quiz_questions`.`quiz_sectionid` AS `quiz_sectionid`, `quiz_questions`.`quiz_questionid` AS `quiz_questionid`, " .
			"`quiz_questions`.`quiz_question` AS `quiz_question`, `quiz_questiontype`, " .
			"`quiz_rightanswer`, `quiz_submittedanswer`, `quiz_marksallotted` " .
			"FROM `quiz_questions`, `quiz_answersubmissions` WHERE " .
			"`quiz_questions`.`page_modulecomponentid` = $quizId AND " .
			"`quiz_questions`.`page_modulecomponentid` = `quiz_answersubmissions`.`page_modulecomponentid` AND " .
			"`quiz_questions`.`quiz_sectionid` = `quiz_answersubmissions`.`quiz_sectionid` AND " .
			"`quiz_questions`.`quiz_questionid` = `quiz_answersubmissions`.`quiz_questionid` AND " .
			"`user_id` = $userId ORDER BY `quiz_answersubmissions`.`quiz_questionrank`";
	$questionResult = mysql_query($questionQuery);

	if (!$questionResult)
		displayerror($questionQuery . '<br />' . mysql_error());
	$correctFormHtml = '';
	while ($questionRow = mysql_fetch_assoc($questionResult)) {
		$correctFormHtml .= '<table class="quiz_' . (is_null($questionRow['quiz_marksallotted']) || floatval($questionRow['quiz_marksallotted']) <= 0 ? 'wrong' : 'right') . "answer\"><tr><td colspan=\"2\">{$questionRow['quiz_question']}</td></tr>\n";
		if ($questionRow['quiz_questiontype'] == 'subjective') {
			$submittedAnswers = array();
			$submittedAnswers[] = $questionRow['quiz_submittedanswer'];
			$correctAnswers = array();
			$correctAnswers[] = $questionRow['quiz_rightanswer'];
			$correctFormHtml .= '<tr><td nowrap="nowrap" width="10%">Submitted Answer:</td><td>'.implode("<br />\n", $submittedAnswers) . "</td></tr>\n";
			$correctFormHtml .= '<tr><td nowrap="nowrap" width="10%">Correct Answer:</td><td>'.implode("<br />\n", $correctAnswers) . "</td></tr>\n"; 
		}
		elseif ($questionRow['quiz_questiontype'] == 'sso' || $questionRow['quiz_questiontype'] == 'mso') {
			$optionList = getQuestionOptionList($quizId, $questionRow['quiz_sectionid'], $questionRow['quiz_questionid']);
			$options = array();
			for ($i = 0; $i < count($optionList); ++$i)
				$options[$optionList[$i]['quiz_optionid']] = $optionList[$i];

			$correctAnswers = array();
			$rightAnswerIds = explode('|', $questionRow['quiz_rightanswer']);
			for ($i = 0; $i < count($rightAnswerIds); ++$i)
				$correctAnswers[] = $options[$rightAnswerIds[$i]]['quiz_optiontext'];

			$submittedAnswers = array();
			$submittedAnswerIds = explode('|', $questionRow['quiz_submittedanswer']);
			for ($i = 0; $i < count($submittedAnswerIds); ++$i)
				$submittedAnswers[] = $options[$submittedAnswerIds[$i]]['quiz_optiontext'];

			$correctFormHtml .= '<tr><td nowrap="nowrap" width="10%">Submitted Answer:</td><td>' . implode("<br />\n", $submittedAnswers) . "</td></tr>\n";
			$correctFormHtml .= "<tr><td nowrap=\"nowrap\" width=\"10%\">Correct Answer:</td><td>" . implode("<br />\n", $correctAnswers) . "</td></tr>\n";
		}
		$correctFormHtml .= "</table>\n";
	}
	return $correctFormHtml;
}
