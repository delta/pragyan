<?php
/*
 * Created on Jan 14, 2008
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 *
 *
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

$query="SELECT `user_email` FROM `pragyanV2_users`";
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
?>
