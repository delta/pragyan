<?php
if(!defined('__PRAGYAN_CMS'))
{ 
	header($_SERVER['SERVER_PROTOCOL'].' 403 Forbidden');
	echo "<h1>403 Forbidden<h1><h4>You are not authorized to access the page.</h4>";
	echo '<hr/>'.$_SERVER['SERVER_SIGNATURE'];
	exit(1);
}
/*
 * Created on Jan 17, 2009
 */

/**
 * Retrieves the number of questions of each type that have been added to a section.
 * @param integer $quizId Quiz Id.
 * @param integer $sectionId Section Id.
 * @return array An array containing question types, and their corresponding counts.
 */
function getQuestionTypeCounts($quizId, $sectionId) {
	$countQuery = "SELECT `quiz_questiontype`, COUNT(*) FROM `quiz_questions` WHERE `page_modulecomponentid` = '$quizId' AND `quiz_sectionid` = '$sectionId' GROUP BY `quiz_questiontype`";
	$countResult = mysql_query($countQuery);
	$result = array();
	while ($countRow = mysql_fetch_row($countResult))
		$result[$countRow[0]] = $countRow[1];
	$questionTypes = array_keys(getQuestionTypes());
	for ($i = 0; $i < count($questionTypes); ++$i)
		if (!array_key_exists($questionTypes[$i], $result))
			$result[$questionTypes[$i]] = 0;
	return $result;
}

/**
 * Checks whether a quiz has been set up properly with sufficient number of questions.
 * @param integer $quizId Quiz Id.
 * @return boolean True indicating that the quiz is set up properly, false indicating otherwise.
 */
function checkQuizSetup($quizId) {
	$sectionList = getSectionList($quizId);
	if (count($sectionList) == 0)
		return false;

	$questionTypes = array_keys(getQuestionTypes());
	for ($i = 0; $i < count($sectionList); ++$i) {
		$questionCounts = getQuestionTypeCounts($quizId, $sectionList[$i]['quiz_sectionid']);
		for ($j = 0; $j < count($questionTypes); ++$j)
			if ($questionCounts[$questionTypes[$j]] < $sectionList[$i]['quiz_section' . $questionTypes[$j] . 'count'])
				return false;
	}
	return true;
}

/**
 * function checkQuizOpen:
 * returns if the quiz is open or not
 */
function checkQuizOpen($quizId) {
	$quizQuery = "SELECT IF(NOW() < `quiz_startdatetime`, -1, IF(NOW() > `quiz_enddatetime`, 1, 0)) FROM `quiz_descriptions` WHERE `page_modulecomponentid` = '$quizId'";
	$quizResult = mysql_query($quizQuery);
	$quizRow = mysql_fetch_row($quizResult);
	if (!$quizRow) {
		displayerror('Error. Could not find information about the given quiz.');
		return 1;
	}
	return $quizRow[0];
}

/**
 * function checkUserFirstAttempt:
 * returns if the user is attempting the quiz for first time
 */
function checkUserFirstAttempt($quizId, $userId) {
	$attemptQuery = "SELECT COUNT(*) FROM `quiz_userattempts` WHERE `page_modulecomponentid` = '$quizId' AND `user_id` = '$userId'";
	$attemptResult = mysql_query($attemptQuery);
	$attemptRow = mysql_fetch_row($attemptResult);
	return $attemptRow[0] == 0;
}

/**
 * function sectionBelongsToQuiz:
 * returns if the given quiz has a section with given sectionId
 */
function sectionBelongsToQuiz($quizId, $sectionId) {
	$sectionQuery = "SELECT COUNT(*) FROM `quiz_sections` WHERE `page_modulecomponentid` = '$quizId' AND `quiz_sectionid` = '$sectionId'";
	$sectionResult = mysql_query($sectionQuery);
	$sectionRow = mysql_fetch_row($sectionResult);
	return $sectionRow[0] == 1;
}

/**
 * function startSection:
 * Marks the section attempted by the user
 */
function startSection($quizId, $sectionId, $userId) {
	$attemptQuery = "INSERT INTO `quiz_userattempts`(`page_modulecomponentid`, `quiz_sectionid`, `user_id`, `quiz_attemptstarttime`) VALUES " .
			"('$quizId', '$sectionId', '$userId', NOW())";
	if (!mysql_query($attemptQuery)) {
		displayerror('Database Error. Could not mark section as started.');
		return false;
	}
	return true;
}

/**
 * function getFirstSectionId:
 * returns Lowest sectionid in the given quizid
 */
function getFirstSectionId($quizId) {
	$sectionQuery = "SELECT MIN(`quiz_sectionid`) FROM `quiz_sections` WHERE `page_modulecomponentid` = '$quizId'";
	$sectionResult = mysql_query($sectionQuery);
	if (!$sectionResult)
		return -1;
	$sectionRow = mysql_fetch_row($sectionResult);
	return $sectionRow[0];
}

/**
 * function getAttemptRow:
 * returns userattempts row for given quiz, section, user
 */
function getAttemptRow($quizId, $sectionId, $userId) {
	$attemptQuery = "SELECT * FROM `quiz_userattempts` WHERE `page_modulecomponentid` = '$quizId' AND `quiz_sectionid` = '$sectionId' AND `user_id` = '$userId'";
	$attemptResult = mysql_query($attemptQuery);
	return mysql_fetch_assoc($attemptResult);
}
