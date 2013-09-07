<?php
/**
 * @author James Baster  <james@jarofgreen.co.uk>
 * @copyright City of Edinburgh Council & James Baster
 * @license Open Source under the 3-clause BSD License
 * @url https://github.com/City-Outdoors/City-Outdoors-Web
 */

class EventSearch extends BaseSearch {
	
	public function  __construct() {
		$this->className = "Event";
	}
	
	protected $includeDeleted = false;
	
	public function includeDeleted($includeDeleted) {
		$this->includeDeleted = $includeDeleted;
	}
	
	protected  $feature;
	
	public  function onFeature(Feature $feature) {
		$this->feature = $feature;
	}


	/** @var \DateTime **/
	protected $after;
	
	public function setAfter(DateTime $a) {
		$this->after = $a;
		return $this;
	}
	
	public function setAfterNow() {
		$this->after = TimeSource::getDateTime();
		return $this;
	}
	
	/** @var \DateTime **/
	protected $before;
	
	public function setBefore(DateTime $b) {
		$this->before = $b;
		return $this;
	}
	
	protected function execute() {
		if ($this->searchDone) throw new Exception("Search already done!");
		$db = getDB();
		$where = array();
		$joins = array();
		$vars = array();

		if ($this->after) {
			$where[] = ' event.end_at > :after';
			$vars['after'] = $this->after->format("Y-m-d H:i:s");
		}
		
		if ($this->before) {
			$where[] = ' event.start_at < :before';
			$vars['before'] = $this->before->format("Y-m-d H:i:s");
		}
		
		if (!$this->includeDeleted) {
			$where[] = ' event.deleted = 0 ';
		}
		
		
		if ($this->feature) {
			$joins[] = " JOIN feature_has_event ON feature_has_event.event_id = event.id ";
			$where[] = "  feature_has_event.feature_id = :feature_id ";
			$vars['feature_id'] = $this->feature->getId();
		}
		
		
		$sql = "SELECT event.* ".
			"FROM event ".
			implode(" ", $joins).
			(count($where) > 0 ? " WHERE ".implode(" AND ", $where) : "").
			" GROUP BY event.id";
		
		if ($this->currentPage) {
		
			$countSQL =	"SELECT COUNT(*) AS c FROM ".
				" ( SELECT event.id FROM event ".
				implode(" ", $joins).
				(count($where) > 0 ? " WHERE ".implode(" AND ", $where) : "").
				" GROUP BY event.id) AS t";
					
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

