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

if ($featureCheckinQuestion->getQuestionType() != 'CONTENT') {
	header("Location: /admin/featureCheckinQuestion.php?id=".$featureCheckinQuestion->getId());
}

if ($_POST && $_POST['action'] && $_POST['action'] == "update" && $_POST['CSFRToken'] == $_SESSION['CSFRToken']) {
	$featureCheckinQuestion->setQuestion($_POST['question']);
	$featureCheckinQuestion->setAnswerExplanation($_POST['answer_explanation']);
	$featureCheckinQuestion->setSortOrder($_POST['sort_order']);
	$featureCheckinQuestion->setScoresFromString($_POST['score']);
}


$tpl = getSmarty($currentUser);
$tpl->assign('featureCheckinQuestion',$featureCheckinQuestion);
$tpl->display('admin/featureCheckinQuestionContent.htm');


