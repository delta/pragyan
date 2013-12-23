<?php

if(!defined('__PRAGYAN_CMS')) { 
	header($_SERVER['SERVER_PROTOCOL'].' 403 Forbidden');
	echo "<h1>403 Forbidden<h1><h4>You are not authorized to access the page.</h4>";
	echo '<hr/>'.$_SERVER['SERVER_SIGNATURE'];
	exit(1);
}

/**
 * @package pragyan
 * @copyright (c) 2012 Pragyan Team
 * @author shriram<vshriram93@gmail.com>
 * @license http://www.gnu.org/licenses/ GNU Public License
 * For more details, see README
 */

function assignVars($userId,$mcId) {
  $query = "SELECT * FROM `".MYSQL_DATABASE_PREFIX."users` WHERE `user_id` = '".$userId."'";
  $result = mysql_query($query);
  $row = mysql_fetch_assoc($result);
  $arr = array ('PRAGYAN_ID'=> $row['user_id'] , 'NAME' => $row['user_name'] , 'EMAIL' => $row['user_email']);
  $userDetail = "SELECT * FROM `prhospi_accomodation_status` WHERE `page_modulecomponentid`={$mcId} AND `user_id` = {$userId}";
  $userResult = mysql_query($userDetail) or displayerror(mysql_error());
  $row = mysql_fetch_array($userResult);
  $columnDetail = mysql_query("SHOW COLUMNS FROM `prhospi_accomodation_status`") or displayerror(mysql_error());
  $cnt = 0;
  while($res = mysql_fetch_array($columnDetail)) {
    if($res[0] == 'hospi_room_id') {
      $userDataHostel = mysql_query("SELECT `hospi_hostel_name` FROM prhospi_hostel WHERE hospi_room_id={$row[$cnt]}") or displayerror(mysql_error());
      $rowHostelName=mysql_fetch_array($userDataHostel);
      $ret[strtoupper($res[0])] = strtoupper($rowHostelName[0]); 
  
      $userDataHostel = mysql_query("SELECT `hospi_room_no` FROM prhospi_hostel WHERE hospi_room_id={$row[$cnt]}") or displayerror(mysql_error());
      $rowHostelName=mysql_fetch_array($userDataHostel);
      $ret[strtoupper('hospi_room_no')] = strtoupper($rowHostelName[0]); 
      $cnt++;
  }
    else $ret[strtoupper($res[0])] = $row[$cnt++];
  }
  $collegeName = mysql_query("SELECT form_elementdata FROM form_elementdata WHERE user_id={$userId} AND page_modulecomponentid=0 AND form_elementid=6" ) or displayerror(mysql_error()); 
  if(mysql_num_rows($collegeName)) {
      $sol=mysql_fetch_array($collegeName);
      $ret['COLLEGE']=$sol[0];
  } 
  $collegeName = mysql_query("SELECT form_elementdata FROM form_elementdata WHERE user_id={$userId} AND page_modulecomponentid=0 AND form_elementid=5" ) or displayerror(mysql_error()); 
  if(mysql_num_rows($collegeName)) {
      $sol=mysql_fetch_array($collegeName);
      $ret['PHONE']=$sol[0];
  } 
  return array_merge($ret,$arr);
}


function getDisclaimer($team,$mcid) {
  $team = escape($team);
  $mcid = escape($mcid);
  $getQuery="SELECT * FROM `prhospi_disclaimer` WHERE `page_modulecomponentid`={$mcid} AND `disclaimer_team`='{$team}'"; 
  $runQuery=mysql_query($getQuery) or displayerror(mysql_error());
  if(!mysql_num_rows($runQuery)) {
    
    $insertQuery=mysql_query("INSERT INTO `prhospi_disclaimer` VALUES({$mcid},'{$team}','',0)") or displayerror(mysql_error());
    if($insertQuery!="") displayinfo("Field {$team} Inserted TO Table");
    return "";
  }
  $res=mysql_fetch_assoc($runQuery);
  
  return $res['disclaimer_desc'];
}

function printDisclaimer($mcid,$data,$team){
  $updateQuery = "UPDATE `prhospi_accomodation_status` 
                  SET `hospi_printed` = 1 
                  WHERE `page_modulecomponentid`={$mcid} AND `user_id`={$data}";
  
   $updateRes = mysql_query($updateQuery) or displayerror(mysql_error());               

  global $sourceFolder,$moduleFolder;
  require_once($sourceFolder."/".$moduleFolder."/qaos1/excel.php");
  $displayFn = assignVars($data,$mcid);
  $content = getDisclaimer($team,$mcid);  
  //escape quotes
  $content = str_replace ("'", "\'", $content); 
  //replace the vars in file content with those defined
  $content = preg_replace('#\{([a-z0-9\-_]*?)\}#is', "' . ((isset(\$displayFn['\\1'])) ? \$displayFn['\\1'] : '') . '", $content);
  //Make the content parseable
  eval("\$content = '$content';");
  //  displayinfo($content);
  //get parser done
  
  //  displayinfo($content);
  return printContent($content);

}

function getAmount($team,$mcid) {
  $getQuery="SELECT * FROM `prhospi_disclaimer` WHERE `page_modulecomponentid`={$mcid} AND `disclaimer_team`='{$team}'"; 
  $runQuery=mysql_query($getQuery) or displayerror(mysql_error());
  if(!mysql_num_rows($runQuery)) {
    $insertQuery=mysql_query("INSERT INTO `prhospi_disclaimer` VALUES({$mcid},'{$team}','',0)") or displayerror(mysql_error());
    if($insertQuery!="") displayinfo("Field {$team} Inserted TO Table");
    return 0;
  }
  $res=mysql_fetch_assoc($runQuery);
  return $res['team_cost'];
}

function getRoomNoFromRoomId($roomId,$mcid) {
  $roomQuery = "SELECT `hospi_room_no` FROM `prhospi_hostel` WHERE `hospi_room_id`={$roomId} AND `page_modulecomponentid`={$mcid}";
  $result = mysql_query($roomQuery) or displayerror(mysql_error());
  $res = mysql_fetch_array($result);
  return $res[0];
}
function getDescription($team,$mcid) {

  $getQuery="SELECT * FROM `prhospi_disclaimer` WHERE `page_modulecomponentid`={$mcid} AND `disclaimer_team`='{$team}'"; 
  $runQuery=mysql_query($getQuery) or displayerror(mysql_error());
  if(!mysql_num_rows($runQuery)) {
    $insertQuery=mysql_query("INSERT INTO `prhospi_disclaimer` VALUES({$mcid},'{$team}','',0)") or displayerror(mysql_error());
    if($insertQuery!="") displayinfo("Field {$team} Inserted TO Table");
    return "";
  }
  $res=mysql_fetch_assoc($runQuery);
  
  return $res['disclaimer_desc'];
}

function getSuggestionsForIdOrEmail($input) {
    $input=trim($input);
    $emailQuery ="SELECT * FROM `".MYSQL_DATABASE_PREFIX."users` WHERE `user_id` LIKE '%$input%' OR `user_email` LIKE '%$input%'";
    $emailResult = mysql_query($emailQuery);
    $suggestions = array($input);
    while($emailRow = mysql_fetch_row($emailResult)) {
      $suggestions[] = $emailRow[2]. ' - ' . $emailRow[0];
    }                                                                                                                                           
    return join($suggestions, ',');                                                                                                             
}
  
function getHostelsName($mcid) {
   $hostelNameQuery="SELECT DISTINCT `hospi_hostel_name` FROM `prhospi_hostel` WHERE `page_modulecomponentid`={$mcid}";
   $hostelNameRes=mysql_query($hostelNameQuery) or displayerror(mysql_error());
   $ret="<option value=''>SELECT HOSTEL</option>";
   while($result = mysql_fetch_array($hostelNameRes)) {
     $temp=str_replace(" ","_",$result[0]);
    $ret.=<<<OPTIONS
      <option value="{$temp}">$result[0]</option>
OPTIONS;
   }
   return $ret;

}
function getAvailableRooms($mcid) {
     $roomQuery="SELECT `hospi_room_id`,`hospi_hostel_name`,`hospi_room_no`,`hospi_room_capacity` 
                FROM `prhospi_hostel` 
                WHERE `page_modulecomponentid`={$mcid} AND `hospi_blocked`=0 ORDER BY `hospi_hostel_name`,`hospi_room_no` ASC";
    $roomRes=mysql_query($roomQuery) or displayerror(mysql_error());
    $ret="<option value=''>SELECT ROOM NO</option>";
    while($res=mysql_fetch_array($roomRes)) {
       $availableQuery = "SELECT * FROM `prhospi_accomodation_status` 
                          WHERE `hospi_room_id`={$res[0]} AND `page_modulecomponentid`={$mcid}
                          AND hospi_actual_checkout IS NULL";
       $availableRes = mysql_query($availableQuery) or displayerror(mysql_error());
       $availableNo=$res[3]-mysql_num_rows($availableRes);
       
       if(!$availableNo) continue;
       $tempClass=str_replace(" ","_","roomNo_".$res[1]);
       $ret.=<<<OPTIONS
             <option class="$tempClass" style="display:none;height:10px;" value="$res[0]">Room No:$res[2],Vacant:$availableNo / $res[3] </option>                     
OPTIONS;
    }    
    return $ret;
}
function getUserDetailsForHospi($userId,$mcid) {
  if(!isRegisteredToPr($userId,$mcid)) {
    displayerror("User is not register to PR.");
    //   return "";
  }
  if(!is_numeric($userId)|| $userId=="") {
    displayerror("Invalid format.<br/>Format should be 'userid'");
    return "";
  }
  $checkUserExist=mysql_query("SELECT * FROM ".MYSQL_DATABASE_PREFIX."users WHERE `user_id`={$userId}") or displayerror(mysql_error());
  if(!mysql_num_rows($checkUserExist)) {
    displayerror("Invalid User Id");
    return "";
  }
  $isRegisteredQuery = "SELECT * FROM prhospi_admin WHERE `page_modulecomponentid` = {$mcid}";
  $isRegisteredRes = mysql_query($isRegisteredQuery) or displayerror(mysql_error());
  if(!mysql_num_rows($isRegisteredRes)) {
    displayerror("Please Contact CSG for activating the accommodation form");
    return "";
  }  
  $detailQuery = "SELECT hospi.page_getform_modulecomponentid AS mcid,formname.form_elementdisplaytext AS dispText,
                         form.form_elementdata AS dispData,formname.form_elementisrequired AS required 
                  FROM `prhospi_admin` AS hospi 
                  LEFT JOIN `form_elementdata` AS form 
                  ON hospi.page_getform_modulecomponentid = form.page_modulecomponentid AND 
                  form.user_id={$userId} AND hospi.details_to_display = form.form_elementid
                  LEFT JOIN `form_elementdesc` AS formname 
                  ON formname.page_modulecomponentid=hospi.page_getform_modulecomponentid AND formname.form_elementid=hospi.details_to_display 
                  WHERE hospi.page_modulecomponentid = {$mcid} AND hospi.detail_required=1";
  $displayResult=mysql_query($detailQuery) or displayerror(mysql_error());
  $checkRequired=0;
  $userDetails = <<<TABLE
    <table id="tableUserDetailsHospi" border="1">
       <tr>
         <th>Profile/Accommodation</th> 
         <th>Field</th>
         <th>User Details</th>
         <th>Days of stay</th>
       </tr>

TABLE;
  while($result=mysql_fetch_array($displayResult)) {
    $type=($result[0]==0)?"Profile":"Accomadation";
    if($result[2] == "") {
      if(!$result[3]) continue;
      else {
	$checkRequired=($result[0]==0)?(($checkRequired == 2)?3:1):(($checkRequired == 1)?3:2);
	continue;
      }
    }
    $userDetails.=<<<TABLEROW
        <tr>
         <td>{$type}</td>
         <td>$result[1] </td>
         <td>$result[2]</td>
	 <td></td>		
        </tr>
TABLEROW;
  }
  
  if($checkRequired == 3) {
        displayerror("Some fields in profile and accommodation form are not filled");
	return "";
  }
  else if($checkRequired == 2) {
    displayerror("Some fields in accommodation form are not filled");
    return "";
  }
  else if($checkRequired == 1) {
    displayerror("Some fields in profile form are not filled");
        return "";
  }
  $amountToCollect=getAmount("hospihead",$mcid);
  $availableHostels=getHostelsName($mcid);
  $availableRoomNo=getAvailableRooms($mcid);
  $userDetails.=<<<TABLEAMOUNT
  <select style="display:none" id="room_fetchData"> $availableRoomNo</select>
<!--  <form method="post" action="./+view&subaction=accommodation&userId={$userId}">-->
  <tr>    
    <td>Pragyan User Id</td> 
     <td colspan="1"><input type="text" id="userid1" name="userid1" value="{$userId}" id="userid1"/></td>
     <td>
        <select class="hostelNames" id="hostelname1" name="hostelname1">$availableHostels</select>
        <select class="roomNo" id="roomNo1" name="roomNo1"></select>
     </td>
     <td>
       <input type="text" id="stay1" name="stay1"/>
       <input type="button" id="submit1" value="Submit" onclick="submitUserDataToAcco(1,{$userId})"/>
       <input type="button" id="edit1" class="editDate" value="edit data" style="display:none;" onclick="editField(1,{$userId})"/>
       <input type="button" id="update1"  value="Update" style="display:none;" onclick="updateData(1,{$userId})"/>
     </td>
   </tr>
   <tr>    
    <td>Pragyan User Id</td> 
    <td colspan="1"><input type="text" name="userid2" id="userid2"/></td>
    <td>
       <select class="hostelNames" id="hostelname2" name="hostelname2">$availableHostels</select>
       <select class="roomNo" id="roomNo2" name="roomNo2"></select>
    </td>
     <td>
       <input type="text" id="stay2" name="stay2"/>
       <input type="button" id="submit2" value="Submit" onclick="submitUserDataToAcco(2,{$userId})"/>      
       <input type="button" id="edit2" class="editDate" value="edit data" style="display:none;" onclick="editField(2,{$userId})"/>
       <input type="button" id="update2"  value="Update" style="display:none;" onclick="updateData(2,{$userId})"/>
     </td>
  </tr>
  <tr>    
    <td>Pragyan User Id</td> 
     <td colspan="1"><input type="text" name="userid3" id="userid3"/></td>
    <td>
       <select class="hostelNames" id="hostelname3" name="hostelname3">$availableHostels</select>
       <select class="roomNo" id="roomNo3" name="roomNo3"></select>
    </td>
     <td>
       <input type="text" id="stay3" name="stay3"/>
       <input type="button" id="submit3" value="Submit" onclick="submitUserDataToAcco(3,{$userId})"/>     
       <input type="button" id="edit3" class="editDate" value="edit data" style="display:none;" onclick="editField(3,{$userId})"/>
       <input type="button" id="update3"  value="Update" style="display:none;" onclick="updateData(3,{$userId})"/>
</td>
  </tr>
  <tr>    
    <td>Pragyan User Id</td> 
     <td colspan="1"><input type="text" name="userid4" id="userid4"/></td>
    <td>
        <select class="hostelNames" id="hostelname4" name="hostelname4">$availableHostels</select>
        <select class="roomNo" id="roomNo4" name="roomNo4"></select>
    </td> 
     <td>
       <input type="text" id="stay4" name="stay4"/>
       <input type="button" id="submit4" value="Submit" onclick="submitUserDataToAcco(4,{$userId})"/>     
       <input type="button" id="edit4" class="editDate" value="edit data" style="display:none;" onclick="editField(4,{$userId})"/>
       <input type="button" id="update4"  value="Update" style="display:none;" onclick="updateData(4,{$userId})"/>
</td>  </tr>
  <tr>    
    <td>Pragyan User Id</td> 
     <td colspan="1"><input type="text" name="userid5" id="userid5"/></td>
    <td>
       <select class="hostelNames" id="hostelname5" name="hostelname5">$availableHostels</select>
       <select class="roomNo" id="roomNo5" name="roomNo5"></select>

    </td>
     <td>
       <input type="text" id="stay5" name="stay5"/>
       <input type="button" id="submit5" value="Submit" onclick="submitUserDataToAcco(5,{$userId})"/>    
       <input type="button" id="edit5" class="editDate" value="edit data" style="display:none;" onclick="editField(5,{$userId})"/>
       <input type="button" id="update5"  value="Update" style="display:none;" onclick="updateData(5,{$userId})"/>
    </td>
  </tr>
  <tr>
       <td colspan="3">Collected Rs {$amountToCollect} From {$userId}</td>
  </tr>
<!--  <tr>
      <td colspan="3">
            <input type="submit" name="continueToAcco" value="continue" style="width:100%;height:25px;cursor:pointer"/> 
      </td>
    </tr>
   </form>-->
   <script type="text/javascript">
    $(".hostelNames").change(function(){
      var root=this;
      value=this.value;
      $(".roomNo",root.parentNode).html('');
      var room=document.getElementById("room_fetchData");
      arr=$(".roomNo_"+value,room);
      st="";
      $.each(arr,function() {
        this.style.display="";
        $(".roomNo",root.parentNode).append(this);
      });
        var content=$(".roomNo",root.parentNode).html();
        $(".roomNo_"+value,$(".roomNo",root.parentNode)).css({'display':''});   
	//	console.log(content);
	$(room).append(content);
    });
   </script>   



TABLEAMOUNT;
  return $userDetails."</table>";
}


function displayAccommodationForm($userId,$mcid,$registeredBy) {
  if(!isset($_POST['continueToAcco'])||!(isset($_GET['userId']))) {
    return getUserDetailsForHospi(escape($_GET['userId']),$mcid);
  }
  if($_GET['userId'] == 0 || !(is_numeric($_GET['userId']))) return getUserDetailsForHospi(escape($_GET['userId']),$mcid);
  global $urlRequestRoot,$sourceFolder,$templateFolder,$cmsFolder,$moduleFolder;
  require_once("$sourceFolder/$moduleFolder/prhospi/accommodation.php");
  $tableForDisclaimer = <<<TABLE
    <table border="1">
      <tr>
        <th>Pragyan Id</th>
        <th>Name</th>
        <th>Email</th>
        <th>Hostel</th>
        <th>Room No</th>
        <th>DISCLAIMER</th>
      </tr>
TABLE;
  for($i=1;$i<=5;$i++) {
    if(!isset($_POST['userid'.$i])||$_POST['userid'.$i]=="") continue;

    if((!isset($_POST['hostelname'.$i]))||($_POST['hostelname'.$i]=="")) {
      displayinfo("Please Select the hostel name for Pragyan Id:".$_POST['userid'.$i]);
      continue;
    }
    if(!isset($_POST['roomNo'.$i])||($_POST['roomNo'.$i]=="")) {
      displayinfo("Please Select the Room for Pragyan Id:".$_POST['userid'.$i]);
      continue;
    }
    $roomNo = escape($_POST['roomNo'.$i]);
    $hostelName = escape($_POST['hostelname'.$i]);
    $uid = escape($_POST['userid'.$i]);
    if(!is_numeric($uid)) {
      displayinfo("Not a valid Pragyan Id:".$uid);
      continue;
     }
    if(!isRoomAvailable($roomNo,$mcid)) {
      displayinfo("Room Not Available for Pragyan Id : ".$uid.".Please try again ");
      continue;
    }
    if(!addUserToRoom($uid,$roomNo,$mcid,escape($_GET['userId']),$registeredBy)) continue;
    $name = getUserName($uid);
    $email = getUserEmail($uid);
    $room = getRoomNoFromRoomId($roomNo,$mcid);
    $tableForDisclaimer.=<<<TROW
      <tr>
        <td>$uid</td>
        <td>$name</td>
        <td>$email</td>
        <td>$hostelName</td>
        <td>$room</td>
        <td>
          <form method="POST" target="_blank" action="./+view">
           <input type="submit" name="printthis" value="PRINT"/>
           <input type="hidden" name="printHiddenId" value="printHostelAllotmentBill{$uid}" />
          </form>
        </td>
      </tr>
TROW;
  }
  return $tableForDisclaimer."</table>".displayRooms($mcid,escape($_GET['userId']));
}

function ajaxSuggestions($mcid,$type=0,$registeredBy=0) {
  // 0 is for insert . and 1 for update
  $content="";
  if(!isset($_POST['userid'])||$_POST['userid']=="") {
    $content="UserId Can't be Null";
    return $content;
  }
    if((!isset($_POST['hostelname']))||($_POST['hostelname']=="")) {
      $content.= "Please Select the hostel name for Pragyan Id:".$_POST['userid'];
      return $content;
    }
    if(!isset($_POST['roomNo'])||($_POST['roomNo']=="")) {
      $content.="Please Select the Room for Pragyan Id:".$_POST['userid'];
      return $content;
    }
    if(!isset($_POST['stay'])) {
      return "No of days of stay should be defined";
    }
    $roomNo = escape($_POST['roomNo']);
    $hostelName = escape($_POST['hostelname']);
    $uid = escape($_POST['userid']);
    $stay = escape($_POST['stay']);
    if(!is_numeric($uid)) {
      $content="Not a valid Pragyan Id:".$uid;
      return $content;
     }
    if(!is_numeric($stay)) {
      return "No of days of stay should be a number";
    }
    if($stay<=0) return "No of days should be atleast 1";
    if(!isRoomAvailable($roomNo,$mcid)) {
      $content="Room Not Available for Pragyan Id : ".$uid.".Please try again ";
      return $content;
    }
 
    if($type==0)  return addUserToRoomAjax($uid,$roomNo,$mcid,escape($_POST['user_reg_value']),$stay,$registeredBy);
    else          return updateUserToRoomAjax($uid,$roomNo,$mcid,escape($_POST['user_reg_value']),$stay);
      /**code doesn't continue from this part"; */



    $name = getUserName($uid);
    $email = getUserEmail($uid);
    $room = getRoomNoFromRoomId($roomNo,$mcid);
       
    $tableForDisclaimer.=<<<TROW
      <tr>
        <td>$uid</td>
        <td>$name</td>
        <td>$email</td>
        <td>$hostelName</td>
        <td>$room</td>
        <td>
          <form method="POST" target="_blank" action="./+view">
           <input type="submit" name="printthis" value="PRINT"/>
           <input type="hidden" name="printHiddenId" value="printHostelAllotmentBill{$uid}" />
          </form>
        </td>
      </tr>
TROW;
}



function deleteAccomodatedUser($userId,$mcid) {
  $deleteAccoUserQuery="DELETE FROM `prhospi_accomodation_status`
                  WHERE `user_id`={$userId} AND `page_modulecomponentid`={$mcid}";
  $deleteAccoUserRes=mysql_query($deleteAccoUserQuery) or displayerror(mysql_error());
  if($deleteAccoUserRes) 
    displayinfo("Successfully deleted Pragyan User: ".$userId);
  
}

function deletePrUser($userId,$mcid) {
  $deleteAccoUserQuery="DELETE FROM `prhospi_pr_status`
                  WHERE `user_id`={$userId} AND `page_modulecomponentid`={$mcid}";
  $deleteAccoUserRes=mysql_query($deleteAccoUserQuery) or displayerror(mysql_error());
  if($deleteAccoUserRes) 
    displayinfo("Successfully deleted Pragyan User: ".$userId);
  
}


function downloadSampleFormatForRoomUpload() {
  global $sourceFolder,$moduleFolder;
  require_once($sourceFolder."/".$moduleFolder."/qaos1/excel.php");
  $table=<<<TABLE
    <table>
      <thead>
       <td><b>Hostel Name</b></td>
       <td><b>Room Number Starting From</b></td>
       <td><b>Room Number Ending Till</b></td>
       <td><b>Room Capacity</b></td>
       <td><b>Floor(Start Adding From row 2)</b></td>
      </thead>
    </table>


TABLE;
  displayExcelForTable($table);
}

function submitDetailsForPr($userId,$mcid,$registeredBy) {
  if(!is_numeric($userId)|| $userId=="") {
    displayerror("Invalid format.<br/>Format should be 'userid'");
    return '';
  }
  $checkUserExist=mysql_query("SELECT * FROM ".MYSQL_DATABASE_PREFIX."users WHERE `user_id`={$userId}") or displayerror(mysql_error());
  if(!mysql_num_rows($checkUserExist)) {
    displayerror("Invalid User Id");
    return '';
  }

  $profileDetailQuery = "SELECT descr.form_elementdisplaytext AS dispText,
                         data.form_elementdata AS dispData,descr.form_elementisrequired AS required
                         FROM form_elementdesc AS descr
                         LEFT JOIN `form_elementdata` AS data
                         ON descr.form_elementid = data.form_elementid AND
                            descr.page_modulecomponentid = data.page_modulecomponentid AND
                            data.user_id = $userId
                         WHERE descr.page_modulecomponentid = 0
                         ";
  $profileDetailRes = mysql_query($profileDetailQuery) or displayerror(mysql_error());
  if(!$profileDetailRes) {
    displayerror("check the error in getProfileDetailsForPr function in prhospi_common.php");
    return '';
  }
  $checkRequired=0;
  $userDetails = <<<TABLE
    <table id="tableUserDetailsHospi" border="1">
       <tr>
         <th>Pr Id</th> 
         <th>Field</th>
         <th>User Details</th>
       </tr>

TABLE;
  while($result=mysql_fetch_assoc($profileDetailRes)) {
	$dispText = $result['dispText'];
	$dispData = $result['dispData'];
 
	$userDetails .=<<<TR
	  <tr>
	    <td>$userId</td>
	    <td>{$dispText}</td>
	    <td>{$dispData}</td>
	 </tr>		
TR;
  }
  $amtToBeCollected = getAmount("prhead",$mcid); 
  $userName = getUserName($userId);
  $userDetails .=<<<TRAMOUNT
	<tr >
	  <td colspan="3" style="text-align:center">	    
		Collected Rs $amtToBeCollected from $userName
	  </td>	
	</tr>
     </table>

TRAMOUNT;
  return $userDetails.checkInPrUser($userId,$mcid,$registeredBy);	
}

function isRegisteredToPr($userId,$mcId) {
  $userId = escape($userId);
  if(!is_numeric($userId)) return 0;
  $checkUserRegisteredToPr = "SELECT * FROM `prhospi_pr_status` WHERE `page_modulecomponentid` = {$mcId} AND `user_id` = {$userId}";
  $checkUserRegisteredToPrQuery = mysql_query($checkUserRegisteredToPr) or displayerror(mysql_error());
  if(mysql_num_rows($checkUserRegisteredToPrQuery)) return 1;
  return 0;
}

function checkInPrUser($userId,$mcId,$registeredBy) {
  $userId = escape($userId);
  if(!is_numeric($userId)) return 0;
  if(isRegisteredToPr($userId,$mcId)) {
    displaywarning("User already registered to Pr.");
    return ;
  }
  $time = date("Y-m-d H:i:s");
  $amtToBeCollected = getAmount("prhead",$mcId); 
  $addUserToPrReg = "INSERT INTO `prhospi_pr_status` VALUES ({$mcId},{$userId},'{$time}','0000-00-00 00:00:00',{$amtToBeCollected},0,'{$registeredBy}')";
  $addUserToPrRegQuery = mysql_query($addUserToPrReg) or displayerror(mysql_error());
  if(mysql_affected_rows()>0) {
    displayinfo("Sucessfully Registered for Pragyan");
    return displayPrintOptionForPR($userId);
  }
  else displaywarning("User has not registered to pragyan site");
  return "";
}


function checkOutPr($userId,$refund,$mcid) {
 if((!is_numeric($userId))||$userId=="") {
    displaywarning("Invalid UserId");
    return "";
  }
   $time = date("Y-m-d H:i:s");
  if($refund <=0 ) {
    displayerror("Refund not provided.checkOut after receiving Refund amount");
  }
  $checkIfUserEnrolledQuery = "SELECT * FROM `prhospi_pr_status` 
                            WHERE `user_id`={$userId} AND `page_modulecomponentid`={$mcid}";
  $checkIfUserEnrolledRes = mysql_query($checkIfUserEnrolledQuery) or displayerror(mysql_error());
 if(!mysql_num_rows($checkIfUserEnrolledRes)) {
    displaywarning("User ".getUserName($userId).", not registered for pr or for pragyan site ");
    return false;
    }
  $checkIfUserCheckedOutQuery = "SELECT * FROM `prhospi_pr_status` 
                            WHERE `user_id`={$userId} AND `page_modulecomponentid`={$mcid} AND `hospi_checkpout_time`!='0000-00-00 00:00:00'";
  $checkIfUserEnrolledRes = mysql_query($checkIfUserCheckedOutQuery) or displayerror(mysql_error());
  if(mysql_num_rows($checkIfUserEnrolledRes)) {
    displaywarning("User ".getUserName($userId).", has already Checkedout ");
    return false;
    }
  
 $updateQuery = "UPDATE `prhospi_pr_status` 
                  SET `hospi_checkpout_time` = '{$time}',`amount_refunded`={$refund} 
                  WHERE `page_modulecomponentid`={$mcid} AND `user_id`={$userId}";
  
  $updateRes = mysql_query($updateQuery) or displayerror(mysql_error());               
  displayinfo("User Successfully checked out");
}

function displayPrintOptionForPR($userId) {
  $name = getUserName($userId);
  $email = getUserEmail($userId);
  $tableForDisclaimer = <<<TABLE
    <table border="1">
      <tr>
        <th>Pragyan Id</th>
        <th>Name</th>
        <th>Email</th>
        <th>DISCLAIMER</th>
      </tr>
      <tr>
        <td>$userId</td>
        <td>$name</td>
        <td>$email</td>
        <td>
          <form method="POST" target="_blank" action="./+prview">
           <input type="submit" name="printthis" value="PRINT"/>
           <input type="hidden" name="printHiddenId" value="printHostelAllotmentBill{$userId}" />
          </form>

        </td>
       </tr>
      </table>
TABLE;
  return $tableForDisclaimer;
}

function displayUsersRegisteredToPr($mcId) {
  global $STARTSCRIPTS;
  $smarttablestuff = smarttable::render(array('table_prusers'),null);                                                
  $STARTSCRIPTS .="initSmartTable();";                                                                                                 
  $userDetails =<<<TABLE
    $smarttablestuff
    <table class="display" id="table_prusers" width="100%" border="1">
      <thead>
        <tr>
          <th>Pragyan Id</th>
          <th>Name</th>
          <th>E mail</th>
          <th>Phone Number</th>
          <th>Check In Time</th>
          <th>Check Out Time</th>
          <th>Amount Received</th>
          <th>Amount Refunded</th>
          <th>Registered By</th>
          <th>Registered By Email</th>
          <th>Disclaimer</th>
          
        </tr>
    </thead>
TABLE;
  $getRegisteredUserDetailPRQuery = "SELECT * FROM `prhospi_pr_status` WHERE `page_modulecomponentid`={$mcId}";
  $getRegisteredUserPR = mysql_query($getRegisteredUserDetailPRQuery) or displayerror("Error on viewing registered user".mysql_error());
  while($res = mysql_fetch_assoc($getRegisteredUserPR)) {
    $registeredByName = getUserName($res['user_registered_by']);
    $registeredByEmail = getUserEmail($res['user_registered_by']);
    $name = getUserName($res['user_id']);
    $id=$res['user_id'];
    $email = getUserEmail($res['user_id']);
    $getPhoneNumberQuery="SELECT * FROM `form_elementdata` WHERE `user_id`='{$id}' AND `page_modulecomponentid`=0 AND `form_elementid`=5";
    $getPhoneNumberQuery=mysql_query($getPhoneNumberQuery);
    $getPhoneNumberQueryResult=mysql_fetch_assoc($getPhoneNumberQuery);
    $phoneNumber=$getPhoneNumberQueryResult['form_elementdata'];
    $userDetails .=<<<TR
      <tr>
       <td>{$res['user_id']}</td>
       <td>$name</td>
       <td>$email</td>
       <td>$phoneNumber</td>
       <td>{$res['hospi_checkin_time']}</td>
       <td>{$res['hospi_checkpout_time']}</td>
       <td>{$res['amount_recieved']}</td>
       <td>{$res['amount_refunded']}</td>
       <td>$registeredByName</td>
       <td>$registeredByEmail</td>
       <td>
          <form method="POST" target="_blank" action="./+prview">
           <input type="submit" name="printthis" value="PRINT"/>
           <input type="hidden" name="printHiddenId" value="printHostelAllotmentBill{$res['user_id']}" />
          </form>
          
       </td>
      </tr>
TR;
  }
  $userDetails .=<<<TABLEEND
    </table>
TABLEEND;
  return $userDetails;
}


function displayUsersRegisteredToPrDelete($mcId) {
  global $STARTSCRIPTS;
  $smarttablestuff = smarttable::render(array('table_prusers'),null);                                                
  $STARTSCRIPTS .="initSmartTable();";                                                                                                 
  $userDetails =<<<TABLE
    $smarttablestuff
    <table class="display" id="table_prusers" width="100%" border="1">
      <thead>
        <tr>
          <th>Delete</th>
          <th>Pragyan Id</th>
          <th>Name</th>
          <th>E mail</th>
          <th>Check In Time</th>
          <th>Check Out Time</th>
          <th>Amount Refunded</th>
          <th>Disclaimer</th>
          
        </tr>
    </thead>
TABLE;
  $getRegisteredUserDetailPRQuery = "SELECT * FROM `prhospi_pr_status` WHERE `page_modulecomponentid`={$mcId}";
  $getRegisteredUserPR = mysql_query($getRegisteredUserDetailPRQuery) or displayerror("Error on viewing registered user".mysql_error());
  while($res = mysql_fetch_assoc($getRegisteredUserPR)) {
    $name = getUserName($res['user_id']);
    $email = getUserEmail($res['user_id']);
    $userDetails .=<<<TR
      <tr>
       <td>
          <form method="POST" action="./+prhead&subaction=deleteUsers">
           <input type="submit" name="" value="DELETE"/>
           <input type="hidden" name="txtFormUserId" value="$email - {$res['user_id']}" />
          </form>
       </td>
       <td>{$res['user_id']}</td>
       <td>$name</td>
       <td>$email</td>
       <td>{$res['hospi_checkin_time']}</td>
       <td>{$res['hospi_checkpout_time']}</td>
       <td>{$res['amount_refunded']}</td>
       <td>
          <form method="POST" target="_blank" action="./+prview">
           <input type="submit" name="printthis" value="PRINT"/>
           <input type="hidden" name="printHiddenId" value="printHostelAllotmentBill{$res['user_id']}" />
          </form>
          
       </td>
      </tr>
TR;
  }
  $userDetails .=<<<TABLEEND
    </table>
TABLEEND;
  return $userDetails;
}


function displayUsersRegisteredToAcco($mcId) {
  global $STARTSCRIPTS;
  $smarttablestuff = smarttable::render(array('table_accousers'),null);                                                
  $STARTSCRIPTS .="initSmartTable();";                                                                                                 
  $userDetails =<<<TABLE
    $smarttablestuff
    <table class="display" id="table_accousers" width="100%" border="1">
      <thead>
        <tr>
          <th>Pragyan Id</th>
          <th>Name</th>
          <th>E mail</th>
          <th>Check In Time</th>
          <th>Check Out Time</th>
          <th>Amount Received</th> 
          <th>Amount Refunded</th>
          <th>Hostel</th>
          <th>Room No</th>
          <th>Registered By</th>
          <th>Registered By Email</th>
          <th>Disclaimer</th>
        </tr>
    </thead>
TABLE;
  $getRegisteredUserDetailAccoQuery = "SELECT * FROM `prhospi_accomodation_status` AS status
                                       LEFT JOIN `prhospi_hostel` AS hostel ON hostel.hospi_room_id = status.hospi_room_id AND
                                                                               hostel.page_modulecomponentid=status.page_modulecomponentid  
                                       WHERE status.page_modulecomponentid={$mcId}";
  $getRegisteredUserPR = mysql_query($getRegisteredUserDetailAccoQuery) or displayerror("Error on viewing registered user".mysql_error());
  while($res = mysql_fetch_assoc($getRegisteredUserPR)) {
    $registeredByName = getUserName($res['user_registered_by']);
    $registeredByEmail = getUserEmail($res['user_registered_by']);
    $name = getUserName($res['user_id']);
    $email = getUserEmail($res['user_id']);
    $printSt = $res['hospi_printed'];
    $val = "";
    if($printSt == 1) {
      $val = 'value="PRINTED" ';
    }
    else {
      $val = 'value="PRINT" ';
   
    }
    
    $userDetails .=<<<TR
      <tr>
       <td>{$res['user_id']}</td>
       <td>$name</td>
       <td>$email</td>
       <td>{$res['hospi_actual_checkin']}</td>
       <td>{$res['hospi_actual_checkout']}</td>
       <td>{$res['hospi_cash_recieved']}</td>   					      
       <td>{$res['hospi_cash_refunded']}</td>
       <td>{$res['hospi_hostel_name']}</td>
       <td>{$res['hospi_room_no']}</td>
       <td>$registeredByName</td>
       <td>$registeredByEmail</td>
       <td>
          <form method="POST" target="_blank" action="./+view">
           <input type="submit" name="printthis" {$val}/>
           <input type="hidden" name="printHiddenId" value="printHostelAllotmentBill{$res['user_id']}" />
          </form>
       </td>
      </tr>
TR;
  }
  $userDetails .=<<<TABLEEND
    </table>
TABLEEND;
  return $userDetails;
}




function displayUsersRegisteredToAccoWithDelete($mcId) {
  global $STARTSCRIPTS;
  $smarttablestuff = smarttable::render(array('table_accousers'),null);                                                
  $STARTSCRIPTS .="initSmartTable();";                                                                                                 
  $userDetails =<<<TABLE
    $smarttablestuff
    <table class="display" id="table_accousers" width="100%" border="1">
      <thead>
        <tr>
          <th>Delete</th>
          <th>Pragyan Id</th>
          <th>Name</th>
          <th>E mail</th>
          <th>Check In Time</th>
          <th>Check Out Time</th>
          <th>Amount Refunded</th>
          <th>Hostel</th>
          <th>Room No</th>
          <th>Disclaimer</th>
        </tr>
    </thead>
TABLE;
  $getRegisteredUserDetailAccoQuery = "SELECT * FROM `prhospi_accomodation_status` AS status
                                       LEFT JOIN `prhospi_hostel` AS hostel ON hostel.hospi_room_id = status.hospi_room_id AND
                                                                               hostel.page_modulecomponentid=status.page_modulecomponentid  
                                       WHERE status.page_modulecomponentid={$mcId}";
  $getRegisteredUserPR = mysql_query($getRegisteredUserDetailAccoQuery) or displayerror("Error on viewing registered user".mysql_error());
  while($res = mysql_fetch_assoc($getRegisteredUserPR)) {
    
    $name = getUserName($res['user_id']);
    $email = getUserEmail($res['user_id']);
    $userDetails .=<<<TR
      <tr>
        <td>
          <form method="POST"  action="./+hospihead&subaction=deleteUsers">
           <input type="submit" name="" value="DELETE"/>
           <input type="hidden" name="txtFormUserId" value="$email - {$res['user_id']}" />
          </form>
        </td>
        
       <td>{$res['user_id']}</td>
       <td>$name</td>
       <td>$email</td>
       <td>{$res['hospi_actual_checkin']}</td>
       <td>{$res['hospi_actual_checkout']}</td>
       <td>{$res['hospi_cash_refunded']}</td>
       <td>{$res['hospi_hostel_name']}</td>
       <td>{$res['hospi_room_no']}</td>
       <td>
          <form method="POST" target="_blank" action="./+hospihead">
           <input type="submit" name="printthis" value="PRINT"/>
           <input type="hidden" name="printHiddenId" value="printHostelAllotmentBill{$res['user_id']}" />
          </form>
          
       </td>
      </tr>
TR;
  }
  $userDetails .=<<<TABLEEND
    </table>
TABLEEND;
  return $userDetails;
}
function blockRoom($mcid) {
  global $sourceFolder,$moduleFolder;
  if(isset($_POST['roomId'])&&isset($_POST['block'])) {
    if($_POST['block']=='BLOCK') blockRoomNo(substr($_POST['roomId'],9),$mcid);
    if($_POST['block']=='UNBLOCK') unBlockRoomNo(substr($_POST['roomId'],9),$mcid);
  }
  $getAvailableRoomQuery = "SELECT * FROM `prhospi_hostel` WHERE `hospi_blocked`=0 AND `page_modulecomponentid`={$mcid}";
  $getAvailableRoomQueryRes = mysql_query($getAvailableRoomQuery) or displayerror(mysql_error());
  require_once("$sourceFolder/$moduleFolder/prhospi/accommodation.php");
  $roomDetails = displayRooms($mcid);
  $blockRoomForm=<<<FORM
<h1>Available Room</h1>
$roomDetails
<hr/>
<h1> Block Room</h1>
     <form action="./+hospihead&subaction=blockRooms" method="post">
        <select id="blockRoomNo" name="roomAllotted">
        <option class="blockRoom" id="">Select Room</option>
FORM;
  while($details = mysql_fetch_assoc($getAvailableRoomQueryRes)) {
  $blockRoomForm.=<<<FORM
    <option class="blockRoom" id="blockRoom{$details['hospi_room_id']}">{$details['hospi_hostel_name']} RoomNo:{$details['hospi_room_no']}</option>
FORM;
  }
  $blockRoomForm.=<<<FORM
         </select>
        <input type="hidden" id="roomId" name="roomId" />
        <input type="submit" name="block" value="BLOCK"/>
    </form>
    <script type="text/javascript">
    $('#blockRoomNo').change(function(){
	roomIdValue=$('.blockRoom:selected').attr('id');
	$('#roomId').val(roomIdValue);
      });
    </script>
FORM;
  ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  $getAvailableRoomQuery = "SELECT * FROM `prhospi_hostel` WHERE `hospi_blocked`=1 AND `page_modulecomponentid`={$mcid}";
  $getAvailableRoomQueryRes = mysql_query($getAvailableRoomQuery) or displayerror(mysql_error());
  $blockRoomForm.=<<<FORM
<hr/>
<h1> UnBlock Room</h1>
     <form action="./+hospihead&subaction=blockRooms" method="post">
        <select id="unblockRoomNo" name="roomAllotted">
        <option class="unblockRoom" id="">Select Room</option>
FORM;
  while($details = mysql_fetch_assoc($getAvailableRoomQueryRes)) {
  $blockRoomForm.=<<<FORM
    <option class="unblockRoom" id="blockRoom{$details['hospi_room_id']}">{$details['hospi_hostel_name']} RoomNo:{$details['hospi_room_no']}</option>
FORM;
  }
  $blockRoomForm.=<<<FORM
         </select>
        <input type="hidden" id="unblockroomId" name="roomId" />
        <input type="submit" name="block" value="UNBLOCK"/>
    </form>
    <script type="text/javascript">
    $('#unblockRoomNo').change(function(){
	roomIdValue=$('.unblockRoom:selected').attr('id');
	$('#unblockroomId').val(roomIdValue);
      });
    </script>
FORM;

  return $blockRoomForm;
}

function blockRoomNo($roomId,$mcid) {
  $roomId = escape($roomId);
  $blockRoomQuery = "SELECT `hospi_blocked` FROM `prhospi_hostel` WHERE `hospi_blocked`=0 AND `page_modulecomponentid`={$mcid} AND `hospi_room_id`={$roomId}";
  $blockRoomQueryRes = mysql_query($blockRoomQuery) or displayerror(mysql_error());
  if(!mysql_num_rows($blockRoomQueryRes)) {
    displayerror("Room Does Not exist");
    return;
  } 
  $res = mysql_fetch_assoc($blockRoomQueryRes);
  if($res['hospi_blocked']!=0) {
    displaywarning("Room Blocked Already");
    return;
  }
  $blockRoomQuery = "UPDATE `prhospi_hostel` SET `hospi_blocked`=1 WHERE `page_modulecomponentid`={$mcid} AND `hospi_room_id`={$roomId}";
  $blockRoomQueryRes = mysql_query($blockRoomQuery) or displayerror(mysql_error());
  if($blockRoomQueryRes) displayinfo("Room Blocked ");
  else  displayinfo("There is a Error.Please contact System Administrator for Details");
  return;
}


function blockRoomNo($roomId,$mcid) {
  $roomId = escape($roomId);
  $blockRoomQuery = "SELECT `hospi_blocked` FROM `prhospi_hostel` WHERE `hospi_blocked`=0 AND `page_modulecomponentid`={$mcid} AND `hospi_room_id`={$roomId}";
  $blockRoomQueryRes = mysql_query($blockRoomQuery) or displayerror(mysql_error());
  if(!mysql_num_rows($blockRoomQueryRes)) {
    displayerror("Room Does Not exist");
    return;
  } 
  $res = mysql_fetch_assoc($blockRoomQueryRes);
  if($res['hospi_blocked']!=0) {
    displaywarning("Room Blocked Already");
    return;
  }
  $blockRoomQuery = "UPDATE `prhospi_hostel` SET `hospi_blocked`=1 WHERE `page_modulecomponentid`={$mcid} AND `hospi_room_id`={$roomId}";
  $blockRoomQueryRes = mysql_query($blockRoomQuery) or displayerror(mysql_error());
  if($blockRoomQueryRes) displayinfo("Room Blocked ");
  else  displayinfo("There is a Error.Please contact System Administrator for Details");
  return;
}

function unBlockRoomNo($roomId,$mcid) {
  $roomId = escape($roomId);
  $blockRoomQuery = "SELECT `hospi_blocked` FROM `prhospi_hostel` WHERE `hospi_blocked`=1 AND `page_modulecomponentid`={$mcid} AND `hospi_room_id`={$roomId}";
  $blockRoomQueryRes = mysql_query($blockRoomQuery) or displayerror(mysql_error());
  if(!mysql_num_rows($blockRoomQueryRes)) {
    displayerror("Room Does Not exist");
    return;
  } 
  $res = mysql_fetch_assoc($blockRoomQueryRes);
  $blockRoomQuery = "UPDATE `prhospi_hostel` SET `hospi_blocked`=0 WHERE `page_modulecomponentid`={$mcid} AND `hospi_room_id`={$roomId}";
  $blockRoomQueryRes = mysql_query($blockRoomQuery) or displayerror(mysql_error());
  if($blockRoomQueryRes) displayinfo("Room Unblocked ");
  else  displayinfo("There is a Error.Please contact System Administrator for Details");
  
  return;
}

?>