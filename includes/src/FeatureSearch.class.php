<?php
/**
 * @author James Baster  <james@jarofgreen.co.uk>
 * @copyright City of Edinburgh Council & James Baster
 * @license Open Source under the 3-clause BSD License
 * @url https://github.com/City-Outdoors/City-Outdoors-Web
 */

class FeatureSearch extends BaseSearch {
	
	private $boundsLeft, $boundsRight, $boundsTop, $boundsBottom;
	
	private $sortBy = '';
	
	private $showAllFeatures = false;
	
	/** @var User **/
	private $visibleToUser = null;
	
	/** @var User **/
	private $userFavourites;
	
	/** @var User **/
	private $userCheckedin;
		
	/** @var User **/
	private $userNotCheckedin;
	
	/** @var User **/
	private $userCheckedinInformation;	
	
	/** @var Event **/
	private $event;
	
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
	
	/**
	 * Only show features the user has checked in on.
	 * @param User $user 
	 */
	public function userCheckedin(User $user) {
		$this->userCheckedin = $user;
	}
	
	/**
	 * Include info on whether the user has checked in or not at each feature 
	 * @param User $user 
	 */
	public function userCheckedinInformation(User $user = null, $sortByQuestionSortOrder = false) {
		$this->userCheckedinInformation = $user;
		if ($sortByQuestionSortOrder) {
			$this->sortBy = 'questionSortOrder';
		}
	}
	
	/**
	 * Only show features the user has NOT checked in on.
	 * @param User $user 
	 */
	public function userNotCheckedin(User $user, $sortByQuestionSortOrder = false) {
		$this->userNotCheckedin = $user;
		if ($sortByQuestionSortOrder) {
			$this->sortBy = 'questionSortOrder';
		}		
	}
	
	public function hasEvent(Event $event) {
		$this->event = $event;
	}
	
	protected function execute() {
		if ($this->searchDone) throw new Exception("Search already done!");
		$db = getDB();
		$where = array();
		$joins = array();
		$joinItems = false; $joinContent = false; $joinQuestions = false;
		$leftJoinItems = false; $leftJoinContent = false; $leftJoinQuestions = false;
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
			$joinItems = true;
			$where[] = " item.collection_id IN (".  implode(",", $this->collectionIDs).") ";
		}
		
		// Which set of features do we show? Depends on the user &  $this->showAllFeatures flag.
		if ($this->userFavourites) {
			$joins[] = " JOIN feature_favourite ON feature_favourite.feature_id = feature.id AND feature_favourite.user_account_id = :fuid ";
			$vars['fuid'] = $this->userFavourites->getId();
		} else if ($this->userCheckedin) {
			$joinQuestions = true;
			$joins[] = " JOIN feature_checkin_success ON  feature_checkin_question.id =  feature_checkin_success.feature_checkin_question_id AND feature_checkin_success.user_account_id = :ciuid ";
			$vars['ciuid'] = $this->userCheckedin->getId();
		} else if ($this->userNotCheckedin) {
			$joinQuestions = true;
			$joins[] = " LEFT JOIN feature_checkin_success ON  feature_checkin_question.id =  feature_checkin_success.feature_checkin_question_id AND feature_checkin_success.user_account_id = :ciuid ";
			$where[] = " feature_checkin_success.id IS NULL ";
			$vars['ciuid'] = $this->userNotCheckedin->getId();
		} else if (!$this->showAllFeatures) {
			// we search for features visible to a user - feature with content on it, item or question.
			$leftJoinItems = $leftJoinContent = $leftJoinQuestions = true;
			$where[] = " (item.id IS NOT NULL OR feature_content.id IS NOT NULL OR feature_checkin_question.id IS NOT NULL)";
			$select[] = " GROUP_CONCAT(item.collection_id) AS has_collections_ids ";
			if ($this->userCheckedinInformation) {
				$joins[] = " LEFT JOIN feature_checkin_success ON  feature_checkin_success.feature_checkin_question_id = feature_checkin_question.id AND user_account_id = :ciuid ";
				$vars['ciuid'] = $this->userCheckedinInformation->getId();
				$select[] = " GROUP_CONCAT(feature_checkin_question.id) AS has_feature_checkin_question_ids ";
				$select[] = " GROUP_CONCAT(feature_checkin_success.feature_checkin_question_id) AS has_answered_feature_checkin_question_ids ";
			}
		}
		
		if ($this->event) {
			$joins[] = " JOIN feature_has_event ON feature_has_event.feature_id = feature.id ";
			$where[] = "  feature_has_event.event_id = :event_id ";
			$vars['event_id'] = $this->event->getId();
		}
		
		if ($joinContent) {
			array_unshift($joins," JOIN feature_content ON feature_content.feature_id = feature.id AND feature_content.approved_at IS NOT NULL ");
		} else if ($leftJoinContent) {
			array_unshift($joins," LEFT JOIN feature_content ON feature_content.feature_id = feature.id AND feature_content.approved_at IS NOT NULL ");
		}
		if ($joinItems) {
			array_unshift($joins, " JOIN item ON item.feature_id = feature.id AND item.deleted = 0");
		} else if ($leftJoinItems) {
			array_unshift($joins, " LEFT JOIN item ON item.feature_id = feature.id AND item.deleted = 0");
		}
		if ($joinQuestions) {
			array_unshift($joins, " JOIN feature_checkin_question  ON feature_checkin_question.feature_id = feature.id AND feature_checkin_question.deleted = 0 ");
		} else if ($leftJoinQuestions) {
			array_unshift($joins, " LEFT JOIN feature_checkin_question  ON feature_checkin_question.feature_id = feature.id AND feature_checkin_question.deleted = 0 ");
		}


		$sql = "SELECT ".implode(" , ", $select).
			" FROM feature ".implode(" ", $joins).(count($where) > 0 ? " WHERE ".implode(" AND ", $where) : "")." GROUP BY feature.id";
		if ($this->sortBy == 'questionSortOrder') {
			$sql .= " ORDER BY feature_checkin_question.sort_order DESC";
		} else {
			
		}
		$stat = $db->prepare($sql);
		//die($sql);
		$stat->execute($vars);
		while($d = $stat->fetch(PDO::FETCH_ASSOC)) {
			$this->results[] = $d;
		}
		$this->searchDone = true;
	}
	
	public function nextResult() {
		if (!$this->searchDone) $this->execute();
		$d = array_shift($this->results);
		if ($d) {
			if ($this->userCheckedinInformation) {
				$questionIDs = explode(",",$d['has_feature_checkin_question_ids']);
				$answeredQuestionIDs = explode(",", $d['has_answered_feature_checkin_question_ids']);
				$d['has_user_answered_all_questions'] = $this->areAllQuestionsAnswered($questionIDs, $answeredQuestionIDs);
			}
			return new Feature($d);
		}
	}
	
	public function areAllQuestionsAnswered($questionIDs, $answeredQuestionIDs) {
		if (count($questionIDs) == 0) return true;
		foreach($questionIDs as $qID) {
			if (!in_array($qID, $answeredQuestionIDs)) {
				return false;
			}
		}
		return true;		
	}
}
	
