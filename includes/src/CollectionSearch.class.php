<?php
/**
 * @author James Baster  <james@jarofgreen.co.uk>
 * @copyright City of Edinburgh Council & James Baster
 * @license Open Source under the 3-clause BSD License
 * @url https://github.com/City-Outdoors/City-Outdoors-Web
 */

class CollectionSearch extends BaseSearch {
	
	protected $user;
	protected $publicOnly = false;

	public function  __construct() {
		$this->className = "Collection";
	}
	
	public function visibleToUser(User $user) {
		$this->user = $user;
	}

	public function publicOnly() {
		$this->publicOnly = true;
	}
	
	protected function execute() {
		if ($this->searchDone) throw new Exception("Search already done!");
		$db = getDB();
		$where = array();
		$joins = array();
		$vars = array();

		if ($this->user) {
			// TODO
		} else if ($this->publicOnly) {
			// This is in an ELSE IF clause with user as you can't search for both at once, that doesn't make sense.
			// TODO
		}
		

		$sql = "SELECT collection.* ".
			"FROM collection ".
			implode(" ", $joins).
			(count($where) > 0 ? " WHERE ".implode(" AND ", $where) : "").
			" GROUP BY collection.id";
		
		$stat = $db->prepare($sql);
		$stat->execute($vars);
		while($d = $stat->fetch(PDO::FETCH_ASSOC)) {
			$this->results[] = $d;
		}
		$this->searchDone = true;
	}
	
	
	
}

