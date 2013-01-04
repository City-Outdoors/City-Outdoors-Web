<?php
/**
 * @author James Baster  <james@jarofgreen.co.uk>
 * @copyright City of Edinburgh Council & James Baster
 * @license Open Source under the 3-clause BSD License
 * @url https://github.com/City-Outdoors/City-Outdoors-Web
 */


require dirname(__FILE__).'/../src/globalFuncs.php';


class AbstractTest extends PHPUnit_Framework_TestCase {


	function setUp() {
		global $CONFIG;
		$CONFIG->load(dirname(__FILE__).'/../tests.config.ini');
	}

	static $firstRun = true;

	function setupDB() {        
		$db = getDB();
		if (AbstractTest::$firstRun) {
			$db->exec(file_get_contents(dirname(__FILE__).'/../sql/destroy.sql'));			
			DBMigrationManager::upgrade(false);
			AbstractTest::$firstRun = false;
		} else {
			$db->exec(file_get_contents(dirname(__FILE__).'/../sql/truncate.sql'));
		}
		return $db;
	}
}
