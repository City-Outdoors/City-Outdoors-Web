<?php
/**
 * @author James Baster  <james@jarofgreen.co.uk>
 * @copyright City of Edinburgh Council & James Baster
 * @license Open Source under the 3-clause BSD License
 * @url https://github.com/City-Outdoors/City-Outdoors-Web
 */

class FeatureCheckinHigherOrLowerTest extends AbstractTest {
	
	function dataProviderTestParseAnswers() {
		return array(
				array("10 ",10),
				array(" 10.376",null),
				array("5 - 10",array(5,10)),
				array("5 - 5",null),
				array("uoieui - 5",null),
				array("5 - oeuei",null),
				array("10 - 5",null),
				array("5.1 - 10.1",array(5.1,10.1)),
				array("oeueou",null)
			);
	}
	
	/**
     * @dataProvider dataProviderTestParseAnswers
     */
	function testParseAnswers($answer, $parsedData) {
		$db = $this->setupDB();

		$feature = Feature::findOrCreateAtPosition(55, 2);		
		$q1 = FeatureCheckinQuestionHigherOrLower::create($feature, "How many cats?", $answer);
		
		if (is_null($parsedData)) {
			$this->assertNull($q1->parseRealAnswer());
		} else if (is_int($parsedData)) {
			$this->assertEquals($parsedData, $q1->parseRealAnswer());
		} else if (is_array($parsedData)) {
			list($min,$max) = $q1->parseRealAnswer();
			$this->assertEquals($parsedData[0], $min);
			$this->assertEquals($parsedData[1], $max);
		}
	}
	

	function dataProviderTestCheckAnswer() {
		return array(
				array("10","10",0),
				array("10","10.5",0),
				array("10","9",-1),
				array("10","9.5",-1),
				array("10","11",1),
				array("10","11.5",1),
				array("5.5-6.5","5.4",-1),
				array("5.5-6.5","5.5",0),
				array("5.5-6.5","6",0),
				array("5.5-6.5","6.5",0),
				array("5.5-6.5","6.6",1),
				array("5.5-6.5","uoieiei",null),
				array("5.5-6.5","",null),
			);
	}
	
	/**
     * @dataProvider dataProviderTestCheckAnswer
     */
	function testCheckAnswer($realAnswer, $givenAnswer, $expectedResult) {
		$db = $this->setupDB();

		$feature = Feature::findOrCreateAtPosition(55, 2);		
		$q1 = FeatureCheckinQuestionHigherOrLower::create($feature, "How many cats?", $realAnswer);

		$actualResult = $q1->checkAnswer($givenAnswer);
		if (is_null($expectedResult)) {
			$this->assertNull($actualResult);
		} else {
			$this->assertNotNull($actualResult);
			$this->assertEquals($expectedResult, $actualResult);
		}
	}
	

	function testUserCorrectFirstTime() {
		$db = $this->setupDB();

		$user = User::createByEmail("test@test.com", "password", "password");
		$feature = Feature::findOrCreateAtPosition(55, 2);		
		
		$q1 = FeatureCheckinQuestionHigherOrLower::create($feature, "How many cats?", "7");
		$q1->setScoresFromString("10,5,2");
		
		$q1->checkAndSaveAnswer("7", $user);
		
		$user = User::loadByEmail('test@test.com');
		$this->assertEquals(10,$user->getCachedScore());
	}
		
	function testUserCorrectSecordTime() {
		$db = $this->setupDB();

		$user = User::createByEmail("test@test.com", "password", "password");
		$feature = Feature::findOrCreateAtPosition(55, 2);		
		
		$q1 = FeatureCheckinQuestionHigherOrLower::create($feature, "How many cats?", "7");
		$q1->setScoresFromString("10,5,2");
		
		$q1->checkAndSaveAnswer("5", $user);
		$q1->checkAndSaveAnswer("7", $user);
		
		$user = User::loadByEmail('test@test.com');
		$this->assertEquals(5,$user->getCachedScore());
	}
		
	function testUserCorrectThirdTime() {
		$db = $this->setupDB();

		$user = User::createByEmail("test@test.com", "password", "password");
		$feature = Feature::findOrCreateAtPosition(55, 2);		
		
		$q1 = FeatureCheckinQuestionHigherOrLower::create($feature, "How many cats?", "7");
		$q1->setScoresFromString("10,5,2");
		
		$q1->checkAndSaveAnswer("5", $user);
		$q1->checkAndSaveAnswer("8", $user);
		$q1->checkAndSaveAnswer("7", $user);
		
		$user = User::loadByEmail('test@test.com');
		$this->assertEquals(2,$user->getCachedScore());
	}
		
	function testUserCorrectFourthTime() {
		$db = $this->setupDB();

		$user = User::createByEmail("test@test.com", "password", "password");
		$feature = Feature::findOrCreateAtPosition(55, 2);		
		
		$q1 = FeatureCheckinQuestionHigherOrLower::create($feature, "How many cats?", "7");
		$q1->setScoresFromString("10,5,2");
		
		$q1->checkAndSaveAnswer("5", $user);
		$q1->checkAndSaveAnswer("8", $user);
		$q1->checkAndSaveAnswer("9", $user);
		$q1->checkAndSaveAnswer("7", $user);
		
		$user = User::loadByEmail('test@test.com');
		$this->assertEquals(0,$user->getCachedScore());
	}
		
	
}
