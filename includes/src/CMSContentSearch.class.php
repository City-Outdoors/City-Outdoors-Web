<?php
/**
 * @author James Baster  <james@jarofgreen.co.uk>
 * @copyright City of Edinburgh Council & James Baster
 * @license Open Source under the 3-clause BSD License
 * @url https://github.com/City-Outdoors/City-Outdoors-Web
 */

class CMSContentSearch extends BaseSearch {
	
	protected $type = "";

	public function  __construct() {
		$this->className = "CMSContent";
	}
	
	public function pagesOnly() { $this->type = "pages"; }
	
	protected function execute() {
		if ($this->searchDone) throw new Exception("Search already done!");
		$db = getDB();
		$where = array();
		$joins = array();
		$vars = array();

		if ($this->type == "pages") {
			$where[] = " cms_content.page_slug IS NOT NULL ";
		}

		$sql = "SELECT cms_content.* ".
			"FROM cms_content ".implode(" ", $joins).(count($where) > 0 ? " WHERE ".implode(" AND ", $where) : "")." GROUP BY cms_content.id";
		$stat = $db->prepare($sql);
		$stat->execute($vars);
		while($d = $stat->fetch(PDO::FETCH_ASSOC)) {
			$this->results[] = $d;
		}
		$this->searchDone = true;
	}
	
	
	
}

