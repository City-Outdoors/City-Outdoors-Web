<?php
/**
 * @author James Baster  <james@jarofgreen.co.uk>
 * @copyright City of Edinburgh Council & James Baster
 * @license Open Source under the 3-clause BSD License
 * @url https://github.com/City-Outdoors/City-Outdoors-Web
 */


class RenderCMSContentByMonth {

	protected $month;
	protected $blockName;
	
	public function __construct($blockName, $month) {
		$this->month = $month;
		$this->blockName = $blockName;
	}
	
	public function getSmarty(User $currentUser = null) {
		$page = CMSContent::loadBlockBySlug($this->blockName."-".$this->month);
		if (!$page) throw new Exception("No Page");
		
		$tpl = getSmarty($currentUser);
		$tpl->assign("page",$page);
		$tpl->assign("thisMonth",$this->month);
		$tpl->assign("nowMonth", date("n"));
		


		$monthNames = array(
				1=>"January",
				2=>"February",
				3=>"March",
				4=>"April",
				5=>"May",
				6=>"June",
				7=>"July",
				8=>"August",
				9=>"September",
				10=>"October",
				11=>"November",
				12=>"December",
			);
		$tpl->assign("monthName",  $monthNames[$this->month]);

		$monthSlug = array(
				1=>"jan",
				2=>"feb",
				3=>"mar",
				4=>"apr",
				5=>"may",
				6=>"jun",
				7=>"jul",
				8=>"aug",
				9=>"sep",
				10=>"oct",
				11=>"nov",
				12=>"dec",
			);
		$tpl->assign("monthSlug",  $monthSlug[$this->month]);
		return $tpl;

	}
	
}

