<?php
/**
 * @author James Baster  <james@jarofgreen.co.uk>
 * @copyright City of Edinburgh Council & James Baster
 * @license Open Source under the 3-clause BSD License
 * @url https://github.com/City-Outdoors/City-Outdoors-Web
 */


require_once dirname(__FILE__).'/../libs/smarty/plugins/modifier.ordinal.php';


class SmartyModifierOrdinalTest extends PHPUnit_Framework_TestCase {
	/**
	* @dataProvider provider
	*/
	public function test1($in, $out) {
		$this->assertEquals($out, smarty_modifier_ordinal($in));
	}

	public function provider() {
		return array(
			array(1, 'st'),
			array(2, 'nd'),
			array(3, 'rd'),
			array(4, 'th'),
			array(5, 'th'),
			array(6, 'th'),
			array(7, 'th'),
			array(8, 'th'),
			array(9, 'th'),
			array(10, 'th'),
			array(11, 'th'),
			array(12, 'th'),
			array(13, 'th'),
			array(14, 'th'),
			array(15, 'th'),
			array(16, 'th'),
			array(17, 'th'),
			array(18, 'th'),
			array(19, 'th'),
			array(20, 'th'),
			array(21, 'st'),
			array(22, 'nd'),
			array(23, 'rd'),
			array(24, 'th'),
			array(25, 'th'),
		);
	}	
	
	
}