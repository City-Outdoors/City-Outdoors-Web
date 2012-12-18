<?php
/**
 * @author James Baster  <james@jarofgreen.co.uk>
 * @copyright City of Edinburgh Council & James Baster
 * @license Open Source under the 3-clause BSD License
 * @url https://github.com/City-Outdoors/City-Outdoors-Web
 */


class ItemFieldPhone extends ItemFieldString {
	


	public function getValueAsHumanReadableHTML(User $user=null) {
		$this->getLatestValueFromDataBaseIfNeeded();
		if (trim($this->latestValue)) {
			$v =  htmlentities(trim($this->latestValue), ENT_QUOTES, 'UTF-8');
			return '<a href="tel:'.$v.'">'.$v.'</a>';
		} else {
			return '&nbsp;';
		}
	}
	
	public function getType() { return 'phone'; }
	
	
}
