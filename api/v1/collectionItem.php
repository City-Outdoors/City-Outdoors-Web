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

if (isset($data['id'])) $collection = Collection::loadByID($data['id']);
if (isset($data['slug'])) $collection = Collection::loadBySlug($data['slug']);
if (!$collection) die();

if (isset($data['iid'])) $item = Item::loadByIdIncollection($data['iid'], $collection);
if (isset($data['islug'])) $item = Item::loadBySlugIncollection($data['islug'], $collection);
if (!$item) die();


$feature = $item->getFeature();

?>
<data>
	<item id="<?php echo $item->getId() ?>" slug="<?php echo htmlentities($item->getSlug(),ENT_QUOTES,'UTF-8') ?>">
		<link rel="self" href="http://<?php echo $CONFIG->HTTP_HOST ?>/api/v1/collectionItem.php?slug=<?php echo $collection->getSlug() ?>&amp;islug=<?php echo $item->getSlug() ?>"/>
		<fields>
			<?php foreach($item->getFields() as $field) { ?>
				<field id="<?php echo $field->getFieldID() ?>" title="<?php echo htmlentities($field->getTitle(),ENT_QUOTES,'UTF-8') ?>">
					<valueHTML><?php echo htmlentities($field->getValueAsHumanReadableHTML(),ENT_NOQUOTES,'UTF-8') ?></valueHTML>
					<valueText><?php echo htmlentities($field->getValueAsHumanReadableText(),ENT_NOQUOTES,'UTF-8') ?></valueText>
				</field>
			<?php } ?>
		</fields>
		<feature id="<?php echo $feature->getId() ?>" lat="<?php echo $feature->getPointLat() ?>" lng="<?php echo $feature->getPointLng() ?>">
			<?php if ($showLinks) { ?><link rel="self" href="http://<?php echo $CONFIG->HTTP_HOST ?>/api/v1/feature.php?id=<?php echo $feature->getId() ?>"/><?php } ?>
		</feature>
	</item>
</data>
