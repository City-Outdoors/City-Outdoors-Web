<?php
require '../includes/src/global.php';


$currentUser = mustBeLoggedIn();

$tpl = getSmarty($currentUser);

$featureSearch = new FeatureSearch();
$featureSearch->userCheckedin($currentUser);
$tpl->assign('featureSearch',$featureSearch);

$featureSearchToDo = new FeatureSearch();
$featureSearchToDo->userNotCheckedin($currentUser);
$tpl->assign('featureSearchToDo',$featureSearchToDo);

$collectionSearch = new CollectionSearch();
$tpl->assign('collections', $collectionSearch->getAllResultsIndexed());

$tpl->display('myAccount/checkins.htm');
