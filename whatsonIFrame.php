<?php
/**
 * @author James Baster  <james@jarofgreen.co.uk>
 * @copyright City of Edinburgh Council & James Baster
 * @license Open Source under the 3-clause BSD License
 * @url https://github.com/City-Outdoors/City-Outdoors-Web
 */
require 'includes/src/global.php';


$eventSearch = new EventSearch();
$eventSearch->setAfterNow();
$eventSearch->setPaging(1, $CONFIG->WHATSON_IFRAME_SHOW_FUTURE_EVENTS);

$tpl = getSmarty();
$tpl->assign('eventSearch',$eventSearch);
$tpl->display('whatsonIFrame.htm');




