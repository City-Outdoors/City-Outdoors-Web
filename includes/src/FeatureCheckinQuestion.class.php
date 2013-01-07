<?php
/**
 * @author James Baster  <james@jarofgreen.co.uk>
 * @copyright City of Edinburgh Council & James Baster
 * @license Open Source under the 3-clause BSD License
 * @url https://github.com/City-Outdoors/City-Outdoors-Web
 */

class FeatureCheckinQuestion extends BaseDataWithOneID {

	
	protected $feature_id;
	protected $question;
	protected $answers;
	protected $answer_explanation;
	

	public function __construct($data) {
		parent::__construct($data);
		if ($data && isset($data['feature_id'])) $this->feature_id = $data['feature_id'];
		if ($data && isset($data['question'])) $this->question = $data['question'];
		if ($data && isset($data['answers'])) $this->answers = $data['answers'];
		if ($data && isset($data['answer_explanation'])) $this->answer_explanation = $data['answer_explanation'];
	}	
 
	/** @return FeatureCheckinQuestion **/
	public static function findByID($id) {
		global  $CONFIG;
		$db = getDB();
		$stat = $db->prepare('SELECT * FROM feature_checkin_question '.
				'WHERE id = :id ');
		$stat->execute(array('id'=>$id));
		if ($d = $stat->fetch()) {
			return new FeatureCheckinQuestion($d);	
		}
	}
 
	/** @return FeatureCheckinQuestion **/
	public static function findByIDInFeature($id, Feature $feature) {
		global  $CONFIG;
		$db = getDB();
		$stat = $db->prepare('SELECT * FROM feature_checkin_question '.
				'WHERE id = :id AND feature_id = :fid');
		$stat->execute(array('id'=>$id,'fid'=>$feature->getId()));
		if ($d = $stat->fetch()) {
			return new FeatureCheckinQuestion($d);	
		}
	}
	
	/** @return FeatureCheckinQuestion **/
	public static function findOrCreateAtPosition(Feature $feature, $question, $answers) {
		global  $CONFIG;
		$db = getDB();
		$stat = $db->prepare('INSERT INTO feature_checkin_question  (feature_id, question, answers, created_at) '.
				'VALUES (:feature_id, :question, :answers, :created_at)');
		$data = array(
				'feature_id'=>$feature->getId(), 
				'question'=>$question, 
				'answers'=>$answers,
				'created_at'=>date('Y-m-d H:i:s')
			);
		$stat->execute($data);
		$data['id'] = $db->lastInsertId();
		return new FeatureCheckinQuestion($data);
	}	

	public function getQuestion() { return $this->question; }
	public function getFeatureID() { return $this->feature_id; }

	public function getAnswers() { return $this->answers; }
	public function getAnswerExplanation() { return $this->answer_explanation; }
	
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
		
			$stat = $db->prepare('INSERT INTO feature_checkin_success  (user_account_id, feature_checkin_question_id, answer_given, created_at, user_agent, ip) '.
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
			return true;		
		}
	}
	
	public function hasAnswered(User $user) {
		$db = getDB();
		$stat = $db->prepare("SELECT * FROM feature_checkin_success WHERE user_account_id=:uid AND feature_checkin_question_id=:qid");
		$stat->execute(array('uid'=>$user->getId(),'qid'=>$this->id));
		return $stat->rowCount() > 0;				
	}
	
	public function getAllCorrectAnswersGiven() {
		$db = getDB();
		$stat = $db->prepare("SELECT * FROM feature_checkin_success WHERE feature_checkin_question_id=:qid");
		$stat->execute(array('qid'=>$this->id));
		$out = array();
		while ($d = $stat->fetch()) $out[] = new FeatureCheckinQuestionAnswer($d);
		return $out;					
	}
	
	public function getAllWrongAnswersGiven() {
		$db = getDB();
		$stat = $db->prepare("SELECT * FROM feature_checkin_failure WHERE feature_checkin_question_id=:qid");
		$stat->execute(array('qid'=>$this->id));
		$out = array();
		while ($d = $stat->fetch()) $out[] = new FeatureCheckinQuestionAnswer($d);
		return $out;							
	}

	public function setQuestion($question) {
		$this->question = $question;
		$db = getDB();
		$stat = $db->prepare("UPDATE feature_checkin_question SET question=:q WHERE id=:id");
		$stat->execute(array('q'=>$question,'id'=>$this->id));
	}

	public function setAnswers($answers) {
		$this->answers = $answers;
		$db = getDB();
		$stat = $db->prepare("UPDATE feature_checkin_question SET answers=:a WHERE id=:id");
		$stat->execute(array('a'=>$answers,'id'=>$this->id));
	}

	public function setAnswerExplanation($answerExplanation) {
		$this->answer_explanation = $answerExplanation;
		$db = getDB();
		$stat = $db->prepare("UPDATE feature_checkin_question SET answer_explanation=:a WHERE id=:id");
		$stat->execute(array('a'=>$answerExplanation,'id'=>$this->id));
	}
		
}




