<?php
/**
 * @author James Baster  <james@jarofgreen.co.uk>
 * @copyright City of Edinburgh Council & James Baster
 * @license Open Source under the 3-clause BSD License
 * @url https://github.com/City-Outdoors/City-Outdoors-Web
 */
require 'includes/src/global.php';

$currentUser = getCurrentUser();

$collection = Collection::loadByFieldContentsSlug($_GET['s']);
if (!$collection) die('Not found!');

$fieldToDisplay = $collection->getFieldByFieldContentsSlug($_GET['s']);
if (!$fieldToDisplay) die('Not found!');

$page = isset($_GET['page']) ? max(intval($_GET['page']),1) : 1;
$itemSearch = new ItemSearch();
$itemSearch->setPaging($page, 10);
$itemSearch->inCollection($collection);
$itemSearch->fieldHasValue($fieldToDisplay);

$tpl = getSmarty($currentUser);
$tpl->assign('collection', $collection);
$tpl->assign('fieldToDisplay', $fieldToDisplay);
$tpl->assign('itemSearch', $itemSearch);
$tpl->assign('inCollectionTab',true);
$tpl->assign('inFieldContentsSlug', $fieldToDisplay->getFieldContentsSlug());
$tpl->display('fieldContents.htm');





