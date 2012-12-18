<?php
/**
 * @author James Baster  <james@jarofgreen.co.uk>
 * @copyright City of Edinburgh Council & James Baster
 * @license Open Source under the 3-clause BSD License
 * @url https://github.com/City-Outdoors/City-Outdoors-Web
 */
require 'includes/src/global.php';


$currentUser = getCurrentUser();

$feature = Feature::loadByID($_GET['id']);
if (!$feature) die('Not found!');

$tpl = getSmarty($currentUser);
$tpl->assign('feature',$feature);
$tpl->assign('inCollectionTab',true);

$itemSearch = new ItemSearch();
$itemSearch->onFeature($feature);
$items = $itemSearch->getAllResults();
$tpl->assign('items',$items);

if ($items) {
	$tpl->assign('inCollectionId',$items[0]->getCollectionId());
}

$featureImageSearch = new FeatureContentSearch();
$featureImageSearch->forFeature($feature);
$featureImageSearch->hasImages();
$featureImageSearch->approvedOnly();
$featureImageSearch->promotedOnly();
$tpl->assign('featureImageSearch',$featureImageSearch);
$tpl->assign('featureImageSearchCount',$featureImageSearch->num());

$tpl->display('featureDetails.htm');
