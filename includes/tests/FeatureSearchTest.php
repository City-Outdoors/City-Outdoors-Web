<?php
/**
 * @author James Baster  <james@jarofgreen.co.uk>
 * @copyright City of Edinburgh Council & James Baster
 * @license Open Source under the 3-clause BSD License
 * @url https://github.com/City-Outdoors/City-Outdoors-Web
 */

class FeatureSearchTest extends AbstractTest {

	function testContent() {
		global $CONFIG;
		$db = $this->setupDB();
		
        $user = User::createByEmail("test@example.com","pass","pass");		
		
		$CONFIG->SUBMIT_CONTENT_ANYONE = 0;
		$CONFIG->SUBMIT_CONTENT_USERS = 0;
		$CONFIG->SUBMIT_CONTENT_ADMINISTRATORS = 0;

		$feature = Feature::findOrCreateAtPosition(55, 1);
		$comment = $feature->newAnonymousContent("TEST");
		
		$this->assertEquals(false, $comment->isApproved());

		$itemSearch = new FeatureSearch();
		$this->assertEquals(0, $itemSearch->num());

		$comment->approve($user);
		
		$itemSearch = new FeatureSearch();
		$this->assertEquals(1, $itemSearch->num());

		
    }

	function testItem() {
		global $CONFIG;
		$db = $this->setupDB();

		$user = User::createByEmail("test@example.com","pass","pass");		

		# nothing
		$feature = Feature::findOrCreateAtPosition(55, 1);
		
		$itemSearch = new FeatureSearch();
		$this->assertEquals(0, $itemSearch->num());
		
		# now make item
		$collection = Collection::create("Test", $user);
		$item = $collection->getBlankItem($user);
		$item->setPosition(55, 1);
		$item->writeToDataBase($user);
		
		$itemSearch = new FeatureSearch();
		$this->assertEquals(1, $itemSearch->num());
		
		$feature = $itemSearch->nextResult();
		$this->assertNotNull($feature);
		
		$data = $feature->getCollectionIDS();
		$this->assertEquals(1, count($data));
		$this->assertEquals($collection->getId(), $data[0]);
		
    }

	function testItemDeleted() {
		global $CONFIG;
		$db = $this->setupDB();

		$user = User::createByEmail("test@example.com","pass","pass");		

		# nothing
		$feature = Feature::findOrCreateAtPosition(55, 1);
		
		$itemSearch = new FeatureSearch();
		$this->assertEquals(0, $itemSearch->num());
		
		# now make item
		$collection = Collection::create("Test", $user);
		$item = $collection->getBlankItem($user);
		$item->setPosition(55, 1);
		$item->writeToDataBase($user);
		$item->delete();
		
		# The feature only has a deleted item on it so it shouldn't appear!
		$itemSearch = new FeatureSearch();
		$this->assertEquals(0, $itemSearch->num());
		
		
    }

	function testQuestion() {
		global $CONFIG;
		$db = $this->setupDB();

		$user = User::createByEmail("test@example.com","pass","pass");		

		# nothing
		$feature = Feature::findOrCreateAtPosition(55, 1);

		$itemSearch = new FeatureSearch();
		$this->assertEquals(0, $itemSearch->num());

		# now make question
		$question = FeatureCheckinQuestionContent::create($feature, "Test?");

		$itemSearch = new FeatureSearch();
		$this->assertEquals(1, $itemSearch->num());

	}

	function testQuestionDeleted() {
		global $CONFIG;
		$db = $this->setupDB();

		$user = User::createByEmail("test@example.com","pass","pass");		

		# nothing
		$feature = Feature::findOrCreateAtPosition(55, 1);

		$itemSearch = new FeatureSearch();
		$this->assertEquals(0, $itemSearch->num());

		# now make question
		$question = FeatureCheckinQuestionContent::create($feature, "Test?");
		$question->setDeleted(true);

		$itemSearch = new FeatureSearch();
		$this->assertEquals(0, $itemSearch->num());

	}
	

	/** tests userCheckedinInformation() and getHasUserAnsweredAllQuestions() functions. **/
	function testQuestionUnansweredInfo1() {
		global $CONFIG;
		$db = $this->setupDB();

		$user = User::createByEmail("test@example.com","pass","pass");		
		$feature = Feature::findOrCreateAtPosition(55, 1);
		$question1 = FeatureCheckinQuestionFreeText::create($feature, "Test?","123");

		# search
		$featureSearch = new FeatureSearch();
		$featureSearch->userCheckedinInformation($user);
		$this->assertEquals(1, $featureSearch->num());
		
		$feature = $featureSearch->nextResult();
		$this->assertEquals(false, $feature->getHasUserAnsweredAllQuestions());
	}

	/** tests userCheckedinInformation() and getHasUserAnsweredAllQuestions() functions. **/
	function testQuestionUnansweredInfo2() {
		global $CONFIG;
		$db = $this->setupDB();

		$user = User::createByEmail("test@example.com","pass","pass");		
		$feature = Feature::findOrCreateAtPosition(55, 1);
		$question1 = FeatureCheckinQuestionFreeText::create($feature, "Test?","123");
		$question1->checkAndSaveAnswer("123", $user);
		$question2 = FeatureCheckinQuestionFreeText::create($feature, "Quack?","duck");
		
		# search
		$featureSearch = new FeatureSearch();
		$featureSearch->userCheckedinInformation($user);
		$this->assertEquals(1, $featureSearch->num());
		
		$feature = $featureSearch->nextResult();
		$this->assertEquals(false, $feature->getHasUserAnsweredAllQuestions());
	}

	/** tests userCheckedinInformation() and getHasUserAnsweredAllQuestions() functions. **/
	function testQuestionAnsweredInfo1() {
		global $CONFIG;
		$db = $this->setupDB();

		$user = User::createByEmail("test@example.com","pass","pass");		
		$feature = Feature::findOrCreateAtPosition(55, 1);
		$question1 = FeatureCheckinQuestionFreeText::create($feature, "Test?","123");
		$question1->checkAndSaveAnswer("123", $user);
		
		# search
		$featureSearch = new FeatureSearch();
		$featureSearch->userCheckedinInformation($user);
		$this->assertEquals(1, $featureSearch->num());
		
		$feature = $featureSearch->nextResult();
		$this->assertEquals(true, $feature->getHasUserAnsweredAllQuestions());
	}

	/** tests userCheckedinInformation() and getHasUserAnsweredAllQuestions() functions. **/
	function testQuestionAnsweredInfo2() {
		global $CONFIG;
		$db = $this->setupDB();

		$user = User::createByEmail("test@example.com","pass","pass");		
		$feature = Feature::findOrCreateAtPosition(55, 1);
		$question1 = FeatureCheckinQuestionFreeText::create($feature, "Test?","123");
		$question1->checkAndSaveAnswer("123", $user);
		$question2 = FeatureCheckinQuestionFreeText::create($feature, "Quack?","duck");
		$question2->checkAndSaveAnswer("duck", $user);
		
		# search
		$featureSearch = new FeatureSearch();
		$featureSearch->userCheckedinInformation($user);
		$this->assertEquals(1, $featureSearch->num());
		
		$feature = $featureSearch->nextResult();
		$this->assertEquals(true, $feature->getHasUserAnsweredAllQuestions());
	}
	
	
	function testFeatureWith2Collections() {
		global $CONFIG;
		$db = $this->setupDB();

		$user = User::createByEmail("test@example.com","pass","pass");		

		# nothing
		$feature = Feature::findOrCreateAtPosition(55, 1);
		
		$itemSearch = new FeatureSearch();
		$this->assertEquals(0, $itemSearch->num());
		
		# now make item
		$collection1 = Collection::create("Test", $user);
		$item1 = $collection1->getBlankItem($user);
		$item1->setPosition(55, 1);
		$item1->writeToDataBase($user);
		
		$collection2 = Collection::create("Test2", $user);
		$item2 = $collection2->getBlankItem($user);
		$item2->setPosition(55, 1);
		$item2->writeToDataBase($user);	
		
		$itemSearch = new FeatureSearch();
		$this->assertEquals(1, $itemSearch->num());
		
		$feature = $itemSearch->nextResult();
		$this->assertNotNull($feature);
		
		$data = $feature->getCollectionIDS();
		$this->assertEquals(2, count($data));
		$this->assertEquals($collection1->getId(), $data[0]);
		$this->assertEquals($collection2->getId(), $data[1]);
		
    }
}

