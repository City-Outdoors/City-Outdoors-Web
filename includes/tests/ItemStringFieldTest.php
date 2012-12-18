<?php
/**
 * @author James Baster  <james@jarofgreen.co.uk>
 * @copyright City of Edinburgh Council & James Baster
 * @license Open Source under the 3-clause BSD License
 * @url https://github.com/City-Outdoors/City-Outdoors-Web
 */

class ItemStringFieldTest extends AbstractTest {
    
    function testBasicSaving() {
        $this->setupDB();
		
        $user = User::createByEmail("test@example.com","pass","pass");

		$collection = Collection::create("test",$user);
		$collection->addStringField('Title');

		// default value
		$item = $collection->getBlankItem($user);
		$item->setPosition(55,1);
		$item->writeToDataBase($user);
		$field = $item->getTitleField();
		$this->assertEquals(null,$field->getValue());
		$this->assertEquals(false,$field->hasValue());

		// new value
		$field->update('newTitle',$user);
		$this->assertEquals(0, count($item->getValidationErrors()));
		$item->writeToDataBase($user);

		// check new value saved
		$item = Item::loadBySlugIncollection('item',$collection);
		$field = $item->getTitleField();
		$this->assertEquals('newTitle',$field->getValue());
		$this->assertEquals(true,$field->hasValue());

		// new value
		$field->update('thanks',$user);
		$this->assertEquals(0, count($item->getValidationErrors()));
		$item->writeToDataBase($user);

		// check new value saved
		$item = Item::loadBySlugIncollection('item',$collection);
		$field = $item->getTitleField();
		$this->assertEquals('thanks',$field->getValue());
		$this->assertEquals(true,$field->hasValue());
    }

    function testToLong() {
        $this->setupDB();
		
        $user = User::createByEmail("test@example.com","pass","pass");

		$collection = Collection::create("test",$user);
		$collection->addStringField('Title');

		// default value
		$item = $collection->getBlankItem($user);
		$item->setPosition(55,1);
		$item->writeToDataBase($user);
		$field = $item->getTitleField();
		$this->assertEquals(null,$field->getValue());

		// new value is to long!
		$field->update(generateRandomString(256),$user);
		$this->assertEquals(1, count($item->getValidationErrors()));		
    }
}
