<?php
/**
 * @author James Baster  <james@jarofgreen.co.uk>
 * @copyright City of Edinburgh Council & James Baster
 * @license Open Source under the 3-clause BSD License
 * @url https://github.com/City-Outdoors/City-Outdoors-Web
 */

/**if (!isset($_GET['search']) || !trim($_GET['search'])) {
	header("Location: /");
	die();
}**/

require 'includes/src/global.php';


$currentUser = getCurrentUser();



$tpl = getSmarty($currentUser);

$collectionSearch = new CollectionSearch();
$tpl->assign('collections', $collectionSearch->getAllResultsIndexed());


$page = isset($_GET['page']) ? max(intval($_GET['page']),1) : 1;
$itemSearch = new ItemSearch();
$itemSearch->setPaging($page, 10);
$itemSearch->freeTextSearch($_GET['search']);
$itemSearch->includeChildCollections();
$tpl->assign('itemSearch',$itemSearch);

$tpl->assign('searchTerm',$_GET['search']);

$tpl->display('search.htm');

