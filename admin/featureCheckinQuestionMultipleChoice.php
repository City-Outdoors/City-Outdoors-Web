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

if ($featureCheckinQuestion->getQuestionType() != 'MULTIPLECHOICE') {
	header("Location: /admin/featureCheckinQuestion.php?id=".$featureCheckinQuestion->getId());
}

if ($_POST && $_POST['action'] && $_POST['action'] == "update" && $_POST['CSFRToken'] == $_SESSION['CSFRToken']) {
	$featureCheckinQuestion->setQuestion($_POST['question']);
	$featureCheckinQuestion->setAnswerExplanation($_POST['answer_explanation']);
	$featureCheckinQuestion->setSortOrder($_POST['sort_order']);
}

if ($_POST && $_POST['action'] && $_POST['action'] == "updateAnswer" && $_POST['CSFRToken'] == $_SESSION['CSFRToken']) {
	$answer = $featureCheckinQuestion->getAnswer($_POST['answerID']);
	if ($answer) {
		$answer->setAnswer($_POST['answer']);
		$answer->setSortOrder($_POST['sortOrder']);
		$answer->setScoresFromString($_POST['scores']);
	}
} else if ($_POST && $_POST['action'] && $_POST['action'] == "newAnswer" && $_POST['CSFRToken'] == $_SESSION['CSFRToken']) {
	$answer = $featureCheckinQuestion->addAnswerWithScoresFromString($_POST['answer'], $_POST['scores']);
	$answer->setSortOrder($_POST['sortOrder']);
}


$tpl = getSmarty($currentUser);
$tpl->assign('featureCheckinQuestion',$featureCheckinQuestion);
$tpl->display('admin/featureCheckinQuestionMultipleChoice.htm');


