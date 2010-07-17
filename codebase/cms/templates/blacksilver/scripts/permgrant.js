// enable the groups options, and disable the users fields when the user clicks the
// groups radio button
function enableGroups(grantForm) {
	optionButtons = document.getElementsByName('optgroup012');

	for(i = 0; i < optionButtons.length; i++) {
		optionButtons[i].disabled = false;
	}
	document.getElementById('modifiableGroupsList').disabled = !optionButtons[2].checked;
	document.getElementById('userEmail').disabled = true;
	document.getElementById('btnUserPermTable').disabled = true;
	document.getElementById('btnGroupPermTable').disabled = false;
}


// enable the users fields and disable the groups fields when the user clicks the
// users radio button
function enableUsers() {
	optionButtons = document.getElementsByName('optgroup012');
	for(i = 0; i < optionButtons.length; i++) {
		optionButtons[i].disabled = true;
	}

	document.getElementById('modifiableGroupsList').disabled = true;
	document.getElementById('userEmail').disabled = false;
	document.getElementById('btnUserPermTable').disabled = false;
	document.getElementById('btnGroupPermTable').disabled = true;
}

function checkAllPermissions(selected) {
	var inputElements = document.getElementById('grantablepermissions').getElementsByTagName('input');
	for (var i = 0; i < inputElements.length; ++i)
		if (inputElements[i].type == "checkbox")
			inputElements[i].checked = selected;
}

function toggleAllPermissions() {
	var inputElements = document.getElementById('grantablepermissions').getElementsByTagName('input');
	for (var i = 0; i < inputElements.length; ++i)
		if (inputElements[i].type == "checkbox")
			inputElements[i].checked = !inputElements[i].checked;
}