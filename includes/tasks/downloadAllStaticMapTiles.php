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
	$feature->downloadStaticMapTile();
	print ".";
	sleep(1);
}
print "\n";

