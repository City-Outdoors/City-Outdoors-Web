<?php
/**
 * @author James Baster  <james@jarofgreen.co.uk>
 * @copyright City of Edinburgh Council & James Baster
 * @license Open Source under the 3-clause BSD License
 * @url https://github.com/City-Outdoors/City-Outdoors-Web
 */
require '../../includes/src/global.php';
require '../../includes/src/APIV1Funcs.php';
startXMLDoc();

$user = loadAPIUser();
$feature =  null;
$data = array_merge($_POST,$_GET);

if (isset($data['featureID']) && intval($data['featureID'])) {
	$feature = Feature::loadByID($data['featureID']);
} else if (isset($data['lat']) && isset($data['lng']) && $data['lat'] != "null" && $data['lng'] != "null" ) {
	$feature = Feature::findOrCreateAtPosition($data['lat'], $data['lng']);
}

if (!$feature) die("<data><error>No Feature</error></data>");

$comment = isset($data['comment']) ? $data['comment'] : '';
$name = isset($data['name']) ? $data['name'] : '';

logInfo("New Feature Content");

if (isset($_FILES['photo']['error']) && in_array($_FILES['photo']['error'], array(UPLOAD_ERR_INI_SIZE,UPLOAD_ERR_FORM_SIZE))) {

	?><data><error code="NEW_FEATURE_CONTENT_PHOTO_TO_BIG">Sorry, The file you uploaded was to big! Please reduce it or comment without it.</error></data><?

} else if (isset($_FILES['photo']['error']) && in_array($_FILES['photo']['error'], array(UPLOAD_ERR_PARTIAL ,UPLOAD_ERR_NO_TMP_DIR, UPLOAD_ERR_CANT_WRITE, UPLOAD_ERR_EXTENSION))) {

	?><data><error code="NEW_FEATURE_CONTENT_PHOTO_ERROR">Sorry, there was a problem uploading this file. Please try again or contact us for help.</error></data><?

} else {

	if ($user) {
		$featureContent = $feature->newContent($comment, $user, $name, null, false, $_SERVER['HTTP_USER_AGENT'], $_SERVER['REMOTE_ADDR']);
	} else {
		$featureContent = $feature->newAnonymousContent($comment, $name, null, false, $_SERVER['HTTP_USER_AGENT'], $_SERVER['REMOTE_ADDR']);
	}
	
	if (isset($_FILES['photo']['error']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
		logInfo(" ... which has image");
		$featureContent->newImage($_FILES['photo']['name'],$_FILES['photo']['tmp_name']);
	}

	?><data><result success="yes">OK</result></data><?

}

