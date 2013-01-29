<?php
/**
 * @author James Baster  <james@jarofgreen.co.uk>
 * @copyright City of Edinburgh Council & James Baster
 * @license Open Source under the 3-clause BSD License
 * @url https://github.com/City-Outdoors/City-Outdoors-Web
 */

class ItemFieldDefinitionText extends BaseItemFieldDefinition {

	public function getType() { return 'text'; }
	
	protected $tableName = 'item_has_text_field';
	
	/**
	* @return array 1st element is array of joins, 2nd element is array of where statements
	*/
	public function getHasValueJoinsAndWheres() {
		$db = getDB();
		$tblAlias = "field".$this->fieldID;
		$joins  = array("JOIN ".$this->tableName." AS ".$tblAlias." ON ".$tblAlias.".item_id = item.id  ".
				"AND ".$tblAlias.".field_id = ".  intval($this->fieldID)." AND ".$tblAlias.".is_latest = 1");
		$where = array(" field".$this->fieldID.".field_value IS NOT NULL AND field".$this->fieldID.".field_value != '' ");
		return array($joins,$where);
	}	
	
}


