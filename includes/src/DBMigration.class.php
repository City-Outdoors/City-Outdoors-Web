<?php
/**
 * @author James Baster  <james@jarofgreen.co.uk>
 * @copyright City of Edinburgh Council & James Baster
 * @license Open Source under the 3-clause BSD License
 * @url https://github.com/City-Outdoors/City-Outdoors-Web
 */

class DBMigration {
	

	private $id;
	private $sql;
	private $applied = false;


	public function __construct($id=null, $sql=null) {
		$this->id = $id;
		$this->sql = $sql;
	}
	
	public function getId() { return $this->id; }
	public function getApplied() { return $this->applied; }
	public function setIsApplied() { $this->applied = true; }

	public  function performMigration(PDO $db) {
		foreach(explode(";", $this->sql) as $line) {
			if (trim($line)) {
				$db->query($line.';');
			}
		}
	}
	
	public function getIdAsUnixTimeStamp() {
		$year = substr($this->id, 0, 4);
		$month = substr($this->id, 4, 2);
		$day = substr($this->id, 6, 2);
		$hour = substr($this->id, 8, 2);
		$min = substr($this->id, 10, 2);
		$sec = substr($this->id, 12, 2);
		return mktime($hour,$min,$sec,$month,$day,$year);
	}
	
}


