<?php
if(!defined('__PRAGYAN_CMS'))
{ 
	header($_SERVER['SERVER_PROTOCOL'].' 403 Forbidden');
	echo "<h1>403 Forbidden<h1><h4>You are not authorized to access the page.</h4>";
	echo '<hr/>'.$_SERVER['SERVER_SIGNATURE'];
	exit(1);
}
function getLanguageSelectionBox($languageList) {
	if ($languageList == '')
		$languageList = array(
			'C',
			'C++',
			'Java',
			'C#'
		);
	else
		$languageList = split('@', $languageList);

	$html = '<select name="language" id="language">';
	for ($i = 0; $i < count($languageList); ++$i) {
		$html .= '<option value="' . $languageList[$i] . '">' . $languageList[$i] . '</option>';
	}
	$html .= '</select>';

	return $html;
}

function showSubmitForm($contestId, $problemId = -1) {
	global $userId;

	if ($userId < 0)
		return 'You need to <a href="./+login">login</a> to submit a solution.';

	if ($contestCode == '')
		$problemCode = '';
	else
		if (!isContestOpen($contestCode)) {
			display_error('Error. The specified contest is not open yet. Please check back later.');
			return '';
		}

	$problemSelect = '<tr><td><label for="problemcode">Problem Code:</label></td><td><input type="text" name="problemcode" id="problemcode" value="' . $problemCode . '" /></td></tr>';
	$contestSelect = '<tr><td><label for="contestcode">Contest:</label></td><td><input type="text" name="contestcode" id="contestcode" value="' . $contestCode . '" /></td></tr>';

	$languageList = '';
	if ($contestCode != '') {
		$contestRow = getContestRow($contestCode);
		if ($contestRow) {
			$contestId = $contestRow['cid'];
			if ($problemCode != '') {
				$problemRow = getProblemRow($contestId, $problemCode);
				if ($problemRow) {
					$languageList = $problemRow['plang'];
				}
			}
		}
	}

	$languageSelect = getLanguageSelectionBox($languageList);
	$languageSelect = '<tr><td><label for="languagebox">Language:</label></td><td>' . $languageSelect . '</td></tr>';

	$codeBox = <<<CODEBOX
		<tr><td><label for="codebox">Enter your code here:</label></td><td><textarea name="codebox" id="codebox" rows="10" cols="80"></textarea></td></tr>
		<tr><td><label for="fileuploadbox">Or upload your code here:</label></td><td><input type="hidden" name="MAX_FILE_SIZE" value="30000" /><input type="file" name="fileuploadbox" id="fileuploadbox" /></td></tr>
CODEBOX;

	return <<<SUBMITFORM
	<form name="solutionsubmit" method="POST" action="">
		<table border="0">
			$loginBox
			$contestSelect
			$problemSelect
			$languageSelect
			$codeBox
			$fileUploadBox
			<tr><td></td><td><input type="submit" name="btnSubmit" value="Submit Solution" /></td></tr>
		</table>
	</form>
SUBMITFORM;
}

function submitPostSolution($contestCode, $problemCode) {
	global $userId;

	$contestRow = getContestRow($contestCode);
	if ($contestRow === false) {
		display_error('Error. Could not find the specified contest.');
		return '';
	}

	$problemRow = getProblemRow($problemCode);
	if ($problemRow === false) {
		display_error('Error. Could not find the specified problem.');
		return '';
	}

	$allowableLanguages = split('@', $problemRow['plang']);

	if (!isset($_POST['language']) || !in_array($_POST['language'], $allowableLanguages)) {
		display_error('Error. No language specified, or submissions in the specified language are not allowed for this problem.');
		return '';
	}

	$uploadedSource = '';
	if (isset($_POST['codebox']) && $_POST['codebox'] != '')
		$uploadedSource = $_POST['codebox'];
	if (isset($_FILES['fileuploadbox']) && $_FILES['fileuploadbox']['error'] == UPLOAD_ERR_OK && is_uploaded_file($_FILES['fileuploadbox']['tmp_name'])) {
		$src = file_get_contents($_FILES['fileuploadbox']['tmp_name']);
		if ($src !== false) {
			$uploadedSource = $src;
			unlink($_FILES['fileuploadbox']['tmp_name']);
		}
	}

	if ($uploadedSource == '') {
		display_error('Error. Empty submission.');
		return '';
	}

	$time = time();
	$insertQuery = 'INSERT INTO `problem`(`pid`, `uid`, `lang`, `stid`, `status`, `code`, `runout`, `time`, `score`) VALUES ' .
		"({$problemRow['pid']}, {$userId}, '{$_POST['language']}', 1, 'Waiting', '$uploadedSource', '', $time, 0)";
	if (mysql_query($insertQuery))
		return "<p>Your solution has been submitted. Please check the <a href=\"../../status/\">status page</a> for more.</p>";
	else {
		display_error("Error. Could not insert submission into database.");
		return '';
	}
}

function submitSolution($contestCode, $problemCode) {
	global $userId;

	if ($userId > 0 && isset($_POST['codebox'])) {
		if (!isContestOpen($contestCode)) {
			display_error('Error. The specified contest is not open just yet. Please check back later.');
			return '';
		}

		return submitPostSolution($contestCode, $problemCode);
	}
	else
		return showSubmitForm($contestCode, $problemCode);
}
