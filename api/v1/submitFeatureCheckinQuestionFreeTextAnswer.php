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

$featureCheckinQuestion = BaseFeatureCheckinQuestion::findByID($data['id']);
if (!$featureCheckinQuestion) die("<data><error>No Feature Checkin Question</error></data>");

if ($featureCheckinQuestion->getQuestionType() != "FREETEXT") {
	die("<data><error>Feature Checkin Question is wrong type</error></data>");
}

$result = $featureCheckinQuestion->checkAndSaveAnswer($data['answer'], $user, $_SERVER['HTTP_USER_AGENT'], $_SERVER['REMOTE_ADDR']);

if ($result) {
	?><data>
		<result success="1">OK
			<explanation>
				<valueHTML><?php echo xmlEscape($featureCheckinQuestion->getAnswerExplanation(),false) ?></valueHTML>
			</explanation>
		</result>
	</data><?php
} else {
	?><data><result success="0">FAIL</result></data><?php
}


