<?php
if(!defined('__PRAGYAN_CMS'))
{ 
	header($_SERVER['SERVER_PROTOCOL'].' 403 Forbidden');
	echo "<h1>403 Forbidden<h1><h4>You are not authorized to access the page.</h4>";
	echo '<hr/>'.$_SERVER['SERVER_SIGNATURE'];
	exit(1);
}
/**
 * @package pragyan
 * @copyright (c) 2010 Pragyan Team
 * @license http://www.gnu.org/licenses/ GNU Public License
 * For more details, see README
 */
/*
assign $directory to the directory to be emptied. And see whether only files older than 10 days should be deleted or is it ok to delete all files during every maintenace.
*/

function emptycache(){
	global $sourceFolder,$uploadFolder;
	$captchaImageFolder = "$sourceFolder/$uploadFolder/temp";
	$filedisposalage_days=10;
	$seconds_old=$filedisposalage_days*86400;
	if(!$dirhandle=@opendir($captchaImageFolder)){
	echo "error in opening directory";
	return false;
	}
	while($filename=readdir($dirhandle))
	 if( $filename != "." && $filename != ".." ) {
	        $filename = $captchaImageFolder. "/". $filename;
		if(filemtime($filename)<(time()-$seconds_old))
		 unlink($filename);
	}
	return true;
}


function runMaintenance() {
	//remove all unactivated more than 10 days old.
	/*$removeUnactivatedQuery = "DELETE FROM `".MYSQL_DATABASE_PREFIX."users` WHERE `user_regdate` < SUBDATE( NOW(),10) AND `user_activated` = 0";
	$removeUnactivatedResult = mysql_query($removeUnactivatedQuery);*/
	emptycache();
	return true;
}



