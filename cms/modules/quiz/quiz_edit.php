<?php
/*
 * Created on Jan 4, 2008, 8:02:57 PM
 *
 * abhilash
 */



/**
 * Returns Html Tables summarizing all the questions associated with a quiz
 * @param $moduleComponentId Id of the quiz
 * @return String containing Html for the questions summary table
 */
function getQuestionsTable($moduleComponentId) {
	$questionQuery = 'SELECT * FROM `quiz_questions` WHERE `page_modulecomponentid` = ' . $moduleComponentId;
	$questionResult = mysql_query($questionQuery);
	$i = 1;

	$questionsTable =<<<QUESTIONSTABLE

	<table border="1">
		<tr>
			<th nowrap="nowrap">S. No.</th>
			<th nowrap="nowrap">Edit</th>
			<th nowrap="nowrap">Delete</th>
			<th nowrap="nowrap">Title</th>
			<th nowrap="nowrap">Question</th>
			<th nowrap="nowrap">Question Type</th>
			</tr>
QUESTIONSTABLE;

	global $sourceFolder, $urlRequestRoot, $templateFolder;
	$iconsFolderUrl = "$urlRequestRoot/$sourceFolder/$templateFolder/common/icons";
	$editImage = '<img alt="Edit" src="' . $iconsFolderUrl . '/16x16/apps/accessories-text-editor.png" style="padding: 0px" />';
	$deleteImage = '<img alt="Delete" src="' . $iconsFolderUrl . '/16x16/actions/edit-delete.png" style="padding: 0px" />';

	while($questionRow = mysql_fetch_assoc($questionResult)) {
		$question = $questionRow['quiz_question'];
		if(strlen($question) > 100) {
			$question = substr($question, 0, 100) . '...';
		}

		$questionsTable .= <<<QUESTIONSTABLE
			<tr>
				<td>$i</td>
				<th><a href="./+edit&subaction=editquestion&questionid={$questionRow['quiz_questionid']}">$editImage</a></th>
				<th><a href="./+edit&subaction=deletequestion&questionid={$questionRow['quiz_questionid']}">$deleteImage</a></th>
				<td>{$questionRow['quiz_questiontitle']}</td>
				<td>$question</td>
				<td>{$questionRow['quiz_questiontype']}</td>
				</tr>
QUESTIONSTABLE;

		$i++;
	}

	return $questionsTable . '</table>';
}

/**
 * Generate HTML for a form to help upload files to a quiz
 * @param $moduleComponentId Id of the quiz
 * @param $uploadFieldCount Optional field to specify how many upload fields must be shown in the form
 * @param $redirectPage Optional field to make the form redirect to a specific subaction
 *
function getQuizFileUploadForm($moduleComponentId, $uploadFieldCount = 1, $redirectPage = '') {
	global $sourceFolder;
	require_once("$sourceFolder/upload.lib.php");

	$uploadedFiles = getUploadedFiles($moduleComponentId, 'quiz');

	$fileUploadForm = <<<FILEUPLOADFORM
		<table border="1">
			<tr><th align="left" colspan="4">Uploaded Files:</th></tr>
			<tr><th>Upload Filename</th><th>File Type</th><th>Uploaded Time</th><th>Uploaded By</th></tr>
FILEUPLOADFORM;

	for($i = 0; $i < count($uploadedFiles); $i++) {
		$fileUploadForm .= '<tr><td><a href="./' . $uploadedFiles[$i]['upload_filename'] . '">' . $uploadedFiles[$i]['upload_filename'] . '</a></td>' .
				'<td>' . $uploadedFiles[$i]['upload_filetype'] . '</td>' .
				'<td>' . $uploadedFiles[$i]['upload_time'] . '</td>' .
				'<td>' . getUserName($uploadedFiles[$i]['user_id']) . '</td></tr>';
	}
	$fileUploadForm .= '</table>';

	if($redirectPage != '') {
		$redirectPage = '&redirectto=' . $redirectPage;
	}
	$fileUploadForm .= '<form name="quizFileUpload" method="POST" enctype="multipart/form-data" action="./+edit&subaction=uploadfile'.$redirectPage.'">';
	$fileUploadForm .= '<br /><strong>Upload new file(s):</strong><br />';
	for($i = 0; $i < $uploadFieldCount; $i++) {
		$fileUploadForm .= <<<FILEUPLOADFORM
			<input type="file" name="fileUpload[]" /><br />
FILEUPLOADFORM;
	}
	$fileUploadForm .= '<p><input type="submit" name="btnUploadFile" value="Upload File" /></p>' .
				'</form>';

	return $fileUploadForm;
}
*/

/**
 * Returns HTML for a form to edit properties of the quiz
 * @param $moduleComponentId Id of the quiz
 * @return String containing HTML for a form to edit the quiz
 */
function getQuizEditForm($moduleComponentId, $quizTypes) {
	$quizDescQuery = "SELECT `quiz_title`, `quiz_headertext`, `quiz_submittext`, `quiz_quiztype`, " .
			"TIME_FORMAT(`quiz_testduration`, '%H:%i') AS `quiz_testduration`, `quiz_questionspertest`, " .
			"`quiz_objectivecount`, `quiz_questiongrouping`, " .
			"DATE_FORMAT(`quiz_startdatetime`, '%Y-%m-%d %H:%i') AS `quiz_startdatetime`, " .
			"DATE_FORMAT(`quiz_enddatetime`, '%Y-%m-%d %H:%i') AS `quiz_enddatetime`, " .
			"`quiz_showtesttimer`, `quiz_showpagetimer` FROM `quiz_descriptions` WHERE `page_modulecomponentid` = $moduleComponentId";
	$quizDescResult = mysql_query($quizDescQuery) or die($quizDescQuery . '<br /> ' . mysql_error());
	$quizDescRow = mysql_fetch_assoc($quizDescResult);
	if(!$quizDescRow) {
		displayerror('Error! Information about the requested quiz could not be loaded.');
		return '';
	}

	$quizTypeBox = '';
	foreach($quizTypes as $quizTypeName => $quizTypeDescription) {
		$quizTypeBox .= '<option value="'.$quizTypeName.'"';
		if($quizTypeName == $quizDescRow['quiz_quiztype'])
			$quizTypeBox .= ' selected="selected"';
		$quizTypeBox .= ">$quizTypeDescription</option>\n";
	}

	$selShuffle = $quizDescRow['quiz_questiongrouping'] == 'shuffle' ? 'selected="selected"' : '';
	$selObjectiveFirst = $quizDescRow['quiz_questiongrouping'] == 'objectivefirst' ? 'selected="selected"' : '';
	$selSubjectiveFirst = $quizDescRow['quiz_questiongrouping'] == 'subjectivefirst' ? 'selected="selected"' : '';

	$chkPageTimerChecked = $quizDescRow['quiz_showpagetimer'] == 1 ? 'checked="checked"' : '';
	$chkTestTimerChecked = $quizDescRow['quiz_showtesttimer'] == 1 ? 'checked="checked"' : '';

	$quizEditForm = '';
	$jsDeclarationFunction = 'get' . ucfirst($quizDescRow['quiz_quiztype']) . 'QuizJSDefinitions';
	if(function_exists($jsDeclarationFunction)) {
		$quizEditForm = '<script type="text/javascript" language="javascript">' . $jsDeclarationFunction($moduleComponentId) . '</script>';
	}

	global $sourceFolder, $urlRequestRoot, $moduleFolder, $templateFolder;
	$quizFolderUrl = "$urlRequestRoot/$sourceFolder/$moduleFolder/quiz";
	$iconsFolderUrl = "$urlRequestRoot/$sourceFolder/$templateFolder/common/icons";
	$calpath = "$urlRequestRoot/$sourceFolder/$moduleFolder";

	$quizEditForm .=<<<QUIZEDITFORM
		<script language="javascript" type="text/javascript" src="$quizFolderUrl/quizeditform.js">
		</script>
		<br /><h3>Edit Quiz Properties</h3><br />
		<form name="quizEditForm" method="POST" action="./+edit">
			<link rel="stylesheet" type="text/css" media="all" href="$calpath/form/calendar/calendar.css" title="Aqua" />
			<script type="text/javascript" src="$calpath/form/calendar/calendar.js"></script>
			<table>
				<tr><td><label for="txtQuizTitle">Quiz Title:</label></td><td><input type="text" name="txtQuizTitle" id="txtQuizTitle" value="{$quizDescRow['quiz_title']}" /></td></tr>
				<tr><td><label for="txtQuizHeaderText">Header Text:</label></td><td><input type="text" name="txtQuizHeaderText" id="txtQuizHeaderText" value="{$quizDescRow['quiz_headertext']}" /></td></tr>
				<tr><td><label for="txtQuizSubmitText">Submit Message:</label></td><td><input type="text" name="txtQuizSubmitText" id="txtQuizSubmitText" value="{$quizDescRow['quiz_submittext']}" /></td></tr>
				<tr><td><label for="txtQuizDuration">Duration of Test (HH:MM):</label></td><td><input type="text" name="txtQuizDuration" id="txtQuizDuration" value="{$quizDescRow['quiz_testduration']}" /></td></tr>
				<tr><td><label for="txtQuestionsPerTest">Questions Per Test:</label></td><td><input type="text" name="txtQuestionsPerTest" id="txtQuestionsPerTest" value="{$quizDescRow['quiz_questionspertest']}" /></td></tr>
				<tr><td><label for="txtObjectiveCount">Number of Objective Questions:</label></td><td><input type="text" name="txtObjectiveCount" id="txtObjectiveCount" value="{$quizDescRow['quiz_objectivecount']}" /></td></tr>
				<tr>
					<td><label for="selQuestionGrouping">Question Grouping:</label></td>
					<td>
						<select id="selQuestionGrouping" name="selQuestionGrouping">
							<option value="shuffle" $selShuffle>Shuffle</option>
							<option value="objectivefirst" $selObjectiveFirst>Objective First</option>
							<option value="subjectivefirst" $selSubjectiveFirst>Subjective First</option>
						</select>
					</td>
				</tr>
				<tr><td><label for="txtQuizStartTime">Quiz Opens On (YYYY-MM-DD HH:MM):</label></td><td><input type="text" name="txtQuizStartTime" id="txtQuizStartTime" value="{$quizDescRow['quiz_startdatetime']}" /><input name="calQuizStartTime" type="reset" value=" ... " onclick="return showCalendar('txtQuizStartTime', '%Y-%m-%d %H:%M', '24', true);" /></td></tr>
				<tr><td><label for="txtQuizEndTime">Quiz Closes On (YYYY-MM-DD HH:MM):</label></td><td><input type="text" name="txtQuizEndTime" id="txtQuizEndTime" value="{$quizDescRow['quiz_enddatetime']}" /><input name="calQuizEndTime" type="reset" value=" ... " onclick="return showCalendar('txtQuizEndTime', '%Y-%m-%d %H:%M', '24', true);" /></td></tr>
				<tr>
					<td>Show Timer?</td>
					<td>
						<label><input type="checkbox" name="chkShowPageTimer" value="perpage" $chkPageTimerChecked />Per Page</label>
						<label><input type="checkbox" name="chkShowTestTimer" value="pertest" $chkTestTimerChecked />Per Test</label>
					</td>
				</tr>
				<tr>
					<td>Quiz Type:</td>
					<td><select name="selQuizType">$quizTypeBox</select></td>
				</tr>
			</table>
			<br /><br />
			<input type="submit" name="btnSubmitQuizEditForm" value="Save Quiz Properties" />
		</form>
		<br />
		<a href="./+edit&subaction=typespecoptions">Quiz Type Specific Options</a>
		<br /><br />

QUIZEDITFORM;

	$quizEditForm .= getQuestionsTable($moduleComponentId);
	$quizEditForm .= '<br /><a href="./+edit&subaction=addquestions"><img src="'. $iconsFolderUrl .'/16x16/actions/list-add.png" alt="Add New Question" style="padding:0px" />Add More Questions</a><br /><br />';

	include_once("$sourceFolder/upload.lib.php");
	$quizEditForm .= getUploadedFilePreviewDeleteForm($moduleComponentId, 'quiz', './+edit');
	$quizEditForm .= '<br />' . getFileUploadForm($moduleComponentId, 'quiz', './+edit',15*1024*1024);

	return $quizEditForm;
}

/**
 * Checks whether a duration entered by a user is of the format HH:MM
 * @param $duration The input by the user that is to be validated
 * @return Boolean, true indicating that the input is valid, false indicating otherwise
 */
function validateDuration($duration) {
	return preg_match('/^\d{2}:[0-5][0-9]$/', $duration);
}

/**
 * Checks whether a Date-Time entered by a user is of the format YYYY-MM-DD HH:MM
 * @param $datetime The input by the user that is to be validated
 * @return Boolean, true indicating that the input is valid, false indicating otherwise
 */
function validateDateTime($datetime) {
	global $sourceFolder, $moduleFolder;
	include_once("$sourceFolder/$moduleFolder/form/registrationformsubmit.php");
	$arr = explode(' ', $datetime);

	if(count($arr) != 2) {
		displayerror('Invalid Date/Time entered. Enter the time in the format YYYY-MM-DD HH:MM');
		return false;
	}

	return verifyDate($arr[0]) && verifyTime($arr[1]);
}

/**
 * Submits the data entered by a user in a form generated by getQuizEditForm
 * @param $moduleComponentId Id of the quiz
 * @param $quizTypes Different allowable types of quizzes
 * @return Boolean, true indicating success, false indicating failure
 */
function submitQuizEditForm($moduleComponentId, &$quizTypes) {
	$updates = array();

	if(isset($_POST['txtQuizTitle'])) $updates[] = "`quiz_title` = '{$_POST['txtQuizTitle']}'";
	if(isset($_POST['txtQuizHeaderText'])) $updates[] = "`quiz_headertext` = '{$_POST['txtQuizHeaderText']}'";
	if(isset($_POST['txtQuizSubmitText'])) $updates[] = "`quiz_submittext` = '{$_POST['txtQuizSubmitText']}'";
	if(isset($_POST['txtQuizDuration']) && validateDuration($_POST['txtQuizDuration']))
		$updates[] = "`quiz_testduration` = '{$_POST['txtQuizDuration']}'";
	else
		displayerror('Invalid time period provided for Quiz Duration');
	if(isset($_POST['txtQuestionsPerTest']) && ctype_digit($_POST['txtQuestionsPerTest'])) $updates[] = "`quiz_questionspertest` = '{$_POST['txtQuestionsPerTest']}'";
//	if(isset($_POST['txtQuestionsPerPage']) && ctype_digit($_POST['txtQuestionsPerPage'])) $updates[] = "`quiz_questionsperpage` = '{$_POST['txtQuestionsPerPage']}'";
	if(isset($_POST['txtObjectiveCount']) && ctype_digit($_POST['txtObjectiveCount']) && $_POST['txtObjectiveCount'] <= $_POST['txtQuestionsPerTest']) $updates[] = "`quiz_objectivecount` = '{$_POST['txtObjectiveCount']}'";
	if(isset($_POST['selQuestionGrouping'])) $updates[] = "`quiz_questiongrouping` = '{$_POST['selQuestionGrouping']}'";

	if(isset($_POST['txtQuizStartTime']) && validateDateTime($_POST['txtQuizStartTime'])) $updates[] = "`quiz_startdatetime` = '{$_POST['txtQuizStartTime']}'";
	if(isset($_POST['txtQuizEndTime']) && validateDateTime($_POST['txtQuizEndTime'])) $updates[] = "`quiz_enddatetime` = '{$_POST['txtQuizEndTime']}'";

	$updates[] = '`quiz_showpagetimer` = ' . (isset($_POST['chkShowPageTimer']) ? '1' : '0');
	$updates[] = '`quiz_showtesttimer` = ' . (isset($_POST['chkShowTestTimer']) ? '1' : '0');

	$quizType = getQuizType($moduleComponentId);
	if(isset($_POST['selQuizType'])) {
		if(array_key_exists($_POST['selQuizType'], $quizTypes)) {
			$updates[] = "`quiz_quiztype` = '{$_POST['selQuizType']}'";
			$quizType = $_POST['selQuizType'];
		}
		else {
			displayerror('Invalid quiz type.');
		}
	}

	$updateQuery = 'UPDATE `quiz_descriptions` SET ' . join($updates, ', ') . ' WHERE `page_modulecomponentid` = ' . $moduleComponentId;
	if(!mysql_query($updateQuery)) {
		displayerror('Could not update database: ' . $updateQuery . '<br />' . mysql_error());
		return false;
	}

	return true;
}




///////////////////////////////////////////////////////////////////////////////
///                                                                         ///
/// Functions to deal with Questions                                        ///
///                                                                         ///
///////////////////////////////////////////////////////////////////////////////


/**
 * Adds a default template question to a quiz
 * @param $moduleComponentId Id of the quiz
 */
function addDefaultQuestion($moduleComponentId) {
	$newQuestionId = 1;
	$questionIdQuery = 'SELECT MAX(`quiz_questionid`) FROM `quiz_questions` WHERE `page_modulecomponentid` = ' . $moduleComponentId;
	$questionIdResult = mysql_query($questionIdQuery);
	$questionIdRow = mysql_fetch_row($questionIdResult);
	if(!is_null($questionIdRow[0])) {
		$newQuestionId = $questionIdRow[0] + 1;
	}

	$insertQuery = 'INSERT INTO `quiz_questions` ' .
			'(`page_modulecomponentid`, `quiz_questionid`, `quiz_questiontitle`, `quiz_question`) VALUES ' .
			"($moduleComponentId, $newQuestionId, 'New Question', 'Is this your new question?')";
	if(!mysql_query($insertQuery)) {
		displayerror('An unknown error occurred while trying to insert a new question.');
	}
}

/**
 * Deletes a question from a quiz's question bank
 * @param $moduleComponentId Id of the quiz
 * @param $questionId Id of a question
 */
function deleteQuestion($moduleComponentId, $questionId) {
	$deleteQuery = 'DELETE FROM `quiz_questions` WHERE `page_modulecomponentid` = ' . $moduleComponentId . ' AND `quiz_questionid` = ' . $questionId;
	mysql_query($deleteQuery);
	$deleteQuery = 'DELETE FROM `quiz_questionoptions` WHERE `page_modulecomponentid` = ' . $moduleComponentId . ' AND `quiz_questionid` = ' . $questionId;
	mysql_query($deleteQuery);
}

/**
 * Determines the type of a given quiz
 * @param $moduleComponentId Id of the quiz
 * @return String containing the quiz type
 */
function getQuizType($moduleComponentId) {
	$quizTypeQuery = 'SELECT `quiz_quiztype` FROM `quiz_descriptions` WHERE `page_modulecomponentid` = ' . $moduleComponentId;
	$quizTypeResult = mysql_query($quizTypeQuery);
	if(!$quizTypeResult) {
		return '';
	}
	$quizTypeRow = mysql_fetch_row($quizTypeResult);
	if(!$quizTypeRow) {
		return '';
	}

	return $quizTypeRow[0];
}




///////////////////////////////////////////////////////////////////////////////
///                                                                         ///
/// Quiz Type Specific Functions                                            ///
///                                                                         ///
///////////////////////////////////////////////////////////////////////////////


///////////////////////////////////////////////////////////////////////////////
///	Simple Quiz                                                             ///
///////////////////////////////////////////////////////////////////////////////
/**
 * Generates HTML for a form to edit the type specific options for a simple quiz
 * @param $moduleComponentId Id of the quiz
 * @return String containing HTML for the edit form
 */
function getSimpleQuizEditForm($moduleComponentId) {
	$quizDescQuery = 'SELECT `quiz_questionsperpage` FROM `quiz_descriptions` WHERE `page_modulecomponentid` = ' . $moduleComponentId;
	$quizDescResult = mysql_query($quizDescQuery);
	$quizDescRow = mysql_fetch_row($quizDescResult);
	$questionsPerPage = $quizDescRow[0];

	$markQuery = 'SELECT `quiz_weightpositivemarks`, `quiz_weightnegativemarks` FROM `quiz_weightmarks` WHERE `page_modulecomponentid` = ' . $moduleComponentId . ' AND `quiz_questionweight` = 1';
	$markResult = mysql_query($markQuery);
	$markRow = mysql_fetch_row($markResult);
	$positiveMarks = 3;
	$negativeMarks = 1;
	if($markRow) {
		$positiveMarks = $markRow[0];
		$negativeMarks = $markRow[1];
	}

	$quizEditForm = <<<QUIZEDITFORM
		<br /><h2>Quiz Type Specific Options</h2><br />

		<form name="quizEditForm" method="POST" action="./+edit&subaction=typespecoptions">
			<table>
				<tr>
					<td><label for="txtQuestionsPerPage">Questions Per Page:</label></td>
					<td><input type="text" name="txtQuestionsPerPage" id="txtQuestionsPerPage" value="$questionsPerPage" /></td>
				</tr>
				<tr>
					<td><label for="txtPositiveMarks">Marks to be awarded for a correct answer:</label></td>
					<td><input type="text" name="txtPositiveMarks" id="txtPositiveMarks" value="$positiveMarks" />
				</tr>
				<tr>
					<td><label for="txtNegativeMarks">Marks to be deducted for a wrong answer:</label></td>
					<td><input type="text" name="txtNegativeMarks" id="txtNegativeMarks" value="$negativeMarks" />
				</tr>
			</table>
			<br />
			<input type="submit" name="btnSubmitQuizEditForm" value="Save Changes" />
		</form>

		<br />
		<a href="./+edit">Back to Quiz Properties</a>
QUIZEDITFORM;

	return $quizEditForm;
}

/**
 * Submits a form generated by getSimpleQuizEditForm
 * @param $moduleComponentId Id of the quiz
 * @return Boolean, true indicating success, false indicating failure
 */
function submitSimpleQuizEditForm($moduleComponentId) {
	$updates = array();
	$updateErrors = false;

	if(isset($_POST['txtQuestionsPerPage']) && ctype_digit($_POST['txtQuestionsPerPage'])) {
		$updateQuery = "UPDATE `quiz_descriptions` SET `quiz_questionsperpage` = {$_POST['txtQuestionsPerPage']} WHERE `page_modulecomponentid` = $moduleComponentId";
		if(!mysql_query($updateQuery)) {
			displayerror('Could not find information for the quiz.');
			return false;
		}
	}

	$existsQuery = 'SELECT 1 FROM `quiz_weightmarks` WHERE `page_modulecomponentid` = ' . $moduleComponentId . ' AND `quiz_questionweight` = 1';
	$existsResult = mysql_query($existsQuery);
	$exists = mysql_num_rows($existsResult) > 0;

	$positiveMarks = 3;
	$negativeMarks = 1;
	if(isset($_POST['txtPositiveMarks']) && strval(floatval($_POST['txtPositiveMarks'])) == $_POST['txtPositiveMarks']) {
		if($exists)
			$updates[] = '`quiz_weightpositivemarks` = ' . $_POST['txtPositiveMarks'];
		else
			$positiveMarks = $_POST['txtPositiveMarks'];
	}
	if(isset($_POST['txtNegativeMarks']) && strval(floatval($_POST['txtNegativeMarks'])) == $_POST['txtNegativeMarks']) {
		if($exists)
			$updates[] = '`quiz_weightnegativemarks` = ' . $_POST['txtNegativeMarks'];
		else
			$negativeMarks = $_POST['txtNegativeMarks'];
	}

	if(count($updates) > 0 || !$exists) {
		if($exists) {
			$updateQuery = 'UPDATE `quiz_weightmarks` SET ' . join($updates, ', ') . " WHERE `page_modulecomponentid` = $moduleComponentId AND `quiz_questionweight` = 1";
		}
		else {
			$updateQuery = 'INSERT INTO `quiz_weightmarks` (`page_modulecomponentid`, `quiz_questionweight`, `quiz_weightpositivemarks`, `quiz_weightnegativemarks`) VALUES' .
					"($moduleComponentId, 1, $positiveMarks, $negativeMarks)";
		}
		if(!mysql_query($updateQuery)) {
			displayerror($updateQuery . ' ' . mysql_error());
			return false;
		}
	}

	return true;
}


///////////////////////////////////////////////////////////////////////////////
///	GRE Quiz                                                                ///
///////////////////////////////////////////////////////////////////////////////

function getGreQuizWeightRange($moduleComponentId) {
	$quizDescQuery = 'SELECT MAX(`quiz_questionweight`) FROM `quiz_weightmarks` WHERE `page_modulecomponentid` = ' . $moduleComponentId;
	$quizDescResult = mysql_query($quizDescQuery);
	$quizDescRow = mysql_fetch_row($quizDescResult);
	return is_null($quizDescRow[0]) ? 0 : $quizDescRow[0];
}

function getGreQuizEditForm($moduleComponentId) {
	$weightRange = getGreQuizWeightRange($moduleComponentId);

	if($weightRange != 0) {
		$positiveRow = $negativeRow = $headerRow = '<tr>';

		$headerRow .= '<th>Weights:</th>';
		$positiveRow .= '<th>Positive Marks:</th>';
		$negativeRow .= '<th>Negative Marks:</th>';

		$quizDescQuery = 'SELECT `quiz_questionweight`, `quiz_weightpositivemarks`, `quiz_weightnegativemarks` ' .
				"FROM `quiz_weightmarks` WHERE `page_modulecomponentid` = $moduleComponentId ORDER BY `quiz_questionweight`";
		$quizDescResult = mysql_query($quizDescQuery);

		while($quizDescRow = mysql_fetch_assoc($quizDescResult)) {
			$headerRow .= "<td>{$quizDescRow['quiz_questionweight']}</td>\n";
			$positiveRow .= "<td>{$quizDescRow['quiz_weightpositivemarks']}</td>\n";
			$negativeRow .= "<td>{$questionDescRow['quiz_weightnegativemarks']}</td>\n";
		}
		$headerRow .= '</tr>';
		$negativeRow .= '</tr>';
		$positiveRow .= '</tr>';
	}

	$quizEditForm = <<<QUIZEDITFORM
		<script language="javascript" type="text/javascript">
		<!--
			function showWeightMarksTable(count) {
				if(isNaN(count) || parseInt(count) < 0 || parseInt(count) > 10) return;
				count = parseInt(count);
				positiveMarkBoxes = document.getElementsByName('txtPositiveMarks[]');
				negativeMarkBoxes = document.getElementsByName('txtNegativeMarks[]');

				var headerRow = positiveRow = negativeRow = '<tr>';
				headerRow += '<th>Weights:</th>';
				positiveRow += '<th>Positive Marks:</th>';
				negativeRow += '<th>Negative Marks:</th>';

				for(i = 0; i < count; i++) {
					headerRow += '<th>' + i + '</th>';
					positiveRow += '<td><input type="text" name="txtPositiveMarks[]" size="5" maxlength="5" value="';
					if(positiveMarkBoxes.length > i)
						positiveRow += positiveMarkBoxes[i].value;
					positiveRow += '" /></td>';
					negativeRow += '<td><input type="text" name="txtNegativeMarks[]" size="5" maxlength="5" value="';
					if(negativeMarkBoxes.length > i)
						negativeRow += negativeMarkBoxes[i].value;
					negativeRow += '" /></td>';
				}
				headerRow += '</tr>';
				positiveRow += '</tr>';
				negativeRow += '</tr>';

				document.getElementById('weightmarks').innerHTML = headerRow + positiveRow + negativeRow;
			}
		-->
		</script>

		<form name="quizEditForm" method="POST" action="./+edit&subaction=typespecoptions">
			<table>
				<tr>
					<td><label for="txtWeightRange">Weight Range:</label></td>
					<td><input type="text" onchange="showWeightMarksTable(this.value)" name="txtWeightRange" id="txtWeightRange" value="{$quizDescRow[0]}" /></td>
				</tr>
			</table>
			<table id="weightmarks">
				$headerRow
				$positiveRow
				$negativeRow
			</table>
			<br />
			<input type="submit" name="btnSubmitQuizEditForm" value="Save Changes" />
		</form>

		<br />
		<a href="./+edit">Back to Quiz Properties</a>
QUIZEDITFORM;

	return $quizEditForm;
}

function submitGreQuizEditForm($moduleComponentId) {
	if(isset($_POST['txtWeightRange']) && ctype_digit($_POST['txtWeightRange']) && $_POST['txtWeightRange'] > 0 && $_POST['txtWeightRange'] < 10) {

	}
}

?>