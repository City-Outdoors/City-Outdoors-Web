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

		if ($currentUser) {
			$featureContent = $feature->newContent($_POST['comment_body'], $currentUser, null, $_POST['comment_email'], true, $_SERVER['HTTP_USER_AGENT'], $_SERVER['REMOTE_ADDR']);
		} else {
			$featureContent = $feature->newAnonymousContent($_POST['comment_body'], $_POST['comment_name'], $_POST['comment_email'], true, $_SERVER['HTTP_USER_AGENT'], $_SERVER['REMOTE_ADDR']);
		}

		if (isset($_FILES['picture']['error']) && $_FILES['picture']['error'] === UPLOAD_ERR_OK) {
			try {
				$featureContent->newImage($_FILES['picture']['name'],$_FILES['picture']['tmp_name']);
			} catch (Exception $e) {
				$tpl->assign('errorMessage','Your report was posted but there was a problem adding your image: '.$e->getMessage());
			}		
		}

		$featureContent->sendReport();

		$tpl->display('featureReports.submitted.htm');
		die();
	
	}
}

$tpl->assign('commentBody', isset($_POST['comment_body']) ? $_POST['comment_body'] : '');
$tpl->assign('commentName', isset($_POST['comment_name']) ? $_POST['comment_name'] : '');
$tpl->assign('commentEmail', isset($_POST['comment_email']) ? $_POST['comment_email'] : ($currentUser ? $currentUser->getEmail() : ''));
$tpl->display('featureReports.htm');
