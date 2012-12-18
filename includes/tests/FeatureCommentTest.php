<?php
/**
 * @author James Baster  <james@jarofgreen.co.uk>
 * @copyright City of Edinburgh Council & James Baster
 * @license Open Source under the 3-clause BSD License
 * @url https://github.com/City-Outdoors/City-Outdoors-Web
 */

class ItemCommentTest extends AbstractTest {
    
    function testModerateComments() {
		global $CONFIG;
		$db = $this->setupDB();
		
		$CONFIG->SUBMIT_CONTENT_ANYONE = 0;
		$CONFIG->SUBMIT_CONTENT_USERS = 0;
		$CONFIG->SUBMIT_CONTENT_ADMINISTRATORS = 0;

		$feature = Feature::findOrCreateAtPosition(55, 1);
		$comment = $feature->newAnonymousContent("TEST");
		
		$this->assertEquals(false, $comment->isApproved());

		$itemSearch = new FeatureContentSearch();
		$itemSearch->forFeature($feature);
		$itemSearch->approvedOnly();
		$this->assertEquals(0, $itemSearch->num());

		$itemSearch = new FeatureContentSearch();
		$itemSearch->forFeature($feature);
		$itemSearch->toModerateOnly();
		$this->assertEquals(1, $itemSearch->num());
    }


    function testDontModerateComments() {
		global $CONFIG;
        $db = $this->setupDB();
		
		$CONFIG->SUBMIT_CONTENT_ANYONE = 1;
		$CONFIG->SUBMIT_CONTENT_USERS = 1;
		$CONFIG->SUBMIT_CONTENT_ADMINISTRATORS = 1;

		$feature = Feature::findOrCreateAtPosition(55, 1);
		$comment = $feature->newAnonymousContent("TEST");
		
		$this->assertEquals(true, $comment->isApproved());

		$itemSearch = new FeatureContentSearch();
		$itemSearch->forFeature($feature);
		$itemSearch->approvedOnly();
		$this->assertEquals(1, $itemSearch->num());

		$itemSearch = new FeatureContentSearch();
		$itemSearch->forFeature($feature);
		$itemSearch->toModerateOnly();
		$this->assertEquals(0, $itemSearch->num());
    }

}
