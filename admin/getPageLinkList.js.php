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


$pageSearch = new CMSContentSearch();
$pageSearch->pagesOnly();

$collectionSearch = new CollectionSearch();

?>
var tinyMCELinkList = new Array(
	<?php while($page = $pageSearch->nextResult()) { ?>["<?php print $page->getPageTitle() ?>", "/page.php?p=<?php print $page->getPageSlug() ?>"],<?php } ?>
	<?php while($collection = $collectionSearch->nextResult()) { ?>["List <?php print $collection->getTitle() ?>", "/collectionAsList.php?c=<?php print $collection->getSlug() ?>"],<?php } ?>
	["Home", "/"],
	["New Content", "/newFeatureContent.php"]
);
