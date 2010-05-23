function validatePrRegistrationForm(formObj) {
	if(formObj.getElementById('txtUserPassword').value == '') {
		alert('The Password field is empty. Please fill in a password.');
		return false;
	}
	if(formObj.getElementById('txtUserPassword').value != formObj.getElementById('txtUserConfirmPassword').value) {
		alert('The passwords in the Password field and the Confirm Password field do not match');
		formObj.getElementById('txtUserPassword').focus();
		return false;
	}

	var re = /^[\d\s ().-]+$/;
	if(!re.test(formObj.getElementById('txtUserPhone').value)) {
		alert('The phone number entered is invalid.');
		formObj.getElementById('txtUserPhone').focus();
		return false;
	}

	if(formObj.getElementById('txtUserInstitution').value.length == 0) {
		alert('The College/Institute field cannot be left blank.');
		formObj.getElementById('txtUserInstitution').focus();
		return false;
	}

	if(!checkEmail(formObj.getElementById('txtUserEmail'))) {
		formObj.getElementById('txtUserEmail').focus();
		return false;
	}

	return true;
}
