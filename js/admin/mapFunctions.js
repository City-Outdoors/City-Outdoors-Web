
var map;


function startMap(pointLat, pointLng) {

	var myOptions = {
		center: new google.maps.LatLng(pointLat, pointLng),
		zoom: 13,
		mapTypeId: google.maps.MapTypeId.ROADMAP 
	};
	map = new google.maps.Map(document.getElementById("mapInPage"), myOptions);
	
	new google.maps.Marker({
				position: new google.maps.LatLng(pointLat,pointLng), 
				map: map,
				title:"Hello World!",
				optimized: false				
			});



}




