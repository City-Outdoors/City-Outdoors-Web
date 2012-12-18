<?php
/**
 * @author James Baster  <james@jarofgreen.co.uk>
 * @copyright City of Edinburgh Council & James Baster
 * @license Open Source under the 3-clause BSD License
 * @url https://github.com/City-Outdoors/City-Outdoors-Web
 */

class FeatureTest extends AbstractTest {
	
	
	function testExpandBounds() {
		global $CONFIG;
		
		$CONFIG->LAT_ACCURACY = 0.000005;
		$CONFIG->LNG_ACCURACY = 0.000005;

		$db = $this->setupDB();
		
		
		$feature = Feature::findOrCreateAtPosition(55, 2);		
		$featureMax = Feature::findOrCreateAtPosition(56, 3);		
		$featureMin = Feature::findOrCreateAtPosition(54, 1);		

		
		$feature->expandToIncludeFeature($featureMax);
		$feature->expandToIncludeFeature($featureMin);
		
		
		$feature = Feature::findOrCreateAtPosition(55, 2);
		$this->assertEquals(56, $feature->getBoundsMaxLat());
		$this->assertEquals(54, $feature->getBoundsMinLat());
		$this->assertEquals(3, $feature->getBoundsMaxLng());
		$this->assertEquals(1, $feature->getBoundsMinLng());
		

		
		
	}
	
	
}
