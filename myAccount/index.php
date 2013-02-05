<?php
/**
 * @author James Baster  <james@jarofgreen.co.uk>
 * @copyright City of Edinburgh Council & James Baster
 * @license Open Source under the 3-clause BSD License
 * @url https://github.com/City-Outdoors/City-Outdoors-Web
 */
require '../includes/src/global.php';


$currentUser = mustBeLoggedIn();

$tpl = getSmarty($currentUser);

if ($_POST && isset($_POST['action']) && $_POST['CSFRToken'] == $_SESSION['CSFRToken']) {
	if ($_POST['action'] == 'changeName' && isset($_POST['name']) && $_POST['name']) {
		$currentUser->updateName($_POST['name']);
		$tpl->assign('okMessage', 'Your screen name is now &lsquo;'. $_POST['name'] .'&rsquo;.');
	} else if ($_POST['action'] == 'changePassword') {
		if ($currentUser->checkPassword($_POST['oldPassword'])) { 
			try {
				$currentUser->setNewPassword($_POST['newPassword1'], $_POST['newPassword2']);
				$tpl->assign('okMessage','Your password has been changed.');
			} catch (Exception $e) {
				$tpl->assign('errorMessage',$e->getMessage());
			}
		} else {
			$tpl->assign('errorMessage','Sorry, you didn\'t type your old password correctly. Please try again.');
		}
	}
}


$tpl->display('myAccount/index.htm');

