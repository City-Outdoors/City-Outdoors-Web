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

if ($featureCheckinQuestion->getQuestionType() != 'HIGHERORLOWER') {
	header("Location: /admin/featureCheckinQuestion.php?id=".$featureCheckinQuestion->getId());
}

if ($_POST && $_POST['action'] && $_POST['action'] == "update" && $_POST['CSFRToken'] == $_SESSION['CSFRToken']) {
	$featureCheckinQuestion->setQuestion($_POST['question']);
	$featureCheckinQuestion->setSortOrder($_POST['sort_order']);
	$featureCheckinQuestion->setAnswerExplanation($_POST['answer_explanation']);
	$featureCheckinQuestion->setAnswers($_POST['answers']);
	$featureCheckinQuestion->setScoresFromString($_POST['score']);
	$featureCheckinQuestion->setActive($_POST['active'] == 'yes');
	$featureCheckinQuestion->setInactiveReason($_POST['inactive_reason']);
	$featureCheckinQuestion->setDeleted($_POST['deleted'] == 'yes');
}


$tpl = getSmarty($currentUser);
$tpl->assign('featureCheckinQuestion',$featureCheckinQuestion);
$tpl->display('admin/featureCheckinQuestionHigherOrLower.htm');


