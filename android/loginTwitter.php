<?php
/**
 * @author James Baster  <james@jarofgreen.co.uk>
 * @copyright City of Edinburgh Council & James Baster
 * @license Open Source under the 3-clause BSD License
 * @url https://github.com/City-Outdoors/City-Outdoors-Web
 */

require '../includes/src/global.php';

$connection = new TwitterOAuth($CONFIG->TWITTER_APP_KEY, $CONFIG->TWITTER_APP_SECRET);
$temporary_credentials = $connection->getRequestToken('http://'.$CONFIG->HTTP_HOST.'/android/loginTwitterCallback.php');
if (!session_id()) session_start();
$_SESSION['oauth_token'] = $temporary_credentials['oauth_token'];
$_SESSION['oauth_token_secret'] = $temporary_credentials['oauth_token_secret'];
if ($connection->http_code == 200) {
	$redirect_url = $connection->getAuthorizeURL($temporary_credentials);
	header("Location: ".$redirect_url);
} else {
	die("Could not make connection to Twitter ".$connection->http_code);
}
