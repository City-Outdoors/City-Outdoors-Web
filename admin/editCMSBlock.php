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

$block = CMSContent::loadBlockBySlug($_GET['s']);
if (!$block) $block = CMSContent::createBlock($_GET['s'], $currentUser);

if ($block->getIsImported()) die("Imported Content can not be edited");

if ($_POST && $_POST['CSFRToken'] == $_SESSION['CSFRToken']) {
	$block->newVersion($_POST['content'], $currentUser);
}

$tpl = getSmarty($currentUser);
$tpl->assign('block',$block);
$tpl->display('admin/editCMSBlock.htm');


