<?php
/**
 * @author James Baster  <james@jarofgreen.co.uk>
 * @copyright City of Edinburgh Council & James Baster
 * @license Open Source under the 3-clause BSD License
 * @url https://github.com/City-Outdoors/City-Outdoors-Web
 */

require '../includes/src/global.php';

define('NUMBER_OF_QUESTION_FIELDS_TO_SHOW',10);

$currentUser = mustBeLoggedIn();
if (!$currentUser->isAdministrator()) die('No Access');

$feature = Feature::loadByID($_GET['id']);
if (!$feature) die('not found');


if (isset($_POST['CSFRToken']) && $_POST['action'] == 'newCheckinQuestion' && $_POST['CSFRToken'] == $_SESSION['CSFRToken']) {
	$fciq = FeatureCheckinQuestionMultipleChoice::create($feature, $_POST['question']);
	for($i = 1; $i <= NUMBER_OF_QUESTION_FIELDS_TO_SHOW; $i++) {
		if (trim($_POST['answer'.$i])) {
			$fciq->addAnswerWithScoresFromString($_POST['answer'.$i],$_POST['answerScores'.$i]);
		}
	}
	header("Location: /admin/featureCheckinQuestion.php?id=".$fciq->getId());
	die();
}


$tpl = getSmarty($currentUser);
$tpl->assign('feature',$feature);
$tpl->assign('collectionSearch', new CollectionSearch);
$tpl->assign('NUMBER_OF_QUESTION_FIELDS_TO_SHOW',NUMBER_OF_QUESTION_FIELDS_TO_SHOW);
$tpl->display('admin/newFeatureCheckinQuestionMultipleChoice.htm');


