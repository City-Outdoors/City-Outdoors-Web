

var markerLayer;
var map;
var infoWindow;


$(document).ready(function() {

	var center = new google.maps.LatLng(mapStartingLat, mapStartingLng)
	var myOptions = {
		center: center,
		mapTypeId: google.maps.MapTypeId.ROADMAP,
		styles: mapStyle,
		minZoom: mapMinZoom, 
		maxZoom: mapMaxZoom, 
    mapTypeControl: false,
    streetViewControl: false		
	};
	map = new google.maps.Map(document.getElementById("map"), myOptions);
	
	infoWindow = new google.maps.InfoWindow({
			content: 'Loading ...'
		});
	
	for(id in collectionData) {
		collectionData[id].icon = new google.maps.MarkerImage(
				collectionData[id].icon_url,
				new google.maps.Size(collectionData[id].icon_width, collectionData[id].icon_height),
				new google.maps.Point(0,0),
				new google.maps.Point(collectionData[id].icon_offset_x, collectionData[id].icon_offset_y)
			); 
	}
	
	var bounds = new google.maps.LatLngBounds();
	var hasData = false;
	for (id in markerData) {
		var position = new google.maps.LatLng(markerData[id].lat,markerData[id].lng);
		addMarker(position, id, markerData[id].collectionID, markerData[id].title, markerData[id].thumbnailURL);
		bounds.extend(position);
		hasData = true;
	}
	map.fitBounds(bounds);
	
	if (!hasData) {
		map.fitBounds(
			new google.maps.LatLngBounds( 
				new google.maps.LatLng(mapStartingMinLat, mapStartingMinLng), 
				new google.maps.LatLng(mapStartingMaxLat, mapStartingMaxLng)
			)
		);
	};
	
	$('ul#collectionListList li').hover(
		function () {
			var id = $(this).attr('id').substr(18);
			highlightFeature(id);
		}, 
		function () {
			// nowt
		}
	);

});

function addMarker(position, id, collectionID, title, thumbnailURL) {
	// this is in an seperate function so the ID in the anonymous function is picked up properly 
	// http://stackoverflow.com/questions/2489483/google-maps-marker-click-event
	var data = {
			position: position, 
			title:title,
			map: map,
			optimized: false,
			icon: collectionData[collectionID].icon
		};
	var marker = new google.maps.Marker(data);
	google.maps.event.addListener(marker, "click", function() {
		markerClicked(id,title,this, thumbnailURL); 
	});
}

function markerClicked(id, title,marker, thumbnailURL) {
	$('#collectionListList li').removeClass('current');
	$('#collectionListItem'+id).addClass('current');
	
	var html;
	if (!title) title = "Images and comments";
	if (inHiddenCollection) {
		html = '<h3>'+title+'</h3>';
	} else {
		html = '<a class="mapPopup" href="/featureDetails.php?id='+id+'"><h3>'+title+'</h3>';
		if (thumbnailURL) html += '<img src="'+thumbnailURL+'">';
			html += '</a>'
	}
	infoWindow.setContent(html);
	infoWindow.open(map,marker);
	
}

function highlightFeature(id) {
	var position = new google.maps.LatLng(markerData[id].lat,markerData[id].lng);
	map.panTo(position);
	if (map.getZoom() < 14) {
		map.setZoom(14);
	}
}

