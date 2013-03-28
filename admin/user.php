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

$user = User::loadByID($_GET['id']);
if (!$user) die('not found');

$tpl = getSmarty($currentUser);

if (isset($_POST['CSFRToken']) && $_POST['CSFRToken'] == $_SESSION['CSFRToken']) {

	if ($_POST['action'] == 'enable') {
		$user->enable();
		$tpl->assign('okMessage','Enabled');
	} else {
		$user->disable();
		$tpl->assign('okMessage','Disabled');
	}
	
}

$tpl->assign('user',$user);
$tpl->display('admin/user.htm');


