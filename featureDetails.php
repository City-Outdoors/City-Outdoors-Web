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
$itemSearch->setIncludeOfficialCollectionsOnly(true);
$items = $itemSearch->getAllResults();
$tpl->assign('items',$items);

$itemSearch = new ItemSearch();
$itemSearch->onFeature($feature);
$itemSearch->setIncludeUnofficialCollectionsOnly(true);
$unofficialItemsData = array();
while($item = $itemSearch->nextResult()) {
	$collection = $item->getCollection();
	$unofficialItemsData[] = array(
		'item'=>$item,
		'collection'=>$collection,
		'organisation'=>$collection->getOrganisation(),
	);
}
$tpl->assign('unofficialItems',$unofficialItemsData);

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
	$q = BaseFeatureCheckinQuestion::findByIDInFeature($_POST['questionID'], $feature);
	if ($q) {
		if ($q->getQuestionType() == "FREETEXT") {
			if ($q->checkAndSaveAnswer($_POST['answer'], $currentUser, $_SERVER['HTTP_USER_AGENT'], $_SERVER['REMOTE_ADDR'])) {
				$tpl->assign('okMessageBlock','feature_checkin_question_answered_correctly');
			} else {
				$tpl->assign('errorMessageBlock','feature_checkin_question_answered_wrongly');
			}
		} else if ($q->getQuestionType() == "MULTIPLECHOICE") {
			if ($q->checkAndSaveAnswer($_POST['answerID'], $currentUser, $_SERVER['HTTP_USER_AGENT'], $_SERVER['REMOTE_ADDR'])) {
				$tpl->assign('okMessageBlock','feature_checkin_question_answered_correctly');
			} else {
				$tpl->assign('errorMessageBlock','feature_checkin_question_answered_wrongly');
			}				
		} else if ($q->getQuestionType() == "HIGHERORLOWER") {
			$a = $q->checkAndSaveAnswer($_POST['answer'], $currentUser, $_SERVER['HTTP_USER_AGENT'], $_SERVER['REMOTE_ADDR']);
			if (is_null($a)) {
				$tpl->assign('errorMessageBlock','feature_checkin_question_higher_or_lower_not_a_number');
			} else if ($a == 0) {
				$tpl->assign('okMessageBlock','feature_checkin_question_answered_correctly');
			} else if ($a == 1) {
				$tpl->assign('errorMessageBlock','feature_checkin_question_higher_or_lower_wrong_to_high');
			} else if ($a == -1) {
				$tpl->assign('errorMessageBlock','feature_checkin_question_higher_or_lower_wrong_to_low');
			}				
		}
	}
	
}

$eventSearch = new EventSearch();
$eventSearch->setAfterNow();
$eventSearch->onFeature($feature);
$eventSearch->setPaging(1, $CONFIG->FEATURE_DETAILS_SHOW_FUTURE_EVENTS);
$tpl->assign('eventSearch',$eventSearch);

$tpl->display('featureDetails.htm');
