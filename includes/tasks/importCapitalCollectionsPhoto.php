<?php
/**
 * @author James Baster  <james@jarofgreen.co.uk>
 * @copyright City of Edinburgh Council & James Baster
 * @license Open Source under the 3-clause BSD License
 * @url https://github.com/City-Outdoors/City-Outdoors-Web
 */
include dirname(__FILE__).'/../src/global.php';
include dirname(__FILE__).'/importFunctions.php';

#### Config
$config = json_decode(file_get_contents($argv[1]));

$user = User::loadByEmail($config->destination->userEmail);
if (!$user) die("No User found\n");


function saveSmallerImage($originalFileName, $newFileName, $newSize) {
	$extensionBits = explode(".", $originalFileName);
	$extension = strtolower(array_pop( $extensionBits ));
	list($width, $height, $type, $attr)= getimagesize($originalFileName);
	$imgratio = floatval($height) / floatval($width);
	switch ($extension) {
		case "jpg": case "jpeg":
			$image = imagecreatefromjpeg($originalFileName);
			break;
		case "png":
			$image = imagecreatefrompng($originalFileName);
			break;
		case "gif":
			$image = imagecreatefromgif($originalFileName);
			break;						
		default:
			$image = imagecreatetruecolor($new_width, $new_height);
	}

	$scale = max(1,max($width/$newSize, $height/$newSize));
	list($new_width, $new_height) = array(intval($width/$scale), intval($height/$scale));
	$image_p = imagecreatetruecolor($new_width, $new_height);
	imagecopyresampled($image_p, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
	if (!imagejpeg($image_p, $newFileName)) {
		throw new Exception("Creating smaller image failed for some reason!");
	}

}	


### Data
$idsImported = array();
if ($config->dataFile && file_exists($config->dataFile)) {
	print "Loading Old Data File\n";
	$d = json_decode(file_get_contents($config->dataFile));
	if ($d) $idsImported = $d->idsImported;
} else {
	die('No Data File! Create By Hand!');
}

foreach(array_unique($config->source->ids) as $id) {

	if (in_array($id, $idsImported)) {
		print "Skipping ID ".$id." cos already imported\n";		
	} else {
	
		print "Getting ID ".$id."\n";

		############### Get Data
		$url = 'http://www.capitalcollections.org.uk/rest-capitalcollections/search?itemtype=&itemtypeid=1&criteria='.
			$id.'&pageindex=0&resultsperpage=10&details=full';

		$ch = curl_init();	
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_USERAGENT, 'Heres-a-Tree');
		$rawData = curl_exec($ch);
		curl_close($ch);

		$dom = new DOMDocument();
		$dom->loadXML($rawData);

		$title = $lat = $lng = $imageSRC = null;

		foreach($dom->getElementsByTagName('field') as $domElement) {
			if ($domElement->getAttribute('label') == "Title") $title = DOMinnerHTML($domElement);
			if ($domElement->getAttribute('name') == "geo_loc") list($lat,$lng) = explode(",",DOMinnerHTML($domElement));
		}
		foreach($dom->getElementsByTagName('image') as $domElement) {
			$src = $domElement->getAttribute('src');
			if (substr($src,-5) == '.ptif') $imageSRC = $src;
		}

		print " .. URL ".$imageSRC."\n";
		print " .. Title ".$title."\n";
		print " .. Lat ".$lat."\n";
		print " .. Lng ".$lng."\n";

		if ($lat && $lng) {

			################### Get Feature
			$feature = Feature::findOrCreateAtPosition($lat, $lng);
			print " .. Feature ID ".$feature->getId()."\n";

			################### Get Image
			$tmpFileNamePrefix = tempnam("/tmp","capitalCollectionsImport");
			$tmpFileNamePTIF = $tmpFileNamePrefix.".ptif";
			$tmpFileNameJPEG = $tmpFileNamePrefix.".jpg";
			$tmpFileNameJPEGActual = $tmpFileNamePrefix."-0.jpg";
			$tmpFileNameJPEGActualResized = $tmpFileNamePrefix."-resized.jpg";

			print " .. Downloading ".$imageSRC."\n";
			$ch = curl_init();	
			curl_setopt($ch, CURLOPT_URL, $imageSRC);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
			curl_setopt($ch, CURLOPT_USERAGENT, 'Heres-a-Tree');
			$rawData = curl_exec($ch);
			curl_close($ch);

			print " .. saving to ".$tmpFileNamePTIF."\n";
			file_put_contents($tmpFileNamePTIF, $rawData);

			################### Convert
			print " .. converting\n";
			exec("convert ".$tmpFileNamePTIF." ".$tmpFileNameJPEG);

			################### Resizo te safe max
			print " .. resizing\n";
			saveSmallerImage($tmpFileNameJPEGActual, $tmpFileNameJPEGActualResized, 1000);
			
			################### Finally import
			print " .. commenting\n";
			$featureContent = $feature->newContent($title." from Capital Collections", $user);
			$featureContent->newImage("image.jpg", $tmpFileNameJPEGActualResized, false);
			//unlink($tmpFileName);

			#################### save import data back. 
			// We do this after every one so if crash we don't lose data so far.
			$idsImported[] = $id;
			$out = array('idsImported'=>$idsImported);
			file_put_contents($config->dataFile , json_encode($out));
			
		} else {
			print " .. SKIPPING\n";
		}
	}
}
print " .. DONE\n";




