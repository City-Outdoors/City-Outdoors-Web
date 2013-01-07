<?php
/**
 * @author James Baster  <james@jarofgreen.co.uk>
 * @copyright City of Edinburgh Council & James Baster
 * @license Open Source under the 3-clause BSD License
 * @url https://github.com/City-Outdoors/City-Outdoors-Web
 */

abstract class BaseItemField {
	
	/** @var Item **/
	protected $item;

	protected $collectionID;
	protected $fieldID;
	protected $fieldTitle;
	protected $is_summary;
	protected $in_content_areas;

	/**
	 * This class has several functions that may help child classes - if you use them you must set this var to the name of the table data is stored in.
	 * @see getLatestValueFromDataBaseIfNeeded()
	 ***/
	protected $tableName = null;
	
	
	protected $latestValueLoaded = false;
	protected $latestValue = null;
	
	public function __construct($fieldData, $collectionID, Item $item = null) {
		$this->item = $item;
		$this->collectionID = $collectionID;
		$this->fieldID = $fieldData['id'];
		$this->fieldTitle = $fieldData['title'];
		$this->is_summary = $fieldData['is_summary'];
		$this->in_content_areas = $fieldData['in_content_areas'];
	}
	
	
	public function getFieldID() { return $this->fieldID; }
	public function getTitle() { return $this->fieldTitle; }
	public function getIsSummary() { return $this->is_summary; }
	public function getInContentAreas() { return $this->in_content_areas; }	
	public abstract function getEditTemplateFileName();
	
	public abstract function getFreeTextSearchValue();
	
	public abstract function getType();	

	public function isInContentArea($area) { 
		$data = explode(",", $this->in_content_areas);
		return in_array(trim($area), $data ); 
	}	
	
	/**
	 * Gets the latest value, in whatever data format makes sense for this field.
	 * $user is not passed because this function should return the raw value, not a value personalised for the user.
	 *   (eg Dates should always be in UTC time zone from this function)
	 * @return <type> See particular fields for more info. **/
	public abstract function getValue();
	
	/** 
	 * @return boolean is there a value, or is it null? 
	 */
	public abstract function hasValue();

	/** 
	 * @param User $user Users can set personal display options, so the field might have to know which user is looking
	 */
	public abstract function getValueAsHumanReadableHTML(User $user=null);

	/** This is used in tables, where space is limited. Some column types will return a shortened version.
	 * @param User $user Users can set personal display options, so the field might have to know which user is looking
	 ***/
	public function getValueAsShortHumanReadableHTML(User $user=null) {   return $this->getValueAsHumanReadableHTML($user); }

	/** This is used in CSVs and Emails
	 * @param User $user Users can set personal display options, so the field might have to know which user is looking
	 ***/
	public abstract function getValueAsHumanReadableText(User $user=null);	
	
	/**
	 *
	 * @param Boolean $alwaysFetchFromDB
	 * @see $tableName
	 */
	protected function getLatestValueFromDataBaseIfNeeded($alwaysFetchFromDB=false) {
		if (!$this->latestValueLoaded || $alwaysFetchFromDB) {
			$db = getDB();
			$stat = $db->prepare("SELECT field_value FROM ".$this->tableName." WHERE  field_id=:fid AND item_id=:iid AND is_latest =1");
			$stat->execute(array('fid'=>$this->fieldID,'iid'=>$this->item->getId() ));
			if ($stat->rowCount() > 0) {
				$d = $stat->fetch(PDO::FETCH_ASSOC);
				$this->latestValue = $d['field_value'];
			}
			$this->latestValueLoaded = true;			
		}
	}

	protected $validationErrors = array();

	/**
	 * Returns any validation errors on this field that stop the field/issue being saved!
	 * @return Array an array of human readable strings of the errors.
	 */
	public function getValidationErrors() {
		return $this->validationErrors;
	}

	/** Used by updateFromTemplate() & writeToDataBase() funcs - store whether anything actually changed .... **/
	protected $hasChange = false;

	public function hasChange() { return $this->hasChange; }

	/** this takes data from an array of data from an edit form. Whatever key names a field sets on the form will be here.
	 * Should also set $this->hasChange - a Boolean of whether anything actually changed or not
	 * @param Array $data The array of data to update from.
	 * @param User $user Users can set personal display options, so the field might have to know which user is setting.
	 * **/
	public abstract function updateFromTemplate($data, User $user=null);

	/** This function takes data from memory and saves it into the database. **/
	public abstract function writeToDataBase(User $user);

        /**
         * This should always be called as part of a DB Transaction.
         * @param <type> $value
         * @param User $user
         * @see $tableName
         */
        protected function putValueInDataBase($value, User $user) {
                $db = getDB();
                # remove old values
                $stat = $db->prepare("UPDATE ".$this->tableName." SET is_latest=0 WHERE item_id=:iid AND field_id=:fid");
                $stat->execute(array('iid'=>$this->item->getId(),'fid'=>$this->fieldID));
                # insert new value
                $stat = $db->prepare("INSERT INTO ".$this->tableName." (item_id,field_id,field_value,created_at,created_by) ".
					"VALUES(:item_id,:field_id,:field_value,:created_at,:created_by) ");
                $stat->execute( array(
						'item_id'=>$this->item->getId(),
						'field_id'=>$this->fieldID,
						'field_value'=>$value, 
						"created_at"=>date("Y-m-d H:i:s") ,
						'created_by'=>$user->getID()
					));
        }

        /**
         *
         * @param User $user
         * @see $tableName
         */
        protected function putNullValueInDataBase(User $user) {
                $db = getDB();
                # remove old values
                $stat = $db->prepare("UPDATE ".$this->tableName." SET is_latest=0 WHERE item_id=:iid AND field_id=:fid");
                $stat->execute(array('iid'=>$this->item->getId(),'fid'=>$this->fieldID));
                # insert new value
                $stat = $db->prepare("INSERT INTO ".$this->tableName." (item_id,field_id,field_value,created_at,created_by) ".
					"VALUES(:item_id,:field_id, NULL ,:created_at,:created_by) ");
                $stat->execute( array(
						'item_id'=>$this->item->getId(),
						'field_id'=>$this->fieldID,
						"created_at"=>date("Y-m-d H:i:s")  ,
						'created_by'=>$user->getID()
					));
        }

}
