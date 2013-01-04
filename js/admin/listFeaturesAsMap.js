
var map;
var mapMarkerClusterer;
var infoWindow;


function startMap() {

	var myOptions = {
		center: new google.maps.LatLng(mapStartingLat, mapStartingLng),
		zoom: 13,
		mapTypeId: google.maps.MapTypeId.ROADMAP 
	};
	map = new google.maps.Map(document.getElementById("MainMap"), myOptions);

	var mcOptions = {
		maxZoom: 16,
		styles: [
				{
					hideText: true,
					height: 39,
					url: "/img/marker-cluster-small.png",
					width: 28,
					anchor: [6]
				},				
				{
					hideText: true,				
					height: 52,
					url: "/img/marker-cluster-med.png",
					width: 38,
					anchor: [10]
				},
				{
					hideText: true,				
					height: 64,
					url: "/img/marker-cluster-large.png",
					width: 47,
					anchor: [15]
				},				
			]
		};	
	mapMarkerClusterer = new MarkerClusterer(map,[],mcOptions);

	infoWindow = new google.maps.InfoWindow({
				content: 'Loading ...'
			});
			
	google.maps.event.addListener(map, 'click', function(event) {
		var pos = event.latLng;
		$('#lat').val(pos.lat());
		$('#lng').val(pos.lng());
	});		
}


function addFeature(id, lat, lng) {
	var data = {
			position: new google.maps.LatLng(lat,lng), 
			title: String(id),
			optimized: false
		};
	var m = new google.maps.Marker(data);
	google.maps.event.addListener(m, "click", function() {
		mapMarkersMarkerClicked(id,this); 
	});
	mapMarkerClusterer.addMarker(m);	
}
function mapMarkersMarkerClicked(id,marker) {
	var html = '<a class="mapPopup" href="/admin/feature.php?id='+id+'">Feature '+id+'</a>';
	infoWindow.setContent(html);
	infoWindow.open(map,marker);
}
