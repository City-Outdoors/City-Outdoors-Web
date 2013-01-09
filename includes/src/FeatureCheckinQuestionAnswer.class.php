<?php
/**
 * @author James Baster  <james@jarofgreen.co.uk>
 * @copyright City of Edinburgh Council & James Baster
 * @license Open Source under the 3-clause BSD License
 * @url https://github.com/City-Outdoors/City-Outdoors-Web
 */

class FeatureCheckinQuestionAnswer extends BaseDataWithOneID {

	protected $user_account_id;
	protected $answer_given;
	protected $score;
	
	public function __construct($data) {
		parent::__construct($data);
		if ($data && isset($data['answer_given'])) $this->answer_given = $data['answer_given'];
		if ($data && isset($data['user_account_id'])) $this->user_account_id = $data['user_account_id'];
		if ($data && isset($data['score'])) $this->score = $data['score'];
	}
	
	public function getAnswerGiven() { return $this->answer_given; }
	public function getUserAccountID() { return $this->user_account_id; }
	public function getScore() { return $this->score; }
	
}

