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


$s = new CollectionSearch;
$s->setOrganisation($organisationAdminLogin->getOrganisation());

$tpl->assign('collectionSearch',$s);
$tpl->display('organisationadmin/listCollections.htm');

