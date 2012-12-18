<?php
/**
 * @author James Baster  <james@jarofgreen.co.uk>
 * @copyright City of Edinburgh Council & James Baster
 * @license Open Source under the 3-clause BSD License
 * @url https://github.com/City-Outdoors/City-Outdoors-Web
 */

class CollectionSearchTest extends AbstractTest {
    
    function testCollection() {
        $this->setupDB();
		
        $user = User::createByEmail("test@example.com","pass","pass");
		$collection = Collection::create("test",$user);

		$collectionSearch = new CollectionSearch();
		$this->assertEquals(1, $collectionSearch->num());



    }

	
}
