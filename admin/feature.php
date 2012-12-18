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

$feature = Feature::loadByID($_GET['id']);
if (!$feature) die('not found');


if (isset($_POST['CSFRToken']) && $_POST['CSFRToken'] == $_SESSION['CSFRToken']) {
	if ($_POST['action'] == 'newCheckinQuestion') {
		FeatureCheckinQuestion::findOrCreateAtPosition($feature, $_POST['question'], $_POST['answers']);
		
	
	}
}


$tpl = getSmarty($currentUser);
$tpl->assign('feature',$feature);
$tpl->display('admin/feature.htm');


