<?php
/**
 * @author James Baster  <james@jarofgreen.co.uk>
 * @copyright City of Edinburgh Council & James Baster
 * @license Open Source under the 3-clause BSD License
 * @url https://github.com/City-Outdoors/City-Outdoors-Web
 */

class FeatureCheckinQuestionSearch extends BaseSearch {
	
	public function  __construct() {
		$this->className = "FeatureCheckinQuestion";
	}
	
	
	protected  $featureIDs = array();


	public function  withinFeature(Feature $feature) {
		$this->featureIDs[] = $feature->getId();
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

		$sql = "SELECT feature_checkin_question.* ".
			"FROM feature_checkin_question ".implode(" ", $joins).(count($where) > 0 ? " WHERE ".implode(" AND ", $where) : "")." GROUP BY feature_checkin_question.id";
		$stat = $db->prepare($sql);
		$stat->execute($vars);
		while($d = $stat->fetch(PDO::FETCH_ASSOC)) {
			$this->results[] = $d;
		}
		$this->searchDone = true;
	}
	
}
	
