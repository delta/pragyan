function isFilled(elem, helpName) {
	if(document.getElementById(elem).value.length == 0) {
		alert("You must fill in the required field " + helpName);
		return false;
	}

	return true;
}

function isChecked(elem, helpName,numberOfFields) {
	var i;
	var checkedCount = 0;
	for(i=0;i<numberOfFields;i++)
		if(document.getElementById(elem+"_"+i).checked)
			checkedCount++;

	if(checkedCount == 0) {
		alert("You must check a required field : " + helpName);
		return false;
	}

	return true;
}

function checkUBInt(elem, val, helpName) {
	if(!isNaN(document.getElementById(elem).value)) {
		if(parseInt(document.getElementById(elem).value) >= val) {
			alert("The field " + helpName + " must be less than " + val);
			return false;
		}
		return true;
	}

	alert("The field " + helpName + " must be a number less than " + val);
	return false;
}

function checkLBInt(elem, val, helpName) {
	if(!isNaN(document.getElementById(elem).value)) {
		if(parseInt(document.getElementById(elem).value) <= val) {
			alert("The field " + helpName + " must be greater than " + val);
			return false;
		}
		return true;
	}

	alert("The field " + helpName + " must be a number greater than " + val);
	return false;
}

function checkUBDate(elem, val, fmt, helpName) {
	d1 = parseDate(document.getElementById(elem).value, fmt);
	d2 = parseDate(val, fmt);

	if(d1 == false) {
		alert("You must enter a date in the format " + fmt.replace('%Y', 'YYYY').replace('%m', 'MM').replace('%d', 'DD').replace('%H', 'HH').replace('%M', 'MM') + " in the field '" + helpName + "'");
		return false;
	}
	else if(d1 >= d2) {
		alert("You must enter a date before " + d2.toLocaleString() + " in the field '" + helpName + "'");
		return false;
	}

	return true;
}


function checkLBDate(elem, val, fmt, helpName) {
	d1 = parseDate(document.getElementById(elem).value, fmt);
	d2 = parseDate(val, fmt);

	if(d1 == false) {
		alert("You must enter a date in the format " + fmt.replace('%Y', 'YYYY').replace('%m', 'MM').replace('%d', 'DD').replace('%H', 'HH').replace('%M', 'MM') + " in the field '" + helpName + "'");
		return false;
	}
	else if(d1 <= d2) {
		alert("You must enter a date after " + d2.toLocaleString() + " in the field '" + helpName + "'");
		return false;
	}

	return true;
}

function checkInt(elem, helpName) {
	if(!isNaN(document.getElementById(elem).value)) {
		return true;
	}
	else {
		alert("The field " + helpName + " must be a number");
		return false;
	}
}





function parseDate(str, fmt) {
	var today = new Date();
	var y = 0;
	var m = -1;
	var d = 0;
	var a = str.split(/\W+/);
	var b = fmt.match(/%./g);
	var i = 0, j = 0;
	var hr = 0;
	var min = 0;

	for (i = 0; i < a.length; ++i) {
		if (!a[i])
			continue;
		switch (b[i]) {
		    case "%d":
		    case "%e":
			d = parseInt(a[i], 10);
			break;

		    case "%m":
			m = parseInt(a[i], 10) - 1;
			break;

		    case "%Y":
		    case "%y":
			y = parseInt(a[i], 10);
			(y < 100) && (y += (y > 29) ? 1900 : 2000);
			break;

		    case "%b":
		    case "%B":
			for (j = 0; j < 12; ++j) {
				if (Calendar._MN[j].substr(0, a[i].length).toLowerCase() == a[i].toLowerCase()) { m = j; break; }
			}
			break;

		    case "%H":
		    case "%I":
		    case "%k":
		    case "%l":
			hr = parseInt(a[i], 10);
			break;

		    case "%P":
		    case "%p":
			if (/pm/i.test(a[i]) && hr < 12)
				hr += 12;
			else if (/am/i.test(a[i]) && hr >= 12)
				hr -= 12;
			break;

		    case "%M":
			min = parseInt(a[i], 10);
			break;
		}
	}

	if (y != 0 && m != -1 && d != 0)
		return new Date(y, m, d, hr, min, 0);

	y = 0; m = -1; d = 0;
	for (i = 0; i < a.length; ++i) {
		if (a[i].search(/[a-zA-Z]+/) != -1) {
			var t = -1;
			for (j = 0; j < 12; ++j) {
				if (Calendar._MN[j].substr(0, a[i].length).toLowerCase() == a[i].toLowerCase()) { t = j; break; }
			}
			if (t != -1) {
				if (m != -1) {
					d = m+1;
				}
				m = t;
			}
		} else if (parseInt(a[i], 10) <= 12 && m == -1) {
			m = a[i]-1;
		} else if (parseInt(a[i], 10) > 31 && y == 0) {
			y = parseInt(a[i], 10);
			(y < 100) && (y += (y > 29) ? 1900 : 2000);
		} else if (d == 0) {
			d = a[i];
		}
	}

	if (m != -1 && d != 0 && y != 0)
		return new Date(y, m, d, hr, min, 0);

	return false;
}
