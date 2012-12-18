<?php
/**
 * @author James Baster  <james@jarofgreen.co.uk>
 * @copyright City of Edinburgh Council & James Baster
 * @license Open Source under the 3-clause BSD License
 * @url https://github.com/City-Outdoors/City-Outdoors-Web
 */


class ItemFieldEmail extends ItemFieldString {
	
	
	public function updateFromJadu($string, User $user) {
		$prefix = '<a href="mailto:';
		if (substr($string,0,  strlen($prefix)) == $prefix) {
			$bits = explode('"', substr($string, strlen($prefix)));
			$this->update($bits[0], $user);			
		} else {
			$this->update($string, $user);						
		}		
	}

	public function getValueAsHumanReadableHTML(User $user=null) {
		$this->getLatestValueFromDataBaseIfNeeded();
		if (filter_var(trim($this->latestValue), FILTER_VALIDATE_EMAIL)) {
			$e = htmlentities(trim($this->latestValue), ENT_QUOTES, 'UTF-8');
			return '<a href="mailto:'.$e.'">'.$e.'</a>';
		}  else {
			return trim($this->latestValue) ? htmlentities(trim($this->latestValue), ENT_QUOTES, 'UTF-8')  : '&nbsp;';
		}
	}
	
	public function getType() { return 'email'; }	
}

