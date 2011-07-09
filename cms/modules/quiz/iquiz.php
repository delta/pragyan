<?php
if(!defined('__PRAGYAN_CMS'))
{ 
	header($_SERVER['SERVER_PROTOCOL'].' 403 Forbidden');
	echo "<h1>403 Forbidden<h1><h4>You are not authorized to access the page.</h4>";
	echo '<hr/>'.$_SERVER['SERVER_SIGNATURE'];
	exit(1);
}

interface IQuiz {
	public function __construct($quizId);

	public function getPropertiesForm($dataSource);
	public function submitPropertiesForm();

	public function getFrontPage($userId);

	public function getQuizPage($userId);
	public function submitQuizPage($userId);

	public function initQuiz($userId);

	public function deleteEntries($userId);
}
