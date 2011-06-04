<?php
if(!defined('__PRAGYAN_CMS'))
{ 
	header($_SERVER['SERVER_PROTOCOL'].' 403 Forbidden');
	echo "<h1>403 Forbidden<h1><h4>You are not authorized to access the page.</h4>";
	echo '<hr/>'.$_SERVER['SERVER_SIGNATURE'];
	exit(1);
}
/**
*	This file is part of Dope OpenID.
*   Author: Steve Love (http://www.stevelove.org)
*   
*   Some code has been modified from Simple OpenID:
*   http://www.phpclasses.org/browse/package/3290.html
*
*   Yadis Library provided by JanRain:
*   http://www.openidenabled.com/php-openid/
*
*	Dope OpenID is free software: you can redistribute it and/or modify
*	it under the terms of the GNU General Public License as published by
*	the Free Software Foundation, either version 3 of the License, or
*	(at your option) any later version.
*
*	Dope OpenID is distributed in the hope that it will be useful,
*	but WITHOUT ANY WARRANTY; without even the implied warranty of
*	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
*	GNU General Public License for more details.
*
*	You should have received a copy of the GNU General Public License
*	along with Dope OpenID. If not, see <http://www.gnu.org/licenses/>.
**/

/*
* Example uses default PHP sessions.
* Feel free to use whatever session management you prefer.
*/
session_start();


require 'class.dopeopenid.php';
/*
* If $_POST['process'] is set, begin OpenID login form processing.
*/
//echo "hello";

function openid_endpoint($openid_url){
    
  /*
   * If running PHP 5, use the built-in URL validator.
   * Else use something like the following regex to validate input.
   */
  echo $openid_url;
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
  // Proceed if we made it through without setting $error
  if ( ! isset($error)) {
    /*
     * Store the user's submitted OpenID Identity for later use.
     */
    $_SESSION['openid_url'] = $openid_url;

    /*
     * Create a new Dope_OpenID object
     */
    $openid = new Dope_OpenID($openid_url);
    /*
     * YOU MUST EDIT THIS LINE.
     * The user's OpenID provider will return them to the URL that you provide here.
     * It could be a separate verify.php script, or just pass a parameter to tell a
     * single processing script what to do (like I've done with this file you're reading).
     */
    $openid->setReturnURL("http://".$_SERVER['HTTP_HOST'].dirname(isset($_SERVER['ORIG_SCRIPT_NAME'])?$_SERVER['ORIG_SCRIPT_NAME']:$_SERVER['SCRIPT_NAME'])."../../../index.php?action=login&subaction=openid_verify");
	
    /*
     * YOU MUST EDIT THIS LINE
     * Set the trust root. This is the URL or set of URLs the user will be asked
     * to trust when signing in with their OpenID Provider. It could be your base
     * URL or a subdirectory thereof. Up to you.
     */
    $openid->SetTrustRoot("http://".$_SERVER['HTTP_HOST'].dirname(isset($_SERVER['ORIG_SCRIPT_NAME'])?$_SERVER['ORIG_SCRIPT_NAME']:$_SERVER['SCRIPT_NAME'])."../../../");
    //            echo "http://".$_SERVER['HTTP_HOST'].dirname($_SERVER['SCRIPT_NAME'])."../../";
    //    exit;
    /*
     * EDIT THIS LINE (OPTIONAL)
     * When the user signs in with their OpenID Provider, these are
     * the details you would like sent back for your own use.
     * Dope OpenID attempts to get this information using both Simple Registration
     * and Attribute Exchange protocols. The type that is returned depends on the
     * user's Provider. Each provider chooses what they wish to provide and all 
     * defined attributes may not be available. To see where these two types of
     *  attributes intersect, see the following: http://www.axschema.org/types/
     */
    $openid->setOptionalInfo(array('nickname','fullname','email'));
		
    /*
     * EDIT THIS LINE (OPTIONAL)
     * This is the same as above, except much stricter. By using this method, you
     * are telling the OpenID Provider you *must* have this information. If the Provider
     * will not give you the information the transaction should logically fail, either 
     * at the Provider's end or yours. No info, no sign in. Uncomment to use it.
     */
    //$openid->setRequiredInfo(array('email','http://axschema.org/contact/email','contact/email'));
		
    /*
     * EDIT THIS LINE (OPTIONAL)
     * PAPE Policies help protect users and you against phishing and other authentication
     * forgeries. It's an optional extension, so not all OpenID Providers will be using it.
     * Uncomment to use it.
     * More info and possible policy values here: http://openid.net/specs/openid-provider-authentication-policy-extension-1_0-01.html
     */
    //$openid->setPapePolicies('http://schemas.openid.net/pape/policies/2007/06/phishing-resistant ');
		
    /*
     * EDIT THIS LINE (OPTIONAL)
     * Also part of the PAPE extension, you can set a time limit for users to
     * authenticate themselves with their OpenID Provider. If it takes too long,
     * authentication will fail and the user will not be allowed access to your site.
     * Uncomment and set a value in seconds to use.
     */
    //$openid->setPapeMaxAuthAge(120);
		
    /*
     * Attempt to discover the user's OpenID provider endpoint
     */
    $endpoint_url = $openid->getOpenIDEndpoint();
    if($endpoint_url){
      // If we find the endpoint, you might want to store it for later use.
      $_SESSION['openid_endpoint_url'] = $endpoint_url;
      // Redirect the user to their OpenID Provider
      $openid->redirect();
      // Call exit so the script stops executing while we wait to redirect.
      exit;
    }
    else{
      /*
       * Else we couldn't find an OpenID Provider endpoint for the user.
       * You can report this error any way you like, but just for demonstration
       * purposes we'll get the error as reported by Dope OpenID. It will be
       * displayed farther down in this file with the HTML.
       */
      $the_error = $openid->getError();
      $error = "Error Code: {$the_error['code']}<br />";
      $error .= "Error Description: {$the_error['description']}<br />";
    }
  }
  
}

 
if(isset($_POST['process']))
  {
    $openid_url = trim($_POST['openid_identifier']);
    openid_endpoint($openid_url);
  }
