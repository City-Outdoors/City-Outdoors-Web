<?php
/**
 * @author James Baster  <james@jarofgreen.co.uk>
 * @copyright City of Edinburgh Council & James Baster
 * @license Open Source under the 3-clause BSD License
 * @url https://github.com/City-Outdoors/City-Outdoors-Web
 */

require '../includes/src/global.php';


$currentUser = mustBeLoggedIn();
$organisationAdminLogin =  new OrganisationAdminLogin($currentUser, ISSET($_GET['organisationid']) ? $_GET['organisationid'] : null);
$tpl = getSmarty($currentUser);
$organisationAdminLogin->setSmartyVariables($tpl);
if (!$organisationAdminLogin->isLoggedIntoOrganisation()) {
	$tpl->display('organisationadmin/login.htm');
	die();
}


$collection = Collection::loadBySlugForOrganisation($_GET['c'], $organisationAdminLogin->getOrganisation());
if (!$collection) die('not found');


$tpl->assign('collection',$collection);

$itemSearch = new ItemSearch();
$itemSearch->inCollection($collection);
$itemSearch->includeDeleted(true);
$tpl->assign('itemSearch',$itemSearch);

$tpl->display('organisationadmin/listCollection.htm');

