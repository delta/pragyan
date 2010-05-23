function initXMLRequestObject()
{
	var XMLHttpRequestObject = false;
	try
	{
		if (window.XMLHttpRequest)
		{
			XMLHttpRequestObject = new XMLHttpRequest();
		} 	
		else if (window.ActiveXObject)
		{
			XMLHttpRequestObject = new ActiveXObject("Microsoft.XMLHTTP");
		}		
	}
	catch(error){}
	return XMLHttpRequestObject;
}
function makeAJAXRequest(url,stateFunc)
{
	var XMLHttpRequestObject = initXMLRequestObject();
	var iniText=document.getElementById("ajaxloader").value;
	XMLHttpRequestObject.open("GET",url);
	XMLHttpRequestObject.onreadystatechange = function()
		{
		
			if (XMLHttpRequestObject.readyState == 1)
			{	
				document.getElementById("ajaxloader").value="Loading...";
				
			}
			if (XMLHttpRequestObject.readyState == 4 && XMLHttpRequestObject.status == 200)
			{
				document.getElementById("ajaxloader").value=iniText;
				retval=XMLHttpRequestObject.responseText;
				var funcCall=stateFunc+"("+XMLHttpRequestObject.responseText+")";
				eval(funcCall);
			}
		

		}
	
	XMLHttpRequestObject.send(null);
	
}
