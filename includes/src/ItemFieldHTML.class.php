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
	
	public function getEditTemplateFileName() { return 'itemField.html.edit.htm'; }

	public function getValueAsHumanReadableHTML(User $user=null) {
		$this->getLatestValueFromDataBaseIfNeeded();
		return trim($this->latestValue) ? trim($this->latestValue) : '&nbsp;';
	}

	/** 
	 * This does some special stuff; the first <a> tag in the value, the href is pulled out and put as plain text at the end of the return value.
	 * This is so readers that don't use the HTML still get some value out of it by seeing what the first link is.
	 * @param User $user
	 * @return type 
	 */
	public function getValueAsHumanReadableText(User $user=null) {
		$this->getLatestValueFromDataBaseIfNeeded();
		$value = $this->latestValue ? trim($this->latestValue) : '';
		preg_match('/<a href="(.+)">/', $value, $match);
		if ($match) {
			$value .= " ".$match[1];
		}
		return strip_tags($value);
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
		
	public function updateFromTemplate($data, User $user=null) {
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
	
