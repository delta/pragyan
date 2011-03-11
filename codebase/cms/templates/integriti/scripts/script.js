/**
 * @author Boopathi
 * @description Adding an extra behaviour to the native Javascript function
 * @usage function(){}.debounce()
*/ 
Function.prototype.debounce = function(threshold, execAsap) {
	var func = this, timeout;
	return function debounced(){
		var obj= this, args = arguments;
		function delayed() {
			if(!execAsap)
				func.apply(obj,args);
			timeout = null;
		}
		if(timeout)
			clearTimeout(timeout);
		else if(execAsap)
			func.apply(obj,args);
		timeout = setTimeout(delayed, threshold || 100);
	}
}

$(function() {
	
	/**
	* Enable keyboard Shortcuts
	*/
	$.mykey = function(key, callback, args) {
		if(key.match("/(ctrl|alt/)i"))
		var isCtrl = false;
		var isAlt = false;
		var isEnabled = true;
		$(document).keydown(function(e) {
			if(!args)args=[];
			if(e.keyCode==17)isCtrl = true;
			if(e.keyCode==18)isAlt = true;
			if(e.keyCode == key.charCodeAt(0) && isCtrl && isEnabled) {
            	callback.apply(this, args);
            	return false;
        	}
        	if(e.keyCode == "K".charCodeAt(0) && isCtrl && isAlt) {
        		if(isEnabled) {
        			isEnabled=false;
        			alert("disabled keyboard shortcuts");
        		}
        		else {
        			isEnabled=true;
        			alert("enabled keyboard shortcuts");
        		}
        		return false;
        	}
    	}).keyup(function(e) {
    	    if(e.keyCode == 17) isCtrl = false;
    	    if(e.keyCode == 18) isAlt = false;
		});
    }
    
    /*Assign Shortcuts
    $.mykey("H", function(){location.href=urlRequestRoot;});
    $.mykey("E", function(){location.href=urlRequestRoot+ "/home/events/";});
    $.mykey("Q", function(){extendHeader();});
    $.mykey("L", function(){openLoginDialog("a.cms-actionlogin")});
    $.mykey("W", function(){location.href=urlRequestRoot+"/home/workshops/";});
    */
    $.mykey("S", function(){location.href="./+pdf"});
    //$.mykey("R", function(){location.href="+login&subaction=register"});
    
    
});

///Login form Validation
function checkLoginForm(inputhandler) {
	if(inputhandler.user_email.value.length==0) {
		alert("Blank Email Address not allowed.");
		return false;
	}
	else if(inputhandler.user_email.value.indexOf('@') == -1)
	{
		alert("Invalid Email address");
		return false;
	}
	else if(inputhandler.user_password.value.length==0) {
		alert("Blank password not allowed.");
		return false;
	}
}
