/* 
------------------------------------------
	Flipbox written by CrappoMan
	simonpatterson@dsl.pipex.com
------------------------------------------
*/
function flipBox(who) {
	var tmp; 
	if (document.images['b_' + who].src.indexOf('_on') == -1) { 
		tmp = document.images['b_' + who].src.replace('_off', '_on');
		document.getElementById('box_' + who).style.display = 'none';
		document.images['b_' + who].src = tmp;
	} else { 
		tmp = document.images['b_' + who].src.replace('_on', '_off');
		document.getElementById('box_' + who).style.display = 'block';
		document.images['b_' + who].src = tmp;
	} 
}

function addText(elname, wrap1, wrap2) {
	if (document.selection) { // for IE 
		var str = document.selection.createRange().text;
		document.forms['inputform'].elements[elname].focus();
		var sel = document.selection.createRange();
		sel.text = wrap1 + str + wrap2;
		return;
	} else if ((typeof document.forms['inputform'].elements[elname].selectionStart) != 'undefined') { // for Mozilla
		var txtarea = document.forms['inputform'].elements[elname];
		var selLength = txtarea.textLength;
		var selStart = txtarea.selectionStart;
		var selEnd = txtarea.selectionEnd;
		var oldScrollTop = txtarea.scrollTop;
		//if (selEnd == 1 || selEnd == 2)
		//selEnd = selLength;
		var s1 = (txtarea.value).substring(0,selStart);
		var s2 = (txtarea.value).substring(selStart, selEnd)
		var s3 = (txtarea.value).substring(selEnd, selLength);
		txtarea.value = s1 + wrap1 + s2 + wrap2 + s3;
		txtarea.selectionStart = s1.length;
		txtarea.selectionEnd = s1.length + s2.length + wrap1.length + wrap2.length;
		txtarea.scrollTop = oldScrollTop;
		txtarea.focus();
		return;
	} else {
		insertText(elname, wrap1 + wrap2);
	}
}

function insertText(elname, what) {
	if (document.forms['inputform'].elements[elname].createTextRange) {
		document.forms['inputform'].elements[elname].focus();
		document.selection.createRange().duplicate().text = what;
	} else if ((typeof document.forms['inputform'].elements[elname].selectionStart) != 'undefined') { // for Mozilla
		var tarea = document.forms['inputform'].elements[elname];
		var selEnd = tarea.selectionEnd;
		var txtLen = tarea.value.length;
		var txtbefore = tarea.value.substring(0,selEnd);
		var txtafter =  tarea.value.substring(selEnd, txtLen);
		var oldScrollTop = tarea.scrollTop;
		tarea.value = txtbefore + what + txtafter;
		tarea.selectionStart = txtbefore.length + what.length;
		tarea.selectionEnd = txtbefore.length + what.length;
		tarea.scrollTop = oldScrollTop;
		tarea.focus();
	} else {
		document.forms['inputform'].elements[elname].value += what;
		document.forms['inputform'].elements[elname].focus();
	}
}

function show_hide(msg_id) {
	msg_id.style.display = msg_id.style.display == 'none' ? 'block' : 'none';
}
function toggle(showHideDiv, switchTextDiv) {
	var ele = document.getElementById(showHideDiv);
	var text = document.getElementById(switchTextDiv);
	if(ele.style.display == "block") {
    		ele.style.display = "none";
		text.innerHTML = "Show Details";
  	}
	else {
		ele.style.display = "block";
		text.innerHTML = "Hide Details";
	}
} 