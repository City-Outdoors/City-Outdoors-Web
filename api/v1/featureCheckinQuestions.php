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

$data = array_merge($_POST,$_GET);

$feature = Feature::loadByID($data['id']);
if (!$feature) die();

$user = loadAPIUser();

?>
<data>
	<feature id="<?php echo $feature->getId() ?>" lat="<?php echo $feature->getPointLat() ?>" lng="<?php echo $feature->getPointLng() ?>">
		<?php if ($showLinks) { ?><link rel="self" href="http://<?php echo $CONFIG->HTTP_HOST ?>/api/v1/feature.php?id=<?php echo $feature->getId() ?>"/><?php } ?>
		<checkinQuestions>
			<?php foreach($feature->getCheckinQuestions() as $question) { ?>
				<checkinQuestion id="<?php echo $question->getId() ?>" type="<?php echo htmlentities($question->getQuestionType()) ?>" question="<?php echo htmlentities($question->getQuestion()) ?>" <?php if ($user) { ?>hasAnswered="<?php echo ($question->hasAnswered($user)) ? 1 : 0 ?>"<?php } ?>>
					<?php if ($user && $question->hasAnswered($user)) { ?>
						<explanation>
							<valueHTML><?php echo htmlentities($question->getAnswerExplanation()) ?></valueHTML>
						</explanation>
					<?php } ?>
				</checkinQuestion>
			<?php } ?>
		</checkinQuestions>
	</feature>
</data>
