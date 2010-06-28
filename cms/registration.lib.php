<?php
/**
 * @package pragyan
 * @copyright (c) 2008 Pragyan Team
 * @license http://www.gnu.org/licenses/ GNU Public License
 * For more details, see README
 */
 
function getRegistrationForm() {
	$captchaHtml = getCaptchaHtml();
	$reg_str =<<<REG
<script language="javascript">
			function checkPassword(inputhandler2) {
				inputhandler1=inputhandler2.form.user_password;
				if(inputhandler1.value!=inputhandler2.value) {
					alert("Passwords do not match");
					inputhandler2.value="";
					inputhandler1.value="";
					inputhandler1.focus();
					return false;
				}
				return true;
			}
			function checkRegistrationForm(inputhandler) {
				if(inputhandler.user_password.value.length==0) {
					alert("Blank password not allowed.");
					return false;
				}
				if(inputhandler.user_name.value.length==0) {
					alert("Blank 'User name' not allowed.");
					return false;
				}
				if(inputhandler.user_fullname.value.length==0) {
					alert("Blank 'Full name' not allowed.");
					return false;
				}
				return (checkEmail(this.user_email)&&checkPassword(this.user_repassword));
			}
</script>
<form class="cms-registrationform"  method="POST" name="user_reg_usrFrm" onsubmit="return checkRegistrationForm(this)" action="./+login&subaction=register">
	<fieldset>
	<legend> Sign Up</legend>
		<table>
	       <tr>	<td><label for="user_email" class="labelrequired">Email</label></td>
				<td><input name="user_email" id="user_email" class="required" onchange="if(this.length!=0) return checkEmail(this);" type="text"></td>
           </tr>
           <tr>	<td><label for="user_password" class="labelrequired">Password</label></td>
	     		<td>  <input name="user_password" id="user_password" class="required" type="password"></td>
	     	</tr>
			<tr> <td><label for="user_repassword" class="labelrequired">Re-enter Password</label></td>
	   			<td> <input name="user_repassword" id="user_repassword" class="required" onchange="if(this.length!=0) return checkPassword(this);" type="password"></td>
  			</tr>
  			<tr>
  				<td><label for="user_name" class="labelrequired">User name</label></td>
				<td><input name="user_name" id="user_name" class="required" type="text"></td>
   			</tr>
   			<tr>
   				<td><label for="user_fullname" class="labelrequired">Full Name</label></td>
   				<td><input name="user_fullname" id="user_fullname" class="required" type="text"></td>
   			</tr>
   			$captchaHtml
   			<tr>
				<td colspan="2">&nbsp;</td>
			</tr>

			<tr>
				<td><input type="submit" id="submitbutton" value="Sign Up"></td>
				<td><a href="./+login&subaction=register&reSendKey=1">Resend Activation link?</a> <a href="./+login">Login?</a></td>
			</tr>
		</table>
	</fieldset>
</form>
REG;
	return $reg_str;
}

function register() {
	///registration formmessenger
	global $uploadFolder;global $sourceFolder;global $moduleFolder;global $urlRequestRoot;
	require("$sourceFolder/$moduleFolder/form/registrationformgenerate.php");
	require("$sourceFolder/$moduleFolder/form/registrationformsubmit.php");
	if ((!isset ($_GET['key'])) && (!isset ($_GET['reSendKey'])) && (!isset ($_POST['user_email']))) {
		return getRegistrationForm();
	}
	///Activation key resend form
	elseif ((isset ($_GET['reSendKey'])) && (!isset ($_POST['resend_key_email'])) && SEND_MAIL_ON_REGISTRATION) {

		$reSendForm =<<<FORM
<form  class="cms-registrationform" method="POST" name="user_resend_key" onsubmit="return checkForm(this)" action="./+login&subaction=register&reSendKey">
   <fieldset>
   <legend>Resend Activation Link</legend>
   <table>
		<tr>
			<td><label for="resend_key_email"  class="labelrequired">Email</label></td>
			<td><input type="text" name="resend_key_email" id="resend_key_email" class="required" onchange="if(this.length!=0) return checkEmail(this);"/><br /></td>
		</tr>
		<tr>
			<td colspan="2">&nbsp;</td>
		</tr>
		<tr>
			<td><input type="submit" id="submitbutton" value="Submit"></td>
			<td><a href="./+login&subaction=register">Sign Up</a> <a href="./+login">Login?</a></td>
		</tr>
	</table>
	</fieldset>
</form>
FORM;
		return $reSendForm;
	}
	///Activation key resend submission
	elseif (isset ($_POST['resend_key_email'])) {
		$email = escape($_POST['resend_key_email']);
		$query = "SELECT * FROM  `" . MYSQL_DATABASE_PREFIX . "users`  WHERE `user_email`='$email' ";
		$result = mysql_query($query) or displayerror(mysql_error() . "registration L:131");
		if (!mysql_num_rows($result))
			displayinfo("This email-id has not yet been registered. Kindly <a href=\"./+login&subaction=register\">register</a>.");
		else {
			$temp = mysql_fetch_assoc($result);
			if ($temp['user_activated'] == 1)
				displayinfo("E-mail $email has already been verified.<a href=\"./+login\"> Login</a> <a href=\"./+login&subaction=resetPasswd\">Forgot Password?</a>");
			else {
				$key = getVerificationKey($email, $temp['user_password'], $temp['user_regdate']);

				// send mail code starts here - see common.lib.php for more
				$from = CMS_EMAIL;
				$to = "$email";
				$mailtype = "activation_mail";
				
				
				$messenger = new messenger(false);
				global $onlineSiteUrl;
				$messenger->assign_vars(array('ACTIVATE_URL'=>"$onlineSiteUrl/+login&subaction=register&verify=$to&key=$key",'NAME'=>"$temp[user_fullname]",'WEBSITE'=>CMS_TITLE));

				if ($messenger->mailer($to,$mailtype,$key,$from))
					displayinfo("Activation link resent. Kindly check your e-mail for activation link.");
				else 
					displayerror("Activation link resending failure. Kindly contact administrator");
				// send mail code ends here
							
			}
		}
	}
	///Activation key submission
	elseif (isset ($_GET['key'])) {
		$emailId = escape($_GET['verify']);
		$query = "SELECT * FROM  `" . MYSQL_DATABASE_PREFIX . "users`  WHERE `user_email`='{$emailId}'";
		$result = mysql_query($query) or displayerror(mysql_error() . "registration L:76");
		$temp = mysql_fetch_assoc($result);
		if ($temp['user_activated'] == 1)
			displayinfo("E-mail ".escape($_GET[verify])." has already been verified");
		else {
			if ($_GET['key'] == getVerificationKey($_GET['verify'], $temp['user_password'], $temp['user_regdate'])) {
				$query = "UPDATE `" . MYSQL_DATABASE_PREFIX . "users` SET `user_activated`=1  WHERE `user_email`='$emailId'";
				mysql_query($query) or die(mysql_error());
				if (mysql_affected_rows() > 0)
					displayinfo("Your e-mail ".escape($_GET[verify])." has been verified. Now you can fill your profile information by clicking <a href=\"./+profile\">here</a> or by clicking on the preferences link in the action bar any time you are logged in.");
				else
					displayerror("Verification error for ".escape($_GET[verify]).". Please contact administrator");
			} else
				displayerror("Verification error for ".escape($_GET[verify]).". Please contact administrator");
		}
	}
	///Registration form submission
	else {

		if ((($_POST['user_email']) == "") || (($_POST['user_password']) == "")) {
			displayerror("Blank e-mail/password NOT allowed");
			return getRegistrationForm();
		}

		if ((($_POST['user_name']) == "") || (($_POST['user_fullname']) == "")) {
			displayerror("Please fill in your user name and Full name");
			return getRegistrationForm();
		}

		if (!eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$", $_POST['user_email'])) {
			displayerror("Invalid Email Id");
			return getRegistrationForm();
		}
		if (($_POST['user_password']) != ($_POST['user_repassword'])) {
			displayerror("Passwords are not same");
			return getRegistrationForm();
		}
		if (submitCaptcha()==false) {
			return getRegistrationForm();
		}
		/*For new registrations*/

		$umail = escape($_POST['user_email']);
		$umail = trim($umail);

		$query = "SELECT * FROM `" . MYSQL_DATABASE_PREFIX . "users` WHERE `user_email`='" . $umail . "'";
		$result = mysql_query($query) or displayerror(mysql_error() . "in registration L:115");
		if (mysql_num_rows($result)) {
			displaywarning("Email already exists in database. Please use a different e-mail.");
			return getRegistrationForm();
		} else {
			$passwd = md5($_POST['user_password']);
			$query = "INSERT INTO `" . MYSQL_DATABASE_PREFIX . "users` " .
					"(`user_name`, `user_email`, `user_fullname`, `user_password`, `user_activated`) " .
					"VALUES ('".escape($_POST['user_name'])."', '".escape($_POST['user_email'])."', '".escape($_POST['user_fullname'])."', '$passwd', ".ACTIVATE_USER_ON_REG.")";
			if (mysql_query($query))
			{
				if(ACTIVATE_USER_ON_REG)
					displayinfo("You have been successfully registered. You can now <a href=\"./+login\">log in</a>.");
				else displayinfo("Your registration was successful but your account is not activated yet. Kindly check your email, or wait for the website administrator to activate you.");
			}
			if(SEND_MAIL_ON_REGISTRATION)
			{
				$email = $umail;
				$query = "SELECT * FROM  `" . MYSQL_DATABASE_PREFIX . "users`  WHERE `user_email`='$email' ";
				$result = mysql_query($query) or displayerror(mysql_error() . "registration L:211");
			
				$temp = mysql_fetch_assoc($result);
				$key = getVerificationKey($email, $temp['user_password'], $temp['user_regdate']);

				// send mail code starts here - see common.lib.php for more
				$from = CMS_EMAIL;
				$to = "$email";
				$mailtype = "activation_mail";
		
		
				$messenger = new messenger(false);
				global $onlineSiteUrl;
				$messenger->assign_vars(array('ACTIVATE_URL'=>"$onlineSiteUrl/+login&subaction=register&verify=$to&key=$key",'NAME'=>"$temp[user_fullname]",'WEBSITE'=>CMS_TITLE, 'DOMAIN'=>$onlineSiteUrl));

				if ($messenger->mailer($to,$mailtype,$key,$from))
					displayinfo("Kindly check your e-mail for activation link.");
				else 
					displayerror("Activation link sending failure. Kindly contact administrator");
				// send mail code ends here
					
			}
			
		}
	}
}

function getVerificationKey($userEmail, $userPassword, $userRegistrationTime) {
	return md5(substr($userEmail, 0, 6) . substr(md5($userPassword), -17) . $userRegistrationTime . $userPassword);
}





