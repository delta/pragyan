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
  $result = mysql_query($query);
  $row = mysql_fetch_row($result);
  return $row[0];

}

function handleRegistrationFormSubmit($userId,$mcId) {
   $email = getUserEmail($userId);
   $authType = getAuthMethod($userId);
   if($authType != "imap") {
     displaywarning("You need to be logged in via Webmail to access this page.<br/>");
     if(!$userId) displayinfo("click <a href='./+login'></a> to login");
     return false;
   }
   $rollNo=substr($email,0,strrpos($email,'@'.$authmethods['imap']['user_domain']));
   displayinfo('Welcome'.'&nbsp'.$rollNo);  
   $checkIfUserRegisteredquery = "SELECT * from `oc_form_reg` WHERE `page_moduleComponentId`={$mcId} AND `user_id`={$userId}";
   $checkIfUserRegisteredResult = mysql_query($checkIfUserRegisteredquery) or die(mysql_error());
   if(mysql_num_rows($checkIfUserRegisteredResult)>0) {
     displayinfo("You have already registered.");
     return false;
   }
   if(isset($_POST['submit_reg_form'])) {  
     if(!(isset($_POST['amount_plan'])&&($_POST['amount_plan']=='500'||isset($_POST['size_tshirt']))&&isset($_POST['name_registrant']))) {
       displaywarning("Invalid Information.Your IP has been tracked for misuse.Do not try it again.");
       return true;
     } 
     $name   = escape($_POST['name_registrant']); 
     $amount = escape($_POST['amount_plan']);
     $tsize  = isset($_POST['size_tshirt'])?escape($_POST['size_tshirt']):'';
     $query  = "";
     if($_POST['amount_plan']=='500') {
       $query="INSERT INTO `oc_form_reg` (`page_modulecomponentid`,`name`,`amount`,`user_id`,`Tshirt_size`,`updated_time`) 
                                 VALUES ('$mcId','{$name}','{$amount}','{$userId}','{$tsize}',NOW())";
     }
     else if($_POST['amount_plan']=='700'&&$tsize!="") {
       $query="INSERT INTO `oc_form_reg` (`page_modulecomponentid`,`name`,`amount`,`user_id`,`Tshirt_size`,`updated_time`) 
                                 VALUES ('$mcId','{$name}','{$amount}','{$userId}','{$tsize}',NOW())";
     }
     else displaywarning("Good Try.But you won't get Food Coupon worth Rs.700");
     if(mysql_query($query)) {
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
  global $STARTSCRIPTS;
  $smarttablestuff = smarttable::render(array('table_accousers'),null);                                                
  $STARTSCRIPTS .="initSmartTable();";                                                                                                 
  $userDetails =<<<TABLE
    $smarttablestuff
    <table class="display" id="table_accousers" width="100%" border="1">
      <thead>
        <tr>
          <th>Name</th>
          <th>E mail</th>
          <th>Roll No.</th>
          <th>Plan</th>
          <th>T-Shirt</th>
          <th>Food Coupon</th>
          <th>Extras</th>
        </tr>
      </thead>
TABLE;
  $Yes = "Distributed";
  $No = "Pending";
  $getRegisteredUserDetailQuery = "SELECT * FROM `oc_form_reg` WHERE `page_moduleComponentId`={$mcId}";
  $getRegisteredUserOc = mysql_query($getRegisteredUserDetailQuery) or displayerror("Error on viewing registered user".mysql_error());
  while($res = mysql_fetch_assoc($getRegisteredUserOc)) {
    $userDetails .=<<<TR
      <tr>
        <td>{$res['name']}</td>
        <td>{$res['user_id']}</td>
        <td>{$res['user_id']}</td>
        <td>{$res['amount']}</td>
        <td>{$res['Tshirt_size']}(${$res['oc_tshirt_distributed']})</td>
        <td>(${$res['oc_food_coupon_distributed']})</td>
        <td>(${$res['oc_extra_distributed']})</td>
        
      </tr>
TR;
  }
  $userDetails .=<<<TABLEEND
    </table>
TABLEEND;
  return $userDetails;
}

function view_whitelist_emails($mcId){
  global $STARTSCRIPTS;
  $smarttablestuff = smarttable::render(array('table_accousers'),null);                                                
  $STARTSCRIPTS .="initSmartTable();";
  if(isset($_POST['remove_email'])){
    $removing_user=escape($_POST['removing']);
    $query="DELETE FROM `oc_valid_emails` WHERE `oc_valid_email`='{$removing_user}' AND `page_moduleComponentId`={$mcId}";
    mysql_query($query) or displayerror(mysql_error());
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
  $getRegisteredUserOc = mysql_query($getRegisteredUserDetailQuery) or displayerror("Error on viewing registered user".mysql_error());
  while($res = mysql_fetch_assoc($getRegisteredUserOc)) {
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
    $query = mysql_query("INSERT IGNORE INTO `oc_valid_emails` (`page_modulecomponentid`,`oc_name`,`oc_valid_email`) 
                                                      VALUES ('$mcId','{$name}','{$email}')");
    if($query) {
      displayinfo("Successfully Added");
    }
    else {
      displayinfo(mysql_error());
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
  $checkIfKeyExistResult = mysql_query($checkIfKeyExistQuery) or displayerror(mysql_error());
  if((!$checkIfKeyExistResult)) {
    return;
  }
  if(mysql_num_rows($checkIfKeyExistResult)) {
    return;
  }
  $insertNewKeyQuery = "INSERT INTO `oc_config` VALUES ('{$mcId}','{$key}','{$pair}')";
  $insertNewKeyResult = mysql_query($insertNewKeyQuery) or displayerror(mysql_error()); 
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
      $updateDetailsResult = mysql_query($updateDetailsQuery) or displayerror(mysql_error());
    }
  }  

  $getKeyQuery = "SELECT * from `oc_config` WHERE `page_moduleComponentId`={$mcId}";
  $getKeyResult = mysql_query($getKeyQuery) or displayerror(mysql_error());
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

  while($result = mysql_fetch_assoc($getKeyResult)) {
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
  $getRegStatus700 = mysql_query("SELECT * FROM `oc_form_reg` WHERE `amount`='700' AND `page_moduleComponentId`={$mcId}") 
                             or displayerror(mysql_error());
  $getRegStatus500 = mysql_query("SELECT * FROM `oc_form_reg` WHERE `amount`='500' AND `page_moduleComponentId`={$mcId}")
                             or displayerror(mysql_error());
  $totalReg = mysql_query("SELECT `user_id` FROM `oc_form_reg` WHERE `page_moduleComponentId`={$mcId}")
                      or displayerror(mysql_error());
  $countReg=mysql_num_rows($totalReg); 
  $countReg500=mysql_num_rows($getRegStatus500); 
  $countReg700=mysql_num_rows($getRegStatus700);
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
   $checkIfWhiteListResult = mysql_query($checkIfWhiteListQuery);
   if(mysql_num_rows($checkIfWhiteListResult)==1) return true;
   return false;
}

function isAvailable($mcId,$str) {
  $str=escape($str);
  $query = "SELECT `value` FROM `oc_config` WHERE `page_moduleComponentId` = '{$mcId}' AND `key` = '{$str}'";
  $queryResult = mysql_query($query) or displayerror(mysql_error());
  if(!$queryResult) return false;
  if(!mysql_num_rows($queryResult)) {
    displaywarning("Invalid Key Given");
    return false;
  }
  $value = mysql_fetch_assoc($queryResult);
  if($value == 'Yes')  return true;
  return false;
}

function handleTShirtDistribution($mcId,$userId,$tShirtSize) {
   if(!isAvailable($mcId,$tShirtSize)) {
      echo "T-Shirt Not Available.<br/>";
      return;
   }
   $updateQuery = "UPDATE `oc_form_reg` SET `oc_tshirt_distributed`='Yes' 
                           WHERE `user_id`={$userId} AND `page_moduleComponentId`={$mcId}";
   if(mysql_query($updateQuery)) {
    echo "Distribute ".$tShirtSize." to ".getUserEmail($userId)."<br/>";
   }       
   else {
    echo "There is a error in T-Shirt Distribution.Contact System Administrator.Do not Distribute Food Coupon<br/>";
   }
   return;
}

function handleFoodCouponDistribution($mcId,$userId) {
   if(!isAvailable($mcId,'food_coupon')) {
      echo "Food Coupon Not Available.";
      return;
   }
   $updateQuery = "UPDATE `oc_form_reg` SET `oc_food_coupon_distributed`='Yes' 
                           WHERE `user_id`={$userId} AND `page_moduleComponentId`={$mcId}";
   if(mysql_query($updateQuery)) {
    echo "Distribute Food Coupon to ".getUserEmail($userId)."<br/>";
   }       
   else {
    echo "There is a error in Food Coupon Distribution.Contact System Administrator.Do not Distribute Food Coupon<br/>";
   }
   return;
}

function handleExtras($mcId,$userId) {
   if(!isAvailable($mcId,'Extra')) {
      echo "Food Coupon Not Available.<br/>";
      return;
   }
   $updateQuery = "UPDATE `oc_form_reg` SET `oc_extra_distributed`='Yes' 
                           WHERE `user_id`={$userId} AND `page_moduleComponentId`={$mcId}";
   if(mysql_query($updateQuery)) {
    echo "Distribute Extra to ".getUserEmail($userId)."<br/>";
   }       
   else {
    echo "There is a error in Extra(s) Distribution.Contact System Administrator.Do not Distribute Extra(s)<br/>";
   }
   return;
}

function checkExisting($mcId,$barCode_roll){
  $email = $barCode_roll.'@'.$authmethods['imap']['user_domain'];
  if(!checkIfUserWhiteListed($mcId,$email)) {
    echo "User is not registered to Hostel.<br/>";
    return;
  }
  $userId = getUserIdFromEmail($email);
  $fetchUserDetailQuery = "SELECT * FROM `oc_form_reg` WHERE `page_moduleComponentId`={$mcId} AND 
                                      `user_id`='{$userId}'";
  $fetchUserDetailResult = mysql_query($fetchUserDetailQuery);
  if(!$fetchUserDetailResult) {
    echo "There is an error is handling details.Contact CSG for more details.<br/>";
    return;
  }
  $userDetails = mysql_fetch_assoc($fetchUserDetailResult);
  if(mysql_num_rows($fetchUserDetailResult)!=1) {
    echo "User ".$barCode_roll." has not registered for Coupons or T-Shirt.<br/>";
    return;
  }
  $amount = $userDetails['amount'];
  if($amount == '700') {
    if($userDetails['oc_tshirt_distributed']=='No') {
      handleTShirtDistribution($mcId,$userId,$userDetails['Tshirt_size']);
    }
    else {
      echo "T-Shirt Distributed already<br/>";
    } 
    if($userDetails['oc_food_coupon_distributed']=='No') {
       handleFoodCouponDistribution($mcId,$userId);
    }
    else {
      echo "Food Coupon Distributed already<br/>";
    }
    if($userDetails['oc_extra_distributed']=='No') {
       handleExtras($mcId,$userId);
    }
    else {
      echo "Extras Distributed already<br/>";
    }
    return;
  }  
  else if($amount == '500') {
    if($userDetails['oc_food_coupon_distributed']=='No') {
       handleFoodCouponDistribution($mcId,$userId);
    }
    else {
      echo "Food Coupon already Distributed<br/>";
    }
    return;
  }
  echo "Invalid Amount.Contact System Administrator.<br/>";
  return;
}
