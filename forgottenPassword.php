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

if (isset($_POST) && isset($_POST['email']) && filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
	$user = User::loadByEmail($_POST['email']);
	if ($user) {

		$tplEmail = getEmailSmarty();
		$tplEmail->assign('user',$user);
		$tplEmail->assign('code',$user->getForgottenPasswordCode());
		$body = $tplEmail->fetch('forgottenpassword.email.txt');
		//print ($body); die();
		mail($user->getEmail(), "You wanted to reset your password?", $body, "From: ".$CONFIG->EMAILS_FROM);
		
	} else {
		
		$tplEmail = getEmailSmarty();
		$tplEmail->assign('email',$_POST['email']);
		$body = $tplEmail->fetch('forgottenpassword.notfound.email.txt');
		//print ($body); die();
		mail($_POST['email'], "You wanted to reset your password?", $body, "From: ".$CONFIG->EMAILS_FROM);
		
	}
	$tpl->assign('okMessage','Please check your email for more instructions.');
} 

$tpl->display('forgottenPassword.htm');
