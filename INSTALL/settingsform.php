<?php
/*
 * Created on Sep 28, 2008
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */


$settingsForm = <<<SETTINGSFORM
<script type="text/javascript" language="javascript">
	var error=0;
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
	
	function trim(str)
	{
	  return str.replace(/^\s+|\s+$/g, '');
	}		
	function validate_domain(field)
	{
		error=0;
		var val = field.value;
		var check = /^(\d{1,3})\.(\d{1,3})\.(\d{1,3})\.(\d{1,3})$/; //IP Address
		var check2 = /^[A-Za-z\.\s]*$/; //Domain Name
		if(val.length != 0)
		{
			if(!check.test(val) && !check2.test(val))
			{
			error=1;
			
			alert("Enter a valid server address !");
			field.focus();
			field.select();
			}
		}
	}
	
	function validate_name(field)
	{
		error=0;
		var val = trim(field.value);
		var check = /^[A-Za-z\.\s]*$/;
	
		if(val.length != 0)
		{	
			if(!check.test(val))
			{
				error=1;
				alert("Enter a valid name !");
				field.focus();
				field.select();
			}
		}
	}

	function validate_num(field)
	{
		error=0;
		var val = trim(field.value);
		var check = /^\d*$/;
	
		if(val.length != 0)
		{	
			if(!check.test(val))
			{
			error=1;
			alert("Enter a valid numeric value");
			field.focus();
			field.select();
			}
		}
	}

	function validate_username(field)
	{
		error=0;
		var val = trim(field.value);
		var a = val.indexOf(" ")
		var check = /^[A-Za-z\d]*$/;
	
		if(val.length != 0)
		{
			if(a != -1)
			{
				error=1;
				alert("Dont leave space in between characters !");
				field.focus();
				field.select();
			}
	
			else if(!check.test(val))
			{
				error=1;
				alert("Entry not valid ! The Username should contain only characters and numbers .");
				field.focus();
				field.select();
			}
		}
	}

	function validate_email(field)
	{
		error=0;
		var val = trim(field.value);
		var check = /^[A-Za-z0-9][a-z0-9._-]+(\.[a-z0-9_\+-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*\.([a-z]{2,4})$/;
	
		if(val.length != 0)
		{
			if(!check.test(val))
			{
			error=1;
			alert("Email Id not valid ! Check for the following : Start with Alphabets or Numbers, Special Characters allowed : '.' , '_' , '-' and Special characters should not occur in succession");
			field.focus();
			field.select();
			}
		}
	}

	function validate_port(field)
	{
		error=0;
		var val = trim(field.value);
		var check = /^\d{1,5}$/;
	
		if(val.length != 0)
		{	
			if(!check.test(val))
			{
			error=1;
			alert("Enter a valid Port number");
			field.focus();
			field.select();
			}
		}
	}

	function validate_form()
	{
		var txtMySQLServer      = trim(document.SettingsForm.txtMySQLServer.value);
		var txtMySQLUsername    = trim(document.SettingsForm.txtMySQLUsername.value);
		var txtMySQLPassword    = trim(document.SettingsForm.txtMySQLPassword.value);
		var txtMySQLDatabase    = trim(document.SettingsForm.txtMySQLDatabase.value);

		var txtAdminUsername    = trim(document.SettingsForm.txtAdminUsername.value);
		var txtAdminEmail	= trim(document.SettingsForm.txtAdminEmail.value);
		var txtAdminFullname	= trim(document.SettingsForm.txtAdminFullname.value);
		var txtAdminPassword	= trim(document.SettingsForm.txtAdminPassword.value);
		var txtAdminPassword2	= trim(document.SettingsForm.txtAdminPassword2.value);

		var txtCMSTitle	        = trim(document.SettingsForm.txtCMSTitle.value);
		var txtCMSMailId	= trim(document.SettingsForm.txtCMSMailId.value);

		var txtUploadLimit	= trim(document.SettingsForm.txtUploadLimit.value);
		var txtCookieTimeout	= trim(document.SettingsForm.txtCookieTimeout.value);

		var txtIMAPServerAddress= trim(document.SettingsForm.txtIMAPServerAddress.value);
		var txtIMAPPort	        = trim(document.SettingsForm.txtIMAPPort.value);
		var txtIMAPUserDomain	= trim(document.SettingsForm.txtIMAPUserDomain.value);

		var txtLDAPServerAddress= trim(document.SettingsForm.txtLDAPServerAddress.value);
		var txtLDAPSearchGroup  = trim(document.SettingsForm.txtLDAPSearchGroup.value);
		var txtLDAPUserDomain	= trim(document.SettingsForm.txtLDAPUserDomain.value);

		var txtADSServerAddress	= trim(document.SettingsForm.txtADSServerAddress.value);
		var txtADSNetworkName	= trim(document.SettingsForm.txtADSNetworkName.value);
		var txtADSUserDomain	= trim(document.SettingsForm.txtADSUserDomain.value);

		var empty = 0;

		if(txtADSUserDomain.length == 0) empty++;
		if(txtADSNetworkName.length == 0) empty++;
		if(txtADSServerAddress.length == 0) empty++;

		if(txtLDAPUserDomain.length == 0) empty++;
		if(txtLDAPSearchGroup.length == 0) empty++;
		if(txtLDAPServerAddress.length == 0) empty++;

		if(txtIMAPUserDomain.length == 0) empty++;
		if(txtIMAPPort.length == 0) empty++;
		if(txtIMAPServerAddress.length == 0) empty++;

		if(txtCookieTimeout.length == 0) empty++;
		if(txtUploadLimit.length == 0) empty++;

		if(txtCMSMailId.length == 0) empty++;
		if(txtCMSTitle.length == 0) empty++;

		if(txtAdminPassword2.length == 0) empty++;
		if(txtAdminPassword.length == 0) empty++;
		if(txtAdminFullname.length == 0) empty++;
		if(txtAdminEmail.length == 0) empty++;
		if(txtAdminUsername.length == 0) empty++;

		
		if(txtMySQLDatabase.length == 0) empty++;
		if(txtMySQLPassword.length == 0) empty++;
		if(txtMySQLUsername.length == 0) empty++;
		if(txtMySQLServer.length == 0) empty++;
		
	
	
		if(empty) alert("You have left "+empty+" required field(s) Empty !");
	
		document.SettingsForm.txtMySQLUsername.focus();
	
		return (empty == 0 && error==0);
	}
	function checkForm()
	{
		if(document.getElementById('txtAdminPassword').value!=document.getElementById('txtAdminPassword2').value)
		{
			alert("Administrator Passwords do not match");
			return false;
		}
		return validate_form();
	}


</script>

<form name="SettingsForm" method="POST" action="./install.php">



<fieldset name="DatabaseSettings">
	<legend>Database Settings</legend>
	<table border="0" width="580px">
		<tr>
			<td width="210px"><label for="txtMySQLServer">MySQL Server:</label></td>
			<td><input type="text" name="txtMySQLServer" id="txtMySQLServer" value="localhost" onblur="validate_domain(this)" /></td>
		</tr>
		<tr>
			<td><label for="txtMySQLUsername">Username:</label></td>
			<td><input type="text" name="txtMySQLUsername" id="txtMySQLUsername" value="" onblur="validate_username(this)" /></td>
		</tr>
		<tr>
			<td><label for="txtMySQLPassword">Password:</label></td>
			<td><input type="password" name="txtMySQLPassword" id="txtMySQLPassword" autocomplete="off" value="" /></td>
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
	<legend>Administrator Details</legend>
	<table border="0" width="580px">
		<tr>
			<td width="210px"><label for="txtAdminUsername">Administrator Username:</label></td>
			<td><input type="text" name="txtAdminUsername" id="txtAdminUsername" value="admin" onblur="validate_username(this)" /></td>
		</tr>
		<tr>
			<td><label for="txtAdminEmail">Administrator Email:</label></td>
			<td><input type="text" name="txtAdminEmail" id="txtAdminEmail" value="" onblur="validate_email(this)" /></td>
		</tr>
		<tr>
			<td><label for="txtAdminFullname">Administrator Full Name:</label></td>
			<td><input type="text" name="txtAdminFullname" id="txtAdminFullname" value="" autocomplete="off" onblur="validate_name(this)" /></td>
		</tr>
		<tr>
			<td><label for="txtAdminPassword">Administrator Password:</label></td>
			<td><input type="password" name="txtAdminPassword" id="txtAdminPassword" autocomplete="off" value="" /></td>
		</tr>
		<tr>
			<td><label for="txtAdminPassword2">Administrator Password (Verify):</label></td>
			<td><input type="password" name="txtAdminPassword2" id="txtAdminPassword2" autocomplete="off" value="" /></td>
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
			<td><input type="text" name="txtCMSMailId" id="txtCMSMailId" value="no-reply@pragyan.org" onblur="validate_email(this)" /></td> 
		</tr>
		<tr>
			<td><label for="txtUploadLimit">Upload Limit (bytes):</label></td>
			<td><input type="text" name="txtUploadLimit" id="txtUploadLimit" value="50000000" onblur="validate_num(this)" /></td>
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
			<td><input type="text" id="txtIMAPServerAddress" name="txtIMAPServerAddress" value="10.0.0.2" onblur="validate_domain(this)" /></td>
		</tr>
		<tr>
			<td><label for="txtIMAPPort">Port:</label></td>
			<td><input type="text" id="txtIMAPPort" name="txtIMAPPort" value="143" onblur="validate_port(this)" /></td>
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
			<td><input type="text" id="txtADSServerAddress" name="txtADSServerAddress" value="10.0.0.2" onblur="validate_domain(this)" /></td>
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

