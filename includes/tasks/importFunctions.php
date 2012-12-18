<?php
/**
 * @author James Baster  <james@jarofgreen.co.uk>
 * @copyright City of Edinburgh Council & James Baster
 * @license Open Source under the 3-clause BSD License
 * @url https://github.com/City-Outdoors/City-Outdoors-Web
 */


## http://stackoverflow.com/questions/5868480/how-should-i-get-a-divs-content-like-this-using-dom-in-php
function DOMinnerHTML($element) 
{ 
    $innerHTML = ""; 
    $children = $element->childNodes; 
    foreach ($children as $child) 
    { 
		$tmp_dom = new DOMDocument(); 
        $tmp_dom->appendChild($tmp_dom->importNode($child, true)); 
        $innerHTML.=trim($tmp_dom->saveHTML()); 
    } 
    return $innerHTML; 
} 

