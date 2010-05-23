<?php
/*
 * Created on Jan 15, 2009
 */

class GreQuiz implements IQuiz {
	private $quizId;
	private $quizRow;

	public function __construct($quizId) {
		$this->quizId = $quizId;
		$this->quizRow = getQuizRow($quizId);
	}

	public function getPropertiesForm($dataSource) {
		return 'No quiz specific properties.';
	}

	public function submitPropertiesForm() {
		return true;
	}

	public function getFrontPage($userId) {
		
	}

	public function getQuizPage($userId) {
		
	}

	public function submitQuizPage($userId) {
		
	}

	public function initQuiz($userId) {
		
	}

	public function deleteEntries($userId) {
		$tableNames = array('quiz_userattempts', 'quiz_answersubmissions');
		$affectedRows = array();
		return deleteItem($tableNames, "`page_modulecomponentid` = $quizId AND `user_id` = $userId", $affectedRows);
	}

	// Utility Functions
	private function getUserQuestionIds($sectionId, $userId) {
		$questionIdQuery = "SELECT `quiz_questionid` FROM `quiz_answersubmissions` WHERE `page_modulecomponentid` = {$this->quizId} AND `quiz_sectionid` = $sectionId AND `user_id` = $userId AND `quiz_answersubmittime` = '0000-00-00 00:00:00'";
		$questionIdResult = mysql_query($questionIdQuery);
		$questionIds = array();
		if (!$questionIdResult) {
			displayerror('Database Error. Could not fetch questions.');
		}
		else {
			while ($questionIdRow = mysql_fetch_row($questionIdResult))
				$questionIds[] = $questionIdRow[0];
		}
		return $questionIds;
	}
}