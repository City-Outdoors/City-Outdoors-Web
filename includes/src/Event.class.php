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
	}
	
	public function writeToDataBase(User $user) {
		if ($this->id) {
			$db = getDB();
			$stat = $db->prepare('UPDATE event SET title=:title, description_text=:description_text, start_at=:start_at, '.
					'end_at=:end_at WHERE id=:id');
			$stat->bindValue('title', $this->title);
			$stat->bindValue('description_text', $this->description_text);
			$stat->bindValue('start_at', $this->start_at->format("Y-m-d H:i:s"));
			$stat->bindValue('end_at', $this->end_at->format("Y-m-d H:i:s"));
			$stat->bindValue('id', $this->id);
			$stat->execute();
		} else {
			$db = getDB();
			$stat = $db->prepare('INSERT INTO event (title, description_text, start_at, end_at, import_id, import_source) '.
					'VALUES (:title, :description_text, :start_at, :end_at, :import_id, :import_source)');
			$stat->bindValue('title', $this->title);
			$stat->bindValue('description_text', $this->description_text);
			$stat->bindValue('start_at', $this->start_at->format("Y-m-d H:i:s"));
			$stat->bindValue('end_at', $this->end_at->format("Y-m-d H:i:s"));
			$stat->bindValue('import_id', $this->import_id);
			$stat->bindValue('import_source', $this->import_source);
			$stat->execute();			
			$this->id = $db->lastInsertId();
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


}

