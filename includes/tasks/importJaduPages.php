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

$user = User::loadByEmail($config->destination->userEmail);
if (!$user) die("No User found\n");


foreach($config->data as $data) {
	
	
	
	$block = CMSContent::loadBlockBySlug($data->blockName);
	if (!$block) $block = CMSContent::createBlock ($data->blockName, $user);
	
	$import->importDocumentFromIDToCMSContent($data->documentID, $data->documentPageID, $block, $user);
	
}

print "Done\n";







