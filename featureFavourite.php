<?php
/**
 * @author James Baster  <james@jarofgreen.co.uk>
 * @copyright City of Edinburgh Council & James Baster
 * @license Open Source under the 3-clause BSD License
 * @url https://github.com/City-Outdoors/City-Outdoors-Web
 */
require 'includes/src/global.php';

$feature = Feature::loadByID($_GET['id']);
if (!$feature) die('Not found!');

$currentUser = getCurrentUser();

if ($currentUser) {

	$feature->favourite($currentUser, null, $_SERVER['HTTP_USER_AGENT'], $_SERVER['REMOTE_ADDR']);

	$_SESSION['okMessage'] = "Thanks, this has been marked as a favourite!";
	header("Location: /featureDetails.php?id=".$feature->getId());
} else {
	if (!session_id()) session_start();
	if (!isset($_SESSION['favourite']) || !is_array($_SESSION['favourite'])) $_SESSION['favourite'] = array();
	$_SESSION['favourite'][] = $feature->getId();
	header("Location: /login.php");

}

