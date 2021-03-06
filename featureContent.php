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
$tpl->assign('inCollectionTab',true);
if ($feature->getTitleItem()) $tpl->assign('inCollectionId',$feature->getTitleItem()->getCollectionId());

if ($_POST && isset($_POST['comment_body']) && 
		(($currentUser && $_POST['CSFRToken'] == $_SESSION['CSFRToken']) || (!$currentUser && isset($_POST['tandc']) && $_POST['tandc'] == 'agree'))) {
	
	
	if (isset($_FILES['picture']['error']) && in_array($_FILES['picture']['error'], array(UPLOAD_ERR_INI_SIZE,UPLOAD_ERR_FORM_SIZE))) {

		$tpl->assign('errorMessage','Sorry, The file you uploaded was to big! Please reduce it or comment without it.');
		
	} else if (isset($_FILES['picture']['error']) && in_array($_FILES['picture']['error'], array(UPLOAD_ERR_PARTIAL ,UPLOAD_ERR_NO_TMP_DIR, UPLOAD_ERR_CANT_WRITE, UPLOAD_ERR_EXTENSION))) {

		$tpl->assign('errorMessage','Sorry, there was a problem uploading this file. Please try again or contact us for help.');
		
	} else {
		
		if ($currentUser && $currentUser->isAdministrator()) {
			if ($_POST['post_as'] == 'anon') { // done this way around so if $_POST['post_as'] is undefined we post as user
				$featureContent = $feature->newAnonymousContent($_POST['comment_body'], $_POST['post_as_anon'] , null, false, $_SERVER['HTTP_USER_AGENT'], $_SERVER['REMOTE_ADDR']);
				$featureContent->approve($currentUser); // post anon, however it as approved straight away
			} else {
				$featureContent = $feature->newContent($_POST['comment_body'], $currentUser , null, null, false, $_SERVER['HTTP_USER_AGENT'], $_SERVER['REMOTE_ADDR']);
			}
		} else if ($currentUser) {
			$featureContent = $feature->newContent($_POST['comment_body'], $currentUser , null, null, false, $_SERVER['HTTP_USER_AGENT'], $_SERVER['REMOTE_ADDR']);
		} else {
			$featureContent = $feature->newAnonymousContent($_POST['comment_body'], $_POST['comment_name'] , null, false, $_SERVER['HTTP_USER_AGENT'], $_SERVER['REMOTE_ADDR']);
		}
		
		if (isset($_FILES['picture']['error']) && $_FILES['picture']['error'] === UPLOAD_ERR_OK) {
			try {
				$featureContent->newImage($_FILES['picture']['name'],$_FILES['picture']['tmp_name']);
			} catch (Exception $e) {
				$tpl->assign('errorMessage','Your comment was posted but there was a problem adding your image: '.$e->getMessage());
			}
		}

		$tpl->display('featureContent.submitted.htm');
		die();
	
	}
	
}

$tpl->assign('commentBody', isset($_POST['comment_body']) ? $_POST['comment_body'] : '');
$tpl->assign('commentName', isset($_POST['comment_name']) ? $_POST['comment_name'] : '');

$featureImageSearch = new FeatureContentSearch();
$featureImageSearch->forFeature($feature);
$featureImageSearch->approvedOnly();
$tpl->assign('featureContentSearch',$featureImageSearch);

$tpl->display('featureContent.htm');
