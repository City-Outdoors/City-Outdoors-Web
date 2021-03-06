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

$featureCheckinQuestion = BaseFeatureCheckinQuestion::findByID($_GET['id']);
if (!$featureCheckinQuestion) die('not found');

if ($featureCheckinQuestion->getQuestionType() == 'FREETEXT') {
	header("Location: /admin/featureCheckinQuestionFreeText.php?id=".$featureCheckinQuestion->getId());
} else if ($featureCheckinQuestion->getQuestionType() == 'CONTENT') {
	header("Location: /admin/featureCheckinQuestionContent.php?id=".$featureCheckinQuestion->getId());
} else if ($featureCheckinQuestion->getQuestionType() == 'MULTIPLECHOICE') {
	header("Location: /admin/featureCheckinQuestionMultipleChoice.php?id=".$featureCheckinQuestion->getId());
} else if ($featureCheckinQuestion->getQuestionType() == 'HIGHERORLOWER') {
	header("Location: /admin/featureCheckinQuestionHigherOrLower.php?id=".$featureCheckinQuestion->getId());
}


