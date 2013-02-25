<?php
/**
 * @author James Baster  <james@jarofgreen.co.uk>
 * @copyright City of Edinburgh Council & James Baster
 * @license Open Source under the 3-clause BSD License
 * @url https://github.com/City-Outdoors/City-Outdoors-Web
 */


class User extends BaseDataWithOneID {
	
	protected $display_name;
	protected $email;
	protected $password_crypted;
	protected $password_salt;

	protected $twitter_id;
	protected $twitter_screen_name;	
	
	protected $profile_url;
	
	protected $enabled;
	protected $administrator;
	protected $system_administrator;
	
	protected $forgotten_password_code;
	
	protected $cached_score;

	/**
	 * @return \User 
	 */
	public static function loadByID($id) {
		$db = getDB();
		$stat = $db->prepare('SELECT * FROM user_account WHERE id=:id');
		$stat->bindValue('id', $id);
		$stat->execute();
		if($stat->rowCount() == 1) {
			return new User($stat->fetch(PDO::FETCH_ASSOC));
		}		
	}
	
	/**
	 * @return \User 
	 */
	public static function loadByIDAndSession($id,$session) {
		$db = getDB();
		$stat = $db->prepare("SELECT user_account.* FROM user_account ".
			"JOIN user_session ON user_session.user_account_id = user_account.id ".
			"WHERE user_account.id=:id AND user_session.id = :session");
		$stat->bindValue('id', $id);
		$stat->bindValue('session', $session);
		$stat->execute();
		if($stat->rowCount() == 1) {
			$user = new User($stat->fetch(PDO::FETCH_ASSOC)); 
			// TODO update last_used_at col on user_session table
			return $user;
		}		
	}	

	/** 
	 *
	 * @param type $id Pass in to use on loading
	 * @param type $screen_name Screen_name may change; so check and if it's different save new one to DB.
	 * @return \User 
	 */
	public static function loadByTwitterID($id,$screen_name) {
		$db = getDB();
		$stat = $db->prepare('SELECT * FROM user_account WHERE twitter_id=:id');
		$stat->bindValue('id', $id);
		$stat->execute();
		if($stat->rowCount() == 1) {
			$data = $stat->fetch(PDO::FETCH_ASSOC);
			if ($data['twitter_screen_name'] != $screen_name) {
				$stat = $db->prepare("UPDATE user_account SET twitter_screen_name = :name WHERE id=:id");
				$stat->execute(array('name'=>$screen_name,'id'=>$data['id']));
			}
			return new User($data);
		}		
	}
	
	/**
	 * @return \User 
	 */
	public static function loadByEmail($email) {
		$db = getDB();
		$stat = $db->prepare('SELECT * FROM user_account WHERE email=:email');
		$stat->bindValue('email', $email);
		$stat->execute();
		if($stat->rowCount() == 1) {
			return new User($stat->fetch(PDO::FETCH_ASSOC));
		}		
	}
	
	/**
	 * @return \User 
	 */	
	public static function createByEmail($email,$password1,$password2,$displayName=null) {
		global $CONFIG;
		if (!filter_var($email, FILTER_VALIDATE_EMAIL)) throw new UserExceptionEmailNotValid("Email not Valid!");
		if ($password1 != $password2) throw new UserExceptionPasswordsDontMatch("Passwords don't match");
		if (strlen($password1) <= 3) throw new UserExceptionPasswordsToShort("Must be longer than 3 characters");
		
		$passwordSalt = generateRandomString(22);
		
		if (!$displayName) {
			$emailBits = explode('@', $email, 2);
			$displayName = $emailBits[0];
		}
		$data = array(
				'display_name' => $displayName,
				'email' => $email,
				'password_crypted' => crypt($password1,'$2a$'.$CONFIG->BCRYPT_ROUNDS.'$'.$passwordSalt),
				'password_salt' => $passwordSalt,
				'created_at' => date('Y-m-d H:i:s')
			);
		
		$db = getDB();
		$stat = $db->prepare('INSERT INTO user_account (display_name, email, password_crypted, password_salt, created_at) '.
			'VALUES (:display_name, :email, :password_crypted, :password_salt, :created_at)');
		
		try {		
			$stat->execute($data);
		} catch (Exception $e) {
			$msg = $e->getMessage();
			if (strpos($msg, 'Integrity constraint violation') > 0 && strpos($msg, 'Duplicate entry') > 0 && strpos($msg, 'user_account_email') > 0) {
				throw new UserExceptionEmailAlreadyKnown('That email address is already in use!');
			} else {
				throw $e;
			}
		}
		$data['id'] = $db->lastInsertId();
		$data['enabled'] = 1;
		return new User($data);
		
	}
	
	/**
	 * @TODO catch duplicate errors
	 * @return \User 
	 */	
	public static function createByTwitter($id,$name,$screen_name, $token,$secret) {
		$data = array(
				'display_name' => $name,
				'created_at' => date('Y-m-d H:i:s'),
				'twitter_id'=>$id,			
				'twitter_screen_name'=>$screen_name,			
				'twitter_token'=>$token,
				'twitter_token_secret'=>$secret
			);
		
		$db = getDB();
		$stat = $db->prepare('INSERT INTO user_account (display_name, twitter_id, twitter_screen_name, twitter_token, twitter_token_secret, created_at) '.
			'VALUES (:display_name, :twitter_id, :twitter_screen_name, :twitter_token, :twitter_token_secret,  :created_at)');
		$stat->execute($data);
		$data['id'] = $db->lastInsertId();
		$data['enabled'] = 1;
		return new User($data);
	}
	
	
	public function __construct($data) {
		parent::__construct($data);
		if ($data && isset($data['display_name'])) $this->display_name = $data['display_name'];
		if ($data && isset($data['email'])) $this->email = $data['email'];
		if ($data && isset($data['password_crypted'])) $this->password_crypted = $data['password_crypted'];
		if ($data && isset($data['password_salt'])) $this->password_salt = $data['password_salt'];
		if ($data && isset($data['enabled'])) $this->enabled = (Boolean)$data['enabled'];
		if ($data && isset($data['administrator'])) $this->administrator = (Boolean)$data['administrator'];
		if ($data && isset($data['system_administrator'])) $this->system_administrator = (Boolean)$data['system_administrator'];
		if ($data && isset($data['forgotten_password_code'])) $this->forgotten_password_code = $data['forgotten_password_code'];
		if ($data && isset($data['twitter_id'])) $this->twitter_id = $data['twitter_id'];
		if ($data && isset($data['twitter_screen_name'])) $this->twitter_screen_name = $data['twitter_screen_name'];
		if ($data && isset($data['profile_url'])) $this->profile_url = $data['profile_url'];
		if ($data && isset($data['cached_score'])) $this->cached_score = $data['cached_score'];
	}	
	
	public function getName() { return $this->display_name; }
	public function getEmail() { return $this->email; }
	public function getTwitterID() { return $this->twitter_id; }
	public function getTwitterScreenName() { return $this->twitter_screen_name; }
	public function getProfileURL() { return $this->profile_url; }
	public function hasProfileURL() { return $this->profile_url && filter_var($this->profile_url, FILTER_VALIDATE_URL); }
	public function isEnabled() { return $this->enabled; }
	public function isAdministrator() { return $this->administrator || $this->system_administrator; }
	public function isSystemAdministrator() { return $this->system_administrator; }
	public function getCachedScore() { return $this->cached_score; }
	
	public function hasPassword() { return (boolean)$this->password_crypted; }
	
	public function checkPassword($password) {
		global $CONFIG;
		return $this->password_crypted ==  crypt($password,'$2a$'.$CONFIG->BCRYPT_ROUNDS.'$'.$this->password_salt);
	}
	
	public function setNewPassword($password1,$password2) {
		global $CONFIG;
		if ($password1 != $password2) throw new UserExceptionPasswordsDontMatch("Passwords don't match");
		if (strlen($password1) <= 3) throw new UserExceptionPasswordsToShort("Must be longer than 3 characters");
		
		$this->password_salt = generateRandomString(22);
		$this->password_crypted = crypt($password1,'$2a$'.$CONFIG->BCRYPT_ROUNDS.'$'.$this->password_salt);
		$this->forgotten_password_code = null;
		
		$db = getDB();
		$stat = $db->prepare('UPDATE user_account SET '.
				'password_salt=:password_salt, password_crypted=:password_crypted, forgotten_password_code=null, forgotten_password_code_generated_at=null '.
				'WHERE id=:id');
		$stat->execute(array('id'=>$this->id,'password_salt'=>$this->password_salt,'password_crypted'=>$this->password_crypted));		
	}
	
	public function updateName($name) {
		$this->display_name = $name;
		$db = getDB();
		$stat = $db->prepare('UPDATE user_account SET display_name=:display_name WHERE id=:id');
		$stat->execute(array('display_name'=>$name,'id'=>$this->id));		
	}
	
	public function getNewSessionID() {
		$db = getDB();
		$s1 = $db->prepare('INSERT INTO user_session (user_account_id,id,created_at,last_used_at) '.
					'VALUES (:uid,:id,:created_at,:last_used_at)');
		$s1->bindValue('uid', $this->id);
		$s1->bindValue('created_at', date("Y-m-d H:i:s"));
		$s1->bindValue('last_used_at', date("Y-m-d H:i:s"));
		try {
			$id = getRandomString(mt_rand(10,100));
			$s1->bindValue('id', $id);
			$s1->execute();
			return $id;
		} catch (Exception $e) {
			// TODO: Catch non-unique id			
			throw $e;
		}
	}

	public function getForgottenPasswordCode() {
		// TODO should really use forgotten_password_code_generated_at here and in checkForgottenPasswordCode()
		if ($this->forgotten_password_code) return $this->forgotten_password_code;

		$code = getRandomString(20);

		$db = getDB();
		$s = $db->prepare("UPDATE user_account SET forgotten_password_code=:c, forgotten_password_code_generated_at=:at WHERE id=:id");
		$s->execute(array('c'=>$code, 'id'=>$this->id,'at'=>date("Y-m-d H:i:s")));

		$this->forgotten_password_code = $code;
		return $code;

	}	
	
	public function checkForgottenPasswordCode($code) {
		return ($this->forgotten_password_code && ($this->forgotten_password_code == $code));
	}	
	
	public function makeAdmin() {
		$this->administrator = true;
		$db = getDB();
		$stat = $db->prepare('UPDATE user_account SET administrator=1 WHERE id=:id');
		$stat->execute(array('id'=>$this->id));		
	}
	
	public function makeSystemAdmin() {
		$this->system_administrator = true;
		$db = getDB();
		$stat = $db->prepare('UPDATE user_account SET system_administrator=1 WHERE id=:id');
		$stat->execute(array('id'=>$this->id));		
	}
	
	public function removeAdmin() {
		$this->administrator = true;
		$db = getDB();
		$stat = $db->prepare('UPDATE user_account SET administrator=0, system_administrator=0  WHERE id=:id');
		$stat->execute(array('id'=>$this->id));		
	}
	
	public function removeSystemAdmin() {
		$this->system_administrator = true;
		$db = getDB();
		$stat = $db->prepare('UPDATE user_account SET system_administrator=0, administrator=1 WHERE id=:id');
		$stat->execute(array('id'=>$this->id));		
	}
	
	public function enable() {
		$this->enabled = 1;
		$db = getDB();
		$stat = $db->prepare('UPDATE user_account SET enabled=1 WHERE id=:id');
		$stat->execute(array('id'=>$this->id));		
	}		

	public function disable() {
		$this->enabled = 0;
		$db = getDB();
		$stat = $db->prepare('UPDATE user_account SET enabled=0 WHERE id=:id');
		$stat->execute(array('id'=>$this->id));		
	}		
	
	public function calculateAndCacheScore() {
		$db = getDB();
		# get score
		$stat = $db->prepare("SELECT score FROM feature_checkin_success WHERE user_account_id=:id");
		$stat->execute(array('id'=>$this->id));
		$score = 0;
		while ($d = $stat->fetch()) {
			$score += $d['score'];
		}
		# write score
		$stat = $db->prepare("UPDATE user_account SET cached_score=:s WHERE id=:id");
		$stat->execute(array('id'=>$this->id,'s'=>$score));
	}
}

class UserException extends Exception {};
class UserExceptionEmailNotValid extends UserException {};
class UserExceptionPasswordsToShort extends UserException {};
class UserExceptionPasswordsDontMatch extends UserException {};
class UserExceptionEmailAlreadyKnown extends UserException {};
