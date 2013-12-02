function cmsShow(type,message) {
	if(message != '')
		$("#cms-content").prepend("<div class='cms-" + type + "'>" + message + "</div>");
	else
		$("#cms-content").prepend("");
}

function submitAddEventData() {
	var ajx=$.ajax({
		type: "POST",
		url: "./+eventshead",
		data: {
			eventName: document.getElementById("eventName").value,
			eventVenue: document.getElementById("eventVenue").value,
			eventDate: document.getElementById("eventDate").value,
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
			window.location = ("./+eventshead&subaction=addEvent");
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
				window.location = ("./+eventshead&subaction=viewAll");
				cmsShow("info", "Event deleted");
			}
			else{
				cmsShow('error', "Error");
			}
		});
	}
}
