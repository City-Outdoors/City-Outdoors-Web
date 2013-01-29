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

if ($_POST && $_POST['action'] && $_POST['action'] == "newField" && $_POST['CSFRToken'] == $_SESSION['CSFRToken'] && trim($_POST['name'])) {
	if ($_POST['type'] == 'string') {
		$collection->addStringField($_POST['name']);
	} else if ($_POST['type'] == 'text') {
		$collection->addTextField($_POST['name']);
	} else if ($_POST['type'] == 'html') {
		$collection->addHTMLField($_POST['name']);
	} else if ($_POST['type'] == 'email') {
		$collection->addEmailField($_POST['name']);
	} else if ($_POST['type'] == 'phone') {
		$collection->addPhoneField($_POST['name']);
	}
}
if ($_POST && $_POST['action'] && isset($_POST['fieldID']) && $_POST['fieldID'] && $_POST['CSFRToken'] == $_SESSION['CSFRToken']) {
	$field = $collection->getFieldByID($_POST['fieldID']);
	if ($field) {
		if ($_POST['action'] == 'makeFieldNotSummary') {
			$field->makeNotSummary();
			// redirect here so user reloading doesn't do anything
			header("Location: /admin/collectionFields.php?c=".$collection->getSlug());
			die();
		} else if ($_POST['action'] == 'makeFieldSummary') {
			$field->makeSummary();
			//// redirect here so user reloading doesn't do anything
			header("Location: /admin/collectionFields.php?c=".$collection->getSlug());
			die();
		} else if ($_POST['action'] == 'editInContentAreas') {
			$field->setContentAreas($_POST['value']);
			// redirect here so user reloading doesn't do anything
			header("Location: /admin/collectionFields.php?c=".$collection->getSlug());
			die();
		} else if ($_POST['action'] == 'editSortOrder') {
			$field->setSortOrder($_POST['value']);
			// must redirect here so when page reloads fields are in correct order. and so user reloading doesn't do anything
			header("Location: /admin/collectionFields.php?c=".$collection->getSlug());
			die();
		} else if ($_POST['action'] == 'editFieldContentsSlug') {
			$field->setFieldContentsSlug($_POST['value']);
			// redirect here so user reloading doesn't do anything
			header("Location: /admin/collectionFields.php?c=".$collection->getSlug());
			die();
		}
	}
}

$tpl = getSmarty($currentUser);
$tpl->assign('collection',$collection);
$tpl->display('admin/collectionFields.htm');


