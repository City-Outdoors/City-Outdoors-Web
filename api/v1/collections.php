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

$collectionSearch =  new CollectionSearch();

?>
<data>
	<collections>
		<?php while($collection = $collectionSearch->nextResult()) { ?>
			<collection id="<?php echo $collection->getId() ?>" slug="<?php echo htmlentities($collection->getSlug(),ENT_QUOTES,'UTF-8') ?>">
				<title><?php echo htmlentities($collection->getTitle(),ENT_NOQUOTES,'UTF-8') ?></title>
				<icon height="<?php print $collection->getIconHeight() ?>" width="<?php print $collection->getIconWidth() ?>" offset_x="<?php print $collection->getIconOffsetX() ?>" offset_y="<?php print $collection->getIconOffsetY() ?>" url="<?php print $collection->getIconURL() ?>"/>
				<questionIcon height="<?php print $collection->getQuestionIconHeight() ?>" width="<?php print $collection->getQuestionIconWidth() ?>" offset_x="<?php print $collection->getQuestionIconOffsetX() ?>" offset_y="<?php print $collection->getQuestionIconOffsetY() ?>" url="<?php print $collection->getQuestionIconURL() ?>"/>
				<thumbnail  url="<?php print $collection->getThumbnailURL() ?>"/>
				<?php if ($showLinks) { ?><link rel="self" href="http://<?php echo $CONFIG->HTTP_HOST ?>/api/v1/collection.php?slug=<?php echo $collection->getSlug() ?>"/><?php } ?>
				<description><?php echo htmlentities($collection->getDescription(),ENT_NOQUOTES,'UTF-8') ?></description>
			</collection>
		<?php } ?>
	</collections>
</data>



