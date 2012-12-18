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


$collection = Collection::loadBySlug($_GET['c']);
if (!$collection) die('not found');

$item = Item::loadByIdIncollection($_GET['i'], $collection);
if (!$item) die('not found');

if ($_POST && $_POST['action'] && $_POST['CSFRToken'] == $_SESSION['CSFRToken']) {
	if ($_POST['action'] == 'delete') {
		$item->delete();
	}
}

$tpl = getSmarty($currentUser);
$tpl->assign('collection',$collection);
$tpl->assign('item',$item);
$tpl->display('admin/item.htm');

