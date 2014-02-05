<?php
/**
 * @author James Baster  <james@jarofgreen.co.uk>
 * @copyright City of Edinburgh Council & James Baster
 * @license Open Source under the 3-clause BSD License
 * @url https://github.com/City-Outdoors/City-Outdoors-Web
 */

class CMSContentTest extends AbstractTest {
	
	/**
	* @dataProvider providerFilterSlug
	*/
	public function testFilterSlug($in, $out) {
		$this->assertEquals($out, CMSContent::filterSlug($in));
	}

	public function providerFilterSlug() {
		return array(
			array('12345678901234567890123456789012345678901234567890a', '12345678901234567890123456789012345678901234567890'),
			array('cat Dog Hat', 'cat-dog-hat'),
			array('cat', 'cat'),
		);
	}
	
}

