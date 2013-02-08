<?php
/**
 * @author James Baster  <james@jarofgreen.co.uk>
 * @copyright City of Edinburgh Council & James Baster
 * @license Open Source under the 3-clause BSD License
 * @url https://github.com/City-Outdoors/City-Outdoors-Web
 */

require '../includes/src/global.php';

if ($CONFIG->TWITTER_APP_KEY && $CONFIG->TWITTER_APP_SECRET) {

	// First we redirect to the correct domain and HTTPS or not. 
	// This ensures both this page call and the callback call happen on the same domain, and thus the same session will be accessed.
	// (Also forcing onte HTTPS gives a bit more security)
	if ($_SERVER["SERVER_PORT"] != "80") {
		$domainUsed = $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"];
	} else {
		$domainUsed = $_SERVER["SERVER_NAME"];
	}
	if ($CONFIG->HTTPS_AVAILABLE) {
		if ($domainUsed != $CONFIG->HTTPS_HOST || $_SERVER["HTTPS"] != "on") {
			header("Location: https://".$CONFIG->HTTPS_HOST."/android/loginTwitter.php");
			die();
		}
	} else {
		if ($domainUsed != $CONFIG->HTTP_HOST || $_SERVER["HTTPS"] == "on") {
			header("Location: http://".$CONFIG->HTTP_HOST."/android/loginTwitter.php");
			die();
		}
	}	

	// Now set up Twitter stuff ....
	$connection = new TwitterOAuth($CONFIG->TWITTER_APP_KEY, $CONFIG->TWITTER_APP_SECRET);
	if ($CONFIG->HTTPS_AVAILABLE) {
		$url = 'https://'.$CONFIG->HTTPS_HOST.'/android/loginTwitterCallback.php';
	} else {
		$url = 'http://'.$CONFIG->HTTP_HOST.'/android/loginTwitterCallback.php';
	}
	$temporary_credentials = $connection->getRequestToken($url);
	if (!session_id()) session_start();
	$_SESSION['oauth_token'] = $temporary_credentials['oauth_token'];
	$_SESSION['oauth_token_secret'] = $temporary_credentials['oauth_token_secret'];
	if ($connection->http_code == 200) {
		$redirect_url = $connection->getAuthorizeURL($temporary_credentials);
		header("Location: ".$redirect_url);
	} else {
		die("Could not make connection to Twitter ".$connection->http_code);
	}

} else {
	print "This server is not configured to allow Twitter.";
}