<?php
/**
 * @author James Baster  <james@jarofgreen.co.uk>
 * @copyright City of Edinburgh Council & James Baster
 * @license Open Source under the 3-clause BSD License
 * @url https://github.com/City-Outdoors/City-Outdoors-Web
 */

abstract class BaseItemFieldDefinition {
	
	protected $collectionID;
	protected $fieldID;
	protected $fieldTitle;
	protected $is_summary;	
	protected $in_content_areas;	
	protected $sort_order;
	protected $field_contents_slug;
	
	public function __construct($fieldData, $collectionID) {
		$this->collectionID = $collectionID;
		$this->fieldID = $fieldData['id'];
		$this->fieldTitle = $fieldData['title'];		
		$this->is_summary = $fieldData['is_summary'];		
		$this->in_content_areas = $fieldData['in_content_areas'];	
		$this->sort_order = $fieldData['sort_order'];		
		$this->field_contents_slug = $fieldData['field_contents_slug'];		
	}

	public function getFieldID() { return $this->fieldID; }
	public function getTitle() { return $this->fieldTitle; }
	public abstract function getType();
	public function getIsSummary() { return $this->is_summary; }
	public function getInContentAreas() { return $this->in_content_areas; }		
	public function getSortOrder() { return $this->sort_order; }	
	public function getFieldContentsSlug() { return $this->field_contents_slug; }	
	
	public function setContentAreas($newValue) {
		$this->in_content_areas = $newValue;
		$db = getDB();
		$stat = $db->prepare("UPDATE collection_has_field SET in_content_areas=:v WHERE id=:id");
		$stat->execute(array('id'=>$this->fieldID,'v'=>$newValue));
	}
	
	public function setSortOrder($newValue) {
		$this->sort_order = $newValue;
		$db = getDB();
		$stat = $db->prepare("UPDATE collection_has_field SET sort_order=:v WHERE id=:id");
		$stat->execute(array('id'=>$this->fieldID,'v'=>$newValue));
	}
	
	public function setFieldContentsSlug($newValue) {
		$this->field_contents_slug = $newValue;
		$db = getDB();
		$stat = $db->prepare("UPDATE collection_has_field SET field_contents_slug=:s WHERE id=:id");
		$stat->execute(array('id'=>$this->fieldID,'s'=>$newValue));
	}
	
	public function makeNotSummary() {
		$this->is_summary = false;
		$db = getDB();
		$stat = $db->prepare("UPDATE collection_has_field SET is_summary=0 WHERE id=:id");
		$stat->execute(array('id'=>$this->fieldID));
	}
		
	public function makeSummary() {
		$this->is_summary = true;
		$db = getDB();
		$stat = $db->prepare("UPDATE collection_has_field SET is_summary=1 WHERE id=:id");
		$stat->execute(array('id'=>$this->fieldID));
	}
		
	/**
	* @return array 1st element is array of joins, 2nd element is array of where statements
	*/
	public function getValueStartsWithJoinsAndWheres($startsWith) {
		return array(array(''),array(''));
	}
		
	/**
	* @return array 1st element is array of joins, 2nd element is array of where statements
	*/
	public function getValueSearchJoinsAndWheres($startsWith) {
		return array(array(''),array(''));
	}
	/**
	* @return array 1st element is array of joins, 2nd element is array of where statements
	*/
	public function getValueMatchesJoinsAndWheres($startsWith) {
		return array(array(''),array(''));
	}
	/**
	* @return array 1st element is array of joins, 2nd element is array of where statements
	*/
	public function getHasValueJoinsAndWheres() {
		return array(array(''),array(''));
	}	
	/**
	* @return array 1st element is array of joins, 2nd element is string of field to sort on
	*/	
	public function getOrderByJoinsAndField() {
		return array(array(''),'');
	}
		
}


