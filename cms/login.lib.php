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
 * @copyright (c) 2010 Pragyan Team
 * @license http://www.gnu.org/licenses/ GNU Public License
 * For more details, see README
 */

function resetPasswd($allow_login) {
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
									<td>
RESET;
		if($allow_login)
			$resetPasswd .="<a href='./+login&subaction=register'>Sign Up</a> ";
			$resetPasswd .= "<a href='./+login'>Login</a></td>
								</tr>
							</table>
						</fieldset>
					</form>";
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
							elseif ($temp['user_loginmethod']==='openid')
		displayerror("This email is registered as an OpenID user. You do not have a permanent account on our server. Hence, we do not keep or maintain your password. Please ask the parent OpenID provider to reset the password for you");
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
/**
 * This function takes the OpenID given by the user and
 * try to find out the final endpoint by parsing the OpenID URL.
 * It will check if the OpenID URL supplied is a valid URL or not.
 * OpenID is stored in $_SESSION['openid_url'] for later use.
 * It Uses the Dope_OpenID class found in cms/openid/.
 * After the Endpoint URL has being found out, this function redirects
 * the user to the OpenID provider's website for authentication
 * @param $openid_url The OpenID of the user as string.
 * @return Nothing
 */
function openid_endpoint($openid_url){
    
  /**
   * If running PHP 5, use the built-in URL validator.
   * Else use something like the following regex to validate input.
   */
if(function_exists('filter_input')) {
    if( ! filter_input(INPUT_POST, "openid_identifier", FILTER_VALIDATE_URL)) {
      $error = "Error: OpenID Identifier is not in proper format.";
    }
  }
  else 
    {
      // Found this on Google. Seems to match most valid URLs. Feel free to modify or replace.
      if( ! eregi("^((https?)://)?(((www\.)?[^ ]+\.[com|org|net|edu|gov|us]))([^ ]+)?$",$openid_url)) {
	$error = "Error: OpenID Identifier is not in proper format.";
      }
    }	
  /// Proceed if we made it through without setting $error
  if ( ! isset($error)) {
    /**
     * Store the user's submitted OpenID Identity for later use.
     */
    $_SESSION['openid_url'] = $openid_url;

    /**
     * Create a new Dope_OpenID object
     */
    $openid = new Dope_OpenID($openid_url);
    /**
     * ReturnURL: The URL to which the OpenID provider should return the user to,
     * after the authentication has been done.
     * This Line might require editing:
     * The user's OpenID provider will return them to the URL that you provide here.
     */
    global $rewriteEngineEnabled;

    ///if rewriteEngine is enabled, then write explicit name index.php (direct filename are saved from being processed by rewrite engine)
    ///since rewriteEngine is poorly coded. It doesn't allow longer GET queries.
    ///if rewriteEngine is off, we can remove the index.php part to make the url look non-php
    if($rewriteEngineEnabled=='true')
      $returnURL="http://".$_SERVER['HTTP_HOST'].dirname(isset($_SERVER['ORIG_SCRIPT_NAME'])?$_SERVER['ORIG_SCRIPT_NAME']:$_SERVER['SCRIPT_NAME'])."/index.php?action=login&subaction=openid_verify";
    else
      $returnURL="http://".$_SERVER['HTTP_HOST'].dirname(isset($_SERVER['ORIG_SCRIPT_NAME'])?$_SERVER['ORIG_SCRIPT_NAME']:$_SERVER['SCRIPT_NAME'])."/?action=login&subaction=openid_verify";

    $openid->setReturnURL($returnURL);

    /**
     * TrustRoot: The URL to which your user would be asked to trust. This is
     * usually the parent directory of ReturnURL
     * Set the trust root. This is the URL or set of URLs the user will be asked
     * to trust when signing in with their OpenID Provider. It could be your base
     * URL or a subdirectory thereof. Up to you.
     */
    $openid->SetTrustRoot("http://".$_SERVER['HTTP_HOST'].dirname(isset($_SERVER['ORIG_SCRIPT_NAME'])?$_SERVER['ORIG_SCRIPT_NAME']:$_SERVER['SCRIPT_NAME']));
    
    /**
     * OptionalInfo: The information you  need to fetch form the Provider
     * When the user signs in with their OpenID Provider, these are
     * the details you would like sent back for your own use.
     * Dope OpenID attempts to get this information using both Simple Registration
     * and Attribute Exchange protocols. The type that is returned depends on the
     * user's Provider. Each provider chooses what they wish to provide and all 
     * defined attributes may not be available. To see where these two types of
     *  attributes intersect, see the following: http://www.axschema.org/types/
     */
    $openid->setOptionalInfo(array('nickname','fullname','email'));
		
    		
    /**
     * EDIT THIS LINE (OPTIONAL)
     * PAPE Policies help protect users and you against phishing and other authentication
     * forgeries. It's an optional extension, so not all OpenID Providers will be using it.
     * Uncomment to use it.
     * More info and possible policy values here: http://openid.net/specs/openid-provider-authentication-policy-extension-1_0-01.html
     */
    //$openid->setPapePolicies('http://schemas.openid.net/pape/policies/2007/06/phishing-resistant ');
		
    /**
     * EDIT THIS LINE (OPTIONAL)
     * Also part of the PAPE extension, you can set a time limit for users to
     * authenticate themselves with their OpenID Provider. If it takes too long,
     * authentication will fail and the user will not be allowed access to your site.
     * Uncomment and set a value in seconds to use.
     */
    //$openid->setPapeMaxAuthAge(120);
		
   
    /// Attempt to discover the user's OpenID provider endpoint
    
    $endpoint_url = $openid->getOpenIDEndpoint();
    if($endpoint_url){
      /// If we find the endpoint, you might want to store it for later use.
      $_SESSION['openid_endpoint_url'] = $endpoint_url;
      /// Redirect the user to their OpenID Provider
      $openid->redirect();
      /// Call exit so the script stops executing while we wait to redirect.
      exit;
    }
    else{
      /**
       * Else we couldn't find an OpenID Provider endpoint for the user.
       * You can report this error any way you like. but just for demonstration
       * purposes we'll get the error as reported by Dope OpenID. It will be
       * displayed farther down in this file with the HTML.
       */
      $the_error = $openid->getError();
      $error = "Error Code: {$the_error['code']}<br />";
      $error .= "Error Description: {$the_error['description']}<br />";
    }
  }
  
}
/**
 * Performs the actual openid login once the authentication has been confirmed
 * from the Provider.
 * Basically deals with four cases:
 * 1. The user has used this OpenID before:
 *       This means that this OpenID entry is there in the _openid_users table
 *       and thus the user has previously used this OpenID before.
 *       In such case, the authentication is done and the user logs in.
 * 2. When the OpenID provider didn't returned the user's email address:
 *       We currently do not support such OpenID provider, and thus an
 *       error message is recieved by the user.
 * 3. When OpenID provider returns an Email which is already there in our records:
 *       This means that the user of this OpenID is already being registered also
 *       as a normal Pragyan User (or other OpenID user). The main thing is that 
 *       the there is an entry for this particular EmailID in _users table.
 *       When this happens, user is asked to give the password of the pre-existing
 *       account at the PragyanCMS so that it can be linked to this OpenID
 * @todo Check what happen if the entry in _users is because of another OpenID entry
 *       and not because of a Pragyan user. I suspect that the code will still ask 
 *       for the password (which it shouldn't). The code shouldn't check Pre-existing
 *       email ID for those entries which have login_method as openid.
 * 4. When OpenID proovider returns an Email which is not there in our records:
 *       In this case, the system demands the user to give their full name and thus
 *       it registers themselves as a dummy openid user in _users (with login_method
 *       = openid) and create entries in _openid_users too. After this, the user
 *       can start using his account.
 *
 * @param $userdata user information returned by the OpenID provider. Can be fetched
 *        by the ->filteruserinfo() function in DopeOpenID class
 */
function openid_login($userdata){
  $userdata['openid_url']=escape($_GET['openid_identity']);
  /// Build a query to check if the OpenID already exits in openid_users table
  $query="SELECT * FROM `" . MYSQL_DATABASE_PREFIX . "openid_users` WHERE `openid_url` = '". $userdata['openid_url'] . "';";

  $result=mysql_query($query) or die(mysql_error(). " in openid_login() inside login.lib.php while executing query for openid_row");
  $openid_row=mysql_fetch_array($result);
  if($openid_row)
    { ///the record exists, this user has already used his OpenID before
      //print_r($row);
      ///Fetch the user_id that corresponds to user_id in the _users table
      $userid=$openid_row['user_id'];
      
    
        ///the OpenID provider did sent us the email of the user. Check if it exists in our database and is activated
	$userdetails = getUserInfo(getUserEmail($userid));
	
	if(!$userdetails)
	{
		displayerror("Your openid registration is corrupted. Please contact site administrator.");
		return;
	}
	/// ASSUMPTION : the `user_activated' column in _users table is 1 if and only if his email is verified.
	if($userdetails && ($userdetails['user_activated']==0))
	{
			displayerror("Your account is not activated. Please verify your account using the email sent to you during registration or contact site administrator.");
			return;
	}
    
      ///Assign the value to $_SESSION['last_to_last_login_datetime']
      $query = "SELECT `user_lastlogin` FROM `". MYSQL_DATABASE_PREFIX .  "users` WHERE `user_id`='".$openid_row['user_id']. "';";
      $result=mysql_query($query) or die(mysql_error(). " in openid_login() inside login.lib.php while trying to fetch last login");
      $last_login_row=mysql_fetch_array($result);
      $_SESSION['last_to_last_login_datetime']=$last_login_row['user_lastlogin'];
      
      ///update the last login
      $query = "UPDATE `" . MYSQL_DATABASE_PREFIX . "users` SET `user_lastlogin`=NOW() WHERE `" . MYSQL_DATABASE_PREFIX . "users`.`user_id` ='". $openid_row['user_id']. "';" ;
      mysql_query($query) or die(mysql_error() . " in openid_login() inside login.lib.php while trying to update the last login");
      ///logging in the user
      setAuth($openid_row['user_id']);
					
      return $openid_row['user_id'];
		
    }
  else
    {
      /**This user is first time using the OpenID
       * display a small form to input User's Details
       * System should now check if the email ID is provided by the openID provider is already there in Our records.
       * If yes, the current account should be linked up to the account in the database after accepting the password.
       * Else, User should provide few details about him/her like Full name, and Email. Now after he provides the email,
       * The System again has to check if the email is under records and if it is, ask the password from user to link
       * it else, just make a new user in table _users
      */

      //Save the OpenID url first in Session
      $_SESSION['openid_url']=$userdata['openid_url'];
      $_SESSION['openid_email']=$userdata['email'];
      if(array_key_exists('email',$userdata))
	{
	  ///the OpenID provider did sent us the email of the user. Check if it exists in our database and is activated
	  $userdetails = getUserInfo($userdata['email']);
	  $userid= $userdetails['user_id'];
	  /// ASSUMPTION : the `user_activated' column in _users table is 1 if and only if his email is verified.
	  if($userdetails && ($userdetails['user_activated']==0))
		{
			displayerror("Your account is not activated. Please verify your account using the email sent to you during registration or contact site administrator.");
			return;
		}
	  if($userdetails && $userdetails['user_activated'] && ($userdetails['user_loginmethod']!='openid'))
	    {
	      ///if the Email was found in the records
	      ///Display a Form to capture the Password and connect it 
	      $username=getUserName($userid);
	      displayinfo("<ul><li>An account with your Email was found in our record already. This mean you are already registered as a user.</li>".
			  "<li>You just need to provide your password of your existing account to link your OpenID with.</li>".
			  "<li> This is a one time step after which you can use your OpenID account to Login.</li></ul>");
	      $cmstitle=CMS_TITLE;
	       $openid_pass_form=<<<OPENIDPASS
		
	<form method="POST" class="registrationform" name="openid_pass"  action="./home/+login&subaction=openid_pass">
		<fieldset>
		 <legend>Password for the existing account </legend>
					    Please Enter the Password of the pre-existing account on $cmstitle
		<input type="hidden" name="email" value="${userdata['email']}" />														      
        <table>

<tr><td>Username</td>

<td>$username</td></tr>

<tr><td>Email</td>
<td>${userdata['email']}</td></tr>
 <tr><td><label for="user_password" class="labelrequired">Password</label></td>
				      <td><input type="password" name="user_password"  id="user_password"  class="required" /><br /></td>
				      </tr>
				      <tr>
				      <td><input type="submit" value="Submit" /></td>
				      
                                                                            </tr>
                                                                            </table>
                                                                            </fieldset>
                                                                            </form>
OPENIDPASS;
	      return $openid_pass_form;

	    }

	  else
	    {
	      /**
	       * User have not used this OpenID before. The EmailID returned wasn't found in our records. Hence
	       * now we will have to get the full name of the user and then create a dummy user in _users table
	       * with the login_method as `openid'. Then we also have to make entries in the _openid_users table
	       * and add the user there appropriately.
	      */
	      displayinfo("Seems like you are using this OpenID for the first time. We just need your full name to continue.");
	      $openid_detail_form=<<<OPENIDFORM
	<form method="POST" class="registrationform" name="quick_openid_reg"  action="./home/+login&subaction=quick_openid_reg">
	<fieldset>
	<legend>Just give us your Full name</legend>
	<table>
	<tr>
	<td><label for="user_email"  class="labelrequired">Email</label></td>
        <td><input type="text" name="user_email" value="${userdata['email']}"  id="user_email" class="required" readonly="true" onchange="if(this.length!=0) return checkEmail(this);"/><br /></td>
        </tr>

	<tr>
	<td><label for="user_name">Full Name</label></td>
        <td><input type="text" name="user_name" value="${userdata['fullname']}"  id="user_name" class="required"/><br /></td>
        </tr>

        <tr>
        <td><input type="submit" value="Submit" /></td>
        
        </tr>
    
        </table>
        </fieldset>
        </form>
OPENIDFORM;
	            return $openid_detail_form;
	    
	   
	    
	    }
	}
      else
	{
	  /**
	     The OpenID provider didn't sent us the Email. Tell the user that he can't authenticate using such providers
	  */
	  displayerror("The OpenID provider didn't return your Email Address. Please configure your Provider to provide your Email address");
	  return;
	}
      
				     
      
    }
}

function loginForm($allow_login=1)
{
  global $urlRequestRoot;
  global $cmsFolder;
  $openidFolder=$urlRequestRoot.'/'.$cmsFolder.'/openid';
	$openid_login_str =<<<OPENIDLOGIN

        <!-- Simple OpenID Selector -->
        <link rel="stylesheet" href="$openidFolder/css/openid.css" />
 
        <script type="text/javascript" src="$openidFolder/js/openid-jquery.js.php?imgpath=$openidFolder/images/"></script>
        <script type="text/javascript">
        $(document).ready(function() {
            openid.init('openid_identifier');
        });
        </script>
        <!-- /Simple OpenID Selector -->

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

<fieldset>
<legend>Login With your OpenID</legend>
<!-- Simple OpenID Selector -->
<form action="./+login&subaction=openid_login" method="post" id="openid_form">
        <input type="hidden" name="process" value="1" />
        
			   <p> Sign-in using your existing account on popular websites
<br>Please click your account provider:</p>

                <div id="openid_choice">
    
                        <div id="openid_btns"></div>
                        </div>
                        
                        <div id="openid_input_area">
                                <input id="openid_identifier" name="openid_identifier" type="text" value="http://" />
                                <br/>
                                <input id="openid_submit" type="submit" value="Sign-In"/>
                        </div>
                        <noscript>
                        <p>OpenID is service that allows you to log-on to many different websites using a single
 indentity.
                        Find out <a href="http://openid.net/what/">more about OpenID</a> and <a href="http://openid.net/get/">how to get an OpenID enabled account</a>.</p>
                        </noscript>
        
</form>
<!-- /Simple OpenID Selector -->
</fieldset>
OPENIDLOGIN;
	$login_str=<<<LOGIN
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
					<form method="POST" class="registrationform" name="user_loginform" id="pragyan_loginform" onsubmit="return checkLoginForm(this);" action="./+login">
						<fieldset>
						<legend>Login</legend>
							<table cellspacing=0 cellpadding=0>
								<tr>
									<td><label for="user_email"  class="labelrequired">Email</label></td>
									<td><input type="text" name="user_email" id="user_email" class="required" onchange="if(this.length!=0) return checkEmail(this);"/><br /></td>
								</tr>
								<tr><td><label for="user_password" class="labelrequired">Password</label></td>
									<td><input type="password" name="user_password"  id="user_password"  class="required" /><br /></td>
								</tr>
								<tr>
									<td><input type="submit" value="Login" /></td>
									<td><a href="./+login&subaction=resetPasswd">Lost Password?</a> 
LOGIN;
	if($allow_login)
		$login_str .= "<a href=\"./+login&subaction=register\">Sign Up</a>";
		$login_str .= "</td>
								</tr>
							</table>
						</fieldset>
					</form>";
	global $openid_enabled;
	if($openid_enabled=='true')
	  return $openid_login_str.$login_str;
	else
	  return $login_str;
}

/** Undocumented Function.
 * Basically performs the whole login routine
 * @todo Document it
 */
function login() {
  $allow_login_query = "SELECT `value` FROM `".MYSQL_DATABASE_PREFIX."global` WHERE `attribute` = 'allow_login'";
  $allow_login_result = mysql_query($allow_login_query);
  $allow_login_result = mysql_fetch_array($allow_login_result);
  if(isset($_GET['subaction'])) {
    if($_GET['subaction']=="resetPasswd") {
      return resetPasswd($allow_login_result[0]);
    }
   if($allow_login_result[0])
    if($_GET['subaction']=="register") {
      require_once("registration.lib.php");
      return register();
    }
    global $openid_enabled;
    if(($openid_enabled=='true')&&($allow_login_result[0])){
      if($_GET['subaction']=="openid_login")
	{
	  if(isset($_POST['process']))
	    {
	      $openid_url = trim($_POST['openid_identifier']);
	      openid_endpoint($openid_url);
	    }
	}
      if($_GET['subaction']=="openid_verify"){
	if($_GET['openid_mode'] != "cancel")
	  {
	  
	    $openid_url = $_GET['openid_identity'];             // Get the user's OpenID Identity as returned to us from the OpenID Provider
	    $openid = new Dope_OpenID($openid_url);	          //Create a new Dope_OpenID object.
	    $validate_result = $openid->validateWithServer();   //validate to see if everything was recieved properly
	    if ($validate_result === TRUE) {
	      $userinfo = $openid->filterUserInfo($_GET);
	      return openid_login($userinfo);
	    }
	    else if ($openid->isError() === TRUE){// Else if you're here, there was some sort of error during processing.
	      $the_error = $openid->getError();
	      $error = "Error Code: {$the_error['code']}<br />";
	      $error .= "Error Description: {$the_error['description']}<br />";
	    }
	    else{//Else validation with the server failed for some reason.
	      $error = "Error: Could not validate the OpenID at {$_SESSION['openid_url']}";
	    }
	  }
	else //cancelled
	  {
	    displayerror("User cancelled the OpenID authorization");
	  }
      }
      if($_GET['subaction']=="openid_pass")
	{
	  if(!isset($_SESSION['openid_url']) || !isset($_SESSION['openid_email']))
	    {
	      displayerror("You are trying to link an OpenID account without validating your log-in. Please <a href=\"./+login\">Login</a> with your OpenID account first.");
	      return;
	    }
	  else
	    {
	      $openid_url=$_SESSION['openid_url'];
	      $openid_email=$_SESSION['openid_email'];
	      unset($_SESSION['openid_url']);
	      unset($_SESSION['openid_email']);
	      if(!isset($_POST['user_password']))
		{
		  displayerror("Empty Passwords not allowed");
		  return;
		}
	      $user_passwd=$_POST['user_password'];
	      $info=getUserInfo($openid_email);
	      if(!$info)
		{
		  displayerror("No user with Email $openid_email");
		}
	      else
		{
		  $check=checkLogin($info['user_loginmethod'],$info['user_name'],$openid_email,$user_passwd);
		  if($check)
		    {
		      //Password was correct. Link the account
		      $query="INSERT INTO `" . MYSQL_DATABASE_PREFIX ."openid_users` (`openid_url`,`user_id`) VALUES ('$openid_url',".$info['user_id'].")";
		      $result=mysql_query($query) or die(mysql_error()." in login() subaction=openid_pass while trying to Link OpenID account");
		      if($result)
			{
			  displayinfo("Account successfully Linked. Log In one more time to continue.");
			}
		    }
		  else
		    {
		      displayerror("The password you specified was incorrect");
		    }
				  
		}
	    }
	}
      if($_GET['subaction']=="quick_openid_reg")
	{
	  if(!isset($_SESSION['openid_url']) || !isset($_SESSION['openid_email']))
	    {
	      displayerror("You are trying to register an OpenID account without validating your log-in. Please <a href=\"./+login\">Login</a> with your OpenID account first.");
	      return;
	    }
	  else
	    {
	      $openid_url=$_SESSION['openid_url'];
	      $openid_email=$_SESSION['openid_email'];
	      unset($_SESSION['openid_url']);
	      unset($_SESSION['openid_email']);
	      if(!isset($_POST['user_name']) || $_POST['user_name']=="")
		{
		  displayerror("You didn't specified your Full name. Please <a href=\"./+login\">Login</a> again.");
		  return ;
		}
	      $openid_fname=escape($_POST['user_name']);
	      //Now let's start making the dummy user
	      $query = "INSERT INTO `" . MYSQL_DATABASE_PREFIX . "users` " ."(`user_name`, `user_email`, `user_fullname`, `user_password`, `user_activated`,`user_loginmethod`) ".
		"VALUES ('".$openid_email."', '".$openid_email."','".$openid_fname."','0',1,'openid');";	    
	      $result=mysql_query($query) or die(mysql_error()." in login() subaction=quick_openid_reg while trying to insert information of new account");
	      if($result)
		{
		  $id=mysql_insert_id();
		  $query="INSERT INTO `" . MYSQL_DATABASE_PREFIX ."openid_users` (`openid_url`,`user_id`) VALUES ('$openid_url',".$id.")";
		  $result=mysql_query($query) or die(mysql_error()." in login() subaction=quick_openid_reg while trying to Link OpenID account");
		  if($result)
		    {
		      displayinfo("Account successfully registered. You can now login via OpenID. Please complete your profile information after logging in.");
		    }

		}
	    
	      return "";
	      
	    }
	}
    }
  }

  if (!isset ($_POST['user_email'])) {
    return loginForm($allow_login_result[0]);
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
      if ((($_POST['user_email']) == "") || (($_POST['user_password']) == "")){
	displayerror("Blank e-mail or password NOT allowed. <br /><input type=\"button\" onclick=\"history.go(-1)\" value=\"Go back\" />");
      	return loginForm($allow_login_result[0]);
	}
	else {
	$user_email = escape($_POST['user_email']);
	$user_passwd = escape($_POST['user_password']);
	$login_method = '';
	if(!check_email($user_email))
		{
		displayerror("Your E-Mail Provider has been blackilisted. Please contact the website administrator");
		return loginForm($allow_login_result[0]);
		}				
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
	    $query = "UPDATE `" . MYSQL_DATABASE_PREFIX . "users` SET `user_lastlogin`=NOW() WHERE `" . MYSQL_DATABASE_PREFIX . "users`.`user_id` ='$temp[user_id]'";
	    mysql_query($query) or die(mysql_error() . " in login.lib.L:111");
	    $_SESSION['last_to_last_login_datetime']=$temp['user_lastlogin'];
	    setAuth($temp['user_id']);
							
	    //exit();
	    //displayinfo("Welcome " . $temp['user_name'] . "!");
	    return $temp['user_id'];
	  }
	}
	else {
	  displaywarning("Wrong E-mail or password. <a href='./+login&subaction=resetPasswd'>Lost Password?</a><br />");
		return loginForm($allow_login_result[0]);
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



