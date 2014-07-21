<?php
/**
 * @author James Baster  <james@jarofgreen.co.uk>
 * @copyright City of Edinburgh Council & James Baster
 * @license Open Source under the 3-clause BSD License
 * @url https://github.com/City-Outdoors/City-Outdoors-Web
 */

class OrganisationSearch extends BaseSearch {
	
	public function  __construct() {
		$this->className = "Organisation";
	}
	
	/** @var User **/
	protected $adminUser;
	
	public function setAdminUser(User $adminUser) {
		$this->adminUser = $adminUser;
		return $this;
	}

	protected function execute() {
		if ($this->searchDone) throw new Exception("Search already done!");
		$db = getDB();
		$where = array();
		$joins = array();
		$vars = array();

		if ($this->adminUser) {
			$joins[] = " JOIN organisation_has_admin ON organisation_has_admin.organisation_id = organisation.id ";
			$where[] = " organisation_has_admin.user_account_id = :user_account_id ";
			$vars['user_account_id'] = $this->adminUser->getId();		
		}
		
		
		$sql = "SELECT organisation.* ".
			"FROM organisation ".
			implode(" ", $joins).
			(count($where) > 0 ? " WHERE ".implode(" AND ", $where) : "").
			" GROUP BY organisation.id";
		
		if ($this->currentPage) {
		
			$countSQL =	"SELECT COUNT(*) AS c FROM ".
				" ( SELECT organisation.id FROM organisation ".
				implode(" ", $joins).
				(count($where) > 0 ? " WHERE ".implode(" AND ", $where) : "").
				" GROUP BY organisation.id) AS t";
					
			$stat = $db->prepare($countSQL);
			$stat->execute($vars);
			$data =  $stat->fetch(PDO::FETCH_ASSOC);
			$this->totalResultsWithOutPaging = $data['c'];
			$this->resultsTotalPages = ceil($this->totalResultsWithOutPaging / $this->numberOnAPage);
			if ($this->currentPage > $this->resultsTotalPages) $this->currentPage = $this->resultsTotalPages;

			if ($this->resultsTotalPages > 1) {
				$sql .= " LIMIT ". (($this->currentPage-1)*$this->numberOnAPage) . "," . $this->numberOnAPage;
			}

		}
		
		
		
		
		$stat = $db->prepare($sql);
		$stat->execute($vars);
		while($d = $stat->fetch(PDO::FETCH_ASSOC)) {
			$this->results[] = $d;
		}
		$this->searchDone = true;
	}
	
	
}

