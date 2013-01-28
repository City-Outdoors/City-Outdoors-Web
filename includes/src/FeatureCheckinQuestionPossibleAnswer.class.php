<?php
/**
 * @author James Baster  <james@jarofgreen.co.uk>
 * @copyright City of Edinburgh Council & James Baster
 * @license Open Source under the 3-clause BSD License
 * @url https://github.com/City-Outdoors/City-Outdoors-Web
 */

class FeatureCheckinQuestionPossibleAnswer extends BaseDataWithOneID {

	protected $answer;
	protected $score;
	protected $sort_order;
	
	public function __construct($data) {
		parent::__construct($data);
		if ($data && isset($data['answer'])) $this->answer = $data['answer'];
		if ($data && isset($data['score'])) $this->score = json_decode ($data['score']);
		if ($data && isset($data['sort_order'])) $this->sort_order = json_decode ($data['sort_order']);
	}
	
	public function getAnswer() { return $this->answer; }
	public function getScores() {  return $this->score ? $this->score->scores : array(0);	}
	public function getSortOrder() { return $this->sort_order; }
		
	public function getScoreOnAttempt($attempt) {
		$scores = $this->getScores();
		if (count($scores) > $attempt - 1) {
			return $scores[$attempt - 1];
		}
		return 0;
	}
	

	public function setSortOrder($sortOrder) {
		$this->sort_order = $sortOrder;
		$db = getDB();
		$stat = $db->prepare("UPDATE feature_checkin_question_possible_answer SET sort_order=:so WHERE id=:id");
		$stat->execute(array('so'=>$sortOrder,'id'=>$this->id));
	}	
	

	public function setAnswer($answer) {
		$this->answer = $answer;
		$db = getDB();
		$stat = $db->prepare("UPDATE feature_checkin_question_possible_answer SET answer=:a WHERE id=:id");
		$stat->execute(array('a'=>$answer,'id'=>$this->id));
	}	

	public function setScoresFromString($scoresAsString) {
		$scores = array();
		foreach(explode(",",$scoresAsString) as $i) {
			if (intval($i) > 0) { // No negative scores for this type of questions.
				$scores[] = intval($i);
			}
		}
		$scoreJSONObject = array('scores'=>$scores);
		$scoreJSONText = json_encode($scoreJSONObject);
		$this->score = json_decode($scoreJSONText);
		$db = getDB();
		$stat = $db->prepare("UPDATE feature_checkin_question_possible_answer SET score=:s WHERE id=:id");
		$stat->execute(array('s'=>$scoreJSONText,'id'=>$this->id));		
	} 	
	
}

