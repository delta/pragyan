function pageX(elem) {
	return elem.offsetParent ?
		elem.offsetLeft + pageX( elem.offsetParent ) :
		elem.offsetLeft;
}

function pageY(elem) {
	return elem.offsetParent ?
		elem.offsetTop + pageY( elem.offsetParent ) :
		elem.offsetTop;
}

function getStyle( elem, styleName ) {
	if (elem.style[styleName])
		return elem.style[styleName];
	else if (elem.currentStyle)
		return elem.currentStyle[styleName];
	else if (document.defaultView && document.defaultView.getComputedStyle) {
		styleName = styleName.replace(/([A-Z])/g,"-$1");
		styleName = styleName.toLowerCase();
		var s = document.defaultView.getComputedStyle(elem,"");
		return s && s.getPropertyValue(styleName);
	}
	else
		return null;
}

function getHeight( elem ) {
    return parseInt( getStyle( elem, 'height' ) );
}

function getWidth( elem ) {
    return parseInt( getStyle( elem, 'width' ) );
}

function stopBubble(e) {
    if ( e && e.stopPropagation )
         e.stopPropagation();
    else
         window.event.cancelBubble = true;
}

function suggestionClicked(span, textBoxId, suggestionBoxId) {
	document.getElementById(textBoxId).value = span.innerHTML;
	document.getElementById(suggestionBoxId).style.display = 'none';
}


if(typeof XMLHttpRequest == "undefined") {
	XMLHttpRequest = function() {
		return new ActiveXObject(
					navigator.userAgent.indexOf("MSIE 5") >= 0 ?
					"Microsoft.XMLHTTP" : "Msxml2.XMLHTTP"
				);
	};
}



function SuggestionBox(textBox, suggestionDiv, requestUrl) {
	this.init(textBox, suggestionDiv, requestUrl);
}

SuggestionBox.prototype = {
	init: function(textBox, suggestionDiv, requestUrl) {
		/// All the initializations
		this.textBox = textBox;
		this.suggestionDiv = suggestionDiv;
		this.requestUrl = requestUrl;
		this.textBoxId = textBox.id;
		this.suggestionDivId = suggestionDiv.id;
		this.lastTimeoutId = 0;
		this.xmlObject = null;
		this.lastRequest = '';
		this.cacheRequests = new Array();
		this.cacheResponses = new Array();
		this.loadingImageUrl = '';
		this.responseDelimiter = ',';

		suggestionDiv.style.left = pageX(textBox) + "px";
		var h = getHeight(textBox);
		if(h) {
			h = h + 4;
		}
		else {
			h = 20;			// in case height couldn't be computed, let's assign a safe value of 20px
		}
		suggestionDiv.style.top = (pageY(textBox) + h) + "px";

		textBox.onclick = suggestionDiv.onclick = function(e) {
			stopBubble(e);
		}

		var self = this;
		textBox.onkeyup = function(e) { self.resetTimer(e); };

		var taskType = 'click';
		taskType=(window.addEventListener)? taskType : "on"+taskType;
		if (window.addEventListener)
			window.addEventListener(taskType, function() { document.getElementById(self.suggestionDivId).style.display = 'none'; }, false)
		else if (window.attachEvent)
			window.attachEvent(taskType, function() { document.getElementById(self.suggestionDivId).style.display = 'none'; } );
	},

	resetTimer: function(e) {
		if(!e) {
			e = window.event;
		}

		if(e.keyCode == 40) {	// down key was pressed... display the div
			document.getElementById(this.suggestionDivId).style.display = 'block';
			return;
		}
		else if(e.keyCode == 38) {
			document.getElementById(this.suggestionDivId).style.display = 'none';
			return;
		}

		if(this.lastTimeoutId != 0) {
			clearTimeout(this.lastTimeoutId);
		}
		var self = this;
		this.lastTimeoutId = setTimeout(function() { self.checkInput(); }, 600);
	},

	checkInput: function() {
		var newRequest = document.getElementById(this.textBoxId).value;

		if(this.lastRequest != newRequest && newRequest.length >= 3) {
			var div = document.getElementById(this.suggestionDivId);
			div.innerHTML = '<center><img src="' + this.loadingImageUrl + '" alt="Loading..." /></center>';
			div.style.display = 'block';

			this.lastRequest = newRequest;
			this.getSuggestions(newRequest);
		}

		this.lastTimeoutId = 0;
	},

	getSuggestions: function(request) {
		this.xmlObject = null;
		var div = document.getElementById(this.suggestionDivId);

		for(var i = 0; i < this.cacheRequests.length; i++) {
			if(this.cacheRequests[i] == request) {
				var ih = 'No matching entries';
				if(this.cacheResponses[i].length > 0) {
					ih = '<span class="suggestion" onclick="suggestionClicked(this, \'' + this.textBoxId + '\', \'' + this.suggestionDivId + '\')">' + this.cacheResponses[i].join("</span>\n<span class=\"suggestion\" onclick=\"suggestionClicked(this, '" + this.textBoxId + "', '" + this.suggestionDivId + "')\">") + '</span>';

				}
				div.innerHTML = ih;
				div.style.display = "block";
				return;
			}
		}

		this.xmlObject = new XMLHttpRequest();
		this.xmlObject.open("GET", this.requestUrl.replace('%pattern%', request), true);
		var self = this;
		this.xmlObject.onreadystatechange = function() { self.retrieveResults(); };
		this.xmlObject.send(null);
	},

	retrieveResults: function() {
		if(this.xmlObject.readyState == 4) {
			if(this.xmlObject.responseText.length > 0) {
				var suggestions = this.xmlObject.responseText.split(this.responseDelimiter);

				if(suggestions[0] == this.lastRequest) {
					this.cacheRequests.push(suggestions.shift());
					this.cacheResponses.push(suggestions);

					var div = document.getElementById(this.suggestionDivId);
					var ih = 'No matching entries';
					if(suggestions.length > 0) {
						ih =
							'<span class="suggestion" onclick="suggestionClicked(this, \'' + this.textBoxId + '\', \'' + this.suggestionDivId + '\')">' +
							suggestions.join("</span>\n<span class=\"suggestion\" onclick=\"suggestionClicked(this, \'" + this.textBoxId + "', '" + this.suggestionDivId + "')\">") +
							'</span>';
					}
					div.innerHTML = ih;
					div.style.display = 'block';
					this.xmlObject = null;
				}
			}
		}
	}
}
