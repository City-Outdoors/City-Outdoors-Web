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


if ($_POST && isset($_POST['contentID']) && isset($_POST['action']) && $_POST['CSFRToken'] == $_SESSION['CSFRToken']) {
	$content = FeatureContent::loadByID($_POST['contentID']);
	if ($content && !$content->isApproved() && !$content->isRejected()) {
		if ($_POST['action'] == 'Approve') {
			$content->updateBody($_POST['body']);
			if (isset($_POST['name'])) $content->updateCreatedName($_POST['name']);
			$content->approve($currentUser);
			if (isset($_POST['promote']) && $_POST['promote'] == 'yes') $content->promote($currentUser);
			
			if (isset($_POST['question']) && is_array($_POST['question'])) {
				$feature = $content->getFeature();
				foreach($_POST['question'] as $id=>$score) {
					if (intval($score) > 0) {
						$question = BaseFeatureCheckinQuestion::findByIDInFeature($id, $feature);
						if ($question && get_class($question) == 'FeatureCheckinQuestionContent') {
							$question->awardPoints($content, $score);
						}
					}
				}
			}
			
		} else if ($_POST['action'] == 'Disapprove') {
			$content->disapprove($currentUser);
		}
	}
}


$s = new FeatureContentSearch();
$s->toModerateOnly();
// TODO 1 only!
$content = $s->nextResult();



$tpl = getSmarty($currentUser);

if ($content) {
	$tpl->assign('content',$content);
	
	$feature = $content->getFeature();
	$tpl->assign('feature',$feature);
	
	$questionSearch = new FeatureCheckinQuestionSearch();
	$questionSearch->withinFeature($feature);
	$questionSearch->ofType("CONTENT");
	$tpl->assign('questionSearch',$questionSearch);

	$tpl->display('admin/moderate.content.htm');
} else {
	$tpl->display('admin/moderate.nothing.htm');
}

