<?php
/**
 * @author James Baster  <james@jarofgreen.co.uk>
 * @copyright City of Edinburgh Council & James Baster
 * @license Open Source under the 3-clause BSD License
 * @url https://github.com/City-Outdoors/City-Outdoors-Web
 */
require 'includes/src/global.php';


$month = isset($_GET['month']) && intval($_GET['month']) > 0 && intval($_GET['month']) < 13 ? intval($_GET['month']) : date('n');

$d = new RenderCMSContentByMonth("wildlife",$month);

$tpl = $d->getSmarty(getCurrentUser());

$tpl->assign('monthlyPageURLForSubNav','/wildlife.php?month=');
$tpl->assign('inWildlifeTab',true);
$tpl->display('wildlife.htm');




