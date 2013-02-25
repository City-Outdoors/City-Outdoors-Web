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
	if (!isset($_POST['tandc']) || $_POST['tandc'] != 'agree') {
		$tpl->assign('errorMessage','Please agree to the terms and conditions');
	} else {
		try {
			$user = User::createByEmail($_POST['email'],$_POST['password1'],$_POST['password2'],(isset($_POST['name'])?$_POST['name']:null));
			logInUser($user);
			header("Location: /");
			die();	
		} catch (UserException $e) {
			$tpl->assign('errorMessage',$e->getMessage());
		}
	} 
} 

$tpl->assign('hasUnsavedFavourites',(isset($_SESSION['favourite']) && is_array($_SESSION['favourite']) && count($_SESSION['favourite']) > 0));
$tpl->display('signup.htm');
