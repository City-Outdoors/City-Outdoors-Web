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

$data = array_merge($_POST,$_GET);

$content = FeatureContent::loadByID($data['contentID']);
if (!$content) die("No Content");

if ($data['CSFRToken'] == $_SESSION['CSFRToken']) {
	$content->promote($currentUser);
}

// You may want to return to a place in the admin interface at some point in the 
// future but at the moment this is only called from the normal interfare.
//if (isset($data['return']) && $data['return'] == 'normalui') {
	header("Location: /featureContent.php?id=".$content->getFeatureId());
//}



