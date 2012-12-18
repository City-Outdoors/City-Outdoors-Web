<?php
/**
 * @author James Baster  <james@jarofgreen.co.uk>
 * @copyright City of Edinburgh Council & James Baster
 * @license Open Source under the 3-clause BSD License
 * @url https://github.com/City-Outdoors/City-Outdoors-Web
 */
require 'includes/src/global.php';

$currentUser = getCurrentUser();

if ($currentUser) {
	header("Location: /");
	die();
}

$tpl = getSmarty();

if (isset($_POST) && isset($_POST['email'])) {
	$user = User::loadByEmail($_POST['email']);
	if ($user && $user->checkPassword($_POST['password'])) {
		if ($user->isEnabled()) {
			logInUser($user);
			header("Location: /");
			die();	
		} else {
			die("ACCOUNT BLOCKED"); // TODO better message
		}
	} else {
		$tpl->assign('errorMessage','Either your username or password is incorrect. Please check and try again, or click the link below the form if you have forgotten your password.');
	}
} 

$tpl->assign('hasUnsavedFavourites',(isset($_SESSION['favourite']) && is_array($_SESSION['favourite']) && count($_SESSION['favourite']) > 0));
$tpl->display('login.htm');
