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
if (!$user) die("<data><error>No User</error></data>");

$feature =  null;
$data = array_merge($_POST,$_GET);
//logDebug("NewFeatureFavouriteCalled ".  var_export($data, true));

if (isset($data['featureID'])) $feature = Feature::loadByID($data['featureID']);
if (!$feature) die("<data><error>No Feature</error></data>");

$favouriteAt = null;
if (isset($data['favouriteAt']) && intval($data['favouriteAt']))  $favouriteAt = intval($data['favouriteAt']);

$feature->favourite($user,$favouriteAt, $_SERVER['HTTP_USER_AGENT'], $_SERVER['REMOTE_ADDR']);


?><data><result>OK</result></data>

