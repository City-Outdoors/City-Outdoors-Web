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
	$collection = Collection::create($_POST['title'],$currentUser);
	header("Location: /collectionAsList.php?c=".$collection->getSlug());
	die();
}


$tpl = getSmarty($currentUser);
$tpl->display('admin/newCollection.htm');


