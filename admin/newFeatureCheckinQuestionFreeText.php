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


if (isset($_POST['CSFRToken']) && $_POST['action'] == 'newCheckinQuestion' && $_POST['CSFRToken'] == $_SESSION['CSFRToken']) {
	$fciq = FeatureCheckinQuestionFreeText::create($feature, $_POST['question'], $_POST['answers'], $_POST['score']);
	header("Location: /admin/featureCheckinQuestion.php?id=".$fciq->getId());
	die();
}


$tpl = getSmarty($currentUser);
$tpl->assign('feature',$feature);
$tpl->assign('collectionSearch', new CollectionSearch);
$tpl->display('admin/newFeatureCheckinQuestionFreeText.htm');


