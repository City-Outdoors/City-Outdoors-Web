<?php
/**
 * @author James Baster  <james@jarofgreen.co.uk>
 * @copyright City of Edinburgh Council & James Baster
 * @license Open Source under the 3-clause BSD License
 * @url https://github.com/City-Outdoors/City-Outdoors-Web
 */

class SmartyHelper {
	
	public static function showFields(Item $item, $contentArea) {
		
		$out = '<dl>';
		foreach($item->getFields() as $field) {
			if ($field->isInContentArea($contentArea) && $field->hasValue()) {
				$out .= '<dt>'.htmlentities($field->getTitle()).'</dt>';
				$out .= '<dd>'.$field->getValueAsHumanReadableHTML().'</dd>';
			}
		} 
		return $out.'</dl>';
		
	}
	
	public static function showFieldsNoHeader(Item $item, $contentArea) {
		
		$out = '';
		foreach($item->getFields() as $field) {
			if ($field->isInContentArea($contentArea) && $field->hasValue()) {
				$out .= '<dd>'.$field->getValueAsHumanReadableHTML().'</dd>';
			}
		} 
		return $out.'';
		
	}
	

	public static function hasFieldsInArea(Item $item, $contentArea) {
		
		foreach($item->getFields() as $field) {
			if ($field->isInContentArea($contentArea) && $field->hasValue()) {
				return true;
			}
		} 
		return false;
		
	}
	
}


