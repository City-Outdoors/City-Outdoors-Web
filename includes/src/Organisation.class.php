<?php
/**
 * @author James Baster  <james@jarofgreen.co.uk>
 * @copyright City of Edinburgh Council & James Baster
 * @license Open Source under the 3-clause BSD License
 * @url https://github.com/City-Outdoors/City-Outdoors-Web
 */

class Organisation extends BaseDataWithOneID {
	
	
	protected $title;
	protected $description_text;


	public static function loadById($id) {
		$db = getDB();
		$stat = $db->prepare("SELECT organisation.*  FROM organisation  ".
			" WHERE organisation.id=:id");
		$stat->bindValue('id', $id);
		$stat->execute();
		if($stat->rowCount() == 1) {
			return new Organisation($stat->fetch(PDO::FETCH_ASSOC));
		}		
	}
		
	public static function create($title, $description, User $user) {
		if (!$title) throw new Exception("Must set some title!");

		$data = array(
			'title' => $title,
			'description_text' => $description,
			'created_at'=>date('Y-m-d H:i:s'),
		);
		
		$db = getDB();
		$stat = $db->prepare('INSERT INTO organisation (title, description_text, created_at) '.
			'VALUES (:title, :description_text, :created_at) ');
			
		$stat->execute($data);
		
		$data['id'] = $db->lastInsertId();		
		$organisation = new Organisation($data);
		return $organisation;		
	}
	
	
	public function __construct($data) {
		parent::__construct($data);
		if ($data && isset($data['title'])) $this->title = $data['title'];
		if ($data && isset($data['description_text'])) $this->description_text = $data['description_text'];
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



	
} 


