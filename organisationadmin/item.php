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

$item = Item::loadByIdIncollection($_GET['i'], $collection);
if (!$item) die('not found');

/**
if ($_POST && $_POST['action'] && $_POST['CSFRToken'] == $_SESSION['CSFRToken']) {
	if ($_POST['action'] == 'delete') {
		$item->delete();
		$tpl->assign('okMessage','Deleted!');		
	} else if ($_POST['action'] == 'removeParentItem') {
		$item->setChildOf(null);
		$tpl->assign('okMessage','Parent removed');
	} else if ($_POST['action'] == 'addParentItem') {
		$parentItem = Item::loadById($_POST['parentID']);
		if ($parentItem) {
			if ($parentItem->getId() == $item->getId()) {
				$tpl->assign('errorMessage',"you can't set something as it's own Parent");
			} else { 
				$item->setChildOf($parentItem);
				$tpl->assign('okMessage','Parent set');
			}
		}
	}
}
**/

$tpl->assign('collection',$collection);
$tpl->assign('item',$item);
$tpl->assign('parentItem',$item->getParentItem());
$tpl->display('organisationadmin/item.htm');

