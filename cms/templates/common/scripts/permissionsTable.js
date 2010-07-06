var xmlhttp;
var xmlhttp2;

function changePerm(e, permid, usergroupid, ug) {
	var permsDesc = {'Y' : 'grant', 'N' : 'deny', 'U' : 'unset'};
	var question = '';
	if(ug == 'user')
		question = 'Are you sure want to ' + permsDesc[e.value] + ' ' + permissions[permid] + ' permission for user ' + users[usergroupid] + '?';
	else
		question = 'Are you sure want to ' + permsDesc[e.value] + ' ' + permissions[permid] + ' permission for group ' + groups[usergroupid] + '?';
	if(confirm(question)) {
		xmlhttp = GetXmlHttpObject();
		if (xmlhttp == null) {
			alert ("Browser does not support HTTP Request");
			return;
		}
		var url = './+grant&doaction=changePerm&permid=' + permid + '&usergroupid=' + usergroupid + '&permtype=' + ug + '&pageid=' + pageid + '&perm=' + e.value;
		xmlhttp.onreadystatechange = stateChanged;
		xmlhttp.open("GET",url,true);
		xmlhttp.send(null);
	} else
		generateTable();
}

function stateChanged() {
	if (xmlhttp.readyState==4) {
		var response = xmlhttp.responseText;
		if(response=='1')
			cmsShow('info', 'Permission saved');
		else
			cmsShow('error', 'Some error in saving permission.');
		var url = './+grant&doaction=getpermvars&pageid=' + pageid;
		xmlhttp2 = GetXmlHttpObject();
		xmlhttp2.onreadystatechange = gotVars;
		xmlhttp2.open("GET",url,true);
		xmlhttp2.send(null);
	}
}

function gotVars() {
	if (xmlhttp2.readyState == 4) {
		var response = xmlhttp2.responseText;
		if(response.substr(0,5) == 'Error')
			cmsShow('error', 'couldn\'t get permission variables');
		else {
			eval(response);
			generateTable();
		}
	}
}

function GetXmlHttpObject() {
	if (window.XMLHttpRequest)
		return new XMLHttpRequest();
	if (window.ActiveXObject)
		return new ActiveXObject("Microsoft.XMLHTTP");
	return null;
}

function renderPermElement(active, permid, usergroupid, ug) {
	var onChangeCall = "changePerm(this, '" + permid + "', '" + usergroupid + "', '" + ug + "')";
	var ret = '<select onChange="' + onChangeCall + '">';
	var els = ['Yes', 'No', 'Unset'];
	for(var key in els) {
		selected = '';
		if(els[key] == active)
			selected = ' selected';
		ret += '<option' + selected + ' value=' + els[key][0] + '>' + els[key] + '</option>';
	}
	ret += '</select>';
	return ret;
}

function groupPerm(groupid, permid) {
	if(permGroups['Y'][groupid] && inArray(permGroups['Y'][groupid],permid))
		return 'Yes';
	else if(permGroups['N'][groupid] && inArray(permGroups['N'][groupid],permid))
		return 'No';
	return 'Unset';
}

function userPerm(userid, permid) {
	if(permUsers['Y'][userid] && inArray(permUsers['Y'][userid],permid))
		return 'Yes';
	else if(permUsers['N'][userid] && inArray(permUsers['N'][userid],permid))
		return 'No';
	return 'Unset';
}

function generateTable() {
	var table = "<table name='permtable id='permtable' class='userlisttable display'><thead><td>User/Group--></td>";
	var selPerms = selected['permissions'];
	var selGroups = selected['groups'];
	var selUsers = selected['users'];
	var backup = selected;
	for(var key in selGroups)
		table += "<td>" + groups[selGroups[key]] + "</td>";
	for(var key in selUsers)
		table += "<td>" + users[selUsers[key]] + "</td>";
	table += "</thead>";
	
	table += "<tbody>";
	for(var key in selPerms) {
		table += "<tr><td>" + permissions[selPerms[key]] + "</td>";
		for(var ikey in selGroups)
			table += "<td>" + renderPermElement(groupPerm(selGroups[ikey], selPerms[key]), selPerms[key], selGroups[ikey], 'group') + "</td>";
		for(var ikey in selUsers)
			table += "<td>" + renderPermElement(userPerm(selUsers[ikey], selPerms[key]), selPerms[key], selUsers[ikey], 'user') + "</td>";
		table += "</tr>";
	}
	table += "</tbody></table>";
	document.getElementById('permTable').innerHTML = table;
	selected = backup; // i found the selected object getting deleted lost inside the loop, when someone finds how to fix it, this backup can be avoided.
	//initSmartTable();
}

function cmsShow(type,message) {
	if(message != '')
		document.getElementById('info').innerHTML = "<div class='cms-" + type + "'>" + message + "</div>";
	else
		document.getElementById('info').innerHTML = "";
}

function showPermissions() {
	
	var message = '';
	if(selected['permissions'].length == 0)
		message += 'No permissions selected<br>';
	if(selected['groups'].length + selected['users'].length == 0)
		message += 'No user/group selected<br>';
	if(selected['groups'].length + selected['users'].length > 6)
		message += 'Number of selected user/group seems to be more, if you still want to display the table <a href="javascript:generateTable()">Click here</a>';
	if(message != '') {
		cmsShow('info',message);
		document.getElementById('permTable').innerHTML = '';
	}
	else {
		generateTable();
		cmsShow('','');
	}
}

function inArray(list, ele) {
	for(var key in list)
		if(list[key] == ele)
			return true;
	return false;
}

function removeByElement(arrayName,arrayElement) {
	for(var i=0; i<arrayName.length;i++ ) { 
		if(arrayName[i]==arrayElement)
		arrayName.splice(i,1); 
	}
}

function savelist(e) {
	class = e.className.substr(6,e.className.length-6);
	if(inArray(selected[class], e.value) != e.checked) {
		if(e.checked)
			if(selected[class])
				selected[class].push(e.value);
			else
				selected[class] = Array(e.value);
		else
			removeByElement(selected[class],e.value);
	}
}

function checkClick(e) {
	savelist(e);
	showPermissions();
}

function render(arr,class) {
	var ret = '';
	var isChecked = '';
	for(var key in arr) {
		if(arr[key].substr(0,8) == '<b><big>') {
			isChecked= '';
			if(inArray(selected[class],key))
				isChecked = ' checked';
			ret += "<tr><td><INPUT type=checkbox class='check_" + class + "' value='" + key + "' onChange='checkClick(this)'" + isChecked + ">&nbsp;&nbsp;&nbsp;" + arr[key] + "</td></tr>";
			delete arr[key];
			//unset arr[key]
		}
	}
	for(var key in arr) {
		isChecked = '';
		if(inArray(selected[class],key))
			isChecked = ' checked';
		ret += "<tr><td><INPUT type=checkbox class='check_" + class + "' value='" + key + "' onChange='checkClick(this)'" + isChecked + ">&nbsp;&nbsp;&nbsp;" + arr[key] + "</td></tr>";
	}
	return ret;
}

function populateList() {
	if(document.getElementById('searchAction').value.length == 0)
		document.getElementById('actionsList').innerHTML = render(permissions,'permissions');
	if(document.getElementById('searchUsers').value.length == 0) {
		//document.getElementById('usersList').innerHTML = '<tr><td>Groups:</td></tr>';
		document.getElementById('usersList').innerHTML += render(groups,'groups');
		//document.getElementById('usersList').innerHTML += '<tr><td>Users:</td></tr>';
		document.getElementById('usersList').innerHTML += render(users,'users');
	}
	initSmartTable();
}

function search(list, search) {
	var obj = {};
	for(var lkey in list)
		for(var skey in search)
			if(list[lkey].indexOf(search[skey]) != -1)
				if(obj[lkey])
					obj[lkey] = '<b><big>' + list[lkey] + '</big></b>';
				else
					obj[lkey] = list[lkey];
	return obj;
}

function searchUsers() {
	document.getElementById('usersList').innerHTML = "";
	var words = document.getElementById('searchUsers').value.split(' ');
	var obj = search(groups, words);
	var count = 0;
	for(var key in obj)
		count++;
	document.getElementById('usersList').innerHTML += '<tr><td>Groups:</td></tr>';
	if(count > 0)
		document.getElementById('usersList').innerHTML += render(obj,'groups');
	else
		document.getElementById('usersList').innerHTML = "<tr><td>Oops!! nothing matched search '" + document.getElementById('searchUsers').value + "'</td></tr>";
	obj = search(users, words);
	count = 0;
	for(var key in obj)
		count++;
	document.getElementById('usersList').innerHTML += '<tr><td>Users:</td></tr>';
	if(count > 0)
		document.getElementById('usersList').innerHTML += render(obj,'users');
	else
		document.getElementById('usersList').innerHTML += "<tr><td>Oops!! nothing matched search '" + document.getElementById('searchUsers').value + "'</td></tr>";
}

function searchAction() {
	var words = document.getElementById('searchAction').value.split(' ');
	var obj = search(permissions, words);
	var count = 0;
	for(var key in obj)
		count++;
	if(count > 0)
		document.getElementById('actionsList').innerHTML = render(obj,'permissions');
	else
		document.getElementById('actionsList').innerHTML = "<tr><td>Oops!! nothing matched search '" + document.getElementById('searchAction').value + "'</td></tr>";
}

function selectAll1() {
	var checks = document.getElementsByClassName('check_permissions');
	var l = checks.length;
	for(var i=0; i<l; i++) {
		checks[i].checked = true;
		savelist(checks[i]);
	}
	showPermissions();
}

function selectAll2() {
	var checks = document.getElementsByClassName('check_groups');
	var l = checks.length;
	for(var i=0; i<l; i++) {
		checks[i].checked = true;
		savelist(checks[i]);
	}
	checks = document.getElementsByClassName('check_users');
	l = checks.length;
	for(var i=0; i<l; i++) {
		checks[i].checked = true;
		savelist(checks[i]);
	}
	showPermissions();
}

function clearAll1() {
	var checks = document.getElementsByClassName('check_permissions');
	var l = checks.length;
	for(var i=0; i<l; i++) {
		checks[i].checked = false;
		savelist(checks[i]);
	}
	showPermissions();
}

function clearAll2() {
	var checks = document.getElementsByClassName('check_groups');
	var l = checks.length;
	for(var i=0; i<l; i++) {
		checks[i].checked = false;
		savelist(checks[i]);
	}
	checks = document.getElementsByClassName('check_users');
	l = checks.length;
	for(var i=0; i<l; i++) {
		checks[i].checked = false;
		savelist(checks[i]);
	}
	showPermissions();
}

function toggle1() {
	var checks = document.getElementsByClassName('check_permissions');
	var l = checks.length;
	for(var i=0; i<l; i++) {
		checks[i].checked = !(checks[i].checked);
		savelist(checks[i]);
	}
	showPermissions();
}

function toggle2() {
	var checks = document.getElementsByClassName('check_groups');
	var l = checks.length;
	for(var i=0; i<l; i++) {
		checks[i].checked = !(checks[i].checked);
		savelist(checks[i]);
	}
	checks = document.getElementsByClassName('check_users');
	l = checks.length;
	for(var i=0; i<l; i++) {
		checks[i].checked = !(checks[i].checked);
		savelist(checks[i]);
	}
	showPermissions();
}
