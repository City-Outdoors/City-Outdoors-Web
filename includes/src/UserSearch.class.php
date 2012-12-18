<?php
/**
 * @author James Baster  <james@jarofgreen.co.uk>
 * @copyright City of Edinburgh Council & James Baster
 * @license Open Source under the 3-clause BSD License
 * @url https://github.com/City-Outdoors/City-Outdoors-Web
 */

class UserSearch extends BaseSearch {
	
	protected $adminsOnly = false;
	
	public function  __construct() {
		$this->className = "User";
	}
	
	public function adminsOnly() {
		$this->adminsOnly = true;
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

