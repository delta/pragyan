var map, placed=0, marker;


function initializeAddEventMap(){
	var mapProp = {
		center:new google.maps.LatLng(10.76155,78.815768),
		zoom:15,
		mapTypeId:google.maps.MapTypeId.ROADMAP
	};

	map = new google.maps.Map(document.getElementById("addEventGoogleMap"),mapProp);

	google.maps.event.addListener(map, 'click', function(event) {
		placeMarker(event.latLng);
	});
}


google.maps.event.addDomListener(window, 'load', initializeAddEventMap);

function initializeEditEventMap(){
	var mapProp = {
		center:new google.maps.LatLng(10.76155,78.815768),
		zoom:15,
		mapTypeId:google.maps.MapTypeId.ROADMAP
	};

	map = new google.maps.Map(document.getElementById("editEventGoogleMap"),mapProp);


	google.maps.event.addListener(map, 'click', function(event) {
		placeMarker(event.latLng);
	});
	var oldlat=document.getElementById('lat').value;
	var oldlng=document.getElementById('lng').value;
	var oldloc = new google.maps.LatLng(oldlat, oldlng);

	marker = new google.maps.Marker({
		position: oldloc,
		map: map,
	});
	placed=1;
}

function placeMarker(location){
	if(placed==1){
		marker.setMap(null);
	}
	marker = new google.maps.Marker({
		position: location,
		map: map,
	});
	placed=1;
	document.getElementById('lat').value=location.lat();
	document.getElementById('lng').value=location.lng();
}

google.maps.event.addDomListener(window, 'load', initializeEditEventMap);


function placeAllEventMarkers(){
	var map, placed=0, marker;
	var ajx=$.ajax({
		type: "GET",
		url: "./+view&subaction=mobile&ipp=100",
		data: {}, 
		dataType: "json"
	});
	ajx.done(function(msg) {
		eventsJSON=eval(msg);
		if(eventsJSON.status=='success'){
		    var cdate=new Date();
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
			console.log(edt > cdate && sdt < cdate);
			if((sdt-cdate)/60000<=30 && (sdt-cdate)/60000>=-30){
			    
			    var eventLatlng = new google.maps.LatLng(eventsJSON.data[i].event_loc_y, eventsJSON.data[i].event_loc_x);
			    marker = new google.maps.Marker({
				    position: eventLatlng,
				    map: allEventsMap,
				    title: eventsJSON.data[i].event_name,
				    labelContent: "test",
				});
			    var infoboxContent="<span>" + eventsJSON.data[i].event_name + " at "+ eventsJSON.data[i].event_start_time.substring(0, 5)+"</span>"
				infowindow = new google.maps.InfoWindow({
					content: infoboxContent,
				    }).open(allEventsMap, marker);
			}
		    }
		}
	    });
}

function initAllEventsMap(){
    var mapProp = {
	center:new google.maps.LatLng(10.702384,78.81565),//10.009890,78.815600),
		//10.75155,78.815768
		zoom:17,
		mapTypeId:google.maps.MapTypeId.ROADMAP
	};

	allEventsMap = new google.maps.Map(document.getElementById("allEventGoogleMap"),mapProp);
	placeAllEventMarkers();
	    //	var bounds=
}

google.maps.event.addDomListener(window, 'load', initAllEventsMap);