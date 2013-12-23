function submitUserDataToAcco(formval,userId) {
    var uid=document.getElementById("userid"+formval).value;
    var hostelName=document.getElementById("hostelname"+formval).value;
    var stay=document.getElementById("stay"+formval).value;
    var roomNos=document.getElementById("roomNo"+formval).value;
    if(stay==""||uid==""||hostelName=="") {
	alert("Please Fill All the required Details");
	return "";
    }
   var ajx=$.ajax({
	type: "POST",
	url: "./+view",
	data: {
	    subaction:"accoRegUser",
	    hostelname: hostelName,
	    userid :uid,
	    user_reg_value :userId,
	    roomNo :roomNos,
	    stay :stay
	}, 
	dataType: "html"    
       });//,(function(){
    ajx.done(function( msg ) {
	if(msg.substr(0,7)=="Success") {
	    alert( "Success ");
   	    document.getElementById("room_fetchData").innerHTML=msg.substr(7);
//	    document.getElementById("userid"+formval).parentNode.parentNode.style.display="none";	
	    document.getElementById("userid"+formval).disabled=true;
	    document.getElementById("hostelname"+formval).disabled=true;
	    document.getElementById("stay"+formval).disabled=true;
	    document.getElementById("roomNo"+formval).disabled=true;
	    document.getElementById("edit"+formval).style.display="";
	    document.getElementById("submit"+formval).style.display="none";
	}
	else alert( msg );
	});
}

function editField(formval,userId) {
    document.getElementById("userid"+formval).disabled=false;
    document.getElementById("hostelname"+formval).disabled=false;
    document.getElementById("stay"+formval).disabled=false;
    document.getElementById("roomNo"+formval).disabled=false;
    document.getElementById("edit"+formval).style.display="none";
    document.getElementById("update"+formval).style.display="";
}

function updateData(formval,userId) {
    var uid=document.getElementById("userid"+formval).value;
    var hostelName=document.getElementById("hostelname"+formval).value;
    var stay=document.getElementById("stay"+formval).value;
    var roomNos=document.getElementById("roomNo"+formval).value;
    if(stay==""||uid==""||hostelName=="") {
	alert("Please Fill All the required Details");
	return "";
    }
   var ajx=$.ajax({
	type: "POST",
	url: "./+view",
	data: {
	    subaction:"accoRegUserUpdate",
	    hostelname: hostelName,
	    userid :uid,
	    user_reg_value :userId,
	    roomNo :roomNos,
	    stay:stay

	}, 
	dataType: "html"    
   });
    ajx.done(function( msg ) {
	if(msg.substr(0,7)=="Success") {
	    alert("Success");
  	    document.getElementById("room_fetchData").innerHTML=msg.substr(7);
	    document.getElementById("userid"+formval).disabled=true;
	    document.getElementById("hostelname"+formval).disabled=true;
	    document.getElementById("stay"+formval).disabled=true;
	    document.getElementById("roomNo"+formval).disabled=true;
	    document.getElementById("edit"+formval).style.display="";
	    document.getElementById("update"+formval).style.display="none";
	    
	}
	else 	alert(msg);

    });
}