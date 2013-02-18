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

$data = array_merge($_POST,$_GET);

$feature = Feature::loadByID($data['id']);
if (!$feature) die();

$user = loadAPIUser();

?>
<data>
	<feature id="<?php echo $feature->getId() ?>" lat="<?php echo $feature->getPointLat() ?>" lng="<?php echo $feature->getPointLng() ?>">
		<?php if ($showLinks) { ?><link rel="self" href="http://<?php echo $CONFIG->HTTP_HOST ?>/api/v1/feature.php?id=<?php echo $feature->getId() ?>"/><?php } ?>
		<checkinQuestions>
			<?php foreach($feature->getCheckinQuestions(true) as $question) { ?>
				<?php if ($question->getIsDeleted()) { ?>
					<checkinQuestion id="<?php echo $question->getId() ?>" type="<?php echo xmlEscape($question->getQuestionType(),true) ?>" deleted="yes"></checkinQuestion>
				<?php } else { ?>
					<checkinQuestion id="<?php echo $question->getId() ?>" type="<?php echo xmlEscape($question->getQuestionType(),true) ?>" question="<?php echo xmlEscape($question->getQuestion(),true) ?>" active="<?php echo ($question->getIsActive()) ? 'yes' : 'no' ?>" <?php if ($user) { ?>hasAnswered="<?php echo ($question->hasAnswered($user)) ? 'yes' : 'no' ?>"<?php } ?>>
						<?php if ($question->getQuestionType() == 'MULTIPLECHOICE') { ?>
							<possibleAnswers>
								<?php foreach($question->getPossibleAnswers() as $possibleAnswer) { ?>
									<possibleAnswer id="<?php echo $possibleAnswer->getId($possibleAnswer) ?>"><?php echo xmlEscape($possibleAnswer->getAnswer(),false) ?></possibleAnswer>
								<?php } ?>
							</possibleAnswers>
						<?php } ?>
						<?php if ($user && $question->hasAnswered($user)) { ?>
							<explanation>
								<valueHTML><?php echo xmlEscape($question->getAnswerExplanation(),false) ?></valueHTML>
							</explanation>
						<?php } ?>
						<?php if (!$question->getIsActive()) { ?>
							<inactiveReason>
								<valueText><?php echo xmlEscape($question->getInactiveReason(),false) ?></valueText>
							</inactiveReason>
						<?php } ?>						
					</checkinQuestion>
				<?php } ?>
			<?php } ?>
		</checkinQuestions>
	</feature>
</data>
