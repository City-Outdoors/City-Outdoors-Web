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

$tpl = getSmarty($currentUser);


if (isset($_GET['scan']) && $_GET['scan']) {
	
	$out = parse_ini_file('../includes/CMSBlocks.ini');
	foreach($out as $name=>$defaultContent) {
		$block = CMSContent::loadBlockBySlug($name);
		if (!$block) {
			$block = CMSContent::createBlock($name, $currentUser);
			if ($defaultContent) {
				$block->newVersion($defaultContent, $currentUser);
			}
		}
	}
	$tpl->assign('okMessage','Scanned');
}

$search = new CMSContentSearch();
$search->blocksOnly();

$tpl->assign('CMSBlockSearch', $search );
$tpl->display('admin/listCMSBlocks.htm');

