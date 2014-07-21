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


$organisation = Organisation::loadByID($_GET['id']);
if (!$organisation) die('not found');

$collectionSearch = new CollectionSearch();
$collectionSearch->setOrganisation($organisation);

$userSearch = new UserSearch();
$userSearch->setOrganisationAdmins($organisation);


$tpl = getSmarty($currentUser);
$tpl->assign('organisation', $organisation);
$tpl->assign('collectionSearch', $collectionSearch);
$tpl->assign('adminSearch', $userSearch);
$tpl->display('admin/organisation.htm');

