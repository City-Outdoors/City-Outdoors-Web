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

$user = loadAPIUser();
if (!$user) die("<data><error>No User</error></data>");

$data = array_merge($_POST,$_GET);

$featureCheckinQuestion = FeatureCheckinQuestion::findByID($data['id']);
if (!$featureCheckinQuestion) die("<data><error>No Feature Checkin Question</error></data>");

if ($featureCheckinQuestion->getQuestionType() != "HIGHERORLOWER") {
	die("<data><error>Feature Checkin Question is wrong type</error></data>");
}

$result = $featureCheckinQuestion->checkAndSaveAnswer($data['answer'], $user, $_SERVER['HTTP_USER_AGENT'], $_SERVER['REMOTE_ADDR']);

if (is_null($result)) {
	?><data><error>Could not parse answer</error></data><?php
} else if ($result == 0) {
	?><data>
		<result success="1">OK
			<explanation>
				<valueHTML><?php echo xmlEscape($featureCheckinQuestion->getAnswerExplanation(),false) ?></valueHTML>
			</explanation>
		</result>
	</data><?php
} else if ($result == 1 || $result == -1) {
	?><data><result success="0" trueAnswer="<?php echo $result ?>">FAIL</result></data><?php
}


