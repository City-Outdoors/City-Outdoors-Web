<?php
/**
 * @author James Baster  <james@jarofgreen.co.uk>
 * @copyright City of Edinburgh Council & James Baster
 * @license Open Source under the 3-clause BSD License
 * @url https://github.com/City-Outdoors/City-Outdoors-Web
 */

class CollectionTest extends AbstractTest {
    
    function testDuplicateCollectionSlug() {
        $this->setupDB();
		
        $user = User::createByEmail("test@example.com","pass","pass");

		$collection1 = Collection::create("test",$user);
		$this->assertEquals("test",$collection1->getSlug());

		$collection2 = Collection::create("test",$user);
		$this->assertEquals("test-1",$collection2->getSlug());

		$collection3 = Collection::create("test",$user);
		$this->assertEquals("test-2",$collection3->getSlug());

    }


}