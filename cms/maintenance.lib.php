<?php
/*
 * Created on Jan 20, 2008
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */

function runMaintenance() {
	//remove all unactivated more than 10 days old.
	$removeUnactivatedQuery = "DELETE FROM `".MYSQL_DATABASE_PREFIX."users` WHERE `user_regdate` < SUBDATE( NOW(),10) AND `user_activated` = 0";
	$removeUnactivatedResult = mysql_query($removeUnactivatedQuery);

	return true;
}



?>
