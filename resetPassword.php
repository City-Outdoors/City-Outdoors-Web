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

//----------------------------------- Load user, check code
$user = User::loadByID($_GET['id']);
if (!$user) die("No User");

if (!$user->checkForgottenPasswordCode($_GET['c'])) die('code wrong');

//--------------------------------------- Reset password?


if (isset($_POST['NewPassword1']) && $_POST['NewPassword1']) {
	try {
		$user->setNewPassword($_POST['NewPassword1'],$_POST['NewPassword2']);
		logInUser($user);
		header("Location: /");
	} catch (Exception $e) {
		$tpl->assign('errorMessage', $e->getMessage());
	}
}

//--------------------------------------- Display Form
$tpl->assign('user', $user);
$tpl->assign('code', $_GET['c']);
$tpl->display('resetPassword.htm');

