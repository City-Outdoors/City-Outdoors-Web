<?php
/**
 * @author James Baster  <james@jarofgreen.co.uk>
 * @copyright City of Edinburgh Council & James Baster
 * @license Open Source under the 3-clause BSD License
 * @url https://github.com/City-Outdoors/City-Outdoors-Web
 */

class FeatureCheckinQuestionMultipleChoice extends FeatureCheckinQuestion {

	public function __construct($data) {
		parent::__construct($data);
	}	
 
	
	/** @return FeatureCheckinQuestionMultipleChoice **/
	public static function create(Feature $feature, $question) {
		$db = getDB();
		$stat = $db->prepare('INSERT INTO feature_checkin_question  (feature_id, question, created_at, question_type) '.
				'VALUES (:feature_id, :question, :created_at, :type)');
		$data = array(
				'feature_id'=>$feature->getId(), 
				'question'=>$question, 
				'created_at'=>date('Y-m-d H:i:s'),
				'type'=>'MULTIPLECHOICE',
			);
		$stat->execute($data);
		$data['id'] = $db->lastInsertId();
		return new FeatureCheckinQuestionMultipleChoice($data);
	}	
	
	public function addAnswerWithScoresFromString($answer, $scoresAsString) {
		$scores = array();
		foreach(explode(",",$scoresAsString) as $i) {
			if (intval($i) > 0) { // No negative scores for this type of questions.
				$scores[] = intval($i);
			}
		}
		$db = getDB();
		$stat = $db->prepare("INSERT INTO feature_checkin_question_possible_answer (feature_checkin_question_id, answer, score, created_at) ".
				'VALUES (:feature_checkin_question_id, :answer, :score, :created_at)');
		$data = array(
				'feature_checkin_question_id'=>$this->getId(), 
				'answer'=>$answer, 
				'score'=>  json_encode(array('scores'=>$scores)),
				'created_at'=>date('Y-m-d H:i:s'),
			);
		$stat->execute($data);		
		$data['id'] = $db->lastInsertId();
		return new FeatureCheckinQuestionPossibleAnswer($data);
	}
	
	public function getPossibleAnswers() {
		$db = getDB();
		$stat = $db->prepare("SELECT * FROM feature_checkin_question_possible_answer WHERE feature_checkin_question_id=:fcqid ORDER BY sort_order DESC, id ASC");
		$stat->execute(array('fcqid'=>$this->id));
		$out = array();
		while($d = $stat->fetch()) {
			$out[] = new FeatureCheckinQuestionPossibleAnswer($d);
		}
		return $out;
	}
	
	/**
	 *
	 * @param type $id
	 * @return FeatureCheckinQuestionAnswer 
	 */
	public function getAnswer($id) {
		$db = getDB();
		$stat = $db->prepare("SELECT * FROM feature_checkin_question_possible_answer WHERE feature_checkin_question_id=:fcqid AND id=:id ORDER BY sort_order DESC, id ASC");
		$stat->execute(array('fcqid'=>$this->id, 'id'=>$id));
		$d = $stat->fetch();
		return $d ? new FeatureCheckinQuestionPossibleAnswer($d) : null;
	}	
	
	
	public function checkAnswer($answerID) {
		$answer = $this->getAnswer($answerID);
		if ($answer && $answer->getScoreOnAttempt(1) > 0) {
			// A correct answer is one that has any positive points for the first attempt.
			// This allows several correct answers, with one being more correct and giving more points than the others.
			return $answer;
		}
	}
	
	public function checkAndSaveAnswer($attemptAnswerID, User $useraccount, $userAgent = null, $ip=null) {
		$db = getDB();
		$answer = $this->checkAnswer($attemptAnswerID);
		if (!$answer) {
			$stat = $db->prepare('INSERT INTO feature_checkin_failure  (user_account_id, feature_checkin_question_id, answer_given, created_at, user_agent, ip) '.
					'VALUES (:user_account_id, :feature_checkin_question_id, :answer_given, :created_at, :user_agent, :ip)');
			$data = array(
					'user_account_id'=>$useraccount->getId(), 
					'feature_checkin_question_id'=>$this->getId(), 
					'answer_given'=>$attemptAnswerID,
					'created_at'=>date('Y-m-d H:i:s'),
					'user_agent'=>$userAgent,
					'ip'=>$ip			
				);
			$stat->execute($data);		
			return false;
		} else {
			
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
					'answer_given'=>$attemptAnswerID,
					'created_at'=>date('Y-m-d H:i:s'),
					'user_agent'=>$userAgent,
					'ip'=>$ip,
					'score'=>$answer->getScoreOnAttempt($attempt),
				);
			$stat->execute($data);	
			$useraccount->calculateAndCacheScore();
			return true;		
		}
	}	
	
}




