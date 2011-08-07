String.prototype.trim = function()
{
	return this.replace(/^[\s]+|[\s]+$/, "");
};

function SuggestFramework_Create(instance)
{
	if(SuggestFramework_Name[instance] && SuggestFramework_Action[instance])
	{
		SuggestFramework_InputContainer[instance]              = document.getElementById(SuggestFramework_Name[instance]);
		SuggestFramework_InputContainer[instance].autocomplete = "off";
		SuggestFramework_InputContainer[instance].onblur       = function() { SuggestFramework_HideOutput(instance); };
		SuggestFramework_InputContainer[instance].onclick      = function() { SuggestFramework_ShowOutput(instance); SuggestFramework_Previous[instance] = '';};
		SuggestFramework_InputContainer[instance].onfocus      = function() { 			SuggestFramework_ShowOutput(instance); 		};
		SuggestFramework_InputContainer[instance].onkeydown    = function(event) { SuggestFramework_ProcessKeys(instance, event); };

		SuggestFramework_OutputContainer[instance]                = document.createElement("div");
		SuggestFramework_OutputContainer[instance].id             = SuggestFramework_Name[instance] + "SuggestList";
		SuggestFramework_OutputContainer[instance].className      = "SuggestFramework_List";
		SuggestFramework_OutputContainer[instance].style.position = "absolute";
		SuggestFramework_OutputContainer[instance].style.zIndex   = "1";
		SuggestFramework_OutputContainer[instance].style.width    = SuggestFramework_InputContainer[instance].clientWidth + "px";
		SuggestFramework_OutputContainer[instance].style.wordWrap = "break-word";
		SuggestFramework_OutputContainer[instance].style.cursor   = "default";
		SuggestFramework_InputContainer[instance].parentNode.insertBefore(SuggestFramework_OutputContainer[instance], SuggestFramework_InputContainer[instance].nextSibling);
		SuggestFramework_InputContainer[instance].parentNode.insertBefore(document.createElement("br"), SuggestFramework_OutputContainer[instance]);

		if(!SuggestFramework_CreateConnection())
		{
			SuggestFramework_Proxy[instance]               = document.createElement("iframe");
			SuggestFramework_Proxy[instance].id            = "proxy";
			SuggestFramework_Proxy[instance].style.width   = "0";
			SuggestFramework_Proxy[instance].style.height  = "0";
			SuggestFramework_Proxy[instance].style.display = "none";
			document.body.appendChild(SuggestFramework_Proxy[instance]);

			if(window.frames && window.frames["proxy"])
				SuggestFramework_Proxy[instance] = window.frames["proxy"];
			else if(document.getElementById("proxy").contentWindow)
				SuggestFramework_Proxy[instance] = document.getElementById("proxy").contentWindow;
			else
				SuggestFramework_Proxy[instance] = document.getElementById("proxy");
		}
	    SuggestFramework_Previous[instance] = SuggestFramework_InputContainer[instance].value;
	
		SuggestFramework_HideOutput(instance);
		SuggestFramework_Throttle(instance);

	}
	else
	{
		throw 'Error: SuggestFramework for instance "' + SuggestFramework_Name[instance] + '" not initialized';
	}
};

function SuggestFramework_CreateConnection()
{
	var asynchronousConnection;

	try
	{
		asynchronousConnection = new ActiveXObject("Microsoft.XMLHTTP");
	}
	catch(e)
	{
		if(typeof(XMLHttpRequest) != "undefined")
			asynchronousConnection = new XMLHttpRequest();
	}

	return asynchronousConnection;
};

function SuggestFramework_HideOutput(instance)
{
	SuggestFramework_OutputContainer[instance].style.display = "none";
};

function SuggestFramework_Highlight(instance, index)
{
	SuggestFramework_SuggestionsIndex[instance] = index;

	for(var i in SuggestFramework_Suggestions[instance])
	{
		var suggestColumns = document.getElementById(SuggestFramework_Name[instance] + "Suggestions[" + i + "]").getElementsByTagName("td");
		for(var j in suggestColumns)
			suggestColumns[j].className = "SuggestFramework_Normal";
	}

	var suggestColumns = document.getElementById(SuggestFramework_Name[instance] + "Suggestions[" + SuggestFramework_SuggestionsIndex[instance] + "]").getElementsByTagName("td");
	for(var i in suggestColumns)
		suggestColumns[i].className = "SuggestFramework_Highlighted";
};

function SuggestFramework_IsHidden(instance)
{
	return ((SuggestFramework_OutputContainer[instance].style.display == "none") ? true : false);
};

function SuggestFramework_ProcessKeys(instance, e)
{
	var keyDown   = 40;
	var keyUp     = 38;
	var keyTab    = 9;
	var keyEnter  = 13;
	var keyEscape = 27;

	var keyPressed = ((window.event) ? window.event.keyCode : e.which);

	if(!SuggestFramework_IsHidden(instance))
	{
		switch(keyPressed)
		{
			case keyDown:   SuggestFramework_SelectNext(instance);     return;
			case keyUp:     SuggestFramework_SelectPrevious(instance); return;
			case keyTab:    SuggestFramework_SelectThis(instance);     return;
			case keyEnter:  SuggestFramework_SelectThis(instance);     return;
			case keyEscape: SuggestFramework_HideOutput(instance);     return;
			default: return;
		}
	}
};

function SuggestFramework_ProcessProxyRequest(instance)
{
	var result = ((SuggestFramework_Proxy[instance].document) ? SuggestFramework_Proxy[instance].document : SuggestFramework_Proxy[instance].contentDocument);
	result = result.body.innerHTML.replace(/\r|\n/g, " ").trim();

	if(typeof(eval(result)) == "object")
		SuggestFramework_Suggest(instance, eval(result));
	else
		setTimeout("SuggestFramework_ProcessProxyRequest(" + instance + ")", 100);
};

function SuggestFramework_ProcessRequest(instance)
{
	if(SuggestFramework_Connection[instance].readyState == 4)
	{
		if(SuggestFramework_Connection[instance].status == 200) {
			SuggestFramework_Suggest(instance, eval(SuggestFramework_Connection[instance].responseText));
		}
	}
};

function SuggestFramework_Query(instance)
{
	SuggestFramework_Throttle(instance);
	var phrase = SuggestFramework_InputContainer[instance].value;
	if(phrase == "" || phrase == SuggestFramework_Previous[instance]) return;
	SuggestFramework_Previous[instance] = phrase;
	//alert(SuggestFramework_Previous[instance]+'  = '+phrase); 
	phrase = phrase.trim();
	phrase = escape(phrase);
	SuggestFramework_Request(instance, SuggestFramework_Action[instance] + "?type=" + SuggestFramework_Name[instance] + "&q=" + phrase);
};

function SuggestFramework_Request(instance, url)
{
	if(SuggestFramework_Connection[instance] = SuggestFramework_CreateConnection())
	{
		SuggestFramework_Connection[instance].onreadystatechange = function() { SuggestFramework_ProcessRequest(instance) };
		SuggestFramework_Connection[instance].open("GET", url, true);
		SuggestFramework_Connection[instance].send(null);

	}
	else
	{
		SuggestFramework_Proxy[instance].location.replace(url);
		SuggestFramework_ProcessProxyRequest(instance);
	}
};

function SuggestFramework_SelectThis(instance, index)
{
	if(!isNaN(index))
		SuggestFramework_SuggestionsIndex[instance] = index;

	if(SuggestFramework_Columns[instance] > 1)
		SuggestFramework_InputContainer[instance].value = SuggestFramework_Suggestions[instance][SuggestFramework_SuggestionsIndex[instance]][SuggestFramework_Capture[instance] - 1];
	else
		SuggestFramework_InputContainer[instance].value = SuggestFramework_Suggestions[instance][SuggestFramework_SuggestionsIndex[instance]];

	SuggestFramework_Previous[instance] = SuggestFramework_InputContainer[instance].value;
	SuggestFramework_HideOutput(instance);
	
	/* submit form after selecting */
	document.forms[0].submit();
};

function SuggestFramework_SelectNext(instance)
{
	SuggestFramework_SetTextSelectionRange(instance);
	if(typeof(SuggestFramework_Suggestions[instance][(SuggestFramework_SuggestionsIndex[instance] + 1)]) != "undefined")
	{
		if(typeof(SuggestFramework_Suggestions[instance][SuggestFramework_SuggestionsIndex[instance]]) != "undefined")
			document.getElementById(SuggestFramework_Name[instance] + "Suggestions[" + SuggestFramework_SuggestionsIndex[instance] + "]").className = "SuggestFramework_Normal";
		SuggestFramework_SuggestionsIndex[instance]++;
		SuggestFramework_Highlight(instance, SuggestFramework_SuggestionsIndex[instance]);
	}
};

function SuggestFramework_SelectPrevious(instance)
{
	SuggestFramework_SetTextSelectionRange(instance);
	if(typeof(SuggestFramework_Suggestions[instance][(SuggestFramework_SuggestionsIndex[instance] - 1)]) != "undefined")
	{
		if(typeof(SuggestFramework_Suggestions[instance][SuggestFramework_SuggestionsIndex[instance]]) != "undefined")
			document.getElementById(SuggestFramework_Name[instance] + "Suggestions[" + SuggestFramework_SuggestionsIndex[instance] + "]").className = "SuggestFramework_Normal";
		SuggestFramework_SuggestionsIndex[instance]--;
		SuggestFramework_Highlight(instance, SuggestFramework_SuggestionsIndex[instance]);
	}
};

function SuggestFramework_SetTextSelectionRange(instance, start, end)
{
	if(!start) var start = SuggestFramework_InputContainer[instance].value.length;
	if(!end)   var end   = SuggestFramework_InputContainer[instance].value.length;

	if(SuggestFramework_InputContainer[instance].setSelectionRange)
	{
		SuggestFramework_InputContainer[instance].setSelectionRange(start, end);
	}
	else if(SuggestFramework_InputContainer[instance].createTextRange)
	{
		var selection = SuggestFramework_InputContainer[instance].createTextRange();
		selection.moveStart("character", start);
		selection.moveEnd("character", end);
		selection.select();
	}
};

function SuggestFramework_ShowOutput(instance)
{
	if(typeof(SuggestFramework_Suggestions[instance]) != "undefined" && SuggestFramework_Suggestions[instance].length)
		SuggestFramework_OutputContainer[instance].style.display = "block";
};

function SuggestFramework_Suggest(instance, list)
{
	SuggestFramework_Suggestions[instance]               = list;
	SuggestFramework_SuggestionsIndex[instance]          = -1;
	SuggestFramework_OutputContainer[instance].innerHTML = "";

	var table = '<table class="SuggestFramework_Combo" cellspacing="0" cellpadding="0">';
	if(SuggestFramework_Heading[instance] && SuggestFramework_Suggestions[instance].length)
	{
		var heading = SuggestFramework_Suggestions[instance].shift();
		var thead   = '<thead>';
		var headingContainer = '<tr>';
		for(var i = 0; i < SuggestFramework_Columns[instance]; i++)
		{
			var value  = ((SuggestFramework_Columns[instance] > 1) ? heading[i] : heading);
			var column = '<td class="SuggestFramework_Heading"';
			if(SuggestFramework_Columns[instance] > 1 && i == SuggestFramework_Columns[instance] - 1)
				column += ' style="text-align: right"';
			column += '>' + value.trim() + '</td>';
			headingContainer += column;
		}
		headingContainer += '</tr>';
		thead  += headingContainer;
		thead  += '</thead>';
		table  += thead;
	}
	var tbody = '<tbody>';
	for(var i in SuggestFramework_Suggestions[instance])
	{
		var suggestionContainer = '<tr id="' + SuggestFramework_Name[instance] + 'Suggestions[' + i + ']">';
		for(var j = 0; j < SuggestFramework_Columns[instance]; j++)
		{
			var value  = ((SuggestFramework_Columns[instance] > 1) ? SuggestFramework_Suggestions[instance][i][j] : SuggestFramework_Suggestions[instance][i]);
			var column = '<td class="SuggestFramework_Normal"';
			if(SuggestFramework_Columns[instance] > 1 && j == SuggestFramework_Columns[instance] - 1)
				column += ' style="text-align: right"';
			column += '>' + value.trim() + '</td>';
			suggestionContainer += column;
		}
		suggestionContainer += '</tr>';
		table += suggestionContainer;
	}
	tbody += '</tbody>';
	table += tbody;
	table += '</table>';
	SuggestFramework_OutputContainer[instance].innerHTML = table;
	for(var i in SuggestFramework_Suggestions[instance])
	{
		var row = document.getElementById(SuggestFramework_Name[instance] + 'Suggestions[' + i + ']');
		row.onmouseover = new Function("SuggestFramework_Highlight(" + instance + ", " + i + ")");
		row.onmousedown = new Function("SuggestFramework_SelectThis(" + instance + ", " + i + ")");
	}

	SuggestFramework_ShowOutput(instance);
};

function SuggestFramework_Throttle(instance)
{
	setTimeout("SuggestFramework_Query(" + instance + ")", SuggestFramework_Delay[instance]);
};

function initializeSuggestFramework()
{

	function getAttributeByName(node, attributeName)
	{
		if(typeof(NamedNodeMap) != "undefined")
		{
			if(node.attributes.getNamedItem(attributeName))
				return node.attributes.getNamedItem(attributeName).value;
		}
		else
		{
			return node.getAttribute(attributeName);
		}
	}

	var inputElements = document.getElementsByTagName("input");

	try
	{
		for(var instance = 0; instance < inputElements.length; instance++)
		{
			if(getAttributeByName(inputElements[instance], "name") &&
			   getAttributeByName(inputElements[instance], "type") == "text" && 
			   getAttributeByName(inputElements[instance], "action"))
			{

				SuggestFramework_Action[instance]  = getAttributeByName(inputElements[instance], "action");
				SuggestFramework_Capture[instance] = 1;
				SuggestFramework_Columns[instance] = 1;
				SuggestFramework_Delay[instance]   = 1000;
				SuggestFramework_Heading[instance] = false;
				SuggestFramework_Name[instance]    = getAttributeByName(inputElements[instance], "name");

				if(getAttributeByName(inputElements[instance], "capture"))
					SuggestFramework_Capture[instance] = getAttributeByName(inputElements[instance], "capture");
				if(getAttributeByName(inputElements[instance], "columns"))
					SuggestFramework_Columns[instance] = getAttributeByName(inputElements[instance], "columns");
				if(getAttributeByName(inputElements[instance], "delay"))
					SuggestFramework_Delay[instance] = getAttributeByName(inputElements[instance], "delay");
				if(getAttributeByName(inputElements[instance], "heading"))
					SuggestFramework_Heading[instance] = getAttributeByName(inputElements[instance], "heading");

				SuggestFramework_Create(instance);
			}
		}
	}
	catch(e) {}
};

// External
var SuggestFramework_Action           = new Array();
var SuggestFramework_Capture          = new Array(); // Default = 1;
var SuggestFramework_Columns          = new Array(); // Default = 1;
var SuggestFramework_Delay            = new Array(); // Default = 1000;
var SuggestFramework_Heading          = new Array(); // Default = false;
var SuggestFramework_Name             = new Array();

// Internal
var SuggestFramework_Connection       = new Array();
var SuggestFramework_InputContainer   = new Array();
var SuggestFramework_OutputContainer  = new Array();
var SuggestFramework_Previous         = new Array();
var SuggestFramework_Proxy            = new Array();
var SuggestFramework_Suggestions      = new Array();
var SuggestFramework_SuggestionsIndex = new Array();