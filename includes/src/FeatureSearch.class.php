<?php
/**
 * @author James Baster  <james@jarofgreen.co.uk>
 * @copyright City of Edinburgh Council & James Baster
 * @license Open Source under the 3-clause BSD License
 * @url https://github.com/City-Outdoors/City-Outdoors-Web
 */

class FeatureSearch extends BaseSearch {
	
	private $boundsLeft, $boundsRight, $boundsTop, $boundsBottom;
	
	
	private $showAllFeatures = false;
	
	/** @var User **/
	private $visibleToUser = null;
	
	/** @var User **/
	private $userFavourites;
	
	/** @var User **/
	private $userCheckedin;
	
	
	/** @var User **/
	private $userNotCheckedin;
	
	
	private $collectionIDs = array();
	
	public function  __construct() {
		$this->className = "Feature";
	}
	
	public function withinBounds($left, $right, $top, $bottom) {
		$this->boundsLeft = $left;
		$this->boundsRight = $right;
		$this->boundsTop = $top;
		$this->boundsBottom = $bottom;
	}
	
	public function allFeatures() {
		$this->showAllFeatures = true;
	}
	
	public function  visibleToUser(User $user = null) {
		$this->showAllFeatures = false;
		$this->visibleToUser = $user;
	}
	
	public function  withinCollection(Collection $collection) {
		$this->collectionIDs[] = $collection->getId();
	}
	
	public function userFavourites(User $user) {
		$this->userFavourites = $user;
	}
	
	public function userCheckedin(User $user) {
		$this->userCheckedin = $user;
	}
	
	public function userNotCheckedin(User $user) {
		$this->userNotCheckedin = $user;
	}
	
	protected function execute() {
		if ($this->searchDone) throw new Exception("Search already done!");
		$db = getDB();
		$where = array();
		$joins = array();
		$vars = array();
		$select = array('feature.*');
		
		if (!is_null($this->boundsLeft)) {
			$where[] = " feature.point_lng >= :boundsLeft";
			$vars['boundsLeft'] = $this->boundsLeft;
		}
		if (!is_null($this->boundsRight)) {
			$where[] = " feature.point_lng <= :boundsRight";
			$vars['boundsRight'] = $this->boundsRight;
		}
		if (!is_null($this->boundsTop)) {
			$where[] = " feature.point_lat <= :boundsTop";
			$vars['boundsTop'] = $this->boundsTop;
		}
		if (!is_null($this->boundsBottom)) {
			$where[] = " feature.point_lat >= :boundsBottom";
			$vars['boundsBottom'] = $this->boundsBottom;
		}
		if ($this->collectionIDs) {
			$where[] = " item.collection_id IN (".  implode(",", $this->collectionIDs).") ";
		}
		
		if ($this->userFavourites) {
			$joins[] = " JOIN feature_favourite ON feature_favourite.feature_id = feature.id AND feature_favourite.user_account_id = :fuid ";
			$vars['fuid'] = $this->userFavourites->getId();
		}
		if ($this->userCheckedin) {
			$joins[] = " JOIN feature_checkin_question ON feature_checkin_question.feature_id = feature.id";
			$joins[] = " JOIN feature_checkin_success ON  feature_checkin_question.id =  feature_checkin_success.feature_checkin_question_id AND feature_checkin_success.user_account_id = :ciuid ";
			$vars['ciuid'] = $this->userCheckedin->getId();
		} else if ($this->userNotCheckedin) {
			$joins[] = " JOIN feature_checkin_question ON feature_checkin_question.feature_id = feature.id";
			$joins[] = " LEFT JOIN feature_checkin_success ON  feature_checkin_question.id =  feature_checkin_success.feature_checkin_question_id AND feature_checkin_success.user_account_id = :ciuid ";
			$where[] = " feature_checkin_success.id IS NULL ";
			$vars['ciuid'] = $this->userNotCheckedin->getId();
		}
		
		if (!$this->showAllFeatures) {
			// we search for features visible to a user eg (feature with content on it)
			$joins[] = " LEFT JOIN item ON item.feature_id = feature.id ";
			$joins[] = " LEFT JOIN feature_content ON feature_content.feature_id = feature.id AND feature_content.approved_at IS NOT NULL ";
			$where[] = " (item.id IS NOT NULL OR feature_content.id IS NOT NULL)";
			$select[] = " GROUP_CONCAT(item.collection_id) AS has_collections_ids ";
		}

		$sql = "SELECT ".implode(" , ", $select).
			"FROM feature ".implode(" ", $joins).(count($where) > 0 ? " WHERE ".implode(" AND ", $where) : "")." GROUP BY feature.id";
		$stat = $db->prepare($sql);
		$stat->execute($vars);
		while($d = $stat->fetch(PDO::FETCH_ASSOC)) {
			$this->results[] = $d;
		}
		$this->searchDone = true;
	}
	
}
	
