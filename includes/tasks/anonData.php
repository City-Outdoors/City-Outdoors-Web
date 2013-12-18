<?php
/**
 * 
 * Use on test servers only.
 * 
 * This will remove all email addresses from the DB and replace them with dummy data;
 * 
 * @author James Baster  <james@jarofgreen.co.uk>
 * @copyright City of Edinburgh Council & James Baster
 * @license Open Source under the 3-clause BSD License
 * @url https://github.com/City-Outdoors/City-Outdoors-Web
 */
include dirname(__FILE__).'/../src/global.php';

die("IS THIS A TEST SERVER? COMMENT OUT IF SO!\n\n");

$db = getDB();
$stat = $db->prepare("UPDATE user_account SET email = CONCAT(id, '@edinburghoutdoors.org.uk') WHERE email != 'james@jarofgreen.co.uk'");
$stat->execute();

