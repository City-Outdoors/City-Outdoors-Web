<?php
/**
 * @author James Baster  <james@jarofgreen.co.uk>
 * @copyright City of Edinburgh Council & James Baster
 * @license Open Source under the 3-clause BSD License
 * @url https://github.com/City-Outdoors/City-Outdoors-Web
 */




class ImportJADU {

	protected $sourceWebsite;
	protected $apiKey;
	protected $useSSL = false;
	
	protected $printVerbose = true;
	
	public function __construct($sourceWebsite,$apiKey) {
		$this->sourceWebsite = $sourceWebsite;
		$this->apiKey = $apiKey;
	}

	protected function log($message) {
		if ($this->printVerbose) {
			print $message."\n";
		}
	}

	
	public function importCollection($directoryID, Collection $collection, User $userToWrite,  
			$locationField, $mapToFieldID, $mapPageURLToFieldID, $imageFields, $imageImportDataFile, Collection $parentCollection = null, $fieldNameMapsToParent=null) {
		
		$page = 1;
		$totalPages = 1;
		while($page <= $totalPages) {
			$url = 'http'.($this->useSSL?'s':'').'://'.$this->sourceWebsite.'/api/directories/'.$directoryID.'/entries.xml'.
					'?api_key='.$this->apiKey.'&per_page=100&page='.$page;

			$this->log("Fetching: ".$url);
			$ch = curl_init();	
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
			curl_setopt($ch, CURLOPT_USERAGENT, 'City Outdoors');
			$rawData = curl_exec($ch);
			curl_close($ch);

			$dom = new DOMDocument();
			$dom->loadXML($rawData);

			#Load total number of pages
			$entriesList = $dom->getElementsByTagName('entries');
			if ($entriesList->length > 0) {
				$totalPages = $entriesList->item(0)->getAttribute('pages');
			}

			foreach($dom->getElementsByTagName('entry') as $domElement) {
				$id = $domElement->getAttribute('id');
				$title = null;
				$parentItem = null;
				$fields = array();
				# title
				$titleElements = $domElement->getElementsByTagName('title');
				if ($titleElements->length > 0) {
					$title = DOMinnerHTML($titleElements->item(0));
				}
				#data
				foreach($domElement->getElementsByTagName('field') as $fieldElement) {
					$name = $fieldElement->getAttribute('name');
					$data = DOMinnerHTML($fieldElement);
					$data = html_entity_decode($data,ENT_COMPAT,'UTF-8');
					$fields[trim($name)] = $data;
				}

				$this->log("Found Item Source ID: ".$id." Titled:".$title);

				list($lat,$lng) = explode(",", $fields[$locationField]);
				if ($lat && $lng) {

					# New item or load existing?			
					$itemSearch = new ItemSearch();
					$itemSearch->inCollection($collection);
					$itemSearch->titleMatches($collection, $title);
					
							
					if ($itemSearch->num() > 0) {
						$item = $itemSearch->nextResult();
						$this->log(" ... found old item ".$item->getSlug());
					} else {						
						$item = $collection->getBlankItem($userToWrite);
						$this->log(" ... new item");
					}
					
					if ($parentCollection && !$item->getParentItemID()) {
						$this->log(" ... finding parent ID");
						$parentItem = $this->findParentItemInCollectionFor(
								$parentCollection, 
								$lat, 
								$lng, 
								isset($fields[$fieldNameMapsToParent]) ? $fields[$fieldNameMapsToParent] : null
							);
					}

					# Position
					$item->setPosition($lat, $lng);

					# title field
					$titleField = $item->getTitleField();
					if ($titleField) $titleField->update($title);

					# map other data fields
					foreach($mapToFieldID as $k=>$v) {
						$k = trim($k);
						$v = trim($v);
						if (isset($fields[$k]) && $fields[$k]) {
							$f = $item->getFieldByTitle($v);
							if ($f) {
								if (get_class($f) == 'ItemFieldEmail') {
									$f->updateFromJadu($fields[$k], $userToWrite);
								} else {
									$f->update($fields[$k], $userToWrite);
								}
							} else {
								$this->log("Failed to find field by title: ".$v);
							}
						} else {
							$this->log("No value for field: ".$k);
						}
					}

					# map other data fields,'imageURLSSeen'=>$imageURLSSeen
					foreach($mapPageURLToFieldID as $k=>$v) {
						if (isset($fields[$k]) && $fields[$k]) {
							$f = $item->getFieldByTitle($v);
							if ($f) $this->importDocumentFromURLToField($fields[$k],$f,$userToWrite);
						}
					}

					# save!
					$errors = $item->getValidationErrors();
					if (count($errors)) {
						throw new Exception("Validation Errors on Source ID ".$id." where ".var_export($errors,true));
					} else {
						$item->writeToDataBase($userToWrite);
						$dataMapping[$id] = $item->getId();
					}
					if ($parentItem) {
						$item->setChildOf($parentItem);
						$parentItem->getFeature()->expandToIncludeFeature($item->getFeature());
					}

					/**
					 * WE DON@T IMPORT IMAGES FROM DIRECTORY NOW, THEY ARE TO SMALL.
					# image fields become feature content (done after save so feature is there)
					foreach($imageFields as $fieldName) {
						if (isset($fields[$fieldName]) && $fields[$fieldName]) {
							$this->importImageFromURLToFeature($fields[$fieldName], $item->getFeature(), $userToWrite, $imageImportDataFile);
						}
					}
					 * 
					 */

				} else {
					$this->log(".. No Lat/Lng so skipped!");
				}
			}
			$page++;
		}
		
	}
	
	
	public function importImageFromURLToFeature($url, Feature $feature, User $userToWrite, $imageImportDataFile) {
		# Load images already imported
		$imageURLSSeen = array();
		if (file_exists($imageImportDataFile)) {
			$this->log("Loading Old Image Data File");
			$d = json_decode(file_get_contents($imageImportDataFile));
			foreach(get_object_vars($d->imageURLSSeen) as $urlFromDataFile=>$checksum) $imageURLSSeen[$urlFromDataFile] = $checksum;
		} else {
			$this->log("Making New Image Data File");

		}
		
		# import??
		$importThisImage = false;
		if (array_key_exists($url, $imageURLSSeen)) {
			$this->log("  ... skipping already seen image ".$url);
			// TODO download and check MD5();
		} else {
			$importThisImage = true;
		}
		if ($importThisImage) {
			$tmpFileName = tempnam("/tmp","jaduImport");
			$imageRecognised = false;
			if (strtolower(substr($url,-4)) == '.jpg') {
				$tmpFileName .= ".jpg";
				$imageRecognised = true;
			} else {
				$this->log("  ... SKIPPING IMAGE ".$url);
			}				
			if ($imageRecognised) {
				$this->log("  ... getting image ".$url);
				$ch = curl_init();	
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
				curl_setopt($ch, CURLOPT_USERAGENT, 'Heres-a-Tree');
				$rawData = curl_exec($ch);
				curl_close($ch);
				file_put_contents($tmpFileName, $rawData);
				$imageURLSSeen[$url] = md5_file($tmpFileName);
				$urlBits = explode("/", $url);
				$featureContent = $feature->newContent("Imported", $userToWrite);
				$featureContent->newImage($urlBits[count($urlBits)-1], $tmpFileName, false);
				//unlink($tmpFileName);
			}
		}
		
		# Save Back
		$out = array('imageURLSSeen'=>$imageURLSSeen);
		file_put_contents($imageImportDataFile , json_encode($out));
		sleep(2);
	}
	
	
	public function importDocumentFromURLToField($url, $field, User $userToWrite) {
		
		$start = "http://".$this->sourceWebsite."/info/";
		if (substr($url,0,  strlen($start)) == $start) {


			list($dunno1,$dunno2,$documentID,$dunno3,$documentPageNumber) = explode("/", substr($url,strlen($start)));

			$this->log(" ... from page URL ".$url." got document ".$documentID." and page ".$documentPageNumber."!\n");

			$url = 'http'.($this->useSSL?'s':'').'://'.$this->sourceWebsite.'/api/documents/'.$documentID.'/pages.xml'.
					'?api_key='.$this->apiKey.'&per_page=100';
			$this->log("  ... Getting ".$url."\n");

			$ch = curl_init();	
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
			curl_setopt($ch, CURLOPT_USERAGENT, 'Heres-a-Tree');
			$rawData = curl_exec($ch);
			curl_close($ch);

			$domOfDocument = new DOMDocument();
			$domOfDocument->loadXML($rawData);

			$found = false;
			foreach($domOfDocument->getElementsByTagName('page') as $domElement) {
				if ($domElement->getAttribute('page_number') == $documentPageNumber) {
					$content = $domElement->getElementsByTagName('content');
					if ($content->length > 0) {
						$html = DOMinnerHTML($content->item(0));
						$field->update($html, $userToWrite);
						$found = true;
					}
				}							
			}
			$this->log("  ... Data was ".($found?"found":"NOT FOUND"));

		} else {

			$this->log(" ... found page URL ".$url." but match failed!\n");

		}
	}
	
	public function importDocumentFromIDToCMSContent($documentID, $documentPageNumber, CMSContent $cmscontent, User $user) {

		$this->log("Importing ".$documentID." page ".$documentPageNumber. " to ".$cmscontent->getPageSlug()." user ".$user->getId());
		
		$url = 'http'.($this->useSSL?'s':'').'://'.$this->sourceWebsite.'/api/documents/'.$documentID.'/pages.xml'.
				'?api_key='.$this->apiKey.'&per_page=100';
		$this->log("  ... Getting ".$url."\n");

		$ch = curl_init();	
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_USERAGENT, 'Heres-a-Tree');
		$rawData = curl_exec($ch);
		curl_close($ch);

		$domOfDocument = new DOMDocument();
		$domOfDocument->loadXML($rawData);

		$found = false;
		foreach($domOfDocument->getElementsByTagName('page') as $domElement) {
			if ($domElement->getAttribute('page_number') == $documentPageNumber) {
				$content = $domElement->getElementsByTagName('content');
				if ($content->length > 0) {
					$html =  DOMinnerHTML($content->item(0));
					if ($cmscontent->getLatestVersionHTML() != $html) {
						$cmscontent->newVersion($html, $user);
						$cmscontent->setImported(true);
					}
				}
			}							
		}

	}
	
	public function findParentItemInCollectionFor(Collection $parentCollection, $lat, $lng, $titleOfItem) {
		# can we find by title
		if ($titleOfItem) {
			if ($titleOfItem == 'Princes Street Gardens East') $titleOfItem = 'Princes Street Gardens';
			if ($titleOfItem == 'Princes Street Gardens West') $titleOfItem = 'Princes Street Gardens';
			if ($titleOfItem == 'West Princes Street Gardens') $titleOfItem = 'Princes Street Gardens';
			if ($titleOfItem == 'East Princes Street Gardens') $titleOfItem = 'Princes Street Gardens';
			
			$itemSearch = new ItemSearch();
			$itemSearch->inCollection($parentCollection);
			$itemSearch->fieldSearch($parentCollection->getTitleField(), $titleOfItem);
			if ($itemSearch->num() > 0) {
				return $itemSearch->nextResult();
			}
		}

		# just give up and find geographically
		$itemSearch = new ItemSearch();
		$itemSearch->inCollection($parentCollection);
		$itemSearch->closestTo($lat, $lng);
		return $itemSearch->nextResult();		
	}
	
}




