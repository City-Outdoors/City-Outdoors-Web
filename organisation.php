<?php
/**
 * @author James Baster  <james@jarofgreen.co.uk>
 * @copyright City of Edinburgh Council & James Baster
 * @license Open Source under the 3-clause BSD License
 * @url https://github.com/City-Outdoors/City-Outdoors-Web
 */
require 'includes/src/global.php';


$currentUser = getCurrentUser();


$organisation = Organisation::loadByID($_GET['id']);
if (!$organisation) die('not found');

$collectionSearch = new CollectionSearch();
$collectionSearch->setOrganisation($organisation);


$tpl = getSmarty($currentUser);
$tpl->assign('organisation', $organisation);
$tpl->assign('collectionSearch', $collectionSearch);
$tpl->display('organisation.htm');

