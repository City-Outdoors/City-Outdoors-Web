<?php
/**
 * @author James Baster  <james@jarofgreen.co.uk>
 * @copyright City of Edinburgh Council & James Baster
 * @license Open Source under the 3-clause BSD License
 * @url https://github.com/City-Outdoors/City-Outdoors-Web
 */
require 'includes/src/global.php';

$feature = Feature::loadByID($_GET['id']);
if (!$feature) die('Not found!');

$currentUser = getCurrentUser();

$tpl = getSmarty($currentUser);
$tpl->assign('inCollectionTab',true);
if ($feature->getTitleItem()) $tpl->assign('inCollectionId',$feature->getTitleItem()->getCollectionId());
$tpl->assign('feature',$feature);
$tpl->assign('featureCheckInQuestions',$feature->getCheckinQuestions());


if ($currentUser && isset($_POST['CSFRToken']) && $_POST['CSFRToken'] == $_SESSION['CSFRToken']) {
	$q = FeatureCheckinQuestion::findByIDInFeature($_POST['questionID'], $feature);
	if ($q) {
		if ($q->getQuestionType() == "FREETEXT") {
			if ($q->checkAndSaveAnswer($_POST['answer'], $currentUser, $_SERVER['HTTP_USER_AGENT'], $_SERVER['REMOTE_ADDR'])) {
				$tpl->assign('okMessage','That is correct!');
			} else {
				$tpl->assign('errorMessage','Sorry, that is wrong.');
			}
		} else if ($q->getQuestionType() == "MULTIPLECHOICE") {
			if ($q->checkAndSaveAnswer($_POST['answerID'], $currentUser, $_SERVER['HTTP_USER_AGENT'], $_SERVER['REMOTE_ADDR'])) {
				$tpl->assign('okMessage','That is correct!');
			} else {
				$tpl->assign('errorMessage','Sorry, that is wrong.');
			}				
		}
	}
	
}

$tpl->display('featureCheckin.htm');


