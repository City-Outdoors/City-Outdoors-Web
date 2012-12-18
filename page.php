<?php
/**
 * @author James Baster  <james@jarofgreen.co.uk>
 * @copyright City of Edinburgh Council & James Baster
 * @license Open Source under the 3-clause BSD License
 * @url https://github.com/City-Outdoors/City-Outdoors-Web
 */
require 'includes/src/global.php';

$currentUser = getCurrentUser();

$page = CMSContent::loadPageBySlug($_GET['s']);
if (!$page) die("Page not found.");

$tpl = getSmarty($currentUser);
$tpl->assign("page",$page);
$tpl->display('CMSContent.htm');




