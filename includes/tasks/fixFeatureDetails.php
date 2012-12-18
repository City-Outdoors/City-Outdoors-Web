<?php
/**
 * @author James Baster  <james@jarofgreen.co.uk>
 * @copyright City of Edinburgh Council & James Baster
 * @license Open Source under the 3-clause BSD License
 * @url https://github.com/City-Outdoors/City-Outdoors-Web
 */
include dirname(__FILE__).'/../src/global.php';

$featureSearch = new FeatureSearch();
while ($feature = $featureSearch->nextResult()) {
	
	# title
	$item = $feature->getTitleItem();
	if ($item) {
		$feature->setTitle($item->getTitle());
		$item->__destruct();
	}
	unset($item);
	
	# thumbnail
	$featureContentSearch = new FeatureContentSearch();
	$featureContentSearch->approvedOnly();
	$featureContentSearch->hasImages();
	$featureContentSearch->promotedOnly();
	$featureContentSearch->forFeature($feature);
	$featureContent = $featureContentSearch->nextResult();
	if ($featureContent) {
		$feature->setThumbnailURL($featureContent->getThumbPictureURL());
	}
	
	unset($featureContentSearch);
		
	print ".";
}

print memory_get_peak_usage()."\n";