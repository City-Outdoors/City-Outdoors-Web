<?php
/**
 * Smarty plugin
 * 
 * @package Smarty
 * @subpackage PluginsModifier
 */

/**
 * 
 * linkify
 * 
 * @author James Baster  <james@jarofgreen.co.uk>
 * @copyright City of Edinburgh Council & James Baster
 * @license Open Source under the 3-clause BSD License
 * @url https://github.com/City-Outdoors/City-Outdoors-Web
 */
function smarty_modifier_linkify($string)
{
	// This is from http://stackoverflow.com/questions/4452571/smarty-modifier-turn-urls-into-links
	$string =	preg_replace_callback("/\b(https?):\/\/([-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|]*)\b/i",
                                create_function(
                                '$matches',
                                'return "<a href=\'".($matches[0])."\'>".($matches[0])."</a>";'
                                ),$string);
	
	// This regular expression is from http://www.regular-expressions.info/email.html
	$string =	preg_replace_callback("/\b[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}\b/i",
                                create_function(
                                '$matches',
                                'return "<a href=\'mailto:".($matches[0])."\'>".($matches[0])."</a>";'
                                ),$string);	
	return $string;
	
} 


