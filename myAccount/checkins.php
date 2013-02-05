<?php
/**
 * @author James Baster  <james@jarofgreen.co.uk>
 * @copyright City of Edinburgh Council & James Baster
 * @license Open Source under the 3-clause BSD License
 * @url https://github.com/City-Outdoors/City-Outdoors-Web
 */
require '../includes/src/global.php';


$currentUser = mustBeLoggedIn();

$tpl = getSmarty($currentUser);

$collectionSearch = new CollectionSearch();
$collectionSearch->withFeatureCheckinQuestions(true);
$tpl->assign('collectionSearch', $collectionSearch);

$tpl->display('myAccount/checkins.htm');
