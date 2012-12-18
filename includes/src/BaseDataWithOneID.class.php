<?php
/**
 * @author James Baster  <james@jarofgreen.co.uk>
 * @copyright City of Edinburgh Council & James Baster
 * @license Open Source under the 3-clause BSD License
 * @url https://github.com/City-Outdoors/City-Outdoors-Web
 */


abstract class BaseDataWithOneID {
	
	protected $id;
	
	public function __construct($data) {
		if ($data && isset($data['id'])) $this->id = $data['id'];
	}
	
	public function getId() { return $this->id; }
	
}
