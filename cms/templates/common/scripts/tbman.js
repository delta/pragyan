      <link rel="stylesheet" type="text/css" href="query.css"/>
      
	 
	 function toggle(imgname)
	 {
	   if(document.getElementById("chan"+imgname).value=="notchanged")
	     {	
	       document.getElementById("dat"+imgname).style.display="none";
	       document.getElementById("inp"+imgname).style.display="";
	       document.getElementById("chan"+imgname).value="changed";
	       document.getElementById("checkd"+imgname).checked="checked";
	       document.getElementById("checki"+imgname).checked="checked";
	     }
	   else if(document.getElementById("chan"+imgname).value=="changed")
		{	
		  document.getElementById("dat"+imgname).style.display="";
		  document.getElementById("inp"+imgname).style.display="none";
		  document.getElementById("chan"+imgname).value="notchanged";
		  document.getElementById("checkd"+imgname).checked="";
		  document.getElementById("checki"+imgname).checked="";
		}
	   statusOfUpdateButton();
	   statusOfDeleteSelected();
	 }
    function statusOfDeleteSelected()
    {
      var i=0;
      var j=document.getElementById("noOfRows").value;
      document.getElementById("deleteSelectedText").style.display="";
      document.getElementById("deleteSelectedLink").style.display="none";
      for(i=0;i<j;i++)
	{
	  if((document.getElementById("checki"+i).value=="changed")||(document.getElementById("checkd"+i).checked==true))
	    {
	      document.getElementById("deleteSelectedText").style.display="none";
	      document.getElementById("deleteSelectedLink").style.display="";
	      break;
	    }	      
	}
    }
    function statusOfUpdateButton()
    {
      var i=0;
      var j=document.getElementById("noOfRows").value;
      document.getElementById("updateButton").style.display="none";
      for(i=0;i<j;i++)
	{
	  if(document.getElementById("chan"+i).value=="changed"||document.getElementById('checkiaddRow').checked==true)
	    {
	      document.getElementById("updateButton").style.display="";
	      break;
	    }
	}
    }
    function challtoggle(t)
    {
      var i=0;
      //	var j=document.f1.elements.length;
      var j=document.getElementById("noOfRows").value;
      if(t.checked==true)
	for(i=0;i<j;i++)
	  {
	    document.getElementById("checkd"+i).checked="checked";
	    document.getElementById("checki"+i).checked="checked";
	  }
      else
	for(i=0;i<j;i++)
	  {
	    document.getElementById("checkd"+i).checked="";
	    document.getElementById("checki"+i).checked="";
	  }
      statusOfDeleteSelected();
    }
  
  function update()
  {
  	document.getElementById("buttonpressed").value="updatebutton";
  	var j=document.getElementById("noOfRows").value;
  	for(i=0;i<j;i++)
   	{
  	 	if(document.getElementById("checki"+i).checked==true)
		  document.getElementById("buttonpressed").value+="|"+i;
		}
		if(document.getElementById("checki"+"addRow").checked==true)
		document.getElementById("buttonpressed").value+="|"+i;
  } 
  function deleteRow(t)
  {
  	document.getElementById("buttonpressed").value="deletebutton";
    if(t=="selected")
		{
	 		var j=document.getElementById("noOfRows").value;
   		for(i=0;i<j;i++)
   		{
  		 	if(document.getElementById("checkd"+i).checked==true)
			  document.getElementById("buttonpressed").value+="|"+i;
			}
	  //if they were deleted from the delete all button
	  //send php a no of comma seperated values.... then make it for all the information of 
	  //of all those rows and not only the row no..... because of the possibilty of use of "WHERE" in the
	  //"SELECT" statement so that the results dont come row wise

	  //if they were deleted from the usual delete button
	  //send only one varible to php then make it scan the whole info of that rows(same as above)
	  //document.getElementById("toDelete").value=t;
	  //submit;
		}
		else
			document.getElementById("buttonpressed").value="deletebutton"+"|"+t;
		document.f1.submit();
   }
   function alternateRowColor(t)
   {
      if(!document.getElementsByTagName) return;
      var table=document.getElementById(t);
      var rows=table.getElementsByTagName("tr");
      for(i=0;i<rows.length;i++)
	{
	  if(i==0)
	    rows[i].className="first";
	  else if((i-1)%4<2)
	    rows[i].className="even";
	  else
	    rows[i].className="odd";
	}
    }
