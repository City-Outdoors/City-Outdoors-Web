<?php
/**
 * @author James Baster  <james@jarofgreen.co.uk>
 * @copyright City of Edinburgh Council & James Baster
 * @license Open Source under the 3-clause BSD License
 * @url https://github.com/City-Outdoors/City-Outdoors-Web
 */
require 'includes/src/global.php';

$currentUser = getCurrentUser();

$tpl = getSmarty($currentUser);
$tpl->assign('fromForm',(boolean)(isset($_GET['fromForm']) && $_GET['fromForm']));
$tpl->display('privacyPolicy.htm');


