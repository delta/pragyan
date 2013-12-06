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
 * @author Chakradar Raju
 * @copyright (c) 2010 Pragyan Team
 * @license http://www.gnu.org/licenses/ GNU Public License
 * For more details, see README
 */
 
// For the messenger email class, see common.lib.php

function getAllUsers() {
	$ret = "";
	$result = mysql_query("SELECT `user_name`,`user_id`,`user_fullname`,`user_email` FROM `" . MYSQL_DATABASE_PREFIX . "users`");
	while($row = mysql_fetch_array($result))
		$ret .= "'{$row['user_id']}' : '{$row['user_name']} - {$row['user_fullname']} [{$row['user_email']}]', ";
	$ret = rtrim($ret,", ");
	return $ret;	
}

function getAllGroups() {
	$ret = "";
	$result = mysql_query("SELECT `group_name`,`group_id` FROM `" . MYSQL_DATABASE_PREFIX . "groups`");
	while($row = mysql_fetch_array($result))
		$ret .= "'{$row['group_id']}' : '{$row['group_name']}', ";
	$ret = rtrim($ret,", ");
	return $ret;
}

function displayEmail($template = "") {
	global $sourceFolder,$urlRequestRoot;
	$newSelected = "";
	if($template == "" || $template == "new") {
		$newSelected = " selected";
		$content = "";
	}
	$emailTemplates = "<select id='emailtemplates' name='emailtemplates'><option value='new'{$newSelected}>Create New Template</option>";
	$dir = "$sourceFolder/languages/" . LANGUAGE . "/email/templates/";
	$handle = opendir($dir);
	$subject="";
	$content="";
	while($file = readdir($handle)) {
		if(substr($file, -4, 4) == ".txt") {
			$name = substr($file, 0, -4);
			if($name == $template) {
				$emailTemplates .= "<option value='$name' selected> $name </option>";
				$content = fread(fopen($dir . $file,'r'),filesize($dir.$file));
				$subject = $name;
			} else {
				$emailTemplates .= "<option value='$name'> $name </option>";
			}
		}
	}
	$emailTemplates .= "</select>";
	global $ICONS;
	$groups = getAllGroups();
	$users = getAllUsers();
	
	
	$ret = smarttable::render(array('rcpttable'),null);
	
	$ret .= <<<RET
	<script type='text/javascript'>
	var grouplist = { $groups };
	var userlist = { $users };
	function fetchEmail() {
		window.location = './+admin&subaction=openemail&name=' + document.getElementById('emailtemplates').value;
	}
	
	function saveEmail(e) {
		var name = prompt("enter name for template", document.getElementById('subject').value);
		if(name != '') {
			e.form.action += 'save&name=';
			e.form.action += name;
			e.form.submit();
		}
	}
	
	function renderCheckList(arr,class) {
		var ret = '<table name="rcptlisttable" id="rcpttable" class="userlisttable display"><thead><tr><th>Select Recipients</th></thead><tbody>';
		for(var key in arr)
			ret += "<tr><td><INPUT type=checkbox class=" + class + " value='" + key + "'>&nbsp;&nbsp;&nbsp;" + arr[key] + "</td></tr>";
		ret += "</tbody></table>";
		return ret;
	}
	
	function selectAll() {
		document.getElementById('recipient').innerHTML = 'All';
		document.getElementById('list').innerHTML = 'All users selected';
	}
	
	function selectGroups() {
		document.getElementById('recipient').innerHTML = 'Selected Groups';
		document.getElementById('list').innerHTML = "Group List:<br>" + renderCheckList(grouplist,'grouplist');
		initSmartTable();
	}
	
	function selectUsers() {
		document.getElementById('recipient').innerHTML = 'Selected Users';
		document.getElementById('list').innerHTML = "User List:<br>" + renderCheckList(userlist,'userlist');
		initSmartTable();
	}
	
	function getList(class) {
		var listarr = new Array();
		var list = document.getElementsByClassName(class);
		var l = list.length;
		for(var i = 0; i<l; i++)
			if(list[i].checked)
				listarr.push(list[i].value);
		return listarr;
	}
	
	function renderRecipient(arr) {
		var ret = '';
		for(var ele in arr)
			ret += arr[ele] + ', '
		var l = ret.length;
		if(l>0)
			ret = ret.substring(0,l-2);
		return ret;
	}
	
	function sendMail(e) {
		var recipient = document.getElementById('recipient').innerHTML;
		var recipients = 'all';
		if(recipient == 'Selected Groups')
			recipients = 'groups:' + renderRecipient(getList('grouplist'));
		else if(recipient == 'Selected Users')
			recipients = 'users:' + renderRecipient(getList('userlist'));
		document.getElementById('recipients').value = recipients;
		e.form.action += 'send';
		e.form.submit();
	}
	</script>
	<fieldset>
	<legend>{$ICONS['Email Registrants']['small']}Email Registrants</legend>
	<form name=emailcontent action='./+admin&subaction=email' method=POST>
	<input type='hidden' id='recipients' name='recipients' value='all'>
	<table border=0 margin=0 width=100%>
	<tr><td>
	Subject: <input type=text value='$subject' id=subject name=subject>
	</td></tr>
	<tr><td>
	Content:
	{$emailTemplates} <input type=button value=Open onClick='fetchEmail()'> <input type=button id=btnSave value=Save onClick='saveEmail(this);'>
	<textarea cols=60 rows=15 name=templateContent>$content</textarea>
	</td></tr>
	</table>
	<table border=0 margin=0 width=100%>
	<tr><td style="text-align:center">
	Recipients: <span id='recipient'>All</span><br/>
	<input type=button value=All onClick='selectAll()'> <input type=button value='Select Groups' onClick='selectGroups()'> <input type=button value='Select Users' onClick='selectUsers()'></td>
	</td></tr>
	<tr><td>
	<span id='list'>
	All users selected
	</span>
	</td>
	</tr>
	</table>
	<input type=button value='Send Email' onClick='sendMail(this);'>
	</form>
	</fieldset>
RET;
	return $ret;
}

function getTo($desc) {
	$ret = "";
	if($desc == "all") {
		$result = mysql_query("SELECT `user_email` FROM `" . MYSQL_DATABASE_PREFIX . "users`");
		while($row = mysql_fetch_array($result))
			$ret .= $row['user_email'] . ", ";
		$ret = rtrim($ret, ", ");
	} else if(substr($desc, 0, 5) == "users") {
		$in = substr($desc, 6);
		$result = mysql_query("SELECT `user_email` FROM `" . MYSQL_DATABASE_PREFIX . "users` WHERE `user_id` IN ({$in})");
		while($row = mysql_fetch_array($result))
			$ret .= $row['user_email'] . ", ";
		$ret = rtrim($ret, ", ");
	} else if(substr($desc, 0, 6) == "groups") {
		$in = substr($desc, 7);
		$result = mysql_query("SELECT `user_email` FROM `" . MYSQL_DATABASE_PREFIX . "users` WHERE `user_id` IN (SELECT `user_id` FROM `" . MYSQL_DATABASE_PREFIX . "usergroup` WHERE `group_id` IN ({$in}))");
		while($row = mysql_fetch_array($result))
			$ret .= $row['user_email'] . ", ";
		$ret = rtrim($ret, ", ");
	}
	return $ret;
}

function sendEmail() {
	$to = getTo($_POST['recipients']);
	$subject = CMS_TITLE . $_POST['emailtemplates'];
	$from = "from: ".CMS_TITLE." <".CMS_EMAIL.">";
	if(@mail($to,$_POST['subject'],$_POST['templateContent'],$from))
		displayinfo("Mail sent successfully.");
	else
		displayerror("Error in sending mail.");
	
}

function saveEmail() {
	$name = escape($_GET['name']);
	$content = $_POST['templateContent'];
	global $sourceFolder;
	$dir = "$sourceFolder/languages/" . LANGUAGE . "/email/templates/";
	if(!file_exists($dir))
		mkdir($dir);
	if(!file_exists($dir . $name . ".txt")) {
		$fh = fopen($dir . $name . ".txt", "w");
		if(fwrite($fh, $content)) {
			displayinfo("Template saved");
			fclose($fh);
		} else
			displayerror("Error in saving template. May be the webserver doesn't have write permissions.");
	} else if($_POST['emailtemplates'] == $name) {
		$fh = fopen($dir . $name . ".txt", "w");
		if(fwrite($fh, $content)) {
			displayinfo("Template saved");
			fclose($fh);
		} else
			displayerror("Error in saving template");
	} else
		displayerror("File already exist.");
}
?>
