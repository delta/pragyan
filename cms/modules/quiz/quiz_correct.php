<?php
/*
 * Created on Jan 19, 2008
 * Shruthi
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
function getUserIds($modulecomponentid)
{
	global $sourceFolder;
	include_once($sourceFolder.'/common.lib.php');
	$UseridQuery = 'SELECT * FROM `quiz_quizattemptdata` WHERE `page_modulecomponentid` = ' . $modulecomponentid;
	$UseridResult = mysql_query($UseridQuery);
	if(!(mysql_num_rows($UseridResult)))
	{
		displayerror('No user has taken this quiz');
		return '';
	}
	$i=1;
	$useridtable = <<<USERIDTABLE
	<script language="javascript">

			function checkDelete(butt,userDel) {
				if(confirm('Are you sure you want to delete submitted answers of '+userDel+'?')) {
					window.location+= '&subaction=deletedata&useremail='+userDel;
				}
				else
					return false;
			}
	    </script>
		<table border="1">
		<tr>
			<th nowrap="nowrap">S. No.</th>
			<th nowrap="nowrap">User Email</th>
						<th nowrap="nowrap">Delete</th>
			<th nowrap="nowrap">Status</th>
		</tr>
USERIDTABLE;
//<a href="./+correct&subaction=deletedata&useremail=$useremail"> Delete user data</a>

	while($UserRow = mysql_fetch_assoc($UseridResult))
	{

		$userid = $UserRow['user_id'];
		$useremail=getUserEmail($userid);
		$useridtable .= <<<USERIDTABLE
		<tr>
		<td>$i</td>
		<td><a href="./+correct&subaction=viewuserdetails&useremail=$useremail">$useremail</a></td><td><input type="button" value="Delete" onclick="return checkDelete(this, '$useremail');" /></td>
USERIDTABLE;
		$i++;
		$qid=4;
		$UseransQuery = "SELECT * FROM `quiz_submittedanswers` WHERE `page_modulecomponentid` = $modulecomponentid AND `user_id`=$userid";
		$UseransResult = mysql_query($UseransQuery);
		$no=mysql_num_rows($UseransResult);
		if(!$UseransResult)
		{
			displayerror('The user has not submitted any answers');
			return '';
		}
		while($UseransRow=mysql_fetch_assoc($UseransResult))
		{
			$quesid=$UseransRow['quiz_questionid'];
			$UserquesQuery= "SELECT * FROM `quiz_questions` WHERE `page_modulecomponentid`= $modulecomponentid and `quiz_questionid`=$quesid";
			$UserquesResult=mysql_query($UserquesQuery);
			$UserquesRow=mysql_fetch_array($UserquesResult);
			if(!$UserquesResult)
			{
				displayerror('Error while fetching question data');
				return '';
			}
			if(($UserquesRow['quiz_questiontype']=='singleselectobjective')||($UserquesRow['quiz_questiontype']=='multiselectobjective'))
			{
//				if($UseransRow['quiz_markssecured']==NULL )
//				if(!$noAns_in_db)
				{
					$marks=0;
					$check="";
					$weight=$UserquesRow['quiz_questionweight'];
					$UsermarksQuery="SELECT * FROM `quiz_weightmarks` WHERE `page_modulecomponentid` =  $modulecomponentid AND `quiz_questionweight`=$weight";
					$UsermarksResult=mysql_query($UsermarksQuery);
					$UsermarksRow=mysql_fetch_array($UsermarksResult);
					if(!$UsermarksResult)
					{
						displayerror('Error while fetching marks data');
						return'';
						//$marks=$UsermarksRow['quiz_weightnegativemarks'];

					}
//					if()
					if(($UserquesRow['quiz_rightanswer']==NULL)&&(!$noAns_in_db))
					{
						 displayerror("No right answer entered in database by the moderator for Question Id . $quesid Correction aborted");
						 displayinfo("Please enter the right answer for <a href=\"+edit&subaction=editquestion&questionid=$quesid\">Question Id $quesid </a>  ");
						 displaywarning("The correction wont be complete unless the moderator enters the correct answer option(s) for Question Id.$quesid");
						 $noAns_in_db=1;
					}

					if(($UseransRow['quiz_submittedanswer']==$UserquesRow['quiz_rightanswer'])&&($UserquesRow['quiz_rightanswer']!=NULL))
					{
						$marks=$UsermarksRow['quiz_weightpositivemarks'];
						$check="correct answer";
					}
					elseif($UseransRow['quiz_submittedanswer']==NULL)
					{
						$marks=0;
						$check="no answer submitted";
					}
					else
					{
						$marks=0-$UsermarksRow['quiz_weightnegativemarks'];
						$check="wrong answer";
					}
					$check.=$quesid;
					$check.=$marks;
					//return $check;
					$updatequery="UPDATE `quiz_submittedanswers` SET `quiz_markssecured`= '$marks' WHERE".
					" `page_modulecomponentid`=$modulecomponentid AND `user_id`=$userid AND `quiz_questionid`=$quesid";
					$updateresult=mysql_query($updatequery);
					if(!$updateresult)
					{
						displayerror('Error while updating');
						return '';
					}
				}
//				else
//					break;
			}
		}
	$CorrectedansQuery = "SELECT * FROM `quiz_submittedanswers` WHERE `page_modulecomponentid` = $modulecomponentid AND `user_id`=$userid AND `quiz_markssecured` IS NULL";
	$CorrectedansResult = mysql_query($CorrectedansQuery);
	$no=mysql_num_rows($CorrectedansResult);
	if($no)
	{
		$useridtable.=<<<USERIDTABLE
		<th>Questions to be corrected</th>
USERIDTABLE;
	}
	else
	{
		$Sumquery="SELECT SUM(`quiz_markssecured`) AS TOTAL FROM `quiz_submittedanswers` WHERE `page_modulecomponentid`=$modulecomponentid AND `user_id`=$userid";
		$Sumresult=mysql_query($Sumquery);
		$Sumrow=mysql_fetch_array($Sumresult);
		if(!$Sumresult)
		{
			displayerror('Error while calculating total score');
		}
		$score=$Sumrow['TOTAL'];
		$useridtable.=<<<USERIDTABLE
		<th>Total score:$score</th>
USERIDTABLE;
	}
	$useridtable.='</tr>';
	}
	return $useridtable.'</table>';
}


function getUserAnswers($modulecomponentid,$useremail)
{
	global $sourceFolder;
	include_once($sourceFolder.'/common.lib.php');
	$userid=getUserIdFromEmail($useremail);
	//return 'hello'.$userid;
	$UsermarksQuery = "SELECT  * FROM `quiz_submittedanswers` WHERE `page_modulecomponentid` =  $modulecomponentid AND `user_id`=$userid AND `quiz_markssecured` IS NOT NULL";
	$UsermarksResult = mysql_query($UsermarksQuery);
	$i=1;
	$useranstable = <<<USERANSTABLE
		<table border="1">
		<tr>
			<th nowrap="nowrap">S. No.</th>
			<th nowrap="nowrap">Question Id</th>
			<th nowrap="nowrap">Question</th>
			<th nowrap="nowrap">Submitted Ans</th>
			<th nowrap="nowrap">Marks Allotted</th>
			<th nowrap="nowrap">Correct Ans</th>
		</tr>
USERANSTABLE;
	$useranstable.='<tr><b>Corrected questions</b></tr><br>';
	while($AnsRow = mysql_fetch_assoc($UsermarksResult))
	{
		$quesid=$AnsRow['quiz_questionid'];
		$quesQuery= "SELECT * FROM `quiz_questions` WHERE `page_modulecomponentid` =  $modulecomponentid AND `quiz_questionid`=$quesid";
		$quesResult=mysql_query($quesQuery);
		$quesRow=mysql_fetch_array($quesResult);

		if($AnsRow['quiz_submittedanswer']==NULL)
		{
			$answer="No answer submitted";
		}
		else
		{
			$answer=$AnsRow['quiz_submittedanswer'];
		}
		if($quesRow['quiz_rightanswer']==NULL)
		{
			$rightanswer="No answer entered";
		}
		else
		{
			$rightanswer=$quesRow['quiz_rightanswer'];
		}
		if(!$quesResult)
		{
			displayerror('error while fetching data');
			return'';
		}
			if($quesRow['quiz_rightanswer']==NULL) {
				displayerror("No right answer entered in database by the moderator for Question No.$quesid Correction aborted");
				displayinfo("Please eneter the right answer for Question No.$quesid");

						 }
		$useranstable .= <<<USERANSTABLE
		<tr>
			<td nowrap="nowrap">$i</td>
			<td nowrap="nowrap">{$AnsRow['quiz_questionid']}</td>
			<td nowrap="nowrap">{$quesRow['quiz_question']}</td>
			<td nowrap="nowrap">$answer</td>
			<td nowrap="nowrap">{$AnsRow['quiz_markssecured']}</td>
			<td nowrap="nowrap">{$quesRow['quiz_rightanswer']}</td>
		</tr>
USERANSTABLE;
		$i++;
	}

	$useranstable.='</table><br><br>';
	$AnscorrQuery = "SELECT  * FROM `quiz_submittedanswers` WHERE `page_modulecomponentid` =  $modulecomponentid AND `user_id`=$userid AND `quiz_markssecured` IS NULL";
	$AnscorrResult = mysql_query($AnscorrQuery);
	if(!mysql_num_rows($AnscorrResult))
	{
		$useranstable.='<tr><b>All Questions have been corrected</b></tr><br>';
		$useranstable.=<<<USERANSTABLE
	<tr><th><a href="./+correct&subaction=viewuserids">Go back to user ids</a></th>
USERANSTABLE;
		return $useranstable;
	}
	$useranstable .= <<<USERANSTABLE
		<table border="1">
		<tr>
			<th nowrap="nowrap">S. No.</th>
			<th nowrap="nowrap">Correct</th>
			<th nowrap="nowrap">Question Id</th>
			<th nowrap="nowrap">Question</th>
		</tr>
USERANSTABLE;
	$useranstable.='<tr><b>Questions to be corrected</b></tr><br>';
	$i=1;
	global $sourceFolder, $urlRequestRoot, $templateFolder;
	$iconsFolderUrl = "$urlRequestRoot/$sourceFolder/$templateFolder/common/icons";
	$correctImage = '<img alt="Correct" src="' . $iconsFolderUrl . '/16x16/apps/accessories-text-editor.png" style="padding: 0px" />';
	while($AnsRow = mysql_fetch_assoc($AnscorrResult))
	{
		$quesid=$AnsRow['quiz_questionid'];
		$quesQuery= "SELECT * FROM `quiz_questions` WHERE `page_modulecomponentid` =  $modulecomponentid AND `quiz_questionid`=$quesid";
		$quesResult=mysql_query($quesQuery);
		$quesRow=mysql_fetch_array($quesResult);
		$useranstable .= <<<USERANSTABLE
		<tr>
			<th nowrap="nowrap">$i</th>
			<th><a href="./+correct&subaction=correctquestion&useremail=$useremail&questionid={$AnsRow['quiz_questionid']}">$correctImage</a></th>
			<th nowrap="nowrap">{$AnsRow['quiz_questionid']}</th>
			<th nowrap="nowrap">{$quesRow['quiz_question']}</th>
		</tr>
USERANSTABLE;
		$i++;
	}

	$useranstable.='</table>';
	$useranstable.=<<<USERANSTABLE
	<tr><th><a href="./+correct&subaction=viewuserids">Go back to user ids</a></th>
USERANSTABLE;
	return $useranstable;
}

function getQuestionCorrectForm($moduleComponentId, $questionid, $useremail)
{
	global $sourceFolder;
	include_once($sourceFolder.'/common.lib.php');
	$userid=getUserIdFromEmail($useremail);
	$quesQuery= "SELECT * FROM `quiz_questions` WHERE `page_modulecomponentid` =  $moduleComponentId AND `quiz_questionid`=$questionid";
	$quesResult=mysql_query($quesQuery);
	$quesRow=mysql_fetch_array($quesResult);
	if(!$quesResult)
	{
		displayerror('Error while fetching question data');
		return '';
	}
	if($quesRow['quiz_rightanswer']==NULL)
	{
		$hanswer="no answer entered";
	}
	else
	{
		$hanswer=$quesRow['quiz_rightanswer'];
	}
	$ansQuery= "SELECT * FROM `quiz_submittedanswers` WHERE `page_modulecomponentid` =  $moduleComponentId AND `quiz_questionid`=$questionid AND `user_id`=$userid";
	$ansResult=mysql_query($ansQuery);
	$ansRow=mysql_fetch_array($ansResult);
	if(!$ansResult)
	{
		displayerror('Error while fetching answer data');
		return '';
	}
	if($ansRow['quiz_submittedanswer']==NULL)
	{
		$answer="no answer submitted";
	}
	else
	{
		$answer=$ansRow['quiz_submittedanswer'];
	}

	$quescorrform=<<<QUESCORRFORM
				<form name="quescorrectform" method="POST" action="./+correct&subaction=correctquestion&useremail=$useremail&questionid=$questionid">
				<table border="1">
				<tr><td nowrap="nowrap"><b>Question Id:</b></td><td>{$quesRow['quiz_questionid']}</td></tr>
				<tr><td nowrap="nowrap"><b>Question:</b></td><td>{$quesRow['quiz_question']}</td></tr>
				<tr><td nowrap="nowrap"><b>Hint answer:</b></td><td>$hanswer</td></tr>
				<tr><td nowrap="nowrap"><b>Submitted answer:</b></td><td>$answer</td></tr>
				<tr><td nowrap="nowrap"><b>Enter marks</b></td><td><input type="textbox" name="marks"</td></tr>
				</table>
				<br/>
				<tr><td><input type="submit" name="btnSubmitQuestionCorrectForm" value="Save Marks" /></tr></td>
				</form>

QUESCORRFORM;
	return $quescorrform;
}

function submitQuestionCorrectForm($modulecomponentid, $questionid, $useremail)
{
	global $sourceFolder;
	include_once($sourceFolder.'/common.lib.php');
	$userid=getUserIdFromEmail($useremail);
	$marks=$_POST['marks'];
	if($marks=='')
	{
		displayerror('Marks has not been entered');
		return'';
	}
	$updatequery="UPDATE `quiz_submittedanswers` SET `quiz_markssecured`= '$marks' WHERE".
				" `page_modulecomponentid`=$modulecomponentid AND `user_id`=$userid AND `quiz_questionid`=$questionid";
	$updateresult=mysql_query($updatequery);
	if(!$updateresult)
	{
		displayerror('Error while updating');
		return '';
	}

}

?>
