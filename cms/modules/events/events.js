function cmsShow(type,message) {
	if(message != '')
		$("#cms-content").prepend("<div class='cms-" + type + "'>" + message + "</div>");
	else
		$("#cms-content").prepend("");
}

function submitAddEventData() {
	console.log("lkjlkj");
	var ajx=$.ajax({
		type: "POST",
		url: "./+eventshead",
		data: {
			type: "add",
			eventName: document.getElementById("eventName").value,
			eventCluster: document.getElementById("eventCluster").value,
			eventFormId: document.getElementById("eventFormId").value,
			eventVenue: document.getElementById("eventVenue").value,
			eventDate: document.getElementById("eventDate").value.split(".").reverse().join("-"),
			eventDesc: document.getElementById("eventDesc").value,
			eventDesc: document.getElementById("eventDesc").value,
			eventStartTime: document.getElementById("eventStartTime").value,
			eventEndTime: document.getElementById("eventEndTime").value,
			lat: document.getElementById("lat").value,
			lng: document.getElementById("lng").value,
		}, 
		dataType: "html"
	});
	ajx.done(function(msg) {
		if(msg=="Valid") {
			window.location = ("./+eventshead");
			var isAdded=1;
			window.onload(function(){
				cmsShow("info", "Event successfully added");
			});
		}
		else{
			cmsShow('error', "Invalid data");
		}
	});
}

function submitEditEventData(event_id) {
	console.log("EDITING");
	var ajx=$.ajax({
		type: "POST",
		url: "./+eventshead",
		data: {
			type: "edit",
			eventId: event_id,
			eventName: document.getElementById("eventName").value,
			eventCluster: document.getElementById("eventCluster").value,
			eventFormId: document.getElementById("eventFormId").value,
			eventVenue: document.getElementById("eventVenue").value,
			eventDate: document.getElementById("eventDate").value.split(".").reverse().join("-"),
			eventDesc: document.getElementById("eventDesc").value,
			eventDesc: document.getElementById("eventDesc").value,
			eventStartTime: document.getElementById("eventStartTime").value,
			eventEndTime: document.getElementById("eventEndTime").value,
			lat: document.getElementById("lat").value,
			lng: document.getElementById("lng").value,
		}, 
		dataType: "html"
	});
	ajx.done(function(msg) {
		if(msg=="Valid") {
			window.location = ("./+eventshead");
			var isAdded=1;
			window.onload(function(){
				cmsShow("info", "Event successfully edited");
			});
		}
		else{
			cmsShow('error', "Invalid data");
		}
	});
}

function showSchedule(pcmid){
	console.log(pcmid);
	var ajx=$.ajax({
		type: "GET",
		url: "./+view&subaction=mobile&ipp=100",
		data: {}, 
		dataType: "json"
	});
	ajx.done(function(msg) {
		eventsJSON=eval(msg);
		if(eventsJSON.status=='success'){
			all_events=Array()
			var date = new Date();
			var d = date.getDate();
			var m = date.getMonth();
			var y = date.getFullYear();
			for(var i=0; i<eventsJSON.data.length; i=i+1){
				year=eventsJSON.data[i].event_date.substring(6, 10)
				month=eventsJSON.data[i].event_date.substring(3, 5)
				day=eventsJSON.data[i].event_date.substring(0, 2)
				single_event={"title":eventsJSON.data[i].event_name,
				start: new Date(year, month, day, 11, 2),
				end: new Date(year, month, day, 14, 5),
				allDay: false,
			}
			all_events.push(single_event);
		}
		var calendar = $('#calendar').fullCalendar({
			header: {
				left: 'prev,next today',
				center: 'title',
				right: 'month,agendaWeek,agendaDay'
			},
			selectable: true,
			selectHelper: true,
			editable: false,
			events: all_events,
		});

	}
});
}

function deleteEvent(eventid) {
	var r=confirm("Are you sure?");
	if (r==true){
		var ajx=$.ajax({
			type: "POST",
			url: "./+eventshead&subaction=deleteEvent",
			data: {
				eventId: eventid,
			},
			dataType: "html"
		});
		ajx.done(function(msg) {
			if(msg=="Success") {
				window.location = ("./+eventshead");
				cmsShow("info", "Event deleted");
			}
			else{
				cmsShow('error', "Error");
			}
		});
	}
}


function confirmParticipant(){
	if(confirm("Are You Sure?") == false){
		return false;
	}
}

function confirmDelete(){
    if(confirm("Are you Sure?") == false)
	return false;
}


function editParticipant(userid,eventid){
	$(".userDataDisp"+userid).css('display','none');
	$(".userDataEdit"+userid).css('display','block');
	$(".userDataEditVal"+userid).css('display','block');
}

function cancelEditParticipant(userid,eventid){
	$(".userDataDisp"+userid).css('display','block');
	$(".userDataEdit"+userid).css('display','none');
	$(".userDataEditVal"+userid).css('display','none');
}

function updateParticipant(gotoaction,userid,teamid,formid,rowId,eventId){
	//alert(gotoaction);
	var getAllEdits = document.getElementsByClassName('userDataEditVal'+userid);
	var rowValue = new Array();
	for(var i=0;i<getAllEdits.length;i++)
		rowValue.push(getAllEdits[i].value);
	//var rowValue="";
	/*for(var i=0;i<rowValues.length;i++)
		rowValue+=getAllEdits[i]+",";
	rowValue = */
	rowValue = rowValue.join("::");
	console.log(rowValue,rowId);
	//rowId = rowId.toString();
	var actUrl = "./+"+gotoaction+"&subaction=editParticipant";
	var ajaxRequest = $.ajax({
		type : "POST",
		datatype : "html",
		url : actUrl,
		data : {
			formId : formid,
			userId : userid,
			rowValue : rowValue,
			rowId : rowId,
			eventId : eventId,
			teamId : teamid,
		},
		success : function(data){
			$('#partRow'+userid).html(data);
		}
	});
/*	$(".userDataDisp"+userid).css('display','block');
	$(".userDataEdit"+userid).css('display','none');
	$(".userDataEditVal"+userid).css('display','none');*/
}

function downloadDetails(gotoaction,eventId){
	var actUrl = "./+"+gotoaction+"&subaction=downloadDetails";
	var ajaxRequest = $.ajax({
			type : "POST",
			datatype : "html",
			url : actUrl,
			data : {
				eventId : eventId,
			}
		});
}

function editParticipantRank(userid,eventid){
	$("#userId"+userid).css('display','none');
	$("#userIdEdit"+userid).css('display','block');
	$(".editRankButtons"+userid).css('display','none');
	$(".editRankOptionButtons"+userid).css('display','block');
}

function cancelEditRank(userid,eventid){
	$("#userId"+userid).css('display','block');
	$("#userIdEdit"+userid).css('display','none');
	$(".editRankButtons"+userid).css('display','block');
	$(".editRankOptionButtons"+userid).css('display','none');
}

function confirmEditRank(gotoaction,userid,eventid){
	var newRank = $("#userIdEdit"+userid).val();
	var actUrl = "./+"+gotoaction+"&subaction=editParticipantRank"
	var ajaxRequest = $.ajax({
		type : "POST",
		datatype : "html",
		url : actUrl,
		data :{
			eventId : eventid,
			userId : userid,
			newRank : newRank,
		},
		success:function(data){
				$('#userId'+userid).html(data);
				$("#userId"+userid).css('display','block');
				$("#userIdEdit"+userid).css('display','none');
				$(".editRankButtons"+userid).css('display','block');	
				$(".editRankOptionButtons"+userid).css('display','none');	
			}
	});
	/*ajaxRequest.done(function(msg){
		console.log(msg);
	});
	cmsShow("info","Success!");*/
}

function lockConfirm(){
	if(confirm("Do you want to lock this event?")){
		if(confirm("Are You Sure?") == false)
			return false;
	}
	else
		return false;
}

function unlockConfirm(){
	if(confirm("Do you want to unlock this event?")){
		if(confirm("Are You Sure?") == false)
			return false;
	}
	else
		return false;
}

function submitAddProcurementData() {
	var ajx=$.ajax({
		type: "POST",
		url: "./+ochead",
		data: {
			eventName: document.getElementById("eventName").value,
			procurementName: document.getElementById("procurementName").value,
			quantity: document.getElementById("quantity").value,
		}, 
		dataType: "html"
	});
	ajx.done(function(msg) {
		if(msg=="Valid") {
			window.location = ("./+ochead&subaction=addEventProcurement");
			var isAdded=1;
			window.onload(function(){
				cmsShow("info", "Procurement successfully added");
			});
		}
		else if(msg=="Invalid"){
			cmsShow('error', "Invalid data");
		}
		else
			cmsShow('error',msg);
	});
}

function submitEditProcurementData(eventnum) {
	var ajx=$.ajax({
		type: "POST",
		url: "./+ochead",
		data: {
			eventName: document.getElementById("eventName").value,
			procurementName: document.getElementById("procurementName").value,
			editquantity: document.getElementById("quantity").value,
			eventnum: eventnum,
		}, 
		dataType: "html"
	});
	ajx.done(function(msg) {
		if(msg=="Valid") {
			window.location = ("./+ochead&subaction=addEventProcurement");
			var isAdded=1;
			window.onload(function(){
				cmsShow("info", "Procurement successfully added");
			});
		}
		else if(msg=="Invalid"){
			cmsShow('error', "Invalid data");
		}
		else
			cmsShow('error',msg);
	});
}

function submitAddProc() {
	var ajx=$.ajax({
		type: "POST",
		url: "./+ochead",
		data: {
			newProc: document.getElementById("newProc").value,
		}, 
		dataType: "html"
	});
	ajx.done(function(msg) {
		if(msg=="Valid") {
			window.location = ("./+ochead&subaction=addProcurement");
			var isAdded=1;
			window.onload(function(){
				cmsShow("info", "Procurement successfully added");
			});
		}
		else if(msg=="Exists"){
			cmsShow('error', " Procurement already exists");
		}
		
		else{
			cmsShow('error', "Invalid data");
		}
	});
}

function deleteProcurement(eventName) {
	var r=confirm("Are you sure?");
	if (r==true){
		var ajx=$.ajax({
			type: "POST",
			url: "./+ochead&subaction=deleteProcurement",
			data: {
				eventname: eventName,
			},
			dataType: "html"
		});
		ajx.done(function(msg) {
			if(msg=="Success") {
				window.location = ("./+ochead&subaction=viewAll");
				cmsShow("info", "Procurement deleted");
			}
			else{
				cmsShow('error', "Error");
			}
		});
	}
}

function getUpcomingEventsTable(pmcid){
	$("#upcomingEventTable").html("");
	var ajx=$.ajax({
		type: "GET",
		url: "./+view&subaction=mobile&ipp=100",
		data: {}, 
		dataType: "json"
	    });
	ajx.done(function(msg) {
		eventsJSON=eval(msg);
		if(eventsJSON.status=='success'){
		    var cdate = new Date();
		    //console.log(cdate);
		    for(var i=0; i<eventsJSON.data.length; i=i+1){
			var event_date=eventsJSON.data[i].event_date;
			var event_start_time=eventsJSON.data[i].event_start_time;
			var year=event_date.split("-")[0];
			var month=event_date.split("-")[1] - 1;
			var day=event_date.split("-")[2];

			var hour=event_start_time.split(":")[0];
			var min=event_start_time.split(":")[1];
			var sec=event_start_time.split(":")[2];
			    
			var sdt=new Date(year, month, day, hour, min, sec);

			var event_end_time=eventsJSON.data[i].event_end_time;
			var hour=event_end_time.split(":")[0];
			var min=event_end_time.split(":")[1];
			var sec=event_end_time.split(":")[2];

			var edt=new Date(year, month, day, hour, min, sec);

			//console.log(dt);
			//console.log(edt-cdate);
			console.log(edt > cdate && sdt < cdate);
			//edt > cdate && sdt < cdate
			if((sdt-cdate)/60000<=30 && (sdt-cdate)/60000>=-30){
			//if(1){
			    var row_string="<tr><td style='font-size:2em;border:1px solid #C3AC7A; border-radius: 5px'>"+eventsJSON.data[i].event_name+" at "
				+eventsJSON.data[i].event_venue+" at "
				+eventsJSON.data[i].event_start_time.substring(0, 5)+"</tr></td>"
				$("#upcomingEventTable").append(row_string);
			}
		}
	    }
	    });

}
