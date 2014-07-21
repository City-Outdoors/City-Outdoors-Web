<?php
/**
 * @author James Baster  <james@jarofgreen.co.uk>
 * @copyright City of Edinburgh Council & James Baster
 * @license Open Source under the 3-clause BSD License
 * @url https://github.com/City-Outdoors/City-Outdoors-Web
 */
require 'includes/src/global.php';

header("Content-Type: text/javascript");
// TODO Send directives to tell browsers to cache this page. 

$collectionSearch = new CollectionSearch();

$collectionData = array();
while($collection = $collectionSearch->nextResult()) { 
	$collectionData[$collection->getId()] = array(
		'title'=>$collection->getTitle(),
		'slug'=>$collection->getSlug(),
		'icon_height'=>$collection->getIconHeight(),
		'icon_width'=>$collection->getIconWidth(),
		'icon_offset_x'=>$collection->getIconOffsetX(),
		'icon_offset_y'=>$collection->getIconOffsetY(),
		'icon_url'=>$collection->getIconURL(),
		'question_icon_height'=>$collection->getQuestionIconHeight(),
		'question_icon_width'=>$collection->getQuestionIconWidth(),
		'question_icon_offset_x'=>$collection->getQuestionIconOffsetX(),
		'question_icon_offset_y'=>$collection->getQuestionIconOffsetY(),
		'question_icon_url'=>$collection->getQuestionIconURL(),
		'organisation_id'=>$collection->getOrganisationId(),
	);
}

?>
var mapStartingLat = <?php print ($CONFIG->MAP_STARTING_MIN_LAT + ($CONFIG->MAP_STARTING_MAX_LAT - $CONFIG->MAP_STARTING_MIN_LAT)/2) ?>;
var mapStartingLng= <?php print ($CONFIG->MAP_STARTING_MIN_LNG + ($CONFIG->MAP_STARTING_MAX_LNG - $CONFIG->MAP_STARTING_MIN_LNG)/2) ?>;
var mapStartingMinLat = <?php print $CONFIG->MAP_STARTING_MIN_LAT ?>;
var mapStartingMaxLat = <?php print $CONFIG->MAP_STARTING_MAX_LAT ?>;
var mapStartingMinLng = <?php print $CONFIG->MAP_STARTING_MIN_LNG ?>;
var mapStartingMaxLng = <?php print $CONFIG->MAP_STARTING_MAX_LNG ?>;


var siteTitle = <?php print json_encode($CONFIG->SITE_TITLE) ?>;

var collectionData = <?php print json_encode($collectionData) ?>;

var mapMinZoom = <?php print $CONFIG->MAP_MIN_ZOOM ?>;
var mapMaxZoom = <?php print $CONFIG->MAP_MAX_ZOOM ?>;

