var map, placed=0, marker;

function initialize(){
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

google.maps.event.addDomListener(window, 'load', initialize);