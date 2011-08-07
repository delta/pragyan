<?php
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
* The Yadis library is necessary for OpenID 2.0 specifications.
* The default path assumes the library is located in the same directory
* as the Dope OpenID class file. Feel free to change the path to this 
* library if necessary.
*/
require_once 'Services/Yadis/Yadis.php';


class Dope_OpenID
{
	public $fields = array('required' => array(),'optional' => array());

	public $arr_userinfo = array();
	
	// An associative array of AX schema definitions
	private $arr_ax_types = array(
						'nickname'  => 'http://axschema.org/namePerson/friendly',
						'email'     => 'http://axschema.org/contact/email',
						'fullname'  => 'http://axschema.org/namePerson',
						'dob'       => 'http://axschema.org/birthDate',
						'gender'    => 'http://axschema.org/person/gender',
						'postcode'  => 'http://axschema.org/contact/postalCode/home',
						'country'   => 'http://axschema.org/contact/country/home',
						'language'  => 'http://axschema.org/pref/language',
						'timezone'  => 'http://axschema.org/pref/timezone',
						'prefix'    => 'http://axschema.org/namePerson/prefix',
						'firstname' => 'http://axschema.org/namePerson/first',
						'lastname'  => 'http://axschema.org/namePerson/last',
						'suffix'    => 'http://axschema.org/namePerson/suffix'
	                );
	
	private $openid_url_identity;
	private $openid_ns;
    private $openid_version;
	
	private $error = array();
	private $URLs  = array();
	
	// PHP 4 compatible constructor calls the PHP 5 constructor.
	public function Dope_OpenID($identity)
	{
		$this->__construct($identity);
	}
	
	// The PHP 5 constructor
	public function __construct($identity)
	{
		if ( ! $identity) {
			$this->errorStore('OPENID_NOIDENTITY','No identity passed to Dope OpenID constructor.');
			return FALSE;
		}
		
		// cURL is required for Dope OpenID to work.
		if ( ! function_exists('curl_exec')) {
			die('Error: Dope OpenID requires the PHP cURL extension.');
		}
		
		// Set user's identity.
		$this->setIdentity($identity);
	}
	
	public function setReturnURL($url)
	{
		$this->URLs['return'] = $url;
	}
	
	public function setTrustRoot($url)
	{
		$this->URLs['trust_root'] = $url;
	}
	
	public function setCancelURL($url)
	{
		$this->URLs['cancel'] = $url;
	}
	
	public function setRequiredInfo($fields)
	{
		if (is_array($fields)){
			$this->fields['required'] = $fields;
		}
		else {
			$this->fields['required'][] = $fields;
		}
	}
    
	public function setOptionalInfo($fields)
	{
		if (is_array($fields)) {
			$this->fields['optional'] = $fields;
		}
		else {
			$this->fields['optional'][] = $fields;
		}
	}
	
	public function setPapePolicies($policies)
	{
		if (is_array($policies)) {
			$this->fields['pape_policies'] = $policies;
		}
		else {
			$this->fields['pape_policies'][] = $policies;
		}
	}
	
	public function setPapeMaxAuthAge($seconds){
	    // Numeric value greater than or equal to zero in seconds
	    // How much time should the user be given to authenticate?
	    if(preg_match("/^[1-9]+[0-9]*$/",$seconds)){
	        $this->fields['pape_max_auth_age'] = $seconds;
	    }
	    else {
	        $this->errorStore('OPENID_MAXAUTHAGE','Max Auth Age must be a numeric value greater than or equal to zero in seconds.');
			return FALSE;
	    }
	}
	
	public function isError()
	{
		if ( ! empty($this->error)) {
			return TRUE;
		}
		else{
			return FALSE;
		}
	}
	
	public function getError()
	{
		$the_error = $this->error;
		return array('code'=>$the_error[0],'description'=>$the_error[1]);
	}
	
	/*
	* Method to discover the OpenID Provider's endpoint location
	*/
	public function getOpenIDEndpoint()
	{
		//Try Yadis Protocol discovery first
		$http_response = array();
		$fetcher = Services_Yadis_Yadis::getHTTPFetcher();
		$yadis_object = Services_Yadis_Yadis::discover($this->openid_url_identity, $http_response, $fetcher);
		
		// Yadis object is returned if discovery is successful
		if($yadis_object != NULL) {
			
			$service_list  = $yadis_object->services();
			$service_types = $service_list[0]->getTypes();
			
			$servers   = $service_list[0]->getURIs();
			$delegates = $service_list[0]->getElements('openid:Delegate');
		
		}
		// Else try HTML discovery
		else { 
			$response = $this->makeCURLRequest($this->openid_url_identity);
			list($servers, $delegates) = $this->parseHTML($response);
		}
		
		// If no servers were discovered by Yadis or by parsing HTML, error out
		if (empty($servers)){
			$this->errorStore('OPENID_NOSERVERSFOUND');
			return FALSE;
		}
		
		// If $service_type has at least one non-null character
		if (isset($service_types[0]) && ($service_types[0] != "")) {
			$this->setServiceType($service_types[0]);
		}
		
		// If $delegates has at least one non-null character
		if (isset($delegates[0]) && ($delegates[0] != "")) {
			$this->setIdentity($delegates[0]);
		}
		
		$this->setOpenIDEndpoint($servers[0]);
		
		return $servers[0];
	}
	
	/*
	* Method to redirect user to their OpenID Provider's endpoint
	*/
	public function redirect()
	{
        $redirect_to = $this->getRedirectURL();
        // If headers() have already been sent
        if (headers_sent()) {
        	// PHP header() redirect won't work if headers already sent.
        	// JavaScript redirect is pretty much only option in this case.
            echo '<script language="JavaScript" type="text/javascript">window.location=\'';
            echo $redirect_to;
            echo '\';</script>';
        }
        // Else we can use PHP header() redirect
        else {
            header('Location: ' . $redirect_to);
        }
    }
    
    /*
    * Method to validate information with the OpenID Provider
    */
    public function validateWithServer()
    {
	
		$params = array();
		
		// Find keys that include dots and store them in an array
		preg_match_all("/([\w]+[\.])/",$_GET['openid_signed'],$arr_periods);
		$arr_periods = array_unique(array_shift($arr_periods));
		
		// Duplicate the dot keys array, but replace the dot with an underscore
		$arr_underscores = preg_replace("/\./","_",$arr_periods);
		
		// The OpenID Provider returns a list of signed keys we need to validate
		$arr_get_signed_keys = explode(",",str_replace($arr_periods, $arr_underscores, $_GET['openid_signed']));
		
		// Send back only the signed keys to confirm validity
		foreach($arr_get_signed_keys as $key) {
			$paramKey = str_replace($arr_underscores, $arr_periods, $key);
			$params["openid.$paramKey"] = urlencode($_GET["openid_$key"]);
		}
		
		// If we're using OpenID 2.0 specs, we must include these values also
		if($this->openid_version != "2.0"){
			$params['openid.assoc_handle'] = urlencode($_GET['openid_assoc_handle']);
			$params['openid.signed']       = urlencode($_GET['openid_signed']);
		}
		
		$params['openid.sig']  = urlencode($_GET['openid_sig']);
		$params['openid.mode'] = "check_authentication";
		
		$endpoint_url = $this->getOpenIDEndpoint();
		
		if ($endpoint_url == FALSE) {
			return FALSE;
		}
		
		// Send the signed keys back to the OpenID Provider using cURL
		$response = $this->makeCURLRequest($endpoint_url,'POST',$params);
		$data = $this->splitResponse($response);
		
		// If the response is successful, OpenID Provider will return [is_valid => 'true']
   		if ($data['is_valid'] == "true") {
			return TRUE;
		}else{
			return FALSE;
		}
	}
	
	/*
	* Method to filter through $_GET array for requested user info.
	* TODO: Add documentation.
	*/
	public function filterUserInfo($arr_get)
	{
		foreach($arr_get as $key => $value){
			$trimmed_key = substr($key,strrpos($key,"_")+1);
			if(stristr($key, 'openid_ext1_value') && isset($value[1])) {
				$this->arr_userinfo[$trimmed_key] = $value;
			}
			if( (stristr($key, 'sreg_') || stristr($key, 'ax_value_')) &&
			    array_key_exists($trimmed_key, $this->arr_ax_types)) {
				$this->arr_userinfo[$trimmed_key] = $value;
			}
		}
		return $this->arr_userinfo;
	}
	
	/*
	* Method to set the user's OpenID identity url.
	* As of OpenID 2.0, this identifier could be an XRI Identifier
	* TODO: Add XRI support.
	*/
	private function setIdentity($identity)
	{
		/* XRI support is not ready yet.
		$xriIdentifiers = array('=', '$', '!', '@', '+');
		$xriProxy = 'http://xri.net/';

		// Is this an XRI string?
		// Check for "xri://" prefix or XRI Global Constant Symbols
		if (stripos($identity, 'xri://') OR in_array($identity[0], $xriIdentifiers)){	
			// Attempts to convert an XRI into a URI by removing the "xri://" prefix and
			// appending the remainder to the URI of an XRI proxy such as "http://xri.net"
			if (stripos($identity, 'xri://') == 0) {
				if (stripos($identity, 'xri://$ip*') == 0) {
					$identity = substr($identity, 10);
				} elseif (stripos($identity, 'xri://$dns*') == 0) {
					$identity = substr($identity, 11);
				} else {
					$identity = substr($identity, 6);
				}
			}
			$identity = $xriProxy.$identity;
		}*/
		
		// Append "http://" to the identity string if not already present.
		if ((stripos($identity, 'http://') === FALSE) && 
			(stripos($identity, 'https://') === FALSE)) {
				$identity = 'http://'.$identity;
		}
		
		// Google is not publishing its XRDS document yet, so the OpenID
		// endpoint must be set manually for now.
		if (stripos($identity, 'gmail') OR stripos($identity, 'google')) {
			$identity = "https://www.google.com/accounts/o8/id";
		}
		$this->openid_url_identity = $identity;
	}
	
	private function getIdentity()
	{ 	// Get Identity
		return $this->openid_url_identity;
	}
	
	/*
	* Method to make cURL request.
	*/
	private function makeCURLRequest($url, $method="GET", $params = "")
	{
		if (is_array($params)) {
			$params = $this->createQueryString($params);
		}
		
		$curl = curl_init($url . ($method == "GET" && $params != "" ? "?" . $params : ""));
		
		//curl_setopt($curl, CURLOPT_FOLLOWLOCATION, TRUE);
		curl_setopt($curl, CURLOPT_HEADER, FALSE);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($curl, CURLOPT_HTTPGET, ($method == "GET"));
		curl_setopt($curl, CURLOPT_POST, ($method == "POST"));
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
		if ($method == "POST") {
			curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
		}
		
		$response = curl_exec($curl);
		
		if (curl_errno($curl) == 0) {
			$response;
		}
		else {
			$this->errorStore('OPENID_CURL', curl_error($curl));
		}
		
		return $response;
	}
	
	private function parseHTML($content)
	{
		$ret = array();
		
		// Get details of their OpenID server and (optional) delegate
		preg_match_all('/<link[^>]*rel=[\'"]openid.server[\'"][^>]*href=[\'"]([^\'"]+)[\'"][^>]*\/?>/i', $content, $matches1);
		preg_match_all('/<link[^>]*rel=[\'"]openid2.provider[\'"][^>]*href=[\'"]([^\'"]+)[\'"][^>]*\/?>/i', $content, $matches2);
		preg_match_all('/<link[^>]*href=\'"([^\'"]+)[\'"][^>]*rel=[\'"]openid.server[\'"][^>]*\/?>/i', $content, $matches3);
		preg_match_all('/<link[^>]*href=\'"([^\'"]+)[\'"][^>]*rel=[\'"]openid2.provider[\'"][^>]*\/?>/i', $content, $matches4);
		
		$servers = array_merge($matches1[1], $matches2[1], $matches3[1], $matches4[1]);
		
		preg_match_all('/<link[^>]*rel=[\'"]openid.delegate[\'"][^>]*href=[\'"]([^\'"]+)[\'"][^>]*\/?>/i', $content, $matches1);
		preg_match_all('/<link[^>]*rel=[\'"]openid2.local_id[\'"][^>]*href=[\'"]([^\'"]+)[\'"][^>]*\/?>/i', $content, $matches2);
		preg_match_all('/<link[^>]*href=[\'"]([^\'"]+)[\'"][^>]*rel=[\'"]openid.delegate[\'"][^>]*\/?>/i', $content, $matches3);
		preg_match_all('/<link[^>]*href=[\'"]([^\'"]+)[\'"][^>]*rel=[\'"]openid2.local_id[\'"][^>]*\/?>/i', $content, $matches4);
		
		$delegates = array_merge($matches1[1], $matches2[1], $matches3[1], $matches4[1]);
		
		$ret = array($servers, $delegates);
		return $ret;
	}
	
	private function splitResponse($response)
	{
		$r = array();
		$response = explode("\n", $response);
		foreach($response as $line) {
			$line = trim($line);
			if ($line != "") {
				list($key, $value) = explode(":", $line, 2);
				$r[trim($key)] = trim($value);
			}
		}
	 	return $r;
	}
	
	private function createQueryString($array_params)
	{
		if ( ! is_array($array_params)) {
			return FALSE;
		}
		
		$query = "";
		
		foreach($array_params as $key => $value){
			$query .= $key . "=" . $value . "&";
		}
		
		return $query;
	}
	
	private function setOpenIDEndpoint($url)
	{
		$this->URLs['openid_server'] = $url;
	}
    
    private function setServiceType($url)
    {
        /* 
        * Hopefully the provider is using OpenID 2.0 but let's check
        * the protocol version in order to handle backwards compatibility.
        * Probably not the most efficient method, but it works for now.
        */
        if (stristr($url, "2.0")) {
            $ns = "http://specs.openid.net/auth/2.0";
            $version = "2.0";
        }
        else if (stristr($url, "1.1")) {
            $ns = "http://openid.net/signon/1.1";
            $version = "1.1";
        }
        else {
            $ns = "http://openid.net/signon/1.0";
            $version = "1.0";
        }
        $this->openid_ns      = $ns;
        $this->openid_version = $version;
    }
    
    function getRedirectURL()
    {
    	$params = array();
    	
    	$params['openid.return_to'] = urlencode($this->URLs['return']);
    	$params['openid.identity']  = urlencode($this->openid_url_identity);
    	
    	if($this->openid_version == "2.0"){
    		$params['openid.ns']         = urlencode($this->openid_ns);
    		$params['openid.claimed_id'] = urlencode("http://specs.openid.net/auth/2.0/identifier_select");
    		$params['openid.identity']   = urlencode("http://specs.openid.net/auth/2.0/identifier_select");
    		$params['openid.realm']      = urlencode($this->URLs['trust_root']);
    	}
    	else {
    		$params['openid.trust_root'] = urlencode($this->URLs['trust_root']);
    	}
    	
    	$params['openid.mode'] = 'checkid_setup';
    	
    	/**
    	* Handle user attribute requests.
    	*/
		$info_request = FALSE;
    	
    	// User Info Request: Setup
    	if (isset($this->fields['required']) OR isset($this->fields['optional'])) {    		
    		$params['openid.ns.ax']   = "http://openid.net/srv/ax/1.0";
    		$params['openid.ax.mode'] = "fetch_request";
    		$params['openid.ns.sreg'] = "http://openid.net/extensions/sreg/1.1";

    		$info_request = TRUE;
    	}
    	
    	// MyOpenID.com is using an outdated AX schema URI
    	if (stristr($this->URLs['openid_server'], 'myopenid.com') && $info_request == TRUE) {
    		$this->arr_ax_types = preg_replace("/axschema.org/","schema.openid.net",$this->arr_ax_types);
    	}
    	
    	// If we're requesting user info from Google, it MUST be specified as "required"
    	// Will not work otherwise.
    	if (stristr($this->URLs['openid_server'], 'google.com') && $info_request == TRUE) {
    		$this->fields['required'] = array_unique(array_merge($this->fields['optional'], $this->fields['required']));
    		$this->fields['optional'] = array();
    	}
    	
    	// User Info Request: Required data
    	if (isset($this->fields['required']) && ( ! empty($this->fields['required']))) {
    		
    		// Set required params for Attribute Exchange (AX) protocol
    		$params['openid.ax.required']   = implode(',',$this->fields['required']);
    		
    		foreach($this->fields['required'] as $field) {
    			if(array_key_exists($field,$this->arr_ax_types)) {
    				$params["openid.ax.type.$field"] = urlencode($this->arr_ax_types[$field]);
    			}
    		}
    		
    		// Set required params for Simple Registration (SREG) protocol
    		$params['openid.sreg.required'] = implode(',',$this->fields['required']);
    	}
    	
    	// User Info Request: Optional data
    	if (isset($this->fields['optional']) && ( ! empty($this->fields['optional']))) {
    		// Set optional params for Attribute Exchange (AX) protocol
    		$params['openid.ax.if_available'] = implode(',',$this->fields['optional']);
    		
    		foreach($this->fields['optional'] as $field) {
    			if(array_key_exists($field,$this->arr_ax_types)) {
    				$params["openid.ax.type.$field"] = urlencode($this->arr_ax_types[$field]);
    			}
    		}
    		// Set optional params for Simple Registration (SREG) protocol
    		$params['openid.sreg.optional'] = implode(',',$this->fields['optional']);
    	}
    	
    	// Add PAPE params if exists
    	if (isset($this->fields['pape_policies']) && ( ! empty($this->fields['pape_policies']))) {
    		$params['openid.ns.pape'] = "http://specs.openid.net/extensions/pape/1.0";
    		$params['openid.pape.preferred_auth_policies'] = urlencode(implode(' ',$this->fields['pape_policies']));
    		
    		if($this->fields['pape_max_auth_age']) {
    			$params['openid.pape.max_auth_age'] = $this->fields['pape_max_auth_age'];
    		}
    	}
    	
    	$urlJoiner = (strstr($this->URLs['openid_server'], "?")) ? "&" : "?";
    	
    	return $this->URLs['openid_server'] . $urlJoiner . $this->createQueryString($params);
    	
    }
	
	private function errorStore($code, $desc = 	NULL)
	{
		$errs['OPENID_NOSERVERSFOUND'] = 'Cannot find OpenID Server TAG on Identity page.';
		
		if ($desc == NULL){
			$desc = $errs[$code];
		}
		
		$this->error = array($code,$desc);
	}
}

?>
