<?php
/**
 * @author James Baster  <james@jarofgreen.co.uk>
 * @copyright City of Edinburgh Council & James Baster
 * @license Open Source under the 3-clause BSD License
 * @url https://github.com/City-Outdoors/City-Outdoors-Web
 */
require 'includes/src/global.php';


$currentUser = getCurrentUser();

$officialCollectionSearch = new CollectionSearch();
$officialCollectionSearch->setWithNoOrganisationOnly(true);

$unofficialCollectionSearch = new CollectionSearch();
$unofficialCollectionSearch->setWithOrganisationOnly(true);

$tpl = getSmarty($currentUser);
$tpl->assign('inCollectionTab',true);
$tpl->assign('inMap',true);
$tpl->assign('officialCollectionSearch',$officialCollectionSearch);
$tpl->assign('unofficialCollectionSearch',$unofficialCollectionSearch);

$tpl->display('collections.htm');
