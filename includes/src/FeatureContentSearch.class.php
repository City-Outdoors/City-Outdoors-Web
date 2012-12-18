<?php
/**
 * @author James Baster  <james@jarofgreen.co.uk>
 * @copyright City of Edinburgh Council & James Baster
 * @license Open Source under the 3-clause BSD License
 * @url https://github.com/City-Outdoors/City-Outdoors-Web
 */

class FeatureContentSearch extends BaseSearch {
	
	
	public $featureIDs = array();
	public $state = '';	
	/** 0= dosn't care, 1=yes, -1 no **/
	public $hasImages = 0;
	
	/** 0= doesn't care, 1=yes, -1 no **/
	public $is_report = -1;
	
	/** 0= doesn't care, 1=yes, -1 no **/
	public $promoted = 0;
	
	public function  __construct() {
		$this->className = "FeatureContent";
	}
	
	public function forFeature(Feature $feature) {
		$this->featureIDs[] = $feature->getId();
	}

	/** @var User **/
	private $byUser;
	
		
	public function byUser(User $user) {
		$this->byUser = $user;
		
	}
	
	public function hasImages() { $this->hasImages = 1; }
	public function hasNoImages() { $this->hasImages = -1; }
	
	public function isReport() { $this->is_report = 1; }
	public function isNotReport() { $this->is_report = -1; }
	public function isReportOrNotReport() { $this->is_report = 0; }
	
	public function approvedOnly() { $this->state = 'APPROVED'; }
	public function toModerateOnly() { $this->state = 'TOMODERATE'; }
	
	public function promotedOnly() {
		$this->promoted = 1;
	}
	
	protected function execute() {
		if ($this->searchDone) throw new Exception("Search already done!");
		$db = getDB();
		$where = array();
		$joins = array();
		$vars = array();

		if ($this->featureIDs) {
			$where[] = " feature_content.feature_id IN (".implode(",", $this->featureIDs).")";
		}
		
		if ($this->byUser) {
			$where[] = " feature_content.created_by  = :uid";
			$vars['uid'] = $this->byUser->getId();
		}

		if ($this->state == 'APPROVED') {
			$where[] = " feature_content.approved_at IS NOT NULL ";
		} else if ($this->state == 'TOMODERATE') {
			$where[] = " feature_content.approved_at IS NULL AND feature_content.rejected_at IS NULL ";
		}
		
		if ($this->hasImages == 1) {
			$where[] = " feature_content_image.full_filename IS NOT NULL ";
		} else if ($this->hasImages == -1) {
			$where[] = " feature_content_image.full_filename IS NULL ";
		}
		
		if ($this->is_report == 1) {
			$where[] = " feature_content.is_report = 1 ";
		} else if ($this->is_report == -1) {
			$where[] = " feature_content.is_report = 0 ";
		}
		if ($this->promoted == 1) {
			$where[] = " feature_content.promoted = 1 ";
		} else if ($this->promoted == -1) {
			$where[] = " feature_content.promoted = 0 ";
		}

		$sql = "SELECT feature_content.*, user_account.display_name AS display_name, ".
			"feature_content_image.full_filename AS picture_full_filename, feature_content_image.normal_filename AS picture_normal_filename, feature_content_image.thumb_filename AS picture_thumb_filename ".
			"FROM feature_content ".
			"LEFT JOIN feature_content_image ON feature_content_image.feature_content_id = feature_content.id ".
			"LEFT JOIN user_account ON user_account.id = feature_content.created_by ".
			implode(" ", $joins).
			(count($where) > 0 ? " WHERE ".implode(" AND ", $where) : "").
			" GROUP BY feature_content.id";
		$stat = $db->prepare($sql);
		$stat->execute($vars);
		while($d = $stat->fetch(PDO::FETCH_ASSOC)) {
			$this->results[] = $d;
		}
		$this->searchDone = true;
	}
	
}
	
