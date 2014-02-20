<?php
/**
 * @author James Baster  <james@jarofgreen.co.uk>
 * @copyright City of Edinburgh Council & James Baster
 * @license Open Source under the 3-clause BSD License
 * @url https://github.com/City-Outdoors/City-Outdoors-Web
 */

class Collection extends BaseDataWithOneID {
	
	const DEFAULT_ICON_URL = "http://www.google.com/mapfiles/markerA.png";
	const DEFAULT_ICON_WIDTH = 20;
	const DEFAULT_ICON_HEIGHT = 34;
	const DEFAULT_ICON_OFFSET_X = 10;
	const DEFAULT_ICON_OFFSET_Y = 34;
	
	protected $title, $slug;
	
	protected $icon_width;
	protected $icon_height;
	protected $icon_offset_x;
	protected $icon_offset_y;
	protected $icon_url;
	
	protected $question_icon_width;
	protected $question_icon_height;
	protected $question_icon_offset_x;
	protected $question_icon_offset_y;
	protected $question_icon_url;
	
	protected $description;
	protected $thumbnail_url;	
	protected $organisation_id;


	/** @var Array of fields, sorted by sortOrder, most important first **/
	private $fields = array();

	public static function loadBySlug($slug) {
		$db = getDB();
		$stat = $db->prepare('SELECT * FROM collection WHERE slug=:slug');
		$stat->bindValue('slug', $slug);
		$stat->execute();
		if($stat->rowCount() == 1) {
			return new Collection($stat->fetch(PDO::FETCH_ASSOC));
		}		
	}

	/**
	 * @return \Collection 
	 */
	public static function loadByFieldContentsSlug($slug) {
		$db = getDB();
		$stat = $db->prepare('SELECT collection.* FROM collection '.
				'LEFT JOIN collection_has_field ON collection_has_field.collection_id = collection.id '.
				'WHERE collection_has_field.field_contents_slug=:slug');
		$stat->bindValue('slug', $slug);
		$stat->execute();
		if($stat->rowCount() == 1) {
			return new Collection($stat->fetch(PDO::FETCH_ASSOC));
		}		
	}

	/** Returns only one; but collections don't neccisarily have unique titles so beware. **/
	public static function loadByTitle($title) {
		$db = getDB();
		$stat = $db->prepare('SELECT * FROM collection WHERE title=:title');
		$stat->bindValue('title', $title);
		$stat->execute();
		if($stat->rowCount() == 1) {
			return new Collection($stat->fetch(PDO::FETCH_ASSOC));
		}		
	}
		
	public static function loadByID($id) {
		$db = getDB();
		$stat = $db->prepare('SELECT * FROM collection WHERE id=:id');
		$stat->bindValue('id', $id);
		$stat->execute();
		if($stat->rowCount() == 1) {
			return new Collection($stat->fetch(PDO::FETCH_ASSOC));
		}		
	}	

	public static function create($title, User $user, Organisation $organisation = null) {
		if (!$title) throw new Exception("Must set some title!");
		// TODO Transaction!

		$data = array(
			'title' => $title,
			'slug' => generateSlug($title),
			'created_at' => date('Y-m-d H:i:s'),
			'created_by' => $user->getId(),
			'organisation_id' => ($organisation ? $organisation->getId() : null),
		);
		
		$db = getDB();
		$stat = $db->prepare('INSERT INTO collection (title, slug, created_at, created_by, organisation_id) '.
			'VALUES (:title, :slug, :created_at, :created_by, :organisation_id) ');
			
		try {
			$stat->execute($data);
		} catch (PDOException $e) {
			// assume it's duplicate slug error, probably shouldn't do that - we may mask other errors
			$count = 1;
			$inserted = false;
			while(!$inserted) {
				try {
					$data['slug'] = generateSlug($title)."-".$count;
					$stat->execute($data);
					$inserted = true;
				} catch (PDOException $e) {
					// assume it's duplicate slug error, probably shouldn't do that - we may mask other errors
					$count++;
				}
			}
		}
		$data['id'] = $db->lastInsertId();		
		$collection = new Collection($data);
		$collection->addStringField('Title');
		return $collection;		
	}
	
	public function __construct($data) {
		parent::__construct($data);
		if ($data && isset($data['title'])) $this->title = $data['title'];
		if ($data && isset($data['slug'])) $this->slug = $data['slug'];
		if ($data && isset($data['icon_height'])) $this->icon_height = $data['icon_height'];
		if ($data && isset($data['icon_width'])) $this->icon_width = $data['icon_width'];
		if ($data && isset($data['icon_offset_x'])) $this->icon_offset_x = $data['icon_offset_x'];
		if ($data && isset($data['icon_offset_y'])) $this->icon_offset_y = $data['icon_offset_y'];
		if ($data && isset($data['icon_url'])) $this->icon_url = $data['icon_url'];
		if ($data && isset($data['question_icon_height'])) $this->question_icon_height = $data['question_icon_height'];
		if ($data && isset($data['question_icon_width'])) $this->question_icon_width = $data['question_icon_width'];
		if ($data && isset($data['question_icon_offset_x'])) $this->question_icon_offset_x = $data['question_icon_offset_x'];
		if ($data && isset($data['question_icon_offset_y'])) $this->question_icon_offset_y = $data['question_icon_offset_y'];
		if ($data && isset($data['question_icon_url'])) $this->question_icon_url = $data['question_icon_url'];
		if ($data && isset($data['description'])) $this->description = $data['description'];
		if ($data && isset($data['thumbnail_url'])) $this->thumbnail_url = $data['thumbnail_url'];
		if ($data && isset($data['organisation_id'])) $this->organisation_id = $data['organisation_id'];
	}
	
	public function getTitle() { return $this->title; }
	public function getSlug() { return $this->slug; }
	public function getIconHeight() { return $this->icon_height ? $this->icon_height : Collection::DEFAULT_ICON_HEIGHT; }
	public function getIconWidth() { return $this->icon_width ? $this->icon_width : Collection::DEFAULT_ICON_WIDTH; }
	public function getIconOffsetX() { return $this->icon_offset_x ? $this->icon_offset_x : Collection::DEFAULT_ICON_OFFSET_X; }
	public function getIconOffsetY() { return $this->icon_offset_y ? $this->icon_offset_y : Collection::DEFAULT_ICON_OFFSET_Y; }
	public function getIconURL() { return $this->icon_url ? $this->icon_url : Collection::DEFAULT_ICON_URL; }
	public function getIconURLAbsolute() { 
		global $CONFIG;
		$iconURL = $this->icon_url ? $this->icon_url : Collection::DEFAULT_ICON_URL;
		if (substr($iconURL, 0, 1) == "/") {
			return 'http://'.$CONFIG->HTTP_HOST.$iconURL;
		} else {
			return $iconURL;
		}
	}
	public function getQuestionIconHeight() { return $this->question_icon_height ? $this->question_icon_height : Collection::DEFAULT_ICON_HEIGHT; }
	public function getQuestionIconWidth() { return $this->question_icon_width ? $this->question_icon_width : Collection::DEFAULT_ICON_WIDTH; }
	public function getQuestionIconOffsetX() { return $this->question_icon_offset_x ? $this->question_icon_offset_x : Collection::DEFAULT_ICON_OFFSET_X; }
	public function getQuestionIconOffsetY() { return $this->question_icon_offset_y ? $this->question_icon_offset_y : Collection::DEFAULT_ICON_OFFSET_Y; }
	public function getQuestionIconURL() { return $this->question_icon_url ? $this->question_icon_url : Collection::DEFAULT_ICON_URL; }
	public function getQuestionIconURLAbsolute() { 
		global $CONFIG;
		$iconURL = $this->question_icon_url ? $this->question_icon_url : Collection::DEFAULT_ICON_URL;
		if (substr($iconURL, 0, 1) == "/") {
			return 'http://'.$CONFIG->HTTP_HOST.$iconURL;
		} else {
			return $iconURL;
		}
	}
	public function getDescription() { return $this->description; }
	public function getThumbnailURL() { return $this->thumbnail_url; }
	
	public function getOrganisationId() {
		return $this->organisation_id;
	}
	
	public function getOrganisation() {
		return $this->organisation_id ? Organisation::loadById($this->organisation_id) : null;
	}

	public function setOrganisationId($organisation_id) {
		$this->organisation_id = $organisation_id;
		return $this;
	}

 	
	/** loads all field data, builds objects and caches them on this object. **/
	private function loadFields() {
		if (count($this->fields) > 0) return;
		$db = getDB();
		$stat = $db->prepare("SELECT * FROM collection_has_field WHERE collection_id=:cid ORDER BY sort_order DESC");
		$stat->execute(array('cid'=>$this->id));
		while($d = $stat->fetch()) {
			if ($d['type'] == "STRING") {
				$this->fields[] = new ItemFieldDefinitionString( $d, $this->id);
			} else if ($d['type'] == "TEXT") {
				$this->fields[] = new ItemFieldDefinitionText( $d, $this->id);
			} else if ($d['type'] == "HTML") {
				$this->fields[] = new ItemFieldDefinitionHTML( $d, $this->id);
			} else if ($d['type'] == "EMAIL") {
				$this->fields[] = new ItemFieldDefinitionEmail( $d, $this->id);
			} else if ($d['type'] == "PHONE") {
				$this->fields[] = new ItemFieldDefinitionPhone( $d, $this->id);
			} else {
				throw new Exception("We don't know about type: ".$d['type']);
			}
		}
	}
	
	
	public function getFields() {
		$this->loadFields();
		return $this->fields;
	}
	
	/** We assume the 1st string field on an item is it's title, for purposes of showing in tables and summaries. 
	  * @return ItemFieldString gets the field to use as a title. This can return NULL if there is none - be careful! **/
	public function getTitleField() {
		$this->loadFields();
		foreach($this->fields as $field) {
			if (get_class($field) == "ItemFieldDefinitionString") return $field;
		}
	}
	
	/** @return BaseItemField **/
	public function getFieldByID($id) {
		$this->loadFields();
		foreach($this->fields as $field) {
			if ($field->getFieldId() == $id) return $field;
		}
	}
	
	/** @return BaseItemField **/
	public function getFieldByFieldContentsSlug($slug) {
		$this->loadFields();
		foreach($this->fields as $field) {
			if ($field->getFieldContentsSlug() == $slug) return $field;
		}
	}
	
	public function addStringField($title) {
		// TODO get max sort order, place this at max + 1 so it's at the bottom. 
		$db = getDB();
		$stat = $db->prepare("INSERT INTO collection_has_field (collection_id,title,type,sort_order) ".
			"VALUES (:collection_id,:title,:type,:sort_order);");
		$stat->bindValue('collection_id',$this->id);
		$stat->bindValue('title',$title);
		$stat->bindValue('type','STRING');
		$stat->bindValue('sort_order',0);
		$stat->execute();
	}
	
	
	public function addHTMLField($title) {
		// TODO get max sort order, place this at max + 1 so it's at the bottom. 
		$db = getDB();
		$stat = $db->prepare("INSERT INTO collection_has_field (collection_id,title,type,sort_order) ".
			"VALUES (:collection_id,:title,:type,:sort_order);");
		$stat->bindValue('collection_id',$this->id);
		$stat->bindValue('title',$title);
		$stat->bindValue('type','HTML');
		$stat->bindValue('sort_order',0);
		$stat->execute();
	}
	
	
	public function addTextField($title) {
		// TODO get max sort order, place this at max + 1 so it's at the bottom. 
		$db = getDB();
		$stat = $db->prepare("INSERT INTO collection_has_field (collection_id,title,type,sort_order) ".
			"VALUES (:collection_id,:title,:type,:sort_order);");
		$stat->bindValue('collection_id',$this->id);
		$stat->bindValue('title',$title);
		$stat->bindValue('type','TEXT');
		$stat->bindValue('sort_order',0);
		$stat->execute();
	}
	
	public function addPhoneField($title) {
		// TODO get max sort order, place this at max + 1 so it's at the bottom. 
		$db = getDB();
		$stat = $db->prepare("INSERT INTO collection_has_field (collection_id,title,type,sort_order) ".
			"VALUES (:collection_id,:title,:type,:sort_order);");
		$stat->bindValue('collection_id',$this->id);
		$stat->bindValue('title',$title);
		$stat->bindValue('type','PHONE');
		$stat->bindValue('sort_order',0);
		$stat->execute();
	}
	
	public function addEmailField($title) {
		// TODO get max sort order, place this at max + 1 so it's at the bottom. 
		$db = getDB();
		$stat = $db->prepare("INSERT INTO collection_has_field (collection_id,title,type,sort_order) ".
			"VALUES (:collection_id,:title,:type,:sort_order);");
		$stat->bindValue('collection_id',$this->id);
		$stat->bindValue('title',$title);
		$stat->bindValue('type','EMAIL');
		$stat->bindValue('sort_order',0);
		$stat->execute();
	}

	public function getBlankItem(User $user) {
		return new Item(array("collection_id"=>$this->id));
	}
	
	public function setTitle($title) {
		$this->title = $title;
		$db = getDB();
		$stat = $db->prepare("UPDATE collection SET title=:t WHERE id=:id");
		$stat->execute(array('t'=>$title,'id'=>$this->id));
	}
	
	public function setDescription($description) {
		$this->description = $description;
		$db = getDB();
		$stat = $db->prepare("UPDATE collection SET description=:d WHERE id=:id");
		$stat->execute(array('d'=>$description,'id'=>$this->id));
	}
	
	public function setThumbnailURLFromURL($url) {
		$this->thumbnail_url = $url;
		$db = getDB();
		$stat = $db->prepare("UPDATE collection SET thumbnail_url=:u WHERE id=:id");
		$stat->execute(array('u'=>$url,'id'=>$this->id));
	}
	
	
	public function setIcon($url,$width,$height,$offset_x, $offset_y) {
		$this->icon_url = $url;
		$this->icon_height = $height;
		$this->icon_width = $width;
		$this->icon_offset_x = $offset_x;
		$this->icon_offset_y = $offset_y;
		$db = getDB();
		$stat = $db->prepare("UPDATE collection SET icon_url=:icon_url, icon_height=:icon_height , ".
				"icon_width =:icon_width ,icon_offset_x =:icon_offset_x , icon_offset_y=:icon_offset_y WHERE id=:id");
		$stat->execute(array('icon_url'=>$this->icon_url,'icon_height'=>$this->icon_height,'icon_width'=>$this->icon_width,
			'icon_offset_x'=>$this->icon_offset_x,'icon_offset_y'=>$this->icon_offset_y,'id'=>$this->id));
	}
	
	
	public function setQuestionIcon($url,$width,$height,$offset_x, $offset_y) {
		$this->question_icon_url = $url;
		$this->question_icon_height = $height;
		$this->question_icon_width = $width;
		$this->question_icon_offset_x = $offset_x;
		$this->question_icon_offset_y = $offset_y;
		$db = getDB();
		$stat = $db->prepare("UPDATE collection SET question_icon_url=:icon_url, question_icon_height=:icon_height , ".
				"question_icon_width =:icon_width ,question_icon_offset_x =:icon_offset_x , question_icon_offset_y=:icon_offset_y WHERE id=:id");
		$stat->execute(array('icon_url'=>$this->question_icon_url,'icon_height'=>$this->question_icon_height,'icon_width'=>$this->question_icon_width,
			'icon_offset_x'=>$this->question_icon_offset_x,'icon_offset_y'=>$this->question_icon_offset_y,'id'=>$this->id));
	}
	
	
} 


