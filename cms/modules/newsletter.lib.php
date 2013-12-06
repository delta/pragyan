<?php
if(!defined('__PRAGYAN_CMS'))
{ 
	header($_SERVER['SERVER_PROTOCOL'].' 403 Forbidden');
	echo "<h1>403 Forbidden<h1><h4>You are not authorized to access the page.</h4>";
	echo '<hr/>'.$_SERVER['SERVER_SIGNATURE'];
	exit(1);
}
/*
 * Created on Oct 20, 2008
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */


class newsletter implements module {
	private $userId;
	private $moduleComponentId;
	private $action;

	public function getHtml($userId, $moduleComponentId, $action) {
		$this->userId = $userId;
		$this->moduleComponentId = $moduleComponentId;
		$this->action = $action;

		switch ($action) {
			case 'view':
				return $this->actionView();
			case 'edit':
				return $this->actionEdit();
			case 'editregistrants':
				return $this->actionEditregistrants();
			 	
		}
	}

	public function actionView() {
		echo "<p><b>Hi</b></p>";
	}

	public function actionEdit() {
		
	}

	public function actionEditregistrants() {
		
	}

	public static function isUserRegistered($userId, $moduleComponentId) {
		if (isInternalUserRegistered($userId, $moduleComponentId, false))
			return true;

		$userEmail = getUserEmail($userId);
		if (isExternalUserRegistered($userEmail, $moduleComponentId)) {
			moveUserToInternal($userEmail, $userId);
			return true;
		}

		return false;
	}

	private static function moveUserToInternal($userEmail, $userId) {
		$query = "SELECT `page_modulecomponentid` FROM `newsletter_externalusers` WHERE `user_email` = '$userEmail'";
		$result = mysql_query($query);
		while ($row = mysql_fetch_row($result)) {
			if (!isInternalUserRegistered($userId, $row[0], false)) {
				$insertQuery = "INSERT INTO `newsletter_users`(`page_modulecomponentid`, `newsletter_subscriptiontype`, `user_id`, `user_joindatetime`) VALUES ({$row[0]}, 'user', $userId, NOW())";
				if (!mysql_query($insertQuery)) {
					displayerror('Could not add user to internal list.');
				}
				else {
					$deleteQuery  = "DELETE FROM `newsletter_externalusers` WHERE `page_modulecomponentid` = {$row[0]} AND `user_email` = '$userEmail'";
					if (!mysql_query($deleteQuery))
						displayerror('Could not remove user from external list.');
				}
			}
			else {
				$deleteQuery = "DELETE FROM `newsletter_externalusers` WHERE `page_modulecomponentid` = {$row[0]} AND `user_email` = '$userEmail'";
				if (!mysql_query($deleteQuery))
					displayerror('Could not remove user from external list.');
			}
		}
	}

	private static function isInternalUserRegistered($userId, $moduleComponentId, $testGroups = true) {
		$userExistsQuery = "SELECT COUNT(*) FROM `newsletter_users` WHERE `page_modulecomponentid` = $moduleComponentId AND `newsletter_subscriptiontype` = 'group'";
		$userExistsResult = mysql_query($userExistsQuery);
		if ($userExistsRow = mysql_fetch_row($userExistsResult))
			if ($userExistsRow[0] == 1)
				return true;

		if ($testGroups) {
			$userGroups = getGroupIds($userId);
			$usergroupTable = MYSQL_DATABASE_PREFIX . 'usergroup';
			$groupsQuery = "SELECT COUNT(*) FROM `newsletter_users`, `$usergroupTable` WHERE `newsletter_users`.`page_modulecomponentid` = $moduleComponentId AND `newsletter_users`.`newsletter_subscriptiontype` = 'group' " .
					"AND `$usergroupTable`.`user_id` = $userId AND `newsletter_users`.`usergroup_id` = `$usergroupTable`.`group_id`";
			$groupsResult = mysql_query($groupsQuery);
			if ($groupsResultRow = mysql_fetch_row($groupsResult))
				if ($groupsResultRow[0] > 0)
					return true;
		}

		return false;
	}

	public static function isExternalUserRegistered($userEmail, $moduleComponentId) {
		$userExistsQuery = "SELECT COUNT(*) FROM `newsletter_externalusers` WHERE `page_modulecomponentid` = $moduleComponentId AND `user_email` = '$userEmail'";
		$userExistsResult = mysql_query($userExistsQuery);
		if ($userExistsRow = mysql_fetch_row($userExistsResult))
			if ($userExistsRow[0] == 1)
				return true;
		return false;
	}

	private function getNewsletterName($listId) {
		$listNameQuery = 'SELECT `newsletter_name` FROM `newsletter_desc` WHERE `page_modulecomponentid` = ' . $listId;
		$listNameResult = mysql_query($listNameQuery);
		if ($listNameRow = mysql_fetch_row($listNameQuery))
			return $listNameRow[0];
		return '';
	}

	private function getNewsletterPath($pageId) {
		return getPagePath($pageId);
	}

	// return an array containing name of list, path, boolean indicating if user is registered or not
	public static function getSubscribableLists($userId) {
		$newsletterListQuery = 'SELECT `page_id`, `page_modulecomponentid` FROM `' . MYSQL_DATABASE_PREFIX . 'pages` WHERE `page_module` = \'newsletter\' ORDER BY `page_modulecomponentid`';
		$newsletterListResult = mysql_query($newsletterListQuery);

		$subscribableLists = array();
		while ($newsletterListRow = mysql_fetch_row($newsletterListQuery)) {
			if (getPermissions($userId, $newsletterListRow[0], 'view', 'newsletter')) {
				$listName = getNewsletterName($newsletterListRow[1]);
				$listPath = getNewsletterPath($newsletterListRow[0]);
				$subscribed = newsletter::isUserRegistered($userId, $newsletterListRow[1]);
				$subscribableLists[] = array($listName, $listPath, $subscribed);
			}
		}

		$subscribableLists[] = array('newsletter1', 'newsletter1', 0);
		$subscribableLists[] = array('newsletter2', 'newsletter3', 1);
		$subscribableLists[] = array('newsletter3', 'newsletter2', 1);

		return $subscribableLists;
	}

	public static function addUserToList($userEmail) {
		// select from newsletter_unsubscribed where email = $userEmail
		// if exists, return error
		// 
	}

	// Return an array containing all distinct email ids newsletters for this module must be sent to
	public function getUserList() {
		
	}

	public function createModule($compId) {
		$query = "INSERT INTO `newsletter_desc` (`page_modulecomponentid` ,`newsletter_name`)VALUES ('$compId', 'New Newsletter')";
		$result = mysql_query($query) or die(mysql_error()."newsletter.lib L:188");
	}

	public function deleteModule($moduleComponentId) {
		return true;	
	}

	public function copyModule($moduleComponentId,$newId) {
		return true;
	}
}
