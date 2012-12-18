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
if ($feature->getTitleItem()) $tpl->assign('inCollectionId',$feature->getTitleItem()->getCollectionId());

$itemSearch = new ItemSearch();
$itemSearch->onFeature($feature);
$items = $itemSearch->getAllResults();
$tpl->assign('items',$items);

if (count($items)) {
	$page = isset($_GET['page']) ? max(intval($_GET['page']),1) : 1;
	$childItemSearch = new ItemSearch();
	$childItemSearch->setPaging($page, 10);
	$hiddenCollection = Collection::loadBySlug($CONFIG->HIDDEN_COLLECTION_SLUG);
	if ($hiddenCollection) $childItemSearch->notInCollection($hiddenCollection);	
	foreach($items as $item) {
		$childItemSearch->hasParentItem($item);
	}
	$tpl->assign('childItemSearch',$childItemSearch);
} else {
	$tpl->assign('childItemSearch',null);
}

$collectionSearch = new CollectionSearch();
$tpl->assign('collections', $collectionSearch->getAllResultsIndexed());

$tpl->display('featureChildren.htm');
