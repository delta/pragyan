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
 * @copyright (c) 2008 Pragyan Team
 * @license http://www.gnu.org/licenses/ GNU Public License
 * For more details, see README
 */

 /*
  *
  * This imports the email from pragaynV2_users
  * checks if that mail is there in newsletter
  * if not then inserts the mail into newsletter.
  *
  * */
 include_once("../../config.inc.php");
include_once("../../common.lib.php");
connect();

$query="SELECT `user_email` FROM `".MYSQL_DATABASE_PREFIX."users`";
$result=mysql_query($query);
while($temp=mysql_fetch_array($result,MYSQL_NUM))
{
foreach($temp as  $mail)
{

	$query1="SELECT * FROM `newsletter` WHERE `email`='$mail' ";
//	echo $query1."<br>";
	$result1=mysql_query($query1);
	echo mysql_num_rows($result1).$query1."<br>";

	if(!mysql_num_rows($result1))
	{
		$query2="INSERT INTO `newsletter` (`email` ,`sent`)VALUES ('$mail','0')";
		echo $query2."<br>";
		$result2=mysql_query($query2) ;
		if(mysql_affected_rows($result2)>0)echo "$mail inserted into newsletter<br>";
		echo mysql_affected_rows($result2)."<=RES<br>";

//		echo $query2."<br>";
	}


}
}

 disconnect();
