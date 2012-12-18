<?php
/**
 * @author James Baster  <james@jarofgreen.co.uk>
 * @copyright City of Edinburgh Council & James Baster
 * @license Open Source under the 3-clause BSD License
 * @url https://github.com/City-Outdoors/City-Outdoors-Web
 */

class Item extends BaseDataWithOneID {
	
	
	protected $slug;
	protected $collection_id;
	protected $feature_id;
	protected $parent_id;
	protected $deleted;
	
	/** These are used when writing a new Item **/
	protected $lat, $lng;
	
	protected $has_child_collection_ids = array();
	

	/** @var Array of fields, sorted by sortOrder, most important first **/
	private $fields = array();
	
	public static function loadBySlugIncollection($slug, Collection $collection) {
		$db = getDB();
		$stat = $db->prepare("SELECT item.*, feature.point_lat, feature.point_lng FROM item LEFT JOIN feature ON feature.id = item.feature_id ".
			"WHERE item.slug=:slug AND item.collection_id=:cid");
		$stat->bindValue('slug', $slug);
		$stat->bindValue('cid', $collection->getId());
		$stat->execute();
		if($stat->rowCount() == 1) {
			return new Item($stat->fetch(PDO::FETCH_ASSOC));
		}		
	}
	
	public static function loadByIdIncollection($id, Collection $collection) {
		$db = getDB();
		$stat = $db->prepare("SELECT item.*, feature.point_lat, feature.point_lng FROM item LEFT JOIN feature ON feature.id = item.feature_id ".
			" WHERE item.id=:id AND item.collection_id=:cid");
		$stat->bindValue('id', $id);
		$stat->bindValue('cid', $collection->getId());
		$stat->execute();
		if($stat->rowCount() == 1) {
			return new Item($stat->fetch(PDO::FETCH_ASSOC));
		}		
	}
	
	public static function loadById($id) {
		$db = getDB();
		$stat = $db->prepare("SELECT item.*, feature.point_lat, feature.point_lng FROM item LEFT JOIN feature ON feature.id = item.feature_id ".
			" WHERE item.id=:id");
		$stat->bindValue('id', $id);
		$stat->execute();
		if($stat->rowCount() == 1) {
			return new Item($stat->fetch(PDO::FETCH_ASSOC));
		}		
	}
		
	public function __construct($data) {
		parent::__construct($data);
		if ($data && isset($data['slug'])) $this->slug = $data['slug'];
		if ($data && isset($data['collection_id'])) $this->collection_id = $data['collection_id'];
		if ($data && isset($data['feature_id'])) $this->feature_id = $data['feature_id'];
		if ($data && isset($data['point_lat'])) $this->lat = $data['point_lat'];
		if ($data && isset($data['point_lng'])) $this->lng = $data['point_lng'];
		if ($data && isset($data['parent_id'])) $this->parent_id = $data['parent_id'];
		if ($data && isset($data['deleted'])) $this->deleted = $data['deleted'];
		if ($data && isset($data['has_child_collection_ids'])) {
			$this->has_child_collection_ids = array_unique(explode(",", $data['has_child_collection_ids']));
		}
	}

	public function getSlug() { return $this->slug; }	
	public function getIsDeleted() { return $this->deleted; }	
	
	public function getCollectionID() { return $this->collection_id; }	
	/** @return Collection **/
	public function getCollection() { return Collection::loadByID($this->collection_id); }

	public function getFeatureID() { return $this->feature_id; }	
	/** @return Collection **/
	public function getFeature() { return Feature::loadByID($this->feature_id); }	
	
	

	public function getParentItem() { return Item::loadById($this->parent_id); }
	
	public function getChildCollectionIDs() {
		return $this->has_child_collection_ids;
	}
	
	/** TODO This is here as we probably want to cache the results of this call at some point **/
	public function getTitle() {
		$field = $this->getTitleField();
		return $field ? $field->getValue() : '';
	}
	
	/** TODO This is here as we probably want to cache the results of this call at some point **/
	public function getDescription() {
		$field = $this->getDescriptionField();
		return $field ? $field->getValue() : '';
	}
	
	/** loads all field data, builds objects and caches them on this object. **/
	private function loadFields() {
		if (count($this->fields) > 0) return;
		$db = getDB();
		$stat = $db->prepare("SELECT * FROM collection_has_field WHERE collection_id=:cid ORDER BY sort_order DESC");
		$stat->execute(array('cid'=>$this->collection_id));
		while($d = $stat->fetch()) {
			if ($d['type'] == "STRING") {
				$this->fields[] = new ItemFieldString( $d, $this->collection_id, $this);
			} else if ($d['type'] == "TEXT") {
				$this->fields[] = new ItemFieldText( $d, $this->collection_id, $this);
			} else if ($d['type'] == "HTML") {
				$this->fields[] = new ItemFieldHTML( $d, $this->collection_id, $this);
			} else if ($d['type'] == "EMAIL") {
				$this->fields[] = new ItemFieldEmail( $d, $this->collection_id, $this);
			} else if ($d['type'] == "PHONE") {
				$this->fields[] = new ItemFieldPhone( $d, $this->collection_id, $this);
			} else {
				throw new Exception("We don't know about type: ".$d['type']);
			}
		}
	}
	
	/** We assume the 1st string field on an item is it's title, for purposes of showing in tables and summaries. 
	  * @return ItemFieldString gets the field to use as a title. This can return NULL if there is none - be careful! **/
	public function getTitleField() {
		$this->loadFields();
		foreach($this->fields as $field) {
			if (get_class($field) == "ItemFieldString") return $field;
		}
	}
	
	/** We assume the 1st text or HTML field which is summary on an item is it's description, for purposes of showing in tables and summaries. 
	  * @return ItemFieldString gets the field to use as a title. This can return NULL if there is none - be careful! **/
	public function getDescriptionField() {
		$this->loadFields();
		foreach($this->fields as $field) {
			if ($field->getIsSummary() && in_array(get_class($field),array( "ItemFieldText","ItemFieldHTML"))) return $field;
		}
	}	
	
	public function getFields() {
		$this->loadFields();
		return $this->fields;
	}
	
	/** @return BaseItemField **/
	public function getFieldID($id) {
		$this->loadFields();
		foreach($this->fields as $field) {
			if ($field->getFieldId() == $id) return $field;
		}
	}

	/** @return BaseItemField **/
	public function getFieldByTitle($title) {
		$this->loadFields();
		foreach($this->fields as $field) {
			if ($field->getTitle() == $title) return $field;
		}
	}	
	
	public function updateData($data,User $user=null) {
		$this->loadFields();
		foreach($this->fields as $field) {
			$field->updateFromData($data, $user);
		}
	}

	/**
	 * Returns any validation errors on this issue that stops the item being saved!
	 * @return Array an array of human readable strings of the errors.
	 */
	public function getValidationErrors() {
		$out = array();
		$this->loadFields();
		foreach($this->fields as $field) {
			$out = array_merge($out, $field->getValidationErrors());
		}
		if ((!$this->lat || !$this->lng) && !$this->feature_id) {
			$out[] = "You must set a location!";
		}
		return $out;
	}

    public function setPosition($lat,$lng) {
		$this->lat = $lat;
		$this->lng = $lng;
		// clearing this ensures that on save a new one is created or found at the correct place.
		$this->feature_id = null;
    }
	public function setFeature(Feature $feature) {
		$this->feature_id = $feature->getId();
	}
	
	public function getLat() { return $this->lat; }	
	public function getLng() { return $this->lng; }	

	public function writeToDataBase(User $user) {
		$db = getDB();
		$this->loadFields();
		if (count($this->getValidationErrors()) > 0) throw new Exception ("There are validation errors!");
		if (!$this->feature_id) {
			$feature = Feature::findOrCreateAtPosition($this->lat, $this->lng);
			$this->feature_id = $feature->getId();
		}				
		try {
			$db->beginTransaction();
			if (is_null($this->id)) {
				// Create Item If New
				$titleField = $this->getTitleField();
				$this->slug = $slugRoot = ($titleField && $titleField->getValueAsHumanReadableText() ? generateSlug($titleField->getValueAsHumanReadableText()) : 'item');
				
				$data = array(
					'slug' => $this->slug,
					'created_at' => date('Y-m-d H:i:s'),
					'collection_id'=>$this->collection_id,
					'feature_id'=>$this->feature_id,
				);
				$stat = $db->prepare('INSERT INTO item (slug, collection_id, feature_id, created_at) '.
					'VALUES (:slug, :collection_id, :feature_id, :created_at) ');

				try {
					$stat->execute($data);
				} catch (PDOException $e) {
					// assume it's duplicate slug error, probably shouldn't do that - we may mask other errors
					$count = 1;
					$inserted = false;
					while(!$inserted) {
						try {
							$data['slug'] = $this->slug = $slugRoot."-".$count;
							$stat->execute($data);
							$inserted = true;
						} catch (PDOException $e) {
							// assume it's duplicate slug error, probably shouldn't do that - we may mask other errors
							$count++;
						}
					}
				}
				$this->id = $db->lastInsertId();
			} else {
				// TODO feature_id might have changed! update.
			}


			// now save fields.
			$updated = false;
			$free_text_search = '';
			foreach($this->fields as $field) {
				if ($field->hasChange()) {
					$field->writeToDataBase($user);
					$updated = true;
				}
				$free_text_search .= ' '.$field->getFreeTextSearchValue();
 
			}
			// update item
			if ($updated) {
					$stat = $db->prepare("UPDATE item SET free_text_search=:v WHERE id=:id");
					$stat->execute(array('id'=>$this->id, 'v'=>$free_text_search));
			}

			$db->commit();
		} catch (Exception $e) {
			$db->rollBack();
			throw $e;
		}
	}

	public function setChildOf(Item $parentItem) {
		$db = getDB();
		$stat = $db->prepare('UPDATE item SET parent_id=:parent_id WHERE id=:id');
		$stat->execute(array('parent_id'=>$parentItem->getId(),'id'=>$this->id));
	}
	
	public function __destruct() {
		// break circular references
		$this->fields = array();
	}
		
	public function delete() {
		$this->deleted = true;
		$db = getDB();
		$stat = $db->prepare('UPDATE item SET deleted=1 WHERE id=:id');
		$stat->execute(array('id'=>$this->id));		
	}
	
} 


