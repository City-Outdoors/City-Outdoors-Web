<?php
/**
 * @author James Baster  <james@jarofgreen.co.uk>
 * @copyright City of Edinburgh Council & James Baster
 * @license Open Source under the 3-clause BSD License
 * @url https://github.com/City-Outdoors/City-Outdoors-Web
 */

require '../includes/src/global.php';

if (!session_id()) session_start();

if ($_SESSION['oauth_token'] !== $_REQUEST['oauth_token']) {
  die("Wrong Session. Sorry, a fault has occured.");
}

$connection = new TwitterOAuth($CONFIG->TWITTER_APP_KEY, $CONFIG->TWITTER_APP_SECRET, $_SESSION['oauth_token'],$_SESSION['oauth_token_secret']);
$token_credentials = $connection->getAccessToken($_REQUEST['oauth_verifier']);

unset($_SESSION['oauth_token']);
unset($_SESSION['oauth_token_secret']);

$connection = new TwitterOAuth($CONFIG->TWITTER_APP_KEY, $CONFIG->TWITTER_APP_SECRET, $token_credentials['oauth_token'],$token_credentials['oauth_token_secret']);
$content = $connection->get('account/verify_credentials');

//TODO check for fail

$user = User::loadByTwitterID($content->id, $content->screen_name);
if (!$user) {
	$user = User::createByTwitter($content->id,  $content->name, $content->screen_name, $token_credentials['oauth_token'],$token_credentials['oauth_token_secret']);
}


$token = $user->getNewSessionID();

?>
<html>
	<head>
	</head>
	<body>
		Logged In!
		<script>CityOutdoors.authDone(<?php print $user->getId() ?>, "<?php print $token ?>");</script>
	<body>
</html>
