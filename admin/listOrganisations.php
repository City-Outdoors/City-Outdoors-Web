<?php
/**
 * @author James Baster  <james@jarofgreen.co.uk>
 * @copyright City of Edinburgh Council & James Baster
 * @license Open Source under the 3-clause BSD License
 * @url https://github.com/City-Outdoors/City-Outdoors-Web
 */

require '../includes/src/global.php';


$currentUser = mustBeLoggedIn();
if (!$currentUser->isSystemAdministrator()) die('No Access');


$s = new OrganisationSearch();

$tpl = getSmarty($currentUser);
$tpl->assign('organisationSearch',$s);
$tpl->display('admin/listOrganisations.htm');

