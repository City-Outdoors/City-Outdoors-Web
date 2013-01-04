<?php
/**
 * @author James Baster  <james@jarofgreen.co.uk>
 * @copyright City of Edinburgh Council & James Baster
 * @license Open Source under the 3-clause BSD License
 * @url https://github.com/City-Outdoors/City-Outdoors-Web
 */

class DBMigrationManager {

	public static function upgrade($verbose = false) {
		
		$db = getDB();
		
		// First, the migrations table.
		$stat = $db->query("SHOW TABLES LIKE 'migration'");
		$tableExists = ($stat->rowCount() == 1);
		
		if ($tableExists) {
			if ($verbose) print "Migrations table exists.\n";
		} else {
			if ($verbose) print "Creating migration table.\n";
			$db->query("CREATE TABLE migration ( id VARCHAR(255) NOT NULL, installed_at DATETIME NOT NULL, PRIMARY KEY(id)  ) ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci");
		}

		// Now load all possible migrations from disk & sort them
		$migrations = array();
		$dir = dirname(__FILE__).'/../sql/migrations/';
		$handle = opendir($dir);		
		while (false !== ($file = readdir($handle))) {
			if ($file != '.' && $file != '..') {
				if ($verbose) echo "Checking ".$file."\n";
				if (substr($file, -4) == '.sql') {
					$migrations[] = new DBMigration(substr($file, 0, -4), file_get_contents($dir.$file));
				}
			}
		}
		closedir($handle);
		usort($migrations, "DBMigrationManager::compareMigrations");
		
		// Now see what is already applied 
		// ... in an O(N^2) loop inside a loop, performance could be better but doesn't matter here for now.
		$stat = $db->query("SELECT id FROM migration");
		while($result = $stat->fetch()) {
			foreach($migrations as $migration) { 
				if ($migration->getId() == $result['id']) {
					$migration->setIsApplied();
				}
			}
		}
		
		// Finally apply the new ones!
		if ($verbose) {
			foreach($migrations as $migration) {
				if (!$migration->getApplied()) print "Will apply ".$migration->getId()."\n";				
			}
		}
		$stat = $db->prepare("INSERT INTO migration (id, installed_at) VALUES (:id, :at)");
		foreach($migrations as $migration) {
			if (!$migration->getApplied()) {
				if ($verbose) print "Applying ".$migration->getId()."\n";
				$db->beginTransaction();
				$migration->performMigration($db);
				$stat->execute(array('id'=>$migration->getId(),'at'=>date('Y-m-h H:i:s')));
				$db->commit();
				if ($verbose) print "Applied ".$migration->getId()."\n";
			}
		}
		
		if ($verbose) print "Done\n";
		
		
	}
	
	private static function compareMigrations(DBMigration $a, DBMigration $b) {
		if ($a->getIdAsUnixTimeStamp() == $b->getIdAsUnixTimeStamp()) return 0;
		return ($a->getIdAsUnixTimeStamp() < $b->getIdAsUnixTimeStamp()) ? -1 : 1;
	}
}



