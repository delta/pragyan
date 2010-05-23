<?php

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