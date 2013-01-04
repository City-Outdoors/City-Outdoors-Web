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

if (isset($_POST) && isset($_POST['lat']) && isset($_POST['lng']) && $_POST['CSFRToken'] == $_SESSION['CSFRToken']) {
	$feature = Feature::findOrCreateAtPosition($_POST['lat'], $_POST['lng']);
	header("Location: /admin/feature.php?id=".$feature->getId());
	exit();
}

$s = new FeatureSearch();
$s->allFeatures();

$tpl = getSmarty($currentUser);
$tpl->assign('featureSearch',$s);
$tpl->display('admin/listFeaturesAsMap.htm');

