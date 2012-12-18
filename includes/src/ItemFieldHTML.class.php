<?php
/**
 * @author James Baster  <james@jarofgreen.co.uk>
 * @copyright City of Edinburgh Council & James Baster
 * @license Open Source under the 3-clause BSD License
 * @url https://github.com/City-Outdoors/City-Outdoors-Web
 */


class ItemFieldHTML extends BaseItemField {
	
	const MAX_LENGHT = 16000000;

	
	protected $tableName = 'item_has_html_field';
	
	public function getEditTemplateFileName() { return 'itemField.string.edit.htm'; }

	public function getValueAsHumanReadableHTML(User $user=null) {
		$this->getLatestValueFromDataBaseIfNeeded();
		return trim($this->latestValue) ? trim($this->latestValue) : '&nbsp;';
	}
	
	public function getValueAsHumanReadableText(User $user=null) {
		$this->getLatestValueFromDataBaseIfNeeded();
		return trim($this->latestValue) ? strip_tags(trim($this->latestValue)) : '';
	}	

	public function getValue() {
		$this->getLatestValueFromDataBaseIfNeeded();
		return $this->latestValue;
	}

	public function hasValue() {
		$this->getLatestValueFromDataBaseIfNeeded();
		return (boolean)trim($this->latestValue);
	}
	
	public function getFreeTextSearchValue() {
		$this->getLatestValueFromDataBaseIfNeeded();
		return strip_tags($this->latestValue);
	}
		
	public function updateFromData($data, User $user=null) {
		$this->update($data['field'.$this->fieldID], $user);
	}

	public function update($newValue, User $user=null) {
		$this->getLatestValueFromDataBaseIfNeeded();
		$v = trim($newValue) ? trim($newValue) : null;
		if ($v != $this->latestValue) {
			$this->latestValue = $v;
			$this->hasChange = true;
			$len = mb_strlen($this->latestValue);
			if ($len > self::MAX_LENGHT) {
					$this->validationErrors[] = $this->getTitle()." Is ".($len - self::MAX_LENGHT)." characters longer than the allowed maximum lenght.";
			}
		}
	}	

        public function writeToDataBase(User $user) {
                if ($this->hasChange) {
                        if (is_null($this->latestValue)) {
                                $this->putNullValueInDataBase($user);
                        } else {
                                $this->putValueInDataBase($this->latestValue, $user);
                        }
                }
        }


	public function getType() { return 'html'; }

}
	
