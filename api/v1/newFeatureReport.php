<?php
/**
 * @author James Baster  <james@jarofgreen.co.uk>
 * @copyright City of Edinburgh Council & James Baster
 * @license Open Source under the 3-clause BSD License
 * @url https://github.com/City-Outdoors/City-Outdoors-Web
 */
require '../../includes/src/global.php';
require '../../includes/src/APIV1Funcs.php';

header('Content-type: application/xml');

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
$email = isset($data['email']) ? $data['email'] : '';

logInfo("New Feature Report");
if ($user) {
	$featureContent = $feature->newContent($data['comment'], $user, $name, $email, true, $_SERVER['HTTP_USER_AGENT'], $_SERVER['REMOTE_ADDR']);
} else {
	$featureContent = $feature->newAnonymousContent($data['comment'],  $name, $email, true, $_SERVER['HTTP_USER_AGENT'], $_SERVER['REMOTE_ADDR']);
}
if (isset($_FILES['photo']['error']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
	logInfo(" ... which has image");
	$featureContent->newImage($_FILES['photo']['name'],$_FILES['photo']['tmp_name']);
}


?><data><result>OK</result></data>

