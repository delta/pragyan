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
 * @copyright (c) 2012 Pragyan Team
 * @author shriram<vshriram93@gmail.com>
 * @author Abhishek Kaushik
 * @license http://www.gnu.org/licenses/ GNU Public License
 * For more details, see README
 */

function displayRegistrationForm() {
  $registerForm =<<<FORM
    <form action="./+view" method="post">
      <h1 align='center' ><u>Tshirt and Food Coupon Registration Form</u></h2>
      <table width="100%" class="table table-bordered">  
      <tr>
      <td style="vertical-align: middle;">Name</td>
      <td style="vertical-align: middle;">  
        <input type="Name" name="name_registrant" required autofocus placeholder="Enter Your Name" />
      </td>
      </tr>
      <tr> 
       <td>Amount</td>
       <td>             <input type='radio' name='amount_plan' value='500' onclick="document.getElementById('display_sizeTshirt').style.display='none' "> Rs.500 (Only Food Coupon)
</td>
       </tr> 
      <tr id='display_gender'>
         <td>Gender</td>
         <td>
         <input type='radio' name='gender' value='male' >Male
         <input type='radio' name='gender' value='female'>Female
         </td>
       </tr>

      <tr><td colspan="2">
      <input type='submit' name='submit_reg_form'  Value='Register'></td></tr>
</table>
    <span style="color:white;margin-left:25%;text-align:center;font-size:14px">Rgistration Closes on 11th Spetember 2014 at 9:00pm</span><br/>
      <span style="color:white;margin-left:25%;text-align:center;font-size:13px">Note : The amount you choose will be deducted from your hostel fees.You won't be charged seperately</span>
    <table style='width:30%;margin-left:23%;position:relative;float:left;'>
        <tr>
            <td>BOYS</td>
            <td>Small</td>
            <td>Medium</td>
            <td>Large</td>
            <td>XL</td>
            <td>XXL</td>
        </tr>
        <tr>
            <td>Chest</td>
            <td>19</td>
            <td>20</td>
            <td>21</td>
            <td>22</td>
            <td>23</td>
        </tr>
        <tr>
            <td>Length</td>
            <td>26</td>
            <td>27</td>
            <td>28</td>
            <td>29</td>
            <td>30</td>
        </tr>
        <tr>
            <td>Sleeve Length</td>
            <td>8</td>
            <td>8</td>
            <td>9</td>
            <td>9</td>
            <td>10</td>
        </tr>
        <tr>
            <td>Gen. Shirt Size</td>
            <td>36</td>
            <td>38</td>
            <td>40</td>
            <td>42</td>
            <td>44</td>
        </tr>
    </table>
    <table style='position:relative;margin-left:5%;float:left;width:30%;'>
        <tr>
            <td>GIRLS</td>
            <td>Small</td>
            <td>Medium</td>
            <td>Large</td>
            <td>XL</td>
        </tr>
        <tr>
            <td>Shoulder</td>
            <td>13</td>
            <td>14</td>
            <td>15</td>
            <td>16</td>
        </tr>
        <tr>
            <td>Bust</td>
            <td>17</td>
            <td>18</td>
            <td>19</td>
            <td>20</td>
        </tr>
        <tr>
            <td>Waist</td>
            <td>16</td>
            <td>17</td>
            <td>18</td>
            <td>19</td>
        </tr>
        <tr>
            <td>Hips</td>
            <td>18.5</td>
            <td>19.5</td>
            <td>21</td>
            <td>22.5</td>
        </tr>
        <tr>
            <td>Back Length</td>
            <td>21</td>
            <td>22</td>
            <td>23.5</td>
            <td>24</td>
        </tr>
    </table><br/>
    <span style="color:white;margin-left:25%;text-align:center;font-size:13px">Note : All measurements are in inches</span>
  </form>
FORM;
  return $registerForm;
}


function handleDistribution() {
  $distributionFlowHandle=<<<TABLE
    <div style="border:1px solid black; width:30%;">
       <form method="post" action="./+octeam&subaction=choose">
          <input type="checkbox" name="changeUserDetail[]" value="S"/> Small<br/>
          <input type="checkbox" name="changeUserDetail[]" value="M"/> Medium <br/>
          <input type="checkbox" name="changeUserDetail[]" value="L"/> Large<br/>
          <input type="checkbox" name="changeUserDetail[]" value="XL"/> XL<br/>
          <input type="checkbox" name="changeUserDetail[]" value="XXL"/> XXL<br/>
          <input type="checkbox" name="changeUserDetail[]" value="food_coupon"/> Food Coupon<br/>
          <input type="checkbox" name="changeUserDetail[]" value="extra"/> Extra<br/>
          Password: <input type="password" name="passwordChangeOption" /><br/>
          <input type="submit" value="change" style="font-size:15px;"/>
      </form>
     </div>
     <hr/>
     <h1>Enter User Detail</h1>
     <table id="tableDistribution" border="1">
       <thead>
         <td>INPUT</td>
         <td>STATUS</td>
       </thead>
     </table>
TABLE;
  $distributionFlowHandle.=<<<SCRIPTS
    <script type="text/javascript">
      var formIndex = 0,typed_into=false,chars=[];
    var pressed = false;
    function createBarCode(id){
    $("#"+id).keypress(function (e) {
    var changes = this;
    chars.push(String.fromCharCode(e.which));
        if (pressed == false) {
            setTimeout(function () {
                if (chars.length == 9 || chars.length == 6) {
                  fIndex = $(changes).attr('id');
                  fIndex=fIndex.substr(17)
                  ajaxSendDetails(fIndex);
                }
                pressed = false;
                chars=[];
            }, 500);
        }
              pressed=true;
    });
}







      function ajaxSendDetails(fIndex) {
            var userDetail = $("#submitUserDetail_"+fIndex).val();
              $("#statusOC_"+fIndex).html('Processing...');
              $.ajax({
                type:"POST",
                url :"./+octeam",
                data:{
                  roll    : userDetail
                }
              }).done(function(msg){
                  $("#statusOC_"+fIndex).html(msg);
                  pressed=false;

              });
              formIndex=formIndex+1;
              appendRowForNewUser();  
              $("#submitUserDetail_"+formIndex).focus();
          
      }
      function sendDetails(e,fIndex){
            if(e.keyCode == 13) {
              ajaxSendDetails(fIndex);
            }            

         }
         function appendRowForNewUser() {
         var tableField="<tr class='submitInformation'>";
	 tableField+="<td><input class='submitUserDetail' style='height:25px;font-size:15px;' placeholder='Scan User Detail' onkeypress='return sendDetails(event,formIndex)' type='text' id='submitUserDetail_"+formIndex+"' /></td>";
             tableField+="<td id='statusOC_"+formIndex+"'>....</td>";
             tableField+="</tr>";
         $('#tableDistribution').append(tableField); 
         createBarCode('submitUserDetail_'+formIndex);
      }
      appendRowForNewUser();              
    </script>

SCRIPTS;
return $distributionFlowHandle;
}

function final_submit() {
    $form=<<<FORM
      <script type="text/javascript">
      function submitLatest(frm) {
      console.log(frm);
      val = frm.getElementsByClassName("rolledValue")[0].value;
              $.ajax({
                type:"POST",
                url :"./+octeam",
                data:{
		  roll_latest_submit    : val 
                }
              }).done(function(msg){
                  $(frm).parent().html(msg);
                  pressed=false;

              });
      
      return false;
      }
      </script>
FORM;
    return $form;
}
