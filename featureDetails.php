<?php
/**
 * @author James Baster  <james@jarofgreen.co.uk>
 * @copyright City of Edinburgh Council & James Baster
 * @license Open Source under the 3-clause BSD License
 * @url https://github.com/City-Outdoors/City-Outdoors-Web
 */
require 'includes/src/global.php';


$currentUser = getCurrentUser();

$feature = Feature::loadByID($_GET['id']);
if (!$feature) die('Not found!');

$tpl = getSmarty($currentUser);
$tpl->assign('feature',$feature);
$tpl->assign('featureCheckInQuestions',$feature->getCheckinQuestions());
$tpl->assign('inCollectionTab',true);

$itemSearch = new ItemSearch();
$itemSearch->onFeature($feature);
$items = $itemSearch->getAllResults();
$tpl->assign('items',$items);

if ($items) {
	$tpl->assign('inCollectionId',$items[0]->getCollectionId());
}

$featureImageSearch = new FeatureContentSearch();
$featureImageSearch->forFeature($feature);
$featureImageSearch->hasImages();
$featureImageSearch->approvedOnly();
$featureImageSearch->promotedOnly();
$tpl->assign('featureImageSearch',$featureImageSearch);
$tpl->assign('featureImageSearchCount',$featureImageSearch->num());


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
		} else if ($q->getQuestionType() == "HIGHERORLOWER") {
			$a = $q->checkAndSaveAnswer($_POST['answer'], $currentUser, $_SERVER['HTTP_USER_AGENT'], $_SERVER['REMOTE_ADDR']);
			if (is_null($a)) {
				$tpl->assign('errorMessage','Sorry, that is wrong. Did you type in a number?');
			} else if ($a == 0) {
				$tpl->assign('okMessage','That is correct!');
			} else if ($a == 1) {
				$tpl->assign('errorMessage','Sorry, that is wrong. To high! Try a lower answer.');
			} else if ($a == -1) {
				$tpl->assign('errorMessage','Sorry, that is wrong. To low! Try a higher answer.');
			}				
		}
	}
	
}

$tpl->display('featureDetails.htm');
