<?php
/**
 * @author James Baster  <james@jarofgreen.co.uk>
 * @copyright City of Edinburgh Council & James Baster
 * @license Open Source under the 3-clause BSD License
 * @url https://github.com/City-Outdoors/City-Outdoors-Web
 */

class FeatureFavouriteTest extends AbstractTest {
	
	
	function test1() {
		$db = $this->setupDB();

		$user = User::createByEmail("test@test.com", "password", "password");
		
		$feature = Feature::findOrCreateAtPosition(55, 2);		
		
		
		
		$this->assertEquals(false, $feature->hasUserFravourited($user));
		$this->assertEquals(0, $feature->getFravouriteCount());
		
		$feature->favourite($user);
		
		$this->assertEquals(true, $feature->hasUserFravourited($user));
		$this->assertEquals(1, $feature->getFravouriteCount());
		
		// test can call a second time without crashing and messing up data
		$feature->favourite($user);

		$this->assertEquals(true, $feature->hasUserFravourited($user));
		$this->assertEquals(1, $feature->getFravouriteCount());

		
	}
	
	
}
