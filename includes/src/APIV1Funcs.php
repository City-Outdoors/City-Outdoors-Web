<?php
/**
 * @author James Baster  <james@jarofgreen.co.uk>
 * @copyright City of Edinburgh Council & James Baster
 * @license Open Source under the 3-clause BSD License
 * @url https://github.com/City-Outdoors/City-Outdoors-Web
 */

$showLinks = isset($_GET['showLinks']) ? intval($_GET['showLinks']) : true;

function loadAPIUser() {
	$data = array_merge($_POST,$_GET);
	if (isset($data['userID']) && isset($data['userToken'])) {
		return User::loadByIDAndSession($data['userID'],$data['userToken']);
	}
}



