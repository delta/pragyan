<?php
  /****
       addHostels To be completed 
       create Database and logic For it
  ****/
function displayAddNewRooms($action,$mcid) {
  $newhostel=<<<HOSTEL
    <h3> Add Hostel : </h3><br/>
      
    <form method="POST" action="./+{$action}&subaction=AddRoom">
      <input type="hidden" name="addHostels"/>
      <input type="button"  id="addHostel" onclick="addNewHostel()" value="Add Hostel">
      <input type="submit" value="Confirm"><br/>
    </form> 
   <script type="text/javascript">
      
     function addNewHostel() {
      var hostelDetail=document.getElementsByClassName("hostel");
      var addDetail=document.getElementById("addHostel");
      var hostelLen=hostelDetail.length;
      var toAdd='<label>Hostel #'+(hostelLen+1)+'</label>: <input type="text" name="addHostel'+(hostelLen+1)+'" class="hostel"><br/>';
      $(toAdd).insertBefore(addDetail);
     }
   </script>
HOSTEL;

  return $newhostel;
}

function isRoomAvailable($roomNo,$mcid) {

  $countRoomQuery="SELECT `hospi_room_capacity` FROM `prhospi_hostel` 
                   WHERE `page_modulecomponentid`={$mcid} AND `hospi_room_id` = {$roomNo} LIMIT 1"; 
  $countRoomRes = mysql_query($countRoomQuery) or displayerror(mysql_error());
  $res = mysql_fetch_array($countRoomRes);
  $availableQuery = "SELECT * FROM `prhospi_accomodation_status` 
                   WHERE `page_modulecomponentid`={$mcid} AND `hospi_room_id` = {$roomNo}
                   AND `hospi_actual_checkout` IS NULL"; 
  $availableRes = mysql_query($availableQuery) or displayerror(mysql_error());                 
  if(mysql_num_rows($availableRes)>=$res[0]) return false;
  return true;

}

// not yet done
function displayUsersInRoom($roomId,$mcid) {
  $displayQuery = "SELECT * FROM `prhospi_accomodation_status` WHERE `page_modulecomponentid`={$mcid} AND `hospi_room_id` = {$roomId}";
  $displayResult = mysql_query($displayQuery) or displayerror(mysql_error());
  $retForm =<<<DIV
    <div class="roomDetails" style="display:none" id="userInRoom$roomId">
DIV;
  while($res = mysql_fetch_assoc($displayResult)) {
    $retForm.="PID: ".$res['user_id'].", Name: ".getUserName($res['user_id']).", Email: ".getUserEmail($res['user_id'])."<br/>";
  }
  $retForm .=<<<DIV
    </div>
DIV;
  return $retForm;
}
function addUserToRoom($userId,$roomId,$mcid,$checkedInBy) {
  displayinfo($userId);
  global $sourceFolder,$moduleFolder;
  require_once("$sourceFolder/$moduleFolder/prhospi/prhospi_common.php");
  if(!isRegisteredToPr($userId,$mcid)) {
    displayerror("User is not register to PR.");
    return false;
  }
  
  $userExistQuery="SELECT `user_email` FROM `".MYSQL_DATABASE_PREFIX."users` WHERE `user_id` = '".$userId."'";
    $userExistRes = mysql_query($userExistQuery) or displayerror(mysql_error());
    if(!mysql_num_rows($userExistRes)) {
      displayinfo("Invalid Pragyan Id:".$userId);
      return false;
    }
    $checkIfUserEnrolledQuery = "SELECT * FROM `prhospi_accomodation_status` 
                            WHERE `user_id`={$userId} AND `page_modulecomponentid`={$mcid}";
    $checkIfUserEnrolledRes = mysql_query($checkIfUserEnrolledQuery) or displayerror(mysql_error());
    if(mysql_num_rows($checkIfUserEnrolledRes)) {
      displayinfo($userId." has already been alloted:");
      return false;
    }
    $time = date("Y-m-d H:i:s");
    
    $insertDetailsQuery = "INSERT INTO `prhospi_accomodation_status` 
                            (page_modulecomponentid,hospi_room_id,user_id,hospi_actual_checkin,hospi_checkedin_by)
                            VALUES ($mcid,$roomId,$userId,'{$time}','{$checkedInBy}')";
    $insertDetailsRes = mysql_query($insertDetailsQuery) or displayerror(mysql_error());
    return true;

}

function checkOut($userId,$refund,$mcid) {
  if((!is_numeric($userId))||$userId=="") {
    displaywarning("Invalid UserId");
    return "";
  }
  $time = date("Y-m-d H:i:s");
  if($refund ==0 ) {
    displayerror("Refund not provided.checkOut after recieving Refund amount");
    return "";
  }
  
  $checkIfUserEnrolledQuery = "SELECT * FROM `prhospi_accomodation_status` 
                            WHERE `user_id`={$userId} AND `page_modulecomponentid`={$mcid}";
  $checkIfUserEnrolledRes = mysql_query($checkIfUserEnrolledQuery) or displayerror(mysql_error());
  if(!mysql_num_rows($checkIfUserEnrolledRes)) {
    displaywarning("User ".getUserName($userId).", not registered for acco or for pragyan site ");
    return false;
    }
  $checkIfUserCheckedOutQuery = "SELECT * FROM `prhospi_accomodation_status` 
                            WHERE `user_id`={$userId} AND `page_modulecomponentid`={$mcid} AND `hospi_actual_checkout`!='0000-00-00 00:00:00'";
  $checkIfUserEnrolledRes = mysql_query($checkIfUserCheckedOutQuery) or displayerror(mysql_error());
  if(mysql_num_rows($checkIfUserEnrolledRes)) {
    displaywarning("User ".getUserName($userId).", has already Checkedout ");
    return false;
    }
   
  $updateQuery = "UPDATE `prhospi_accomodation_status` 
                  SET `hospi_actual_checkout` = '{$time}',`hospi_cash_refunded`={$refund} 
                  WHERE `page_modulecomponentid`={$mcid} AND `user_id`={$userId}";
  
   $updateRes = mysql_query($updateQuery) or displayerror(mysql_error());               
   if(mysql_affected_rows()<=0) {
     displaywarning("Pragyan Id: ".$userId.", not registered for acco or for pragyan site ");
     return;
   }
   displayinfo("User Successfully Checked Out for Accomodation");
}




function displayRooms ($mcid,$userId=0) {
  $query="SELECT DISTINCT `hospi_hostel_name` FROM `prhospi_hostel` WHERE `page_modulecomponentid`={$mcid}";
  $result4=mysql_query($query)or displayerror(mysql_error());
  $statusall="";
  static $i;
  while($temp4=mysql_fetch_array($result4,MYSQL_ASSOC)) {
    //    $statusall.=$temp4['hospi_hostel_name'];
    $statusall.='<table border="1">';
    $statusall.="<tr><td colspan='8'>{$temp4['hospi_hostel_name']}</td></tr>";
    for($i=0;$i<3;$i++) {
      $j=0;
      $statusall.='<tr>';
      $query="SELECT *  FROM `prhospi_hostel` 
                WHERE `hospi_hostel_name`='$temp4[hospi_hostel_name]' AND `hospi_room_no`<>0  
                       AND `hospi_floor`=$i AND `page_modulecomponentid`={$mcid}";
      $result=mysql_query($query)or displayerror(mysql_error());
      $num=mysql_num_rows($result);
      $x=$num/8;
      $x++;
      
      $statusall.="<td rowspan=$x>$i</td>";
      while($temp=mysql_fetch_array($result,MYSQL_ASSOC)) {
	$status="<br>Vacant";
	$query1="SELECT * FROM `prhospi_accomodation_status` 
                 WHERE `hospi_room_id`='$temp[hospi_room_id]' AND `page_modulecomponentid`={$mcid} AND `hospi_actual_checkout` IS NULL";
	$result1=mysql_query($query1);
	if(mysql_num_rows($result1)<$temp['hospi_room_capacity']);
	else $status="Full";
	if(mysql_num_rows($result1)>=$temp['hospi_room_capacity']||$temp['hospi_blocked']==1) {
	  $statusall.='<td id="asdf">';
	}
	else {
	  $statusall.='<td id="asdf1">';
	}
	$usersInRoom = displayUsersInRoom($temp['hospi_room_id'],$mcid);
	$statusall.=<<<ADDUSER
             <div class="details">
	  Room No: {$temp['hospi_room_no']}{$usersInRoom}
ADDUSER;
	$statusall.="$status      (".mysql_num_rows($result1)."/".$temp['hospi_room_capacity'].")";
	   $statusall.='</div></td>';
	   $j++;
	   if($j==8) {
	     $j=0;
	     $statusall.='</tr><tr>';
	   }
      }
      $statusall.='</tr>';
    }
    $statusall.='</tr>';
  }
  $statusall.='</tr></table>';
  $statusall.=<<<RED
	  <style type="text/css">
           #asdf 
           {
	      background-color: #FF0000;
	   }
	   #asdf1 
           {
	      background-color: #00FF00;
	   }
 	  </style>
	  <script type="text/javascript"> 
	      $(".details").hover(function(){
		  $(this).children('.roomDetails').css('display','');
		},function(){
		  $(this).children('.roomDetails').css('display','none');
		}
               );
          </script>     
RED;
  return $statusall;
}



function addUserToRoomAjax($userId,$roomId,$mcid,$checkedInBy,$stay) {
 global $sourceFolder,$moduleFolder;
  require_once("$sourceFolder/$moduleFolder/prhospi/prhospi_common.php");

  if(!isRegisteredToPr($userId,$mcid)) {
    return "User is not register to PR.";
  }
  
  $userExistQuery="SELECT `user_email` FROM `".MYSQL_DATABASE_PREFIX."users` WHERE `user_id` = '".$userId."'";
    $userExistRes = mysql_query($userExistQuery) or displayerror(mysql_error());
    if(!mysql_num_rows($userExistRes)) {
      return "Invalid Pragyan Id:".$userId;
    }
    $checkIfUserEnrolledQuery = "SELECT * FROM `prhospi_accomodation_status` 
                            WHERE `user_id`={$userId} AND `page_modulecomponentid`={$mcid}";
    $checkIfUserEnrolledRes = mysql_query($checkIfUserEnrolledQuery) or displayerror(mysql_error());
    if(mysql_num_rows($checkIfUserEnrolledRes)) {
      return getUserEmail($userId)." has already been alloted:";
    }
    $time = date("Y-m-d H:i:s");
    $initialAmount=getAmount("hospihead",$mcid);
    $initialAmount1=getAmount("hospihead1",$mcid);
    $amtRecieved = $initialAmount+($stay*$initialAmount1); 
    $insertDetailsQuery = "INSERT INTO `prhospi_accomodation_status` 
                            (page_modulecomponentid,hospi_room_id,user_id,hospi_actual_checkin,hospi_checkedin_by,hospi_cash_recieved)
                            VALUES ($mcid,$roomId,$userId,'{$time}','{$checkedInBy}',{$amtRecieved})";
    $insertDetailsRes = mysql_query($insertDetailsQuery) or die(mysql_error());
    $availableRoomNo=getAvailableRooms($mcid);
    return "Success  {$availableRoomNo}";
}









function updateUserToRoomAjax($userId,$roomId,$mcid,$checkedInBy,$stay) {
 global $sourceFolder,$moduleFolder;
  require_once("$sourceFolder/$moduleFolder/prhospi/prhospi_common.php");

  if(!isRegisteredToPr($userId,$mcid)) {
    return "User is not register to PR.";
  }
  
  $userExistQuery="SELECT `user_email` FROM `".MYSQL_DATABASE_PREFIX."users` WHERE `user_id` = '".$userId."'";
    $userExistRes = mysql_query($userExistQuery) or displayerror(mysql_error());
    if(!mysql_num_rows($userExistRes)) {
      return "Invalid Pragyan Id:".$userId;
    }
    $checkIfUserEnrolledQuery = "SELECT * FROM `prhospi_accomodation_status` 
                            WHERE `user_id`={$userId} AND `page_modulecomponentid`={$mcid}";
    $checkIfUserEnrolledRes = mysql_query($checkIfUserEnrolledQuery) or displayerror(mysql_error());
    if(!mysql_num_rows($checkIfUserEnrolledRes)) {
      return getUserEmail($userId)." has not been alloted:";
    }
    $time = date("Y-m-d H:i:s");
    
    $insertDetailsQuery = "UPDATE `prhospi_accomodation_status` 
                               SET page_modulecomponentid=$mcid,hospi_room_id=$roomId,user_id=$userId,hospi_actual_checkin='{$time}',hospi_checkedin_by='{$checkedInBy}' WHERE `page_modulecomponentid`={$mcid} AND `user_id`={$userId}";
    $insertDetailsRes = mysql_query($insertDetailsQuery) or displayerror(mysql_error());

    $availableRoomNo=getAvailableRooms($mcid);
    return "Success  {$availableRoomNo}";
}


?>
