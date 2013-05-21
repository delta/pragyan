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
			for(var i=0; i<eventsJSON.data.length; i=i+1){
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
	});
}

function initAllEventsMap(){
	var mapProp = {
		center:new google.maps.LatLng(10.56155,78.815768),
		zoom:17,
		mapTypeId:google.maps.MapTypeId.ROADMAP
	};

	allEventsMap = new google.maps.Map(document.getElementById("allEventGoogleMap"),mapProp);
	placeAllEventMarkers()
}

google.maps.event.addDomListener(window, 'load', initAllEventsMap);