<?php
/**
 * @author James Baster  <james@jarofgreen.co.uk>
 * @copyright City of Edinburgh Council & James Baster
 * @license Open Source under the 3-clause BSD License
 * @url https://github.com/City-Outdoors/City-Outdoors-Web
 */

require '../includes/src/global.php';


$currentUser = mustBeLoggedIn();
if (!$currentUser->isAdministrator()) die('No Access');

$folder = realpath(dirname(__FILE__).DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."content").DIRECTORY_SEPARATOR."uploads".DIRECTORY_SEPARATOR;
if (!file_exists($folder)) mkdir ($folder);
		
$validImageExtensions = array('png','gif','jpg','jpeg');
$validAudioExtensions = array('mp3');

$tpl = getSmarty($currentUser);

if ($_POST && $_POST['CSFRToken'] == $_SESSION['CSFRToken'] && isset($_FILES['picture']['error']) && $_FILES['picture']['error'] === UPLOAD_ERR_OK) {
	$extensionBits = explode(".", $_FILES['picture']['name']);
	$extension = strtolower(array_pop( $extensionBits ));
	if (in_array($extension,$validImageExtensions)) {
		list($width, $height, $type, $attr)= getimagesize($_FILES['picture']['tmp_name']);
		if (in_array($type, array(IMAGETYPE_GIF, IMAGETYPE_PNG, IMAGETYPE_JPEG, IMAGETYPE_JPEG2000))) {
			move_uploaded_file($_FILES['picture']['tmp_name'], $folder.DIRECTORY_SEPARATOR.time()."_".$_FILES['picture']['name']);
		} else {
			$tpl->assign('errorMessage','Failed: File not a image!');	
		}
	} else if (in_array($extension,$validAudioExtensions)) {
		move_uploaded_file($_FILES['picture']['tmp_name'], $folder.DIRECTORY_SEPARATOR.time()."_".$_FILES['picture']['name']);
	} else {
		$tpl->assign('errorMessage','Failed: File name not recognised!');		
	}
}


$outImages = array();
$outAudios = array();
if ($handle = opendir($folder)) {
    /* This is the correct way to loop over the directory. */
    while (false !== ($entry = readdir($handle))) {
		if ($entry != '.' && $entry != '..') {
			$bits = explode(".", $entry);
			$ext = array_pop($bits);
			if (in_array(strtolower($ext), $validImageExtensions)) {
				$outImages[] = $entry;
			}
			if (in_array(strtolower($ext), $validAudioExtensions)) {
				$outAudios[] = $entry;
			}
		}
    }
    closedir($handle);
}
$tpl->assign('uploadsImages',$outImages);
$tpl->assign('uploadsAudios',$outAudios);

$tpl->display('admin/uploads.htm');

