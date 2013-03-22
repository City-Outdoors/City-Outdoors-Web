<?php
/**
 * @author James Baster  <james@jarofgreen.co.uk>
 * @copyright City of Edinburgh Council & James Baster
 * @license Open Source under the 3-clause BSD License
 * @url https://github.com/City-Outdoors/City-Outdoors-Web
 */

abstract class BaseFeatureCheckinQuestion extends BaseDataWithOneID {

	protected $feature_id;
	protected $question;
	protected $answers;
	protected $answer_explanation;
	protected $question_type;
	protected $sort_order;
	protected $score;
	protected $active;
	protected $inactive_reason;
	protected $deleted;

	public function __construct($data) {
		parent::__construct($data);
		if ($data && isset($data['feature_id'])) $this->feature_id = $data['feature_id'];
		if ($data && isset($data['question'])) $this->question = $data['question'];
		if ($data && isset($data['answers'])) $this->answers = $data['answers'];
		if ($data && isset($data['answer_explanation'])) $this->answer_explanation = $data['answer_explanation'];
		if ($data && isset($data['question_type'])) $this->question_type = $data['question_type'];
		if ($data && isset($data['sort_order'])) $this->sort_order = $data['sort_order'];
		if ($data && isset($data['score'])) $this->score = json_decode ($data['score']);
		if ($data && isset($data['active'])) $this->active = $data['active'];
		if ($data && isset($data['deleted'])) $this->deleted = $data['deleted'];
		if ($data && isset($data['inactive_reason'])) $this->inactive_reason = $data['inactive_reason'];
	}	
 
	/** @return FeatureCheckinQuestion **/
	public static function findByID($id) {
		global  $CONFIG;
		$db = getDB();
		$stat = $db->prepare('SELECT * FROM feature_checkin_question '.
				'WHERE id = :id ');
		$stat->execute(array('id'=>$id));
		if ($d = $stat->fetch()) {
			if ($d['question_type'] == 'FREETEXT') {
				return new FeatureCheckinQuestionFreeText($d);	
			} else if ($d['question_type'] == 'CONTENT') {
				return new FeatureCheckinQuestionContent($d);	
			} else if ($d['question_type'] == 'MULTIPLECHOICE') {
				return new FeatureCheckinQuestionMultipleChoice($d);	
			} else if ($d['question_type'] == 'HIGHERORLOWER') {
				return new FeatureCheckinQuestionHigherOrLower($d);	
			}
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
			if ($d['question_type'] == 'FREETEXT') {
				return new FeatureCheckinQuestionFreeText($d);		
			} else if ($d['question_type'] == 'CONTENT') {
				return new FeatureCheckinQuestionContent($d);
			} else if ($d['question_type'] == 'MULTIPLECHOICE') {
				return new FeatureCheckinQuestionMultipleChoice($d);	
			} else if ($d['question_type'] == 'HIGHERORLOWER') {
				return new FeatureCheckinQuestionHigherOrLower($d);						
			}
		}
	}
	
	public function getQuestion() { return $this->question; }
	public function getQuestionType() { return $this->question_type; }
	public function getFeatureID() { return $this->feature_id; }
	public function getSortOrder() { return $this->sort_order; }
	public function getIsActive() { return (boolean)$this->active; }
	public function getIsDeleted() { return (boolean)$this->deleted; }
	public function getInactiveReason() { return $this->inactive_reason; }

	public function getAnswers() { return $this->answers; }
	public function getAnswerExplanation() { return $this->answer_explanation; }
	
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

	public function setSortOrder($sortOrder) {
		$this->sort_order = $sortOrder;
		$db = getDB();
		$stat = $db->prepare("UPDATE feature_checkin_question SET sort_order=:so WHERE id=:id");
		$stat->execute(array('so'=>$sortOrder,'id'=>$this->id));
	}
	
	public function setActive($active) {
		$this->active = $active;
		$db = getDB();
		$stat = $db->prepare("UPDATE feature_checkin_question SET active=:a WHERE id=:id");
		$stat->execute(array('a'=>$active?1:0,'id'=>$this->id));
	}	
	
	
	public function setInactiveReason($inactive_reason) {
		$this->inactive_reason = $inactive_reason;
		$db = getDB();
		$stat = $db->prepare("UPDATE feature_checkin_question SET inactive_reason=:r WHERE id=:id");
		$stat->execute(array('r'=>$inactive_reason,'id'=>$this->id));
	}	
	
	public function setDeleted($deleted) {
		$this->deleted = $deleted;
		$db = getDB();
		$stat = $db->prepare("UPDATE feature_checkin_question SET deleted=:d WHERE id=:id");
		$stat->execute(array('d'=>$deleted?1:0,'id'=>$this->id));
	}	
}




