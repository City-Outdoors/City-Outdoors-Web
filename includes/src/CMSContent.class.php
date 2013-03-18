<?php
/**
 * @author James Baster  <james@jarofgreen.co.uk>
 * @copyright City of Edinburgh Council & James Baster
 * @license Open Source under the 3-clause BSD License
 * @url https://github.com/City-Outdoors/City-Outdoors-Web
 */




class CMSContent extends BaseDataWithOneID {

	protected $page_slug;
	protected $page_title;
	protected $block_slug;
	protected $imported;
	
	protected $latest_version;
	protected $latest_html;
	
	public static function createPage($slug, $title, User $user) {
		$db = getDB();
		
		$stat1 = $db->prepare("INSERT INTO cms_content (page_slug, page_title) VALUES (:page_slug, :page_title)");
		$stat1->bindValue('page_slug', $slug);
		$stat1->bindValue('page_title', $title);
		
		$stat2 = $db->prepare("INSERT INTO cms_content_version (cms_content_id,version,html,created_at,created_by) VALUES (:cms_content_id,0,'',:created_at,:created_by)");
		$stat2->bindValue('created_at', date('Y-m-d H:i:s'));
		$stat2->bindValue('created_by', $user->getId());
		
		try {
			$db->beginTransaction();
			
			$stat1->execute();
			$id = $db->lastInsertId();
			
			$stat2->bindParam('cms_content_id', $id);
			$stat2->execute();

			$db->commit();
		} catch (Exception $e) {
			$db->rollBack();
			// TODO check for dulpicate slugs!
			throw $e;
		}
		
		return $id;
	}
	
	/** @return CMSContent **/
	public static function createBlock($slug, User $user) {
		$db = getDB();
		
		$stat1 = $db->prepare("INSERT INTO cms_content (block_slug) VALUES (:block_slug)");
		$stat1->bindValue('block_slug', $slug);
		
		$stat2 = $db->prepare("INSERT INTO cms_content_version (cms_content_id,version,html,created_at,created_by) VALUES (:cms_content_id,0,'',:created_at,:created_by)");
		$stat2->bindValue('created_at', date('Y-m-d H:i:s'));
		$stat2->bindValue('created_by', $user->getId());
		
		try {
			$db->beginTransaction();
			
			$stat1->execute();
			$id = $db->lastInsertId();
			
			$stat2->bindParam('cms_content_id', $id);
			$stat2->execute();

			$db->commit();
		} catch (Exception $e) {
			$db->rollBack();
			// TODO check for dulpicate slugs!
			throw $e;
		}
		
		return new CMSContent(array('id'=>$id, 'block_slug'=>$slug));
	}
	
	public static function loadPageByID($id) {
		$db = getDB();
		$stat = $db->prepare('SELECT * FROM cms_content WHERE id=:id AND page_slug IS NOT NULL');
		$stat->bindValue('id', $id);
		$stat->execute();
		if($stat->rowCount() == 1) {
			return new CMSContent($stat->fetch(PDO::FETCH_ASSOC));
		}		
	}	
	
	public static function loadPageBySlug($slug) {
		$db = getDB();
		$stat = $db->prepare('SELECT * FROM cms_content WHERE page_slug = :slug');
		$stat->bindValue('slug', $slug);
		$stat->execute();
		if($stat->rowCount() == 1) {
			return new CMSContent($stat->fetch(PDO::FETCH_ASSOC));
		}		
	}	
	
	public static function loadBlockBySlug($slug) {
		$db = getDB();
		$stat = $db->prepare('SELECT * FROM cms_content WHERE block_slug = :slug');
		$stat->bindValue('slug', $slug);
		$stat->execute();
		if($stat->rowCount() == 1) {
			return new CMSContent($stat->fetch(PDO::FETCH_ASSOC));
		}
	}	
	
	public static function renderBlock($slug) {
		$user = getCurrentUser();
		$preamble = '';
		if ($user && $user->isAdministrator()) {
			$preamble = '<div class="inplace-admin-info">Block '.$slug.' appears here. <a href="/admin/editCMSBlock.php?s='.$slug.'">Edit</a></div>';
		}
		$db = getDB();
		$stat = $db->prepare('SELECT * FROM cms_content WHERE block_slug = :slug');
		$stat->bindValue('slug', $slug);
		$stat->execute();
		if($stat->rowCount() == 1) {
			$block = new CMSContent($stat->fetch(PDO::FETCH_ASSOC));
			return $preamble . $block->getLatestVersionHTML();
		} else {
			return $preamble . '&nbsp;<!-- NO BLOCK '.$slug.'-->';
		}
	}		
	
	public function __construct($data) {
		parent::__construct($data);
		if ($data && isset($data['page_title'])) $this->page_title = $data['page_title'];
		if ($data && isset($data['page_slug'])) $this->page_slug = $data['page_slug'];
		if ($data && isset($data['imported'])) $this->imported = $data['imported'];
		if ($data && isset($data['block_slug'])) $this->block_slug = $data['block_slug'];
	}	
	public function getPageTitle() { return $this->page_title; }
	public function getPageSlug() { return $this->page_slug; }
	public function getBlockSlug() { return $this->block_slug; }
	public function getIsImported() { return $this->imported; }
	
	protected function loadLatestContent() {
		$db = getDB();
		$stat = $db->prepare('SELECT * FROM cms_content_version WHERE cms_content_id=:id ORDER BY version DESC LIMIT 1');
		$stat->bindValue('id', $this->id);
		$stat->execute();
		if($stat->rowCount() == 1) {
			$data = $stat->fetch(PDO::FETCH_ASSOC);
			$this->latest_version = $data['version'];
			$this->latest_html = $data['html'];
		}		
	}
	
	public function getLatestVersionHTML() { 
		$this->loadLatestContent();
		return $this->latest_html; 
	}
	
	
	public function newVersion($html, User $user) {
		$db = getDB();
		$stat2 = $db->prepare("INSERT INTO cms_content_version (cms_content_id,version,html,created_at,created_by) ".
				"(SELECT cms_content_id, version+1 AS version, :html, :created_at,:created_by FROM cms_content_version WHERE cms_content_id=:id  ORDER BY version DESC LIMIT 1)");
		$stat2->bindValue('created_at', date('Y-m-d H:i:s'));
		$stat2->bindValue('created_by', $user->getId());
		$stat2->bindValue('html', $html);
		$stat2->bindValue('id', $this->id);
		$stat2->execute();
	}
	

	public function setImported($imported) {
		$this->imported = $imported;
		$db = getDB();
		$stat = $db->prepare("UPDATE cms_content SET imported=:i WHERE id=:id");
		$stat->execute(array('i'=>($imported?1:0),'id'=>$this->id));
	}
	
	
}

