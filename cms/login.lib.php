<?php
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
						$user_email = $_GET['user_email'];
						if (!eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$", $_POST['user_email']))
							displayerror("Invalid Email Id. <br /><input type=\"button\" onclick=\"history.go(-1)\" value=\"Go back\" />");
						else {
							$query = "SELECT * FROM `" . MYSQL_DATABASE_PREFIX . "users` WHERE `user_email`='$_POST[user_email]' ";
							$result = mysql_query($query);
							$temp = mysql_fetch_assoc($result);
							if (mysql_num_rows($result) == 0)
								displayerror("E-mail not in registered accounts list. <br /><input type=\"button\" onclick=\"history.go(-1)\" value=\"Go back\" />");
							elseif ($temp['user_activated'] == 0) {
								displayerror("Account not yet activated.<b>Please check your email</b> and click on the activation link. <a href=\"./+login&subaction=register&reSendKey=1\">Resend activation mail?</a><br /><input type=\"button\" onclick=\"history.go(-1)\" value=\"Go back\" />");
							} else {
								$server = $_SERVER['SCRIPT_URI'];
								$key = md5($temp['user_password'].'xXc'.substr($temp['user_email'],1,2));
								$message = "Dear $temp[user_fullname],\n" .
								"\nYou (or someone else) has requested a reset of the Pragayan'08 password associated with this account.\n" .
								"Use the following link to reset your password:\n" .
								"http://pragyan.org/08/home/+login&subaction=resetPasswd&resetPasswd=$temp[user_email]&key=$key\n" .
								"(Copy and paste the given link in your browser window if required )\n" .
								"\nSee you at Pragyan'08 (Feb 28th to March 2nd 2008).\n" .
								"Let's Celebrate technology!!\n" .
								"\nKindly ignore this mail if you do not want to reset your password.\n";
								"\nPragyan Team 2008.";
								$to = "$temp[user_email]";
								$subject = "Pragyan'08 password reset request";
								$body = "";
								$from = "From:no-reply@pragyan.org";
								if (mail($to, $subject, $message, $from)) {
									displayinfo("Password reset link sent. Kindly check your e-mail. <br /><input type=\"button\" onclick=\"history.go(-2)\" value=\"Go back\" />");
								} else {
									displayerror("Password reset failed. Kindly contact webadmin@pragyan.org");
								}
							}
						}
	}
	else {
					$key = $_GET['key'];
					$user_email = $_GET['resetPasswd'];
					$password = rand();
					$dbpassword = md5($password);
					$query = "SELECT * FROM `" . MYSQL_DATABASE_PREFIX . "users` WHERE `user_email`='" . $user_email . "'";
					$result = mysql_query($query);
					$temp = mysql_fetch_assoc($result);
					if ($key == md5($temp['user_password'].'xXc'.substr($temp['user_email'],1,2))) {
						$query = "UPDATE `" . MYSQL_DATABASE_PREFIX . "users`  SET `user_password`='$dbpassword' WHERE `user_email`='$user_email'";
						$result = mysql_query($query);
						if (mysql_affected_rows() > 0) {
							$message = "Dear $temp[user_fullname],\n" .
							"\nYou requested a reset of the Pragayan'08 password associated with this account.\n" .
							"Your password has been reset and your new password is: $password \n" .
							"Kindly use this password to login and then change it using user preferences.\n" .
							"\nSee you at Pragyan'08 (Feb 28th to March 2nd 2008).\n" .
							"Let's Celebrate technology!!\n" .
							"\nPragyan Team 2008.";
							$to = "$temp[user_email]";
							$subject = "Pragyan'08 password reset complete";
							$body = "";
							$from = "From:no-reply@pragyan.org";
							if (mail($to, $subject, $message, $from)) {
								displayinfo("Password reset. Kindly check your e-mail.");
							} else {
								displayerror("Password reset failed. Kindly contact webadmin@pragyan.org");
							}
						}
					} else
						displayinfo("Authentication failure for password reset for $user_email");
	}
	return "";
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
					<form method="POST" class="registrationform" name="user_loginform" onsubmit="return checkLoginForm(this);" action="./+login">
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
									<td><br /><input type="submit" value="Login" /></td>
									<td><a href="./+login&subaction=resetPasswd">Lost Password?</a> <a href="./+login&subaction=register">Sign Up</a></td>
								</tr>
							</table>
						</fieldset>
					</form>
LOGIN;
		return $login_str;
	} else {
			global $cookieSupported;
			if($cookieSupported==true) {
				if ((($_POST['user_email']) == "") || (($_POST['user_password']) == ""))
					displayerror("Blank e-mail or password NOT allowed. <br /><input type=\"button\" onclick=\"history.go(-1)\" value=\"Go back\" />");
				else {
					$user_email = $_POST['user_email'];
					$user_passwd = $_POST['user_password'];
					$query = "SELECT `user_id`,`user_password`,`user_name`,`user_activated`,`user_lastlogin` FROM `" . MYSQL_DATABASE_PREFIX . "users` WHERE `user_email` = '" . $user_email . "'";
					$result = mysql_query($query) or die(mysql_error() . "login query FAILURE login.lib L:100");
					$temp = mysql_fetch_assoc($result);
					if (!$temp['user_activated'])
						displayinfo("The e-mail has not yet been verified. Kindly check your email and click on verification link. <br /><input type=\"button\" onclick=\"history.go(-1)\" value=\"Go back\" />");
					elseif (MD5($user_passwd) != $temp['user_password'])
						displaywarning("Wrong E-mail or password. <a href='./+login&subaction=resetPasswd'>Lost Password?</a><br /><input type=\"button\" onclick=\"history.go(-1)\" value=\"Go back\" />");
					else {
						$query = "UPDATE `" . MYSQL_DATABASE_PREFIX . "users` SET `user_lastlogin`=NOW() WHERE `" . MYSQL_DATABASE_PREFIX . "users`.`user_id` =$temp[user_id]";
						mysql_query($query) or die(mysql_error() . " in login.lib.L:111");
						$_SESSION['last_to_last_login_datetime']=$temp['user_lastlogin'];
						setAuth($temp['user_id']);
						displayinfo("Welcome " . $temp['user_name'] . "!");
						return $temp['user_id'];
					}
				}
				return 0;
			} else {
				showCookieWarning();
				return 0;
			}
	}
}
?>