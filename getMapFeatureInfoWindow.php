<?php
/**
 * @author James Baster  <james@jarofgreen.co.uk>
 * @copyright City of Edinburgh Council & James Baster
 * @license Open Source under the 3-clause BSD License
 * @url https://github.com/City-Outdoors/City-Outdoors-Web
 */
require 'includes/src/global.php';

$currentUser = getCurrentUser();

$feature = Feature::findByID($_GET['id']);
if (!$feature) die("No Feature");

		
$tpl = getSmarty();
$tpl->assign('feature',$feature);
$tpl->assign('item',$feature->getTitleItem());
$tpl->display('getMapFeatureInfoWindow.htm');
