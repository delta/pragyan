<?php
/*
 * Created on Sep 28, 2008
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */

$settingsForm = <<<SETTINGSFORM
<form name="SettingsForm" method="POST" action="./install.php">
<script type="text/javascript" language="javascript">
	function toggleImap(b) {
		document.getElementById('txtIMAPServerAddress').disabled =
		document.getElementById('txtIMAPPort').disabled =
		document.getElementById('txtIMAPUserDomain').disabled = b;
	}

	function toggleLdap(b) {
		document.getElementById('txtLDAPServerAddress').disabled =
		document.getElementById('txtLDAPSearchGroup').disabled = 
		document.getElementById('txtLDAPUserDomain').disabled = b;
	}

	function toggleAds(b) {
		document.getElementById('txtADSServerAddress').disabled =
		document.getElementById('txtADSNetworkName').disabled =
		document.getElementById('txtADSUserDomain').disabled = b;
	}
	function checkForm()
	{
		if(document.getElementById('txtAdminPassword').value!=document.getElementById('txtAdminPassword2').value)
		{
			alert("Administrator Passwords do not match");
			return false;
		}
		return true;
	}

</script>

<fieldset name="DatabaseSettings">
	<legend>Database Settings</legend>
	<table border="0" width="580px">
		<tr>
			<td width="210px"><label for="txtMySQLServer">MySQL Server:</label></td>
			<td><input type="text" name="txtMySQLServer" id="txtMySQLServer" value="localhost" /></td>
		</tr>
		<tr>
			<td><label for="txtMySQLUsername">Username:</label></td>
			<td><input type="text" name="txtMySQLUsername" id="txtMySQLUsername" value="" /></td>
		</tr>
		<tr>
			<td><label for="txtMySQLPassword">Password:</label></td>
			<td><input type="password" name="txtMySQLPassword" id="txtMySQLPassword" value="" /></td>
		</tr>
		<tr>
			<td><label for="txtMySQLDatabase">Database:</label></td>
			<td><input type="text" name="txtMySQLDatabase" id="txtMySQLDatabase" value="" /></td>
		</tr>
		<tr>
			<td><label for="txtMySQLTablePrefix">Table Prefix:</label></td>
			<td><input type="text" name="txtMySQLTablePrefix" id="txtMySQLTablePrefix" value="pragyanV3_" /></td>
		</tr>
	</table>
</fieldset>

<fieldset name="AdminUser">
	<legend>Administrator Detailss</legend>
	<table border="0" width="580px">
		<tr>
			<td width="210px"><label for="txtAdminUsername">Administrator Username:</label></td>
			<td><input type="text" name="txtAdminUsername" id="txtAdminUsername" value="admin" /></td>
		</tr>
		<tr>
			<td><label for="txtAdminEmail">Administrator Email:</label></td>
			<td><input type="text" name="txtAdminEmail" id="txtAdminEmail" value="" /></td>
		</tr>
		<tr>
			<td><label for="txtAdminFullname">Administrator Full Name:</label></td>
			<td><input type="text" name="txtAdminFullname" id="txtAdminFullname" value="" /></td>
		</tr>
		<tr>
			<td><label for="txtAdminPassword">Administrator Password:</label></td>
			<td><input type="password" name="txtAdminPassword" id="txtAdminPassword" value="" /></td>
		</tr>
		<tr>
			<td><label for="txtAdminPassword2">Administrator Password (Verify):</label></td>
			<td><input type="password" name="txtAdminPassword2" id="txtAdminPassword2" value="" /></td>
		</tr>
	</table>
</fieldset>

<fieldset name="CMSSettings">
	<legend>CMS Settings</legend>

	<table border="0" width="580px">
		<tr>
			<td width="210px"><label for="txtCMSTitle">Website Title:</label></td>
			<td><input type="text" name="txtCMSTitle" id="txtCMSTitle" value="Pragyan CMS" /></td>
		</tr>
		<tr>
			<td><label for="selTemplate">Template:</label></td>
			<td>
				<select name="selTemplate" id="selTemplate">
					<option selected="selected" value="crystalx">Crystal X</option>
					<option value="blacksilver">Black Silver</option>
				</select>
			</td>
		</tr>
		<tr>
			<td>Activate user on registration?</td>
			<td>
				<label><input type="radio" name="optDefaultUserActive" id="optDefaultUserActiveYes" value="Yes" />Yes</label>
				<label><input type="radio" name="optDefaultUserActive" id="optDefaultUserActiveNo" value="No" checked="checked" />No</label>
			</td>
		</tr>
		<tr>
			<td>Send mail on registration?</td>
			<td>
				<label><input type="radio" name="optSendVerification" id="optSendVerificationYes" value="Yes" />Yes</label>
				<label><input type="radio" name="optSendVerification" id="optSendVerificationNo" value="No" checked="checked" />No</label>
			</td>
		</tr>
		<tr>
			<td><label for="txtCMSMailId">Website Email Id:</label></td>
			<td><input type="text" name="txtCMSMailId" id="txtCMSMailId" value="no-reply@pragyan.org" /></td> 
		</tr>
		<tr>
			<td><label for="txtUploadLimit">Upload Limit (bytes):</label></td>
			<td><input type="text" name="txtUploadLimit" id="txtUploadLimit" value="50000000" /></td>
		</tr>
		
		<tr>
			<td><label for="txtCookieTimeout">Cookie Timeout (seconds):</label></td>
			<td><input type="text" name="txtCookieTimeout" id="txtCookieTimeout" value="60 * 30" /></td>
		</tr>
		<tr>
			<td><label for="selErrorReporting">Error Reporting Level:</label></td>
			<td>
				<select name="selErrorReporting" id="selErrorReporting">
					<option value="0" selected="selected">None</option>
					<option value="1">Errors and Warnings</option>
					<option value="2">Errors, Warnings, Notices, Debugging Information</option>
					<option value="3">Everything except Notices</option>
					<option value="4">Everything</option>
				</select>
			</td>
		</tr>
	</table>
</fieldset>

<fieldset name="AuthenticationSettings">
	<legend>Authentication Settings</legend>

	<table border="0" width="580px">
		<tr>
			<td width="210px">Enable IMAP Login:</td>
			<td>
				<label><input type="radio" name="optEnableIMAP" id="optEnableIMAPYes" value="Yes" onclick="toggleImap(false)"/>Yes</label>
				<label><input type="radio" name="optEnableIMAP" id="optEnableIMAPNo" value="No" onclick="toggleImap(true)" checked="checked" />No</label>
			</td>
		</tr>
		<tr>
			<td><label for="txtIMAPServerAddress">Server Address:</label></td>
			<td><input type="text" id="txtIMAPServerAddress" name="txtIMAPServerAddress" value="10.0.0.2" /></td>
		</tr>
		<tr>
			<td><label for="txtIMAPPort">Port:</label></td>
			<td><input type="text" id="txtIMAPPort" name="txtIMAPPort" value="143" /></td>
		</tr>
		<tr>
			<td><label for="txtIMAPUserDoman">User Domain:</label></td>
			<td><input type="text" id="txtIMAPUserDomain" name="txtIMAPUserDomain" value="nitt.edu" /></td>
		</tr>

		<tr>
			<td>Enable LDAP Login:</td>
			<td>
				<label><input type="radio" name="optEnableLDAP" id="optEnableLDAPYes" value="Yes" onclick="toggleLdap(false)"/>Yes</label>
				<label><input type="radio" name="optEnableLDAP" id="optEnableLDAPNo" value="No" onclick="toggleLdap(true)" checked="checked" />No</label>
			</td>
		</tr>
		<tr>
			<td><label for="txtLDAPServerAddress">Server Address:</label></td>
			<td><input type="text" id="txtLDAPServerAddress" name="txtLDAPServerAddress" value="delta.nitt.edu" /></td>
		</tr>
		<tr>
			<td><label for="txtLDAPSearchGroup">Search Group:</label></td>
			<td><input type="text" id="txtLDAPSearchGroup" name="txtLDAPSearchGroup" value="ou=Webteam,dc=delta,dc=nitt.edu" /></td>
		</tr>
		<tr>
			<td><label for="txtLDAPUserDomain">User Domain:</label></td>
			<td><input type="text" id="txtLDAPUserDomain" name="txtLDAPUserDomain" value="delta.nitt.edu" /></td>
		</tr>

		<tr>
			<td>Enable ADS Login:</td>
			<td>
				<label><input type="radio" name="optEnableADS" id="optEnableADSYes" value="Yes" onclick="toggleAds(false)" />Yes</label>
				<label><input type="radio" name="optEnableADS" id="optEnableADSNo" value="No" onclick="toggleAds(true)" checked="checked" />No</label>
			</td>
		</tr>
		<tr>
			<td><label for="txtADSServerAddress">Server Address:</label></td>
			<td><input type="text" id="txtADSServerAddress" name="txtADSServerAddress" value="10.0.0.2" /></td>
		</tr>
		<tr>
			<td><label for="txtADSNetworkName">Network Name:</label></td>
			<td><input type="text" id="txtADSNetworkName" name="txtADSNetworkName" value="NITT\\" /></td>
		</tr>
		<tr>
			<td><label for="txtADSUserDomain">User Domain:</label></td>
			<td><input type="text" id="txtADSUserDomain" name="txtADSUserDomain" value="sangam.nitt.edu" /></td>
		</tr>
	</table>
</fieldset>

<script type="text/javascript" language="javascript">
	toggleImap(true);
	toggleLdap(true);
	toggleAds(true);
</script>
<input type="submit" name="btnSubmitSettings" onclick="return checkForm();" value="Continue" />
</form>

SETTINGSFORM;

