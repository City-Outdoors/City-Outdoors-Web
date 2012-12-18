<?php
/**
 * @author James Baster  <james@jarofgreen.co.uk>
 * @copyright City of Edinburgh Council & James Baster
 * @license Open Source under the 3-clause BSD License
 * @url https://github.com/City-Outdoors/City-Outdoors-Web
 */
require 'includes/src/global.php';

$currentUser = getCurrentUser();

$featureSearch = new FeatureSearch();
$featureSearch->withinBounds($_GET['left'], $_GET['right'], $_GET['top'], $_GET['bottom']);
$featureSearch->visibleToUser($currentUser);

$hiddenCollection = Collection::loadBySlug($CONFIG->HIDDEN_COLLECTION_SLUG);

if (isset($_GET['collections'])) {
	// Sometimes the IDS for all possible collections are passed back. 
	// In this case, we want to do no filtering so features with content only are included.
	// If the use has selected some collections only (ie deselected some) then we do want to filter.
	$collectionIDs = explode(",", $_GET['collections']);
	$collectionSearch = new CollectionSearch();
	$anyCollectionNotSelected = false;
	$collectionsToAdd = array();
	while($collection = $collectionSearch->nextResult()) {
		if (in_array($collection->getId(), $collectionIDs)) {
			$collectionsToAdd[] = $collection;
		} else {
			$anyCollectionNotSelected = true;
		}
	}
	if ($anyCollectionNotSelected) {
		if ($collectionsToAdd) {
			foreach($collectionsToAdd as $collection) $featureSearch->withinCollection ($collection);
		} else {
			// special case; user appears to have selected no collections. Well, ok ...
			header('Content-type: application/json');
			print json_encode(array('data'=>array(),'result'=>true));
			die();
		}
	}
}


$data = array('data'=>array(),'result'=>true);
while($feature = $featureSearch->nextResult()) {
	$inHiddenCollection = in_array($hiddenCollection->getId(), $feature->getCollectionIDS());
	$data['data'][] = array(
			'id'=>$feature->getId(),
			'lat'=>$feature->getPointLat(),
			'lng'=>$feature->getPointLng(),
			'collectionIDS'=>$feature->getCollectionIDS(),
			'title'=>$feature->getTitle(),
			'thumbnailURL'=>$feature->getThumbnailURL(),
			'inHiddenCollection'=>$inHiddenCollection
		);
}


header('Content-type: application/json');
print json_encode($data);

