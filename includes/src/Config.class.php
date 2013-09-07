<?php
/**
 * @author James Baster  <james@jarofgreen.co.uk>
 * @copyright City of Edinburgh Council & James Baster
 * @license Open Source under the 3-clause BSD License
 * @url https://github.com/City-Outdoors/City-Outdoors-Web
 */

class Config {
	
	protected $data = array(
			'SITE_TITLE'=>"City Outdoors",
			'DB_DSN'=>"",
			'DB_USERNAME'=>"",
			'DB_PASSWORD'=>"",
			'BCRYPT_ROUNDS'=>5,
			'EMAILS_FROM'=>"test@example.com",
			'EMAIL_REPORTS_TO'=>"",
			'EMAIL_CONTENT_TO_MODERATE_TO'=>"",
			'HTTP_HOST'=>"",
			'HTTPS_HOST'=>"",
			'HTTPS_AVAILABLE'=>false,
			'MAP_STARTING_MIN_LAT'=>55.878290,
			'MAP_STARTING_MAX_LAT'=>55.993363,
			'MAP_STARTING_MIN_LNG'=>-3.314781,
			'MAP_STARTING_MAX_LNG'=>-3.044586,
			'MAIN_COLLECTION_SLUG'=>null,
			'LAT_ACCURACY'=>0.00005,
			'LNG_ACCURACY'=>0.00005,
			'SUBMIT_CONTENT_ANYONE'=>0,
			'SUBMIT_CONTENT_USERS'=>0,
			'SUBMIT_CONTENT_ADMINISTRATORS'=>1,
			'TWITTER_APP_KEY'=>null,
			'TWITTER_APP_SECRET'=>null,
			'TWITTER_USER_KEY'=>null,
			'TWITTER_USER_SECRET'=>null,
			'TWITTER_USERNAME'=>null,
			'GOOGLE_MAP_API_KEY'=>null,
			'GOOGLE_MAP_FEATURE_MAP_WIDTH'=>210,
			'GOOGLE_MAP_FEATURE_MAP_HEIGHT'=>210,
			'GOOGLE_ANALYTICS_CODE'=>null,
			'MAIN_COLLECTION_SLUG'=>null,
			'HIDDEN_COLLECTION_SLUG'=>null,
			'MAP_MIN_ZOOM'=>10,
			'MAP_MAX_ZOOM'=>19,
			'LOG_FILE'=>'',
			'LOG_INFO'=>true,
			'LOG_DEBUG'=>true,
			'ASSETS_VERSION'=>1,
			'FACEBOOK_LINK'=>'https://www.facebook.com/',
			'ALLOW_EDITING_COLLECTION_ITEMS_IN_ADMIN_UI'=>true,
			'EXTENSIONS'=>array(),
			'MAXIMUM_UPLOAD_ALLOWED'=>10485760,
			'LOCAL_TIME_ZONE'=>'Europe/London',
			'EVENT_PAGE_SHOW_FUTURE_EVENTS'=>3,
			'WHATSON_IFRAME_SHOW_FUTURE_EVENTS'=>5,
		);
	
	
	public function load($filename) {
		$this->data = array_merge($this->data, parse_ini_file($filename));
	}
	
	public function __get($key) { 
		if ($key == 'MAXIMUM_UPLOAD_ALLOWED') {
			$upload_limit = min($this->ini_get_in_bytes('upload_max_filesize'), $this->ini_get_in_bytes('post_max_size'), $this->ini_get_in_bytes('memory_limit'));
			return isset($this->data[$key]) ?  min($this->data[$key], $upload_limit) : $upload_limit;
		} else {
			return $this->data[$key];	 
		}
	}
	
	/** With thanks to http://www.php.net/manual/en/function.ini-get.php example 1 **/
	private function ini_get_in_bytes($key) {
		$val = ini_get($key);
		$last = strtolower($val[strlen($val)-1]);
		switch($last) {
			// The 'G' modifier is available since PHP 5.1.0
			case 'g':
				$val *= 1024;
			case 'm':
				$val *= 1024;
			case 'k':
				$val *= 1024;
		}
		return $val;
	}
	
	
	public function __set($key,$val) { $this->data[$key] = $val;	}
	public function __isset($key) { 
		if ($key == 'MAXIMUM_UPLOAD_ALLOWED') {
			// We pull this from the server config so it's always set.
			return true;
		} else {
			return isset($this->data[$key]);	
		}
	}
	
}
