<?php
if(!defined('__PRAGYAN_CMS'))
{ 
	header($_SERVER['SERVER_PROTOCOL'].' 403 Forbidden');
	echo "<h1>403 Forbidden<h1><h4>You are not authorized to access the page.</h4>";
	echo '<hr/>'.$_SERVER['SERVER_SIGNATURE'];
	exit(1);
}

/**
 * Quiz Edit Form
 */

/** get*Types() series of functions */
function getQuizTypes() {
	return array('simple');
}
/// returns types of questions
function getQuestionTypes() {
	return array('sso' => 'Single Select Objective', 'mso' => 'Multiselect Objective', 'subjective' => 'Subjective');
}


/**
 * function get*TypeBox
 * returns select box listing the type
 */
function getQuizTypeBox($quizType) {
	$quizTypes = getQuizTypes();
	$quizTypeBox = '<select name="selQuizType" id="selQuizType">';
	for ($i = 0; $i < count($quizTypes); ++$i) {
		$quizTypeBox .= '<option value="' . addslashes($quizTypes[$i]) . '"';
		if ($quizTypes[$i] == $quizType)
			$quizTypeBox .= ' selected="selected"';
		$quizTypeBox .= '>' . $quizTypes[$i] . '</option>';
	}
	return $quizTypeBox;
}

function getQuestionTypeBox($questionType) {
	$questionTypes = getQuestionTypes();
	$questionTypesBox = '<select name="selQuestionType" id="selQuestionType">';
	foreach ($questionTypes as $key => $text) {
		$questionTypesBox .= "<option value=\"$key\"";
		if ($key == $questionType)
			$questionTypesBox .= ' selected="selected"';
		$questionTypesBox .= ">$text</option>\n";
	}

	return $questionTypesBox . "</select>\n";
}


/**
 * function get*Row:
 * gets rows from database
 */
function getTableRow($tableName, $condition) {
	$query = "SELECT * FROM `$tableName` WHERE $condition";
	echo $query;
	$result = mysql_query($query);
	if (!$result) {
		displayerror('Database error. Could not retrieve information from the database.');
		return null;
	}
	return mysql_fetch_assoc($result);
}

function getQuizRow($quizId) {
	return getTableRow('quiz_descriptions', "`page_modulecomponentid` = '$quizId'");
}

function getSectionRow($quizId, $sectionId) {
	return getTableRow('quiz_sections', "`page_modulecomponentid` = '$quizId' AND `quiz_sectionid` = '$sectionId'");
}

function getQuestionRow($quizId, $sectionId, $questionId) {
	return getTableRow('quiz_questions', "`page_modulecomponentid` = '$quizId' AND `quiz_sectionid` = '$sectionId' AND `quiz_questionid` = '$questionId'");
}


/**
 * function get*FormFieldMap:
 * returns form fieldmap
 */
function getQuizEditFormFieldMap() {
	return array(
		array('txtTitle', 'quiz_title', 'quiz_quiztitle', 'text'),
		array('txtHeaderText', 'quiz_headertext', 'quiz_headertext', 'text'),
		array('txtSubmitText', 'quiz_submittext', 'quiz_submittext', 'text'),
		array('selQuizType', 'quiz_quiztype', 'quiz_quiztype', 'select'),
		array('txtDuration', 'quiz_testduration', 'quiz_testduration', 'text'),
		array('txtQuestionCount', 'quiz_questionspertest', 'quiz_questionspertest', 'text'),
		array('txtQuestionsPerPage', 'quiz_questionsperpage', 'quiz_questionsperpage', 'text'),
		array('chkShowTimerPerTest', 'quiz_showquiztimer', 'quiz_showtesttimer', 'checkbox'),
		array('chkShowTimerPerPage', 'quiz_showpagetimer', 'quiz_showpagetimer', 'checkbox'),
		array('txtOpenTime', 'quiz_startdatetime', 'quiz_startdatetime', 'datetime'),
		array('txtCloseTime', 'quiz_enddatetime', 'quiz_closedatetime', 'datetime'),
		array('chkSectionRandomAccess', 'quiz_allowsectionrandomaccess', 'quiz_sectionrandomaccess', 'checkbox'),
		array('chkMixSections', 'quiz_mixsections', 'quiz_mixsections', 'checkbox'),
	);
}

function getSectionEditFormFieldMap() {
	return array(
		array('txtSectionTitle', 'quiz_sectiontitle', 'section_sectiontitle', 'text'),
		array('txtSSOCount', 'quiz_sectionssocount', 'section_ssocount', 'text'),
		array('txtMSOCount', 'quiz_sectionmsocount', 'section_msocount', 'text'),
		array('txtSubjectiveCount', 'quiz_sectionsubjectivecount', 'section_subjectivecount', 'text'),
		array('txtSessionTimeLimit', 'quiz_sectiontimelimit', 'section_timelimit', 'text'),
		array('chkShuffle', 'quiz_sectionquestionshuffled', 'section_shuffle', 'checkbox'),
		array('chkShowLimit', 'quiz_sectionshowlimit', 'section_showlimit', 'checkbox'),
	);
}

function getQuestionEditFormFieldMap() {
	return array(
		array('txtQuestion', 'quiz_question', 'question_question', 'text'),
		array('txtQuestionWeight', 'quiz_questionweight', 'question_weight', 'text'),
		array('selQuestionType', 'quiz_questiontype', 'question_type', 'select'),
	);
}


/**
 * function add*:
 * adding quiz elements to database, which can be section, question, etc
 */
function addItems($insertQuery, $count) {
	$idArray = array();
	for ($i = 0; $i < $count; ++$i) {
		$result = mysql_query($insertQuery);
		if (!$result) {
			displayerror('Database Error. Could not create new item.');
			return false;
		}
		$idArray[] = mysql_insert_id();
	}
	return $idArray;
}

function addSections($quizId, $count) {
	$sectionQuery = "INSERT INTO `quiz_sections`(`page_modulecomponentid`, `quiz_sectiontitle`, `quiz_sectionssocount`, `quiz_sectionmsocount`, `quiz_sectionsubjectivecount`, `quiz_sectiontimelimit`, `quiz_sectionquestionshuffled`, `quiz_sectionrank`) " .
			"(SELECT '$quizId', 'New Section', 0, 0, 0, '00:32', 0, IFNULL(MAX(`quiz_sectionrank`), 0) + 1 FROM `quiz_sections` WHERE `page_modulecomponentid` = '$quizId' LIMIT 1)";
	return addItems($sectionQuery, $count);
}

function addQuestions($quizId, $sectionId, $count) {
	$questionQuery = "INSERT INTO `quiz_questions`(`page_modulecomponentid`, `quiz_sectionid`, `quiz_question`, `quiz_questiontype`, `quiz_questionrank`, `quiz_questionweight`, `quiz_answermaxlength`, `quiz_rightanswer`) " .
			"(SELECT '$quizId','$sectionId', 'Your new question here?', 'subjective', IFNULL(MAX(`quiz_questionrank`), 0) + 1, 1, 1024, 'Yes, it sure is.' FROM `quiz_questions` WHERE `page_modulecomponentid` = '$quizId' AND `quiz_sectionid` = '$sectionId' LIMIT 1)";
	return addItems($questionQuery, $count);
}

/**
 * function delete*:
 * deleting quiz elements from database, which can be section, question, etc
 */
function deleteItem($tableNames, $conditions, &$affectedRows) {
	if (!is_array($tableNames))
		$tableNames = array($tableNames);

	$affectedRows = array();
	$allOk = true;
	for ($i = 0; $i < count($tableNames); ++$i) {
		$deleteQuery = "DELETE FROM `{$tableNames[$i]}` WHERE $conditions";
		if (!mysql_query($deleteQuery)) {
			displayerror("Database Error. Could not remove information from table `{$tableNames[$i]}`.");
			$allOk = false;
			$affectedRows[] = false;
		}
		else
			$affectedRows[] = mysql_affected_rows();
	}
	return $allOk;
}

function deleteSection($quizId, $sectionId) {
	$tables = array('quiz_sections', 'quiz_questions', 'quiz_objectiveoptions', 'quiz_answersubmissions');
	$affectedRows = array();
	return deleteItem($tables, "`page_modulecomponentid` = '$quizId' AND `quiz_sectionid` = '$sectionId'", $affectedRows);
}

function deleteQuestion($quizId, $sectionId, $questionId) {
	$tableNames = array('quiz_questions', 'quiz_objectiveoptions');
	$affectedRows = array();
	return deleteItem($tableNames, "`page_modulecomponentid` = '$quizId' AND `quiz_sectionid` = '$sectionId' AND `quiz_questionid` = '$questionId'", $affectedRows);
}

function deleteQuestionOptions($quizId, $sectionId, $questionId) {
	$tableNames = array('quiz_objectiveoptions');
	$affectedRows = array();
	return deleteItem($tableNames, "`page_modulecomponentid` = '$quizId' AND `quiz_sectionid` = '$sectionId' AND `quiz_questionid` = '$questionId'", $affectedRows);
}

/**
 * function move*:
 * moves section, question etc
 */
function moveItem($itemId, $itemRank, $tableName, $idFieldName, $rankFieldName, $conditions, $direction) {
	$function = $direction == 'up' ? 'DESC' : 'ASC';
	$operator = $direction == 'up' ? '<' : '>';

	$neighbourQuery = "SELECT `$idFieldName`, `$rankFieldName` FROM `$tableName` WHERE " . $conditions . ($conditions == '' ? '' : ' AND') . " `$rankFieldName` $operator $itemRank ORDER BY `$rankFieldName` $function LIMIT 1";
	$neighbourResult = mysql_query($neighbourQuery);
	if (!$neighbourResult) {
		displayerror('Database Error. Could not fetch information about the given item.');
		return false;
	}

	if (mysql_num_rows($neighbourResult) == 0) {
		displaywarning('The item that you tried to move ' . $direction . ' is already at the ' . ($direction == 'up' ? 'top' : 'bottom') . ' of the list.');
		return true;
	}
	$neighbourRow = mysql_fetch_assoc($neighbourResult);
	$itemId2 = $neighbourRow[$idFieldName];
	$itemRank2 = $neighbourRow[$rankFieldName];

	$updateQuery1 = "UPDATE `$tableName` SET `$rankFieldName` = $itemRank2 WHERE " . $conditions . ($conditions == '' ? '' : ' AND') . " `$idFieldName` = '$itemId'";
	$updateQuery2 = "UPDATE `$tableName` SET `$rankFieldName` = $itemRank WHERE " . $conditions . ($conditions == '' ? '' : ' AND') . " `$idFieldName` = '$itemId2'";

	if (!mysql_query($updateQuery1) || !mysql_query($updateQuery2)) {
		displayerror('Database Error. Could not move the specified item.');
		return false;
	}

	return true;
}

function moveSection($quizId, $sectionId, $direction) {
	$sectionRow = getSectionRow($quizId, $sectionId);
	if (!$sectionRow) {
		displayerror('Error. Could not find the section that you were trying to move.');
		return false;
	}
	return moveItem($sectionId, $sectionRow['quiz_sectionrank'], 'quiz_sections', 'quiz_sectionid', 'quiz_sectionrank', "`page_modulecomponentid` = $quizId", $direction);
}

function moveQuestion($quizId, $sectionId, $questionId, $direction) {
	$questionRow = getQuestionRow($quizId, $sectionId, $questionId);
	if (!$questionRow) {
		displayerror('Could not find the specified question.');
		return false;
	}
	return moveItem($questionId, $questionRow['quiz_questionrank'], 'quiz_questions', 'quiz_questionid', 'quiz_questionrank', "`page_modulecomponentid` = $quizId AND `quiz_sectionid` = $sectionId", $direction);
}


/**
 * function get*List:
 * returns list from the specified tablename
 */
function getItemList($tableName, $conditions = '1') {
	$itemQuery = "SELECT * FROM `$tableName` WHERE $conditions";
	$itemResult = mysql_query($itemQuery);
	if (!$itemResult)
		return false;
	$itemList = array();
	while ($itemList[] = mysql_fetch_assoc($itemResult));
	array_pop($itemList);
	return $itemList;
}

function getSectionList($quizId) {
	return getItemList('quiz_sections', "`page_modulecomponentid` = '$quizId' ORDER BY `quiz_sectionrank`");
}

/**
 * function getQuestionTableHtml:
 * displays all the questions in a given section, quiz
 * with proper formatting
 */
function getQuestionTableHtml($quizId, $sectionId) {
	global $urlRequestRoot, $sourceFolder, $templateFolder,$cmsFolder;
	$editImage = "<img style=\"padding:0px\" src=\"$urlRequestRoot/$cmsFolder/$templateFolder/common/icons/16x16/apps/accessories-text-editor.png\" alt=\"Edit\" />";
	$deleteImage = "$urlRequestRoot/$cmsFolder/$templateFolder/common/icons/16x16/actions/edit-delete.png";
	$moveUpImage = "<img src=\"$urlRequestRoot/$cmsFolder/$templateFolder/common/icons/16x16/actions/go-up.png\" alt=\"Move Section Up\" />";
	$moveDownImage = "<img src=\"$urlRequestRoot/$cmsFolder/$templateFolder/common/icons/16x16/actions/go-down.png\" alt=\"Move Section Down\" />";	

	$questionQuery = "SELECT * FROM `quiz_questions` WHERE `page_modulecomponentid` = '$quizId' AND `quiz_sectionid` = '$sectionId' ORDER BY `quiz_questionrank`";
	$questionResult = mysql_query($questionQuery);

	$questionListHtml = '<table border="1"><tr><th>Question</th><th>Type</th><th></th></tr>';
	$questionCount = mysql_num_rows($questionResult);
	$i = 0;
	while ($questionRow = mysql_fetch_assoc($questionResult)) {
		$questionId = $questionRow['quiz_questionid'];
		$rightAnswer = $questionRow['quiz_rightanswer'];

		$optionsText = '';
		$optionsQuery = "SELECT * FROM `quiz_objectiveoptions` WHERE `page_modulecomponentid` = '$quizId' AND `quiz_sectionid` = '$sectionId' AND `quiz_questionid` = '$questionId' ORDER BY `quiz_optionrank";
		$optionsResult = mysql_query($optionsQuery);
		$j = 1;
		while ($optionsRow = mysql_fetch_assoc($optionsResult)) {
			$style = '';
			if ($j == $rightAnswer)
				$style = 'font-weight: bold;';
			$optionsText .= "<p style=\"margin-left: 12px; $style\">{$optionsRow['quiz_optiontext']}</p>\n";
			++$j;
		}

		global $sourceFolder, $moduleFolder;
		require_once($sourceFolder."/pngRender.class.php");
		$render = new pngrender();
		$optionsText = $render->transform($optionsText);
		$questionDesc = $render->transform($questionRow['quiz_question']);

		$moveUp = ($i == 0 ? '' : "<a href=\"./+edit&subaction=movequestion&direction=up&sectionid=$sectionId&questionid=$questionId\">$moveUpImage</a>");
		$moveDown = ($i == $questionCount - 1 ? '' : "<a href=\"./+edit&subaction=movequestion&direction=down&sectionid=$sectionId&questionid=$questionId\">$moveDownImage</a>");
		$questionListHtml .= <<<QUESTIONROW
		<tr>
			<td>{$questionDesc}<br />$optionsText</td><td>{$questionRow['quiz_questiontype']}</td>
			<td nowrap="nowrap">
				<a href="./+edit&subaction=editquestion&sectionid=$sectionId&questionid={$questionRow['quiz_questionid']}">$editImage</a>
				<form style="display:inline;margin:0;padding:0;border:0" method="POST" action="./+edit&subaction=deletequestion">
					<input type="hidden" name="hdnSectionId" value="$sectionId" />
					<input type="hidden" name="hdnQuestionId" value="$questionId" />
					<input type="image" name="btnDelete" src="$deleteImage" title="Delete" />
				</form>
				$moveUp
				$moveDown
			</td>
		</tr>
QUESTIONROW;
		++$i;
	}

	return $questionListHtml . '</table>';
}

/**
 * function getQuestionOptionList:
 * Returns the list of options for the given question
 */
function getQuestionOptionList($quizId, $sectionId, $questionId) {
	return getItemList('quiz_objectiveoptions', "`page_modulecomponentid` = '$quizId' AND `quiz_sectionid` = '$sectionId' AND `quiz_questionid` = '$questionId' ORDER BY `quiz_optionrank`");
}

/**
 * function getQuestionOptionListHtml:
 * returns HTML format list of options with check boxes to check right answer
 */
function getQuestionOptionListHtml($quizId, $sectionId, $questionId, $questionType, $rightAnswer, $editable = false) {
	$optionList = getQuestionOptionList($quizId, $sectionId, $questionId);
	if ($optionList === false)
		return false;

	if ($questionType == 'mso')
		$rightAnswer = explode('|', $rightAnswer);
	else
		$rightAnswer = array($rightAnswer);

	$inputTypePrefix = $questionType == 'sso' ? 'opt' : 'chk';
	$html = '<table id="optionsTable" border="0" width="100%">';
	for ($i = 0; $i < count($optionList); ++$i) {
		$elementName = "{$inputTypePrefix}Option";
		if ($questionType == 'mso')
			$elementName .= $i;
		$elementId = "{$inputTypePrefix}Option$i";
		$html .= '<tr><td style="width:32px"><input type="' . ($questionType == 'sso' ? 'radio' : 'checkbox') . '" id="' . $elementId . '" name="' . $elementName . '" value="' . $i . '"';
		if ($editable && in_array($optionList[$i]['quiz_optionid'], $rightAnswer))
			$html .= ' checked="checked"';
		$html .= ' /></td><td>';

		if ($editable)
			$html .= '<input type="text" name="txtOptionText' . $i . '" id="txtOptionText' . $i . '" value="' . $optionList[$i]['quiz_optiontext'] . '" />';
		else
			$html .= $optionList[$i]['quiz_optiontext'];
		$html .= "</td></tr>\n";
	}
	$html .= '</table> <input type="button" name="btnAddOption" onclick="addOption()" value="Add Option" />';
	return $html;
}

/**
 * function getSubmittedQuestionOptionListHtml:
 * returns list of options for a question which is submitted
 */
function getSubmittedQuestionOptionListHtml($questionType) {
	$html = '<table border="0" id="optionsTable" border="0" width="100%">';
	$inputType = $questionType == 'sso' ? 'radio' : 'checkbox';
	$prefix = ($questionType == 'sso' ? 'opt' : 'chk') . 'Option';
	$i = 0;
	while (true) {
		if (!isset($_POST['txtOptionText' . $i]))
			break;
		$elementName = $prefix;
		$elementId = $prefix . $i;
		if ($questionType == 'mso')
			$elementName .= $i;

		$html .= '<tr><td><input type="' . $inputType . '" name="' . $elementName . '" id="' . $elementName . '" value="' . $i . '" ';
		if (($questionType == 'sso' && $_POST['optOption'] == $i) || isset($_POST[$elementName]))
			$html .= 'checked="checked" ';
		$html .= '/></td><td><input type="text" name="txtOptionText' . $i . '" id="txtOptionText' . $i . '" value="' . safe_html($_POST["txtOptionText$i"]) . '" /></td></tr>';
		$i++;
	}
	$html .= "</table> <input type='button' name='btnAddOption' onclick='addOption()' value='Add Option' />\n";
	return $html;
}

/**
 * function setWeightMark:
 * Updates marks for given weight if already some marks are assigned
 * Otherwise inserts a new record for the current weight with the speicified marks
 */
function setWeightMark($quizId, $weight, $positive, $negative) {
	$result = mysql_query("SELECT `question_weight` FROM `quiz_weightmarks` WHERE `page_modulecomponentid` = '$quizId' AND `question_weight` = '$weight'");
	if(mysql_fetch_assoc($result))
		mysql_query("UPDATE `quiz_weightmarks` SET `question_positivemarks` = '$positive', `question_negativemarks` = '$negative' WHERE `page_modulecomponentid` = '$quizId' AND `question_weight` = '$weight'");
	else
		mysql_query("INSERT INTO `quiz_weightmarks`(`page_modulecomponentid`, `question_weight`, `question_positivemarks`, `question_negativemarks`) VALUES ('$quizId', '$weight', '$positive', '$negative')");
	return mysql_affected_rows();
}

/**
 * function weightMarksForm:
 * generates form which is used to set marks for each weight used in this quiz
 */
function weightMarksForm($quizId) {
	$result = mysql_query("SELECT DISTINCT `quiz_questionweight` FROM `quiz_questions` WHERE `page_modulecomponentid` = $quizId");
	
	$ret = "<table><thead><th>Weight</th><th>Marks</th></thead>";
	$pmarks="";
	$nmarks="";
	while($row = mysql_fetch_assoc($result))
	{
		$result2= mysql_query("SELECT `question_positivemarks`,`question_negativemarks` FROM `quiz_weightmarks` WHERE `page_modulecomponentid` = '$quizId' AND `question_weight`='{$row['quiz_questionweight']}'");
		
		if(mysql_num_rows($result2)==0)
		{
			$pmarks=$row['quiz_questionweight'];
			$nmarks=$row['quiz_questionweight'];
		}
		else
		{
			$row2=mysql_fetch_assoc($result2);
			$pmarks=$row2["question_positivemarks"];
			$nmarks=$row2["question_negativemarks"];
		}
		$ret .= "<tr><td>{$row['quiz_questionweight']}</td><td><form method=POST action='./+edit&subaction=setweightmark'><input type=hidden name=weight value='{$row['quiz_questionweight']}'><input type=hidden name=quizId value='{$quizId}'>Positive<input type=text name='pos' value='$pmarks' size=5> Negative<input type=text name='neg' value='$nmarks' size=5> <input type=submit name=btnSetWeightMarks value='Set Marks'></form></td></tr>";
	}
	return $ret . "</table>";
}

/**
 * function getQuizEditForm:
 * returns the HTML interface to edit the quiz,
 * which includes editing quiz properties, setting weight marks, editing sections, and adding questions to sections
 * and gives link to editing questions
 */
function getQuizEditForm($quizId, $dataSource) {
	$fieldMap = getQuizEditFormFieldMap();
	if ($dataSource == 'POST') {
		for ($i = 0; $i < count($fieldMap); ++$i) {
			if ($fieldMap[$i][3] == 'chk')
				$$fieldMap[$i][2] = isset($_POST[$fieldMap[$i][0]]) ? 'checked="checked"' : '';
			else
				$$fieldMap[$i][2] = isset($_POST[$fieldMap[$i][0]]) ? htmlentities(safe_html($_POST[$fieldMap[$i][0]])) : '';
		}
	}
	elseif ($dataSource == 'db') {
		$quizRow = getQuizRow($quizId);
		if (!$quizRow) {
			displayerror('Could not retrieve information about the specified quiz. Quiz not found.');
			return '';
		}
		for ($i = 0; $i < count($fieldMap); ++$i) {
			if ($fieldMap[$i][3] == 'checkbox')
				$$fieldMap[$i][2] = isset($quizRow[$fieldMap[$i][1]]) && $quizRow[$fieldMap[$i][1]] != 0 ? 'checked="checked"' : '';
			else
				$$fieldMap[$i][2] = isset($quizRow[$fieldMap[$i][1]]) ? htmlentities($quizRow[$fieldMap[$i][1]]) : '';
		}
	}

	$quizTypeBox = getQuizTypeBox($quiz_quiztype);
	
	$setWeightMarks = weightMarksForm($quizId);
	
	global $moduleFolder, $cmsFolder, $urlRequestRoot;
	$calpath = "$urlRequestRoot/$cmsFolder/$moduleFolder";
	$quizEditForm = <<<QUIZEDITFORM

	<link rel="stylesheet" type="text/css" media="all" href="$calpath/form/calendar/calendar.css" title="Aqua" />
	<script type="text/javascript" src="$calpath/form/calendar/calendar.js"></script>

	<form name="quizpropertiesform" action="./+edit" method="POST">
		<h3>Quiz Properties</h3>

		<fieldset style="padding:8px">
			<legend>General Properties</legend>

			<table border="0">
				<tr><td><label for="txtTitle">Quiz Title:</label></td><td><input type="text" name="txtTitle" id="txtTitle" value="$quiz_quiztitle" /></td></tr>
				<tr><td><label for="txtHeaderText">Header Text:</label></td><td><textarea rows="5" cols="30" id="txtHeaderText" name="txtHeaderText">$quiz_headertext</textarea></td></tr>
				<tr><td><label for="txtSubmitText">Submit Text:</label></td><td><textarea rows="5" cols="30" id="txtSubmitText" name="txtSubmitText">$quiz_submittext</textarea></td></tr>
				<tr><td><label for="selQuizType">Quiz Type:</label></td><td>$quizTypeBox</td></tr>
				<tr><td><label for="txtDuration">Quiz Duration (HH:MM):</label></td><td><input type="text" name="txtDuration" id="txtDuration" value="$quiz_testduration" /></td></tr>
				<tr><td><label for="txtQuestionCount">Questions Per Test:</label></td><td><input type="text" name="txtQuestionCount" id="txtQuestionCount" value="$quiz_questionspertest" /></td></tr>
				<tr><td><label for="txtQuestionsPerPage">Questions Per Page:</label></td><td><input type="text" name="txtQuestionsPerPage" id="txtQuestionsPerPage" value="$quiz_questionsperpage" /></td></tr>
				<tr><td>Show Timers:</td><td><label><input type="checkbox" name="chkShowTimerPerTest" id="chkShowTimerPerTest" value="pertest" $quiz_showtesttimer /> Per Test</label> <label><input type="checkbox" name="chkShowTimerPerPage" id="chkShowTimerPerPage" value="perpage" $quiz_showpagetimer /> Per Page</label></td></tr>
				<tr><td><label>Allow Random Access to Sections?</label></td><td><label><input type="checkbox" id="chkSectionRandomAccess" name="chkSectionRandomAccess" $quiz_sectionrandomaccess /> Yes</label></td></tr>
				<tr><td><label>Mix Sections?</label></td><td><label><input type="checkbox" name="chkMixSections" id="chkMixSections" value="mixsections" $quiz_mixsections /> Yes</label></td></tr>
				<tr><td><label for="txtOpenTime">Opening Time:</label></td><td><input type="text" name="txtOpenTime" id="txtOpenTime" value="$quiz_startdatetime" /><input name="calc" type="reset" value="  ...  " onclick="return showCalendar('txtOpenTime', '%Y-%m-%d %H:%M:%S', '24', true);" /></td></tr>
				<tr><td><label for="txtCloseTime">Closing Time:</label></td><td><input type="text" name="txtCloseTime" id="txtCloseTime" value="$quiz_closedatetime" /><input name="calc" type="reset" value="  ...  " onclick="return showCalendar('txtCloseTime', '%Y-%m-%d %H:%M:%S', '24', true);" /></td></tr>
			</table>
		</fieldset>
		
		<fieldset id="quizWeightMarks">
		<legend>Weight - Marks</legend>
		{$setWeightMarks}
		</fieldset>
		
		<fieldset style="padding:8px" id="quizTypeSpecificProperties">
			<legend>Quiz Type Specific Properties</legend>
QUIZEDITFORM;

	$quizTypes = getQuizTypes();
	for ($i = 0; $i < count($quizTypes); ++$i) {
		$quizObjectClassName = ucfirst($quizTypes[$i]) . 'Quiz';
		$quizObject = new $quizObjectClassName($quizId);
		$quizEditForm .= "<div id=\"{$quizTypes[$i]}QuizProperties\">" . $quizObject->getPropertiesForm($dataSource) . "</div>\n";
	}

	$quizEditForm .= <<<QUIZEDITFORM
		</fieldset>
		<script type="text/javascript">
			function quizTypeChanged(e) {
				var selQuizType = document.getElementById('selQuizType');
				var showId = selQuizType.value + 'QuizProperties';
				var options = selQuizType.getElementsByTagName('option');

				for (var i = 0; i < options.length; ++i) {
					var curId = options[i].value + 'QuizProperties';
					document.getElementById(curId).style.display = (curId == showId ? '' : 'none');
				}
			}

			function validateAddQuestions(form) {
				var questionCount = form.txtQuestionCount.value;
				if (!/^\d+$/.test(questionCount)) {
					alert('Please enter the number of questions to add.');
					return false;
				}
				questionCount = parseInt(sectionCount);
				if (questionCount <= 0) {
					alert('Please enter a positive number of questions to add.');
					return false;
				}
				if (questionCount > 100)
					if (!confirm('You are about to add ' + questionCount + ' questions. Are you sure you wish to do this?'))
						return false;
				return true;
			}

			function validateAddSections() {
				var sectionCount = document.getElementById('txtSectionCount').value;
				if (!/^\d+$/.test(sectionCount)) {
					alert('Please enter the number of sections to add.');
					return false;
				}
				sectionCount = parseInt(sectionCount);
				if (sectionCount <= 0) {
					alert('Please enter a positive number of sections to add.');
					return false;
				}
				if (sectionCount > 100)
					if (!confirm('You are about to add ' + sectionCount + ' sections. Are you sure you wish to do this?'))
						return false;
				return true;
			}
			document.getElementById('selQuizType').onchange = quizTypeChanged;
			quizTypeChanged(null);
		</script>
		<br />
		<input type="submit" name="btnSubmit" value="Submit" />
	</form>
	<hr />

	<fieldset style="padding:8px">
		<legend>Sections</legend>
QUIZEDITFORM;

	global $urlRequestRoot, $sourceFolder, $templateFolder,$cmsFolder;
	$editImage = "<img style=\"padding:0px\" src=\"$urlRequestRoot/$cmsFolder/$templateFolder/common/icons/16x16/apps/accessories-text-editor.png\" alt=\"Edit\" />";
	$deleteImage = "$urlRequestRoot/$cmsFolder/$templateFolder/common/icons/16x16/actions/edit-delete.png";
	$moveUpImage = "<img src=\"$urlRequestRoot/$cmsFolder/$templateFolder/common/icons/16x16/actions/go-up.png\" alt=\"Move Section Up\" />";
	$moveDownImage = "<img src=\"$urlRequestRoot/$cmsFolder/$templateFolder/common/icons/16x16/actions/go-down.png\" alt=\"Move Section Down\" />";

	$quizSections = getSectionList($quizId);
	$questionTypes = getQuestionTypes();

	$sectionCount = count($quizSections);
	for ($i = 0; $i < $sectionCount; ++$i) {
		$moveUp = ($i == 0 ? '' : "<a href=\"./+edit&subaction=movesection&direction=up&sectionid={$quizSections[$i]['quiz_sectionid']}\" />$moveUpImage</a>");
		$moveDown = ($i == $sectionCount - 1 ? '' : "<a href=\"./+edit&subaction=movesection&direction=down&sectionid={$quizSections[$i]['quiz_sectionid']}\" />$moveDownImage</a>");
		$stats = array();
		foreach ($questionTypes as $questionTypeName => $questionTypeTitle)
			$stats[] = $quizSections[$i]['quiz_section' . $questionTypeName . 'count'] . ' ' . $questionTypeTitle . ' question(s)';
		$stats = implode(', ', $stats) . ' to be chosen from this section.';
		$timeLimit = $quizSections[$i]['quiz_sectiontimelimit'] == '00:00:00' ? 'No Time Limit' : $quizSections[$i]['quiz_sectiontimelimit'];

		$questionTable = getQuestionTableHtml($quizId, $quizSections[$i]['quiz_sectionid']);

		$quizEditForm .= <<<SECTIONTEXT
			<h3>Section: {$quizSections[$i]['quiz_sectiontitle']}</h3>
			Options:
				<a href="./+edit&subaction=editsection&sectionid={$quizSections[$i]['quiz_sectionid']}">$editImage</a>
				<form style="display:inline;" name="deletesectionform" method="POST" action="./+edit&subaction=deletesection" onsubmit="return confirm('You are about to delete a complete section. This will delete all questions that belong to that section. Are you sure you wish to proceed?')">
					<input type="hidden" name="hdnSectionId" value="{$quizSections[$i]['quiz_sectionid']}" />
					<input type="image" name="btnDelete" id="btnDelete" src="$deleteImage" />
				</form>
				$moveUp
				$moveDown
			<p>$stats</p>
			<p>Time limit: $timeLimit</p>

			$questionTable

			<form name="questionaddform" action="./+edit&subaction=addquestions&sectionid={$quizSections[$i]['quiz_sectionid']}" method="POST" onsubmit="return validateAddQuestions(this)">
				<p>Add <input type="text" name="txtQuestionCount" value="1" size="3" /> Questions
				<input type="submit" name="btnAddQuestions" value="Go" />
			</form>

			<p></p>
			<hr />
			<br />
SECTIONTEXT;
	}

	$quizEditForm .= <<<QUIZEDITFORM

	<form name="sectionaddform" action="./+edit&subaction=addsections" method="POST" onsubmit="return validateAddSections()">
		<p>
			Add <input type="text" size="3" name="txtSectionCount" id="txtSectionCount" value="1" /> Section(s)
			<input type="submit" name="btnAddSections" id="btnAddSections" value="Go" />
		</p>
	</fieldset>
QUIZEDITFORM;
	return $quizEditForm;
}

/**
 * function getSectionEditForm:
 * this function returns HTML edit form for a section, where section specific properties can be edited
 */
function getSectionEditForm($quizId, $sectionId, $dataSource) {
	$fieldMap = getSectionEditFormFieldMap();

	if ($dataSource == 'POST') {
		for ($i = 0; $i < count($fieldMap); ++$i) {
			if ($fieldMap[$i][3] == 'chk')
				$$fieldMap[$i][2] = isset($_POST[$fieldMap[$i][0]]) ? 'checked="checked"' : '';
			else
				$$fieldMap[$i][2] = isset($_POST[$fieldMap[$i][0]]) ? htmlentities(safe_html($_POST[$fieldMap[$i][0]])) : '';
		}
	}
	elseif ($dataSource == 'db') {
		$sectionRow = getSectionRow($quizId, $sectionId);
		if (!$sectionRow) {
			displayerror('Error. Could not load information about the specified section. Section not found.');
			return '';
		}
		for ($i = 0; $i < count($fieldMap); ++$i) {
			if ($fieldMap[$i][3] == 'checkbox')
				$$fieldMap[$i][2] = isset($sectionRow[$fieldMap[$i][1]]) && $sectionRow[$fieldMap[$i][1]] != 0 ? 'checked="checked"' : '';
			else
				$$fieldMap[$i][2] = isset($sectionRow[$fieldMap[$i][1]]) ? htmlentities($sectionRow[$fieldMap[$i][1]]) : '';
		}
	}

	$sectionEditForm = <<<SECTIONEDITFORM
		<form name="sectioneditform" action="" method="POST">
			<fieldset style="padding:8px">
				<legend>Edit Section Properties</legend>

				<table border="0">
					<tr><td><label for="txtSectionTitle">Section Title:</label></td><td><input type="text" name="txtSectionTitle" id="txtSectionTitle" value="$section_sectiontitle" /></td></tr>
					<tr><td><label for="txtSSOCount">Number of Single Select Objective Questions:</label></td><td><input type="text" name="txtSSOCount" id="txtSSOCount" value="$section_ssocount" /></td></tr>
					<tr><td><label for="txtMSOCount">Number of Multi-select Objective Questions:</label></td><td><input type="text" name="txtMSOCount" id="txtMSOCount" value="$section_msocount" /></td></tr>
					<tr><td><label for="txtSubjectiveCount">Number of Subjective Questions:</label></td><td><input type="text" name="txtSubjectiveCount" id="txtSubjectiveCount" value="$section_subjectivecount" /></td></tr>
					<tr><td><label for="txtSessionTimeLimit">Time Limit:</label></td><td><input type="text" name="txtSessionTimeLimit" id="txtSessionTimeLimit" value="$section_timelimit" /></td></tr>
					<tr><td><label>Show Limit?</label></td><td><label><input type="checkbox" name="chkShowLimit" id="chkShowLimit" value="yes" $section_showlimit /> Yes</label></td></tr>
					<tr><td><label>Shuffle Questions?</label></td><td><label><input type="checkbox" name="chkShuffle" id="chkShuffle" value="yes" $section_shuffle /> Yes</label></td></tr>
				</table>
			</fieldset>

			<input type="submit" name="btnSubmit" id="btnSubmit" value="Submit" />
		</form>

		<p><a href="./+edit">&laquo; Back to Quiz Properties</a></p>
SECTIONEDITFORM;

	return $sectionEditForm;
}

/**
 * function getQuestionEditForm:
 * this function returns HTML form to edit question
 */
function getQuestionEditForm($quizId, $sectionId, $questionId, $dataSource) {
	$fieldMap = getQuestionEditFormFieldMap();
	$question_type = 'subjective';
	if ($dataSource == 'POST') {
		for ($i = 0; $i < count($fieldMap); ++$i) {
			if ($fieldMap[$i][3] == 'chk')
				$$fieldMap[$i][2] = isset($_POST[$fieldMap[$i][0]]) ? 'checked="checked"' : '';
			else
				$$fieldMap[$i][2] = isset($_POST[$fieldMap[$i][0]]) ? htmlentities(safe_html($_POST[$fieldMap[$i][0]])) : '';
		}
	}
	elseif ($dataSource == 'db') {
		$questionRow = getQuestionRow($quizId, $sectionId, $questionId);
		if (!$questionRow) {
			displayerror('Error. Could not load information about the specified section. Section not found.');
			return '';
		}
		for ($i = 0; $i < count($fieldMap); ++$i) {
			if ($fieldMap[$i][3] == 'checkbox')
				$$fieldMap[$i][2] = isset($questionRow[$fieldMap[$i][1]]) && $questionRow[$fieldMap[$i][1]] != 0 ? 'checked="checked"' : '';
			else
				$$fieldMap[$i][2] = isset($questionRow[$fieldMap[$i][1]]) ? htmlentities($questionRow[$fieldMap[$i][1]]) : '';
		}
	}

	$questionTypeBox = getQuestionTypeBox($question_type);
	$solutionContainer = '';
	if ($question_type == 'subjective') {
		$solutionContainer .= '<textarea name="txtRightAnswer" id="txtRightAnswer" rows="5" cols="36">';
		if ($dataSource == 'POST') {
			$solutionContainer .= (isset($_POST['txtRightAnswer']) ? htmlentities(safe_html($_POST['txtRightAnswer'])) : '') . '</textarea>';
		}
		elseif ($dataSource == 'db') {
			$solutionContainer .= (isset($questionRow['quiz_rightanswer']) ? htmlentities($questionRow['quiz_rightanswer']) : '');
		}
		$solutionContainer .= '</textarea>';
	}
	else {
		if ($dataSource == 'POST') {
			$solutionContainer .= getSubmittedQuestionOptionListHtml($question_type);
		}
		elseif ($dataSource == 'db') {
			$solutionContainer .= getQuestionOptionListHtml($quizId, $sectionId, $questionId, $question_type, $questionRow['quiz_rightanswer'], true);
		}
	}

	$questionEditForm = <<<QUESTIONEDITFORM
		<fieldset><legend>Question Properties</legend>
		<form name="questioneditform" method="POST" onSubmit="return validate();" action="">
			<table border="0" width="100%">
				<tr><td><label for="txtQuestion">Question:</label></td><td><textarea name="txtQuestion" id="txtQuestion" rows="5" cols="36">$question_question</textarea></td></tr>
				<tr><td><label for="selQuestionType">Question Type:</label></td><td>$questionTypeBox</td></tr>
				<tr><td><label for="txtQuestionWeight">Question Weight:</label></td><td><input type="text" name="txtQuestionWeight" id="txtQuestionWeight" value="$question_weight" /></td></tr>
			</table>

			<p><strong>Answers</strong></p>
			<p>In case of subjective questions, you can provide a hint to the solution here to help during correction.</p>
			<p>In case of objective questions, please type in the options, and check the right answer(s).</p>
			<div id="solutionContainer">$solutionContainer</div>

			<script type="text/javascript">
				var questionType = '$question_type';
				var objectiveOptions = new Array();
				var subjectiveAnswer = '';
				function validate() {
					var elementCount = document.getElementById('optionsTable').getElementsByTagName('tr').length;
					if(elementCount > 1)
						return true;
					alert('No answer/option specified');
					return false;
				}

				function addOption() {
					var optionsTable = document.getElementById('optionsTable');
					var elementCount = optionsTable.getElementsByTagName('tr').length;
					var elementName = (questionType == 'sso' ? 'opt' : 'chk') + 'Option';
					if (questionType == 'mso')
						elementName += elementCount;
					var elementId = (questionType == 'sso' ? 'opt' : 'chk') + 'Option' + elementCount;
					var elementType = (questionType == 'sso' ? 'radio' : 'checkbox');
					var optionsRow = document.createElement('tr');
					var td1 = document.createElement('td');
					var td2 = document.createElement('td');
					td1.innerHTML = '<input type="' + elementType + '" id="' + elementId + '" name="' + elementName + '" value="' + elementCount + '" />';
					td2.innerHTML = '<input type="text" name="txtOptionText' + elementCount + '" id="txtOptionText' + elementCount + '" value="" />';
					optionsRow.appendChild(td1);
					optionsRow.appendChild(td2);
					var tbody = optionsTable.getElementsByTagName('tbody');
					if (tbody.length) {
						tbody = tbody[0];
						if (tbody.parentNode == optionsTable)
							tbody.appendChild(optionsRow);
					}
					else
						optionsTable.appendChild(optionsRow);
				}

				function questionTypeChanged(e) {
					var newQuestionType = document.getElementById('selQuestionType').value;
					var solutionContainer = document.getElementById('solutionContainer');

					if (questionType == 'sso' || questionType == 'mso') {
						objectiveOptions = new Array();
						var i = 0;
						while (true) {
							var option = document.getElementById('txtOptionText' + i);
							var boxId = (questionType == 'sso' ? 'opt' : 'chk') + 'Option' + i;
							var box = document.getElementById(boxId);
							if (option)
								objectiveOptions.push(new Array(option.value, box.checked));
							else
								break;
							++i;
						}
					}
					else if (questionType == 'subjective') {
						var txtRightAnswer = document.getElementById('txtRightAnswer');
						if (txtRightAnswer)
							subjectiveAnswer = txtRightAnswer.innerHTML;
					}

					var innerHTML = '';
					if (newQuestionType == 'sso' || newQuestionType == 'mso') {
						innerHTML = '<table border="0" id="optionsTable">'
						var inputType = newQuestionType == 'sso' ? 'radio' : 'checkbox';
						var inputTypePrefix = newQuestionType == 'sso' ? 'opt' : 'chk';
						for (var i = 0; i < objectiveOptions.length; ++i) {
							var elementName = inputTypePrefix + 'Option';
							var elementId = elementName + i;
							if (newQuestionType == 'mso')
								elementName += i;
							innerHTML +=
								'<tr><td><input type="' + inputType + '" name="' + elementName + '" id="' + elementId + '"' + (objectiveOptions[i][1] ? ' checked="checked"' : '') + ' /></td>' +
								'<td><input type="text" name="txtOptionText' + i + '" id="txtOptionText' + i + '" value="' + objectiveOptions[i][0] + '" /></td></tr>';
						}
						innerHTML += '</table>';
						innerHTML += '<input type="button" name="btnAddOption" onclick="addOption()" value="Add Option" />'
					}
					else {
						innerHTML += '<textarea name="txtRightAnswer" id="txtRightAnswer" rows="5" cols="36">' + subjectiveAnswer + '</textarea>';
					}

					solutionContainer.innerHTML = innerHTML;

					questionType = newQuestionType;
				}

				document.getElementById('selQuestionType').onchange = questionTypeChanged;
			</script>
			<input type="submit" name="btnSubmit" id="btnSubmit" value="Save Question" />
		</form>
		</fieldset>
		<p><a href="./+edit">&laquo; Back to Quiz Properties</a></p>
QUESTIONEDITFORM;
	return $questionEditForm;
}


/**
 * function submitQuizEditForm:
 * this function updates quiz properties in database when a quizedit form is submitted
 */
function submitQuizEditForm($quizId) {
	$fieldMap = getQuizEditFormFieldMap();
	$updates = array();

	for ($i = 0; $i < count($fieldMap); ++$i) {
		$update = "`{$fieldMap[$i][1]}` = ";
		if ($fieldMap[$i][3] == 'checkbox') {
			$update .= (isset($_POST[$fieldMap[$i][0]]) ? '1' : '0');
		}
		else {
			if (!isset($_POST[$fieldMap[$i][0]]))
				continue;
			$update .= "'" . ($_POST[$fieldMap[$i][0]]) . "'";
		}
		$updates[] = $update;
	}

	if (count($updates) == 0)
		return true;

	$updateQuery = 'UPDATE `quiz_descriptions` SET ' . implode(', ', $updates) . " WHERE `page_modulecomponentid` = '$quizId'";
	if (!mysql_query($updateQuery)) {
		displayerror('Database Error. Could not save quiz form. ' . $updateQuery . ' ' . mysql_error());
		return false;
	}

	return true;
}

/**
 * function submitSectionEditForm:
 * updates section properties in database when a sectionedit form is submitted
 */
function submitSectionEditForm($quizId, $sectionId) {
	$fieldMap = getSectionEditFormFieldMap();
	$updates = array();
	for ($i = 0; $i < count($fieldMap); ++$i) {
		$update = "`{$fieldMap[$i][1]}` = ";
		if ($fieldMap[$i][3] == 'checkbox') {
			$update .= (isset($_POST[$fieldMap[$i][0]]) ? '1' : '0');
		}
		else {
			if (!isset($_POST[$fieldMap[$i][0]]))
				continue;
			$update .= "'" . safe_html($_POST[$fieldMap[$i][0]]) . "'";
		}
		$updates[] = $update;
	}

	if (count($updates) == 0)
		return true;

	$updateQuery = "UPDATE `quiz_sections` SET " . implode(', ', $updates) . " WHERE `page_modulecomponentid` = $quizId AND `quiz_sectionid` = '$sectionId'";

	if (!mysql_query($updateQuery)) {
		displayerror('Database Error. Could not save section details.');
		return false;
	}
	return true;
}

/**
 * function submitQuestionEditForm:
 * updates question properties in database when a question edit form is submitted.
 * for objective answers also the options are updated
 */
function submitQuestionEditForm($quizId, $sectionId, $questionId) {
	$updates = array();
	$done = true;
	if (isset($_POST['txtQuestion']))
		$updates[] = "`quiz_question` = '" . escape($_POST['txtQuestion']) . "'";
	if (isset($_POST['selQuestionType']) && in_array($_POST['selQuestionType'], array_keys(getQuestionTypes())))
		$updates[] = "`quiz_questiontype` = '" . escape($_POST['selQuestionType']) . "'";
	else {
		displayerror('No or invalid question type specified.');
		return false;
	}

	if (isset($_POST['txtQuestionWeight']) && is_numeric($_POST['txtQuestionWeight']) && $_POST['txtQuestionWeight'] > 0)
		$updates[] = "`quiz_questionweight` = " . escape($_POST['txtQuestionWeight']);

	deleteQuestionOptions($quizId, $sectionId, $questionId);

	$questionType = escape($_POST['selQuestionType']);
	if ($questionType != 'subjective') {
		$i = 0;
		$rightAnswer = array();
		while (true) {
			if (!isset($_POST['txtOptionText' . $i]) || $_POST["txtOptionText$i"] == '')
				break;
			$optionText = escape($_POST['txtOptionText' . $i]);
			$insertQuery = "INSERT INTO `quiz_objectiveoptions`(`page_modulecomponentid`, `quiz_sectionid`, `quiz_questionid`, `quiz_optiontext`, `quiz_optionrank`) " .
					"SELECT '$quizId', '$sectionId', '$questionId', '{$optionText}', IFNULL(MAX(`quiz_optionrank`), 0) + 1 FROM `quiz_objectiveoptions` WHERE `page_modulecomponentid` = '$quizId' AND `quiz_sectionid` = '$sectionId' AND `quiz_questionid` = '$questionId' LIMIT 1";
			if (!mysql_query($insertQuery)) {
				displayerror('Database Error. Could not insert options.');
				return false;
			}
			$optionId = mysql_insert_id();

			if (
				($questionType == 'sso' && isset($_POST['optOption']) && $_POST['optOption'] == $i) ||
				($questionType == 'mso' && isset($_POST['chkOption' . $i]))
			)
				$rightAnswer[] = $optionId;
			++$i;
		}
		if(!isset($rightAnswer[0])) {
			displayerror('No options specified for objective answer');
			$done = false;
		}
		$rightAnswer = implode('|', $rightAnswer);
	}
	else {
		$rightAnswer = isset($_POST['txtRightAnswer']) ? safe_html($_POST['txtRightAnswer']) : '';
	}
	$updates[] = "`quiz_rightanswer` = '{$rightAnswer}'";

	$updateQuery = "UPDATE `quiz_questions` SET " . implode(', ', $updates) . " WHERE `page_modulecomponentid` = $quizId AND `quiz_sectionid` = '$sectionId' AND `quiz_questionid` = '$questionId'";
	if (!mysql_query($updateQuery)) {
		displayerror('Database Error. Could not save section details. ' . $updateQuery . ' ' . mysql_error());
		return false;
	}

	return $done;
}
