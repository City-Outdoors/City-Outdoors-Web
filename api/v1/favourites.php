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

$featureSearch = new FeatureSearch();
$featureSearch->userFavourites($user);
	
?>
<data>
	<features>
		<?php while($feature = $featureSearch->nextResult()) { ?>
			<feature id="<?php echo $feature->getId() ?>" lat="<?php echo $feature->getPointLat() ?>" lng="<?php echo $feature->getPointLng() ?>" title="<?php echo xmlEscape($feature->getTitle(),true) ?>" answeredAllQuestions="<?php echo  $feature->getHasUserAnsweredAllQuestions() ? 'yes' : 'no' ?>">
				<?php if ($showLinks) { ?><link rel="self" href="http://<?php echo $CONFIG->HTTP_HOST ?>/api/v1/feature.php?id=<?php echo $feature->getId() ?>"/><?php } ?>
				<items><?php foreach($feature->getCollectionIDS() as $collectionID) { ?><item collectionID="<?php echo $collectionID ?>"/><?php } ?></items>
			</feature>
		<?php } ?>
	</features>
</data>
