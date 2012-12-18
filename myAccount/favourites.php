<?php
require '../includes/src/global.php';


$currentUser = mustBeLoggedIn();

$tpl = getSmarty($currentUser);

$featureSearch = new FeatureSearch();
$featureSearch->userFavourites($currentUser);
$tpl->assign('featureSearch',$featureSearch);

$collectionSearch = new CollectionSearch();
$tpl->assign('collections', $collectionSearch->getAllResultsIndexed());

$tpl->display('myAccount/favourites.htm');
