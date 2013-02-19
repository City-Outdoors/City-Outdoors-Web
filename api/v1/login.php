<?php
/**
 * @author James Baster  <james@jarofgreen.co.uk>
 * @copyright City of Edinburgh Council & James Baster
 * @license Open Source under the 3-clause BSD License
 * @url https://github.com/City-Outdoors/City-Outdoors-Web
 */
require '../../includes/src/global.php';
require '../../includes/src/APIV1Funcs.php';
startXMLDoc();

$user = $loginToken = null;
$data = array_merge($_POST,$_GET);

if (isset($data['email']) && isset($data['password'])) {
	$user = User::loadByEmail($data['email']);
	if ($user && $user->checkPassword($data['password'])) {
		$loginToken = $user->getNewSessionID();
	}
} 


if ($user && $loginToken) { ?>
	<data>
		<user id="<?php print $user->getId() ?>" token="<?php print $loginToken ?>" email="<?php print xmlEscape($user->getEmail(),true) ?>"  name="<?php print xmlEscape($user->getName(),true) ?>" score="<?php print intval($user->getCachedScore()) ?>">
		</user>
	</data>
<?php } else { ?>
	<data>
		<error>Log In Failed</error>
	</data>
<?php } ?>

