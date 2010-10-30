<?php
/**
 * @package pragyan
 * @copyright (c) 2010 Pragyan Team
 * @license http://www.gnu.org/licenses/ GNU Public License
 * For more details, see README
 */

function resetPasswd() {
	if((!isset($_POST['user_email']))&&(!isset($_GET['key']))) {
		$resetPasswd =<<<RESET
					<form class="registrationform" method="POST" name="user_passreset" onsubmit="return checkForm(this)" action="./+login&subaction=resetPasswd">
						<fieldset>
						<legend>Reset Password</legend>
							<table>
								<tr>
									<td><label for="user_email"  class="labelrequired">Email</label></td>
									<td><input type="text" name="user_email" id="user_email" class="required" onchange="if(this.length!=0) return checkEmail(this);"/><br /></td>
								</tr>
								<tr>
									<td colspan="2">&nbsp;</td>
								</tr>
								<tr>
									<td><input type="submit" id="submitbutton" value="Submit"></td>
									<td><a href='./+login&subaction=register'>Sign Up</a> <a href="./+login">Login</a></td>
								</tr>
							</table>
						</fieldset>
					</form>
RESET;
		return $resetPasswd;
	}
	elseif(!isset($_GET['key'])) {
						$user_email = escape($_GET['user_email']);
						if (!eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$", escape($_POST['user_email'])))
							displayerror("Invalid Email Id. <br /><input type=\"button\" onclick=\"history.go(-1)\" value=\"Go back\" />");
						else {
							$query = "SELECT * FROM `" . MYSQL_DATABASE_PREFIX . "users` WHERE `user_email`='".escape($_POST[user_email])."' ";
							$result = mysql_query($query);
							$temp = mysql_fetch_assoc($result);
							if (mysql_num_rows($result) == 0)
								displayerror("E-mail not in registered accounts list. <br /><input type=\"button\" onclick=\"history.go(-1)\" value=\"Go back\" />");
							elseif ($temp['user_activated'] == 0) {
								displayerror("Account not yet activated.<b>Please check your email</b> and click on the activation link. <a href=\"./+login&subaction=register&reSendKey=1\">Resend activation mail?</a><br /><input type=\"button\" onclick=\"history.go(-1)\" value=\"Go back\" />");
							} else {
								
								$key = md5($temp['user_password'].'xXc'.substr($temp['user_email'],1,2));
								
								// send mail code starts here - see common.lib.php for more
//								$from = "no-reply@pragyan.org";
								$to = "$temp[user_email]";
								$mailtype = "password_forgot_reset";
								$language = "en";
								
								$messenger = new messenger(false);
								global $onlineSiteUrl;
								$messenger->assign_vars(array('RESETPASS_URL'=>"$onlineSiteUrl/+login&subaction=resetPasswd&resetPasswd=$temp[user_email]&key=$key", 'NAME'=>"$temp[user_fullname]", 'WEBSITE'=>CMS_TITLE, 'DOMAIN' => $onlineSiteUrl));
				
								if ($messenger->mailer($to,$mailtype,$key))
									displayinfo("Password reset link sent. Kindly check your e-mail. <br /><input type=\"button\" onclick=\"history.go(-2)\" value=\"Go back\" />");
								else 
									displayerror("Password reset failed. Kindly contact webadmin@pragyan.org");
								// send mail code ends here
								
							}
						}
	}
	else {
					$key = escape($_GET['key']);
					$user_email = escape($_GET['resetPasswd']);
					$password = rand();
					$dbpassword = md5($password);
					$query = "SELECT * FROM `" . MYSQL_DATABASE_PREFIX . "users` WHERE `user_email`='" . $user_email . "'";
					$result = mysql_query($query);
					$temp = mysql_fetch_assoc($result);
					if ($key == md5($temp['user_password'].'xXc'.substr($temp['user_email'],1,2))) {
						$query = "UPDATE `" . MYSQL_DATABASE_PREFIX . "users`  SET `user_password`='$dbpassword' WHERE `user_email`='$user_email'";
						$result = mysql_query($query);
						if (mysql_affected_rows() > 0) { 
							// send mail code starts here
//							$from = "no-reply@pragyan.org";
							$to = "$temp[user_email]";
							$mailtype = "password_reset";
							$language = "en";
							
							$messenger = new messenger(false);
							global $onlineSiteUrl;
							$messenger->assign_vars(array('PASSWORD'=>"$password",'NAME'=>"$temp[user_fullname]", 'WEBSITE'=>CMS_TITLE, 'DOMAIN'=>$onlineSiteUrl));
			
							if ($messenger->mailer($to,$mailtype,$key))
								displayinfo("Password reset. Kindly check your e-mail.");
							else 
								displayerror("Password reset failed. Kindly contact administrator");
							// send mail code ends here
			
						}
					} else
						displayinfo(safe_html("Authentication failure for password reset for $user_email"));
	}
	return "";
}

function loginForm()
{
	$login_str =<<<LOGIN
					<script language="javascript" type="text/javascript">
					<!--
					function checkLoginForm(inputhandler) {
						if(inputhandler.user_password.value.length==0) {
							alert("Blank password not allowed.");
							return false;
						}
						return checkEmail(this.user_email);
					}
					-->
					</script>
					<form method="POST" class="registrationform" name="user_loginform" onsubmit="return checkLoginForm(this);" action="./+login" autocomplete="off">
						<fieldset>
						<legend>Login</legend>
							<table>
								<tr>
									<td><label for="user_email"  class="labelrequired">Email</label></td>
									<td><input type="text" name="user_email" id="user_email" class="required" onchange="if(this.length!=0) return checkEmail(this);"/><br /></td>
								</tr>
								<tr><td><label for="user_password" class="labelrequired">Password</label></td>
									<td><input type="password" name="user_password"  id="user_password"  class="required" /><br /></td>
								</tr>
								<tr>
									<td><input type="submit" value="Login" /></td>
									<td><a href="./+login&subaction=resetPasswd">Lost Password?</a> <a href="./+login&subaction=register">Sign Up</a></td>
								</tr>
							</table>
						</fieldset>
					</form>
LOGIN;
	return $login_str;
}
function login() {
	if(isset($_GET['subaction'])) {
		if($_GET['subaction']=="resetPasswd") {
			return resetPasswd();
		}
		if($_GET['subaction']=="register") {
			require_once("registration.lib.php");
			return register();
		}
	}
	if (!isset ($_POST['user_email'])) {
		return loginForm();
	} else {
			
			/*if it is, 
							then userLDAPVerify($user_email,$user_passwd);
							if the password is correct, update his password in DB
							else $dontloginLDAP = true;
					}
					else {
						if(userLDAPVerify($user_email,$user_passwd)) {
							create his row in DB with loginmethod = ldap and user_activated = 1
							(for this, use the createUser funciton in common.lib.php)
						}
					}*/
					
			
			global $cookieSupported;
			$login_status = false;
			if($cookieSupported==true) {
				if ((($_POST['user_email']) == "") || (($_POST['user_password']) == ""))
					displayerror("Blank e-mail or password NOT allowed. <br /><input type=\"button\" onclick=\"history.go(-1)\" value=\"Go back\" />");
				else {
					$user_email = escape($_POST['user_email']);
					$user_passwd = escape($_POST['user_password']);
					$login_method = '';
					
					if($temp = getUserInfo($user_email)) { 
						// check if exists in DB
						$login_status = checkLogin($temp['user_loginmethod'],$temp['user_name'],$user_email,$user_passwd);
						// This is to make sure when user logs in through LDAP, ADS or IMAP accounts, his passwords should be changed in database also, incase its old.
						if ($login_status)
							updateUserPassword($user_email,$user_passwd); //update passwd in db
					}
					else { //if user is not in db
						global $authmethods;
						if(strpos($user_email,'@') > -1) {
							$tmp = explode('@',$user_email);
							$user_name = $tmp[0];
							$user_domain = strtolower($tmp[1]);
							}
						else $user_name = $user_email;

						if(isset($user_domain) && $user_domain==$authmethods['imap']['user_domain']) {
							if($login_status = checkLogin('imap',$user_name,$user_email,$user_passwd)) $login_method='imap';
							}
						elseif(isset($user_domain) && $user_domain==$authmethods['ads']['user_domain']) {
							if($login_status = checkLogin('ads',$user_name,$user_email,$user_passwd)) $login_method='ads';
						}
						
						elseif(isset($user_domain) && $user_domain==$authmethods['ldap']['user_domain']) {
							if(($login_status = checkLogin('ldap',$user_name,$user_email,$user_passwd))) $login_method='ldap';
						}
						
						if($login_status) { //create new user in db and activate the user (only if user's login is valid)
								$user_fullname = strtoupper($user_name);
								$user_md5passwd = md5($user_passwd);
								$query = "INSERT INTO `" . MYSQL_DATABASE_PREFIX . "users` " .
								"(`user_id`, `user_name`, `user_email`, `user_fullname`, `user_password`, `user_loginmethod`, `user_activated`) " .
								"VALUES (DEFAULT, '{$user_name}', '{$user_email}', '{$user_fullname}', '{$user_md5passwd}', '{$login_method}', '1')";
								mysql_query($query) or die(mysql_error() . " creating new user !");
						}
						else displaywarning("Incorrect username and/or password for <b>".(isset($user_domain)?$user_domain."</b> domain!":$user_name."</b> user"));
					}
				
					if($login_status) {
						$temp = getUserInfo($user_email);
						if (!$temp['user_activated']) {
							displayinfo("The e-mail has not yet been verified. Kindly check your email and click on verification link. <br /><input type=\"button\" onclick=\"history.go(-1)\" value=\"Go back\" />");
							// if user exists in db and admin has set user_activated = false delibrately
							// then it means that the user has been denied access !!!
							}
						else {
							$query = "UPDATE `" . MYSQL_DATABASE_PREFIX . "users` SET `user_lastlogin`=NOW() WHERE `" . MYSQL_DATABASE_PREFIX . "users`.`user_id` =$temp[user_id]";
							mysql_query($query) or die(mysql_error() . " in login.lib.L:111");
							$_SESSION['last_to_last_login_datetime']=$temp['user_lastlogin'];
							setAuth($temp['user_id']);
							
							//exit();
							//displayinfo("Welcome " . $temp['user_name'] . "!");
							return $temp['user_id'];
							}
						}
					else {
						displaywarning("Wrong E-mail or password. <a href='./+login&subaction=resetPasswd'>Lost Password?</a><br /><input type=\"button\" onclick=\"history.go(-1)\" value=\"Go back\" />");
					}
				}
				return 0;
			} else {
				showCookieWarning();
				return 0;
			}
	}
}

/*** ALL auth FUNCTIONS USED HERE CAN BE FOUND at authenticate.lib.php ***/



