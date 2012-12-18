<?php
/**
 * @author James Baster  <james@jarofgreen.co.uk>
 * @copyright City of Edinburgh Council & James Baster
 * @license Open Source under the 3-clause BSD License
 * @url https://github.com/City-Outdoors/City-Outdoors-Web
 */
include dirname(__FILE__).'/../src/global.php';
include dirname(__FILE__).'/importFunctions.php';


$config = json_decode(file_get_contents($argv[1]));
if (!is_object($config)) die("Config failed to load\n");

$import = new ImportJADU($config->source->website, $config->source->apiKEY);

$collection = Collection::loadByTitle($config->destination->collectionTitle);
if (!$collection) die("No Collection found\n");
$user = User::loadByEmail($config->destination->userEmail);
if (!$user) die("No User found\n");
$parentCollection = null;
if (isset($config->destination->parentCollectionTitle)) {
	$parentCollection = Collection::loadByTitle($config->destination->parentCollectionTitle);
	if (!$parentCollection) die("No Collection found\n");
}

# Load config from 
$mapToFieldID = array();
foreach($config->data->mapToFieldID as $k=>$v) $mapToFieldID[$k] = $v;

$mapPageURLToFieldID = array();
foreach($config->data->mapPageURLToFieldID  as $k=>$v) $mapPageURLToFieldID[$k] = $v;

$imageFields = array();
foreach($config->data->imageFields as $v) $imageFields[] = $v;

# call actual import
$import->importCollection(
			$config->source->directoryID, 
			$collection, 
			$user, 
			$config->data->locationField,
			$mapToFieldID, 
			$mapPageURLToFieldID, 
			$imageFields, 
			$config->imageDataFile,
			$parentCollection,
			isset($config->data->fieldMapsToParent) ? $config->data->fieldMapsToParent : null
		);


print "Done\n";




