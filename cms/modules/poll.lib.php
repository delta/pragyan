<?php


/*
 * Created on Oct 22, 2007
 */

class poll implements module {
	private $userId;
	private $moduleComponentId;
	private $action;

	public function getHtml($gotuid, $gotmoduleComponentId, $gotaction) {
		$this->userId = $gotuid;
		$this->moduleComponentId = $gotmoduleComponentId;
		$this->action = $gotaction;
		if ($this->action == "vote")
			return $this->actionVote();
		if ($this->action == "viewresults")
			return $this->actionViewresults();
		if ($this->action == "create"){ echo 'hoe';
			return $this->actionAdmincreatepoll("", 10);}
		if ($this->action == "edit")
			return $this->actionAdmineditpoll();
		if ($this->action == "archive")
			return $this->actionAdminarchivepoll();
		if ($this->action == "delete")
			return $this->actionAdmindeletepoll();
		if ($this->action == "viewstats")
			return $this->actionAdminviewstats();
	}

	/*Options to be displayed :
	 * View Polls					View the polls created under a page.
	 * Vote							Vote in a poll.
	 * View Results					View the results of open polls.
	 * Create						Create a poll(Admin only)
	 * Edit							Edit an existing poll(Admin only)
	 * Archive/close				Close polls and archive them if necessary(Admin only)
	 * Delete						Delete an open poll/an archive(Admin only)
	 * View statistics				View the voter statistics(Admin only)
	 */

	/*functions being written*/
	/*TABLES USED
	POLLS (ID,QUESTION,PARENT,STATUS,EXP_TIME,EXPIRE,COMMENTS)
	POLLDATA  (POLLID,QUESTION,OPTIONID,OPTIONTEXT,VOTES,TOTALVOTES,COLOUR)
	USERDATA(ID,USER,POLLID,OPTION,COMMENT)
	*/
	function createtables(){
		mysql_connect("localhost","delta","DeltainC") or die(mysql_error());
mysql_select_db("polls") or die(mysql_error());
mysql_query("CREATE TABLE polls(id INT AUTO_INCREMENT NOT NULL ,PRIMARY KEY(id),question VARCHAR(200),parent VARCHAR(100),status INT,exp_time INT,expire INT,comments INT)") or die(mysql_error());
		mysql_query("CREATE TABLE polldata(id INT AUTO_INCREMENT NOT NULL ,PRIMARY KEY(id),pollid INT,question VARCHAR(200),optionid INT,optiontext VARCHAR(200),votes INT,totalvotes INT,colour VARCHAR(20))") or die(mysql_error());
		mysql_query("CREATE TABLE userdata(id INT AUTO_INCREMENT NOT NULL,PRIMARY KEY(id),userid INT,pollid INT,optionid INT,comment VARCHAR(1000))") or die(mysql_error());
	}
	function connect() {
		mysql_connect("localhost","delta","DeltainC") or die (mysql_error());
		mysql_select_db("poll") or die (mysql_error());
	}

	function actionviewpolls() {
		function checkexpiry($pollid, $exp_time) {
		$timestamp = time();
		if ($timestamp >= $exp_time) {
			mysql_query("UPDATE polls SET expire=2 status='archived' WHERE id=$pollid");
			return 1;
		}
	}

		mysql_connect("localhost","delta","DeltainC") or die (mysql_error());
		mysql_select_db("polls") or die (mysql_error());
		$list = mysql_query("SELECT * FROM polls ") or die (mysql_error());
		$content="<ol>";
		$ctr = 0;
		$content.='<h2>current polls</h2>';
		while ($poll = mysql_fetch_array($list)) {
			$ctr++;
			if ($poll['status'] != 'off') {
				checkexpiry($poll['id'], $poll['exp_time']); 								//check for expiry
				if ($poll['status'] == 'on') { 												//enabled polls
					$pollurl = $poll['parent'].'vote.php?pollid='.$poll['id'].'&action=vote';
					$content.='<li><a href ="'.$pollurl.'">'.$poll['question'].'</a></li>';
				}
			}
		}
		if ($ctr == 0)
			$content.="No current polls exist here</ol>";
		mysql_data_seek($list); //or die (mysql_error());
		$ctr=0;
		$content.='<h2>archived polls</h2>';
		$poll = mysql_fetch_array($list,0) ;
		echo $poll['id'];
		while ($poll = mysql_fetch_array($list)) {
				echo $poll['id'];
				if ($poll['status'] != 'off') {
					if ($poll['status'] == 'archived') { 									//archived polls
						$pollurl = $poll['parent'].'vote.php?pollid='.$poll['id'].'&action=vote';
						$content.='<li><a href ="'.$pollurl.'">'.$poll['question'].'</a></li>';
					}
				}
		}
		if ($ctr == 0)
			$content.="No archived polls exist here</ol>";
		echo $content;


	}


	function actionvote() {
		mysql_connect("localhost","delta","DeltainC") or die (mysql_error());
			mysql_select_db("polls") or die (mysql_error());
			if ($_GET['action'] == 'vote') {
					$pollarray = mysql_query("SELECT * FROM polls WHERE id=".$_GET['pollid']) or die (mysql_error());
					$poll = mysql_fetch_array($pollarray) or die (mysql_error());
					if ($poll['status'] == 'archived')
						$content.="The poll has been closed. You can view the results in the results page<br>";
					else
					if ($poll['status'] == 'off')
							$content.="The poll has been disabled. You cannot view it.<br>";
					else {
							$content.="<form action=submitvote.php method=POST>";
							$content.="<h2>" . $poll['question'] . "</br></h2>";
							$content.="<ol type=a>";
							$optionarray = mysql_query("SELECT optionid,optiontext FROM polldata WHERE pollid=".$_GET['pollid']) or die (mysql_error());
							while ($options = mysql_fetch_array($optionarray)) {
									$oid = $options['optionid'];
									$value = $options['optiontext'];
									$content.='<li><input type=radio name="oid'.$oid.'" >' . $value . '</li>';
								}
							$content.='</ol><input type= hidden name=user value=' . $userId . '><input type= hidden name=pollid value=' . $_GET['pollid'] . '></br><div align = "center"><input type=submit name="vote" value="VOTE"></div>';
						}
				}
			else 	$content.="There seems to have been some internal error. Try again later. click here to go to the results page.";


		echo $content;
	}
	function submitvote(){
	mysql_connect("localhost","delta","DeltainC") or die (mysql_error());
	mysql_select_db("polls") or die (mysql_error());
	$user=4;
	$voted=0;
	$query = mysql_query("SELECT pollid FROM userdata WHERE userid=".$user);
	while ($check = mysql_fetch_array($query)) {
			if ($check['pollid'] == $_POST['pollid']) {
				$content.="You have already voted in this poll.";
				$voted = 1;
				break;
				}
			}
	if ($voted!=1) {
				mysql_query("UPDATE polldata SET totalvotes=totalvotes+1 WHERE pollid=".$_POST['pollid']);
				$i=0;
				$pollid=$_POST['pollid'];
				if(!isset($_POST['comment']))
					$comment='No Comments';
				while ($_POST['oid'.$i]!='on') {
						$i++;
						}
				mysql_query("UPDATE polldata SET votes=votes+1 WHERE pollid=".$_POST['pollid']." AND optionid=".$i);
				mysql_query("INSERT INTO userdata  (userid,pollid,optionid,comment) VALUES ('$user','$pollid','$i','$comment')") or die (mysql_error());
				$content.="You have voted successfully";
			}
	echo $content;
	}


	function actionAdminuploadpoll() { //to upload the created polls-form action
		require_once 'create.php';
mysql_connect("localhost","delta","DeltainC") or die (mysql_error());
mysql_select_db("polls") or die (mysql_error());
if (isset ($_POST['submit'])) {

			if ($_POST['optionnumber']==0)
			{
				$question = trim($_POST['question']);
			 	$error=false;
			 	$err = array ();
				$comments = trim($_POST['comments']);
				$expire = trim($_POST['expire']);
				$status = trim($_POST['status']);
				$parent = substr($_SERVER['PHP_SELF'], 0, -strlen(strrchr($_SERVER['PHP_SELF'],'/'))+1);
				if (strlen($question) == 0) { //error reporing
					$error = true;
					$err[0] = true;
				}
				if($expire!=NULL)
						if ($_POST['exp_time']==NULL) {//error reporing
							$error = true;
							$err[3] = true;
							}
						else{
							$err[3]=false;
							$exp_time = $timestamp + trim($_POST['exp_time']) * 86400;}
				else
						$expire='no';
				if (strlen($_POST['ch1'])== 0) {
					$error = true;
					$err[1] = true;
				}
				if (strlen($_POST['ch2'])== 0) {
					$error = true;
					$err[2] = true;
				}

				if ($error==false) {
					echo 'hi';
						$timestamp = time();

					if ($status==NULL)
						$status = 'off';
					if ($comments==NULL)
						$comments = 'off';
					echo 'hi';
					mysql_query("INSERT INTO polls (question,parent,status,exp_time,expire,comments) VALUES 	('$question','$parent','$status','$exp_time','$expire','$comments')") or die (mysql_error());
					$pid = mysql_insert_id();
					$m=1;
					while($_POST['ch'.$m]!=NULL){
					$choice=trim($_POST['ch'.$m]);;
					mysql_query("INSERT INTO polldata  (pollid,question,optionid,optiontext,votes,totalvotes) VALUES ('$pid','$question','$m','$choice','0','0')") or die (mysql_error());
					$m++;
					}
				$content.="Your poll has been created successfully";
				echo $content;
				}
				else
					actionAdmincreatepoll($err,10);
			}
			else
				actionAdmincreatepoll(NULL,$_POST['optionnumber']);
	}
	else
		actionAdmincreatepoll(NULL,10);
	}

	function actionAdmincreatepoll($err,$options) {
		mysql_connect("localhost","delta","DeltainC") or die (mysql_error());
		mysql_select_db("polls") or die (mysql_error());
		$content.="<form method=POST action='uploadpoll.php'>";
		$content.="If you need more than 10 options select here. Specify the number.<br><select name=optionnumber><option value=0>&nbsp;";
		for ($i = 11; $i < 100; $i++)
			$content.="<option value=$i>$i";
			$content.="</select>";
			if (!isset($err[0])&&!isset($err[1])&&!isset($err[2])) {
				$content.='Enter the question here<input name="question" MAXLENGTH=100><br>';
				for ($i = 1; $i <= $options; $i++)

					$content.='OPTION' . $i . ' <input name="ch'.$i.'" MAXLENGTH=50><br>';
			} else {
				$content.='<br>ERROR!<br>';
				if ($err[0] == true)
					$content.="U forgot to Type a question<input name='question' MAXLENGTH=100><br>";
				else
					$content.='Type the question again<input name="question" value='.$question.'><br>';
				if ($err[1] == true)
					$content.='First option needs to be entered<input name="ch1" MAXLENGTH=100><br>';
				else
					$content.='OPTION1<input name="ch1" MAXLENGTH=50><br>';
				if ($err[2] == true)
					$content.='Second option needs to be entered<input name="ch2" MAXLENGTH=100><br>';
				else
					$content.='OPTION2 <input name="ch2" MAXLENGTH=50><br>';
				for ($i = 3; $i <= 10; $i++)
					$content.='OPTION' . $i . ' <input name="ch'.$i.'" MAXLENGTH=50><br>';
			}

			$content.="ENABLE COMMENTS<input type=checkbox name=comments ><br>";
			$content.="SHOULD POLL EXPIRE?<input type=checkbox name=expire ><br>";
			$content.="POLL ENABLED<input type=checkbox name=status CHECKED><br>";
			if ($err[3]==true){
				$content.="ERROR! YOU DIDN'T SPECIFY HOW MANY DAYS SHOULD THE POLL BE OPEN <select name=exp_time>";
				for ($i = 0; $i < 30; $i++) $content.="<option value=$i>$i";
					$content.="</select>";}
			else{
				$content.="IF APPLICABLE HOW MANY DAYS SHOULD THE POLL BE OPEN <select name=exp_time>";
				for ($i = 0; $i < 30; $i++) $content.="<option value=$i>$i";
					$content.="</select>";}

			$content.="<center><input type=submit name=submit value=submit></center>";
			$content.="</form>";
			echo $content;
			unset ($err[0],$err[1],$err[2],$err[3]);
	}

	function actionAdminarchivepoll($pollid) {
		require_once ("$this->connect()");
		$query = "UPDATE polls SET status ='2' WHERE id=$pollid";
	}

	function checkexpiry($pollid, $exp_time) {
		require_once $this->connect();
		$timestamp = time();
		if ($timestamp >= $exp_time) {
			mysql_query("UPDATE polls SET expire=2 status=2 WHERE id=$pollid");
			return 1;
		}
	}

	function actionAdmineditpoll() {

		if ($_GET['action'] == savechanges) {
			$id = $_POST['pollid'];

			$qn = trim($_POST['newname']);

			$sql = "UPDATE `poll` SET `question` = '$qn' WHERE `id` = '$id'";
			if (!empty ($qn)) {
				mysql_query($sql);
			}

			$sql = "SELECT optionid,optiontext FROM polldata WHERE id = $id";
			$result = mysql_query($sql);
			while ($disp = mysql_fetch_array($result)) {
				$no = $disp[optionid];
				$content.="$no ";
				$ch = trim($_POST["newch$no"]);
				$content.="$ch<br>";
				$sql = "UPDATE `choices` SET `optiontext` = '$ch' WHERE `optionid` = $no";
				if (!empty ($ch)) {
					mysql_query($sql);
				}
			}

			$content.="Changes saved!";
			exit;
		}

		elseif ($_GET['action'] == 'edit') {

			$id = $_GET['id'];
$content.='<form action="poll2.lib.php.php?action=savechanges" method=post>';


			$sql = "SELECT question FROM poll WHERE id = $id";
			$result = mysql_query($sql);
			$disp = mysql_fetch_array($result);
$content.='<table>
		<tr><td><b>Poll Question :</b></td></tr>
		<tr><td>';


			$content.="$disp['question']";

$content.='</td><td>
		<input name = "newname" ></td></tr><br>
		<tr><td><b>Choices : </b></td></tr>';



			$sql = "SELECT optionid,optiontext FROM polldata WHERE pollid = $id ORDER BY optionid ASC";
			$result = mysql_query($sql);
			while ($disp = mysql_fetch_array($result)) {
				$no = $disp[optionid];


				$content.='<tr><td>'.$disp[optiontext].'</td><td>';

				$content.="<input name = 'newch$no'></td></tr>";



			}

		$content.="</table>
		<input type=checkbox name=comments value='ENABLE COMMENTS'><br>
		<input type=checkbox name=expire value='CHECK IF YOU DO NOT WANT THE POLL TO EXPIRE'><br>
		<input type=checkbox name=status value='POLL ENABLED' CHECKED><br>
		<input type=checkbox name=exp_date value='HOW MANY DAYS SHOULD THE POLL BE OPEN (IF APPLICABLE)'><br>
		<input type='submit' name='submit' value=' Save Changes'>";

		$content.='<input type=hidden   name=pollid   value=' . $_GET['id'] . '></form>';
			exit;
		}
		return $content;
	}

}
?>