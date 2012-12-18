<?php
/**
 * @author James Baster  <james@jarofgreen.co.uk>
 * @copyright City of Edinburgh Council & James Baster
 * @license Open Source under the 3-clause BSD License
 * @url https://github.com/City-Outdoors/City-Outdoors-Web
 */
require 'includes/src/global.php';

if (!session_id()) session_start();
unset($_SESSION['userID']);
		
header("Location: /");
