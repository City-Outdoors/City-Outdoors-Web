<?php
/**
 * @author James Baster  <james@jarofgreen.co.uk>
 * @copyright City of Edinburgh Council & James Baster
 * @license Open Source under the 3-clause BSD License
 * @url https://github.com/City-Outdoors/City-Outdoors-Web
 */

class ItemFieldDefinitionString extends BaseItemFieldDefinition {
	
	protected $tableName = 'item_has_string_field';
	
	public function getType() { return 'string'; }
	
	public function getValueStartsWithJoinsAndWheres($startsWith) {
		$db = getDB();
		$tblAlias = "field".$this->fieldID;
		$joins  = array("JOIN ".$this->tableName." AS ".$tblAlias." ON ".$tblAlias.".item_id = item.id ".
				"AND ".$tblAlias.".field_id = ".  intval($this->fieldID)." AND ".$tblAlias.".is_latest = 1");
		$where = array(" field".$this->fieldID.".field_value LIKE ".$db->quote($startsWith.'%')."");
		return array($joins,$where);
	}
	
	public function getValueSearchJoinsAndWheres($startsWith) {
		$db = getDB();
		$tblAlias = "field".$this->fieldID;
		$joins  = array("JOIN ".$this->tableName." AS ".$tblAlias." ON ".$tblAlias.".item_id = item.id  ".
				"AND ".$tblAlias.".field_id = ".  intval($this->fieldID)." AND ".$tblAlias.".is_latest = 1");
		$where = array(" field".$this->fieldID.".field_value LIKE ".$db->quote('%'.$startsWith.'%')."");
		return array($joins,$where);
	}
	
	
	public function getValueMatchesJoinsAndWheres($value) {
		$db = getDB();
		$tblAlias = "field".$this->fieldID;
		$joins  = array("JOIN ".$this->tableName." AS ".$tblAlias." ON ".$tblAlias.".item_id = item.id  ".
				"AND ".$tblAlias.".field_id = ".  intval($this->fieldID)." AND ".$tblAlias.".is_latest = 1");
		$where = array(" field".$this->fieldID.".field_value =  ".$db->quote($value)."");
		return array($joins,$where);
	}
	
	public function getOrderByJoinsAndField() {
		$tblAlias = "orderfield".$this->fieldID;
		$joins  = array("JOIN ".$this->tableName." AS ".$tblAlias." ON ".$tblAlias.".item_id = item.id  ".
				"AND ".$tblAlias.".field_id = ".  intval($this->fieldID)." AND ".$tblAlias.".is_latest = 1");
		return array($joins,$tblAlias.".field_value ");
	}
	
}


