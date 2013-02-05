<?php
/**
 * @author James Baster  <james@jarofgreen.co.uk>
 * @copyright City of Edinburgh Council & James Baster
 * @license Open Source under the 3-clause BSD License
 * @url https://github.com/City-Outdoors/City-Outdoors-Web
 */
require '../includes/src/global.php';


$currentUser = mustBeLoggedIn();

$collection = Collection::loadBySlug($_GET['c']);
if (!$collection) die('not found');

$tpl = getSmarty($currentUser);
$tpl->assign('collection',$collection);

$featureSearch = new FeatureSearch();
$featureSearch->userCheckedin($currentUser);
$featureSearch->withinCollection($collection);
$tpl->assign('featureSearch',$featureSearch);

$featureSearchToDo = new FeatureSearch();
$featureSearchToDo->userNotCheckedin($currentUser);
$featureSearchToDo->withinCollection($collection);
$tpl->assign('featureSearchToDo',$featureSearchToDo);

$collectionSearch = new CollectionSearch();
$tpl->assign('collections', $collectionSearch->getAllResultsIndexed());

$tpl->display('myAccount/checkinsForCollection.htm');
