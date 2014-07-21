<?php
/**
 * @author James Baster  <james@jarofgreen.co.uk>
 * @copyright City of Edinburgh Council & James Baster
 * @license Open Source under the 3-clause BSD License
 * @url https://github.com/City-Outdoors/City-Outdoors-Web
 */

class CollectionSearch extends BaseSearch {
	

	public function  __construct() {
		$this->className = "Collection";
	}
	
	private $withFeatureCheckinQuestions = false;
	public function withFeatureCheckinQuestions($withFeatureCheckinQuestions = true) {
		$this->withFeatureCheckinQuestions = $withFeatureCheckinQuestions;
		
	}
	
	/** @var Organisation **/
	private $organisation;
	
	public function setOrganisation(Organisation $organisation) {
		$this->organisation = $organisation;
		return $this;
	}

	private $withNoOrganisationOnly = false;
	
	public function setWithNoOrganisationOnly($withNoOrganisationOnly) {
		$this->withNoOrganisationOnly = $withNoOrganisationOnly;
		return $this;
	}

	private $withOrganisationOnly = false;

	public function setWithOrganisationOnly($withOrganisationOnly) {
		$this->withOrganisationOnly = $withOrganisationOnly;
		return $this;
	}

		
	
	protected function execute() {
		if ($this->searchDone) throw new Exception("Search already done!");
		$db = getDB();
		$where = array();
		$joins = array();
		$vars = array();

		if ($this->withFeatureCheckinQuestions) {
			$joins[] = " JOIN item ON item.collection_id = collection.id ";
			$joins[] = " JOIN feature_checkin_question ON feature_checkin_question.feature_id = item.feature_id AND feature_checkin_question.deleted = 0";
			
		}

		if ($this->withNoOrganisationOnly) {
			$where[] = ' collection.organisation_id is null ';
		} else if ($this->withOrganisationOnly) {
			$where[] = ' collection.organisation_id is not null ';
		} else if ($this->organisation) {
			$where[] = ' collection.organisation_id = :organisationid ';
			$vars['organisationid'] = $this->organisation->getId();
		}

		$sql = "SELECT collection.* ".
			"FROM collection ".
			implode(" ", $joins).
			(count($where) > 0 ? " WHERE ".implode(" AND ", $where) : "").
			" GROUP BY collection.id ".
			" ORDER BY  collection.organisation_id, collection.id ";
		
		$stat = $db->prepare($sql);
		$stat->execute($vars);
		while($d = $stat->fetch(PDO::FETCH_ASSOC)) {
			$this->results[] = $d;
		}
		$this->searchDone = true;
	}
	
	
	
}

