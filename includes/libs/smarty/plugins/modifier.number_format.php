<?php
/**
 * Smarty number format modifier plugin
 * 
 * @copyright City of Edinburgh Council & James Baster
 * @license Open Source under the 3-clause BSD License
 * @url https://github.com/City-Outdoors/City-Outdoors-Web
 * @package Smarty
 * @subpackage PluginsModifier
 * @return string
 */
function smarty_modifier_number_format($number, $decimals = 0 , $dec_point = '.' , $thousands_sep = ',') {
	return number_format($number, $decimals, $dec_point, $thousands_sep);
} 

