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
#include_once("../../config.inc.php");
#include_once("../../common.lib.php");

$errorFile = fopen("send-errors.txt", 'w');
$sentFile = fopen("send-success.txt", 'w');

#connect();

/*
 * Enter logic here to generate appropriate list of recipients
 * @return Array of strings, denoting the email ids of intended recipients
 */
function getRecipientList() {
	//return array('jithinkr@gmail.com','mradul88@gmail.com');
	return file('mailinglisthexa');
}

function getMailContents() {
	return file_get_contents('pragyanmail_workshops_feb7');
}

function getMailSubject() {
	return 'Workshops and Guest Lectures @ Pragyan\'10 - NIT Trichy\'s Annual Techno-Management Fest'; // 'Pragyan 2010';
}

function registerMailSent($email) { global $sentFile; fwrite($sentFile, $email . "\n"); }
function registerMailSendError($email) { global $errorFile; fwrite($errorFile, $email . "\n"); }

$recipients = getRecipientList();
$sender = 'Pragyan 10 <info@pragyan.org>';
$replyTo = 'info@pragyan.org';
$subject = getMailSubject();
$message = getMailContents();

for ($i = 0; $i < count($recipients); ++$i) {
	echo "Mailing {$recipients[$i]}... ";
	$headers =
		"From: $sender\r\n" .
		"Reply-To: $replyTo\r\n" .
		"MIME-Version: 1.0\r\n" .
		"Content-Type: multipart/alternative; boundary=\"000708050804010404000804\"";

	if (@mail($recipients[$i], $subject, $message, $headers)) {
		echo "Success\n";
		registerMailSent($recipients[$i]);
	}
	else {
		echo "Failure\n";
		registerMailSendError($recipients[$i]);
	}
}

?>
