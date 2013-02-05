<?php
/**
 * @author James Baster  <james@jarofgreen.co.uk>
 * @copyright City of Edinburgh Council & James Baster
 * @license Open Source under the 3-clause BSD License
 * @url https://github.com/City-Outdoors/City-Outdoors-Web
 */
require 'includes/src/global.php';


$currentUser = getCurrentUser();

$collection = Collection::loadBySlug($_GET['c']);
if (!$collection) die('not found');


$tpl = getSmarty($currentUser);
$tpl->assign('inCollectionTab',true);
$tpl->assign('inCollectionId',$collection->getId());


$tpl->assign('collection',$collection);

$collectionSearch = new CollectionSearch();
$tpl->assign('collections', $collectionSearch->getAllResultsIndexed());

$page = isset($_GET['page']) ? max(intval($_GET['page']),1) : 1;
$itemSearch = new ItemSearch();
$itemSearch->setPaging($page, 10);
$itemSearch->inCollection($collection);
$itemSearch->orderByField($collection->getTitleField());
if (isset($_GET['letter']) && trim($_GET['letter'])) {
	$tpl->assign('activeLetter',trim($_GET['letter']));
	$itemSearch->fieldStartsWith($collection->getTitleField(), trim($_GET['letter']));
} else {
	$tpl->assign('activeLetter',null);
}
$itemSearch->includeChildCollections();
$tpl->assign('itemSearch',$itemSearch);

if ($collection->getSlug() == $CONFIG->HIDDEN_COLLECTION_SLUG) {
	$tpl->display('collectionAsList.hidden.htm');
} else {
	$tpl->assign('hiddenCollection', Collection::loadBySlug($CONFIG->HIDDEN_COLLECTION_SLUG));
	$tpl->display('collectionAsList.htm');
}

