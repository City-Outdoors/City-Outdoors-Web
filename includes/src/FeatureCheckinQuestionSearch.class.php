<?php
/**
 * @author James Baster  <james@jarofgreen.co.uk>
 * @copyright City of Edinburgh Council & James Baster
 * @license Open Source under the 3-clause BSD License
 * @url https://github.com/City-Outdoors/City-Outdoors-Web
 */

class FeatureCheckinQuestionSearch extends BaseSearch {
	
	protected $orderBy = "sort_order DESC";
	protected $types = array();
	
	public function  __construct() {
		$this->className = "FeatureCheckinQuestion";
	}
	
	
	protected  $featureIDs = array();


	public function  withinFeature(Feature $feature) {
		$this->featureIDs[] = $feature->getId();
	}
	
	public function ofType($type) {
		if (in_array($type, array('CONTENT','FREETEXT'))) {
			$this->types[] = $type;
		}
	}
	
	protected function execute() {
		if ($this->searchDone) throw new Exception("Search already done!");
		$db = getDB();
		$where = array();
		$joins = array();
		$vars = array();
		
		if ($this->featureIDs) {
			$where[] = " feature_checkin_question.feature_id IN (".  implode(",", $this->featureIDs).") ";
		}
		if ($this->types) {
			$typesForSQL = array();
			foreach($this->types as $t) $typesForSQL[] = "'".$t."'";  // it is crucial ofType() function only allows set types otherwise there is an SOL injection security problem here
			$where[] = " feature_checkin_question.question_type IN (".  implode(",", $typesForSQL).") ";
		}

		$sql = "SELECT feature_checkin_question.* ".
			"FROM feature_checkin_question ".implode(" ", $joins).(count($where) > 0 ? " WHERE ".implode(" AND ", $where) : "").
			" GROUP BY feature_checkin_question.id ORDER BY ".$this->orderBy;
		$stat = $db->prepare($sql);
		$stat->execute($vars);
		while($d = $stat->fetch(PDO::FETCH_ASSOC)) {
			$this->results[] = $d;
		}
		$this->searchDone = true;
	}
	
	public function nextResult() {
		if (!$this->searchDone) $this->execute();
		$d = array_shift($this->results);
		if ($d) {
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
	
}
	
