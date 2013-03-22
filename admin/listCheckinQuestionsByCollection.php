<?php
/**
 * @author James Baster  <james@jarofgreen.co.uk>
 * @copyright City of Edinburgh Council & James Baster
 * @license Open Source under the 3-clause BSD License
 * @url https://github.com/City-Outdoors/City-Outdoors-Web
 */

require '../includes/src/global.php';


$currentUser = mustBeLoggedIn();
if (!$currentUser->isAdministrator()) die('No Access');

$collection = Collection::loadBySlug($_GET['c']);
if (!$collection) die('not found');

$s = new FeatureCheckinQuestionSearch;
$s->withinCollection($collection);
$s->includeDeleted(true);
		
$tpl = getSmarty($currentUser);
$tpl->assign('checkinQuestionSearch',$s);
$tpl->assign('collection',$collection);
$tpl->display('admin/listCheckinQuestionsByCollection.htm');





