<?php 
/**
 * @author James Baster  <james@jarofgreen.co.uk>
 * @copyright City of Edinburgh Council & James Baster
 * @license Open Source under the 3-clause BSD License
 * @url https://github.com/City-Outdoors/City-Outdoors-Web
 */

abstract class BaseSearch {
	
	
	protected $searchDone = false;

	protected $className = null;
	
	protected $results = array();

	public function  __construct() {

	}
	
	protected $currentPage = null;
	protected $numberOnAPage = null;
	protected $resultsTotalPages = null;
	protected $totalResultsWithOutPaging = null;
	
	/** Paging is turned off unless this is called.
	 * Note not all Search classes can do Paging yet.
	**/
	public function setPaging($currentPage=1, $numberOnAPage=20) {
		if ($this->searchDone) throw new Exception("Search already done!");
		if ($currentPage < 1) $currentPage = 1;
		$this->currentPage = $currentPage;
		$this->numberOnAPage = $numberOnAPage;
		return $this;
	}
	
	public function nextResult() {
		if (!$this->searchDone) $this->execute();
		$d = array_shift($this->results);
		return $d ? new $this->className($d) : null;
	}

	/** @return Integer the number of results  **/
	public function num() {
		if (!$this->searchDone) $this->execute();
		return count($this->results);
	}

	public function getAllResults() {
		$out = array();
		while($r = $this->nextResult()) $out[] = $r;
		return $out;
	}

	public function getAllResultsIndexed() {
		$out = array();
		while($r = $this->nextResult()) $out[$r->getId()] = $r;
		return $out;
	}

	public function getCurrentPage() {
		return $this->currentPage ? $this->currentPage : 1;
	}
	public function getTotalPages() {
		if (!$this->searchDone) $this->execute();
		return $this->resultsTotalPages ? $this->resultsTotalPages : 1;
	}
	
	public function numOnAllPages() {
		if (!$this->searchDone) $this->execute();
		return $this->totalResultsWithOutPaging;
	}
	
}
