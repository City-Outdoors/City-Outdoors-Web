<?php
/**
 * @author James Baster  <james@jarofgreen.co.uk>
 * @copyright City of Edinburgh Council & James Baster
 * @license Open Source under the 3-clause BSD License
 * @url https://github.com/City-Outdoors/City-Outdoors-Web
 */

class FeatureCheckinTest extends AbstractTest {
	
	
	function test1() {
		$db = $this->setupDB();

		$user = User::createByEmail("test@test.com", "password", "password");
		
		$feature = Feature::findOrCreateAtPosition(55, 2);		
		
		$q1 = FeatureCheckinQuestion::createFreeTextQuestion($feature, "How many cats?", "  one \n  1  \r\n 1.0 ");
		
		
		# answer checkin routines
		$this->assertEquals(false, $q1->checkAnswer(""));
		$this->assertEquals(false, $q1->checkAnswer("  "));
		$this->assertEquals(false, $q1->checkAnswer(" 2 "));
		$this->assertEquals(false, $q1->checkAnswer(" two "));
		$this->assertEquals(true, $q1->checkAnswer(" one "));
		$this->assertEquals(true, $q1->checkAnswer(" ONe "));
		$this->assertEquals(true, $q1->checkAnswer("one"));
		$this->assertEquals(true, $q1->checkAnswer("ONE"));
		$this->assertEquals(true, $q1->checkAnswer(" 1 "));
		$this->assertEquals(true, $q1->checkAnswer("1"));
		$this->assertEquals(true, $q1->checkAnswer(" 1.0 "));
		$this->assertEquals(true, $q1->checkAnswer("1.0"));
				
	}
	

	function test2() {
		$db = $this->setupDB();

		$user = User::createByEmail("test@test.com", "password", "password");
		
		$feature = Feature::findOrCreateAtPosition(55, 2);		
		
		// this is needed so feature will show up in search. Only features with content on appear.
		$content = $feature->newContent("Test", $user);
		$content->approve($user);
		
		$q1 = FeatureCheckinQuestion::createFreeTextQuestion($feature, "How many cats?", "  one \n  1  \r\n 1.0 ");
		$q2 = FeatureCheckinQuestion::createFreeTextQuestion($feature, "How many dogs?", "  one \n  1  \r\n 1.0 ");
		
		# 0 no answers
		$this->assertEquals(false, $q1->hasAnswered($user));
		$this->assertEquals(false, $q2->hasAnswered($user));
		$this->assertEquals(false, $feature->hasUserCheckedIn($user));
		$this->assertEquals(0, $feature->getCheckinCount());
		$s = new FeatureSearch();
		$s->userCheckedin($user);
		$this->assertEquals(0,$s->num());
		$s = new FeatureSearch();
		$s->userNotCheckedin($user);
		$this->assertEquals(1,$s->num());
		
		# 1 first answer
		$q1->checkAndSaveAnswer('1', $user);
		$this->assertEquals(true, $q1->hasAnswered($user));
		$this->assertEquals(false, $q2->hasAnswered($user));
		$this->assertEquals(false, $feature->hasUserCheckedIn($user));
		$this->assertEquals(1, $feature->getCheckinCount());
		$s = new FeatureSearch();
		$s->userCheckedin($user);
		$this->assertEquals(1,$s->num());
		$s = new FeatureSearch();
		$s->userNotCheckedin($user);
		$this->assertEquals(1,$s->num());
		
		# 2 both answers
		$q2->checkAndSaveAnswer('1', $user);
		$this->assertEquals(true, $q1->hasAnswered($user));
		$this->assertEquals(true, $q2->hasAnswered($user));
		$this->assertEquals(true, $feature->hasUserCheckedIn($user));
		$this->assertEquals(2, $feature->getCheckinCount());
		$s = new FeatureSearch();
		$s->userCheckedin($user);
		$this->assertEquals(1,$s->num());
		$s = new FeatureSearch();
		$s->userNotCheckedin($user);
		$this->assertEquals(0,$s->num());
		
	}
		
}
