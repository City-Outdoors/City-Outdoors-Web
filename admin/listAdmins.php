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
$userSearch->adminsOnly();


$tpl = getSmarty($currentUser);


if (isset($_POST) && isset($_POST['email']) && $_POST['CSFRToken'] == $_SESSION['CSFRToken']) {
	$user = User::loadByEmail($_POST['email']);
	if ($user) {
		if ($_POST['sysadmin'] == 1) {
			$user->makeSystemAdmin();
			$tpl->assign('okMessage','Sys Admin Added');
		} else {
			$user->makeAdmin();
			$tpl->assign('okMessage','Admin Added');
		}
	} else {
		$tpl->assign('errorMessage','Cant find Email');
	}
}

if (isset($_POST) && isset($_POST['id']) && $_POST['CSFRToken'] == $_SESSION['CSFRToken']) {
	$user = User::loadByID($_POST['id']);
	if ($user) {
		if ($_POST['action'] == 'makeSysAdmin') {
			$user->makeSystemAdmin();
			$tpl->assign('okMessage','Sys Admin Added');
		} if ($_POST['action'] == 'removeSysAdmin') {
			$user->removeSystemAdmin();
			$tpl->assign('okMessage','Sys Admin Added');
		} if ($_POST['action'] == 'removeAdmin') {
			$user->removeAdmin();
			$tpl->assign('okMessage','Admin Added');
		}
	}
}

$tpl->assign('userSearch',$userSearch);
$tpl->display('admin/listAdmins.htm');


