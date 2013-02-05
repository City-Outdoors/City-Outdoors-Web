<?php
/**
 * @author James Baster  <james@jarofgreen.co.uk>
 * @copyright City of Edinburgh Council & James Baster
 * @license Open Source under the 3-clause BSD License
 * @url https://github.com/City-Outdoors/City-Outdoors-Web
 */
require 'includes/src/global.php';


$currentUser = getCurrentUser();


$collectionSearch = new CollectionSearch();


$tpl = getSmarty($currentUser);
$tpl->assign('inCollectionTab',true);
$tpl->assign('inMap',true);
$tpl->assign('collectionSearch',$collectionSearch);
$tpl->assign('feature',null);

if (isset($_GET['featureID']) && intval($_GET['featureID'])) {
	$feature = Feature::findByID($_GET['featureID']);
	if ($feature) {
		$tpl->assign('feature',$feature);
	}
} 

$tpl->display('map.htm');
