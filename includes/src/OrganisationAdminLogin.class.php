<?php
/**
 * @author James Baster  <james@jarofgreen.co.uk>
 * @copyright City of Edinburgh Council & James Baster
 * @license Open Source under the 3-clause BSD License
 * @url https://github.com/City-Outdoors/City-Outdoors-Web
 */

class OrganisationAdminLogin  {
	
	/** @var User **/
	protected $user;
	protected $organisationID;
	
	protected $allowedOrganisations = array();
	
	/** @var Organisation **/
	protected $organisation;
			
	function __construct(User $user, $organisationID) {
		$this->user = $user;
		$this->organisationID = $organisationID;
		
		$organisationSearch = new OrganisationSearch();
		$organisationSearch->setAdminUser($user);
		
		while($organisation = $organisationSearch->nextResult()) {
			$this->allowedOrganisations[$organisation->getId()] = $organisation;
		}
		
		if (intval($organisationID) && array_key_exists(intval($organisationID), $this->allowedOrganisations)) {
			$this->organisation = $this->allowedOrganisations[intval($organisationID)];
		}
		
	}

	function isLoggedIntoOrganisation() {
		return (boolean)$this->organisation;
	}
	
	function getOrganisation() {
		return $this->organisation;
	}
	
	function getAllowedOrganisations() {
		return $this->allowedOrganisations;	
	}
	
	function setSmartyVariables(Smarty $smarty) {
		$smarty->assign('currentOrganisation',$this->organisation);
		$smarty->assign('allowedOrganisations',$this->allowedOrganisations);
	}
}

