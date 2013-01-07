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

if (!$CONFIG->ALLOW_EDITING_COLLECTION_ITEMS_IN_ADMIN_UI) die("No Editing");

$collection = Collection::loadBySlug($_GET['c']);
if (!$collection) die('not found');

$feature = Feature::findByID($_GET['f']);
if (!$feature) die('not found');

$item = $collection->getBlankItem($currentUser);
$item->setFeature($feature);

$tpl = getSmarty($currentUser);
$tpl->assign('collection',$collection);
$tpl->assign('item',$item);
$tpl->assign('feature',$feature);
$tpl->assign('validationErrors',null);

if ($_POST && $_POST['submit'] && $_POST['submit'] == "yes" && $_POST['CSFRToken'] == $_SESSION['CSFRToken']) {
	$item->updateFromTemplate($_POST, $currentUser);
	$valErrors = $item->getValidationErrors();
	if ($valErrors) {
		$tpl->assign('validationErrors',$valErrors);		
	} else {
		$item->writeToDataBase($currentUser);
		header("Location: /admin/item.php?c=".$collection->getSlug()."&i=".$item->getId());
	}	
}

$tpl->display('admin/newItem.htm');
