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

?>
<data>
	<startingBounds minLat="<?php print $CONFIG->MAP_STARTING_MIN_LAT ?>" maxLat="<?php print $CONFIG->MAP_STARTING_MAX_LAT ?>"
		minLng="<?php print $CONFIG->MAP_STARTING_MIN_LNG ?>" maxLng="<?php print $CONFIG->MAP_STARTING_MAX_LNG ?>" />
	<uploads maxSize="<?php  print $CONFIG->MAXIMUM_UPLOAD_ALLOWED ?>" />
	<?php if ($showLinks) { ?>
	<link rel="features" href="http://<?php echo $CONFIG->HTTP_HOST ?>/api/v1/features.php"/>
	<link rel="collections" href="http://<?php echo $CONFIG->HTTP_HOST ?>/api/v1/collections.php"/>
	<link rel="termsandconditions" href="http://<?php echo $CONFIG->HTTP_HOST ?>/api/v1/termsAndConditions.php"/>
	<link rel="privacypolicy" href="http://<?php echo $CONFIG->HTTP_HOST ?>/api/v1/privacyPolicy.php"/>
	<link rel="whatsonJan" href="http://<?php echo $CONFIG->HTTP_HOST ?>/api/v1/whatson.php?month=1"/>
	<link rel="whatsonFeb" href="http://<?php echo $CONFIG->HTTP_HOST ?>/api/v1/whatson.php?month=2"/>
	<link rel="whatsonMar" href="http://<?php echo $CONFIG->HTTP_HOST ?>/api/v1/whatson.php?month=3"/>
	<link rel="whatsonApr" href="http://<?php echo $CONFIG->HTTP_HOST ?>/api/v1/whatson.php?month=4"/>
	<link rel="whatsonMay" href="http://<?php echo $CONFIG->HTTP_HOST ?>/api/v1/whatson.php?month=5"/>
	<link rel="whatsonJun" href="http://<?php echo $CONFIG->HTTP_HOST ?>/api/v1/whatson.php?month=6"/>
	<link rel="whatsonJul" href="http://<?php echo $CONFIG->HTTP_HOST ?>/api/v1/whatson.php?month=7"/>
	<link rel="whatsonAug" href="http://<?php echo $CONFIG->HTTP_HOST ?>/api/v1/whatson.php?month=8"/>
	<link rel="whatsonSep" href="http://<?php echo $CONFIG->HTTP_HOST ?>/api/v1/whatson.php?month=9"/>
	<link rel="whatsonOct" href="http://<?php echo $CONFIG->HTTP_HOST ?>/api/v1/whatson.php?month=10"/>
	<link rel="whatsonNov" href="http://<?php echo $CONFIG->HTTP_HOST ?>/api/v1/whatson.php?month=11"/>
	<link rel="whatsonDec" href="http://<?php echo $CONFIG->HTTP_HOST ?>/api/v1/whatson.php?month=12"/>
	<link rel="wildlifeJan" href="http://<?php echo $CONFIG->HTTP_HOST ?>/api/v1/wildlife.php?month=1"/>
	<link rel="wildlifeFeb" href="http://<?php echo $CONFIG->HTTP_HOST ?>/api/v1/wildlife.php?month=2"/>
	<link rel="wildlifeMar" href="http://<?php echo $CONFIG->HTTP_HOST ?>/api/v1/wildlife.php?month=3"/>
	<link rel="wildlifeApr" href="http://<?php echo $CONFIG->HTTP_HOST ?>/api/v1/wildlife.php?month=4"/>
	<link rel="wildlifeMay" href="http://<?php echo $CONFIG->HTTP_HOST ?>/api/v1/wildlife.php?month=5"/>
	<link rel="wildlifeJun" href="http://<?php echo $CONFIG->HTTP_HOST ?>/api/v1/wildlife.php?month=6"/>
	<link rel="wildlifeJul" href="http://<?php echo $CONFIG->HTTP_HOST ?>/api/v1/wildlife.php?month=7"/>
	<link rel="wildlifeAug" href="http://<?php echo $CONFIG->HTTP_HOST ?>/api/v1/wildlife.php?month=8"/>
	<link rel="wildlifeSep" href="http://<?php echo $CONFIG->HTTP_HOST ?>/api/v1/wildlife.php?month=9"/>
	<link rel="wildlifeOct" href="http://<?php echo $CONFIG->HTTP_HOST ?>/api/v1/wildlife.php?month=10"/>
	<link rel="wildlifeNov" href="http://<?php echo $CONFIG->HTTP_HOST ?>/api/v1/wildlife.php?month=11"/>
	<link rel="wildlifeDec" href="http://<?php echo $CONFIG->HTTP_HOST ?>/api/v1/wildlife.php?month=12"/>
	<?php } ?>
</data>

