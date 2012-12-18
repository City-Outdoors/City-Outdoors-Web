<?php
require '../includes/src/global.php';


$currentUser = mustBeLoggedIn();

$tpl = getSmarty($currentUser);



$featureContentSearch = new FeatureContentSearch();
$featureContentSearch->byUser($currentUser);
$tpl->assign('featureContentSearch',$featureContentSearch);

$tpl->display('myAccount/content.htm');
