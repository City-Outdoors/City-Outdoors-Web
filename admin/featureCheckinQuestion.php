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

$featureCheckinQuestion = FeatureCheckinQuestion::findByID($_GET['id']);
if (!$featureCheckinQuestion) die('not found');

if ($_POST && $_POST['action'] && $_POST['action'] == "update" && $_POST['CSFRToken'] == $_SESSION['CSFRToken']) {
	$featureCheckinQuestion->setQuestion($_POST['question']);
	$featureCheckinQuestion->setAnswers($_POST['answers']);
}


$tpl = getSmarty($currentUser);
$tpl->assign('featureCheckinQuestion',$featureCheckinQuestion);
$tpl->display('admin/featureCheckinQuestion.htm');


