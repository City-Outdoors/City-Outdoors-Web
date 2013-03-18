<?php
/**
 * @author James Baster  <james@jarofgreen.co.uk>
 * @copyright City of Edinburgh Council & James Baster
 * @license Open Source under the 3-clause BSD License
 * @url https://github.com/City-Outdoors/City-Outdoors-Web
 */


function my_autoload($class_name){
	$inc = dirname(__FILE__);	
	if ($class_name == 'TwitterOAuth') {
		require $inc.'/../libs/twitteroauth/twitteroauth.php';
	} else if ($class_name == 'Twilio' || $class_name == 'Services_Twilio') {
		require $inc.'/../libs/twilio/Twilio.php';	
	} else if ($class_name == 'DOMPDF') {
		require $inc.'/../libs/dompdf/dompdf_config.inc.php';
		DOMPDF_autoload($class_name);
	} else if(file_exists($inc."/".$class_name.'.class.php')){
		require_once($inc."/".$class_name.'.class.php');
	} else if(file_exists($inc."/../libs/dompdf/include/".mb_strtolower($class_name).'.cls.php')){
		require_once($inc."/../libs/dompdf/include/".mb_strtolower($class_name).'.cls.php');
	}
}
spl_autoload_register("my_autoload");

$CONFIG = new Config();
// in the file that called this, the right config file settings will be loaded

if (isset($_POST) && isset($_POST['acceptCookieContinue'])) {
	setcookie("acceptCookies", "yes", time()+60*60*24*365);
	$_COOKIE['acceptCookies'] = 'yes';
}

/** we store all times as UTC then convert them to the users choosen time zone on display. **/
date_default_timezone_set('UTC');


$DB_CONNECTION = null;
/** @return PDO **/
function getDB() {
	global $DB_CONNECTION, $CONFIG;
	if (!$DB_CONNECTION) {
		$DB_CONNECTION = new PDO($CONFIG->DB_DSN, $CONFIG->DB_USERNAME, $CONFIG->DB_PASSWORD);
		$DB_CONNECTION->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
		$DB_CONNECTION->exec("SET NAMES 'utf8'");
	}
	return $DB_CONNECTION;
}


/** @return Smarty **/
function getSmarty(User $user = null) {
	global $CURRENT_USER, $CONFIG, $_COOKIE;
	require_once dirname(__FILE__).'/../libs/smarty/Smarty.class.php';
	$s = new Smarty();
	$templateDirs = array();
	if (is_array($CONFIG->EXTENSIONS)) {
		foreach($CONFIG->EXTENSIONS as $extensionName) {
			$templateDirs[] = dirname(__FILE__) . '/../../extension.'.$extensionName."/includes/templates/";
		}
	}
	$templateDirs[] = dirname(__FILE__) . '/../templates/';
	$s->template_dir = $templateDirs;
	$s->compile_dir = dirname(__FILE__) . '/../smarty_c/';
	$s->assign('Config',$CONFIG);
	$s->assign('httpHost',$CONFIG->HTTP_HOST);
	$s->assign('httpsHost',$CONFIG->HTTPS_HOST);
	$s->assign('assetsVersion',$CONFIG->ASSETS_VERSION);
	$s->assign('mostSecureDomain',($CONFIG->HTTPS_AVAILABLE ?  'https://'. $CONFIG->HTTPS_HOST : 'http://'. $CONFIG->HTTP_HOST));
	$s->assign('siteTitle',$CONFIG->SITE_TITLE);
	$s->assign('mainCollectionSlug',$CONFIG->MAIN_COLLECTION_SLUG);
	$s->assign('currentUser',$user);
	$s->assign('CSFRToken',isset($_SESSION['CSFRToken']) ? $_SESSION['CSFRToken'] : '');
	if (isset($_SESSION['okMessage'])) {
		$s->assign('okMessage',$_SESSION['okMessage']);
		unset($_SESSION['okMessage']);
	} else {
		$s->assign('okMessage',null);
	}
	if (isset($_SESSION['okMessageBlock'])) {
		$s->assign('okMessageBlock',$_SESSION['okMessageBlock']);
		unset($_SESSION['okMessageBlock']);
	} else {
		$s->assign('okMessageBlock',null);
	}
	if (isset($_SESSION['errorMessage'])) {
		$s->assign('errorMessage',$_SESSION['errorMessage']);
		unset($_SESSION['errorMessage']);
	} else {
		$s->assign('errorMessage',null);
	}       
	if (isset($_SESSION['errorMessageBlock'])) {
		$s->assign('errorMessageBlock',$_SESSION['errorMessageBlock']);
		unset($_SESSION['errorMessageBlock']);
	} else {
		$s->assign('errorMessageBlock',null);
	}       
	$s->assign('canMakeFeatureContent',  FeatureContent::canCreate($user));
	$s->assign('canMakeReport',  true);
	$s->assign('inHomeTab',false);
	$s->assign('inWhatsOnTab',false);
	$s->assign('inWildlifeTab',false);
	$s->assign('inCollectionTab',false);
	$s->assign('inCollectionId',false);
	$s->assign('inFieldContentsSlug', false);
	$s->assign('inMap',false);
	$s->registerClass('CMSContent','CMSContent');
	$s->registerClass('SmartyHelper','SmartyHelper');
	
	if (isset($_COOKIE['acceptCookies']) && $_COOKIE['acceptCookies'] == 'yes') {
		$s->assign('showCookieInfo',false);
	} else {
		$s->assign('showCookieInfo',true);
	}
	$collections = array();
	$collectionSearch = new CollectionSearch();
	while($collection = $collectionSearch->nextResult()) $collections[] = $collection;
	$s->assign('collections',$collections);
	
	return $s;
}

/** @return Smarty **/
function getEmailSmarty() {
	global $CONFIG;
	require_once dirname(__FILE__).'/../libs/smarty/Smarty.class.php';
	$s = new Smarty();
	$templateDirs = array();
	if (is_array($CONFIG->EXTENSIONS)) {
		foreach($CONFIG->EXTENSIONS as $extensionName) {
			$templateDirs[] = dirname(__FILE__) . '/../../extension/'.$extensionName."/includes/templates/";
		}
	}
	$templateDirs[] = dirname(__FILE__) . '/../templates/';
	$s->template_dir = $templateDirs;
	$s->compile_dir = dirname(__FILE__) . '/../smarty_c/';
	$s->assign('httpHost',$CONFIG->HTTP_HOST);
	$s->assign('httpsHost',$CONFIG->HTTPS_HOST);
	$s->assign('siteTitle',$CONFIG->SITE_TITLE);
	return $s;
}

/** @return User **/ 
function getCurrentUser() {
	if (!session_id()) session_start();
	if (isset($_SESSION['userID']) && $_SESSION['userID']) {
		$user = User::loadByID($_SESSION['userID']);	
		return $user && $user->isEnabled() ? $user : null;
	}	
}

/** @return User **/
function mustBeLoggedIn() {
	$user = getCurrentUser();
	if ($user) {
		return $user;
	} else {
		header("Location: /");
		die();
	}
}

function logInUser(User $user) {
	if (!$user->isEnabled()) throw new Exception("User is not Emabled!");
	if (!session_id()) session_start();
	$_SESSION['userID'] = $user->getId();	
	$_SESSION['CSFRToken'] =  getRandomString(mt_rand(10,40));
		
	if (isset($_SESSION['favourite']) && is_array($_SESSION['favourite'])) {
		foreach($_SESSION['favourite'] as $fid) {
			$feature = Feature::findByID($fid);
			if ($feature) $feature->favourite($user, null, $_SERVER['HTTP_USER_AGENT'], $_SERVER['REMOTE_ADDR']);
		}
		unset($_SESSION['favourite']);
	}
}

/** The output of this is used for both system calls like crypt() and human things, 
 *  so we use alpha-numeric only and remove all confusing ones */
function generateRandomString($length=10) {
	$charset='ABCDEFGHJKMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz23456789';
    $str = '';
    $count = strlen($charset);
    while ($length--) {
        $str .= $charset[mt_rand(0, $count-1)];
    }
    return $str;
}

setlocale(LC_ALL, 'en_US.UTF8');
function generateSlug($str) {
	$clean = iconv('UTF-8', 'ASCII//TRANSLIT', $str);
	$clean = preg_replace("/[^a-zA-Z0-9\/_| -]/", '', $clean);
	$clean = strtolower(trim($clean, '-'));
	$clean = preg_replace("/[\/_| -]+/", '-', $clean);
	return $clean;
}

function getRandomString($length=40) {
    $characters = '23456789abcdefghjkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ';
    $string = '';
    for ($p = 0; $p < $length; $p++) $string .= $characters[mt_rand(0, strlen($characters)-1)];
    return $string;
}

$LOG_FILE_HANDLER = null;

function prepareLogging() {
	global $LOG_FILE_HANDLER, $CONFIG;	
	if ($CONFIG->LOG_FILE && !$LOG_FILE_HANDLER) {
		$LOG_FILE_HANDLER = fopen($CONFIG->LOG_FILE,"a");
	}
}

function logInfo($msg) {
	global $LOG_FILE_HANDLER, $CONFIG;	
	if ($CONFIG->LOG_INFO) {
		if (isset($_SERVER) && isset($_SERVER['REMOTE_ADDR'])) $msg = $_SERVER['REMOTE_ADDR']." ".$msg;
		$msg = date("c")." ".$msg;
		if ($CONFIG->LOG_FILE) {
			prepareLogging();
			fwrite($LOG_FILE_HANDLER,"INFO ".$msg."\n");		
		}
	}	
}

function logDebug($msg) {
	global $LOG_FILE_HANDLER, $CONFIG;	
	if ($CONFIG->LOG_DEBUG) {
		if (isset($_SERVER) && isset($_SERVER['REMOTE_ADDR'])) $msg = $_SERVER['REMOTE_ADDR']." ".$msg;
		$msg = date("c")." ".$msg;
		if ($CONFIG->LOG_FILE) {
			prepareLogging();		
			fwrite($LOG_FILE_HANDLER,"DEBUG ".$msg."\n");		
		}
	}		
}

function scriptShutDown() {
	global $_SERVER;
	if (isset($_SERVER['REQUEST_URI'])) {
		logDebug("Page ".$_SERVER['REQUEST_URI']. " finished with peak memory ".number_format(memory_get_peak_usage()));
	} else if (isset($_SERVER['SCRIPT_FILENAME'])) {
		logDebug("Script ".$_SERVER['SCRIPT_FILENAME']. " finished with peak memory ".number_format(memory_get_peak_usage()));
	}
}
register_shutdown_function('scriptShutDown');

