<?php
/**
 * @author James Baster  <james@jarofgreen.co.uk>
 * @copyright City of Edinburgh Council & James Baster
 * @license Open Source under the 3-clause BSD License
 * @url https://github.com/City-Outdoors/City-Outdoors-Web
 */

class FeatureCheckinQuestionFreeText extends FeatureCheckinQuestion {

	public function __construct($data) {
		parent::__construct($data);
	}	
 
	
	/** @return FeatureCheckinQuestion **/
	public static function create(Feature $feature, $question, $answers, $score=10) {
		global  $CONFIG;
		$db = getDB();
		$stat = $db->prepare('INSERT INTO feature_checkin_question  (feature_id, question, answers, created_at, question_type, score) '.
				'VALUES (:feature_id, :question, :answers, :created_at, :type, :score)');
		$data = array(
				'feature_id'=>$feature->getId(), 
				'question'=>$question, 
				'answers'=>$answers,
				'created_at'=>date('Y-m-d H:i:s'),
				'type'=>'FREETEXT',
				'score'=> json_encode(array('score'=>$score)),
			);
		$stat->execute($data);
		$data['id'] = $db->lastInsertId();
		return new FeatureCheckinQuestionFreeText($data);
	}	


	public function getScoreForFreeTextQuestion() { return $this->score ? $this->score->score : 0;	}
	
	
	public function checkAnswer($attemptAnswer) {
		if (trim($attemptAnswer) == '') return false;
		
		foreach(explode("\n", $this->answers) as $answer) {
			if (strtolower(trim($answer)) == strtolower(trim($attemptAnswer))) {
				return true;
			}
		}
		
		return false;
	}
	
	public function checkAndSaveAnswer($attemptAnswer, User $useraccount, $userAgent = null, $ip=null) {
		$db = getDB();
		if (!$this->checkAnswer($attemptAnswer)) {
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
			return false;
		} else {
		
			$stat = $db->prepare('INSERT INTO feature_checkin_success  (user_account_id, feature_checkin_question_id, answer_given, score, created_at, user_agent, ip) '.
					'VALUES (:user_account_id, :feature_checkin_question_id, :answer_given, :score, :created_at, :user_agent, :ip)');
			$data = array(
					'user_account_id'=>$useraccount->getId(), 
					'feature_checkin_question_id'=>$this->getId(), 
					'answer_given'=>$attemptAnswer,
					'created_at'=>date('Y-m-d H:i:s'),
					'user_agent'=>$userAgent,
					'ip'=>$ip,
					'score'=>$this->getScoreForFreeTextQuestion(),
				);
			$stat->execute($data);	
			$useraccount->calculateAndCacheScore();
			return true;		
		}
	}
		
	public function setScoreForFreeTextQuestion($score) {
		$scoreJSONObject = array('score'=>$score);
		$scoreJSONText = json_encode($scoreJSONObject);
		$this->score = json_decode($scoreJSONText);
		$db = getDB();
		$stat = $db->prepare("UPDATE feature_checkin_question SET score=:s WHERE id=:id");
		$stat->execute(array('s'=>$scoreJSONText,'id'=>$this->id));		
	} 
}




