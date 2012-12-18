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


$collection = Collection::loadBySlug($_GET['c']);
if (!$collection) die('not found');


$tpl = getSmarty($currentUser);
$tpl->assign('collection',$collection);

$itemSearch = new ItemSearch();
$itemSearch->inCollection($collection);
$itemSearch->includeDeleted(true);
$tpl->assign('itemSearch',$itemSearch);

$tpl->display('admin/listCollection.htm');

