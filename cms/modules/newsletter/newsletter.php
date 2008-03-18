<?php
include_once("../../config.inc.php");
include_once("../../common.lib.php");
connect();
$mailby=system('whoami');
$user="bytecode";
if ($mailby!=$user)
{
	echo "This newsletter can be sent only be user $user";
	exit;
}

/*
$mail=shell_exec('cat /webteam/anshu/mail');
$tmp=explode(',',$mail);
foreach ($tmp as $mail)
{
	mysql_query("INSERT INTO `newsletter` (`email` ,`sent`)VALUES ('$mail', '')");
}
*/


//mail3 IS NULL
//$test="email LIKE 'sahilahuja@gmail.com' OR email LIKE 'sahil@pragyan.org' OR email LIKE 'anshu@pragyan.org' OR email LIKE 'anshprat@gmail.com'";
$query="SELECT email FROM newsletter WHERE mail4 IS NULL";
$result=mysql_query($query) or die(mysql_error());
while ($temp = mysql_fetch_array($result, MYSQL_NUM))
{
	foreach($temp as $email)
	{

//		$message = "Content of the newsletter.\n";
		$message=file_get_contents("mail");
		$to = "$email";
		$subject = "Bytecode - Pragyan 2008 ";
		$header =/* "From:public_relations@pragyan.org\r " .
				"Reply-To:public_relations@pragyan.org\r " .
				"MIME-Version: 1.0\r\n" .
				"Content-Type:multipart/alternative; charset=ISO-8859-1\r\n" .
				"" .*/
				"" .
				"MIME-Version: 1.0
Content-Type: multipart/alternative;
 boundary=\"------------000708050804010404000804\"

This is a multi-part message in MIME format.
--------------000708050804010404000804
Content-Type: text/plain; charset=ISO-8859-1; format=flowed
Content-Transfer-Encoding: 7bit
";
		if (mail($to, $subject, $message, $header)) {

			echo "Mail sent to $email<br>\n";
			 $query="UPDATE `newsletter` SET `mail4`=NOW() WHERE `email`='$email'";
                        $result1=mysql_query($query);
                        echo mysql_affected_rows();
                        if(mysql_affected_rows())
                          echo "Mail sent status updated in db for $email\n";
                        else
                          echo "FAILURE Mail sent status NOT updated in db for $email\n";

			} else {
			echo "mail not sent to $email<br>\n";
			}

	}
}
disconnect();
?>