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
			'HTTP_HOST'=>"",
			'HTTPS_HOST'=>"",
			'HTTPS_AVAILABLE'=>false,
			'MAP_STARTING_MIN_LAT'=>55.878290,
			'MAP_STARTING_MAX_LAT'=>55.993363,
			'MAP_STARTING_MIN_LNG'=>-3.314781,
			'MAP_STARTING_MAX_LNG'=>-3.044586,
			'MAIN_COLLECTION_SLUG'=>null,
			'LAT_ACCURACY'=>0.000005,
			'LNG_ACCURACY'=>0.000005,
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
			'MAIN_COLLECTION_SLUG'=>'parks',
			'HIDDEN_COLLECTION_SLUG'=>'public-toilets',
			'MAP_MIN_ZOOM'=>10,
			'MAP_MAX_ZOOM'=>19,
			'LOG_FILE'=>'',
			'LOG_INFO'=>true,
			'LOG_DEBUG'=>true,
			'ASSETS_VERSION'=>1,
			'FACEBOOK_LINK'=>'https://www.facebook.com/'
		);
	
	
	public function load($filename) {
		$this->data = array_merge($this->data, parse_ini_file($filename));
	}
	
	public function __get($key) { return $this->data[$key];	 }
	public function __set($key,$val) { $this->data[$key] = $val;	}
	public function __isset($key) { return isset($this->data[$key]);	}
	
}
