<?php
/**
 * @author James Baster  <james@jarofgreen.co.uk>
 * @copyright City of Edinburgh Council & James Baster
 * @license Open Source under the 3-clause BSD License
 * @url https://github.com/City-Outdoors/City-Outdoors-Web
 */



class TimeSource {
	
	/** @var DateTime **/
	public static function getDateTime() {
		$dt = new DateTime('', new DateTimeZone('UTC'));
		return $dt;
	}
}