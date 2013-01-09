<?php
/**
 * @author James Baster  <james@jarofgreen.co.uk>
 * @copyright City of Edinburgh Council & James Baster
 * @license Open Source under the 3-clause BSD License
 * @url https://github.com/City-Outdoors/City-Outdoors-Web
 */

class FeatureContent extends BaseDataWithOneID {

	private $feature_id;
	private $user_id;
	private $comment_body;
	private $created_at;
	private $approved_at;
	private $rejected_at;
	private $created_by;
	private $approved_by;
	private $rejected_by;
	private $display_name;
	private $is_report;
	private $created_name;
	private $created_email;
	private $promoted;
	
	private $picture_full_filename;
	private $picture_normal_filename;
	private $picture_thumb_filename;
	
	
	public static function canCreate(User $user=null) {
		global $CONFIG;
		if ($CONFIG->SUBMIT_CONTENT_ANYONE > -1) return true;
		if ($user && $CONFIG->SUBMIT_CONTENT_USERS > -1) return true;
		if ($user && $user->isAdministrator() && $CONFIG->SUBMIT_CONTENT_ADMINISTRATORS > -1) return true;
		return false;
	}

	public static function isCreatedContentModerated(User $user=null) {
		global $CONFIG;
		if ($CONFIG->SUBMIT_CONTENT_ANYONE == 1) return false;
		if ($user && $CONFIG->SUBMIT_CONTENT_USERS == 1) return false;		
		if ($user && $user->isAdministrator() && $CONFIG->SUBMIT_CONTENT_ADMINISTRATORS == 1) return false;
		return true;
	}
	

	/** @return FeatureContent **/
	public static function loadByID($id) {
		$db = getDB();
		$stat = $db->prepare('SELECT feature_content.*, '.
				' feature_content_image.full_filename AS picture_full_filename, feature_content_image.normal_filename AS picture_normal_filename, feature_content_image.thumb_filename AS picture_thumb_filename '.
				' , user_account.display_name AS display_name '.
				' FROM feature_content '.
				' LEFT JOIN feature_content_image ON feature_content_image.feature_content_id = feature_content.id '.
				' LEFT JOIN user_account ON user_account.id = feature_content.created_by '.
				'WHERE feature_content.id=:id');
		$stat->bindValue('id', $id);
		$stat->execute();
		if($stat->rowCount() == 1) {
			return new FeatureContent($stat->fetch(PDO::FETCH_ASSOC));
		}		
	}		
	
	public function __construct($data) {
		parent::__construct($data);
		if ($data && isset($data['feature_id'])) $this->feature_id = $data['feature_id'];
		if ($data && isset($data['user_id'])) $this->user_id = $data['user_id'];
		if ($data && isset($data['comment_body'])) $this->comment_body = $data['comment_body'];
		if ($data && isset($data['created_at'])) $this->created_at = $data['created_at'];
		if ($data && isset($data['approved_at'])) $this->approved_at = $data['approved_at'];
		if ($data && isset($data['rejected_at'])) $this->rejected_at = $data['rejected_at'];
		if ($data && isset($data['created_by'])) $this->created_by = $data['created_by'];
		if ($data && isset($data['created_name'])) $this->created_name = $data['created_name'];
		if ($data && isset($data['created_email'])) $this->created_email = $data['created_email'];
		if ($data && isset($data['approved_by'])) $this->approved_by = $data['approved_by'];
		if ($data && isset($data['rejected_by'])) $this->rejected_by = $data['rejected_by'];
		if ($data && isset($data['picture_full_filename'])) $this->picture_full_filename = $data['picture_full_filename'];
		if ($data && isset($data['picture_normal_filename'])) $this->picture_normal_filename = $data['picture_normal_filename'];
		if ($data && isset($data['picture_thumb_filename'])) $this->picture_thumb_filename = $data['picture_thumb_filename'];
		if ($data && isset($data['display_name'])) $this->display_name = $data['display_name'];
		if ($data && isset($data['promoted'])) $this->promoted = $data['promoted'];
		if ($data && isset($data['is_report'])) $this->is_report = $data['is_report'];
	}
	

	public function getBody() { return $this->comment_body; }	
	public function getDisplayName() { 
		return $this->created_by? $this->display_name : ($this->created_name? $this->created_name : "Anonymous"); 
	}	
	public function getFeature() {
		return Feature::loadByID($this->feature_id);
	}
	public function getFeatureId() { return $this->feature_id; }
	public function isApproved() { return (boolean)$this->approved_at; }
	public function isRejected() { return (boolean)$this->rejected_at; }
	public function isPromoted() { return (boolean)$this->promoted; }
	public function isReport() { return (boolean)$this->is_report; }
	
	public function getCreatedAt() { return $this->created_at; }
	public function getCreatedBy() { return $this->created_by; }	
	public function getCreatedEmail() { 
		// email can always be provided and override user options (for twitter users)
		if ($this->created_email) return $this->created_email;
		// get from user
		if ($this->created_by) {
			$user = User::loadByID($this->created_by); // TODO cache result of this on the object
			return $user->getEmail();
		}
		// no idea :-(
		return '';
	}	
	
	/** @return Boolean true if user account on this site, false if anonymous user **/
	public function hasAuthor() {return (boolean)$this->created_by;  }
	public function getAuthor() {return User::loadByID($this->created_by);  }
	public function getAuthorID() {return $this->created_by;  }
	public function hasPicture() {return (boolean)$this->picture_full_filename;  }
	public function getFullPictureURL() { return '/content/'.$this->picture_full_filename; }
	public function getNormalPictureURL() { return '/content/'.$this->picture_normal_filename; }
	public function getThumbPictureURL() { return '/content/'.$this->picture_thumb_filename; }
		
	public function approve(User $user) {
		$db = getDB();
		$stat = $db->prepare("UPDATE feature_content SET approved_at=:at, approved_by=:by, rejected_at=null, rejected_by=null WHERE id=:id");
		$stat->execute(array('id'=>$this->id, 'at'=>date('Y-m-d H:i:s'), 'by'=>$user->getId()));
		$this->approved_at = time();
		$this->approved_by = $user->getId();
		$this->rejected_at = $this->rejected_by = null;
	}
	
	public function disapprove(User $user) {
		$db = getDB();
		$stat = $db->prepare("UPDATE feature_content SET approved_at=null, approved_by=null, rejected_at=:at, rejected_by=:by WHERE id=:id");
		$stat->execute(array('id'=>$this->id, 'at'=>date('Y-m-d H:i:s'), 'by'=>$user->getId()));		
		$this->approved_at = $this->approved_by = null;
		$this->rejected_at = time();
		$this->rejected_by = $user->getId();		
	}

	public function updateBody($body) {
		$db = getDB();
		$stat = $db->prepare("UPDATE feature_content SET comment_body=:b WHERE id=:id");
		$stat->execute(array('id'=>$this->id,  'b'=>$body));
		$this->comment_body = $body;
	}

	public function updateCreatedName($name) {
		$db = getDB();
		$stat = $db->prepare("UPDATE feature_content SET created_name=:b WHERE id=:id");
		$stat->execute(array('id'=>$this->id,  'b'=>$name));
		$this->created_name = $name;
	}
		
	public function sendReport() {
		global $CONFIG;
		if (!$this->is_report || !$CONFIG->EMAIL_REPORTS_TO) return;
		
		$tpl = getEmailSmarty();
		$tpl->assign('report',$this);
		$tpl->assign('feature',$this->getFeature());
		$tpl->assign('user',$this->created_by ? User::loadByID($this->created_by)  : null);
		$body = $tpl->fetch('report.email.txt');
		
		//var_dump($body);die();
		mail($CONFIG->EMAIL_REPORTS_TO, "New Report", $body, "From: ".$CONFIG->EMAILS_FROM);
	}
		
	public function newImage($originalFileName, $serverFileName, $fromUserUpload = true) {
		## check file
		$extensionBits = explode(".", $originalFileName);
		$extension = strtolower(array_pop( $extensionBits ));
		if (!in_array($extension,array('png','jpg','jpeg','gif'))) {
			throw new Exception("That does not appear to be an image file! It has the extension ".$extension);
		}

		list($width, $height, $type, $attr)= getimagesize($serverFileName);
		if ($width < 3 || $height < 3) {
			throw new Exception("That image file is too small");
		}			
		if (!in_array($type, array(IMAGETYPE_GIF, IMAGETYPE_PNG, IMAGETYPE_JPEG, IMAGETYPE_JPEG2000))) {
			throw new Exception("not an image file!");
		}
		
		## find new names
		$rootContentFolder = dirname(__FILE__).DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."content".DIRECTORY_SEPARATOR;
		$tmp = date("Y").DIRECTORY_SEPARATOR.date("M").DIRECTORY_SEPARATOR.date("d").DIRECTORY_SEPARATOR;
		if (!file_exists($rootContentFolder.$tmp)) mkdir($rootContentFolder.$tmp,0755,true);
		do {
			$tmp2 = $tmp.getRandomString(mt_rand(2,30));
			$fullFileName = $tmp2.".full.".$extension;
			$normalFileName = $tmp2.".normal.jpg";
			$thumbFileName = $tmp2.".thumb.jpg";
		} while (file_exists($rootContentFolder.$fullFileName) || file_exists($rootContentFolder.$normalFileName) || file_exists($rootContentFolder.$thumbFileName));
		
		## Move original
		if ($fromUserUpload) {
			if (!move_uploaded_file($serverFileName, $rootContentFolder.$fullFileName)) {
				throw new Exception("Possible file upload attack!");
			}
		} else {
			if (!rename($serverFileName, $rootContentFolder.$fullFileName)) {
				throw new Exception("Failed to move file");			
			}
		}

		$this->saveSmallerImage(
					$rootContentFolder.$fullFileName, 
					array(400=>$rootContentFolder.$normalFileName,100=>$rootContentFolder.$thumbFileName)
				);
		
		$db = getDB();
		$data = array(
				'feature_content_id'=>$this->id,
				'full_filename'=>$fullFileName,
				'normal_filename'=>$normalFileName,
				'thumb_filename'=>$thumbFileName,
			);		
		$stat = $db->prepare('INSERT INTO feature_content_image (feature_content_id,full_filename,normal_filename,thumb_filename) '.
			'VALUES (:feature_content_id,:full_filename,:normal_filename,:thumb_filename)');

		$stat->execute($data);		
		
		$this->picture_full_filename = $fullFileName;
		$this->picture_normal_filename = $normalFileName;
		$this->picture_thumb_filename = $thumbFileName;
		
	}

	protected function saveSmallerImage($originalFileName, $newData) {
		$extensionBits = explode(".", $originalFileName);
		$extension = strtolower(array_pop( $extensionBits ));
		list($width, $height, $type, $attr)= getimagesize($originalFileName);
		$imgratio = floatval($height) / floatval($width);
		switch ($extension) {
			case "jpg": case "jpeg":
				$image = imagecreatefromjpeg($originalFileName);
				break;
			case "png":
				$image = imagecreatefrompng($originalFileName);
				break;
			case "gif":
				$image = imagecreatefromgif($originalFileName);
				break;						
			default:
				$image = imagecreatetruecolor($new_width, $new_height);
		}
		
		foreach($newData as $newSize=>$newFileName) {
			$scale = max(1,max($width/$newSize, $height/$newSize));
			list($new_width, $new_height) = array(intval($width/$scale), intval($height/$scale));
			$image_p = imagecreatetruecolor($new_width, $new_height);
			imagecopyresampled($image_p, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
			if (!imagejpeg($image_p, $newFileName)) {
				throw new Exception("Creating smaller image failed for some reason!");
			}
		}
		
	}	
	
	public function promote(User $user) {
		$db = getDB();
		$stat = $db->prepare("UPDATE feature_content SET promoted=1 WHERE id=:id");
		$stat->execute(array('id'=>$this->id));
		$this->promoted = 1;
	}
	
	public function demote(User $user) {
		$db = getDB();
		$stat = $db->prepare("UPDATE feature_content SET promoted=0 WHERE id=:id");
		$stat->execute(array('id'=>$this->id));
		$this->promoted = 0;
	}
	
	
}
	
	
