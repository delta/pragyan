//Disabling this effect for now, smoother without this.
/*window.addEvent('domready', function(){
			var list = $$('.menu a div');
			list.each(function(element) {

				var fx = new Fx.Styles(element, {duration:200, wait:false});

				element.addEvent('mouseenter', function(){
					fx.start({
						'margin-left': 5,
						'background-color': '#444',
						color: '#eee'
					});
				});

				element.addEvent('mouseleave', function(){
					if(element.hasClass('menuitem')) {
						fx.start({
							'margin-left': 0,
							'background-color': '#E3E0E0',
							'color': '#555'
						});
					}
					else {
						fx.start({
							'margin-left': 0,
							'background-color': '#555',
							'color': '#DDD'
						});
					}
				});

			});
		});
*/

function blurOthers(bloc) {
	return;
	for(var i=0;i<bloc.parentNode.childNodes.length;i++) {
		if(bloc.parentNode.childNodes[i].nodeValue=="\n") continue;
		if(bloc.parentNode.childNodes[i]==bloc) {
			bloc.style.opacity=1;
			bloc.parentNode.childNodes[i].style.filter="alpha(opacity=100)";
			continue;
		}
		bloc.parentNode.childNodes[i].style.opacity=0.55;
        bloc.parentNode.childNodes[i].style.filter="alpha(opacity=55)";
	}
}

function unBlurAll(bloc) {
	return;
	for(var i=0;i<bloc.childNodes.length;i++) {
		if(bloc.childNodes[i].nodeValue=="\n") continue;
		bloc.childNodes[i].style.opacity=1;
        bloc.childNodes[i].style.filter="alpha(opacity=100)";
	}
}



function checkEmail(inputhandler) {

    var emailStr = inputhandler.value;
/* The following variable tells the rest of the function whether or not
to verify that the address ends in a two-letter country or well-known
TLD.  1 means check it, 0 means don't. */

var checkTLD=1;

/* The following is the list of known TLDs that an e-mail address must end with. */

var knownDomsPat=/^(com|net|org|edu|int|mil|gov|arpa|biz|aero|name|coop|info|pro|museum)$/;

/* The following pattern is used to check if the entered e-mail address
fits the user@domain format.  It also is used to separate the username
from the domain. */

var emailPat=/^(.+)@(.+)$/;

/* The following string represents the pattern for matching all special
characters.  We don't want to allow special characters in the address.
These characters include ( ) < > @ , ; : \ " . [ ] */

var specialChars="\\(\\)><@,;:\\\\\\\"\\.\\[\\]";

/* The following string represents the range of characters allowed in a
username or domainname.  It really states which chars aren't allowed.*/

var validChars="\[^\\s" + specialChars + "\]";

/* The following pattern applies if the "user" is a quoted string (in
which case, there are no rules about which characters are allowed
and which aren't; anything goes).  E.g. "jiminy cricket"@disney.com
is a legal e-mail address. */

var quotedUser="(\"[^\"]*\")";

/* The following pattern applies for domains that are IP addresses,
rather than symbolic names.  E.g. joe@[123.124.233.4] is a legal
e-mail address. NOTE: The square brackets are required. */

var ipDomainPat=/^\[(\d{1,3})\.(\d{1,3})\.(\d{1,3})\.(\d{1,3})\]$/;

/* The following string represents an atom (basically a series of non-special characters.) */

var atom=validChars + '+';

/* The following string represents one word in the typical username.
For example, in john.doe@somewhere.com, john and doe are words.
Basically, a word is either an atom or quoted string. */

var word="(" + atom + "|" + quotedUser + ")";

// The following pattern describes the structure of the user

var userPat=new RegExp("^" + word + "(\\." + word + ")*$");

/* The following pattern describes the structure of a normal symbolic
domain, as opposed to ipDomainPat, shown above. */

var domainPat=new RegExp("^" + atom + "(\\." + atom +")*$");

/* Finally, let's start trying to figure out if the supplied address is valid. */

/* Begin with the coarse pattern to simply break up user@domain into
different pieces that are easy to analyze. */

var matchArray=emailStr.match(emailPat);

if (matchArray==null) {

/* Too many/few @'s or something; basically, this address doesn't
even fit the general mould of a valid e-mail address. */

alert("Email address seems incorrect (check @ and .'s)");
inputhandler.value="";
inputhandler.focus();
return false;
}
var user=matchArray[1];
var domain=matchArray[2];

// Start by checking that only basic ASCII characters are in the strings (0-127).

for (i=0; i<user.length; i++) {
if (user.charCodeAt(i)>127) {
alert("Ths username contains invalid characters.");
inputhandler.value="";
inputhandler.focus();
return false;
   }
}
for (i=0; i<domain.length; i++) {
if (domain.charCodeAt(i)>127) {
alert("Ths domain name contains invalid characters.");
inputhandler.value="";
inputhandler.focus();
return false;
   }
}

// See if "user" is valid

if (user.match(userPat)==null) {

// user is not valid

alert("The username doesn't seem to be valid.");
inputhandler.value="";
inputhandler.focus();
return false;
}

/* if the e-mail address is at an IP address (as opposed to a symbolic
host name) make sure the IP address is valid. */

var IPArray=domain.match(ipDomainPat);
if (IPArray!=null) {

// this is an IP address

for (var i=1;i<=4;i++) {
if (IPArray[i]>255) {
alert("Destination IP address is invalid!");
inputhandler.value="";
inputhandler.focus();
return false;
   }
}
return true;
}

// Domain is symbolic name.  Check if it's valid.

var atomPat=new RegExp("^" + atom + "$");
var domArr=domain.split(".");
var len=domArr.length;
for (i=0;i<len;i++) {
if (domArr[i].search(atomPat)==-1) {
alert("The domain name does not seem to be valid.");
inputhandler.value="";
inputhandler.focus();
return false;
   }
}

/* domain name seems valid, but now make sure that it ends in a
known top-level domain (like com, edu, gov) or a two-letter word,
representing country (uk, nl), and that there's a hostname preceding
the domain or country. */

if (checkTLD && domArr[domArr.length-1].length!=2 &&
domArr[domArr.length-1].search(knownDomsPat)==-1) {
alert("The address must end in a well-known domain or two letter " + "country.");
inputhandler.value="";
inputhandler.focus();
return false;
}

// Make sure there's a host name preceding the domain.

if (len<2) {
alert("This address is missing a hostname!");
inputhandler.value="";
inputhandler.focus();
return false;
}

// If we've gotten this far, everything's valid!
return true;
}



function startTicker()
{
 // Define run time values
 theCurrentStory = -1;
 theCurrentLength = 0;
 // Locate base objects
 if (document.getElementById) {
 theAnchorObject = document.getElementById("tickerAnchor");
 runTheTicker();
 }
 else {
 document.write("<style>.ticki{display:none;}.ticko{border:0px; padding:0px;}</style>");
 return true;
 }
}
// Ticker main run loop
function runTheTicker()
{
 var myTimeout;
 // Go for the next story data block
 if(theCurrentLength == 0)
 {
 theCurrentStory++;
 theCurrentStory = theCurrentStory % theItemCount;
 theStorySummary = theSummaries[theCurrentStory].replace(/&quot;/g,'"');
 theTargetLink = theSiteLinks[theCurrentStory];
 theAnchorObject.href = theTargetLink;
 thePrefix = "<span class=\"tickls\">" + theLeadString + "</span>";
 }
 // Stuff the current ticker text into the anchor
 theAnchorObject.innerHTML =  theStorySummary.substring(0,theCurrentLength) + whatWidget();
 // Modify the length for the substring and define the timer
 if(theCurrentLength != theStorySummary.length)
 {
 theCurrentLength++;
 myTimeout = theCharacterTimeout;
 }
 else
 {
 theCurrentLength = 0;
 myTimeout = theStoryTimeout;
 }
 // Call up the next cycle of the ticker
 setTimeout("runTheTicker()", myTimeout);
}
// Widget generator
function whatWidget()
{
 if(theCurrentLength == theStorySummary.length)
 {
 return theWidgetNone;
 }

 if((theCurrentLength % 2) == 1)
 {
 return theWidgetOne;
 }
 else
 {
 return theWidgetTwo;
 }
}