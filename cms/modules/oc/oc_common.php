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
 * @author Abhishek Kaushik
 * @license http://www.gnu.org/licenses/ GNU Public License
 * For more details, see README
 */


function getAuthMethod($userId) {
  if($userId <= 0) return "Anonymous";
  $query = "SELECT `user_loginmethod` FROM `".MYSQL_DATABASE_PREFIX."users` WHERE `user_id` = '".$userId."'";
  $result = mysqli_query($GLOBALS["___mysqli_ston"], $query);
  $row = mysqli_fetch_row($result);
  return $row[0];

}

function handleRegistrationFormSubmit($userId,$mcId) {
   $email = getUserEmail($userId);
   $authType = getAuthMethod($userId);
   if($authType != "imap") {
     displaywarning("You need to be logged in via Webmail to access this page.<br/>");
     if(!$userId) displayinfo("Click <a href='./+login'>here</a> to login");
     return false;
   }
   global $authmethods;
   $rollNo=substr($email,0,strrpos($email,'@'.$authmethods['imap']['user_domain']));
   $checkIfUserRegisteredquery = "SELECT * from `oc_form_reg` WHERE `page_moduleComponentId`={$mcId} AND `user_id`={$userId}";
   $checkIfUserRegisteredResult = mysqli_query($GLOBALS["___mysqli_ston"], $checkIfUserRegisteredquery) or die(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
   if(mysqli_num_rows($checkIfUserRegisteredResult)>0) {
  if(!checkIfUserWhiteListed($mcId,getUserEmail($userId))) {
    displaywarning("<b>There are problems that persisting with your current mess account.</b><br/>Pragyan team will get back to you after identification of your problem.<br/><b>Your details have been noted</b>" );
   return false;
    }
  else displayinfo("You have already registered.");
     return false;
   }
   if(isset($_POST['submit_reg_form'])) {  
     if(!(isset($_POST['amount_plan'])&&($_POST['amount_plan']=='500'||isset($_POST['size_tshirt']))&&isset($_POST['name_registrant'])&&isset($_POST['gender']))) {
       displaywarning("Invalid Information.Your IP has been tracked for misuse.Do not try it again.");
       return true;
     } 
if(!isset($_POST['gender'])){
    displaywarning("Please Fill in your gender");
    return false;
}
     $name   = escape($_POST['name_registrant']); 
     $amount = escape($_POST['amount_plan']);
     $gender = escape($_POST['gender']);
     $tsize  = isset($_POST['size_tshirt'])?escape($_POST['size_tshirt']):'';
     $query  = "";
  if(!checkIfUserWhiteListed($mcId,getUserEmail($userId))) {
    displaywarning("There are problems that persisting with your current mess account.<br/>Pragyan team will get back to you after identification of your problem.." );
    return false;  
}
     if($_POST['amount_plan']=='500') {
       $query="INSERT INTO `oc_form_reg` (`page_modulecomponentid`,`name`,`amount`,`user_id`,`Tshirt_size`,`gender`, `updated_time`,`oc_roll_no`) 
                                 VALUES ('$mcId','{$name}','{$amount}','{$userId}','','{$gender}', NOW(),$rollNo)";
     }
    /* else if($_POST['amount_plan']=='700'&&$tsize!="") {
       $query="INSERT INTO `oc_form_reg` (`page_modulecomponentid`,`name`,`amount`,`user_id`,`Tshirt_size`,`gender`,`updated_time`,`oc_roll_no`) 
                                 VALUES ('$mcId','{$name}','{$amount}','{$userId}','{$tsize}','{$gender}',NOW(),$rollNo)";
     }*/
     else displaywarning("Good Try.But you won't get Food Coupon worth Rs.700");
     if(mysqli_query($GLOBALS["___mysqli_ston"], $query)) {
	 displayinfo("Your registration is complete.");
       
       return false;
     }
     else {
       displayerror("There was some error in registration!.<br/>Please try again.<br/>If the problem persist,Contact Delta-Webteam.");
     }  
   }
   return true;
}

function displayOCDownload() {
  global $sourceFolder,$moduleFolder;
  require_once($sourceFolder."/".$moduleFolder."/qaos1/excel.php");
  $table=<<<TABLE
    <table>
      <thead>
        <td width="1000px"><b>Name</b></td>
        <td width="1000px"><b>Email(Start Adding From row 2)</b></td>
      </thead>
    </table>
TABLE;
  displayExcelForTable($table);  
}

function view_registered_users($mcId) {
  if(isset($_GET['saveAsExcel'])) $saveAsExcel = true;
  global $sourceFolder,$moduleFolder;
  require_once($sourceFolder."/".$moduleFolder."/qaos1/excel.php");
  global $STARTSCRIPTS;
  $smarttablestuff = "";
  if($saveAsExcel == false) {
    $smarttablestuff = smarttable::render(array('table_accousers'),null);    $STARTSCRIPTS .="initSmartTable();";                                   }
  $userDetails =<<<TABLE
    $smarttablestuff
    <table class="display" id="table_accousers" width="100%" border="1">
      <thead>
        <tr>
          <th>Name</th>
          <th>Roll No.</th>
          <th>Plan</th>
            <th>Gender</th>
          <th>T-Shirt</th>
          <th>Food Coupon</th>
          <th>Extras</th>
        </tr>
      </thead>
TABLE;
  $Yes = "green";
  $No = "red";
  $getRegisteredUserDetailQuery = "SELECT * FROM `oc_form_reg` WHERE `page_moduleComponentId`=1 ORDER BY oc_roll_no";
  $getRegisteredUserOc = mysqli_query($GLOBALS["___mysqli_ston"], $getRegisteredUserDetailQuery) or displayerror("Error on viewing registered user".((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
  while($res = mysqli_fetch_assoc($getRegisteredUserOc)) {
    //    $email = getUserEmail($res['user_id']);
    $rollNumber = $res['oc_roll_no'];
    $email = $res['oc_roll_no'].'@nitt.edu';
    //  if(!checkIfUserWhiteListed($mcId,$email)) continue;
  $getRegisteredUserDetailQuery1 = "SELECT * FROM `oc_valid_emails` WHERE `page_moduleComponentId`=1 AND oc_valid_email='{$email}'";
  $getRegisteredUserOc1 = mysqli_query($GLOBALS["___mysqli_ston"], $getRegisteredUserDetailQuery1) or displayerror("Error on viewing registered user".((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
  $result12345 = mysqli_fetch_assoc($getRegisteredUserOc1);
  
    
    $userDetails .=<<<TR
      <tr>
        <td>{$result12345['oc_name']}</td>
        <td>{$rollNumber}</td>
        <td>{$res['amount']}</td>
        <td>{$res['gender']}</td>
        <td style="background-color:${$res['oc_tshirt_distributed']}">{$res['Tshirt_size']}({$res['oc_tshirt_distributed']})</td>
        <td style="background-color:${$res['oc_food_coupon_distributed']}">({$res['oc_food_coupon_distributed']})</td>
        <td style="background-color:${$res['oc_extra_distributed']}">({$res['oc_extra_distributed']})</td>
      </tr>
TR;
  }
  $userDetails .=<<<TABLEEND
    </table>
TABLEEND;
  if($saveAsExcel) displayExcelForTable($userDetails);  
  $userDetails='<div style="background-color:yellow;;font-size:15px;"><a href="./+ochead&subaction=view_registered_users&saveAsExcel" target="_blank">Save As Excel</a></div><br/>'.$userDetails;

USER;
  return $userDetails;
  
}

function view_whitelist_emails($mcId){
  global $STARTSCRIPTS;
  $smarttablestuff = smarttable::render(array('table_accousers'),null);                                                
  $STARTSCRIPTS .="initSmartTable();";
  if(isset($_POST['remove_email'])){
    $removing_user=escape($_POST['removing']);
    $query="DELETE FROM `oc_valid_emails` WHERE `oc_valid_email`='{$removing_user}' AND `page_moduleComponentId`={$mcId}";
    mysqli_query($GLOBALS["___mysqli_ston"], $query) or displayerror(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
  }
  $userDetails =<<<TABLE
    $smarttablestuff
    <table class="display" id="table_accousers" width="100%" border="1">
      <thead>
        <tr>
          <th>Name</th>
          <th>Email</th>
          <th>Remove Email</th>
        </tr>
    </thead>
TABLE;
  $getRegisteredUserDetailQuery = "SELECT oc_name,oc_valid_email FROM `oc_valid_emails` WHERE `page_moduleComponentId`={$mcId}";
  $getRegisteredUserOc = mysqli_query($GLOBALS["___mysqli_ston"], $getRegisteredUserDetailQuery) or displayerror("Error on viewing registered user".((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
  while($res = mysqli_fetch_assoc($getRegisteredUserOc)) {
    $name = $res['oc_name'];
    $email = $res['oc_valid_email'];
    $userDetails .=<<<TR
      <tr>
        <td>{$name}</td>
        <td>{$email}</td> 
        <td>
          <form method="POST" action="./+ochead&subaction=view_whitelist_users">
           <input type="hidden" name="removing" value="{$email}" />
           <input type="submit" name="remove_email" value="REMOVE"/>
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

function add_whitelist_email($mcId){
  if(isset($_POST['add_email'])) {
    $name  = escape($_POST['roll']); 
    $email = escape($_POST['email']);
    $query = mysqli_query($GLOBALS["___mysqli_ston"], "INSERT IGNORE INTO `oc_valid_emails` (`page_modulecomponentid`,`oc_name`,`oc_valid_email`) 
                                                      VALUES ('$mcId','{$name}','{$email}')");
    if($query) {
      displayinfo("Successfully Added");
    }
    else {
      displayinfo(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
    }
  }
  $addWhiteList=<<<FORM
    <form action="./+ochead&subaction=add_whitelist_email" method="post">
      <input type="text" name="roll" autofocus required placeholder='Name' style='height:25px;width:200px;font-size:20px;'>
      <input type="text" name="email"  required placeholder='Email' style='height:25px;width:200px;font-size:20px;'>
      <input type="submit" name="add_email" style="font-size:18px" value="Add This User">
    </form>
FORM;
  return $addWhiteList;
}

function addToAvailability($mcId,$key,$pair) {
  escape($mcId);
  escape($key);
  escape($pair);
  $checkIfKeyExistQuery = "SELECT * from `oc_config` WHERE `key`='{$key}' AND `page_moduleComponentId`={$mcId}";
  $checkIfKeyExistResult = mysqli_query($GLOBALS["___mysqli_ston"], $checkIfKeyExistQuery) or displayerror(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
  if((!$checkIfKeyExistResult)) {
    return;
  }
  if(mysqli_num_rows($checkIfKeyExistResult)) {
    return;
  }
  $insertNewKeyQuery = "INSERT INTO `oc_config` VALUES ('{$mcId}','{$key}','{$pair}')";
  $insertNewKeyResult = mysqli_query($GLOBALS["___mysqli_ston"], $insertNewKeyQuery) or displayerror(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false))); 
  return;
}

function availability($mcId){
  addToAvailability($mcId,'S','No');  
  addToAvailability($mcId,'M','No');  
  addToAvailability($mcId,'L','No');  
  addToAvailability($mcId,'XL','No');  
  addToAvailability($mcId,'XXL','No');  
  addToAvailability($mcId,'food_coupon','No');  
  addToAvailability($mcId,'Extra','No');  
  if(isset($_POST['statusPairValue'])&&(isset($_POST['statusKeyValue']))) {
    $pair = escape($_POST['statusPairValue']);
    $key = escape($_POST['statusKeyValue']);
    if(!($pair=='No'||$pair=='Yes')) {
      displayerror("Invalid Pattern.Should be (Yes|No)");
    }
    else {
      $updateDetailsQuery = "UPDATE `oc_config` SET `value`='{$pair}' WHERE `key`='{$key}' AND `page_moduleComponentId`={$mcId}";
      $updateDetailsResult = mysqli_query($GLOBALS["___mysqli_ston"], $updateDetailsQuery) or displayerror(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
    }
  }  

  $getKeyQuery = "SELECT * from `oc_config` WHERE `page_moduleComponentId`={$mcId}";
  $getKeyResult = mysqli_query($GLOBALS["___mysqli_ston"], $getKeyQuery) or displayerror(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
  if((!$getKeyResult)) {
    displayerror("Please contact System Administrator for ficing the error");
    return;
  }
  $tableDetails=<<<TABLE
    <table>
      <tr>
        <th>Key</th>
        <th>Pair</th>
        <th>Change</th>
      </tr>
TABLE;

  while($result = mysqli_fetch_assoc($getKeyResult)) {
    $tableDetails.=<<<TABLE
      <tr>
        <th>{$result['key']}</th>
        <th>{$result['value']}</th>
        <th>
          <form action="./+ochead&subaction=availability" method="post">
            <input type="text" name="statusPairValue"/>
            <input type="hidden" name="statusKeyValue" value="{$result['key']}"/>
            <input type="submit" value="UPDATE"/>            
          </form>           
        </th>
      </tr>
TABLE;
  }
$tableDetails.="</table>";
return $tableDetails;  
}

function reg_status($mcId){
  global $STARTSCRIPTS;
  $smarttablestuff = smarttable::render(array('table_accousers'),null);                                                
  $STARTSCRIPTS .="initSmartTable();";
  $userDetails =<<<TABLE
    $smarttablestuff
    <table class="display" id="table_accousers" width="100%" border="1">
      <thead>
          <th>Total No. Of Registrations</th>
          <th>Total No. Of 500 Plan</th>
          <th>Total No. Of 700 Plan</th>
      </thead>
TABLE;
  $getRegStatus700 = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT * FROM `oc_form_reg` WHERE `amount`='700' AND `page_moduleComponentId`={$mcId}") 
                             or displayerror(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
  $getRegStatus500 = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT * FROM `oc_form_reg` WHERE `amount`='500' AND `page_moduleComponentId`={$mcId}")
                             or displayerror(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
  $totalReg = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT `user_id` FROM `oc_form_reg` WHERE `page_moduleComponentId`={$mcId}")
                      or displayerror(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
  $countReg=mysqli_num_rows($totalReg); 
  $countReg500=mysqli_num_rows($getRegStatus500); 
  $countReg700=mysqli_num_rows($getRegStatus700);
  $userDetails .=<<<TR
      <tr>
        <td>{$countReg}</td>
        <td>{$countReg500}</td>
        <td>{$countReg700}</td>
      </tr>
TR;
 $userDetails .=<<<TABLEEND
    </table>
TABLEEND;
 return $userDetails;
}

function checkIfUserWhiteListed($mcId,$email) {
   $checkIfWhiteListQuery = "SELECT  `oc_name` FROM `oc_valid_emails` 
                        WHERE `page_moduleComponentId`={$mcId} AND `oc_valid_email`='{$email}'";
   $checkIfWhiteListResult = mysqli_query($GLOBALS["___mysqli_ston"], $checkIfWhiteListQuery);
   if(mysqli_num_rows($checkIfWhiteListResult)==1) return true;
   return false;
}

function isAvailable($mcId,$str) {
  $str=escape($str);
  $query = "SELECT `value` FROM `oc_config` WHERE `page_moduleComponentId` = '{$mcId}' AND `key` = '{$str}'";
  $queryResult = mysqli_query($GLOBALS["___mysqli_ston"], $query) or displayerror(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
  if(!$queryResult) return false;
  if(!mysqli_num_rows($queryResult)) {
    displaywarning("Invalid Key Given");
    return false;
  }
  $value = mysqli_fetch_assoc($queryResult);
  if($value['value'] == 'Yes')  return true;
  return false;
}

function handleTShirtDistribution($mcId,$userId,$tShirtSize,$toDistribute = 0,$registeredBy) {
  global $urlRequestRoot,$sourceFolder,$templateFolder,$cmsFolder,$moduleFolder;
  $checkPNG = "$urlRequestRoot/$cmsFolder/$moduleFolder/oc/images/check.png";
  $wrongPNG = "$urlRequestRoot/$cmsFolder/$moduleFolder/oc/images/dialog-error.png";
  $checkIMG = "<img src=\"$checkPNG\" />";
  $wrongIMG = "<img src=\"$wrongPNG\" />";
  $processPNG = "$urlRequestRoot/$cmsFolder/$moduleFolder/oc/images/dialog-information.png";
  $processIMG = "<img src=\"$processPNG\" />";

  if(!(isset($_SESSION['availability_'.$tShirtSize])&&$_SESSION['availability_'.$tShirtSize]==1)) {
    echo "You are not eligible to distribute T Shirt of size $tShirtSize .  $wrongIMG<br/>";
    return "invalid";
  }
  
  if(!isAvailable($mcId,$tShirtSize)) {     
    echo "T-Shirt Size ".$tShirtSize." Not Available. $wrongIMG<br/><hr/>";
     return "false";
  }
  if($toDistribute == 0) {
    echo "Distribute ".$tShirtSize." to ".$userId.". $processIMG<br/><hr/>";
    return "true";
  } 
   $updateQuery = "UPDATE `oc_form_reg` SET `oc_tshirt_distributed`='Yes' , `updated_time`=NOW()
                           WHERE `oc_roll_no`={$userId} AND `page_moduleComponentId`={$mcId}";
   if(mysqli_query($GLOBALS["___mysqli_ston"], $updateQuery)) {
    echo "Confirmed {$userId}:  ".$tShirtSize." to ".$userId.". $processIMG<br/><hr/>";
    $mailtype = "tshirt_registration";
    $messenger = new messenger(false);
    global $onlineSiteUrl;				
    date_default_timezone_set('Asia/Kolkata');
    $from = "from: Pragyan Team <oc@pragyan.org>";
    $to = "107111099@nitt.edu";
    $messenger->assign_vars(array('TSHIRT'=>$tShirtSize,'ROLLNO'=>"$userId",'REGISTEREDBY'=>getUserEmail($registeredBy),'TIME'=>date('Y-m-d H:i:s')));
    $messenger->mailer($to,$mailtype,"",$from);
    $to=$userId."@nitt.edu";
    $messenger->mailer($to,$mailtype,"",$from);
   }       
   else {
    echo "There is a error in T-Shirt Distribution.Contact System Administrator.Do not Distribute T-Shirt. $wrongIMG<br/><hr/>";
   }
   return "true";
}

function handleFoodCouponDistribution($mcId,$userId,$toDistribute = 0,$registeredBy) { 
  global $urlRequestRoot,$sourceFolder,$templateFolder,$cmsFolder,$moduleFolder;
  $checkPNG = "$urlRequestRoot/$cmsFolder/$moduleFolder/oc/images/check.png";
  $wrongPNG = "$urlRequestRoot/$cmsFolder/$moduleFolder/oc/images/dialog-error.png";
  $checkIMG = "<img src=\"$checkPNG\" />";
  $wrongIMG = "<img src=\"$wrongPNG\" />";
  $processPNG = "$urlRequestRoot/$cmsFolder/$moduleFolder/oc/images/dialog-information.png";
  $processIMG = "<img src=\"$processPNG\" />";
  if(!(isset($_SESSION['availability_food_coupon'])&&$_SESSION['availability_food_coupon']==1)) {
    echo "You are not eligible to distribute Food Coupon.$wrongIMG<br/>";
    return;
  }
  if(!isAvailable($mcId,'food_coupon')) {
      echo "Food Coupon Not Available. $wrongIMG<br/><hr/>";
      return;
   }
  if($toDistribute == 0) {
    echo "Distribute Food Coupon to ".$userId.". $processIMG<br/><hr/>";
    return "true";
  } 
   $updateQuery = "UPDATE `oc_form_reg` SET `oc_food_coupon_distributed`='Yes' , `updated_time`=NOW()
                           WHERE `oc_roll_no`={$userId} AND `page_moduleComponentId`={$mcId}";
   if(mysqli_query($GLOBALS["___mysqli_ston"], $updateQuery)) {
    echo "Confirmed: Food Coupon to ".$userId.". $processIMG<br/><hr/>";
    $mailtype = "food_registration";
    $messenger = new messenger(false);
    global $onlineSiteUrl;				
    date_default_timezone_set('Asia/Kolkata');
    $from = "from: Pragyan Team <oc@pragyan.org>";
    $to = "107111099@nitt.edu";
    $messenger->assign_vars(array('TSHIRT'=>$tShirtSize,'ROLLNO'=>"$userId",'REGISTEREDBY'=>getUserEmail($registeredBy),'TIME'=>date('Y-m-d H:i:s')));
    $messenger->mailer($to,$mailtype,"",$from);
    $to=$userId."@nitt.edu";
    $messenger->mailer($to,$mailtype,"",$from);
    }       
   else {
    displayerror(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
    echo "There is a error in Food Coupon Distribution.Contact System Administrator.Do not Distribute Food Coupon. $wrongIMG<br/><hr/>";
   }
   return;
}

function handleExtras($mcId,$userId,$toDistribute = 0) {
  global $urlRequestRoot,$sourceFolder,$templateFolder,$cmsFolder,$moduleFolder;
  $checkPNG = "$urlRequestRoot/$cmsFolder/$moduleFolder/oc/images/check.png";
  $wrongPNG = "$urlRequestRoot/$cmsFolder/$moduleFolder/oc/images/dialog-error.png";
  $processPNG = "$urlRequestRoot/$cmsFolder/$moduleFolder/oc/images/dialog-information.png";
  $processIMG = "<img src=\"$processPNG\" />";
  $checkIMG = "<img src=\"$checkPNG\" />";
  $wrongIMG = "<img src=\"$wrongPNG\" />";
  if(!(isset($_SESSION['availability_extra'])&&$_SESSION['availability_extra']==1)) {
    echo "You are not eligible to distribute Extras<br/>";
    return;
  }
  if(!isAvailable($mcId,'Extra')) {
      echo "Extras Not Available. $wrongIMG<br/><hr/>";
      return;
   }
   
  if($toDistribute == 0) {
    echo "Distribute Extra to ".$userId.". $processIMG<br/><hr/>";
    return "true";
  } 
   $updateQuery = "UPDATE `oc_form_reg` SET `oc_extra_distributed`='Yes' , `updated_time` = NOW()
                           WHERE `oc_roll_no`={$userId} AND `page_moduleComponentId`={$mcId}";
   if(mysqli_query($GLOBALS["___mysqli_ston"], $updateQuery)) {
    echo "Confirmed: Distribute Extra to ".$userId.". $processIMG<br/><hr/>";
   }       
   else {
    echo "There is a error in Extra(s) Distribution.Contact System Administrator.Do not Distribute Extra(s). $wrongIMG<br/><hr/>";
   }
   return;
}

function checkExisting($mcId,$barCode_roll,$submit = 0,$registeredBy){
  global $urlRequestRoot,$sourceFolder,$templateFolder,$cmsFolder,$moduleFolder;
  $checkPNG = "$urlRequestRoot/$cmsFolder/$moduleFolder/oc/images/check.png";
  $wrongPNG = "$urlRequestRoot/$cmsFolder/$moduleFolder/oc/images/dialog-error.png";
  $checkIMG = "<img src=\"$checkPNG\" />";
  $wrongIMG = "<img src=\"$wrongPNG\" />";
  $processPNG = "$urlRequestRoot/$cmsFolder/$moduleFolder/oc/images/dialog-information.png";
  $processIMG = "<img src=\"$processPNG\" />";
  global $authmethods;

  $email = $barCode_roll.'@'.$authmethods['imap']['user_domain'];
  $userId = $barCode_roll;
  if(!checkIfUserWhiteListed($mcId,$email)) {
    echo "User's not White Listed. $wrongIMG<br/><hr/>";
    return;
  }
  $userId = getUserIdFromEmail($email);
  $fetchUserDetailQuery = "SELECT * FROM `oc_form_reg` WHERE `page_moduleComponentId`={$mcId} AND 
                                      `oc_roll_no`='{$barCode_roll}'";
  $fetchUserDetailResult = mysqli_query($GLOBALS["___mysqli_ston"], $fetchUserDetailQuery);
  if(!$fetchUserDetailResult) {
    echo "There is an error is handling details.Contact CSG for more details. $wrongIMG<br/><hr/>";
    return;
  }
  $userDetails = mysqli_fetch_assoc($fetchUserDetailResult);
  if(mysqli_num_rows($fetchUserDetailResult)!=1) {
    echo "User ".$barCode_roll." has not registered for Coupons or T-Shirt. $wrongIMG<br/><hr/>";
    return;
  }
  $amount = $userDetails['amount'];
  if($amount == '700') {
    $bool = isset($_SESSION['availability_S']) || isset($_SESSION['availability_M']) || isset($_SESSION['availability_L']) || isset($_SESSION['availability_XL']) || isset($_SESSION['availability_XXL']);
    if($userDetails['oc_tshirt_distributed']=='No' && $bool) {
      if(handleTShirtDistribution($mcId,$barCode_roll,$userDetails['Tshirt_size'],$submit,$registeredBy)=="invalid") {
		return;
      }
    }
    else if($userDetails['oc_tshirt_distributed']=='Yes' && $bool){
      echo "T-Shirt Distributed already. $checkIMG<br/><hr/>";
    }
    if($userDetails['oc_food_coupon_distributed']=='No' && isset($_SESSION['availability_food_coupon'])) {
       handleFoodCouponDistribution($mcId,$barCode_roll,$submit,$registeredBy);
    }
    else if($userDetails['oc_food_coupon_distributed']=='Yes' && isset($_SESSION['availability_food_coupon'])){
      echo "Food Coupon Distributed already. $checkIMG<br/><hr/>";
    }
    if($userDetails['oc_extra_distributed']=='No' && isset($_SESSION['availability_extra'])) {
       handleExtras($mcId,$barCode_roll,$submit);
    }
    else if($userDetails['oc_extra_distributed']=='Yes' && isset($_SESSION['availability_extra'])){
      echo "Extras Distributed already. $checkIMG<br/><hr/>";
    }
    return;
  }  
  else if($amount == '500') {
    if($userDetails['oc_food_coupon_distributed']=='No' && isset($_SESSION['availability_food_coupon'])) {
       handleFoodCouponDistribution($mcId,$barCode_roll,$submit,$registeredBy);
    }
    else if($userDetails['oc_food_coupon_distributed']=='Yes'&& isset($_SESSION['availability_food_coupon'])){
      echo "Food Coupon already Distributed. $checkIMG<br/><hr/>";
    }
    return;
  }
  echo "Invalid Amount.Contact System Administrator. $wrongIMG<br/><hr/>";
  return;
}

function upload_tshirt_list($mcId) {
  global $sourceFolder,$moduleFolder;
  require_once($sourceFolder."/".$moduleFolder."/qaos1/excel.php");
  if(isset($_FILES['uploadTShirtDetail']['name'][0])) {
    $date = date_create();
    $timeStamp = date_timestamp_get($date);
    $tempVar=$sourceFolder."/uploads/temp/".$timeStamp.$_FILES['uploadTShirtDetail']['name'][0];
    move_uploaded_file($_FILES["uploadTShirtDetail"]["tmp_name"][0],$tempVar);
    $excelData = readExcelSheet($tempVar);
    $success = 1;
    for($i=2;$i<=count($excelData);$i++)  {
      $name = $excelData[$i][1];
      $rollNumber = $excelData[$i][2];
      $tsize = $excelData[$i][3];
      if($tsize == '') $tsize='N';
            $query="INSERT IGNORE INTO `oc_form_reg` (`page_modulecomponentid`,`name`,`amount`,`user_id`,`Tshirt_size`,`updated_time`,`oc_roll_no`) 
                                 VALUES ('$mcId','{$name}','700','0','{$tsize}',NOW(),'{$rollNumber}')";
      mysqli_query($GLOBALS["___mysqli_ston"], $query) or displayerror(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
      
      //      $query = "UPDATE `oc_form_reg` SET Tshirt_size='{$tsize}' WHERE page_modulecomponentid='{$mcId}' AND oc_roll_no='{$rollNumber}'";  
      // mysql_query($query) or displayerror(mysql_error());
      
    }
  }
     
  
  $uploadValidEmail=getFileUploadForm($mcId,"oc",'./+ochead&subaction=upload_tshirt_list',false,1,'uploadTShirtDetail');
  return $uploadValidEmail;
}

function download_black_list($mcId) {
  global $sourceFolder,$moduleFolder;
  require_once($sourceFolder."/".$moduleFolder."/qaos1/excel.php");
  $tableUpdate=<<<TABLE
    <table>
      <tr>
        <th>NAME</th>
        <th>Roll Number</th>
      </tr>
TABLE;
  $viewBlackListQuery="SELECT `name`,`oc_roll_no` FROM oc_form_reg WHERE `page_moduleComponentId`={$mcId} ORDER BY oc_roll_no";
  $viewBlackListQueryRes = mysqli_query($GLOBALS["___mysqli_ston"], $viewBlackListQuery) or displayerror(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
  while($res = mysqli_fetch_assoc($viewBlackListQueryRes)) {
    $email = $res['oc_roll_no'].'@nitt.edu';
    $checkIfExist = "SELECT oc_valid_email FROM oc_valid_emails WHERE oc_valid_email='{$email}' AND `page_moduleComponentId`={$mcId}";
    $checkIfExistRes = mysqli_query($GLOBALS["___mysqli_ston"], $checkIfExist) or displayerror(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
    if(!mysqli_num_rows($checkIfExistRes)) {
      $tableUpdate.=<<<TR
      <tr>
        <td>{$res['name']}</td>
        <td>{$res['oc_roll_no']}</td>
      </tr>
TR;
    }
  }
  $tableUpdate.="</table>";
  displayExcelForTable($tableUpdate);  

}
