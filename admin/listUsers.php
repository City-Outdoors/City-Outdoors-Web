<?php
/**
 * @author James Baster  <james@jarofgreen.co.uk>
 * @copyright City of Edinburgh Council & James Baster
 * @license Open Source under the 3-clause BSD License
 * @url https://github.com/City-Outdoors/City-Outdoors-Web
 */

require '../includes/src/global.php';


$currentUser = mustBeLoggedIn();
if (!$currentUser->isSystemAdministrator()) die('No Access');

$userSearch = new UserSearch();

$tpl = getSmarty($currentUser);



if (isset($_POST) && isset($_POST['userID']) && $_POST['CSFRToken'] == $_SESSION['CSFRToken']) {

	$user = User::loadByID($_POST['userID']);
	if ($user) {
		if ($_POST['action'] == 'enable') {
			$user->enable();
			$tpl->assign('okMessage','Enabled');
		} else {
			$user->disable();
			$tpl->assign('okMessage','Disabled');
		}
	} else {
		$tpl->assign('errorMessage','Cant find Email');
	}
	
}




$tpl->assign('userSearch',$userSearch);
$tpl->display('admin/listUsers.htm');


