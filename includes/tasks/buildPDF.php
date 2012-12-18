<?php
/**
 * @author James Baster  <james@jarofgreen.co.uk>
 * @copyright City of Edinburgh Council & James Baster
 * @license Open Source under the 3-clause BSD License
 * @url https://github.com/City-Outdoors/City-Outdoors-Web
 */
include dirname(__FILE__).'/../src/global.php';

$folder = dirname(__FILE__).'/../../content/pdf/';

if (!file_exists($folder)) mkdir($folder);

$tpl = getSmarty();
$tpl->assign('dir',  realpath(dirname(__FILE__).'/../../'));

$collectionSearch = new CollectionSearch();
$tpl->assign('collections', $collectionSearch->getAllResultsIndexed());	




$featureSearch = new FeatureSearch();
while ($feature = $featureSearch->nextResult()) {
	
	print $feature->getId()."\n";
		
	$tpl->assign('feature',$feature);

	$titleItem = $feature->getTitleItem();
	$tpl->assign('titleItem',$titleItem);
	
	if ($titleItem) {
		$titleCollection = $titleItem->getCollection();
		$tpl->assign('titleCollection',$titleCollection);
		$googleStaticMapURLMarker = 'icon:'.  urlencode($titleCollection->getIconURL());
	} else {
		$tpl->assign('titleCollection',null);
		$googleStaticMapURLMarker = 'color:blue%7Clabel:S';
	}
	
	$tpl->assign('mapImageURL','http://maps.googleapis.com/maps/api/staticmap?center='.$feature->getPointLat().','.$feature->getPointLng().
			'&zoom=14&size=194x194&maptype=roadmap&markers='.$googleStaticMapURLMarker.'%7C'.$feature->getPointLat().','.$feature->getPointLng().
			'&sensor=false&key='.$CONFIG->GOOGLE_MAP_API_KEY);
	
	$featureContentSearch = new FeatureContentSearch();
	$featureContentSearch->forFeature($feature);
	$featureContentSearch->approvedOnly();
	$tpl->assign('featureContentSearch',$featureContentSearch);
	
	$tpl->assign('featureCheckInQuestions',$feature->getCheckinQuestions());

	
	
	if ($titleItem) {
		$childItemSearch = new ItemSearch();
		$childItemSearch->hasParentItem($titleItem);
		$tpl->assign('childItems',$childItemSearch->getAllResults());
	} else {
		$tpl->assign('childItems',array());
	}
	//die($tpl->fetch("feature.pdf.htm"));
	
	$dompdf = new DOMPDF();
	$dompdf->set_paper('a4', 'portrait');	
	$dompdf->load_html($tpl->fetch("feature.pdf.htm"));
	$dompdf->render();

	$contents = $dompdf->output();
	file_put_contents($folder.$feature->getId().'.pdf', $contents);

	
	//die();
}
