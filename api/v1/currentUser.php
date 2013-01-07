<?php
/**
 * @author James Baster  <james@jarofgreen.co.uk>
 * @copyright City of Edinburgh Council & James Baster
 * @license Open Source under the 3-clause BSD License
 * @url https://github.com/City-Outdoors/City-Outdoors-Web
 */
require '../../includes/src/global.php';
require '../../includes/src/APIV1Funcs.php';
header('Content-type: application/xml');

$user = loadAPIUser();


if ($user) { 
?>
	<data>
		<user id="<?php print $user->getId() ?>" email="<?php htmlspecialchars(print $user->getEmail()) ?>"  name="<?php htmlspecialchars(print $user->getName()) ?>" score="<?php htmlspecialchars(print $user->getCachedScore()) ?>">
		</user>
	</data>
<?php } else { ?>
	<data>
		<error>Log In Failed</error>
	</data>
<?php } ?>

