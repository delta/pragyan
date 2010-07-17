<?php
/**
 * @package pragyan
 * @copyright (c) 2008 Pragyan Team
 * @license http://www.gnu.org/licenses/ GNU Public License
 * For more details, see README
 */
/*
 * +view=> the hospi person can see all the available actions
 * +accomodate => the hospi desk gets to see the status of various rooms and their availability
 * 	subaction
 * +view&mode=hostel		=> Hostel wise view(default +accomodate view)
 * +view&mode=all			=> All rooms in all hostels.
 * check in					=> Clicks on a room in one of the view formats and then enters the guest mail id
 * check out				=> puts in the mail id of the user and then checks him out
 * addRoom					=> adds room to the hostel.+ capacity
 * addHostel				=> add hostel
 *
 * +(Not in hospi module) to get the list of people registered for workshop/prelim selected ppl/ paper presentation etc)

Hospi charges paid/ how much/caution deposit refundable/
reimbursement.


user Id of the person manning the hospi desk.
The user Id of the person refunding the caution/reimbursement.

Print the details of the user along with the name of the hospi person manning the desk

 */
class hospi implements module {
	private $userId;
	private $moduleComponentId;
	private $action;

	public function getHtml($gotuid, $gotmoduleComponentId, $gotaction) {
		$this->userId = $gotuid;
		$this->moduleComponentId = $gotmoduleComponentId;
		$this->action = $gotaction;

		if ($gotaction == 'view')
			return $this->actionView();
		if ($gotaction == 'post')/// what do post and accomodate do????
			return $this->actionPost();
		if ($gotaction == 'moderate')
			return $this->actionModerate();
		if ($gotaction == 'accomodate')
			return $this->actionAccomodate();
		if ($gotaction == 'addroom')
			return $this->actionAddroom();

	}

public function viewall()
{
	$hospiview=<<<VIEW
	<br><br>
		<a href="./+addRoom"> Add Room</a><br>
		<a href="./+addRoom&subaction=addhostel">Add Hostel</a><br>
		<!--a href="./+accomodate">Accomodate</a><br-->
		<a href="./+accomodate&quick"><b>Fast Accomodate</b></a><br>
		<a href="./+view&subaction=finduser">Search user</a><br>
		<a href="./+view&subaction=findroom">Search room</a><br>
		<a href="./+view&subaction=viewvacantrooms">View vacant rooms</a><br>
		<a href="./+view&subaction=viewstatus">View All Rooms</a><br>

VIEW;
	return $hospiview;
}


private function getEmailSuggestions($input) {
	$emailQuery ="SELECT `form_elementdata` FROM `form_elementdata` WHERE `page_modulecomponentid`=36 AND form_elementid IN (3,13,14,15)  AND form_elementdata LIKE '%$input%' ";
	$emailResult = mysql_query($emailQuery);
	$suggestions = array($input);
	while($emailRow = mysql_fetch_row($emailResult)) {
		$suggestions[] = $emailRow[0];
	}
	$query ="SELECT  `user_id` FROM `form_regdata` WHERE `page_modulecomponentid`=36 ";
	$result = mysql_query($query);
	while($temp=mysql_fetch_array($result))
	{
		$query1 = 'SELECT `user_email` FROM `' . MYSQL_DATABASE_PREFIX . 'users` WHERE `user_email` LIKE "%'.$input.'%" AND `user_id`='.$temp[0];
		$result1=mysql_query($query1);
		if(mysql_num_rows($result1)){
		$temp1=mysql_fetch_array($result1,MYSQL_NUM);
		$suggestions[] = $temp1[0];
		}
	}
	return join($suggestions, ',');
}


public function getUserDetails($email)
{
	$query="SELECT * FROM `hospi_accomodation_status` WHERE `hospi_guest_email`='$email' ORDER BY `hospi_actual_checkin` DESC  LIMIT 0,1";
	$result=mysql_query($query) or die (mysql_error()."in function getUserDetails in hospi") ;
	$temp=mysql_fetch_array($result,MYSQL_ASSOC);
	$query="SELECT `hospi_hostel_name`,`hospi_room_no` FROM `hospi_hostel` WHERE `hospi_room_id`=$temp[hospi_room_id]";
	$result=mysql_query($query) or die (mysql_error()."in function getUserDetails in hospi") ;
	$temp1=mysql_fetch_array($result,MYSQL_ASSOC);
	$userdetail=<<<UD
<table border="1">
<tr><td>Name</td><td>$temp[hospi_guest_name]</td></tr>
<tr><td>Email</td><td>$temp[hospi_guest_email]</td></tr>
<tr><td>Phone</td><td>$temp[hospi_guest_phone]</td></tr>
<tr><td>Hostel</td><td>$temp1[hospi_hostel_name]</td></tr>
<tr><td>Room</td><td>$temp1[hospi_room_no]</td></tr>
</table>
<a onClick="history.go(-1)">BACK</a>
UD;
return $userdetail;

}



public function actionAccomodate() {
	$i=0;
static $j=0;
foreach ($_GET as $var => $val) {
	

if($j<(count($_GET)-1))
{
			if ($i == 1)
				$action .= "&" . $var . "=" . $val;
			if ($val=='accomodate') {
				$action .= "+" . $val;
				$i = 1;
			}
			$j=$j+1;

}
			}
			$action='<a onClick="history.go(-1)">BACK</a>';
			
			if(isset($_GET['displayUserDetails']))
{
	return $this->getUserDetails($_GET['displayUserDetails']);
}

if(isset($_GET['quick'])){
global $sourceFolder,$cmsFolder;
		global $moduleFolder;
		global $urlRequestRoot;
		global $templateFolder;
		$calpath = "$urlRequestRoot/$cmsFolder/$moduleFolder";
		$scriptsFolder = "$urlRequestRoot/$cmsFolder/$templateFolder/common/scripts";
		$imagesFolder = "$urlRequestRoot/$cmsFolder/$templateFolder/common/images";
$checkIn=<<<CHECKIN
<link rel="stylesheet" type="text/css" media="all" href="$calpath/form/calendar/calendar.css" title="Aqua" />
						 <script type="text/javascript" src="$calpath/form/calendar/calendar.js"></script>

<form name="hospi_check_in" method="POST" action="+accomodate&quick">



<table>
<tr>
<td>Guest Name:</td><td><input type="text" name="guest_name" size="20" maxlength="100"   ></td>
</tr><tr>
<td>Email:</td><td><input type="text" name="guest_email" id="guest_email" size="20" maxlength="100" />
<div id="suggestionsBox" style="background-color: white; width: 260px; border: 1px solid black; position: absolute; overflow-y: scroll; max-height: 180px; display: none"></div>
</td>
</tr><tr>

<td>Phone No.:</td><td><input type="text" name="guest_phone" size="20" maxlength="100" ></td>
</tr><tr>
<td>College:</td><td><input type="text" name="guest_college" size="20" maxlength="100" ></td>
</tr><tr>
<td>Expected Check out:</td>
<td><input type="text"  name="check_out"  id="check_out" /><input name="calc" type="reset" value="Calendar" onclick="return showCalendar('check_out', '%Y-%m-%d %H:%M:%S', '24', true);" /></td>
</tr><tr>


<td>Cash Paid?</td><td><input type="checkbox" name="cash_paid"></td>
</tr><tr>
<td>Hostel allotted:</td></tr>

CHECKIN;

$query="SELECT DISTINCT `hospi_hostel_name` FROM `hospi_hostel` ";
		$result=mysql_query($query)or die(mysql_error());
		while($temp=mysql_fetch_array($result,MYSQL_ASSOC))
		{
			$hostel=$temp['hospi_hostel_name'];
			$checkIn.='<td><div><tr><td><b>'.$hostel.'</b></td><td>';
			$checkIn.="<select name=\"hostel_$hostel\"><option>Room No.</option>";
			$query1="SELECT `hospi_room_no` FROM `hospi_hostel` WHERE  `hospi_hostel_name`='$hostel' AND `hospi_room_no`<>0";
			$result1=mysql_query($query1);
			while($temp1=mysql_fetch_array($result1,MYSQL_NUM)){
			foreach($temp1 as $room)

			{

				$query3="SELECT * FROM `hospi_hostel` WHERE `hospi_hostel_name`='$hostel' AND `hospi_room_no`=$room";
				$result3=mysql_query($query3);
				$temp3=mysql_fetch_array($result3, MYSQL_ASSOC);
				$query4="SELECT * FROM `hospi_accomodation_status` WHERE `hospi_room_id`=$temp3[hospi_room_id] AND `hospi_actual_checkout` IS NULL";
				$result4=mysql_query($query4);
				$num=mysql_num_rows($result4);

				if ($num<$temp3['hospi_room_capacity'])
				$status="VACANT";
				else $status="FULL";









				$checkIn.="<option value=\"$room\" id=\"$room\">".$room."  ".$status."  (".$num."/".$temp3[hospi_room_capacity].")"."</option>";

			}


			}
				$checkIn.="</select></td></tr></div>";
		}
	$checkIn.='</td></tr><tr><td><input type="submit" value="Check In"></td></tr></table>';
	$checkIn.=<<<TAG
<script type="text/javascript" language="javascript" src="$scriptsFolder/ajaxsuggestionbox.js">

</script>
<script language="javascript">
	var userBox = new SuggestionBox(document.getElementById('guest_email'), document.getElementById('suggestionsBox'), "./+accomodate&subaction=getsuggestions&forwhat=%pattern%");
	userBox.loadingImageUrl = '$imagesFolder/ajaxloading.gif';
</script>

</form>
TAG;




if(isset($_POST['guest_name']))
{
		static $room_no,$hostel;
		$query="SELECT DISTINCT `hospi_hostel_name` FROM `hospi_hostel` ";
		$result=mysql_query($query)or die(mysql_error());
		while($temp=mysql_fetch_array($result,MYSQL_ASSOC))
		{
			$hostel_name="hostel_".$temp['hospi_hostel_name'];
			if(is_numeric($_POST[''.$hostel_name.'']))
			{
				if(is_numeric($room_no))
				{
					displayerror("More than one room selected!!");
					return '';

				}
				$room_no=$_POST[''.$hostel_name.''];
				$hostel=$temp['hospi_hostel_name'];
			}
		}

		$query="SELECT `hospi_room_id` FROM `hospi_hostel` WHERE `hospi_hostel_name`='$hostel' AND `hospi_room_no`=$room_no";
		$result1=mysql_query($query);
		$temp=mysql_fetch_assoc($result1);
		$room_Id=$temp['hospi_room_id'];
		if($room_Id==0)
		{
			displayerror("No room allotted!!");
			return;
		}

		if(isset($_POST['cash_paid']))$paid=1;else $paid=0;

	$query="INSERT INTO `hospi_accomodation_status` (`hospi_room_id`,`hospi_actual_checkin`,`hospi_checkedin_by`,`hospi_projected_checkout`,`hospi_cash_collected`,`hospi_guest_name`,`hospi_guest_college`,`hospi_guest_phone`,`hospi_guest_email`) VALUES ('$room_Id',NOW(),'$this->userId','$_POST[check_out]','$paid','$_POST[guest_name]','$_POST[guest_college]','$_POST[guest_phone]','$_POST[guest_email]')";
	$result=mysql_query($query) or displayerror(mysql_error());
	if(!(mysql_error()))
	displayinfo("$_POST[guest_name] checked in successfully");
	else displayerror("Failed to check in $_POST[guest_name]");

}

return $checkIn.$this->viewall();
}


$room.='<table border="1"><tr>';
if(isset($_GET['subaction']) && $_GET['subaction'] == 'getsuggestions' && isset($_GET['forwhat'])) {
	echo $this->getEmailSuggestions($_GET['forwhat']);
	disconnect();
	exit();

}
elseif(!isset($_GET['hostel'])){
$query="SELECT DISTINCT `hospi_hostel_name` FROM `hospi_hostel` ";
		$result=mysql_query($query);
		while($temp=mysql_fetch_array($result,MYSQL_ASSOC))
		{
			$room.='<td > <a href="+accomodate&hostel='.$temp['hospi_hostel_name'].'">'. $temp['hospi_hostel_name'].' </td>';
		}
$room.="</tr></table>";
$room.=$action;

	return $room.$this->viewall();
}
elseif(!isset($_GET['room_id']))
{
	$query="SELECT * FROM `hospi_hostel` WHERE `hospi_hostel_name`='$_GET[hostel]' AND `hospi_room_id`!=0";
		$result=mysql_query($query);
	$room.='</tr><tr ><td >'.$_GET['hostel'].'</td>';
		while($temp=mysql_fetch_array($result,MYSQL_ASSOC))
		{
			$status="Vacant";
			$query1="SELECT * FROM `hospi_accomodation_status` WHERE `hospi_room_id`=$temp[hospi_room_id] AND `hospi_actual_checkout` IS NULL";
			$result1=mysql_query($query1);
			$temp1=mysql_fetch_array($result1, MYSQL_ASSOC);
			if(mysql_num_rows($result1)<$temp['hospi_room_capacity']);
			else $status="Full";
			$room.='<td > <a href="+accomodate&hostel='.$temp['hospi_hostel_name'].'&room_id='.$temp['hospi_room_id'].'">'.$temp['hospi_room_no'].'              ' ;
			$room.="$status      (".mysql_num_rows($result1)."/".$temp['hospi_room_capacity'].")";
			$room.='</td>';
		}
$room.="</tr></table>";
$room.=$action;;
	return $room.$this->viewall();
}
else{

	if(isset($_GET['checkIn']))
{
	if(isset($_POST['cash_paid']))$paid=1;else $paid=0;
	$userId=getUserIdFromEmail($_POST['txtUserEmail']);
	if($userId!=0){
	$query1="SELECT * FROM `hospi_accomodation_status` WHERE `user_id`=$userId AND `hospi_actual_checkout` IS NULL ";
	$result1=mysql_query($query1);
	$room1=mysql_fetch_assoc($result1);
	if(!(mysql_num_rows($result1))){
		$name=getUserFullName($userId);
		$email=getUserEmail($userId);


	$query="INSERT INTO `hospi_accomodation_status` (`hospi_room_id`,`user_id`,`hospi_actual_checkin`,`hospi_checkedin_by`,`hospi_projected_checkout`,`hospi_cash_collected`,`hospi_guest_name`,`hospi_guest_email`) VALUES ('$_GET[room_id]','$userId',NOW(),'$this->userId','$_POST[check_out]','$paid','$name','$email')";
	$result=mysql_query($query) or displayerror(mysql_error());
	if(!(mysql_error()))
	displayinfo("$_POST[txtUserEmail] checked in successfully");
	else displayerror("Failed to check in $_POST[txtUserEmail]");
	}
	else
	{
		$query="SELECT `hospi_hostel_name` FROM `hospi_hostel` WHERE `hospi_room_id`='$room1[hospi_room_id]'";
		$result=mysql_query($query) or die (mysql_error());
		$room2=mysql_fetch_row($result);
		displayerror("User is already checked in <a href=\"+accomodate&hostel=$room2[0]&room_id=$room1[hospi_room_id]\">here</a>");
	}
}
}


if((isset($_GET['checkOut'])))
{
	if(is_numeric($_GET['checkOut']))
	$cond='`user_id`='.$_GET['checkOut'].'';
	else $cond='`hospi_guest_name`=\''.$_GET['checkOut'].'\' AND `hospi_actual_checkin`=\''.$_GET['checkinTime'].'\' AND `hospi_checkedin_by`='.$_GET['by'].'';
	$query="UPDATE `hospi_accomodation_status` SET `hospi_actual_checkout`=NOW(),`hospi_checkedout_by`= '$this->userId' WHERE `hospi_room_id`='$_GET[room_id]' AND $cond AND `hospi_actual_checkout` IS NULL ";
	$result=mysql_query($query);
	if(mysql_error())displayerror(mysql_error());


}
	global $urlRequestRoot;
	global $sourceFolder,$cmsFolder;
	global $templateFolder;
	$scriptsFolder = "$urlRequestRoot/$cmsFolder/$templateFolder/common/scripts";
	$imagesFolder = "$urlRequestRoot/$cmsFolder/$templateFolder/common/images";

	$query="SELECT * FROM `hospi_accomodation_status` WHERE `hospi_room_id`=$_GET[room_id] AND `hospi_actual_checkout` IS NULL ";
	$result=mysql_query($query);
	$room.='</tr><tr ><td >'.$_GET['hostel'].'</td></tr>';
	while($temp=mysql_fetch_array($result,MYSQL_ASSOC))
	{
		if($temp['user_id']<>0)
		$room.="<tr><td><a href=\"+accomodate&displayUserDetails=$temp[hospi_guest_email] \">".getUserFullName($temp['user_id'])."</a></td><td><input type=\"submit\" value=\"Check Out\" onclick=\"window.location='./+accomodate&hostel=$_GET[hostel]&room_id=$_GET[room_id]&checkOut=$temp[user_id]'\"></td></tr>";
		else
		$room.="<tr><td><a href=\"+accomodate&displayUserDetails=$temp[hospi_guest_email]\">".$temp['hospi_guest_name']."</td><td><input type=\"submit\" value=\"Check Out\" onclick=\"window.location='./+accomodate&hostel=$_GET[hostel]&room_id=$_GET[room_id]&checkOut=$temp[hospi_guest_name]&checkinTime=$temp[hospi_actual_checkin]&by=$temp[hospi_checkedin_by]'\"></td></tr>";

	}
$room.="</tr></table>";
global $sourceFolder,$cmsFolder;
		global $moduleFolder;
		global $urlRequestRoot;

		$calpath = "$urlRequestRoot/$cmsFolder/$moduleFolder";
$room.=<<<FORM
<style type="text/css">
<!--
	span.suggestion {
		padding: 2px 4px 2px 4px;
		display: block;
		background-color: white;
		cursor: pointer;
	}
	span.suggestion:hover {
		background-color: #DEDEDE;
	}
-->
</style>
<link rel="stylesheet" type="text/css" media="all" href="$calpath/form/calendar/calendar.css" title="Aqua" />
						 <script type="text/javascript" src="$calpath/form/calendar/calendar.js"></script>

<form method="POST" action="./+accomodate&hostel=$_GET[hostel]&room_id=$_GET[room_id]&checkIn=1">

Guest Name<input type="text" name="txtUserEmail" id="txtUserEmail"  autocomplete="off" style="width: 256px" /><br>

Expected Check out<input type="text"  name="check_out"  id="check_out" /><input name="calc" type="reset" value="Calendar" onclick="return showCalendar('check_out', '%Y-%m-%d %H:%M:%S', '24', true);" />

<div id="suggestionsBox" style="background-color: white; width: 260px; border: 1px solid black; position: absolute; overflow-y: scroll; max-height: 180px; display: none"></div>

<br>Cash Paid?<input type="checkbox" name="cash_paid">

<br>
<input type="submit" value="Check In" />

<script type="text/javascript" language="javascript" src="$scriptsFolder/ajaxsuggestionbox.js">

</script>
<script language="javascript">
	var userBox = new SuggestionBox(document.getElementById('txtUserEmail'), document.getElementById('suggestionsBox'), "./+accomodate&subaction=getsuggestions&forwhat=%pattern%");
	userBox.loadingImageUrl = '$imagesFolder/ajaxloading.gif';
</script>


</form>
FORM;

$room.=$action;
	return $room.$this->viewall();

}
}


public function actionAddroom() {




	/*
	 * SELECT DISTINCT hospi_hostel_name FROM hospi_hostel
	 * display each as an option in a dropdown menu
	 * add room no. and capacity to the room
	 * insert into hospi_hostel with hospi_room_id as
	 *
	 * $query = 'SELECT MAX(`hospi_room_id`) FROM `' . MYSQL_DATABASE_PREFIX . 'hospi_hostel`';
	 * 		$result = mysql_query($query) or die(mysql_error());
	 * 		$row = mysql_fetch_row($result);
	 * 		$room_id = 1;
	 * 		if(!is_null($row[0])) {
	 * 			$room_id = $row[0] + 1;
	 * 	}
	 *
	 *subaction=addHostel
	 *
	 *just a simple text box with add hostel if the hostel added is not already in db
	 *
	 *For Shruthi:: The user must be able to jump on to any page from any other page,
	 *	basically links for each action to be present in every action.
	 *	You can make this into a function and then call it every where else.
	 *
	 * */



		 $subaction=$_GET['subaction'];
	 	$i=0;
		static $j=0;
		foreach ($_GET as $var => $val) {
		if($j<(count($_GET)-1))
		{
			if ($i == 1)
				$action .= "&" . $var . "=" . $val;
			if ($val=='addRoom') {
				$action .= "+" . $val;
			$i = 1;
		}
		$j=$j+1;
	}
				}
				$action='<a onClick="history.go(-1)">BACK</a>';
	 if(isset($_GET['subaction']))
	 {

	 	if($subaction=='submitaddroom')
	 	{
			if(($_POST['roomNo']=='')or($_POST['capacity']==''))
			{
				displayerror('All fields not entered');
				return'';
			}
			$query="SELECT * FROM `hospi_hostel` WHERE `hospi_room_no`={$_POST['roomNo']} AND `hospi_hostel_name`={$_POST['hostels']}";
			$result=mysql_query($query);
			if(mysql_num_rows($result))
			{
				displayerror('Room no. already exists in the database for the hostel.');
				return'';
			}
			$query = 'SELECT MAX(`hospi_room_id`) FROM `hospi_hostel`';
	  		$result = mysql_query($query) or die('error');
	  		$row = mysql_fetch_row($result);
//	  		$room_id = 1;
	  		if(!is_null($row[0])) {
	  			$room_id = $row[0] + 1;
	  		}
	  		$query="INSERT INTO `hospi_hostel` (`hospi_room_id`,`hospi_hostel_name`,`hospi_room_capacity`,`hospi_room_no`)".
					"VALUES('$room_id','{$_POST['hostels']}',{$_POST['capacity']},{$_POST['roomNo']}) ";
			$result=mysql_query($query);
			if(!$result)
			{
				displayerror('Error while adding room data');
				return'';
			}
			return $action.$this->viewall();
		}
		else if(isset($_POST['hostel']))
		{
			if($_POST['hostel']=='')
			{
				displayerror('Please enter a name for hostel');
				return'';
			}

			$query = 'SELECT MAX(`hospi_room_id`) FROM `hospi_hostel`';
	  		$result = mysql_query($query) or die('error');
	  		$row = mysql_fetch_row($result);
//	  		$room_id = 1;
	  		if(!is_null($row[0])) {
	  			$room_id = $row[0] + 1;
	  		}

			$query="INSERT INTO `hospi_hostel` (`hospi_hostel_name`,`hospi_room_id`) VALUES ('{$_POST['hostel']}','$room_id')";
			$result=mysql_query($query);
			if(!$result)
			{
				displayerror(mysql_error());
				return'';
			}
			$action;
		}
	 	else if($subaction=='addhostel')
	 	{
			$newhostel=<<<HOSTEL
			<form method="POST" action="./+addroom&subaction=addhostel">
			Hostel:<input type="text" name="hostel" id="hostel"><br>
			<input type="submit" value="Add Hostel"><br>
HOSTEL;
			return $newhostel.$action.$this->viewall();

	 	}
	 }



	 $query="SELECT DISTINCT `hospi_hostel_name` FROM `hospi_hostel`";
	 $result=mysql_query($query);
	 $hostel.=<<<ROOM
<form method="POST" action="+addroom&subaction=submitaddroom">
<select name="hostels" id="hostels" >
ROOM;
	 while($temp=mysql_fetch_array($result,MYSQL_NUM))
	 {
	 	foreach($temp as $hostelname)
	 	{
	 		$hostel.='<option value='.$hostelname.'>'.$hostelname.'</option>';
	 	}

	 }
$hostel.=<<<HOSTEL
</select>
<br>
Room No:<input type="text" name="roomNo" id="roomNo"><br>
Capacity:<input type="text" name="capacity" id="capacity"><br>
<input type="submit" value="Add Room"><br>
</form>
<br><a href="./+addroom&subaction=addhostel">Add hostel<br>
HOSTEL;

	 return $hostel.$action.$this->viewall();
}


	public function Uplevelroom()
	{
		foreach ($_GET as $var => $val) {
			$i=0;
			if ($i == 1)
				$action .= "&" . $var . "=" . $val;
			if ($val=='addroom') {
				$action .= "+" . $val;
				$i = 1;
			}}
			return $action.$this->viewall();
	}
	public function actionView() {
		$subaction=$_GET['subaction'];
	 	$i=0;
		static $j=0;
		foreach ($_GET as $var => $val) {
		if($j<(count($_GET)-1))
		{
			if ($i == 1)
				$action .= "&" . $var . "=" . $val;
			if ($val=='View') {
				$action .= "+" . $val;
			$i = 1;
		}
		$j=$j+1;
	}
				}
				$action='<a onClick="history.go(-1)">BACK</a>';
		if(isset($_GET['subaction']))
		{
			 if($_GET['subaction'] == 'getsuggestions' && isset($_GET['forwhat'])) {
				echo $this->getEmailSuggestions($_GET['forwhat']);
				exit();
				}
			$subaction=$_GET['subaction'];
			if($subaction=='displayuser')
			{
//				print_r($_POST);
				$search=$_POST['txtUserEmail'];
				$userid=getUserIdFromEmail($_POST[txtUserEmail]);
				if(is_numeric($userid))
				$query="SELECT * FROM `hospi_accomodation_status` WHERE `user_id`=$userid";
				else
				$query="SELECT * FROM `hospi_accomodation_status` WHERE `hospi_guest_name` LIKE '%$search%' OR `hospi_guest_email` LIKE '%$search%' OR `hospi_guest_college` LIKE '%$search%'";
				$result=mysql_query($query);
				if(!$result)
				{

					displayerror(mysql_error());
					return'';
				}
				if(!mysql_num_rows($result))
				{
					displayinfo('The user has not checked into any room');
					return'';
				}
				else
				{
					$details.=<<<USER
					<b>User Email:{$_POST['txtUserEmail']}</b><br>
USER;
					while($row=mysql_fetch_array($result))
					{
					$query="SELECT * FROM `hospi_hostel` WHERE `hospi_room_id`={$row['hospi_room_id']}";
					$result1=mysql_query($query);
					$row1=mysql_fetch_array($result1);
					$details.=<<<USER
					<br>
					<table border="1">
					<tr>
					<td nowrap="nowrap">Name</td>
					<td nowrap="nowrap">{$row['hospi_guest_name']}</td>
					</tr>
					<tr>
					<td nowrap="nowrap">Email</td>
					<td nowrap="nowrap">{$row['hospi_guest_email']}</td>
					</tr>
					<tr>
					<td nowrap="nowrap">College</td>
					<td nowrap="nowrap">{$row['hospi_guest_college']}</td>
					</tr>
					<tr>
					<td nowrap="nowrap">Phone</td>
					<td nowrap="nowrap">{$row['hospi_guest_phone']}</td>
					</tr>
					<tr>
					<td nowrap="nowrap">Hostel</td>
					<td nowrap="nowrap">{$row1['hospi_hostel_name']}</td>
					</tr>
					<tr>
					<td nowrap="nowrap">Room no.</td>
					<td nowrap="nowrap">{$row1['hospi_room_no']}</td>
					</tr>
					<tr>
					<td nowrap="nowrap">Checked in on</td>
					<td nowrap="nowrap">{$row['hospi_actual_checkin']}</td>
					</tr>



USER;
					if($row['hospi_actual_checkout'])
					{
						$details.="<tr><th nowrap=\"nowrap\">Checked out on</th><th nowrap=\"nowrap\">{$row['hospi_actual_checkout']}</th></tr>";
					}
					$details.='</table>';
					}
				return $details.'<a onClick="history.go(-1)">BACK</a>'.$this->viewall();

				}
			}
			if($subaction=='finduser')
			{
				global $urlRequestRoot,$sourceFolder,$templateFolder,$cmsFolder;

			$scriptsFolder = "$urlRequestRoot/$cmsFolder/$templateFolder/common/scripts";
			$imagesFolder = "$urlRequestRoot/$cmsFolder/$templateFolder/common/images";
			$find=<<<USER


			<form method="POST" action="./+view&subaction=displayuser">
			Enter user:<input type="text" name="txtUserEmail" id="txtUserEmail"  autocomplete="off" style="width: 256px" />
			<div id="suggestionsBox" style="background-color: white; width: 260px; border: 1px solid black; position: absolute; overflow-y: scroll; max-height: 180px; display: none"></div>
			<input type="submit" Value="Find User"/>
			<script type="text/javascript" language="javascript" src="$scriptsFolder/ajaxsuggestionbox.js">
			</script>
			<script language="javascript">
			var userBox = new SuggestionBox(document.getElementById('txtUserEmail'), document.getElementById('suggestionsBox'), "./+view&subaction=getsuggestions&forwhat=%pattern%");
			userBox.loadingImageUrl = '$imagesFolder/ajaxloading.gif';
			</script>
			</form>




USER;
			return $find.$action.$this->viewall();
			}

			if($subaction=="viewstatus")
			{
					$query="SELECT DISTINCT `hospi_hostel_name` FROM `hospi_hostel` ";
					$result4=mysql_query($query)or die(mysql_error());
					$statusall=<<<ROOM
					<table border="1">

ROOM;
					static $i;
					while($temp4=mysql_fetch_array($result4,MYSQL_ASSOC))
					{
						$query="SELECT *  FROM `hospi_hostel` WHERE `hospi_hostel_name`='$temp4[hospi_hostel_name]' AND `hospi_room_no`<>0";
						$result=mysql_query($query)or die(mysql_error());
						$num=mysql_num_rows($result);
						$statusall.='<tr><td rowspan="'.($num+1).'">'.$temp4['hospi_hostel_name'].'</td>';
						while($temp=mysql_fetch_array($result,MYSQL_ASSOC))
						{

							$statusall.="</tr>";


						$status="Vacant";
						$query1="SELECT * FROM `hospi_accomodation_status` WHERE `hospi_room_id`=$temp[hospi_room_id] AND `hospi_actual_checkout` IS NULL";
						$result1=mysql_query($query1);

						if(mysql_num_rows($result1)<$temp['hospi_room_capacity']);
						else $status="Full";
						$statusall.='<tr><td> <a href="+accomodate&hostel='.$temp['hospi_hostel_name'].'&room_id='.$temp['hospi_room_id'].'">'.$temp['hospi_room_no'].'              ' ;
						$statusall.="$status      (".mysql_num_rows($result1)."/".$temp['hospi_room_capacity'].")";
						$statusall.='</td><td>';
						while($temp1=mysql_fetch_array($result1, MYSQL_ASSOC)){
						
						{
							$statusall.="<a href=\"+accomodate&displayUserDetails=$temp1[hospi_guest_email]\" >$temp1[hospi_guest_name]</a>,";

						}
						}
						$statusall.='</td></tr>';


						}
					}
					$statusall.='</tr></table>';
					return $statusall.$this->viewall();





			}

			if($subaction=='displayroom')
			{
				if($_POST['roomno']<>'')$cond="`hospi_room_no`={$_POST['roomno']} AND";
				$query="SELECT * FROM `hospi_hostel` WHERE $cond `hospi_hostel_name`='{$_POST['hostels']}' ";
				$result=mysql_query($query);
				if(!mysql_num_rows($result))
				{
					displayerror('Room not present');
					return'';
				}
				$row=mysql_fetch_array($result);
				$query="SELECT * FROM `hospi_accomodation_status` WHERE `hospi_room_id`={$row['hospi_room_id']} AND `hospi_actual_checkin` IS NOT NULL";
				$result1=mysql_query($query);
				$room=<<<DETAILS
				<table border="1">
				<tr>
				<th nowrap="nowrap">Hostel:</th>
				<th nowrap="nowrap">{$row['hospi_hostel_name']}</th>
				</tr>
				<tr>
				<th nowrap="nowrap">Room No.:</th>
				<th nowrap="nowrap">{$row['hospi_room_no']}</th>
				</tr>
				</table><br><br>

				Guests alloted:<br>

DETAILS;
					while($row1=mysql_fetch_assoc($result1))
					{
						$username=getUserEmail($row1['user_id']);
						$room.=<<<DETAILS
						<br>
						<table border="1">
						<tr>
						<th nowrap="nowrap">email:</th>
						<th nowrap="nowrap">$username</th>
						</tr>
						<tr>
						<th nowrap="nowrap">Checked in on:</th>
						<th nowrap="nowrap">{$row1['hospi_actual_checkin']}</th>
						</tr>
						<tr>
DETAILS;
						if($row1['hospi_actual_checkout'])
						{
							$room.="<th nowrap=\"nowrap\">Checked out on:</th><th nowrap=\"nowrap\">{$row1['hospi_actual_checkout']}</th></tr></table>";
						}
						else
						{
							$room.="<th nowrap=\"nowrap\">Projected check out:</th><th nowrap=\"nowrap\">{$row1['hospi_projected_checkout']}</th></tr></table>";
						}
					}
			return $room.'<a onClick="history.go(-1)">BACK</a>'.$this->viewall();
			}
			if($subaction=='findroom')
			{
				$query="SELECT DISTINCT `hospi_hostel_name` FROM `hospi_hostel`";
	 			$result=mysql_query($query);
				$room=<<<ROOM
				<form method="POST" action="./+view&subaction=displayroom">
				<select name="hostels" id="hostels" >
ROOM;
				 while($temp=mysql_fetch_array($result,MYSQL_NUM))
	 			{
	 				foreach($temp as $hostelname)
	 				{
	 					$room.='<option value='.$hostelname.'>'.$hostelname.'</option>';
	 				}
	 			}
	 			$room.=<<<ROOM
	 			</select><br>
				Room No.:<input type="text" name="roomno" />
				<input type="submit" Value="Find Room"/>
				</form>
ROOM;
				return $room.$action.$this->viewall();
			}
			if($subaction=='displayvacantrooms')
			{
				if($_POST['hostels']=="all")
				{
					$query="SELECT DISTINCT `hospi_hostel_name` FROM `hospi_hostel`";
	 				$res=mysql_query($query);
	 				while($row=mysql_fetch_array($res))
	 				{
	 					$query="SELECT * FROM `hospi_hostel` WHERE `hospi_hostel_name`='{$row[hospi_hostel_name]}'  ";
						$result=mysql_query($query);
						$room.='<table border="1"><tr>';
						$room.='</tr><tr ><td >'.$row['hospi_hostel_name'].'</td>';
						while($temp=mysql_fetch_array($result,MYSQL_ASSOC))
						{
						$status="Vacant";
						$query1="SELECT * FROM `hospi_accomodation_status` WHERE `hospi_room_id`=$temp[hospi_room_id] AND `hospi_actual_checkout` IS NULL";
						$result1=mysql_query($query1);
						$temp1=mysql_fetch_array($result1, MYSQL_ASSOC);
						if(mysql_num_rows($result1)<$temp['hospi_room_capacity'])
						{
						$room.='<td width="95" height="95"> <a href="+accomodate&hostel='.$temp['hospi_hostel_name'].'&room_id='.$temp['hospi_room_id'].'">'.$temp['hospi_room_no'].'              ' ;
						$room.="$status (".mysql_num_rows($result1)."/".$temp['hospi_room_capacity'].")";
						$room.='</td>';
						}
						}
				    	$room.="</tr></table>";
					}
					$room.='<a onClick="history.go(-1)">BACK</a>';
					return $room.$this->viewall();
	 			}
				else
				{
					$query="SELECT * FROM `hospi_hostel` WHERE `hospi_hostel_name`='$_POST[hostels]'  ";
					$result=mysql_query($query);
					$room.='<table border="1"><tr>';
					$room.='</tr><tr ><td >'.$_POST['hostels'].'</td>';
					while($temp=mysql_fetch_array($result,MYSQL_ASSOC))
					{
						$status="Vacant";
						$query1="SELECT * FROM `hospi_accomodation_status` WHERE `hospi_room_id`=$temp[hospi_room_id] AND `hospi_actual_checkout` IS NULL";
						$result1=mysql_query($query1);
						$temp1=mysql_fetch_array($result1, MYSQL_ASSOC);
						if(mysql_num_rows($result1)<$temp['hospi_room_capacity']);
						else $status="Full";
						if($status!='Full'){
						$room.='<td width="95" height="95"> <a href="+accomodate&hostel='.$temp['hospi_hostel_name'].'&room_id='.$temp['hospi_room_id'].'">'.$temp['hospi_room_no'].'              ' ;
						$room.="$status (".mysql_num_rows($result1)."/".$temp['hospi_room_capacity'].")";
						$room.='</td>';}
					}
				    $room.="</tr></table>";
					$room.='<a onClick="history.go(-1)">BACK</a>';
					return $room.$this->viewall();
				}
			}
			if($subaction=='viewvacantrooms')
			{
				$query="SELECT DISTINCT `hospi_hostel_name` FROM `hospi_hostel`";
	 			$result=mysql_query($query);
				$room=<<<ROOM
				<form method="POST" action="./+view&subaction=displayvacantrooms">
				Choose hostel:<select name="hostels" id="hostels" >
ROOM;
				 while($temp=mysql_fetch_array($result,MYSQL_NUM))
	 			{
	 				foreach($temp as $hostelname)
	 				{
	 					$room.='<option value='.$hostelname.'>'.$hostelname.'</option>';
	 				}

	 			}
	 			$room.='<option value="all">All hostels</option></select><br><br>';
	 			$room.='<input type="submit" Value="Find Vacant Rooms"/></form>';
	 			return $room.$this->viewall();
			}
		}
		return($this->viewall());
	}
	public function deleteModule($moduleComponentId) {

	}public function createModule(&$moduleComponentId) {
		$query = 'SELECT MAX(`hospi_room_id`) FROM `hospi_hostel`';
	  		$result = mysql_query($query) or die('error');
	  		$row = mysql_fetch_row($result);
//	  		$room_id = 1;
	  		if(!is_null($row[0])) {
	  			$room_id = $row[0] + 1;
	  		}
	  		$query="INSERT INTO `hospi_hostel` (`hospi_room_id`)".
					"VALUES('$room_id') ";
			$result=mysql_query($query);


	}


	public function copyModule($moduleComponentId)
	{

	}

	}
