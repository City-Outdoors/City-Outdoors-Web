<?php
/**
 * @author James Baster  <james@jarofgreen.co.uk>
 * @copyright City of Edinburgh Council & James Baster
 * @license Open Source under the 3-clause BSD License
 * @url https://github.com/City-Outdoors/City-Outdoors-Web
 */
include dirname(__FILE__).'/../src/global.php';


$connection = new TwitterOAuth($CONFIG->TWITTER_APP_KEY, $CONFIG->TWITTER_APP_SECRET, 
			$CONFIG->TWITTER_USER_KEY,$CONFIG->TWITTER_USER_SECRET);
$content = $connection->get('statuses/user_timeline',array(
		'screen_name'=>$CONFIG->TWITTER_USERNAME,
		'include_rts'=>false, 
		'trim_user'=>true,
		'exclude_replies '=>true,
		'count'=>40,
	));

$latestID = null;

foreach($content as $tweet) {
	if (is_null($latestID) && substr($tweet->text,0,1) != '@') {
		$latestID = $tweet->id_str;
	}
}

if (!is_null($latestID)) {
	
	$content = $connection->get('statuses/oembed',array('id'=>$latestID));
	var_dump($content);
	
	file_put_contents(dirname(__FILE__).'/../../content/lastTweet.htm', $content->html);
	
}

print "Done\n";


