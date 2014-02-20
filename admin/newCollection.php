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


if (isset($_POST) && isset($_POST['title']) && $_POST['CSFRToken'] == $_SESSION['CSFRToken']) {
	$organisation = intval($_POST['organisation']) > 0 ? Organisation::loadById($_POST['organisation']) : null;
	$collection = Collection::create($_POST['title'],$currentUser,$organisation);
	header("Location: /admin/collection.php?c=".$collection->getSlug());
	die();
}


$organisationSearch = new OrganisationSearch();


$tpl = getSmarty($currentUser);
$tpl->assign('organisationSearch',$organisationSearch);
$tpl->display('admin/newCollection.htm');


