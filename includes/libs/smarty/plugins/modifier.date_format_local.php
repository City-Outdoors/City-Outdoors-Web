<?php
/**
 * Smarty plugin
 * 
 * @package Smarty
 * @subpackage PluginsModifier
 */

/**
 * 
 * date_format_local
 * 
 * @author James Baster  <james@jarofgreen.co.uk>
 * @copyright City of Edinburgh Council & James Baster
 * @license Open Source under the 3-clause BSD License
 * @url https://github.com/City-Outdoors/City-Outdoors-Web
 */
function smarty_modifier_date_format_local(DateTime $dateTimeObj, $format=null)
{
	global $CONFIG;
    if ($format === null) {
        $format = 'g:ia D jS M Y';
    }
	$dt = clone $dateTimeObj;
	$dt->setTimezone(new DateTimeZone($CONFIG->LOCAL_TIME_ZONE));
	return $dt->format($format);
} 


