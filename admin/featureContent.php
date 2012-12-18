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

$featureContent = FeatureContent::loadByID($_GET['id']);
if (!$featureContent) die('not found');

if (isset($_POST['action']) && $_POST['CSFRToken'] == $_SESSION['CSFRToken']) {
	if ($_POST['action'] == 'approve') {
		$featureContent->approve($currentUser);
	} else if ($_POST['action'] == 'reject') {
		$featureContent->disapprove($currentUser);
	}
}


$tpl = getSmarty($currentUser);
$tpl->assign('featureContent',$featureContent);
$tpl->display('admin/featureContent.htm');


