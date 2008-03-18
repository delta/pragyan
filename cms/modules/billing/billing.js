function validateBillingForm(formObj) {
	if(formObj.optPaymentMethod[0].checked == true && formObj.txtRollNumber.value == '') {
		alert('You must provide a roll number for carrying out a transaction using messbill.');
		return false;
	}

	if(formObj.getElementById('spanTotal').innerHTML == 0) {
		alert('No items were selected for purchase.');
		return false;
	}

	return true;
}

function evaluateAmount(itemObj) {
	var trObj = itemObj;
	while(trObj.tagName.toLowerCase() != 'tr') {
		trObj = trObj.parentNode;
	}
	var itemId = itemObj.id.substr(3);

	chk = trObj.getElementById('chk' + itemId);
	sel = trObj.getElementById('sel' + itemId);
	price = trObj.getElementById('prc' + itemId);
	amt = trObj.getElementById('amt' + itemId);

	amt.innerHTML = '';
	if(chk.checked == true) {
		amt.innerHTML = 'Rs. ' + (sel.value * parseFloat(price.innerHTML));
	}

	showTotals();
}


function showTotals() {
	var itemDetails = document.getElementById('itemdetails');
	var stallwiseCosts = new Array();

	for(var i = 0; i < stallItems.length; i++) {
		var amt = itemDetails.getElementById('chk' + stallItems[i][1]).checked == true ? parseInt(itemDetails.getElementById('amt' + stallItems[i][1]).innerHTML.substr(4)) : 0;

		if(typeof stallwiseCosts[stallItems[i][0]] == 'undefined') stallwiseCosts[stallItems[i][0]] = 0;
		stallwiseCosts[stallItems[i][0]] += amt;
	}

	var totalCost = 0;
	for(i = 0; i < stallwiseCosts.length; i++) {
		document.getElementById('td' + openStalls[i]).innerHTML = '';
		document.getElementById('td' + openStalls[i]).style.padding = '0px';
		document.getElementById('td' + openStalls[i] + 'Header').innerHTML = '';
		if(stallwiseCosts[i] != 0) {
			document.getElementById('td' + openStalls[i]).innerHTML = 'Rs. ' + stallwiseCosts[i];
			document.getElementById('td' + openStalls[i]).style.padding = '4px';
			totalCost += stallwiseCosts[i];
			document.getElementById('td' + openStalls[i] + 'Header').innerHTML = openStalls[i];
		}
	}

	document.getElementById('spanTotal').innerHTML = totalCost;
}
