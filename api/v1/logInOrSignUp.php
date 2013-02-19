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

$email = isset($data['email']) ? $data['email'] : '';
$password = isset($data['password']) ? $data['password'] : '';

if ($email && $password) {	
	$user = User::loadByEmail($email);
	if ($user) {
		if ($user->checkPassword($data['password'])) {
			$loginToken = $user->getNewSessionID();
			?><data>
				<user id="<?php print $user->getId() ?>" token="<?php print $loginToken ?>" email="<?php print xmlEscape($user->getEmail(),true) ?>"  name="<?php print xmlEscape($user->getName(),true) ?>" score="<?php print intval($user->getCachedScore()) ?>" state="existing">
				</user>
			</data><?php 
		} else { 
			?><data>
				<error code="LOG_IN_OR_SIGN_UP_USER_EXISTS_PASSWORD_WRONG">Password wrong</error>
			</data><?php 
		} 
	} else {
		try {
			$user = User::createByEmail($email,$password,$password);
			$loginToken = $user->getNewSessionID();
			?><data>
				<user id="<?php print $user->getId() ?>" token="<?php print $loginToken ?>" email="<?php print xmlEscape($user->getEmail(),true) ?>"  name="<?php print xmlEscape($user->getName(),true) ?>" score="<?php print intval($user->getCachedScore()) ?>"  state="new">
				</user>
			</data><?php 
		} catch (UserExceptionEmailNotValid $e) { 
			?><data>
				<error code="LOG_IN_OR_SIGN_UP_USER_EXISTS_EMAIL_INVALID">Email not valid</error>
				</data><?php
		} catch (UserExceptionPasswordsToShort $e) { 
			?><data>
				<error code="LOG_IN_OR_SIGN_UP_USER_EXISTS_PASSWORD_TO_SHORT">Password to short</error>
			</data><?php
		} catch (UserExceptionEmailAlreadyKnown $e) { 
			?><data>
				<error code="LOG_IN_OR_SIGN_UP_USER_EXISTS_EMAIL_ALREADY_EXISTS">Email address already known</error>
			</data><?php
		} catch (UserException $e) { 
			?><data>
				<error><?php print $e->getMessage() ?></error>
			</data><?php
		} catch (Exception $e) { 
			?><data>
				<error><?php print $e->getMessage() ?></error>
			</data><?php
		}
	}
} else {
	?><data>
			<error>No Details Sent</error>
		</data><?php
}
			
			
			