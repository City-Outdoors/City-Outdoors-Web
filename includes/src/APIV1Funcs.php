<?php
/**
 * @author James Baster  <james@jarofgreen.co.uk>
 * @copyright City of Edinburgh Council & James Baster
 * @license Open Source under the 3-clause BSD License
 * @url https://github.com/City-Outdoors/City-Outdoors-Web
 */

/** This can be passed to API and is used in most end points, so just checked here. **/
$showLinks = isset($_GET['showLinks']) ? intval($_GET['showLinks']) : true;
/** This can be passed to API and is used in most end points, so just checked here. **/
$showDeleted = isset($_GET['showDeleted']) ? intval($_GET['showDeleted']) : false;

function loadAPIUser() {
	$data = array_merge($_POST,$_GET);
	if (isset($data['userID']) && isset($data['userToken'])) {
		return User::loadByIDAndSession($data['userID'],$data['userToken']);
	}
}


function startXMLDoc() {
	header('Content-type: application/xml');
	print '<?xml version="1.0" encoding="UTF-8"?>';		
}

function xmlEscape($s,$inAttribute=true) {
	if ($inAttribute) {
		return str_replace(array('&','>','<','"'), array('&amp;','&gt;','&lt;','&quot;'), $s);
	} else {
		return str_replace(array('&','>','<'), array('&amp;','&gt;','&lt;'), $s);
	}
}

