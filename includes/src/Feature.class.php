<?php
/**
 * @author James Baster  <james@jarofgreen.co.uk>
 * @copyright City of Edinburgh Council & James Baster
 * @license Open Source under the 3-clause BSD License
 * @url https://github.com/City-Outdoors/City-Outdoors-Web
 */

class Feature extends BaseDataWithOneID {
	
	protected $title;

	protected $point_lat, $point_lng;
	
	protected $bounds_min_lat, $bounds_max_lat, $bounds_min_lng, $bounds_max_lng;
	
	protected $thumbnail_url;


	/** populated by FeatureSearch **/
	protected $has_collections_ids;
	
	/** populated by FeatureSearch  **/
	protected $has_user_answered_all_questions;
	
	/** @return Feature **/
	public static function findByID($id) {
		global  $CONFIG;
		$db = getDB();
		$stat = $db->prepare('SELECT * FROM feature '.
				'WHERE id = :id ');
		$stat->execute(array('id'=>$id));
		if ($d = $stat->fetch()) {
			return new Feature($d);	
		}
	}
	
	/** @return Feature **/
	public static function findOrCreateAtPosition($lat,$lng) {
		// TODO check lat & lng are actually valid!
		global  $CONFIG;
		$db = getDB();
		$stat = $db->prepare('SELECT * FROM feature '.
				'WHERE point_lat >= :lat_min AND point_lat <= :lat_max '.
				'AND point_lng >= :lng_min AND point_lng <= :lng_max ');
		$stat->execute(array(
				'lat_min'=>$lat-$CONFIG->LAT_ACCURACY,
				'lat_max'=>$lat+$CONFIG->LAT_ACCURACY,
				'lng_min'=>$lng-$CONFIG->LNG_ACCURACY,
				'lng_max'=>$lng+$CONFIG->LNG_ACCURACY
			));
		if ($d = $stat->fetch()) {
			return new Feature($d);
		} else {
			$stat = $db->prepare('INSERT INTO feature (point_lat, point_lng, bounds_min_lat, bounds_max_lat, bounds_min_lng, bounds_max_lng,  created_at) '.
					'VALUES (:point_lat, :point_lng, :bounds_min_lat, :bounds_max_lat, :bounds_min_lng, :bounds_max_lng, :created_at)');
			$data = array(
					'point_lat'=>$lat, 
					'point_lng'=>$lng, 
					'bounds_min_lat'=>$lat-$CONFIG->LAT_ACCURACY,
					'bounds_max_lat'=>$lat+$CONFIG->LAT_ACCURACY,
					'bounds_min_lng'=>$lng-$CONFIG->LNG_ACCURACY,
					'bounds_max_lng'=>$lng+$CONFIG->LNG_ACCURACY,
					'created_at'=>date('Y-m-d H:i:s')
				);
			$stat->execute($data);
			$data['id'] = $db->lastInsertId();
			return new Feature($data);
		}		
	}
	
	/** @return Feature **/
	public static function loadByID($id) {
		$db = getDB();
		$stat = $db->prepare('SELECT * FROM feature WHERE id=:id');
		$stat->bindValue('id', $id);
		$stat->execute();
		if($stat->rowCount() == 1) {
			return new Feature($stat->fetch(PDO::FETCH_ASSOC));
		}		
	}		
	
	public function __construct($data) {
		parent::__construct($data);
		if ($data && isset($data['point_lat'])) $this->point_lat = $data['point_lat'];
		if ($data && isset($data['point_lng'])) $this->point_lng = $data['point_lng'];
		if ($data && isset($data['bounds_min_lat'])) $this->bounds_min_lat = $data['bounds_min_lat'];
		if ($data && isset($data['bounds_max_lat'])) $this->bounds_max_lat = $data['bounds_max_lat'];
		if ($data && isset($data['bounds_min_lng'])) $this->bounds_min_lng = $data['bounds_min_lng'];
		if ($data && isset($data['bounds_max_lng'])) $this->bounds_max_lng = $data['bounds_max_lng'];
		if ($data && isset($data['has_collections_ids'])) $this->has_collections_ids = $data['has_collections_ids'];
		if ($data && isset($data['has_user_answered_all_questions'])) $this->has_user_answered_all_questions = $data['has_user_answered_all_questions'];
		if ($data && isset($data['title'])) $this->title = $data['title'];
		if ($data && isset($data['thumbnail_url'])) $this->thumbnail_url = $data['thumbnail_url'];
	}	
	
	public function getPointLat() { return $this->point_lat; }	
	public function getPointLng() { return $this->point_lng; }	
	public function getTitle() { return $this->title; }	
	public function getThumbnailURL() { return $this->thumbnail_url; }	
	public function getBoundsMinLng() { return $this->bounds_min_lng; }	
	public function getBoundsMinLat() { return $this->bounds_min_lat; }	
	public function getBoundsMaxLng() { return $this->bounds_max_lng; }	
	public function getBoundsMaxLat() { return $this->bounds_max_lat; }	
	
	public function getCollectionIDS() { return $this->has_collections_ids ? explode(",", $this->has_collections_ids) : array(); }
	public function getHasUserAnsweredAllQuestions() { return $this->has_user_answered_all_questions; }
	
	public function newContent($body, User $by, $name=null, $email=null, $report=false, $userAgent = null, $ip=null) {
		$db = getDB();
		$data = array(
				'feature_id'=>$this->id,
				'created_by'=>($by?$by->getId():null),
				'comment_body'=>$body,
				'created_name'=>$name,
				'created_email'=>$email,
				'created_at'=>date('Y-m-d H:i:s'),
				'is_report'=>($report?1:0),
				'user_agent'=>substr($userAgent,0,255),
				'ip'=>substr($ip,0,50)
			);		
		if (FeatureContent::isCreatedContentModerated($by)) {
			$stat = $db->prepare('INSERT INTO feature_content (feature_id,created_by,comment_body,created_at,is_report,created_name,created_email,user_agent,ip) '.
				'VALUES (:feature_id,:created_by,:comment_body,:created_at,:is_report,:created_name,:created_email,:user_agent,:ip)');
		} else {
			$stat = $db->prepare('INSERT INTO feature_content (feature_id,created_by,comment_body,created_at,approved_at,is_report,created_name,created_email,user_agent,ip) '.
				'VALUES (:feature_id,:created_by,:comment_body,:created_at,:approved_at,:is_report,:created_name,:created_email,:user_agent,:ip)');
			$data['approved_at'] = date('Y-m-d H:i:s');
		}
		$stat->execute($data);
		$data['id'] = $db->lastInsertId();
		// normally we do a join to include the user row when we load FeatureContent. Need to add the data in now instead.
		$data['display_name'] = $by->getName();
		return new FeatureContent($data);
	}
	
	public function newAnonymousContent($body, $name=null, $email=null, $report=false, $userAgent = null, $ip=null) {
		$db = getDB();
		$data = array(
				'feature_id'=>$this->id,
				'created_name'=>$name,
				'comment_body'=>$body,
				'created_email'=>$email,
				'created_at'=>date('Y-m-d H:i:s'),
				'is_report'=>($report?1:0),
				'user_agent'=>substr($userAgent,0,255),
				'ip'=>substr($ip,0,50)
			);		
		if (FeatureContent::isCreatedContentModerated(null)) {
			$stat = $db->prepare('INSERT INTO feature_content (feature_id,created_name,comment_body,created_at,is_report,created_email,user_agent,ip) '.
				'VALUES (:feature_id,:created_name,:comment_body,:created_at,:is_report,:created_email,:user_agent,:ip)');
		} else {
			$stat = $db->prepare('INSERT INTO feature_content (feature_id,created_name,comment_body,created_at,approved_at,is_report,created_email,user_agent,ip) '.
				'VALUES (:feature_id,:created_name,:comment_body,:created_at,:approved_at,:is_report,:created_email,:user_agent,:ip)');
			$data['approved_at'] = date('Y-m-d H:i:s');
		}
		$stat->execute($data);
		$data['id'] = $db->lastInsertId();
		return new FeatureContent($data);
	}

	/** should return true if all questions for this feature have been answered **/
	public function hasUserCheckedIn(User $user) {
		$db = getDB();
		$stat = $db->prepare("SELECT feature_checkin_question.* FROM feature_checkin_question ".
				" LEFT JOIN feature_checkin_success ON feature_checkin_success.feature_checkin_question_id = feature_checkin_question.id AND feature_checkin_success.user_account_id=:uid".
				" WHERE feature_checkin_success.user_account_id IS NULL AND feature_checkin_question.feature_id=:fid");
		$stat->execute(array('uid'=>$user->getId(),'fid'=>$this->id));
		return $stat->rowCount() == 0;		
	}
	
	public function hasCheckedInQuestions() {
		$s = new FeatureCheckinQuestionSearch();
		$s->withinFeature($this);
		return $s->num() > 0;		
	}
	
	public function hasUserFravourited(User $user) {
		$db = getDB();
		$stat = $db->prepare("SELECT * FROM feature_favourite WHERE user_account_id=:uid AND feature_id=:fid");
		$stat->execute(array('uid'=>$user->getId(),'fid'=>$this->id));
		return $stat->rowCount() > 0;
	}
		
	public function favourite(User $user, $favouritedAt = null, $userAgent = null, $ip=null) {
		if (!$favouritedAt) $favouritedAt = time();
		$db = getDB();
		
		$stat = $db->prepare("SELECT * FROM feature_favourite WHERE user_account_id=:uid AND feature_id=:fid");
		$stat->execute(array('uid'=>$user->getId(),'fid'=>$this->id));
		if ($stat->rowCount() == 0) {
			$data = array(
					'feature_id'=>$this->id,
					'user_account_id'=>$user->getId(),
					'favourited_at'=>date('Y-m-d H:i:s', $favouritedAt),
					'created_at'=>date('Y-m-d H:i:s', time()),
					'user_agent'=>substr($userAgent,0,255),
					'ip'=>substr($ip,0,50)
				);		
			$stat = $db->prepare("INSERT INTO feature_favourite (user_account_id,feature_id,favourited_at,created_at,user_agent,ip) ".
					"VALUES (:user_account_id,:feature_id,:favourited_at,:created_at,:user_agent,:ip)");
			$stat->execute($data);
		}
	}
	
	public function getFravouriteCount() {
		$db = getDB();
		$stat = $db->prepare("SELECT COUNT(*) AS c FROM (SELECT user_account_id FROM feature_favourite WHERE feature_id=:fid GROUP BY user_account_id) AS t");
		$stat->execute(array('fid'=>$this->id));
		$data = $stat->fetch();
		return $data['c'];
	}
	
	public function getCheckinCount() {
		$db = getDB();
		$stat = $db->prepare("SELECT COUNT(*) AS c FROM feature_checkin_success  ".
				"JOIN feature_checkin_question ON feature_checkin_question.id = feature_checkin_success.feature_checkin_question_id ".
				"WHERE feature_checkin_question.feature_id = :fid");
		$stat->execute(array('fid'=>$this->id));
		$data = $stat->fetch();
		return $data['c'];
	}
	
	
	public function expandToIncludeFeature(Feature $feature) {
		$flag = false;
		
		if ($feature->getPointLat() < $this->bounds_min_lat) {
			$this->bounds_min_lat = $feature->getPointLat();
			$flag = true;
		}
		if ($feature->getPointLat() > $this->bounds_max_lat) {
			$this->bounds_max_lat = $feature->getPointLat();
			$flag = true;
		}
		if ($feature->getPointLng() < $this->bounds_min_lng) {
			$this->bounds_min_lng = $feature->getPointLng();
			$flag = true;
		}
		if ($feature->getPointLng() > $this->bounds_max_lng) {
			$this->bounds_max_lng = $feature->getPointLng();
			$flag = true;
		}
		
		if ($flag) {
			$db = getDB();
			$stat = $db->prepare('UPDATE feature SET  bounds_min_lat=:bounds_min_lat, bounds_max_lat=:bounds_max_lat, 
				bounds_min_lng=:bounds_min_lng, bounds_max_lng=:bounds_max_lng WHERE id=:id');
			$data = array(
					'bounds_min_lat'=>$this->bounds_min_lat,
					'bounds_max_lat'=>$this->bounds_max_lat,
					'bounds_min_lng'=>$this->bounds_min_lng,
					'bounds_max_lng'=>$this->bounds_max_lng,
					'id'=>$this->id
				);		
			$stat->execute($data);
		}
		
	}
	
	
	function getCountChildItems() {
		$db = getDB();
		$stat = $db->prepare("SELECT count(child_item.id) AS c FROM item AS child_item ".
				"JOIN item AS parent_item ON parent_item.id = child_item.parent_id ".
				"WHERE parent_item.feature_id = :id");
		$stat->execute(array('id'=>$this->id));
		$data = $stat->fetch();
		return $data['c'];
	}

	
	function getCountChildItemsNotInHiddenCollection() {
		global $CONFIG;
		
		$collection = Collection::loadBySlug($CONFIG->HIDDEN_COLLECTION_SLUG);
		if (!$collection) return $this->getCountChildItems ();
		
		$db = getDB();
		$stat = $db->prepare("SELECT count(child_item.id) AS c FROM item AS child_item ".
				"JOIN item AS parent_item ON parent_item.id = child_item.parent_id ".
				"WHERE parent_item.feature_id = :id AND child_item.collection_id != :cid");
		$stat->execute(array('id'=>$this->id, 'cid'=>$collection->getId()));
		$data = $stat->fetch();
		return $data['c'];
	}

	protected $titleItem = null;
	protected $titleItemLoaded = false;
	
	/** @return Item **/
	function getTitleItem() {
		if (!$this->titleItemLoaded) {
			$itemSearch = new ItemSearch();
			$itemSearch->onFeature($this);
			$this->titleItem = $itemSearch->nextResult();
			// TODO if more than one item on this point, pick the one from the main collection first
			$this->titleItemLoaded = true;
		}
		return $this->titleItem;
	}
	
	
	function setTitle($title) {
		$db = getDB();
		$stat = $db->prepare("UPDATE feature SET title=:title WHERE id=:id");
		$stat->execute(array('title'=>$title,'id'=>$this->id));
	}
	
	
	function setThumbnailURL($thumbnail_url) {
		$db = getDB();
		$stat = $db->prepare("UPDATE feature SET thumbnail_url=:thumbnail_url WHERE id=:id");
		$stat->execute(array('thumbnail_url'=>$thumbnail_url,'id'=>$this->id));
	}
	
	function getCheckinQuestions($includeDeleted = false) {
		$s = new FeatureCheckinQuestionSearch();
		$s->withinFeature($this);
		if ($includeDeleted) $s->includeDeleted(true);
		return $s->getAllResultsIndexed();
	}
	
	/** 
	 * @global type $CONFIG
	 * @return type The URL of the map tile from the map source.
	 */
	function getRealStaticMapTileURL() {
		global $CONFIG;
		$titleItem = $this->getTitleItem();
		if ($titleItem) {
			$marker = "icon:".urlencode("http://".$CONFIG->HTTP_HOST."/".$titleItem->getCollection()->getIconURL());
		} else {
			$marker = "icon:".urlencode("http://".$CONFIG->HTTP_HOST."/img/marker-usercontent-med.png");
		}
		
		return 'http://maps.googleapis.com/maps/api/staticmap?center='.$this->point_lat.','.$this->point_lng.
				'&zoom=14&size='.$CONFIG->GOOGLE_MAP_FEATURE_MAP_WIDTH.'x'.$CONFIG->GOOGLE_MAP_FEATURE_MAP_HEIGHT.
				'&maptype=roadmap&markers='.$marker.'%7C'.$this->point_lat.','.$this->point_lng.
				'&sensor=false&key='.$CONFIG->GOOGLE_MAP_API_KEY;
	}
	
	/**
	 * @return String the cached local copy or the real URL 
	 */
	function getOurStaticMapTileURL() {
		$folder = realpath(dirname(__FILE__).DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."content").DIRECTORY_SEPARATOR."featureMaps".DIRECTORY_SEPARATOR;
		$filename = $folder.DIRECTORY_SEPARATOR.$this->id.".png";
		if (file_exists($filename)) {
			return '/content/featureMaps/'.$this->id.'.png';
		} else {
			return $this->getRealStaticMapTileURL();
		}
	}
	
	function downloadStaticMapTile() {
		$folder = realpath(dirname(__FILE__).DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."content").DIRECTORY_SEPARATOR."featureMaps".DIRECTORY_SEPARATOR;
		$filename = $folder.DIRECTORY_SEPARATOR.$this->id.".png";
		if (!file_exists($folder)) mkdir ($folder);
		
		
		$ch = curl_init();	
		curl_setopt($ch, CURLOPT_URL, $this->getRealStaticMapTileURL());
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_USERAGENT, 'City Outdoors');
		$rawData = curl_exec($ch);
		curl_close($ch);
		
		file_put_contents($filename, $rawData);
		
	}
	
}
