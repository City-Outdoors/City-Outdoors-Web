<?php
/**
 * @author James Baster  <james@jarofgreen.co.uk>
 * @copyright City of Edinburgh Council & James Baster
 * @license Open Source under the 3-clause BSD License
 * @url https://github.com/City-Outdoors/City-Outdoors-Web
 */

class FeatureCheckinMultipleChoiceTest extends AbstractTest {
	
	function dataProviderTestCorrectAnswers() {
		return array(array("10"),array("10,5,2"),array("1"));
	}
	
	/**
     * @dataProvider dataProviderTestCorrectAnswers
     */
	function testCorrectAnswers($scores) {
		$db = $this->setupDB();

		$user = User::createByEmail("test@test.com", "password", "password");
		
		$feature = Feature::findOrCreateAtPosition(55, 2);		
		
		$q1 = FeatureCheckinQuestionMultipleChoice::create($feature, "How many cats?");
		$a1 = $q1->addAnswerWithScoresFromString("none", $scores);
		
		$test1 = $q1->checkAnswer($a1->getId());
		$this->assertEquals('FeatureCheckinQuestionPossibleAnswer', get_class($test1));
	}
	
	
	
	function dataProviderTestWrongAnswers() {
		return array(array(""),array("-1"));
	}
	
	/**
     * @dataProvider dataProviderTestWrongAnswers
     */
	function testWrongAnswers($scores) {
		$db = $this->setupDB();

		$user = User::createByEmail("test@test.com", "password", "password");
		
		$feature = Feature::findOrCreateAtPosition(55, 2);		
		
		$q1 = FeatureCheckinQuestionMultipleChoice::create($feature, "How many cats?");
		$a1 = $q1->addAnswerWithScoresFromString("none", $scores);
		
		$test1 = $q1->checkAnswer($a1->getId());
		$this->assertNull($test1);
	}

	
	function testUserCorrectFirstTime() {
		$db = $this->setupDB();

		$user = User::createByEmail("test@test.com", "password", "password");
		
		$feature = Feature::findOrCreateAtPosition(55, 2);		
		
		$q1 = FeatureCheckinQuestionMultipleChoice::create($feature, "How many cats?");
		$a1 = $q1->addAnswerWithScoresFromString("none", "10,5,2");
		
		$test1 = $q1->checkAndSaveAnswer($a1->getId(), $user);
		
		$user = User::loadByEmail('test@test.com');
		$this->assertEquals(10,$user->getCachedScore());
	}
	
	function testUserCorrectSecondTime() {
		$db = $this->setupDB();

		$user = User::createByEmail("test@test.com", "password", "password");
		
		$feature = Feature::findOrCreateAtPosition(55, 2);		
		
		$q1 = FeatureCheckinQuestionMultipleChoice::create($feature, "How many cats?");
		$a1 = $q1->addAnswerWithScoresFromString("none", "10,5,2");
		$a2 = $q1->addAnswerWithScoresFromString("37,458", "");
		
		$test1 = $q1->checkAndSaveAnswer($a2->getId(), $user);
		$test1 = $q1->checkAndSaveAnswer($a1->getId(), $user);
		
		$user = User::loadByEmail('test@test.com');
		$this->assertEquals(5,$user->getCachedScore());
	}

	function testUserCorrectThirdTime() {
		$db = $this->setupDB();

		$user = User::createByEmail("test@test.com", "password", "password");
		
		$feature = Feature::findOrCreateAtPosition(55, 2);		
		
		$q1 = FeatureCheckinQuestionMultipleChoice::create($feature, "How many cats?");
		$a1 = $q1->addAnswerWithScoresFromString("none", "10,5,2");
		$a2 = $q1->addAnswerWithScoresFromString("37,458", "");
		$a3 = $q1->addAnswerWithScoresFromString("5", "");
		
		$test1 = $q1->checkAndSaveAnswer($a3->getId(), $user);
		$test1 = $q1->checkAndSaveAnswer($a2->getId(), $user);
		$test1 = $q1->checkAndSaveAnswer($a1->getId(), $user);
		
		$user = User::loadByEmail('test@test.com');
		$this->assertEquals(2,$user->getCachedScore());
	}

	function testUserCorrectFourthTime() {
		$db = $this->setupDB();

		$user = User::createByEmail("test@test.com", "password", "password");
		
		$feature = Feature::findOrCreateAtPosition(55, 2);		
		
		$q1 = FeatureCheckinQuestionMultipleChoice::create($feature, "How many cats?");
		$a1 = $q1->addAnswerWithScoresFromString("none", "10,5,2");
		$a2 = $q1->addAnswerWithScoresFromString("37,458", "");
		$a3 = $q1->addAnswerWithScoresFromString("5", "");
		$a4 = $q1->addAnswerWithScoresFromString("a half", "");
		
		$test1 = $q1->checkAndSaveAnswer($a4->getId(), $user);
		$test1 = $q1->checkAndSaveAnswer($a3->getId(), $user);
		$test1 = $q1->checkAndSaveAnswer($a2->getId(), $user);
		$test1 = $q1->checkAndSaveAnswer($a1->getId(), $user);
		
		$user = User::loadByEmail('test@test.com');
		$this->assertEquals(0,$user->getCachedScore());
	}

		
}
