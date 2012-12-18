<?php
/**
 * @author James Baster  <james@jarofgreen.co.uk>
 * @copyright City of Edinburgh Council & James Baster
 * @license Open Source under the 3-clause BSD License
 * @url https://github.com/City-Outdoors/City-Outdoors-Web
 */
require 'includes/src/global.php';

$month = isset($_GET['month']) && intval($_GET['month']) > 0 && intval($_GET['month']) < 13 ? intval($_GET['month']) : date('n');

$d = new RenderCMSContentByMonth("whatson",$month);

$tpl = $d->getSmarty(getCurrentUser());

$tpl->assign('monthlyPageURLForSubNav','/whatson.php?month=');
$tpl->assign('inWhatsOnTab',true);
$tpl->display('whatson.htm');







