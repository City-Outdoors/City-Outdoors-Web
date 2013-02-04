

var History;
var markerLayer;
var map;
var mapMarkerClusterer;
var infoWindow;

$(document).ready(function() {

	var center;
	if (loadPageAtLat && loadPageAtLng) {
		center = new google.maps.LatLng(loadPageAtLat, loadPageAtLng);
	} else {
		center = new google.maps.LatLng(mapStartingLat, mapStartingLng);
	}
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
	
	if (loadPageAtLat && loadPageAtLng) {
		map.setZoom(17);
	} else {
		map.fitBounds(
			new google.maps.LatLngBounds( 
				new google.maps.LatLng(mapStartingMinLat, mapStartingMinLng), 
				new google.maps.LatLng(mapStartingMaxLat, mapStartingMaxLng)
			)
		);
	}


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

	google.maps.event.addListener(map, 'bounds_changed', mapMarkersMapEvent);
	//mapMarkersTimerEvent();

	infoWindow = new google.maps.InfoWindow({
			content: 'Loading ...'
		});

	var html = '';
	html += '<div class="mapCategoriesTitle">Show/hide:</div>';
	html += '<ul>';


	for(id in collectionData) {
		html += '<li class="collection-'+collectionData[id].slug+'"><label><input type="checkbox" name="'+id+'" value="1">'+collectionData[id].title+'</label></li>';
	}
	html += '</ul>';
	$('#mapLayers').html(html);
	$('#mapLayers input[type="checkbox"]').prop("checked", true).change(mapMarkersMapEventWithClear);
});


function mapMarkersGetIconForCollection(collectionID) {
	return new google.maps.MarkerImage(
			collectionData[collectionID].icon_url,
			new google.maps.Size(parseInt(collectionData[collectionID].icon_width), parseInt(collectionData[collectionID].icon_height)),
			new google.maps.Point(0,0),
			new google.maps.Point(parseInt(collectionData[collectionID].icon_offset_x), parseInt(collectionData[collectionID].icon_offset_y))
		);
}

function mapMarkersGetQuestionIconForCollection(collectionID) {
	return new google.maps.MarkerImage(
			collectionData[collectionID].question_icon_url,
			new google.maps.Size(parseInt(collectionData[collectionID].question_icon_width), parseInt(collectionData[collectionID].question_icon_height)),
			new google.maps.Point(0,0),
			new google.maps.Point(parseInt(collectionData[collectionID].question_icon_offset_x), parseInt(collectionData[collectionID].question_icon_offset_y))
		);
}

function mapMarkersGetDefaultIcon() {
	return new google.maps.MarkerImage(
			'/img/marker-usercontent-med.png',
			new google.maps.Size(47,64),
			new google.maps.Point(0,0),
			new google.maps.Point(23,64)
		);
}


var	mapMarkersEndTimer = null;
var	mapMarkersDelayBeforeRedraw= 1000;
var	mapMarkersLoadData= null;
var mapMarkersMarkers = {};
var mapMarkersAjax =  null;
var mapMarkersClearOnTimerEvent =  false;
function mapMarkersMapEventWithClear() {
	mapMarkersClearOnTimerEvent = true;
	mapMarkersMapEvent();
}
function mapMarkersMapEvent() {
	$('#mapMarkersLoading').show();
	if (mapMarkersEndTimer) clearTimeout(mapMarkersEndTimer);
	mapMarkersEndTimer = setTimeout('mapMarkersTimerEvent()', mapMarkersDelayBeforeRedraw);		
}
function mapMarkersTimerEvent() {
	if (mapMarkersAjax) mapMarkersAjax.abort();
	mapMarkersEndTimer = null;
	if (mapMarkersClearOnTimerEvent) {
		mapMarkersClearOnTimerEvent = false;
		mapMarkersClearAll();
	}
	var bounds = map.getBounds();
	var bottomLeft = bounds.getSouthWest();
	var topRight = bounds.getNorthEast();
	var url = '/getMapFeatures.php?left='+bottomLeft.lng()+"&right="+topRight.lng()+"&top="+topRight.lat()+"&bottom="+bottomLeft.lat()+"&collections="; 
	$('#mapLayers input[type="checkbox"]').each(function(index) {
		var t = $(this);
		if (t.prop("checked")) {
			url += t.attr('name')+",";
		}			
	});
	mapMarkersAjax = $.ajax({			
		url: url,
		success: function(data) {
			if (data.result) {
				for(i in data.data) {
					var thisMarkerData = data.data[i];
					if (!(thisMarkerData.id in mapMarkersMarkers)) {
						mapMarkersAddMarker(thisMarkerData.lat,thisMarkerData.lng, thisMarkerData.id, 
							thisMarkerData.collectionIDS, thisMarkerData.title, thisMarkerData.thumbnailURL , 
							thisMarkerData.inHiddenCollection, thisMarkerData.answeredAllQuestions, data.userID);
					}
				}					
			}
			$('#mapMarkersLoading').hide();
		}
	});
}
function mapMarkersAddMarker(lat, lng, id, collectionIDS, title, thumbnailURL, inHiddenCollection, answeredAllQuestions, userID) {
	// this is in an seperate function so the ID in the anonymous function is picked up properly 
	// http://stackoverflow.com/questions/2489483/google-maps-marker-click-event
	var data = {
			position: new google.maps.LatLng(lat,lng), 
			title:title,
			optimized: false,
		};
	if (collectionIDS.length > 0) {
		if (userID && !answeredAllQuestions) {
			data.icon = mapMarkersGetQuestionIconForCollection(collectionIDS[0]);
		} else {
			data.icon = mapMarkersGetIconForCollection(collectionIDS[0]);			
		}
	} else {
		data.icon = mapMarkersGetDefaultIcon();
	}
	mapMarkersMarkers[id] = new google.maps.Marker(data);
	google.maps.event.addListener(mapMarkersMarkers[id], "click", function() {
		mapMarkersMarkerClicked(id,title,this,thumbnailURL,inHiddenCollection); 
	});
	mapMarkerClusterer.addMarker(mapMarkersMarkers[id]);
};
function mapMarkersMarkerClicked(id,title,marker,thumbnailURL,inHiddenCollection) {
	var html;
	if (inHiddenCollection) {
		html = 'Loading please wait ...';
		$.ajax({			
				url: '/getMapFeatureInfoWindow.php?id='+id,
				success: function(data) {
							infoWindow.setContent(data);
				}
			});				
	} else {
		if (!title) title = "Images and comments";
		html = '<a class="mapPopup" href="/featureDetails.php?id='+id+'"><h3>'+title+'</h3>';
		if (thumbnailURL) html += '<img src="'+thumbnailURL+'">';
			html += '</a>'
	}
	infoWindow.setContent(html);
	infoWindow.open(map,marker);
}
function mapMarkersClearAll() {
	for(id in mapMarkersMarkers) {
		mapMarkerClusterer.removeMarker(mapMarkersMarkers[id]);
		delete mapMarkersMarkers[id];
	}
}


