<?php
/**
 * @author James Baster  <james@jarofgreen.co.uk>
 * @copyright City of Edinburgh Council & James Baster
 * @license Open Source under the 3-clause BSD License
 * @url https://github.com/City-Outdoors/City-Outdoors-Web
 */

class FeatureCheckinQuestionHigherOrLower extends BaseFeatureCheckinQuestion {

	public function __construct($data) {
		parent::__construct($data);
	}	
 
	
	/** @return FeatureCheckinQuestionHigherOrLower **/
	public static function create(Feature $feature, $question, $answers) {
		$db = getDB();
		$stat = $db->prepare('INSERT INTO feature_checkin_question  (feature_id, question, answers, created_at, question_type) '.
				'VALUES (:feature_id, :question, :answers, :created_at, :type)');
		$data = array(
				'feature_id'=>$feature->getId(), 
				'question'=>$question, 
				'answers'=>$answers,
				'created_at'=>date('Y-m-d H:i:s'),
				'type'=>'HIGHERORLOWER',
			);
		$stat->execute($data);
		$data['id'] = $db->lastInsertId();
		return new FeatureCheckinQuestionHigherOrLower($data);
	}	
	
	
	/** Takes string admin user has put in as the real answer, attemps to parse it to a sensible structure and return the results or NULL on failure **/
	public function parseRealAnswer() {
		if (strpos($this->answers, "-") > 0) {
			list($min,$max) = explode("-", $this->answers);
			if (is_numeric(trim($min)) && is_numeric(trim($max))) {
				$min = floatval(trim($min));
				$max = floatval(trim($max));
				if ($max > $min) {
					return array($min,$max);
				}
			}
		} else {
			// If it's one value only, must be an int. 
			// (If a float lots of problems with how many decimal places do you check to? bit unfair on player.)
			// Hence we use ctype_digit, ctype_digit does not accept "." characters.
			if (ctype_digit(trim($this->answers))) {
				return intval(trim($this->answers));
			}
		}
		return null;
	}

	/** Takes answer from user.
	 *
	 * @param type $answer
	 * @return int|null 0 if correct, -1 if answer is to low, 1 if answer is to high. null if error.
	 */
	public function checkAnswer($answer) {
		if (!is_numeric($answer)) return null;
		$realAnswer = $this->parseRealAnswer();
		if (is_array($realAnswer)) {
			list($min,$max) = $realAnswer;
			$answer = floatval(trim($answer));
			if (($answer >= $min) && ($answer <= $max)) {
				return 0;
			} else if ($answer < $min) {
				return -1;
			} else if ($answer > $max) {
				return 1;
			}
		} else if (is_int($realAnswer)) {
			$answer = intval(trim($answer));
			if ($answer == $realAnswer) {
				return 0;
			} else if ($answer < $realAnswer) {
				return -1;
			} else if ($answer > $realAnswer) {
				return 1;
			}
		} else {
			return null;
		}
	}
	
	public function checkAndSaveAnswer($attemptAnswer, User $useraccount, $userAgent = null, $ip=null) {
		$db = getDB();
		$answer = $this->checkAnswer($attemptAnswer);
		if (is_null($answer)) {
			return null;
		} else if ($answer == 1 || $answer == -1) {
			$stat = $db->prepare('INSERT INTO feature_checkin_failure  (user_account_id, feature_checkin_question_id, answer_given, created_at, user_agent, ip) '.
					'VALUES (:user_account_id, :feature_checkin_question_id, :answer_given, :created_at, :user_agent, :ip)');
			$data = array(
					'user_account_id'=>$useraccount->getId(), 
					'feature_checkin_question_id'=>$this->getId(), 
					'answer_given'=>$attemptAnswer,
					'created_at'=>date('Y-m-d H:i:s'),
					'user_agent'=>$userAgent,
					'ip'=>$ip			
				);
			$stat->execute($data);		
			return $answer;
		} else  if ($answer == 0) {
			
			// how many wrong goes did they take?
			$attempt = 1;
			$stat = $db->prepare('SELECT id FROM feature_checkin_failure WHERE feature_checkin_question_id=:fciqid AND user_account_id=:uaid');
			$stat->execute(array(
					'fciqid'=>$this->getId(),
					'uaid'=>$useraccount->getId()
				));
			while($d = $stat->fetch()) $attempt++;
			
			// now insert the correct answer with the score based on how any goes it took
			$stat = $db->prepare('INSERT INTO feature_checkin_success  (user_account_id, feature_checkin_question_id, answer_given, score, created_at, user_agent, ip) '.
					'VALUES (:user_account_id, :feature_checkin_question_id, :answer_given, :score, :created_at, :user_agent, :ip)');
			$data = array(
					'user_account_id'=>$useraccount->getId(), 
					'feature_checkin_question_id'=>$this->getId(), 
					'answer_given'=>$attemptAnswer,
					'created_at'=>date('Y-m-d H:i:s'),
					'user_agent'=>$userAgent,
					'ip'=>$ip,
					'score'=>$this->getScoreOnAttempt($attempt),
				);
			$stat->execute($data);	
			$useraccount->calculateAndCacheScore();
			return 0;		
		}
	}	
			
	public function getScoreOnAttempt($attempt) {
		$scores = $this->getScores();
		if (count($scores) > $attempt - 1) {
			return $scores[$attempt - 1];
		}
		return 0;
	}
	
	public function getScores() {  return $this->score ? $this->score->scores : 0;	}
		
	public function setScoresFromString($score = "10,5") {
		$scores = array();
		foreach(explode(",",$score) as $i) {
			if (intval($i)) { // I suspose someone may want negative scores.
				$scores[] = intval($i);
			}
		}
		$this->setScores($scores);
	}
	
	public function setScores($score = array(10,5)) {
		$scoreJSONObject = array('scores'=>$score);
		$scoreJSONText = json_encode($scoreJSONObject);
		$this->score = json_decode($scoreJSONText);
		$db = getDB();
		$stat = $db->prepare("UPDATE feature_checkin_question SET score=:s WHERE id=:id");
		$stat->execute(array('s'=>$scoreJSONText,'id'=>$this->id));		
	} 
}




