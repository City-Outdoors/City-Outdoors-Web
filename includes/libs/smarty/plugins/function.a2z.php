<?php
/**
* Prints out A- Z with a link
* Based on http://www.smarty.net/forums/viewtopic.php?p=60176 and rewritten
*/

function smarty_function_a2z($params=array(), $template) {
   if (empty($params['url'])) $params['url'] = 'letter.php';
   if (empty($params['name'])) $params['name'] = 'letter';
   if (empty($params['active'])) $params['active'] = '';
   
   $links = '<ul class="atoz cf">';
   $active = (''==$params['active'])?' class="active"':'';
   
   for($letter = ord('A'); $letter <= ord('Z'); ++$letter) {
      $alphabet = chr($letter);
      $active = ($alphabet==$params['active'])?' class="active"':'';
      $links .= "<li{$active}><a href='{$params['url']}&{$params['name']}={$alphabet}'>{$alphabet}</a></li>";
   }
   return $links.'</ul>';
} 

