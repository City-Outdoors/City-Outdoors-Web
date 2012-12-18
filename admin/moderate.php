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


if ($_POST && isset($_POST['contentID']) && isset($_POST['action']) && $_POST['CSFRToken'] == $_SESSION['CSFRToken']) {
	$content = FeatureContent::loadByID($_POST['contentID']);
	if ($content && !$content->isApproved() && !$content->isRejected()) {
		if ($_POST['action'] == 'Approve') {
			$content->updateBody($_POST['body']);
			if (isset($_POST['name'])) $content->updateCreatedName($_POST['name']);
			$content->approve($currentUser);
			if (isset($_POST['promote']) && $_POST['promote'] == 'yes') $content->promote($currentUser);
		} else if ($_POST['action'] == 'Disapprove') {
			$content->disapprove($currentUser);
		}
	}
}


$s = new FeatureContentSearch();
$s->toModerateOnly();
// TODO 1 only!
$content = $s->nextResult();


$tpl = getSmarty($currentUser);

if ($content) {
	$tpl->assign('content',$content);
	$tpl->display('admin/moderate.content.htm');
} else {
	$tpl->display('admin/moderate.nothing.htm');
}

