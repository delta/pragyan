<?php
/**
 * Created on Dec 27, 2007
 *
 *
 * Defines the variable $quizEditForm to contain the HTML for a form
 * to help edit a quiz.
 *
 * Variables that will need to be defined are:
 *
 * $quizFolderUrl: Path to the folder modules/quiz, as it should appear at the front end
 * $quizDescRow: Row describing a quiz from the MySQL database
 *
 *
 *
 * $questionTypes: Associative array (typeName => Display Text) containing the question types available
 */


$questionTypesBox = '<select name="selQuestionTypes" onchange="QuestionTypeChanged(this.value, document.getElementById(\'questionTypeSpecOpts\'))"><option>' .
								 join($questionTypes, "</option>\n<option>") . '</option></select>';



$quizEditForm = <<<EDITFORM
		<script language="javascript" type="text/javascript" src="{$this->quizFolderUrl}/quiztypeoptions.js"></script>
/*
		<form name="quizproperties" action="./+edit" method="POST">
		  <table>
		  	<tr>
		  		<td nowrap="nowrap"><label for="txtQuizTitle">Quiz Title:</label></td>
		  		<td><input type="text" name="txtQuizTitle" id="txtQuizTitle" value="$quizTitle" size="41" /></td>
		  	</tr>
				<tr>
					<td><label for="txtQuizHeader">Quiz Header Text:</label></td>
					<td><textarea name="txtQuizHeader" id="txtQuizHeader" rows="5" cols="54">$quizHeaderText</textarea></td>
				</tr>
				<tr>
					<td><label for="txtQuizSubmitText">Quiz Submit Text:</label></td>
					<td><textarea name="txtQuizSubmitText" id="txtQuizSubmitText" rows="5" cols="54">$quizSubmitText</textarea></td>
				</tr>
				<tr>
					<td><label for="selQuizType">Quiz Type:</label></td>
					<td><select name="selQuizType" id="selQuizType" onchange="QuizTypeChanged(this.value, document.getElementById('quizTypeSpecOpts'))">$quizTypesBox</select></td>
				</tr>
				<tr><td>&nbsp;</td><td></td></tr>
			</table>

			<table id="quizTypeSpecOpts">
			</table>

			<table>
				<tr><td>&nbsp;</td><td></td></tr>
				<tr>
					<td nowrap="nowrap">Allow user to view score?</td>
					<td>
						<label><input type="radio" name="optAllowViewScore" value="yes" $allowViewScore />Yes</label>
						<label><input type="radio" name="optAllowViewScore" value="no" $allowViewScoreN />No</label>
					</td>
				</tr>
				<tr>
					<td>Multiple pages?</td>
					<td>
						<label><input type="radio" name="optMultiplePages" value="yes" $multiPage />Yes</label>
						<label><input type="radio" name="optMultiplePages" value="no" $multiPageN />No</label>
					</td>
				</tr>
				<tr>
					<td>Allow user to browse back?</td>
					<td>
						<label><input type="radio" name="optAllowBrowseBack" value="yes" $allowBrowseBack />Yes</label>
						<label><input type="radio" name="optAllowBrowseBack" value="no" $allowBrowseBackN />No</label>
					</td>
				</tr>
			</table>

			<input type="submit" name="btnSubmitProperties" value="Save Changes" />

			<table>
				$questionsTable
			</table>
		</form>

		<form name="newquestion" action="./+edit" method="POST">
			<table>
				<tr><td>Question Title:</td><td><input type="text" name="txtQuestionTitle" value="" /></tr>
				<tr><td>Question:</td><td><textarea name="txtQuestionText" rows="5" cols="40"></textarea></tr>
				<tr><td>Question Type:</td><td>$questionTypesBox</td></tr>

				<tr><td colspan="2"><table id="questionTypeSpecOpts"></table></td></tr>
			</table>

			<input type="submit" name="btnAddQuestion" value="Add New Question" />
		</form>

		<script language="javascript" type="text/javascript">
		<!--
			QuizTypeChanged(document.getElementById('selQuizType').value, document.getElementById('quizTypeSpecOpts'));
			QuestionTypeChanged(document.getElementById('selQuestionType').value, document.getElementById('questionTypeSpecOpts'));
		-->
		</script>
		*/
EDITFORM;


?>