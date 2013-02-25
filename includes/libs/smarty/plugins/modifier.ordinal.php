<?php
/**
 * Smarty ordinal modifier plugin
 * 
 * Thanks to http://stackoverflow.com/questions/3109978/php-display-number-with-ordinal-suffix
 * 
 * @copyright City of Edinburgh Council & James Baster
 * @license Open Source under the 3-clause BSD License
 * @url https://github.com/City-Outdoors/City-Outdoors-Web
 * @package Smarty
 * @subpackage PluginsModifier
 * @return string
 */
function smarty_modifier_ordinal($number) {
	$number = intval($number);
    if ($number == 0)
        return '';
	
	if (($number %100) >= 11 && ($number%100) <= 13) {
		return 'th';
	} else {
		$ends = array('th','st','nd','rd','th','th','th','th','th','th');
		return $ends[$number % 10];
	}
} 

