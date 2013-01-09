<?php
/**
 * @author James Baster  <james@jarofgreen.co.uk>
 * @copyright City of Edinburgh Council & James Baster
 * @license Open Source under the 3-clause BSD License
 * @url https://github.com/City-Outdoors/City-Outdoors-Web
 */

class FeatureCheckinQuestionContent extends FeatureCheckinQuestion {

	public function __construct($data) {
		parent::__construct($data);
	}	
 
	
	/** @return FeatureCheckinQuestion **/
	public static function create(Feature $feature, $question, $score=array(10,5)) {
		global  $CONFIG;
		$db = getDB();
		$stat = $db->prepare('INSERT INTO feature_checkin_question  (feature_id, question, created_at, question_type, score) '.
				'VALUES (:feature_id, :question, :created_at, :type, :score)');
		$data = array(
				'feature_id'=>$feature->getId(), 
				'question'=>$question, 
				'created_at'=>date('Y-m-d H:i:s'),
				'type'=>'CONTENT',
				'score'=> json_encode(array('scores'=>$score)),
			);
		$stat->execute($data);
		$data['id'] = $db->lastInsertId();
		return new FeatureCheckinQuestionContent($data);
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
	
	public function awardPoints(FeatureContent $content, $score) {
		// safety check; we can't award points to anonymous authors
		if(!$content->getAuthorID()) return;
		// lets go
		$db = getDB();
		$stat = $db->prepare('INSERT INTO feature_checkin_success  (user_account_id, feature_checkin_question_id, feature_content_id, score, created_at) '.
				'VALUES (:user_account_id, :feature_checkin_question_id, :feature_content_id, :score, :created_at)');
		$data = array(
				'user_account_id'=>$content->getAuthorID(), 
				'feature_checkin_question_id'=>$this->getId(), 
				'created_at'=>date('Y-m-d H:i:s'),
				'feature_content_id'=>$content->getId(),
				'score'=>$score,
			);
		$stat->execute($data);
		
	}
}




