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

	function testWthQuestions() {
		global $CONFIG;
		$db = $this->setupDB();

		$user = User::createByEmail("test@example.com","pass","pass");	
		$collection1 = Collection::create("Test1", $user);
		$collection2 = Collection::create("Test2", $user);
		
		# test
		$collectionSearch = new CollectionSearch();
		$collectionSearch->withFeatureCheckinQuestions(true);
		$this->assertEquals(0, $collectionSearch->num());
		
		$collectionSearch = new CollectionSearch();
		$this->assertEquals(2, $collectionSearch->num());
		
		# add item
		$item1 = $collection1->getBlankItem($user);
		$item1->setPosition(55, 1);
		$item1->writeToDataBase($user);
		$question1 = FeatureCheckinQuestionFreeText::create(Feature::findOrCreateAtPosition(55, 1), "Test?","123");
		

		# test
		$collectionSearch = new CollectionSearch();
		$collectionSearch->withFeatureCheckinQuestions(true);
		$this->assertEquals(1, $collectionSearch->num());
		
		$collectionSearch = new CollectionSearch();
		$this->assertEquals(2, $collectionSearch->num());		
		

		# add item
		$item2 = $collection2->getBlankItem($user);
		$item2->setPosition(55, 2);
		$item2->writeToDataBase($user);
		$question2 = FeatureCheckinQuestionFreeText::create(Feature::findOrCreateAtPosition(55, 2), "Test?","123");
		
		# test
		$collectionSearch = new CollectionSearch();
		$collectionSearch->withFeatureCheckinQuestions(true);
		$this->assertEquals(2, $collectionSearch->num());
		
		$collectionSearch = new CollectionSearch();
		$this->assertEquals(2, $collectionSearch->num());		
		
		
	}	
	
}
