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

$page = CMSContent::loadPageByID($_GET['id']);
if (!$page) die('not found');

if ($page->getIsImported()) die("Imported Content can not be edited");

if ($_POST && $_POST['CSFRToken'] == $_SESSION['CSFRToken']) {
	$page->newVersion($_POST['content'], $currentUser);
}

$tpl = getSmarty($currentUser);
$tpl->assign('page',$page);
$tpl->display('admin/editCMSPage.htm');


