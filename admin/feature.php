<?php
/**
 * @author James Baster  <james@jarofgreen.co.uk>
 * @copyright City of Edinburgh Council & James Baster
 * @license Open Source under the 3-clause BSD License
 * @url https://github.com/City-Outdoors/City-Outdoors-Web
 */

require '../includes/src/global.php';


$currentUser = mustBeLoggedIn();
if (!$currentUser->isAdministrator()) die('No Access');

$feature = Feature::loadByID($_GET['id']);
if (!$feature) die('not found');

$itemSearch = new ItemSearch();
$itemSearch->onFeature($feature);
$itemSearch->includeDeleted(true);

$tpl = getSmarty($currentUser);
$tpl->assign('feature',$feature);
$tpl->assign('itemSearch',$itemSearch);
$tpl->assign('collectionSearch', new CollectionSearch);
$tpl->display('admin/feature.htm');


