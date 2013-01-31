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

$featureContentSearch = new FeatureContentSearch();
$featureContentSearch->forFeature($feature);
$featureContentSearch->approvedOnly();

$itemSearch = new ItemSearch();
$itemSearch->onFeature($feature);

$childItemSearch = new ItemSearch();
// $childItemSearch->hasParentItem($item); is called below in the <item> loop.

$fieldsInContentArea = isset($data['fieldInContentArea']) && $data['fieldInContentArea'] ? $data['fieldInContentArea'] : null;

?>
<data>
	<feature id="<?php echo $feature->getId() ?>" lat="<?php echo $feature->getPointLat() ?>" lng="<?php echo $feature->getPointLng() ?>" shareURL="http://<?php echo $CONFIG->HTTP_HOST ?>/featureDetails.php?id=<?php echo $feature->getId() ?>" title="<?php echo htmlentities($feature->getTitle(),ENT_QUOTES,'UTF-8') ?>">
		<?php if ($showLinks) { ?><link rel="self" href="http://<?php echo $CONFIG->HTTP_HOST ?>/api/v1/feature.php?id=<?php echo $feature->getId() ?>"/><?php } ?>
		<contents>
			<?php while ($featureContent = $featureContentSearch->nextResult()) { ?>
				<content id="<?php echo $featureContent->getId() ?>" hasPicture="<?php echo $featureContent->hasPicture() ? "yes" : "no" ?>" promoted="<?php echo $featureContent->isPromoted() ? "yes" : "no" ?>">
					<body><?php echo htmlentities($featureContent->getBody(),ENT_NOQUOTES,'UTF-8') ?></body>
					<displayName><?php echo htmlentities($featureContent->getDisplayName(),ENT_NOQUOTES,'UTF-8') ?></displayName>
					<?php if ($featureContent->hasPicture()) { ?>
						<picture fullURL="http://<?php echo $CONFIG->HTTP_HOST ?><?php echo $featureContent->getFullPictureURL() ?>" normalURL="http://<?php echo $CONFIG->HTTP_HOST ?><?php echo $featureContent->getNormalPictureURL() ?>" thumbURL="http://<?php echo $CONFIG->HTTP_HOST ?><?php echo $featureContent->getThumbPictureURL() ?>" ></picture>
					<?php } ?>
				</content>		
			<?php } ?>
		</contents>
		<items>
			<?php while ($item = $itemSearch->nextResult()) { $collection = $item->getCollection(); $childItemSearch->hasParentItem($item); ?>
				<item id="<?php echo $item->getId() ?>" collectionID="<?php echo $item->getCollectionID() ?>" slug="<?php echo htmlentities($item->getSlug(),ENT_QUOTES,'UTF-8') ?>">
					<?php if ($showLinks) { ?>
					<link rel="self" href="http://<?php echo $CONFIG->HTTP_HOST ?>/api/v1/collectionItem.php?slug=<?php echo $collection->getSlug() ?>&amp;islug=<?php echo $item->getSlug() ?>"/>
					<link rel="collection" href="http://<?php echo $CONFIG->HTTP_HOST ?>/api/v1/collection.php?slug=<?php echo $collection->getSlug() ?>"/>
					<?php } ?>
					<fields>
						<?php foreach($item->getFields() as $field) { ?>
							<?php if (is_null($fieldsInContentArea) || $field->isInContentArea($fieldsInContentArea) ) { ?>
								<field id="<?php echo $field->getFieldID() ?>" title="<?php echo htmlentities($field->getTitle(),ENT_QUOTES,'UTF-8') ?>" hasValue="<?php echo $field->hasValue()?'yes':'no' ?>" type="<?php echo $field->getType() ?>">
									<valueHTML><?php echo htmlentities($field->getValueAsHumanReadableHTML(),ENT_NOQUOTES,'UTF-8') ?></valueHTML>
									<valueText><?php echo htmlentities($field->getValueAsHumanReadableText(),ENT_NOQUOTES,'UTF-8') ?></valueText>
								</field>
							<?php } ?>
						<?php } ?>
					</fields>
				</item>	
			<?php } ?>
		</items>
		<childItems>
			<?php while ($item = $childItemSearch->nextResult()) { ?>
				<item id="<?php echo $item->getId() ?>"></item>
			<?php } ?>
		</childItems>
		<checkinQuestions>
			<?php foreach($feature->getCheckinQuestions() as $question) { ?>
				<checkinQuestion id="<?php echo $question->getId() ?>" type="<?php echo htmlentities($question->getQuestionType(),ENT_QUOTES,'UTF-8') ?>" question="<?php echo htmlentities($question->getQuestion(),ENT_QUOTES,'UTF-8') ?>" <?php if ($user) { ?>hasAnswered="<?php echo ($question->hasAnswered($user)) ? 1 : 0 ?>"<?php } ?>>
					<?php if ($question->getQuestionType() == 'MULTIPLECHOICE') { ?>
						<possibleAnswers>
							<?php foreach($question->getPossibleAnswers() as $possibleAnswer) { ?>
								<possibleAnswer id="<?php echo $possibleAnswer->getId($possibleAnswer) ?>"><?php echo htmlentities($possibleAnswer->getAnswer(),ENT_NOQUOTES,'UTF-8') ?></possibleAnswer>
							<?php } ?>
						</possibleAnswers>
					<?php } ?>
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
