<?php
/**
 * @author James Baster  <james@jarofgreen.co.uk>
 * @copyright City of Edinburgh Council & James Baster
 * @license Open Source under the 3-clause BSD License
 * @url https://github.com/City-Outdoors/City-Outdoors-Web
 */
require '../../includes/src/global.php';
require '../../includes/src/APIV1Funcs.php';

$data = array_merge($_POST,$_GET);

$month = isset($data['month']) && intval($data['month']) > 0 && intval($data['month']) < 13 ? intval($data['month']) : date('n');

print CMSContent::renderBlock('wildlife-'.$month);


