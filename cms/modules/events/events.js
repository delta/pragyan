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


//Test... (Should be changed)
function confirmParticipant(userid,eventid){
        var option = confirm("Are you sure?");
        if(option == true){
                var ajaxReq = $.ajax({
                        type:"POST",
                        dataType :"html",
                        url:"./+qa&subaction=confirmParticipant",
                        data:{
                                userid:userid,
                                eventid:eventid,
                        }
                });
                ajaxReq.done(function(){
                        cmsShow("info","Participant Added.");
                });
        }
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
