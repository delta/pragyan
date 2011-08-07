<?php
if(!defined('__PRAGYAN_CMS'))
{ 
	header($_SERVER['SERVER_PROTOCOL'].' 403 Forbidden');
	echo "<h1>403 Forbidden<h1><h4>You are not authorized to access the page.</h4>";
	echo '<hr/>'.$_SERVER['SERVER_SIGNATURE'];
	exit(1);
}
/*
 * Created on Jan 23, 2009
 */

function isQuizEvaluated($quizId) {
	$countQuery = "SELECT COUNT(*) FROM `quiz_userattempts` WHERE `quiz_marksallotted` IS NULL AND `page_modulecomponentid` = '$quizId'";
	$countResult = mysql_query($countQuery);
	$countRow = mysql_fetch_row($countResult);
	return $countRow[0] == 0;
}

/**
 * For every user who has taken a quiz, updates marks allotted for all questions the user has submitted (except subjective questions) in quiz_answersubmissions
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
//	echo $updateQuery;

	$updateResult = mysql_query($updateQuery);
	if (!$updateResult) {
		displayerror('Database Error. Could not correct questions.');
		return false;
	}
	
	return true;
}
/**
 * updates total scores in quiz_userattempts.
 * @return Boolean True indicating success, and false indicating errors.
*/
function updateSectionMarks($quizId) {
	$updateQuery = "UPDATE `quiz_userattempts` SET `quiz_marksallotted` = (" .
			"SELECT SUM(`quiz_marksallotted`) FROM `quiz_answersubmissions` WHERE " .
			"`quiz_answersubmissions`.`user_id` = `quiz_userattempts`.`user_id` AND " .
			"`quiz_answersubmissions`.`quiz_sectionid` = `quiz_userattempts`.`quiz_sectionid` AND " .
			"`quiz_answersubmissions`.`page_modulecomponentid` = '$quizId'" .
			") WHERE `page_modulecomponentid` = '$quizId'";
	$updateResult = mysql_query($updateQuery);

	if (!$updateResult) {
		displayerror('Database Error. Could not update section marks. ' . $updateQuery . ' ' . mysql_error());
		return false;
	}

	return true;
}

/**
 * function getWeights:
 * returns all weights which has not be assigned marks, in weightmarks
 */
function getWeights($quizId) {
	$weighs = array();
	$result = mysql_query("SELECT `quiz_questionweight` FROM `quiz_questions` WHERE `page_modulecomponentid` = '$quizId' AND `quiz_questionweight` NOT IN (SELECT `question_weight` FROM `quiz_weightmarks` WHERE `page_modulecomponentid` = '$quizId')");
	while($row = mysql_fetch_assoc($result))
		$weighs[] = $row['quiz_questionweight'];
	return $weighs;
}

/**
 * function getQuizUserListHtml:
 * Generates the User list with their total, section specific marks
 */
function getQuizUserListHtml($quizId) {
	// Evaluate the quiz,
	// retrieve list of users and their total marks, and display
	$quizRow = getQuizRow($quizId);
	$weights = getWeights($quizId);
	
	if(count($weights) > 0) {
		displayerror("Marks for questions with weight " . join(", ", $weights) . " is not set. Correct the quiz after setting marks for all weigh. You can set that in <a href='./+edit#quizWeightMarks'>Edit</a>.");
		return '';
	}

	if (isset($_POST['btnCalculateMarks']))
	{
		evaluateQuiz($quizId);
		updateSectionMarks($quizId);
	}
	
	
	$tableJqueryStuff="";
	$numSecColumns=0;
	$userTable = MYSQL_DATABASE_PREFIX . 'users';
	$markQuery = "SELECT `$userTable`.`user_email` AS `email`, `$userTable`.`user_id` AS `user_id`, SUM(`quiz_marksallotted`) AS `total`, MIN(`quiz_attemptstarttime`) AS `starttime`, MAX(`quiz_submissiontime`) AS `finishtime`, TIMEDIFF(MAX(`quiz_submissiontime`), MIN(`quiz_attemptstarttime`)) AS `timetaken` FROM `$userTable`, `quiz_userattempts` WHERE " .
			"`$userTable`.`user_id` = `quiz_userattempts`.`user_id` AND " .
			"`quiz_userattempts`.`page_modulecomponentid` = '$quizId' " .
			"GROUP BY `quiz_userattempts`.`user_id` ORDER BY `total` DESC, `timetaken`, `starttime`, `finishtime`, `email`";
	
	$profileQuery = 'SELECT `form_elementname` FROM `form_elementdesc` WHERE `page_modulecomponentid` = 0 ORDER BY `form_elementrank`';
	$profileResult = mysql_query($profileQuery);
	$profilecolumns=array();
	while($profileRow = mysql_fetch_row($profileResult)) {
		$profilecolumns['form0_' . $profileRow[0]] = $profileRow[0];
	}

	
	$markResult = mysql_query($markQuery);
	if (!$markResult) {
		displayerror($markQuery . '  ' . mysql_error());
	}
	$query = mysql_fetch_array(mysql_query("SELECT `quiz_title` FROM `quiz_descriptions` WHERE `page_modulecomponentid` = '$quizId'"));
	$result = mysql_query("SELECT `quiz_sectiontitle` FROM `quiz_sections` WHERE `page_modulecomponentid` = '$quizId' ORDER BY `quiz_sectionid`");
	$sectionHead = "";
	$secCols="";
	$toggleColumns="<tr><td><input type='checkbox' onclick='fnShowHide(0);' checked />User Full Name<br/></td>";
	$toggleColumns.="<td><input type='checkbox' onclick='fnShowHide(1);' />User Email<br/></td>";
	$toggleColumns.="<td><input type='checkbox' onclick='fnShowHide(2);' checked />Marks<br/></td>";
	$toggleColumns.="<td><input type='checkbox' onclick='fnShowHide(3);' checked />Time Taken<br/></td>";
	$toggleColumns.="<td><input type='checkbox' onclick='fnShowHide(4);' />Started<br/></td>";
	$toggleColumns.="<td><input type='checkbox' onclick='fnShowHide(5);' />Finished<br/></td>";
	
	$c=6;
	while($row = mysql_fetch_array($result))
	{
		$sectionHead .= "<th>Section : {$row['quiz_sectiontitle']}</th>";
		$tableJqueryStuff.="null,";
		if($c%6==0) $secCols.="</tr><tr>";
		$secCols.="<td><input type='checkbox' onclick='fnShowHide($c);' checked />Section : {$row['quiz_sectiontitle']}<br/></td>";		
		$numSecColumns++;
		$c++;
	}
	$toggleColumns.=$secCols;
	
	$columnNames=array();	
	foreach($profilecolumns as $columnName => $columnTitle) 
	{
		$sectionHead .= "<th>$columnTitle</th>\n";
		$columnNames[] = $columnName;
		
		
		$checked="checked";
		if(!($columnName=="useremail" || $columnName=="registrationdate" || $columnName=="lastupdated"))
		{
			$tableJqueryStuff.="/* $columnTitle */ { \"bVisible\": false },";
			$checked="";
		}
		else $tableJqueryStuff.="null,";
	
		if($c%6==0)
		 $toggleColumns.="</tr><tr>";
		$toggleColumns.="<td><input type='checkbox' onclick='fnShowHide($c);' $checked />$columnTitle <br/></td>";
		
		$c=$c+1;
	}
	
	$toggleColumns.="</tr>";
	
	global $urlRequestRoot, $cmsFolder, $STARTSCRIPTS;
	
	$tableJqueryStuff=<<<STUFF
							null,
							{ "bVisible": false },
							null,
							null,
							{ "bVisible": false },
							{ "bVisible": false },
							$tableJqueryStuff
							null
STUFF;
	
	$smarttable = smarttable::render(array('userstable'),array('userstable'=>array('aoColumns'=>"$tableJqueryStuff")));
	
	$STARTSCRIPTS.="initSmartTable();";
	
	
	$userListHtml = <<<HEAD
	$smarttable
	<script type="text/javascript" charset="utf-8">
			function fnShowHide( iCol )
			{
				var bVis = oTable.fnSettings().aoColumns[iCol].bVisible;
				oTable.fnSetColumnVis( iCol, bVis ? false : true );
			}

	</script>

HEAD;
	
	global $ICONS_SRC,$ICONS;
	$quizName=$query[0];
	$userListHtml .= "<h3>User Submissions for Quiz: {$query[0]}</h3>
		<fieldset><legend>Select Columns</legend><table>$toggleColumns</table></fieldset>" .
		"<form action='./+correct' method=POST><input type='submit' value='Calculate Marks' name='btnCalculateMarks' />
		<form action='./+correct' method=POST><input type='submit' value='Save As Excel' name='save_as_excel' /></form>";
	$userListTable = "
		<table class=\"userlisttable display\" border=\"1\" id='userstable'>" .
		"<thead><tr><th>User Full Name</th><th>User Email</th><th>Total Marks</th><th>Time Taken</th><th>Started</th><th>Finished</th>$sectionHead<th>Action</th></tr></thead><tbody>";
		
		
	while ($markRow = mysql_fetch_assoc($markResult)) {
		$userMarks = "";
		$marksResult = mysql_query("SELECT `quiz_marksallotted`,`quiz_sectionid` FROM `quiz_userattempts` WHERE `user_id` = '{$markRow['user_id']}' AND `page_modulecomponentid` = '$quizId' ORDER BY `quiz_sectionid`");
		$cc=1;
		
		while($row = mysql_fetch_array($marksResult))
		{
			
			if($row['quiz_sectionid']!=$cc) // To check if some sections are missing, if yes then add NA value
			{	
				while($row['quiz_sectionid']>$cc) { $userMarks .= "<td>-0</td>"; $cc++; }
			}
			$userMarks .= "<td>{$row['quiz_marksallotted']}</td>";
			$cc++;
			
		}
		
		while($cc<=$numSecColumns) {  $userMarks .= "<td>-0</td>"; $cc++;}
		
		if (is_null($markRow['finishtime'])) {
			$markRow['finished'] = 0;
			$markRow['finishtime'] = 'NULL';
		}
		$userfullname=getUserFullNameFromEmail($markRow['email']);
		
		$elementDataQuery = 'SELECT `form_elementdata`, `form_elementdesc`.`form_elementid`, `form_elementdesc`.`form_elementname`, `form_elementdesc`.`form_elementtype` FROM `form_elementdesc`, `form_elementdata` WHERE ' .
						"`form_elementdata`.`page_modulecomponentid` = 0 AND `user_id` = '{$markRow['user_id']}' AND " .
						"`form_elementdata`.`page_modulecomponentid` = `form_elementdesc`.`page_modulecomponentid` AND " .
						"`form_elementdata`.`form_elementid` = `form_elementdesc`.`form_elementid` ORDER BY `form_elementrank`";
			$elementDataResult = mysql_query($elementDataQuery) or die($elementDataQuery . '<br />' . mysql_error());
			$elementRow=array();
			while($elementDataRow = mysql_fetch_assoc($elementDataResult)) {
				$elementRow['form0_' . $elementDataRow['form_elementname']] = $elementDataRow['form_elementdata'];
				if($elementDataRow['form_elementtype'] == 'file') {
					$elementRow['form0_' . $elementDataRow['form_elementname']] = '<a href="./'.$elementDataRow['form_elementdata'].'">' . $elementDataRow['form_elementdata'] . '</a>';
				}
			}
			
			
	        $display=array();
		$columnCount = count($columnNames);
		for($i = 0; $i < count($columnNames); $i++) {
			if(isset($elementRow[$columnNames[$i]])) {
				$display[] = $elementRow[$columnNames[$i]];
			}
//			else {
	//			$display[] = ' ';
		//	}
		}
		
		$profileStuff = '';
		if(count($display))
		$profileStuff='<td>'.join($display,'</td><td>').'</td>';
		
		
		
		if($userfullname=="") $userfullname="Anonymous";
		
		$userListTable .= "<tr><td>$userfullname</td><td>{$markRow['email']}</td><td>{$markRow['total']}</td><td>{$markRow['timetaken']}</td><td>{$markRow['starttime']}</td><td>{$markRow['finishtime']}</td>$userMarks $profileStuff";
		
		
		$userListTable .= '<td><form name="userclearform" method="POST" action=""><input type="hidden" name="hdnUserId" id="hdnUserId" value="' . $markRow['user_id'] . "\" /><a href=\"./+correct&useremail={$markRow['email']}\">".$ICONS['Correct']['small'].'</a><input type="image" src="'.$ICONS_SRC["Delete"]["small"].'" name="btnDeleteUser" id="btnDeleteUser" value="Reject Submission" title="Reject Submission"/></form></td>';
		
		
		$userListTable .= "</tr>\n";
	}
	$userListTable .= "</tbody></table>\n";


	if(isset($_POST['save_as_excel']))
	{
		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Cache-Control: private",false); 
		header("Content-Type: application/vnd.ms-excel");
		header("Content-Disposition: attachment; filename=\"$quizName.xls\";" );
		header("Content-Transfer-Encoding: binary");
		echo $userListTable;
		exit(1);
	}

	return $userListHtml.$userListTable;
}

/**
 * fuction getQuizCorrectForm:
 * returns form where user answers submissions will be displayed, marks can be alloted for subjective answers
 */
function getQuizCorrectForm($quizId, $userId) {
	$marks = mysql_fetch_array(mysql_query("SELECT SUM(`quiz_marksallotted`) AS `total`, MIN(`quiz_attemptstarttime`) AS `starttime`, MAX(`quiz_submissiontime`) AS `finishtime`, TIMEDIFF(MAX(`quiz_submissiontime`), MIN(`quiz_attemptstarttime`)) AS `timetaken` FROM `quiz_userattempts` WHERE `user_id` = '{$userId}' AND `page_modulecomponentid` = '$quizId'"));
	$title = mysql_fetch_array(mysql_query("SELECT `quiz_title` FROM `quiz_descriptions` WHERE `page_modulecomponentid` = '$quizId'"));

	$correctFormHtml = "";
	$sectionHead="";

	$sections = mysql_query("SELECT `quiz_sections`.`quiz_sectiontitle` AS `quiz_sectiontitle`, `quiz_sections`.`quiz_sectionid` AS `quiz_sectionid`, `quiz_marksallotted` FROM `quiz_userattempts` JOIN `quiz_sections` ON `quiz_userattempts`.`quiz_sectionid` = `quiz_sections`.`quiz_sectionid` WHERE `user_id` = '$userId' AND `quiz_userattempts`.`page_modulecomponentid` = '$quizId' AND `quiz_sections`.`page_modulecomponentid` = '$quizId'");
	
	while($sectionsRow = mysql_fetch_array($sections)) {
	$correctFormHtml .= "<h4>{$sectionsRow['quiz_sectiontitle']}(Marks: {$sectionsRow['quiz_marksallotted']})</h4>";
	$sectionHead .="<td><b>{$sectionsRow['quiz_sectiontitle']}</b> section marks: {$sectionsRow['quiz_marksallotted']}</td>";
	
	
	$questionQuery = "SELECT `quiz_questions`.`quiz_questionid` AS `quiz_questionid`, " .
			"`quiz_questions`.`quiz_question` AS `quiz_question`, `quiz_questiontype`, " .
			"`quiz_rightanswer`, `quiz_submittedanswer`, `quiz_marksallotted`,`quiz_questions`.`quiz_sectionid` " .
			"FROM `quiz_questions`, `quiz_answersubmissions` WHERE " .
			"`quiz_questions`.`page_modulecomponentid` = '$quizId' AND " .
			"`quiz_questions`.`page_modulecomponentid` = `quiz_answersubmissions`.`page_modulecomponentid` AND " .
			"`quiz_questions`.`quiz_sectionid` = `quiz_answersubmissions`.`quiz_sectionid` AND " .
			"`quiz_questions`.`quiz_questionid` = `quiz_answersubmissions`.`quiz_questionid` AND " .
			"`quiz_questions`.`quiz_sectionid` = '{$sectionsRow['quiz_sectionid']}' AND " .
			"`user_id` = '$userId' ORDER BY `quiz_answersubmissions`.`quiz_questionrank`";


	$questionResult = mysql_query($questionQuery);

	if (!$questionResult)
		displayerror($questionQuery . '<br />' . mysql_error());
	
	while ($questionRow = mysql_fetch_assoc($questionResult)) {
		$correctFormHtml .= '<table class="quiz_' . (is_null($questionRow['quiz_marksallotted']) || floatval($questionRow['quiz_marksallotted']) <= 0 ? 'wrong' : 'right') . "answer\"><tr><td colspan=\"2\">{$questionRow['quiz_question']}</td></tr>\n";
		if ($questionRow['quiz_questiontype'] == 'subjective') {
			$submittedAnswers = array();
			$submittedAnswers[] = $questionRow['quiz_submittedanswer'];
			$correctAnswers = array();
			$correctAnswers[] = $questionRow['quiz_rightanswer'];
			$correctFormHtml .= '<tr><td nowrap="nowrap" width="10%">Submitted Answer:</td><td>'.implode("<br />\n", $submittedAnswers) . "</td></tr>\n";
			$correctFormHtml .= '<tr><td nowrap="nowrap" width="10%">Correct Answer:</td><td>'.implode("<br />\n", $correctAnswers) . "</td></tr>\n"; 
			$correctFormHtml .= "<tr><td>Mark:</td><td><form method=POST action='./+correct&useremail=" . safe_html($_GET['useremail']) . "'><input type=hidden name=quizid value='{$quizId}'><input type=hidden name=sectionid value={$questionRow['quiz_sectionid']}><input type=hidden name=questionid value={$questionRow['quiz_questionid']}><input type=hidden name=userid value={$userId}><input type=text name=mark size=5 value='{$questionRow['quiz_marksallotted']}'><input type=submit value='Submit' name=btnSetMark></form></td></tr>";
		}
		elseif ($questionRow['quiz_questiontype'] == 'sso' || $questionRow['quiz_questiontype'] == 'mso') {
			$optionList = getQuestionOptionList($quizId, $sectionsRow['quiz_sectionid'], $questionRow['quiz_questionid']);
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
			$correctFormHtml .= "<tr><td>Mark:</td><td>{$questionRow['quiz_marksallotted']}</td></tr>";
		}
		$correctFormHtml .= "</table>\n";
	}
	}
	$quizcorrectinfo="<h3>{$title['quiz_title']} - Quiz Answers Correct form for user: " . safe_html($_GET['useremail']) . "</h3><form name='userclearform' method='POST' action='./+correct'><a href='./+correct'>&lt;&lt;Back</a> &nbsp;&nbsp;&nbsp;<input type='hidden' name='hdnUserId' id='hdnUserId' value='{$userId}' /><input type='submit' name='btnDeleteUser' id='btnDeleteUser' value='Reject Submission' /></form><table width=100%><tr><td>Total marks: {$marks['total']}</td>$sectionHead<td>Start time: {$marks['starttime']}</td><td>Finish time: {$marks['finishtime']}</td><td>Time taken: {$marks['timetaken']}</td></tr></table>";
	$correctFormHtml .= "<a href='./+correct'>&lt;&lt;Back</a>";
	return $quizcorrectinfo.$correctFormHtml;
}
