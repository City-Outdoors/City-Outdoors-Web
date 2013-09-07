<?php
/**
 * @author James Baster  <james@jarofgreen.co.uk>
 * @copyright City of Edinburgh Council & James Baster
 * @license Open Source under the 3-clause BSD License
 * @url https://github.com/City-Outdoors/City-Outdoors-Web
 */


class Event extends BaseDataWithOneID {
	
	protected $title;
	protected $description_text;
	protected $start_at;
	protected $end_at;
	protected $import_source;
	protected $import_id;
	protected $deleted;
	protected $features = array();
	
	/** @return Event **/
	public static function loadByID($id) {
		$db = getDB();
		$stat = $db->prepare('SELECT * FROM event WHERE id=:id');
		$stat->bindValue('id', $id);
		$stat->execute();
		if($stat->rowCount() == 1) {
			return new Event($stat->fetch(PDO::FETCH_ASSOC));
		}		
	}	
	
	/** @return Event **/
	public static function loadByImportDetails($source, $id) {
		$db = getDB();
		$stat = $db->prepare('SELECT * FROM event WHERE import_source=:s AND import_id=:id');
		$stat->bindValue('s', $source);
		$stat->bindValue('id', $id);
		$stat->execute();
		if($stat->rowCount() == 1) {
			return new Event($stat->fetch(PDO::FETCH_ASSOC));
		}
	}	
	
	public function __construct($data = null) {
		if (!$data) $data = array();
		parent::__construct($data);
		if ($data && isset($data['title'])) $this->title = $data['title'];
		if ($data && isset($data['description_text'])) $this->description_text = $data['description_text'];
		$utc = new DateTimeZone("UTC");
		if ($data && isset($data['start_at'])) {
			$this->start_at = new DateTime($data['start_at'], $utc);
		} else {
			$this->start_at = new DateTime("", $utc);
		}
		if ($data && isset($data['end_at'])) {
			$this->end_at = new DateTime($data['end_at'], $utc);
		} else {
			$this->end_at = new DateTime("", $utc);
		}
		if ($data && isset($data['import_id'])) $this->import_id = $data['import_id'];
		if ($data && isset($data['import_source'])) $this->import_source = $data['import_source'];
		if ($data && isset($data['deleted'])) $this->deleted = intval($data['deleted']);
	}
	
	public function writeToDataBase(User $user) {
		$db = getDB();
		
		if ($this->id) {
			$db = getDB();
			$stat = $db->prepare('UPDATE event SET title=:title, description_text=:description_text, start_at=:start_at, '.
					'end_at=:end_at WHERE id=:id');
			$stat->bindValue('id', $this->id);
		} else {
			$db = getDB();
			$stat = $db->prepare('INSERT INTO event (title, description_text, start_at, end_at, import_id, import_source) '.
					'VALUES (:title, :description_text, :start_at, :end_at, :import_id, :import_source)');
			$stat->bindValue('import_id', $this->import_id);
			$stat->bindValue('import_source', $this->import_source);
		}
		$stat->bindValue('title', $this->title);
		$stat->bindValue('description_text', $this->description_text);
		$stat->bindValue('start_at', $this->start_at->format("Y-m-d H:i:s"));
		$stat->bindValue('end_at', $this->end_at->format("Y-m-d H:i:s"));
		$stat->execute();			
		if (!$this->id) {
			$this->id = $db->lastInsertId();
		}

		$stat = $db->prepare("INSERT IGNORE INTO feature_has_event (feature_id,event_id) VALUES (:fid,:eid)");
		foreach ($this->features as $id=>$flag) {
			$stat->execute(array('fid'=>$id,'eid'=>  $this->id));
		}
		
		$statSelect = $db->prepare("SELECT feature_id FROM feature_has_event WHERE event_id=:eid");
		$statDelete = $db->prepare("DELETE FROM feature_has_event WHERE feature_id=:fid AND event_id=:eid)");
		$statSelect->execute(array('eid'=>$this->id));
		while($data = $statSelect->fetch()) {
			if (!isset($this->features[$data['feature_id']])) {
				$statDelete->execute(array('fid'=>$data['feature_id'],'eid'=>$this->id));
			}
		}

	}
	
	
	
	public function getTitle() {
		return $this->title;
	}

	public function setTitle($title) {
		$this->title = $title;
		return $this;
	}

	public function getDescriptionText() {
		return $this->description_text;
	}

	public function setDescriptionText($description) {
		$this->description_text = $description;
		return $this;
	}

	public function getStartAt() {
		return $this->start_at;
	}

	public function setStartAt($start_at) {
		$this->start_at = $start_at;
		return $this;
	}
	
	public function setStartAtTimestamp($start_at) {
		$this->start_at->setTimestamp($start_at);
		return $this;
	}

	public function getEndAt() {
		return $this->end_at;
	}

	public function setEndAt($end_at) {
		$this->end_at = $end_at;
		return $this;
	}
	
	public function setEndAtTimestamp($end_at) {
		$this->end_at->setTimestamp($end_at);
		return $this;
	}

	public function getImportSource() {
		return $this->import_source;
	}

	public function setImportSource($import_source) {
		$this->import_source = $import_source;
		return $this;
	}

	public function getImportId() {
		return $this->import_id;
	}

	public function setImportId($import_id) {
		$this->import_id = $import_id;
		return $this;
	}
	public function getDeleted() {
		return $this->deleted;
	}

	public function setDeleted($deleted) {
		$this->deleted = $deleted;
		return $this;
	}

	public function addFeature(Feature $feature) {
		$this->features[$feature->getId()] = true;
	}

	public function removeFeature(Feature $feature) {
		unset($this->features[$feature->getId()]);
	}
	
	public function removeAllFeatures() {
		$this->features = array();
	}

}

