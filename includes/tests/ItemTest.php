<?php
/**
 * @author James Baster  <james@jarofgreen.co.uk>
 * @copyright City of Edinburgh Council & James Baster
 * @license Open Source under the 3-clause BSD License
 * @url https://github.com/City-Outdoors/City-Outdoors-Web
 */

class ItemTest extends AbstractTest {
    

    function testDuplicateItemSlug() {
        $this->setupDB();
		
        $user = User::createByEmail("test@example.com","pass","pass");

		$collection = Collection::create("test",$user);
		$collection->addStringField('Title');

		$item1 = $collection->getBlankItem($user);
		$item1->setPosition(55,1);
		$item1->getTitleField()->update('test',$user);
		$item1->writeToDataBase($user);
		$this->assertEquals("test",$item1->getSlug());

		$item2 = $collection->getBlankItem($user);
		$item2->setPosition(55,1);
		$item2->getTitleField()->update('test',$user);
		$item2->writeToDataBase($user);
		$this->assertEquals("test-1",$item2->getSlug());

		$item3 = $collection->getBlankItem($user);
		$item3->setPosition(55,1);
		$item3->getTitleField()->update('test',$user);
		$item3->writeToDataBase($user);
		$this->assertEquals("test-2",$item3->getSlug());
    }

    function testDefaultItemSlug() {
        $this->setupDB();
		
        $user = User::createByEmail("test@example.com","pass","pass");

		$collection = Collection::create("test",$user);
		
		$item1 = $collection->getBlankItem($user);
		$item1->setPosition(55,1);
		$item1->writeToDataBase($user);
		$this->assertEquals("item",$item1->getSlug());

		$item2 = $collection->getBlankItem($user);
		$item2->setPosition(55,1);
		$item2->writeToDataBase($user);
		$this->assertEquals("item-1",$item2->getSlug());

		$item3 = $collection->getBlankItem($user);
		$item3->setPosition(55,1);
		$item3->writeToDataBase($user);
		$this->assertEquals("item-2",$item3->getSlug());
    }

	function testChildItems() {
		global $CONFIG;
		$CONFIG->HIDDEN_COLLECTION_SLUG = 'badplaces';
		
		$this->setupDB();

		$user = User::createByEmail("test@example.com","pass","pass");

		$collection1 = Collection::create("test1",$user);		
		$collection2 = Collection::create("test2",$user);		

		$feature = Feature::findOrCreateAtPosition(55, 1);

		$this->assertEquals(0, $feature->getCountChildItems());
		$this->assertEquals(0, $feature->getCountChildItemsNotInHiddenCollection());

		$item1 = $collection1->getBlankItem($user);
		$item1->setPosition(55,1);
		$item1->writeToDataBase($user);

		$this->assertEquals(0, $feature->getCountChildItems());
		$this->assertEquals(0, $feature->getCountChildItemsNotInHiddenCollection());

		$item2 = $collection2->getBlankItem($user);
		$item2->setPosition(56,2);
		$item2->writeToDataBase($user);
		$item2->setChildOf($item1);

		$this->assertEquals(1, $feature->getCountChildItems());
		$this->assertEquals(1, $feature->getCountChildItemsNotInHiddenCollection());

	}
	
	function testChildItemsHidden() {
		global $CONFIG;
		$CONFIG->HIDDEN_COLLECTION_SLUG = 'test2';
		
		$this->setupDB();

		$user = User::createByEmail("test@example.com","pass","pass");

		$collection1 = Collection::create("test1",$user);		
		$collection2 = Collection::create("test2",$user);		

		$feature = Feature::findOrCreateAtPosition(55, 1);

		$this->assertEquals(0, $feature->getCountChildItems());
		$this->assertEquals(0, $feature->getCountChildItemsNotInHiddenCollection());

		$item1 = $collection1->getBlankItem($user);
		$item1->setPosition(55,1);
		$item1->writeToDataBase($user);

		$this->assertEquals(0, $feature->getCountChildItems());
		$this->assertEquals(0, $feature->getCountChildItemsNotInHiddenCollection());

		$item2 = $collection2->getBlankItem($user);
		$item2->setPosition(56,2);
		$item2->writeToDataBase($user);
		$item2->setChildOf($item1);

		$this->assertEquals(1, $feature->getCountChildItems());
		$this->assertEquals(0, $feature->getCountChildItemsNotInHiddenCollection());

	}
}
