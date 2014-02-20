<?php
/**
 * @author James Baster  <james@jarofgreen.co.uk>
 * @copyright City of Edinburgh Council & James Baster
 * @license Open Source under the 3-clause BSD License
 * @url https://github.com/City-Outdoors/City-Outdoors-Web
 */

class ItemSearch extends BaseSearch {
	
	
	protected $collectionIDs = array();
	protected $notCollectionIDs = array();
	protected $featureIDs= null;
	protected $closestToLat, $closestToLng = null;

	protected $parentIDs = array();
	
	protected $fieldStartsWithField, $fieldStartsWithLetter = null; 
	
	protected $fieldSearchField, $fieldSearchText = null; 
	
	protected $fieldHasValue;
	
	protected $fieldOrderBy = null;
	
	protected $includeChildCollections = false;
	
	protected $includeDeleted = false;

	protected $includeOfficialCollectionsOnly = false;
	protected $includeUnofficialCollectionsOnly = false;

	public function  __construct() {
		$this->className = "Item";
	}
	
	public function inCollection(Collection $collection) {
		$this->collectionIDs[] = $collection->getId();
	}
	
	public function notInCollection(Collection $collection) {
		$this->notCollectionIDs[] = $collection->getId();
	}
	
	public function onFeature(Feature $feature) {
		$this->featureIDs[] = $feature->getId();
	}
	
	public function closestTo($lat,$lng) {
		$this->closestToLat = $lat;
		$this->closestToLng = $lng;
	}
	
	public function hasParentItem(Item $item) {
		$this->parentIDs[] = $item->getId();
	}
	
	public function fieldStartsWith(BaseItemFieldDefinition $field, $letter) {
		$this->fieldStartsWithField = $field;
		$this->fieldStartsWithLetter = $letter;
		
	}
	
	public function fieldSearch(BaseItemFieldDefinition $field, $letter) {
		$this->fieldSearchField = $field;
		$this->fieldSearchText = $letter;
		
	}
	
	public function fieldHasValue(BaseItemFieldDefinition $field) {
		$this->fieldHasValue = $field;
	}
	
	public function orderByField(BaseItemFieldDefinition $field) {
		$this->fieldOrderBy = $field;
	}
	
	public function includeDeleted($val = true) {
		$this->includeDeleted = $val;		
	}
	
	protected $titleMatches;
	protected $titleField;
	public function titleMatches(Collection $collection, $title) {
		$this->titleMatches = $title;
		$this->titleField = $collection->getTitleField();
	}
	
	protected $freeTextSearch;
	public function freeTextSearch($value) {
		$this->freeTextSearch = $value;
	}
	
	public function includeChildCollections() { $this->includeChildCollections = true; }
	

	public function setIncludeOfficialCollectionsOnly($includeOfficialCollectionsOnly) {
		$this->includeOfficialCollectionsOnly = $includeOfficialCollectionsOnly;
		return $this;
	}

	public function setIncludeUnofficialCollectionsOnly($includeUnofficialCollectionsOnly) {
		$this->includeUnofficialCollectionsOnly = $includeUnofficialCollectionsOnly;
		return $this;
	}

 	
	protected function execute() {
		if ($this->searchDone) throw new Exception("Search already done!");
		$db = getDB();
		$where = array();
		$joins = array(" LEFT JOIN feature ON feature.id = item.feature_id ");
		$vars = array();
		$orderBy = "item.id ASC";
		$select = array("item.*","feature.point_lat","feature.point_lng");
		
		if ($this->collectionIDs) {
			$where[] = " item.collection_id IN (".implode(",", $this->collectionIDs).")";
		} else if ($this->notCollectionIDs) {
			$where[] = " item.collection_id NOT IN (".implode(",", $this->notCollectionIDs).")";
		} else if ($this->includeOfficialCollectionsOnly) {
			$joins[] = " LEFT JOIN collection ON collection.id = item.collection_id";
			$where[] = " collection.organisation_id IS NULL ";
		} else if ($this->includeUnofficialCollectionsOnly) {
			$joins[] = " LEFT JOIN collection ON collection.id = item.collection_id";
			$where[] = " collection.organisation_id IS NOT NULL ";
		}
		
		if ($this->fieldStartsWithField) {
			list($j,$w) = $this->fieldStartsWithField->getValueStartsWithJoinsAndWheres($this->fieldStartsWithLetter);
			$joins = array_merge($joins, $j);
			$where = array_merge($where, $w);
		}
		if ($this->fieldSearchField) {
			list($j,$w) = $this->fieldSearchField->getValueSearchJoinsAndWheres($this->fieldSearchText);
			$joins = array_merge($joins, $j);
			$where = array_merge($where, $w);
		}
		if ($this->fieldHasValue) {
			list($j,$w) = $this->fieldHasValue->getHasValueJoinsAndWheres();
			$joins = array_merge($joins, $j);
			$where = array_merge($where, $w);			
		}
		
		if ($this->titleMatches) {
			list($j,$w) = $this->titleField->getValueMatchesJoinsAndWheres($this->titleMatches);
			$joins = array_merge($joins, $j);
			$where = array_merge($where, $w);			
		}
		
		if ($this->featureIDs) {
			$where[] = "  item.feature_id IN (".implode(",", $this->featureIDs).")";
		}
		if ($this->closestToLat && $this->closestToLng) {			
			$vars['closestToLat'] = $this->closestToLat;
			$vars['closestToLng'] = $this->closestToLng;
			$select[] = "((ACOS(SIN(:closestToLat * PI() / 180) * SIN(feature.point_lat * PI() / 180) + COS(:closestToLat * PI() / 180) * COS(feature.point_lat * PI() / 180) * COS((:closestToLng - feature.point_lng) * PI() / 180)) * 180 / PI()) * 60 * 1.1515) AS `distance`";
			$orderBy = " distance ASC ";
		}
		
		if (count($this->parentIDs)) {
			$where[] = " item.parent_id IN (".  implode(",", $this->parentIDs).") ";
		}

		if ($this->includeChildCollections) {
			$joins[] = "  LEFT JOIN item AS childitem ON childitem.parent_id = item.id ";
			$select[] = " GROUP_CONCAT(childitem.collection_id) AS has_child_collection_ids  ";
		}
		if ($this->freeTextSearch) {
			$where[] = " item.free_text_search LIKE ".$db->quote('%'.$this->freeTextSearch.'%');
		}
		if(!$this->includeDeleted) {
			$where[] = " item.deleted = 0";
		}

		if ($this->fieldOrderBy) {
			list($j,$f) = $this->fieldOrderBy->getOrderByJoinsAndField();
			$joins = array_merge($joins, $j);
			$orderBy = $f. " ASC";
		}
		
		
		$sql = "SELECT  ".implode(" , ", $select).
			" FROM item ".
			implode(" ", $joins).
			(count($where) > 0 ? " WHERE ".implode(" AND ", $where) : "").
			" GROUP BY item.id".
			" ORDER BY ".$orderBy;
		//print $sql; die();
		
		if ($this->currentPage) {
		
			$countSQL =	"SELECT COUNT(*) AS c FROM ".
				" ( SELECT item.id FROM item ".
				implode(" ", $joins).
				(count($where) > 0 ? " WHERE ".implode(" AND ", $where) : "").
				" GROUP BY item.id) AS t";
					
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

