//Newer version available of boxes script at http://www.456bereastreet.com/archive/200609/transparent_custom_corners_and_borders_version_2/ but this breaks in IE 5& 5.5
/*
createElement function found at http://simon.incutio.com/archive/2003/06/15/javascriptWithXML
*/
function createElement(element) {
	if (typeof document.createElementNS != 'undefined') {
		return document.createElementNS('http://www.w3.org/1999/xhtml', element);
	}
	if (typeof document.createElement != 'undefined') {
		return document.createElement(element);
	}
	return false;
}

function insertTop(obj) {
	// Create the two div elements needed for the top of the box
	d=createElement("div");
	d.className="bt"; // The outer div needs a class name
    d2=createElement("div");
    d.appendChild(d2);
	obj.insertBefore(d,obj.firstChild);
}

function insertBottom(obj) {
	// Create the two div elements needed for the bottom of the box
	d=createElement("div");
	d.className="bb"; // The outer div needs a class name
    d2=createElement("div");
    d.appendChild(d2);
	obj.appendChild(d);
}

function initCB()
{
	// Find all div elements
	var divs = document.getElementsByTagName('div');
	var cbDivs = [];
	for (var i = 0; i < divs.length; i++) {
	// Find all div elements with cbb in their class attribute while allowing for multiple class names
		if (/\bcbb\b/.test(divs[i].className))
			cbDivs[cbDivs.length] = divs[i];
	}
	// Loop through the found div elements
	var thediv, outer, i1, i2;
	for (var i = 0; i < cbDivs.length; i++) {
	// Save the original outer div for later
		thediv = cbDivs[i];
	// 	Create a new div, give it the original div's class attribute, and replace 'cbb' with 'cb'
		outer = createElement('div');
		outer.className = thediv.className;
		outer.className = thediv.className.replace('cbb', 'cb');
	// Change the original div's class name and replace it with the new div
		thediv.className = 'i3';
		thediv.parentNode.replaceChild(outer, thediv);
	// Create two new div elements and insert them into the outermost div
		i1 = createElement('div');
		i1.className = 'i1';
		outer.appendChild(i1);
		i2 = createElement('div');
		i2.className = 'i2';
		i1.appendChild(i2);
	// Insert the original div
		i2.appendChild(thediv);
	// Insert the top and bottom divs
		insertTop(outer);
		insertBottom(outer);
	}
}

//doPopups displays new window icon after links that open in new window
function doPopups() {
  if (!document.getElementsByTagName) return false;
  var links = document.getElementsByTagName("a");
  for (var i=0; i < links.length; i++) {
	linkAttribute = links[i].getAttribute("target")
    if (linkAttribute == "_blank")
		{
		links[i].className = links[i].className + " newWinStyle";
		if (links[i].title == "") {
		links[i].title = "(new window)";
		}
		else {
		links[i].title = links[i].title + " (new window)";	
		}
	 	links[i].onclick = function(e) {
			if(!e)e=window.event;
			if(e.shiftKey || e.ctrlKey || e.altKey) return;
			window.open(this.href);
			return false;
			}
      }
    }
}

function ss_fixAllLinks() { 
 // Get a list of all links in the page 
 var allLinks = document.getElementsByTagName('a'); 
 // Walk through the list 
 for (var i=0;i<allLinks.length;i++) { 
   var lnk = allLinks[i]; 
   if ((lnk.href&& lnk.href.indexOf('#') != -1)&&  
       ( (lnk.pathname == location.pathname) || 
   ('/'+lnk.pathname == location.pathname) )&&  
       (lnk.search == location.search)) { 
     // If the link is internal to the page (begins in #) 
     // then attach the smoothScroll function as an onclick 
     // event handler 
     addEvent(lnk,'click',smoothScroll); 
   } 
 } 
} 

function smoothScroll(e) { 
 // This is an event handler; get the clicked on element, 
 // in a cross-browser fashion 
 if (window.event) { 
   target = window.event.srcElement; 
 } else if (e) { 
   target = e.target; 
 } else return; 
  
 // Make sure that the target is an element, not a text node 
 // within an element 
 if (target.nodeType == 3) { 
   target = target.parentNode; 
 } 
  
 // Paranoia; check this is an A tag 
 if (target.nodeName.toLowerCase() != 'a') return; 
  
 // Find the <a name> tag corresponding to this href 
 // First strip off the hash (first character) 
 anchor = target.hash.substr(1); 
 // Now loop all A tags until we find one with that name 
 var allLinks = document.getElementsByTagName('a'); 
 var destinationLink = null; 
 for (var i=0;i<allLinks.length;i++) { 
   var lnk = allLinks[i]; 
   if (lnk.name&& (lnk.name == anchor)) { 
     destinationLink = lnk; 
     break; 
   } 
 } 
  
 // If we didn't find a destination, give up and let the browser do 
 // its thing 
 if (!destinationLink) return true; 
  
 // Find the destination's position 
 var destx = destinationLink.offsetLeft;  
 var desty = destinationLink.offsetTop; 
 var thisNode = destinationLink; 
 while (thisNode.offsetParent&&  
       (thisNode.offsetParent != document.body)) { 
   thisNode = thisNode.offsetParent; 
   destx += thisNode.offsetLeft; 
   desty += thisNode.offsetTop; 
 } 
  
 // Stop any current scrolling 
 clearInterval(ss_INTERVAL); 
  
 cypos = ss_getCurrentYPos(); 
  
 ss_stepsize = parseInt((desty-cypos)/ss_STEPS); 
 ss_INTERVAL = setInterval('ss_scrollWindow('+ss_stepsize+','+desty+',"'+anchor+'")',10); 
  
 // And stop the actual click happening 
 if (window.event) { 
   window.event.cancelBubble = true; 
   window.event.returnValue = false; 
 } 
 if (e&& e.preventDefault&& e.stopPropagation) { 
   e.preventDefault(); 
   e.stopPropagation(); 
 } 
} 

function ss_scrollWindow(scramount,dest,anchor) { 
 wascypos = ss_getCurrentYPos(); 
 isAbove = (wascypos < dest); 
 window.scrollTo(0,wascypos + scramount); 
 iscypos = ss_getCurrentYPos(); 
 isAboveNow = (iscypos < dest); 
 if ((isAbove != isAboveNow) || (wascypos == iscypos)) { 
   // if we've just scrolled past the destination, or 
   // we haven't moved from the last scroll (i.e., we're at the 
   // bottom of the page) then scroll exactly to the link 
   window.scrollTo(0,dest); 
   // cancel the repeating timer 
   clearInterval(ss_INTERVAL); 
   // and jump to the link directly so the URL's right 
   location.hash = anchor; 
 } 
} 

function ss_getCurrentYPos() { 
 if (document.body&& document.body.scrollTop) 
   return document.body.scrollTop; 
 if (document.documentElement&& document.documentElement.scrollTop) 
   return document.documentElement.scrollTop; 
 if (window.pageYOffset) 
   return window.pageYOffset; 
 return 0; 
} 

var ss_INTERVAL; 
var ss_STEPS = 25; 


// add 'back to top' link on long pages where scrollbar required
function topLink()
{
	// original viewport measurements courtesy ppk @ www.quirksmode.org/viewport/compatibility.html

	var clt_ht = null;
	var doc_ht = null;
	if (self.innerHeight) // all except Explorer
		clt_ht = self.innerHeight;
	else if (document.documentElement && document.documentElement.clientHeight)
	// Explorer 6 Strict Mode
		clt_ht = document.documentElement.clientHeight;
	else if (document.body) // other Explorers
		clt_ht = document.body.clientHeight;

	doc_ht = document.getElementById('block_2').clientHeight;

	if (clt_ht && doc_ht)
		document.getElementById('toplink').style.display = (doc_ht > clt_ht) ? 'block' : 'none';
}


/* addEvent call for each of functions above */
function addEvent(obj, type, fn) {
	if (obj.addEventListener)
		obj.addEventListener(type, fn, false);
	else if (obj.attachEvent) {
		obj.detachEvent('on'+ type, obj['e'+ type + fn] || new Function);
		obj.attachEvent('on'+ type, obj['e'+ type + fn] = fn);
	}
}

function removeEvent(obj, type, fn) {
	if (obj.removeEventListener)
		obj.removeEventListener(type, fn, false);
	else if (obj.detachEvent) {
		obj.detachEvent('on'+ type, obj['e'+ type + fn]);
		obj['e'+ type + fn] = null;
		delete obj['e'+ type + fn];
	}
}


addEvent(window, 'load', topLink); //add 'back to top' link
addEvent(window, 'resize', topLink); //add 'back to top' link on resize
addEvent(window, 'load', initCB); //creates smooth borders around boxes.
addEvent(window, 'load', doPopups); // new window graphic for external links
addEvent(window, 'load', ss_fixAllLinks); // smooth scroll



/* tabbed content switcher */
/* Copyright (c) 2005 by Michael Hanselmann - http://hansmi.ch/download/css-tabs/demo.html

   This program is free software; you can redistribute it and/or modify
   it under the terms of the GNU General Public License as published by
   the Free Software Foundation; either version 2 of the License, or
   (at your option) any later version.
*/
function TabSwitchInit(opts, sel) {
    var d = document;
    if(!d.getElementById) return;

    var init = null;
    for(var i = 2; i < opts.length; i++) {
        var link = d.getElementById(opts[i][1]);
        if(link) {
			link.onclick = _TabClick;
            link._tabs = opts;
            if(!init || sel == opts[i][0] || isFinite(sel)&& (i - 2) == sel) init = link;
        }
    }
    if(init) init.onclick();
}

function _TabClick() {
    var d = document;
    if(!d.getElementById) return;

    var opts = this._tabs;
    var minh = opts[1];

    var sel = (opts[0]?d.getElementById(opts[0]):null);

    for(var i = 2; i < opts.length; i++) {
        var div = d.getElementById(opts[i][0]);
        var link = d.getElementById(opts[i][1]);

        if(!div || !link) continue;

        if(this == link) {
            div.style.display = '';
            link.className = 'active';
            if(minh) {
                div.style.minHeight = minh;
                if(d.all) div.style.height = minh; // IE-Fix
            }
            if(sel) sel.value = div.id;
        }else{
            div.style.display = 'none';
            link.className = '';
        }
    }

    return false;
}

/* style switcher*/
function setActiveStyleSheet(title) {
  var i, a, main;
  for(i=0; (a = document.getElementsByTagName("link")[i]); i++) {
    //line below & other references to '&& a.getAttribute("title")!="this stylesheet is activated by javascript"' are required for wforms-jsonly.css compatibility
	if(a.getAttribute("rel").indexOf("style") != -1&& a.getAttribute("title")&& a.getAttribute("title")!="This stylesheet activated by javascript") {
      a.disabled = true;
      if(a.getAttribute("title") == title) a.disabled = false;
    }
  }
}

function getActiveStyleSheet() {
  var i, a;
  for(i=0; (a = document.getElementsByTagName("link")[i]); i++) {
    if(a.getAttribute("rel").indexOf("style") != -1&& a.getAttribute("title")&& !a.disabled&& a.getAttribute("title")!="This stylesheet activated by javascript") return a.getAttribute("title");
  }
  return null;
}

function getPreferredStyleSheet() {
  var i, a;
  for(i=0; (a = document.getElementsByTagName("link")[i]); i++) {
    if(a.getAttribute("rel").indexOf("style") != -1
      && a.getAttribute("rel").indexOf("alt") == -1
      && a.getAttribute("title")
	  && a.getAttribute("title")!="This stylesheet activated by javascript"
       ) return a.getAttribute("title");
  }
  return null;
}

function createCookie(name,value,days) {
  if (days) {
    var date = new Date();
    date.setTime(date.getTime()+(days*24*60*60*1000));
    var expires = "; expires="+date.toGMTString();
  }
  else expires = "";
  document.cookie = name+"="+value+expires+"; path=/";
}

function readCookie(name) {
  var nameEQ = name + "=";
  var ca = document.cookie.split(';');
  for(var i=0;i < ca.length;i++) {
    var c = ca[i];
    while (c.charAt(0)==' ') c = c.substring(1,c.length);
    if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
  }
  return null;
}

window.onload = function(e) {
  var cookie = readCookie("style");
  var title = cookie ? cookie : getPreferredStyleSheet();
  setActiveStyleSheet(title);
}

window.onunload = function(e) {
  var title = getActiveStyleSheet();
  createCookie("style", title, 365);
}

var cookie = readCookie("style");
var title = cookie ? cookie : getPreferredStyleSheet();
setActiveStyleSheet(title);

/* jquery code goes below here */
$(document).ready(function(){
	//remove left nav if empty
	/*
	if (!$("ul#left_nav > li").length > 0) {
		$(".left_content").remove();
		$("#block_1").append("<div class='left_content'>&nbsp;</div>");
	}
	*/
	// zebra tables					   
	$(".zebra tr:odd").addClass("odd");
	// max width tables for IE < 7
	$("#block_2 > table").minmax();
	// meaningful filesizes on DPS index pages
	$(".assetSize").each(function(){	
		var filesize = $(this).text() / 1024; // file size in KB
		filesize = Math.round(filesize * 10) / 10; // round to 1 decimal place
		if (filesize > 1000) {
			filesize = filesize / 1024; // file size in MB
			filesize = Math.round(filesize * 10) / 10; // round to 1 decimal place
			$(this).empty().text(filesize + "MB");
		} else {
			$(this).empty().text(filesize + "KB");
		}
	});
});

