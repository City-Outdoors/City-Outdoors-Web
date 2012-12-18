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
$tpl->assign('inHomeTab',true);

$tpl->assign('hasLastTweet',false);
if (file_exists('content/lastTweet.htm')) {	
	$tpl->assign('hasLastTweet',true);
	$tpl->assign('lastTweetHTML',  file_get_contents('content/lastTweet.htm'));
}

$tpl->display('index.htm');
