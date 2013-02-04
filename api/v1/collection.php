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

if (isset($data['id'])) $collection = Collection::loadByID($data['id']);
if (isset($data['slug'])) $collection = Collection::loadBySlug($data['slug']);
if (!$collection) die();

$itemSearch = new ItemSearch();
$itemSearch->inCollection($collection);
$itemSearch->includeDeleted(true);

?>
<data>
	<collection id="<?php echo $collection->getId() ?>" slug="<?php echo htmlentities($collection->getSlug(),ENT_QUOTES,'UTF-8') ?>">
		<title><?php echo htmlentities($collection->getTitle(),ENT_NOQUOTES,'UTF-8') ?></title>
		<?php if ($showLinks) { ?><link rel="self" href="http://<?php echo $CONFIG->HTTP_HOST ?>/api/v1/collection.php?slug=<?php echo $collection->getSlug() ?>"/><?php } ?>
		<icon height="<?php print $collection->getIconHeight() ?>" width="<?php print $collection->getIconWidth() ?>" offset_x="<?php print $collection->getIconOffsetX() ?>" offset_y="<?php print $collection->getIconOffsetY() ?>"><?php print $collection->getIconURL() ?></icon>
		<fields>
			<?php foreach($collection->getFields() as $field) { ?>
				<field id="<?php echo $field->getFieldID() ?>">
					<title><?php echo htmlentities($field->getTitle(),ENT_NOQUOTES,'UTF-8') ?></title>
				</field>
			<?php } ?>
		</fields>
		<items>
			<?php while($item = $itemSearch->nextResult()) { ?>
				<?php if ($item->getIsDeleted()) { ?>
					<item id="<?php echo $item->getId() ?>" slug="<?php echo htmlentities($item->getSlug(),ENT_QUOTES,'UTF-8') ?>" deleted="yes"></item>
				<?php } else  { ?>
					<item id="<?php echo $item->getId() ?>" slug="<?php echo htmlentities($item->getSlug(),ENT_QUOTES,'UTF-8') ?>">
						<title><?php echo htmlentities($item->getTitle(),ENT_NOQUOTES,'UTF-8') ?></title>
						<?php if ($showLinks) { ?><link rel="self" href="http://<?php echo $CONFIG->HTTP_HOST ?>/api/v1/collectionItem.php?slug=<?php echo $collection->getSlug() ?>&amp;islug=<?php echo $item->getSlug() ?>"/><?php } ?>
						<feature id="<?php echo $item->getFeatureId() ?>" lat="<?php echo $item->getLat() ?>" lng="<?php echo $item->getLng() ?>">
							<link rel="self" href="http://<?php echo $CONFIG->HTTP_HOST ?>/api/v1/feature.php?id=<?php echo $item->getFeatureId() ?>"/>
						</feature>
					</item>
				<?php } ?>
			<?php } ?>
		</items>
	</collection>
</data>
