<?php
/**
 * @author James Baster  <james@jarofgreen.co.uk>
 * @copyright City of Edinburgh Council & James Baster
 * @license Open Source under the 3-clause BSD License
 * @url https://github.com/City-Outdoors/City-Outdoors-Web
 */

class UserSearch extends BaseSearch {
	
	protected $adminsOnly = false;
	protected $userScoreChart = false;
	
	public function  __construct() {
		$this->className = "User";
	}
	
	public function adminsOnly() {
		$this->adminsOnly = true;
	}
	
	public function setUserScoreChart($v = true) {
		$this->userScoreChart = $v;
	}
	
	/** @var Organisation **/
	protected $organisationAdmins;
	
	public function setOrganisationAdmins(Organisation $organisationAdmins) {
		$this->organisationAdmins = $organisationAdmins;
		return $this;
	}

			
	protected function execute() {
		if ($this->searchDone) throw new Exception("Search already done!");
		$db = getDB();
		$where = array();
		$joins = array();
		$vars = array();
		$orderBy = "user_account.id ASC";
		$select = array("user_account.*");
		
		if ($this->adminsOnly) {
			$where[] = "  user_account.administrator   = 1 OR user_account.system_administrator   = 1 ";
		}
		if ($this->userScoreChart) {
			// admin may want to answer Q's to check and we don't want them appearing on the charts, they have an unfair advantage.
			$where[] = "  user_account.administrator   = 0 AND user_account.system_administrator   = 0 ";
			// only show users with score
			$where[] = "  user_account.cached_score > 0 ";
			$orderBy = " user_account.cached_score DESC ";
		}
		
		if ($this->organisationAdmins) {
			$joins[] = " JOIN organisation_has_admin ON organisation_has_admin.user_account_id = user_account.id ";
			$where[] = " organisation_has_admin.organisation_id = :organisation_id ";
			$vars['organisation_id'] = $this->organisationAdmins->getId();
		}
		
		$sql = "SELECT  ".implode(" , ", $select).
			" FROM user_account ".
			implode(" ", $joins).
			(count($where) > 0 ? " WHERE ".implode(" AND ", $where) : "").
			" GROUP BY user_account.id".
			" ORDER BY ".$orderBy;
		$stat = $db->prepare($sql);
		$stat->execute($vars);
		while($d = $stat->fetch(PDO::FETCH_ASSOC)) {
			$this->results[] = $d;
		}
		$this->searchDone = true;
	}
	

}

