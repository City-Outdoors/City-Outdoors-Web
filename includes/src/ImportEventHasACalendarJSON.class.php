<?php
/**
 * @author James Baster  <james@jarofgreen.co.uk>
 * @copyright City of Edinburgh Council & James Baster
 * @license Open Source under the 3-clause BSD License
 * @url https://github.com/City-Outdoors/City-Outdoors-Web
 */

class ImportEventHasACalendarJSON {
	
	protected $title;
	protected $url;
	protected $data;
	protected $user;
	protected $searchCollection;


	public function __construct($title, $url, User $user, Collection $searchCollection = null) {
		$this->title = $title;
		$this->url = $url;
		$this->user = $user;
		$this->searchCollection = $searchCollection;
	}
	
	
	public function import() {
		$this->getFile();
		$this->parseData();
	}
	
	public function getFile() {
		$ch = curl_init();	
		curl_setopt($ch, CURLOPT_URL, $this->url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_USERAGENT, 'City Outdoors');
		$this->data = json_decode(curl_exec($ch));
		curl_close($ch);
	}
	
	public function parseData() {
		if (!is_object($this->data)) {
			return;
		}
		foreach($this->data->data as $data) {
			$event = Event::loadByImportDetails($this->title, $data->slug);
			
			if (!$event) {
				$event = new Event();
				$event->setImportSource($this->title);
				$event->setImportId($data->slug);
			}
			
			$event->setTitle($data->summaryDisplay);
			$event->setDescriptionText($data->description);
			$event->setStartAtTimestamp($data->start->timestamp);
			$event->setEndAtTimestamp($data->end->timestamp);
			
			
			if ($this->searchCollection) {
				$itemSearch = new ItemSearch();
				$itemSearch->inCollection($this->searchCollection);
				while($item = $itemSearch->nextResult()) {
					if (stripos($data->description, $item->getTitle()) !== FALSE) {
						$event->addFeature($item->getFeature());
					}
				}
			}
			
			$event->writeToDataBase($this->user);
						
		}
	}
	
	
}