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

if ($_POST && $_POST['action'] && $_POST['action'] == "update" && $_POST['CSFRToken'] == $_SESSION['CSFRToken']) {
	if (trim($_POST['title'])) $collection->setTitle ($_POST['title']);
	if (trim($_POST['thumbnailurl'])) $collection->setThumbnailURLFromURL($_POST['thumbnailurl']);
	$collection->setDescription($_POST['description']);
	$collection->setIcon($_POST['icon_url'], $_POST['icon_width'], $_POST['icon_height'], $_POST['icon_offset_x'], $_POST['icon_offset_y']);
	$collection->setQuestionIcon($_POST['question_icon_url'], $_POST['question_icon_width'], $_POST['question_icon_height'], $_POST['question_icon_offset_x'], $_POST['question_icon_offset_y']);
}


$tpl = getSmarty($currentUser);
$tpl->assign('collection',$collection);
$tpl->display('admin/collection.htm');


