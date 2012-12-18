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


$featureSearch = new FeatureSearch();

?>
<data>
	<features>
		<?php while($feature = $featureSearch->nextResult()) { ?>
			<feature id="<?php echo $feature->getId() ?>" lat="<?php echo $feature->getPointLat() ?>" lng="<?php echo $feature->getPointLng() ?>">
				<?php if ($showLinks) { ?><link rel="self" href="http://<?php echo $CONFIG->HTTP_HOST ?>/api/v1/feature.php?id=<?php echo $feature->getId() ?>"/><?php } ?>
				<items><?php foreach($feature->getCollectionIDS() as $collectionID) { ?><item collectionID="<?php echo $collectionID ?>"/><?php } ?></items>
			</feature>
		<?php } ?>
	</features>
</data>