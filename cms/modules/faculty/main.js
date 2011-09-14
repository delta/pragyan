function ConfirmAddData(confirmImage) {
	var thisVar=confirmImage;		
	var parent=thisVar.parentNode;
	var valueToAdd=parent.parentNode.getElementsByTagName("TD")[1].getElementsByClassName("addNewDataTextArea")[0].value;
	var sectionvalue=thisVar.id;
	var	sectionId=parseInt(sectionvalue.substr(14));
	if(valueToAdd=="") {
	   	alert("Enter a proper Data");
		return;
	  	}
	if(valueToAdd==null) return;
	var	dataToSend="addFacultyData="+valueToAdd+"&sectionId="+sectionId;
	$.ajax({
  		type: "POST",
  		url: "./+faculty",
  		data: dataToSend,
		success: function(msg){
			if(msg=="<span style='color:#fff'></span>Limit Exceeded") alert("Limit Exceeded");
			else window.location.href=window.location.href;
		}
	});	
}


function DeleteAddData (DeleteImage) {
	
  	var parent=DeleteImage.parentNode.parentNode.parentNode;
	var addImg=parent.parentNode.parentNode;
		addImg.getElementsByClassName("addData")[0].style.display="";
	  	parent.parentNode.removeChild(parent);
	
}



$(document).ready(function(){
	$(".headerFirstSectionConfirm").css({'display':'none'});
	
	
	$(".headerFirstSectionEdit").click(function(){
		this.style.display="none";
		var parent=this.parentNode;
			parent.getElementsByClassName("headerFirstSectionConfirm")[0].style.display="";
		var DataTableTr=parent.parentNode.getElementsByClassName("sectionDataInTable")[0];
		var headerValue=DataTableTr.getElementsByClassName("headerFirstSection")[0].innerHTML;
		var inpBox='<textarea class="changeData" style="width:100%">'
			$(DataTableTr).append(inpBox)
			DataTableTr.getElementsByClassName("changeData")[0].innerHTML=headerValue;
		});
	

	$(".headerFirstSectionConfirm").click(function(){
		this.style.display="none";
		var parent=this.parentNode;
			parent.getElementsByClassName("headerFirstSectionEdit")[0].style.display="block";
		var DataTableTr=parent.parentNode.getElementsByClassName("sectionDataInTable")[0];
		var textBoxNode=DataTableTr.getElementsByClassName("changeData")[0];
		var headerValue=textBoxNode.value;
		    DataTableTr.removeChild(textBoxNode);
		    DataTableTr.getElementsByClassName("headerFirstSection")[0].innerHTML=headerValue;
		var SectionId=parent.getElementsByClassName("headerFirstSectionEdit")[0].id
		var facultyId=parseInt(SectionId.substr(20));
		var dataToSend="updateDetail="+headerValue+"&facultyId="+facultyId;
		$.ajax({
  			type: "POST",
  			url: "./+faculty",
  			data: dataToSend
		});
		DataTableTr.getElementsByClassName("headerFirstSection")[0].style.display="";
	});
	


	$(".headerFirstSectionDelete").click(function(){
		var parent=this.parentNode.parentNode;
		var SectionId=parent.getElementsByClassName("headerFirstSectionDelete")[0].id
		var facultyId=parseInt(SectionId.substr(24));
		var dataToSend="DeleteFacultyId="+facultyId;
		$.ajax({
  			type: "POST",
  			url: "./+faculty",
  			data: dataToSend,
  			success:function(msg){
  				parent.parentNode.removeChild(parent);
  				}
			});	
		});


	$(".addData").click(function(){
		
		var addImage=this;
		    addImage.style.display="none";
		var parent=this.parentNode;
		var tableElement=parent.getElementsByTagName('TABLE')[0];
		var sectionvalue=this.id;
		var sectionId=parseInt(sectionvalue.substr(7));		
		var imgSrc=this.src;
		    imgSrc=imgSrc.substr(0,imgSrc.length-7);
		var appendData='<tr><td>';
			appendData+='<img src="'+imgSrc+'confirm.png" class="confirmAddData" id="confirmAddData'+sectionId+'" style="cursor:pointer;" onClick="ConfirmAddData(this)" />&nbsp;&nbsp;&nbsp;';
		    appendData+='<img src="'+imgSrc+'delete.png" class="deleteAddData" id="deleteAddData'+sectionId+'" style="cursor:pointer;" onClick="DeleteAddData(this)" />';
		    appendData+='</td><td>';
       		appendData+='<textarea class="addNewDataTextArea" style="width:100%;" /></td></tr>';
		$(tableElement).append(appendData);
		tableElement.style.display="";
		});
});


